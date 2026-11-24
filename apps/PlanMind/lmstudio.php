<?php
require_once __DIR__ . '/../../comun/seguridad.php';
require_once __DIR__ . '/lib/lmstudio.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
guagua_exigir_rol(guagua_roles_gestion_curricular());

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['message' => 'Método no permitido.']);
    exit;
}
guagua_exigir_origen_legado();
// La generación puede tardar; no bloquear otras peticiones de esta sesión.
session_write_close();
set_time_limit(320);

try {
    $request = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($request) || !is_string($request['endpoint'] ?? null)
        || !is_string($request['operation'] ?? null)
        || !is_array($request['payload'] ?? [])) {
        throw new InvalidArgumentException('Solicitud de LM Studio no válida.');
    }
    $data = planmind_lmstudio_request($request['endpoint'], $request['operation'], $request['payload'] ?? []);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException | JsonException $error) {
    http_response_code(422);
    echo json_encode(['message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    http_response_code(502);
    echo json_encode(['message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
}
