<?php
// eliminar_op.php 01/06/25 - 10:51

header('Content-Type: application/json');
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0775, true);
}

$logFile = $logDir . '/error_eliminar_op.txt'; // Log específico para este script

function log_error($mensaje) {
    global $logFile;
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$fecha] $mensaje\n", FILE_APPEND);
}

try {
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('No autorizado - Sesion no iniciada');
    }

    // Incluir funciones_op para la conexión segura
    require_once 'funciones_op.php';
    $conn = conectarDB(); // Usar la función de conexión que usa mysqli

    $json = file_get_contents("php://input");
    if (empty($json)) {
        throw new Exception("No se recibieron datos.");
    }

    $datos = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    // Validar que se recibió el ID
    if (empty($datos['id']) || !is_numeric($datos['id']) || $datos['id'] < 1) {
        throw new Exception('ID de Orden de Pago inválido.');
    }

    $id = (int)$datos['id'];

    // Iniciar transacción
    $conn->begin_transaction();

    // Verificar que el registro existe antes de eliminar
    $stmt_check = $conn->prepare("SELECT id FROM detallepagoxcpbte WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'mensaje' => 'No se encontró la Orden de Pago especificada.'
        ]);
        exit();
    }

    // Preparar la sentencia de eliminación
    $stmt = $conn->prepare("DELETE FROM detallepagoxcpbte WHERE id = ?");
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'mensaje' => 'No se pudo eliminar la Orden de Pago.'
        ]);
        exit();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'mensaje' => 'Orden de Pago eliminada exitosamente.']);

} catch (mysqli_sql_exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    log_error("Error de base de datos al eliminar OP: " . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    log_error("Error inesperado al eliminar OP: " . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
} finally {
    if (isset($stmt_check)) $stmt_check->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>