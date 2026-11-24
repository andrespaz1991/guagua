<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$fixture = $root . '/tmp/tienda-menu-' . bin2hex(random_bytes(8));
mkdir($fixture . '/comun', 0700, true);
mkdir($fixture . '/sga-data/tienda', 0700, true);
copy($root . '/comun/tienda_estado.php', $fixture . '/comun/tienda_estado.php');
file_put_contents($fixture . '/sga-data/tienda/catalogo.json', json_encode(['version'=>1, 'apps'=>[
    ['carpeta'=>'Mi App+[x]', 'estado'=>'disponible'], ['carpeta'=>'otra_app', 'estado'=>'disponible'], ['carpeta'=>'activa', 'estado'=>'instalada'],
]]));
require $fixture . '/comun/tienda_estado.php';
$cases = [
    ['../apps/Mi App+[x]/index.php', false],
    ['apps/Mi%20App%2B%5Bx%5D/index.php', false],
    ['/guagua/apps/Mi App+[x]/', false],
    ['apps/otra_app?dato=1', false],
    ['apps/OTRA_APP/index.php', false],
    ['apps/otra_app_extra/index.php', true],
    ['apps/activa/index.php', true],
    ['Planeador/calendario.php', true],
];
$db = null;
try {
    foreach ($cases as [$url, $expected]) if (guagua_tienda_app_disponible($url) !== $expected) throw new RuntimeException('Visibilidad incorrecta: ' . $url);
    if (in_array('--mysql', $argv, true)) {
        require_once $root . '/comun/database.php';
        $db = guagua_db_mysqli();
        $filter = guagua_tienda_filtro_menu($db);
        foreach ($cases as [$url, $expected]) {
            $stmt = $db->prepare('SELECT menu_url FROM (SELECT ? AS menu_url) AS prueba WHERE 1=1 ' . $filter);
            $stmt->bind_param('s', $url); $stmt->execute();
            $visible = $stmt->get_result()->num_rows === 1; $stmt->close();
            if ($visible !== $expected) throw new RuntimeException('Filtro SQL incorrecto: ' . $url);
        }
    }
    echo 'OK: accesos y ' . ($db ? 'filtro MySQL real' : 'rutas') . ' con espacios, URL codificada, signos, mayúsculas, consultas y nombres similares.' . PHP_EOL;
} finally {
    if ($db) $db->close();
    unlink($fixture . '/comun/tienda_estado.php');
    unlink($fixture . '/sga-data/tienda/catalogo.json');
    rmdir($fixture . '/sga-data/tienda'); rmdir($fixture . '/sga-data'); rmdir($fixture . '/comun'); rmdir($fixture);
}
