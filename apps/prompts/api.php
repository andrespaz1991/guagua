<?php
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401, 'Inicia sesión para abrir tus prompts.');
$method = $_SERVER['REQUEST_METHOD'];
if (!in_array($method, ['GET', 'POST'], true)) guagua_error_acceso(405, 'Método no permitido.');
if ($method === 'POST') guagua_exigir_csrf();
try {
    $db = new PDO('sqlite:' . __DIR__ . '/data/prompts.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('PRAGMA busy_timeout=5000');
    $db->exec('CREATE TABLE IF NOT EXISTS libraries (owner TEXT PRIMARY KEY, version INTEGER NOT NULL, document TEXT NOT NULL)');
    $owner = (string) $_SESSION['id_usuario'];
    if ($method === 'GET') {
        $q = $db->prepare('SELECT version, document FROM libraries WHERE owner=?'); $q->execute([$owner]); $row = $q->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['version' => $row ? (int)$row['version'] : 0, 'library' => $row ? json_decode($row['document'], true) : ['categories' => [], 'prompts' => []]]); exit;
    }
    $raw = file_get_contents('php://input', false, null, 0, 2000001);
    if (strlen($raw) > 2000000) guagua_error_acceso(413, 'La biblioteca supera el límite de 2 MB.');
    $input = json_decode($raw, true); $library = $input['library'] ?? null;
    if (!is_int($input['version'] ?? null) || !is_array($library) || !is_array($library['categories'] ?? null) || !is_array($library['prompts'] ?? null) || count($library['categories']) > 100 || count($library['prompts']) > 1000) guagua_error_acceso(422, 'Biblioteca no válida.');
    $categories = []; $ids = [];
    foreach (['categories', 'prompts'] as $collection) foreach ($library[$collection] as $item) {
        if (!is_array($item) || !is_string($item['id'] ?? null) || !preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $item['id']) || isset($ids[$item['id']]) || !is_string($item['color'] ?? null) || !preg_match('/^#[a-fA-F0-9]{6}$/D', $item['color'])) guagua_error_acceso(422, 'Elemento no válido.');
        $ids[$item['id']] = true;
        if ($collection === 'categories') {
            if (!is_string($item['name'] ?? null) || trim($item['name']) === '' || mb_strlen($item['name']) > 60) guagua_error_acceso(422, 'Nombre de categoría no válido.');
            $categories[$item['id']] = true;
        } else {
            foreach (['title' => 180, 'content' => 50000, 'tags' => 300, 'category' => 80, 'updated' => 40] as $field => $limit) if (!is_string($item[$field] ?? null) || mb_strlen($item[$field]) > $limit) guagua_error_acceso(422, 'Prompt no válido.');
            if (trim($item['title']) === '' || trim($item['content']) === '' || !is_bool($item['favorite'] ?? null) || ($item['category'] !== '' && !isset($categories[$item['category']]))) guagua_error_acceso(422, 'Revisa el título, el contenido y la categoría.');
        }
    }
    $db->exec('BEGIN IMMEDIATE');
    $q = $db->prepare('SELECT version FROM libraries WHERE owner=?'); $q->execute([$owner]); $version = (int)$q->fetchColumn();
    if ($version !== $input['version']) { $db->exec('ROLLBACK'); guagua_error_acceso(409, 'La biblioteca cambió en otra pestaña. Exporta tus datos y recarga antes de continuar.'); }
    $q = $db->prepare('INSERT INTO libraries(owner,version,document) VALUES(?,?,?) ON CONFLICT(owner) DO UPDATE SET version=excluded.version, document=excluded.document');
    $q->execute([$owner, $version + 1, json_encode($library, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
    $db->exec('COMMIT'); echo json_encode(['version' => $version + 1]);
} catch (Throwable $error) {
    error_log('[Guagua prompts] ' . $error->getMessage());
    guagua_error_acceso(500, 'No se pudo acceder a la biblioteca. Intenta nuevamente.');
}
