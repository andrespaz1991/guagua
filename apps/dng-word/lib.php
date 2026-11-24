<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/comun/seguridad.php';

const DNG_MAX_FILES = 20;
const DNG_MAX_BYTES = 209715200;
const DNG_CHUNK_BYTES = 6291456;
const DNG_TTL = 7200;

function dng_session(): void {
    guagua_iniciar_sesion_segura();
    if (empty($_SESSION['id_usuario'])) {
        http_response_code(401);
        exit('Debes iniciar sesión en Guagua para usar este módulo.');
    }
    $_SESSION['dng_jobs'] ??= [];
}
function dng_dir(string $id): string {
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
        . 'guagua-dng-word' . DIRECTORY_SEPARATOR . hash('sha256', session_id())
        . DIRECTORY_SEPARATOR . $id;
}
function dng_remove(string $dir): void {
    if (!is_dir($dir)) return;
    foreach (new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS) as $item) {
        if ($item->isDir()) dng_remove($item->getPathname());
        else @unlink($item->getPathname());
    }
    @rmdir($dir);
}
function dng_cleanup(): void {
    foreach ($_SESSION['dng_jobs'] as $id => $job) {
        if (($job['created'] ?? 0) < time() - DNG_TTL) {
            dng_remove(dng_dir((string)$id));
            unset($_SESSION['dng_jobs'][$id]);
        }
    }
}
function dng_job(string $id): array {
    if (!preg_match('/\A[a-f0-9]{32}\z/', $id) || !isset($_SESSION['dng_jobs'][$id])) {
        throw new RuntimeException('El lote expiró. Vuelve a cargar las imágenes.');
    }
    if ((string)($_SESSION['dng_jobs'][$id]['owner'] ?? '') !== (string)$_SESSION['id_usuario']) throw new RuntimeException('El lote no pertenece a esta sesión.');
    return $_SESSION['dng_jobs'][$id];
}
function dng_engine(): ?string {
    $candidates = [];
    $configured = getenv('GUAGUA_RAWTHERAPEE_CLI');
    if (is_string($configured) && $configured !== '') $candidates[] = $configured;
    $binary = PHP_OS_FAMILY === 'Windows' ? 'rawtherapee-cli.exe' : 'rawtherapee-cli';
    foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $folder) {
        if ($folder !== '') $candidates[] = rtrim($folder, '\\/') . DIRECTORY_SEPARATOR . $binary;
    }
    if (PHP_OS_FAMILY === 'Windows') {
        $candidates[] = dirname(__DIR__, 4) . DIRECTORY_SEPARATOR . 'rawtherapee' . DIRECTORY_SEPARATOR . $binary;
        foreach (['ProgramFiles', 'ProgramFiles(x86)'] as $key) {
            $base = getenv($key);
            if (!$base) continue;
            foreach (glob($base . DIRECTORY_SEPARATOR . 'RawTherapee*', GLOB_ONLYDIR) ?: [] as $folder) {
                $candidates[] = $folder . DIRECTORY_SEPARATOR . $binary;
                foreach (glob($folder . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $sub) {
                    $candidates[] = $sub . DIRECTORY_SEPARATOR . $binary;
                }
            }
        }
    }
    foreach ($candidates as $path) {
        if (is_file($path) && (PHP_OS_FAMILY === 'Windows' || is_executable($path))) return realpath($path) ?: $path;
    }
    return null;
}
function dng_profile(int $edge, string $path): void {
    $profile = "[Resize]\nEnabled=" . ($edge ? 'true' : 'false')
        . "\nAppliesTo=Cropped area\nMethod=Lanczos\nDataSpecified=3\nWidth={$edge}\nHeight={$edge}"
        . "\nAllowUpscaling=false\n\n[Color Management]\nOutputProfile=RTv4_sRGB\n";
    if (file_put_contents($path, $profile, LOCK_EX) === false) throw new RuntimeException('No se pudo preparar el perfil.');
}
function dng_extract_embedded_jpeg(string $dng, string $target): ?array {
    if (!extension_loaded('exif')) return null;
    $metadata = @exif_read_data($dng, null, true, false);
    $ifd = is_array($metadata) ? ($metadata['IFD0'] ?? null) : null;
    if (!is_array($ifd) || (int)($ifd['Compression'] ?? 0) !== 7) return null;
    $offset = $ifd['StripOffsets'] ?? null;
    $length = $ifd['StripByteCounts'] ?? null;
    $width = (int)($ifd['ImageWidth'] ?? 0);
    $height = (int)($ifd['ImageLength'] ?? 0);
    $fileSize = @filesize($dng);
    if (!is_int($offset) || !is_int($length) || $offset < 0 || $length < 100000
        || $length > 100 * 1024 * 1024 || $fileSize === false
        || $offset > $fileSize || $length > $fileSize - $offset
        || min($width, $height) < 2000) return null;
    $source = @fopen($dng, 'rb');
    $destination = @fopen($target, 'xb');
    if (!$source || !$destination) {
        if ($source) fclose($source);
        if ($destination) fclose($destination);
        @unlink($target);
        return null;
    }
    try {
        if (fseek($source, $offset) !== 0 || stream_copy_to_stream($source, $destination, $length) !== $length) return null;
    } finally {
        fclose($source);
        fclose($destination);
    }
    $image = @getimagesize($target);
    if (!$image || $image[2] !== IMAGETYPE_JPEG || $image[0] !== $width || $image[1] !== $height) {
        @unlink($target);
        return null;
    }
    return ['width' => $width, 'height' => $height];
}
function dng_convert(string $input, string $output, string $format, int $quality, int $edge): array {
    $engine = dng_engine();
    if ($engine === null) throw new RuntimeException('Falta RawTherapee CLI en el servidor.');
    $folder = dirname($output);
    $suffix = bin2hex(random_bytes(5));
    $profile = $folder . DIRECTORY_SEPARATOR . "word-{$suffix}.pp3";
    $log = $folder . DIRECTORY_SEPARATOR . "rawtherapee-{$suffix}.log";
    dng_profile($edge, $profile);
    $settings = $folder . DIRECTORY_SEPARATOR . 'rt-settings';
    $cache = $folder . DIRECTORY_SEPARATOR . 'rt-cache';
    foreach ([$settings, $cache] as $runtimeDir) {
        if (!is_dir($runtimeDir) && !@mkdir($runtimeDir, 0700, true)) {
            throw new RuntimeException('No se pudo preparar el espacio de RawTherapee.');
        }
    }
    $environment = getenv();
    if (!is_array($environment)) $environment = [];
    $environment['RT_SETTINGS'] = $settings;
    $environment['RT_CACHE'] = $cache;
    $args = [$engine, '-d', '-p', $profile, '-o', $output];
    if ($format === 'png') { $args[] = '-b8'; $args[] = '-n'; }
    else { $args[] = '-j' . $quality; $args[] = '-js3'; }
    array_push($args, '-Y', '-c', $input);
    try {
        $process = proc_open($args, [0 => ['pipe', 'r'], 1 => ['file', $log, 'w'], 2 => ['file', $log, 'a']], $pipes, $folder, $environment);
        if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar RawTherapee CLI.');
        fclose($pipes[0]);
        $status = proc_close($process);
        if ($status !== 0 || !is_file($output) || filesize($output) === 0) {
            $detail = is_file($log) ? trim((string)file_get_contents($log)) : '';
            throw new RuntimeException('RawTherapee no pudo convertir este DNG.' . ($detail ? ' ' . mb_substr($detail, -500) : ''));
        }
        $image = @getimagesize($output);
        $expected = $format === 'png' ? IMAGETYPE_PNG : IMAGETYPE_JPEG;
        if (!$image || $image[2] !== $expected || $image[0] < 1 || $image[1] < 1) {
            @unlink($output);
            throw new RuntimeException('La imagen generada no es válida.');
        }
        return ['width' => $image[0], 'height' => $image[1], 'bytes' => filesize($output), 'source' => 'raw'];
    } catch (RuntimeException $failure) {
        if (strtolower(pathinfo($input, PATHINFO_EXTENSION)) !== 'dng') throw $failure;
        $preview = $folder . DIRECTORY_SEPARATOR . "preview-{$suffix}.jpg";
        try {
            $embedded = dng_extract_embedded_jpeg($input, $preview);
            if ($embedded === null) throw $failure;
            $info = dng_convert($preview, $output, $format, $quality, $edge);
            $info['source'] = 'embedded_jpeg';
            $info['source_width'] = $embedded['width'];
            $info['source_height'] = $embedded['height'];
            return $info;
        } finally {
            @unlink($preview);
        }
    } finally {
        @unlink($profile);
        @unlink($log);
    }
}
function dng_safe_name(string $name): string {
    $stem = pathinfo($name, PATHINFO_FILENAME);
    $stem = preg_replace('/[^\p{L}\p{N}._-]+/u', '_', $stem) ?: 'imagen';
    return mb_substr(trim($stem, '._-'), 0, 70) ?: 'imagen';
}





function dng_server_output_allowed(): bool {
    return in_array(strtolower((string)($_SESSION['rol'] ?? '')), ['admin', 'directivo', 'docente'], true);
}
function dng_validate_output_dir(string $path): string {
    if (!dng_server_output_allowed()) throw new RuntimeException('Solo el personal autorizado puede guardar en una carpeta del servidor.');
    $path = trim($path);
    if ($path === '' || strlen($path) > 1000 || strpos($path, "\0") !== false) {
        throw new RuntimeException('Escribe una ruta de carpeta válida.');
    }
    if (PHP_OS_FAMILY === 'Windows') {
        if (!preg_match('~\A[A-Za-z]:/~', str_replace('\\', '/', $path))) throw new RuntimeException('Usa una ruta local absoluta, por ejemplo D:\\Imagenes\\Word.');
    } elseif ($path[0] !== '/') {
        throw new RuntimeException('Usa una ruta absoluta.');
    }
    $resolved = realpath($path);
    if ($resolved === false || !is_dir($resolved) || !is_writable($resolved)) {
        throw new RuntimeException('La carpeta debe existir y permitir escritura al servidor Apache.');
    }
    if (PHP_OS_FAMILY === 'Windows' && !preg_match('~\A[A-Za-z]:/~', str_replace('\\', '/', $resolved))) {
        throw new RuntimeException('La carpeta debe estar en una unidad local.');
    }
    $normalized = str_replace('\\', '/', $resolved);
    $webRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2))) ?: dirname(__DIR__, 2);
    $webRoot = str_replace('\\', '/', $webRoot);
    if (PHP_OS_FAMILY === 'Windows') {
        $normalized = strtolower($normalized);
        $webRoot = strtolower($webRoot);
    }
    $webRoot = rtrim($webRoot, '/');
    if ($normalized === $webRoot || str_starts_with($normalized, $webRoot . '/')) {
        throw new RuntimeException('Elige una carpeta fuera de la raíz pública de Apache para proteger tus fotos.');
    }
    if (preg_match('~\A[A-Za-z]:/\z~', $normalized) || $normalized === '/') {
        throw new RuntimeException('Elige una carpeta, no la raíz de la unidad.');
    }
    return $resolved;
}
function dng_save_output(string $source, string $directory, string $originalName): string {
    $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'png'], true)) throw new RuntimeException('Formato de salida no permitido.');
    $stem = dng_safe_name($originalName);
    $size = filesize($source);
    for ($number = 1; $number <= 1000; $number++) {
        $name = $stem . ($number === 1 ? '' : '-' . $number) . '.' . $extension;
        $destination = rtrim($directory, '\\/') . DIRECTORY_SEPARATOR . $name;
        $out = @fopen($destination, 'xb');
        if (!$out) {
            if (file_exists($destination)) continue;
            throw new RuntimeException('No se pudo escribir en la carpeta de salida.');
        }
        $in = @fopen($source, 'rb');
        if (!$in) {
            fclose($out);
            @unlink($destination);
            throw new RuntimeException('No se pudo leer la imagen convertida.');
        }
        $written = stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);
        if ($written !== $size) {
            @unlink($destination);
            throw new RuntimeException('La copia a la carpeta de salida quedó incompleta.');
        }
        return $destination;
    }
    throw new RuntimeException('Hay demasiados archivos con el mismo nombre en la carpeta de salida.');
}

