<?php
header('Content-Type: application/json');

// Obtener información del cliente
$clientInfo = [
    'hostname' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
    'ip' => $_SERVER['REMOTE_ADDR']
];

echo json_encode($clientInfo);
?>