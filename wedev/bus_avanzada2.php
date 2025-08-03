<?php
// bus_avanzada2.php - 04/06/2025 - 19:44

session_start();
if (!isset($_SESSION['usuario'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit();
}

ini_set('max_execution_time', 300);
set_time_limit(300);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require_once 'funciones_op.php';
$conn = conectarDB();

header('Content-Type: application/json');

try {
    $datos = json_decode(file_get_contents('php://input'), true);
    
    if (!$datos) {
        throw new Exception('Datos de búsqueda no válidos');
    }

    // Paginación
    $page = isset($datos['page']) ? (int)$datos['page'] : 1;
    $size = 500;
    $offset = ($page - 1) * $size;

    // Construcción de la consulta SQL con filtros
    $sql = "SELECT id, 
                   anio, nro_comprobante, expediente, tc, tipo_comprobante, 
                   monto_total, monto_pagado_total, op_descrip, cuenta_pago, nombre_cuenta_pago, 
                   cuit_benef, beneficiario, fecha_ordenado, fecha_pago, tp, pago_e, descrip_carga 
            FROM detallepagoxcpbte WHERE 1=1";

    $params = [];
    $types = "";

    if (!empty($datos['nro_comprobante'])) {
        $sql .= " AND nro_comprobante = ?";
        $params[] = $datos['nro_comprobante'];
        $types .= "s";

        if (!empty($datos['anio'])) {
            $sql .= " AND anio = ?";
            $params[] = $datos['anio'];
            $types .= "i";
        }
    } elseif (!empty($datos['expediente'])) {
        $sql .= " AND expediente = ?";
        $params[] = $datos['expediente'];
        $types .= "s";
    } else {
        if (!empty($datos['anio'])) {
            $sql .= " AND anio = ?";
            $params[] = $datos['anio'];
            $types .= "i";
        }

        if (!empty($datos['fecha_desde']) && !empty($datos['fecha_hasta'])) {
            $sql .= " AND fecha_pago BETWEEN ? AND ?";
            $params[] = $datos['fecha_desde'];
            $params[] = $datos['fecha_hasta'];
            $types .= "ss";
        }
    }

    // Obtener el total de registros
    $sqlTotal = "SELECT COUNT(*) FROM detallepagoxcpbte WHERE 1=1 " . substr($sql, strpos($sql, "AND"));
    $stmtTotal = $conn->prepare($sqlTotal);
    if (!empty($params)) {
        $stmtTotal->bind_param($types, ...$params);
    }
    $stmtTotal->execute();
    $stmtTotal->bind_result($total);
    $stmtTotal->fetch();
    $stmtTotal->close();

    $last_page = $total > 0 ? ceil($total / $size) : 1;

    // Aplicar ordenación y paginación
    $sql .= " ORDER BY anio DESC, nro_comprobante DESC LIMIT ? OFFSET ?";
    $params[] = $size;
    $params[] = $offset;
    $types .= "ii";

    // Ejecutar consulta
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Procesar datos con formatos corregidos
    $data = [];
    while ($fila = $resultado->fetch_assoc()) {
        // Formatear montos
        $fila['monto_total'] = number_format((float)$fila['monto_total'], 2, ',', '.');
        $fila['monto_pagado_total'] = number_format((float)$fila['monto_pagado_total'], 2, ',', '.');

        // Formatear fechas
        if (!empty($fila['fecha_ordenado']) && $fila['fecha_ordenado'] !== '0000-00-00') {
            $fila['fecha_ordenado'] = date('d/m/Y', strtotime($fila['fecha_ordenado']));
        } else {
            $fila['fecha_ordenado'] = '';
        }

        if (!empty($fila['fecha_pago']) && $fila['fecha_pago'] !== '0000-00-00') {
            $fila['fecha_pago'] = date('d/m/Y', strtotime($fila['fecha_pago']));
        } else {
            $fila['fecha_pago'] = '';
        }

        $data[] = $fila;
    }

    $stmt->close();
    $conn->close();

    echo json_encode([
        "data" => $data,
        "total" => $total,
        "last_page" => $last_page,
        "current_page" => $page,
        "per_page" => $size
    ], JSON_UNESCAPED_UNICODE);
    
    exit();
} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error: " . $e->getMessage()]);
    exit();
}
