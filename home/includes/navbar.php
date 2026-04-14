<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}

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
            <a href="<?php echo BASE_URL; ?>/admin/admin.php"><i class="fa-solid fa-cogs"></i></a>
            <a href="<?php echo BASE_URL; ?>"><i class="fa-solid fa-home"></i></a>
        <?php endif; ?>

        <?php if ($rol === 'barbero'): ?>
            <a href="<?php echo BASE_URL; ?>"><i class="fa-solid fa-home"></i></a>
        <?php endif; ?>

        <?php if ($rol === 'cliente'): ?>
            <a href="<?php echo BASE_URL; ?>/cliente"><i class="fa-solid fa-home"></i></a>
        <?php endif; ?>
    </div>   

</div>