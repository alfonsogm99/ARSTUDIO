<?php
require_once './bootstrap.php';
require_once './auth.php';

$errores = [];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_horario'])) {

    $user_id = $_SESSION['user_id'];

    $barbero_row = $conn->query("SELECT id_barbero FROM barbero WHERE id_usuario = $user_id")->fetch_assoc();

    if (!$barbero_row) {
        $errores[] = "No se encontró barbero para este usuario.";
    } else {

        $id_barbero = (int)$barbero_row['id_barbero'];
        $nombre_horario = trim($_POST['nombre_horario'] ?? '');
        if ($nombre_horario === '') {
            $errores[] = "Debes ingresar un nombre para el nuevo horario.";
        } else {

            $horas_json = $_POST['horas'] ?? '';
            $horas = [];
            $modo = $_POST['modo_horario'] ?? 'new';
            $id_general_editando = (int)($_POST['id_general_editando'] ?? 0);

            $sql_general = "
                INSERT INTO horario_general
                (nombre, id_barbero)
                VALUES (?, ?)
            ";

            $stmt = $conn->prepare($sql_general);
            $stmt->bind_param("si", $nombre_horario, $id_barbero);

            if (!$stmt->execute()) {
                $errores[] = "Error guardando horario general: " . $stmt->error;
            } else {
                $id_general = $stmt->insert_id;
            }

            if ($horas_json && is_string($horas_json)) {
                $horas = json_decode($horas_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errores[] = "Error al interpretar las horas: " . json_last_error_msg();
                    $horas = [];
                }
            }

            $dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

            foreach ($dias as $dia) {
                $h = $horas[$dia] ?? [];
                
                $inicio = !empty($h['inicio']) ? $h['inicio'] : '00:00:00';
                $fin    = !empty($h['fin']) ? $h['fin'] : '00:00:00';
                $pi     = !empty($h['pausa_inicio']) ? $h['pausa_inicio'] : '00:00:00';
                $pf     = !empty($h['pausa_fin']) ? $h['pausa_fin'] : '00:00:00';

                $sql = "
                    INSERT INTO horario_barbero
                    (
                        id_barbero,
                        id_general,
                        nombre,
                        dia_semana,
                        hora_inicio,
                        hora_fin,
                        pausa_inicio,
                        pausa_fin
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = $conn->prepare($sql);

                $stmt->bind_param(
                    "iissssss",
                    $id_barbero,
                    $id_general,
                    $nombre_horario,
                    $dia,
                    $inicio,
                    $fin,
                    $pi,
                    $pf
                );

                if (!$stmt->execute()) {
                    $errores[] = "Error guardando $dia: " . $stmt->error;
                }

            }

            // Desactivar todos los horarios del barbero
            $stmt = $conn->prepare("
                UPDATE horario_general
                SET activo = 0
                WHERE id_barbero = ?
            ");

            $stmt->bind_param(
                "i",
                $id_barbero
            );

            $stmt->execute();

            error_log("ACTIVANDO HORARIO: ".$id_general." BARBERO: ".$id_barbero);

            // Activar solamente el horario actual
            $stmt = $conn->prepare("
                UPDATE horario_general
                SET activo = 1
                WHERE id_general = ?
                AND id_barbero = ?
            ");

            $stmt->bind_param(
                "ii",
                $id_general,
                $id_barbero
            );

            $stmt->execute();


            if (empty($errores)) {
                $mensaje = "Horario creado correctamente.";
            }
        }
    }
}

$_SESSION['mensaje_horario'] = $mensaje;
$_SESSION['errores_horario'] = $errores;

header("Location: ./horario.php");
exit;