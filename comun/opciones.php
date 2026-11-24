<?php
require_once __DIR__ . '/database.php';

function guagua_ruta_asistencia(): string
{
    $ruta = GuaguaDatabase::environment('GUAGUA_ATTENDANCE_FILE');
    if (is_string($ruta) && trim($ruta) !== '') return trim($ruta);
    $archivo = dirname(__DIR__) . '/apps/asistencia/config_sincronizacion.json';
    $opciones = is_file($archivo) ? json_decode(file_get_contents($archivo), true) : [];
    return is_array($opciones) && is_string($opciones['ruta'] ?? null) ? trim($opciones['ruta']) : '';
}

function guagua_plantilla_institucion(array $opciones): string
{
    $numero = filter_var($opciones['plantilla'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
    if (!$numero) throw new RuntimeException('La institución no tiene una plantilla configurada.');
    // La plantilla 1 vigente es la compartida; las adicionales tienen su carpeta.
    $ruta = $numero === 1 ? __DIR__ . '/plantillas/plantilla_kids_m.php'
        : __DIR__ . '/plantillas/plantilla' . $numero . '/plantilla_kids_m.php';
    if (!is_file($ruta)) throw new RuntimeException('La plantilla configurada de la institución no está instalada.');
    return $ruta;
}
