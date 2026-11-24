<?php if (!defined('SGA_SERVER')) { http_response_code(404); exit; } ?>
<section id="evidencias-view" class="hidden" aria-labelledby="ev-title">
    <div class="ev-hero">
        <div><span class="ev-eyebrow">DEL APRENDIZAJE A LO OBSERVABLE</span><h2 id="ev-title">Cada DBA, una evidencia.</h2><p>Conecte lo que se aprende con lo que el estudiante puede demostrar.</p></div>
        <span class="ev-hero-icon" aria-hidden="true"><i class="fa-solid fa-seedling"></i></span>
    </div>
    <div id="ev-stats" class="ev-stats" aria-live="polite"></div>
    <div class="ev-toolbar">
        <div class="ev-segments" aria-label="Herramientas de evidencias">
            <button type="button" class="active" data-ev-mode="library"><i class="fa-solid fa-layer-group"></i> Biblioteca</button>
            <button type="button" data-ev-mode="import"><i class="fa-solid fa-file-import"></i> Importar</button>
            <button type="button" data-ev-mode="ai"><i class="fa-solid fa-wand-magic-sparkles"></i> Generar con IA</button>
        </div>
        <button class="btn btn-secondary" type="button" id="ev-refresh"><i class="fa-solid fa-rotate"></i> Actualizar</button>
    </div>
    <p id="ev-status" role="status" aria-live="polite" class="ev-status"></p>
    <div class="ev-filters">
        <label class="ev-search"><span class="sr-only">Buscar DBA, evidencia o palabra clave</span><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><input id="ev-search" type="search" placeholder="Buscar DBA, evidencia o palabra clave…"></label>
        <label><span class="sr-only">Período</span><select id="ev-period" class="modern-select"><option value="">Todos los períodos</option><option value="1">Período 1</option><option value="2">Período 2</option><option value="3">Período 3</option><option value="4">Período 4</option></select></label>
        <label><span class="sr-only">Estado de evidencias</span><select id="ev-filter" class="modern-select"><option value="">Todos los estados</option><option value="missing">Sin evidencia</option><option value="pending">IA por revisar</option><option value="complete">Con evidencia</option></select></label>
        <button type="button" id="ev-clear" class="btn btn-secondary">Limpiar filtros</button>
    </div>
    <p class="ev-hint">Los filtros de grado y materia del panel lateral se aplican a esta biblioteca y a la generación.</p>
    <div id="ev-library-panel">
        <div class="ev-selection"><label><input type="checkbox" id="ev-select-all"> Seleccionar resultados filtrados</label><span id="ev-selection-count"></span><button type="button" class="btn btn-secondary" id="ev-generate-selection">Generar para seleccionados</button></div>
        <div id="ev-library" class="ev-library"></div>
        <div class="ev-pagination"><button type="button" class="btn btn-secondary" id="ev-prev">Anterior</button><span id="ev-page"></span><button type="button" class="btn btn-secondary" id="ev-next">Siguiente</button></div>
    </div>
    <div id="ev-import-panel" class="hidden ev-workbench">
        <aside class="ev-panel"><span class="ev-step">01 / PREPARE SU ARCHIVO</span><h3>Importe con confianza</h3><p>Descargue la plantilla con los ID y nombres reales de sus DBA. Complete <strong>evidencia</strong>; puede repetir un ID para añadir varias evidencias.</p><a class="btn btn-primary" href="api/evidencias.php?action=template"><i class="fa-solid fa-download"></i> Descargar plantilla CSV</a><p class="ev-hint">Columnas: id_dba, dba_referencia, evidencia, palabras_clave. Las filas vacías y los duplicados se omiten. Los conceptos completan campos vacíos.</p><label class="ev-file">Seleccionar archivo<input id="ev-file" type="file" accept=".csv,.txt,.json"></label><p class="ev-hint">CSV, TXT o JSON · UTF-8 · hasta 5 MB / 1.000 filas.</p><button type="button" id="ev-import-preview" class="btn btn-secondary" disabled>Validar y previsualizar</button></aside>
        <div class="ev-panel"><span class="ev-step">02 / REVISE Y GUARDE</span><h3>Vista previa de la importación</h3><p id="ev-import-summary" role="status">Seleccione un archivo para revisar las evidencias antes de importarlas.</p><div id="ev-import-errors" class="ev-errors"></div><div id="ev-import-preview-list" class="ev-proposals"></div><button type="button" id="ev-import-save" class="btn btn-primary" disabled>Guardar evidencias del archivo</button></div>
    </div>
    <div id="ev-ai-panel" class="hidden ev-workbench">
        <aside class="ev-panel ev-studio"><span class="ev-step"><i class="fa-solid fa-wand-magic-sparkles"></i> ESTUDIO DE EVIDENCIAS</span><h3>Asistente curricular</h3><p>La IA lee el texto de cada DBA y tiene en cuenta su grado y materia para proponer desempeños observables.</p><div class="ev-connection"><span id="ev-connection-state">Sin comprobar</span><button type="button" id="ev-connect" class="btn btn-secondary">Conectar</button></div><p class="ev-hint" id="ev-server">LM Studio · 169.254.173.61:1234</p><label class="ev-field">Modelo<select id="ev-model" class="modern-select"><option value="gemma-2-2b-it">gemma-2-2b-it</option></select></label><label class="ev-field">Qué DBA generar<select id="ev-scope" class="modern-select"><option value="selected">DBA seleccionados</option><option value="missing">Filtrados sin evidencia</option><option value="filtered">Todos los DBA filtrados</option></select></label><label class="ev-field">Evidencias por DBA<select id="ev-quantity" class="modern-select"><option value="1">1 evidencia concreta</option><option value="2">2 evidencias complementarias</option><option value="3">3 evidencias complementarias</option></select></label><p id="ev-ai-targets" class="ev-hint"></p><button type="button" id="ev-generate" class="btn btn-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Generar propuestas</button><button type="button" id="ev-stop" class="btn btn-secondary hidden">Detener después del DBA actual</button><progress id="ev-progress" max="1" value="0" aria-label="Avance de generación"></progress><p id="ev-progress-text" role="status" aria-live="polite"></p><p class="ev-hint">Las propuestas se pueden editar antes de guardarlas. Se identifican como IA pendiente de revisión; no son citas oficiales del MEN.</p></aside>
        <div class="ev-panel"><div class="ev-preview-heading"><div><span class="ev-step">SU CRITERIO, LA ÚLTIMA PALABRA</span><h3>Propuestas para revisar</h3></div><button type="button" class="btn btn-secondary" id="ev-discard">Limpiar propuestas</button></div><div id="ev-ai-errors" class="ev-errors"></div><div id="ev-ai-proposals" class="ev-proposals"><div class="ev-empty"><i class="fa-regular fa-lightbulb"></i><h3>De la intención a la evidencia</h3><p>Seleccione los DBA y genere propuestas. Aquí podrá ajustarlas y elegir cuáles guardar.</p></div></div><button type="button" id="ev-ai-save" class="btn btn-primary" disabled>Guardar propuestas seleccionadas</button></div>
    </div>
</section>
<dialog id="ev-dialog" class="ev-dialog">
    <form id="ev-form"><header><div><span class="ev-step">EVIDENCIA DE APRENDIZAJE</span><h3 id="ev-dialog-title">Nueva evidencia</h3></div><button type="button" id="ev-dialog-close" class="btn-icon" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button></header><p id="ev-dialog-context"></p><label class="ev-field" for="ev-text">¿Qué podrá demostrar el estudiante?</label><textarea id="ev-text" class="modern-input" rows="7" maxlength="6000" required placeholder="Ej. Explica mediante un esquema…"></textarea><p class="ev-hint">Describa una acción observable y cómo se comprobará. Guardar una propuesta de IA aquí la marca como revisada.</p><p id="ev-dialog-error" role="alert"></p><footer><button type="button" id="ev-dialog-cancel" class="btn btn-secondary">Cancelar</button><button type="submit" id="ev-dialog-save" class="btn btn-primary">Guardar evidencia</button></footer></form>
</dialog>
