<?php
header('Content-Type: application/json');

// Configuración de la base de datos
$db_path = 'C:\WebExptes\sqlite\db\webexptes.db';

try {
    $db = new SQLite3($db_path);
    
    // Obtener datos POST
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validación básica
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Usuario y contraseña requeridos']);
        exit;
    }
    
    // Consulta preparada
    $stmt = $db->prepare('SELECT id, password, rol FROM usuarios WHERE username = :username');
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['rol'] = $user['rol'];
        
        echo json_encode([
            'success' => true,
            'redirect' => $user['rol'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Credenciales incorrectas. Intente nuevamente.'
        ]);
    }
} catch (Exception $e) {
    error_log('Error de autenticación: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor. Por favor intente más tarde.'
    ]);
}
?>