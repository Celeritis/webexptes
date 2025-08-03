<?php
define('RUTA_BASE', dirname(__DIR__, 2));
require_once RUTA_BASE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';

cargarVariablesEntorno();

// Valores de conexión disponibles globalmente

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');

// Clave desde entorno
$clave_encriptacion = getenv('ENCRYPTION_KEY');
echo $clave_encriptacion . '<br>';

require_once RUTA_BASE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';


if (!$clave_encriptacion || strlen($clave_encriptacion) !== 32) {
    die("⛔ Clave de encriptación inválida o no encontrada en el .env (debe tener 32 caracteres) : " . $clave_encriptacion );
}

// Datos del usuario
$usuario = 'rene'; // ← Cambiá esto
$clave_a_encriptar = 'renerivero27'; // ← Cambiá esto
echo $usuario . '<br>';
echo $clave_a_encriptar . '<br>';

// Función de encriptación compatible con decryptData()
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(16); // Vector de inicialización de 16 bytes
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    if ($encrypted === false) {
        throw new Exception("⛔ Falló la encriptación.");
    }
    return base64_encode($encrypted . '::' . $iv); // Formato esperado por decryptData()
}

try {
    $conn = DB::get();

    $encriptado = encryptData($clave_a_encriptar, $clave_encriptacion);
    echo 'encriptado: ' . $encriptado . '<br>';
    $stmt = $conn->prepare("UPDATE usuarios SET loginpass = ? WHERE usuario = ?");
    $stmt->bind_param("ss", $encriptado, $usuario);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "✅ Clave encriptada correctamente y actualizada para el usuario: $usuario";
    } else {
        echo "⚠️ No se actualizó ningún registro. ¿El usuario existe?";
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    die("⛔ Error: " . $e->getMessage());
}
