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
                    header("Location: ../../home");
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
    <div class="input-wrapper">
        <input type="text" name="username" placeholder="username" value="aaleexrp" required>
    </div><br>

    <div class="input-wrapper">
        <input type="password" name="password" id="password" placeholder="password" value="ARBARBER01" required>
        <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
    </div>
    <br>
    <button type="submit">Aceptar</button>
</form>

<div class="signup">
  <p class="signup-text">
    ¿Aún no tienes cuenta? <a href="register.php">Tu próxima cita te espera💈
        <br>Regístrate aquí
    </a>
  </p>
</div>

</div>

<?php if($error) echo "<br/><p style='color:rgb(214, 105, 105); text-align:center; margin:auto;'>$error</p>"; ?>

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


<section class="cookies">
    <h2 class="cookies__titulo">🍪 ¿Aceptas nuestras Cookies?</h2>
    <p class="cookies__texto">Este sitio utiliza cookies para mejorar la experiencia del usuario.</p>
    <div class="cookies__botones">
        <button class="cookies__boton cookies__boton--si">Si</button>
        <button class="cookies__boton cookies__boton--no">No</button>
    </div>
</section>

<!-- No borrar, aquí se generarán todas las etiquetas <script> si acepta el usuario -->
<div id="nuevosScripts"></div>

<footer class="site-footer">
    <span class="version">v.<?= htmlspecialchars($version) ?></span><br>
    <span class="copyright">© 2026 alfonsogm</span>
</footer>

<br><br>
<footer class="policy">
        <a href="../includes/privacidad.html">Política de Privacidad</a> |
        <a href="../includes/cookies.html">Política de Cookies</a> |
        <a href="../includes/terminos.html">Términos y Condiciones</a>
</footer>

<script src="../../js/togglePassword.js"></script>
<script src="../../js/cookies.js"></script>
</body>
</html>