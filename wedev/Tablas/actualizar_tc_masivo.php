<?php
// actualizar_tc_masivo.php - Asigna valores aleatorios válidos a campo TC secuencialmente

set_time_limit(0);

$host = 'localhost:3306';
$user = 'webexptes';
$pass = 'a1234567879A!$webexptes';
$db   = 'webexptes';

$conexion = new mysqli($host, $user, $pass, $db);
if ($conexion->connect_error) {
    die("❌ Conexión fallida: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Valores válidos para TC
$valores = ['FF', 'GA', 'GC', 'GE'];

// Obtener ID máximo
$resultado = $conexion->query("SELECT MAX(id) as max_id FROM detallepagoxcpbte");
$fila = $resultado->fetch_assoc();
$max_id = (int)$fila['max_id'];

if ($max_id < 101) {
    die("⚠️ No hay suficientes filas para actualizar.");
}

// Preparar UPDATE
$stmt = $conexion->prepare("UPDATE detallepagoxcpbte SET tc = ? WHERE id = ?");
if (!$stmt) {
    die("❌ Error en la preparación: " . $conexion->error);
}

// Recorrido secuencial por id
for ($id = 101; $id <= $max_id; $id++) {
    $tc = $valores[array_rand($valores)];
    $stmt->bind_param("si", $tc, $id);
    $stmt->execute();
}

$stmt->close();
$conexion->close();

echo "✅ Se actualizaron aleatoriamente los valores de TC desde ID 101 hasta $max_id.";
?>