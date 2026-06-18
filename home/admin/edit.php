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

    if (in_array($field, ['password','otp_code', 'otp_expires', 'status'])) continue;

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
$stmt = $conn->prepare("
    SELECT c.*
    FROM cliente c
    WHERE c.id_usuario = ?
");
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


    /* ======================
       CLIENTE (SEARCH SYSTEM)
    ====================== */

    $clienteInput = $_POST['cliente'] ?? [];

    $idCliente = !empty($clienteInput['id_cliente'])
        ? (int)$clienteInput['id_cliente']
        : null;

    $cSets = [];

    foreach ($clienteCols as $col) {
        if (in_array($col, ['id_cliente', 'id_usuario'])) continue;

        $cSets[$col] = $conn->real_escape_string($clienteInput[$col] ?? '');
    }

    // sync usuario → cliente
    $cSets['email'] = $_POST['usuario']['email'] ?? '';

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


    /* ======================
       VALIDACIÓN CLIENTE
    ====================== */

    if ($idCliente > 0) {

        // comprobar si el cliente existe y su dueño actual
        $stmt = $conn->prepare("
            SELECT id_usuario
            FROM cliente
            WHERE id_cliente = ?
        ");
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();

        // si está asignado a otro usuario → bloquear
        if (!empty($existing['id_usuario']) && $existing['id_usuario'] != $id) {
            die("Este cliente ya está asociado a otro usuario");
        }

        // vincular cliente al usuario EDITADO
        $stmt = $conn->prepare("
            UPDATE cliente
            SET
                id_usuario = ?,
                email = ?,
                prefijo_pais = ?,
                telefono = ?
            WHERE id_cliente = ?
        ");

        $cSets['prefijo_pais'] = $cSets['prefijo_pais'] ?? '+34';
        $cSets['telefono'] = $cSets['telefono'] ?? '';

        $stmt->bind_param(
            "isssi",
            $id,
            $cSets['email'],
            $cSets['prefijo_pais'],
            $cSets['telefono'],
            $idCliente
        );

        $stmt->execute();
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

<label>Cliente:</label>

<input
    type="hidden"
    name="cliente[id_cliente]"
    id="id_cliente"
    value="<?= htmlspecialchars($cliente['id_cliente'] ?? '') ?>"
>

<input
    type="text"
    id="cliente_input"
    value="<?= htmlspecialchars($cliente['nombre'] ?? $data['nombre'] ?? '') ?>"
    placeholder="Escribe nombre o DNI"
    autocomplete="off"
    required
>

<div id="cliente_suggestions"></div>

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

<script src="../../js/search_clients.js"></script>

</body>
</html>