<?php

require_once './bootstrap.php';
require_once './auth.php';

header('Content-Type: application/json');

if (!isset($_POST['id_cita'])) {
    echo json_encode(['success' => false]);
    exit;
}

$id_cita = intval($_POST['id_cita']);

$stmt = $conn->prepare("
    UPDATE cita
    SET estado = 'pendiente_cancelacion'
    WHERE id_cita = ?
");

$stmt->bind_param("i", $id_cita);

$ok = $stmt->execute();

echo json_encode([
    'success' => $ok
]);