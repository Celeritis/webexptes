<?php
// Configuración de la conexión
$host = 'localhost:3306'; 
$user = 'webexptes';
$password = 'a1234567879A!$webexptes';
$dbname = 'webexptes';

// -----------------------------------------------------
//$user = 'root';
//$password = 'a123456789A!$root';
// -----------------------------------------------------


// Clave de encriptación (¡DEBES CAMBIARLA y ALMACENARLA SEGURA!)
define('ENCRYPTION_KEY', '12!$34!$56!$78!$90!$webexptes!$police!$F');

// Función para encriptar
function encryptData($data) {
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

// Función para desencriptar
function decryptData($data) {
    list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', ENCRYPTION_KEY, 0, $iv);
}

try {
    // 1. Conexión a MySQL
    $conn = new mysqli($host, $user, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Error de conexión 1: " . $conn->connect_error);
    }

    // 2. Creación de tablas
    $tables = [
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            rol INT DEFAULT 1 NOT NULL,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            loginpass VARCHAR(255) NOT NULL,
            pass VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
            fecha_login DATETIME,
            fecha_logout DATETIME,
            fecha_bloqueo  DATETIME,
            activo BOOLEAN DEFAULT TRUE,
            motivo VARCHAR(100),
            descrip VARCHAR(200),
            foto LONGBLOB
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) UNIQUE NOT NULL,
            nivel INT NOT NULL,
            grupo INT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS nivel (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) UNIQUE NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS grupo (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) UNIQUE NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($tables as $sql) {
        if (!$conn->query($sql)) {
            throw new Exception("Error creando tabla: " . $conn->error);
        }
    }

    // 3. Insertar datos de prueba
    // Encriptar contraseñas
    $loginpass_hash = password_hash('webexptes', PASSWORD_DEFAULT);
    $db_pass_encrypted = encryptData('a1234567879A!$webexptes');
    
    // Insertar en usuarios
    $sql = "INSERT INTO usuarios (
        rol, usuario, nombre, loginpass, pass, email
    ) VALUES (
        1,
        'admin',
        'Administrador Principal',
        '$loginpass_hash',
        '$db_pass_encrypted',
        'admin@webexptes.com'
    )";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error insertando usuario: " . $conn->error);
    }

    // Insertar en roles (nombre, nivel, grupo)
    $roles = [
        ['Administrador', 1, 1],
        ['Editor', 2, 2],
        ['Consultor', 5, 4],
        ['Basico', 8, 4]
    ];
    
    foreach ($roles as $rol) {
        $sql = "INSERT INTO roles (nombre, nivel, grupo) VALUES ('$rol[0]', $rol[1], $rol[2])";
        $conn->query($sql);
    }

    // Insertar en nivel
    $niveles = ['TODO', 'BORRAR', 'ACTUALIZAR', 'INSERTAR', 'CONSULTAR', 'RECIENTES7','RECIENTES3','RECIENTES1','NADA'];
    foreach ($niveles as $nivel) {
        $sql = "INSERT INTO nivel (nombre) VALUES ('$nivel')";
        $conn->query($sql);
    }

    // Insertar en grupo
    $grupos = ['Verificadores', 'Ejecutores', 'Iniciadores', 'Consultores'];
    foreach ($grupos as $grupo) {
        $sql = "INSERT INTO grupo (nombre) VALUES ('$grupo')";
        $conn->query($sql);
    }

    echo "¡Base de datos configurada exitosamente!";
    
    $conn->close();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>