<?php
$path = trim((string)($_GET['path'] ?? ''));

function send_json_error(int $status, string $message): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'message' => $message]);
    exit;
}

if ($path === '') {
    send_json_error(400, 'No se recibió la ruta de la plantilla.');
}

$path = preg_replace('#^file:///?#i', '', $path);
$path = str_replace('/', DIRECTORY_SEPARATOR, $path);

if (strpos($path, "\0") !== false) {
    send_json_error(400, 'La ruta de la plantilla contiene caracteres inválidos.');
}

if (is_dir($path)) {
    $preferred = rtrim($path, "\\/") . DIRECTORY_SEPARATOR . 'PLANEADOR2026.docx';
    if (is_file($preferred)) {
        $path = $preferred;
    } else {
        $matches = glob(rtrim($path, "\\/") . DIRECTORY_SEPARATOR . '*.docx') ?: [];
        if (count($matches) === 1) {
            $path = $matches[0];
        } elseif (count($matches) > 1) {
            send_json_error(400, 'La carpeta contiene varias plantillas .docx. Escribe la ruta completa del archivo.');
        } else {
            send_json_error(404, 'La carpeta no contiene ninguna plantilla .docx.');
        }
    }
}

if (!is_file($path)) {
    send_json_error(404, 'No se encontró el archivo de plantilla indicado.');
}

if (!preg_match('/\.docx$/i', $path)) {
    send_json_error(400, 'La ruta de plantilla debe apuntar a un archivo .docx o a una carpeta con una plantilla .docx.');
}

if (!is_readable($path)) {
    send_json_error(500, 'XAMPP no tiene permisos para leer la plantilla.');
}

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . basename($path) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
