<?php
session_start();
header('Content-Type: application/json');

// Configuración de la conexión inicial
$host = 'localhost:3306';
$dbname = 'webexptes';
$user = 'webexptes';
$pass = 'a1234567879A!$webexptes';

// Clave de encriptación (debe coincidir con la usada anteriormente)
define('ENCRYPTION_KEY', '12!$34!$56!$78!$90!$webexptes!$police!$F');

function decryptData($data) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

try {
    // Primera conexión con usuario webexptes
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión inicial: " . $conn->connect_error);
    }

    $inputUser = $_POST['username'];
    $inputPass = $_POST['password'];
    
    // Buscar usuario en la base de datos
    $stmt = $conn->prepare("SELECT usuario, nombre, loginpass, pass FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $inputUser);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Usuario no encontrado");
    }

    $userData = $result->fetch_assoc();
    
    // Verificar contraseña de login
    if (!password_verify($inputPass, $userData['loginpass'])) {
        throw new Exception("Contraseña incorrecta");
    }

    // Desencriptar contraseña de conexión
    $dbPassword = decryptData($userData['pass']);
    
    // Cerrar conexión inicial
    $conn->close();

    // Segunda conexión con credenciales del usuario
    $userConn = new mysqli($host, $userData['usuario'], $dbPassword, $dbname);
    
    if ($userConn->connect_error) {
        throw new Exception("Error al conectar con credenciales de usuario: " . $userConn->connect_error);
    }

    // Actualizar fecha de login
    $updateStmt = $userConn->prepare("UPDATE usuarios SET fecha_login = NOW() WHERE usuario = ?");
    $updateStmt->bind_param("s", $userData['usuario']);
    $updateStmt->execute();
    
    // Preparar datos para redirección
    $_SESSION['usuario'] = $userData['usuario'];
    $_SESSION['nombre'] = $userData['nombre'];
    $_SESSION['fecha_login'] = date('Y-m-d H:i:s');

    echo json_encode([
        'success' => true,
        'redirect' => 'bienvenida.php?user='.urlencode($userData['usuario']).'&datetime='.urlencode(date('Y-m-d H:i:s'))
    ]);

    $userConn->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error de autenticación: ' . $e->getMessage()
    ]);
}
?>