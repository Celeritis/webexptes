<?php
// Configuración de conexión
$host = 'localhost';
$dbname = 'webexptes';
$user = 'webexptes';
$pass = 'a1234567879A!$webexptes';
$encryption_key = '12!$34!$56!$78!$90!$webexptes!$police!$F';

// Funciones de encriptación
function encryptData($data, $key) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decryptData($data, $key) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

// Procesar formulario
$message = '';
$search_result = null;

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión: " . $conn->connect_error);
    }

    // Crear nuevo usuario (usando encriptación para ambos campos)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
        $usuario = $conn->real_escape_string($_POST['usuario']);
        $nombre = $conn->real_escape_string($_POST['nombre']);
        $loginpass = encryptData($_POST['loginpass'], $encryption_key); // Encriptado
        $pass = encryptData($_POST['pass'], $encryption_key); // Encriptado
        $email = $conn->real_escape_string($_POST['email']);
        $activo = isset($_POST['activo']) ? 1 : 0;
        $motivo = $conn->real_escape_string($_POST['motivo']);
        $descrip = $conn->real_escape_string($_POST['descrip']);

        $sql = "INSERT INTO usuarios (
            usuario, nombre, loginpass, pass, email, 
            activo, motivo, descrip
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiss", 
            $usuario, $nombre, $loginpass, $pass, $email,
            $activo, $motivo, $descrip
        );
        
        if ($stmt->execute()) {
            $message = "Usuario creado exitosamente!";
        } else {
            throw new Exception("Error al crear usuario: " . $stmt->error);
        }
    }

    // Buscar usuario con desencriptación
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
        $search_user = $conn->real_escape_string($_POST['buscar_usuario']);
        
        $result = $conn->query("
            SELECT * 
            FROM usuarios 
            WHERE usuario = '$search_user'
        ");
        
        if ($result->num_rows > 0) {
            $search_result = $result->fetch_assoc();
            // Desencriptar ambos campos
            $search_result['loginpass_decrypted'] = decryptData($search_result['loginpass'], $encryption_key);
            $search_result['pass_decrypted'] = decryptData($search_result['pass'], $encryption_key);
        } else {
            $message = "Usuario no encontrado";
        }
    }

} catch (Exception $e) {
    $message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Estilos igual que antes -->
</head>
<body>
    <div class="container">
        <h1>Gestión de Usuarios</h1>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de creación igual que antes -->

        <div class="form-section">
            <h2>Buscar Usuario</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nombre de Usuario:</label>
                    <input type="text" name="buscar_usuario" required>
                </div>
                <button type="submit" name="buscar">Buscar</button>
            </form>
            
            <?php if ($search_result): ?>
                <div class="user-details">
                    <h3>Detalles del Usuario</h3>
                    <p><strong>Usuario:</strong> <?= htmlspecialchars($search_result['usuario']) ?></p>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($search_result['nombre']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($search_result['email']) ?></p>
                    <p><strong>Loginpass:</strong> <?= htmlspecialchars($search_result['loginpass_decrypted']) ?></p>
                    <p><strong>Contraseña DB:</strong> <?= htmlspecialchars($search_result['pass_decrypted']) ?></p>
                    <p><strong>Estado:</strong> <?= $search_result['activo'] ? 'Activo' : 'Inactivo' ?></p>
                    <p><strong>Fecha Creación:</strong> <?= $search_result['fecha_creacion'] ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>