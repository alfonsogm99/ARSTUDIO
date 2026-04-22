<?php
require_once './bootstrap.php';
require_once './auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
<title>ARSTUDIO · Registro</title>
</head>
<body id="registro" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

<div id="top-fixed">
<?php include '../includes/navbar.php'; ?>
</div>

<h2>Registro</h2>

<div id="logs"></div>

<script>
function getAccionTexto(accion) {
    switch (accion) {
        case 'INSERT': return 'ha creado';
        case 'UPDATE': return 'ha modificado';
        case 'DELETE': return 'ha eliminado';
        default: return 'ha hecho';
    }
}

function renderDatos(tabla, datos) {

    if (!datos) return '-';

    switch (tabla) {

        case 'cliente':
            return `
Nombre: ${datos.nombre ?? '-'}
DNI: ${datos.dni ?? '-'}
Email: ${datos.email ?? '-'}
Teléfono: ${datos.telefono ?? '-'}
            `;

        case 'cita':
            return `
Cliente: ${datos.cliente_nombre ?? '-'}
Barbero: ${datos.barbero_nombre ?? '-'}
Fecha: ${datos.fecha ?? '-'}
Hora: ${datos.hora ?? '-'}
Estado: ${datos.estado ?? '-'}
            `;

        default:
            if (typeof datos === 'object') {
                return Object.entries(datos)
                    .map(([key, val]) => `${key}: ${val ?? '-'}`)
                    .join('\n');
            }
            return 'Datos no estructurados';
    }
}

function getEntidadTexto(tabla) {
    const articulo = (tabla === 'cita') ? 'la' : 'el';
    return `${articulo} ${tabla ?? 'registro'}`;
}

function loadLogs() {
    fetch("get_logs.php")
    .then(res => res.json())
    .then(parsed => {

        let html = "";

        parsed.forEach(log => {

            let typeClass = log.accion.toLowerCase();
            let accionTexto = getAccionTexto(log.accion);

            html += `
                <div class="log ${typeClass}">
                    <b>${log.usuario ?? 'system'}</b>
                    ${accionTexto} <b>${getEntidadTexto(log.tabla)}</b>
                    (ID ${log.registro_id ?? '-'})

                    <div class="time">${log.fecha}</div>

                    <pre>
ANTES:
${renderDatos(log.tabla, log.datos_antes)}

DESPUÉS:
${renderDatos(log.tabla, log.datos_despues)}
                    </pre>
                </div>
            `;
        });

        document.getElementById("logs").innerHTML = html;
    })
    .catch(err => console.error("Fetch error:", err));
}

loadLogs();
setInterval(loadLogs, 3000);
</script>

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
        <a href="#">Política de Privacidad</a> |
        <a href="#">Política de Cookies</a> |
        <a href="#">Términos y Condiciones</a>
</footer>

</body>
</html>