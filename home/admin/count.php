<?php

require_once './bootstrap.php';
require_once './auth.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','barbero'])){
    header("Location: ../login");
    exit;
}

$mes_actual = date('m');
$year_actual = date('Y');


$sqlGanancias = "
SELECT
(
    SELECT IFNULL(SUM(p.total),0)
    FROM pago p
    INNER JOIN cita c
        ON p.id_cita = c.id_cita
    WHERE MONTH(p.fecha_pago)=?
    AND YEAR(p.fecha_pago)=?
    AND c.estado_pago = 'pagado'
    AND c.estado NOT IN ('cancelada','pendiente_cancelacion')
)
+
(
    SELECT IFNULL(SUM(dvp.cantidad * dvp.precio_unitario),0)
    FROM detalle_venta_producto dvp
    INNER JOIN venta_producto vp
        ON dvp.id_venta = vp.id_venta
    WHERE MONTH(vp.fecha)=?
    AND YEAR(vp.fecha)=?
)
AS total_mes
";

$stmt = $conn->prepare($sqlGanancias);
$stmt->bind_param(
    "iiii",
    $mes_actual,
    $year_actual,
    $mes_actual,
    $year_actual
);

$stmt->execute();

$resGanancias = $stmt->get_result()->fetch_assoc();

$total_mes = $resGanancias['total_mes'];



$sqlProductos = "
SELECT
    IFNULL(SUM(dvp.cantidad * dvp.precio_unitario),0) AS total_productos
FROM detalle_venta_producto dvp
INNER JOIN venta_producto vp
    ON dvp.id_venta = vp.id_venta
WHERE MONTH(vp.fecha)=?
AND YEAR(vp.fecha)=?
";

$stmt = $conn->prepare($sqlProductos);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();

$resProductos = $stmt->get_result()->fetch_assoc();

$total_productos = $resProductos['total_productos'];



$sqlPendiente = "
SELECT IFNULL(SUM(s.precio),0) AS pendiente
FROM cita c
LEFT JOIN detalle_cita dc ON c.id_cita = dc.id_cita
LEFT JOIN servicio s ON dc.id_servicio = s.id_servicio
WHERE c.estado_pago = 'pendiente'
AND c.estado NOT IN ('cancelada','pendiente_cancelacion')
AND s.precio IS NOT NULL
AND MONTH(c.fecha)=?
AND YEAR(c.fecha)=?
";

$stmt = $conn->prepare($sqlPendiente);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();
$resPendiente = $stmt->get_result()->fetch_assoc();

$total_pendiente = $resPendiente['pendiente'];


$sqlCompletadas = "
SELECT COUNT(*) AS total
FROM cita
WHERE estado='completada'
AND MONTH(fecha)=?
AND YEAR(fecha)=?
";

$stmt = $conn->prepare($sqlCompletadas);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();
$resCompletadas = $stmt->get_result()->fetch_assoc();

$total_completadas = $resCompletadas['total'];


$sqlCanceladas = "
SELECT COUNT(*) AS total
FROM cita
WHERE estado='cancelada'
AND MONTH(fecha)=?
AND YEAR(fecha)=?
";

$stmt = $conn->prepare($sqlCanceladas);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();
$resCanceladas = $stmt->get_result()->fetch_assoc();

$total_canceladas = $resCanceladas['total'];


$sqlMetodos = "
SELECT
    p.metodo,
    COUNT(*) as cantidad,
    SUM(p.total) as total
FROM pago p
INNER JOIN cita c
    ON p.id_cita = c.id_cita
WHERE MONTH(p.fecha_pago)=?
AND YEAR(p.fecha_pago)=?
AND c.estado_pago = 'pagado'
AND c.estado NOT IN ('cancelada','pendiente_cancelacion')
GROUP BY p.metodo
";

$stmt = $conn->prepare($sqlMetodos);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();
$metodos = $stmt->get_result();


$sqlPagos = "
SELECT
    p.id_pago,
    p.total,
    p.metodo,
    p.fecha_pago,
    cliente.nombre AS cliente_nombre
FROM pago p
INNER JOIN cita c
    ON p.id_cita = c.id_cita
INNER JOIN cliente
    ON c.id_cliente = cliente.id_cliente
WHERE c.estado_pago = 'pagado'
AND c.estado NOT IN ('cancelada','pendiente_cancelacion')
ORDER BY p.fecha_pago DESC
LIMIT 10
";

$ultimosPagos = $conn->query($sqlPagos);
$tienePagos = $ultimosPagos->num_rows > 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Panel de Contabilidad</title>
</head>
<body id="contabilidad" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<div class="contabilidad_wrapper">

<h1 class="contabilidad_title">
    <i class="fa-solid fa-chart-line"></i>
    Resumen
</h1>

<div class="stats_grid">

    <div class="stat_card">

        <div class="stat_icon green">
            <i class="fa-solid fa-euro-sign"></i>
        </div>

        <div class="stat_label">
            Ganancias mensuales
        </div>

        <div class="stat_value green">
            <?= number_format($total_mes,2) ?>€
            <span class="stat_brutos">/brutos</span>
        </div>

    </div>

    <div class="stat_card">

        <div class="stat_icon orange">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>

        <div class="stat_label">
            Pendiente de pago
        </div>

        <div class="stat_value orange">
            <?= number_format($total_pendiente,2) ?>€
        </div>

    </div>

    <div class="stat_card">

        <div class="stat_icon">
            <i class="fa-solid fa-scissors"></i>
        </div>

        <div class="stat_label">
            Citas completadas
        </div>

        <div class="stat_value">
            <?= $total_completadas ?>
        </div>

    </div>

    <div class="stat_card">

        <div class="stat_icon red">
            <i class="fa-solid fa-xmark"></i>
        </div>

        <div class="stat_label">
            Citas canceladas
        </div>

        <div class="stat_value red">
            <?= $total_canceladas ?>
        </div>

    </div>

    <div class="stat_card">

        <div class="stat_icon green">
            <i class="fa-solid fa-euro-sign"></i>
        </div>

        <div class="stat_label">
            Productos vendidos
        </div>

        <div class="stat_value green">
            <?= number_format($total_productos,2) ?>€
        </div>


    </div>

<?php 

$sqlTiposProductos = "
SELECT
    p.tipo,

    IFNULL(SUM(dvp.cantidad),0) AS vendidos,

    (
        SELECT IFNULL(SUM(p2.stock),0)
        FROM producto p2
        WHERE p2.tipo = p.tipo
        AND p2.activo = TRUE
    ) AS stock

FROM producto p

LEFT JOIN detalle_venta_producto dvp
    ON p.id_producto = dvp.id_producto

LEFT JOIN venta_producto vp
    ON dvp.id_venta = vp.id_venta
    AND MONTH(vp.fecha)=?
    AND YEAR(vp.fecha)=?

WHERE p.activo = TRUE

GROUP BY p.tipo
";



$stmt = $conn->prepare($sqlTiposProductos);

$stmt->bind_param(
    "ii",
    $mes_actual,
    $year_actual
);

$stmt->execute();

$resultTiposProductos = $stmt->get_result();


$productos_tipo = [];

while($row = $resultTiposProductos->fetch_assoc()){

    $productos_tipo[$row['tipo']] = [
        'vendidos' => $row['vendidos'],
        'stock' => $row['stock']
    ];

}


$total_ceras = $productos_tipo['cera'] ?? [
    'vendidos'=>0,
    'stock'=>0
];


$total_polvos = $productos_tipo['polvos'] ?? [
    'vendidos'=>0,
    'stock'=>0
];



$sqlStock = "
SELECT 
    IFNULL(SUM(stock),0) AS total_stock
FROM producto
WHERE activo = TRUE
";

$resStock = $conn->query($sqlStock)->fetch_assoc();

$total_stock = $resStock['total_stock'];


?>

    <div class="stat_card">

        <div class="stat_icon green">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>

        <div class="stat_label">
            Productos en stock
        </div>

        <div class="stat_value">
            <?= $total_stock ?>
        </div>

    </div>

    <div class="stat_card">

        <div class="stat_icon">
            <i class="fa-solid fa-pump-soap"></i>
        </div>

        <div class="stat_label">
            Ceras vendidas
        </div>

        <div class="stat_value">
            <?= $total_ceras['vendidos'] ?>
            <span class="stat_brutos">
                / <?= $total_ceras['stock'] ?>
            </span>
        </div>

    </div>



    <div class="stat_card">

        <div class="stat_icon">
            <i class="fa-solid fa-box"></i>
        </div>

        <div class="stat_label">
            Polvos cabello vendidos
        </div>

        <div class="stat_value">
            <?= $total_polvos['vendidos'] ?>
            <span class="stat_brutos">
                / <?= $total_polvos['stock'] ?>
            </span>
        </div>

    </div>


</div>


<div class="contabilidad_box">

<h2>
    <i class="fa-solid fa-clock-rotate-left"></i>
    Últimos pagos
</h2>

<?php if($tienePagos): ?>

<table>

<thead>
<tr>
    <th>ID</th>
    <th>Cliente</th>
    <th>Método</th>
    <th>Total</th>
    <th>Fecha</th>
</tr>
</thead>

<tbody>

<?php while($p = $ultimosPagos->fetch_assoc()): ?>

<tr>

<td><strong>#<?= $p['id_pago'] ?></strong></td>

<td><?= htmlspecialchars($p['cliente_nombre']) ?></td>

<td>
    <span class="badge <?= $p['metodo'] ?>">
        <?= strtoupper($p['metodo']) ?>
    </span>
</td>

<td><?= number_format($p['total'],2) ?>€</td>

<td><?= date('d/m/Y H:i', strtotime($p['fecha_pago'])) ?></td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<?php else: ?>

<p class="empty-state">
    Aún no hay pagos registrados.
</p>

<?php endif; ?>

</div>

</div>

<?php
$configFile = './config.json';

$config = [];

if(file_exists($configFile)){

    $json = file_get_contents($configFile);
    $decoded = json_decode($json, true);

    if(json_last_error() === JSON_ERROR_NONE){
        $config = $decoded;
    }

}

$version = $config['app']['version'] ?? '0.0.0';
?>

<footer class="site-footer">
    <span class="version">
        v.<?= htmlspecialchars($version) ?>
    </span><br>

    <span class="copyright">
        © 2026 alfonsogm
    </span>
</footer>

<footer class="policy">
    <a href="../includes/privacidad.html">
        Política de Privacidad
    </a> |

    <a href="../includes/cookies.html">
        Política de Cookies
    </a> |

    <a href="../includes/terminos.html">
        Términos y Condiciones
    </a>
</footer>

</body>
</html>