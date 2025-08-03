<?php
//**  actualizar_op.php      ver 0002 - 26/06/2025 11:32   */

header('Content-Type: application/json');
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/error_actualizar_op.txt'; // Log específico para este script

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

    require_once 'funciones_op.php';
    $conn = conectarDB(); 

    $json = file_get_contents("php://input");
    if (empty($json)) {
        throw new Exception("No se recibieron datos.");
    }

    $datos = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    // --- Validaciones de datos (backend) ---
    // El ID es crucial para la actualización
    if (empty($datos['id']) || !is_numeric($datos['id'])) {
        throw new Exception('ID de Orden de Pago no válido para actualizar.');
    }
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
        if (!empty($datos[$campo]) && !ctype_digit(str_replace(['.', ','], '', $datos[$campo]))) {
            throw new Exception("El campo '" . ucfirst(str_replace('_', ' ', $campo)) . "' solo debe contener números (o estar vacío).");
        }
    }

    // --- Preparación de datos ---
    $id = (int)$datos['id'] ?? null;
    $fecha_ordenado = convertir_fecha_mysql($datos['fecha_ordenado'] ?? '');
    $anio = (int)($datos['anio'] ?? 0);
    $nro_comprobante = trim((string)($datos['nro_comprobante'] ?? ''));
    $tmonto_total = floatval($datos['monto_total']);
    $tmonto_pagado_total = floatval($datos['monto_pagado_total']);
    $cancelado = (abs($tmonto_total - $tmonto_pagado_total) < 0.01) ? 1 : 0;
    $expediente = trim((string)($datos['expediente'] ?? ''));
    $tc = trim((string)($datos['tc'] ?? ''));
    $tipo_comprobante = trim((string)($datos['tipo_comprobante'] ?? '')); // Este campo no está en el form
    $monto_total = limpiar_importe($datos['monto_total'] ?? '0');
    $monto_pagado_total = limpiar_importe($datos['monto_pagado_total'] ?? '0');
    $op_descrip = strtoupper(trim((string)($datos['op_descrip'] ?? ''))); // A mayúsculas
    $cuenta_pago = trim((string)($datos['cuenta_pago'] ?? ''));
    $nombre_cuenta_pago = strtoupper(trim((string)($datos['nombre_cuenta_pago'] ?? ''))); // A mayúsculas
    $cuit_benef = trim((string)($datos['cuit_benef'] ?? ''));
    $beneficiario = strtoupper(trim((string)($datos['beneficiario'] ?? ''))); // A mayúsculas
    $fecha_pago = convertir_fecha_mysql($datos['fecha_pago'] ?? '');
    $tp = trim((string)($datos['tp'] ?? ''));
    $pago_e = trim((string)($datos['pago_e'] ?? ''));
    $descrip_carga = strtoupper(trim((string)($datos['descrip_carga'] ?? ''))); // A mayúsculas
    // fecha_carga NO se actualiza aquí, ya que es la fecha de creación

    // Iniciar transacción
    $conn->begin_transaction();

    // --- INICIO DE VALIDACIÓN DE DUPLICIDAD ---
    // Excluir el propio ID del registro que se está actualizando
    $sql_check_duplicate = "SELECT anio, nro_comprobante, expediente FROM detallepagoxcpbte WHERE anio = ? AND nro_comprobante = ? AND id != ?";
    $stmt_check = $conn->prepare($sql_check_duplicate);
    $stmt_check->bind_param("isi", $anio, $nro_comprobante, $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $duplicados = [];
        while ($row = $result_check->fetch_assoc()) {
            $duplicados[] = $row;
        }
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'mensaje' => 'Ya existe otra Orden de Pago con el mismo Año y Nro. de Comprobante. Modifique los datos y reintente.',
            'duplicados' => $duplicados // Enviar los datos de las filas duplicadas
        ]);
        exit(); // Detener la ejecución
    }
    // --- FIN DE VALIDACIÓN DE DUPLICIDAD ---

    // Preparar la sentencia de actualización
    $stmt = $conn->prepare("
        UPDATE detallepagoxcpbte SET
            fecha_ordenado = ?,
            anio = ?,
            nro_comprobante = ?,
            cancelado = ?,
            expediente = ?,
            tc = ?,
            tipo_comprobante = ?,
            monto_total = ?,
            monto_pagado_total = ?,
            op_descrip = ?,
            cuenta_pago = ?,
            nombre_cuenta_pago = ?,
            cuit_benef = ?,
            beneficiario = ?,
            fecha_pago = ?,
            tp = ?,
            pago_e = ?,
            descrip_carga = ?
        WHERE id = ?
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
    }

    // Asignar parámetros. s=string, i=integer, d=double. Ajustar según tipos de columna.
    // 'sisisssddsssssssssi' -> 19 parámetros, el último es el ID
    $stmt->bind_param(
        "sisisssddsssssssssi", // TIPOS DE DATOS: fecha_ordenado (s), anio (i), nro_comprobante (s), cancelado (i), expediente (s), tc (s), tipo_comprobante (s), monto_total (d), monto_pagado_total (d), op_descrip (s), cuenta_pago (s), nombre_cuenta_pago (s), cuit_benef (s), beneficiario (s), fecha_pago (s), tp (s), pago_e (s), descrip_carga (s), id (i)
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
        $descrip_carga,
        $id
    );

    $stmt->execute();

    // Verificar si se actualizó alguna fila
    if ($stmt->affected_rows === 0) {
        // Podría ser que el ID no existe o los datos son los mismos
        // O si intentas cambiar año/nro_comprobante a valores ya existentes en otra fila (UNIQUE INDEX)
        // La excepción de duplicado ya lo maneja si se viola la clave única
        $conn->rollback(); // No hubo cambios, no se necesita commit
        echo json_encode(['success' => false, 'mensaje' => 'La Orden de Pago no fue actualizada (posiblemente no se encontraron cambios o el ID no existe).']);
    } else {
        $conn->commit();
        echo json_encode(['success' => true, 'mensaje' => 'Orden de Pago actualizada exitosamente.']);
    }


} catch (mysqli_sql_exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    // Error 1062 es para violación de clave única (ej: cambiar anio/nro_comprobante a uno ya existente)
    if ($e->getCode() == 1062) {
        log_error("Error de duplicidad al actualizar OP: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'Ya existe otra Orden de Pago con el mismo Año y Nro. de Comprobante.']);
    } else {
        log_error("Error de base de datos al actualizar OP: " . $e->getMessage());
        echo json_encode(['success' => false, 'mensaje' => 'Error de base de datos: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    log_error("Error inesperado al actualizar OP: " . $e->getMessage());
    echo json_encode(['success' => false, 'mensaje' => 'Ocurrió un error inesperado: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>