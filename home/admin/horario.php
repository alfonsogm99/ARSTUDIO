<?php

require_once './bootstrap.php';
require_once './auth.php';

$data = json_decode(file_get_contents('./config.json'), true);

if (isset($_POST['theme'])) {
    $data['theme'] = $_POST['theme'];
    file_put_contents('./config.json', json_encode($data));
}

$user_id = $_SESSION['user_id'];
$rol = $_SESSION['user_type'];

if ($rol !== 'barbero') {
    header("Location: ../logout");
    exit;
}


$dias = [
    'lunes',
    'martes',
    'miercoles',
    'jueves',
    'viernes',
    'sabado',
    'domingo'
];


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


$vip = null;

if ($id_barbero) {
    $vip = $conn->query("
        SELECT id_reserva, fecha, hora_inicio, hora_fin, motivo
        FROM vip_horario
        WHERE id_barbero = $id_barbero
        ORDER BY fecha DESC
    ");
}



$mensaje = '';
$errores = [];
$input_errors = [];
$show_username_inputs = false;
$show_password_inputs = false;
$show_data_inputs = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && ($_POST['action'] ?? '') === 'set_horario_activo'
    ) {

        $id_general = $_POST['id_horario'] ?? null;

        if (!$id_general || $id_general === 'new') {
            header("Location: ./confpan.php");
            exit;
        }

        $id_general = (int)$id_general;

        $res = $conn->query("
            SELECT id_barbero 
            FROM barbero 
            WHERE id_usuario = $user_id
        ");

        $id_barbero = $res->fetch_assoc()['id_barbero'] ?? null;

        if (!$id_barbero) {
            header("Location: ./confpan.php");
            exit;
        }


        $conn->query("
            UPDATE horario_general 
            SET activo = 0 
            WHERE id_barbero = $id_barbero
        ");

        $conn->query("
            UPDATE horario_general 
            SET activo = 1 
            WHERE id_barbero = $id_barbero 
            AND id_general = $id_general
        ");

        header("Location: ./confpan.php");
        exit;
    }


    // --- CAMBIO DE HORARIO ASIGNADO ---
    if (
        ($_POST['action'] ?? '') === 'cambiar_horario'
        && $rol === 'barbero'
    ) {

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

                $sql_on = "UPDATE horario_general 
                        SET activo = 1 
                        WHERE id_barbero = $id_barbero 
                            AND id_general = $id_general";
                if ($conn->query($sql_on)) {
                    $_SESSION['mensaje_horario'] = "Horario actualizado correctamente.";
                    header("Location: ./horario.php");
                    exit;
                } else {
                    $_SESSION['errores_horario'] = "Error al actualizar el horario: " . $conn->error;
                    header("Location: ./horario.php");
                    exit;
                }

            } elseif ($id_general === 'new') {
                header("Location: nuevo_horario.php");
                exit;
            }
        }
    }

    if (isset($_POST['editar_horario'])) {

        $id_general = (int)$_POST['id_general_editando'];
        $nombre = $_POST['nombre_horario'];

        $stmt = $conn->prepare("
            UPDATE horario_general
            SET nombre = ?
            WHERE id_general = ?
            AND id_barbero = ?
        ");

        $stmt->bind_param(
            "sii",
            $nombre,
            $id_general,
            $id_barbero
        );

        $stmt->execute();

        foreach ($dias as $dia) {

            $inicio = $_POST['horarios'][$dia]['inicio'] ?: '00:00:00';
            $fin    = $_POST['horarios'][$dia]['fin'] ?: '00:00:00';
            $pi     = $_POST['horarios'][$dia]['pausa_inicio'] ?: null;
            $pf     = $_POST['horarios'][$dia]['pausa_fin'] ?: null;

            $stmt = $conn->prepare("
                UPDATE horario_barbero
                SET
                    hora_inicio = ?,
                    hora_fin = ?,
                    pausa_inicio = ?,
                    pausa_fin = ?
                WHERE
                    id_general = ?
                    AND id_barbero = ?
                    AND dia_semana = ?
            ");

            $stmt->bind_param(
                "sssssis",
                $inicio,
                $fin,
                $pi,
                $pf,
                $id_general,
                $id_barbero,
                $dia
            );

            $stmt->execute();
        }

    $_SESSION['mensaje_horario'] = "Horario actualizado correctamente.";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;

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

                    $_SESSION['mensaje_horario'] = " Horario eliminado correctamente.";
                    header("Location: ./horario.php");
                    exit;

                } else {

                    $_SESSION['errores_horario'] = "Error al eliminar horario: " . $conn->error;
                    header("Location: ./horario.php");
                    exit;

                }
            }
        }
    }


}


// =======================
// 🔹 AUSENCIAS
// =======================
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    ($_POST['action'] ?? '') === 'editar_ausencia'
){

    $id_barbero = $conn->query("
        SELECT id_barbero
        FROM barbero
        WHERE id_usuario = $user_id
    ")->fetch_assoc()['id_barbero'] ?? null;

    if (!$id_barbero) {
        die("No se pudo identificar el barbero");
    }

    $stmt = $conn->prepare("
        UPDATE bloqueo_horario
        SET
            fecha=?,
            hora_inicio=?,
            hora_fin=?,
            tipo=?,
            motivo=?
        WHERE id_bloqueo=?
          AND id_barbero=?
    ");

    $stmt->bind_param(
        "sssssii",
        $_POST['fecha'],
        $_POST['hora_inicio'],
        $_POST['hora_fin'],
        $_POST['tipo'],
        $_POST['motivo'],
        $_POST['id_bloqueo'],
        $id_barbero
    );

    $stmt->execute();

    header("Location: ./horario.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ausencia_delete') {

    $id_barbero = $conn->query("
        SELECT id_barbero
        FROM barbero
        WHERE id_usuario = $user_id
    ")->fetch_assoc()['id_barbero'] ?? null;

    $id_bloqueo = (int)($_POST['id_bloqueo'] ?? 0);

    if ($id_barbero && $id_bloqueo > 0) {

        $stmt = $conn->prepare("
            DELETE FROM bloqueo_horario
            WHERE id_bloqueo = ?
              AND id_barbero = ?
        ");

        $stmt->bind_param("ii", $id_bloqueo, $id_barbero);
        $stmt->execute();
    }

    header("Location: ./horario.php");
    exit;
}



// =======================
// 🔹 HORARIO VIP
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] ?? '') === 'vip_create') {

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

    header("Location: ./horario.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'vip_edit') {

    $id_barbero = $conn->query("
        SELECT id_barbero
        FROM barbero
        WHERE id_usuario = $user_id
    ")->fetch_assoc()['id_barbero'] ?? null;

    $id_reserva = (int)$_POST['id_reserva'];

    $stmt = $conn->prepare("
        UPDATE vip_horario
        SET fecha=?,
            hora_inicio=?,
            hora_fin=?,
            motivo=?
        WHERE id_reserva=? AND id_barbero=?
    ");

    $stmt->bind_param(
        "ssssii",
        $_POST['fecha'],
        $_POST['hora_inicio'],
        $_POST['hora_fin'],
        $_POST['motivo'],
        $id_reserva,
        $id_barbero
    );

    $stmt->execute();

    header("Location: ./horario.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'vip_delete') {

    $id_barbero = $conn->query("
        SELECT id_barbero
        FROM barbero
        WHERE id_usuario = $user_id
    ")->fetch_assoc()['id_barbero'] ?? null;

    $id_reserva = (int)$_POST['id_reserva'];

    $conn->query("
        DELETE FROM vip_horario
        WHERE id_reserva = $id_reserva
          AND id_barbero = $id_barbero
    ");

    header("Location: ./horario.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>ARSTUDIO · Horario</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="icon" type="image/x-icon" href="../../img/favicon.ico">
<link rel="stylesheet" href="../css/style.css">
</head>

<body id="horario" class="<?php echo $theme === 'light' ? 'light-mode' : ''; ?>">

    <div id="top-fixed">
    <?php include '../includes/navbar.php'; ?>
    </div>


    <h2 style="text-align:center;">Horario</h2>

    <?php

    $mensaje = $_SESSION['mensaje_horario'] ?? '';
    $errores = $_SESSION['errores_horario'] ?? [];

    unset($_SESSION['mensaje_horario']);
    unset($_SESSION['errores_horario']);

    if ($mensaje) {
        echo "<br/> <p style='color:green;text-align:center;'>".htmlspecialchars($mensaje)."</p>";
    }

    foreach ($errores as $err) {
        echo "<br/> <p style='color:red;text-align:center;'>".htmlspecialchars($err)."</p>";
    }

    ?>

    <div class="perfil_wrapper">

        <div class="horario_box">

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

            <h3>
                Horario activo

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
                    <br><span style="font-weight: normal; font-size: 0.9em; color: #aaa;">
                        <?= htmlspecialchars($nombre_horario_asignado) ?>
                    </span>
                <?php endif; ?>
            </h3>

            <?php if (!$nombre_horario_asignado): ?>
                <p>No hay un horario definido</p>

            <button type="button" onclick="toggleHorario()"> 
                Añadir 
            </button>

            <?php endif; ?>

            <?php if ($nombre_horario_asignado): ?>

                <button type="button" onclick="toggleHorario()"> 
                    Editar 
                </button>

            <?php endif; ?>



            <div id="panel_horario" style="display:none; margin-top:70px;">

                <h4>Semana</h4>

                <p id="horario_duracion" style="margin-top:5px; color:#555; font-size:0.9em;">

                <?php

                $horario_activo = null;

                foreach($horarios_array as $h){
                    if($h['activo']){
                        $horario_activo = $h;
                        break;
                    }
                }

                if($horario_activo){

                    foreach($horario_activo['dias'] as $dia){

                        echo "<div style='margin-bottom:4px;'>";

                        echo "<b>" . ucfirst($dia['dia_semana']) . "</b>: ";

                        $hora_inicio = substr($dia['hora_inicio'] ?? '', 0, 5);
                        $hora_fin = substr($dia['hora_fin'] ?? '', 0, 5);

                        echo ($hora_inicio === '00:00' ? '' : $hora_inicio);
                        echo " - ";
                        echo ($hora_fin === '00:00' ? '' : $hora_fin);

                        if(!empty($dia['pausa_inicio']) && !empty($dia['pausa_fin'])){

                            echo "<br/><span style='font-size:14px;'>⤷ Pausa: ";

                            $pausa_inicio = substr($dia['pausa_inicio'] ?? '', 0, 5);
                            $pausa_fin = substr($dia['pausa_fin'] ?? '', 0, 5);

                            echo ($pausa_inicio === '00:00' ? '' : $pausa_inicio);
                            echo " - ";
                            echo ($pausa_fin === '00:00' ? '' : $pausa_fin);

                            echo "</span>";
                        }

                        echo "</div>";
                    }
                }
                ?>

                </p>

                <br><hr style="width:10px; margin:auto; opacity:.5;"><br>

                <h4>Cambiar horario:</h4>

                <form method="POST" id="form_horario">

                    <?php $tiene_horario = !empty($horario_asignado_id); ?>

                    <input type="hidden" name="action" id="action" value="">
                    
                    <div class="selector-horario">
                        <select name="id_horario" id="select_horario">
                            <option value="new">-- Crear nuevo horario --</option>
                            <?php foreach($horarios_array as $h): ?>
                            <option value="<?= $h['id_general'] ?>" 
                            <?php if($h['id_general'] == $horario_asignado_id) echo 'selected'; ?>>
                            <?= htmlspecialchars($h['nombre_general']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit"
                            name="action"
                            value="cambiar_horario">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                    </div>

                        <div id="nuevo_horario" style="display:none;">

                        <input type="hidden" id="modo_horario" name="modo_horario" value="new">
                        <input type="hidden" id="id_general_editando" name="id_general_editando">

                        <p>
                            <label>Nombre del horario:</label><br>
                            <input type="text" id="nombre_horario" name="nombre_horario" placeholder="Nombre del horario">
                        </p>

                        <?php
                        foreach($dias as $dia):
                        ?>
                        <div class="dia-block">
                            <div class="dia-title"><?php echo ucfirst($dia); ?></div>
                            <div class="horario-grid">

                                <div class="bloque">
                                    <label for="inicio_<?php echo $dia; ?>">Inicio</label>
                                    <input type="time"
                                        id="inicio_<?php echo $dia; ?>"
                                        name="horarios[<?php echo $dia; ?>][inicio]">
                                </div>

                                <div class="bloque">
                                    <label for="fin_<?php echo $dia; ?>">Fin</label>
                                    <input type="time"
                                        id="fin_<?php echo $dia; ?>"
                                        name="horarios[<?php echo $dia; ?>][fin]">
                                </div>

                                <div class="bloque">
                                    <label for="pausa_inicio_<?php echo $dia; ?>">Pausa Inicio</label>
                                    <input type="time"
                                        id="pausa_inicio_<?php echo $dia; ?>"
                                        name="horarios[<?php echo $dia; ?>][pausa_inicio]">
                                </div>

                                <div class="bloque">
                                    <label for="pausa_fin_<?php echo $dia; ?>">Pausa Fin</label>
                                    <input type="time"
                                        id="pausa_fin_<?php echo $dia; ?>"
                                        name="horarios[<?php echo $dia; ?>][pausa_fin]">
                                </div>

                            </div>
                        </div>
                        <?php endforeach; ?>

                        <button type="button" id="guardar_horario" onclick="guardarHorario()" style="display:none;">
                            Guardar
                        </button>
                    </div>

                    <div class="btn_horario_form">

                        <button type="button" id="btn_editar_horario" onclick="editarHorario(document.getElementById('select_horario').value)">
                            Editar
                        </button>
                        
                        <button type="submit"
                                name="editar_horario"
                                value="1"
                                id="btn_guardar_cambios"
                                style="display:none">
                            Guardar
                        </button>

                        <button type="submit" name="eliminar_horario" value="1" id="btn_eliminar_horario" 
                        onclick="return confirm('¿Eliminar este horario?');" 
                        style="background:#171717;color:white;">
                            Eliminar
                        </button>
                    </div>

                </form>

            </div>

        </div>

    </div>

    <?php endif; ?>

    <div id="bloque_ausencias">

        <button type="button" id="btn_ausencias" onclick="toggleAusencias()">
            Ausencias
        </button>

        <button type="button" id="btn_vip" onclick="toggleVIP()">
            VIP
        </button>

        <div id="panel_vip" style="display:none; margin-top:15px;">

            <br>
            <h4>Bloqueos VIP existentes</h4>

            <?php if ($vip && $vip->num_rows > 0): ?>

                <div class="ausencias-lista">

                    <?php while($v = $vip->fetch_assoc()): ?>

                        <div class="ausencia-item">

                            <div class="ausencia-fecha">
                                <b><?= htmlspecialchars($v['fecha']) ?></b>
                            </div>

                            <div class="ausencia-horas">
                                <?= substr($v['hora_inicio'],0,5) ?> - <?= substr($v['hora_fin'],0,5) ?>
                            </div>

                            <?php if (!empty($v['motivo'])): ?>
                                <div class="ausencia-motivo">
                                    <?= htmlspecialchars($v['motivo']) ?>
                                </div>
                            <?php endif; ?>

                            <br>

                            <div class="ausencia-acciones">

                                <button type="button"
                                    onclick="editarVIP(
                                        <?= $v['id_reserva'] ?>,
                                        '<?= $v['fecha'] ?>',
                                        '<?= substr($v['hora_inicio'],0,5) ?>',
                                        '<?= substr($v['hora_fin'],0,5) ?>',
                                        '<?= htmlspecialchars($v['motivo'], ENT_QUOTES) ?>'
                                    )">
                                    Editar
                                </button>

                                <form method="POST">
                                    <input type="hidden" name="action" value="vip_delete">
                                    <input type="hidden" name="id_reserva" value="<?= $v['id_reserva'] ?>">
                                    <button type="submit"
                                        onclick="return confirm('¿Eliminar este bloqueo VIP?');">
                                        Eliminar
                                    </button>
                                </form>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>
                <p>Aún no hay bloqueos</p>
            <?php endif; ?>

            <br>

            <form method="POST" id="formVIP">

                <input type="hidden" name="action" id="vip_action" value="vip_create">
                <input type="hidden" name="id_reserva" id="id_reserva">

                <input type="date" name="fecha" id="vip_fecha" required>

                <h5>Inicio</h5>
                <input type="time" name="hora_inicio" id="vip_inicio" required>

                <h5>Fin</h5>
                <input type="time" name="hora_fin" id="vip_fin" required>

                <input type="text" name="motivo" id="vip_motivo" placeholder="Motivo">

                <button type="submit" id="btn_vip_guardar">Guardar</button>

            </form>

        </div>

        <div id="panel_ausencias" style="display:none; margin-top:15px;">

            <br>
                        
            <h4>Ausencias existentes</h4>

            <?php if (!$id_barbero): ?>

                <p>Error: no se pudo identificar el barbero</p>

            <?php elseif ($ausencias && $ausencias->num_rows > 0): ?>

                <div class="ausencias-lista">

                    <?php while($a = $ausencias->fetch_assoc()): ?>

                        <div class="ausencia-item">

                            <div class="ausencia-fecha">
                                <b><?= htmlspecialchars($a['fecha']) ?></b>
                            </div>

                            <div class="ausencia-horas">
                                <?= htmlspecialchars($a['hora_inicio']) ?> - <?= htmlspecialchars($a['hora_fin']) ?>
                            </div>

                            <div class="ausencia-tipo">
                                <?= htmlspecialchars($a['tipo']) ?>
                            </div>

                            <?php if (!empty($a['motivo'])): ?>
                                <div class="ausencia-motivo">
                                    <?= htmlspecialchars($a['motivo']) ?>
                                </div>
                            <?php endif; ?>

                            <br>

                            <div class="ausencia-acciones">

                                <button type="button"
                                    onclick="editarAusencia(
                                        <?= $a['id_bloqueo'] ?>,
                                        '<?= $a['fecha'] ?>',
                                        '<?= substr($a['hora_inicio'],0,5) ?>',
                                        '<?= substr($a['hora_fin'],0,5) ?>',
                                        '<?= $a['tipo'] ?>',
                                        '<?= htmlspecialchars($a['motivo'], ENT_QUOTES) ?>'
                                    )">
                                    Editar
                                </button>

                                <form method="POST">
                                    <input type="hidden" name="action" value="ausencia_delete">
                                    <input type="hidden" name="id_bloqueo" value="<?= $a['id_bloqueo'] ?>">
                                    <button type="submit"
                                        onclick="return confirm('¿Eliminar esta ausencia?');">
                                        Eliminar
                                    </button>
                                </form>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <p>Aún no hay ausencias</p>

            <?php endif; ?>

            <br>

            <form method="POST"
                action="./bloquear_horario.php"
                id="formAusencia">

                <input type="hidden"
                    name="modo"
                    id="modo"
                    value="crear">

                <input type="hidden"
                    name="id_bloqueo"
                    id="id_bloqueo">

                <input type="hidden"
                    name="id_barbero"
                    value="<?= $id_barbero ?>">

                <input type="hidden"
                    name="id_horario"
                    value="<?= $horario_asignado_id ?>">

                <input type="date"
                    name="fecha"
                    id="fecha"
                    required>

                <input type="time"
                    name="hora_inicio"
                    id="hora_inicio"
                    required>

                <input type="time"
                    name="hora_fin"
                    id="hora_fin"
                    required>

                <select name="tipo" id="tipo">
                    <option value="descanso">Descanso</option>
                    <option value="ausencia">Ausencia</option>
                    <option value="vacaciones">Vacaciones</option>
                    <option value="formacion">Formación</option>
                    <option value="bloqueo_manual">Manual</option>
                </select>

                <input type="text"
                    name="observaciones"
                    id="motivo"
                    placeholder="Observaciones">

                <button type="submit"
                        id="btn_guardar">
                    Guardar
                </button>

            </form>

        </div>

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

    <script src="../../js/toggleHorario.js"></script>

    <script>
        const horariosData = <?= json_encode($horarios_array) ?>;
    </script>

    <script src="../../js/editarHorario.js"></script>

</body>
</html>