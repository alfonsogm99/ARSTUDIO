<?php

require_once './bootstrap.php';
require_once './auth.php';

// =========================
// DATOS DEL USUARIO LOGUEADO
// =========================
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT c.id_cliente, c.nombre, c.email
    FROM cliente c
    WHERE c.id_usuario = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userData = $stmt->get_result()->fetch_assoc();

// si no existe cliente aún
if (!$userData) {
    die("No hay cliente asociado a este usuario.");
}

$id_cliente = $userData['id_cliente'];
$nombre     = $userData['nombre'];
$email      = $userData['email'];

// =========================
// CONFIG
// =========================
$configFile = './config.json';
$config = [];

if (file_exists($configFile)) {
    $decoded = json_decode(file_get_contents($configFile), true);
    if (is_array($decoded)) $config = $decoded;
}

$theme = $config['theme'] ?? 'dark';

// =========================
// DATOS DE LA CITA (GET)
// =========================
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$hora  = $_GET['hora'] ?? '08:00';

// =========================
// BARBEROS
// =========================
$barberos = [];
$res = $conn->query("SELECT id_barbero, nombre FROM barbero WHERE activo = 1");
while ($b = $res->fetch_assoc()) {
    $barberos[] = $b;
}

// =========================
// POST (CREAR CITA)
// =========================
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_barbero = intval($_POST['id_barbero'] ?? 0);
    $hora       = $_POST['hora'] ?? '';
    $fecha      = $_POST['fecha'] ?? '';
    $duracion   = intval($_POST['duracion'] ?? 0);

    // =========================
    // VALIDACIÓN BÁSICA
    // =========================
    if (!$id_barbero || !$hora || !$fecha || !$duracion) {

        $error = "Faltan datos obligatorios.";

    } elseif (!strtotime($fecha)) {

        $error = "Fecha inválida.";

    } elseif (!preg_match('/^(0\d|1\d|2[0-3]):([0-5]\d)$/', $hora)) {

        $error = "Hora inválida.";

    } elseif (strtotime($fecha) < strtotime(date('Y-m-d'))) {

        $error = "No puedes reservar en fechas pasadas.";
    }



    if (!$error) {

        // =========================
        // 7 DÍAS → MAX 2
        // =========================
        $from7 = date('Y-m-d', strtotime($fecha . ' -7 days'));
        $to7 = $fecha;

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM cita
            WHERE id_cliente = ?
            AND fecha BETWEEN ? AND ?
            AND estado IN (
                'pendiente',
                'confirmada',
                'pendiente_cancelacion'
            )
        ");

        $stmt->bind_param("iss", $id_cliente, $from7, $to7);
        $stmt->execute();

        $total7 = $stmt->get_result()->fetch_assoc()['total'];

        if ($total7 >= 2) {
            $error = "Máximo 2 citas en los últimos 7 días.";
        }
    }

    if (!$error) {

        // =========================
        // 30 DÍAS → MAX 4
        // =========================
        $from30 = date('Y-m-d', strtotime($fecha . ' -30 days'));
        $to30 = $fecha;

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM cita
            WHERE id_cliente = ?
            AND fecha BETWEEN ? AND ?
            AND estado IN (
                'pendiente',
                'confirmada',
                'pendiente_cancelacion'
            )
        ");

        $stmt->bind_param("iss", $id_cliente, $from30, $to30);
        $stmt->execute();

        $total30 = $stmt->get_result()->fetch_assoc()['total'];

        if ($total30 >= 4) {
            $error = "Máximo 4 citas en los últimos 30 días.";
        }
    }

    // =========================
    // CONFLICTOS HORARIO
    // =========================
    if (!$error) {

        $inicioNueva = strtotime($fecha . ' ' . $hora);
        $finNueva = $inicioNueva + ($duracion * 60);

        $stmt = $conn->prepare("
            SELECT hora, duracion_total
            FROM cita
            WHERE fecha = ?
            AND id_barbero = ?
            AND estado IN ('pendiente','confirmada')
        ");

        $stmt->bind_param("si", $fecha, $id_barbero);
        $stmt->execute();

        $res = $stmt->get_result();

        while ($c = $res->fetch_assoc()) {

            $inicio = strtotime($fecha . ' ' . $c['hora']);
            $fin = $inicio + ($c['duracion_total'] * 60);

            if ($inicioNueva < $fin && $finNueva > $inicio) {
                $error = "Ese horario no está disponible.";
                break;
            }
        }
    }


    // =========================
    // CITA FUTURA
    // Bloqueo: Lunes → cita, Martes → cita, Miércoles → cita...
    // =========================
    if (!$error) {

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM cita
            WHERE id_cliente = ?
            AND fecha >= CURDATE()
            AND estado IN (
                'pendiente',
                'confirmada',
                'pendiente_cancelacion'
            )
        ");

        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();

        $totalFuture = $stmt->get_result()->fetch_assoc()['total'];

        if ($totalFuture >= 1) {
            $error = "Ya tienes una cita pendiente.";
        }
    }

    if (!$error) {

        $nuevaCita = strtotime($fecha . ' ' . $hora);

        if (($nuevaCita - time()) < 3600) {
            $error = "Debes reservar con al menos 1 hora de antelación.";
        }
    }

    if (!$error) {

        if (strtotime($fecha) > strtotime('+60 days')) {
            $error = "No puedes reservar con más de 60 días de antelación.";
        }
    }


    // =========================
    // INSERT FINAL
    // =========================
    if (!$error) {

        $stmt = $conn->prepare("
            INSERT INTO cita (
                id_cliente,
                id_barbero,
                fecha,
                hora,
                duracion_total,
                estado
            )
            VALUES (?, ?, ?, ?, ?, 'pendiente')
        ");

        $stmt->bind_param(
            "iissi",
            $id_cliente,
            $id_barbero,
            $fecha,
            $hora,
            $duracion
        );

        if ($stmt->execute()) {
            $success = "Cita creada correctamente.";
        } else {
            $error = "Error al crear la cita.";
        }
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
<title>ARSTUDIO · Cliente</title>
</head>

<body class="<?= $theme === 'light' ? 'light-mode' : '' ?>">

<?php include '../includes/navbar.php'; ?>

<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
<p>
    <?= htmlspecialchars($success) ?><br>
    Serás redirigido en 5 segundos...
</p>
<?php endif; ?>

<?php if ($success): ?>
<script>
    setTimeout(() => {
        window.location.href = "../../home";
    }, 5000);
</script>
<?php endif; ?>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box">

    <h2>Reservar cita</h2><br>

    <input type="hidden" name="fecha" value="<?= $fecha ?>">

    <label>Cliente</label>
    <input class="readonly-input" type="text" value="<?= htmlspecialchars($nombre) ?>" readonly>

    <label>Email</label>
    <input class="readonly-input" type="email" value="<?= htmlspecialchars($email) ?>" readonly>

    <label>Barbero</label>
    <select name="id_barbero" required>

        <?php foreach ($barberos as $b): ?>
            <option 
                value="<?= $b['id_barbero'] ?>"
                <?= $b['id_barbero'] == ($_POST['id_barbero'] ?? '') ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($b['nombre']) ?>
            </option>
        <?php endforeach; ?>

    </select>

    <label>Hora</label>
    <input type="time" value="<?= substr($hora, 0, 5) ?>" readonly class="readonly-input">
    <input type="hidden" name="hora" value="<?= substr($hora, 0, 5) ?>">

    <?php 
    $servicios = $conn->query("SELECT * FROM servicio ORDER BY nombre ASC");

    $servicios = $conn->query("
        SELECT *
        FROM servicio
        WHERE precio > 0
        ORDER BY nombre ASC
    ");

    $id_servicio_actual = $servicio_actual['id_servicio'] ?? null;
    ?>

    <br><br>
    <h3>Servicio</h3>

    <select name="id_servicio" required>

        <option value="">Selecciona un servicio</option>

        <?php while($s = $servicios->fetch_assoc()): ?>
            <option value="<?= $s['id_servicio'] ?>"
                <?= ($id_servicio_actual == $s['id_servicio']) ? 'selected' : '' ?>>

                <?= htmlspecialchars($s['nombre']) ?> - <?= number_format($s['precio'],2) ?>€

            </option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Reservar cita</button>

</form>
</div>

<?php
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