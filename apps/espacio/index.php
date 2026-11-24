<?php
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
if (empty($_SESSION['id_usuario'])) {
    header('Location: ../../usuario/login.php');
    exit;
}
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Espacio · Guagua</title><link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__ . '/style.css') ?>"></head>
<body>
<aside id="sidebar">
 <a class="workspace" href="../../index.php"><span class="brand">G</span><strong>Guagua <small>Espacio personal</small></strong><span>⌄</span></a>
 <button id="search-open" class="nav"><span>⌕</span> Buscar <kbd>Ctrl K</kbd></button>
 <button id="home" class="nav"><span>⌂</span> Todas las páginas</button>
 <button id="reports-open" class="nav"><span>▥</span> Reportes de tareas</button>
 <button id="import-open" class="nav" disabled><span>↥</span> Importar proyectos</button>
 <div class="section-label">FAVORITOS</div><nav id="favorites" aria-label="Favoritos"></nav>
 <div class="section-label">PRIVADO <button id="new-page" title="Crear página">＋</button></div><nav id="pages" aria-label="Páginas"></nav>
 <button id="new-bottom" class="nav muted">＋ Nueva página</button>
 <div class="sidebar-bottom"><button id="templates" class="nav">▦ Plantillas</button><button id="trash" class="nav">♲ Papelera</button><a class="nav" href="../../index.php">↗ Volver a Guagua</a><div class="privacy">◉ Solo tú tienes acceso</div></div>
</aside>
<main>
 <header><button id="toggle-sidebar" title="Mostrar u ocultar barra lateral">☰</button><div id="breadcrumb"></div><span id="save-state" role="status">Cargando…</span><button id="save-button" type="button" title="Guardar ahora (Ctrl+S)" disabled>Guardar</button><button id="print-open" type="button" title="Imprimir página, proyecto o reporte" disabled>Imprimir</button><button id="favorite" title="Marcar como favorito">☆</button><button id="more" title="Acciones de página">•••</button></header>
 <div id="cover"><span>UN ESPACIO PARA TUS IDEAS</span><button id="change-cover">Cambiar portada</button></div>
 <article id="document"><button id="page-icon" title="Cambiar icono">🌿</button><div id="parent-label"></div><textarea id="title" rows="1" placeholder="Sin título" aria-label="Título de la página"></textarea><div class="page-meta">Tu espacio para pensar, crear y conectar.</div><div id="blocks"></div><button id="add-block">＋ Añadir un bloque <span>o escribe / para ver las opciones</span></button><div id="children"></div><footer>GUAGUA ESPACIO <span>Hecho para tus ideas.</span></footer></article>
 <section id="listing" hidden></section>
 <section id="reports" hidden aria-labelledby="reports-title">
  <div class="report-eyebrow">TU ESPACIO, EN PERSPECTIVA</div>
  <h1 id="reports-title" tabindex="-1">Reportes de tareas</h1>
  <p class="report-intro">Lo que ya hiciste y lo que viene. Todas tus tareas y subtareas, en un solo lugar.</p>
  <div id="report-summary" class="report-summary"></div>
  <div class="report-section-heading"><h2>Avance por página</h2><span>Incluye las tareas y subtareas de cada documento</span></div>
  <div id="report-pages" class="report-pages"></div>
  <div class="report-section-heading"><h2>Seguimiento de tareas</h2><span id="report-count" role="status"></span></div>
  <div class="report-filters">
   <label>Página<select id="report-page"><option value="">Todas las páginas</option></select></label>
   <label>Estado<select id="report-status"><option value="all">Todos los estados</option><option value="pending">Pendientes</option><option value="done">Completadas</option></select></label>
   <label>Orden<select id="report-order"><option value="recent">Cumplimientos más recientes</option><option value="pending">Pendientes primero</option><option value="page">Por página y nivel</option></select></label>
   <label class="report-search-label">Buscar<input id="report-search" type="search" placeholder="Tarea, subtarea o página…"></label>
  </div>
  <p class="report-help">Cada casilla cuenta por separado. Completar una tarea no completa sus subtareas. Las páginas en la papelera no se incluyen.</p>
  <div id="report-task-list" class="report-task-list"></div>
  <button id="report-more" type="button" hidden>Mostrar más tareas</button>
  <p class="report-help">Las fechas se muestran en hora de Colombia. Las tareas completadas antes de activar este reporte aparecen como «Sin fecha registrada».</p>
 </section>
</main>
<div id="popover" hidden></div>
<dialog id="print-dialog" aria-labelledby="print-heading">
 <div class="print-toolbar"><div><h2 id="print-heading">Preparar impresión</h2><p>Papel A4 · También puedes guardar como PDF desde el diálogo de impresión.</p></div><button id="print-close" type="button" aria-label="Cerrar vista de impresión">✕</button></div>
 <div class="print-controls">
  <label>Qué imprimir<select id="print-scope"><option value="page">Página actual</option><option value="project">Proyecto con sus subpáginas</option><option value="section">Una sección de esta página</option><option value="report">Reporte con los filtros actuales</option></select></label>
  <label id="print-section-field" hidden>Sección<select id="print-section"></select></label>
  <button id="print-confirm" type="button">Imprimir / Guardar PDF</button>
 </div>
 <p class="print-browser-tip">Para un acabado limpio, desactiva los encabezados y pies del navegador en el diálogo de impresión.</p>
 <p id="print-notice" role="status"></p>
 <div id="print-preview" aria-label="Vista previa del contenido para imprimir"></div>
</dialog>
<div id="print-output"></div>
<dialog id="import-dialog" aria-labelledby="import-heading">
 <h2 id="import-heading">Importar proyectos</h2>
 <p>Elige un archivo JSON exportado desde Guagua Espacio. Se añadirán copias independientes; tus proyectos actuales se conservan.</p>
 <label for="import-file" class="import-file-label">Archivo de proyectos (.json)</label>
 <input id="import-file" type="file" accept=".json,application/json">
 <p id="import-error" role="alert"></p>
 <div id="import-preview" hidden><h3>Selecciona los proyectos que quieres importar</h3><div id="import-projects"></div><p id="import-summary" role="status"></p></div>
 <div class="import-actions"><button id="import-cancel" type="button">Cancelar</button><button id="import-confirm" type="button" disabled>Importar seleccionados</button></div>
</dialog>
<dialog id="search-dialog"><input id="search-input" placeholder="Buscar en tus páginas…" aria-label="Buscar páginas"><div id="search-results"></div><button id="search-close">Cerrar · Esc</button></dialog>
<dialog id="template-dialog"><h2>Empieza con una idea</h2><p>Un punto de partida para hacer tuyo.</p><div id="template-options"></div><button id="template-close">Cerrar</button></dialog>
<dialog id="task-link-dialog" aria-labelledby="task-link-heading">
 <form id="task-link-form">
  <h2 id="task-link-heading">Enlace de la tarea</h2>
  <p>Vincula un documento, una página de Guagua o información de referencia.</p>
  <label for="task-link-url">Dirección del documento o información</label>
  <input id="task-link-url" type="text" inputmode="url" maxlength="2048" placeholder="https://… o /guagua/…" autocomplete="off" aria-describedby="task-link-error">
  <p id="task-link-error" role="alert"></p>
  <div class="task-link-actions"><button id="task-link-remove" type="button">Quitar enlace</button><button id="task-link-cancel" type="button">Cancelar</button><button type="submit" class="task-link-save">Guardar enlace</button></div>
 </form>
</dialog>
<div id="drag-announcement" class="sr-only" role="status" aria-live="polite"></div>
<script>window.espacioCsrf = <?= json_encode(guagua_csrf_token()) ?>;</script><script src="ordering.js?v=<?= filemtime(__DIR__ . '/ordering.js') ?>"></script><script src="report-data.js?v=<?= filemtime(__DIR__ . '/report-data.js') ?>"></script><script src="import-data.js?v=<?= filemtime(__DIR__ . '/import-data.js') ?>"></script><script src="print-data.js?v=<?= filemtime(__DIR__ . '/print-data.js') ?>"></script><script src="app.js?v=<?= filemtime(__DIR__ . '/app.js') ?>"></script>
</body></html>
