<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método no permitido.']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Solicitud inválida.']);
    exit;
}

$directory = trim((string)($payload['directory'] ?? ''));
$filename = trim((string)($payload['filename'] ?? ''));
$contentBase64 = (string)($payload['contentBase64'] ?? '');

if ($directory === '' || $filename === '' || $contentBase64 === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Faltan datos para guardar el documento.']);
    exit;
}

if (strpos($directory, "\0") !== false || strpos($filename, "\0") !== false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'La ruta contiene caracteres inválidos.']);
    exit;
}

$filename = preg_replace('/[\\\\\/:*?"<>|]+/', '_', $filename);
if (!preg_match('/\.docx$/i', $filename)) {
    $filename .= '.docx';
}

if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'No se pudo crear o acceder a la carpeta de destino.']);
    exit;
}

if (!is_writable($directory)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'La carpeta de destino no tiene permisos de escritura para XAMPP.']);
    exit;
}

$content = base64_decode($contentBase64, true);
if ($content === false) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'El contenido del documento no es válido.']);
    exit;
}

$fullPath = rtrim($directory, "\\/") . DIRECTORY_SEPARATOR . $filename;

if (file_put_contents($fullPath, $content) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'No se pudo escribir el archivo en la ruta configurada.']);
    exit;
}

echo json_encode(['ok' => true, 'path' => $fullPath]);
