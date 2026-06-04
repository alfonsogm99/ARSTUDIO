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

$cita = $conn->query("SELECT * FROM cita WHERE id_cita=$id")->fetch_assoc();

if(!$cita){
    die("Cita no encontrada");
}

$pago = $conn->query("
    SELECT * FROM pago WHERE id_cita = $id LIMIT 1
")->fetch_assoc();

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


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $usuario = $_SESSION['user_name'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
    $conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");


    $id_servicio = !empty($_POST['id_servicio']) ? intval($_POST['id_servicio']) : 0;

    $precio = 0;
    $duracion = 0;

    if($id_servicio > 0){

        $stmt = $conn->prepare("
            SELECT precio, duracion
            FROM servicio
            WHERE id_servicio = ?
        ");
        $stmt->bind_param("i", $id_servicio);
        $stmt->execute();
        $serv = $stmt->get_result()->fetch_assoc();

        if($serv){
            $precio = (float)$serv['precio'];
            $duracion = (int)$serv['duracion'];
        }
    }
    

    $set = [];
    $params = [];
    $types = '';

    foreach($editableFields as $field){

        if(!isset($_POST[$field])) continue;

        $set[] = "$field = ?";
        $params[] = $_POST[$field];
        $types .= 's';
    }


    if($duracion > 0){
        $set[] = "duracion_total = ?";
        $params[] = $duracion;
        $types .= 'i';
    }

    if(!empty($set)){

        $params[] = $id;
        $types .= 'i';

        $sql = "UPDATE cita SET " . implode(', ', $set) . " WHERE id_cita = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    if($id_servicio > 0){

        $conn->query("DELETE FROM detalle_cita WHERE id_cita = $id");

        $stmt = $conn->prepare("
            INSERT INTO detalle_cita (id_cita, id_servicio, precio_aplicado)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iid", $id, $id_servicio, $precio);
        $stmt->execute();
    }

    if(isset($_POST['metodo_pago'])){

        $metodo = $_POST['metodo_pago'];

        $check = $conn->query("
            SELECT id_pago FROM pago WHERE id_cita = $id
        ")->fetch_assoc();

        if($check){
            $stmt = $conn->prepare("
                UPDATE pago SET total=?, metodo=? WHERE id_cita=?
            ");
            $stmt->bind_param("dsi", $precio, $metodo, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                INSERT INTO pago (id_cita, total, metodo)
                VALUES (?,?,?)
            ");
            $stmt->bind_param("ids", $id, $precio, $metodo);
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

        <input 
            type="text" 
            value="<?= htmlspecialchars($value) ?> minutos" 
            readonly 
            class="readonly-input">

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

<?php 
$servicios = $conn->query("SELECT * FROM servicio ORDER BY nombre ASC");

$servicio_actual = $conn->query("
    SELECT id_servicio
    FROM detalle_cita
    WHERE id_cita = $id
    LIMIT 1
")->fetch_assoc();

$id_servicio_actual = $servicio_actual['id_servicio'] ?? null;
?>

<h3>Servicio</h3><br>

<select name="id_servicio" required>
    <option value="">Selecciona un servicio</option>

    <?php while($s = $servicios->fetch_assoc()): ?>
        <option value="<?= $s['id_servicio'] ?>"
            <?= ($id_servicio_actual == $s['id_servicio']) ? 'selected' : '' ?>>

            <?= htmlspecialchars($s['nombre']) ?> - <?= number_format($s['precio'],2) ?>€

        </option>
    <?php endwhile; ?>
</select>
<br><br>

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