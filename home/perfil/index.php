<?php
session_start();

include '../admin/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit;
}

$data = json_decode(file_get_contents('../admin/config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('../admin/config.json', json_encode($data));
}

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['user_type'];

$username = '';
$nombre = '';
$email = '';
$telefono = '';
$especialidad = '';

$res = $conn->query("SELECT username FROM usuario WHERE id_usuario = $user_id");
if ($res && $res->num_rows) {
    $username = $res->fetch_assoc()['username'];
}

switch ($rol) {
    case 'cliente':
        $res = $conn->query("SELECT nombre, email, telefono FROM cliente WHERE id_usuario = $user_id");
        if ($res && $res->num_rows) {
            $row = $res->fetch_assoc();
            $nombre = $row['nombre'];
            $email = $row['email'];
            $telefono = $row['telefono'];
        }
        break;
    case 'barbero':
        $res = $conn->query("SELECT nombre, especialidad FROM barbero WHERE id_usuario = $user_id");
        if ($res && $res->num_rows) {
            $row = $res->fetch_assoc();
            $nombre = $row['nombre'];
            $especialidad = $row['especialidad'];
        }
        break;
}

$mensaje = '';
$errores = [];
$input_errors = [];
$show_username_inputs = false;
$show_password_inputs = false;
$show_data_inputs = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- CAMBIO DE USERNAME ---
    if(!empty($_POST['username_current']) && !empty($_POST['username_new'])){
        $show_username_inputs = true;
        $user_actual = $_POST['username_current'];
        $user_nuevo = $_POST['username_new'];

        if($user_actual === $username){
            $sql = "UPDATE usuario SET username='$user_nuevo' WHERE id_usuario=$user_id";
            if($conn->query($sql)){
                $username = $user_nuevo;
                $_SESSION['user_name'] = $username;
                $mensaje .= " Usuario actualizado correctamente.";
                $show_username_inputs = false;
            } else { 
                $errores[] = "Error al actualizar el usuario: ".$conn->error;
                $input_errors[] = 'username_current_input';
            }
        } else {
            $errores[] = "Usuario actual incorrecto.";
            $input_errors[] = 'username_current_input';
        }
    }

    // --- CAMBIO DE CONTRASEÑA ---
    if(!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])){
        $show_password_inputs = true;

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $res = $conn->query("SELECT password FROM usuario WHERE id_usuario=$user_id");
        $hash_actual = $res->fetch_assoc()['password'];

        if(password_verify($current,$hash_actual)){
            if($new === $confirm){
                if(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new)){
                    $errores[] = "La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.";
                    $input_errors[] = 'new_password_input';
                    $input_errors[] = 'confirm_password_input';
                } else {
                    $hash_new = password_hash($new,PASSWORD_DEFAULT);
                    $conn->query("UPDATE usuario SET password='$hash_new' WHERE id_usuario=$user_id");
                    $mensaje .= " Contraseña actualizada correctamente.";
                    $show_password_inputs = false;
                }
            } else {
                $errores[] = "Nueva contraseña y confirmación no coinciden.";
                $input_errors[] = 'new_password_input';
                $input_errors[] = 'confirm_password_input';
            }
        } else {
            $errores[] = "Contraseña actual incorrecta.";
            $input_errors[] = 'current_password_input';
        }
    }

    // --- DATOS ---
    if(!empty($_POST['nombre'])){
        $show_data_inputs = true;
        $nombre = $conn->real_escape_string($_POST['nombre']);
        if($rol==='cliente'){
            $email = $conn->real_escape_string($_POST['email'] ?? '');
            $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
            $conn->query("UPDATE cliente SET nombre='$nombre', email='$email', telefono='$telefono' WHERE id_usuario=$user_id");
        } else {
            $especialidad = $conn->real_escape_string($_POST['especialidad'] ?? '');
            $conn->query("UPDATE barbero SET nombre='$nombre', especialidad='$especialidad' WHERE id_usuario=$user_id");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>ARSTUDIO · Perfil</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<?php include '../includes/navbar.php'; ?>

<h1>Perfil</h1>

<?php 
if($mensaje) echo "<p style='color:green; text-align:center;'>$mensaje</p>"; 
if($errores) foreach($errores as $err) echo "<p style='color:red; text-align:center;'>$err</p>";
?>

<div class="perfil_wrapper">
<form method="POST" class="perfil_box" id="perfil_form">


<p>
    <i class="fa-solid fa-user"></i>
    <b>Usuario:</b> <?php echo $username; ?>
    <i class="fa-solid fa-pen edit_icon" data-target="username" onclick="editar(this)"></i>
    <div id="username_inputs" style="display:<?php echo $show_username_inputs?'block':'none'; ?>;">
        <input type="text" id="username_current_input" name="username_current" class="editable_input <?php echo in_array('username_current_input',$input_errors)?'error_input':''; ?>" placeholder="Usuario actual">
        <input type="text" id="username_new_input" name="username_new" class="editable_input" placeholder="Nuevo usuario">
    </div>
</p>


<p>
    <i class="fa-solid fa-key"></i>
    <b>Contraseña:</b> ********
    <i class="fa-solid fa-pen edit_icon" data-target="password" onclick="editar(this)"></i>
    <div id="password_inputs" style="display:<?php echo $show_password_inputs?'block':'none'; ?>;">
        <div class="input_wrapper">
            <input type="password" id="current_password_input" name="current_password" class="editable_input <?php echo in_array('current_password_input',$input_errors)?'error_input':''; ?>" placeholder="Contraseña actual">
            <i class="fa-solid fa-eye toggle_eye" data-target="current_password_input" onclick="togglePassword(this)"></i>
        </div>
        <div class="input_wrapper">
            <input type="password" id="new_password_input" name="new_password" class="editable_input <?php echo in_array('new_password_input',$input_errors)?'error_input':''; ?>" placeholder="Nueva contraseña">
            <i class="fa-solid fa-eye toggle_eye" data-target="new_password_input" onclick="togglePassword(this)"></i>
        </div>
        <div class="input_wrapper">
            <input type="password" id="confirm_password_input" name="confirm_password" class="editable_input <?php echo in_array('confirm_password_input',$input_errors)?'error_input':''; ?>" placeholder="Confirmar nueva contraseña">
            <i class="fa-solid fa-eye toggle_eye" data-target="confirm_password_input" onclick="togglePassword(this)"></i>
        </div>
        <p style="font-size:10px; color:#555;">Contraseña: 8+ caracteres, mayúscula, minúscula, número y carácter especial.</p>
    </div>
</p>


<?php if($rol==='cliente'): ?>
<p><input type="text" name="nombre" value="<?php echo $nombre; ?>" placeholder="Nombre"></p>
<p><input type="email" name="email" value="<?php echo $email; ?>" placeholder="Email"></p>
<p><input type="text" name="telefono" value="<?php echo $telefono; ?>" placeholder="Teléfono"></p>
<?php elseif($rol==='barbero'): ?>
<p><input type="text" name="nombre" value="<?php echo $nombre; ?>" placeholder="Nombre"></p>
<p><input type="text" name="especialidad" value="<?php echo $especialidad; ?>" placeholder="Especialidad"></p>
<?php endif; ?>


<p><i class="fa-solid fa-user-shield"></i> <b>Rol:</b> <?php echo $rol; ?></p>

<button type="submit" id="save_btn" style="display:none;">Guardar</button>

</form>
</div>

<?php
$configFile = '../admin/config.json';

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

<footer class="version">
    <p>v.<?= htmlspecialchars($version) ?></p>
</footer>

<footer class="copyright">
    <p>© 2026 alfonsogm</p>
</footer>


<script src="../../js/editpasS.js"></script>
<script src="../../js/lightmode.js"></script>

</body>
</html>