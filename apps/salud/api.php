<?php
require_once __DIR__ . '/../../comun/seguridad.php';
require_once __DIR__ . '/../../comun/database.php';
require_once __DIR__ . '/store.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
date_default_timezone_set(GuaguaDatabase::settings()['timezone']);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? 'history';
if (!in_array($method, ['GET','POST'], true)) guagua_error_acceso(405, 'Método no permitido.');
$import = $action === 'import' && $method === 'POST';
$token = '';
if ($import) {
    if (!preg_match('/\ABearer ([a-f0-9]{64})\z/', $_SERVER['HTTP_AUTHORIZATION'] ?? '', $match)) guagua_error_acceso(401, 'Falta la clave de envío del iPhone.');
    $token = $match[1];
} else {
    if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401, 'Inicia sesión en Guagua.');
    if ($method === 'POST' && !guagua_validar_csrf()) guagua_error_acceso(403, 'Recarga la página e intenta de nuevo.');
}
try {
    $store = new GuaguaSalud(new PDO('sqlite:' . __DIR__ . '/data/salud.sqlite'));
    $owner = $import ? $store->owner($token) : (string)$_SESSION['id_usuario'];
    if ($owner === null) guagua_error_acceso(401, 'Clave revocada o incorrecta.');
    if ($import) {
        $raw = file_get_contents('php://input', false, null, 0, 1000001);
        if (strlen($raw) > 1000000) guagua_error_acceso(413, 'Envía el historial en lotes de menos de 1 MB.');
        $input = json_decode($raw, true, 32, JSON_THROW_ON_ERROR);
        if (!is_array($input)) throw new InvalidArgumentException('Se esperaba un objeto JSON.');
        $result = ['ok' => true, 'days' => $store->import($owner, $input)];
    } elseif ($method === 'POST' && $action === 'pair') {
        $result = ['token' => $store->pair($owner)];
    } elseif ($method === 'POST' && $action === 'revoke') {
        $store->revoke($owner); $result = ['ok' => true];
    } elseif ($method === 'GET' && $action === 'history') {
        if (($_GET['source'] ?? '') === 'google') $owner = 'google:' . $owner;
        $from = $_GET['from'] ?? date('Y-m-d', strtotime('-29 days'));
        $to = $_GET['to'] ?? date('Y-m-d');
        foreach ([$from, $to] as $date) {
            if (!is_string($date) || !preg_match('/\A(20[0-9]{2})-([0-9]{2})-([0-9]{2})\z/', $date, $m) || !checkdate((int)$m[2], (int)$m[3], (int)$m[1])) throw new InvalidArgumentException('Rango de fechas no válido.');
        }
        if ($from > $to) throw new InvalidArgumentException('La fecha inicial debe ser anterior a la final.');
        $result = ['days' => $store->history($owner, $from, $to), 'metrics'=>guagua_salud_metrics(), 'paired' => $store->paired((string)$_SESSION['id_usuario'])];
    } else { guagua_error_acceso(404, 'Acción no disponible.'); }
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException | JsonException $e) {
    guagua_error_acceso(422, $e instanceof JsonException ? 'JSON no válido.' : $e->getMessage());
} catch (Throwable $e) {
    error_log('[Guagua Salud] ' . $e->getMessage());
    guagua_error_acceso(500, 'No se pudo guardar o consultar tu actividad.');
}
