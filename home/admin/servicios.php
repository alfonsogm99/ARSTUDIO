<?php
session_start();
include 'config.php';

function formatDuracion($minutos) {
    $h = floor($minutos / 60);
    $m = $minutos % 60;

    if ($h == 0) {
        return $m . ' min';
    } elseif ($m == 0) {
        return $h . 'h';
    } else {
        return $h . 'h ' . $m . 'min';
    }
}

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','barbero'])){
    header("Location: ../logout");
    exit;
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $duracion = intval($_POST['duracion']);

    if (
        $duracion < 30 ||
        $duracion > 360 ||
        ($duracion % 30 !== 0 && $duracion != 40)
    ) {
        die("Duración no válida");
    }

    if(isset($_POST['id_servicio']) && $_POST['id_servicio'] != ''){

        // UPDATE
        $stmt = $conn->prepare("
            UPDATE servicio
            SET nombre=?, precio=?, duracion=?
            WHERE id_servicio=?
        ");
        $stmt->bind_param("sdii", $nombre, $precio, $duracion, $_POST['id_servicio']);
        $stmt->execute();

    } else {

        $stmt = $conn->prepare("
            INSERT INTO servicio (nombre, precio, duracion)
            VALUES (?,?,?)
        ");
        $stmt->bind_param("sdi", $nombre, $precio, $duracion);
        $stmt->execute();
    }

    header("Location: servicios.php");
    exit;
}


if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM servicio WHERE id_servicio=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: servicios.php");
    exit;
}

$edit = null;

if(isset($_GET['edit'])){

    $id = intval($_GET['edit']);
    $edit = $conn->query("SELECT * FROM servicio WHERE id_servicio=$id")->fetch_assoc();
}

$servicios = $conn->query("
    SELECT s.*,
           (SELECT COUNT(*) FROM detalle_cita dc WHERE dc.id_servicio = s.id_servicio) AS total_citas
    FROM servicio s
    ORDER BY s.id_servicio DESC
");

$tieneServicios = $servicios->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Servicios</title>
</head>
<body id="servicios">

<div id="top-fixed">
    <?php include '../includes/navbar.php'; ?>
</div>

<h2 style="text-align:center;">Servicios</h2>

<div class="contabilidad_box">

<?php if($tieneServicios): ?>

<table id="tablaServicios" class="tableau">

    <thead>
        <tr>
            <th onclick="ordenarTabla(this, 0)">ID</th>
            <th onclick="ordenarTabla(this, 1)">Nombre</th>
            <th onclick="ordenarTabla(this, 2)">Precio</th>
            <th onclick="ordenarTabla(this, 3)">Duración</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>
    <?php while($s = $servicios->fetch_assoc()): ?>

    <tr>
        <td>#<?= $s['id_servicio'] ?></td>
        <td><?= htmlspecialchars($s['nombre']) ?></td>
        <td><?= number_format($s['precio'],2) ?>€</td>
        <td><?= formatDuracion($s['duracion']) ?></td>
        <td class="actions">

            <a href="javascript:void(0)"
            onclick='toggleForm({
                id_servicio: <?= $s["id_servicio"] ?>,
                nombre: "<?= htmlspecialchars($s["nombre"], ENT_QUOTES) ?>",
                precio: <?= $s["precio"] ?>,
                duracion: <?= $s["duracion"] ?>
            })'>
                <b>Editar</b>
            </a>

            <a class="delete"
            href="?delete=<?= $s['id_servicio'] ?>"
            onclick="return confirm('<?= $s['total_citas'] > 0
                    ? '⚠️Atención⚠️ Este servicio tiene ' . $s['total_citas'] . ' cita(s) asignada(s). ¿Seguro que quieres eliminarlo?'
                    : '¿Eliminar servicio?' ?>')">
                <b>Eliminar</b>
            </a>

        </td>
    </tr>

    <?php endwhile; ?>
    </tbody>

</table>

<?php else: ?>

<p class="empty-state">
    Aún no hay servicios registrados.
</p>

<?php endif; ?>

</div>

<br>
<button type="button" style="margin:auto; display:block;" onclick="toggleForm(null)">
    Nuevo servicio
</button>

<div id="formServicio" style="display:none;">
    <div class="perfil_wrapper">
    <form method="POST" class="perfil_box">

    <input type="hidden" name="id_servicio" value="<?= $edit['id_servicio'] ?? '' ?>">

    <label>Nombre</label>
    <input type="text" name="nombre" required
    value="<?= htmlspecialchars($edit['nombre'] ?? '') ?>">

    <label>Precio (€)</label>
    <input type="number" step="0.01" name="precio" required
    value="<?= $edit['precio'] ?? '' ?>">

    <label>Duración (min)</label>

    <select name="duracion" required>

        <option value="30"
            <?= (isset($edit['duracion']) && $edit['duracion'] == 30) ? 'selected' : '' ?>>
            30 min
        </option>

        <option value="40"
            <?= (isset($edit['duracion']) && $edit['duracion'] == 40) ? 'selected' : '' ?>>
            40 min
        </option>

        <?php for ($i = 60; $i <= 360; $i += 30): ?>
            <option value="<?= $i ?>"
                <?= (isset($edit['duracion']) && $edit['duracion'] == $i) ? 'selected' : '' ?>>

                <?php
                    $h = floor($i / 60);
                    $m = $i % 60;

                    if ($h == 0) {
                        echo $m . ' min';
                    } elseif ($m == 0) {
                        echo $h . 'h';
                    } else {
                        echo $h . 'h ' . $m . 'min';
                    }
                ?>

            </option>
        <?php endfor; ?>

    </select>

    <button type="submit">
        <?= $edit ? 'Actualizar' : 'Crear' ?>
    </button>

    </form>
    </div>
</div>

<?php if($edit): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('formServicio').style.display = 'block';
});
</script>
<?php endif; ?>

<script src="../../js/toggleForm.js"></script>

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

<script src="../../js/orderTableau.js"></script>

</body>
</html>