    /** Validaciones para config_usuarios2.php */
    
    /** Función para mostrar mensaje de error como globo */
    export function mostrarError(campoId, mensaje) {
      const campo = document.getElementById(campoId);
      if (!campo) return;
      
      let contenedorCampo = campo.closest('.campo');
      if (!contenedorCampo) return;
      
      // Limpiar errores anteriores
      const errorAnterior = contenedorCampo.querySelector('.mensaje-error');
      if (errorAnterior) errorAnterior.remove();
      
      // Quitar clase de error
      contenedorCampo.classList.remove('campo-con-error');
      
      // Si no hay mensaje, terminar
      if (!mensaje) return;
            
      // Crear elemento de mensaje como globo
      const errorElement = document.createElement('div');
      errorElement.className = 'mensaje-error';
      errorElement.textContent = mensaje;

      // Posicionamiento especial para el modal de búsqueda avanzada
      if (campo.closest('#modalBusquedaAvanzada')) {
        errorElement.style.left = '0';
        errorElement.style.width = '100%';
        errorElement.style.whiteSpace = 'normal';
      }
  
      // Estilo especial para errores en modales
      if (campo.closest('.modal-mensaje')) {
        errorElement.style.position = 'absolute';
        errorElement.style.zIndex = '10020';
      }

      contenedorCampo.appendChild(errorElement);
      contenedorCampo.classList.add('campo-error', 'campo-con-error');
      
      // Posicionamiento dinámico para campos especiales
      if (campoId === 'monto_total' || campoId === 'monto_pagado_total') {
        errorElement.style.left = '100px';
      }
    }

    // Función para limpiar todos los mensajes de error visibles
    export function limpiarTodosLosErrores() {
      // Limpiar errores del formulario principal
      document.querySelectorAll('.mensaje-error').forEach(errorElement => {
        errorElement.remove();
      });
      document.querySelectorAll('.campo-con-error').forEach(campoConError => {
        campoConError.classList.remove('campo-con-error');
        campoConError.classList.remove('campo-error');
      });
      
      // Limpiar errores específicos de búsqueda avanzada
      const camposBusqueda = ['ba-anio', 'ba-nro_comprobante', 'ba-expediente', 'campo-rango-fechas'];
      camposBusqueda.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
          const contenedor = campo.closest('.campo');
          if (contenedor) {
            const errorAnterior = contenedor.querySelector('.mensaje-error');
            if (errorAnterior) errorAnterior.remove();
            contenedor.classList.remove('campo-con-error', 'campo-error');
          }
        }
      });
    }

export function configurarValidacionesFormularioUsuario() {
  // USUARIO
  const campoUsuario = document.getElementById("usuario");
  campoUsuario.addEventListener("blur", function () {
    const val = this.value.trim();
    if (!val) {
      mostrarError('usuario', "El campo Usuario es obligatorio");
    } else {
      mostrarError('usuario', '');
    }
  });
  campoUsuario.addEventListener("focus", () => mostrarError('usuario', ''));

  // NOMBRE
  const campoNombre = document.getElementById("nombre");
  campoNombre.addEventListener("blur", function () {
    const val = this.value.trim();
    if (!val) {
      mostrarError('nombre', "Debe ingresar el nombre completo");
    } else {
      mostrarError('nombre', '');
    }
  });
  campoNombre.addEventListener("focus", () => mostrarError('nombre', ''));

  // LOGINPASS
  const campoLogin = document.getElementById("loginpass");
  campoLogin.addEventListener("blur", function () {
    const val = this.value.trim();
    if (!val) {
      mostrarError('loginpass', "Debe definir una contraseña de acceso");
    } else {
      mostrarError('loginpass', '');
    }
  });
  campoLogin.addEventListener("focus", () => mostrarError('loginpass', ''));

  // EMAIL
  const campoEmail = document.getElementById("email");
  campoEmail.addEventListener("blur", function () {
    const val = this.value.trim();
    if (val && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) {
      mostrarError('email', "Formato de correo no válido");
    } else {
      mostrarError('email', '');
    }
  });
  campoEmail.addEventListener("focus", () => mostrarError('email', ''));

  // ACTIVO y MOTIVO
  const campoActivo = document.getElementById("activo");
  campoActivo.addEventListener("change", function () {
    const campoMotivo = document.getElementById("campoMotivo");
    if (this.value === "0") {
      campoMotivo.style.display = "block";
    } else {
      campoMotivo.style.display = "none";
      document.getElementById("motivo").value = "";
    }
  });

  // MOTIVO
  const campoMotivo = document.getElementById("motivo");
  campoMotivo.addEventListener("blur", function () {
    const activoVal = document.getElementById("activo").value;
    const motivoVal = this.value.trim();
    if (activoVal === "0" && !motivoVal) {
      mostrarError('motivo', "Debe indicar el motivo de deshabilitación");
    } else {
      mostrarError('motivo', '');
    }
  });
  campoMotivo.addEventListener("focus", () => mostrarError('motivo', ''));

  // DESCRIP
  const campoDescrip = document.getElementById("descrip");
  campoDescrip.addEventListener("blur", function () {
    const val = this.value.trim();
    if (val.length > 200) {
      mostrarError('descrip', "Máximo 200 caracteres");
    } else {
      mostrarError('descrip', '');
    }
  });
  campoDescrip.addEventListener("focus", () => mostrarError('descrip', ''));
}

