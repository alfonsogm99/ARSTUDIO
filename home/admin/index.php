<?php
session_start();

define('BASE_URL', '/home');


if (!isset($_SESSION['user_type'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

if ($_SESSION['user_type'] === 'admin') {
    header("Location: " . BASE_URL . "/admin/admin.php");
    exit;
} elseif ($_SESSION['user_type'] === 'barbero') {
    header("Location: " . BASE_URL . "/control_panel.php");
    exit;
} else {
    header("Location: " . BASE_URL . "/logout.php");
    exit;
}