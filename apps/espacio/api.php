<?php
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401, 'Inicia sesión para abrir tu espacio.');
$owner = (string) $_SESSION['id_usuario'];
try {
    $dir = __DIR__ . '/data';
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) throw new RuntimeException('storage');
    $db = new PDO('sqlite:' . $dir . '/espacio.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $db->exec('PRAGMA busy_timeout=5000');
    $db->exec('CREATE TABLE IF NOT EXISTS workspaces (owner TEXT PRIMARY KEY, version INTEGER NOT NULL, document TEXT NOT NULL)');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $q = $db->prepare('SELECT version, document FROM workspaces WHERE owner=?');
        $q->execute([$owner]); $row = $q->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['version' => $row ? (int)$row['version'] : 0, 'pages' => $row ? json_decode($row['document'], true) : null]); exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') guagua_error_acceso(405, 'Método no permitido.');
    guagua_exigir_csrf();
    $raw = file_get_contents('php://input', false, null, 0, 2000001);
    if (strlen($raw) > 2000000) guagua_error_acceso(413, 'El espacio supera el límite de 2 MB.');
    $input = json_decode($raw, true);
    if (!is_array($input) || !is_int($input['version'] ?? null) || !is_array($input['pages'] ?? null) || count($input['pages']) > 500) guagua_error_acceso(422, 'Datos no válidos.');
    $ids = [];
    foreach ($input['pages'] as $p) {
        if (!is_array($p) || !is_string($p['id'] ?? null) || !preg_match('/^[a-zA-Z0-9_-]{1,80}$/D', $p['id']) || isset($ids[$p['id']]) || !is_string($p['title'] ?? null) || strlen($p['title']) > 2000 || !is_array($p['blocks'] ?? null) || count($p['blocks']) > 1000) guagua_error_acceso(422, 'Página no válida.');
        $ids[$p['id']] = true;
        foreach ($p['blocks'] as $b) {
            if (isset($b['indent']) && (!is_int($b['indent']) || $b['indent'] < 0 || $b['indent'] > 5)) guagua_error_acceso(422, 'El nivel de la subtarea no es válido.');
            if (!is_array($b) || !in_array($b['type'] ?? '', ['text','h1','h2','bullet','todo','quote','callout','divider'], true) || !is_string($b['text'] ?? null)) guagua_error_acceso(422, 'Bloque no válido.');
            if (isset($b['completedAt']) && (!is_string($b['completedAt']) || !preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z\z/', $b['completedAt']) || strtotime($b['completedAt']) === false)) guagua_error_acceso(422, 'La fecha de cumplimiento no es válida.');
            if (isset($b['link']) && (!is_string($b['link']) || strlen($b['link']) > 2048 || ($b['link'] !== '' && (!filter_var($b['link'], FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($b['link'], PHP_URL_SCHEME)), ['http', 'https'], true))))) guagua_error_acceso(422, 'El enlace debe ser una dirección HTTP o HTTPS válida.');
        }
    }
    $db->exec('BEGIN IMMEDIATE');
    $q = $db->prepare('SELECT version FROM workspaces WHERE owner=?'); $q->execute([$owner]);
    $current = $q->fetchColumn(); $current = $current === false ? 0 : (int)$current;
    if ($current !== $input['version']) { $db->exec('ROLLBACK'); guagua_error_acceso(409, 'Este espacio cambió en otra pestaña. Exporta tus cambios y recarga.'); }
    $q = $db->prepare('INSERT INTO workspaces(owner,version,document) VALUES(?,?,?) ON CONFLICT(owner) DO UPDATE SET version=excluded.version,document=excluded.document');
    $q->execute([$owner, $current + 1, json_encode($input['pages'], JSON_UNESCAPED_UNICODE)]);
    $db->exec('COMMIT'); echo json_encode(['version' => $current + 1]);
} catch (Throwable $e) {
    http_response_code(500); echo json_encode(['message' => 'No se pudo guardar el espacio. Intenta nuevamente.']);
}
