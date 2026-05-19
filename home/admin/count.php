<?php

require_once './bootstrap.php';
require_once './auth.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','barbero'])){
    header("Location: ../login");
    exit;
}

/*
|--------------------------------------------------------------------------
| PANEL CONTABILIDAD
|--------------------------------------------------------------------------
| - Ganancias mensuales
| - Pérdidas / cancelaciones
| - Métodos de pago
| - Últimos pagos
| - Estadísticas rápidas
|--------------------------------------------------------------------------
*/

$mes_actual = date('m');
$year_actual = date('Y');

/* =========================
   GANANCIAS DEL MES
========================= */
$sqlGanancias = "
SELECT 
    IFNULL(SUM(total),0) AS total_mes
FROM pago
WHERE MONTH(fecha_pago)=?
AND YEAR(fecha_pago)=?
";

$stmt = $conn->prepare($sqlGanancias);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();
$resGanancias = $stmt->get_result()->fetch_assoc();

$total_mes = $resGanancias['total_mes'];


/* =========================
   CITAS COMPLETADAS
========================= */
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


/* =========================
   CITAS CANCELADAS
========================= */
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


/* =========================
   MÉTODOS DE PAGO
========================= */
$sqlMetodos = "
SELECT metodo, COUNT(*) as cantidad, SUM(total) as total
FROM pago
WHERE MONTH(fecha_pago)=?
AND YEAR(fecha_pago)=?
GROUP BY metodo
";

$stmt = $conn->prepare($sqlMetodos);
$stmt->bind_param("ii", $mes_actual, $year_actual);
$stmt->execute();
$metodos = $stmt->get_result();


/* =========================
   ÚLTIMOS PAGOS
========================= */
$sqlPagos = "
SELECT 
    pago.id_pago,
    pago.total,
    pago.metodo,
    pago.fecha_pago,
    cliente.nombre AS cliente_nombre
FROM pago
INNER JOIN cita ON pago.id_cita = cita.id_cita
INNER JOIN cliente ON cita.id_cliente = cliente.id_cliente
ORDER BY pago.fecha_pago DESC
LIMIT 10
";

$ultimosPagos = $conn->query($sqlPagos);

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

<style>

/* =========================
CONTABILIDAD
========================= */

body#contabilidad{
    padding-top:20vh;
    min-height:100dvh;
}

.contabilidad_wrapper{
    width:min(1400px,95%);
    margin:auto;
}

.contabilidad_title{
    text-align:center;
    margin-bottom:30px;
}

/* =========================
STATS
========================= */

.stats_grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;

    margin-bottom:30px;
}

.stat_card{

    background: #1e1e1e;
    border:1px solid #333;

    border-radius:16px;

    padding:25px;

    transition:.2s ease;

    position:relative;
    overflow:hidden;
}

.stat_card:hover{
    transform:translateY(-3px);
}

.stat_card::after{
    content:"";
    position:absolute;
    inset:0;

    background:url('../../img/separator_negativo.png') no-repeat center center;
    background-size:40px;

    opacity:.04;
    pointer-events:none;
}

.stat_icon{
    font-size:22px;
    margin-bottom:15px;

    opacity:.8;
}

.stat_label{
    font-size:13px;
    opacity:.7;

    margin-bottom:8px;
}

.stat_value{
    font-size:30px;
    font-weight:700;
}

.green{
    color:#2ecc71;
}

.red{
    color:#e74c3c;
}

.orange{
    color:#f39c12;
}

/* =========================
TABLAS CONTABILIDAD
========================= */

.contabilidad_box table {
    width: 100%;
    border-collapse: separate; /* importante para border-radius */
    border-spacing: 0;
    border: 0;
    overflow: hidden;
    background: #1a1a1a;
    border-radius: 12px;
}

/* TH */
.contabilidad_box th {
    background: #111;
    color: #fff;
    padding: 12px;
    text-align: left;

    border: 0;
    border-bottom: 1px solid #333;
}

/* efecto redondeado SOLO en esquinas */
.contabilidad_box th:first-child {
    border-top-left-radius: 12px;
}

.contabilidad_box th:last-child {
    border-top-right-radius: 12px;
}

/* TD */
.contabilidad_box td {
    padding: 10px 12px;
    border: 0;
    border-bottom: 1px solid #2a2a2a;
}

/* hover filas */
.contabilidad_box tr:hover td {
    background: #222;
}

/* última fila sin borde */
.contabilidad_box tr:last-child td {
    border-bottom: none;
}

/* =========================
BADGES
========================= */

.badge{

    display:inline-flex;
    align-items:center;
    justify-content:center;

    padding:6px 10px;

    border-radius:999px;

    font-size:11px;
    font-weight:600;

    border:1px solid transparent;
}

.badge.efectivo{
    background:rgba(46,204,113,.12);
    color: #2ecc71;
    border-color:rgba(46,204,113,.25);
}

.badge.tarjeta{
    background:rgba(52,152,219,.12);
    color: #3498db;
    border-color:rgba(52,152,219,.25);
}

.badge.bizum{
    background:rgba(155,89,182,.12);
    color: #9b59b6;
    border-color:rgba(155,89,182,.25);
}

/* =========================
RESPONSIVE
========================= */

@media(max-width:768px){

    .stats_grid{
        grid-template-columns:1fr;
    }

    .stat_value{
        font-size:24px;
    }

    .contabilidad_box{
        overflow-x:auto;
    }

}

</style>
</head>
<body id="contabilidad" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<div class="contabilidad_wrapper">

<h1 class="contabilidad_title">
    <i class="fa-solid fa-chart-line"></i>
    Panel de Contabilidad
</h1>

<!-- =========================
STATS
========================= -->

<div class="stats_grid">

    <div class="stat_card">

        <div class="stat_icon green">
            <i class="fa-solid fa-euro-sign"></i>
        </div>

        <div class="stat_label">
            Ganancias del mes
        </div>

        <div class="stat_value green">
            <?= number_format($total_mes,2) ?>€
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

        <div class="stat_icon orange">
            <i class="fa-solid fa-chart-pie"></i>
        </div>

        <div class="stat_label">
            Balance estimado
        </div>

        <div class="stat_value orange">
            <?= number_format($total_mes - ($total_canceladas * 10),2) ?>€
        </div>

    </div>

</div>

<!-- =========================
METODOS DE PAGO
========================= -->

<div class="contabilidad_box">

<h2>
    <i class="fa-solid fa-credit-card"></i>
    Métodos de pago
</h2>

<table>

<thead>

<tr>
    <th>Método</th>
    <th>Operaciones</th>
    <th>Total</th>
</tr>

</thead>

<tbody>

<?php while($m = $metodos->fetch_assoc()): ?>

<tr>

<td>
    <span class="badge <?= $m['metodo'] ?>">
        <?= strtoupper($m['metodo']) ?>
    </span>
</td>

<td><?= $m['cantidad'] ?></td>

<td><?= number_format($m['total'],2) ?>€</td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

</div>

<!-- =========================
ULTIMOS PAGOS
========================= -->

<div class="contabilidad_box">

<h2>
    <i class="fa-solid fa-clock-rotate-left"></i>
    Últimos pagos
</h2>

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

<td>#<?= $p['id_pago'] ?></td>

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