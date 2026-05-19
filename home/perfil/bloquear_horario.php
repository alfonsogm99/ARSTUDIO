<?php
require_once '../admin/bootstrap.php';
session_start();

$id_user = $_SESSION['user_id'];

// obtener barbero
$res = $conn->query("SELECT id_barbero FROM barbero WHERE id_usuario = $id_user");
$id_barbero = $res->fetch_assoc()['id_barbero'];

$fecha = $_POST['fecha'];
$inicio = $_POST['hora_inicio'];
$fin = $_POST['hora_fin'];
$tipo = $_POST['tipo'];
$motivo = $conn->real_escape_string($_POST['motivo']);

$conn->query("
INSERT INTO bloqueo_horario
(id_barbero, fecha, hora_inicio, hora_fin, tipo, motivo)
VALUES
($id_barbero, '$fecha', '$inicio', '$fin', '$tipo', '$motivo')
");

header("Location: ../perfil");