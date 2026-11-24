<?php
/**
 * Contenedor común de módulos registrados en menu_items2.
 * Mantiene la cabecera de Guagua disponible aunque el módulo sea una página
 * PHP, HTML estática o una aplicación independiente.
 */
require_once __DIR__ . '/seguridad.php';
guagua_iniciar_sesion_segura();
require_once __DIR__ . '/tienda_rutas.php';
$legacyMenuPath = $_GET['path'] ?? '';
if (is_string($legacyMenuPath) && guagua_tienda_es_gestor_antiguo(rawurldecode($legacyMenuPath))) {
    header('Location: tienda.php?vista=menu', true, 302);
    exit;
}

require_once __DIR__ . '/conexion.php';

// Construir URLs limpias desde la petición actual. Algunas instalaciones
// antiguas definen SGA_URL con barras duplicadas, lo que puede impedir que el
// navegador cargue las hojas que dibujan los iconos del menú.
$hostActual = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
$raizAplicacion = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
$raizAplicacion = $raizAplicacion === '/' ? '' : rtrim($raizAplicacion, '/');
$urlBaseAplicacion = '//' . $hostActual . $raizAplicacion;
$urlComunAplicacion = $urlBaseAplicacion . '/comun';

$menuItemId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);

$rutaSolicitada = trim((string)($_GET['path'] ?? ''));
if ($rutaSolicitada !== '') {
    $rutaSolicitada = strtolower(trim($rutaSolicitada, '/'));
}

if (!$menuItemId && $rutaSolicitada === '') {
    http_response_code(400);
    exit('Módulo no válido.');
}

if ($menuItemId) {
    $consulta = $mysqli->prepare('SELECT menu_item_name, menu_url FROM menu_items2 WHERE menu_item_id = ? LIMIT 1');
    $consulta->bind_param('i', $menuItemId);
    $consulta->execute();
    $modulo = $consulta->get_result()->fetch_assoc();
    $consulta->close();
} else {
    $modulo = null;

    $rutasEquivalentes = [$rutaSolicitada];
    $rutaSinIndice = preg_replace('#/index2?\.(?:php|html)$#i', '', $rutaSolicitada);
    if ($rutaSinIndice !== $rutaSolicitada) {
        $rutasEquivalentes[] = $rutaSinIndice;
    } else {
        $rutasEquivalentes[] = $rutaSolicitada . '/index.php';
        $rutasEquivalentes[] = $rutaSolicitada . '/index.html';
        $rutasEquivalentes[] = $rutaSolicitada . '/index2.php';
    }
    if (!$modulo) {
        $resultado = $mysqli->query('SELECT menu_item_name, menu_url FROM menu_items2 WHERE menu_url IS NOT NULL');
        while ($resultado && ($candidato = $resultado->fetch_assoc())) {
            $rutaCandidata = preg_replace('#^(?:\.\./)+#', '', trim((string)$candidato['menu_url']));
            // La ruta se compara sin los filtros registrados (p. ej. ?u=acudiente).
            $rutaCandidata = preg_split('/[?#]/', $rutaCandidata, 2)[0];
            $rutaCandidata = strtolower(trim($rutaCandidata, '/'));
            if (in_array($rutaCandidata, $rutasEquivalentes, true)) {
                $modulo = $candidato;
                break;
            }
        }
    }
}

if (!$modulo || trim((string)$modulo['menu_url']) === '') {
    http_response_code(404);
    exit('El módulo solicitado no está disponible.');
}
if (guagua_tienda_es_gestor_antiguo((string)$modulo['menu_url'])) {
    header('Location: tienda.php?vista=menu', true, 302);
    exit;
}

require_once __DIR__ . '/tienda_estado.php';
if (!guagua_tienda_app_disponible((string)$modulo['menu_url'])) {
    http_response_code(410);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Aplicación desinstalada</title><p>Esta aplicación está desinstalada. Un administrador puede recuperarla desde la tienda.</p><a href="tienda.php">Abrir la tienda de aplicaciones</a></html>';
    exit;
}

$rutaRegistrada = str_replace('\\', '/', trim((string)$modulo['menu_url']));
if (preg_match('#^https?://#i', $rutaRegistrada)) {
    $urlModulo = $rutaRegistrada;
} else {
    // La mayoría de rutas se guardan desde la raíz de Guagua. Algunas
    // antiguas, como ../../wsp/index.html, son relativas a comun/ y apuntan
    // a una aplicación hermana de Guagua; se conservan sus niveles ../.
    if (preg_match('#^(?:\.\./)+#', $rutaRegistrada)) {
        [$rutaSinConsulta, $consulta] = array_pad(explode('?', $rutaRegistrada, 2), 2, '');
        $segmentos = [];
        foreach (explode('/', dirname($_SERVER['SCRIPT_NAME']) . '/' . $rutaSinConsulta) as $segmento) {
            if ($segmento === '' || $segmento === '.') {
                continue;
            }
            if ($segmento === '..') {
                array_pop($segmentos);
                continue;
            }
            $segmentos[] = $segmento;
        }
        $urlModulo = '//' . $hostActual . '/' . implode('/', array_map('rawurlencode', $segmentos));
        if ($consulta !== '') {
            $urlModulo .= '?' . $consulta;
        }
    } else {
        $rutaRelativa = ltrim($rutaRegistrada, '/');
        $urlModulo = rtrim($urlBaseAplicacion, '/') . '/' . $rutaRelativa;
    }
}

// Diario necesita su servicio Node y Socket.IO, no la página estática de Apache.
if (preg_match('#^(?:\.\./)*wsp/(?:index\.html)?$#i', $rutaRegistrada)) {
    $urlModulo = $urlComunAplicacion . '/whatsapp_diario.php';
}

// SISGA genera código que el docente copia a la consola. Añadir la fecha de
// modificación de su archivo evita que un iframe reutilice un inyector viejo.
$rutaSisgaNormalizada = strtolower(str_replace('\\', '/', $rutaRegistrada));
if (preg_match('#(?:^|/)sisga/index\.html$#', $rutaSisgaNormalizada)) {
    $archivoSisga = dirname(__DIR__) . '/sisga/index.html';
    if (is_file($archivoSisga)) {
        $urlModulo .= (strpos($urlModulo, '?') === false ? '?' : '&')
            . 'v=' . filemtime($archivoSisga);
    }
}

// Conserva los parámetros propios del módulo al abrirlo desde una URL directa
// (por ejemplo: ia/reporte.php?grupo=1), pero evita reenviar el id del menú.
$rutaArchivoModulo = (string)(parse_url($rutaRegistrada, PHP_URL_PATH) ?: '');
$esArchivoDescargable = (bool)preg_match('/\.(?:docx?|xlsx?|pptx?|zip)$/i', $rutaArchivoModulo);
$esModuloNotificaciones = (bool)preg_match('#(?:^|/)apps/noti(?:/index\.html)?/?$#i', str_replace('\\', '/', $rutaArchivoModulo));
$urlChapolas = rtrim($urlBaseAplicacion, '/') . '/apps/noti/chapolas.html?guagua_embedded=1';
if (!$esArchivoDescargable) {
    $parametrosModulo = $_GET;
    unset($parametrosModulo['id'], $parametrosModulo['path'], $parametrosModulo['guagua_embedded']);
    $parametrosModulo['guagua_embedded'] = '1';
    $urlModulo .= (strpos($urlModulo, '?') === false ? '?' : '&') . http_build_query($parametrosModulo);
}

$nombreModulo = htmlspecialchars($modulo['menu_item_name'], ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombreModulo; ?> | Guagua</title>
    <link rel="stylesheet" href="<?php echo $urlComunAplicacion; ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $urlComunAplicacion; ?>/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $urlComunAplicacion; ?>/css/estilos_guagua.css?v=<?php echo filemtime(__DIR__ . '/css/estilos_guagua.css'); ?>">
    <link rel="stylesheet" href="<?php echo $urlComunAplicacion; ?>/img/png/icon-sga.php?v=<?php echo filemtime(__DIR__ . '/img/png/icon-sga.php'); ?>">
    <script src="<?php echo $urlComunAplicacion; ?>/js/jquery-3.6.0.min.js"></script>
    <script src="<?php echo $urlComunAplicacion; ?>/js/bootstrap3.3.7.min.js"></script>
    <script src="<?php echo $urlComunAplicacion; ?>/vendor/sortable.min.js"></script>
    <style>
        :root { --menu-height: 145px; }
        * { box-sizing: border-box; }
        body { background: #f8fafc; margin: 0; overflow: hidden; }
        .module-frame { border: 0; display: block; height: calc(100dvh - var(--menu-height)); width: 100%; }
        .module-frame-fallback { background: #fff7ed; border-bottom: 1px solid #fed7aa; color: #9a3412; display: none; font: 14px/1.4 Arial, sans-serif; padding: 9px 16px; }
        .module-frame-fallback a { color: inherit; font-weight: 700; }
        .module-download { display: grid; height: calc(100dvh - var(--menu-height)); place-items: center; padding: 24px; }
        .module-download-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; max-width: 480px; padding: 32px; text-align: center; width: 100%; }
        .module-download-card a { background: #2563eb; border-radius: 8px; color: #fff; display: inline-block; font-weight: 700; margin-top: 16px; padding: 12px 20px; text-decoration: none; }
        .module-tabs { align-items: center; background: #fff; border-bottom: 1px solid #e2e8f0; display: flex; gap: 6px; height: 48px; padding: 0 16px; }
        .module-tabs a { border-radius: 8px; color: #475569; font: 700 13px/1 Arial, sans-serif; padding: 9px 13px; text-decoration: none; transition: background .18s, color .18s; }
        .module-tabs a:hover { background: #f1f5f9; color: #1c6628; }
        .module-tabs a.is-active { background: #eef8f0; color: #1c6628; }
        .module-with-tabs .module-frame { height: calc(100dvh - var(--menu-height) - 48px); }
        @media (max-width: 768px) { :root { --menu-height: clamp(62px, 16vh, 92px); } }
    </style>
</head>
<body class="<?php echo $esModuloNotificaciones ? 'module-with-tabs' : ''; ?>">
    <?php require __DIR__ . '/menu.php'; ?>
    <?php if ($esArchivoDescargable): ?>
    <main class="module-download">
        <div class="module-download-card">
            <h1><?php echo $nombreModulo; ?></h1>
            <p>El archivo está listo para descargar.</p>
            <a href="<?php echo htmlspecialchars($urlModulo, ENT_QUOTES, 'UTF-8'); ?>" download>Descargar archivo</a>
        </div>
    </main>
    <?php else: ?>
    <div id="module-frame-fallback" class="module-frame-fallback">
        El módulo no permite mostrarse integrado. <a id="module-direct-link" href="<?php echo htmlspecialchars($urlModulo, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">Abrirlo en una pestaña nueva</a>.
    </div>
    <?php if ($esModuloNotificaciones): ?>
    <nav class="module-tabs" aria-label="Herramientas de notificaciones">
        <a href="<?php echo htmlspecialchars($urlModulo, ENT_QUOTES, 'UTF-8'); ?>" target="module-frame" class="is-active" data-noti-tab="circulares">Circulares</a>
        <a href="<?php echo htmlspecialchars($urlChapolas, ENT_QUOTES, 'UTF-8'); ?>" target="module-frame" data-noti-tab="chapolas">Chapolas</a>
    </nav>
    <?php endif; ?>
    <iframe
        id="module-frame"
        name="module-frame"
        class="module-frame"
        src="<?php echo htmlspecialchars($urlModulo, ENT_QUOTES, 'UTF-8'); ?>"
        title="<?php echo $nombreModulo; ?>"
    ></iframe>

    <script>
        document.getElementById('module-frame').addEventListener('load', function () {
            // Algunos servicios externos bloquean iframes. El enlace de
            // respaldo sigue disponible para abrirlos directamente.
            try {
                if (this.contentWindow.location.href === 'about:blank') {
                    document.getElementById('module-frame-fallback').style.display = 'block';
                }
            } catch (error) {
                document.getElementById('module-frame-fallback').style.display = 'block';
            }
        });
        <?php if ($esModuloNotificaciones): ?>
        document.querySelectorAll('[data-noti-tab]').forEach(function (tab) {
            tab.addEventListener('click', function () {
                document.querySelectorAll('[data-noti-tab]').forEach(function (item) {
                    item.classList.toggle('is-active', item === tab);
                });
            });
        });
        <?php endif; ?>
    </script>
    <?php endif; ?>
</body>
</html>
