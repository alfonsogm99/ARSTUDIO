<?php

require_once './bootstrap.php';
require_once './auth.php';

$data = json_decode(file_get_contents('./config.json'), true) ?? [];

if (isset($_POST['theme'])) {
    $newTheme = $_POST['theme'];
    // Guardar solo si cambió
    if (!isset($data['theme']) || $data['theme'] !== $newTheme) {
        $data['theme'] = $newTheme;
        file_put_contents('./config.json', json_encode($data, JSON_PRETTY_PRINT));
    }
}

$theme = $data['theme'] ?? 'dark';

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Configuración</title>
</head>
<style>
    @font-face {
        font-family: "AR_font";
        src: url("../../fonts/blackletter_ds/BLACEB__.TTF") format("truetype");
        font-weight: 400;
        font-style: normal;
        font-display: swap;
    }

    .AR{
        font-family: "AR_font", sans-serif;
        opacity: .7;
    }

</style>

<body id="confpan" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>


<div class="confpan-settings">

    <a href="<?php echo BASE_URL; ?>/admin/mainsets.php">
        <div class="confpan-option">
            <p class="AR">AR</p>
        </div>
    </a>

    <a href="<?php echo BASE_URL; ?>/admin/servicios.php">
        <div class="confpan-option">
            <i class="fa-solid fa-scissors"></i>
        </div>
    </a>

    <a href="<?php echo BASE_URL; ?>/admin/users.php">
        <div class="confpan-option">
            <i class="fa-solid fa-user"></i>
        </div>
    </a>

    <a href="<?php echo BASE_URL; ?>/admin/horario.php">
        <div class="confpan-option">
            <i class="fa-solid fa-clock"></i>
        </div>
    </a>

    <a href="">
        <div class="confpan-option">
            <i class="fa-solid fa-none"></i>
        </div>
    </a>

    <a href="">
        <div class="confpan-option">
            <i class="fa-solid fa-none"></i>
        </div>
    </a>

    <br>
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
        <a href="./includes/privacidad.html">Política de Privacidad</a> |
        <a href="./includes/cookies.html">Política de Cookies</a> |
        <a href="./includes/terminos.html">Términos y Condiciones</a>
</footer>
</body>
</html>