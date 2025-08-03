<?php
// listar_tc2.php - versión 002 - 22/05/2025 - 20:12

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'funciones_op.php';

try {
    $conn = conectarDB();
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $query = "SELECT tc, tipo_comprobante FROM tc ORDER BY tc ASC";
    $result = $conn->query($query);

    $datos = [];
    while ($fila = $result->fetch_assoc()) {
        $datos[] = $fila;
    }

    // ✅ Guardar en archivo de log local
    // $logPath = __DIR__ . '/log_tc.txt';
    // file_put_contents($logPath, print_r($datos, true));

    // También podés guardar como JSON puro:
    // file_put_contents($logPath, json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode($datos, JSON_UNESCAPED_UNICODE);

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["error" => "❌ Error al obtener datos: " . $e->getMessage()]);
    error_log("❌ listar_tc.php: " . $e->getMessage());
}
