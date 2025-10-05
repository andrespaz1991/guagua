<?php
/**
 * =================================================================
 * MÓDULO DE CALENDARIO DE PLANEACIÓN - VERSIÓN CON PAGINACIÓN NUMÉRICA
 * =================================================================
 *
 * Mejoras realizadas (v3):
 * 1.  Paginación Numérica Asíncrona:
 * -   Se reemplaza el botón "Cargar más" por un control de paginación numérico (Ej: < 1 2 3 >).
 * -   El backend ahora calcula y devuelve el número total de registros que coinciden con los filtros.
 * -   El frontend renderiza dinámicamente los controles de paginación basados en el total de resultados.
 *
 * 2.  Optimización de Consultas:
 * -   El endpoint AJAX ahora realiza dos consultas: una para contar el total de registros y otra para obtener la página actual, asegurando que la paginación sea precisa.
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';

// =================================================================
// 2. ENDPOINT AJAX PARA BÚSQUEDA Y PAGINACIÓN
// =================================================================
if (isset($_GET['action']) && $_GET['action'] == 'buscar_planeaciones') {
    header('Content-Type: application/json');

    $recordsPerPage = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $recordsPerPage;

    // Construcción de la consulta con filtros
    $baseSql = "FROM planeador_vallesol p
                JOIN asignacion a ON a.id_asignacion = p.materia
                JOIN materia_oficial m ON m.id_materia = a.id_asignatura";
    
    $whereConditions = [];
    $params = [];
    $types = '';

    if (!empty($_GET['materia'])) {
        $whereConditions[] = "m.nombre_materia = ?";
        $params[] = $_GET['materia'];
        $types .= 's';
    }
    if (!empty($_GET['grado'])) {
        $whereConditions[] = "p.grado = ?";
        $params[] = $_GET['grado'];
        $types .= 's';
    }
    if (!empty($_GET['startDate'])) {
        $whereConditions[] = "p.fecha_fin >= ?";
        $params[] = $_GET['startDate'];
        $types .= 's';
    }
    if (!empty($_GET['endDate'])) {
        $whereConditions[] = "p.fecha_inicio <= ?";
        $params[] = $_GET['endDate'];
        $types .= 's';
    }

    $whereClause = "";
    if (!empty($whereConditions)) {
        $whereClause = " WHERE " . implode(" AND ", $whereConditions);
    }
    
    // Consulta para contar el total de registros
    $totalSql = "SELECT COUNT(DISTINCT p.id_plan) as total " . $baseSql . $whereClause;
    $stmtTotal = $mysqli->prepare($totalSql);
    if ($stmtTotal) {
        if (!empty($params)) {
            $stmtTotal->bind_param($types, ...$params);
        }
        $stmtTotal->execute();
        $totalResult = $stmtTotal->get_result()->fetch_assoc();
        $totalRecords = $totalResult['total'];
    } else {
        $totalRecords = 0;
    }

    // Consulta para obtener los datos de la página actual
    $dataSql = "SELECT DISTINCT p.id_plan, m.nombre_materia AS title, p.grado, p.fecha_inicio AS start, p.fecha_fin AS end " . $baseSql . $whereClause . " ORDER BY p.fecha_inicio DESC LIMIT ? OFFSET ?";
    
    $dataParams = $params;
    $dataParams[] = $recordsPerPage;
    $dataParams[] = $offset;
    $dataTypes = $types . 'ii';
    
    $stmtData = $mysqli->prepare($dataSql);
    if ($stmtData) {
        $stmtData->bind_param($dataTypes, ...$dataParams);
        $stmtData->execute();
        $resultado = $stmtData->get_result();
        $planes = $resultado->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['data' => $planes, 'total' => $totalRecords]);
    } else {
        echo json_encode(['error' => 'Error en la consulta: ' . $mysqli->error, 'data' => [], 'total' => 0]);
    }
    exit;
}


// =================================================================
// 3. LÓGICA PARA LA CARGA INICIAL DE LA PÁGINA
// =================================================================
$eventos_calendario = [];
$materias_unicas = [];
$grados_unicos = [];

$sql_inicial = "SELECT
            p.id_plan,
            p.grado,
            m.nombre_materia,
            p.fecha_inicio AS fecha_iniciop,
            p.fecha_fin AS fecha_finp,
            h.hora_inicio AS horario_hora_inicio,
            h.hora_fin AS horario_hora_fin,
            h.dia,
            p.objetivo AS texto_planeacion
        FROM planeador_vallesol AS p
        JOIN asignacion AS a ON a.id_asignacion = p.materia
        JOIN materia_oficial AS m ON m.id_materia = a.id_asignatura
        JOIN horario AS h ON h.id_asignacion = a.id_asignacion
        ORDER BY p.fecha_inicio DESC, m.nombre_materia";

$resultado = $mysqli->query($sql_inicial);

if ($resultado) {
    $diasSemana = [
        'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4,
        'viernes' => 5, 'sabado' => 6, 'domingo' => 0
    ];
    
    while ($fila = $resultado->fetch_assoc()) {
        if (!in_array($fila['grado'], $grados_unicos)) $grados_unicos[] = $fila['grado'];
        if (!in_array($fila['nombre_materia'], $materias_unicas)) $materias_unicas[] = $fila['nombre_materia'];
        
        try {
            $fecha_inicio = new DateTime($fila['fecha_iniciop']);
            $fecha_fin = new DateTime($fila['fecha_finp']);
            $dia_semana_str = strtolower(trim($fila['dia']));

            if (isset($diasSemana[$dia_semana_str])) {
                $numero_dia = $diasSemana[$dia_semana_str];
                $fecha_actual = clone $fecha_inicio;
                $nombre_materia_limpio = strtolower(preg_replace('/[^a-z0-9]/i', '', $fila['nombre_materia']));

                while ($fecha_actual <= $fecha_fin) {
                    if ((int)$fecha_actual->format('w') === $numero_dia) {
                        $eventos_calendario[] = [
                            'title' => $fila['nombre_materia'],
                            'start' => $fecha_actual->format('Y-m-d') . 'T' . $fila['horario_hora_inicio'],
                            'end' => $fecha_actual->format('Y-m-d') . 'T' . $fila['horario_hora_fin'],
                            'description' => 'Grado: ' . htmlspecialchars($fila['grado']) . ' - ' . htmlspecialchars($fila['texto_planeacion']),
                            'id_plan' => $fila['id_plan'],
                            'className' => 'evento-' . $nombre_materia_limpio
                        ];
                    }
                    $fecha_actual->modify('+1 day');
                }
            }
        } catch (Exception $e) {
            error_log("Error de fecha para el plan ID " . $fila['id_plan'] . ": " . $e->getMessage());
        }
    }
    sort($materias_unicas);
    sort($grados_unicos);
} else {
    error_log("Error en la consulta del calendario: " . $mysqli->error);
}

// 4. VISTA DEL CALENDARIO (HTML, CSS, JS)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Planeación</title>
    <style>
        :root {
            --primary-color: #2c5282;
            --accent-color: #ed8936;
            --background-color: #f7fafc;
            --card-background: #ffffff;
            --text-color: #2d3748;
            --light-gray: #e2e8f0;
            --today-bg: #fffde7;
            --event-text-color: #ffffff;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .calendar-container {
            background: var(--card-background);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }
        .calendar-header .title-group {
            text-align: right;
        }
        .calendar-header .title-group h1 {
            margin: 0;
            font-size: 1.8em;
            color: var(--primary-color);
        }
         .calendar-header .title-group p {
            margin: 4px 0 0;
            color: #718096;
        }
        .calendar-nav button {
            background: none;
            border: 1px solid var(--light-gray);
            padding: 8px 14px;
            margin: 0 4px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }
        .calendar-nav button:hover {
            background-color: #edf2f7;
            border-color: #cbd5e0;
        }
        .calendar-nav button#today-btn {
            background-color: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }
        .calendar-nav button#today-btn:hover {
            background-color: #dd6b20;
        }
        #month-year-display {
            font-size: 1.5em;
            font-weight: 600;
            color: var(--primary-color);
            text-transform: capitalize;
            min-width: 200px;
            text-align: right;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background-color: var(--light-gray);
            border: 1px solid var(--light-gray);
        }

        .day-header, .day {
            background-color: var(--card-background);
            padding: 8px;
        }
        
        .day-header {
            text-align: center;
            font-weight: 600;
            color: #718096;
            padding: 12px 5px;
            font-size: 0.9em;
        }

        .day {
            min-height: 120px;
            position: relative;
        }
        
        .day-number {
            font-size: 0.85em;
            font-weight: 600;
            color: #4a5568;
        }
        
        .day.outside-month .day-number { color: #a0aec0; }

        .day.today { background-color: var(--today-bg); }
        .day.today .day-number {
            color: var(--accent-color);
            background-color: #feebc8;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .events { margin-top: 5px; }

        .event {
            font-size: 0.75em;
            padding: 3px 6px;
            border-radius: 4px;
            margin-bottom: 4px;
            color: var(--event-text-color);
            cursor: pointer;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Colores para materias */
        .evento-matematicas { background-color: #4299e1; }
        .evento-cienciassociales { background-color: #f56565; }
        .evento-educacionfisica { background-color: #48bb78; }
        .evento-emprendimiento { background-color: #dd6b20; }
        .evento-tecnologiaeinformatica { background-color: #718096; }
        .evento-urbanidad { background-color: #d69e2e; }
        .evento-fisica { background-color: #805ad5; }
        .evento-economiapolitica { background-color: #319795; }
        .evento-geometria { background-color: #d53f8c; }
        .event:not([class*="evento-"]) { background-color: #a0aec0; }

        #calendar-legend {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .legend-item { display: flex; align-items: center; font-size: 0.85em; }
        .legend-color-box { width: 15px; height: 15px; border-radius: 4px; margin-right: 8px; }

        /* Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal-content {
            background: white; padding: 30px; border-radius: 12px;
            width: 90%; max-width: 500px; position: relative;
        }
        .modal-close-btn {
            position: absolute; top: 15px; right: 15px; background: none; border: none;
            font-size: 1.8em; cursor: pointer; color: #a0aec0;
        }
        #modal-title { margin-top: 0; color: var(--primary-color); }
        #modal-description { font-size: 0.95em; line-height: 1.6; color: #4a5568; }
        .modal-button {
            display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: var(--accent-color);
            color: white; text-decoration: none; border-radius: 8px;
        }
        
        /* Search Section Styles */
        .search-container {
            background: var(--card-background);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .search-container h2 {
            margin-top: 0;
            color: var(--primary-color);
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        .filter-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        .filter-group select, .filter-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            background-color: #fdfdfd;
        }
        #search-results { list-style: none; padding: 0; min-height: 100px; }
        .result-item {
            display: block;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: #f7fafc;
            border: 1px solid var(--light-gray);
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s;
        }
        .result-item:hover {
            border-color: var(--accent-color);
            background-color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .result-item strong { color: var(--primary-color); }
        .result-item span { color: #718096; font-size: 0.9em; }
        #no-results-message {
            text-align: center;
            padding: 40px;
            color: #718096;
        }

        /* --- NUEVO --- Estilos de paginación numérica */
        #pagination-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
        }
        .page-link {
            padding: 8px 14px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            background-color: #fff;
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        .page-link:hover {
            background-color: #edf2f7;
            border-color: #cbd5e0;
        }
        .page-link.active {
            background-color: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
            cursor: default;
        }
        .page-link.disabled {
            color: #a0aec0;
            cursor: not-allowed;
            background-color: #f7fafc;
        }
        .page-link.disabled:hover {
            border-color: var(--light-gray);
            background-color: #f7fafc;
        }
        .page-info {
            padding: 8px 12px;
            font-size: 0.9em;
            color: #718096;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="calendar-container">
        <!-- ... (código del calendario sin cambios) ... -->
        <div class="calendar-header">
            <div class="calendar-nav">
                <button id="prev-month-btn">&lt; Anterior</button>
                <button id="today-btn">Hoy</button>
                <button id="next-month-btn">Siguiente &gt;</button>
            </div>
            <div class="title-group">
                <h1 id="month-year-display"></h1>
            </div>
        </div>
        <div class="calendar-grid day-headers">
            <div class="day-header">Domingo</div>
            <div class="day-header">Lunes</div>
            <div class="day-header">Martes</div>
            <div class="day-header">Miércoles</div>
            <div class="day-header">Jueves</div>
            <div class="day-header">Viernes</div>
            <div class="day-header">Sábado</div>
        </div>
        <div id="calendar-body" class="calendar-grid"></div>
        <div id="calendar-legend"></div>
    </div>

    <!-- Search Section -->
    <div class="search-container">
        <h2>Búsqueda de Planeaciones</h2>
        <div class="filters">
            <div class="filter-group">
                <label for="materia-filter">Materia</label>
                <select id="materia-filter"><option value="">Todas</option></select>
            </div>
            <div class="filter-group">
                <label for="grado-filter">Grado</label>
                <select id="grado-filter"><option value="">Todos</option></select>
            </div>
            <div class="filter-group">
                <label for="start-date-filter">Desde</label>
                <input type="date" id="start-date-filter">
            </div>
            <div class="filter-group">
                <label for="end-date-filter">Hasta</label>
                <input type="date" id="end-date-filter">
            </div>
        </div>
        <ul id="search-results"></ul>
        <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">Cargando...</div>
        <p id="no-results-message" style="display: none;">No se encontraron planeaciones con los filtros seleccionados.</p>
        
        <!-- --- MODIFICADO --- Contenedor para los controles de paginación -->
        <div id="pagination-controls"></div>
    </div>
</div>

<!-- Modal Structure -->
<div id="event-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2 id="modal-title"></h2>
        <p id="modal-description"></p>
        <a id="modal-link" href="#" target="_blank" class="modal-button">Ver Planeación Completa</a>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- CALENDAR VARIABLES ---
        const calendarBody = document.getElementById('calendar-body');
        const monthYearDisplay = document.getElementById('month-year-display');
        const prevMonthBtn = document.getElementById('prev-month-btn');
        const nextMonthBtn = document.getElementById('next-month-btn');
        const todayBtn = document.getElementById('today-btn');
        const legendContainer = document.getElementById('calendar-legend');

        // --- MODAL VARIABLES ---
        const modal = document.getElementById('event-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalDescription = document.getElementById('modal-description');
        const modalLink = document.getElementById('modal-link');
        const modalCloseBtn = modal.querySelector('.modal-close-btn');

        // --- SEARCH & PAGINATION VARIABLES ---
        const materiaFilter = document.getElementById('materia-filter');
        const gradoFilter = document.getElementById('grado-filter');
        const startDateFilter = document.getElementById('start-date-filter');
        const endDateFilter = document.getElementById('end-date-filter');
        const searchResults = document.getElementById('search-results');
        const noResultsMessage = document.getElementById('no-results-message');
        const paginationControls = document.getElementById('pagination-controls');
        const loadingSpinner = document.getElementById('loading-spinner');
        
        const recordsPerPage = 10;
        let isLoading = false;

        // --- DATA FROM PHP ---
        const eventos_calendario = <?php echo json_encode($eventos_calendario); ?>;
        const materias_filtro = <?php echo json_encode($materias_unicas); ?>;
        const grados_filtro = <?php echo json_encode($grados_unicos); ?>;

        let currentDate = new Date();
        
        function renderCalendar() {
            // ... (código de renderCalendar sin cambios) ...
            calendarBody.innerHTML = '';
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            monthYearDisplay.textContent = new Date(year, month).toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

            const firstDayOfMonth = new Date(year, month, 1);
            const lastDayOfMonth = new Date(year, month + 1, 0);
            const startDayOfWeek = firstDayOfMonth.getDay();

            for (let i = 0; i < startDayOfWeek; i++) {
                calendarBody.insertAdjacentHTML('beforeend', '<div class="day outside-month"></div>');
            }

            for (let day = 1; day <= lastDayOfMonth.getDate(); day++) {
                const dayDiv = document.createElement('div');
                dayDiv.classList.add('day');
                
                const dayNumber = document.createElement('div');
                dayNumber.classList.add('day-number');
                dayNumber.textContent = day;
                dayDiv.appendChild(dayNumber);

                const today = new Date();
                if (day === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
                    dayDiv.classList.add('today');
                }
                
                const eventsDiv = document.createElement('div');
                eventsDiv.classList.add('events');
                
                const fechaActualStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const eventosDelDia = eventos_calendario.filter(e => e.start.startsWith(fechaActualStr));
                
                eventosDelDia.forEach(evento => {
                    const eventDiv = document.createElement('div');
                    eventDiv.classList.add('event', evento.className);
                    eventDiv.textContent = evento.title;
                    eventDiv.title = evento.description;
                    eventDiv.addEventListener('click', () => showModal(evento));
                    eventsDiv.appendChild(eventDiv);
                });

                dayDiv.appendChild(eventsDiv);
                calendarBody.appendChild(dayDiv);
            }
             
             const totalDaysInGrid = startDayOfWeek + lastDayOfMonth.getDate();
             const remainingDays = (7 - (totalDaysInGrid % 7)) % 7;
             for (let i = 0; i < remainingDays; i++) {
                calendarBody.insertAdjacentHTML('beforeend', '<div class="day outside-month"></div>');
             }
        }

        function renderLegend() {
            // ... (código de renderLegend sin cambios) ...
             const materiasEnUso = {};
            eventos_calendario.forEach(e => {
                if (!materiasEnUso[e.className]) {
                    materiasEnUso[e.className] = e.title;
                }
            });

            legendContainer.innerHTML = '';
            for (const className in materiasEnUso) {
                legendContainer.innerHTML += `
                    <div class="legend-item">
                        <div class="legend-color-box ${className}"></div>
                        <span>${materiasEnUso[className]}</span>
                    </div>
                `;
            }
        }
        
        function populateFilters() {
            materias_filtro.forEach(materia => {
                materiaFilter.innerHTML += `<option value="${materia}">${materia}</option>`;
            });
            grados_filtro.forEach(grado => {
                gradoFilter.innerHTML += `<option value="${grado}">${grado}</option>`;
            });
        }

        // --- FUNCIÓN MODIFICADA --- Para obtener resultados del servidor por página
        async function fetchResults(page = 1) {
            if (isLoading) return;
            isLoading = true;
            
            searchResults.innerHTML = '';
            loadingSpinner.style.display = 'block';
            noResultsMessage.style.display = 'none';
            paginationControls.innerHTML = '';

            const params = new URLSearchParams({
                action: 'buscar_planeaciones',
                page: page,
                materia: materiaFilter.value,
                grado: gradoFilter.value,
                startDate: startDateFilter.value,
                endDate: endDateFilter.value
            });

            try {
                const response = await fetch(`?${params.toString()}`);
                const { data: planes, total } = await response.json();

                if (planes.length > 0) {
                    planes.forEach(plan => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <a href="planeador.php?pdf=1&idplan=${plan.id_plan}" target="_blank" class="result-item">
                                <strong>${plan.title}</strong> (Grado: ${plan.grado})
                                <br>
                                <span>${plan.start} al ${plan.end}</span>
                            </a>`;
                        searchResults.appendChild(li);
                    });
                    renderPaginationControls(total, page);
                } else {
                    noResultsMessage.style.display = 'block';
                }
            } catch (error) {
                console.error('Error al buscar planeaciones:', error);
                noResultsMessage.textContent = 'Ocurrió un error al cargar los resultados.';
                noResultsMessage.style.display = 'block';
            } finally {
                isLoading = false;
                loadingSpinner.style.display = 'none';
            }
        }

        // --- NUEVA FUNCIÓN --- Para renderizar los controles de paginación
        function renderPaginationControls(total, currentPage) {
            paginationControls.innerHTML = '';
            const totalPages = Math.ceil(total / recordsPerPage);

            if (totalPages <= 1) return;

            let html = '';

            // Botón "Anterior"
            html += `<a href="#" class="page-link ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">&laquo;</a>`;

            // Lógica para mostrar números de página
            for (let i = 1; i <= totalPages; i++) {
                html += `<a href="#" class="page-link ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`;
            }

            // Botón "Siguiente"
            html += `<a href="#" class="page-link ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}">&raquo;</a>`;
            
            paginationControls.innerHTML = html;
        }

        function showModal(evento) {
            modalTitle.textContent = evento.title;
            modalDescription.textContent = evento.description;
            modalLink.href = `planeador.php?pdf=1&idplan=${evento.id_plan}`;
            modal.style.display = 'flex';
        }

        function hideModal() {
            modal.style.display = 'none';
        }

        // --- INITIALIZE ---
        renderCalendar();
        renderLegend();
        populateFilters();
        fetchResults(1); // Carga inicial de la página 1

        // --- EVENT LISTENERS ---
        prevMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
        nextMonthBtn.addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });
        todayBtn.addEventListener('click', () => { currentDate = new Date(); renderCalendar(); });
        modalCloseBtn.addEventListener('click', hideModal);
        modal.addEventListener('click', e => (e.target === modal) && hideModal());
        document.addEventListener('keydown', e => (e.key === 'Escape') && hideModal());
        
        [materiaFilter, gradoFilter, startDateFilter, endDateFilter].forEach(el => {
            el.addEventListener('change', () => fetchResults(1));
        });

        // --- NUEVO --- Listener para los controles de paginación (delegación de eventos)
        paginationControls.addEventListener('click', e => {
            e.preventDefault();
            const target = e.target.closest('.page-link');
            if (target && !target.classList.contains('disabled') && !target.classList.contains('active')) {
                const page = parseInt(target.dataset.page, 10);
                fetchResults(page);
            }
        });
    });
</script>

</body>
</html>
<?php
// $contenido = ob_get_clean();
// require("../comun/plantilla.php");
?>

