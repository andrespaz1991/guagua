<?php
/**
 * ====================================================================
 * MÓDULO CRUD - GESTIÓN DE ESTÁNDARES Y DBA (Derechos Básicos)
 * ====================================================================
 * Arquitectura: Single File Component (Backend AJAX + Frontend HTML/JS)
 */

session_start();

// ====================================================================
// 1. CONTROLADOR BACKEND (API REST LIGERA)
// ====================================================================
if (isset($_GET['ajax_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    require '../comun/conexion.php'; // Conexión a $mysqli

    $action = $_GET['ajax_action'];
    $response = ['status' => 'error', 'message' => 'Acción no válida', 'data' => []];

    try {
        // Cargar materias para los selects
        if ($action === 'fetch_materias') {
            $result = $mysqli->query("SELECT id_materia, nombre_materia FROM materia_oficial ORDER BY nombre_materia");
            $materias = [];
            while ($row = $result->fetch_assoc()) {
                $materias[] = $row;
            }
            $response = ['status' => 'success', 'data' => $materias];
        }
        // Cargar matriz principal (Estándares y DBAs)
        elseif ($action === 'fetch_data') {
            $grado = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
            $periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 0;
            $materia = isset($_GET['materia']) ? (int)$_GET['materia'] : 0;
            $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

            $sql = "
                SELECT 
                    e.id_estandar, e.nombre_estandar, e.descripcion_estandar, e.grado, e.id_periodo, 
                    m.id_materia, m.nombre_materia,
                    d.id_dba, d.nombre_dba, d.descripcion_dba
                FROM estandar e
                LEFT JOIN materia_oficial m ON e.id_materia_oficial = m.id_materia
                LEFT JOIN dba d ON e.id_estandar = d.id_estandar
                WHERE 1=1
            ";

            $types = "";
            $params = [];

            if ($grado > 0) { $sql .= " AND e.grado = ?"; $types .= "i"; $params[] = $grado; }
            if ($periodo > 0) { $sql .= " AND e.id_periodo = ?"; $types .= "i"; $params[] = $periodo; }
            if ($materia > 0) { $sql .= " AND e.id_materia_oficial = ?"; $types .= "i"; $params[] = $materia; }
            
            if ($busqueda !== '') {
                $sql .= " AND (e.nombre_estandar LIKE ? OR e.descripcion_estandar LIKE ? OR d.nombre_dba LIKE ? OR m.nombre_materia LIKE ?)";
                $search_term = "%{$busqueda}%";
                $types .= "ssss";
                array_push($params, $search_term, $search_term, $search_term, $search_term);
            }

            $sql .= " ORDER BY e.grado ASC, e.id_periodo ASC, m.nombre_materia ASC, e.id_estandar DESC";

            $stmt = $mysqli->prepare($sql);
            if ($types !== "") {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $estandares = [];
            while ($row = $result->fetch_assoc()) {
                $e_id = $row['id_estandar'];
                if (!isset($estandares[$e_id])) {
                    $estandares[$e_id] = [
                        'id_estandar' => $e_id,
                        'nombre_estandar' => $row['nombre_estandar'],
                        'descripcion_estandar' => $row['descripcion_estandar'],
                        'grado' => $row['grado'],
                        'id_periodo' => $row['id_periodo'],
                        'materia' => $row['nombre_materia'] ?? 'Sin Asignar',
                        'dbas' => []
                    ];
                }
                if ($row['id_dba']) {
                    $estandares[$e_id]['dbas'][] = [
                        'id_dba' => $row['id_dba'],
                        'nombre_dba' => $row['nombre_dba'],
                        'descripcion_dba' => $row['descripcion_dba']
                    ];
                }
            }
            $stmt->close();
            $response = ['status' => 'success', 'data' => array_values($estandares)];
        } 
        // Cargar Informe de Faltantes (Matriz cruzada con CTE)
        elseif ($action === 'fetch_informe') {
            $grado = isset($_GET['grado']) ? (int)$_GET['grado'] : 0;
            $periodo = isset($_GET['periodo']) ? (int)$_GET['periodo'] : 0;
            $materia = isset($_GET['materia']) ? (int)$_GET['materia'] : 0;

            // Uso de CTE (Common Table Expressions) soportado en MariaDB 10.4+
            // Construimos la matriz ideal: Grados(6-11) x Periodos(1-4) x Materias
            $sql = "
                WITH Grados AS (
                    SELECT 6 AS grado UNION ALL SELECT 7 UNION ALL SELECT 8 
                    UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
                ),
                Periodos AS (
                    SELECT 1 AS periodo UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
                )
                SELECT 
                    g.grado, p.periodo, m.id_materia, m.nombre_materia,
                    COUNT(DISTINCT e.id_estandar) as num_estandares,
                    COUNT(DISTINCT d.id_dba) as num_dbas
                FROM Grados g
                CROSS JOIN Periodos p
                CROSS JOIN materia_oficial m
                LEFT JOIN estandar e ON e.grado = g.grado AND e.id_periodo = p.periodo AND e.id_materia_oficial = m.id_materia
                LEFT JOIN dba d ON d.id_estandar = e.id_estandar
                WHERE 1=1
            ";

            $types = "";
            $params = [];

            if ($grado > 0) { $sql .= " AND g.grado = ?"; $types .= "i"; $params[] = $grado; }
            if ($periodo > 0) { $sql .= " AND p.periodo = ?"; $types .= "i"; $params[] = $periodo; }
            if ($materia > 0) { $sql .= " AND m.id_materia = ?"; $types .= "i"; $params[] = $materia; }

            $sql .= " GROUP BY g.grado, p.periodo, m.id_materia, m.nombre_materia
                      HAVING num_estandares = 0 OR num_dbas = 0
                      ORDER BY g.grado ASC, p.periodo ASC, m.nombre_materia ASC";

            $stmt = $mysqli->prepare($sql);
            if ($types !== "") {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            $informe = [];
            while ($row = $result->fetch_assoc()) {
                $estado = '';
                if ($row['num_estandares'] == 0) {
                    $estado = 'Falta Estándar y DBA';
                } elseif ($row['num_estandares'] > 0 && $row['num_dbas'] == 0) {
                    $estado = 'Falta DBA (Tiene Estándar)';
                }

                $informe[] = [
                    'grado' => $row['grado'],
                    'periodo' => $row['periodo'],
                    'materia' => $row['nombre_materia'],
                    'estado' => $estado
                ];
            }
            $stmt->close();
            $response = ['status' => 'success', 'data' => $informe];
        }
        elseif ($action === 'delete_estandar') {
            $id = (int)$_POST['id_estandar'];
            $mysqli->query("DELETE FROM dba WHERE id_estandar = $id");
            $stmt = $mysqli->prepare("DELETE FROM estandar WHERE id_estandar = ?");
            $stmt->bind_param("i", $id);
            if($stmt->execute()) {
                $response = ['status' => 'success', 'message' => 'Estándar y DBA eliminados'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        $response['message'] = 'Error del servidor: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matriz Curricular y DBAs</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .loader { border-top-color: #3b82f6; animation: spinner 1.5s linear infinite; }
        @keyframes spinner { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* Pestañas activas */
        .tab-active { border-bottom: 2px solid #2563eb; color: #2563eb; font-weight: 600; }
        .tab-inactive { color: #64748b; font-weight: 500; }
        .tab-inactive:hover { color: #334155; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased p-6">

    <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph ph-books text-blue-600 text-3xl"></i>
                    Matriz Curricular y Auditoría
                </h1>
                <p class="text-sm text-slate-500 mt-1">Gestión de Estándares, DBA y análisis de vacíos</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <button onclick="openModalEstandar()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-medium transition-colors shadow-sm flex items-center gap-2">
                    <i class="ph ph-plus-circle text-xl"></i> Nuevo Estándar
                </button>
            </div>
        </header>

        <!-- Navegación de Pestañas -->
        <div class="border-b border-slate-200">
            <nav class="flex gap-6" aria-label="Tabs">
                <button onclick="switchTab('tab-matriz')" id="btn-tab-matriz" class="tab-active py-3 px-1 flex items-center gap-2 transition-colors">
                    <i class="ph ph-grid-four text-lg"></i> Matriz de Estándares
                </button>
                <button onclick="switchTab('tab-informe')" id="btn-tab-informe" class="tab-inactive py-3 px-1 flex items-center gap-2 transition-colors">
                    <i class="ph ph-warning-circle text-lg"></i> Informe de Faltantes
                </button>
            </nav>
        </div>

        <!-- Filtros Globales -->
        <section class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <div id="searchContainer">
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Buscar</label>
                <div class="relative">
                    <i class="ph ph-magnifying-glass absolute left-3 top-3 text-slate-400 text-lg"></i>
                    <input type="text" id="filterSearch" placeholder="Nombre, descripción, DBA..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Grado</label>
                <select id="filterGrado" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="0">Todos los grados</option>
                    <option value="6">Sexto (6°)</option>
                    <option value="7">Séptimo (7°)</option>
                    <option value="8">Octavo (8°)</option>
                    <option value="9">Noveno (9°)</option>
                    <option value="10">Décimo (10°)</option>
                    <option value="11">Undécimo (11°)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Periodo</label>
                <select id="filterPeriodo" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="0">Todos los periodos</option>
                    <option value="1">Periodo 1</option>
                    <option value="2">Periodo 2</option>
                    <option value="3">Periodo 3</option>
                    <option value="4">Periodo 4</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Materia</label>
                <select id="filterMateria" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="0">Cargando materias...</option>
                </select>
            </div>
        </section>

        <!-- Contenedor Principal -->
        <main class="relative min-h-[400px]">
            <!-- Spinner -->
            <div id="loadingIndicator" class="absolute inset-0 bg-white/80 z-20 flex flex-col justify-center items-center rounded-2xl hidden">
                <div class="loader ease-linear rounded-full border-4 border-slate-200 h-12 w-12 mb-4"></div>
                <p class="text-slate-500 font-medium">Procesando datos...</p>
            </div>

            <!-- PESTAÑA 1: MATRIZ -->
            <div id="tab-matriz" class="block space-y-4">
                <div id="dataContainer" class="space-y-4"></div>
            </div>

            <!-- PESTAÑA 2: INFORME -->
            <div id="tab-informe" class="hidden space-y-4">
                <div class="flex justify-between items-center bg-amber-50 border border-amber-200 p-4 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-info text-amber-600 text-2xl"></i>
                        <p class="text-amber-800 text-sm font-medium">
                            Este reporte cruza la base de datos para detectar qué materias, en qué grado y periodo, 
                            <b class="font-bold">no tienen un Estándar o un DBA asignado.</b>
                        </p>
                    </div>
                    <button onclick="exportToCSV()" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                        <i class="ph ph-download-simple"></i> Exportar CSV
                    </button>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-200">
                                <th class="p-4 font-semibold">Grado</th>
                                <th class="p-4 font-semibold">Periodo</th>
                                <th class="p-4 font-semibold">Materia</th>
                                <th class="p-4 font-semibold">Faltante detectado</th>
                            </tr>
                        </thead>
                        <tbody id="informeContainer" class="text-sm divide-y divide-slate-100">
                            <!-- Inyectado vía JS -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Estado Vacío -->
            <div id="emptyState" class="hidden bg-white p-12 rounded-2xl shadow-sm border border-slate-100 text-center mt-4">
                <i class="ph ph-check-circle text-6xl text-emerald-400 mx-auto mb-4"></i>
                <h3 class="text-lg font-bold text-slate-700" id="emptyTitle">Todo en orden</h3>
                <p class="text-slate-500 mt-2" id="emptyDesc">No se encontraron resultados para los filtros aplicados.</p>
            </div>
        </main>
    </div>

    <script>
        let currentTab = 'tab-matriz';
        let currentReportData = []; // Para exportar a CSV

        document.addEventListener('DOMContentLoaded', () => {
            loadMaterias();
            
            // Listeners de filtros
            const filters = ['filterSearch', 'filterGrado', 'filterPeriodo', 'filterMateria'];
            let debounceTimer;
            filters.forEach(id => {
                document.getElementById(id).addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(fetchActiveData, 400);
                });
            });
        });

        async function loadMaterias() {
            try {
                const res = await fetch('?ajax_action=fetch_materias');
                const result = await res.json();
                if(result.status === 'success') {
                    const select = document.getElementById('filterMateria');
                    select.innerHTML = '<option value="0">Todas las materias</option>';
                    result.data.forEach(m => {
                        select.innerHTML += `<option value="${m.id_materia}">${m.nombre_materia}</option>`;
                    });
                    fetchActiveData(); // Cargar datos iniciales
                }
            } catch (e) {
                console.error("Error cargando materias", e);
            }
        }

        function switchTab(tabId) {
            currentTab = tabId;
            
            // UI de Pestañas
            document.getElementById('btn-tab-matriz').className = tabId === 'tab-matriz' ? 'tab-active py-3 px-1 flex items-center gap-2 transition-colors' : 'tab-inactive py-3 px-1 flex items-center gap-2 transition-colors';
            document.getElementById('btn-tab-informe').className = tabId === 'tab-informe' ? 'tab-active py-3 px-1 flex items-center gap-2 transition-colors' : 'tab-inactive py-3 px-1 flex items-center gap-2 transition-colors';
            
            // Contenedores
            document.getElementById('tab-matriz').classList.toggle('hidden', tabId !== 'tab-matriz');
            document.getElementById('tab-informe').classList.toggle('hidden', tabId !== 'tab-informe');
            
            // Ocultar buscador si estamos en informe
            const searchContainer = document.getElementById('searchContainer');
            searchContainer.style.opacity = tabId === 'tab-informe' ? '0.3' : '1';
            document.getElementById('filterSearch').disabled = (tabId === 'tab-informe');

            fetchActiveData();
        }

        function fetchActiveData() {
            if (currentTab === 'tab-matriz') {
                fetchEstandares();
            } else {
                fetchInforme();
            }
        }

        // Pestaña 1: Matriz
        async function fetchEstandares() {
            toggleLoading(true);
            const search = document.getElementById('filterSearch').value;
            const grado = document.getElementById('filterGrado').value;
            const periodo = document.getElementById('filterPeriodo').value;
            const materia = document.getElementById('filterMateria').value;

            try {
                const response = await fetch(`?ajax_action=fetch_data&busqueda=${encodeURIComponent(search)}&grado=${grado}&periodo=${periodo}&materia=${materia}`);
                const result = await response.json();
                
                toggleLoading(false);
                if (result.status === 'success') {
                    if (result.data.length === 0) {
                        showEmptyState('No se encontraron Estándares', 'Intenta modificar los filtros de búsqueda.');
                    } else {
                        hideEmptyState();
                        renderEstandares(result.data);
                    }
                }
            } catch (error) {
                toggleLoading(false);
                Swal.fire('Error', 'No se pudo cargar la matriz', 'error');
            }
        }

        function renderEstandares(estandares) {
            let html = '';
            estandares.forEach(est => {
                let dbasHtml = '';
                if (est.dbas.length > 0) {
                    dbasHtml = `<div class="mt-4 bg-slate-50 border border-slate-100 rounded-xl p-4">
                                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                                        <i class="ph ph-list-checks text-lg"></i> Derechos Básicos de Aprendizaje (${est.dbas.length})
                                    </h4>
                                    <div class="space-y-3">`;
                    est.dbas.forEach((dba, index) => {
                        dbasHtml += `
                            <div class="flex gap-3 group">
                                <span class="text-slate-400 font-bold text-sm mt-0.5">${index + 1}.</span>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-slate-800">${dba.nombre_dba}</p>
                                    <p class="text-xs text-slate-500 mt-1">${dba.descripcion_dba || ''}</p>
                                </div>
                                <button onclick="editDBA(${dba.id_dba})" class="opacity-0 group-hover:opacity-100 text-slate-400 hover:text-blue-600 transition-all p-1">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </button>
                            </div>
                        `;
                    });
                    dbasHtml += `</div></div>`;
                } else {
                    dbasHtml = `<div class="mt-4 bg-rose-50 border border-rose-100 rounded-xl p-3 text-sm text-rose-600 font-medium flex items-center gap-2">
                                    <i class="ph ph-warning-circle text-lg"></i> Atención: Este estándar no tiene DBA asociados.
                                </div>`;
                }

                html += `
                    <article class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 fade-in relative hover:shadow-md transition-shadow">
                        <div class="flex gap-2 mb-3 items-center flex-wrap">
                            <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-md text-xs font-bold border border-purple-100">Grado ${est.grado}°</span>
                            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-md text-xs font-bold border border-emerald-100">P${est.id_periodo}</span>
                            <span class="px-3 py-1 bg-blue-50 text-blue-700 rounded-md text-xs font-bold border border-blue-100"><i class="ph ph-book-open"></i> ${est.materia}</span>
                        </div>
                        
                        <div class="absolute top-6 right-6 flex gap-2">
                            <button onclick="addDBA(${est.id_estandar})" title="Agregar DBA" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-700 flex items-center justify-center transition-colors"><i class="ph ph-plus-bold"></i></button>
                            <button onclick="editEstandar(${est.id_estandar})" title="Editar Estándar" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-blue-100 hover:text-blue-700 flex items-center justify-center transition-colors"><i class="ph ph-pencil-simple"></i></button>
                            <button onclick="deleteEstandar(${est.id_estandar})" title="Eliminar Estándar" class="w-8 h-8 rounded-full bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700 flex items-center justify-center transition-colors"><i class="ph ph-trash"></i></button>
                        </div>

                        <div class="pr-32">
                            <h3 class="text-lg font-bold text-slate-800 leading-tight mb-2">
                                <span class="text-slate-400 font-normal mr-1">Estándar:</span> ${est.nombre_estandar}
                            </h3>
                            <p class="text-sm text-slate-600">${est.descripcion_estandar}</p>
                        </div>
                        ${dbasHtml}
                    </article>
                `;
            });
            document.getElementById('dataContainer').innerHTML = html;
        }

        // Pestaña 2: Informe
        async function fetchInforme() {
            toggleLoading(true);
            const grado = document.getElementById('filterGrado').value;
            const periodo = document.getElementById('filterPeriodo').value;
            const materia = document.getElementById('filterMateria').value;

            try {
                const response = await fetch(`?ajax_action=fetch_informe&grado=${grado}&periodo=${periodo}&materia=${materia}`);
                const result = await response.json();
                
                toggleLoading(false);
                if (result.status === 'success') {
                    currentReportData = result.data;
                    if (result.data.length === 0) {
                        showEmptyState('¡Excelente!', 'No hay faltantes registrados según los criterios de búsqueda. Todo está completo.');
                        document.getElementById('informeContainer').innerHTML = '';
                    } else {
                        hideEmptyState();
                        renderInforme(result.data);
                    }
                }
            } catch (error) {
                toggleLoading(false);
                Swal.fire('Error', 'No se pudo cargar el informe', 'error');
            }
        }

        function renderInforme(data) {
            let html = '';
            data.forEach(item => {
                const badgeColor = item.estado.includes('Estándar') ? 'bg-rose-100 text-rose-700 border-rose-200' : 'bg-orange-100 text-orange-700 border-orange-200';
                const icon = item.estado.includes('Estándar') ? 'ph-warning-octagon' : 'ph-warning';
                
                html += `
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 font-semibold text-slate-700 text-center w-24">${item.grado}°</td>
                        <td class="p-4 text-slate-600 text-center w-24">P${item.periodo}</td>
                        <td class="p-4 text-slate-800 font-medium">${item.materia}</td>
                        <td class="p-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border ${badgeColor}">
                                <i class="ph ${icon} text-sm"></i> ${item.estado}
                            </span>
                        </td>
                    </tr>
                `;
            });
            document.getElementById('informeContainer').innerHTML = html;
        }

        function exportToCSV() {
            if(currentReportData.length === 0) {
                Swal.fire('Aviso', 'No hay datos para exportar.', 'info');
                return;
            }
            
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Grado,Periodo,Materia,Faltante Detectado\n";
            
            currentReportData.forEach(row => {
                const materiaEscaped = `"${row.materia.replace(/"/g, '""')}"`;
                csvContent += `${row.grado},${row.periodo},${materiaEscaped},${row.estado}\n`;
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "informe_faltantes_curriculares.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Utilidades UI
        function toggleLoading(show) {
            const loader = document.getElementById('loadingIndicator');
            show ? loader.classList.remove('hidden') : loader.classList.add('hidden');
        }

        function showEmptyState(title, desc) {
            document.getElementById('dataContainer').innerHTML = '';
            document.getElementById('emptyTitle').textContent = title;
            document.getElementById('emptyDesc').textContent = desc;
            document.getElementById('emptyState').classList.remove('hidden');
        }

        function hideEmptyState() {
            document.getElementById('emptyState').classList.add('hidden');
        }

        // Acciones Globales
        window.deleteEstandar = function(id) {
            Swal.fire({
                title: '¿Eliminar Estándar?',
                text: "Se eliminarán también todos los DBA asociados. Esta acción es irreversible.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id_estandar', id);
                    fetch('?ajax_action=delete_estandar', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(res => {
                        if(res.status === 'success') {
                            Swal.fire('¡Eliminado!', res.message, 'success');
                            fetchActiveData();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        };

        window.openModalEstandar = () => { Swal.fire('En desarrollo', 'Modal para crear Estándar', 'info'); };
        window.editEstandar = (id) => { Swal.fire('En desarrollo', `Editar estándar ${id}`, 'info'); };
        window.addDBA = (id) => { Swal.fire('En desarrollo', `Añadir DBA al estándar ${id}`, 'info'); };
        window.editDBA = (id) => { Swal.fire('En desarrollo', `Editar DBA ${id}`, 'info'); };
    </script>
</body>
</html>