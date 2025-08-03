// import { limpiarTodosLosErrores } from './validaciones_abm_op.js';

import {  limpiarTodosLosErrores, mostrarError,
          configurarValidacionesFormularioUsuario 
        } from './validaciones_crud_usr.js';

export function mostrarFormulario() {
  const form = document.getElementById("formUsuario");
  form.reset();

  document.getElementById("id").value = "";
  document.getElementById("pass").value = "";

  document.getElementById("tituloFormulario").textContent = "Nuevo Usuario";

  const preview = document.getElementById("previewFoto");
  if (preview) preview.innerHTML = "";

  document.getElementById("rol").value = "2";
  document.getElementById("activo").value = "1";
  document.getElementById("campoMotivo").style.display = "none";
  document.getElementById("motivo").value = "";

  limpiarTodosLosErrores();

  document.getElementById("modalFormulario").style.display = "block";
  document.querySelector("#modalFormulario .contenido").scrollTop = 0;

  setTimeout(() => document.getElementById("usuario").focus(), 50);
}

// Avanzar con ENTER
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

// Validaciones
configurarValidacionesFormularioUsuario();

export function cerrarFormulario() {
  document.getElementById("modalFormulario").style.display = "none";
}

export function cerrarMensaje() {
  document.getElementById("mensajeEmergente").style.display = "none";
}
