<?php

define('BASE_URL', '/arbarber_project/home');

$host = "localhost";
$user = "root";
$password = "";
$dbname = "AR_BARBER_PROJECT";

$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>