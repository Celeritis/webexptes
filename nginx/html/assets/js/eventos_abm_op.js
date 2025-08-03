
export function manejarEventos() {
    document.addEventListener("DOMContentLoaded", () => {
        configurarEventosFormulario();
        configurarEventosBusqueda();
        configurarEventosTabulator();
    });
}


function configurarEventosFormulario() {
    const form = document.getElementById("formOP");
    if (!form) return;

    form.addEventListener("submit", function(e) {
        e.preventDefault();
        validarYEnviarFormulario();
    });

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
}

export { configurarEventosFormulario };

function configurarEventosBusqueda() {
    const btnBusquedaAvanzada = document.getElementById("btnBusquedaAvanzada");
    if (!btnBusquedaAvanzada) {
        console.warn("⚠️ btnBusquedaAvanzada no encontrado en el DOM");
        return;
    }
    btnBusquedaAvanzada.addEventListener("click", mostrarBusquedaAvanzada);

    const btnQuitarFiltros = document.getElementById("btnQuitarFiltros");
    if (!btnQuitarFiltros) {
        console.warn("⚠️ btnQuitarFiltros no encontrado en el DOM");
        return;
    }
    btnQuitarFiltros.addEventListener("click", quitarFiltros);
}


export { configurarEventosBusqueda };

function configurarEventosTabulator() {
    if (typeof tabla !== "undefined") {
        tabla.on("rowClick", function(e, row) {
            row.select();
        });
    }
}

export { configurarEventosTabulator };
