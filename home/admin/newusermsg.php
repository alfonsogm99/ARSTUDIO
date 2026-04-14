<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: ../login.php");
    exit;
}

$nombre = $_POST['nombre'] ?? '';
$dni = $_POST['dni'] ?? '';
$email = $_POST['email'] ?? '';

$error = '';
$success = '';
$id_nuevo = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre = trim($nombre);
    $dni = trim($dni);
    $email = trim($email);

    if($nombre && $dni && $email){
        $check = $conn->prepare("SELECT id_cliente FROM cliente WHERE dni=?");
        $check->bind_param("s", $dni);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $error = "Ya existe un cliente con ese DNI.";
        } else {
            $stmt = $conn->prepare("INSERT INTO cliente (nombre, dni, email) VALUES (?,?,?)");
            $stmt->bind_param("sss", $nombre, $dni, $email);

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
                //header("Refresh:5; url=../control_panel.php");


                $id_nuevo = $stmt->insert_id;
                $success = "El cliente <strong>".htmlspecialchars($nombre)."</strong> recibirá un correo electrónico de verificación para la activación de su usuario.";

                // Redirección automática en 5 segundos
                header("Refresh:5; url=../control_panel.php");
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

<?php include '../includes/navbar.php'; ?>

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

<footer class="version">
    <p>v.1.0</p>
</footer>
<footer class="copyright">
    <p>© 2026 alfonsogm</p>
</footer>

</body>
</html>