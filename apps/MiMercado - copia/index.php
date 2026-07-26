<?php
@session_start();
require_once '../../comun/config.php';
require_once '../../comun/funciones.php';
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../usuario/login.php");
    exit();
}
require_once '../../comun/conexion.php';
global $mysqli;

// Verificar si las tablas existen (redirección a install si no)
$res = $mysqli->query("SHOW TABLES LIKE 'mercado_productos'");
if ($res->num_rows == 0) {
    header("Location: install.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiMercado - Control de Compras</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Estilos de MiMercado -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <!-- Navegación Top -->
    <header class="app-header">
        <div class="header-container">
            <div class="logo">
                <i class="fa fa-shopping-cart"></i>
                <span>MiMercado</span>
            </div>
            <nav class="nav-tabs">
                <button class="tab-btn active" data-target="dashboard"><i class="fa fa-home"></i> <span>Dashboard</span></button>
                <button class="tab-btn" data-target="inventario"><i class="fa fa-cubes"></i> <span>Inventario</span></button>
                <button class="tab-btn" data-target="lista"><i class="fa fa-list-ul"></i> <span>Lista</span></button>
                <button class="tab-btn" data-target="estadisticas"><i class="fa fa-line-chart"></i> <span>Estadísticas</span></button>
                <button class="tab-btn" data-target="alertas"><i class="fa fa-bell"></i> <span>Alertas</span></button>
                <button class="tab-btn" data-target="importar"><i class="fa fa-upload"></i> <span>Importar</span></button>
                <button class="tab-btn" data-target="configuracion"><i class="fa fa-cog"></i> <span>Ajustes</span></button>
                <button class="tab-btn" id="themeToggleBtn" title="Cambiar Tema" onclick="toggleTheme()"><i class="fa fa-sun-o"></i></button>
            </nav>
        </div>
    </header>

    <main class="app-main">
        <!-- ========================================== -->
        <!-- DASHBOARD SECTION -->
        <!-- ========================================== -->
        <section id="dashboard" class="tab-content active">
            <div class="section-header">
                <h2>Resumen del Mes</h2>
                <div class="date-badge" id="currentDateBadge">Mes actual</div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon bg-emerald"><i class="fa fa-money"></i></div>
                    <div class="stat-info">
                        <p>Total Gastado</p>
                        <h3 id="statTotalGastado">$0</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-blue"><i class="fa fa-cubes"></i></div>
                    <div class="stat-info">
                        <p>Productos en Casa</p>
                        <h3 id="statTotalProductos">0</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-amber"><i class="fa fa-exclamation-triangle"></i></div>
                    <div class="stat-info">
                        <p>Por Agotarse</p>
                        <h3 id="statPorAgotar">0</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-purple"><i class="fa fa-pie-chart"></i></div>
                    <div class="stat-info">
                        <p>Presupuesto</p>
                        <h3 id="statPresupuesto">$0</h3>
                    </div>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-card">
                    <h3>Gastos por Quincena</h3>
                    <canvas id="barChartQ"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Gastos por Categoría</h3>
                    <canvas id="doughnutChartCat"></canvas>
                </div>
            </div>
            
            <div class="alert-section">
                <h3>Productos próximos a agotarse</h3>
                <div id="porAgotarList" class="empty-state">
                    <i class="fa fa-check-circle text-emerald"></i>
                    <p>Todo está en orden</p>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- INVENTARIO SECTION -->
        <!-- ========================================== -->
        <section id="inventario" class="tab-content">
            <div class="section-header">
                <h2>Mi Inventario</h2>
                <button class="btn btn-primary" onclick="openProductModal()"><i class="fa fa-plus"></i> Nuevo Producto</button>
            </div>
            
            <div class="filters-bar">
                <div class="search-box" style="flex: 3;">
                    <i class="fa fa-search"></i>
                    <input type="text" id="searchInv" placeholder="Buscar producto...">
                </div>
                <select id="filterCatInv" class="form-select" style="flex: 1;">
                    <option value="">Todas las categorías</option>
                </select>
            </div>

            <div id="productosGrid" class="products-grid">
                <!-- Se llena por JS -->
                <div class="loading-spinner"></div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- LISTA DE COMPRAS SECTION -->
        <!-- ========================================== -->
        <section id="lista" class="tab-content">
            <div class="section-header">
                <h2>Lista de Compras</h2>
                <button class="btn btn-secondary" onclick="limpiarListaComprada()"><i class="fa fa-trash"></i> Limpiar Comprados</button>
            </div>

            <div class="add-list-bar">
                <input type="text" id="nuevoItemNombre" placeholder="¿Qué necesitas comprar?">
                <button class="btn btn-primary" onclick="agregarItemLista()"><i class="fa fa-plus"></i> Agregar</button>
            </div>

            <div class="shopping-list-container">
                <ul id="shoppingList" class="shopping-list">
                    <!-- JS fills this -->
                    <div class="loading-spinner"></div>
                </ul>
            </div>
            
            <div class="list-summary-bar">
                <span>Total Estimado:</span>
                <span id="listaTotalEstimado" class="total-amount">$0</span>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ESTADÍSTICAS SECTION -->
        <!-- ========================================== -->
        <section id="estadisticas" class="tab-content">
            <div class="section-header">
                <h2>Análisis de Gastos</h2>
            </div>
            
            <div class="empty-state">
                <i class="fa fa-cogs"></i>
                <p>Módulo de estadísticas avanzadas en construcción.</p>
                <p>Puedes ver tus gastos básicos en el Dashboard.</p>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ALERTAS SECTION -->
        <!-- ========================================== -->
        <section id="alertas" class="tab-content">
            <div class="section-header">
                <h2>Estado de Productos</h2>
                <div class="semaforo-leyenda" style="display:flex; gap:15px; font-size:14px; color:var(--text-secondary)">
                    <span><i class="fa fa-circle" style="color:#22C55E"></i> Buen estado</span>
                    <span><i class="fa fa-circle" style="color:#F59E0B"></i> Por vencer (≤3 días)</span>
                    <span><i class="fa fa-circle" style="color:#EF4444"></i> Vencido/Agotado</span>
                </div>
            </div>
            
            <div id="alertasGrid" class="products-grid">
                <!-- Se llena por JS -->
                <div class="loading-spinner"></div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- CONFIGURACION SECTION -->
        <!-- ========================================== -->
        <section id="configuracion" class="tab-content">
            <div class="section-header">
                <h2>Configuración</h2>
            </div>
            
            <div class="config-card" style="background: var(--card-bg); padding: 30px; border-radius: 16px; border: 1px solid var(--border-color); max-width: 600px;">
                <h3 style="margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">Notificaciones</h3>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin-bottom: 5px;">Alertas Push</h4>
                        <p style="font-size: 14px; color: var(--text-secondary);">Recibe notificaciones cuando un producto esté por agotarse.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="pushNotifToggle" onchange="togglePushNotifications(this)">
                        <span class="slider round"></span>
                    </label>
                </div>

                <div id="pushStatusMsg" style="font-size: 13px; color: var(--primary); display: none;">
                    <i class="fa fa-check-circle"></i> Notificaciones activadas en este navegador.
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 20px;">
                    <div>
                        <h4 style="margin-bottom: 5px;">Sincronización Automática</h4>
                        <p style="font-size: 14px; color: var(--text-secondary);">Agrega automáticamente productos vencidos o agotados a tu lista de compras.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="syncVencidosToggle" onchange="toggleSyncVencidos(this)">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- IMPORTAR SECTION -->
        <!-- ========================================== -->
        <section id="importar" class="tab-content">
            <div class="section-header">
                <h2>Importar Datos</h2>
            </div>

            <div class="import-card">
                <div class="import-instructions">
                    <i class="fa fa-file-excel-o"></i>
                    <h3>Sube tu archivo CSV</h3>
                    <p>Las columnas requeridas son: <strong>nombre, precio, categoria, duracion_dias</strong></p>
                    <a href="api.php?action=descargar_plantilla" class="btn btn-outline">Descargar Plantilla CSV</a>
                </div>
                
                <div class="upload-area" id="uploadArea">
                    <input type="file" id="csvFile" accept=".csv" class="hidden-input">
                    <label for="csvFile" class="upload-label">
                        <i class="fa fa-cloud-upload"></i>
                        <span>Arrastra el archivo o haz clic aquí</span>
                    </label>
                </div>
                
                <button id="btnImportar" class="btn btn-primary btn-block" style="display:none;" onclick="procesarImportacion()">Importar Productos</button>
            </div>
        </section>
    </main>

    <!-- MODAL PRODUCTO -->
    <div id="productModal" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3 id="modalTitle">Nuevo Producto</h3>
                <button class="close-btn" onclick="closeModal('productModal')"><i class="fa fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" id="prodId" name="id">
                    
                    <div class="form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" id="prodNombre" name="nombre" class="form-input" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Categoría</label>
                            <select id="prodCategoria" name="id_categoria" class="form-select"></select>
                        </div>
                        <div class="form-group">
                            <label>Precio (Aprox)</label>
                            <input type="number" id="prodPrecio" name="precio" class="form-input" step="0.01">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Duración Estimada (Días)</label>
                            <input type="number" id="prodDuracion" name="duracion_dias" class="form-input" value="15">
                        </div>
                        <div class="form-group">
                            <label>Cantidad</label>
                            <input type="number" id="prodCantidad" name="cantidad" class="form-input" value="1">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Foto (Opcional)</label>
                        <input type="file" id="prodFoto" name="foto" class="form-input" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal('productModal')">Cancelar</button>
                <button class="btn btn-primary" onclick="saveProduct()">Guardar Producto</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="js/app.js"></script>
</body>
</html>
