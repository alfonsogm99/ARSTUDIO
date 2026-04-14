<?php
include 'config.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

// Validar token
$stmt = $conn->prepare("
    SELECT a.id_cliente, a.expiracion, a.usado, c.nombre
    FROM activacion_usuario a
    JOIN cliente c ON c.id_cliente = a.id_cliente
    WHERE token=?
");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

if(!$data){
    die('<body style="background-color:#000;"><p style="color:ccc">Token inválido</p></body>');
}

if($data['usado']){
    die("Este enlace ya fue utilizado.");
}

if(strtotime($data['expiracion']) < time()){
    die("Token expirado.");
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if($username && $_POST['password']){

        // Crear usuario
        $stmt = $conn->prepare("INSERT INTO usuario (username,password,rol) VALUES (?,?, 'cliente')");
        $stmt->bind_param("ss", $username, $password);

        if($stmt->execute()){
            $id_usuario = $stmt->insert_id;

            // Vincular cliente
            $stmt2 = $conn->prepare("UPDATE cliente SET id_usuario=? WHERE id_cliente=?");
            $stmt2->bind_param("ii", $id_usuario, $data['id_cliente']);
            $stmt2->execute();

            // Marcar token usado
            $stmt3 = $conn->prepare("UPDATE activacion_usuario SET usado=1 WHERE token=?");
            $stmt3->bind_param("s", $token);
            $stmt3->execute();

            $success = "Usuario creado correctamente.";
        } else {
            $error = "Error: ".$conn->error;
        }

    } else {
        $error = "Completa todos los campos.";
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
<title>ARSTUDIO · Nuevo Usuario</title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<?php include '../includes/navbar.php'; ?>

<h2>Crear tu cuenta</h2>

<p>Hola <?php echo htmlspecialchars($data['nombre']); ?></p>

<?php if($error) echo "<p class='error'>$error</p>"; ?>
<?php if($success) echo "<p class='success'>$success</p>"; ?>

<?php if(!$success): ?>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box">
    <label>Usuario:</label>
    <input type="text" name="username" required>

    <label>Contraseña:</label>
    <input type="password" name="password" required>

    <button type="submit">Crear cuenta</button>
</form>
</div>
<?php endif; ?>

</body>
</html>