<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/EvaluationService.php';

header('Content-Type: application/json; charset=utf-8');

/** @param mixed $data */
function respond(bool $success, mixed $data = null, ?string $message = null, int $statusCode = 200): never
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** @return array<string,mixed> */
function jsonBody(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody === false || trim($rawBody) === '') {
        return [];
    }
    $body = json_decode($rawBody, true);
    if (!is_array($body)) {
        throw new InvalidArgumentException('La solicitud no contiene un JSON válido.');
    }

    return $body;
}

try {
    $action = (string) ($_GET['action'] ?? '');
    $service = new EvaluationService(EvidenceDatabase::connect());

    switch ($action) {
        case 'evaluations':
            respond(true, $service->listEvaluations());

        case 'dashboard':
            respond(true, $service->getDashboard((int) ($_GET['evaluation_id'] ?? 0)));

        case 'evaluation':
            respond(true, $service->getDetail((int) ($_GET['evaluation_id'] ?? 0)));

        case 'score':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Use POST para registrar puntajes.');
            }
            $data = jsonBody();
            respond(true, $service->updateScore((int) ($data['competencia_id'] ?? 0), $data['puntaje_val_1'] ?? null, $data['puntaje_val_2'] ?? null));

        case 'settings':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Use POST para actualizar la concertación.');
            }
            $data = jsonBody();
            respond(true, $service->updateEvaluationSettings((int) ($data['evaluacion_id'] ?? 0), $data));

        case 'state':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Use POST para actualizar el estado.');
            }
            $data = jsonBody();
            respond(true, $service->updateEvaluationState((int) ($data['evaluacion_id'] ?? 0), (string) ($data['estado'] ?? '')));

        case 'create_evaluation':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Use POST para crear una evaluación.');
            }
            respond(true, $service->createEvaluation(jsonBody()));

        case 'attachment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new InvalidArgumentException('Use POST para adjuntar una evidencia.');
            }
            respond(true, $service->addAttachment($_POST, $_FILES, __DIR__));

        default:
            throw new InvalidArgumentException('Acción no reconocida.');
    }
} catch (InvalidArgumentException $exception) {
    respond(false, null, $exception->getMessage(), 422);
} catch (Throwable $exception) {
    respond(false, null, $exception->getMessage(), 500);
}
