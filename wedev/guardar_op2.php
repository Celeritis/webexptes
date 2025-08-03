<?php
// guardar_op.php ver 001 08/07/2025 09:01
 
header('Content-Type: application/json');
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0775, true);
}

$logFile = $logDir . '/error_guardar_op.txt'; // Log específico para este script

function log_error($mensaje) {
    global $logFile;
    $fecha = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$fecha] $mensaje\n", FILE_APPEND);
}

// Replicando funciones de limpieza de guardar_autorizados2.php
function limpiar_importe($importe_txt) {
    $importe_txt = trim($importe_txt);
    if ($importe_txt === '' || $importe_txt === null) {
        return 0.0; // O null, si tu columna DECIMAL lo permite y lo prefieres
    }
    $importe_txt = str_replace('.', '', $importe_txt);
    $importe_txt = str_replace(',', '.', $importe_txt);
    return (float)$importe_txt;
}

function convertir_fecha_mysql($fecha_txt) {
    $fecha_txt = trim($fecha_txt);
    if (empty($fecha_txt)) {
        return null; // En lugar de '0000-00-00' para columnas DATE NULL
    }
    // Asegura el formato YYYY-MM-DD si ya viene así del input type="date"
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_txt)) {
        return $fecha_txt;
    }
    // Intenta formatear desde d/m/Y si el formato es distinto
    $formateada = date_create_from_format('d/m/Y', $fecha_txt);
    if ($formateada) {
        return $formateada->format('Y-m-d');
    }
    return null; // Si no se puede formatear, devuelve null
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

    // --- Validaciones de datos (backend) ---
    // Similares a las de abm_op2.php para mayor seguridad
    if (empty($datos['anio']) || !is_numeric($datos['anio']) || $datos['anio'] < 1900 || $datos['anio'] > 2100) {
        throw new Exception('El Año debe ser un número válido entre 1900 y 2100.');
    }
    if (empty($datos['nro_comprobante']) || !is_numeric($datos['nro_comprobante']) || $datos['nro_comprobante'] < 1) {
        throw new Exception('El Nro. de Comprobante debe ser un número válido mayor o igual a 1.');
    }
    if (empty($datos['expediente']) || !preg_match('/^\d+\/\d{3}\/\d{2}$/', $datos['expediente'])) {
        throw new Exception('Formato de Expediente incorrecto. Debe ser N/XXX/YY.');
    }
    if (empty($datos['op_descrip'])) { // Campo op_descrip como NOT NULL
        throw new Exception('La Descripción no puede estar vacía.');
    }

    // Validar campos numéricos que pueden ser opcionales
    $campos_numericos_opcionales = ['cuenta_pago', 'cuit_benef', 'pago_e'];
    foreach ($campos_numericos_opcionales as $campo) {
        if (!empty($datos[$campo]) && !ctype_digit(str_replace(['.', ','], '', $datos[$campo]))) { // Permite puntos/comas temporalmente para validar si son solo dígitos o vacío
            throw new Exception("El campo '" . ucfirst(str_replace('_', ' ', $campo)) . "' solo debe contener números (o estar vacío).");
        }
    }

    // --- Preparación de datos ---
    $id = $datos['id'] ?? null; // ID solo se usa para edición, aquí debe ser null
    $fecha_ordenado = convertir_fecha_mysql($datos['fecha_ordenado'] ?? '');
    $anio = (int)($datos['anio'] ?? 0);
    $nro_comprobante = trim((string)($datos['nro_comprobante'] ?? ''));
    $tmonto_total = floatval($datos['monto_total']);
    $tmonto_pagado_total = floatval($datos['monto_pagado_total']);
    $cancelado = (abs($tmonto_total - $tmonto_pagado_total) < 0.01) ? 1 : 0;
    $expediente = trim((string)($datos['expediente'] ?? ''));
    $tc = trim((string)($datos['tc'] ?? ''));
    $tipo_comprobante = trim((string)($datos['tipo_comprobante'] ?? '')); // Este campo no está en el form, ¿viene del TC seleccionado?
    $monto_total = limpiar_importe($datos['monto_total'] ?? '0');
    $monto_pagado_total = limpiar_importe($datos['monto_pagado_total'] ?? '0');
    $op_descrip = strtoupper(trim((string)($datos['op_descrip'] ?? ''))); // A mayúsculas
    $cuenta_pago = trim((string)($datos['cuenta_pago'] ?? ''));
    $nombre_cuenta_pago = strtoupper(trim((string)($datos['nombre_cuenta_pago'] ?? ''))); // A mayúsculas
    $cuit_benef = trim((string)($datos['cuit_benef'] ?? ''));
    $beneficiario = strtoupper(trim((string)($datos['beneficiario'] ?? ''))); // A mayúsculas
    $fecha_pago = convertir_fecha_mysql($datos['fecha_pago'] ?? '');
    $tp = strtoupper(trim((string)($datos['tp'] ?? ''))); // Asumiendo que TP es un código alfanumérico si se aplica strtoupper
    $pago_e = trim((string)($datos['pago_e'] ?? ''));
    $descrip_carga = strtoupper(trim((string)($datos['descrip_carga'] ?? ''))); // A mayúsculas

    // Iniciar transacción
    $conn->begin_transaction();

    // --- INICIO DE VALIDACIÓN DE DUPLICIDAD ---
    $sql_check_duplicate = "SELECT anio, nro_comprobante, expediente FROM detallepagoxcpbte WHERE anio = ? AND nro_comprobante = ?";
    $stmt_check = $conn->prepare($sql_check_duplicate);
    $stmt_check->bind_param("is", $anio, $nro_comprobante);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $duplicados = [];
        while ($row = $result_check->fetch_assoc()) {
            $duplicados[] = $row;
        }
        $conn->rollback();
        ob_clean();
        flush();
        echo json_encode([
            'success' => false,
            'mensaje' => 'Ya existe una Orden de Pago con el mismo Año y Nro. de Comprobante. Modifique los datos y reintente.',
            'duplicados' => $duplicados // Enviar los datos de las filas duplicadas
        ]);
        exit(); // Detener la ejecución
    }
    // --- FIN DE VALIDACIÓN DE DUPLICIDAD ---

    // Preparar la sentencia de inserción
    $stmt = $conn->prepare("
        INSERT INTO detallepagoxcpbte (
            fecha_ordenado, anio, nro_comprobante, cancelado, expediente, tc, tipo_comprobante,
            monto_total, monto_pagado_total, op_descrip, cuenta_pago, nombre_cuenta_pago,
            cuit_benef, beneficiario, fecha_pago, tp, pago_e, descrip_carga, fecha_carga
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
    }

    // Asignar parámetros. s=string, i=integer, d=double. Ajustar según tipos de columna.
    // 'sisisssddsssssssss' -> 18 parámetros
    // Ajustado para que null pase como null y 0 como 0 para fechas/decimales/ints
    // TIPOS DE DATOS: fecha_ordenado (s), anio (i), nro_comprobante (s), cancelado (i), expediente (s), tc (s), tipo_comprobante (s), monto_total (d), monto_pagado_total (d), op_descrip (s), cuenta_pago (s), nombre_cuenta_pago (s), cuit_benef (s), beneficiario (s), fecha_pago (s), tp (s), pago_e (s), descrip_carga (s)
    $stmt->bind_param(
        "sisisssddsssssssss", 
        $fecha_ordenado,
        $anio,
        $nro_comprobante,
        $cancelado,
        $expediente,
        $tc,
        $tipo_comprobante,
        $monto_total,
        $monto_pagado_total,
        $op_descrip,
        $cuenta_pago,
        $nombre_cuenta_pago,
        $cuit_benef,
        $beneficiario,
        $fecha_pago,
        $tp,
        $pago_e,
        $descrip_carga
    );

    $stmt->execute();
   
    // Obtener el ID del último registro insertado.
    // Confirmado que 'id' es AUTO_INCREMENT y PRIMARY KEY.
   $nuevo_id = $conn->insert_id; 

    $conn->commit();
    ob_clean();
    flush();
    //echo json_encode(['success' => true, 'mensaje' => 'Orden de Pago guardada exitosamente.']);
    echo json_encode(['success' => true, 'mensaje' => 'Orden de Pago guardada exitosamente.', 'id_insertado' => $nuevo_id]);

} catch (mysqli_sql_exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    // Error 23000 es para violación de integridad (ej: clave única duplicada)
    if ($e->getCode() == 1062) { // MySQL error code for Duplicate entry for key 'PRIMARY' or 'UNIQUE'
        log_error("Error de duplicidad al guardar OP: " . $e->getMessage());
        ob_clean();
        flush();
        echo json_encode(['success' => false, 'mensaje' => 'Ya existe una Orden de Pago con el mismo Año y Nro. de Comprobante.']);
    } else {
        log_error("Error de base de datos al guardar OP: " . $e->getMessage());
        ob_clean();
        flush();
        echo json_encode(['success' => false, 'mensaje' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    log_error("Error inesperado al guardar OP: " . $e->getMessage());
    ob_clean();
    flush();
    echo json_encode(['success' => false, 'mensaje' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}