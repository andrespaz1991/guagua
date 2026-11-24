<?php
// Ejecutar con: php tests/menu_embebido_test.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/../comun/vista_modulo.php';
require_once __DIR__ . '/../comun/cabecera_modulo.php';

function menu_check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$cases = [
    [[], [], false, 'página directa'],
    [['guagua_embedded'=>'1'], [], true, 'contenedor de módulos'],
    [['embebido'=>''], [], true, 'bandera heredada'],
    [[], ['HTTP_SEC_FETCH_DEST'=>'iframe'], true, 'navegación en iframe'],
    [[], ['HTTP_SEC_FETCH_DEST'=>'document'], false, 'pestaña normal'],
    [['guagua_embedded'=>'0'], [], false, 'bandera desactivada'],
    [['guagua_embedded'=>['1']], [], false, 'parámetro no escalar'],
];
foreach ($cases as [$query, $server, $expected, $label]) {
    $_GET = $query; $_SERVER = $server;
    menu_check(guagua_modulo_embebido() === $expected, 'Detección incorrecta: ' . $label);
    menu_check(guagua_url_integrada('Mascotas.php') === ($expected ? 'Mascotas.php?guagua_embedded=1' : 'Mascotas.php'), 'El formulario perdió el modo integrado: ' . $label);
    menu_check(guagua_url_integrada('Mascotas.php?xls') === ($expected ? 'Mascotas.php?xls&guagua_embedded=1' : 'Mascotas.php?xls'), 'La exportación perdió sus parámetros: ' . $label);
    if ($expected) {
        ob_start(); guagua_menu_renderizar(); $output = ob_get_clean();
        menu_check($output === '', 'La cabecera auxiliar se duplicó: ' . $label);
    }
}

// Renderizar las plantillas reales con dependencias de presentación mínimas,
// sin acceder a la base de datos ni a una sesión de usuario existente.
$root = dirname(__DIR__);
$fixture = sys_get_temp_dir() . '/guagua-menu-' . bin2hex(random_bytes(8));
$directories = [$fixture, $fixture.'/comun', $fixture.'/comun/plantillas', $fixture.'/comun/plantillas/plantilla1', $fixture.'/usuario'];
$files = [];
foreach ($directories as $directory) mkdir($directory);
$write = static function (string $relative, string $content) use ($fixture, &$files): void {
    $path = $fixture . '/' . $relative;
    file_put_contents($path, $content); $files[] = $path;
};
$cwd = getcwd();
try {
    $write('comun/vista_modulo.php', '<?php require_once ' . var_export($root.'/comun/vista_modulo.php', true) . ';');
    $write('comun/config.php', '<?php $GLOBALS["menu_config_initialized"] = true;');
    $write('comun/funciones.php', '<?php');
    $write('comun/autoload.php', '<?php if (!class_exists("Institucion", false)) { class Institucion { public $nombre_institucion="Prueba"; public function __construct($id) {} } }');
    $write('comun/seguridad.php', '<?php function guagua_iniciar_sesion_segura() { $GLOBALS["menu_session_initialized"] = true; }');
    $write('comun/menu.php', '<nav data-test="guagua-menu">Menú</nav>');
    $write('comun/plantillas/footer.php', '<footer data-test="guagua-footer">Pie</footer>');
    $write('comun/plantillas/plantilla1/footer.php', '<footer data-test="guagua-footer">Pie</footer>');
    define('SGA_COMUN_SERVER', $fixture.'/comun');
    foreach (['SGA_COMUN_URL','SGA_COMUN_IMAGES_URL','SGA_URL','NOMBRE_INSTITUCION'] as $constant) define($constant, '');
    session_save_path($fixture);
    session_id('guaguamenu' . bin2hex(random_bytes(8)));
    session_start();
    $files[] = $fixture.'/sess_'.session_id();
    $_SESSION = ['institucion'=>1];
    chdir($fixture.'/usuario');
    foreach (['plantilla.php', 'plantilla_kids_m.php', 'plantilla1/plantilla.php', 'plantilla1/plantilla_kids_m.php'] as $template) {
        $relative = 'comun/plantillas/' . $template;
        $write($relative, file_get_contents($root.'/'.$relative));
        foreach (array_slice($cases, 0, 5) as [$query, $server, $embedded, $label]) {
            $_GET = $query; $_SERVER = $server; $contenido = '<main data-test="contenido">Contenido</main>';
            ob_start(); include $fixture.'/'.$relative; $html = ob_get_clean();
            menu_check(substr_count($html, 'data-test="contenido"') === 1, 'Contenido perdido: ' . $template);
            $menuCount = !$embedded && $template !== 'plantilla.php' ? 1 : 0;
            menu_check(substr_count($html, 'data-test="guagua-menu"') === $menuCount, 'Cabecera duplicada o ausente: ' . $template . ' / ' . $label);
            menu_check(substr_count($html, 'data-test="guagua-footer"') === ($embedded ? 0 : 1), 'Pie duplicado o ausente: ' . $template . ' / ' . $label);
        }
    }
    // El menú real mantiene la inicialización antes de omitir su HTML.
    file_put_contents($fixture.'/comun/menu.php', file_get_contents($root.'/comun/menu.php'));
    $_GET = ['guagua_embedded'=>'1']; $_SERVER = [];
    ob_start(); include $fixture.'/comun/menu.php'; $html = ob_get_clean();
    menu_check($html === '', 'El menú real produjo una segunda cabecera.');
    menu_check(!empty($GLOBALS['menu_session_initialized']) && !empty($GLOBALS['menu_config_initialized']), 'El modo integrado omitió la inicialización de sesión/configuración.');
    echo "OK: cabecera única, cuatro plantillas, compatibilidad heredada y navegación/formularios en iframe.\n";
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    chdir($cwd);
    foreach (array_reverse($files) as $file) if (is_file($file)) unlink($file);
    foreach (array_reverse($directories) as $directory) rmdir($directory);
}
