<?php
define('RUTA_BASE_DE', dirname(__DIR__, 2)); 
require_once RUTA_BASE_DE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
cargarVariablesEntorno();

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');

require_once RUTA_BASE_DE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';

try {
    $conn = DB::get();
    if (!$conn) {
        throw new Exception("No se pudo obtener la conexión desde DB::get()");
    }

    // 🔒 Activamos los errores para que mysqli los arroje como excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $sql = "
    CREATE TABLE IF NOT EXISTS detallepagoxcpbte (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha_ordenado DATE,
        anio YEAR GENERATED ALWAYS AS (YEAR(fecha_ordenado)) STORED,
        nro_comprobante VARCHAR(8),
        cancelado TINYINT(1) GENERATED ALWAYS AS (IF(monto_total - monto_pagado_total = 0, 1, 0)) STORED,
        expediente VARCHAR(20),
        tc CHAR(2),
        tipo_comprobante VARCHAR(50),
        monto_total DECIMAL(18,2),
        monto_pagado_total DECIMAL(18,2),
        op_descrip VARCHAR(60),
        cuenta_pago VARCHAR(20),
        nombre_cuenta_pago VARCHAR(60),
        cuit_benef CHAR(11),
        beneficiario VARCHAR(60),
        fecha_pago DATE,
        tp CHAR(1),
        pago_e VARCHAR(6),
        fecha_carga DATETIME,
        descrip_carga VARCHAR(100),
        UNIQUE KEY uk_anio_comprobante (anio, nro_comprobante),
        INDEX idx_fecha_pago (fecha_pago),
        INDEX idx_expediente (expediente),
        INDEX idx_cuenta_pago (cuenta_pago),
        INDEX idx_beneficiario (beneficiario)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $conn->query($sql);
    echo "✅ Tabla 'detallepagoxcpbte' creada correctamente.";

    DB::close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    error_log("❌ Error SQL o de conexión: " . $e->getMessage());
}
?>
