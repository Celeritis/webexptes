<?php
echo 'Este es el camino actual antes de aplicar nada : ' . __DIR__ . '<br>';

define('RUTA_BASE_MC', dirname(__DIR__, 2)); 
require_once RUTA_BASE_MC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';

echo 'Este la RUTA_BASE_MC                            : ' . RUTA_BASE_MC . '<br>';
echo 'Ubicación de config.php                         : ' . RUTA_BASE_MC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php' . '<br>';

// Valores de conexión disponibles globalmente

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');
$ENCRYPTION_KEY = getenv('ENCRYPTION_KEY');

echo 'RUTA_BASE (definida una sola vez en config.php) : ' . RUTA_BASE . '<br>';
echo '-------------------------------------------' . '<br>';
echo 'V a r i a b l e s     d e     E n t o r n o  : ' . '<br>';
echo '-------------------------------------------' . '<br>';
echo 'DB_HOST :' . $DB_HOST . ' DB_NAME :' . $DB_NAME . ' DB_USER :' . $DB_USER . ' ENCRYPTION_KEY :' . $ENCRYPTION_KEY . '<br>';
