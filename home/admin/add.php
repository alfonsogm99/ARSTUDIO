<?php
include 'config.php';
$tabla = $_GET['tabla'];

// Columnas (excepto AUTO_INCREMENT)
$res = $conn->query("SHOW COLUMNS FROM $tabla");
$columns = [];
while($row = $res->fetch_assoc()) {
    if($row['Extra'] != 'auto_increment') $columns[] = $row['Field'];
}

// Procesar formulario
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = implode(",", array_keys($_POST));
    $values = implode("','", array_map([$conn,'real_escape_string'], $_POST));
    $sql = "INSERT INTO $tabla ($fields) VALUES ('$values')";
    if($conn->query($sql)) header("Location: admin.php?tabla=$tabla");
    else echo "Error: ".$conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../css/style.css">
<title>Añadir registro - <?php echo $tabla; ?></title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">
<h1>Añadir registro a <?php echo $tabla; ?></h1>
<form method="post">
<?php foreach($columns as $col): ?>
<label><?php echo $col; ?>: <input type="text" name="<?php echo $col; ?>"></label><br>
<?php endforeach; ?>
<button type="submit">Guardar</button>
</form>

<p><a href="admin.php?tabla=<?php echo $tabla; ?>" id="btn_back">Volver</a></p>

</body>
</html>