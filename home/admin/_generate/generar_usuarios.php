<?php

define('BASE_URL', '/arbarber_project/home');

require_once __DIR__ . '/admin/config.php';

$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}



$pass_plana1 = 'ARBARBER01';
$hash = password_hash($pass_plana1, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuario (username, password, rol) VALUES ('aaleexrp', '$hash', 'barbero')";
$conn->query($sql);

$sql2 = "INSERT INTO barbero (nombre, especialidad, activo, id_usuario) VALUES ('Alejandro Rivilla', '', TRUE, LAST_INSERT_ID())";
$conn->query($sql2);


$pass_plana2 = 'admin';
$hash = password_hash($pass_plana2, PASSWORD_DEFAULT);

$sql3 = "INSERT INTO usuario (username, password, rol) VALUES ('alfonsogm', '$hash', 'admin')";
$conn->query($sql3);

$sql4 = "INSERT INTO admin (id_usuario) VALUES (LAST_INSERT_ID())";
$conn->query($sql4);


?>