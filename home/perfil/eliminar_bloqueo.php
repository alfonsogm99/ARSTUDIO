<?php

require_once '../admin/bootstrap.php';
require_once '../admin/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../perfil");
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$rol = $_SESSION['user_type'] ?? null;

if (!$user_id || $rol !== 'barbero') {
    header("Location: ../perfil");
    exit;
}

$id_bloqueo = isset($_POST['id_bloqueo']) ? (int) $_POST['id_bloqueo'] : 0;

if ($id_bloqueo <= 0) {
    header("Location: ../perfil");
    exit;
}

/* Obtener id_barbero del usuario logueado */
$stmt = $conn->prepare("SELECT id_barbero FROM barbero WHERE id_usuario = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();

if (!$row) {
    header("Location: ../perfil");
    exit;
}

$id_barbero = (int)$row['id_barbero'];

/* Borrar solo si pertenece a ese barbero */
$stmt = $conn->prepare("
    DELETE FROM bloqueo_horario
    WHERE id_bloqueo = ?
    AND id_barbero = ?
");

$stmt->bind_param("ii", $id_bloqueo, $id_barbero);

if ($stmt->execute()) {
    $_SESSION['mensaje'] = "Ausencia eliminada correctamente.";
} else {
    $_SESSION['error'] = "Error al eliminar la ausencia.";
}

$stmt->close();

header("Location: ../perfil");
exit;