/**  script principal de abm_op2.php  ver 003 - 08/07/2025  021:08  */

import { mostrarError, limpiarTodosLosErrores } from './validaciones_abm_op.js';
import {  formatearFechaSeguro, formatearMontoSeguro, 
          formatearTextoSeguro, formatearNumeroSeguro, 
          formatearCUITSeguro, prepararDatosParaExportacion,
          generarNombreArchivo, cargarOpcionesTC,
          mostrarFormulario, cerrarFormulario,
          editarOrden, mostrarMensaje, cerrarMensaje,
          eliminarOrden, confirmarEliminacion, cancelarEliminacion,
          imprimirOrden, exportarAExcel, obtenerDatosFiltradosTabulator, 
          quitarFiltros, mostrarMensajeConCancelar, mostrarMensajeSINO,
          actualizarProgresoBusqueda, cancelarBusquedaAvanzada,
          cerrarMensajeBusquedaAvanzada, recargarTablaDesdeBackend ,
          mostrarModalTemporizado, esconderModalTemporizado,
          abrirModalSino, cerrarModalSino, mostrarMensajetempoTemporal,
          activarDeteccionCambioPaginaNAV , almacenarEstadoTablaOriginal
        } from './utilidades_abm_op.js';
import { mostrarBusquedaAvanzada, cerrarBusquedaAvanzada, aplicarBusquedaAvanzada } 
          from './bus_adv_abm_op.js';

window.mostrarFormulario = mostrarFormulario;
window.cerrarFormulario = cerrarFormulario;
window.editarOrden = editarOrden;
window.mostrarMensaje = mostrarMensaje;
window.cerrarMensaje = cerrarMensaje;
window.eliminarOrden = eliminarOrden;
window.confirmarEliminacion = confirmarEliminacion;
window.cancelarEliminacion = cancelarEliminacion;
window.imprimirOrden = imprimirOrden;
window.exportarAExcel = exportarAExcel;
window.obtenerDatosFiltradosTabulator = obtenerDatosFiltradosTabulator;
window.mostrarBusquedaAvanzada = mostrarBusquedaAvanzada;
window.cerrarBusquedaAvanzada = cerrarBusquedaAvanzada;
window.aplicarBusquedaAvanzada = aplicarBusquedaAvanzada;
window.quitarFiltros = quitarFiltros;
window.mostrarMensajeConCancelar = mostrarMensajeConCancelar;
window.actualizarProgresoBusqueda = actualizarProgresoBusqueda;
window.cancelarBusquedaAvanzada = cancelarBusquedaAvanzada;
window.cerrarMensajeBusquedaAvanzada = cerrarMensajeBusquedaAvanzada;
window.recargarTablaDesdeBackend =  recargarTablaDesdeBackend;
window.mostrarModalTemporizado = mostrarModalTemporizado;
window.esconderModalTemporizado = esconderModalTemporizado;
window.abrirModalSino = abrirModalSino;
window.cerrarModalSino = cerrarModalSino;
window.mostrarMensajetempoTemporal = mostrarMensajetempoTemporal;
window.activarDeteccionCambioPaginaNAV = activarDeteccionCambioPaginaNAV;
window.almacenarEstadoTablaOriginal = almacenarEstadoTablaOriginal;

    /**  Configuración de exportación Excel para Tabulator */
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
    
    // Inicialización de variables globales
    window.tabla = null; 
    window.busquedaEnCurso = false;
    window.controladorAbort = null;
    window.todosLosDatos = [];
    window.continuar = true;
    window.ultimaPaginaNAV = null;

    //  Variables internas para callbacks del modal-sino
    window._sinoAceptar = null;
    window._sinoCancelar = null;

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
          mostrarError('expediente', ''); 
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
            mostrarError('expediente', "Formato incorrecto. Debe ser: N/XXX/AA (N: número, X: código, AA: año)");
            this.value = ""; // Opcional: borrar el campo si el formato es incorrecto
            this.focus();
          } else {
            mostrarError('expediente', '');
          }
        });

        // Al obtener el foco, para limpiar el mensaje de error
        campoExpediente.addEventListener("focus", function() {
          mostrarError('expediente', ''); 
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
            mostrarError('op_descrip', ''); 
          }
        });

        campoOpDescrip.addEventListener("focus", function() {
          mostrarError('op_descrip', '');
        });
      }

      // VALIDACIONES PARA FECHA ORDENADO Y TC al perder el foco

      // Validación para campo fecha_ordenado (type="date") con control de año entre 1900 y 2100
      const inputFechaOrdenado = document.getElementById("fecha_ordenado");
      if (inputFechaOrdenado) {
        inputFechaOrdenado.addEventListener("blur", function () {
          const fechaIngresada = this.value.trim();

          if (!fechaIngresada) {
            mostrarError("fecha_ordenado", "Debe ingresar la fecha de ordenado.");
            return;
          }

          const fechaObj = new Date(fechaIngresada);
          if (isNaN(fechaObj.getTime())) {
            mostrarError("fecha_ordenado", "Fecha inválida.");
            return;
          }

          const anioFecha = fechaObj.getFullYear();
          if (anioFecha < 1900 || anioFecha > 2100) {
            mostrarError("fecha_ordenado", "El año debe estar entre 1900 y 2100.");
          } else {
            mostrarError("fecha_ordenado", "");
          }
        });

        inputFechaOrdenado.addEventListener("focus", function () {
          mostrarError("fecha_ordenado", "");
        });
      }

    // Validación para campo fecha_pago (type="date") – permite vacío, pero no incompleto o inválido
    const inputFechaPago = document.getElementById("fecha_pago");
    if (inputFechaPago) {
      inputFechaPago.addEventListener("blur", function () {
        const valorFecha = this.value.trim();

        if (valorFecha === "") {
          mostrarError("fecha_pago", ""); // vacío permitido
          return;
        }

        const fechaDate = this.valueAsDate;
        if (!(fechaDate instanceof Date) || isNaN(fechaDate)) {
          mostrarError("fecha_pago", "Debe ingresar una fecha válida completa.");
          return;
        }

        const anio = fechaDate.getFullYear();
        if (anio < 1900 || anio > 2100) {
          mostrarError("fecha_pago", "El año debe estar entre 1900 y 2100.");
        } else {
          mostrarError("fecha_pago", "");
        }
      });

      inputFechaPago.addEventListener("focus", function () {
        mostrarError("fecha_pago", "");
      });
    }

      // Validación para el campo TC (select)
      const selectTipoComprobante = document.getElementById("tc");
      if (selectTipoComprobante) {
        selectTipoComprobante.addEventListener("blur", function () {
          if (!this.value) {
            mostrarError("tc", "Debe seleccionar un tipo de comprobante.");
          } else {
            mostrarError("tc", "");
          }
        });

        selectTipoComprobante.addEventListener("focus", function () {
          mostrarError("tc", "");
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
      window.tabla = new Tabulator("#tablaOP", {
        index: "id",
        selectable: 1,
        layout: "fitDataStretch",
        pagination: "remote",
        paginationDataReceived: {
        last_page:    "last_page",    // clave en la respuesta PHP
        data:         "data",         // array de filas
        },
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
          {
            title: "F.Ordenado",
            field: "fecha_ordenado",
            sorter: function(a, b) {
              // Función optimizada para convertir y comparar
              const pad = n => n.padStart(2, '0');
              const dateA = a && a !== '0000-00-00' ? a.split('/').reverse().join('') : '00000000';
              const dateB = b && b !== '0000-00-00' ? b.split('/').reverse().join('') : '00000000';
              return dateA.localeCompare(dateB);
            },
            formatter: function(cell) {
              return cell.getValue() || '';
            },
            hozAlign: "center",
            width: 100
          },
          {
            title: "F.Pago",
            field: "fecha_pago", headerFilter: "input", headerFilterFunc: "like", 
            sorter: function(a, b) {
              // Función optimizada para convertir y comparar
              const pad = n => n.padStart(2, '0');
              const dateA = a && a !== '0000-00-00' ? a.split('/').reverse().join('') : '00000000';
              const dateB = b && b !== '0000-00-00' ? b.split('/').reverse().join('') : '00000000';
              return dateA.localeCompare(dateB);
            },
            formatter: function(cell) {
              return cell.getValue() || '';
            },
            hozAlign: "center",
            width: 100
          },
          { title: "TP", field: "tp" },
          { title: "Pago E.", field: "pago_e" },
          { title: "Observaciones", field: "descrip_carga" }
        ],
      });

      window.tabla.on("rowClick", function(e, row) {
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
        window.tabla.setPageSize(Number(selector.value));
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
      window.tabla.on("dataLoaded", traducirBotonesPaginacion);
      window.tabla.on("pageLoaded", traducirBotonesPaginacion);

      //  RDRJOB
       window.tabla.on("tableBuilt", () => {
          activarDeteccionCambioPaginaNAV();    
      });
    });
      
    function cerrarModal() {
      document.getElementById("modalMensaje").style.display = "none";
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

        // Validar Fecha Ordenado (yyyy-mm-dd del input type="date")
        const fechaOrdenado = document.getElementById("fecha_ordenado").value.trim();
        if (!fechaOrdenado) {
        mostrarError('fecha_ordenado', "Debe ingresar la fecha de ordenado.");
        valido = false;
        } else {
        const fechaObj = new Date(fechaOrdenado);
        const anio = fechaObj.getFullYear();
        if (isNaN(fechaObj.getTime()) || anio < 1900 || anio > 2100) {
            mostrarError('fecha_ordenado', "Fecha inválida o fuera de rango (1900-2100).");
            valido = false;
        } else {
            mostrarError('fecha_ordenado', '');
        }
        }
    
      // Validar TC
      const tc = document.getElementById("tc").value;
      if (!tc) {
        mostrarError('tc', "Debe seleccionar un tipo de comprobante.");
        valido = false;
      } else {
        mostrarError('tc', '');
      }

      // Validar tipo comprobante (si se define como campo explícito)
      const tipoComprobante = document.getElementById("tipo_comprobante");
      if (tipoComprobante && tipoComprobante.value.trim() === "") {
        mostrarError('tipo_comprobante', "Tipo de comprobante no válido.");
        valido = false;
      } else if (tipoComprobante) {
        mostrarError('tipo_comprobante', '');
      }

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
          // mostrarMensaje(d.mensaje);
          // mostrarMensajetempoTemporal (d.mensaje,0.75);
          cerrarFormulario();
         
        if (datos.id) {
          // Es una actualización - actualizar la fila existente
          await actualizarFilaTabulator(datos);
        } else {
          // Es un nuevo registro - agregar nueva fila sin recargar toda la tabla
          datos.id = d.id_insertado;
          await agregarNuevaFilaTabulator(datos);
        }

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
    
  //  FUNCIÓN PARA ACTUALIZAR SOLO UNA FILA
  async function actualizarFilaTabulator(datos) {
  // Calcular el estado de cancelado EXACTAMENTE como lo hace el PHP
  const montoTotal = parseFloat(datos.monto_total) || 0;
  const montoPagado = parseFloat(datos.monto_pagado_total) || 0;
  const cancelado = Math.abs(montoTotal - montoPagado) < 0.01 ? "Si" : "No";
  
  // Preparar los datos EXACTAMENTE como los procesa el PHP
  const datosTabla = {
    id: datos.id,
    cancelado: cancelado,
    anio: parseInt(datos.anio),
    nro_comprobante: datos.nro_comprobante.toString(),
    expediente: datos.expediente.trim(),
    tc: datos.tc.trim(),
    tipo_comprobante: datos.tipo_comprobante || "",
    monto_total: montoTotal.toFixed(2),
    monto_pagado_total: montoPagado.toFixed(2),
    op_descrip: datos.op_descrip.trim().toUpperCase(),
    cuenta_pago: datos.cuenta_pago.trim(),
    nombre_cuenta_pago: datos.nombre_cuenta_pago.trim().toUpperCase(),
    cuit_benef: datos.cuit_benef.trim(),
    beneficiario: datos.beneficiario.trim().toUpperCase(),
    fecha_ordenado: formatearFechaSeguro(datos.fecha_ordenado),
    fecha_pago: formatearFechaSeguro(datos.fecha_pago),
    tp: datos.tp.trim(),
    pago_e: datos.pago_e.trim(),
    descrip_carga: datos.descrip_carga.trim().toUpperCase()
  };
  
  // Actualizar la fila específica en Tabulator
  //  window.tabla.updateRow(datos.id, datosTabla);
  //  window.tabla.updateOrAddRow(datos.id, datosTabla);

    ;(async () => {
      mostrarModalTemporizado()

      try {
        // Espera a que Tabulator agregue o actualice la fila
        await window.tabla.updateOrAddRow(datos.id, datosTabla);
        await  recargarTablaDesdeBackend();
        await window.tabla.setPage(window.ultimaPaginaNAV);
        await  new Promise(res => setTimeout(res, 350));
      } catch (err) {
        console.error('Error al actualizar fila:', err)
      } finally {
        esconderModalTemporizado()
      }
    })()

}
  
// FUNCIÓN PARA AGREGAR NUEVA FILA
async function VIEJAFUNC_agregarNuevaFilaTabulator(datos) {
  // 1. Calcular 'cancelado' igual que en tu back-end
  const monto_total        = parseFloat(datos.monto_total)        || 0;
  const monto_pagado_total = parseFloat(datos.monto_pagado_total) || 0;
  const cancelado = Math.abs(monto_total - monto_pagado_total) < 0.01
    ? "Si"
    : "No";

  // 2. Construir el objeto con las mismas keys que tu definición de columnas
  const filaTabla = {
    id: datos.id,
    cancelado: cancelado,
    anio: parseInt(datos.anio, 10),
    nro_comprobante: datos.nro_comprobante.trim(),
    expediente: datos.expediente.trim(),
    tc: datos.tc.trim(),
    tipo_comprobante: datos.tipo_comprobante || "",
    monto_total: monto_total.toFixed(2),
    monto_pagado_total: monto_pagado_total.toFixed(2),
    op_descrip: datos.op_descrip.trim().toUpperCase(),
    cuenta_pago: datos.cuenta_pago.trim(),
    nombre_cuenta_pago: datos.nombre_cuenta_pago.trim().toUpperCase(),
    cuit_benef: datos.cuit_benef.trim(),
    beneficiario: datos.beneficiario.trim().toUpperCase(),
    fecha_ordenado: formatearFechaSeguro(datos.fecha_ordenado),
    fecha_pago: formatearFechaSeguro(datos.fecha_pago),
    tp: datos.tp.trim(),
    pago_e: datos.pago_e.trim(),
    descrip_carga: datos.descrip_carga.trim().toUpperCase(),
  };

  try {
    // 3. Agregar la fila en la página actual (top) y reprocesar filtros/orden
      await window.tabla.addData([filaTabla], true); 
      //await window.tabla.setData(); 
      // await window.tabla.updateOrAddData([filaTabla]);  

    // 4. Con paginación remota: navegar hasta la fila y centrarla
    await window.tabla.scrollToRow(datos.id, true, "center");

    // 5. Seleccionar la fila para resaltarla
    const row = window.tabla.getRow(datos.id);
    if (row) row.select();

    // 6. Preguntar al usuario si quiere ver la nueva Orden de Pago
    abrirModalSino(
      '¿Quiere ver la nueva Orden de Pago?',
      () => {
        // Si confirma, aplicar filtro sobre nro_comprobante
        window.tabla.setFilter('nro_comprobante', '=', filaTabla.nro_comprobante);

        // 2. Verificar visibilidad de btnQuitarFiltros
        const btnQuitarFiltros = document.getElementById('btnQuitarFiltros');
        if (btnQuitarFiltros) {
          const estilo = window.getComputedStyle(btnQuitarFiltros);

          // 1.1 Si NO está visible, lo mostramos
          if (estilo.display === 'none') {
            // ajusta el display según tu CSS (block, inline-block, flex…)
            btnQuitarFiltros.style.display = 'inline-block';
          }
          // 1.2 Si ya está visible (display != 'none'), no hacemos nada
        }        
      },

       // No → muestra temporizado mientras recarga
      async () => {
          mostrarModalTemporizado()
          try {
            await Promise.all([
              recargarTablaDesdeBackend(),
              window.tabla.setPage(window.ultimaPaginaNAV),
              new Promise(res => setTimeout(res, 350))
            ]);
            
          } catch (err) {
            console.error('Fallo al recargar:', err)
          } finally {
            esconderModalTemporizado()
          }
      }
    )

  } catch (err) {
    console.error("Error al agregar/navegar a la nueva fila:", err);
  }
}


export async function ver_002_agregarNuevaFilaTabulator(datos) {
  // 1) Calcular 'cancelado' idéntico al back-end
  const monto_total        = parseFloat(datos.monto_total)        || 0;
  const monto_pagado_total = parseFloat(datos.monto_pagado_total) || 0;
  const cancelado = Math.abs(monto_total - monto_pagado_total) < 0.01
    ? "Si"
    : "No";

  // 2) Preparar el objeto con las mismas keys de columnas
  const filaTabla = {
    id: datos.id,
    cancelado,
    anio: parseInt(datos.anio, 10),
    nro_comprobante: datos.nro_comprobante.trim(),
    expediente: datos.expediente.trim(),
    tc: datos.tc.trim(),
    tipo_comprobante: datos.tipo_comprobante || "",
    monto_total: monto_total.toFixed(2),
    monto_pagado_total: monto_pagado_total.toFixed(2),
    op_descrip: datos.op_descrip.trim().toUpperCase(),
    cuenta_pago: datos.cuenta_pago.trim(),
    nombre_cuenta_pago: datos.nombre_cuenta_pago.trim().toUpperCase(),
    cuit_benef: datos.cuit_benef.trim(),
    beneficiario: datos.beneficiario.trim().toUpperCase(),
    fecha_ordenado: formatearFechaSeguro(datos.fecha_ordenado),
    fecha_pago: formatearFechaSeguro(datos.fecha_pago),
    tp: datos.tp.trim(),
    pago_e: datos.pago_e.trim(),
    descrip_carga: datos.descrip_carga.trim().toUpperCase(),
  };

  try {
    // 3) Insertar la fila (se reprocesan filtros/orden)
    await window.tabla.addData([filaTabla], true);

    // 4) Centrar y seleccionar la nueva fila
    await window.tabla.scrollToRow(datos.id, true, "center");
    const row = window.tabla.getRow(datos.id);
    if (row) row.select();

    // 5) Preguntar al usuario si quiere verla
    abrirModalSino(
      "¿Quiere ver la nueva Orden de Pago?",
      // → Si responde “Sí”
      () => {
        window.tabla.setFilter("nro_comprobante", "=", filaTabla.nro_comprobante);

        const btnQuitarFiltros = document.getElementById("btnQuitarFiltros");
        if (btnQuitarFiltros && getComputedStyle(btnQuitarFiltros).display === "none") {
          btnQuitarFiltros.style.display = "inline-block";
        }
      },
      // → Si responde “No”
      async () => {
        mostrarModalTemporizado();
        try {
          // 6) Recargar el grid completo
          await recargarTablaDesdeBackend();

          // 7) Volver a la página que el usuario estaba viendo
          await window.tabla.setPage(window.ultimaPaginaNAV);
        } catch (err) {
          console.error("Fallo al recargar y restaurar página:", err);
        } finally {
          esconderModalTemporizado();
        }
      }
    );
  } catch (err) {
    console.error("Error al agregar/navegar a la nueva fila:", err);
  }
}

// RDRJOB
export async function agregarNuevaFilaTabulator(datos) {
  // 1) Calcular 'cancelado' como lo hace el backend
  const monto_total        = parseFloat(datos.monto_total)        || 0;
  const monto_pagado_total = parseFloat(datos.monto_pagado_total) || 0;
  const cancelado = Math.abs(monto_total - monto_pagado_total) < 0.01 ? "Si" : "No";

  // 2) Construir el objeto compatible con las columnas del Tabulator
  const filaTabla = {
    id: datos.id,
    cancelado,
    anio: parseInt(datos.anio, 10),
    nro_comprobante: datos.nro_comprobante.trim(),
    expediente: datos.expediente.trim(),
    tc: datos.tc.trim(),
    tipo_comprobante: datos.tipo_comprobante || "",
    monto_total: monto_total.toFixed(2),
    monto_pagado_total: monto_pagado_total.toFixed(2),
    op_descrip: datos.op_descrip.trim().toUpperCase(),
    cuenta_pago: datos.cuenta_pago.trim(),
    nombre_cuenta_pago: datos.nombre_cuenta_pago.trim().toUpperCase(),
    cuit_benef: datos.cuit_benef.trim(),
    beneficiario: datos.beneficiario.trim().toUpperCase(),
    fecha_ordenado: formatearFechaSeguro(datos.fecha_ordenado),
    fecha_pago:      formatearFechaSeguro(datos.fecha_pago),
    tp: datos.tp.trim(),
    pago_e: datos.pago_e.trim(),
    descrip_carga: datos.descrip_carga.trim().toUpperCase(),
  };

  try {
    // 3) Insertar la nueva fila
    await window.tabla.addData([filaTabla], true);
    await recargarTablaDesdeBackend();
    
     window.tabla.on("tableBuilt", () => {
          activarDeteccionCambioPaginaNAV();
      });
     await window.tabla.setPage(window.ultimaPaginaNAV); 
     
    // 4) Verificar si la fila está visible en el DOM del Tabulator
    const row = window.tabla.getRow(datos.id);
    const isVisible = row && row.getElement().offsetParent !== null;

    if (isVisible) {
      // Si la fila está en pantalla, la centro y selecciono
      await window.tabla.scrollToRow(datos.id, true, "center");
      //row.select();
    } else {
      // Si no está visible, pregunto si quiere verla
      abrirModalSino(
        "¿Desea visualizar la nueva Orden de Pago?",
        // → Si elige mostrar
        () => {
          window.tabla.setFilter("nro_comprobante", "=", filaTabla.nro_comprobante);

          const btnQuitarFiltros = document.getElementById("btnQuitarFiltros");
          if (btnQuitarFiltros && getComputedStyle(btnQuitarFiltros).display === "none") {
            btnQuitarFiltros.style.display = "inline-block";
          }
        },
        // → Si elige mantener vista actual
        async () => {
          mostrarModalTemporizado();
          try {
            //await recargarTablaDesdeBackend();
            //await window.tabla.setPage(window.ultimaPaginaNAV);
          } catch (err) {
            console.error("Error al recargar o restaurar página:", err);
          } finally {
            esconderModalTemporizado();
          }
        }
      );
    }
  } catch (err) {
    console.error("Error al insertar y gestionar la nueva fila:", err);
  }
}
