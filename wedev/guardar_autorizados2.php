<?php
header('Content-Type: application/json');
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/error_guardar_autorizados.txt';

function log_error($mensaje) {
    global $logFile;
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$fecha] $mensaje\n", FILE_APPEND);
}

function limpiar_importe($importe_txt) {
    $importe_txt = trim($importe_txt);
    if ($importe_txt === '' || $importe_txt === null) {
        return 0.0;
    }
    $importe_txt = str_replace('.', '', $importe_txt);
    $importe_txt = str_replace(',', '.', $importe_txt);
    return (float)$importe_txt;
}

function convertir_fecha_mysql($fecha_txt) {
    $fecha_txt = trim($fecha_txt);
    if (empty($fecha_txt)) {
        return '0000-00-00';
    }
    $formateada = date_create_from_format('d/m/Y', $fecha_txt);
    if (!$formateada) {
        return '0000-00-00';
    }
    return $formateada->format('Y-m-d');
}

try {
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('No autorizado - Sesion no iniciada');
    }

    define('RUTA_BASE_DE', dirname(__DIR__, 1));
    require_once RUTA_BASE_DE . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
    cargarVariablesEntorno();

    $clave_encriptacion = getenv('ENCRYPTION_KEY');
    $dbusrpass = openssl_decrypt(
        $_SESSION['dbusr_pass_encrypted'],
        'aes-256-cbc',
        $clave_encriptacion,
        0,
        substr(hash('sha256', $clave_encriptacion), 0, 16)
    );

    $json = file_get_contents("php://input");
    if (empty($json)) {
        throw new Exception("No se recibieron datos");
    }

    $datos = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    if (!isset($datos['data']) || !is_array($datos['data'])) {
        throw new Exception('Estructura incorrecta: falta propiedad "data" o no es un array');
    }

    $conn = new mysqli(
        getenv('DB_HOST'),
        $_SESSION['usuario'],
        $dbusrpass,
        getenv('DB_NAME')
    );

    $insertados = 0;
    $duplicados = 0;

    $stmtInsert = $conn->prepare("
        INSERT INTO webexptes.detallepagoxcpbte (
            fecha_ordenado, anio, nro_comprobante, cancelado, expediente, tc, tipo_comprobante,
            monto_total, monto_pagado_total, op_descrip, cuenta_pago, nombre_cuenta_pago,
            cuit_benef, beneficiario, fecha_pago, tp, pago_e, descrip_carga, fecha_carga
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmtCheck = $conn->prepare("SELECT COUNT(*) AS cantidad FROM webexptes.detallepagoxcpbte WHERE anio = ? AND nro_comprobante = ? AND expediente = ?");

    foreach ($datos['data'] as $fila) {
        if (!isset($fila['autorizado']) || $fila['autorizado'] !== true) {
            continue;
        }

        $anio = (int)($fila['anio'] ?? 0);
        $nro_comprobante = (string)($fila['nro_comprobante'] ?? '');
        $expediente = (string)($fila['expediente'] ?? '');

        // Verificar duplicado
        $stmtCheck->bind_param("iss", $anio, $nro_comprobante, $expediente);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
        $row = $result->fetch_assoc();
        if ($row['cantidad'] > 0) {
            $duplicados++;
            continue;
        }

        // Preparar campos
        $fecha_ordenado_mysql = convertir_fecha_mysql($fila['fecha_ordenado']);
        $cancelado_valor = ($fila['cancelado'] === 'Si' || $fila['cancelado'] === true) ? 1 : 0;
        $tc = (string)($fila['tc'] ?? '');
        $tipo_comprobante = trim((string)($fila['tipo_comprobante'] ?? ''));
        if ($tipo_comprobante === '0') {
            $tipo_comprobante = '';
        }
        $monto_total_limpio = limpiar_importe($fila['monto_total']);
        $monto_pagado_limpio = limpiar_importe($fila['monto_pagado_total']);
        $op_descrip = (string)($fila['op_descrip'] ?? '');
        $cuenta_pago = (string)($fila['cuenta_pago'] ?? '');
        $nombre_cuenta_pago = (string)($fila['nombre_cuenta_pago'] ?? '');
        $cuit_benef = (string)($fila['cuit_benef'] ?? '');
        $beneficiario = (string)($fila['beneficiario'] ?? '');
        $fecha_pago_mysql = convertir_fecha_mysql($fila['fecha_pago']);
        $tp = (string)($fila['tp'] ?? '');
        $pago_e = (string)($fila['pago_e'] ?? '');
        $descrip_carga = (string)($fila['descrip_carga'] ?? '');

        $stmtInsert->bind_param(
            "sisisssddsssssssss",
            $fecha_ordenado_mysql,
            $anio,
            $nro_comprobante,
            $cancelado_valor,
            $expediente,
            $tc,
            $tipo_comprobante,
            $monto_total_limpio,
            $monto_pagado_limpio,
            $op_descrip,
            $cuenta_pago,
            $nombre_cuenta_pago,
            $cuit_benef,
            $beneficiario,
            $fecha_pago_mysql,
            $tp,
            $pago_e,
            $descrip_carga
        );

        $stmtInsert->execute();
        $insertados++;
    }

    echo json_encode([
        'status' => 'ok',
        'mensaje' => "Se insertaron $insertados registros correctamente. Se detectaron $duplicados duplicados no insertados.",
        'count' => count($datos['data']),
        'duplicados' => $duplicados
    ]);

} catch (Exception $e) {
    log_error($e->getMessage());
    echo json_encode([
        'status' => 'error',
        'mensaje' => $e->getMessage()
    ]);
} finally {
    if (isset($stmtInsert)) $stmtInsert->close();
    if (isset($stmtCheck)) $stmtCheck->close();
    if (isset($conn)) $conn->close();
}
?>
