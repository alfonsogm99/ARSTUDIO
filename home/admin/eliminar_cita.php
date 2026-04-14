<?php
session_start();
include 'config.php';

// 🔹 Verificar permisos
if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// 🔹 Verificar ID de cita
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $_SESSION['error'] = "Cita inválida.";
    header("Location: ../control_panel.php");
    exit;
}

$cita_id = intval($_GET['id']);

// 🔹 Verificar que el usuario pueda eliminar la cita
if($user_type === 'barbero'){
    $check = $conn->prepare("SELECT id_cita FROM cita WHERE id_cita = ? AND id_barbero = ?");
    $check->bind_param("ii", $cita_id, $user_id);
    $check->execute();
    $result = $check->get_result();
    if($result->num_rows === 0){
        $_SESSION['error'] = "No tienes permisos para eliminar esta cita.";
        header("Location: ../control_panel.php");
        exit;
    }
} else {
    // Admin puede eliminar cualquier cita
    $check = $conn->prepare("SELECT id_cita FROM cita WHERE id_cita = ?");
    $check->bind_param("i", $cita_id);
    $check->execute();
    $result = $check->get_result();
    if($result->num_rows === 0){
        $_SESSION['error'] = "Cita no encontrada.";
        header("Location: ../control_panel.php");
        exit;
    }
}

// 🔹 Eliminar cita
$delete = $conn->prepare("DELETE FROM cita WHERE id_cita = ?");
$delete->bind_param("i", $cita_id);
if($delete->execute()){
    $_SESSION['success'] = "Cita eliminada correctamente.";
} else {
    $_SESSION['error'] = "Error al eliminar la cita.";
}

// 🔹 Redirigir a la vista diaria si se pasa fecha, si no a la mensual
if(isset($_GET['fecha'])){
    $fecha = $_GET['fecha'];
    header("Location: ../control_panel.php?day=" . date('d', strtotime($fecha)) . "&month=" . date('m', strtotime($fecha)) . "&year=" . date('Y', strtotime($fecha)));
} else {
    header("Location: ../control_panel.php");
}
exit;
?>