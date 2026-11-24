<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

function responder(int $estado, bool $ok, string $mensaje, array $extra = []): void
{
    http_response_code($estado);
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(405, false, 'Método no permitido.');
}

if (empty($_FILES['imagen']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {
    responder(400, false, 'No se recibió una imagen válida.');
}

if ((int) $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    responder(400, false, 'La imagen no pudo cargarse correctamente.');
}

if ((int) $_FILES['imagen']['size'] > 15 * 1024 * 1024) {
    responder(413, false, 'La imagen supera el límite de 15 MB.');
}

$imagen = file_get_contents($_FILES['imagen']['tmp_name']);
if ($imagen === false || substr($imagen, 0, 8) !== "\x89PNG\r\n\x1a\n") {
    responder(400, false, 'El archivo recibido no es una imagen PNG.');
}

$configFile = __DIR__ . DIRECTORY_SEPARATOR . 'config_rutas.json';
$config = is_file($configFile) ? json_decode((string) file_get_contents($configFile), true) : [];
$rutaSalida = is_array($config) ? trim((string) ($config['ruta_salida_whatsapp'] ?? '')) : '';
if ($rutaSalida === '') {
    $rutaSalida = __DIR__ . DIRECTORY_SEPARATOR . 'salidas_whatsapp';
}

if (!is_dir($rutaSalida) && !mkdir($rutaSalida, 0775, true) && !is_dir($rutaSalida)) {
    responder(500, false, 'No se pudo crear la carpeta de salida configurada.');
}

if (!is_writable($rutaSalida)) {
    responder(500, false, 'La carpeta de salida configurada no tiene permisos de escritura.');
}

$nombre = (string) ($_POST['nombre'] ?? 'resumen_whatsapp');
$nombre = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $nombre) ?? 'resumen_whatsapp';
$nombre = trim($nombre, '_') ?: 'resumen_whatsapp';
$archivo = $nombre . '_' . date('Ymd_His') . '.png';
$destino = rtrim($rutaSalida, "\\/") . DIRECTORY_SEPARATOR . $archivo;

if (file_put_contents($destino, $imagen, LOCK_EX) === false) {
    responder(500, false, 'No se pudo guardar la imagen en la carpeta configurada.');
}

responder(200, true, 'Imagen guardada correctamente.', ['archivo' => $archivo]);
