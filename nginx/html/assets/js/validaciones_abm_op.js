    /** validaciones_abm_op.js: Validaciones para abm_op2.php */
    
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