<?php
include 'config.php';
$tabla = $_GET['tabla'];
$id = $_GET['id'];

// Tomar primer campo como PK
$res = $conn->query("SHOW COLUMNS FROM $tabla");
$columns = [];
$pk = '';
while($row = $res->fetch_assoc()) {
    if($row['Extra'] == 'auto_increment') $pk = $row['Field'];
    else $columns[] = $row['Field'];
}

// Obtener datos
$data = $conn->query("SELECT * FROM $tabla WHERE $pk='$id'")->fetch_assoc();

// Procesar formulario
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sets = [];
    foreach($columns as $col) $sets[] = "$col='".$conn->real_escape_string($_POST[$col])."'";
    $sql = "UPDATE $tabla SET ".implode(",",$sets)." WHERE $pk='$id'";
    if($conn->query($sql)) header("Location: admin.php?tabla=$tabla");
    else echo "Error: ".$conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <meta charset="UTF-8"><title>Editar registro - <?php echo $tabla; ?></title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">
<h1>Editar registro de <?php echo $tabla; ?></h1>
<form method="post">
<?php foreach($columns as $col): ?>
<label><?php echo $col; ?>: <input type="text" name="<?php echo $col; ?>" value="<?php echo $data[$col]; ?>"></label><br>
<?php endforeach; ?>
<button type="submit">Guardar cambios</button>
</form>
<p><a href="admin.php?tabla=<?php echo $tabla; ?>"><b>Volver</b></a></p>

<footer class="site-footer">
    <span class="version">v.<?= htmlspecialchars($version) ?></span><br>
    <span class="copyright">© 2026 alfonsogm</span>
</footer>

<footer class="policy">
        <a href="../includes/privacidad.html">Política de Privacidad</a> |
        <a href="../includes/cookies.html">Política de Cookies</a> |
        <a href="../includes/terminos.html">Términos y Condiciones</a>
</footer>

</body>
</html>