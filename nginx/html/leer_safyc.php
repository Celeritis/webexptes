<?php
define('RUTA_BASE_LS', dirname(__DIR__, 2)); 
$ubicacion_actual =  RUTA_BASE_LS . DIRECTORY_SEPARATOR . 'wedev' . DIRECTORY_SEPARATOR . 'leer_safyc2.php';
file_put_contents('ubicacion_leer_safyc.txt', $ubicacion_actual, FILE_APPEND | LOCK_EX);
require_once RUTA_BASE_LS . DIRECTORY_SEPARATOR . 'wedev' . DIRECTORY_SEPARATOR . 'leer_safyc2.php';
