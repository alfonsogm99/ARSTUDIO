<?php
session_start();
require_once 'config.php';

$rol = $_SESSION['user_type'] ?? null;
$idUsuarioSesion = $_SESSION['user_id'] ?? null;

if (!in_array($rol, ['admin', 'barbero'])) {
    header("Location: ../logout");
    exit;
}

/* =========================
   TABLAS PERMITIDAS
========================= */

$tablasPermitidasAdmin = [
    'usuario',
    'cliente',
    'barbero',
    'servicio',
    'cita',
    'detalle_cita',
    'pago',
    'horario_barbero',
    'horario_general',
    'bloqueo_horario',
    'vip_horario'
];

$tablasPermitidasBarbero = [
    'usuario',
    'cliente'
];

/* =========================
   INPUTS
========================= */

$tabla = trim($_GET['tabla'] ?? '');

$id = $_GET['id']
    ?? $_GET['idusuario']
    ?? null;

if (!$id) {
    die("Falta ID");
}

$id = (int)$id;

if (empty($tabla)) {
    die("Falta parámetro tabla");
}

/* =========================
   PERMISOS
========================= */

if ($rol === 'admin') {
    if (!in_array($tabla, $tablasPermitidasAdmin)) {
        die("Tabla no permitida");
    }
}

if ($rol === 'barbero') {
    if (!in_array($tabla, $tablasPermitidasBarbero)) {
        die("No tienes permisos para esta tabla");
    }
}

/* =========================
   DETECTAR PK (CORRECTO)
========================= */

$res = $conn->query("SHOW KEYS FROM `$tabla` WHERE Key_name = 'PRIMARY'");

$pk = '';

if ($row = $res->fetch_assoc()) {
    $pk = $row['Column_name'];
}

if (!$pk) {
    die("No se pudo detectar la clave primaria");
}

/* =========================
   COLUMNAS
========================= */


$usuarioCols = [];
$clienteCols = [];

/* USUARIO */
$res = $conn->query("DESCRIBE usuario");

while ($row = $res->fetch_assoc()) {
    $field = $row['Field'];

    if (in_array($field, ['otp_code', 'otp_expires', 'status'])) continue;

    $usuarioCols[] = $field;
}

/* CLIENTE */
$res = $conn->query("DESCRIBE cliente");

while ($row = $res->fetch_assoc()) {

    $field = $row['Field'];

    if (in_array($field, [
        'id_cliente',
        'id_usuario'
    ])) {
        continue;
    }

    $clienteCols[] = $field;
}
/* =========================
   CARGAR DATOS (FIX REAL)
========================= */

$id = (int)$id;

/* =========================
   USUARIO (SIEMPRE EXISTE)
========================= */
$stmt = $conn->prepare("SELECT * FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado");
}

/* =========================
   CLIENTE (PUEDE NO EXISTIR)
========================= */
$stmt = $conn->prepare("SELECT * FROM cliente WHERE id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();

/* =========================
   DATA FINAL (SI NO EXISTE CLIENTE → VACÍO)
========================= */
$data = $usuario;

/* añadir campos cliente aunque no exista */
$clienteCampos = [
    'telefono',
    'direccion',
    'dni',
    'fecha_nacimiento',
    'notas',
    'activo'
];

foreach ($clienteCampos as $c) {
    $data[$c] = $cliente[$c] ?? '';
}

/* =========================
   CAMPOS BLOQUEADOS
========================= */

$columnasBloqueadas = [
    'id_usuario',
    'id_cliente',
    'rol',
    'fecha_creacion'
];

/* =========================
   UPDATE
========================= */

$clienteOcultos = [
    'email',
    'nombre',
    'apellidos'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ======================
       USUARIO UPDATE
    ====================== */
    $uSets = [];

    foreach ($usuarioCols as $col) {

        if ($col === 'password') continue;

        if ($col === 'activo') {
            $val = isset($_POST['usuario']['activo']) ? 1 : 0;
        } else {
            $val = $conn->real_escape_string($_POST['usuario'][$col] ?? '');
        }

        $uSets[] = "`$col`='$val'";
    }

    if (!empty($uSets)) {
        $conn->query("
            UPDATE usuario
            SET " . implode(",", $uSets) . "
            WHERE id_usuario = $id
        ");
    }




    $cCheck = $conn->query("SELECT id_cliente FROM cliente WHERE id_usuario = $id");
    $exists = $cCheck->num_rows > 0;

    $cSets = [];

    /* =========================
    CLIENTE CAMPOS NORMALES (FORM)
    ========================= */
    $clienteInput = $_POST['cliente'] ?? [];

    foreach ($clienteCols as $col) {

        if (in_array($col, ['id_cliente', 'id_usuario'])) {
            continue;
        }

        if ($col === 'activo') {
            $cSets[$col] = isset($clienteInput['activo']) ? 1 : 0;
            continue;
        }

        $cSets[$col] = $conn->real_escape_string($clienteInput[$col] ?? '');
    }

    /* =========================
    SIEMPRE SINCRONIZAR DESDE USUARIO
    ========================= */

    $cSets['email']  = $_POST['usuario']['email'] ?? '';

    $fullPhone = trim($_POST['usuario']['phone'] ?? '');

    if (str_starts_with($fullPhone, '+34')) {

        $cSets['prefijo_pais'] = '+34';
        $cSets['telefono'] = substr($fullPhone, 3);

    } elseif (str_starts_with($fullPhone, '34')) {

        $cSets['prefijo_pais'] = '+34';
        $cSets['telefono'] = substr($fullPhone, 2);

    } else {

        $cSets['telefono'] = $fullPhone;
    }



    if ($exists) {

        $updateParts = [];

        foreach ($cSets as $k => $v) {
            $updateParts[] = "`$k`='$v'";
        }

        $conn->query("
            UPDATE cliente
            SET " . implode(",", $updateParts) . "
            WHERE id_usuario = $id
        ");

    } else {

        $cols = array_keys($cSets);
        $vals = array_values($cSets);

        $cols[] = "id_usuario";
        $vals[] = $id;

        $conn->query("
            INSERT INTO cliente (" . implode(",", $cols) . ")
            VALUES ('" . implode("','", $vals) . "')
        ");
    }

    header("Location: admin.php?tabla=usuario");
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
<title>ARSTUDIO · Datos del cliente</title>
</head>
<body id="editcita" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box">
<!--<h3>Usuario</h3>-->

<?php foreach ($usuarioCols as $col): ?>

<?php
$isReadonly = in_array($col, [
    'id_usuario',
    'password',
    'rol',
    'fecha_creacion',
    'status'
]);
?>

<label>
    <?= $col ?>:

    <?php if ($col === 'activo'): ?>

        <input type="hidden" name="usuario[activo]" value="0">

        <input
            type="checkbox"
            name="usuario[activo]"
            value="1"
            <?= !empty($usuario['activo']) ? 'checked' : '' ?>
        >

    <?php else: ?>

        <input
            type="text"
            name="usuario[<?= $col ?>]"
            value="<?= htmlspecialchars($usuario[$col] ?? '') ?>"
            class="<?= $isReadonly ? 'readonly-input' : '' ?>"
            <?= $isReadonly ? 'readonly' : '' ?>
        >

    <?php endif; ?>

</label>
<br>

<?php endforeach; ?>


<!--<h3>Cliente</h3>-->

<?php foreach ($clienteCols as $col): ?>

<?php

$isHidden = in_array($col, [
    'otp_code',
    'otp_expires',
    'id_cliente',
    'id_usuario',
    'google_id',
    'email',
    'fecha_registro',
    'prefijo_pais',
    'telefono'
]);
$isReadonly = in_array($col, ['fecha_creacion']);
$valor = $cliente[$col] ?? '';
?>

<?php if ($isHidden): ?>
    <input type="hidden"
           name="cliente[<?= $col ?>]"
           value="<?= htmlspecialchars($valor) ?>">
    <?php continue; ?>
<?php endif; ?>

<label>
    <?= $col ?>:

    <?php if ($col === 'activo'): ?>

        <input type="hidden" name="cliente[activo]" value="0">

        <input type="checkbox"
               name="cliente[activo]"
               value="1"
               <?= (!empty($cliente['activo']) ? 'checked' : '') ?>>

    <?php elseif ($isReadonly): ?>

        <input type="text"
               name="cliente[<?= $col ?>]"
               value="<?= htmlspecialchars($valor) ?>"
               readonly
               class="readonly-input">

    <?php else: ?>

    <input
        type="text"
        name="cliente[<?= $col ?>]"
        value="<?= htmlspecialchars($valor) ?>"
        <?= $isReadonly ? 'readonly class="readonly-input"' : '' ?>
        <?= $col === 'dni' ? 'required' : '' ?>
    >

    <?php endif; ?>

</label>
<br>

<?php endforeach; ?>

<button type="submit">Guardar cambios</button>
</form>
</div>

<?php if ($rol === 'admin'): ?>
    <p>
        <a href="admin.php?tabla=<?php echo $tabla; ?>">
            <b>Volver</b>
        </a>
    </p>
<?php endif; ?>

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