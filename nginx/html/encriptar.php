<?php
define('ENCRYPTION_KEY', '12!$34!$56!$78!$90!$webexptes!$police!$F');

function encryptData($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

$clave_real = 'a1234567879A!$webexptes'; 
$encriptada = encryptData($clave_real);

echo "Clave encriptada para usar en conexion.php:<br><textarea cols='100' rows='2'>" . $encriptada . "</textarea>";
