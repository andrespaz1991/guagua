<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/lib/GuaguaPortableBackup.php';

$checks = 0;
function portable_check(bool $condition, string $message): void
{
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function portable_remove(string $directory): void
{
    if (!is_dir($directory)) return;
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($files as $file) $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    rmdir($directory);
}
$fixture = sys_get_temp_dir() . '/guagua-portable-test-' . bin2hex(random_bytes(8));
mkdir($fixture, 0700);
$originalEnvironment = [];
foreach (['GUAGUA_APP_ENV'=>'local', 'GUAGUA_DB_HOST'=>'127.0.0.1', 'GUAGUA_DB_PORT'=>'3306', 'GUAGUA_DB_USER'=>'origen-privado',
    'GUAGUA_DB_PASSWORD'=>'clave-que-no-debe-viajar', 'GUAGUA_DB_NAME'=>'guagua_fixture', 'GUAGUA_HEALTH_TOKEN_KEY'=>'clave-cifrado-fixture',
    'GUAGUA_DB_HOSTING_PASSWORD'=>'otra-clave-privada'] as $key => $value) {
    $originalEnvironment[$key] = getenv($key);
    putenv($key . '=' . $value);
}
$cacheFixture = $fixture . '-cache';
$backup = new GuaguaPortableBackup($fixture, $cacheFixture);
try {
    $samples = [
        'index.php'=>'<?php echo "Guagua";', 'sga-data/foto/niño.bin'=>"\0\xff\x01Archivo íntegro",
        'comun/vendor/autoload.php'=>'<?php // dependencia instalada', 'apps/demo/database/schema.sql'=>'CREATE TABLE demo (id int);',
        'apps/demo/data/datos.sqlite'=>"SQLite format 3\0datos", 'sga-data/tienda/paquete.zip'=>'paquete de app retirada',
        '.htaccess'=>'Require all granted', 'documentos.zip'=>'archivo del usuario', 'guagua-xampp-anterior.zip'=>'copia anterior',
        '.env'=>"GUAGUA_DB_PASSWORD=clave-de-origen\nGUAGUA_HEALTH_TOKEN_KEY=valor-de-prueba\n",
        '.env.example'=>'GUAGUA_HEALTH_TOKEN_KEY=""', '.env.origen'=>'privado', '.git/config'=>'privado', '.agents/instrucciones'=>'privado',
        'node_modules/demo/index.js'=>'desarrollo', 'tmp/paquete.zip'=>'temporal', 'output/temporal.txt'=>'temporal',
        'guagua-antigua.sql'=>'sql viejo', 'reportes/bd/automatico/antigua.sql'=>'sql viejo', 'reportes/bd/automatico/restaurar.ps1'=>'herramienta',
        'comun/error.log'=>'registro',
    ];
    foreach ($samples as $name => $content) {
        if (!is_dir(dirname($fixture . '/' . $name))) mkdir(dirname($fixture . '/' . $name), 0700, true);
        file_put_contents($fixture . '/' . $name, $content);
    }
    mkdir($fixture . '/sga-data/vacio', 0700);
    $settings = GuaguaDatabase::settings();
    $databases = [['profile'=>'default', 'settings'=>$settings], ['profile'=>'edutask', 'settings'=>array_replace($settings, ['database'=>'edutask_fixture'])]];
    $updates = [];
    $notify = static function (float $percent, string $stage) use (&$updates): void { $updates[] = [$percent, $stage]; };
    $path = $backup->create($databases, ['moodle: ausente en la prueba'], static function (string $file, array $config): void {
        file_put_contents($file, '-- Base: ' . $config['database'] . "\nSELECT 1;\n");
    }, $notify);
    portable_check(count($updates) > 10 && end($updates)[0] === 99.0, 'Falta el avance por etapas de la preparación.');
    $previous = 0;
    foreach ($updates as [$percent, $stage]) {
        portable_check($percent >= $previous && $percent < 100 && $stage !== '', 'El porcentaje retrocede o anuncia el ZIP listo antes de terminar.');
        $previous = $percent;
    }
    portable_check(is_file($path), 'No se creó el ZIP.');
    $zip = new ZipArchive();
    portable_check($zip->open($path, ZipArchive::CHECKCONS) === true, 'El ZIP no pasó la verificación de integridad.');
    foreach (['index.php', 'sga-data/foto/niño.bin', 'comun/vendor/autoload.php', 'apps/demo/database/schema.sql', 'apps/demo/data/datos.sqlite', 'sga-data/tienda/paquete.zip', '.htaccess', 'documentos.zip', 'reportes/bd/automatico/restaurar.ps1'] as $name) {
        portable_check($zip->getFromName('guagua/' . $name) === $samples[$name], 'Archivo alterado o ausente: ' . $name);
    }
    portable_check($zip->locateName('guagua/sga-data/vacio/') !== false, 'No se conservó una carpeta vacía.');
    foreach (['.env.origen', '.git/config', '.agents/instrucciones', 'node_modules/demo/index.js', 'tmp/paquete.zip', 'output/temporal.txt', 'guagua-antigua.sql', 'guagua-xampp-anterior.zip', 'reportes/bd/automatico/antigua.sql', 'comun/error.log'] as $name) {
        portable_check($zip->locateName('guagua/' . $name) === false, 'Se incluyó un archivo excluido: ' . $name);
    }
    $environment = $zip->getFromName('guagua/.env');
    foreach (['GUAGUA_DB_HOST="localhost"', 'GUAGUA_DB_PORT="3306"', 'GUAGUA_DB_USER="root"', 'GUAGUA_DB_PASSWORD=""', 'GUAGUA_DB_NAME="guagua_fixture"', 'GUAGUA_HEALTH_TOKEN_KEY="clave-cifrado-fixture"'] as $expected) {
        portable_check(str_contains($environment, $expected), 'Configuración local incorrecta: ' . $expected);
    }
    foreach (['clave-que-no-debe-viajar', 'origen-privado', 'HOSTING_PASSWORD', 'clave-de-origen'] as $secret) portable_check(!str_contains($environment, $secret), 'Credencial de conexión de origen en la copia.');
    portable_check(str_contains($zip->getFromName('base_de_datos/01-guagua.sql'), 'guagua_fixture'), 'Falta el SQL principal.');
    portable_check(str_contains($zip->getFromName('base_de_datos/02-edutask.sql'), 'edutask_fixture'), 'Falta una base adicional.');
    $manifest = json_decode($zip->getFromName('contenido.json'), true, 512, JSON_THROW_ON_ERROR);
    portable_check(count($manifest['bases']) === 2 && $manifest['bases_omitidas'] === ['moodle: ausente en la prueba'], 'Inventario de bases incorrecto.');
    portable_check(!str_contains(json_encode($manifest), 'password'), 'El inventario revela credenciales.');
    portable_check(str_contains($zip->getFromName('LEEME-XAMPP.txt'), 'moodle: ausente en la prueba'), 'La guía no explica las omisiones.');
    $batch = $zip->getFromName('IMPORTAR_BD.bat');
    portable_check(str_contains($batch, '--password') && str_contains($batch, 'if errorlevel 1') && !preg_match('/(?<!\r)\n/', $batch), 'Asistente de importación incompleto o sin saltos Windows.');
    $zip->close();
    $backup->cleanup();
    portable_check(!file_exists(dirname($path)), 'Quedaron archivos temporales con datos privados.');

    $cacheFiles = glob($cacheFixture . '/archivos-*.zip');
    portable_check(count($cacheFiles) === 1, 'No se creó una única caché de archivos.');
    $cacheHash = hash_file('sha256', $cacheFiles[0]);
    $cachedZip = new ZipArchive();
    $cachedZip->open($cacheFiles[0]);
    portable_check($cachedZip->locateName('guagua/.env') === false && $cachedZip->locateName('base_de_datos/01-guagua.sql') === false, 'La caché reutiliza configuración privada o SQL de otra descarga.');
    $cachedZip->close();
    $updates = [];
    $path = $backup->create($databases, [], static function (string $file): void { file_put_contents($file, "-- SQL recién exportado\nSELECT 2;\n"); }, $notify);
    portable_check(end($updates)[0] === 99.0 && !in_array('Comprimiendo los archivos del sitio…', array_column($updates, 1), true), 'La caché debe informar avance sin volver a comprimir.');
    $zip->open($path, ZipArchive::CHECKCONS);
    portable_check(str_contains($zip->getFromName('base_de_datos/01-guagua.sql'), 'SELECT 2;'), 'La segunda descarga entregó un SQL antiguo.');
    portable_check(hash_file('sha256', $cacheFiles[0]) === $cacheHash, 'Se volvieron a empaquetar archivos que no habían cambiado.');
    $zip->close(); $backup->cleanup();

    file_put_contents($fixture . '/index.php', '<?php echo "Guagua actualizado con contenido nuevo";');
    file_put_contents($fixture . '/nuevo.txt', 'Archivo añadido');
    unlink($fixture . '/documentos.zip');
    $updates = [];
    $path = $backup->create($databases, [], static function (string $file): void { file_put_contents($file, "SELECT 3;\n"); }, $notify);
    portable_check(end($updates)[0] === 99.0 && in_array('Comprimiendo los archivos del sitio…', array_column($updates, 1), true), 'La actualización incremental debe informar la compresión.');
    portable_check($zip->open($path, ZipArchive::CHECKCONS) === true, 'La actualización incremental dañó el ZIP.');
    portable_check(str_contains($zip->getFromName('guagua/index.php'), 'contenido nuevo'), 'No se actualizó un archivo modificado.');
    portable_check($zip->getFromName('guagua/nuevo.txt') === 'Archivo añadido', 'Falta un archivo nuevo.');
    portable_check($zip->locateName('guagua/documentos.zip') === false, 'La copia conservó un archivo eliminado.');
    portable_check($zip->getFromName('guagua/sga-data/foto/niño.bin') === $samples['sga-data/foto/niño.bin'], 'La actualización alteró archivos intactos.');
    portable_check(count(glob($cacheFixture . '/archivos-*.zip')) === 1, 'Se acumularon cachés obsoletas.');
    $zip->close(); $backup->cleanup();
    $cacheLock = fopen($cacheFixture . '/preparacion.lock', 'c');
    flock($cacheLock, LOCK_EX);
    try {
        try {
            $backup->create($databases, [], static function (string $file): void { file_put_contents($file, "SELECT 4;\n"); });
            throw new LogicException('Se permitió otra preparación simultánea.');
        } catch (RuntimeException $error) { portable_check(str_contains($error->getMessage(), 'Otra copia'), 'No se detectó la preparación simultánea.'); }
    } finally { flock($cacheLock, LOCK_UN); fclose($cacheLock); }

    $failing = new GuaguaPortableBackup($fixture);
    try {
        $failing->create($databases, [], static function (string $file): void { file_put_contents($file, 'parcial'); throw new RuntimeException('fallo-controlado'); });
        throw new LogicException('Se aceptó una exportación parcial.');
    } catch (RuntimeException $error) { portable_check($error->getMessage() === 'fallo-controlado', 'Error inesperado en exportación fallida.'); }
    $property = new ReflectionProperty($failing, 'temporary');
    $property->setAccessible(true);
    portable_check($property->getValue($failing) === null, 'No se limpió una exportación fallida.');
    try {
        (new GuaguaPortableBackup($fixture))->create($databases, [], static function (string $file): void { touch($file); });
        throw new LogicException('Se aceptó una exportación vacía.');
    } catch (RuntimeException $error) { portable_check(str_contains($error->getMessage(), 'vacía'), 'No se detectó el SQL vacío.'); }
    $entry = file_get_contents(dirname(__DIR__) . '/reportes/descargar_guagua.php');
    portable_check(str_contains($entry, "guagua_exigir_rol(['admin', 'docente'])") && str_contains($entry, 'guagua_exigir_csrf();'), 'La descarga debe exigir rol administrador o docente y CSRF.');
    echo "OK: $checks comprobaciones de ZIP, archivos binarios, SQL, configuración XAMPP, claves, exclusiones, inventario y limpieza ante fallos.\n";
} finally {
    $backup->cleanup();
    portable_remove($fixture);
    portable_remove($cacheFixture);
    foreach ($originalEnvironment as $key => $value) putenv($value === false ? $key : $key . '=' . $value);
}
