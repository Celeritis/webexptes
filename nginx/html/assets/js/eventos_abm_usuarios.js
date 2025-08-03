// eventos_abm_usuarios.js Ver 0001 - 22/06/2025 - 1906

import { mostrarFormulario, cerrarFormulario,cerrarMensaje } from './utilidades_crud_usr.js';
import { guardarUsuario, editarUsuario, eliminarUsuario, exportarUsuarios } from './scripts_abm_usuarios.js';

export function manejarEventos() {
  document.addEventListener('DOMContentLoaded', () => {
    asociarEventosFormulario();
    asociarEventosBotonera();
  });
}

function asociarEventosFormulario() {
  const form = document.getElementById('formUsuario');
  if (!form) return;

  form.addEventListener('submit', guardarUsuario);

  document.querySelectorAll('#formUsuario input, #formUsuario select, #formUsuario textarea').forEach((el, i, lista) => {
    el.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        let siguiente = lista[i + 1];
        while (siguiente && (siguiente.disabled || siguiente.offsetParent === null)) {
          i++;
          siguiente = lista[i + 1];
        }
        if (siguiente) siguiente.focus();
      }
    });
  });

  const campoActivo = document.getElementById("activo");
  if (campoActivo) {
    campoActivo.addEventListener("change", function () {
      const campoMotivo = document.getElementById("campoMotivo");
      if (this.value === "0") {
        campoMotivo.style.display = "block";
      } else {
        campoMotivo.style.display = "none";
        document.getElementById("motivo").value = "";
      }
    });
  }
}

function asociarEventosBotonera() {
  const btnNuevo = document.querySelector("button[onclick='mostrarFormulario()']");
  if (btnNuevo) btnNuevo.addEventListener("click", mostrarFormulario);

  const btnEditar = document.querySelector("button[onclick='editarUsuario()']");
  if (btnEditar) btnEditar.addEventListener("click", editarUsuario);

  const btnEliminar = document.querySelector("button[onclick='eliminarUsuario()']");
  if (btnEliminar) btnEliminar.addEventListener("click", eliminarUsuario);

  const btnExportar = document.querySelector("button[onclick='exportarUsuarios()']");
  if (btnExportar) btnExportar.addEventListener("click", exportarUsuarios);
}

