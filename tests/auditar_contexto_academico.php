<?php
/** Auditoría de solo lectura del registro de módulos y de sus dependencias. */
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../comun/academico.php';
$root = dirname(__DIR__);
$db = guagua_db_mysqli();
$modules = $db->query('SELECT menu_item_id, menu_item_name, menu_url FROM menu_items2 ORDER BY menu_item_id')->fetch_all(MYSQLI_ASSOC);
$sources = [];
$iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    function ($entry) use ($root) {
        if (!$entry->isDir()) return true;
        if (str_replace('\\', '/', $entry->getPathname()) === str_replace('\\', '/', $root . '/comun/lib')) return false;
        return !in_array($entry->getFilename(), ['.git', 'vendor', 'node_modules', 'PHPWord-master', 'tests', 'output']);
    }
));
foreach ($iterator as $file) {
    if (strtolower($file->getExtension()) !== 'php') continue;
    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    $text = file_get_contents($file->getPathname());
    if (preg_match('/ano_lectivo|estado_periodo|guagua_(?:ano|periodo)|academic_year/', $text)) $sources[] = $relative;
}
$summary = [
    'tipo' => 'Auditoría estática y comprobación de configuración real; no equivale a navegar cada módulo con sesión.',
    'ano_activo' => guagua_ano_activo($db),
    'periodo_activo' => guagua_periodo_activo($db),
    'modulos_registrados' => count($modules),
    'fuentes_con_contexto_academico' => $sources,
    'registro_modulos' => $modules,
    'criterio' => 'Los módulos académicos usan la configuración compartida. Los catálogos, herramientas independientes e históricos explícitos conservan su alcance propio.',
];
$path = $root . '/output/auditoria-contexto-academico.json';
file_put_contents($path, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo count($modules) . " módulos en el registro; " . count($sources) . " fuentes PHP con referencias académicas.\n";
echo 'Año activo: ' . $summary['ano_activo']['nombre_ano_lectivo'] . '; período activo: ' . $summary['periodo_activo']['nombre_periodo'] . ".\n";
echo "Inventario: output/auditoria-contexto-academico.json\n";
