<?php
session_start();
include 'config.php';

// 🔹 Verificar permisos
if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: ../login");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// 🔹 Verificar ID de cita
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    $_SESSION['error'] = "Cita inválida.";
    header("Location: ../index.php");
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
        header("Location: ../index.php");
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
        header("Location: ../index.php");
        exit;
    }
}

// 🔹 Eliminar cita

$usuario = $_SESSION['user_name'];
$ip = $_SERVER['REMOTE_ADDR'];

$conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
$conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");

$stmtFecha = $conn->prepare("SELECT fecha FROM cita WHERE id_cita = ?");
$stmtFecha->bind_param("i", $cita_id);
$stmtFecha->execute();
$resFecha = $stmtFecha->get_result();
$rowFecha = $resFecha->fetch_assoc();

$fecha = $rowFecha['fecha'] ?? null;

$delete = $conn->prepare("DELETE FROM cita WHERE id_cita = ?");
$delete->bind_param("i", $cita_id);
if($delete->execute()){

    $_SESSION['success'] = "Cita eliminada correctamente.";

    if($fecha){
        $year = date('Y', strtotime($fecha));
        $month = date('m', strtotime($fecha));
        $day = date('d', strtotime($fecha));

        header("Location: ../index.php?year=$year&month=$month&day=$day");
    } else {
        header("Location: ../index.php");
    }

} else {

    $_SESSION['error'] = "Error al eliminar la cita.";
    header("Location: ../index.php");

}
exit;
?>