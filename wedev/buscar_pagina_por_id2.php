<?php
// buscar_pagina_por_id.php - version 0001  -  08/07/2025 18:34

header('Content-Type: application/json');
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$logDir = __DIR__ . '/logs';
if (!file_exists($logDir)) {
    mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/error_busxid_op.txt';

try {
    // 🔐 Validar sesión
    if (!isset($_SESSION['usuario'])) {
        throw new Exception('No autorizado - Sesión no iniciada');
    }

    require_once 'funciones_op.php';
    $conn = conectarDB(); // mysqli

    $input = json_decode(file_get_contents("php://input"), true);
    $id = intval($input['id']);
    $filters = $input['filters'] ?? [];
    $sorters = $input['sort'] ?? [];
    $pageSize = isset($input['pageSize']) ? intval($input['pageSize']) : 20;

    $where = [];
    $params = [];
    $types = "";

    // Filtros WHERE
    foreach ($filters as $f) {
        $field = $f['field'];
        $type = $f['type'];
        $value = $f['value'];

        if ($type === "like") {
            $where[] = "$field LIKE ?";
            $params[] = "%$value%";
            $types .= "s";
        } elseif ($type === "=") {
            $where[] = "$field = ?";
            $params[] = $value;
            $types .= "s";
        }
    }

    $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

    // ORDER BY
    $orderBy = "";
    if (!empty($sorters)) {
        $orderParts = [];
        foreach ($sorters as $s) {
            $dir = strtoupper($s['dir']) === "DESC" ? "DESC" : "ASC";
            $orderParts[] = $s['field'] . " " . $dir;
        }
        $orderBy = "ORDER BY " . implode(", ", $orderParts);
    }

    $sql = "SELECT id FROM detallepagoxcpbte $whereSQL $orderBy";
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $todos = [];
    while ($row = $result->fetch_assoc()) {
        $todos[] = intval($row['id']);
    }

    $stmt->close();
    $conn->close();

    $index = array_search($id, $todos);
    if ($index === false) {
        echo json_encode(["success" => false, "mensaje" => "ID no encontrado en el contexto actual"]);
        exit;
    }

    $pagina = intval(floor($index / $pageSize)) + 1;
    echo json_encode(["success" => true, "pagina" => $pagina]);

} catch (Exception $e) {
    file_put_contents($logFile, "[" . date("Y-m-d H:i:s") . "] " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "mensaje" => "❌ Error interno: " . $e->getMessage()]);
}
