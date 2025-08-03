<?php
// -------------------------------------------
// Archivo: conexion.php
// -------------------------------------------

$host   = "localhost:3306";
$user   = "webexptes";
$pass_encrypted = "UVJ3eGVaSXNDRUVZYXlLNVlRRGxUVVQyYUJlN0ZBMzB5eGVrNzhPRE5PST06Oq4ONI8gqcHqjtU5XJ6zh14="; 
$dbname = "webexptes";

// 🔐 Clave de encriptación
define('ENCRYPTION_KEY', '12!$34!$56!$78!$90!$webexptes!$police!$F');

// 🧩 Función para desencriptar
function decryptData($data) {
    $partes = explode('::', base64_decode($data), 2);
    if (count($partes) !== 2) {
        throw new Exception("⚠️ Formato inválido del texto encriptado.");
    }
    list($encrypted_data, $iv) = $partes;
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

try {
    $pass = decryptData($pass_encrypted);
    if (!$pass) {
        throw new Exception("⚠️ No se pudo desencriptar la contraseña.");
    }

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Error de conexión inicial: " . $conn->connect_error);
    }

} catch (Exception $e) {
    die("❌ " . $e->getMessage());
}
?>
