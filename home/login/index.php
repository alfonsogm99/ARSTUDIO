<?php
session_start();

require '../admin/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Evitar SQL injection
    $username_safe = $conn->real_escape_string($username);

    $sql = "SELECT * FROM usuario WHERE username='$username_safe' AND activo=1";
    $res = $conn->query($sql);

    if ($res && $res->num_rows > 0) {

        $user = $res->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            // Guardar sesión
            $_SESSION['user_type'] = $user['rol'];
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_name'] = $user['username'];

            switch ($user['rol']) {

                case 'admin':
                    header("Location: ../admin/admin.php");
                    exit;

                case 'barbero':
                    $res2 = $conn->query("SELECT nombre FROM barbero WHERE id_usuario = " . (int)$user['id_usuario']);
                    if ($res2 && $res2->num_rows) {
                        $row = $res2->fetch_assoc();
                        $_SESSION['user_name'] = $row['nombre'];
                    }
                    header("Location: ../../home");
                    exit;

                case 'cliente':
                    $res2 = $conn->query("SELECT nombre FROM cliente WHERE id_usuario = " . (int)$user['id_usuario']);
                    if ($res2 && $res2->num_rows) {
                        $row = $res2->fetch_assoc();
                        $_SESSION['user_name'] = $row['nombre'];
                    }
                    header("Location: ./cliente");
                    exit;

                default:
                    $error = "Rol no válido.";
            }

        } else {
            $error = "Contraseña incorrecta.";
        }

    } else {
        $error = "Usuario no encontrado o inactivo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../../css/style.css">
<title>ARSTUDIO · Login</title>
</head>
<body>

<div class="form">

<img class="login logo"src="../../img/renewed_logo.png">

<form method="post" class="form_class">
    <div class="input_wrapper">
        <input type="text" name="username" placeholder="username" value="aaleexrp" required>
    </div><br>

    <div class="input_wrapper">
        <input type="password" name="password" id="password" placeholder="password" value="ARBARBER01" required>
        <i class="fa-solid fa-eye toggle_eye" onclick="togglePassword('password', this)"></i>
    </div><br>
    <button type="submit">Aceptar</button>
</form>

</div>

<?php if($error) echo "<br/><p style='color:red; text-align:center; margin:auto;'>$error</p>"; ?>

<?php
$configFile = '../admin/config.json';

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

<script src="../../js/togglePassword.js"></script>
</body>
</html>