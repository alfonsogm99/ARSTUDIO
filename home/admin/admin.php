<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$data = json_decode(file_get_contents('config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('config.json', json_encode($data));
}

// ✅ Tablas que el admin puede ver
$tablas_validas = ['usuario','cliente','barbero','servicio','cita','detalle_cita','pago','historico_cliente'];

// Tablas para el botón añadir
$tablas_validas_add = ['usuario','servicio','cita','detalle_cita','pago','historico_cliente'];

// Tomar tabla de GET si es válida, sino default a 'usuario'
$tabla = (isset($_GET['tabla']) && in_array($_GET['tabla'], $tablas_validas))
    ? $_GET['tabla']
    : 'usuario';

// ⚡ Obtener registros de la tabla actual
$result = $conn->query("SELECT * FROM `$tabla`");
if (!$result) die("Error en la consulta: " . $conn->error);

// Columnas de la tabla
$columns = [];
$fields = $result->fetch_fields();
foreach ($fields as $f) $columns[] = $f->name;

// Saber si se puede añadir
$puede_añadir = in_array($tabla, $tablas_validas_add);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
<title>sysadmin | <?php echo ucfirst($tabla); ?></title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<?php include '../includes/navbar.php'; ?>

<h1>Administración: <?php echo ucfirst($tabla); ?></h1>

<p id="menu_tablas">
<?php
foreach ($tablas_validas as $t) {
    echo "<a href='?tabla=$t'>$t</a> ";
}
?>
</p>


<br>

<!-- Tabla de registros -->
<table>
<tr>
<?php foreach ($columns as $col) echo "<th>$col</th>"; ?>
<th>Acciones</th>
</tr>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<?php foreach ($columns as $col) echo "<td>".$row[$col]."</td>"; ?>
<td>
<a id="btn_edit" href="form.php?tabla=<?php echo $tabla; ?>&id=<?php echo $row[$columns[0]]; ?>">
    <i class="fa-solid fa-pen"></i>
</a>
<a id="btn_delete" href="delete.php?tabla=<?php echo $tabla; ?>&id=<?php echo $row[$columns[0]]; ?>" 
   onclick="return confirm('¿Seguro?');">
    <i class="fa-solid fa-x"></i>
</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<?php
$tablas_validas_add = ['usuario','servicio','detalle_cita','pago','historico_cliente'];
$puede_añadir = in_array($tabla, $tablas_validas_add);
?>

<?php if($puede_añadir): ?>
<p>
    <a href="form.php?tabla=<?php echo $tabla; ?>"><button id="btn_add">Añadir</button></a>
</p>
<?php endif; ?>

</body>
</html>