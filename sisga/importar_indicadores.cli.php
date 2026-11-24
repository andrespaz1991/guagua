<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/database.php';
require_once __DIR__ . '/lib/Indicadores.php';

try {
    $directorio = $argv[1] ?? '';
    if (!$directorio || !is_dir($directorio)) throw new InvalidArgumentException('Uso: php sisga/importar_indicadores.cli.php <carpeta de descargas>');
    $rutas = glob(rtrim($directorio, '/\\') . DIRECTORY_SEPARATOR . 'asignar-indicadores*.js') ?: [];
    if (!$rutas) throw new InvalidArgumentException('No se encontraron scripts asignar-indicadores*.js en esa carpeta.');
    $archivos = array_map(static fn($ruta) => ['nombre' => basename($ruta), 'contenido' => file_get_contents($ruta)], $rutas);
    $repositorio = new SisgaIndicadores(guagua_db_pdo());
    $repositorio->instalar();
    $resumen = $repositorio->importar($archivos);
    $resumen['totalGuardados'] = count($repositorio->listar());
    echo json_encode($resumen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), "\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n"); exit(1);
}
