
    // Función para mostrar mensaje de error como globo
    function mostrarError(campoId, mensaje) {
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
    function limpiarTodosLosErrores() {
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

    // ===== FUNCIONES DE FORMATEO PARA EXPORTACIÓN =====
    /**
     * Formatea montos de manera segura
     * @param {*} valor - El valor a formatear
     * @returns {string} - Monto formateado
     */
    function formatearMontoSeguro(valor) {
        try {
            // Manejar valores nulos o indefinidos
            if (valor === null || valor === undefined || valor === '') {
                return '0,00';
            }
            
            // Si ya es un string formateado, limpiarlo primero
            let cleanValue = valor;
            if (typeof valor === 'string') {
                // Remover caracteres no numéricos excepto punto y coma
                cleanValue = valor.replace(/[^\d,.-]/g, '');
                // Convertir coma decimal a punto
                cleanValue = cleanValue.replace(/,(\d{2})$/, '.$1');
                // Remover puntos de miles
                cleanValue = cleanValue.replace(/\.(?=\d{3})/g, '');
            }
            
            const numero = parseFloat(cleanValue);
            
            if (isNaN(numero)) {
                console.warn('formatearMontoSeguro: Valor no válido:', valor);
                return '0,00';
            }
            
            // Formatear para Argentina (punto para miles, coma para decimales)
            return new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(numero);
            
        } catch (error) {
            console.error('Error en formatearMontoSeguro:', error, 'Valor:', valor);
            return '0,00';
        }
    }

    /**
     * Formatea fechas de manera segura
     * @param {*} fecha - La fecha a formatear
     * @returns {string} - Fecha formateada
     */
    function formatearFechaSeguro(fecha) {
        try {
            // Manejar valores nulos o indefinidos
            if (!fecha || fecha === '' || fecha === '0000-00-00' || fecha === '00/00/0000') {
                return '';
            }
            
            let fechaObj;
            
            // Si es un string, intentar parsearlo
            if (typeof fecha === 'string') {
                // Formato DD/MM/YYYY
                if (fecha.includes('/')) {
                    const partes = fecha.split('/');
                    if (partes.length === 3) {
                        // Asumir DD/MM/YYYY
                        fechaObj = new Date(partes[2], partes[1] - 1, partes[0]);
                    }
                }
                // Formato YYYY-MM-DD
                else if (fecha.includes('-')) {
                    fechaObj = new Date(fecha);
                }
                // Otros formatos
                else {
                    fechaObj = new Date(fecha);
                }
            } else {
                fechaObj = new Date(fecha);
            }
            
            // Verificar si la fecha es válida
            if (isNaN(fechaObj.getTime())) {
                console.warn('formatearFechaSeguro: Fecha no válida:', fecha);
                return '';
            }
            
            // Formatear como DD/MM/YYYY
            const dia = fechaObj.getDate().toString().padStart(2, '0');
            const mes = (fechaObj.getMonth() + 1).toString().padStart(2, '0');
            const anio = fechaObj.getFullYear();
            
            return `${dia}/${mes}/${anio}`;
            
        } catch (error) {
            console.error('Error en formatearFechaSeguro:', error, 'Fecha:', fecha);
            return '';
        }
    }

    /**
     * Formatea texto de manera segura
     * @param {*} texto - El texto a formatear
     * @returns {string} - Texto formateado
     */
    function formatearTextoSeguro(texto) {
        try {
            if (texto === null || texto === undefined) {
                return '';
            }
            
            // Convertir a string y limpiar
            let textoLimpio = String(texto).trim();
            
            // Remover caracteres especiales problemáticos
            textoLimpio = textoLimpio.replace(/[\r\n\t]/g, ' ');
            textoLimpio = textoLimpio.replace(/\s+/g, ' ');
            
            return textoLimpio;
            
        } catch (error) {
            console.error('Error en formatearTextoSeguro:', error, 'Texto:', texto);
            return '';
        }
    }

    /**
     * Formatea números de manera segura
     * @param {*} numero - El número a formatear
     * @returns {string} - Número formateado
     */
    function formatearNumeroSeguro(numero) {
        try {
            if (numero === null || numero === undefined || numero === '') {
                return '0';
            }
            
            const num = parseFloat(numero);
            if (isNaN(num)) {
                return '0';
            }
            
            return num.toString();
            
        } catch (error) {
            console.error('Error en formatearNumeroSeguro:', error, 'Número:', numero);
            return '0';
        }
    }

    /**
     * Formatea CUIT de manera segura
     * @param {*} cuit - El CUIT a formatear
     * @returns {string} - CUIT formateado
     */
    function formatearCUITSeguro(cuit) {
        try {
            if (!cuit || cuit === '') {
                return '';
            }
            
            // Limpiar el CUIT (solo números)
            let cuitLimpio = String(cuit).replace(/\D/g, '');
            
            // Si tiene 11 dígitos, formatear como XX-XXXXXXXX-X
            if (cuitLimpio.length === 11) {
                return `${cuitLimpio.substring(0, 2)}-${cuitLimpio.substring(2, 10)}-${cuitLimpio.substring(10, 11)}`;
            }
            
            // Si no tiene 11 dígitos, devolver tal como está
            return cuit.toString();
            
        } catch (error) {
            console.error('Error en formatearCUITSeguro:', error, 'CUIT:', cuit);
            return cuit ? cuit.toString() : '';
        }
    }

    // ===== FUNCIÓN PRINCIPAL PARA FORMATEAR DATOS DE EXPORTACIÓN =====
    /**
     * Prepara los datos para exportación con formato seguro
     * @param {Array} datos - Array de datos a formatear
     * @returns {Array} - Datos formateados
     */
    function prepararDatosParaExportacion(datos) {
        if (!Array.isArray(datos)) {
            console.error('prepararDatosParaExportacion: Se esperaba un array');
            return [];
        }
        
        return datos.map(fila => {
            const filaFormateada = {};
            
            for (const [campo, valor] of Object.entries(fila)) {
                switch (campo) {
                    // Campos de montos
                    case 'monto_total':
                    case 'monto_pagado_total':
                        filaFormateada[campo] = formatearMontoSeguro(valor);
                        break;
                    
                    // Campos de fechas
                    case 'fecha_ordenado':
                    case 'fecha_pago':
                        filaFormateada[campo] = formatearFechaSeguro(valor);
                        break;
                    
                    // Campo CUIT
                    case 'cuit_benef':
                        filaFormateada[campo] = formatearCUITSeguro(valor);
                        break;
                    
                    // Campos numéricos
                    case 'id':
                    case 'anio':
                    case 'nro_comprobante':
                    case 'expediente':
                    case 'cuenta_pago':
                        filaFormateada[campo] = formatearNumeroSeguro(valor);
                        break;
                    
                    // Campos de texto
                    default:
                        filaFormateada[campo] = formatearTextoSeguro(valor);
                        break;
                }
            }
            
            return filaFormateada;
        });
    }

    // ===== CONFIGURACIÓN PARA TABULATOR =====
    /**
     * Configuración de exportación Excel para Tabulator
     */
    const configExportacionExcel = {
        sheetName: "Órdenes de Pago",
        documentProcessing: function(workbook) {
            // Configuraciones adicionales del workbook si es necesario
            return workbook;
        },
        rowGroupStyles: {
            font: { bold: true },
            fill: { fgColor: { rgb: "CCCCCC" } }
        },
        columnStyles: {
            // Estilos para columnas de montos
            monto_total: { numFmt: "#,##0.00" },
            monto_pagado_total: { numFmt: "#,##0.00" }
        }
    };

    // Función para generar el nombre del archivo con fecha y hora
    function generarNombreArchivo() {
        const ahora = new Date();
        
        // Formatear fecha y hora
        const año = ahora.getFullYear();
        const mes = String(ahora.getMonth() + 1).padStart(2, '0');
        const dia = String(ahora.getDate()).padStart(2, '0');
        const horas = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        
        // Generar nombre del archivo: OrdenesPago_YYYYMMDD_HHMMSS.xlsx
        const nombreArchivo = `OrdenesPago_${año}${mes}${dia}_${horas}${minutos}${segundos}.xlsx`;
        
        return nombreArchivo;
    }
    
    async function cargarOpcionesTC(seleccionado = "") {
      try {
        const res = await fetch("listar_tc.php");
        const lista = await res.json();

        const tcSelect = document.getElementById("tc");
        tcSelect.innerHTML = "";

        lista.forEach(item => {
          const opt = document.createElement("option");
          opt.value = item.tc;
          opt.textContent = item.tc + " - " + item.tipo_comprobante;

          if (item.tc.trim() === seleccionado.trim()) {
            opt.selected = true;
          }

          tcSelect.appendChild(opt);
        });

      } catch (e) {
        console.error("Error cargando lista TC:", e);
      }
    }
    
    let tabla;

    document.addEventListener("DOMContentLoaded", function () {

      // Validación mejorada para el campo año (1900-2100)
      const campoAnio = document.getElementById("anio");
      if (campoAnio) {
        campoAnio.addEventListener("input", function(e) {
          // Solo permite números y limita a 4 dígitos
          this.value = this.value.replace(/[^0-9]/g, '').substring(0, 4);
          mostrarError('anio', '');
        });

        campoAnio.addEventListener("blur", function() {
          const manio = parseInt(this.value);
          if (!this.value) { // Verifica si el campo está vacío
            mostrarError('anio', "Debe ingresar un año válido"); // Nuevo mensaje de error
            this.value = ""; // Limpia el campo si está vacío
            this.focus(); // Mantiene el foco en el campo
          } else if (isNaN(manio) || manio < 1900 || manio > 2100) {
            mostrarError('anio', "El año debe ser consistente. No es un dato válido");
            this.value = "";
            this.focus();
          } else {
            mostrarError('anio', '');
          }
        });
        
        // Establecer año actual como valor por defecto al crear nuevo
        campoAnio.addEventListener("focus", function() {
          if (!this.value && document.getElementById("tituloFormulario").textContent === "Nueva Orden de Pago") {
            const anioActual = new Date().getFullYear();
            this.value = anioActual;
          }
          mostrarError('anio', ''); 
        });

        // Validación mientras se escribe (evita años fuera de rango)
        campoAnio.addEventListener("keyup", function() {
          if (this.value.length === 4) {
            const manio = parseInt(this.value);
            if (manio < 1900 || manio > 2100) {
              mostrarError('anio', "El año debe ser consistente. No es un dato válido");
              this.value = "";
              this.focus();
            } else {
              mostrarError('anio', '');   
            }
          }
        });
      }

      // Validación para el campo nro_comprobante (numérico >= 1)
      const campoNroComprobante = document.getElementById("nro_comprobante");
      if (campoNroComprobante) {
        campoNroComprobante.addEventListener("input", function(e) {
          // Solo permite números
          this.value = this.value.replace(/[^0-9]/g, '');
          mostrarError('nro_comprobante', '');
          
          // Validación en tiempo real para valores < 1
          if (this.value && parseInt(this.value) < 1) {
            this.value = "";
          }
        });

        campoNroComprobante.addEventListener("blur", function() {
          const valornc = parseInt(this.value);
          if (!this.value || isNaN(valornc) || valornc < 1) {
            mostrarError('nro_comprobante', "Debe ingresar un valor numérico válido");
            this.value = "";
            this.focus();
          } else {
            mostrarError('nro_comprobante', '');  
          }
        });

        // Agrega esto para borrar el error al obtener el foco
        campoNroComprobante.addEventListener("focus", function() {
          mostrarError('nro_comprobante', '');
        });

        // Validación mientras se escribe (evita valores < 1)
        campoNroComprobante.addEventListener("keyup", function() {
          if (this.value && parseInt(this.value) < 1) {
            // mostrarMensaje("El número de comprobante debe ser mayor o igual a 1");
            this.value = "";
            this.focus();
          } else {
            mostrarError('nro_comprobante', '');    
          }
        });
      }

      // Validación para el campo ba-nro_comprobante en búsqueda avanzada (numérico >= 1)
      const campoNroComprobanteBusqueda = document.getElementById("ba-nro_comprobante");
      if (campoNroComprobanteBusqueda) {
        campoNroComprobanteBusqueda.addEventListener("input", function(e) {
          // Solo permite números
          this.value = this.value.replace(/[^0-9]/g, '');
          mostrarError('ba-nro_comprobante', '');
          
          // Validación en tiempo real para valores < 1
          if (this.value && parseInt(this.value) < 1) {
            this.value = "";
          }
        });

        campoNroComprobanteBusqueda.addEventListener("blur", function() {
          const valor = this.value.trim();
          if (valor && (isNaN(valor) || parseInt(valor) < 1)) {
            mostrarError('ba-nro_comprobante', "Debe ser mayor o igual a 1");
            this.value = "";
            this.focus();
          } else {
            mostrarError('ba-nro_comprobante', '');  
          }
        });

        campoNroComprobanteBusqueda.addEventListener("focus", function() {
          mostrarError('ba-nro_comprobante', '');
        });
      }

      // Validación para el campo expediente (n...n/nnn/nn)
      const campoExpediente = document.getElementById("expediente");
      if (campoExpediente) {
        // Al escribir, para dar feedback inmediato y limpiar si no cumple la estructura básica
        campoExpediente.addEventListener("input", function() {
          // Permite números y barras, pero no otros caracteres
          this.value = this.value.replace(/[^0-9/]/g, '');
          mostrarError('expediente', ''); // Limpia el error mientras el usuario escribe
        });

        // Al perder el foco, para la validación completa
        campoExpediente.addEventListener("blur", function() {
          const valorExpediente = this.value.trim();
          // Expresión regular para n...n/nnn/nn
          const regexExpediente = /^\d+\/\d{3}\/\d{2}$/;

          if (!valorExpediente) {
            mostrarError('expediente', "Debe ingresar el número de expediente.");
            this.focus();
            return;
          }

          if (!regexExpediente.test(valorExpediente)) {
            mostrarError('expediente', "Formato incorrecto. Debe ser: N/XXX/YY (N: número, X: código, Y: año)");
            this.value = ""; // Opcional: borrar el campo si el formato es incorrecto
            this.focus();
          } else {
            mostrarError('expediente', ''); // Limpia el error si la validación pasa
          }
        });

        // Al obtener el foco, para limpiar el mensaje de error
        campoExpediente.addEventListener("focus", function() {
          mostrarError('expediente', ''); // Borra el mensaje de error cuando el campo obtiene el foco
        });
      }

      // Validación para el campo op_descrip (no vacío)
      const campoOpDescrip = document.getElementById("op_descrip");
      if (campoOpDescrip) {
        campoOpDescrip.addEventListener("blur", function() {
          const valorDescrip = this.value.trim();
          if (!valorDescrip) {
            mostrarError('op_descrip', "La descripción no puede estar vacía.");
            this.focus();
          } else {
            mostrarError('op_descrip', ''); // Limpia el error si el campo tiene contenido
          }
        });

        campoOpDescrip.addEventListener("focus", function() {
          mostrarError('op_descrip', ''); // Borra el mensaje de error cuando el campo obtiene el foco
        });
      }

      // Configuración de validación para campos numéricos de monto
      const camposMontos = ["monto_total", "monto_pagado_total"];
      
      camposMontos.forEach(id => {
        const campo = document.getElementById(id);
        if (!campo) return;

        // Validación en tiempo real
        campo.addEventListener("input", function(e) {
          let valor = this.value.replace(/[^0-9.]/g, '');
          
          // Asegurar solo un punto decimal
          const partes = valor.split('.');
          if (partes.length > 2) {
            valor = partes[0] + '.' + partes.slice(1).join('');
          }
          
          // Limitar a 2 decimales
          if (partes.length === 2 && partes[1].length > 2) {
            valor = partes[0] + '.' + partes[1].substring(0, 2);
          }
          
          this.value = valor;
          mostrarError(id, '');
        });

        // Validación al perder el foco
        campo.addEventListener("blur", function() {
          if (this.value && !/^\d*\.?\d{0,2}$/.test(this.value)) {
            mostrarError(id, "Formato inválido. Use números con hasta 2 decimales");
            this.value = "";
            this.focus();
          }
        });
      });

      // INICIO NUEVAS VALIDACIONES NUMÉRICAS
      const camposSoloNumericos = ["cuenta_pago", "cuit_benef", "pago_e"];

      camposSoloNumericos.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
          campo.addEventListener("input", function() {
            // Elimina cualquier carácter que no sea un dígito
            this.value = this.value.replace(/[^0-9]/g, '');
            mostrarError(id, ''); // Limpia el error mientras el usuario escribe
          });

          campo.addEventListener("blur", function() {
            const valor = this.value.trim();
            // Permite el campo vacío o solo con números
            if (valor !== "" && !/^\d*$/.test(valor)) {
              mostrarError(id, "Solo se permiten números.");
              this.focus();
            } else {
              mostrarError(id, ''); // Limpia el error si es válido o está vacío
            }
          });

          campo.addEventListener("focus", function() {
            mostrarError(id, ''); // Borra el mensaje de error al obtener el foco
          });
        }
      });
      // FIN NUEVAS VALIDACIONES NUMÉRICAS

      // VALIDACIONES PARA BUSQUEDA AVANZADA

      const campoAnioBusqueda = document.getElementById("ba-anio");
      if (campoAnioBusqueda) {
        campoAnioBusqueda.addEventListener("input", function(e) {
          // Solo permite números y limita a 4 dígitos
          this.value = this.value.replace(/[^0-9]/g, '').substring(0, 4);
          mostrarError('ba-anio', '');
        });

        campoAnioBusqueda.addEventListener("blur", function() {
          const manio = parseInt(this.value);
          if (this.value && (isNaN(manio) || manio < 1900 || manio > 2100)) {
            mostrarError('ba-anio', "El año debe estar entre 1900 y 2100");
            this.value = "";
            this.focus();
          } else {
            mostrarError('ba-anio', '');
          }
        });

        campoAnioBusqueda.addEventListener("keyup", function() {
          if (this.value.length === 4) {
            const manio = parseInt(this.value);
            if (manio < 1900 || manio > 2100) {
              mostrarError('ba-anio', "El año debe estar entre 1900 y 2100");
              this.value = "";
              this.focus();
            } else {
              mostrarError('ba-anio', '');   
            }
          }
        });
      }

      // Validación para el campo ba-expediente en búsqueda avanzada (n...n/nnn/nn)
      const campoExpedienteBusqueda = document.getElementById("ba-expediente");
      if (campoExpedienteBusqueda) {
          // Validación mientras se escribe
          campoExpedienteBusqueda.addEventListener("input", function() {
              // Permite números y barras, pero no otros caracteres
              this.value = this.value.replace(/[^0-9/]/g, '');
              mostrarError('ba-expediente', '');
              
              // Autoformato mientras escribe
              const partes = this.value.split('/');
              if (partes.length > 1) {
                  // Primera parte (n...n) - solo números
                  if (partes[0] && !/^\d+$/.test(partes[0])) {
                      this.value = partes[0].replace(/[^0-9]/g, '') + (partes[1] ? '/' + partes[1] : '');
                  }
                  // Si tiene dos barras, aplicar formato nnn/nn
                  if (partes.length > 2) {
                      const valorLimpio = partes[0] + '/' + 
                                        (partes[1] ? partes[1].substring(0, 3).replace(/[^0-9]/g, '') : '') + '/' + 
                                        (partes[2] ? partes[2].substring(0, 2).replace(/[^0-9]/g, '') : '');
                      this.value = valorLimpio;
                  }
              }
          });

          // Validación al perder el foco
          campoExpedienteBusqueda.addEventListener("blur", function() {
              const valorExpediente = this.value.trim();
              // Expresión regular para n...n/nnn/nn
              const regexExpediente = /^\d+\/\d{3}\/\d{2}$/;

              if (valorExpediente && !regexExpediente.test(valorExpediente)) {
                  mostrarError('ba-expediente', "Formato requerido: NÚMERO/XXX/YY (Ej: 1234/567/89)");
                  this.focus();
              } else {
                  mostrarError('ba-expediente', '');
              }
          });

          // Limpiar error al obtener foco
          campoExpedienteBusqueda.addEventListener("focus", function() {
              mostrarError('ba-expediente', '');
          });
      }

      // Navegación con ENTER en el modal de búsqueda avanzada
      document.querySelectorAll('#formBusquedaAvanzada input').forEach((el, i, list) => {
        el.addEventListener('keydown', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            
            // Si estamos en el último campo (fecha-hasta), ejecutar la búsqueda
            if (el.id === 'ba-fecha-hasta') {
              aplicarBusquedaAvanzada();
              return;
            }
            
            // Avanzar al siguiente campo
            let next = list[i + 1];
            while (next && (next.disabled || next.offsetParent === null)) {
              i++;
              next = list[i + 1];
            }
            if (next) next.focus();
          }
        });
      });

      // Configuración de la tabla Tabulator
      tabla = new Tabulator("#tablaOP", {
        selectable: 1,
        layout: "fitDataStretch",
        pagination: "remote",
        paginationSize: 20,
        paginationSizeSelector: [20, 50, 100, 500],
        ajaxURL: "listar_op.php",
        ajaxConfig: "POST",
        ajaxContentType: "json",
        ajaxResponse: function(url, params, response) {
          return response.data;
        },
        locale: true,
        langs: {
          "es-es": {
            "pagination": {
              "page_size": "Filas"
            }
          }
        },
        columns: [
          { title: "Cancelado", field: "cancelado", width: 110, hozAlign: "center" },
          { title: "Año", field: "anio", width: 74 },
          { title: "O.P.", field: "nro_comprobante", headerFilter: "input", width: 70 },
          { title: "Expediente", field: "expediente", width: 110, headerFilter: "input" },
          { title: "TC", field: "tc", width: 40 },
          { title: "Tipo Comprobante", field: "tipo_comprobante", width: 40 },
          { title: "M.Total", field: "monto_total", width: 118, hozAlign: "right" },
          { title: "M.Pagado", field: "monto_pagado_total", width: 118, hozAlign: "right" },
          { title: "Descripción", field: "op_descrip", width: 150, headerFilter: "input", headerFilterFunc: "like" },
          { title: "Cuenta Pago", field: "cuenta_pago" },
          { title: "Nombre Cuenta", field: "nombre_cuenta_pago" },
          { title: "CUIT", field: "cuit_benef", headerFilter: "input", headerFilterFunc: "like" },
          { title: "Beneficiario", field: "beneficiario", headerFilter: "input", headerFilterFunc: "like" },
          { title: "F.Ordenado", field: "fecha_ordenado" },
          { title: "F.Pago", field: "fecha_pago" },
          { title: "TP", field: "tp" },
          { title: "Pago E.", field: "pago_e" },
          { title: "Observaciones", field: "descrip_carga" }
        ],
      });

      tabla.on("rowClick", function(e, row) {
        row.select();
      });
      
      const selector = document.createElement("select");
      [20, 50, 100, 500].forEach(size => {
        const option = document.createElement("option");
        option.value = size;
        option.textContent = size;
        selector.appendChild(option);
      });
      selector.value = 20;
      selector.style.marginBottom = "10px";
      selector.addEventListener("change", () => {
        tabla.setPageSize(Number(selector.value));
      });
      document.querySelector(".botonera").insertAdjacentElement("afterend", selector);

      function traducirBotonesPaginacion() {
        const botones = document.querySelectorAll(".tabulator-paginator button");
        botones.forEach(btn => {
          if (btn.textContent === "First") btn.textContent = "Primero";
          if (btn.textContent === "Last") btn.textContent = "Último";
          if (btn.textContent === "Prev") btn.textContent = "Anterior";
          if (btn.textContent === "Next") btn.textContent = "Siguiente";
        });
      }

      setTimeout(traducirBotonesPaginacion, 300);
      tabla.on("dataLoaded", traducirBotonesPaginacion);
      tabla.on("pageLoaded", traducirBotonesPaginacion);
    });

    function mostrarFormulario() {
      document.getElementById("formOP").reset();
      document.getElementById("id").value = "";
      document.getElementById("tituloFormulario").textContent = "Nueva Orden de Pago";
      
      limpiarTodosLosErrores();
      
      document.getElementById("modalFormulario").style.display = "block";
      const modalContenido = document.querySelector("#modalFormulario .contenido");
      modalContenido.scrollTop = 0;
      setTimeout(() => document.getElementById("anio").focus(), 50);
    }

    function cerrarFormulario() {
      document.getElementById("modalFormulario").style.display = "none";
    }

    function cerrarModal() {
      document.getElementById("modalMensaje").style.display = "none";
    }

    async function editarOrden() {
      const fila = tabla.getSelectedData()[0];
      if (!fila) return mostrarMensaje("Seleccione una orden para editar.");
      
      // Limpia errores antes de cargar los datos y mostrar el modal
      limpiarTodosLosErrores(); 
      
      document.getElementById("tituloFormulario").textContent = "Editar Orden de Pago";

      function formatoFecha(valor) {
        if (!valor) return "";
        const partes = valor.split("-");
        if (partes.length === 3) return valor; // ya es YYYY-MM-DD
        if (valor.includes("/")) {
          const [d, m, a] = valor.split("/");
          return `${a}-${m.padStart(2, "0")}-${d.padStart(2, "0")}`;
        }
        return "";
      }

      function limpiarNumero(valor) {
        if (!valor) return "";
        const limpio = parseFloat(
          valor.toString().replace(/\./g, "").replace(",", ".")
        );
        return isNaN(limpio) ? "" : limpio;
      }
    
      document.getElementById("id").value = fila.id || ""; // Asegúrate de cargar el ID también para la edición
      document.getElementById("anio").value = fila.anio || "";
      document.getElementById("nro_comprobante").value = fila.nro_comprobante || "";
      document.getElementById("expediente").value = fila.expediente || "";
      await cargarOpcionesTC(fila.tc);
      
      document.getElementById("monto_total").value = limpiarNumero(fila.monto_total);
      document.getElementById("monto_pagado_total").value = limpiarNumero(fila.monto_pagado_total);

      document.getElementById("op_descrip").value = fila.op_descrip || "";
      document.getElementById("cuenta_pago").value = fila.cuenta_pago || "";
      document.getElementById("nombre_cuenta_pago").value = fila.nombre_cuenta_pago || "";
      document.getElementById("cuit_benef").value = fila.cuit_benef || "";
      document.getElementById("beneficiario").value = fila.beneficiario || "";

      document.getElementById("fecha_ordenado").value = formatoFecha(fila.fecha_ordenado);
      document.getElementById("fecha_pago").value = formatoFecha(fila.fecha_pago);

      document.getElementById("tp").value = fila.tp || "";
      document.getElementById("pago_e").value = fila.pago_e || "";
      document.getElementById("descrip_carga").value = fila.descrip_carga || "";

      document.getElementById("modalFormulario").style.display = "block";
      document.querySelector("#modalFormulario .contenido").scrollTop = 0;
      setTimeout(() => document.getElementById("anio").focus(), 50);
    }

    function eliminarOrden() {
      const fila = tabla.getSelectedData()[0];
      if (!fila) return mostrarMensaje("Seleccione una orden para eliminar.");
      
      // Llenar los datos en el modal de confirmación
      document.getElementById("eliminar-anio").textContent = fila.anio || "-";
      document.getElementById("eliminar-nro").textContent = fila.nro_comprobante || "-";
      document.getElementById("eliminar-expediente").textContent = fila.expediente || "-";
      document.getElementById("eliminar-descripcion").textContent = fila.op_descrip || "-";
      
      // Guardar el ID para usar en la confirmación
      window.ordenAEliminar = fila.id;
      
      // Mostrar el modal
      document.getElementById("modalConfirmacion").style.display = "flex";
    }

    function confirmarEliminacion() {
      if (!window.ordenAEliminar) return;
      
      fetch("eliminar_op.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: window.ordenAEliminar })
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        // Mostrar mensaje según el resultado
        mostrarMensaje(data.mensaje);
        
        // Solo recargar tabla si la eliminación fue exitosa
        if (data.success) {
          tabla.replaceData();
        }
        
        // Cerrar el modal siempre
        cancelarEliminacion();
      })
      .catch(error => {
        console.error('Error al eliminar:', error);
        mostrarMensaje('Error de conexión al eliminar la orden de pago.');
        cancelarEliminacion(); // Cerrar modal aún con error
      });
    }

    function cancelarEliminacion() {
      document.getElementById("modalConfirmacion").style.display = "none";
      window.ordenAEliminar = null;
    }

    function imprimirOrden() {
      const fila = tabla.getSelectedData()[0];
      if (!fila) return mostrarMensaje("Seleccione una orden para imprimir.");
      window.open("imprimir_op.php?id=" + fila.id, "_blank");
    }
   
    // Función para mostrar mensaje con HTML (mejorada)
    function mostrarMensaje(texto) {
      const mensajeElement = document.getElementById("mensajeTexto");
      
      // Si el texto contiene HTML, usar innerHTML, si no, usar textContent
      if (texto.includes('<')) {
        mensajeElement.innerHTML = texto;
      } else {
        mensajeElement.textContent = texto;
      }
       
      document.getElementById("mensajeEmergente").style.display = "flex";
    }

    function cerrarMensaje() {
      document.getElementById("mensajeEmergente").style.display = "none";
    }

    // Modificación en la validación del submit
    document.getElementById("formOP").addEventListener("submit", function(e) {
      e.preventDefault();
      let valido = true;
      
      // Validación de montos
      const montoTotal = document.getElementById("monto_total").value;
      if (montoTotal && !/^\d*\.?\d{0,2}$/.test(montoTotal)) {
        mostrarError('monto_total', "Formato inválido");
        valido = false;
      }
           
      const montoPagado = document.getElementById("monto_pagado_total").value;
      if (montoPagado && !/^\d*\.?\d{0,2}$/.test(montoPagado)) {
        mostrarError('monto_pagado_total', "Formato inválido");
        valido = false;
      }

      // Validación del año
      const anio = document.getElementById("anio").value;
      const añoNum = parseInt(anio);
      if (!anio || isNaN(añoNum) || añoNum < 1900 || añoNum > 2100) {
        mostrarError('anio', "Debe estar entre 1900 y 2100");
        valido = false;
      }
      
      // Validación del nro_comprobante
      const nroComprobante = document.getElementById("nro_comprobante").value;
      const nroComprobanteNum = parseInt(nroComprobante);
      if (!nroComprobante || isNaN(nroComprobanteNum) || nroComprobanteNum < 1) {
        mostrarError('nro_comprobante', "Debe ser mayor o igual a 1");
        valido = false;
      }

      // Validación para el campo expediente (n...n/nnn/nn) en el submit
      const campoExpediente = document.getElementById("expediente");
      if (campoExpediente) {
        const valorExpediente = campoExpediente.value.trim();
        const regexExpediente = /^\d+\/\d{3}\/\d{2}$/;

        if (!valorExpediente) {
          mostrarError('expediente', "Debe ingresar el número de expediente.");
          valido = false;
        } else if (!regexExpediente.test(valorExpediente)) {
          mostrarError('expediente', "Formato incorrecto. Debe ser: N/XXX/YY (N: número, X: código, Y: año)");
          valido = false;
        }
      }

      // Validación para el campo op_descrip
      const campoOpDescrip = document.getElementById("op_descrip");
      if (campoOpDescrip) {
        const valorDescrip = campoOpDescrip.value.trim();
        if (!valorDescrip) {
          mostrarError('op_descrip', "La descripción no puede estar vacía.");
          valido = false;
        }
      }

      // INICIO VALIDACIONES NUMÉRICAS AL SUBMIT
      const camposSoloNumericos = ["cuenta_pago", "cuit_benef", "pago_e"];
      camposSoloNumericos.forEach(id => {
        const campo = document.getElementById(id);
        if (campo) {
          const valor = campo.value.trim();
          if (valor !== "" && !/^\d*$/.test(valor)) {
            mostrarError(id, "Solo se permiten números.");
            valido = false;
          }
        }
      });
      // FIN VALIDACIONES NUMÉRICAS AL SUBMIT

      if (!valido) {
        return;
      }      
      
    const datos = Object.fromEntries(new FormData(this).entries());
    const url = datos.id ? "actualizar_op.php" : "guardar_op.php";

    fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(datos)
    })
    .then(async r => {
      const texto = await r.text();

      try {
        const d = JSON.parse(texto);

        if (d.success) {
          mostrarMensaje(d.mensaje);
          cerrarFormulario();
          tabla.replaceData();
        } else {
          let mensajeCompleto = d.mensaje;
          
          // Si hay duplicados, construir el mensaje HTML correctamente
          if (d.duplicados && d.duplicados.length > 0) {
            mensajeCompleto += `
              <div style="text-align: left; margin-top: 15px;">
                <p style="margin-bottom: 10px; color: #666;"><strong>📋 Registros encontrados con datos similares:</strong></p>
                <ul style="list-style: none; padding: 0; margin: 0;">
                  ${d.duplicados.map(dup => `
                    <li style="background: #f8f9fa; margin: 5px 0; padding: 8px 12px; border-left: 3px solid #007bff; border-radius: 4px;">
                      <strong>Año:</strong> ${dup.anio} &nbsp;|&nbsp; 
                      <strong>Nro:</strong> ${dup.nro_comprobante} &nbsp;|&nbsp; 
                      <strong>Expte:</strong> ${dup.expediente}
                    </li>
                  `).join("")}
                </ul>
                <p style="margin-top: 10px; font-size: 0.9em; color: #666;">
                  💡 Verifique si desea continuar con estos datos.
                </p>
              </div>
            `;
          }
          
          mostrarMensaje(mensajeCompleto);
          // No cerrar el formulario para que el usuario pueda modificar
        }

      } catch (err) {
        console.error("⚠️ JSON inválido recibido desde el servidor:\n", texto);
        mostrarMensaje("❌ Error interno: la respuesta del servidor no es válida.");
      }
    })
    .catch(error => {
      console.error("Error de red:", error);
      mostrarMensaje("❌ Error de conexión. Verifique su conexión a internet.");
    });
  });
    
    // ENTER para saltar de campo
    document.querySelectorAll('#formOP input, #formOP select, #formOP textarea').forEach((el, i, list) => {
      el.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          let next = list[i + 1];
          while (next && (next.disabled || next.offsetParent === null)) {
            i++;
            next = list[i + 1];
          }
          if (next) next.focus();
        }
      });
    });

    async function obtenerDatosFiltradosTabulator() {
      // Verificar si la tabla tiene datos
      if (tabla.getDataCount() === 0) {
        throw new Error("No hay datos para exportar en la tabla.");
      }

      // Obtener datos filtrados de la tabla (respeta paginación remota y filtros)
      //const datos = tabla.getData("visible"); (esto trae la primera página únicamente)
      const datos = tabla.getData();
      
      // Convertir y mapear los datos con las columnas disponibles en la tabla
      const datosConvertidos = datos.map(fila => ({
        "Cancelado": fila.cancelado || "",
        "Año": fila.anio || "",
        "O.P.": fila.nro_comprobante || "",
        "Expediente": fila.expediente || "",
        "TC": fila.tc || "",
        "Tipo Comprobante": fila.tipo_comprobante || "",
        "M.Total": fila.monto_total || "",
        "M.Pagado": fila.monto_pagado_total || "",
        "Descripción": fila.op_descrip || "",
        "Cuenta Pago": fila.cuenta_pago || "",
        "Nombre Cuenta": fila.nombre_cuenta_pago || "",
        "CUIT": fila.cuit_benef || "",
        "Beneficiario": fila.beneficiario || "",
        "F.Ordenado": fila.fecha_ordenado || "",
        "F.Pago": fila.fecha_pago || "",
        "TP": fila.tp || "",
        "Pago E.": fila.pago_e || "",
        "Observaciones": fila.descrip_carga || ""
      }));

      return datosConvertidos;
    }

    // Función auxiliar para exportar directamente a Excel
    function exportarAExcel() {
      obtenerDatosFiltradosTabulator()
        .then(datosConvertidos => {
          const wb = XLSX.utils.book_new();
          const ws = XLSX.utils.json_to_sheet(datosConvertidos);
          XLSX.utils.book_append_sheet(wb, ws, "Ordenes_Pago");
          XLSX.writeFile(wb, "ordenes_pago_export.xlsx");
        })
        .catch(error => {
          alert(error.message);
        });
    }

    // Función para obtener todos los datos filtrados de Tabulator
    async function obtenerDatosFiltradosTabulator100000() {
        try {
            console.log('Obteniendo datos filtrados de Tabulator...');
            
            // Obtener filtros actuales de la tabla
            const filtrosActuales = tabla.getHeaderFilters() || {};
            
            // Obtener configuración actual de paginación
            const paginacionActual = tabla.getPageSize();
            
            // Crear una copia temporal de la configuración para obtener todos los datos
            let todosLosDatos = [];
            let pagina = 1;
            const tamanioPagina = 1000; // Tamaño de página para consultar al servidor
            let hayMasDatos = true;
            
            // Mostrar progreso
            mostrarMensaje("📊 Obteniendo datos filtrados...");
            
            while (hayMasDatos) {
                console.log(`Obteniendo página ${pagina}...`);
                
                // Preparar parámetros para la consulta
                const requestBody = {
                    page: pagina,
                    size: tamanioPagina,
                    filters: filtrosActuales
                };
                
                try {
                    const response = await fetch('listar_op.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(requestBody)
                    });
                    
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                    }
                    
                    const texto = await response.text();
                    
                    if (!texto.trim()) {
                        throw new Error('Respuesta vacía del servidor');
                    }
                    
                    let resultado;
                    try {
                        resultado = JSON.parse(texto);
                    } catch (parseError) {
                        console.error('Respuesta no es JSON válido:', texto.substring(0, 200) + '...');
                        throw new Error('La respuesta del servidor no es válida');
                    }
                    
                    // Extraer los datos de la respuesta
                    let datosPagina = [];
                    
                    if (Array.isArray(resultado)) {
                        datosPagina = resultado;
                    } else if (resultado.data && Array.isArray(resultado.data)) {
                        datosPagina = resultado.data;
                    } else if (resultado.rows && Array.isArray(resultado.rows)) {
                        datosPagina = resultado.rows;
                    } else {
                        console.error('Estructura de respuesta inesperada:', resultado);
                        throw new Error('Estructura de datos no reconocida del servidor');
                    }
                    
                    // Agregar datos a la colección total
                    todosLosDatos = todosLosDatos.concat(datosPagina);
                    
                    // Verificar si hay más datos
                    if (datosPagina.length < tamanioPagina) {
                        hayMasDatos = false;
                    } else {
                        pagina++;
                    }
                    
                    // Actualizar mensaje de progreso
                    mostrarMensaje(`📊 Obteniendo datos: ${todosLosDatos.length} registros...`);
                    
                    // Límite de seguridad
                    if (pagina > 100) {
                        console.warn('Se alcanzó el límite de páginas (100)');
                        break;
                    }
                    
                } catch (fetchError) {
                    console.error('Error en la consulta:', fetchError);
                    throw new Error(`Error al obtener datos: ${fetchError.message}`);
                }
            }
            
            console.log(`Datos filtrados obtenidos exitosamente: ${todosLosDatos.length} registros`);
            return todosLosDatos;
            
        } catch (error) {
            console.error('Error al obtener datos filtrados:', error);
            throw new Error(`Error al obtener datos filtrados: ${error.message}`);
        }
    }



    // Funciones para la búsqueda avanzada
    function mostrarBusquedaAvanzada() {
      document.getElementById("formBusquedaAvanzada").reset();
      limpiarTodosLosErrores();
      document.getElementById("modalBusquedaAvanzada").style.display = "flex";
      setTimeout(() => {
        document.getElementById("ba-anio").focus();
      }, 50);
    }

    function cerrarBusquedaAvanzada() {
      document.getElementById("modalBusquedaAvanzada").style.display = "none";
    }

    function aplicarBusquedaAvanzada() {
      limpiarTodosLosErrores();

      // 1. Primero validar que haya al menos un criterio de búsqueda
      const bus_anio = document.getElementById("ba-anio").value;
      const bus_nroComprobante = document.getElementById("ba-nro_comprobante").value;
      const bus_expediente = document.getElementById("ba-expediente").value;
      const bus_fechaDesde = document.getElementById("ba-fecha-desde").value;
      const bus_fechaHasta = document.getElementById("ba-fecha-hasta").value;
      
      if (!bus_anio && !bus_nroComprobante && !bus_expediente && !bus_fechaDesde && !bus_fechaHasta) {
        mostrarMensaje("Debe ingresar al menos un criterio de búsqueda");
        return;
      }

      let valido = true;

      document.getElementById("ba-nro_comprobante").addEventListener("input", function(e) {
        // Solo permite números
        this.value = this.value.replace(/[^0-9]/g, '');
        mostrarError('ba-nro_comprobante', '');
        
        // Validación en tiempo real para valores < 1
        if (this.value && parseInt(this.value) < 1) {
          this.value = "";
        }
      });

      document.getElementById("ba-nro_comprobante").addEventListener("blur", function() {
        const valor = this.value.trim();
        if (valor && (isNaN(valor) || parseInt(valor) < 1)) {
          mostrarError('ba-nro_comprobante', "Debe ser mayor o igual a 1");
          this.value = "";
          this.focus();
        } else {
          mostrarError('ba-nro_comprobante', '');  
        }
      });

      document.getElementById("ba-nro_comprobante").addEventListener("focus", function() {
        mostrarError('ba-nro_comprobante', '');
      });

      // Validar año
      const anio = document.getElementById("ba-anio").value;
      if (anio && (isNaN(anio) || anio < 1900 || anio > 2100)) {
        mostrarError('ba-anio', "El año debe ser un dato consistente. Por Ej.");
        valido = false;
      } else {
        mostrarError('ba-anio', ''); // Limpiar error si es válido  
      }

      // Validar número de comprobante
      const nroComprobante = document.getElementById("ba-nro_comprobante").value;
      if (nroComprobante) {
        const num = parseInt(nroComprobante);
        if (isNaN(num) || num < 1) {
          mostrarError('ba-nro_comprobante', "Debe ser un número mayor o igual a 1");
          valido = false;
        } else {
          mostrarError('ba-nro_comprobante', ''); // Limpiar error si es válido
        }
      } else {
        mostrarError('ba-nro_comprobante', ''); // Limpiar error si está vacío
      }

      // Validar expediente
      const expediente = document.getElementById("ba-expediente").value;
      if (expediente && !/^\d+\/\d{3}\/\d{2}$/.test(expediente)) {
        mostrarError('ba-expediente', "Formato debe ser N/XXX/YY");
        valido = false;
      } else {
        mostrarError('ba-expediente', ''); // Limpiar error si es válido o está vacío
      } 

      // Validar fechas
      const fechaDesde = document.getElementById("ba-fecha-desde").value;
      const fechaHasta = document.getElementById("ba-fecha-hasta").value;
      
      if ((fechaDesde && !fechaHasta) || (!fechaDesde && fechaHasta)) {
        const campoFechas = document.getElementById("campo-rango-fechas");
        campoFechas.classList.add('campo-con-error');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'mensaje-error';
        errorElement.textContent = "Debe completar ambas fechas para el rango";
        errorElement.style.left = '0';
        errorElement.style.width = '100%';
        errorElement.style.whiteSpace = 'normal';
        
        campoFechas.appendChild(errorElement);
        valido = false;
      }

      if (!valido) return;

      const form = document.getElementById("formBusquedaAvanzada");
      const datos = Object.fromEntries(new FormData(form).entries());
      
      // Si solo se ingresó nro_comprobante o expediente, limpiar otros filtros
      if (datos.nro_comprobante) {
        datos.expediente = '';
        datos.fecha_desde = '';
        datos.fecha_hasta = '';
      } else if (datos.expediente) {
        datos.fecha_desde = '';
        datos.fecha_hasta = '';
      }
      
      // Enviar la búsqueda al servidor
      fetch("bus_avanzada.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(datos)
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Reemplazar datos en la tabla
          tabla.replaceData(data.data);
          // Mostrar botón para quitar filtros
          document.getElementById("btnQuitarFiltros").style.display = "inline-block";
          // Cerrar el modal
          cerrarBusquedaAvanzada();
          mostrarMensaje(`Se encontraron ${data.data.length} registros`);
        } else {
          mostrarMensaje(data.mensaje || "Error en la búsqueda");
        }
      })
      .catch(error => {
        console.error('Error en la búsqueda:', error);
        mostrarMensaje('Error de conexión al realizar la búsqueda');
      });
    }

    function quitarFiltros() {
      // Restablecer la tabla a su estado inicial
      tabla.replaceData();
      // Ocultar el botón de quitar filtros
      document.getElementById("btnQuitarFiltros").style.display = "none";
      mostrarMensaje("Filtros removidos - Mostrando datos iniciales");
    }