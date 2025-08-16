/** bus_adv_abm_op.js:  Funciones para la búsqueda avanzada */

import { mostrarError, limpiarTodosLosErrores } from './validaciones_abm_op.js';
import { mostrarMensaje, mostrarMensajeConCancelar, 
        actualizarProgresoBusqueda, cerrarMensajeBusquedaAvanzada,
        almacenarEstadoTablaOriginal } 
        from './utilidades_abm_op.js';
        
export function mostrarBusquedaAvanzada() {
    document.getElementById("formBusquedaAvanzada").reset();
    limpiarTodosLosErrores();
    document.getElementById("modalBusquedaAvanzada").style.display = "flex";
    setTimeout(() => {
        document.getElementById("ba-anio").focus();
    }, 50);
}

export function cerrarBusquedaAvanzada() {
    document.getElementById("modalBusquedaAvanzada").style.display = "none";
}

export async function aplicarBusquedaAvanzada() {
    if (window.busquedaEnCurso) {
        mostrarMensaje("⏳ Ya hay una búsqueda en curso. Espere a que finalice.", "warning");
        return;
    }
    limpiarTodosLosErrores();

    // 0. Verificar si hay filtros avanzados aplicados (mediante visibilidad del botón)
    const btnQuitarFiltros = document.getElementById("btnQuitarFiltros");
    if (btnQuitarFiltros && window.getComputedStyle(btnQuitarFiltros).display !== "none") {
        mostrarMensaje("❌ Primero debe quitar los filtros avanzados actuales para realizar una nueva búsqueda", "error");
        cerrarBusquedaAvanzada();
        return;
    }

    // 1. Validar que haya al menos un criterio de búsqueda
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
    almacenarEstadoTablaOriginal();
        
    // Validar año
    const anio = document.getElementById("ba-anio").value;
    if (anio && (isNaN(anio) || anio < 1900 || anio > 2100)) {
        mostrarError('ba-anio', "El año debe ser un dato consistente. Por Ej. 2023");
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
        mostrarError('ba-expediente', "Formato debe ser N/XXX/YY (Ej. 123/456/78)");
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

    // Mostrar mensaje de carga
    window.busquedaEnCurso = true;
    mostrarMensajeConCancelar("🔍 Buscando registros...");

    try {
        const form = document.getElementById("formBusquedaAvanzada");
        const datos = Object.fromEntries(new FormData(form).entries());
        
        // 0. Preparar filtros para listar_op.php
        const filtros = [];
        
        if (datos.anio) filtros.push({ field: 'anio', value: datos.anio });
        if (datos.nro_comprobante) filtros.push({ field: 'nro_comprobante', value: datos.nro_comprobante });
        if (datos.expediente) filtros.push({ field: 'expediente', value: datos.expediente });
        if (datos.fecha_desde && datos.fecha_hasta) {
            // Convertir fechas al formato YYYY-MM-DD para la base de datos
            const fechaDesdeFormatted = new Date(datos.fecha_desde).toISOString().split('T')[0];
            const fechaHastaFormatted = new Date(datos.fecha_hasta).toISOString().split('T')[0];
            
            filtros.push({ 
                field: 'fecha_pago', 
                value: fechaDesdeFormatted, 
                operator: '>=' 
            });
            filtros.push({ 
                field: 'fecha_pago', 
                value: fechaHastaFormatted, 
                operator: '<=' 
            });
        }

        // 2. Obtener el conteo total primero
        let totalRegistros = 0;
        try {
            const responseCount = await fetch('listar_op.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    page: 1,
                    size: 1,
                    filters: filtros,
                    count_only: true
                })
            });
            const countData = await responseCount.json();
            totalRegistros = countData.total || 0;
        } catch (error) {
            console.error("Error al obtener conteo:", error);
        }

        // 3. Configurar variables para paginación
        let todosLosDatos = [];
        window.todosLosDatos = [];
        let pagina = 1;
        const tamanioLote = 1000;
        window.continuar = true;
        let intentosFallidos = 0;
        const MAX_INTENTOS = 3;

        // 4. Mostrar UI de progreso
        const progresoUI = crearUIProgreso(totalRegistros);
        document.body.appendChild(progresoUI.container);

        // 5. Bucle principal de obtención de datos
        while (window.continuar && pagina <= 100) { // Límite de seguridad
            try {
                
                actualizarProgresoUI(progresoUI, todosLosDatos.length, totalRegistros, pagina);
                actualizarProgresoBusqueda(todosLosDatos.length, totalRegistros);

                const response = await fetch('listar_op.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        page: pagina,
                        size: tamanioLote,
                        filters: filtros
                    })
                });

                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                
                const resultado = await response.json();
                const datosLote = resultado.data || [];
                
                // Verificar si recibimos datos
                if (datosLote.length === 0) {
                    window.continuar = false;
                    break;
                }

                // Agregar datos al acumulador
                todosLosDatos = todosLosDatos.concat(datosLote);
                
                // Actualizar UI periódicamente para mantener responsividad
                if (pagina % 5 === 0 || datosLote.length < tamanioLote) {
                    window.tabla.replaceData(todosLosDatos);
                    actualizarProgresoUI(progresoUI, todosLosDatos.length, totalRegistros, pagina);
                    actualizarProgresoBusqueda(todosLosDatos.length, totalRegistros);
                }

                // Verificar si es el último lote
                if (datosLote.length < tamanioLote) {
                    window.continuar = false;
                } else {
                    pagina++;
                    intentosFallidos = 0; // Resetear contador de fallos
                    
                    // Pequeña pausa para no saturar el servidor
                    await new Promise(resolve => setTimeout(resolve, 300));
                }
            } catch (error) {
                console.error(`Error en lote ${pagina}:`, error);
                intentosFallidos++;
                
                if (intentosFallidos >= MAX_INTENTOS) {
                    mostrarMensaje(`❌ Error crítico después de ${MAX_INTENTOS} intentos`);
                    window.continuar = false;
                } else {
                    // Reintentar después de un breve delay
                    await new Promise(resolve => setTimeout(resolve, 1000 * intentosFallidos));
                }
            }
        }

        // 6. Finalización - Actualizar UI completa
        window.tabla.replaceData(todosLosDatos);
        window.todosLosDatos = todosLosDatos;

        document.getElementById("btnQuitarFiltros").style.display = "inline-block";
        cerrarMensajeBusquedaAvanzada();
        cerrarBusquedaAvanzada();
        
        // Mostrar resumen final
        if (todosLosDatos.length > 0) {
            mostrarMensaje(`✅ Búsqueda completada: ${todosLosDatos.length} registros obtenidos`);
        } else {
            mostrarMensaje("No se encontraron registros");
            window.todosLosDatos = [];
        }
        // Variables de control
        window.busquedaEnCurso = false;
        window.controladorAbort = null;

    } catch (error) {
        console.error("Error general:", error);
        mostrarMensaje(`❌ Error: ${error.message}`);
    } finally {
        // Limpiar UI de progreso si existe
        const progreso = document.getElementById('progreso-busqueda');
        if (progreso) progreso.remove();
    }
}

// Funciones auxiliares para la UI de progreso
function crearUIProgreso(total) {
    const container = document.createElement('div');
    container.id = 'progreso-busqueda';
    container.style.position = 'fixed';
    container.style.bottom = '20px';
    container.style.right = '20px';
    container.style.backgroundColor = 'white';
    container.style.padding = '15px';
    container.style.border = '1px solid #ddd';
    container.style.borderRadius = '5px';
    container.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    container.style.zIndex = '10000';
    
    const titulo = document.createElement('h4');
    titulo.textContent = 'Progreso de Búsqueda';
    titulo.style.marginTop = '0';
    titulo.style.marginBottom = '10px';
    
    const texto = document.createElement('div');
    texto.id = 'progreso-texto';
    texto.textContent = total > 0 ? 
        `Obteniendo datos... 0 de ${total} registros` : 
        'Obteniendo datos...';
    
    const barraContainer = document.createElement('div');
    barraContainer.style.width = '300px';
    barraContainer.style.height = '20px';
    barraContainer.style.backgroundColor = '#f0f0f0';
    barraContainer.style.borderRadius = '10px';
    barraContainer.style.marginTop = '10px';
    barraContainer.style.overflow = 'hidden';
    
    const barra = document.createElement('div');
    barra.id = 'progreso-barra';
    barra.style.height = '100%';
    barra.style.width = '0%';
    barra.style.backgroundColor = '#4CAF50';
    barra.style.transition = 'width 0.3s ease';
    
    const detalle = document.createElement('div');
    detalle.id = 'progreso-detalle';
    detalle.style.marginTop = '10px';
    detalle.style.fontSize = '0.9em';
    detalle.style.color = '#666';
    
    barraContainer.appendChild(barra);
    container.appendChild(titulo);
    container.appendChild(texto);
    container.appendChild(barraContainer);
    container.appendChild(detalle);
    
    return {
        container,
        texto,
        barra,
        detalle
    };
}

function actualizarProgresoUI(ui, obtenidos, total, pagina) {
    ui.texto.textContent = total > 0 ? 
        `Obteniendo datos... ${obtenidos} de ${total} registros` : 
        `Obteniendo datos... ${obtenidos} registros`;
    
    ui.detalle.textContent = `Página ${pagina} | Lote de 1000 registros`;
    
    if (total > 0) {
        const porcentaje = Math.min(100, Math.round((obtenidos / total) * 100));
        ui.barra.style.width = `${porcentaje}%`;
        
        if (porcentaje >= 90) {
            ui.barra.style.backgroundColor = '#2E7D32';
        } else if (porcentaje >= 50) {
            ui.barra.style.backgroundColor = '#4CAF50';
        }
    }
}
