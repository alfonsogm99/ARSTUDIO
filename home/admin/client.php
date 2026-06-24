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

$nuevaFechaHora = $fecha . ' ' . $hora;

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

    $id_servicio = intval($_POST['id_servicio'] ?? 0);

    $stmt = $conn->prepare("SELECT duracion FROM servicio WHERE id_servicio = ?");
    $stmt->bind_param("i", $id_servicio);
    $stmt->execute();
    $duracion = $stmt->get_result()->fetch_assoc()['duracion'] ?? 0;

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

    // =========================
    // CITA DIA
    // Bloqueo: Lunes → 1 cita, Martes → 1 cita, Miércoles → 1 cita...
    // =========================
    if (!$error) {

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM cita
            WHERE id_cliente = ?
            AND fecha = ?
            AND estado IN ('pendiente','confirmada','pendiente_cancelacion')
        ");

        $stmt->bind_param("is", $id_cliente, $fecha);
        $stmt->execute();

        $totalDia = $stmt->get_result()->fetch_assoc()['total'];

        if ($totalDia >= 1) {
            $error = "Ya tienes una cita el mismo día.";
        }
    }


    // =========================
    // 7 DÍAS → MAX 2
    // =========================
    if (!$error) {

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM cita
            WHERE id_cliente = ?
            AND CONCAT(fecha, ' ', hora) BETWEEN 
                DATE_SUB(?, INTERVAL 7 DAY) AND DATE_ADD(?, INTERVAL 7 DAY)
            AND estado IN ('pendiente','confirmada','pendiente_cancelacion')
        ");

        $stmt->bind_param("iss", $id_cliente, $nuevaFechaHora, $nuevaFechaHora);
        $stmt->execute();

        $total7 = $stmt->get_result()->fetch_assoc()['total'];

        if ($total7 >= 2) {
            $error = "Has alcanzado el máximo de 2 citas en una ventana de 7 días.";
        }
    }

    // =========================
    // MES NATURAL → MAX 4 (CORRECTO)
    // =========================
    if (!$error) {

        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM cita
            WHERE id_cliente = ?
            AND fecha >= DATE_FORMAT(?, '%Y-%m-01')
            AND fecha < DATE_ADD(DATE_FORMAT(?, '%Y-%m-01'), INTERVAL 1 MONTH)
            AND estado IN ('pendiente','confirmada','pendiente_cancelacion')
        ");

        $stmt->bind_param("iss", $id_cliente, $fecha, $fecha);
        $stmt->execute();

        $totalMes = $stmt->get_result()->fetch_assoc()['total'];

        if ($totalMes >= 4) {
            $error = "Has alcanzado el máximo de 4 citas en este mes.";
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