<?php
//abm_op2.php - versión 0017 - 23/06/25 - 11:00

session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit();
}

ini_set('max_execution_time', 300);
set_time_limit(300);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestión de Órdenes de Pago</title>
  <link href="assets/css/tabulator.min.css" rel="stylesheet">
  <link href="assets/css/abm_op2.css" rel="stylesheet">

</head>
<body>
  <div class="breadcrumb">
    <a href="dashboard.php">Inicio</a> &gt; Órdenes de Pago
  </div>

  <div class="botonera">
    <div>
      <button onclick="mostrarFormulario()">Nuevo</button>
      <button onclick="editarOrden()">Editar</button>
      <button onclick="eliminarOrden()">Eliminar</button>
      <button onclick="imprimirOrden()">Imprimir</button>
      <button onclick="exportarAExcel()">📊 Exportar Excel</button>    
      <button id="btnBusquedaAvanzada" onclick="mostrarBusquedaAvanzada()">🔍 Búsqueda Avanzada</button>
      <button id="btnQuitarFiltros" onclick="quitarFiltros()" style="display:none;">❌ Quitar filtros</button>
    </div>
  </div>

  <div id="panelGrid">
    <div id="tablaOP"></div>
  </div>

  <div id="mensajeEmergente" class="modal-mensaje" style="display: none;">
    <div class="modal-contenido">
    <h3 class="modal-titulo">WebExptes informa:</h3>
    <p id="mensajeTexto">...</p>
    <button onclick="cerrarMensaje()" class="btn-aceptar">Aceptar</button>
    </div>
  </div>

  <div id="mensajeBusquedaAvanzada" class="modal-mensaje" style="display: none;">
    <div class="modal-contenido">
      <h3 class="modal-titulo">WebExptes informa:</h3>
      <p id="mensajeBATexto">🔍 Buscando registros...</p>
      <div id="barraProgresoBA" class="barra-progreso">
        <div class="progreso"></div>
      </div>
      <button onclick="cancelarBusquedaAvanzada()" class="btn-cancelar">Cancelar</button>
    </div>
  </div>

  <div id="modalConfirmacion" class="modal-mensaje" style="display: none;">
    <div class="modal-contenido">
      <h3 class="modal-titulo">⚠️ Confirmar eliminación</h3>
      <div id="textoConfirmacion" style="text-align: left; margin: 15px 0;">
        <p style="margin-bottom: 15px; color: #666;">¿Está seguro que desea eliminar la siguiente orden de pago?</p>
        <div style="background: #f8f9fa; padding: 12px; border-left: 3px solid #dc3545; border-radius: 4px; margin: 10px 0;">
          <div style="display: flex; flex-wrap: wrap; gap: 15px; font-size: 14px;">
            <span><strong>Año:</strong> <span id="eliminar-anio">-</span></span>
            <span><strong>O.P.:</strong> <span id="eliminar-nro">-</span></span>
            <span><strong>Expediente:</strong> <span id="eliminar-expediente">-</span></span>
          </div>
          <div style="margin-top: 8px; font-size: 14px;">
            <strong>Descripción:</strong> <span id="eliminar-descripcion">-</span>
          </div>
        </div>
        <p style="margin-top: 15px; font-size: 0.9em; color: #666;">
          ⚠️ Esta acción no se puede deshacer.
        </p>
      </div>
      <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
        <button onclick="confirmarEliminacion()" class="btn-aceptar" style="background-color: #dc3545;">Eliminar</button>
        <button onclick="cancelarEliminacion()" class="btn-aceptar">Cancelar</button>
      </div>
    </div>
  </div>  

  <div id="modalBusquedaAvanzada" class="modal-busqueda" style="display: none;">  
    <div class="modal-contenido" style="position: relative; max-width: 500px; width: 90%;">
      <button class="cerrar-modal" onclick="cerrarBusquedaAvanzada()">×</button>
      <h3 class="modal-titulo" style="text-align: left; margin-bottom: 15px; color: #2a466b;">🔍 Búsqueda Avanzada</h3>
      <form id="formBusquedaAvanzada" style="display: flex; flex-direction: column; gap: 10px;">
        <!-- Campo Año -->
        <div class="campo-container" style="background-color: #f5f9ff; border-radius: 8px; padding: 12px;">
          <div class="campo">
            <label for="ba-anio" style="min-width: 120px; flex-shrink: 0; color: #3a5b8c; font-weight: 500;">Año:</label>
            <input type="number" id="ba-anio" name="anio" min="1900" max="2100" 
                  style="width: 100px; background-color: #fff; border: 1px solid #d0ddec; padding: 8px 12px; border-radius: 6px;">
          </div>
        </div>
        
        <!-- Campo Orden de Pago -->
        <div class="campo-container" style="background-color: #f5f9ff; border-radius: 8px; padding: 12px;">
          <div class="campo">
            <label for="ba-nro_comprobante" style="min-width: 120px; flex-shrink: 0; color: #3a5b8c; font-weight: 500;">Orden de Pago:</label>
            <input type="number" id="ba-nro_comprobante" name="nro_comprobante" min="1" 
                  style="width: 100px; background-color: #fff; border: 1px solid #d0ddec; padding: 8px 12px; border-radius: 6px;">
          </div>
        </div>
        
        <!-- Campo Expediente -->
        <div class="campo-container" style="background-color: #f5f9ff; border-radius: 8px; padding: 12px;">
          <div class="campo">
            <label for="ba-expediente" style="min-width: 120px; flex-shrink: 0; color: #3a5b8c; font-weight: 500;">Expediente:</label>
            <input type="text" id="ba-expediente" name="expediente" placeholder="Ej: 123/456/78" 
                  style="flex: 1; min-width: 150px; background-color: #fff; border: 1px solid #d0ddec; padding: 8px 12px; border-radius: 6px;">
          </div>
        </div>
        
        <!-- Rango de fechas -->
        <div class="campo-container" style="background-color: #f5f9ff; border-radius: 8px; padding: 12px;">
          <div class="campo-rango-fechas campo-error" id="campo-rango-fechas">
            <label style="min-width: 120px; color: #3a5b8c; font-weight: 500;">Rango de fechas de pago:</label>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
              <input type="date" id="ba-fecha-desde" name="fecha_desde" 
                    style="flex: 1; min-width: 150px; background-color: #fff; border: 1px solid #d0ddec; padding: 8px 12px; border-radius: 6px;">
              <span style="color: #6c757d;">a</span>
              <input type="date" id="ba-fecha-hasta" name="fecha_hasta" 
                    style="flex: 1; min-width: 150px; background-color: #fff; border: 1px solid #d0ddec; padding: 8px 12px; border-radius: 6px;">
            </div>
          </div>
        </div>
        
        <!-- Botones -->
        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
          <button type="button" onclick="aplicarBusquedaAvanzada()" 
                  style="background-color: #2a466b; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; transition: background-color 0.2s;">
            Buscar
          </button>
          <button type="button" onclick="cerrarBusquedaAvanzada()" 
                  style="background-color: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; transition: background-color 0.2s;">
            Cancelar
          </button>
        </div>
      </form>
    </div>
  </div>

  <div class="modalFormulario" id="modalFormulario">
    <div class="contenido">
      <button class="cerrar-modal" onclick="cerrarFormulario()">×</button>
      <h3 id="tituloFormulario">Nueva Orden de Pago</h3>

      <form id="formOP">
        <input type="hidden" id="id" name="id">

        <div class="campo"><label for="anio">Año:</label><input type="number" id="anio" name="anio" required></div>
        <div class="campo"><label for="nro_comprobante">Orden de Pago:</label><input type="text" id="nro_comprobante" name="nro_comprobante" required></div>
        <div class="campo"><label for="expediente">Expediente:</label><input type="text" id="expediente" name="expediente" required></div>
        <div class="campo"><label for="tc">TC:</label><select id="tc" name="tc"></select></div>
        <div class="campo"><label for="monto_total">Monto Total:</label><input type="text" id="monto_total" name="monto_total" pattern="^\d*\.?\d{0,2}$" required></div>
        <div class="campo"><label for="monto_pagado_total">Monto Pagado:</label><input type="text" id="monto_pagado_total" name="monto_pagado_total" pattern="^\d*\.?\d{0,2}$" required></div>
        <div class="campo"><label for="op_descrip">Descripción:</label><input type="text" id="op_descrip" name="op_descrip"></div>

        <div class="campo"><label for="nombre_cuenta_pago">Nombre de Cuenta:</label><input type="text" id="nombre_cuenta_pago" name="nombre_cuenta_pago"></div>
        <div class="campo"><label for="cuenta_pago">Cuenta Pago:</label><input type="text" id="cuenta_pago" name="cuenta_pago" inputmode="numeric" pattern="[0-9]*"></div>
        <div class="campo"><label for="cuit_benef">CUIT:</label><input type="text" id="cuit_benef" name="cuit_benef" inputmode="numeric" pattern="[0-9]*"></div>
        <div class="campo"><label for="beneficiario">Beneficiario:</label><input type="text" id="beneficiario" name="beneficiario"></div>
        <div class="campo"><label for="fecha_ordenado">Fecha de Ordenado:</label><input type="date" id="fecha_ordenado" name="fecha_ordenado"></div>
        <div class="campo"><label for="fecha_pago">Fecha de Pago:</label><input type="date" id="fecha_pago" name="fecha_pago"></div>

        <div class="campo"><label for="tp">TP:</label><input type="text" id="tp" name="tp"></div>
        <div class="campo"><label for="pago_e">Pago E.:</label><input type="text" id="pago_e" name="pago_e" inputmode="numeric" pattern="[0-9]*"></div>
        <div class="campo"><label for="descrip_carga">Observaciones:</label><textarea id="descrip_carga" name="descrip_carga" rows="3"></textarea></div>

        <div class="botones">
          <button type="submit" style="background: #2575fc; color: white; border: none; padding: 8px 16px; border-radius: 6px;">Guardar</button>
          <button type="button" onclick="cerrarFormulario()" style="background: #2575fc; color: white; border: none; padding: 8px 16px; border-radius: 6px;">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal de Confirmación Sí / No -->
  <div id="modal-sino" class="modal-sino-background">
    <div class="modal-sino-contenido">
      <button class="modal-sino-cerrar" onclick="cerrarModalSino()">×</button>
      <h3 class="modal-sino-titulo">Confirmación</h3>
      <div id="texto-modal-sino" class="modal-sino-texto"></div>
      <div class="modal-sino-buttons">
        <button id="btn-sino-si" class="modal-sino-btn-primary">Sí</button>
        <button id="btn-sino-no" class="modal-sino-btn-secondary">No</button>
      </div>
    </div>
  </div>

  <!-- Modal de “Actualizando datos” -->
  <div id="modal-temporizado" class="modal-temporizado-fondo">
    <div class="modal-temporizado-contenido">
      <h3 class="modal-temporizado-titulo">Actualizando datos</h3>
      <div class="modal-temporizado-texto">Por favor, espere…</div>
    </div>
  </div>


  <!--  Modal de Mensaje Dinámico Temporal -->
  <div id="modal-mensajetempo-temporal" class="modal-mensajetempo-fondo">
    <div class="modal-mensajetempo-contenido">
      <div id="modal-mensajetempo-contenido" class="modal-mensajetempo-texto"></div>
    </div>
  </div>


  <script src="assets/js/xlsx.full.min.js"></script>
  <script src="assets/js/tabulator.min.js"></script>
  
  <script type="module" src="assets/js/scripts_abm_op.js"></script>

</body>
</html>