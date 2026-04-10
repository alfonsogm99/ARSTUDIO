<?php

define('BASE_URL', '/arbarber_project/home');

#$host = "localhost";
#$user = "alfgme7_ar_barber_project";
#$password = "8ni0JoHT4)";
#$dbname = "alfgme7_ar_barber_project";

$host = "localhost";
$user = "root";
$password = "";
$dbname = "ar_barber_project";

$conn = new mysqli($host, $user, $password, $dbname);


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>