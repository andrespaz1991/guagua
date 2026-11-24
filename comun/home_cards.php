<?php
function guagua_home_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
}
function guagua_home_link($value): string
{
    $url = trim((string)$value);
    $scheme = parse_url($url, PHP_URL_SCHEME);
    if (preg_match('/[\x00-\x20]/',$url) || ($scheme !== null && $scheme !== false && !in_array(strtolower($scheme),['http','https'],true))) return '#';
    return guagua_home_escape($url);
}
function guagua_home_color($value): string
{
    return in_array($value,['default','primary','success','info','warning','danger'],true) ? $value : 'info';
}
/** Las tarjetas seleccionan renderizadores conocidos; nunca ejecutan PHP de la BD. */
function guagua_render_home_card(int $id, array $services): void
{
    $renderers = [
        1 => ['actividad', 'mis_actividades_docente'],
        2 => ['academico', 'valoraciones'],
        3 => ['actividad', 'mis_actividades'],
        4 => ['curso', 'listar_cursos_home'],
        5 => ['red', 'listar_red_home'],
        67 => ['academico', 'misestudiantes'],
        68 => ['academico', 'home_recursos'],
    ];
    if ($id === 69) {
        $services['mensajes']->leer_mensajes(true);
        $services['mensajes']->mensajes_favoritos(true);
        return;
    }
    if (!isset($renderers[$id])) {
        echo '<p class="text-muted">Esta tarjeta requiere un renderizador registrado.</p>';
        return;
    }
    [$service, $method] = $renderers[$id];
    $services[$service]->$method();
}
