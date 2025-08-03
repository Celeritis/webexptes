<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Insertar filas</title>
</head>
<body>

<?php
// insertar_1000_filas.php - Inserta 1000 filas con datos aleatorios en detallepagoxcpbte

$host = 'localhost:3306';
$user = 'webexptes';
$pass = 'a1234567879A!$webexptes';
$db = 'webexptes';

set_time_limit(0); // sin límite de tiempo

$conexion = new mysqli($host, $user, $pass, $db);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Lista fija de beneficiarios
$beneficiarios = [
    "DEPARTAMENTO GENERAL DE POLICIA", "DESTACAMENTOS POLICIALES", "POLICIA ADICIONAL",
    "CAJA POPULAR DE AHORROS DE LA PROVINCIA DE TUCUMAN", "DYMA ELECTROCOMERCIAL S.R.L.",
    "EDENRED ARGENTINA S.A.", "APEL S.R.L.", "TRAILINGSAT S.A.", "FERCOR S.A.S.",
    "EL ABASTO MATERIALESS.R.L.", "YOUNES, MARCELO EDUARDO"
];

$sql = "INSERT INTO detallepagoxcpbte (
    anio, nro_comprobante, expediente, monto_total, monto_pagado_total,
    beneficiario, fecha_pago, fecha_ordenado, cancelado
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
    die("Error en la preparación: " . $conexion->error);
}

for ($i = 0; $i < 10000; $i++) {
    $anio = 2025;
    $nro_comprobante = rand(30000, 99999);
    $expediente = rand(1000, 9999) . '/' . rand(100, 300) . '/25';
    $monto_total = round(rand(10000, 100000000) + rand() / getrandmax(), 2);
    $monto_pagado_total = $monto_total;
    $beneficiario = $beneficiarios[array_rand($beneficiarios)];
    $fecha_pago = date('Y-m-d', strtotime('+'.rand(0, 90).' days', strtotime('2025-01-01')));
    $fecha_ordenado = date('Y-m-d', strtotime($fecha_pago . ' -' . rand(0, 7) . ' days'));
    $cancelado = 1;

    $stmt->bind_param("iisddsssi", $anio, $nro_comprobante, $expediente, $monto_total, $monto_pagado_total, $beneficiario, $fecha_pago, $fecha_ordenado, $cancelado);
    $stmt->execute();
}

echo "Insertadas 1000 filas correctamente.";
$stmt->close();
$conexion->close();
?>

</body>
</html>
