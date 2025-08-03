export function mostrarMensaje(texto) {
  document.getElementById('mensajeTexto').textContent = texto;
  document.getElementById('mensajeEmergente').style.display = 'flex';
}

export function cerrarModal(id) {
  document.getElementById(id).style.display = 'none';
}

export function cerrarMensaje() {
  cerrarModal('mensajeEmergente');
}
