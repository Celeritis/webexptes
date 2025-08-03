<?php
// Definir la constante primero
define('RUTA_ACTUAL2', dirname(__DIR__, 0));

// Contenido a guardar (leyenda + constante)
$contenido = "El nombre de usuario es : " . $nombre;

// Ruta completa del archivo de logs
$rutaArchivo = 'C:\devs\Logs-webextes\logs.TXT';

// Guardar en el archivo (con FILE_APPEND para añadir al final y LOCK_EX para bloquear el archivo)
file_put_contents($rutaArchivo, $contenido, FILE_APPEND | LOCK_EX);

// Opcional: Verificar si se escribió correctamente

if (file_exists($rutaArchivo)) {
    echo "La información se ha guardado correctamente en $rutaArchivo";
} else {
    echo "Error al guardar el archivo";
}
?>