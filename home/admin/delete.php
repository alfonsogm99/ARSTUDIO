<?php
include 'config.php';
$tabla = $_GET['tabla'];
$id = $_GET['id'];
$pk = $conn->query("SHOW COLUMNS FROM $tabla")->fetch_assoc()['Field'];
$conn->query("DELETE FROM $tabla WHERE $pk='$id'");
header("Location: admin.php?tabla=$tabla");
?>