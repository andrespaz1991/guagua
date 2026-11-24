<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin','directivo','docente','estudiante','acudiente']);
require_once __DIR__ . '/../comun/config.php';
require_once __DIR__ . '/../comun/files.php';
$relative = $_GET['file'] ?? $_POST['file'] ?? '';
if (!is_string($relative)) guagua_error_acceso(400, 'Ruta inválida.');
$path = guagua_media_path(READFILE_SERVER, $relative);
if ($path === null) guagua_error_acceso(404, 'Archivo no disponible.');
$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$inline = ['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','mp3'=>'audio/mpeg','mp4'=>'video/mp4','ogg'=>'audio/ogg','wav'=>'audio/wav'];
$mime = $inline[$extension] ?? 'application/octet-stream';
$disposition = isset($_GET['descargar']) || isset($_POST['descargar']) || !isset($inline[$extension]) ? 'attachment' : 'inline';
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('Content-Type: ' . $mime);
header("Content-Disposition: " . $disposition . "; filename=\"archivo." . $extension . "\"; filename*=UTF-8''" . rawurlencode(basename($path)));
header('Content-Length: ' . filesize($path));
// Liberar el bloqueo de sesión permite cargar otros módulos durante la descarga.
session_write_close();
readfile($path);
