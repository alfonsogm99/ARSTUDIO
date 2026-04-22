<?php

require_once './bootstrap.php';
require_once './auth.php';

$tabla = $_GET['tabla'];
$id = $_GET['id'];

$pkResult = $conn->query("SHOW KEYS FROM $tabla WHERE Key_name = 'PRIMARY'");
$pkRow = $pkResult->fetch_assoc();
$pk = $pkRow['Column_name'] ?? 'id';


$usuario = $_SESSION['user_name'] ?? 'system';
$ip = $_SERVER['REMOTE_ADDR'];
$conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
$conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");

$stmt = $conn->prepare("DELETE FROM $tabla WHERE $pk = ?");
$stmt->bind_param("s", $id);
$stmt->execute();

header("Location: admin.php?tabla=$tabla");
exit;
?>