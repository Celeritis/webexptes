<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo: Ordenes de Pago</title>
</head>
<body>
    <h1>Módulo: Ordenes de Pago</h1>
    <p>Bienvenido, <?php echo $_SESSION['usuario']; ?>.</p>
</body>
</html>
