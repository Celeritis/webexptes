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
    <title>Módulo: Ayuda</title>
</head>
<body>
    <h1>Módulo: Ayuda</h1>
    <p>Bienvenido, <?php echo $_SESSION['usuario']; ?>.</p>
</body>
</html>
