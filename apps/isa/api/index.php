<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../comun/database.php';


header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://127.0.0.1:1420');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function respond(array $payload, int $status = 200): never {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requestBody(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        respond(['error' => 'Invalid JSON body.'], 400);
    }
    return $decoded;
}

function validId(mixed $value): bool {
    return is_string($value) && preg_match('/^[a-zA-Z0-9-]{8,36}$/', $value) === 1;
}

function database(): mysqli {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $db = guagua_db_mysqli();

        return $db;
    } catch (mysqli_sql_exception $exception) {
        respond(['error' => 'Local database is unavailable.', 'detail' => $exception->getMessage()], 503);
    }
}

$db = database();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'bootstrap') {
        $profile = $db->query('SELECT display_name, learner_context FROM isa_profiles WHERE id = 1')->fetch_assoc();
        $conversations = $db->query('SELECT id, title, created_at, updated_at FROM isa_conversations ORDER BY updated_at DESC')->fetch_all(MYSQLI_ASSOC);
        respond(['profile' => $profile ?: ['display_name' => '', 'learner_context' => ''], 'conversations' => $conversations]);
    }

    if ($method === 'GET' && $action === 'conversation') {
        $id = $_GET['id'] ?? '';
        if (!validId($id)) respond(['error' => 'Invalid conversation id.'], 400);
        $statement = $db->prepare('SELECT id, title, created_at, updated_at FROM isa_conversations WHERE id = ?');
        $statement->bind_param('s', $id);
        $statement->execute();
        $conversation = $statement->get_result()->fetch_assoc();
        if (!$conversation) respond(['error' => 'Conversation not found.'], 404);
        $messages = $db->prepare('SELECT role, body, correction FROM isa_messages WHERE conversation_id = ? ORDER BY message_order ASC');
        $messages->bind_param('s', $id);
        $messages->execute();
        $conversation['messages'] = $messages->get_result()->fetch_all(MYSQLI_ASSOC);
        respond($conversation);
    }

    if ($method === 'POST' && $action === 'save') {
        $payload = requestBody();
        $conversation = $payload['conversation'] ?? [];
        $messages = $payload['messages'] ?? [];
        $profile = $payload['profile'] ?? [];
        if (!is_array($conversation) || !validId($conversation['id'] ?? null) || !is_array($messages) || !is_array($profile)) {
            respond(['error' => 'Invalid conversation payload.'], 400);
        }
        $id = $conversation['id'];
        $title = mb_substr((string)($conversation['title'] ?? 'New conversation'), 0, 160);
        $displayName = mb_substr((string)($profile['displayName'] ?? ''), 0, 120);
        $learnerContext = (string)($profile['learnerContext'] ?? '');

        $db->begin_transaction();
        $saveConversation = $db->prepare('INSERT INTO isa_conversations (id, title) VALUES (?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), updated_at = CURRENT_TIMESTAMP');
        $saveConversation->bind_param('ss', $id, $title);
        $saveConversation->execute();
        $saveProfile = $db->prepare('INSERT INTO isa_profiles (id, display_name, learner_context) VALUES (1, ?, ?) ON DUPLICATE KEY UPDATE display_name = VALUES(display_name), learner_context = VALUES(learner_context)');
        $saveProfile->bind_param('ss', $displayName, $learnerContext);
        $saveProfile->execute();
        $deleteMessages = $db->prepare('DELETE FROM isa_messages WHERE conversation_id = ?');
        $deleteMessages->bind_param('s', $id);
        $deleteMessages->execute();
        $insertMessage = $db->prepare('INSERT INTO isa_messages (conversation_id, message_order, role, body, correction) VALUES (?, ?, ?, ?, ?)');
        foreach ($messages as $order => $message) {
            $role = $message['role'] ?? '';
            $body = (string)($message['text'] ?? '');
            $correction = isset($message['correction']) && $message['correction'] !== '' ? (string)$message['correction'] : null;
            if (!in_array($role, ['isa', 'user'], true) || $body === '') continue;
            $insertMessage->bind_param('sisss', $id, $order, $role, $body, $correction);
            $insertMessage->execute();
        }
        $db->commit();
        respond(['ok' => true]);
    }

    if ($method === 'DELETE' && $action === 'conversation') {
        $id = $_GET['id'] ?? '';
        if (!validId($id)) respond(['error' => 'Invalid conversation id.'], 400);
        $statement = $db->prepare('DELETE FROM isa_conversations WHERE id = ?');
        $statement->bind_param('s', $id);
        $statement->execute();
        respond(['ok' => true]);
    }

    respond(['error' => 'Unknown action.'], 404);
} catch (mysqli_sql_exception $exception) {
    if ($db->errno) $db->rollback();
    respond(['error' => 'Database operation failed.', 'detail' => $exception->getMessage()], 500);
}
