<?php
session_start();
if (!isset($_SESSION['usuario']) && isset($_GET['user'])) {
    $_SESSION['usuario'] = htmlspecialchars($_GET['user']);
}

if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit();
}

$usuario = $_SESSION['usuario'];
$fecha = htmlspecialchars($_GET['datetime']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida - WebExptes</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .welcome-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        h1 {
            color: #2575fc;
            margin-bottom: 1rem;
        }
        .user-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        .info-text {
            color: #666;
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h1>¡Bienvenido a WebExptes!</h1>
        <div class="user-info">
            <p class="info-text">Usuario: <?php echo $usuario; ?></p>
            <p class="info-text">Fecha y hora de acceso: <?php echo $fecha; ?></p>
        </div>
        <p>Redirigiendo al dashboard...</p>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = 'dashboard.php';
        }, 3000);
    </script>
</body>
</html>
