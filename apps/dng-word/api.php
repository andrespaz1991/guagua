<?php
declare(strict_types=1);
require_once __DIR__ . '/lib.php';
dng_session();
dng_cleanup();

function dng_json(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function dng_int(mixed $value, int $min, int $max): int {
    if (!is_scalar($value) || !preg_match('/\A\d+\z/', (string)$value)) throw new RuntimeException('Parámetro numérico inválido.');
    $number = (int)$value;
    if ($number < $min || $number > $max) throw new RuntimeException('Parámetro fuera de rango.');
    return $number;
}
function dng_download(string $path, string $name, string $mime): never {
    if (!is_file($path)) throw new RuntimeException('El archivo ya no está disponible.');
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
    header('Cache-Control: private, no-store');
    readfile($path);
    exit;
}

try {
    $action = (string)($_REQUEST['action'] ?? '');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        $id = (string)($_GET['job'] ?? '');
        $job = dng_job($id);
        $dir = dng_dir($id);
        if ($action === 'download') {
            $key = (string)dng_int($_GET['file'] ?? null, 0, DNG_MAX_FILES - 1);
            $file = $job['files'][$key] ?? null;
            if (!$file || empty($file['output'])) throw new RuntimeException('La imagen no está lista.');
            $extension = $file['format'] === 'png' ? 'png' : 'jpg';
            dng_download($dir . DIRECTORY_SEPARATOR . $key . '.' . $extension,
                dng_safe_name($file['name']) . '.' . $extension,
                $extension === 'png' ? 'image/png' : 'image/jpeg');
        }
        if ($action === 'zip') {
            if (!class_exists(ZipArchive::class)) throw new RuntimeException('La extensión ZIP de PHP no está activa.');
            $zipPath = $dir . DIRECTORY_SEPARATOR . 'imagenes-word.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) throw new RuntimeException('No se pudo crear el ZIP.');
            $added = 0;
            $names = [];
            foreach ($job['files'] as $key => $file) {
                if (empty($file['output'])) continue;
                $extension = $file['format'] === 'png' ? 'png' : 'jpg';
                $path = $dir . DIRECTORY_SEPARATOR . $key . '.' . $extension;
                if (!is_file($path)) continue;
                $base = dng_safe_name($file['name']);
                $name = $base . '.' . $extension;
                if (isset($names[mb_strtolower($name)])) $name = $base . '-' . ((int)$key + 1) . '.' . $extension;
                $names[mb_strtolower($name)] = true;
                $zip->addFile($path, $name);
                $added++;
            }
            $zip->close();
            if ($added === 0) throw new RuntimeException('No hay imágenes convertidas para descargar.');
            dng_download($zipPath, 'imagenes-word.zip', 'application/zip');
        }
        throw new RuntimeException('Acción no válida.');
    }
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') throw new RuntimeException('Método no permitido.');
    guagua_exigir_csrf();
    if ($action === 'validate_dir') {
        $path = dng_validate_output_dir((string)($_POST['path'] ?? ''));
        dng_json(['ok' => true, 'path' => $path]);
    }
    if ($action === 'start') {
        if (dng_engine() === null) throw new RuntimeException('Falta RawTherapee CLI. Instálalo y recarga la página.');
        if (count($_SESSION['dng_jobs']) >= 5) throw new RuntimeException('Hay demasiados lotes activos. Vuelve a intentarlo más tarde.');
        $requestedOutput = trim((string)($_POST['output_dir'] ?? ''));
        $outputDirectory = $requestedOutput === '' ? null : dng_validate_output_dir($requestedOutput);
        $id = bin2hex(random_bytes(16));
        $dir = dng_dir($id);
        if (!@mkdir($dir, 0700, true)) throw new RuntimeException('No se pudo crear el espacio temporal.');
        $_SESSION['dng_jobs'][$id] = ['created' => time(), 'owner' => (string)$_SESSION['id_usuario'], 'files' => [], 'bytes' => 0, 'output_dir' => $outputDirectory];
        dng_json(['ok' => true, 'job' => $id]);
    }
    $id = (string)($_POST['job'] ?? '');
    $job = dng_job($id);
    $dir = dng_dir($id);
    $key = (string)dng_int($_POST['file'] ?? null, 0, DNG_MAX_FILES - 1);
    if ($action === 'chunk') {
        $name = (string)($_POST['name'] ?? '');
        if (!preg_match('/\.dng\z/i', $name) || strlen($name) > 255) throw new RuntimeException('Solo se aceptan archivos .dng.');
        $size = dng_int($_POST['size'] ?? null, 1, DNG_MAX_BYTES);
        $index = dng_int($_POST['index'] ?? null, 0, 9999);
        $upload = $_FILES['chunk'] ?? null;
        if (!$upload || is_array($upload['tmp_name']) || $upload['error'] !== UPLOAD_ERR_OK
            || !is_uploaded_file($upload['tmp_name'])) throw new RuntimeException('No se recibió el fragmento.');
        $chunkBytes = filesize($upload['tmp_name']);
        if ($chunkBytes < 1 || $chunkBytes > DNG_CHUNK_BYTES) throw new RuntimeException('El fragmento supera el límite permitido.');
        $file = $job['files'][$key] ?? null;
        if ($file === null) {
            if ($index !== 0 || count($job['files']) >= DNG_MAX_FILES || $job['bytes'] + $size > 1073741824) {
                throw new RuntimeException('El lote supera el límite de 20 archivos o 1 GB.');
            }
            $file = ['name' => $name, 'size' => $size, 'received' => 0, 'next' => 0, 'ready' => false];
            $job['bytes'] += $size;
        }
        if ($file['ready'] || $file['name'] !== $name || $file['size'] !== $size || $file['next'] !== $index
            || $file['received'] + $chunkBytes > $size) throw new RuntimeException('El orden de carga es incorrecto.');
        if ($index === 0) {
            $handle = fopen($upload['tmp_name'], 'rb');
            $magic = fread($handle, 4);
            fclose($handle);
            if ($magic !== "II*\0" && $magic !== "MM\0*" && $magic !== "II+\0" && $magic !== "MM\0+") {
                throw new RuntimeException('El archivo no tiene una cabecera DNG/TIFF válida.');
            }
        }
        $part = $dir . DIRECTORY_SEPARATOR . $key . '.part';
        $in = fopen($upload['tmp_name'], 'rb');
        $out = fopen($part, 'c+b');
        if (!$in || !$out) throw new RuntimeException('No se pudo guardar el fragmento.');
        flock($out, LOCK_EX);
        if ($index > 0 && filesize($part) < $file['received']) throw new RuntimeException('El archivo temporal quedó incompleto.');
        ftruncate($out, $file['received']);
        fseek($out, $file['received']);
        $written = stream_copy_to_stream($in, $out);
        fflush($out);
        flock($out, LOCK_UN);
        fclose($in);
        fclose($out);
        if ($written !== $chunkBytes) throw new RuntimeException('La carga quedó incompleta.');
        $file['received'] += $chunkBytes;
        $file['next']++;
        if ($file['received'] === $size) {
            if (!rename($part, $dir . DIRECTORY_SEPARATOR . $key . '.dng')) throw new RuntimeException('No se pudo completar el DNG.');
            $file['ready'] = true;
        }
        $job['files'][$key] = $file;
        $_SESSION['dng_jobs'][$id] = $job;
        dng_json(['ok' => true, 'received' => $file['received'], 'ready' => $file['ready']]);
    }
    if ($action === 'convert') {
        $file = $job['files'][$key] ?? null;
        if (!$file || !$file['ready']) throw new RuntimeException('El DNG aún no está cargado.');
        $format = (string)($_POST['format'] ?? '');
        if (!in_array($format, ['jpg', 'png'], true)) throw new RuntimeException('Formato no válido.');
        $edge = dng_int($_POST['edge'] ?? null, 0, 6000);
        if (!in_array($edge, [0, 2800, 3600], true)) throw new RuntimeException('Tamaño no válido.');
        $quality = dng_int($_POST['quality'] ?? null, 80, 100);
        if (!in_array($quality, [90, 95], true)) throw new RuntimeException('Calidad no válida.');
        set_time_limit(300);
        $output = $dir . DIRECTORY_SEPARATOR . $key . '.' . $format;
        $info = dng_convert($dir . DIRECTORY_SEPARATOR . $key . '.dng', $output, $format, $quality, $edge);
        $savedPath = null;
        $saveError = null;
        if (!empty($job['output_dir'])) {
            try {
                $targetDirectory = dng_validate_output_dir((string)$job['output_dir']);
                $savedPath = dng_save_output($output, $targetDirectory, $file['name']);
            } catch (Throwable $saveException) {
                $saveError = $saveException->getMessage();
            }
        }
        $file['output'] = true;
        $file['format'] = $format;
        $file['info'] = $info;
        $file['saved_path'] = $savedPath;
        $job['files'][$key] = $file;
        $_SESSION['dng_jobs'][$id] = $job;
        dng_json(['ok' => true, 'info' => $info, 'saved_path' => $savedPath, 'save_error' => $saveError,
            'download' => 'api.php?action=download&job=' . $id . '&file=' . $key]);
    }
    throw new RuntimeException('Acción no válida.');
} catch (Throwable $e) {
    dng_json(['ok' => false, 'error' => $e->getMessage()], 400);
}





