<?php
// 32 caracteres
// define('ENCRYPTION_KEY', '12345678901234567890123456789012'); 
define('ENCRYPTION_KEY', '12!$34!$56!$78!$90!$webexptes!$police!$F');



function encryptData($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData($data) {
    $partes = explode('::', base64_decode($data), 2);
    if (count($partes) !== 2) {
        throw new Exception("Formato inválido del texto encriptado.");
    }
    list($encrypted_data, $iv) = $partes;
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

// Prueba
$original = "renerivero27DB";
$cifrado = encryptData($original);
echo "Cifrado: $cifrado\n";

$descifrado = decryptData($cifrado);
echo "Descifrado: $descifrado\n";
