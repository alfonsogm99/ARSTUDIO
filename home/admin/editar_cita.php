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

// Obtener cita
$cita = $conn->query("SELECT * FROM cita WHERE id_cita=$id")->fetch_assoc();

if(!$cita){
    die("Cita no encontrada");
}

// Actualizar
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $estado = $_POST['estado'];
    $notas = $_POST['notas'];

    $stmt = $conn->prepare("UPDATE cita SET fecha=?, hora=?, estado=?, notas=? WHERE id_cita=?");
    $stmt->bind_param("ssssi", $fecha, $hora, $estado, $notas, $id);
    $stmt->execute();

    header("Location: ../control_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>Calendario</title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<?php include '../includes/navbar.php'; ?>
<h2>Editar cita</h2>

<br>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box">
    Fecha: <input type="date" name="fecha" value="<?= $cita['fecha'] ?>"><br>
    Hora: <input type="time" name="hora" value="<?= $cita['hora'] ?>"><br>

    Estado:
    <select name="estado">
        <option value="pendiente">Pendiente</option>
        <option value="confirmada">Confirmada</option>
        <option value="completada">Completada</option>
        <option value="cancelada">Cancelada</option>
        <option value="fallida">Fallida</option>
    </select><br>

    Notas:<br>
    <textarea name="notas"><?= $cita['notas'] ?></textarea><br>

    <button type="submit">Guardar cambios</button>
</form>
</div>

<footer class="version">
    <p>v.1.0</p>
</footer>

<footer class="copyright">
    <p>© 2026 alfonsogm</p>
</footer>


</body>
</html>