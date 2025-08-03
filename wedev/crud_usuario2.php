<?php
// crud_usuario2.php - version 0001 - 21/06/2025 - 17:14

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 1) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
      <meta charset="UTF-8">
      <title>Acceso denegado</title>
      <style>
        body {
          margin: 0;
          padding: 0;
          font-family: 'Segoe UI', sans-serif;
          background-color: rgba(0, 0, 0, 0.5);
        }
        .modal {
          position: fixed;
          top: 50%; left: 50%;
          transform: translate(-50%, -50%);
          background: white;
          padding: 30px 40px;
          border-radius: 10px;
          box-shadow: 0 0 20px rgba(0,0,0,0.3);
          text-align: center;
        }
        .modal h2 {
          margin: 0 0 10px;
          font-size: 1.2rem;
          color: #dc3545;
        }
        .modal p {
          color: #333;
        }
      </style>
    </head>
    <body>
      <div class="modal">
        <h2>⛔ Acceso denegado</h2>
        <p>Este módulo es solo para administradores.</p>
        <p>Redirigiendo al panel principal...</p>
      </div>

      <script>
        setTimeout(() => {
          window.location.href = 'dashboard.php';
        }, 3000);
      </script>
    </body>
    </html>
    <?php
    exit;
}

require_once 'funciones_op.php';
$conn = conectarDB();

$input = json_decode(file_get_contents('php://input'), true);
$accion = $input['accion'] ?? '';

try {
    switch ($accion) {
        case 'listar':
            $sql = "SELECT id, IF(rol=1,'Administrador','Usuario') AS rol, usuario, nombre, email, IF(activo=1, 'Habilitado','Deshabilitado') AS activo, fecha_creacion, fecha_bloqueo, descrip, foto 
            FROM usuarios 
            WHERE (id <> 1) ORDER BY usuario ASC
            LIMIT 2400";

            $res = $conn->query($sql);
            $datos = [];

            while ($fila = $res->fetch_assoc()) {
                if (!is_null($fila['foto'])) {
                    $fila['foto'] = [
                        'type' => 'Buffer',
                        'data' => array_values(unpack("C*", $fila['foto']))
                    ];
                }
                $datos[] = $fila;
            }

            echo json_encode(['success' => true, 'data' => $datos]);
            break;

        case 'crear':
            $stmt = $conn->prepare("INSERT INTO usuarios 
                (rol, usuario, nombre, loginpass, pass, email, activo, fecha_bloqueo, descrip, foto) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $fotoBin = isset($input['foto']) ? base64_decode($input['foto']) : null;
            $stmt->bind_param(
                "isssssissb",
                $input['rol'],
                $input['usuario'],
                $input['nombre'],
                $input['loginpass'],
                $input['pass'],
                $input['email'],
                $input['activo'],
                $input['fecha_bloqueo'],
                $input['descrip'],
                $fotoBin
            );

            // como bind_param no permite directamente tipo blob nulo, usamos send_long_data:
            if (!is_null($fotoBin)) {
                $stmt->send_long_data(9, $fotoBin); // 9 = índice de la columna 'foto'
            }

            $stmt->execute();
            echo json_encode(['success' => true, 'message' => '✅ Usuario creado']);
            break;

        case 'editar':
            $stmt = $conn->prepare("UPDATE usuarios 
                SET rol=?, nombre=?, email=?, activo=?, fecha_bloqueo=?, descrip=?, foto=? 
                WHERE id=?");

            $fotoBin = isset($input['foto']) ? base64_decode($input['foto']) : null;

            $stmt->bind_param(
                "ississbi",
                $input['rol'],
                $input['nombre'],
                $input['email'],
                $input['activo'],
                $input['fecha_bloqueo'],
                $input['descrip'],
                $fotoBin,
                $input['id']
            );

            if (!is_null($fotoBin)) {
                $stmt->send_long_data(6, $fotoBin); // 6 = índice de columna 'foto'
            }

            $stmt->execute();
            echo json_encode(['success' => true, 'message' => '📝 Usuario actualizado']);
            break;

        case 'eliminar':
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
            $stmt->bind_param("i", $input['id']);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => '🗑️ Usuario eliminado']);
            break;

        default:
            throw new Exception('⚠️ Acción inválida');
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '🚫 Error: ' . $e->getMessage()]);
}
