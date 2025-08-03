export function configurarPaginacion(tabla) {
  const selectorTop = document.createElement("select");
  selectorTop.style.marginBottom = "10px";
  [20, 50, 100, 500].forEach(size => {
    const opt = document.createElement("option");
    opt.value = size;
    opt.textContent = size;
    selectorTop.appendChild(opt);
  });

  selectorTop.value = tabla.getPageSize();
  selectorTop.addEventListener("change", e => {
    tabla.setPageSize(Number(e.target.value));
  });

  // Insertarlo inmediatamente después del header del grid
  const header = document.querySelector("#tablaUsuarios .tabulator-header");
  if (header) header.parentNode.insertBefore(selectorTop, header);

  traducirBotonesPaginacion();
  tabla.on("dataLoaded", traducirBotonesPaginacion);
  tabla.on("pageLoaded", traducirBotonesPaginacion);
}

export function traducirBotonesPaginacion() {
  const botones = document.querySelectorAll(".tabulator-paginator button");
  botones.forEach(btn => {
    if (btn.textContent === "First") btn.textContent = "Primero";
    if (btn.textContent === "Last") btn.textContent = "Último";
    if (btn.textContent === "Prev") btn.textContent = "Anterior";
    if (btn.textContent === "Next") btn.textContent = "Siguiente";
  });
}
