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

// Actualizar
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $estado = $_POST['estado'];
    $notas = $_POST['notas'];

    $usuario = $_SESSION['user_name'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
    $conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");

    $stmt = $conn->prepare("UPDATE cita SET fecha=?, hora=?, estado=?, notas=? WHERE id_cita=?");
    $stmt->bind_param("ssssi", $fecha, $hora, $estado, $notas, $id);
    $stmt->execute();

    header("Location: ../index.php");
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
    Fecha: <input type="date" name="fecha" value="<?= $cita['fecha'] ?>"><br>
    Hora: <input type="time" name="hora" value="<?= $cita['hora'] ?>"><br>

    Estado:
    <select name="estado">
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="completada">Completada</option>
        <option value="cancelada">Cancelada</option>
        <option value="fallida">Fallida</option>
    </select><br>

    Notas:<br>
    <textarea name="notas"><?= $cita['notas'] ?></textarea><br>

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