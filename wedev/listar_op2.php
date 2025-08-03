<?php
// listar_op2.php - versión 0005 - 01/07/2025 -15:00

// ===== CONFIGURACIÓN OPTIMIZADA PARA EXPORTACIONES =====
// Detectar si es una solicitud de exportación (tamaño grande)
$input = json_decode(file_get_contents("php://input"), true);
$size = isset($input['size']) ? (int)$input['size'] : 2400;

// **CORRECCIÓN CRÍTICA**: Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// **CORRECCIÓN**: Enviar headers antes de cualquier otra salida
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Si es una exportación (tamaño > 5000), aplicar configuraciones especiales
if ($size > 5000) {
    // Aumentar límites para exportaciones
    ini_set('memory_limit', '512M');           // Aumentar memoria disponible
    ini_set('max_execution_time', 600);        // 10 minutos máximo
    ini_set('max_input_time', 120);            // 2  minutos para entrada
    
    // Optimizaciones para MySQL
    ini_set('mysql.connect_timeout', 120);
    ini_set('default_socket_timeout', 120);
    
    // Flush para enviar headers inmediatamente
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request();
    } else {
        ignore_user_abort(true);
        flush();
    }
}

session_start();
require_once 'funciones_op.php';

try {
    $page = isset($_POST['page']) ? (int)$_POST['page'] : (isset($input['page']) ? (int)$input['page'] : 1);
    $size = isset($_POST['size']) ? (int)$_POST['size'] : (isset($input['size']) ? (int)$input['size'] : 2400);
    
    // Limitar tamaño máximo para evitar abusos
    if ($size > 100000) {
        $size = 100000; // Máximo 100k registros por solicitud
    }
    
    // Obtener filtros - corregir clave 'filters' en lugar de 'filter'
    $filters = [];
    if (isset($input['filters']) && is_array($input['filters'])) {
        $filters = $input['filters'];
    } elseif (isset($input['filter']) && is_array($input['filter'])) {
        $filters = $input['filter'];
    }

    $offset = ($page - 1) * $size;

    // INSERTAR AQUÍ EL LOG DE DEPURACIÓN
    //error_log("Paginación: Página={$page}, Registros por página={$size}, Offset={$offset}");
    
    $conn = conectarDB();
    
    // Optimizaciones de conexión MySQL para consultas grandes
    if ($size > 5000) {
        $conn->query("SET SESSION sql_buffer_result = 1");      // Buffer resultados
        $conn->query("SET SESSION query_cache_type = OFF");     // Deshabilitar cache para consultas grandes
        $conn->query("SET SESSION tmp_table_size = 268435456"); // 256MB para tablas temporales
        $conn->query("SET SESSION max_heap_table_size = 268435456"); // 256MB para tablas en memoria
    }

    // Construcción de filtros WHERE
    $where = [];
    $params = [];
    $types = '';

    foreach ($filters as $filter) {
        if (isset($filter['field']) && isset($filter['value']) && trim($filter['value']) !== '') {
            $field = $filter['field'];
            $value = trim($filter['value']);
            //$value = '%' . trim($filter['value']) . '%';

            
            // Escapar nombres de campos para evitar inyección SQL
            $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
            
            //$where[] = "`$field` LIKE ?";
            //$params[] = $value;
            //$types .= 's';

            // INICIO DEL MENSAJE DE DEPURACIÓN
             error_log("Depuración de filtro: Campo='" . $field . "', Valor='" . $value . "'");
            // FIN DEL MENSAJE DE DEPURACIÓN

            //  Determinar el operador según el campo
            if ($field === 'nro_comprobante') {
            //if ($field === 'nro_comprobante' || $field === 'expediente') { 
                    // Búsqueda más flexible para nro_comprobante y expediente
                    // Considerar: valor exacto, con trim, y con padding izquierdo
                     $where[] = "(
                            `$field` = ? OR 
                            TRIM(`$field`) = ? OR 
                            CAST(`$field` AS UNSIGNED) = CAST(? AS UNSIGNED)
                        )";
                    $params[] = $value;
                    $params[] = $value;
                    $params[] = $value;
                    $types .= 'sss';
            } else {
                    // Para otros campos: búsqueda parcial (LIKE)
                    $where[] = "`$field` LIKE ?";
                    $params[] = '%' . $value . '%';
                    $types .= 's';
            }

        }
    }

    $sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";

    // ===== OBTENER TOTAL DE REGISTROS =====
    $stmtTotal = $conn->prepare("SELECT COUNT(*) FROM detallepagoxcpbte $sqlWhere");
    if (!empty($params)) {
        $stmtTotal->bind_param($types, ...$params);
    }
    $stmtTotal->execute();
    $stmtTotal->bind_result($total);
    $stmtTotal->fetch();
    $stmtTotal->close();

    // Log para debug en exportaciones grandes
    if ($size > 5000) {
        error_log("Exportación solicitada: Total={$total}, Size={$size}, Page={$page}, Offset={$offset}");
    }

    // ===== OBTENER REGISTROS PAGINADOS =====
    $sql = "SELECT id,
                IF(cancelado = 1, 'Si', 'No') AS cancelado,
                anio, nro_comprobante, expediente, tc, tipo_comprobante,
                monto_total, monto_pagado_total,
                op_descrip, cuenta_pago, nombre_cuenta_pago, cuit_benef, beneficiario,
                fecha_ordenado, fecha_pago, tp, pago_e, descrip_carga
            FROM detallepagoxcpbte
            $sqlWhere
            ORDER BY fecha_pago DESC
            LIMIT ? OFFSET ?";

// ORDER BY id ASC, fecha_pago DESC

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conn->error);
    }

    $typesFull = $types . 'ii';
    $paramsFull = array_merge($params, [$size, $offset]);
    $stmt->bind_param($typesFull, ...$paramsFull);

    if (!$stmt->execute()) {
        throw new Exception("Error ejecutando consulta: " . $stmt->error);
    }

    $resultado = $stmt->get_result();
    if (!$resultado) {
        throw new Exception("Error obteniendo resultados: " . $stmt->error);
    }

    // ===== PROCESAMIENTO OPTIMIZADO DE DATOS =====
    $data = [];
    $contador = 0;
    
    while ($fila = $resultado->fetch_assoc()) {
        // **CORRECCIÓN**: Procesar encoding UTF-8 de manera más robusta
        foreach ($fila as $k => $v) {
            if (is_string($v) && !empty($v)) {
                // Limpiar caracteres de control y espacios problemáticos
                $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $v);
                $v = trim($v);
                
                if (!mb_check_encoding($v, 'UTF-8')) {
                    $fila[$k] = mb_convert_encoding($v, 'UTF-8', 'auto');
                } else {
                    $fila[$k] = $v;
                }
            }
        }
        
        // Formatear montos - más eficiente para exportaciones
        if ($size > 5000) {
            // Para exportaciones, mantener formato numérico simple
            $fila['monto_total'] = number_format((float)$fila['monto_total'], 2, '.', '');
            $fila['monto_pagado_total'] = number_format((float)$fila['monto_pagado_total'], 2, '.', '');
        } else {
            // Para visualización normal, usar formato local
            $fila['monto_total'] = number_format((float)$fila['monto_total'], 2, ',', '.');
            $fila['monto_pagado_total'] = number_format((float)$fila['monto_pagado_total'], 2, ',', '.');
        }

        // Formatear fechas de manera más eficiente
        if (!empty($fila['fecha_ordenado']) && $fila['fecha_ordenado'] !== '0000-00-00') {
            $timestamp = strtotime($fila['fecha_ordenado']);
            $fila['fecha_ordenado'] = $timestamp ? date('d/m/Y', $timestamp) : '';
        } else {
            $fila['fecha_ordenado'] = '';
        }

        if (!empty($fila['fecha_pago']) && $fila['fecha_pago'] !== '0000-00-00') {
            $timestamp = strtotime($fila['fecha_pago']);
            $fila['fecha_pago'] = $timestamp ? date('d/m/Y', $timestamp) : '';
        } else {
            $fila['fecha_pago'] = '';
        }
        
        $data[] = $fila;
        $contador++;
        
        // Para exportaciones grandes, liberar memoria periódicamente
        if ($size > 5000 && $contador % 1000 === 0) {
            // Forzar liberación de memoria cada 1000 registros
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
            
            // Log de progreso
            error_log("Procesados {$contador} registros de {$size} solicitados");
        }
    }
    
    $stmt->close();
    $conn->close();

    // ===== PREPARAR RESPUESTA =====
    $last_page = $total > 0 ? ceil($total / $size) : 1;
    
    $respuesta = [
        "data" => $data,
        "total" => $total,
        "last_page" => $last_page,
        "current_page" => $page,
        "per_page" => $size,
        "from" => $offset + 1,
        "to" => min($offset + count($data), $total)
    ];

    // Para exportaciones grandes, añadir información adicional
    if ($size > 5000) {
        $respuesta["export_info"] = [
            "memory_peak" => memory_get_peak_usage(true),
            "memory_limit" => ini_get('memory_limit'),
            "execution_time" => round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2),
            "records_processed" => count($data)
        ];
        
        error_log("Exportación completada: " . count($data) . " registros, Memoria pico: " . 
                 round(memory_get_peak_usage(true)/1024/1024, 2) . "MB");
    }

    // ===== CODIFICACIÓN JSON OPTIMIZADA =====
    $json_flags = JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR;
    
    // Para exportaciones grandes, usar codificación más conservadora
    if ($size > 5000) {
        $json_flags |= JSON_UNESCAPED_SLASHES;
    }
    
    $json = json_encode($respuesta, $json_flags);
    
    if ($json === false) {
        $error_msg = json_last_error_msg();
        error_log("Error JSON: " . $error_msg);
        throw new Exception("Error al codificar JSON: " . $error_msg);
    }

    // **CORRECCIÓN CRÍTICA**: Asegurar que no hay salida adicional
    echo $json;
    exit(); // Terminar ejecución inmediatamente

} catch (Exception $e) {
    // Log del error
    error_log("Error en listar_op2.php: " . $e->getMessage() . " - Línea: " . $e->getLine());
    
    $error_response = [
        "data" => [],
        "total" => 0,
        "last_page" => 1,
        "current_page" => 1,
        "error" => true,
        "mensaje" => $e->getMessage(),
        "debug_info" => [
            "memory_usage" => memory_get_usage(true),
            "memory_peak" => memory_get_peak_usage(true),
            "time_limit" => ini_get('max_execution_time'),
            "memory_limit" => ini_get('memory_limit')
        ]
    ];
    
    echo json_encode($error_response, JSON_UNESCAPED_UNICODE);
    exit(); // Terminar ejecución inmediatamente
} finally {
    // Limpieza final
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Liberar memoria explícitamente para exportaciones grandes
    if (isset($size) && $size > 5000) {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}
