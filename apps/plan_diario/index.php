<?php
declare(strict_types=1);
require_once __DIR__.'/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
if (empty($_SESSION['id_usuario'])) { header('Location: ../../usuario/login.php'); exit; }
header('Cache-Control: no-store, private');
$hasCsrf = !empty($_SESSION['guagua_csrf']);
$token = guagua_csrf_token();
if ($hasCsrf) session_abort(); else session_write_close();
require_once __DIR__.'/../../comun/database.php';
$settings = GuaguaDatabase::settings();
$today = (new DateTimeImmutable('now',new DateTimeZone($settings['timezone'])))->format('Y-m-d');
?>
<!doctype html>
<html lang="es">
<head>
 <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <meta name="theme-color" content="#315c96"><title>Mi plan diario · Guagua</title>
 <link rel="stylesheet" href="style.css?v=<?= filemtime(__DIR__.'/style.css') ?>">
 <script>window.dailyPlanner = <?= json_encode(['csrf'=>$token,'today'=>$today],JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
 <script src="app.js?v=<?= filemtime(__DIR__.'/app.js') ?>" defer></script>
</head>
<body>
<svg class="symbols" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs>
 <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
 <symbol id="i-calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h2m4 0h2"/></symbol>
 <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></symbol>
 <symbol id="i-edit" viewBox="0 0 24 24"><path d="m15 4 5 5M4 20l5-1L21 7a2 2 0 0 0-5-5L4 14z"/></symbol>
 <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></symbol>
 <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></symbol>
 <symbol id="i-copy" viewBox="0 0 24 24"><rect x="8" y="8" width="13" height="13" rx="2"/><path d="M16 8V3H3v13h5"/></symbol>
 <symbol id="i-chevron" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
 <symbol id="i-trash" viewBox="0 0 24 24"><path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/></symbol>
 <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></symbol>
 <symbol id="i-close" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></symbol>
 <symbol id="i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/></symbol>
 <symbol id="i-lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/></symbol>
 <symbol id="i-chart" viewBox="0 0 24 24"><path d="M4 3v17h17M9 16v-5m5 5V6m5 10V9"/></symbol>
</defs></svg>
<header class="app-toolbar">
 <div class="app-identity"><a href="../../index.php" aria-label="Volver a Guagua">Guagua</a><span>/</span><span>Mi plan diario</span></div>
 <div class="toolbar-right"><span id="save-status" class="save-status" role="status" aria-live="polite">Abriendo la hoja…</span><button class="button" id="nav-categories" disabled>Títulos y categorías</button><button class="button primary" id="new-task" disabled><svg><use href="#i-plus"/></svg> Nueva tarea</button></div>
</header>
<main class="workspace">
 <div class="workspace-toolbar">
  <div class="view-switch" role="group" aria-label="Vista del plan"><button id="view-edit" aria-pressed="true"><svg><use href="#i-edit"/></svg> Editar</button><button id="view-check" aria-pressed="false"><svg><use href="#i-check"/></svg> Seguimiento</button><button id="view-monitor" aria-pressed="false"><svg><use href="#i-chart"/></svg> Cumplimiento</button></div>
  <div class="date-controls"><button class="icon-button" id="prev-week" aria-label="Semana anterior"><svg class="flip"><use href="#i-chevron"/></svg></button><button class="button" id="today">Hoy</button><button class="icon-button" id="next-week" aria-label="Semana siguiente"><svg><use href="#i-chevron"/></svg></button><label class="planning-date-label"><span>Fecha de la planeación</span><input type="date" id="date-picker" min="2000-01-01" max="2100-12-31" aria-label="Fecha de la planeación"></label></div>
 </div>
 <details class="calendar-panel"><summary><svg><use href="#i-calendar"/></svg> Ver semana</summary><div class="calendar-content"><h2 id="month-label">Esta semana</h2><div class="week-strip" id="week-strip"></div></div></details>
 <div id="error-banner" class="error-banner" role="alert" hidden><span></span><button id="retry" class="button">Volver a intentar</button></div>
 <article class="document-sheet" id="daily-sheet" aria-labelledby="tasks-heading">
  <header class="document-heading"><div><p class="document-label">MI HOJA DEL DÍA</p><h1 id="tasks-heading">Plan del día</h1><p id="day-caption">Tus títulos, tus tareas y un día por delante.</p></div><div class="document-actions"><button class="text-button" id="document-reset" type="button" hidden>Cancelar edición</button><button class="button primary" id="document-save" type="button" disabled>Guardar cambios</button></div></header>
  <p id="schedule-info" class="schedule-info" role="status" hidden></p>
  <nav class="category-tabs" id="category-tabs" role="tablist" aria-label="Filtrar por categoría"></nav>
  <div class="document-options"><p id="mode-help">Escribe los títulos y las tareas directamente en la hoja.</p><details class="document-search"><summary>Buscar y filtrar</summary><div class="list-toolbar"><label class="search-field"><svg><use href="#i-search"/></svg><input id="search" type="search" placeholder="Buscar en la hoja" aria-label="Buscar tareas"></label><select id="filter" aria-label="Filtrar por estado"><option value="all">Todas</option><option value="pending">Pendientes</option><option value="done">Completadas</option><option value="high">Prioridad alta</option></select></div></details></div>
  <div id="task-list" class="task-list" aria-busy="true"><p class="loading-message">Preparando tu hoja…</p></div>
  <button class="add-section text-button" id="side-add" type="button" disabled><svg><use href="#i-plus"/></svg> Añadir título</button>
  <footer class="document-footer"><span id="list-count">Tus tareas del día</span><span id="progress-summary">0 completadas</span><button class="text-button" id="copy-open" disabled><svg><use href="#i-copy"/></svg> Traer de otro día</button></footer>
  <details class="day-note"><summary>Nota del día</summary><label class="sr-only" for="focus">Mi enfoque de hoy</label><textarea id="focus" maxlength="1000" rows="3" placeholder="Algo que quieras recordar para este día…" disabled></textarea><div class="note-footer"><span id="focus-status" role="status">Una nota para tu día</span><button class="text-button" id="focus-save" disabled>Guardar nota</button></div></details>
 </article>
 <section class="compliance-sheet" id="compliance-sheet" aria-labelledby="compliance-title" hidden>
  <header class="compliance-heading"><p class="document-label">TU AVANCE EN EL TIEMPO</p><h1 id="compliance-title">Cumplimiento</h1><p>Revisa lo que has terminado y lo que necesita atención.</p></header>
  <form id="compliance-form" class="compliance-filters">
   <label>Período<select id="compliance-period"><option value="week">Semana seleccionada</option><option value="month">Mes seleccionado</option><option value="custom">Rango personalizado</option></select></label>
   <label>Desde<input type="date" id="compliance-from" min="2000-01-01" max="2100-12-31" required></label>
   <label>Hasta<input type="date" id="compliance-to" min="2000-01-01" max="2100-12-31" required></label>
   <button class="button primary" id="compliance-refresh" type="submit">Actualizar</button>
  </form>
  <p class="compliance-definition">El porcentaje compara las tareas completadas con las guardadas para el período. Los días sin tareas no bajan tu avance. Se muestra su estado actual, agrupado por la fecha de planeación.</p>
  <p id="compliance-status" role="status" aria-live="polite"></p>
  <div id="compliance-error" class="error-banner" role="alert" hidden></div>
  <div id="compliance-content" aria-busy="false"></div>
 </section>
 <p class="workspace-hint">Elige la fecha para ver tus materias de ese día. Personal está disponible todos los días. <kbd>Enter</kbd> añade una tarea y <kbd>Ctrl</kbd> + <kbd>S</kbd> guarda tu edición.</p>
</main>
<dialog id="task-dialog" aria-labelledby="task-dialog-title"><form id="task-form"><div class="dialog-heading"><div><div class="eyebrow">DALE UN LUGAR EN TU DÍA</div><h2 id="task-dialog-title">Nueva tarea</h2></div><button type="button" class="icon-button" data-close="task-dialog" aria-label="Cerrar"><svg><use href="#i-close"/></svg></button></div><p id="task-edit-status" class="task-edit-status" hidden></p><input type="hidden" name="id"><label>¿Qué necesitas hacer?<input name="title" required maxlength="240" placeholder="Por ejemplo: preparar el taller de matemáticas" autofocus></label><div class="form-grid"><label>Categoría<select name="category_id" required></select></label><label>Fecha<input type="date" name="task_date" min="2000-01-01" max="2100-12-31" required></label><label>Hora <span class="optional">opcional</span><input type="time" name="task_time"></label><label>Prioridad<select name="priority"><option value="normal">Normal</option><option value="alta">Alta</option></select></label></div><label>Notas <span class="optional">opcional</span><textarea name="description" rows="3" maxlength="4000" placeholder="Detalles, materiales o algo que quieras recordar…"></textarea></label><label>Color al completar<select name="highlight"><option value="green">Verde</option><option value="purple">Violeta</option><option value="blue">Azul</option><option value="orange">Naranja</option><option value="pink">Rosa</option></select></label><p class="form-error" role="alert"></p><div class="dialog-actions"><button type="button" class="button" data-close="task-dialog">Cancelar</button><button class="button primary" type="submit">Guardar tarea</button></div></form></dialog>
<dialog id="categories-dialog" aria-labelledby="categories-title"><div class="dialog-heading"><div><div class="eyebrow">CADA COSA EN SU LUGAR</div><h2 id="categories-title">Títulos y categorías</h2></div><button type="button" class="icon-button" data-close="categories-dialog" aria-label="Cerrar"><svg><use href="#i-close"/></svg></button></div><p class="dialog-intro">Cada categoría es un título de tu hoja y una pestaña. Por ejemplo: Ciencias sociales o Emprendimiento. Se usa en todos tus días.</p><div id="category-editor"></div><form id="category-form" class="category-create"><label class="sr-only" for="category-name">Nombre del nuevo título</label><input id="category-name" name="name" required maxlength="100" placeholder="Por ejemplo: Ciencias sociales"><select name="color" aria-label="Color de nueva categoría"><option value="purple">Violeta</option><option value="blue">Azul</option><option value="green">Verde</option><option value="orange">Naranja</option><option value="pink">Rosa</option></select><button class="button primary" type="submit">Crear</button></form><p class="form-error" role="alert"></p></dialog>
<dialog id="copy-dialog" aria-labelledby="copy-title"><form id="copy-form"><div class="dialog-heading"><div><div class="eyebrow">CONTINÚA DONDE LO DEJASTE</div><h2 id="copy-title">Traer tareas de otro día</h2></div><button type="button" class="icon-button" data-close="copy-dialog" aria-label="Cerrar"><svg><use href="#i-close"/></svg></button></div><p class="dialog-intro">Añade una copia al día seleccionado. El plan original conserva su historial y las copias empiezan pendientes.</p><label>Fecha de origen<input type="date" name="source" min="2000-01-01" max="2100-12-31" required></label><label>Qué quieres traer<select name="scope"><option value="pending">Solo las pendientes</option><option value="all">Todas las tareas</option></select></label><p class="dialog-intro">Las tareas que ya trajiste de esa fecha no se duplican.</p><p class="form-error" role="alert"></p><div class="dialog-actions"><button type="button" class="button" data-close="copy-dialog">Cancelar</button><button type="submit" class="button primary">Traer tareas</button></div></form></dialog>
<dialog id="confirm-dialog" aria-labelledby="confirm-title"><form id="confirm-form"><h2 id="confirm-title">Eliminar tarea</h2><p id="confirm-message"></p><label id="target-label" hidden>Mover las tareas a<select id="target-category"></select></label><p class="form-error" role="alert"></p><div class="dialog-actions"><button class="button" type="button" data-close="confirm-dialog">Cancelar</button><button class="button danger" type="submit">Eliminar</button></div></form></dialog>
<div id="toast" class="toast" role="status" aria-live="polite" hidden></div>

</body>
</html>
