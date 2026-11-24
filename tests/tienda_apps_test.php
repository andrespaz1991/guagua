<?php
/** Prueba destructiva únicamente sobre una raíz temporal creada por esta prueba. */
declare(strict_types=1);
require_once dirname(__DIR__) . '/comun/lib/GuaguaAppStore.php';
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

$fixture = dirname(__DIR__) . '/tmp/tienda-test-' . bin2hex(random_bytes(8));
mkdir($fixture . '/apps/Mi App/data/vacia', 0700, true);
mkdir($fixture . '/apps/PlanMind', 0700, true);
mkdir($fixture . '/apps/oculta - copia', 0700, true);
file_put_contents($fixture . '/apps/Mi App/index.php', '<?php echo "Prueba á 🧩";');
file_put_contents($fixture . '/apps/Mi App/data/datos.json', str_repeat('{"ejemplo":"Árbol"}', 500));
file_put_contents($fixture . '/apps/Mi App/data/binario.bin', "\0\x01\xff\0");
file_put_contents($fixture . '/apps/PlanMind/index.php', '<?php');
file_put_contents($fixture . '/apps/oculta - copia/index.php', '<?php');
$checks = 0;
function store_check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    $checks++;
}
function store_reject(callable $action, string $message): void {
    try { $action(); } catch (RuntimeException $error) { store_check(true, $message); return; }
    throw new RuntimeException($message);
}
function store_cleanup(string $path): void {
    $safeBase = str_replace('\\', '/', (string)realpath(dirname(__DIR__) . '/tmp')) . '/tienda-test-';
    $real = str_replace('\\', '/', (string)realpath($path));
    if (!str_starts_with($real, $safeBase)) throw new RuntimeException('La limpieza salió de la raíz temporal.');
    $walk = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($walk as $file) $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($path);
}
try {
    $store = new GuaguaAppStore($fixture);
    $catalog = $store->catalog([['menu_url'=>'../apps/Mi App/', 'menu_item_name'=>'Nombre del menú', 'menu_description'=>'Descripción del menú']]);
    store_check(count($catalog) === 2, 'No debe mostrar copias ni herramientas sin entrada.');
    $app = array_values(array_filter($catalog, static fn($app) => $app['carpeta'] === 'Mi App'))[0];
    $id = $app['id'];
    store_check($app['nombre'] === 'Nombre del menú' && $app['descripcion'] === 'Descripción del menú', 'Debe reutilizar los nombres del menú.');
    store_check(str_contains($app['ruta'], 'Mi%20App'), 'Las rutas deben codificar espacios.');
    $size = $store->details($id);
    store_check($size['archivos'] === 3 && $size['bytes'] > 1000, 'El tamaño debe incluir archivos y datos.');
    $protected = array_values(array_filter($catalog, static fn($app) => $app['carpeta'] === 'PlanMind'))[0];
    store_reject(fn() => $store->uninstall($protected['id']), 'Debe proteger dependencias compartidas.');
    store_check(is_file($fixture . '/apps/PlanMind/index.php'), 'La app protegida debe seguir instalada.');
    foreach (['../', '../../comun', 'a/b', str_repeat('a', 24)] as $invalid) store_reject(fn() => $store->uninstall($invalid), 'Debe rechazar ids ajenos al catálogo.');
    store_reject(fn() => $store->backup($id, [], static function () { throw new RuntimeException('Exportador fallido'); }), 'Un respaldo fallido debe detenerse.');
    store_check(is_file($fixture . '/apps/Mi App/index.php'), 'Fallar el respaldo no puede retirar la app.');
    $backup = $store->backup($id, [], static function ($path) { file_put_contents($path, "-- Datos de prueba\nSELECT 1;\n"); });
    $zip = new ZipArchive(); $zip->open($backup);
    store_check($zip->getFromName('datos/base-de-datos.sql') === "-- Datos de prueba\nSELECT 1;\n", 'El respaldo debe incluir SQL.');
    store_check($zip->getFromName('app/data/binario.bin') === "\0\x01\xff\0", 'El respaldo debe conservar datos binarios.');
    $zip->close(); unlink($backup);
    $lock = fopen($fixture . '/sga-data/tienda/tienda.lock', 'c'); flock($lock, LOCK_EX);
    store_reject(fn() => $store->uninstall($id), 'Debe impedir operaciones simultáneas.');
    flock($lock, LOCK_UN); fclose($lock);
    $original = hash_file('sha256', $fixture . '/apps/Mi App/data/datos.json');
    $result = $store->uninstall($id);
    store_check(!is_dir($fixture . '/apps/Mi App'), 'Debe retirar realmente la carpeta activa.');
    store_check($result['paquete_bytes'] < $result['bytes'], 'El paquete debe comprimir los archivos de prueba.');
    store_check(!$result['limpieza_pendiente'], 'La desinstalación debe limpiar la carpeta temporal.');
    $available = array_values(array_filter($store->catalog(), static fn($app) => $app['id'] === $id))[0];
    store_check($available['estado'] === 'disponible' && $available['reinstalable'], 'Debe seguir visible y reinstalable.');
    store_check(!isset($available['sha256']) && !isset($available['paquete']), 'El catálogo no debe revelar rutas privadas.');
    store_reject(fn() => $store->uninstall($id), 'No puede desinstalar dos veces.');
    $stateFile = $fixture . '/sga-data/tienda/catalogo.json';
    $saved = json_decode(file_get_contents($stateFile), true);
    $package = $fixture . '/sga-data/tienda/paquetes/' . $saved['apps'][$id]['paquete'];
    $goodPackage = file_get_contents($package);
    file_put_contents($package, 'ZIP dañado');
    store_reject(fn() => $store->install($id), 'Debe rechazar un paquete modificado.');
    store_check(!is_dir($fixture . '/apps/Mi App'), 'Un paquete dañado no puede crear una app parcial.');
    file_put_contents($package, $goodPackage);
    mkdir($fixture . '/apps/Mi App');
    store_reject(fn() => $store->install($id), 'Nunca debe sobrescribir una carpeta existente.');
    rmdir($fixture . '/apps/Mi App');
    $store->install($id);
    store_check(hash_file('sha256', $fixture . '/apps/Mi App/data/datos.json') === $original, 'Debe restaurar los datos exactamente.');
    store_check(is_dir($fixture . '/apps/Mi App/data/vacia'), 'Debe restaurar carpetas vacías.');
    store_check(file_get_contents($fixture . '/apps/Mi App/data/binario.bin') === "\0\x01\xff\0", 'Debe restaurar datos binarios.');
    store_check(!is_file($package), 'No debe dejar un segundo paquete después de instalar.');
    store_reject(fn() => $store->install($id), 'No puede instalar dos veces.');
    $store->uninstall($id);
    $store->install($id);
    store_check(is_file($fixture . '/apps/Mi App/index.php'), 'El ciclo debe poder repetirse.');
    file_put_contents($stateFile, '{contenido inválido');
    store_reject(fn() => $store->uninstall($id), 'Un catálogo dañado no puede retirar archivos.');
    store_check(is_file($fixture . '/apps/Mi App/index.php'), 'La app debe sobrevivir a un catálogo inválido.');
    echo "OK: $checks comprobaciones de catálogo, respaldo, retirada real, compresión, integridad, concurrencia y reinstalación sin pérdida.\n";
} finally { store_cleanup($fixture); }
