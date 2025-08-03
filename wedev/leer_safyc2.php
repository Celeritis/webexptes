<?php
session_start();
header('Content-Type: application/json');

// Función para limpieza básica de texto
function limpiarTexto($texto) {
    $texto = (string)$texto;

    if (!mb_check_encoding($texto, 'UTF-8', true)) {
        $texto = mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1');
    }

    $texto = preg_replace('/[\x00-\x1F\x7F]/u', '', $texto);
    $texto = preg_replace('/[\p{C}]/u', '', $texto);
    $texto = str_replace(['°', 'º'], '°', $texto);
    $texto = preg_replace('/\s+/', ' ', $texto);

    return trim($texto);
}

// Obtener nombre de usuario (asegurarlo siempre en minúsculas, sin espacios ni caracteres raros)
$usuario = isset($_SESSION['usuario']) ? preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($_SESSION['usuario'])) : 'anonimo';

// Definir rutas de archivos
$archivoTemporal = $_FILES['archivo']['tmp_name'] ?? null;
$archivoIntermedio = "resultado_safyc_{$usuario}.txt";
$archivoSanitizado = "resultado_safyc_sanitizado_{$usuario}.txt";

if (!$archivoTemporal || !file_exists($archivoTemporal)) {
    echo json_encode(['error' => 'No se recibió un archivo válido.']);
    exit;
}

// 1) PRIMERA RECORRIDA: Filtrar y guardar en resultado_safyc.txt
file_put_contents($archivoIntermedio, ''); // Vaciar
$lineas = file($archivoTemporal, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$comprobante_visto = [];

foreach ($lineas as $linea) {
    $cols = explode(';', $linea);
    if (count($cols) < 64) continue; // Validar mínimo de columnas

    $nro_comprobante = trim($cols[12]);
    if (in_array($nro_comprobante, $comprobante_visto)) {
        continue; // Saltar duplicados
    }
    $comprobante_visto[] = $nro_comprobante;

    $fecha_ordenado_raw = trim($cols[11]);
    $fecha_ordenado = date_create_from_format('d/m/Y', $fecha_ordenado_raw);
    $anio = $fecha_ordenado ? $fecha_ordenado->format('Y') : '';

    $monto_total = (float) str_replace([',', ' '], ['', ''], $cols[6]);
    $monto_pagado = (float) str_replace([',', ' '], ['', ''], $cols[16]);
    $cancelado = abs($monto_total - $monto_pagado) < 0.01;

    $dato = [
        'cancelado' => $cancelado ? '✔' : '✖',
        'autorizado' => false,
        'anio' => $anio,
        'nro_comprobante' => trim($cols[12]),
        'expediente' => ltrim(preg_replace('/[^0-9\/]/', '', trim($cols[14])), '0'),
        'tc' => trim($cols[4]),
        'tipo_comprobante' => (isset($cols[5]) && trim($cols[5]) !== '') ? trim($cols[5]) : 'SIN TIPO',
        'monto_total' => number_format($monto_total, 2, ',', '.'),
        'monto_pagado_total' => number_format($monto_pagado, 2, ',', '.'),
        'op_descrip' => trim($cols[25]),
        'cuenta_pago' => trim($cols[51]),
        'nombre_cuenta_pago' => trim($cols[52]),
        'cuit_benef' => (strlen(preg_replace('/\D/', '', $cols[58])) === 11) ? preg_replace('/\D/', '', $cols[58]) : '',
        'beneficiario' => trim($cols[59]),
        'fecha_ordenado' => $fecha_ordenado_raw,
        'fecha_pago' => trim($cols[60]),
        'tp' => trim($cols[61]),
        'pago_e' => trim($cols[62])
    ];

    $lineaTexto = json_encode($dato, JSON_UNESCAPED_UNICODE);
    file_put_contents($archivoIntermedio, $lineaTexto . PHP_EOL, FILE_APPEND);
}

// 2) SEGUNDA RECORRIDA: Saneamiento especial y guardar en resultado_safyc_sanitizado.txt
file_put_contents($archivoSanitizado, ''); // Vaciar
$lineasIntermedio = file($archivoIntermedio, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lineasIntermedio as $linea) {
    $linea = trim($linea);
    if ($linea === '') continue; // Saltar líneas en blanco

    // Reemplazar símbolos ✔ y ✖ y autorizados true/false
    $linea = str_replace('"cancelado":"✔","autorizado":false', '"cancelado":"Si","autorizado":"No"', $linea);
    $linea = str_replace('"cancelado":"✖","autorizado":false', '"cancelado":"No","autorizado":"No"', $linea);
    $linea = str_replace('"cancelado":"✔","autorizado":true', '"cancelado":"Si","autorizado":"Si"', $linea);
    $linea = str_replace('"cancelado":"✖","autorizado":true', '"cancelado":"No","autorizado":"Si"', $linea);

    // Reemplazar secuencias \/ por /
    $linea = str_replace('\/', '/', $linea);

    // Guardar la línea limpia en el nuevo archivo
    file_put_contents($archivoSanitizado, $linea . PHP_EOL, FILE_APPEND);
}

// 3) TERCERA RECORRIDA: Leer resultado_safyc_sanitizado.txt y armar el JSON final
$resultadoFinal = [];
$lineasSanitizadas = file($archivoSanitizado, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lineasSanitizadas as $jsonLinea) {
    $dato = json_decode($jsonLinea, true);
    if ($dato) {
        $resultadoFinal[] = $dato;
    }
}

// Devolver JSON final al navegador
echo json_encode($resultadoFinal, JSON_UNESCAPED_UNICODE);
?>
