<?php
session_start();
include 'config.php';

if(!isset($_GET['id'])){
    die("Cita no especificada");
}

$data = json_decode(file_get_contents('config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('config.json', json_encode($data));
}

$id = intval($_GET['id']);

// Obtener cita
$cita = $conn->query("SELECT * FROM cita WHERE id_cita=$id")->fetch_assoc();

if(!$cita){
    die("Cita no encontrada");
}

$pago = $conn->query("
    SELECT * FROM pago WHERE id_cita = $id LIMIT 1
")->fetch_assoc();

// si no existe, lo dejamos vacío
if(!$pago){
    $pago = [
        'total' => '',
        'metodo' => 'efectivo'
    ];
}


$columns = $conn->query("SHOW FULL COLUMNS FROM cita");

$editableFields = [
    'fecha',
    'hora',
    'estado',
    'estado_pago',
    'notas',
    'duracion_total'
];

$fields = [];
while($col = $columns->fetch_assoc()){
    $fields[] = $col;
}

function getEnumValues($type){
    preg_match("/^enum\((.*)\)$/", $type, $matches);
    if(!isset($matches[1])) return [];

    return array_map(fn($v) => trim($v, " '"), explode(",", $matches[1]));
}

// Actualizar
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $usuario = $_SESSION['user_name'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
    $conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");

    /* =========================
       1. UPDATE CITA
    ========================= */

    $set = [];
    $params = [];
    $types = '';

    foreach($editableFields as $field){

        if(!isset($_POST[$field])) continue;

        $set[] = "$field = ?";
        $params[] = $_POST[$field];
        $types .= 's';
    }

    if(!empty($set)){

        $params[] = $id;
        $types .= 'i';

        $sql = "UPDATE cita SET " . implode(', ', $set) . " WHERE id_cita = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    /* =========================
       2. UPDATE / INSERT PAGO
    ========================= */

    if(isset($_POST['total_pago'], $_POST['metodo_pago'])){

        $total = $_POST['total_pago'];
        $metodo = $_POST['metodo_pago'];

        $check = $conn->query("SELECT id_pago FROM pago WHERE id_cita=$id")->fetch_assoc();

        if($check){
            $stmt = $conn->prepare("
                UPDATE pago SET total=?, metodo=? WHERE id_cita=?
            ");
            $stmt->bind_param("dsi", $total, $metodo, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                INSERT INTO pago (id_cita, total, metodo) VALUES (?,?,?)
            ");
            $stmt->bind_param("ids", $id, $total, $metodo);
            $stmt->execute();
        }
    }

    header("Location: ../../home");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Editar Cita</title>
</head>
<body id="editcita" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<h2>Editar cita</h2>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box">

<h3>Cita</h3><br>

<?php foreach($fields as $field):

    $name = $field['Field'];
    $type = $field['Type'];

    // ❌ bloquear campos no editables
    if(!in_array($name, $editableFields)) continue;

    $value = $cita[$name] ?? '';
?>

    <label><?= htmlspecialchars($name) ?></label>

    <?php if($name === 'duracion_total'): ?>

        <select name="<?= $name ?>">
            <option value="30" <?= $value == 30 ? 'selected' : '' ?>>30 minutos</option>
            <option value="60" <?= $value == 60 ? 'selected' : '' ?>>60 minutos</option>
        </select>

    <?php elseif(str_starts_with($type, 'enum')): ?>

        <?php $options = getEnumValues($type); ?>

        <select name="<?= $name ?>">
            <?php foreach($options as $opt): ?>
                <option value="<?= $opt ?>" <?= $value == $opt ? 'selected' : '' ?>>
                    <?= ucfirst($opt) ?>
                </option>
            <?php endforeach; ?>
        </select>

    <?php elseif($name === 'fecha'): ?>

        <input type="date" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">

    <?php elseif($name === 'hora'): ?>

        <input type="time" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">

    <?php elseif(str_contains($type, 'text')): ?>

        <textarea name="<?= $name ?>"><?= htmlspecialchars($value) ?></textarea>

    <?php else: ?>

        <input type="text" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">

    <?php endif; ?>

    <br><br>

<?php endforeach; ?>


<h3>Pago</h3><br>

<label>Total (€)</label>
<input type="number" step="0.01" name="total_pago"
value="<?= $pago['total'] ?? '' ?>"><br><br>

<label>Método</label>
<select name="metodo_pago">
    <option value="efectivo" <?= ($pago['metodo'] ?? '') == 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
    <option value="tarjeta" <?= ($pago['metodo'] ?? '') == 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
    <option value="bizum" <?= ($pago['metodo'] ?? '') == 'bizum' ? 'selected' : '' ?>>Bizum</option>
</select>

<br><br>

<button type="submit">Guardar cambios</button>

</form>
</div>

<?php
$configFile = './config.json';

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
        <a href="../includes/privacidad.html">Política de Privacidad</a> |
        <a href="../includes/cookies.html">Política de Cookies</a> |
        <a href="../includes/terminos.html">Términos y Condiciones</a>
</footer>
</body>
</html>