<?php
declare(strict_types=1);
ini_set('display_errors', '0');
require_once __DIR__ . '/../comun/seguridad.php';
require_once __DIR__ . '/../comun/lib/GuaguaPortableProgress.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    guagua_error_acceso(405, 'Método no permitido.');
}
// Solo lee el avance de esta sesión. La descarga comprueba el rol vigente en MySQL.
// No repetir esas consultas cada dos segundos mientras se está exportando MySQL.
guagua_iniciar_sesion_segura();
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401, 'Debe iniciar sesión.');
if (!in_array((string)($_SESSION['rol'] ?? ''), ['admin', 'docente'], true)) guagua_error_acceso(403, 'No tiene permiso para esta operación.');
$owner = session_id();
session_write_close();
$id = $_GET['descarga'] ?? '';
if (!is_string($id) || !preg_match('/^[a-f0-9]{32}$/D', $id)) guagua_error_acceso(400, 'Solicitud de descarga no válida.');
try {
    $progress = new GuaguaPortableProgress(dirname(__DIR__), $id, $owner);
    $state = $progress->read();
    if ($state === null) {
        http_response_code(202);
        $state = ['status'=>'pending'];
    }
    echo json_encode($state, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    guagua_error_acceso(500, 'No se pudo consultar el avance.');
}
