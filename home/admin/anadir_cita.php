<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['barbero','admin'])){
    header("Location: ../login");
    exit;
}

$data = json_decode(file_get_contents('config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('config.json', json_encode($data));
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$hora_inicial = $_GET['hora'] ?? '08:00:00';
$fecha = $_GET['fecha'] ?? date('Y-m-d');

$id_cliente_prefill = isset($_GET['id_cliente']) ? intval($_GET['id_cliente']) : 0;

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $hora = $_POST['hora'];
    $id_cliente = intval($_POST['id_cliente']);
    $duracion = intval($_POST['duracion']);
    $estado = 'pendiente';
    $id_barbero = ($user_type==='barbero') ? $user_id : intval($_POST['id_barbero']);

    if($id_cliente && $hora && $duracion>0){
        $stmt = $conn->prepare("INSERT INTO cita (id_cliente,id_barbero,fecha,hora,duracion_total,estado) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("iissis",$id_cliente,$id_barbero,$fecha,$hora,$duracion,$estado);
        if($stmt->execute()){
            $success = "Cita creada correctamente!";
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Error al crear la cita: ".$conn->error;
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}

$barberos = [];
if($user_type==='admin'){
    $res = $conn->query("SELECT * FROM barbero ORDER BY nombre ASC");
    while($b = $res->fetch_assoc()) $barberos[] = $b;
}

$nombre_prefill = '';

if($id_cliente_prefill){
    $stmt = $conn->prepare("SELECT nombre FROM cliente WHERE id_cliente=?");
    $stmt->bind_param("i", $id_cliente_prefill);
    $stmt->execute();
    $res = $stmt->get_result();

    if($row = $res->fetch_assoc()){
        $nombre_prefill = $row['nombre'];
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
<title>ARSTUDIO · Nueva Cita</title>
</head>
<body class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<?php include '../includes/navbar.php'; ?>

<h2>Añadir Cita</h2>

<?php if($error) echo "<p class='error'>$error</p>"; ?>
<?php if($success) echo "<p class='success'>$success</p>"; ?>

<div class="perfil_wrapper">
<form method="POST" autocomplete="off" id="cita_form" class="perfil_box">

    <label>Cliente:</label>
    <input type="hidden" name="id_cliente" id="id_cliente" value="<?php echo $id_cliente_prefill; ?>">
    <input type="text"
       id="cliente_input"
       value="<?php echo htmlspecialchars($nombre_prefill); ?>"
       placeholder="Escribe nombre o DNI"
       autocomplete="off"
       required>

    <div id="cliente_suggestions"></div>

    <?php if($user_type==='admin'): ?>
        <label>Barbero:
            <select name="id_barbero" required>
                <option value="">-- Selecciona barbero --</option>
                <?php foreach($barberos as $b): ?>
                    <option value="<?php echo $b['id_barbero']; ?>"><?php echo htmlspecialchars($b['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php endif; ?>

    <label>Hora:
        <input type="time" name="hora" value="<?php echo substr($hora_inicial,0,5); ?>" required>
    </label>

    <label>Duración (minutos):
        <select name="duracion" required>
            <option value="30" selected>30 min</option>
            <option value="60">60 min (1h)</option>
        </select>
    </label>

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

<footer class="version">
    <p>v.<?= htmlspecialchars($version) ?></p>
</footer>

<footer class="copyright">
    <p>© 2026 alfonsogm</p>
</footer>


<script>
const clienteInput = document.getElementById('cliente_input');
const idClienteInput = document.getElementById('id_cliente');
const suggestionsContainer = document.getElementById('cliente_suggestions');

let debounceTimeout;

// Ajustar posición del contenedor
function ajustarPosicion(){
    const rect = clienteInput.getBoundingClientRect();
    suggestionsContainer.style.top = (rect.bottom + window.scrollY) + 'px';
    suggestionsContainer.style.left = (rect.left + window.scrollX) + 'px';
    suggestionsContainer.style.width = rect.width + 'px';
}

// Mostrar / ocultar lista
function mostrarLista() {
    suggestionsContainer.style.display = 'block';
}

function ocultarLista() {
    suggestionsContainer.style.display = 'none';
    suggestionsContainer.innerHTML = '';
}

clienteInput.addEventListener('input', function(){
    ocultarLista();

    const query = this.value.trim();
    if(query.length < 2) return;

    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        ajustarPosicion();
        fetch('buscar_cliente.php?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                suggestionsContainer.innerHTML = '';
                if(data.length === 0){
                    const div = document.createElement('div');
                    div.style.padding = '8px';

                    div.innerHTML = `
                        <p style="margin-bottom:5px;">No se encontró ningún cliente</p>
                        <button type="button" id="crear_cliente_btn">Nuevo usuario</button>
                    `;

                    suggestionsContainer.appendChild(div);

                    document.getElementById('crear_cliente_btn').addEventListener('click', () => {
                        const nombre = encodeURIComponent(clienteInput.value.trim());
                        window.location.href = 'nuevo_usuario.php?nombre=' + nombre;
                    });

                    mostrarLista();
                    return;
                }

                data.forEach(c => {
                    const div = document.createElement('div');
                    div.textContent = c.nombre + " (DNI: " + c.dni + ")"; // se ve el DNI en la lista
                    div.dataset.id = c.id_cliente;
                    div.addEventListener('click', () => {
                        clienteInput.value = c.nombre; // SOLO el nombre en el input
                        idClienteInput.value = c.id_cliente; // el hidden guarda el ID real
                        ocultarLista();
                    });
                    suggestionsContainer.appendChild(div);
                });
                mostrarLista();
            });
    }, 300);
});

// Evitar submit si no hay cliente seleccionado
document.querySelector('form').addEventListener('submit', function(e){
    if(!idClienteInput.value){
        e.preventDefault();
        alert('Selecciona un cliente válido de la lista.');
        clienteInput.focus();
    }
});

// Cerrar lista si se hace clic fuera
document.addEventListener('click', function(e){
    if(!clienteInput.contains(e.target) && !suggestionsContainer.contains(e.target)){
        ocultarLista();
    }
});
</script>
<script src="../../js/lightmode.js"></script>
</body>
</html>
