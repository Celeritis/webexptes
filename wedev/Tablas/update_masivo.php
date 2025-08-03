<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Actualizar tipo_comprobante</title>
</head>
<body>

<?php
$host = 'localhost:3306';
$user = 'webexptes';
$pass = 'a1234567879A!$webexptes';
$db = 'webexptes';

set_time_limit(0);

$conexion = new mysqli($host, $user, $pass, $db);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Obtener el ID máximo de la tabla
$resultado = $conexion->query("SELECT MAX(id) as max_id FROM detallepagoxcpbte");
$fila = $resultado->fetch_assoc();
$max_id = (int)$fila['max_id'];

if ($max_id < 101) {
    echo "No hay suficientes filas para actualizar.";
    exit;
}

// Ejecutar el UPDATE masivo
$sql = "UPDATE detallepagoxcpbte SET cuit_benef = id WHERE id >= 101 AND id <= ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error en la preparación: " . $conexion->error);
}
$stmt->bind_param("i", $max_id);
$stmt->execute();

echo "Filas actualizadas desde ID 101 hasta $max_id.";
$stmt->close();
$conexion->close();
?>

</body>
</html>
