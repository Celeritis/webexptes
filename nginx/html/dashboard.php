<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit();
}

define('RUTA_BASE_D', dirname(__DIR__, 2)); 
require_once RUTA_BASE_D . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';
cargarVariablesEntorno();
$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('PASS_RAW');
$clave_encriptacion = getenv('ENCRYPTION_KEY');
require_once RUTA_BASE_D . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'DB.php';

$usuario = trim($_SESSION['usuario']); // Ojo con espacios
$foto = '';
$nombre = '';

try {
    $conn = DB::get();
    $stmt = $conn->prepare("SELECT nombre, foto FROM usuarios WHERE usuario = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
        $nombre = $userData['nombre'] ?? '';
        $foto = $userData['foto'] ?? '';
    }

    $stmt->close();
    DB::close();
} catch (Exception $e) {
    error_log("Error al obtener datos del usuario: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard WebExptes</title>
    <link rel="stylesheet" href="assets/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f9ff;
        }
        .sidebar {
            background: linear-gradient(135deg, #6a11cb 0%, #80bfff 100%) !important;
            color: #fff;
            width: 250px;
            height: 100vh;
            position: fixed;
            overflow-y: auto;
            transition: width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 60px;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }
        .main-content.collapsed {
            margin-left: 60px;
        }
        .sidebar-header {
            padding: 1rem;
            text-align: right;
            background: #2575fc;
        }
        .sidebar-header button {
            background: transparent;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }
        .user-info {
            padding: 1rem;
            text-align: center;
        }
        .user-photo img, .user-photo i {
            font-size: 3rem;
            border-radius: 50%;
            width: 80px;
            height: 80px;
        }
        .user-name {
            margin-top: 0.5rem;
            font-weight: bold;
            font-size: 14px;
            color: white;
        }
        .nav-menu {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            padding: 1rem;
        }
        .nav-item, .nav-subitem {
            padding: 0.6rem 1rem;
            border-radius: 8px;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            cursor: pointer;
            background-color: transparent;
            transition: background-color 0.3s ease;
        }
        .nav-subitem {
            background-color: transparent;
        }
        .nav-subitem:hover,
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        .submenu {
            display: none;
            flex-direction: column;
            padding-left: 1.5rem;
        }
        .menu-toggle.active + .submenu {
            display: flex;
        }
        .nav-subitem.child {
            font-size: 0.95rem;
        }
        .nav-item.logout {
            margin-top: auto;
            background-color: rgba(255, 255, 255, 0.1);
            font-size: 1rem;
            font-weight: 600;
        }
        .sidebar.collapsed .user-info,
        .sidebar.collapsed .nav-item span,
        .sidebar.collapsed .nav-subitem {
            display: none;
        }
        .sidebar.collapsed .nav-item i {
            margin: auto;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: #ffffff;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: 0.3s ease;
            color: #333;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card i {
            font-size: 2rem;
            color: #2575fc;
            margin-bottom: 0.5rem;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button id="toggleBtn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="user-info">
        <div class="user-photo">
            <?php if ($foto): ?>
                <img src="<?php echo $foto; ?>" alt="Foto">
            <?php else: ?>
                <i class="fas fa-circle-user"></i>
            <?php endif; ?>
        </div>
        <div class="user-name">
            <?php echo $nombre ? htmlspecialchars($nombre) : '<span style="font-size:12px;">Usuario sin nombre</span>'; ?>
        </div>
    </div>
    <div class="nav-menu">
        <div class="nav-item menu-toggle"><i class="fas fa-folder-open"></i><span>Expedientes</span></div>
        <div class="submenu">
            <a href="#" class="nav-subitem">Actualizar</a>
            <a href="#" class="nav-subitem">Consultar</a>
            <a href="#" class="nav-subitem">Informes</a>
        </div>
        <div class="nav-item menu-toggle"><i class="fas fa-money-check-alt"></i><span>Ordenes de Pago</span></div>
        <div class="submenu">
            <a href="importar_safyc.php" class="nav-subitem">Importación SAFyC</a>
            <a href="carga_masiva.php" class="nav-subitem">Carga Masiva</a>
            <a href="carga_individual.php" class="nav-subitem">Carga Individual</a>
            <a href="abm_op.php" class="nav-subitem">Actualizar</a>
            <a href="#" class="nav-subitem">Consultar</a>
            <a href="#" class="nav-subitem">Informes</a>
        </div>
        <div class="nav-item menu-toggle"><i class="fas fa-balance-scale"></i><span>Rendiciones</span></div>
        <div class="submenu">
            <a href="#" class="nav-subitem">Balance</a>
            <a href="#" class="nav-subitem">Actualizar</a>
            <a href="#" class="nav-subitem">Consultar</a>
            <a href="#" class="nav-subitem">Informes</a>
        </div>
        <div class="nav-item menu-toggle"><i class="fas fa-cog"></i><span>Configuración</span></div>
        <div class="submenu">
            <a href="config_general.php" class="nav-subitem">General</a>
            <a href="config_usuario.php" class="nav-subitem">Usuario</a>
            <a href="ayuda.php" class="nav-subitem">Ayuda</a>
        </div>
        <a href="logout.php" class="nav-item logout"><i class="fas fa-sign-out-alt"></i><span>Cerrar Sesión</span></a>
    </div>
</div>

<div class="main-content" id="mainContent">
    <div class="cards">
        <a href="expedientes.php" class="card">
            <i class="fas fa-folder-open"></i>
            <h3>Expedientes</h3>
        </a>
        <a href="orden_pago.php" class="card">
            <i class="fas fa-money-check-alt"></i>
            <h3>Ordenes de Pago</h3>
        </a>
        <a href="rendiciones.php" class="card">
            <i class="fas fa-balance-scale"></i>
            <h3>Rendiciones</h3>
        </a>
        <a href="config_general.php" class="card">
            <i class="fas fa-cogs"></i>
            <h3>Configuración General</h3>
        </a>
        <a href="config_usuario.php" class="card">
            <i class="fas fa-user-cog"></i>
            <h3>Configuración Usuario</h3>
        </a>
        <a href="ayuda.php" class="card">
            <i class="fas fa-question-circle"></i>
            <h3>Ayuda</h3>
        </a>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('toggleBtn');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });

    document.querySelectorAll('.menu-toggle').forEach(toggle => {
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');
            const submenu = toggle.nextElementSibling;
            if (submenu && submenu.classList.contains('submenu')) {
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
            }
        });
    });
</script>
</body>
</html>
