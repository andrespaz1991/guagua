<?php
require_once __DIR__ . '/../../comun/conexion.php';
guagua_exigir_rol(guagua_roles_gestion_curricular());
$csrfToken = guagua_csrf_token();
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malla Curricular - DB Manager</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="/guagua/comun/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/assets/css/style.css'); ?>">
    <link rel="stylesheet" href="assets/css/evidencias.css?v=<?php echo filemtime(__DIR__ . '/assets/css/evidencias.css'); ?>">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar / Navigation -->
        <aside class="sidebar">
            <div class="brand">
                <i class="fa-solid fa-database"></i>
                <h2>Malla viva</h2>
            </div>
            
            <div class="nav-section">
                <h3>Filtros Principales</h3>
                
                <div class="form-group">
                    <label for="select-grado">Grado</label>
                    <div class="select-wrapper">
                        <select id="select-grado">
                            <option value="">Seleccione un grado...</option>
                        </select>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="select-materia">Materia</label>
                    <div class="select-wrapper">
                        <select id="select-materia">
                            <option value="">Seleccione una materia...</option>
                        </select>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </div>
            
            <div class="nav-actions">
                 <p class="help-text"><i class="fa-solid fa-circle-info" aria-hidden="true"></i><span>Use los botones <i class="fa-solid fa-plus" aria-hidden="true"></i>, <i class="fa-solid fa-pen" aria-hidden="true"></i> y <i class="fa-solid fa-trash-can" aria-hidden="true"></i> para crear, modificar o eliminar. Los cambios se guardan directamente.</span></p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-header">
                <div class="header-info">
                    <h1 id="view-title">Configuración Curricular</h1>
                    <p id="view-subtitle">Seleccione un Grado y Materia en el panel lateral para empezar.</p>
                </div>
                <div class="header-actions">
                    <button id="theme-toggle" class="btn-icon" title="Cambiar Tema">
                        <i class="fa-solid fa-moon"></i>
                    </button>
                </div>
            </header>

            <nav class="view-tabs" aria-label="Vistas del módulo">
                <button type="button" class="view-tab active" id="tab-editor" data-view="editor" aria-selected="true">
                    <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Editor curricular
                </button>
                <button type="button" class="view-tab" id="tab-pendientes" data-view="pendientes" aria-selected="false">
                    <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i> Reporte de pendientes
                </button>
                <button type="button" class="view-tab" id="tab-evidencias" data-view="evidencias" aria-selected="false">
                    <i class="fa-solid fa-seedling" aria-hidden="true"></i> Evidencias de aprendizaje
                </button>
                <button type="button" class="view-tab" id="tab-importar" data-view="importar" aria-selected="false">
                    <i class="fa-solid fa-file-import" aria-hidden="true"></i> Importar DBA
                </button>
                <button type="button" class="view-tab" id="tab-exportar" data-view="exportar" aria-selected="false">
                    <i class="fa-solid fa-file-export" aria-hidden="true"></i> Exportar
                </button>
            </nav>

            <?php require __DIR__ . '/views/evidencias.php'; ?>
            <section id="exportar-view" class="hidden" aria-labelledby="exportar-title">
                <div class="import-heading">
                    <h2 id="exportar-title">Exportar malla curricular</h2>
                    <p>Descargue estándares, DBA, descripciones, palabras clave, ejes temáticos y evidencias de aprendizaje. Seleccione aquí el alcance de la descarga.</p>
                </div>
                <div class="export-filters">
                    <div class="form-group">
                        <label for="export-grado">Grado</label>
                        <select id="export-grado" class="modern-select"><option value="">Todos los grados</option></select>
                    </div>
                    <div class="form-group">
                        <label for="export-materia">Materia</label>
                        <select id="export-materia" class="modern-select"><option value="">Todas las materias</option></select>
                    </div>
                    <div class="form-group">
                        <label for="export-periodo">Periodo</label>
                        <select id="export-periodo" class="modern-select">
                            <option value="">Todos los periodos</option>
                            <option value="1">Periodo 1</option>
                            <option value="2">Periodo 2</option>
                            <option value="3">Periodo 3</option>
                            <option value="4">Periodo 4</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="export-formato">Formato</label>
                        <select id="export-formato" class="modern-select">
                            <option value="json">JSON · datos estructurados</option>
                            <option value="csv">CSV · hoja de cálculo</option>
                            <option value="txt">TXT · texto para lectura</option>
                        </select>
                    </div>
                </div>
                <div class="import-panel">
                    <p id="export-summary" class="export-summary" aria-live="polite">Cargando datos para exportar…</p>
                    <p class="ev-hint">Se incluyen los registros guardados y las evidencias de IA pendientes de revisión, con su estado. Cada archivo identifica el grado, la materia y el periodo de los datos.</p>
                    <div class="export-actions">
                        <button type="button" class="btn btn-secondary" id="export-use-editor">Usar grado y materia del panel lateral</button>
                        <button type="button" class="btn btn-primary" id="btn-exportar" disabled><i class="fa-solid fa-download" aria-hidden="true"></i> Descargar archivo</button>
                    </div>
                    <p id="export-result" class="import-result" role="status" aria-live="polite"></p>
                </div>
                <div class="import-guide">
                    <h3>Contenido de los formatos</h3>
                    <p><strong>JSON:</strong> colecciones relacionadas por sus IDs, con filtros y totales. <strong>CSV:</strong> una fila por estándar, DBA, eje o evidencia; la columna <code>tipo_registro</code> identifica cada fila y las columnas de contexto conservan su relación. <strong>TXT:</strong> lectura organizada por grado, materia, periodo, estándar y DBA.</p>
                    <p>Estos archivos son de consulta y respaldo. La importación del módulo utiliza sus propias plantillas.</p>
                </div>
            </section>
            <section id="editor-view">
                <div id="accordion-toolbar" class="accordion-toolbar hidden" role="group" aria-label="Controles de todos los periodos">
                    <span>Todos los periodos</span>
                    <div class="accordion-quick-actions">
                        <button type="button" class="btn btn-secondary" data-accordion-action="expand" aria-controls="periods-container" aria-label="Expandir todo de todos los periodos"><i class="fa-solid fa-angles-down" aria-hidden="true"></i> Expandir todo</button>
                        <button type="button" class="btn btn-secondary" data-accordion-action="collapse" aria-controls="periods-container" aria-label="Contraer todo de todos los periodos"><i class="fa-solid fa-angles-up" aria-hidden="true"></i> Contraer todo</button>
                    </div>
                </div>
                <div class="content-area" id="periods-container">
                    <div class="empty-state">
                        <i class="fa-solid fa-layer-group"></i>
                        <p>No hay datos seleccionados</p>
                    </div>
                </div>
                <p id="operation-status" class="operation-status" role="status" aria-live="polite"></p>
            </section>

            <section id="pendientes-view" class="hidden" aria-labelledby="pendientes-title">
                <div class="audit-heading">
                    <div>
                        <h2 id="pendientes-title">Reporte de diligenciamiento pendiente</h2>
                        <p>Identifique por grado, materia y período los estándares, DBA o ejes temáticos que faltan por registrar.</p>
                    </div>
                </div>
                <div class="audit-filters" aria-label="Filtros del reporte">
                    <div class="form-group">
                        <label for="reporte-grado">Grado</label>
                        <div class="select-wrapper">
                            <select id="reporte-grado"><option value="">Todos los grados</option></select>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reporte-materia">Materia</label>
                        <div class="select-wrapper">
                            <select id="reporte-materia"><option value="">Todas las materias</option></select>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reporte-periodo">Período</label>
                        <div class="select-wrapper">
                            <select id="reporte-periodo">
                                <option value="">Todos los períodos</option>
                                <option value="1">Período 1</option>
                                <option value="2">Período 2</option>
                                <option value="3">Período 3</option>
                                <option value="4">Período 4</option>
                            </select>
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </div>
                    </div>
                    <label class="audit-toggle">
                        <input type="checkbox" id="reporte-mostrar-completos">
                        <span>Mostrar registros completos</span>
                    </label>
                </div>
                <div id="audit-summary" class="audit-summary" aria-live="polite"></div>
                <div class="audit-table-wrap">
                    <table class="audit-table">
                        <thead>
                            <tr>
                                <th>Grado</th>
                                <th>Materia</th>
                                <th>Período</th>
                                <th>Registrado</th>
                                <th>Pendiente</th>
                                <th><span class="sr-only">Acción</span></th>
                            </tr>
                        </thead>
                        <tbody id="audit-table-body"></tbody>
                    </table>
                </div>
            </section>

            <section id="importar-view" class="hidden" aria-labelledby="importar-title">
                <div class="import-heading">
                    <div>
                        <h2 id="importar-title">Importar estructura curricular</h2>
                        <p>Cargue registros de estándares, DBA y ejes temáticos en formato JSON, CSV o TXT. Los duplicados se omiten sin modificar lo que ya está registrado.</p>
                    </div>
                </div>
                <div class="import-panel">
                    <label class="import-dropzone" for="import-file">
                        <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                        <strong>Seleccione un archivo para importar</strong>
                        <span>Formatos admitidos: .json, .csv y .txt · máximo 5 MB</span>
                        <input id="import-file" type="file" accept=".json,.csv,.txt,application/json,text/csv,text/plain">
                    </label>
                    <p id="import-file-name" class="import-file-name" aria-live="polite">Aún no ha seleccionado un archivo.</p>
                    <div class="import-actions">
                        <button type="button" class="btn btn-primary" id="btn-importar" disabled><i class="fa-solid fa-file-import"></i> Importar archivo</button>
                    </div>
                    <p id="import-result" class="import-result" role="status" aria-live="polite"></p>
                </div>
                <div class="import-guide">
                    <h3>Estructura requerida</h3>
                    <p>Las cuatro primeras columnas/campos son obligatorios. Las demás son opcionales.</p>
                    <div class="import-columns"><code>grado</code><code>materia</code><code>periodo</code><code>estandar</code><code>descripcion_estandar</code><code>dba</code><code>descripcion_dba</code><code>eje_tematico</code><code>descripcion_eje</code></div>
                    <p>En <strong>grado</strong> y <strong>materia</strong> puede escribir el nombre existente o su ID. El período debe ser 1, 2, 3 o 4.</p>
                    <div class="import-templates">
                        <a href="assets/plantillas/importacion_dba.csv" download><i class="fa-solid fa-file-csv"></i> Plantilla CSV</a>
                        <a href="assets/plantillas/importacion_dba.txt" download><i class="fa-solid fa-file-lines"></i> Plantilla TXT</a>
                        <a href="assets/plantillas/importacion_dba.json" download><i class="fa-solid fa-file-code"></i> Plantilla JSON</a>
                        <a href="ESTRUCTURA_IMPORTACION.md" target="_blank" rel="noopener"><i class="fa-solid fa-book"></i> Ver guía</a>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modals -->

    <!-- Modal Añadir Estándar -->
    <div id="modal-estandar" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="title-modal-estandar">Añadir Estándar</h3>
                <button class="close-modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre del Estándar</label>
                    <textarea id="input-nombre-estandar" class="modern-input" rows="3" placeholder="Ej: Comprendo la importancia de..."></textarea>
                </div>
                <div class="form-group">
                    <label for="input-descripcion-estandar">Descripción (opcional)</label>
                    <textarea id="input-descripcion-estandar" class="modern-input" rows="2" placeholder="Detalle o aclaración del estándar"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <button class="btn btn-primary" id="btn-save-estandar">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Añadir DBA -->
    <div id="modal-dba" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="title-modal-dba">Añadir DBA</h3>
                <button class="close-modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre del DBA</label>
                    <textarea id="input-nombre-dba" class="modern-input" rows="3" placeholder="Ej: DBA 1: ..."></textarea>
                </div>
                <div class="form-group">
                    <label for="input-descripcion-dba">Descripción (opcional)</label>
                    <textarea id="input-descripcion-dba" class="modern-input" rows="2" placeholder="Detalle o aclaración del DBA"></textarea>
                </div>
                <div class="form-group">
                    <label for="input-palabras-dba">Palabras clave</label>
                    <input id="input-palabras-dba" class="modern-input" maxlength="1200" placeholder="Ej. ecosistemas, biodiversidad, conservación">
                    <small>Separe los conceptos con comas. Máximo 15.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <button class="btn btn-primary" id="btn-save-dba">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Modal Añadir Eje Temático -->
    <div id="modal-eje" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="title-modal-eje">Añadir Eje Temático</h3>
                <button class="close-modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nombre del Eje Temático</label>
                    <textarea id="input-nombre-eje" class="modern-input" rows="2" placeholder="Ej: La democracia..."></textarea>
                </div>
                <div class="form-group">
                    <label for="input-descripcion-eje">Descripción (opcional)</label>
                    <textarea id="input-descripcion-eje" class="modern-input" rows="2" placeholder="Detalle o aclaración del eje temático"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <button class="btn btn-primary" id="btn-save-eje">Guardar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/assets/js/app.js'); ?>"></script>
    <script src="assets/js/evidencias.js?v=<?php echo filemtime(__DIR__ . '/assets/js/evidencias.js'); ?>"></script>
    <script src="assets/js/exportar.js?v=<?php echo filemtime(__DIR__ . '/assets/js/exportar.js'); ?>"></script>
</body>
</html>
