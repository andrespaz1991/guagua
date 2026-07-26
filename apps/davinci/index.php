<?php
@session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../usuario/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#171b35">
    <title>DaVinci · Cuadernos digitales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=<?php echo filemtime(__DIR__ . '/css/styles.css'); ?>">
</head>
<body>
    <div class="app-shell">
        <aside class="library-panel" aria-label="Biblioteca de cuadernos">
            <div class="brand">
                <span class="brand-mark"><i class="fa-solid fa-feather-pointed" aria-hidden="true"></i></span>
                <div><strong>DaVinci</strong><span>Cuadernos digitales</span></div>
            </div>

            <button id="newBookButton" class="btn btn-primary btn-block" type="button"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nuevo cuaderno</button>

            <div class="library-heading"><span>Mis cuadernos</span><span id="bookCount" class="count-badge">0</span></div>
            <nav id="bookList" class="book-list" aria-label="Lista de cuadernos"></nav>

            <div class="library-footer">
                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
                <span>Sincronizado con tu cuenta.</span>
            </div>
        </aside>

        <main class="workspace">
            <header class="workspace-header">
                <button id="mobileLibraryButton" class="icon-button mobile-only" type="button" aria-label="Abrir cuadernos"><i class="fa-solid fa-book-open"></i></button>
                <div class="document-title">
                    <span id="bookLabel">Cuaderno</span>
                    <h1 id="activeBookTitle">Mi primer cuaderno</h1>
                    <small id="saveStatus" class="save-status"><i class="fa-solid fa-check" aria-hidden="true"></i> Guardado localmente</small>
                </div>
                <div class="header-actions">
                    <button id="saveNowButton" class="btn btn-primary save-now-button" type="button"><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i><span>Guardar</span></button>
                    <button id="renameBookButton" class="text-button" type="button"><i class="fa-solid fa-pen" aria-hidden="true"></i> Renombrar</button>
                    <div class="export-menu-wrap">
                        <button id="exportMenuButton" class="btn btn-dark" type="button" aria-expanded="false"><i class="fa-solid fa-download" aria-hidden="true"></i> Exportar <i class="fa-solid fa-chevron-down tiny-icon" aria-hidden="true"></i></button>
                        <div id="exportMenu" class="export-menu" hidden>
                            <button type="button" data-export="image"><i class="fa-regular fa-image" aria-hidden="true"></i> Página como imagen</button>
                            <button type="button" data-export="page-pdf"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i> Página como PDF</button>
                            <button type="button" data-export="book-pdf"><i class="fa-solid fa-book" aria-hidden="true"></i> Cuaderno completo en PDF</button>
                        </div>
                    </div>
                </div>
            </header>

            <section class="drawing-toolbar" aria-label="Herramientas de dibujo">
                <div class="tool-group">
                    <button id="pencilTool" class="tool-button is-active" type="button" aria-pressed="true"><i class="fa-solid fa-pencil" aria-hidden="true"></i><span>Lápiz</span></button>
                    <button id="eraserTool" class="tool-button" type="button" aria-pressed="false"><i class="fa-solid fa-eraser" aria-hidden="true"></i><span>Borrador</span></button>
                </div>
                <span class="toolbar-divider"></span>
                <div class="tool-group color-tools" aria-label="Color del lápiz">
                    <button class="color-swatch is-selected" type="button" data-color="#1B1F35" style="--swatch:#1B1F35" aria-label="Azul tinta"></button>
                    <button class="color-swatch" type="button" data-color="#4F46E5" style="--swatch:#4F46E5" aria-label="Índigo"></button>
                    <button class="color-swatch" type="button" data-color="#E45A86" style="--swatch:#E45A86" aria-label="Rosa"></button>
                    <button class="color-swatch" type="button" data-color="#D97706" style="--swatch:#D97706" aria-label="Ámbar"></button>
                    <button class="color-swatch" type="button" data-color="#168A72" style="--swatch:#168A72" aria-label="Verde"></button>
                    <label class="custom-color" aria-label="Elegir otro color"><i class="fa-solid fa-palette" aria-hidden="true"></i><input id="colorPicker" type="color" value="#1B1F35"></label>
                </div>
                <span class="toolbar-divider"></span>
                <div id="pencilSizeControl" class="size-control">
                    <i class="fa-solid fa-circle dot-small" aria-hidden="true"></i>
                    <input id="brushSize" type="range" min="1" max="24" value="4" aria-label="Grosor del lápiz">
                    <i class="fa-solid fa-circle dot-large" aria-hidden="true"></i>
                    <output id="brushSizeValue" for="brushSize">4 px</output>
                </div>
                <div id="eraserSizeControl" class="size-control" hidden>
                    <i class="fa-solid fa-eraser dot-small" aria-hidden="true"></i>
                    <input id="eraserSize" type="range" min="6" max="80" value="22" aria-label="Tamaño del borrador">
                    <i class="fa-solid fa-eraser dot-large" aria-hidden="true"></i>
                    <output id="eraserSizeValue" for="eraserSize">22 px</output>
                </div>
                <span class="toolbar-divider"></span>
                <div class="tool-group orientation-tools" aria-label="Orientación de la página">
                    <button id="portraitOrientationButton" class="tool-button is-active" type="button" aria-pressed="true" title="Orientación vertical"><i class="fa-regular fa-rectangle-portrait" aria-hidden="true"></i><span>Vertical</span></button>
                    <button id="landscapeOrientationButton" class="tool-button" type="button" aria-pressed="false" title="Orientación horizontal"><i class="fa-regular fa-rectangle-wide" aria-hidden="true"></i><span>Horizontal</span></button>
                </div>
                <span class="toolbar-divider"></span>
                <div class="zoom-control" aria-label="Zoom del lienzo">
                    <button id="zoomOutButton" class="icon-button" type="button" aria-label="Reducir zoom" title="Reducir zoom"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                    <input id="zoomRange" type="range" min="50" max="200" value="100" step="10" aria-label="Zoom del lienzo">
                    <button id="zoomInButton" class="icon-button" type="button" aria-label="Aumentar zoom" title="Aumentar zoom"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                    <output id="zoomValue" for="zoomRange">100%</output>
                </div>
                <button id="rulerToggleButton" class="tool-button" type="button" aria-pressed="false"><i class="fa-solid fa-ruler" aria-hidden="true"></i><span>Regla</span></button>
                <div id="rulerControls" class="ruler-controls" hidden aria-label="Ajustes de la regla">
                    <i class="fa-solid fa-down-left-and-up-right-to-center" aria-hidden="true"></i>
                    <input id="rulerSize" type="range" min="30" max="100" value="72" aria-label="Tamaño de la regla">
                    <output id="rulerSizeValue" for="rulerSize">72%</output>
                    <button id="rulerRotateButton" class="icon-button" type="button" aria-label="Girar regla 15 grados" title="Girar regla"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
                    <output id="rulerAngleValue">0°</output>
                </div>
                <div class="toolbar-spacer"></div>
                <div class="tool-group">
                    <button id="fullscreenCanvasButton" class="icon-button" type="button" aria-pressed="false" aria-label="Pantalla completa del lienzo" title="Pantalla completa"><i class="fa-solid fa-expand" aria-hidden="true"></i></button>
                    <button id="undoButton" class="icon-button" type="button" disabled aria-label="Deshacer"><i class="fa-solid fa-arrow-rotate-left"></i></button>
                    <button id="redoButton" class="icon-button" type="button" disabled aria-label="Rehacer"><i class="fa-solid fa-arrow-rotate-right"></i></button>
                    <button id="clearButton" class="icon-button danger" type="button" aria-label="Limpiar página"><i class="fa-regular fa-trash-can"></i></button>
                </div>
            </section>

            <div class="studio-layout">
                <aside class="pages-panel" aria-label="Páginas del cuaderno">
                    <div class="pages-panel-header"><div><span>Contenido</span><strong id="pageCount">0 páginas</strong></div><button id="newPageButton" class="icon-button inverse" type="button" aria-label="Añadir página"><i class="fa-solid fa-plus"></i></button></div>
                    <div id="pageList" class="page-list"></div>
                </aside>

                <section id="canvasStage" class="canvas-stage" aria-label="Lienzo de dibujo">
                    <button id="stageFullscreenButton" class="stage-fullscreen-button" type="button" aria-label="Salir de pantalla completa"><i class="fa-solid fa-compress" aria-hidden="true"></i><span>Salir</span></button>
                    <div id="canvasViewport" class="canvas-viewport">
                        <div id="paperWrap" class="paper-wrap">
                            <canvas id="paperCanvas" width="1240" height="1754" aria-hidden="true"></canvas>
                            <canvas id="drawingCanvas" width="1240" height="1754" aria-label="Página para dibujar. Usa el ratón, el lápiz óptico o el dedo."></canvas>
                            <div id="ruler" class="ruler" hidden aria-label="Regla movible">
                                <span>0</span><span>5</span><span>10</span><span>15</span><span>20</span><span>25</span><span>30</span>
                            </div>
                        </div>
                    </div>
                    <p id="canvasHint" class="canvas-hint"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Tu trazo se guarda automáticamente.</p>
                </section>
            </div>
        </main>
    </div>

    <div id="bookModal" class="modal-backdrop" hidden>
        <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="bookModalTitle">
            <button class="modal-close" type="button" data-close-modal="bookModal" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            <span class="modal-icon"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span>
            <h2 id="bookModalTitle">Nuevo cuaderno</h2>
            <p>Elige un nombre y un color para identificar tus apuntes.</p>
            <form id="bookForm">
                <label for="bookNameInput">Nombre del cuaderno</label>
                <input id="bookNameInput" type="text" maxlength="60" placeholder="Ej. Física, ideas, clase de hoy" required autocomplete="off">
                <label class="book-color-field" for="bookColorInput"><span>Color del cuaderno</span><input id="bookColorInput" type="color" value="#4F46E5" aria-label="Color del cuaderno"></label>
                <button class="btn btn-primary btn-block" type="submit">Crear cuaderno</button>
            </form>
        </section>
    </div>

    <div id="pageModal" class="modal-backdrop" hidden>
        <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="pageModalTitle">
            <button class="modal-close" type="button" data-close-modal="pageModal" aria-label="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            <span class="modal-icon"><i class="fa-regular fa-note-sticky" aria-hidden="true"></i></span>
            <h2 id="pageModalTitle">Añadir página</h2>
            <p>Escoge el tipo de hoja para continuar tu cuaderno.</p>
            <form id="pageForm">
                <label for="pageNameInput">Título de la página</label>
                <input id="pageNameInput" type="text" maxlength="60" placeholder="Ej. Ejercicios 1" autocomplete="off">
                <span class="field-label">Tipo de papel</span>
                <div class="template-options">
                    <label class="template-option is-selected"><input type="radio" name="pageTemplate" value="blank" checked><span class="template-preview blank-preview"></span><strong>Blanca</strong><small>Sin guías</small></label>
                    <label class="template-option"><input type="radio" name="pageTemplate" value="grid"><span class="template-preview grid-preview"></span><strong>Cuadriculada</strong><small>Cuadros sutiles</small></label>
                </div>
                <span class="field-label">Orientación</span>
                <div class="orientation-options">
                    <label class="orientation-option is-selected"><input type="radio" name="pageOrientation" value="portrait" checked><i class="fa-regular fa-rectangle-portrait" aria-hidden="true"></i><span>Vertical</span></label>
                    <label class="orientation-option"><input type="radio" name="pageOrientation" value="landscape"><i class="fa-regular fa-rectangle-wide" aria-hidden="true"></i><span>Horizontal</span></label>
                </div>
                <button class="btn btn-primary btn-block" type="submit">Añadir página</button>
            </form>
        </section>
    </div>

    <div id="confirmModal" class="modal-backdrop" hidden>
        <section class="modal-card confirm-card" role="dialog" aria-modal="true" aria-labelledby="confirmModalTitle">
            <span class="modal-icon warning"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            <h2 id="confirmModalTitle">¿Limpiar esta página?</h2>
            <p>Se borrarán todos los trazos de la página actual.</p>
            <div class="modal-actions"><button id="cancelClearButton" class="btn btn-light" type="button">Cancelar</button><button id="confirmClearButton" class="btn btn-danger" type="button">Sí, limpiar</button></div>
        </section>
    </div>

    <div id="toast" class="toast" role="status" aria-live="polite" hidden></div>

    <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
    <script src="js/app.js?v=<?php echo filemtime(__DIR__ . '/js/app.js'); ?>"></script>
</body>
</html>
