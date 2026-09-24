<?php

define('BASE_URL', '/arbarber_project/home');

#4079748_arstudio
#^Jt;.H#W1r14zm6h


#$host = "fdb1033.awardspace.net";
#$user = "4751326_arstudio";
#$password = "Jlb}}{Pw6y,OSoGB";
#$dbname = "4751326_arstudio";

$host = "localhost";
$user = "root";
$password = "";
$dbname = "ar_barber_project";

$conn = new mysqli($host, $user, $password, $dbname);




if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

?>