<?php
require_once __DIR__ . '/comun/seguridad.php';
guagua_exigir_rol(['admin','directivo','docente']);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__ . '/comun/database.php';
require_once __DIR__ . '/usuario/lib/estudiantes_home.php';
try {
    foreach (['nombre','nid','name'] as $key) if (isset($_GET[$key]) && !is_string($_GET[$key])) throw new InvalidArgumentException('Filtro inválido.');
    $students = guagua_estudiantes_home(guagua_db_mysqli(), $_SESSION, $_GET['nombre'] ?? '', $_GET['nid'] ?? '', $_GET['name'] ?? '');
    echo json_encode($students, JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
} catch (InvalidArgumentException $error) {
    guagua_error_acceso(422, $error->getMessage());
} catch (Throwable $error) {
    error_log('[Guagua estudiantes inicio] ' . get_class($error) . ' (' . $error->getCode() . ')');
    guagua_error_acceso(500, 'No se pudo consultar el directorio.');
}
