/** scripts_abm_usuarios.js                   */
/**  script principal de config_usuarios2.php */

import { mostrarMensaje, cerrarModal } from './mensajes_usr.js';
import { configurarPaginacion, traducirBotonesPaginacion } 
        from './paginacion_usuario.js';

import {  mostrarFormulario, cerrarFormulario, cerrarMensaje 
       } from './utilidades_crud_usr.js';

import {  limpiarTodosLosErrores, mostrarError,
          configurarValidacionesFormularioUsuario 
        } from './validaciones_crud_usr.js';

import { manejarEventos } from './eventos_abm_usuarios.js';

window.mostrarMensaje = mostrarMensaje;
window.cerrarModal = cerrarModal;
window.configurarPaginacion = configurarPaginacion;
window.traducirBotonesPaginacion = traducirBotonesPaginacion;

window.mostrarFormulario = mostrarFormulario;
window.cerrarFormulario = cerrarFormulario;
window.mostrarError = mostrarError;
window.limpiarTodosLosErrores = limpiarTodosLosErrores;
window.configurarValidacionesFormularioUsuario = configurarValidacionesFormularioUsuario;

window.editarUsuario = editarUsuario;
window.cerrarMensaje = cerrarMensaje;

// Inicialización de variables globales

window.datosSeleccionados = null;
window.tabla = null; 
window.busquedaEnCurso = false;
window.controladorAbort = null;
window.todosLosDatos = [];
window.continuar = true;

manejarEventos();

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', () => {
  iniciarTabla();
  document.getElementById('formUsuario').addEventListener('submit', guardarUsuario);
});

export function iniciarTabla() {
  window.tabla = new Tabulator('#tablaUsuarios', {
    selectable: 1,
    layout: "fitDataStretch",
    pagination: 'local',
    paginationSize: 20,
    paginationSizeSelector: [20, 50, 100, 500],
    paginationButtonCount: 5,
    locale: true,
    langs: {
          "es-es": {
            "pagination": {
              "page_size": "Filas"
            }
          }
    },
  columns: [
  {
    title: 'Foto',
    field: 'foto',
    width: 70,
    hozAlign: 'center',
    formatter: cell => {
      const blob = cell.getValue();
      if (!blob) return "Sin foto";
      const base64 = btoa(
        new Uint8Array(blob.data).reduce((acc, byte) => acc + String.fromCharCode(byte), '')
      );
      return `<img src="data:image/jpeg;base64,${base64}" style="width:40px;height:40px;border-radius:5px;">`;
    }
  },
      { title: 'ID', field: 'id', width: 40, hozAlign: "center" },
      { title: 'Rol', field: 'rol', headerFilter: "input", width: 110 },
      { title: 'Usuario', field: 'usuario', headerFilter: "input", width: 150 },
      { title: 'Nombre', field: 'nombre', headerFilter: "input", width: 220 },
      { title: 'Email', field: 'email' },
      { title: 'Activo', field: 'activo' },
      { title: 'Fecha Alta', field: 'fecha_creacion', 
        formatter: cell => formatearFecha(cell.getValue()),
        width: 160 },
      { title: 'Fecha Bloqueo', field: 'fecha_bloqueo',
        formatter: cell => formatearFecha(cell.getValue()),
        width: 160 },
      { title: 'Comentarios', field: 'descrip', width: 220 }
    ],
    rowSelected: row => window.datosSeleccionados = row.getData(),
    rowDeselected: () => window.datosSeleccionados = null,
  });

  window.tabla.on("tableBuilt", () => {
    configurarPaginacion(tabla);
  });

  listarUsuarios();
}

export function formatearFecha(fechaStr) {
  if (!fechaStr) return '';
  const fecha = new Date(fechaStr);
  if (isNaN(fecha)) return fechaStr;

  const pad = n => n.toString().padStart(2, '0');

  return `${pad(fecha.getDate())}/${pad(fecha.getMonth() + 1)}/${fecha.getFullYear()} ${pad(fecha.getHours())}:${pad(fecha.getMinutes())}:${pad(fecha.getSeconds())}`;
}


export function listarUsuarios() {
  fetch('crud_usuario.php', {
    method: 'POST',
    body: JSON.stringify({ accion: 'listar' })
  })
  .then(res => res.json())
  .then(json => {
    if (json.success) {
        window.tabla.setData(json.data);
    } else {
      mostrarMensaje(json.message || 'Error al listar usuarios');
    }
  })
  .catch(() => mostrarMensaje('🔌 Error de conexión con el servidor'));
}

function limpiarFormulario() {
  document.getElementById('formUsuario').reset();
  document.getElementById('id').value = '';
}

// FUNCIONES PRINCIPALES DEL CRUD

export function editarUsuario() {
  if (!window.datosSeleccionados) {
    return mostrarMensaje('⚠️ Debe seleccionar un usuario');
  }

  document.getElementById('tituloFormulario').textContent = 'Editar Usuario';
  const campos = ['id', 'rol', 'usuario', 'nombre', 'email', 'activo'];

  campos.forEach(campo => {
    document.getElementById(campo).value = window.datosSeleccionados[campo];
  });

  document.getElementById('usuario').disabled = true;
  document.getElementById('modalFormulario').style.display = 'block';
}

export function guardarUsuario(e) {
  e.preventDefault();

  const datos = Object.fromEntries(new FormData(e.target));
  datos.accion = datos.id ? 'editar' : 'crear';

  if (!datos.usuario || !datos.nombre || !datos.rol) {
    return mostrarMensaje('⚠️ Complete los campos obligatorios');
  }

  fetch('crud_usuario.php', {
    method: 'POST',
    body: JSON.stringify(datos)
  })
  .then(res => res.json())
  .then(json => {
    if (json.success) {
      listarUsuarios();
      cerrarFormulario();
    }
    mostrarMensaje(json.message);
  })
  .catch(() => mostrarMensaje('❌ No se pudo guardar el usuario'));
}

export function eliminarUsuario() {
  if (!window.datosSeleccionados) {
    return mostrarMensaje('⚠️ Debe seleccionar un usuario a eliminar');
  }

  const confirmado = confirm(`¿Seguro que desea eliminar a ${window.datosSeleccionados.nombre}?`);
  if (!confirmado) return;

  fetch('crud_usuario.php', {
    method: 'POST',
    body: JSON.stringify({ accion: 'eliminar', id: window.datosSeleccionados.id })
  })
  .then(res => res.json())
  .then(json => {
    if (json.success) {
      listarUsuarios();
      window.datosSeleccionados = null;
    }
    mostrarMensaje(json.message);
  })
  .catch(() => mostrarMensaje('🚫 Falló al eliminar el usuario'));
}

export function exportarUsuarios() {
  window.tabla.download('xlsx', 'usuarios.xlsx', { sheetName: 'Usuarios' });
}
