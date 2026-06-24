<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: ../login");
    exit;
}

$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';

$error = '';
$success = '';
$id_nuevo = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre = trim($nombre);
    $email = trim($email);

    if($nombre && $email){
        $check = $conn->prepare("SELECT id_cliente FROM cliente WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $error = "Ya existe un cliente con ese email.";
        } else {

            $usuario = $_SESSION['user_name'] ?? 'system';
            $ip = $_SERVER['REMOTE_ADDR'];
            $conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
            $conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");

            $stmt = $conn->prepare("INSERT INTO cliente (nombre, email) VALUES (?,?)");
            $stmt->bind_param("ss", $nombre, $email);

            if($stmt->execute()){

                // correo elecrónico cliente __deshabilitado__

                //$id_nuevo = $stmt->insert_id;

                //$subject = "Activación de usuario - ARSTUDIO";
                //$message = "Hola $nombre,\n\nHas sido registrado como cliente en ARSTUDIO.\n";
                //$message .= "Para activar tu usuario, haz clic en el siguiente enlace:\n";
                //$message .= "https://tusitio.com/activar_usuario.php?id=$id_nuevo";
                //$headers = "From: no-reply@tusitio.com\r\n";

                //mail($email, $subject, $message, $headers);

                //$success = "El cliente <strong>".htmlspecialchars($nombre)."</strong> recibirá un correo electrónico de verificación para la activación de su usuario.";

                // Redirección automática en 5 segundos
                //header("Refresh:5; url=../index.php");


                $id_nuevo = $stmt->insert_id;
                $success = "El cliente <strong>".htmlspecialchars($nombre)."</strong> recibirá un correo electrónico de verificación para la activación de su usuario.";

                // Redirección automática en 5 segundos
                header("Refresh:5; url=../../home");
            } else {
                $error = "Error al crear cliente: ".$conn->error;
            }
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
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
<title>ARSTUDIO · Nuevo Cliente</title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<h2>Nuevo Cliente</h2>

<div class="perfil_wrapper">
    <?php if($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php elseif($success): ?>
        <div>
            <div style="text-align:center;">
                <p><?php echo $success; ?></p>
                <div style="height:20px;"></div>
                <p>Redirigiendo en 5 segundos...</p>
            </div>
        </div>
    <?php endif; ?>
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