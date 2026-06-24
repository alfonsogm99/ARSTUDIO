<?php

require_once './bootstrap.php';
require_once './auth.php';

$tabla = $_GET['tabla'];
$id = $_GET['id'] ?? null;

// Columnas
$columns = [];
$enums = [];
$res = $conn->query("SHOW COLUMNS FROM $tabla");
$pk = '';

while ($row = $res->fetch_assoc()) {
    if ($row['Extra'] == 'auto_increment') $pk = $row['Field'];
    $columns[$row['Field']] = $row['Type'];

    if(preg_match("/enum\((.*)\)/",$row['Type'],$matches)) {
        $enums[$row['Field']] = array_map('trim', str_getcsv($matches[1], ',', "'", "\\"));
    }
}

// Columnas extra
$barbero_cols = [];
$cliente_cols = [];
$campos_usuario = array_keys($columns);

$res = $conn->query("SHOW COLUMNS FROM barbero");
while ($r = $res->fetch_assoc()) {
    if($r['Field']!='id_barbero' && $r['Field']!='id_usuario' && !in_array($r['Field'],$campos_usuario)){
        $barbero_cols[] = $r['Field'];
    }
}

$res = $conn->query("SHOW COLUMNS FROM cliente");
while ($r = $res->fetch_assoc()) {
    if($r['Field']!='id_cliente' && $r['Field']!='id_usuario' && !in_array($r['Field'],$campos_usuario)){
        $cliente_cols[] = $r['Field'];
    }
}

// Datos existentes
$data = [];
$extra_data = [];
if($id){
    $data = $conn->query("SELECT * FROM $tabla WHERE $pk='$id'")->fetch_assoc();
    if(isset($data['rol'])){
        if($data['rol']=='barbero') $extra_data = $conn->query("SELECT * FROM barbero WHERE id_usuario=".$data[$pk])->fetch_assoc() ?? [];
        if($data['rol']=='cliente') $extra_data = $conn->query("SELECT * FROM cliente WHERE id_usuario=".$data[$pk])->fetch_assoc() ?? [];
    }
}

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD']=='POST') {

    if(!$id && $tabla=='usuario') {
        $password = $_POST['password'] ?? '';
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}$/';
        if(!preg_match($pattern, $password)) die("Contraseña insegura");
        $_POST['password'] = password_hash($password, PASSWORD_DEFAULT);
        $_POST['fecha_creacion'] = date('Y-m-d H:i:s');
    }

    $sets=[]; $cols=[]; $vals=[];

    foreach($columns as $col=>$type){
        if($col==$pk || $col=='id_usuario' || $col=='fecha_creacion') continue;
        $val = $_POST[$col] ?? '';
        //if($col=='dni'){
        //    if(!preg_match('/^\d{9}[A-Z]$/i',$val)) die("DNI inválido (9 números + 1 letra)");
        //}
        if(strpos($type,'tinyint(1)')!==false) $val = isset($_POST[$col]) ? 1 : 0;

        if($id) $sets[]="$col='".$conn->real_escape_string($val)."'";
        else{
            $cols[]=$col;
            $vals[]="'".$conn->real_escape_string($val)."'";
        }
    }

    $sql = $id ? "UPDATE $tabla SET ".implode(',',$sets)." WHERE $pk='$id'" 
               : "INSERT INTO $tabla (".implode(',',$cols).") VALUES (".implode(',',$vals).")";

    
    $conn->begin_transaction();

    $usuario = $_SESSION['user_name'] ?? $_SESSION['usuario'] ?? 'system';
    $ip = $_SERVER['REMOTE_ADDR'];

    $conn->query("SET @usuario_actual = '" . $conn->real_escape_string($usuario) . "'");
    $conn->query("SET @ip_actual = '" . $conn->real_escape_string($ip) . "'");  


    if($conn->query($sql)){
        $new_user_id = $id ? $id : $conn->insert_id;
        $rol_usuario = $_POST['rol'] ?? $data['rol'] ?? '';

        // 👇 Actualizar o insertar datos extra
        if($rol_usuario=='cliente'){
            $campos = $cliente_cols;
            if($id){
                $sets_extra=[];
                foreach($campos as $c){
                    $v = $_POST[$c] ?? '';
                    $sets_extra[]="$c='".$conn->real_escape_string($v)."'";
                }
                $sql_extra = "UPDATE cliente SET ".implode(',',$sets_extra)." WHERE id_usuario=$new_user_id";
            } else {
                $valores=[];
                foreach($campos as $c) $valores[]="'".$conn->real_escape_string($_POST[$c] ?? '')."'";
                $sql_extra = "INSERT INTO cliente (id_usuario,".implode(',',$campos).") VALUES ($new_user_id,".implode(',',$valores).")";
            }
            if(!$conn->query($sql_extra)) die("Error cliente: ".$conn->error);
        }

        if($rol_usuario=='barbero'){
            $campos = $barbero_cols;
            if($id){
                $sets_extra=[];
                foreach($campos as $c){
                    $v = $_POST[$c] ?? '';
                    $sets_extra[]="$c='".$conn->real_escape_string($v)."'";
                }
                $sql_extra = "UPDATE barbero SET ".implode(',',$sets_extra)." WHERE id_usuario=$new_user_id";
            } else {
                $valores=[];
                foreach($campos as $c) $valores[]="'".$conn->real_escape_string($_POST[$c] ?? '')."'";
                $sql_extra = "INSERT INTO barbero (id_usuario,".implode(',',$campos).") VALUES ($new_user_id,".implode(',',$valores).")";
            }
            if(!$conn->query($sql_extra)) die("Error barbero: ".$conn->error);
        }

        $conn->commit();

        header("Location: admin.php?tabla=$tabla");
        exit;

    } else {
        $conn->rollback();
        die("Error SQL: " . $conn->error);
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
<title>ARSTUDIO · sysadmin</title>
</head>
<body id="admin_form">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<h1><?php echo $id ? "Editar" : "Añadir"; ?> - <?php echo $tabla; ?></h1>
 
<div class="perfil_wrapper">
<form method="post" class="perfil_box">
<?php foreach($columns as $col=>$type):
if($col==$pk || $col=='id_usuario' || $col=='fecha_creacion') continue;
$value = $data[$col] ?? '';

if($col=='password'): ?>
<label for="password">password:</label>
<div class="input-wrapper">
    <input type="password" name="password" id="password"
           pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,}"
           title="Mínimo 8 caracteres, mayúscula, minúscula, número y símbolo"
           <?php echo $id ? '' : 'required'; ?>>
    <i class="fa-solid fa-eye toggle-eye" onclick="togglePassword('password', this)"></i>
</div>
<?php elseif(isset($enums[$col])): ?>
<label><?php echo $col; ?>:</label>
<select name="<?php echo $col; ?>" id="tipo_usuario" onchange="renderExtra()">
<?php foreach($enums[$col] as $opt): ?>
<option value="<?php echo $opt; ?>" <?php echo ($value==$opt)?'selected':''; ?>><?php echo $opt; ?></option>
<?php endforeach; ?>
</select>
<?php elseif(strpos($type,'tinyint(1)')!==false): ?>
<label><?php echo $col; ?>:
<input type="checkbox" name="<?php echo $col; ?>" <?php echo ($value? 'checked':'' ); ?>>
</label>
<?php else: ?>
<label><?php echo $col; ?>:
<input type="text" name="<?php echo $col; ?>" value="<?php echo htmlspecialchars($value); ?>">
</label>
<?php endif; endforeach; ?>

<!-- CAMPOS DINÁMICOS -->
<div id="extra_fields">
<?php
$tipo_extra = $data['rol'] ?? '';
$campos_extra = [];
if($tipo_extra=='barbero') $campos_extra = $barbero_cols;
if($tipo_extra=='cliente') $campos_extra = $cliente_cols;
foreach($campos_extra as $c):
    $val = $extra_data[$c] ?? '';
?>
<label><?php echo $c; ?>:
    <input type="text" name="<?php echo $c; ?>" value="<?php echo htmlspecialchars($val); ?>">
</label>
<?php endforeach; ?>
</div>

<button type="submit">Guardar</button>
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
const barbero = <?php echo json_encode($barbero_cols); ?>;
const cliente = <?php echo json_encode($cliente_cols); ?>;
const extraValues = <?php echo json_encode($extra_data); ?>;

function renderExtra(){
    let tipo = document.getElementById("tipo_usuario")?.value;
    let container = document.getElementById("extra_fields");
    container.innerHTML = "";
    let campos = [];
    if(tipo==='barbero') campos=barbero;
    if(tipo==='cliente') campos=cliente;
    campos.forEach(c=>{
        let val = extraValues[c] ?? '';
        container.innerHTML += `<label>${c}: <input type="text" name="${c}" value="${val.replace(/"/g,'&quot;')}"></label>`;
    });
}

function togglePassword(id, icon){
    const input = document.getElementById(id);
    if(input.type==="password"){
        input.type="text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type="password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

</body>
</html>