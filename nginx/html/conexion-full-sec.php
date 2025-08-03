<?php
require_once 'C:/WebExptes/config/env.php';
cargarVariablesEntorno();

$host   = 'localhost:3306';
$user   = 'webexptes';
$pass_encrypted = 'Y3kyd3Q4Y09sRGtUZ1h2L0cxTzR2dHVUTHJlOXJKa1M2cHE2b0s3WTdWdz06OtHK7wgiZbW17dhdyJ9IJYk=';
$dbname = 'webexptes';

$clave_encriptacion = getenv('ENCRYPTION_KEY'); 

function decryptData($data) {
    global $clave_encriptacion;

    $partes = explode('::', base64_decode($data), 2);
    if (count($partes) !== 2) {
        throw new Exception("⚠️ Formato inválido del texto encriptado.");
    }
    list($encrypted_data, $iv) = $partes;
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $clave_encriptacion, 0, $iv);
}

try {
    // 🔓 Desencriptar contraseña
    $pass = decryptData($pass_encrypted);

    // 🔌 Conexión
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión inicial: " . $conn->connect_error);
    }

    // Si todo está bien
    echo "✅ Conexión exitosa a la base de datos <b>$dbname</b> como usuario <b>$user</b>";

} catch (Exception $e) {
    die("❌ " . $e->getMessage());
}
?>