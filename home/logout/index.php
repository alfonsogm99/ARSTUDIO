<?php
session_start();

// Vaciar variables de sesión
$_SESSION = [];

// Destruir sesión
session_destroy();

// (Opcional pero recomendado) borrar cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Redirigir al login
header("Location: ../login");
exit;