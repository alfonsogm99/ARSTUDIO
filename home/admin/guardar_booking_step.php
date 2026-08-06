<?php

$configFile = './config.json';

$config = json_decode(file_get_contents($configFile), true) ?? [];

$step = (int)($_POST['booking_step'] ?? 30);

$step = max(10, round($step / 10) * 10);

$config['booking']['step'] = $step;

file_put_contents(
    $configFile,
    json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo json_encode([
    'success' => true
]);