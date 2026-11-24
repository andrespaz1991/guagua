<?php
/** Compatibilidad de los enlaces locales del gestor retirado. */
function guagua_tienda_es_gestor_antiguo(string $url): bool
{
    $parts = parse_url(str_replace('\\', '/', $url));
    if ($parts === false || isset($parts['host']) || isset($parts['scheme'])) return false;
    return (bool)preg_match('~(?:^|/)comun/gestor_menu\.php$~i', (string)($parts['path'] ?? ''));
}
