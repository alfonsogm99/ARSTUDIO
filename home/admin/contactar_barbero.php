<?php

require_once './bootstrap.php';
require_once './auth.php';

// =======================
// 🔒 SEGURIDAD BÁSICA
// =======================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if ($user_type !== 'cliente') {
    die("Acceso no autorizado.");
}

// =======================
// 🔐 CSRF
// =======================
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// =======================
// 📌 CITA
// =======================
$id_cita = isset($_GET['cita']) ? intval($_GET['cita']) : 0;

if ($id_cita <= 0) {
    die("Cita no válida.");
}

// Obtener cita + barbero
$stmt = $conn->prepare("
    SELECT 
        c.id_cita,
        c.fecha,
        c.hora,
        c.estado,
        b.id_barbero,
        b.nombre AS barbero_nombre
    FROM cita c
    INNER JOIN barbero b ON b.id_barbero = c.id_barbero
    WHERE c.id_cita = ?
");

$stmt->bind_param("i", $id_cita);
$stmt->execute();
$cita = $stmt->get_result()->fetch_assoc();

if (!$cita) {
    die("Cita no encontrada.");
}

// =======================
// 📤 ENVÍO MENSAJE
// =======================
$enviado = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
        die("CSRF inválido.");
    }

    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($mensaje === '') {
        $error = "El mensaje no puede estar vacío.";
    } else {

        // Aquí puedes guardar en tabla "mensajes" o "solicitudes"
        $stmt = $conn->prepare("
            INSERT INTO solicitudes_barbero 
            (id_cita, id_barbero, id_cliente, mensaje, fecha_envio)
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->bind_param(
            "iiis",
            $id_cita,
            $cita['id_barbero'],
            $user_id,
            $mensaje
        );

        if ($stmt->execute()) {
            $enviado = true;
        } else {
            $error = "Error al enviar la solicitud.";
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
<link rel="icon" type="image/x-icon" href="../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Contacto</title>
</head>

<body class="contact-page">


<?php include '../includes/navbar.php'; ?>
<div class="contact-container">

    <h2>Contactar con el barbero</h2>

    <div class="contact-info">
        <p><strong>Barbero:</strong> <?=htmlspecialchars($cita['barbero_nombre'])?></p>
        <p><strong>Fecha:</strong> <?=htmlspecialchars($cita['fecha'])?></p>
        <p><strong>Hora:</strong> <?=htmlspecialchars($cita['hora'])?></p>
        <p><strong>Estado:</strong> <?=htmlspecialchars($cita['estado'])?></p>
    </div>

    <?php if ($enviado): ?>
        <div class="alert success">
            Mensaje enviado correctamente al barbero.
        </div>

        <a href="calendar.php" class="btn-primary">Volver al calendario</a>

    <?php else: ?>

        <?php if ($error): ?>
            <div class="alert error">
                <?=htmlspecialchars($error)?>
            </div>
        <?php endif; ?>

        <form method="POST" class="contact-form">

            <input type="hidden" name="csrf" value="<?=$csrf_token?>">

            <label>Mensaje</label>
            <textarea name="mensaje" rows="5" placeholder="Escribe tu solicitud..."></textarea>

            <button type="submit" class="btn-primary">
                Enviar
            </button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>