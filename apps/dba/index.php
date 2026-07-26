<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malla Curricular - DB Manager</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar / Navigation -->
        <aside class="sidebar">
            <div class="brand">
                <i class="fa-solid fa-database"></i>
                <h2>Curriculum DB</h2>
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
                 <p class="help-text"><i class="fa-solid fa-info-circle"></i> Los cambios se guardan directamente en la base de datos.</p>
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

            <div class="content-area" id="periods-container">
                <div class="empty-state">
                    <i class="fa-solid fa-layer-group"></i>
                    <p>No hay datos seleccionados</p>
                </div>
            </div>
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
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary close-modal">Cancelar</button>
                <button class="btn btn-primary" id="btn-save-eje">Guardar</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
