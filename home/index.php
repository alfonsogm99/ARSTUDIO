<?php
session_start();

include './admin/config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: " . BASE_URL . "/login/");
    exit;
}

$data = json_decode(file_get_contents('./admin/config.json'), true) ?? [];

if (isset($_POST['theme'])) {
    $newTheme = $_POST['theme'];
    // Guardar solo si cambió
    if (!isset($data['theme']) || $data['theme'] !== $newTheme) {
        $data['theme'] = $newTheme;
        file_put_contents('./admin/config.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}

$theme = $data['theme'] ?? 'dark';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// =======================
// 🔹 Generar token CSRF
// =======================
if(!isset($_SESSION['csrf_token'])){
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// =======================
// 🔹 Filtros GET con validación
// =======================

// Filtro barbero
$barberoFiltro = isset($_GET['barbero']) ? intval($_GET['barbero']) : 0;

// Filtro estado
$estadoFiltro = $_GET['estado'] ?? '';
$estadosValidos = ['pendiente','confirmada','cancelada'];
if(!in_array($estadoFiltro, $estadosValidos)){
    $estadoFiltro = '';
}

// =======================
// 🔹 Validación de fechas
// =======================
$year = isset($_GET['year']) ? intval($_GET['year']) : intval(date('Y'));
$month = isset($_GET['month']) ? intval($_GET['month']) : intval(date('m'));
$day = isset($_GET['day']) ? intval($_GET['day']) : null;

// Validar mes y día
if($month < 1 || $month > 12) $month = intval(date('m'));
if($day && !checkdate($month, $day, $year)) $day = null;

// Fechas de inicio y fin de mes
$firstDay = date('Y-m-01', strtotime("$year-$month-01"));
$lastDay = date('Y-m-t', strtotime($firstDay));
$selectedDate = $day ? date('Y-m-d', strtotime("$year-$month-$day")) : null;

// =======================
// 🔹 Construcción de SQL con prepared statements
// =======================
function buildFilters($conn, $user_type, $user_id, $barberoFiltro, $estadoFiltro, $selectedDate, $firstDay, $lastDay, $day) {

    $types = '';
    $where = [];

    if($user_type === 'barbero'){
        $where[] = 'c.id_barbero = ?';
        $types .= 'i';
        $params[] = $user_id;
    } elseif($barberoFiltro > 0){
        $where[] = 'c.id_barbero = ?';
        $types .= 'i';
        $params[] = $barberoFiltro;
    }

    if($estadoFiltro !== ''){
        $where[] = 'c.estado = ?';
        $types .= 's';
        $params[] = $estadoFiltro;
    }

    if($day && $selectedDate){
        $where[] = 'c.fecha = ?';
        $types .= 's';
        $params[] = $selectedDate;
    } else {
        $where[] = 'c.fecha BETWEEN ? AND ?';
        $types .= 'ss';
        $params[] = $firstDay;
        $params[] = $lastDay;
    }

    $whereSQL = implode(' AND ', $where);

    if($user_type === 'barbero'){
        $sql = "SELECT c.*, cl.nombre AS cliente_nombre 
                FROM cita c
                JOIN cliente cl ON cl.id_cliente = c.id_cliente
                WHERE $whereSQL
                ORDER BY " . ($day ? "c.hora ASC" : "c.fecha, c.hora ASC");
    } else {
        $sql = "SELECT c.*, cl.nombre AS cliente_nombre, b.nombre AS barbero_nombre
                FROM cita c
                JOIN cliente cl ON cl.id_cliente = c.id_cliente
                JOIN barbero b ON b.id_barbero = c.id_barbero
                WHERE $whereSQL
                ORDER BY " . ($day ? "c.hora ASC" : "c.fecha, c.hora ASC");
    }

    return [$sql, $types, $params];
}

// Preparar consulta
list($sql, $types, $params) = buildFilters($conn, $user_type, $user_id, $barberoFiltro, $estadoFiltro, $selectedDate, $firstDay, $lastDay, $day);
$stmt = $conn->prepare($sql);
if($types){
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$citas = $stmt->get_result();

// =======================
// 🔹 Convertir a JS con HTML escapado
// =======================
$appointments = [];
while($row = $citas->fetch_assoc()){
    $title = htmlspecialchars($row['cliente_nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if($user_type === 'admin' && isset($row['barbero_nombre'])){
        $title .= " (" . htmlspecialchars($row['barbero_nombre'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ")";
    }

    $appointments[] = [
        'id' => $row['id_cita'],
        'fecha' => $row['fecha'],
        'hora' => $row['hora'],
        'cliente' => $title,
        'estado' => $row['estado'],
        'duracion' => $row['duracion_total'],
        'estado_pago' => $row['estado_pago'],
        'notas' => htmlspecialchars($row['notas'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')             
    ];
}

// =======================
// 🔹 Arrays de nombres
// =======================
$dayNames = ['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];
$monthNames = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../img/favicon.ico">
<link rel="stylesheet" href="./css/style.css">
<title>ARSTUDIO · Calendario</title>
</head>
<body id="calendar" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include 'includes/navbar.php'; ?>

<button id="toggle-filters">
    <i class="fas fa-filter"></i>
</button>

<div id="floating-filter">
    <div class="filter-group">
        <label>Cita:</label>
        <div class="filter-options estado-options">
            <button data-estado="" class="filter-btn todos active">
                Todas
                <span class="label-color todos"></span>
            </button>
            <button data-estado="pendiente" class="filter-btn pendiente">
                Pendiente
                <span class="label-color pendiente"></span>
            </button>
            <button data-estado="confirmada" class="filter-btn confirmada">
                Confirmada
                <span class="label-color confirmada"></span>
            </button>
            <button data-estado="cancelada" class="filter-btn cancelada">
                Cancelada
                <span class="label-color cancelada"></span>
            </button>
        </div>
    </div>

    <div class="filter-group">
        <br>
        <label>Barbero:</label>
        <div class="filter-options barbero-options">
            
            <button data-barbero="" class="filter-btn active">
                Todos
                <span class="label-color"></span>
            </button>

            <?php
            $barberos = $conn->query("SELECT id_barbero, nombre FROM barbero");
            while($b = $barberos->fetch_assoc()):
                $selected = ($barberoFiltro == $b['id_barbero']) ? 'active' : '';
            ?>
            <button data-barbero="<?=$b['id_barbero']?>" class="filter-btn <?=$selected?>">
                <?=$b['nombre']?>
                <span class="label-color" data-barbero></span>
            </button>
            <?php endwhile; ?>

        </div>
    </div>
</div>

<h2 class="calendar-header">

<?php if($day): ?>
    <span class="calendar-title"><?=date('d/m/Y', strtotime($selectedDate))?></span>
    <div class="calendar_btn back-btn">
        <a href="?year=<?=$year?>&month=<?=$month?>&estado=<?=$estadoFiltro?>&barbero=<?=$barberoFiltro?>">Volver</a>
    </div>
<?php else: ?>
<div class="calendar_btn month-navigation">
    <a href="?month=<?=($month-1<1?12:$month-1)?>&year=<?=($month-1<1?$year-1:$year)?>&estado=<?=$estadoFiltro?>&barbero=<?=$barberoFiltro?>">
        &lt;
    </a>

    <span class="calendar-title">
        <?=$monthNames[intval($month)]?> <?=$year?>
    </span>

    <a href="?month=<?=($month+1>12?1:$month+1)?>&year=<?=($month+1>12?$year+1:$year)?>&estado=<?=$estadoFiltro?>&barbero=<?=$barberoFiltro?>">
        &gt;
    </a>
</div>
<?php endif; ?>
</h2>

<!-- fixed end -->
</div>

<?php if(!$day): ?>
<!-- 🔹 VISTA MENSUAL -->
 <div class="calendar">

    <?php foreach($dayNames as $dn): ?>
        <div class="day-header"><?=$dn?></div>
    <?php endforeach; ?>

    <?php
    $startDayOfWeek = date('N', strtotime($firstDay));
    $totalDays = date('t', strtotime($firstDay));
    $currentDay = 1;

    // Espacios vacíos antes del primer día
    for($i=1; $i<$startDayOfWeek; $i++) echo '<div class="day-cell empty"></div>';

    while($currentDay<=$totalDays){
        $date = date('Y-m-d', strtotime("$year-$month-$currentDay"));
        $dayName = $dayNames[date('N', strtotime($date)) - 1];

        echo '<div class="day-cell" 
                data-date="'.$date.'" 
                data-day-name="'.$dayName.'">
                '.$currentDay.'
            </div>';
        $currentDay++;
    }

    // Espacios vacíos al final
    $cellsFilled = ($startDayOfWeek-1 + $totalDays);
    for($i=$cellsFilled; $i%7!=0; $i++) echo '<div class="day-cell empty"></div>';

    ?>
</div>

<?php else: ?>
<!-- 🔹 VISTA DIARIA -->

<div class="day-view-content">
<div class="day-view">
<?php
$inicio = 9;
$fin = 20;

for($h = $inicio; $h < $fin; $h++){
    foreach([0,15,30,45] as $m){
        $hora = sprintf('%02d:%02d:00', $h, $m);
        echo '<div class="hour-slot" data-hora="'.$hora.'">'.substr($hora,0,5).'</div>';
    }
}
?>
</div>

<div class="logscroll">
    <img src="../img/letters_calendar.png" class="logscroll_let">
    <div class="logscroll_str"></div>
</div>

</div>

<?php endif; ?>

<!-- 🔹 MODAL -->
<div id="modal_cita">
    <div class="modal_content professional">
        <header class="modal_header">
            <h3 id="modal_title"></h3>
        </header>

        <div class="modal_body" id="modal_info"></div>

        <footer class="modal_footer">
            <button id="btn_primary" class="btn-primary"></button>
            <button id="btn_delete" class="btn-danger"></button>
            <button onclick="cerrarModal()" class="btn-secondary">Cerrar</button>
        </footer>
    </div>
</div>

<?php
$configFile = './admin/config.json';

$config = [];

if (file_exists($configFile)) {
    $json = file_get_contents($configFile);
    $decoded = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $config = $decoded;
    }
}

$version = $config['app']['version'] ?? '0.0.0';
?>

<footer class="version">
    <p>v.<?= htmlspecialchars($version) ?></p>
</footer>

<footer class="copyright">
    <p>© 2026 alfonsogm</p>
</footer>

<script>

    const stars = document.querySelector(".logscroll_str");
    const speedFactor = 0.5;

    window.addEventListener("scroll", () => {

        const scrollTop = window.scrollY;
        const maxScroll = document.body.scrollHeight - window.innerHeight;

        const progress = scrollTop / maxScroll;
        
        const rotation = progress * 360 * speedFactor;

        stars.style.transform = `rotate(${rotation}deg)`;
    });

</script>

<script>
window.calendarData = {
    appointments: <?php echo json_encode($appointments); ?>,
    csrfToken: "<?=$csrf_token?>",
    year: <?=$year?>,
    month: <?=$month?>,
    day: <?=($day ?? 'null')?>,
    estadoFiltro: "<?=$estadoFiltro?>",
    barberoFiltro: "<?=$barberoFiltro?>"
};
</script>
<script src="../js/calendar.js"></script>
<script src="../js/lightmode.js"></script>

</body>
</html>