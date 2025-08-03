function limpiarTexto($texto) {
    // 1. Eliminar caracteres de control (ASCII 0-31)
    $texto = preg_replace('/[\x00-\x1F\x7F]/u', '', $texto);

    // 2. Convertir a UTF-8 normalizado si fuera necesario (si venís de otro charset)
    if (!mb_check_encoding($texto, 'UTF-8')) {
        $texto = utf8_encode($texto);
    }

    // 3. Reemplazar comillas raras, símbolos, etc. (opcional pero recomendado)
    $reemplazos = [
        '“' => '"', '”' => '"',
        '‘' => "'", '’' => "'",
        '–' => '-', '—' => '-',
        '…' => '...', '´' => "'"
    ];
    $texto = strtr($texto, $reemplazos);

    return $texto;
}
