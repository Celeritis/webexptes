<?php
// config_usuarios2.php - version 0001 - 21/06/2025 - 00:25

session_start();

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


?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Gestión de Usuarios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="assets/css/tabulator.min.css" rel="stylesheet" />
  <link href="assets/css/estilos_abm_usr.css" rel="stylesheet" />
</head>
<body>

  <div class="breadcrumb">
    <a href="dashboard.php">Inicio</a> &gt; Configuración de Usuarios
  </div>

  <div class="botonera">
    <div>
      <button onclick="mostrarFormulario()">Nuevo</button>
      <button onclick="editarUsuario()">Editar</button>
      <button onclick="eliminarUsuario()">Eliminar</button>
      <button onclick="exportarUsuarios()">📊 Exportar Excel</button>
    </div>
  </div>

  <div id="panelGrid">
    <div id="tablaUsuarios"></div>
  </div>

  <!-- Modal de formulario de usuario -->
  <div class="modalFormulario" id="modalFormulario">
    <div class="contenido">
      <button class="cerrar-modal" onclick="cerrarFormulario()">×</button>
      <h3 id="tituloFormulario">Nuevo Usuario</h3>
      <form id="formUsuario" enctype="multipart/form-data">

        <input type="hidden" id="id" name="id">
        <input type="hidden" id="pass" name="pass">

        <!-- FOTO -->
        <div class="campo">
          <label for="foto">Foto:</label>
          <input type="file" id="foto" name="foto" accept="image/*">
          <div id="previewFoto" style="margin-top: 10px;"></div>
        </div>

        <!-- USUARIO -->
        <div class="campo">
          <label for="usuario">Usuario:</label>
          <input type="text" id="usuario" name="usuario" required />
        </div>

        <!-- NOMBRE -->
        <div class="campo">
          <label for="nombre">Nombre:</label>
          <input type="text" id="nombre" name="nombre" required />
        </div>

        <!-- ROL -->
        <div class="campo">
          <label for="rol">Rol:</label>
          <select id="rol" name="rol" required>
            <option value="1">Administrador</option>
            <option value="2">Usuario</option>
          </select>
        </div>

        <!-- LOGINPASS -->
        <div class="campo">
          <label for="loginpass">Clave de acceso:</label>
          <input type="text" id="loginpass" name="loginpass" required />
        </div>

        <!-- EMAIL -->
        <div class="campo">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" />
        </div>

        <!-- ACTIVO -->
        <div class="campo">
          <label for="activo">Estado:</label>
          <select id="activo" name="activo" required>
            <option value="1">Habilitado</option>
            <option value="0">Deshabilitado</option>
          </select>
        </div>

        <!-- MOTIVO (solo si activo = 0) -->
        <div class="campo" id="campoMotivo" style="display:none;">
          <label for="motivo">Motivo de deshabilitación:</label>
          <input type="text" id="motivo" name="motivo" />
        </div>

        <!-- DESCRIP -->
        <div class="campo">
          <label for="descrip">Comentarios:</label>
          <textarea id="descrip" name="descrip" rows="3"></textarea>
        </div>

        <!-- BOTONES -->
        <div class="botones">
          <button type="submit">Guardar</button>
          <button type="button" onclick="cerrarFormulario()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
  
  <!-- Mensaje emergente -->
  <div id="mensajeEmergente" class="modal-mensaje" style="display:none;">
    <div class="modal-contenido">
      <h3 class="modal-titulo">WebExptes informa:</h3>
      <p id="mensajeTexto">...</p>
      <button onclick="cerrarMensaje()" class="btn-aceptar">Aceptar</button>
    </div>
  </div>

  <script src="assets/js/xlsx.full.min.js"></script>
  <script src="assets/js/tabulator.min.js"></script>

  <script type="module" src="assets/js/scripts_abm_usuarios.js"></script>

</body>
</html>
