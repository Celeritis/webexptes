<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    echo "⚠️ No se encontró sesión de usuario.";
    exit();
}

define('RUTA_BASE_D', dirname(__DIR__, 2)); 
require_once RUTA_BASE_D . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';

cargarVariablesEntorno();

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
//$DB_USER = getenv('DB_USER');
$DB_USER = $_SESSION['usuario'];
// $DB_PASS = getenv('PASS_RAW');
$clave_encriptacion = getenv('ENCRYPTION_KEY');
require_once RUTA_BASE_D . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';

$dbusrpass = openssl_decrypt(
    $_SESSION['dbusr_pass_encrypted'],
    'aes-256-cbc',
    $clave_encriptacion,
    0,
    substr(hash('sha256', $clave_encriptacion), 0, 16)
);

$usuario = trim($_SESSION['usuario']); // Ojo con espacios
$foto = '';
$nombre = '';

try {
    //$conn = DB::get();

    $conn = new mysqli(
        getenv('DB_HOST'),
        $_SESSION['usuario'],
        $dbusrpass,
        getenv('DB_NAME')
    );
    
    if ($conn->connect_error) {
        throw new Exception("Error al conectar a la base de datos: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT nombre, foto FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $nombre = $userData['nombre'] ?? '';
        $foto = $userData['foto'] ?? '';
    } else {
        $nombre = '⚠️ No se encontró usuario en DB';
    }

    $stmt->close();
    DB::close();
} catch (Exception $e) {
    $nombre = '⚠️ Error en DB: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Debug</title>
</head>
<body>
    <h2>DEBUG</h2>
    <p>Sesión usuario: <b><?php echo htmlspecialchars($usuario); ?></b></p>
    <p>Nombre encontrado: <b><?php echo htmlspecialchars($nombre); ?></b></p>
    <hr>
    <p>Esto será reemplazado por el dashboard final una vez que confirmes que los datos se leen correctamente.</p>
</body>
</html>
