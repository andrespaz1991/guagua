<?php
/**
 * Modo de presentación de módulos integrados. No concede permisos de acceso.
 * El encabezado del navegador conserva el modo al navegar o enviar formularios
 * dentro del iframe, aunque la nueva URL ya no contenga la bandera original.
 */
function guagua_modulo_embebido(): bool
{
    return isset($_GET['embebido'])
        || ($_GET['guagua_embedded'] ?? '') === '1'
        || ($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '') === 'iframe';
}

/** Conservar el modo integrado al volver al módulo después de un formulario. */
function guagua_url_integrada(string $url): string
{
    if (!guagua_modulo_embebido()) return $url;
    return $url . (str_contains($url, '?') ? '&' : '?') . 'guagua_embedded=1';
}
