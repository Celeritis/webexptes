<?php
// funciones_op.php - Conexión segura y utilidades comunes para ABM de Órdenes de Pago

function conectarDB() {
    define('RUTA_BASE_FUNC', dirname(__DIR__, 1));
    require_once RUTA_BASE_FUNC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
    cargarVariablesEntorno();

    $clave_encriptacion = getenv('ENCRYPTION_KEY');
    $dbusrpass = openssl_decrypt(
        $_SESSION['dbusr_pass_encrypted'],
        'aes-256-cbc',
        $clave_encriptacion,
        0,
        substr(hash('sha256', $clave_encriptacion), 0, 16)
    );

    $host = getenv('DB_HOST');
    $db = getenv('DB_NAME');
    $user = $_SESSION['usuario'];
    $pass = $dbusrpass;

    $conexion = new mysqli($host, $user, $pass, $db);
    if ($conexion->connect_error) {
        die(json_encode(["error" => "Conexión fallida: " . $conexion->connect_error]));
    }

    $conexion->options(MYSQLI_OPT_CONNECT_TIMEOUT, 120);

    // Forzar UTF-8
    $conexion->set_charset("utf8mb4");

    return $conexion;
}

function conectarDB_PDO() {
    define('RUTA_BASE_FUNC', dirname(__DIR__, 1));
    require_once RUTA_BASE_FUNC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
    cargarVariablesEntorno();
    $clave_encriptacion = getenv('ENCRYPTION_KEY');
    $dbusrpass = openssl_decrypt(
        $_SESSION['dbusr_pass_encrypted'],
        'aes-256-cbc',
        $clave_encriptacion,
        0,
        substr(hash('sha256', $clave_encriptacion), 0, 16)
    );
    $host = getenv('DB_HOST');
    $db = getenv('DB_NAME');
    $user = $_SESSION['usuario'];
    $pass = $dbusrpass;
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $opciones);
    } catch (PDOException $e) {
        echo json_encode(["exito" => false, "mensaje" => "Error de conexión: " . $e->getMessage()]);
        exit;
    }
}

// Utilidad: Sanitizar datos JSON recibidos
function limpiarEntradaJSON() {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["exito" => false, "mensaje" => "JSON inválido recibido."]);
        exit;
    }

    // Sanitizar cada valor del arreglo
    array_walk_recursive($data, function (&$valor) {
        $valor = trim($valor);
        if (is_string($valor)) {
            $valor = htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        }
    });

    return $data;
}
?>
