<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_type']) || !in_array($_SESSION['user_type'], ['admin','barbero'])) {
    header("Location: ../login");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_cliente = intval($_POST['id_cliente']);

    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $prefijo_pais = trim($_POST['prefijo_pais']);
    $telefono = trim($_POST['telefono']);
    $observaciones = trim($_POST['observaciones']);

    $stmt = $conn->prepare("
        UPDATE cliente
        SET nombre=?,
            email=?,
            prefijo_pais=?,
            telefono=?,
            observaciones=?
        WHERE id_cliente=?
    ");

    $stmt->bind_param(
        "sssssi",
        $nombre,
        $email,
        $prefijo_pais,
        $telefono,
        $observaciones,
        $id_cliente
    );

    $stmt->execute();

    header("Location: clientes.php");
    exit;
}

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $conn->prepare("
        DELETE FROM cliente
        WHERE id_cliente=?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: clientes.php");
    exit;
}



$edit = null;

if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    $stmt = $conn->prepare("
        SELECT *
        FROM cliente
        WHERE id_cliente=?
    ");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $edit = $stmt->get_result()->fetch_assoc();
}


$clientes = $conn->query("
    SELECT
        c.*,
        u.username,
        u.rol,
        (
            SELECT COUNT(*)
            FROM cita ci
            WHERE ci.id_cliente = c.id_cliente
        ) AS total_citas
    FROM cliente c
    LEFT JOIN usuario u
        ON c.id_usuario = u.id_usuario
    ORDER BY c.id_cliente DESC
");

$tieneClientes = $clientes->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">

<title>ARSTUDIO · Listado de clientes</title>
</head>
<body id="users" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
    <?php include '../includes/navbar.php'; ?>
</div>

<h2 style="text-align:center;">Clientes</h2>

<div class="contabilidad_box">

<?php if ($tieneClientes): ?>

<table class="tableau">

<thead>
<tr>
    <th>ID</th>
    <th>Nombre</th>
    <th>Email</th>
    <th>Teléfono</th>
    <th>Usuario asociado</th>
    <th>Acciones</th>
</tr>
</thead>

<tbody>

<?php while($c = $clientes->fetch_assoc()): ?>

<tr>

    <td>#<?= $c['id_cliente'] ?></td>

    <td>
        <?php if (!empty($c['nombre'])): ?>

            <?= htmlspecialchars($c['nombre'] ?? '-') ?>


        <?php else: ?>

            <span style="color:#999;">
                -
            </span> 

        <?php endif; ?>
    </td>

    <td>
        <?= htmlspecialchars($c['email'] ?? '-') ?>
    </td>

    <td>

        <?php if (!empty($c['telefono'])): ?>

            <?= htmlspecialchars(trim(($c['prefijo_pais'] ?? '') . ' ' . $c['telefono'])) ?>


        <?php else: ?>

            <span style="color:#999;">
                -
            </span> 

        <?php endif; ?>

    </td>

    <td>

        <?php if ($c['username']): ?>

            <strong><?= htmlspecialchars($c['username']) ?></strong>
            <br>
            <small>(<?= htmlspecialchars($c['rol']) ?>)</small>

        <?php else: ?>

            <span style="color:#999;">
                -
            </span>

        <?php endif; ?>

    </td>

    <td class="actions">

        <a href="javascript:void(0)"
        onclick='toggleForm_2({
            id_cliente: <?= $c["id_cliente"] ?>,
            nombre: <?= json_encode($c["nombre"]) ?>,
            email: <?= json_encode($c["email"]) ?>,
            prefijo_pais: <?= json_encode($c["prefijo_pais"]) ?>,
            telefono: <?= json_encode($c["telefono"]) ?>,
            observaciones: <?= json_encode($c["observaciones"]) ?>
        })'>
            <b>Editar</b>
        </a>

        <a class="delete"
        href="?delete=<?= $c['id_cliente'] ?>"
        onclick="return confirm('<?= $c['total_citas'] > 0
            ? '⚠️ Este cliente tiene '.$c['total_citas'].' cita(s). ¿Seguro que deseas eliminarlo?'
            : '¿Eliminar cliente?' ?>')">

            <b>Eliminar</b>

        </a>

    </td>

</tr>

<?php endwhile; ?>

</tbody>

</table>

<?php else: ?>

<p class="empty-state">
    No existen clientes registrados.
</p>

<?php endif; ?>

</div>

<br>

<div id="formCliente" style="display:none;">

<div class="perfil_wrapper">

<form method="POST" class="perfil_box">

<input type="hidden" name="id_cliente">

<label>Nombre</label>
<input type="text" name="nombre" required>

<label>Email</label>
<input type="email" name="email">

<label>Prefijo</label>
<input class="readonly-input" type="text" name="prefijo_pais">

<label>Teléfono</label>
<input type="text" name="telefono">

<label>Observaciones</label>
<textarea name="observaciones"></textarea>

<button type="submit">
    Actualizar
</button>

</form>

</div>
</div>

<script src="../../js/toggleForm.js"></script>

</body>
</html>