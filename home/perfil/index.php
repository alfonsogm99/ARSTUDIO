<?php

require_once '../admin/bootstrap.php';
require_once '../admin/auth.php';

$data = json_decode(file_get_contents('../admin/config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('../admin/config.json', json_encode($data));
}

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['user_type'];

$username = '';
$nombre_usuario = '';
$email = '';
$telefono = '';
$especialidad = '';
$horario = '';
$nombre_horario = '';
$id_barbero = null;

$res = $conn->query("SELECT id_barbero FROM barbero WHERE id_usuario = $user_id");
if ($res && $res->num_rows > 0) {
    $id_barbero = $res->fetch_assoc()['id_barbero'];
}

$ausencias = null;

if ($id_barbero) {
    $ausencias = $conn->query("
        SELECT id_bloqueo, fecha, hora_inicio, hora_fin, tipo, motivo
        FROM bloqueo_horario
        WHERE id_barbero = $id_barbero
        ORDER BY fecha DESC
    ");
}


$res = $conn->query("SELECT username FROM usuario WHERE id_usuario = $user_id");
if ($res && $res->num_rows) {
    $username = $res->fetch_assoc()['username'];
}

switch ($rol) {
    case 'cliente':
        $res = $conn->query("SELECT nombre, email, telefono FROM cliente WHERE id_usuario = $user_id");
        if ($res && $res->num_rows) {
            $row = $res->fetch_assoc();
            $nombre_usuario = $row['nombre'];
            $email = $row['email'];
            $telefono = $row['telefono'];
        }
        break;
    case 'barbero':
        $res = $conn->query("SELECT nombre, especialidad FROM barbero WHERE id_usuario = $user_id");
        if ($res && $res->num_rows) {
            $row = $res->fetch_assoc();
            $nombre_usuario = $row['nombre'];
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
    if(!empty($_POST['nombre_usuario'])){
        $show_data_inputs = true;
        $nombre_usuario = $conn->real_escape_string($_POST['nombre_usuario']);
        if($rol==='cliente'){
            $email = $conn->real_escape_string($_POST['email'] ?? '');
            $telefono = $conn->real_escape_string($_POST['telefono'] ?? '');
            $conn->query("UPDATE cliente SET nombre='$nombre_usuario', email='$email', telefono='$telefono' WHERE id_usuario=$user_id");
        } else {
            $especialidad = $conn->real_escape_string($_POST['especialidad'] ?? '');
            $conn->query("UPDATE barbero SET nombre='$nombre_usuario', especialidad='$especialidad' WHERE id_usuario=$user_id");
        }
    }


    // --- CAMBIO DE HORARIO ASIGNADO ---
    if (isset($_POST['cambiar_horario']) && $rol === 'barbero') {

        $id_barbero = $conn->query("SELECT id_barbero FROM barbero WHERE id_usuario = $user_id")->fetch_assoc()['id_barbero'] ?? null;

        if ($id_barbero) {
            $id_general = $_POST['id_horario'] ?? null;

            if ($id_general && $id_general !== 'new') {
                $id_general = (int)$id_general;

                // Desactivar todos los horarios de este barbero
                $sql_off = "UPDATE horario_general 
                            SET activo = 0 
                            WHERE id_barbero = $id_barbero";
                $conn->query($sql_off);

                // Activar solo los horarios de la plantilla seleccionada
                $sql_on = "UPDATE horario_general 
                        SET activo = 1 
                        WHERE id_barbero = $id_barbero 
                            AND id_general = $id_general";
                if ($conn->query($sql_on)) {
                    $mensaje .= " Horario actualizado correctamente.";
                } else {
                    $errores[] = "Error al actualizar el horario: " . $conn->error;
                }

            } elseif ($id_general === 'new') {
                header("Location: nuevo_horario.php");
                exit;
            }
        }
    }

    // --- ELIMINAR HORARIO ---
    if (isset($_POST['eliminar_horario']) && $rol === 'barbero') {

        $id_barbero = $conn->query("
            SELECT id_barbero 
            FROM barbero 
            WHERE id_usuario = $user_id
        ")->fetch_assoc()['id_barbero'] ?? null;

        if ($id_barbero) {

            $id_general = (int)($_POST['id_horario'] ?? 0);

            if ($id_general > 0) {

                $sql = "
                    DELETE FROM horario_general
                    WHERE id_general = $id_general
                ";

                if ($conn->query($sql)) {

                    $mensaje .= " Horario eliminado correctamente.";

                    header("Location: ../perfil");
                    exit;

                } else {

                    $errores[] = "Error al eliminar horario: " . $conn->error;
                }
            }
        }
    }
}







// =======================
// 🔹 HORARIO VIP
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'vip_create') {

    $id_barbero = $conn->query("
        SELECT id_barbero 
        FROM barbero 
        WHERE id_usuario = $user_id
    ")->fetch_assoc()['id_barbero'] ?? null;

    if (!$id_barbero) {
        die("No se pudo identificar el barbero");
    }

    $fecha = $_POST['fecha'] ?? null;
    $hora_inicio = $_POST['hora_inicio'] ?? null;
    $hora_fin = $_POST['hora_fin'] ?? null;
    $motivo = trim($_POST['motivo'] ?? '');

    if (!$fecha || !$hora_inicio || !$hora_fin) {
        die("Datos incompletos");
    }

    if ($hora_fin <= $hora_inicio) {
        die("La hora fin debe ser mayor que la inicio");
    }

    $stmt = $conn->prepare("
        INSERT INTO vip_horario (
            id_barbero,
            fecha,
            hora_inicio,
            hora_fin,
            motivo,
            creada_por
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");

    $creada_por = $rol;

    $stmt->bind_param(
        "isssss",
        $id_barbero,
        $fecha,
        $hora_inicio,
        $hora_fin,
        $motivo,
        $creada_por
    );

    $stmt->execute();

    header("Location: ../../home");
    exit;
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

<body id="perfil" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

    <div id="top-fixed">
    <?php include '../includes/navbar.php'; ?>
    </div>

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
            <div class="input-wrapper">
                <input type="password" id="current_password_input" name="current_password" <?php echo in_array('current_password_input',$input_errors)?'error_input':''; ?>" placeholder="Contraseña actual">
                <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
            </div>
            <div class="input-wrapper">
                <input type="password" id="new_password_input" name="new_password" <?php echo in_array('new_password_input',$input_errors)?'error_input':''; ?>" placeholder="Nueva contraseña">
                <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
            </div>
            <div class="input-wrapper">
                <input type="password" id="confirm_password_input" name="confirm_password" <?php echo in_array('confirm_password_input',$input_errors)?'error_input':''; ?>" placeholder="Confirmar nueva contraseña">
                <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
            </div>
            <p style="font-size:10px; color:#555;">Contraseña: 8+ caracteres, mayúscula, minúscula, número y carácter especial.</p>
        </div>
    </p>


    <?php if($rol==='cliente'): ?>
    <p><input type="text" name="nombre_usuario" value="<?php echo $nombre_usuario; ?>" placeholder="Nombre"></p>
    <p><input type="email" name="email" value="<?php echo $email; ?>" placeholder="Email"></p>
    <p><input type="text" name="telefono" value="<?php echo $telefono; ?>" placeholder="Teléfono"></p>
    <?php elseif($rol==='barbero'): ?>
    <p><input type="text" name="nombre_usuario" value="<?php echo $nombre_usuario; ?>" placeholder="Nombre"></p>
    <p><input type="text" name="especialidad" value="<?php echo $especialidad; ?>" placeholder="Especialidad"></p>
    <?php endif; ?>

    <p><i class="fa-solid fa-user-shield"></i> <b>Rol:</b> <?php echo $rol; ?></p>

    <?php
    $id_barbero = $conn->query("SELECT id_barbero FROM barbero WHERE id_usuario = $user_id")->fetch_assoc()['id_barbero'] ?? null;

    if($id_barbero){
    // Obtener el horario activo del barbero
    $horario_asignado_id = $conn->query("
        SELECT hb.id_general 
        FROM horario_general hg
        INNER JOIN horario_barbero hb
            ON hg.id_general = hb.id_general
        WHERE hb.id_barbero = $id_barbero
        AND hg.activo = 1
        LIMIT 1
    ")->fetch_assoc()['id_general'] ?? null;

    // Obtener todos los horarios generales
    $stmt = $conn->prepare("
        SELECT 
            hg.id_general AS id_general,
            hg.nombre AS nombre_general,
            hb.id_horario AS id_horario,
            hb.dia_semana,
            hb.hora_inicio,
            hb.hora_fin,
            hb.pausa_inicio,
            hb.pausa_fin,
            hg.activo AS activo_horario
        FROM horario_general hg
        INNER JOIN horario_barbero hb
            ON hg.id_general = hb.id_general
        WHERE hg.id_barbero = ?
        ORDER BY hg.id_general DESC, hb.dia_semana ASC
    ");

    $stmt->bind_param("i", $id_barbero);

    $stmt->execute();

    $res = $stmt->get_result();

    $horarios_array = [];

    if ($res && $res->num_rows) {

        while ($h = $res->fetch_assoc()) {

            $id = $h['id_general'];

            // Crear horario si no existe
            if (!isset($horarios_array[$id])) {

                $horarios_array[$id] = [
                    'id_general' => $id,
                    'nombre_general' => $h['nombre_general'],
                    'activo' => ($id == $horario_asignado_id),
                    'dias' => []
                ];
            }

            // Añadir día
            $horarios_array[$id]['dias'][] = [
                'dia_semana' => $h['dia_semana'],
                'hora_inicio' => $h['hora_inicio'],
                'hora_fin' => $h['hora_fin'],
                'pausa_inicio' => $h['pausa_inicio'],
                'pausa_fin' => $h['pausa_fin']
            ];
        }
    }
    }
    ?>

    <?php if($rol === 'barbero'): ?>

        <h4>
            <i class="fa-solid fa-clock"></i>
            Horario activo:

        <?php     
            
            $nombre_horario_asignado = null;

            if (!empty($horarios_array)) {
                foreach ($horarios_array as $h) {
                    if ($h['activo']) {
                        $nombre_horario_asignado = $h['nombre_general'];
                        break;
                    }
                }
            }


            if ($nombre_horario_asignado): ?>
                <span style="font-weight: normal; font-size: 0.9em; color: #aaa;">
                    <?= htmlspecialchars($nombre_horario_asignado) ?>
                </span>
            <?php endif; ?>
        </h4>

        <?php if (!$nombre_horario_asignado): ?>
            <p>No hay un horario definido</p>
        <?php endif; ?>


        <button type="submit" id="btn_guardar_perfil" style="display:none;">
            Guardar cambios
        </button>

        </div>

    <?php endif; ?>

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

    <footer class="site-footer">
        <span class="version">v.<?= htmlspecialchars($version) ?></span><br>
        <span class="copyright">© 2026 alfonsogm</span>
    </footer>

    <footer class="policy">
        <a href="../includes/privacidad.html">Política de Privacidad</a> |
        <a href="../includes/cookies.html">Política de Cookies</a> |
        <a href="../includes/terminos.html">Términos y Condiciones</a>
    </footer>

    <script src="../../js/editpasS.js"></script>
    <script src="../../js/btn_guardar_perfil.js"></script>

</body>
</html>
