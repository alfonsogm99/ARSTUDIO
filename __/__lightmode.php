<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'dark';
    $file = __DIR__ . '/config.json';

    if (!is_writable($file)) {
        echo json_encode(['status' => 'error', 'message' => 'Archivo no escribible']);
        exit;
    }

    if (file_put_contents($file, json_encode(['theme' => $theme]))) {
        echo json_encode(['status' => 'ok', 'theme' => $theme]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo escribir en el archivo']);
    }
}