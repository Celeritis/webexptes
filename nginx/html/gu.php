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

    // Crear nuevo usuario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
        $usuario = $conn->real_escape_string($_POST['usuario']);
        $nombre = $conn->real_escape_string($_POST['nombre']);
        $loginpass = password_hash($_POST['loginpass'], PASSWORD_DEFAULT);
        $pass = encryptData($_POST['pass'], $encryption_key);
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

    // Buscar usuario
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar'])) {
        $search_user = $conn->real_escape_string($_POST['buscar_usuario']);
        
        $result = $conn->query("
            SELECT * 
            FROM usuarios 
            WHERE usuario = '$search_user'
        ");
        
        if ($result->num_rows > 0) {
            $search_result = $result->fetch_assoc();
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - WebExptes</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            padding: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        h1, h2 {
            color: #2575fc;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 1rem;
            align-items: center;
        }
        
        input, textarea, select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
        }
        
        button {
            background: #2575fc;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        
        button:hover {
            opacity: 0.9;
        }
        
        .message {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 5px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .user-details {
            margin-top: 2rem;
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .user-details p {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Usuarios</h1>
        
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h2>Crear Nuevo Usuario</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Usuario:</label>
                    <input type="text" name="usuario" required>
                </div>
                
                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Contraseña Login:</label>
                    <input type="password" name="loginpass" required>
                </div>
                
                <div class="form-group">
                    <label>Contraseña DB:</label>
                    <input type="password" name="pass" required>
                </div>
                
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email">
                </div>
                
                <div class="form-group">
                    <label>Activo:</label>
                    <input type="checkbox" name="activo" checked>
                </div>
                
                <div class="form-group">
                    <label>Motivo:</label>
                    <input type="text" name="motivo">
                </div>
                
                <div class="form-group">
                    <label>Descripción:</label>
                    <textarea name="descrip"></textarea>
                </div>
                
                <button type="submit" name="crear">Crear Usuario</button>
            </form>
        </div>

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
                    <p><strong>Contraseña DB:</strong> <?= htmlspecialchars($search_result['pass_decrypted']) ?></p>
                    <p><strong>Estado:</strong> <?= $search_result['activo'] ? 'Activo' : 'Inactivo' ?></p>
                    <p><strong>Fecha Creación:</strong> <?= $search_result['fecha_creacion'] ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>