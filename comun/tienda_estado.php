<?php
/** Consulta liviana del estado; no abre conexiones ni modifica el catálogo. */
function guagua_tienda_desinstaladas(): array
{
    static $folders;
    if ($folders !== null) return $folders;
    $file = dirname(__DIR__) . '/sga-data/tienda/catalogo.json';
    if (!is_file($file)) return $folders = [];
    $state = json_decode((string)file_get_contents($file), true);
    if (!is_array($state) || ($state['version'] ?? null) !== 1 || !isset($state['apps']) || !is_array($state['apps'])) {
        throw new RuntimeException('No se puede leer el catálogo de aplicaciones.');
    }
    $folders = [];
    foreach ($state['apps'] as $app) {
        if (($app['estado'] ?? '') === 'disponible') $folders[] = strtolower($app['carpeta']);
    }
    return $folders;
}

function guagua_tienda_app_disponible(string $url): bool
{
    $path = rawurldecode(str_replace('\\', '/', (string)parse_url($url, PHP_URL_PATH)));
    if (!preg_match('~(?:^|/)apps/([^/]+)(?:/|$)~i', $path, $match)) return true;
    return !in_array(strtolower($match[1]), guagua_tienda_desinstaladas(), true);
}

/** Fragmento interno para que el directorio conserve sus totales y paginación. */
function guagua_tienda_filtro_menu(mysqli $db): string
{
    $sql = '';
    foreach (guagua_tienda_desinstaladas() as $folder) {
        $variants = array_unique([$folder, strtolower(rawurlencode($folder))]);
        $pattern = '(^|/)apps/(' . implode('|', array_map(static fn($value) => preg_quote($value, '~'), $variants)) . ')(/|[?#]|$)';
        $sql .= " AND NOT (LOWER(REPLACE(COALESCE(menu_url, ''), '../', '')) REGEXP '"
            . $db->real_escape_string($pattern) . "')";
    }
    return $sql;
}
