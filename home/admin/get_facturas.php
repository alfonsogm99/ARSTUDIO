<?php
header('Content-Type: application/json');

$api_key_secreta = "";

if (!isset($_GET['key']) || $_GET['key'] !== $api_key_secreta) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit;
}

$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;

if ($year == 0 || $month == 0) {
    echo json_encode(["error" => "Año o mes no válidos"]);
    exit;
}

// Configuración BBDD local en RunHosting
$host = "fdb1031.runhosting.com";
$user = "4079748_arstudio";
$password = "^Jt;.H#W1r14zm6h";
$database = "4079748_arstudio";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["error" => "Error de conexión: " . $conn->connect_error]);
    exit;
}

// COALESCE(cli.dni, 'SIN_DNI') AS dni_cliente,

$sql = "
    SELECT 
        c.id_cita AS id_factura,
        CONCAT('FACT-', DATE_FORMAT(c.fecha, '%Y%m'), '-', c.id_cita) AS numero_factura,
        CONCAT(c.fecha, ' ', c.hora) AS fecha_cita,
        
        cli.nombre AS nombre_completo,
        COALESCE(GROUP_CONCAT(s.nombre SEPARATOR ' + '), 'Servicio Barbería') AS concepto_servicio,
        COALESCE(p.total, SUM(dc.precio_aplicado), 0.00) AS total_importe,
        COALESCE(cli.email, 'Sin email') AS email_cliente,
        COALESCE(CONCAT(cli.prefijo_pais, ' ', cli.telefono), 'Sin teléfono') AS telefono_cliente,
        COALESCE(p.metodo, 'Efectivo') AS metodo_pago,
        COALESCE(b.nombre, 'Barbero Atendido') AS nombre_barbero
    FROM cita c
    INNER JOIN cliente cli ON c.id_cliente = cli.id_cliente
    LEFT JOIN barbero b ON c.id_barbero = b.id_barbero
    LEFT JOIN detalle_cita dc ON c.id_cita = dc.id_cita
    LEFT JOIN servicio s ON dc.id_servicio = s.id_servicio
    LEFT JOIN pago p ON c.id_cita = p.id_cita
    WHERE YEAR(c.fecha) = ? 
      AND MONTH(c.fecha) = ?
      AND (c.estado = 'completada' OR c.estado_pago = 'pagado')
    GROUP BY c.id_cita
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $year, $month);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>