<?php
declare(strict_types=1);

require_once __DIR__ . '/../comun/conexion.php';
guagua_exigir_rol(['admin', 'directivo', 'docente', 'estudiante']);

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file']) || !is_array($_FILES['file'])) {
    http_response_code(400);
    exit('Solicitud de archivo no válida.');
}

$activityId = filter_input(INPUT_POST, 'id_act_adj', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$file = $_FILES['file'];
if (!$activityId || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
    http_response_code(400);
    exit('No fue posible recibir la imagen.');
}
if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
    http_response_code(413);
    exit('La imagen debe pesar máximo 5 MB.');
}

$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $fileInfo->file((string) $file['tmp_name']);
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
];
if (!isset($allowedTypes[$mime]) || @getimagesize((string) $file['tmp_name']) === false) {
    http_response_code(415);
    exit('El archivo debe ser una imagen JPG, PNG, GIF o WEBP válida.');
}

$directory = __DIR__ . '/imagenes/' . $activityId;
if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
    http_response_code(500);
    exit('No fue posible preparar el almacenamiento.');
}

$relativePath = 'imagenes/' . $activityId . '/' . bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
if (!move_uploaded_file((string) $file['tmp_name'], __DIR__ . '/' . $relativePath)) {
    http_response_code(500);
    exit('No fue posible guardar la imagen.');
}

$studentId = (string) $_SESSION['id_usuario'];
$observation = trim((string) ($_POST['observaciones'] ?? ''));
$statement = $mysqli->prepare('INSERT INTO tarea_adjunto (id_actividad, id_estudiante, fechayhora_adjunto, observacion, adjunto) VALUES (?, ?, NOW(), ?, ?)');
if (!$statement) {
    @unlink(__DIR__ . '/' . $relativePath);
    http_response_code(500);
    exit('No fue posible registrar la imagen.');
}
$statement->bind_param('isss', $activityId, $studentId, $observation, $relativePath);
$saved = $statement->execute();
$statement->close();
if (!$saved) {
    @unlink(__DIR__ . '/' . $relativePath);
    http_response_code(500);
    exit('No fue posible registrar la imagen.');
}

echo '1';
