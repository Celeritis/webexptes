<?php
header('Content-Type: application/json');

$authorizedHostsFile = 'C:\WebExptes\nginx\conf\authorized_hosts.txt';
$requestData = json_decode(file_get_contents('php://input'), true);

$hostname = strtolower(trim($requestData['hostname'] ?? ''));
$ip = $requestData['ip'] ?? '';

// Leer lista de hosts autorizados
$authorizedHosts = [];
if (file_exists($authorizedHostsFile)) {
    $lines = file($authorizedHostsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $authorizedHosts[] = strtolower(trim($line));
    }
}

// Verificar coincidencia
$isAuthorized = in_array(strtolower($hostname), $authorizedHosts);

echo json_encode([
    'authorized' => $isAuthorized,
    'hostname' => $hostname,
    'ip' => $ip
]);
?>