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
        fecha_ordenado DATE NOT NULL,
        anio YEAR NOT NULL,
        nro_comprobante VARCHAR(8) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
        cancelado TINYINT(1) NULL DEFAULT NULL,
        expediente VARCHAR(20) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
        tc CHAR(2) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        tipo_comprobante VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        monto_total DECIMAL(18,2) NULL DEFAULT NULL,
        monto_pagado_total DECIMAL(18,2) NULL DEFAULT NULL,
        op_descrip VARCHAR(60) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        cuenta_pago VARCHAR(20) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        nombre_cuenta_pago VARCHAR(60) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        cuit_benef CHAR(11) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        beneficiario VARCHAR(60) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        fecha_pago DATE NULL DEFAULT NULL,
        tp CHAR(1) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        pago_e VARCHAR(6) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        fecha_carga DATETIME NULL DEFAULT NULL,
        descrip_carga VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8mb4_0900_ai_ci',
        INDEX uk_anio_comprobante (anio, nro_comprobante),
        INDEX idx_fecha_pago (fecha_pago),
        INDEX idx_expediente (expediente),
        INDEX idx_cuenta_pago (cuenta_pago),
        INDEX idx_beneficiario (beneficiario)
    ) COLLATE='utf8mb4_0900_ai_ci' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $conn->query($sql);
    echo "✅ Tabla 'detallepagoxcpbte' creada correctamente.";

    DB::close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    error_log("❌ Error SQL o de conexión: " . $e->getMessage());
}
?>
