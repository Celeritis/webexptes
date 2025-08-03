
<?php
session_start();
$usuario = $_SESSION['usuario'] ?? 'Usuario';
session_unset(); // borra variables de sesión
session_destroy(); // destruye sesión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - WebExptes</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5; /* gris suave */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb); /* azul muy suave */
            border-radius: 16px;
            padding: 40px 60px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #0d47a1;
        }
        p {
            font-size: 1.1rem;
            margin-top: 10px;
            color: #1a237e;
        }
        .volver {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            font-size: 1rem;
            text-decoration: none;
            color: #0d47a1;
            border: 1px solid #0d47a1;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .volver:hover {
            background-color: #0d47a1;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>¡Gracias por usar WebExptes, <?= htmlspecialchars($usuario) ?>!</h2>
        <p>Te esperamos en la próxima jornada de trabajo. ¡Que tengas un excelente día!</p>
        <a class="volver" href="index.html">Volver al Inicio</a>
    </div>
</body>
</html>
