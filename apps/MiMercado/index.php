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

// Migración transparente para instalaciones anteriores que solo guardaban la fecha.
$col_fecha_compra = $mysqli->query("SHOW COLUMNS FROM mercado_compras LIKE 'fecha_compra'");
if ($col_fecha_compra && ($fecha_compra_info = $col_fecha_compra->fetch_assoc()) && stripos($fecha_compra_info['Type'], 'datetime') === false) {
    $mysqli->query("ALTER TABLE mercado_compras MODIFY fecha_compra DATETIME NOT NULL");
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
    <link rel="stylesheet" href="css/styles.css?v=<?php echo filemtime(__DIR__ . '/css/styles.css'); ?>">
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
                <div class="section-actions">
                    <button class="btn btn-secondary" onclick="eliminarDuplicadosInventario()"><i class="fa fa-clone"></i> Quitar duplicados</button>
                    <button class="btn btn-primary" onclick="openProductModal()"><i class="fa fa-plus"></i> Nuevo Producto</button>
                </div>
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
                <div class="section-actions">
                    <button id="btnSeleccionarTodos" class="btn btn-secondary" onclick="toggleSeleccionarTodos()"><i class="fa fa-check-square-o"></i> Seleccionar todos</button>
                    <button id="btnComprarSeleccionados" class="btn btn-primary" onclick="comprarSeleccionados()" disabled><i class="fa fa-shopping-cart"></i> Comprar seleccionados</button>
                </div>
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
                <span>Total seleccionado:</span>
                <span id="listaTotalEstimado" class="total-amount">$0</span>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- ESTADÍSTICAS SECTION -->
        <!-- ========================================== -->
        <section id="estadisticas" class="tab-content">
            <div class="stats-hero">
                <div>
                    <span class="eyebrow"><i class="fa fa-line-chart"></i> Inteligencia de compras</span>
                    <h2>Análisis financiero</h2>
                    <p>Entiende cómo, cuándo y en qué estás invirtiendo tu presupuesto del hogar.</p>
                </div>
                <div class="stats-controls" aria-label="Periodo de análisis">
                    <label>
                        <span>Mes</span>
                        <select id="statsMonthSelect" class="form-select" onchange="loadEstadisticas()"></select>
                    </label>
                    <label>
                        <span>Año</span>
                        <select id="statsYearSelect" class="form-select" onchange="loadEstadisticas()"></select>
                    </label>
                    <button class="btn btn-secondary stats-refresh" type="button" onclick="loadEstadisticas()" title="Actualizar estadísticas"><i class="fa fa-refresh"></i><span>Actualizar</span></button>
                </div>
            </div>

            <div id="statsLoading" class="stats-loading" aria-live="polite">
                <div class="loading-spinner"></div>
                <span>Preparando tus estadísticas...</span>
            </div>

            <div id="statsEmpty" class="stats-empty" hidden>
                <div class="stats-empty-icon"><i class="fa fa-bar-chart"></i></div>
                <h3>Aún no hay compras en este periodo</h3>
                <p>Cuando registres una compra, aquí verás su distribución, tendencia y productos principales.</p>
            </div>

            <div id="statsContent" class="stats-content" hidden>
                <div class="stats-kpi-grid">
                    <article class="stats-kpi-card primary">
                        <span class="stats-kpi-icon"><i class="fa fa-wallet"></i></span>
                        <div><span class="stats-kpi-label">Gasto del periodo</span><strong id="statsTotalGastado">$0</strong><small id="statsVariacion">Sin comparación anterior</small></div>
                    </article>
                    <article class="stats-kpi-card">
                        <span class="stats-kpi-icon blue"><i class="fa fa-cubes"></i></span>
                        <div><span class="stats-kpi-label">Unidades compradas</span><strong id="statsUnidadesCompradas">0</strong><small id="statsRegistros">0 registros de compra</small></div>
                    </article>
                    <article class="stats-kpi-card">
                        <span class="stats-kpi-icon purple"><i class="fa fa-receipt"></i></span>
                        <div><span class="stats-kpi-label">Promedio por registro</span><strong id="statsPromedioCompra">$0</strong><small>Valor promedio por producto registrado</small></div>
                    </article>
                    <article class="stats-kpi-card">
                        <span class="stats-kpi-icon amber"><i class="fa fa-pie-chart"></i></span>
                        <div><span class="stats-kpi-label">Categoría principal</span><strong id="statsCategoriaPrincipal">Sin datos</strong><small id="statsCategoriaPrincipalValor">Aún no hay gastos</small></div>
                    </article>
                </div>

                <article class="budget-overview">
                    <div class="budget-overview-heading">
                        <div><span class="eyebrow">Presupuesto mensual</span><h3 id="statsBudgetTitle">Sin presupuesto configurado</h3></div>
                        <strong id="statsBudgetAvailable">$0</strong>
                    </div>
                    <div class="budget-progress-track" role="progressbar" aria-label="Uso del presupuesto" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span id="statsBudgetProgress"></span></div>
                    <div class="budget-overview-footer"><span id="statsBudgetDetail">Configura un presupuesto en el panel principal para comparar tus gastos.</span><strong id="statsBudgetPercent">—</strong></div>
                </article>

                <div class="stats-modern-grid">
                    <article class="stats-panel stats-panel-wide">
                        <div class="stats-panel-header"><div><h3>Tendencia de gasto</h3><p>Últimos seis meses hasta el periodo seleccionado.</p></div><span id="statsTrendPeriod" class="stats-period-chip"></span></div>
                        <div class="stats-chart-area"><canvas id="chartGastosMensuales" aria-label="Tendencia mensual de gastos"></canvas></div>
                    </article>

                    <article class="stats-panel">
                        <div class="stats-panel-header"><div><h3>Distribución por categoría</h3><p>Participación sobre el gasto total.</p></div></div>
                        <div class="stats-category-layout"><div class="stats-doughnut-area"><canvas id="chartCategoriasStats" aria-label="Distribución de gastos por categoría"></canvas></div><ul id="statsCategoryLegend" class="stats-category-legend"></ul></div>
                    </article>

                    <article class="stats-panel">
                        <div class="stats-panel-header"><div><h3>Ritmo de compra</h3><p>Gasto acumulado por cada quincena.</p></div></div>
                        <div class="stats-chart-area compact"><canvas id="chartQuincenalStats" aria-label="Comparación de gasto por quincena"></canvas></div>
                    </article>

                    <article class="stats-panel">
                        <div class="stats-panel-header"><div><h3>Más comprados</h3><p>Productos con mayor número de unidades.</p></div></div>
                        <ol id="listTopComprados" class="stats-ranking-list"></ol>
                    </article>

                    <article class="stats-panel">
                        <div class="stats-panel-header"><div><h3>Mayor inversión</h3><p>Productos que más impactaron el gasto.</p></div></div>
                        <ol id="listTopCostosos" class="stats-ranking-list"></ol>
                    </article>
                </div>
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

                <h3 style="margin-bottom: 20px; font-size: 18px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-top: 25px;">Sincronización Automática</h3>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin-bottom: 5px;"><i class="fa fa-cube" style="color:#3B82F6"></i> Sincronizar por Cantidad (En Cero)</h4>
                        <p style="font-size: 14px; color: var(--text-secondary);">Cuando un producto llega a 0 unidades, se agrega automáticamente a la lista de compras.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="syncCeroToggle" onchange="toggleSyncCero(this)">
                        <span class="slider round"></span>
                    </label>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h4 style="margin-bottom: 5px;"><i class="fa fa-clock-o" style="color:#F59E0B"></i> Sincronizar por Tiempo (Vencidos)</h4>
                        <p style="font-size: 14px; color: var(--text-secondary);">Cuando el tiempo total de duración de un producto se agota, se agrega automáticamente a la lista.</p>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="syncTiempoToggle" onchange="toggleSyncTiempo(this)">
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
    <script src="js/app.js?v=<?php echo filemtime(__DIR__ . '/js/app.js'); ?>"></script>
</body>
</html>
