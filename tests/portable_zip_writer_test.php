<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/lib/GuaguaPortableZipWriter.php';
$directory = sys_get_temp_dir() . '/guagua-zip-writer-' . bin2hex(random_bytes(8));
mkdir($directory, 0700);
$checks = 0;
$check = static function (bool $condition, string $message) use (&$checks): void { $checks++; if (!$condition) throw new RuntimeException($message); };
$input = $directory . '/grande.bin';
$handle = fopen($input, 'xb');
$block = str_repeat("\0\xffÁrbol y datos binarios\n", 4096);
for ($i = 0; $i < 128; $i++) fwrite($handle, $block);
fclose($handle);
file_put_contents($directory . '/codigo.php', '<?php ' . str_repeat('echo "Guagua";', 1000));
try {
    foreach ([true, false] as $streaming) {
        $path = $directory . '/' . ($streaming ? 'continuo' : 'nativo') . '.zip';
        $writer = new GuaguaPortableZipWriter($path, $streaming);
        if ($streaming) {
            $property = new ReflectionProperty($writer, 'streaming'); $property->setAccessible(true);
            $check($property->getValue($writer) !== null, 'No se utilizó la biblioteca instalada para escritura continua.');
        }
        $writer->addDirectory('guagua/vacío');
        $writer->addFile($input, 'guagua/imagen.jpg');
        $writer->addFile($directory . '/codigo.php', 'guagua/codigo.php');
        $writer->addText('LEEME.txt', "Guagua 🚌\r\nInstrucciones\r\n");
        $writer->finish();
        $zip = new ZipArchive();
        $check($zip->open($path, ZipArchive::CHECKCONS) === true, 'ZIP inválido.');
        $check($zip->locateName('guagua/vacío/') !== false, 'Carpeta vacía ausente.');
        $check($zip->statName('guagua/imagen.jpg')['comp_method'] === ZipArchive::CM_STORE, 'Se recomprimió un formato ya comprimido.');
        $check($zip->statName('guagua/codigo.php')['comp_method'] === ZipArchive::CM_DEFLATE, 'El código perdió su compresión rápida.');
        $check($zip->getFromName('LEEME.txt') === "Guagua 🚌\r\nInstrucciones\r\n", 'Texto alterado.');
        $stream = $zip->getStream('guagua/imagen.jpg');
        $hash = hash_init('sha256'); hash_update_stream($hash, $stream); fclose($stream);
        $check(hash_final($hash) === hash_file('sha256', $input), 'Se alteró un archivo grande o binario.');
        $zip->close();
        $writer = new GuaguaPortableZipWriter($path, false, true);
        $writer->remove('guagua/codigo.php');
        $writer->addText('guagua/nuevo.txt', 'Nuevo');
        $writer->finish();
        $zip->open($path, ZipArchive::CHECKCONS);
        $check($zip->locateName('guagua/codigo.php') === false && $zip->getFromName('guagua/nuevo.txt') === 'Nuevo', 'Actualización incremental incorrecta.');
        $zip->close();
    }
    echo "OK: $checks comprobaciones de ambos escritores ZIP, integridad de archivos grandes, UTF-8, carpetas vacías, compresión y actualización incremental.\n";
} finally {
    foreach (glob($directory . '/*') ?: [] as $file) unlink($file);
    rmdir($directory);
}
