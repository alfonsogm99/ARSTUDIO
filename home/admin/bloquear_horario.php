<?php
require_once './bootstrap.php';
session_start();

$id_user = $_SESSION['user_id'];

// obtener barbero
$res = $conn->query("SELECT id_barbero FROM barbero WHERE id_usuario = $id_user");
$id_barbero = $res->fetch_assoc()['id_barbero'];

$modo = $_POST['modo'] ?? 'crear';

$fecha  = $_POST['fecha'];
$inicio = $_POST['hora_inicio'];
$fin    = $_POST['hora_fin'];
$tipo   = $_POST['tipo'];

// OJO: en tu formulario el campo se llama "observaciones", no "motivo"
$motivo = $conn->real_escape_string($_POST['observaciones'] ?? '');

if ($modo === 'editar') {

    $id_bloqueo = (int)($_POST['id_bloqueo'] ?? 0);

    if ($id_bloqueo > 0) {

        $conn->query("
            UPDATE bloqueo_horario
            SET 
                fecha = '$fecha',
                hora_inicio = '$inicio',
                hora_fin = '$fin',
                tipo = '$tipo',
                motivo = '$motivo'
            WHERE id_bloqueo = $id_bloqueo
              AND id_barbero = $id_barbero
        ");
    }

} else {

    $conn->query("
        INSERT INTO bloqueo_horario
        (id_barbero, fecha, hora_inicio, hora_fin, tipo, motivo)
        VALUES
        ($id_barbero, '$fecha', '$inicio', '$fin', '$tipo', '$motivo')
    ");
}

header("Location: ./confpan.php");
exit;