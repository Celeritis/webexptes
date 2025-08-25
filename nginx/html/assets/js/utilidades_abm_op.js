/** utilidades_abm_op.js : Utilidades varias para abm_op2.php  16/08/2025  22:09   **/

import { limpiarTodosLosErrores } from './validaciones_abm_op.js';

/**
     * Formatea montos de manera segura
     * @param {*} valor - El valor a formatear
     * @returns {string} - Monto formateado
     */
    export function formatearMontoSeguro(valor) {
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
    export function formatearFechaSeguro(fecha) {
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
    export function formatearTextoSeguro(texto) {
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
    export function formatearNumeroSeguro(numero) {
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
    export function formatearCUITSeguro(cuit) {
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

    /**
     * Prepara los datos para exportación con formato seguro
     * @param {Array} datos - Array de datos a formatear
     * @returns {Array} - Datos formateados
     */
    export function prepararDatosParaExportacion(datos) {
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
    
    // Función para generar el nombre del archivo con fecha y hora
    export function generarNombreArchivo() {
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

    export async function cargarOpcionesTC(seleccionado = "") {
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

    export async function mostrarFormulario() {
        document.getElementById("formOP").reset();
        document.getElementById("id").value = "";
        document.getElementById("tituloFormulario").textContent = "Nueva Orden de Pago";
        
        limpiarTodosLosErrores();
        await cargarOpcionesTC();
        
        document.getElementById("modalFormulario").style.display = "block";
        const modalContenido = document.querySelector("#modalFormulario .contenido");
        modalContenido.scrollTop = 0;
        setTimeout(() => document.getElementById("anio").focus(), 50);
    }

    export function cerrarFormulario() {
        document.getElementById("modalFormulario").style.display = "none";
    }

    export async function editarOrden() {
        if (typeof window.tabla === "undefined") {
            console.warn("⚠️ La tabla no está inicializada.");
            return mostrarMensaje("Tabla no disponible. Recargue la página.");
        }
        const fila = window.tabla.getSelectedData()[0];
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

      // Función para mostrar mensaje con HTML (mejorada)
      export function mostrarMensaje(texto) {
        const mensajeElement = document.getElementById("mensajeTexto");
        
        // Si el texto contiene HTML, usar innerHTML, si no, usar textContent
        if (texto.includes('<')) {
          mensajeElement.innerHTML = texto;
        } else {
          mensajeElement.textContent = texto;
        }
         
        document.getElementById("mensajeEmergente").style.display = "flex";
      }

      export function cerrarMensaje() {
        document.getElementById("mensajeEmergente").style.display = "none";
      }


    export function mostrarMensajetempoTemporal(texto, segundos) {
      const modal        = document.getElementById('modal-mensajetempo-temporal');
      const mensajeElem  = document.getElementById('modal-mensajetempo-contenido');

      if (!modal || !mensajeElem) return;

      // 1) Rellenar contenido usando innerHTML o textContent
      if (texto.includes('<')) {
        mensajeElem.innerHTML = texto;
      } else {
        mensajeElem.textContent = texto;
      }

      // 2) Mostrar el modal
      modal.classList.add('modal-mensajetempo-mostrar');

      // 3) Ocultar tras el intervalo
      setTimeout(() => {
        modal.classList.remove('modal-mensajetempo-mostrar');
        // Limpiar texto para próximas llamadas
        mensajeElem.textContent = '';
      }, segundos * 1000);
    }


      export function eliminarOrden() {
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

    export function confirmarEliminacion() {
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
                window.tabla.replaceData();
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

    export function cancelarEliminacion() {
        document.getElementById("modalConfirmacion").style.display = "none";
        window.ordenAEliminar = null;
    }

    export function imprimirOrden() {
        const fila = tabla.getSelectedData()[0];
        if (!fila) return mostrarMensaje("Seleccione una orden para imprimir.");
        window.open("imprimir_op.php?id=" + fila.id, "_blank");
    }

    // Función auxiliar para exportar directamente a Excel
    export function exportarAExcel() {
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

    export async function obtenerDatosFiltradosTabulator() {
        // Verificar si la tabla tiene datos
        if (window.tabla.getDataCount() === 0) {
          throw new Error("No hay datos para exportar en la tabla.");
        }
  
        // Obtener datos filtrados de la tabla (respeta paginación remota y filtros)
        //const datos = tabla.getData("visible"); (esto trae la primera página únicamente)
        const datos = window.tabla.getData();
        
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

      export async  function recargarTablaDesdeBackend() {
      try {
        // 1) Opcional: limpia filtros y ordenamientos en memoria
        //window.tabla.clearFilter(true)   // borra todos los filtros
        //window.tabla.clearSort(true)     // borra todos los sorts

        // 2) Petición AJAX para traer *todos* los registros
        const respuesta = await fetch('listar_op.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          // si tu endpoint necesita un flag para devolver todo:
          body: JSON.stringify({ todas: true })
        })

        if (!respuesta.ok) {
          throw new Error(`HTTP ${respuesta.status}`)
        }

        const json = await respuesta.json()
        const datos = json.data || []    // ajusta según tu estructura de respuesta

        // 3) Rellena el Tabulator con el array completo (pasas a paginación local)
        await window.tabla.setData(datos)

      } catch (err) {
        console.error('Error recargando tabla desde backup:', err)
      }
    }

      // Funciones para el modal temporizado
      export function mostrarModalTemporizado() {
        document
          .getElementById('modal-temporizado')
          .classList.add('modal-temporizado-mostrar')
      }

      export function esconderModalTemporizado() {
        document
          .getElementById('modal-temporizado')
          .classList.remove('modal-temporizado-mostrar')
      }

      // Función que abre el modal-sino con texto y callbacks 
      export function abrirModalSino(texto, onAceptar, onCancelar) {
        const modal   = document.getElementById('modal-sino');
        const txtElem = document.getElementById('texto-modal-sino');
        const btnSi   = document.getElementById('btn-sino-si');
        const btnNo   = document.getElementById('btn-sino-no');
 
        // Asigna el texto y guarda callbacks
        txtElem.innerText     =   texto;
            window._sinoAceptar          =     onAceptar;
            window._sinoCancelar         =     onCancelar;

        // Asegura no duplicar listeners
        btnSi.removeEventListener('click', _handleSi);
        btnNo.removeEventListener('click', _handleNo);

        // Registra los controladores
        btnSi.addEventListener   ('click', _handleSi);
        btnNo.addEventListener   ('click', _handleNo);

        // Muestra el modal
        modal.classList.add('modal-sino-show');
      }

      // Función para cerrar el modal-sino y limpiar listeners
      export function cerrarModalSino() {
        const modal = document.getElementById('modal-sino');
        modal.classList.remove('modal-sino-show');
      }

      // Controlador interno para “Sí”
      function _handleSi() {
        cerrarModalSino();
        if (typeof window._sinoAceptar === 'function') window._sinoAceptar();
      }

      // Controlador interno para “No”
      function _handleNo() {
        cerrarModalSino();
        if (typeof window._sinoCancelar === 'function') window._sinoCancelar();
      }


    export function almacenarEstadoTablaOriginal() {
      const tablaInstancia = window.tabla;

      window.tablaEstadoOriginal = {
        // URL configurada en la instancia
        url:   tablaInstancia.options.ajaxURL,
        // Parámetros remotos iniciales: página y tamaño
        params: {
          page: tablaInstancia.getPage(),
          size: tablaInstancia.getPageSize(),
        },
        // Guarda también página y tamaño por separado
        page: tablaInstancia.getPage(),
        size: tablaInstancia.getPageSize(),
        // Estado de filtros y orden antes de la búsqueda
        filters: tablaInstancia.getFilters(),
        sorters: tablaInstancia.getSorters(),
      };
    }

    //  Esta es la peor version 
    export async function V10_quitarFiltros() {
      const btn = document.getElementById("btnQuitarFiltros");
      if (btn) btn.style.display = "none";

      const estado = window.tablaEstadoOriginal;
      if (!estado) {
        console.warn("No existe estado original guardado para restaurar.");
        return;
      }

      const tablaInstancia = window.tabla;

      // 1. Limpiar filtros y orden locales
      tablaInstancia.clearFilter(true);
      tablaInstancia.clearSort();

      // 2. Volver a cargar datos en modo remoto con URL y params guardados
      
      
      try {
        await tablaInstancia.setData(estado.url, {
          page: estado.page,
          size: estado.size,
        });

        // 3. Restaurar orden y filtros si los había
        if (estado.sorters?.length) {
          tablaInstancia.setSort(estado.sorters);
        }
        if (estado.filters?.length) {
          tablaInstancia.setFilter(estado.filters);
        }

        // 4. Finalmente, ir a la página original
        await tablaInstancia.setPage(estado.page);
      } catch (err) {
        console.error("Error restaurando datos remotos:", err);
      } finally {
        // Si no vas a reutilizar este estado, lo descartas
        window.tablaEstadoOriginal = null;
      }
    }

  
    export async function V20_quitarFiltros() {
      mostrarModalTemporizado();

      try {
        // 1) Limpiar filtros y ordenamientos
        window.tabla.clearFilter(true);
        window.tabla.clearSort(true);

        // 2) Obtener máximo de páginas según el último AJAX
        const maxPage = window.tabla.getPageMax() || 1;

        // 3) Ajustar última página al rango válido
        const inicialPage = window.ultimaPaginaNAV || 1;
        const targetPage = Math.min(Math.max(inicialPage, 1), maxPage);

        // 4) Solicitar esa página (Tabulator hace el fetch remoto)
        await window.tabla.setPage(targetPage);

        // 5) Espera breve para UX
        await new Promise(res => setTimeout(res, 100));
      } catch (err) {
        console.error("Error al quitar filtros:", err);
      } finally {
        esconderModalTemporizado();
        const btn = document.getElementById("btnQuitarFiltros");
        if (btn) btn.style.display = "none";
      }
    }

 export async function V30_quitarFiltros() {
  const tabla = window.tabla;

  // 1. Capturamos el estado actual (página, tamaño, filtros y sorters) de la tabla
  const paginaOriginal   = tabla.getPage();
  const tamPaginaOriginal = tabla.getPageSize();
  const filtrosOriginales = tabla.getFilters();
  const sortersOriginales = tabla.getSorters();

  // 2. Ocultamos el botón "Quitar filtros"
  const btn = document.getElementById('btnQuitarFiltros');
  if (btn) btn.style.display = 'none';

  // 3. Vaciar completamente los datos locales y limpiar filtros/ordenamientos
  await tabla.replaceData([]);      // Sale del modo local
  tabla.clearFilter(true);
  tabla.clearSort(true);

  try {
    // 4. Recargar desde el backend usando la URL original y paginación remota
    //    Empieza en la página 1 con el mismo tamaño de página que usabas
    await tabla.setData(tabla.options.ajaxURL, {
      page: 1,
      size: tamPaginaOriginal,
      // Si tu backend acepta sorters/filters por AJAX, también puedes pasarlos aquí.
    });

    // 5. Restaurar tamaño de página si el usuario lo cambió
    if (tabla.getPageSize() !== tamPaginaOriginal) {
      await tabla.setPageSize(tamPaginaOriginal);
    }

    // 6. Volver a aplicar ordenamientos y filtros que tenías antes de la búsqueda
    if (sortersOriginales.length) {
      tabla.setSort(sortersOriginales);
    }
    if (filtrosOriginales.length) {
      tabla.setFilter(filtrosOriginales);
    }

    // 7. Ir a la página original (ajustando al rango disponible)
    const maxPag = tabla.getPageMax() || 1;
    const destino = Math.min(Math.max(paginaOriginal, 1), maxPag);
    await tabla.setPage(destino);
  } catch (err) {
    console.error('Error restaurando datos remotos:', err);
  }
}



    export async function V40_quitarFiltros() {
  // Ocultar el botón inmediatamente
  const btn = document.getElementById('btnQuitarFiltros');
  if (btn) btn.style.display = 'none';

  const estado = window.tablaEstadoOriginal;
  if (!estado) {
    console.warn('No existe estado original guardado para restaurar.');
    return;
  }

  const tabla = window.tabla;

  // 1. Limpiar filtros y ordenamientos actuales (incluye filtros de cabecera)
  tabla.clearFilter(true);
  tabla.clearSort(true);

  try {
    // 2. Restaurar ordenamiento y filtros ANTES de recargar,
    //    de modo que Tabulator los envíe en la misma petición AJAX
    if (estado.sorters?.length) {
      tabla.setSort(estado.sorters);
    }
    if (estado.filters?.length) {
      tabla.setFilter(estado.filters);
    }

    // 3. Recargar datos desde el backend con la URL original.
    //    Empieza en la página 1 para que Tabulator recompute last_page
    await tabla.setData(estado.url, {
      page: 1,
      size: estado.size
    });

    // 4. Restaurar el tamaño de página por si el usuario lo modificó
    if (estado.size !== tabla.getPageSize()) {
      await tabla.setPageSize(estado.size);
    }

    // 5. Calcular la página válida y navegar a ella
    const maxPage = tabla.getPageMax() || 1;
    const target = Math.min(Math.max(estado.page, 1), maxPage);
    await tabla.setPage(target);
  } catch (err) {
    console.error('Error restaurando datos:', err);
  } finally {
    // Descartar el estado guardado para evitar reutilizarlo accidentalmente
    window.tablaEstadoOriginal = null;
  }
}

    // Version  Original
    export async function V50_quitarFiltros() {
      // 1. Muestra el modal de “Actualizando datos”
      mostrarModalTemporizado();

      // 2. Ejecuta recarga AJAX + mínimo 3s de espera
      try {
        await Promise.all([
          recargarTablaDesdeBackend(), 
          window.tabla.setPage(window.ultimaPaginaNAV),
          new Promise(res => setTimeout(res, 55)) 
        ]);
      } catch (err) {
        console.error('Fallo al recargar tabla:', err);
      } finally {
        // 3. Oculta el modal cuando termine todo
        esconderModalTemporizado();
      }

      // 4. Ocultar el botón de quitar filtros
      const btn = document.getElementById('btnQuitarFiltros');
      if (btn) btn.style.display = 'none';
    }

    export async function quitarFiltros() {
      // 1. Muestra el modal de “Actualizando datos”
      mostrarModalTemporizado();

      // 2. Ejecuta recarga AJAX + mínimo 3s de espera
      try {
        await Promise.all([
          recargarTablaDesdeBackend(), 
          window.tabla.setPage(window.ultimaPaginaNAV),
          new Promise(res => setTimeout(res, 55)) 
        ]);
      } catch (err) {
        console.error('Fallo al recargar tabla:', err);
      } finally {
        // 3. Oculta el modal cuando termine todo
        esconderModalTemporizado();
      }

      // 4. Ocultar el botón de quitar filtros
      const btn = document.getElementById('btnQuitarFiltros');
      if (btn) btn.style.display = 'none';
    }


    // Función para mostrar mensaje con cancelar
    export function mostrarMensajeConCancelar(texto) {
      const mensajeElement = document.getElementById("mensajeBATexto");
      mensajeElement.textContent = texto;
      
      // Resetear barra de progreso
      document.querySelector("#barraProgresoBA .progreso").style.width = "0%";
      
      // Mostrar modal específico
      document.getElementById("mensajeBusquedaAvanzada").style.display = "flex";
      
      // Iniciar estado de búsqueda
      window.busquedaEnCurso = true;
      window.controladorAbort = new AbortController();
      window.todosLosDatos = [];
    }

    // Función para mostrar mensaje con  SI / NO
    export function mostrarMensajeSINO(texto, callbackSI, callbackNO) {
      const modal = document.getElementById("modalSINO");
      const textoElemento = document.getElementById("textoModalSINO");
      const botonSI = document.getElementById("btnSINO_SI");
      const botonNO = document.getElementById("btnSINO_NO");

      textoElemento.innerHTML = texto;
      modal.style.display = "flex";

      // Limpiar posibles manejadores anteriores
      botonSI.onclick = null;
      botonNO.onclick = null;

      // Vincular callbacks
      botonSI.onclick = () => {
        modal.style.display = "none";
        if (typeof callbackSI === "function") callbackSI();
      };

      botonNO.onclick = () => {
        modal.style.display = "none";
        if (typeof callbackNO === "function") callbackNO();
      };
    }

    // Función para actualizar progreso
    export function actualizarProgresoBusqueda(obtenidos, total) {
      const barra = document.querySelector("#barraProgresoBA .progreso");
      const texto = document.getElementById("mensajeBATexto");
      
      texto.textContent = `🔍 Obteniendo datos... ${obtenidos} de ${total} registros`;
      
      const porcentaje = Math.min(100, Math.round((obtenidos / total) * 100));
      barra.style.width = `${porcentaje}%`;
      
      if (porcentaje >= 90) barra.style.backgroundColor = "#2E7D32";
      else if (porcentaje >= 50) barra.style.backgroundColor = "#4CAF50";
    }

    // Función para cancelar búsqueda
    export function cancelarBusquedaAvanzada() {
      if (window.busquedaEnCurso && window.controladorAbort) {
        window.controladorAbort.abort();
      }
      cerrarMensajeBusquedaAvanzada();
      
      // Mostrar datos obtenidos hasta el momento
      //if (window.todosLosDatos.length > 0) {
      //  window.tabla.replaceData(window.todosLosDatos);
      //  document.getElementById("btnQuitarFiltros").style.display = "inline-block";
      //  mostrarMensaje(`⚠️ Búsqueda cancelada (${window.todosLosDatos.length} registros obtenidos)`);
      //}
    }

    // Función para cerrar el modal de búsqueda
    export function cerrarMensajeBusquedaAvanzada() {
      document.getElementById("mensajeBusquedaAvanzada").style.display = "none";
      window.busquedaEnCurso = false;
      window.controladorAbort = null;
      window.continuar = false;
    }

    // Función Detectar cambio de pagina en tabulator 

    export function activarDeteccionCambioPaginaNAV() {
      // 1) Guarda la página inicial
        window.ultimaPaginaNAV = window.tabla.getPage();

      // 2) Al cargarse cada página...
      window.tabla.on("pageLoaded", (paginaActualNAV) => {
        // 3) Si cambió, muestro el modal y actualizo mi referencia
        if (paginaActualNAV !== window.ultimaPaginaNAV) {
          //mostrarMensajetempoTemporal(`Página actual: ${paginaActualNAV}`, 0.45);
          window.ultimaPaginaNAV = paginaActualNAV;
        }
      });
    }