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

<body id="confpan" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>


<div class="confpan-settings">

    <!-- <a href="<?php echo BASE_URL; ?>/admin/mainsets.php">
        <div class="confpan-option">
            <p class="AR">AR</p>
        </div>
    </a> -->

    <a href="<?php echo BASE_URL; ?>/admin/users.php">
        <div class="confpan-option">
            <i class="fa-solid fa-user"></i>
        </div>
    </a>

    <a href="<?php echo BASE_URL; ?>/admin/servicios.php">
        <div class="confpan-option">
            <i class="fa-solid fa-scissors"></i>
        </div>
    </a>

    <a href="<?php echo BASE_URL; ?>/admin/productos.php">
        <div class="confpan-option">
            <i class="fa-solid fa-box"></i>
        </div>
    </a>

    <a href="<?php echo BASE_URL; ?>/admin/horario.php">
        <div class="confpan-option">
            <i class="fa-solid fa-clock"></i>
        </div>
    </a>

    <?php 
        $config = json_decode(
            file_get_contents('./config.json'),
            true
        ) ?? [];

        $bookingStep = $config['booking']['step'] ?? 30;
    ?> 

    <div class="confpan-option-config">

        <p class="confpan-title">time cooldown</p>

        <div class="confpan-controls">
            <input
                id="set_time_freeze"
                type="number"
                min="10"
                step="10"
                value="<?= htmlspecialchars($bookingStep) ?>">

            <button
                id="save_booking_step"
                class="btn btn-primary">
                <i class="fa-solid fa-save"></i>
            </button>
        </div>

    </div>

    <div class="confpan-option">
        <i class="fa-solid fa-none"></i>
    </div>

    <br>
</div>


<script>
    document.getElementById("save_booking_step").addEventListener("click", () => {

        const step = document.getElementById("set_time_freeze").value;

        fetch("guardar_booking_step.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                booking_step: step
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                alert("Guardado correctamente");
            } else {
                alert("Error al guardar");
            }
        });
    });
</script>





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