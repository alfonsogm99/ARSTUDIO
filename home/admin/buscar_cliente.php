<?php
include 'config.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = trim($q);

if(strlen($q) < 2){
    echo json_encode([]);
    exit;
}

// Buscar clientes cuyo nombre contenga la cadena al inicio de cualquier palabra o por DNI
$stmt = $conn->prepare("
    SELECT c.id_cliente, c.nombre, c.dni, COUNT(ci.id_cita) AS total_citas
    FROM cliente c
    LEFT JOIN cita ci ON ci.id_cliente = c.id_cliente
    WHERE c.nombre LIKE ? OR c.nombre LIKE ? OR c.dni LIKE ?
    GROUP BY c.id_cliente
    ORDER BY total_citas DESC
    LIMIT 3
");

// Primer ? -> "%q%" (cualquier parte) | Segundo ? -> "q%" (inicio palabra) | Tercer ? -> "%q%" (DNI)
$like_middle = "%$q%";
$like_start = "$q%";
$stmt->bind_param("sss", $like_start, $like_middle, $like_middle);
$stmt->execute();
$res = $stmt->get_result();

$clientes = [];
while($row = $res->fetch_assoc()){
    $clientes[] = [
        'id_cliente' => $row['id_cliente'],
        'nombre' => $row['nombre'],
        'dni' => $row['dni']
    ];
}

echo json_encode($clientes);