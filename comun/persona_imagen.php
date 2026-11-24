<?php
/** Género registrado y avatar común; nunca se infiere por el nombre. */
function guagua_genero_normalizado($genero): string
{
    $value = strtolower(trim((string)$genero));
    if (in_array($value, ['f', 'femenino', 'femenina', 'female', 'mujer'], true)) return 'f';
    if (in_array($value, ['m', 'masculino', 'male', 'hombre'], true)) return 'm';
    return '';
}

function guagua_foto_predeterminada($foto): bool
{
    $name = strtolower(basename(str_replace('\\', '/', trim((string)$foto))));
    return in_array($name, ['', 'user-icon.png', 'user-iconf.png', 'user-icon2.png', 'user-iconf2.png'], true);
}

function guagua_persona_foto($foto, $genero = ''): string
{
    $gender = guagua_genero_normalizado($genero);
    $fallback = $gender === 'f' ? 'user-iconf.png' : ($gender === 'm' ? 'user-icon.png' : 'user-icon2.png');
    $name = basename(str_replace('\\', '/', trim((string)$foto)));
    if (guagua_foto_predeterminada($name) || !is_file(dirname(__DIR__) . '/sga-data/foto/' . $name)) return $fallback;
    return $name;
}
