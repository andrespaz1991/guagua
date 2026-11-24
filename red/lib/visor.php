<?php
/** Utilidades del visor, sin consultas ni efectos sobre la respuesta HTTP. */
function red_ruta_relativa_valida($ruta)
{
    return $ruta !== ''
        && !preg_match('/[\x00-\x1f\x7f]/', $ruta)
        && strpos($ruta, chr(92)) === false
        && substr($ruta, 0, 2) !== '//'
        && !preg_match('/^[a-z][a-z0-9+.-]*:/i', $ruta);
}

function red_actividades_scorm($ruta)
{
    if (!red_ruta_relativa_valida($ruta)) {
        return [];
    }
    $raiz = realpath(__DIR__ . '/..');
    $base = realpath(__DIR__ . '/../' . $ruta);
    if (!$base || !$raiz || strpos($base, $raiz . DIRECTORY_SEPARATOR) !== 0 || !is_file($base . '/imsmanifest.xml')) {
        return [];
    }
    $errores_xml = libxml_use_internal_errors(true);
    $manifest = simplexml_load_file($base . '/imsmanifest.xml', 'SimpleXMLElement', LIBXML_NONET);
    $actividades = [];
    if ($manifest !== false) {
        foreach ($manifest->xpath('//*[local-name()="resources"]/*[local-name()="resource"]') as $nodo) {
            $href = trim((string) $nodo['href']);
            $ruta_decodificada = rawurldecode($href);
            if (!red_ruta_relativa_valida($href) || !red_ruta_relativa_valida($ruta_decodificada)
                || substr($href, 0, 1) === '/' || in_array('..', explode('/', $ruta_decodificada), true)) {
                continue;
            }
            $actividades[] = ['url' => rtrim($ruta, '/') . '/' . $href, 'nombre' => basename($href)];
        }
    }
    libxml_clear_errors();
    libxml_use_internal_errors($errores_xml);
    return $actividades;
}
