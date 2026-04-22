<?php

include 'config.php';

header('Content-Type: application/json; charset=utf-8');

$logs = [];

$sql = "SELECT * FROM audit_log ORDER BY fecha DESC LIMIT 100";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {

    // Parsear JSON antes/después
    $antes = $row['datos_antes'] ? json_decode($row['datos_antes'], true) : null;
    $despues = $row['datos_despues'] ? json_decode($row['datos_despues'], true) : null;

    // Función para enriquecer datos de cita
    $enriquecerDatos = function ($datos) use ($conn) {
        if (!$datos) return null;

        // Nombre cliente
        if (!empty($datos['id_cliente'])) {
            $stmt = $conn->prepare("SELECT nombre FROM cliente WHERE id_cliente = ?");
            $stmt->bind_param("i", $datos['id_cliente']);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($cliente = $res->fetch_assoc()) {
                $datos['cliente_nombre'] = $cliente['nombre'];
            }
        }

        // Nombre barbero
        if (!empty($datos['id_barbero'])) {
            $stmt = $conn->prepare("SELECT nombre FROM barbero WHERE id_barbero = ?");
            $stmt->bind_param("i", $datos['id_barbero']);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($barbero = $res->fetch_assoc()) {
                $datos['barbero_nombre'] = $barbero['nombre'];
            }
        }

        return $datos;
    };

    $row['datos_antes'] = $enriquecerDatos($antes);
    $row['datos_despues'] = $enriquecerDatos($despues);

    $logs[] = $row;
}

echo json_encode($logs, JSON_UNESCAPED_UNICODE);