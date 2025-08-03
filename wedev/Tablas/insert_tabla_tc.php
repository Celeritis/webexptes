<?php
// Inserta 4 registros en tabla 'tc'

define('RUTA_BASE_DE', dirname(__DIR__, 2)); 
require_once RUTA_BASE_ITC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
cargarVariablesEntorno();

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');

require_once RUTA_BASE_ITC . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';

try {
    $conn = DB::get();
    if (!$conn) {
        throw new Exception("No se pudo obtener la conexión desde DB::get()");
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $registros = [
        ['FF', 'COMPROBANTE DE FONDOS FIJOS'],
        ['GA', 'COMPROBANTE DE GASTO DE EJERCICIOS ANTERIORES'],
        ['GC', 'COMPROBANTE DE GASTO DEL EJERCICIO CORRIENTE'],
        ['GE', 'COMPROBANTE DE GASTO EXTRAPRESUPUESTARIO'],
    ];

    $stmt = $conn->prepare("INSERT INTO tc (tc, tipo_comprobante) VALUES (?, ?)");

    foreach ($registros as [$tc, $tipo]) {
        $stmt->bind_param("ss", $tc, $tipo);
        $stmt->execute();
    }

    echo "✅ Registros insertados correctamente en la tabla 'tc'.";

    $stmt->close();
    DB::close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
    error_log("❌ Error SQL o de conexión: " . $e->getMessage());
}
?>
