<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','barbero'])){
    header("Location: ../logout");
    exit;
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $tipo = $_POST['tipo'];

    $precio_compra = floatval($_POST['precio_compra']);
    $precio_venta = floatval($_POST['precio_venta']);

    $stock = intval($_POST['stock']);
    $stock_minimo = intval($_POST['stock_minimo']);


    if(isset($_POST['id_producto']) && $_POST['id_producto'] != ''){


        $stmt = $conn->prepare("
            UPDATE producto
            SET 
                nombre=?,
                descripcion=?,
                tipo=?,
                precio_compra=?,
                precio_venta=?,
                stock=?,
                stock_minimo=?
            WHERE id_producto=?
        ");


        $stmt->bind_param(
            "sssddiii",
            $nombre,
            $descripcion,
            $tipo,
            $precio_compra,
            $precio_venta,
            $stock,
            $stock_minimo,
            $_POST['id_producto']
        );


        $stmt->execute();


    } else {


        $stmt = $conn->prepare("
            INSERT INTO producto
            (
                nombre,
                descripcion,
                tipo,
                precio_compra,
                precio_venta,
                stock,
                stock_minimo
            )
            VALUES (?,?,?,?,?,?,?)
        ");


        $stmt->bind_param(
            "sssddii",
            $nombre,
            $descripcion,
            $tipo,
            $precio_compra,
            $precio_venta,
            $stock,
            $stock_minimo
        );



        $stmt->execute();

    }


    header("Location: productos.php");
    exit;

}



// =========================
// ELIMINAR
// =========================

if(isset($_GET['delete'])){


    $id = intval($_GET['delete']);


    $stmt = $conn->prepare("
        DELETE FROM producto
        WHERE id_producto=?
    ");


    $stmt->bind_param("i",$id);
    $stmt->execute();


    header("Location: productos.php");
    exit;

}



// =========================
// EDITAR
// =========================

$edit = null;


if(isset($_GET['edit'])){

    $id = intval($_GET['edit']);

    $edit = $conn
        ->query("SELECT * FROM producto WHERE id_producto=$id")
        ->fetch_assoc();

}



// =========================
// LISTADO
// =========================

$productos = $conn->query("

SELECT 
    p.*,

    (
        SELECT COUNT(*)
        FROM detalle_venta_producto dvp
        WHERE dvp.id_producto=p.id_producto
    ) AS total_ventas

FROM producto p

ORDER BY p.id_producto DESC

");


$tieneProductos = $productos->num_rows > 0;


?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Productos</title>
</head>
<body id="productos">


<div id="top-fixed">

<?php include '../includes/navbar.php'; ?>

</div>



<h2 style="text-align:center;">
    Productos
</h2>



<div class="contabilidad_box">


<?php if($tieneProductos): ?>


<table id="tablaProductos" class="tableau">


<thead>

<tr>

<th onclick="ordenarTabla(this,0)">
ID
</th>

<th onclick="ordenarTabla(this,1)">
Nombre
</th>

<th onclick="ordenarTabla(this,2)">
Tipo
</th>

<th onclick="ordenarTabla(this,3)">
Precio venta
</th>

<th onclick="ordenarTabla(this,4)">
Stock
</th>

<th>
Acciones
</th>

</tr>

</thead>



<tbody>


<?php while($p = $productos->fetch_assoc()): ?>


<tr>


<td>
#<?= $p['id_producto'] ?>
</td>


<td>
<?= htmlspecialchars($p['nombre']) ?>
</td>

<td>
<?= ucfirst(htmlspecialchars($p['tipo'])) ?>
</td>

<td>
<?= number_format($p['precio_venta'],2) ?>€
</td>


<td>
    <?= $p['stock'] ?>
</td>


<td class="actions">


<a href="javascript:void(0)"

onclick='toggleFormProducto({

id_producto: <?= $p["id_producto"] ?>,

nombre:"<?= htmlspecialchars($p["nombre"],ENT_QUOTES) ?>",

tipo:"<?= htmlspecialchars($p["tipo"],ENT_QUOTES) ?>",

descripcion:"<?= htmlspecialchars($p["descripcion"],ENT_QUOTES) ?>",

precio_compra:<?= $p["precio_compra"] ?>,

precio_venta:<?= $p["precio_venta"] ?>,

stock:<?= $p["stock"] ?>,

stock_minimo:<?= $p["stock_minimo"] ?>

})'>

<b>Editar</b>

</a>



<a class="delete"

href="?delete=<?= $p['id_producto'] ?>"

onclick="return confirm('<?= $p['total_ventas'] > 0

? '⚠️ Este producto tiene '.$p['total_ventas'].' venta(s). ¿Eliminarlo?'

: '¿Eliminar producto?' ?>')">

<b>Eliminar</b>

</a>



</td>


</tr>


<?php endwhile; ?>


</tbody>


</table>


<?php else: ?>


<p class="empty-state">
Aún no hay productos registrados.
</p>


<?php endif; ?>


</div>



<br>


<button type="button"
style="margin:auto;display:block;"
onclick="toggleFormProducto(null)">

Nuevo producto

</button>




<div id="formProducto" style="display:none;">


<div class="perfil_wrapper">


<form method="POST" class="perfil_box">


<input type="hidden"
name="id_producto"
value="<?= $edit['id_producto'] ?? '' ?>">



<label>
Nombre
</label>

<input type="text"
name="nombre"
required
value="<?= htmlspecialchars($edit['nombre'] ?? '') ?>">



<label>
Descripción
</label>

<textarea name="descripcion"><?= htmlspecialchars($edit['descripcion'] ?? '') ?></textarea>


<label>Tipo</label>

<select name="tipo" required>

<?php

$resultTipos = $conn->query("
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'producto'
    AND COLUMN_NAME = 'tipo'
");

$rowTipo = $resultTipos->fetch_assoc();

$enum = $rowTipo['COLUMN_TYPE'];

// Extraer valores del ENUM
preg_match("/^enum\((.*)\)$/", $enum, $matches);

$tipos = str_getcsv($matches[1], ',', "'");


foreach($tipos as $tipo):

?>

<option value="<?= htmlspecialchars($tipo) ?>"
    <?= isset($edit['tipo']) && $edit['tipo'] === $tipo ? 'selected' : '' ?>
>
    <?= ucfirst(htmlspecialchars($tipo)) ?>
</option>

<?php endforeach; ?>

</select>




<label>
Precio compra (€)
</label>

<input type="number"
step="0.01"
name="precio_compra"
value="<?= $edit['precio_compra'] ?? '' ?>">



<label>
Precio venta (€)
</label>

<input type="number"
step="0.01"
name="precio_venta"
required
value="<?= $edit['precio_venta'] ?? '' ?>">



<label>
Stock
</label>

<input type="number"
name="stock"
required
value="<?= $edit['stock'] ?? 0 ?>">



<label>
Stock mínimo
</label>

<input type="number"
name="stock_minimo"
value="<?= $edit['stock_minimo'] ?? 0 ?>">



<button type="submit">

<?= $edit ? 'Actualizar' : 'Crear' ?>

</button>


</form>


</div>


</div>




<?php if($edit): ?>

<script>

document.addEventListener("DOMContentLoaded",function(){

document.getElementById('formProducto').style.display='block';

});

</script>

<?php endif; ?>

<script src="../../js/toggleForm.js"></script>

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

<script src="../../js/orderTableau.js"></script>

</body>
</html>