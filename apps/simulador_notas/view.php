<?php
if (!defined('SN_VIEW_AUTHORIZED')) { http_response_code(404); exit; }
function sn_icon(string $name): string {
    return '<svg class="sn-icon" aria-hidden="true"><use href="#sn-i-'.$name.'"></use></svg>';
}
$assetVersion = (string)max(filemtime(__DIR__.'/style.css'), filemtime(__DIR__.'/reports.css'), filemtime(__DIR__.'/app.js'), filemtime(__DIR__.'/model.js'));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Simulador de notas · Guagua</title>
    <?php guagua_menu_estilos(); ?>
    <link rel="stylesheet" href="style.css?v=<?=sn_e($assetVersion)?>">
    <link rel="stylesheet" href="reports.css?v=<?=sn_e($assetVersion)?>">
</head>
<body class="sn-page">
<?php guagua_menu_renderizar(); ?>
<svg xmlns="http://www.w3.org/2000/svg" class="sn-icon-library" aria-hidden="true"><defs>
    <symbol id="sn-i-chart" viewBox="0 0 24 24"><path d="M4 4v16h16M8 15v-4m5 4V7m5 8V4"/></symbol>
    <symbol id="sn-i-search" viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/></symbol>
    <symbol id="sn-i-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3m2-15a3 3 0 0 1 0 6m2 9v-3a6 6 0 0 0-2-4"/></symbol>
    <symbol id="sn-i-arrow" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5"/></symbol>
    <symbol id="sn-i-chevron" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
    <symbol id="sn-i-upload" viewBox="0 0 24 24"><path d="M12 16V3m-5 5 5-5 5 5M4 15v5h16v-5"/></symbol>
    <symbol id="sn-i-download" viewBox="0 0 24 24"><path d="M12 3v13m-5-5 5 5 5-5M4 17v4h16v-4"/></symbol>
    <symbol id="sn-i-settings" viewBox="0 0 24 24"><path d="M4 7h5m5 0h6M4 17h9m5 0h2"/><circle cx="11.5" cy="7" r="2.5"/><circle cx="15.5" cy="17" r="2.5"/></symbol>
    <symbol id="sn-i-edit" viewBox="0 0 24 24"><path d="m15 4 5 5M4 20l5-1L21 7a2 2 0 0 0-5-5L4 14v6Z"/></symbol>
    <symbol id="sn-i-info" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v.2"/></symbol>
    <symbol id="sn-i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
    <symbol id="sn-i-close" viewBox="0 0 24 24"><path d="m6 6 12 12M6 18 18 6"/></symbol>
    <symbol id="sn-i-undo" viewBox="0 0 24 24"><path d="M4 10h9a6 6 0 0 1 0 12M9 4l-6 6 6 6"/></symbol>
    <symbol id="sn-i-reset" viewBox="0 0 24 24"><path d="M4 9a8 8 0 1 1 0 6m0-12v6h6"/></symbol>
    <symbol id="sn-i-book" viewBox="0 0 24 24"><path d="M12 6C8 3 4 4 2 5v14c3-1 7-1 10 2 3-3 7-3 10-2V5c-2-1-6-2-10 1Zm0 0v15"/></symbol>
    <symbol id="sn-i-file" viewBox="0 0 24 24"><path d="M14 3H5v18h14V8l-5-5Zm0 0v5h5M8 12h8m-8 4h6"/></symbol>
</defs></svg>
<a class="sn-skip" href="#sn-results">Ir a las notas</a>
<main id="simulador" class="sn-app">
    <nav class="sn-breadcrumb" aria-label="Ruta de navegación"><a href="../../comun/dir_menu.php">Aplicaciones</a><?=sn_icon('chevron')?><span>Seguimiento académico</span></nav>
    <header class="sn-app-header">
        <div class="sn-app-heading"><span class="sn-app-mark"><?=sn_icon('chart')?></span><div><h1>Simulador de notas</h1><p>Consulta el año académico y proyecta el resultado del cuarto periodo.</p></div></div>
        <div class="sn-header-actions"><button class="sn-btn sn-btn-icon" id="help-open" type="button" aria-label="Cómo usar el simulador" title="Cómo usar el simulador"><?=sn_icon('info')?></button><button class="sn-btn sn-btn-primary" id="export" type="button"><?=sn_icon('download')?>Exportar vista</button></div>
    </header>
    <?php if($error): ?><div class="sn-alert sn-alert-error" role="alert"><?=sn_icon('info')?><p><?=sn_e($error)?></p></div><?php endif; ?>
    <?php if($message): ?><div class="sn-alert sn-alert-success" role="status"><?=sn_icon('check')?><p><?=sn_e($message)?></p></div><?php endif; ?>
    <section class="sn-panel sn-live-panel" aria-labelledby="pdf-title" data-state="ready">
        <div class="sn-live-heading"><div class="sn-live-title"><span class="sn-live-mark"><?=sn_icon('file')?></span><div><h2 id="pdf-title">Notas de consulta</h2><p>PDF de SISGA y notas actualizadas · P4 editable para simular</p></div></div><div class="sn-live-controls"><button class="sn-btn" id="pdf-reset" type="button" disabled><?=sn_icon('undo')?>Restaurar P4 de consulta</button></div></div>
        <p class="sn-live-status"><?=sn_e($data['source'])?></p>
        <p class="sn-live-note">P4 inicia con la nota de consulta, incluidas las actualizaciones registradas. Puedes editarla para simular y restaurarla por materia o en toda la vista. Tus simulaciones se conservan en esta pestaña.</p>
    </section>
    <div class="sn-layout">
        <aside class="sn-sidebar sn-panel" aria-label="Seleccionar estudiante" data-open="false">
            <button class="sn-roster-toggle" id="roster-toggle" type="button" aria-expanded="false" aria-controls="roster-content"><?=sn_icon('users')?><span>Cambiar estudiante</span><span id="mobile-roster-count"></span><?=sn_icon('chevron')?></button>
            <div class="sn-roster-content" id="roster-content">
                <div class="sn-sidebar-heading"><h2>Estudiantes</h2><span id="student-count" class="sn-count-badge">0</span></div>
                <div class="sn-roster-filters"><label>Año académico<select id="year" aria-label="Año académico"></select></label><label>Grupo<select id="group" aria-label="Grupo"></select></label></div>
                <label class="sn-search"><span class="sn-sr">Buscar estudiante por nombre o código</span><?=sn_icon('search')?><input id="search" type="search" autocomplete="off" placeholder="Buscar nombre o código" aria-controls="student-list"></label>
                <button class="sn-overview-button" id="overview" type="button" aria-pressed="false"><span class="sn-overview-icon"><?=sn_icon('users')?></span><span><strong>Vista general</strong><small>Todos los estudiantes del grupo</small></span><?=sn_icon('arrow')?></button>
                <div class="sn-list-label"><span>SELECCIONA UN ESTUDIANTE</span><span id="list-count"></span></div>
                <div id="student-list" class="sn-student-list" aria-label="Estudiantes disponibles"></div>
                <div class="sn-sidebar-foot"><?=sn_icon('info')?><span>Las notas de consulta provienen del PDF de referencia.</span></div>
            </div>
        </aside>
        <div class="sn-main-column">
            <div class="sn-view-tabs" role="tablist" aria-label="Vistas del seguimiento académico">
                <button id="consultation-tab" type="button" role="tab" aria-selected="true" aria-controls="consultation-panel"><?=sn_icon('book')?>Consulta y simulación</button>
                <button id="alerts-tab" type="button" role="tab" aria-selected="false" aria-controls="alerts-panel" tabindex="-1"><?=sn_icon('info')?>Alertas del año<span id="alerts-badge">0</span></button>
                <button id="reports-tab" type="button" role="tab" aria-selected="false" aria-controls="reports-panel" tabindex="-1"><?=sn_icon('book')?>Fichas anuales</button>
                <button id="missing-tab" type="button" role="tab" aria-selected="false" aria-controls="missing-panel" tabindex="-1"><?=sn_icon('file')?>Notas faltantes<span id="missing-badge">0</span></button>
            </div>
            <div id="consultation-panel" role="tabpanel" aria-labelledby="consultation-tab">
            <section class="sn-context sn-panel" aria-labelledby="context-title">
                <div class="sn-context-main"><span id="context-avatar" class="sn-avatar sn-avatar-large" aria-hidden="true"></span><div><div class="sn-eyebrow" id="context-eyebrow">CONSULTA POR ESTUDIANTE</div><h2 id="context-title">Selecciona un estudiante</h2><p id="context-meta"></p></div></div>
                <div class="sn-context-progress"><div><span>Proyección completada</span><strong id="progress-label">0 de 0 materias</strong></div><progress id="progress" value="0" max="1" aria-label="Materias con proyección completa"></progress><small id="progress-hint">Ingresa P4 para ver la nota final.</small></div>
            </section>
            <section class="sn-stats" aria-label="Resumen de la consulta">
                <article class="sn-metric"><div><span id="stat-records-label">Materias</span><?=sn_icon('book')?></div><strong id="stat-records">0</strong><small id="stat-students">en el reporte del estudiante</small></article>
                <article class="sn-metric" title="Promedio simple de las materias con notas en los tres primeros periodos."><div><span>Promedio P1–P3</span><?=sn_icon('chart')?></div><strong id="stat-history">—</strong><small id="stat-history-hint">Sin notas completas</small></article>
                <article class="sn-metric sn-metric-primary"><div><span>Final proyectada</span><?=sn_icon('edit')?></div><strong id="stat-average">—</strong><small id="stat-complete">Ingresa una nota en P4</small></article>
                <article class="sn-metric"><div><span>Bajo la meta</span><span class="sn-status-dot sn-dot-warning"></span></div><strong id="stat-risk">—</strong><small id="stat-risk-hint">Meta final: 3,00</small></article>
            </section>
            <section class="sn-panel sn-results" id="sn-results" aria-labelledby="results-title" tabindex="-1">
                <div class="sn-results-heading"><div><h2 id="results-title">Materias y simulación</h2><p id="results-description">Escribe una nota en <span class="sn-inline-p4">P4</span> para calcular la final al instante.</p></div><button id="bulk-open" class="sn-btn sn-btn-primary" type="button"><?=sn_icon('edit')?>Simular varias materias</button></div>
                <div class="sn-table-filters">
                    <label><span class="sn-sr">Filtrar por materia</span><select id="subject" aria-label="Filtrar por materia"></select></label>
                    <label><span class="sn-sr">Periodos visibles</span><select id="period" aria-label="Periodos visibles"><option value="all">Todos los periodos</option><option value="1">Periodo 1 y simulación</option><option value="2">Periodo 2 y simulación</option><option value="3">Periodo 3 y simulación</option><option value="4">Solo P4 editable</option></select></label>
                    <label><span class="sn-sr">Filtrar por resultado</span><select id="status" aria-label="Filtrar por resultado"><option value="all">Todos los resultados</option><option value="pass">Alcanza la meta</option><option value="risk">Bajo la meta</option><option value="pending">Sin proyección</option></select></label>
                    <button class="sn-clear-filters" id="clear" type="button" hidden>Limpiar filtros</button>
                </div>
                <div id="filter-context" class="sn-filter-context" hidden></div>
                <div class="sn-table-scroll"><table class="sn-table" aria-describedby="table-help"><caption class="sn-sr">Notas del año y proyección de la nota final</caption><thead id="head"></thead><tbody id="rows"></tbody></table></div>
                <div class="sn-table-footer"><span id="count"></span><div class="sn-pagination"><label class="sn-page-size">Ver <select id="page-size" aria-label="Registros por página"><option value="10">10</option><option value="20">20</option><option value="50">50</option></select></label><button class="sn-btn sn-btn-icon" id="prev" aria-label="Página anterior" type="button"><?=sn_icon('chevron')?></button><span id="page-label"></span><button class="sn-btn sn-btn-icon" id="next" aria-label="Página siguiente" type="button"><?=sn_icon('chevron')?></button></div></div>
                <div class="sn-scenario-bar"><div class="sn-save-state" id="save-state"><?=sn_icon('check')?><span>Escenario guardado en esta pestaña</span></div><div><button id="undo" class="sn-btn sn-btn-quiet" type="button" disabled><?=sn_icon('undo')?>Deshacer</button><button id="reset" class="sn-btn sn-btn-quiet" type="button" disabled><?=sn_icon('reset')?>Restablecer vista</button></div></div>
            </section>
            </div>
            <section id="alerts-panel" class="sn-year-alerts" role="tabpanel" aria-labelledby="alerts-tab" hidden>
                <div class="sn-panel sn-alert-overview">
                    <div class="sn-alert-heading"><div><div class="sn-eyebrow">SEGUIMIENTO DEL AÑO</div><h2 id="alerts-title">Materias en riesgo por estudiante</h2><p id="alerts-scope"></p></div><button id="alerts-export" class="sn-btn" type="button"><?=sn_icon('download')?>Exportar alertas</button></div>
                    <p class="sn-alert-explanation">Sin P4, se revisa el promedio ponderado de P1–P3. Con P4, se revisa la nota final del escenario. La meta actual es <strong id="alerts-goal">3,00</strong>.</p>
                    <div class="sn-alert-metrics" aria-label="Resumen de alertas"><div><strong id="alerts-students">0</strong><span>estudiantes en riesgo</span></div><div><strong id="alerts-subjects">0</strong><span>materias en riesgo</span></div><div><strong id="alerts-incomplete">0</strong><span>materias con datos incompletos</span></div></div>
                    <div class="sn-alert-filters"><label>Buscar estudiante<input id="alerts-search" type="search" placeholder="Nombre o código" autocomplete="off"></label><label>Materia<select id="alerts-subject" aria-label="Materia en alertas"></select></label><label>Mostrar<select id="alerts-filter" aria-label="Estado de las alertas"><option value="risk">Con materias en riesgo</option><option value="incomplete">Con datos incompletos</option><option value="all">Todos los estudiantes</option></select></label></div>
                    <button id="alerts-all" class="sn-text-button" type="button" hidden>Ver alertas de todos los estudiantes</button>
                </div>
                <div id="alerts-list" class="sn-alert-students"></div>
                <div class="sn-alert-pagination"><span id="alerts-count" role="status" aria-live="polite"></span><div><button id="alerts-prev" class="sn-btn" type="button" aria-label="Página anterior de alertas">Anterior</button><button id="alerts-next" class="sn-btn" type="button" aria-label="Página siguiente de alertas">Siguiente</button></div></div>
                <p class="sn-footnote sn-alert-method"><strong>Cómo leer el acumulado:</strong> es el aporte de P1–P3 al 100 % del año. Con tres periodos de 25 %, un promedio de 2,80 aporta 2,10 al año y necesita 3,60 en P4 para llegar a 3,00. Un dato pendiente no se cuenta como cero.</p>
            </section>
            <section id="reports-panel" class="sn-reports" role="tabpanel" aria-labelledby="reports-tab" hidden>
                <div class="sn-panel sn-report-toolbar">
                    <div class="sn-report-toolbar-heading"><div><div class="sn-eyebrow">FICHA ACADÉMICA POR ESTUDIANTE</div><h2 id="reports-title">Notas y promedio del año</h2><p id="reports-scope"></p></div><button id="reports-print" class="sn-btn sn-btn-primary" type="button"><?=sn_icon('file')?>Imprimir fichas</button></div>
                    <div class="sn-report-search"><label for="reports-search">Buscar estudiante<input id="reports-search" type="search" placeholder="Nombre o código" autocomplete="off"></label><button id="reports-all" class="sn-text-button" type="button" hidden>Ver fichas de todos los estudiantes</button></div>
                    <p class="sn-report-hint" id="reports-print-hint"></p>
                </div>
                <div id="reports-list" class="sn-report-list"></div>
                <div class="sn-alert-pagination"><span id="reports-count" role="status" aria-live="polite"></span><div><button id="reports-prev" class="sn-btn" type="button">Anterior</button><button id="reports-next" class="sn-btn" type="button">Siguiente</button></div></div>
            </section>
            <section id="missing-panel" class="sn-missing-panel" role="tabpanel" aria-labelledby="missing-tab" hidden>
                <div class="sn-panel sn-alert-overview">
                    <div class="sn-alert-heading"><div><div class="sn-eyebrow">REVISIÓN DE REGISTROS</div><h2 id="missing-title">Notas faltantes por estudiante</h2><p id="missing-scope"></p></div><button id="missing-export" class="sn-btn" type="button"><?=sn_icon('download')?>Exportar faltantes</button></div>
                    <p class="sn-alert-explanation">Revisa qué estudiante, materia y periodo requieren atención. Los cuatro periodos se consultan en el PDF de referencia. Las simulaciones de P4 no completan las notas que faltan en el documento.</p>
                    <div class="sn-alert-metrics sn-missing-metrics" aria-label="Resumen de notas pendientes"><div><strong id="missing-confirmed">0</strong><span>notas no registradas</span></div><div><strong id="missing-students">0</strong><span>estudiantes por revisar</span></div><div><strong id="missing-review">0</strong><span>notas por verificar o corregir</span></div></div>
                    <div class="sn-alert-filters sn-missing-filters">
                        <label>Buscar estudiante<input id="missing-search" type="search" placeholder="Nombre o código" autocomplete="off" aria-label="Buscar en notas faltantes"></label>
                        <label>Materia<select id="missing-subject" aria-label="Materia con notas faltantes"></select></label>
                        <label>Periodo<select id="missing-period" aria-label="Periodo con notas faltantes"><option value="">Todos los periodos</option><option value="1">Periodo 1</option><option value="2">Periodo 2</option><option value="3">Periodo 3</option><option value="4">Periodo 4</option></select></label>
                        <label>Estado<select id="missing-status" aria-label="Estado de las notas faltantes"><option value="">Todos los estados</option><option value="missing">Nota no registrada</option><option value="invalid">Nota inválida</option></select></label>
                    </div>
                    <button id="missing-all" class="sn-text-button" type="button" hidden>Ver notas faltantes de todos los estudiantes</button>
                    <p id="missing-state" class="sn-missing-state" role="status" hidden></p>
                </div>
                <div class="sn-panel sn-missing-results">
                    <div class="sn-table-scroll"><table class="sn-table sn-missing-table"><caption class="sn-sr">Detalle de notas pendientes por estudiante, materia y periodo</caption><thead><tr><th scope="col">Estudiante y materia</th><th scope="col">Periodo</th><th scope="col">Fuente</th><th scope="col">Estado y motivo</th><th scope="col">Consulta</th></tr></thead><tbody id="missing-rows"></tbody></table></div>
                    <div class="sn-table-footer"><span id="missing-count" role="status" aria-live="polite"></span><div class="sn-pagination"><button id="missing-prev" class="sn-btn" type="button" aria-label="Página anterior de notas faltantes">Anterior</button><button id="missing-next" class="sn-btn" type="button" aria-label="Página siguiente de notas faltantes">Siguiente</button></div></div>
                </div>
                <p class="sn-footnote">Las notas vacías del PDF permanecen pendientes. Las ediciones de P4 solo afectan la simulación.</p>
            </section>
            <div class="sn-calculation-strip"><span><?=sn_icon('info')?><span id="rules-summary">Cada periodo aporta el 25 % · Meta: 3,00</span></span><button id="settings-open" class="sn-text-button" type="button"><?=sn_icon('settings')?>Configurar cálculo</button></div>
            <p id="table-help" class="sn-footnote">«—» indica una nota pendiente. Las simulaciones no cambian las notas oficiales.</p>
        </div>
    </div>
    <footer class="sn-footer"><span><?=sn_icon('file')?><span id="source"></span></span><span id="source-date"></span></footer>
    <div class="sn-toast" id="notice" role="status" aria-live="polite" hidden></div>

    <dialog id="bulk-dialog" class="sn-dialog" aria-labelledby="bulk-title">
        <div class="sn-dialog-heading"><span class="sn-dialog-mark"><?=sn_icon('edit')?></span><button class="sn-btn sn-btn-icon" data-close type="button" aria-label="Cerrar simulación en lote"><?=sn_icon('close')?></button></div>
        <h2 id="bulk-title">Una nota, varias materias</h2><p class="sn-dialog-description" id="bulk-scope"></p>
        <form id="bulk-form"><label class="sn-label" for="bulk">Nota simulada de P4</label><div class="sn-bulk-value"><input id="bulk" type="number" min="1" max="5" step="0.01" inputmode="decimal" autocomplete="off" value="3.50" required aria-describedby="bulk-error"><span>de 5,00</span></div><input id="bulk-range" type="range" min="1" max="5" step="0.05" value="3.5" aria-label="Ajustar nota simulada P4"><div class="sn-range-labels"><span>1,00</span><span>3,00</span><span>5,00</span></div><p class="sn-field-error" id="bulk-error" hidden>Ingresa una nota entre 1,00 y 5,00, con máximo dos decimales.</p><div id="bulk-preview" class="sn-bulk-preview"></div><p class="sn-dialog-note">Se aplicará a toda la vista filtrada, incluidas otras páginas. Puedes deshacer este cambio.</p><div class="sn-dialog-actions"><button class="sn-btn" type="button" data-close>Cancelar</button><button class="sn-btn sn-btn-primary" id="apply" type="submit">Aplicar simulación</button></div></form>
    </dialog>
    <dialog id="settings-dialog" class="sn-dialog" aria-labelledby="settings-title">
        <div class="sn-dialog-heading"><span class="sn-dialog-mark"><?=sn_icon('settings')?></span><button class="sn-btn sn-btn-icon" data-close type="button" aria-label="Cerrar configuración"><?=sn_icon('close')?></button></div>
        <h2 id="settings-title">Reglas de la simulación</h2><p class="sn-dialog-description">Define el aporte de cada periodo y la nota final que quieres alcanzar.</p>
        <form id="settings-form"><div class="sn-label-row"><span class="sn-label">Peso de los periodos</span><button id="equal-weights" class="sn-text-button" type="button">Repartir 25 %</button></div><div class="sn-weights"><?php for($i=1;$i<=4;$i++): ?><label for="w<?=$i?>">Periodo <?=$i?><span><input id="w<?=$i?>" type="number" min="<?=$i===4?'0.1':'0'?>" max="100" step="0.1" value="25" required>%</span></label><?php endfor; ?></div><p id="weight-total" class="sn-weight-total" role="status"></p><label class="sn-label" for="target">Meta de nota final</label><div class="sn-target-field"><input id="target" type="number" min="1" max="5" step="0.01" value="3" required><span>Escala de 1,00 a 5,00</span></div><p class="sn-dialog-note">Los porcentajes deben sumar 100 %. El estado se calcula con la nota sin redondear; la nota necesaria en P4 se redondea hacia arriba a dos decimales.</p><div class="sn-dialog-actions"><button class="sn-btn" type="button" data-close>Cancelar</button><button class="sn-btn sn-btn-primary" id="settings-save" type="submit">Guardar configuración</button></div></form>
    </dialog>
    <dialog id="help-dialog" class="sn-dialog" aria-labelledby="help-title">
        <div class="sn-dialog-heading"><span class="sn-dialog-mark"><?=sn_icon('info')?></span><button class="sn-btn sn-btn-icon" data-close type="button" aria-label="Cerrar ayuda"><?=sn_icon('close')?></button></div>
        <h2 id="help-title">De la consulta a la proyección</h2><p class="sn-dialog-description">Todo lo que necesitas para explorar el cierre del año.</p><ol class="sn-help-steps"><li><strong>Elige un estudiante</strong><p>Búscalo por nombre o código. Usa año y grupo para acotar la lista, o abre Vista general.</p></li><li><strong>Prueba una nota en P4</strong><p>Escribe de 1,00 a 5,00 en una materia. La nota final se actualiza mientras escribes. Con Enter pasas a la siguiente materia.</p></li><li><strong>Compara con la meta</strong><p>«Necesita en P4» indica la nota mínima para alcanzar la meta. Si faltan notas de P1–P3, no se calcula la proyección.</p></li></ol><p class="sn-dialog-note">Tus escenarios se conservan en esta pestaña. Exporta la vista para descargar los resultados y usa Deshacer para recuperar el escenario anterior.</p><div class="sn-dialog-actions"><button class="sn-btn sn-btn-primary" type="button" data-close>Entendido</button></div>
    </dialog>
</main>
<div id="sn-print-cards"></div>
<script type="application/json" id="sn-data"><?=json_encode($payload,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE)?></script>
<script src="model.js?v=<?=sn_e($assetVersion)?>"></script><script src="app.js?v=<?=sn_e($assetVersion)?>"></script>
</body></html>
