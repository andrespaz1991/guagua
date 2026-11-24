<?php
/** Vista y ciclo completos sobre apps ficticias. php -S 127.0.0.1:8099 tests/tienda_ui_router.php */
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(404); exit; }
$root = dirname(__DIR__);
$fixture = $root . '/tmp/tienda-ui-qa';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (in_array($path, ['/guagua/comun/tienda.css', '/guagua/comun/tienda.js', '/guagua/comun/tienda_menu.css', '/guagua/comun/tienda_menu.js', '/guagua/comun/tienda_tema.css', '/guagua/comun/tienda_tema.js'], true)) {
    header('Content-Type: ' . (str_ends_with($path, '.css') ? 'text/css' : 'application/javascript'));
    readfile($root . '/comun/' . basename($path)); exit;
}
if ($path === '/guagua/comun/img/png/icon-sga.php') { header('Content-Type: text/css'); exit; }
if ($path === '/guagua/comun/tienda.php') {
    $base = 'http://127.0.0.1:8099/guagua'; $csrf = 'solo-pruebas-locales';
    $_SESSION = ['rol'=>'admin', 'nombre'=>'Pruebas'];
    function tienda_e($text): string { return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    $source = file_get_contents($root . '/comun/tienda.php');
    $html = substr($source, strpos($source, '?>') + 2);
    eval('?>' . str_replace('__DIR__', var_export($root . '/comun', true), $html)); exit;
}
if ($path !== '/guagua/comun/tienda_api.php') { http_response_code(404); exit; }
require_once $root . '/comun/lib/GuaguaAppStore.php';
if (!is_dir($fixture)) {
    foreach (['collage', 'evidencias', 'MiMercado', 'PlanMind', 'Ruleta', 'simulador_notas', 'espacio', 'prompts'] as $folder) {
        mkdir($fixture . '/apps/' . $folder . '/data', 0700, true);
        file_put_contents($fixture . '/apps/' . $folder . '/index.php', '<?php echo "Aplicación de prueba";');
        file_put_contents($fixture . '/apps/' . $folder . '/data/ejemplo.json', str_repeat('{"solo":"prueba"}', 500));
    }
}
$store = new GuaguaAppStore($fixture);
$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'POST' && ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '') !== 'solo-pruebas-locales') { http_response_code(403); exit; }
$input = $method === 'POST' ? json_decode(file_get_contents('php://input'), true) : $_GET;
$action = $input['action'] ?? 'catalog'; $id = $input['id'] ?? '';
header('Cache-Control: no-store');
try {
    switch ($action) {
        case 'catalog': $result = ['apps'=>$store->catalog(), 'zip'=>true, 'manage'=>true]; break;
        case 'details': $result = ['app'=>$store->details($id)]; break;
        case 'menu_list': $result = ['items'=>[], 'icons'=>[]]; break;
        case 'uninstall': $result = $store->uninstall($id); break;
        case 'install': $result = $store->install($id); break;
        case 'backup':
            $file = $store->backup($id, [], static function ($path) { file_put_contents($path, "-- Solo datos ficticios\nSELECT 1;\n"); });
            header('Content-Type: application/zip'); header('Content-Disposition: attachment; filename="guagua-datos-ficticios.zip"');
            try { readfile($file); } finally { unlink($file); } exit;
        default: throw new RuntimeException('Acción no válida.');
    }
    header('Content-Type: application/json'); echo json_encode(['status'=>'success'] + $result);
} catch (Throwable $error) { http_response_code(400); header('Content-Type: application/json'); echo json_encode(['status'=>'error', 'message'=>$error->getMessage()]); }
