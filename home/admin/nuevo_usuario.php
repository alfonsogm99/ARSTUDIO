<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: ../login.php");
    exit;
}

$nombre = $_GET['nombre'] ?? '';
$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $nombre = trim($_POST['nombre'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if($nombre && $dni && $email){
        $check = $conn->prepare("SELECT id_cliente FROM cliente WHERE dni=?");
        $check->bind_param("s", $dni);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){
            $error = "Ya existe un cliente con ese DNI.";
        } else {
            $stmt = $conn->prepare("INSERT INTO cliente (nombre, dni, email) VALUES (?,?,?)");
            $stmt->bind_param("sss", $nombre, $dni, $email);

            if($stmt->execute()){
                $id_nuevo = $stmt->insert_id;
                header("Location: añadir_cita.php?id_cliente=".$id_nuevo);
                exit;
            } else {
                $error = "Error al crear cliente: ".$conn->error;
            }
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
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
<title>ARSTUDIO · Nuevo Cliente</title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<?php include '../includes/navbar.php'; ?>

<h2>Nuevo cliente</h2>

<?php if($error) echo "<p class='error'>$error</p>"; ?>
<?php if($success) echo "<p class='success'>$success</p>"; ?>

<div class="perfil_wrapper">

<?php if($error) echo "<p class='error'>$error</p>"; ?>
<?php if($success) echo "<p class='success'>$success</p>"; ?>

<form method="POST" class="perfil_box" action="newusermsg.php">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>" required>

    <label>DNI:</label>
    <div class="dni-div">
    <input type="text" name="dni" id="dni" required>
    <button type="button" onclick="generarDNI()"><i class="fa-solid fa-dice-d20"></i></button>
    </div>

    <label>Email:</label>
    <input type="email" name="email" required>

    <button type="submit">Crear cliente y enviar acceso</button>
</form>

</div>

<footer class="version">
    <p>v.1.0</p>
</footer>

<footer class="copyright">
    <p>© 2026 alfonsogm</p>
</footer>

<script src="../../js/random.js"></script>

</body>
</html>