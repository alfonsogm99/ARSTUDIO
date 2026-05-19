<?php
require_once '../admin/bootstrap.php';
require_once '../admin/auth.php';

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
            if ($horas_json && is_string($horas_json)) {
                $horas = json_decode($horas_json, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errores[] = "Error al interpretar las horas: " . json_last_error_msg();
                    $horas = [];
                }
            }

            $sql_general = "INSERT INTO horario_general (nombre, id_barbero)
                            VALUES ('".$conn->real_escape_string($nombre_horario)."', $id_barbero)";

            if (!$conn->query($sql_general)) {
                $errores[] = "Error guardando horario general: " . $conn->error;
            } else {
                $id_general = $conn->insert_id; // Por si quieres vincular luego
            }

            $dias = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];

            foreach ($dias as $dia) {
                $h = $horas[$dia] ?? [];
                $inicio = $h['inicio'] ?? '09:00';
                $fin    = $h['fin'] ?? '17:00';
                $pi     = $h['pausa_inicio'] ?? null;
                $pf     = $h['pausa_fin'] ?? null;

                $conn->query("
                    UPDATE horario_barbero
                    SET activo = 0
                    WHERE id_barbero = $id_barbero
                ");

                $sql = "INSERT INTO horario_barbero 
                        (id_barbero, id_general, nombre, dia_semana, hora_inicio, hora_fin, pausa_inicio, pausa_fin)
                        VALUES (
                            $id_barbero, 
                            $id_general,
                            '".$conn->real_escape_string($nombre_horario)."', 
                            '$dia', 
                            '$inicio', 
                            '$fin', 
                            ".($pi?"'$pi'":"NULL").", 
                            ".($pf?"'$pf'":"NULL")."
                        )";

                if (!$conn->query($sql)) {
                    $errores[] = "Error guardando horario ($dia): " . $conn->error;
                }
            }

            if (empty($errores)) {
                $mensaje = "Horario guardado correctamente.";
            }
        }
    }
}

$_SESSION['mensaje_horario'] = $mensaje;
$_SESSION['errores_horario'] = $errores;

header("Location: index.php");
exit;