<?php

require_once __DIR__ . '/../admin/bootstrap.php';

$user = $_SESSION['user_name'];
$rol = $_SESSION['user_type'];

?>

<div id="control_panel_bar">

    <div id="user_info">
        <a href="<?php echo BASE_URL; ?>/perfil" id="username"> <i class="fa-solid fa-user"></i> <?php echo $user;?></a>
        <a href="<?php echo BASE_URL; ?>/logout">Cerrar sesión</a>
    </div> 

    <br/><br/>

    <div id="menu_bar">
        <i id="lightmode" class="far fa-lightbulb"></i>
        
        <?php if ($rol === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>/admin/registro.php"><i class="fa-solid fa-clock"></i></a>
            <a href="<?php echo BASE_URL; ?>/admin/admin.php"><i class="fa-solid fa-cogs"></i></a>
            <a href="<?php echo BASE_URL; ?>"><i class="fa-solid fa-home"></i></a>
        <?php endif; ?>

        <?php if ($rol === 'barbero'): ?>

                <?php
                
                    $idUsuario = $_SESSION['user_id'] ?? null;

                    $hayNotificaciones = false;

                    if ($idUsuario) {
                        $stmt = $conn->prepare("
                            SELECT 1
                            FROM usuario u
                            LEFT JOIN cliente c ON c.id_usuario = u.id_usuario
                            WHERE u.rol = 'cliente'
                            AND c.id_cliente IS NULL
                            LIMIT 1
                        ");

                        $stmt->execute();
                        $stmt->store_result();

                        $hayNotificaciones = $stmt->num_rows > 0;

                        $stmt->close();
                    }




                    $usuariosPendientes = [];

                    $stmt = $conn->prepare("
                        SELECT 
                            u.id_usuario,
                            u.username,
                            u.email,
                            u.phone,
                            u.fecha_creacion
                        FROM usuario u
                        LEFT JOIN cliente c ON c.id_usuario = u.id_usuario
                        WHERE u.rol = 'cliente'
                        AND c.id_cliente IS NULL
                        ORDER BY u.fecha_creacion DESC
                    ");

                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $usuariosPendientes[] = $row;
                    }

                    $stmt->close();

                    //

                    $cancelacionesPendientes = [];

                    $stmt = $conn->prepare("
                        SELECT
                            c.id_cita,
                            cl.nombre,
                            c.fecha,
                            c.hora
                        FROM cita c
                        INNER JOIN cliente cl ON cl.id_cliente = c.id_cliente
                        WHERE c.estado = 'pendiente_cancelacion'
                        ORDER BY c.fecha ASC, c.hora ASC
                    ");

                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        $cancelacionesPendientes[] = $row;
                    }

                    $stmt->close();







                    $hayNotificaciones =
                        count($usuariosPendientes) > 0 ||
                        count($cancelacionesPendientes) > 0;
                
                ?>

                <div class="menu-inline">
                    <div class="dropdown">
                        <i class="fa-solid fa-bell"></i>
                        <ul class="submenu">
                            <!--<p id="temp_txt">beta</p>-->
                            <?php if (
                                empty($usuariosPendientes) &&
                                empty($cancelacionesPendientes)
                            ): ?>

                                <li>
                                    <p style="opacity:.6;">No hay notificaciones</p>
                                </li>

                            <?php else: ?>

                            <?php foreach ($usuariosPendientes as $u): ?>
                                <li class="notif-item">

                                    <a class="notif-link"
                                    href="<?= BASE_URL ?>/admin/edit.php?tabla=usuario&idusuario=<?= $u['id_usuario'] ?>">

                                        <div class="notif-user">
                                            <i id="i_notif">Usuario pendiente</i><br>
                                            <strong><?= htmlspecialchars($u['username']) ?></strong><br>
                                            <small><?= htmlspecialchars($u['email']) ?></small>                                            
                                        </div>

                                    </a>

                                </li>
                            <?php endforeach; ?>

                            <?php foreach ($cancelacionesPendientes as $c): ?>
                                <li class="notif-item">

                                    <a class="notif-link"
                                    href="<?= BASE_URL ?>/admin/editar_cita.php?id=<?= $c['id_cita'] ?>">

                                        <div class="notif-user">
                                            <i id="i_notif">Solicitud de cancelación</i><br>
                                            <strong><?= htmlspecialchars($c['nombre']) ?></strong><br>
                                            <small>
                                                <?= $c['fecha'] ?> <?= substr($c['hora'], 0, 5) ?>
                                            </small>                                            
                                        </div>

                                    </a>

                                </li>
                            <?php endforeach; ?>

                            <?php endif; ?>
                        </ul>
                    
                        <?php if ($hayNotificaciones): ?>
                            <span class="notif-dot"></span>
                        <?php endif; ?>
                    </div>
                </div>                

                <a href="<?php echo BASE_URL . '/admin/count.php';?>">
                    <i class="fa-solid fa-dollar"></i>
                </a>

                <a href="<?php echo BASE_URL; ?>/admin/confpan.php">
                    <i class="fa-solid fa-gear"></i>
                </a>

                <a href="<?php echo BASE_URL; ?>">
                    <i class="fa-solid fa-home"></i>
                </a>

            <!--</div>-->
        <?php endif; ?>

        <?php if ($rol === 'cliente'): ?>
            <a href="<?php echo BASE_URL; ?>"><i class="fa-solid fa-home"></i></a>
        <?php endif; ?>
    </div>   

</div>