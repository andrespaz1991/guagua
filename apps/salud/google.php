<?php
require_once __DIR__ . '/../../comun/seguridad.php';
require_once __DIR__ . '/../../comun/database.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/google_health.php';
require_once __DIR__ . '/google_connection.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401, 'Inicia sesión en Guagua.');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') guagua_error_acceso(405, 'Método no permitido.');
if (!guagua_validar_csrf()) guagua_error_acceso(403, 'Recarga Guagua para continuar.');
date_default_timezone_set(GuaguaDatabase::settings()['timezone']);
try {
    $raw = file_get_contents('php://input', false, null, 0, 12001);
    if (strlen($raw) > 12000) guagua_error_acceso(413, 'Solicitud demasiado grande.');
    $input = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
    $connection=guagua_health_connection();
    $owner=(string)$_SESSION['id_usuario'];
    $token=$connection->accessToken($owner);
    $from = $input['from'] ?? date('Y-m-d', strtotime('-29 days'));
    $to = $input['to'] ?? date('Y-m-d');
    if (!is_string($from) || !is_string($to)) throw new InvalidArgumentException('Fechas no válidas.');
    $type = $input['type'] ?? 'steps';
    if (!is_string($type)) throw new InvalidArgumentException('Métrica no válida.');
    try { $days = (new GuaguaGoogleHealth())->sync($token, $from, $to, $type); }
    catch (GuaguaGoogleHealthError $e) {
        if ($e->reason!=='UNAUTHENTICATED') throw $e;
        $days=(new GuaguaGoogleHealth())->sync($connection->accessToken($owner,true),$from,$to,$type);
    }
    $store = new GuaguaSalud(new PDO('sqlite:' . __DIR__ . '/data/salud.sqlite'));
    // Fuente independiente: no sobrescribir ni sumar los datos enviados por iPhone.
    if ($days) $store->import('google:' . (string)$_SESSION['id_usuario'], ['days'=>$days]);
    echo json_encode(['ok'=>true, 'days'=>count($days), 'message'=>$days
        ? 'Google Health conectado. Se actualizaron ' . count($days) . ' días para esta métrica.'
        : 'Google Health no devolvió registros para esta métrica en el periodo.'], JSON_UNESCAPED_UNICODE);
} catch (GuaguaGoogleHealthError $e) {
    http_response_code($e->status); echo json_encode(['reason'=>$e->reason,'message'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (InvalidArgumentException | JsonException $e) {
    guagua_error_acceso(422, $e instanceof JsonException ? 'JSON no válido.' : $e->getMessage());
} catch (Throwable $e) {
    // No registrar tokens ni respuestas con datos de salud.
    error_log('[Guagua Google Health] ' . get_class($e));
    guagua_error_acceso(500, 'No se pudo completar la conexión con Google Health.');
}
