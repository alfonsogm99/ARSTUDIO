<?php

require_once './admin/bootstrap.php';
require_once './admin/auth.php';

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

// Filtro barbero, por defecto id barbero = 1
$barberoFiltro = isset($_GET['barbero']) && intval($_GET['barbero']) > 0
    ? intval($_GET['barbero'])
    : 1;

// Filtro estado
$estadoFiltro = $_GET['estado'] ?? '';
$estadosValidos = [
    'pendiente',
    'confirmada',
    'completada',
    'cancelada',
    'pendiente_cancelacion'
];
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

$stmt = $conn->prepare("
    SELECT id_cliente 
    FROM cliente 
    WHERE id_usuario = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$cliente_id = $row['id_cliente'] ?? null;

// =======================
// 🔹 Construcción de SQL con prepared statements
// =======================
function buildFilters(
    $conn,
    $user_type,
    $user_id,
    $cliente_id,
    $barberoFiltro,
    $estadoFiltro,
    $selectedDate,
    $firstDay,
    $lastDay,
    $day
) {

    $params = [];
    $types = '';
    $where = [];

    // =========================
    // 🔹 CLIENTE
    // =========================
    if ($user_type === 'cliente') {

        // El cliente puede VER todos los huecos ocupados
        // NO filtramos por id_cliente

        if ($barberoFiltro > 0) {
            $where[] = 'c.id_barbero = ?';
            $types .= 'i';
            $params[] = $barberoFiltro;
        }
    }

    // =========================
    // 🔹 BARBERO
    // =========================
    elseif ($user_type === 'barbero') {

        $stmt = $conn->prepare("SELECT id_barbero FROM barbero WHERE id_usuario = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $barbero = $stmt->get_result()->fetch_assoc();

        if (!empty($barbero['id_barbero'])) {
            $where[] = 'c.id_barbero = ?';
            $types .= 'i';
            $params[] = $barbero['id_barbero'];
        } else {
            $where[] = '1 = 0';
        }
    }

    // =========================
    // 🔹 FILTRO ADMIN BARBERO
    // =========================
    elseif ($barberoFiltro > 0) {
        $where[] = 'c.id_barbero = ?';
        $types .= 'i';
        $params[] = $barberoFiltro;
    }

    // =========================
    // 🔹 ESTADO
    // =========================
    if (!empty($estadoFiltro)) {
        $where[] = 'c.estado = ?';
        $types .= 's';
        $params[] = $estadoFiltro;
    }

    // =========================
    // 🔹 FECHA
    // =========================
    if ($day && $selectedDate) {
        $where[] = 'c.fecha = ?';
        $types .= 's';
        $params[] = $selectedDate;
    } else {
        $where[] = 'c.fecha BETWEEN ? AND ?';
        $types .= 'ss';
        $params[] = $firstDay;
        $params[] = $lastDay;
    }

    $whereSQL = 'WHERE ' . implode(' AND ', $where);

    // =========================
    // 🔹 QUERY FINAL
    // =========================
    if ($user_type === 'barbero') {
        $sql = "SELECT c.*, cl.nombre AS cliente_nombre
                FROM cita c
                JOIN cliente cl ON cl.id_cliente = c.id_cliente
                $whereSQL
                ORDER BY " . ($day ? "c.hora ASC" : "c.fecha, c.hora ASC");
    } else {
        $sql = "SELECT c.*, cl.nombre AS cliente_nombre, b.nombre AS barbero_nombre
                FROM cita c
                JOIN cliente cl ON cl.id_cliente = c.id_cliente
                JOIN barbero b ON b.id_barbero = c.id_barbero
                $whereSQL
                ORDER BY " . ($day ? "c.hora ASC" : "c.fecha, c.hora ASC");
    }

    return [$sql, $types, $params];
}

// Preparar consulta
list($sql, $types, $params) = buildFilters($conn, $user_type, $user_id, $cliente_id, $barberoFiltro, $estadoFiltro, $selectedDate, $firstDay, $lastDay, $day);
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
        'id_cliente' => $row['id_cliente'],
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

$stmt = $conn->prepare("
    SELECT 
        hb.id_general,
        hb.id_barbero,
        hb.dia_semana,
        hb.hora_inicio,
        hb.hora_fin,
        hb.pausa_inicio,
        hb.pausa_fin
    FROM horario_barbero hb
    INNER JOIN horario_general hg
        ON hb.id_general = hg.id_general
    WHERE hb.id_barbero = ?
    AND hg.activo = 1
");

$stmt->bind_param("i", $barberoFiltro);
$stmt->execute();
$res = $stmt->get_result();

$horarios_array = [];

while ($row = $res->fetch_assoc()) {

    $horarios_array[] = [
        'id_general' => $row['id_general'],
        'id_barbero' => $row['id_barbero'],
        'dia_semana' => $row['dia_semana'],
        'hora_inicio' => $row['hora_inicio'],
        'hora_fin' => $row['hora_fin'],
        'pausa_inicio' => $row['pausa_inicio'],
        'pausa_fin' => $row['pausa_fin']
    ];
}

$stmt = $conn->prepare("
    SELECT 
        id_bloqueo,
        fecha,
        hora_inicio,
        hora_fin,
        tipo,
        motivo
    FROM bloqueo_horario
    WHERE id_barbero = ?
");

$stmt->bind_param("i", $barberoFiltro);
$stmt->execute();
$res = $stmt->get_result();

$bloqueos_array = [];

while ($row = $res->fetch_assoc()) {
    $bloqueos_array[] = $row;
}


$dias = [
    1 => 'lunes',
    2 => 'martes',
    3 => 'miercoles',
    4 => 'jueves',
    5 => 'viernes',
    6 => 'sabado',
    7 => 'domingo'
];

$fechaReferencia = $selectedDate ?? 'now';
$diaSemanaNum = date('N', strtotime($fechaReferencia));

$dia = $dias[$diaSemanaNum] ?? 'lunes';





$vip_array = [];

$tableExists = $conn->query("SHOW TABLES LIKE 'vip_horario'");
if ($tableExists && $tableExists->num_rows > 0) {

    $stmt = $conn->prepare("
        SELECT id_reserva, fecha, hora_inicio, hora_fin, motivo
        FROM vip_horario
        WHERE id_barbero = ?
    ");

    $stmt->bind_param("i", $barberoFiltro);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $vip_array[] = $row;
    }
}

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
            </button>
            <button data-estado="pendiente" class="filter-btn pendiente">
                Pendiente
                <span class="label-color pendiente"></span>
            </button>
            <button data-estado="confirmada" class="filter-btn confirmada">
                Confirmada
                <span class="label-color confirmada"></span>
            </button>
            <button data-estado="completada" class="filter-btn completada">
                Completada
                <span class="label-color completada"></span>
            </button>
            <button data-estado="pendiente_cancelacion" class="filter-btn pendiente_cancelacion">
                Pte. de Cancelar
                <span class="label-color pendiente_cancelacion"></span>
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
            
            <?php
            $barberos = $conn->query("SELECT id_barbero, nombre FROM barbero");
            $totalBarberos = $barberos->num_rows;

            if ($totalBarberos > 1):
            ?>
                <button data-barbero="" class="filter-btn <?= $barberoFiltro == 0 ? 'active' : '' ?>">
                    Todos
                </button>
            <?php endif; ?>

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
$fin = 22;

for($h = $inicio; $h < $fin; $h++){
    foreach([0,15,30,45] as $m){
        $hora = sprintf('%02d:%02d:00', $h, $m);
        echo '
            <div class="hour-slot" data-hora="'.$hora.'">
                <span class="hour-label">'.substr($hora,0,5).'</span>
            </div>';
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

<footer class="site-footer">
    <span class="version">v.<?= htmlspecialchars($version) ?></span><br>
    <span class="copyright">© 2026 alfonsogm</span>
</footer>

<footer class="policy">
        <a href="./includes/privacidad.html">Política de Privacidad</a> |
        <a href="./includes/cookies.html">Política de Cookies</a> |
        <a href="./includes/terminos.html">Términos y Condiciones</a>
</footer>

<script>
window.calendarData = {
    appointments: <?= json_encode($appointments) ?>,
    csrfToken: "<?=$csrf_token?>",
    year: <?=$year?>,
    month: <?=$month?>,
    day: <?=($day ?? 'null')?>,
    estadoFiltro: "<?=$estadoFiltro?>",
    barberoFiltro: "<?=$barberoFiltro?>",
    userType: "<?= $_SESSION['user_type'] ?>",
    userId: <?= $_SESSION['user_id'] ?>,
    clienteId: <?= json_encode($cliente_id) ?>, 
    horariosDisponibles: <?= json_encode($horarios_array ?? [], JSON_UNESCAPED_UNICODE) ?>,
    horarioActivo: <?= json_encode($horario_activo ?? null, JSON_UNESCAPED_UNICODE) ?>,
    diaSemana: "<?= $dia ?>",
    bloqueos: <?= json_encode($bloqueos_array ?? [], JSON_UNESCAPED_UNICODE) ?>,
    vipHorarios: <?= json_encode($vip_array ?? [], JSON_UNESCAPED_UNICODE) ?>
};
</script>
</script>
<script src="../js/calendar.js"></script>
<script src="../js/lightmode.js"></script>
<script src="../js/logoscroll.js"></script>

</body>
</html>