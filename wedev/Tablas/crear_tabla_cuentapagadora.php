<?php
//  Creación de tabla 'cuentapagadora'

define('RUTA_BASE_CTPA', dirname(__DIR__, 2)); 
require_once RUTA_BASE_CTPA . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
cargarVariablesEntorno();

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');

require_once RUTA_BASE_CTPA . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';

try {
    $conn = DB::get();
    if (!$conn) {
        throw new Exception("No se pudo obtener la conexión desde DB::get()");
    }

    // 🔒 Activamos los errores para que mysqli los arroje como excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $sql = "
    CREATE TABLE IF NOT EXISTS cuentapagadora (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cuenta_pago VARCHAR(20),
        nombre_cuenta_pago VARCHAR(60),
        INDEX idx_cp (cuenta_pago),
        INDEX idx_nombre_cuenta_pago (nombre_cuenta_pago)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $conn->query($sql);
    echo "✅ Tabla 'cuentapagadora' creada correctamente.";

    DB::close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    error_log("❌ Error SQL o de conexión: " . $e->getMessage());
}
?>
