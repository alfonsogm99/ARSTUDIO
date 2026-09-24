<?php
session_start();
include 'config.php';

if(!isset($_GET['id'])){
    die("Cita no especificada");
}

$data = json_decode(file_get_contents('config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('config.json', json_encode($data));
}

$id = intval($_GET['id']);

$cita = $conn->query("SELECT * FROM cita WHERE id_cita=$id")->fetch_assoc();

if(!$cita){
    die("Cita no encontrada");
}

$pago = $conn->query("
    SELECT * FROM pago WHERE id_cita = $id LIMIT 1
")->fetch_assoc();

if(!$pago){
    $pago = [
        'total' => '',
        'metodo' => 'efectivo',
        'propina' => 0
    ];
}


$columns = $conn->query("SHOW FULL COLUMNS FROM cita");

$editableFields = [
    'fecha',
    'hora',
    'estado',
    'estado_pago',
    'duracion_total'
];

$fields = [];
while($col = $columns->fetch_assoc()){
    $fields[] = $col;
}

function getEnumValues($type){
    preg_match("/^enum\((.*)\)$/", $type, $matches);
    if(!isset($matches[1])) return [];

    return array_map(fn($v) => trim($v, " '"), explode(",", $matches[1]));
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $usuario = $_SESSION['user_name'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
    $conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");


    $id_servicio = intval($_POST['id_servicio']);

    $stmt = $conn->prepare("
        SELECT precio, duracion
        FROM servicio
        WHERE id_servicio = ?
    ");
    $stmt->bind_param("i", $id_servicio);
    $stmt->execute();

    $serv = $stmt->get_result()->fetch_assoc();

    if(!$serv){
        die("Servicio no encontrado");
    }

    $precio = (float)$serv['precio'];
    $duracion = (int)$serv['duracion'];
    

    $set = [];
    $params = [];
    $types = '';

    foreach($editableFields as $field){

        if(!isset($_POST[$field])) continue;

        $set[] = "$field = ?";
        $params[] = $_POST[$field];
        $types .= 's';
    }


    if($duracion > 0){
        $set[] = "duracion_total = ?";
        $params[] = $duracion;
        $types .= 'i';
    }

    if(!empty($set)){

        $params[] = $id;
        $types .= 'i';

        $sql = "UPDATE cita SET " . implode(', ', $set) . " WHERE id_cita = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
    }

    if($id_servicio > 0){

        $conn->query("DELETE FROM detalle_cita WHERE id_cita = $id");

        $stmt = $conn->prepare("
            INSERT INTO detalle_cita (id_cita, id_servicio, precio_aplicado)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iid", $id, $id_servicio, $precio);
        $stmt->execute();
    }

    $propina = isset($_POST['propina'])
    ? floatval($_POST['propina'])
    : 0;

    $total = $precio + $propina;


    // =========================
    // PRODUCTO DE LA CITA
    // =========================

    $id_producto = isset($_POST['id_producto'])
        ? intval($_POST['id_producto'])
        : 0;

    $cantidad_producto = isset($_POST['cantidad_producto'])
        ? intval($_POST['cantidad_producto'])
        : 0;


    $stmt = $conn->prepare("
        SELECT id_producto, cantidad_cita
        FROM producto
        WHERE id_cita = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $producto_anterior = $stmt->get_result()->fetch_assoc();



    if($producto_anterior){

        $cantidad_anterior = (int)$producto_anterior['cantidad_cita'];
        $id_producto_anterior = (int)$producto_anterior['id_producto'];

        $stmt = $conn->prepare("
            UPDATE producto
            SET 
                stock = stock + ?,
                cantidad_cita = 0,
                id_cita = NULL
            WHERE id_producto = ?
        ");

        $stmt->bind_param(
            "ii",
            $cantidad_anterior,
            $id_producto_anterior
        );

        $stmt->execute();
    }



    $stmt = $conn->prepare("
        SELECT id_venta
        FROM venta_producto
        WHERE id_cita = ?
        LIMIT 1
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $venta_anterior = $stmt->get_result()->fetch_assoc();


    if($venta_anterior){

        $id_venta_anterior = (int)$venta_anterior['id_venta'];

        $stmt = $conn->prepare("
            DELETE FROM detalle_venta_producto
            WHERE id_venta = ?
        ");

        $stmt->bind_param("i", $id_venta_anterior);
        $stmt->execute();

        $stmt = $conn->prepare("
            DELETE FROM venta_producto
            WHERE id_venta = ?
        ");

        $stmt->bind_param("i", $id_venta_anterior);
        $stmt->execute();
    }



    if($id_producto > 0){

        if($cantidad_producto <= 0){
            $cantidad_producto = 1;
        }



        $stmt = $conn->prepare("
            SELECT 
                nombre,
                precio_venta,
                stock
            FROM producto
            WHERE id_producto = ?
            AND activo = TRUE
            LIMIT 1
        ");

        $stmt->bind_param("i", $id_producto);
        $stmt->execute();

        $producto = $stmt->get_result()->fetch_assoc();


        if(!$producto){
            die("Producto no encontrado");
        }


        $stock_disponible = (int)$producto['stock'];

        $precio_producto = (float)$producto['precio_venta'];


        if($cantidad_producto > $stock_disponible){

            die(
                "Stock insuficiente. " .
                "Stock disponible: " . $stock_disponible .
                ". Cantidad solicitada: " . $cantidad_producto
            );
        }



        $stmt = $conn->prepare("
            UPDATE producto
            SET 
                stock = stock - ?,
                cantidad_cita = ?,
                id_cita = ?
            WHERE id_producto = ?
        ");

        $stmt->bind_param(
            "iiii",
            $cantidad_producto,
            $cantidad_producto,
            $id,
            $id_producto
        );

        $stmt->execute();


        $id_cliente = (int)$cita['id_cliente'];

        $total_producto = $precio_producto * $cantidad_producto;

        $metodo = isset($_POST['metodo_pago'])
            ? $_POST['metodo_pago']
            : 'efectivo';


        $stmt = $conn->prepare("
            INSERT INTO venta_producto
            (
                id_cita,
                id_cliente,
                total,
                metodo
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iids",
            $id,
            $id_cliente,
            $total_producto,
            $metodo
        );

        $stmt->execute();

        $id_venta = $conn->insert_id;


        $stmt = $conn->prepare("
            INSERT INTO detalle_venta_producto
            (
                id_venta,
                id_producto,
                cantidad,
                precio_unitario
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiid",
            $id_venta,
            $id_producto,
            $cantidad_producto,
            $precio_producto
        );

        $stmt->execute();
    }


    if(isset($_POST['metodo_pago'])){

        $metodo = $_POST['metodo_pago'];

        $check = $conn->query("
            SELECT id_pago FROM pago WHERE id_cita = $id
        ")->fetch_assoc();

        if($check){
            $stmt = $conn->prepare("
                UPDATE pago
                SET total=?, propina=?, metodo=?
                WHERE id_cita=?
            ");
            $stmt->bind_param("ddsi", $total, $propina, $metodo, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("
                INSERT INTO pago (id_cita, total, propina, metodo)
                VALUES (?,?,?,?)
            ");
            $stmt->bind_param("idds", $id, $total, $propina, $metodo);
            $stmt->execute();
        }
    }

    header("Location: ../../home");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Editar Cita</title>
</head>
<body id="editcita" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<h2>Editar cita</h2>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box">

<h3>Cita</h3><br>

<?php foreach($fields as $field):

    $name = $field['Field'];
    $type = $field['Type'];

    // ❌ bloquear campos no editables
    if(!in_array($name, $editableFields)) continue;

    $value = $cita[$name] ?? '';
?>

    <label><?= htmlspecialchars($name) ?></label>

    <?php if($name === 'duracion_total'): ?>

        <input 
            type="text" 
            value="<?= htmlspecialchars($value) ?> minutos" 
            readonly 
            class="readonly-input">

    <?php elseif(str_starts_with($type, 'enum')): ?>

        <?php $options = getEnumValues($type); ?>

        <select name="<?= $name ?>">
            <?php foreach($options as $opt): ?>

                <?php if($opt === 'pendiente_cancelacion') continue; ?>

                <option value="<?= $opt ?>" <?= $value == $opt ? 'selected' : '' ?>>
                    <?= ucfirst($opt) ?>
                </option>

            <?php endforeach; ?>
        </select>

    <?php elseif($name === 'fecha'): ?>

        <input type="date" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">

    <?php elseif($name === 'hora'): ?>

        <input type="time" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">

    <?php elseif(str_contains($type, 'text')): ?>

        <textarea name="<?= $name ?>"><?= htmlspecialchars($value) ?></textarea>

    <?php else: ?>

        <input type="text" name="<?= $name ?>" value="<?= htmlspecialchars($value) ?>">

    <?php endif; ?>

    <br><br>

<?php endforeach; ?>

<h3>Propina</h3><br>

<input
    type="number"
    name="propina"
    min="0"
    step="0.01"
    value="<?= htmlspecialchars($pago['propina'] ?? 0) ?>"
>

<br><br>

<?php 
$servicios = $conn->query("SELECT * FROM servicio ORDER BY nombre ASC");

$servicio_actual = $conn->query("
    SELECT id_servicio
    FROM detalle_cita
    WHERE id_cita = $id
    LIMIT 1
")->fetch_assoc();

$id_servicio_actual = $servicio_actual['id_servicio'] ?? null;
?>

<h3>Servicio</h3><br>

<select name="id_servicio" required>
    <option value="">Selecciona un servicio</option>

    <?php while($s = $servicios->fetch_assoc()): ?>
        <option value="<?= $s['id_servicio'] ?>"
            <?= ($id_servicio_actual == $s['id_servicio']) ? 'selected' : '' ?>>

            <?= htmlspecialchars($s['nombre']) ?> - <?= number_format($s['precio'],2) ?>€

        </option>
    <?php endwhile; ?>
</select>
<br><br>

<?php 
$productos = $conn->query("
    SELECT *
    FROM producto
    WHERE activo = TRUE
    ORDER BY nombre ASC
");

$producto_actual = $conn->query("
    SELECT id_producto, cantidad_cita, stock
    FROM producto
    WHERE id_cita = $id
    LIMIT 1
")->fetch_assoc();

$id_producto_actual = $producto_actual['id_producto'] ?? null;
$cantidad_producto_actual = $producto_actual['cantidad_cita'] ?? 0;
?>

<h3>Producto</h3><br>

<select name="id_producto" id="id_producto">

    <option value="">Seleccionar</option>

    <?php while($p = $productos->fetch_assoc()): ?>

        <option 
            value="<?= $p['id_producto'] ?>"
            data-stock="<?= (int)$p['stock'] ?>"
            <?= ($id_producto_actual == $p['id_producto']) ? 'selected' : '' ?>
        >
            <?= htmlspecialchars($p['nombre']) ?>
            - <?= number_format($p['precio_venta'], 2) ?>€
        </option>

    <?php endwhile; ?>

</select>

<br><br>

<div id="cantidad_producto_wrapper" style="<?= $id_producto_actual ? '' : 'display:none;' ?>">

    <label for="cantidad_producto">Cantidad</label>

    <input
        type="number"
        name="cantidad_producto"
        id="cantidad_producto"
        min="1"
        value="<?= $id_producto_actual ? max(1, (int)$cantidad_producto_actual) : 1 ?>"
    >

    <br>

    <small id="stock_disponible">
        <?php if($id_producto_actual): ?>
            Stock: <?= (int)$producto_actual['stock'] ?>
        <?php endif; ?>
    </small>

    <br><br>

</div>

<label>Método</label>
<select name="metodo_pago">
    <option value="efectivo" <?= ($pago['metodo'] ?? '') == 'efectivo' ? 'selected' : '' ?>>Efectivo</option>
    <option value="tarjeta" <?= ($pago['metodo'] ?? '') == 'tarjeta' ? 'selected' : '' ?>>Tarjeta</option>
    <option value="bizum" <?= ($pago['metodo'] ?? '') == 'bizum' ? 'selected' : '' ?>>Bizum</option>
</select>

<br><br>

<button type="submit">Guardar cambios</button>

</form>
</div>

<?php
$configFile = './config.json';

$config = [];

if (file_exists($configFile)) {
    $json = file_get_contents($configFile);
    $decoded = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $config = $decoded;
    }
}

$version = $config['app']['version'] ?? '0.0.0';
?>

<footer class="site-footer">
    <span class="version">v.<?= htmlspecialchars($version) ?></span><br>
    <span class="copyright">© 2026 alfonsogm</span>
</footer>

<footer class="policy">
        <a href="../includes/privacidad.html">Política de Privacidad</a> |
        <a href="../includes/cookies.html">Política de Cookies</a> |
        <a href="../includes/terminos.html">Términos y Condiciones</a>
</footer>

<script>

const productoSelect = document.getElementById('id_producto');
const cantidadWrapper = document.getElementById('cantidad_producto_wrapper');
const cantidadInput = document.getElementById('cantidad_producto');
const stockTexto = document.getElementById('stock_disponible');

function actualizarProducto() {

    const opcion = productoSelect.options[productoSelect.selectedIndex];

    // No hay producto seleccionado
    if (!opcion || !opcion.value) {

        cantidadWrapper.style.display = 'none';

        cantidadInput.value = 1;
        cantidadInput.removeAttribute('max');

        stockTexto.textContent = '';

        return;
    }

    // Hay producto seleccionado
    const stock = parseInt(opcion.dataset.stock) || 0;

    cantidadWrapper.style.display = 'block';

    cantidadInput.max = stock;

    // Si la cantidad actual supera el stock
    if (parseInt(cantidadInput.value) > stock) {
        cantidadInput.value = stock;
    }

    stockTexto.textContent = 'Stock: ' + stock;
}


// Cambiar producto
productoSelect.addEventListener('change', actualizarProducto);


// Controlar cantidad manualmente
cantidadInput.addEventListener('input', function() {

    const max = parseInt(this.max);
    const valor = parseInt(this.value);

    if (max && valor > max) {
        this.value = max;
    }

    if (valor < 1 || isNaN(valor)) {
        this.value = 1;
    }
});


// Estado inicial
actualizarProducto();

</script>
</body>
</html>