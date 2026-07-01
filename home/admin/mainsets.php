<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','barbero'])){
    header("Location: ../logout");
    exit;
}


$configFile = __DIR__ . '/config.json';
$config = [];

if (file_exists($configFile)) {
    $json = file_get_contents($configFile);
    $decoded = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $config = $decoded;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $config['main']['mapa']['visible'] = isset($_POST['mapa_visible']);
    $config['main']['mapa']['titulo'] = $_POST['mapa_titulo'] ?? 'Google Maps';

    $config['main']['cita']['visible'] = isset($_POST['cita_visible']);
    $config['main']['cita']['titulo'] = $_POST['cita_titulo'] ?? 'Pedir cita';

    // precios base
    $config['main']['precios']['visible'] = isset($_POST['precios_visible']);
    $config['main']['precios']['titulo'] = $_POST['precios_titulo'] ?? 'Precios';

    // ITEMS DINÁMICOS
    $items = [];

    if (!empty($_POST['precios_items'])) {
        foreach ($_POST['precios_items'] as $id => $item) {

            if (!empty($item['deleted'])) {
                continue; // 👈 eliminado
            }

            if (empty($item['titulo'])) continue;

            $items[] = [
                'id' => $id,
                'visible' => isset($item['visible']),
                'titulo' => $item['titulo'],
                'precio' => (float)($item['precio'] ?? 0)
            ];
        }
    }

    $config['main']['precios']['items'] = $items;

    file_put_contents(
        $configFile,
        json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    $_SESSION['success'] = "Los cambios se han guardado correctamente.";

    header("Location: " . $_SERVER['PHP_SELF']);
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
<title>ARSTUDIO · /main</title>
</head>
<body id="mainsets">

<div id="top-fixed">
    <?php include '../includes/navbar.php'; ?>
</div>

<h2 style="text-align:center;">/main</h2>

<?php if (isset($_SESSION['success'])): ?>
<script>
alert(<?= json_encode($_SESSION['success']) ?>);
</script>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>


<form method="POST" class="mainsets_form">

    <!-- MAPA -->
    <div>
        <label class="switch">
            <input type="checkbox" name="mapa_visible"
                <?= ($config['main']['mapa']['visible'] ?? false) ? 'checked' : '' ?>>
            <span class="slider"></span>
        </label>

        <input type="text" name="mapa_titulo"
            value="<?= htmlspecialchars($config['main']['mapa']['titulo'] ?? 'Google Maps') ?>">
    </div>

    <!-- CITA -->

    <div>
        <label class="switch">
            <input type="checkbox" name="cita_visible"
                <?= ($config['main']['cita']['visible'] ?? false) ? 'checked' : '' ?>>
            <span class="slider"></span>
        </label>

        <input type="text" name="cita_titulo"
            value="<?= htmlspecialchars($config['main']['cita']['titulo'] ?? 'Pedir cita') ?>">
    </div>
    <h6 style="opacity:.5; text-align:right; font-weight:500; font-style:italic;">(enlace deshabilitado)</h6>

    <br><hr style="width:50%; margin:auto;"><br>

    <div>
        <label class="switch">
            <input type="checkbox" name="precios_visible"
                <?= ($config['main']['precios']['visible'] ?? false) ? 'checked' : '' ?>>
            <span class="slider"></span>
        </label>

        <input type="text"
            name="precios_titulo"
            value="<?= htmlspecialchars($config['main']['precios']['titulo'] ?? 'Precios') ?>">
    </div>

    <?php foreach ($config['main']['precios']['items'] ?? [] as $item): ?>

    <div class="price-row">
        <label class="switch">
            <input type="checkbox"
                name="precios_items[<?= $item['id'] ?>][visible]"
                <?= ($item['visible'] ?? false) ? 'checked' : '' ?>>
            <span class="slider"></span>
        </label>

        <input type="text"
            name="precios_items[<?= $item['id'] ?>][titulo]"
            value="<?= htmlspecialchars($item['titulo']) ?>">

        <input type="number"
            step="0.5"
            name="precios_items[<?= $item['id'] ?>][precio]"
            value="<?= htmlspecialchars($item['precio']) ?>">

        <input type="hidden"
            name="precios_items[<?= $item['id'] ?>][deleted]"
            value="0">

        <button type="button" class="delete-btn" onclick="markDelete(this)">🗑</button>
    </div>

    <?php endforeach; ?>

    <button type="button" id="addPrice">+</button>

    <button type="submit">Guardar</button>
</form>

<script src="../../js/addPrice.js"></script>

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