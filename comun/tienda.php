<?php
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/tienda_permisos.php';
guagua_iniciar_sesion_segura();
require_once __DIR__ . '/config.php';
if (empty($_SESSION['id_usuario'])) {
    header('Location: ' . SGA_USUARIO_URL . '/login.php');
    exit;
}
guagua_exigir_rol(guagua_tienda_roles_gestion());
$csrf = guagua_csrf_token();
$base = rtrim(SGA_URL, '/');
function tienda_e($text): string { return htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <meta name="csrf-token" content="<?= tienda_e($csrf) ?>">
    <title>Tienda de aplicaciones | Guagua</title>
    <script src="<?= tienda_e($base) ?>/comun/tienda_tema.js?v=<?= filemtime(__DIR__.'/tienda_tema.js') ?>"></script>
    <link rel="stylesheet" href="<?= tienda_e($base) ?>/comun/tienda.css?v=<?= filemtime(__DIR__.'/tienda.css') ?>">
    <link rel="stylesheet" href="<?= tienda_e($base) ?>/comun/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= tienda_e($base) ?>/comun/img/png/icon-sga.php?v=<?= filemtime(__DIR__.'/img/png/icon-sga.php') ?>">
    <link rel="stylesheet" href="<?= tienda_e($base) ?>/comun/tienda_menu.css?v=<?= filemtime(__DIR__.'/tienda_menu.css') ?>">
    <link rel="stylesheet" href="<?= tienda_e($base) ?>/comun/tienda_tema.css?v=<?= filemtime(__DIR__.'/tienda_tema.css') ?>">
    <script defer src="<?= tienda_e($base) ?>/comun/tienda.js?v=<?= filemtime(__DIR__.'/tienda.js') ?>"></script>
    <script defer src="<?= tienda_e($base) ?>/comun/tienda_menu.js?v=<?= filemtime(__DIR__.'/tienda_menu.js') ?>"></script>
</head>
<body data-base="<?= tienda_e($base) ?>">
<header class="store-header">
    <a class="store-brand" href="tienda.php" aria-label="Tienda Guagua, inicio"><span class="brand-icon" aria-hidden="true">▦</span><span>Guagua <strong>Store</strong></span></a>
    <label class="store-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/></svg><input id="store-search" type="search" placeholder="Buscar aplicaciones para tu Guagua" autocomplete="off" aria-label="Buscar aplicaciones"><kbd>/</kbd></label>
    <div class="store-theme-switch" role="group" aria-label="Apariencia de Apps"><button type="button" data-store-theme-option="light" aria-pressed="false" aria-label="Activar modo claro" title="Modo claro"><span aria-hidden="true">☀</span><span>Claro</span></button><button type="button" data-store-theme-option="dark" aria-pressed="true" aria-label="Activar modo oscuro" title="Modo oscuro"><span aria-hidden="true">☾</span><span>Oscuro</span></button></div>
    <a class="account" href="<?= tienda_e($base) ?>/comun/configuracion.php" title="Centro de configuración"><span class="account-avatar" aria-hidden="true"><?= tienda_e(mb_strtoupper(mb_substr((string)($_SESSION['nombre'] ?? 'G'), 0, 1))) ?></span><span>Mi Guagua<small><?= tienda_e($_SESSION['rol'] === 'docente' ? 'Docente' : 'Administración') ?></small></span></a>
</header>
<aside class="store-sidebar" aria-label="Navegación de la tienda">
    <nav>
        <a href="<?= tienda_e($base) ?>/index.php" title="Volver a Guagua"><span aria-hidden="true">⌂</span>Guagua</a>
        <button type="button" data-view="explorar" class="active" aria-current="page"><span aria-hidden="true">▦</span>Explorar</button>
        <button type="button" data-view="instaladas"><span aria-hidden="true">▣</span>Instaladas</button>
        <button type="button" data-view="disponibles"><span aria-hidden="true">⇩</span>Disponibles</button>
        <button type="button" data-view="biblioteca" class="mobile-library"><span aria-hidden="true">▤</span>Biblioteca</button>
        <button type="button" data-view="menu" class="mobile-menu"><span aria-hidden="true">⚙</span>Menú</button>
    </nav>
    <nav class="sidebar-bottom">
        <button type="button" data-view="biblioteca"><span aria-hidden="true">▤</span>Biblioteca</button>
        <button type="button" data-view="menu" title="Administrar accesos del menú"><span aria-hidden="true">⚙</span>Menú</button>
    </nav>
</aside>
<main class="store-main" id="store-main">
    <div class="page-heading"><div><p class="eyebrow">TU GUAGUA, A TU MEDIDA</p><h1 id="view-title">Aplicaciones</h1></div><div class="store-heading-actions"><span class="local-label"><span aria-hidden="true">◈</span> Tu catálogo local</span><button type="button" id="store-manage-menu" class="store-manage-menu" data-view="menu"><span aria-hidden="true">⚙</span> Gestionar menú</button></div></div>
    <p class="store-view-help" id="view-help" hidden></p>
    <section class="store-hero" id="store-hero" aria-label="Descubre tu tienda">
        <div class="hero-primary"><div class="hero-copy"><span class="hero-tag">HECHO PARA TU DÍA A DÍA</span><h2>Más posibilidades.<br>Solo lo que necesitas.</h2><p>Elige tus herramientas. Haz espacio hoy<br>y vuelve a instalarlas cuando quieras.</p><button type="button" data-view="instaladas" class="hero-button">Administrar mis apps</button></div><div class="hero-art" aria-hidden="true"><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div><span class="hero-tile tile-one">📚</span><span class="hero-tile tile-two">🎨</span><span class="hero-tile tile-three">🧠</span><span class="hero-tile tile-four">🧩</span><span class="hero-tile tile-five">🗓️</span></div></div>
        <div class="hero-secondary"><span class="secondary-icon" aria-hidden="true">☁</span><span class="hero-tag">VUELVE CUANDO QUIERAS</span><h2>Menos peso.<br>La misma Guagua.</h2><p>Tus apps desinstaladas siguen en la tienda, listas para regresar.</p><button type="button" data-view="disponibles">Ver disponibles <span aria-hidden="true">＋</span></button></div>
    </section>
    <section class="library-summary" id="library-summary" hidden aria-label="Resumen de tu biblioteca"></section>
    <div id="store-status" class="store-status" role="status" aria-live="polite"></div>
    <div class="store-toolbar"><div id="category-filters" class="category-filters" role="group" aria-label="Filtrar por categoría"></div><label class="sort-label"><span class="sr-only">Ordenar aplicaciones</span><select id="store-sort"><option value="name">Nombre A–Z</option><option value="category">Por categoría</option><option value="state">Por estado</option></select></label></div>
    <div id="store-content" aria-busy="true"><div class="loading">Cargando tu catálogo de aplicaciones…</div></div>
    <section id="menu-workspace" class="menu-workspace" aria-label="Gestión del menú" hidden>
        <header class="menu-workspace-heading"><div><h2>Tus accesos, en el mismo lugar</h2><p>Edita el menú de Guagua, elige favoritas y organiza los accesos de cabecera.</p></div><div class="menu-workspace-actions"><button type="button" class="secondary-button" id="menu-sync">Añadir apps al menú</button><button type="button" class="primary-button" id="menu-new">＋ Nuevo acceso</button></div></header>
        <div class="menu-filters"><label>Categoría<select id="menu-category"><option value="">Todas las categorías</option></select></label><label>Mostrar<select id="menu-filter"><option value="all">Todos los accesos</option><option value="favorites">Mis favoritas</option><option value="header">En la cabecera</option><option value="other">Otros accesos</option></select></label><span id="menu-count" role="status" aria-live="polite"></span></div>
        <div id="menu-list" aria-busy="true"><p class="loading">Cargando los accesos del menú…</p></div>
        <div id="menu-pagination" class="menu-pagination"></div>
        <p class="menu-footnote">Los cambios del menú se aplican a toda la instalación. Quitar un acceso conserva la app y sus datos.</p>
    </section>
    <footer class="store-footer"><span>GUAGUA STORE</span><p>Las apps se guardan comprimidas al desinstalarlas. Los datos de las bases compartidas permanecen en Guagua.</p></footer>
</main>
<dialog id="app-dialog" class="app-dialog" aria-labelledby="dialog-title"><button type="button" class="dialog-close" aria-label="Cerrar detalles">×</button><div id="dialog-content"></div></dialog>
<dialog id="menu-dialog" class="app-dialog menu-dialog" aria-labelledby="menu-dialog-title"><button type="button" class="dialog-close" id="menu-dialog-close" aria-label="Cerrar editor del menú">×</button><div id="menu-dialog-content"></div></dialog>
<div class="toast" id="store-toast" role="status" aria-live="polite" hidden></div>
<noscript><p class="noscript">Activa JavaScript para buscar y administrar las aplicaciones.</p></noscript>
</body>
</html>
