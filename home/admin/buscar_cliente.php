<?php
include 'config.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
$q = trim($q);

if(strlen($q) < 2){
    echo json_encode([]);
    exit;
}

// Buscar clientes cuyo nombre contenga la cadena al inicio de cualquier palabra
$stmt = $conn->prepare("
    SELECT c.id_cliente, c.nombre, COUNT(ci.id_cita) AS total_citas
    FROM cliente c
    LEFT JOIN cita ci ON ci.id_cliente = c.id_cliente
    WHERE c.nombre LIKE ?
    GROUP BY c.id_cliente
    ORDER BY total_citas DESC
    LIMIT 3
");

$like = "%$q%";
$stmt->bind_param("s", $like);
$stmt->execute();
$res = $stmt->get_result();

$clientes = [];
while($row = $res->fetch_assoc()){
    $clientes[] = [
        'id_cliente' => $row['id_cliente'],
        'nombre' => $row['nombre']
    ];
}

echo json_encode($clientes);