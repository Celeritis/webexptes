<?php
// abm_op2.php - Creación de tabla 'tc'

define('RUTA_BASE_DE', dirname(__DIR__, 2)); 
require_once RUTA_BASE_TC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
cargarVariablesEntorno();

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');

require_once RUTA_BASE_TC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';

try {
    $conn = DB::get();
    if (!$conn) {
        throw new Exception("No se pudo obtener la conexión desde DB::get()");
    }

    // 🔒 Activamos los errores para que mysqli los arroje como excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $sql = "
    CREATE TABLE IF NOT EXISTS tc (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tc CHAR(2),
        tipo_comprobante VARCHAR(50),
        INDEX idx_tc (tc),
        INDEX idx_tipo_comprobante (tipo_comprobante)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $conn->query($sql);
    echo "✅ Tabla 'tc' creada correctamente.";

    DB::close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    error_log("❌ Error SQL o de conexión: " . $e->getMessage());
}
?>
