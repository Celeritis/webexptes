<?php
// -------------------------------------------
// desencriptar_clave.php
// -------------------------------------------

define('ENCRYPTION_KEY', '56!$78!$90!$webexptes!$police!$F');

// 🔐 Función de desencriptado
function decryptData($data) {
            
    $partes = explode('::', base64_decode($data), 2);
    if (count($partes) !== 2) {
        throw new Exception("Formato inválido del texto encriptado.");
    }
    list($encrypted_data, $iv) = $partes;
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}


// 🧩 Conexión a la base
$host = 'localhost:3306';
$user = 'webexptes';
$pass = 'a1234567879A!$webexptes';
$db   = 'webexptes';          

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ Error de conexión: " . $conn->connect_error);
}

try {
    // Leer el campo encriptado
    $sql = "SELECT usuario, loginpass, pass FROM usuarios WHERE usuario = 'rene' LIMIT 1";
    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $clave_desencriptada = decryptData($fila['pass']);
        $clave_loginpass = decryptData($fila['loginpass']);

        echo "<h3>🔐 Clave desencriptada para <b>{$fila['usuario']}</b>:</h3>";
        echo "<pre>" . 'pass      : ' . $clave_desencriptada . '</pre>' . '<br>';
        echo "<pre>" . 'loginpass : ' . $clave_loginpass . '</pre>' ;
    } else {
        echo "⚠️ No se encontró el usuario en la tabla.";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>
