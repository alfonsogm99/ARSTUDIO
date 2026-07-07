<?php
session_start();
require '../admin/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $phone    = preg_replace('/[^0-9]/', '', $_POST['phone']);
    $country  = preg_replace('/[^0-9]/', '', $_POST['country_code']);

    $phone_safe = '+' . $country . $phone;

    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];



    if (!$username || !$email || !$phone || !$password) {
        $error = "Rellena todos los campos.";

    } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
        $error = "El nombre de usuario no puede contener carácteres especiales.";
    } elseif ($password !== $confirm) {
        $error = "Las contraseñas no coinciden.";

    } else {

        $username_safe = $conn->real_escape_string($username);
        $email_safe = $conn->real_escape_string($email);

        $check = $conn->query("
            SELECT id_usuario FROM usuario 
            WHERE username='$username_safe'
            OR phone='$phone_safe'
            OR email='$email_safe'
        ");

        if ($check && $check->num_rows > 0) {
            $error = "El usuario o el teléfono ya están registrados.";

        } else {

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO usuario
                (
                    username,
                    password,
                    rol,
                    activo,
                    email,
                    phone,
                    status,
                    estado_cliente
                )
                VALUES (?, ?, 'cliente', 0, ?, ?, 'pending', 'pendiente_vinculacion')
            ");

            $stmt->bind_param(
                "ssss",
                $username_safe,
                $password_hash,
                $email_safe,
                $phone_safe
            );

            if ($stmt->execute()) {

                $success = "Solicitud enviada correctamente. Tu cuenta está pendiente de aprobación.";

                // Opcional: redirigir
                header("Location: ../login/?pending=1");
                exit;

            } else {
                $error = "Error al crear la cuenta.";
            }
        }
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
<title>ARSTUDIO · Sign Up</title>
</head>
<body>

<div class="form">

<img class="login logo" src="../../img/renewed_logo.png">

<form method="post" style="width:75%;">

    <input type="text" name="username" placeholder="usuario" pattern="[a-zA-Z0-9_.-]{3,30}"
        title="Solo letras, números, _, - y ." required> 
    <br><br>
    <input type="email" name="email" placeholder="email" required><br><br>
    <div class="phone-wrapper">
        <select name="country_code" required>
            <option value="34">🇪🇸 +34</option>
            <!--<option value="33">🇫🇷 +33</option>
            <option value="44">🇬🇧 +44</option>
            <option value="49">🇩🇪 +49</option>
            <option value="1">🇺🇸 +1</option>
            <option value="39">🇮🇹 +39</option>
            <option value="351">🇵🇹 +351</option>
            <option value="52">🇲🇽 +52</option>
            <option value="54">🇦🇷 +54</option>
            <option value="55">🇧🇷 +55</option>-->
        </select>

        <input type="text" name="phone" placeholder="teléfono" required>
    </div><br>
    <div class="input-wrapper">
        <input id="password" type="password" name="password" placeholder="contraseña" required>
        <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
    </div><br>
    <div class="input-wrapper">
        <input id="confirm_password" type="password" name="confirm_password" placeholder="repetir contraseña" required>
        <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('confirm_password', this)"></i>
    </div>

    <button type="submit">Crear cuenta</button>

</form>

<div class="signup">
    <p class="signup-text">
        ¿Ya tienes cuenta? <a href="../login">Inicia sesión 💈</a>
    </p>
</div>

</div>

<?php if ($error): ?>
<p class="error-message"><?= $error ?></p>
<?php endif; ?>

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