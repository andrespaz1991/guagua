<?php
/**
 * Utilidades para insertar el menú de Guagua en módulos PHP propios.
 *
 * Uso recomendado:
 *   require_once __DIR__ . '/../comun/cabecera_modulo.php';
 *   // En <head>: guagua_menu_estilos();
 *   // Al inicio de <body>: guagua_menu_renderizar();
 */
require_once __DIR__ . '/vista_modulo.php';

if (!function_exists('guagua_menu_url_base')) {
    function guagua_menu_url_base(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
        $documentRoot = str_replace('\\', '/', rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '\\/'));
        $directorioComun = str_replace('\\', '/', __DIR__);
        $rutaComun = $documentRoot !== '' && str_starts_with(strtolower($directorioComun), strtolower($documentRoot))
            ? substr($directorioComun, strlen($documentRoot))
            : '/guagua/comun';
        $rutaBase = preg_replace('#/comun$#i', '', rtrim($rutaComun, '/'));

        return '//' . $host . ($rutaBase === '/' ? '' : $rutaBase);
    }
}

if (!function_exists('guagua_menu_estilos')) {
    function guagua_menu_estilos(): void
    {
        $baseUrl = guagua_menu_url_base();
        $comunUrl = $baseUrl . '/comun';
        $versionEstilos = (string) filemtime(__DIR__ . '/css/estilos_guagua.css');
        $versionIconos = (string) filemtime(__DIR__ . '/img/png/icon-sga.php');
        ?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/vendor/fontawesome/css/all.min.css">
<link rel="stylesheet" href="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/css/estilos_guagua.css?v=<?php echo $versionEstilos; ?>">
<link rel="stylesheet" href="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/img/png/icon-sga.php?v=<?php echo $versionIconos; ?>">
<script src="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/js/jquery-3.6.0.min.js"></script>
<script src="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/js/bootstrap3.3.7.min.js"></script>
<script src="<?php echo htmlspecialchars($comunUrl, ENT_QUOTES, 'UTF-8'); ?>/vendor/sortable.min.js"></script>
<?php
    }
}

if (!function_exists('guagua_menu_renderizar')) {
    function guagua_menu_renderizar(): void
    {
        if (guagua_modulo_embebido()) {
            return;
        }
        // menu.php se incluye dentro de esta función: necesita la conexión en
        // su ámbito local, aunque el módulo ya haya inicializado la sesión.
        require_once __DIR__ . '/database.php';
        $mysqli = guagua_db_request();
        require __DIR__ . '/menu.php';
    }
}
