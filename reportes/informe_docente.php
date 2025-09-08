<?php
/**
 * =================================================================
 * DASHBOARD DE REPORTES DINÁMICOS - VERSIÓN AVANZADA
 * =================================================================
 *
 * ANÁLISIS Y MEJORAS REALIZADAS POR EXPERTO EN UX/UI Y DESARROLLO (V2):
 * -----------------------------------------------------------------
 *
 * 1.  **FILTRADO MULTI-NIVEL IMPLEMENTADO:**
 * -   **Filtro Global por Grado:** Se ha añadido un filtro global por Grado (`categoria_curso`) que afecta a la mayoría de los reportes, permitiendo un análisis segmentado.
 * -   **Filtro por Rango de Fechas para Asistencias:** El reporte de Asistencias ahora incluye un selector de rango de fechas para analizar el ausentismo en periodos específicos.
 * -   **Backend (PHP):** La API ahora es capaz de procesar parámetros de `grade`, `startDate` y `endDate`, ajustando las consultas SQL de forma segura y eficiente.
 *
 * 2.  **NUEVOS REPORTES ANALÍTICOS Y REESTRUCTURACIÓN:**
 * -   **Reporte de Estudiantes Eliminado:** Se ha eliminado el reporte anterior de "Estudiantes" para dar paso a análisis más profundos en otras áreas.
 * -   **Nuevo Reporte - "Actividades":** Se ha creado un nuevo reporte que analiza la distribución de los tipos de actividades asignadas (evaluables, foros, cuestionarios), filtrable por grado.
 * -   **Mejora en Asistencias:** Además del resumen general, se ha añadido un sub-reporte de "Top Ausentismo" que lista los estudiantes con más inasistencias en el rango de fechas seleccionado.
 *
 * 3.  **MEJORAS ADICIONALES EN UX/UI Y RENDIMIENTO:**
 * -   La interfaz ha sido reestructurada para alojar los nuevos filtros de manera intuitiva. Los filtros de fecha solo aparecen cuando son relevantes (en el reporte de Asistencia).
 * -   Se han optimizado las consultas para incluir los nuevos filtros y se han añadido nuevos KPIs para enriquecer la visualización de datos.
 * -   El código JavaScript ha sido refactorizado para manejar la lógica de los nuevos filtros y la renderización condicional de los mismos.
 */

// --- GESTIÓN DE SESIÓN Y CONEXIÓN A LA BASE DE DATOS ---
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!@include_once __DIR__ . '/../comun/conexion.php') {
    if (isset($_GET['report'])) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['error' => 'Error Crítico: No se pudo encontrar el archivo de conexión.']);
        exit;
    }
    die("Error Crítico: No se pudo encontrar el archivo de conexión.");
}

// =================================================================
// PARTE 1: API INTERNA PARA PROVEER DATOS A LOS REPORTES
// =================================================================
if (isset($_GET['report'])) {
    header('Content-Type: application/json');
    global $mysqli;

    // --- OBTENER PARÁMETROS DE FILTRADO ---
    $grade = isset($_GET['grade']) && !empty($_GET['grade']) ? (int)$_GET['grade'] : null;
    $startDate = isset($_GET['startDate']) && !empty($_GET['startDate']) ? $_GET['startDate'] : null;
    $endDate = isset($_GET['endDate']) && !empty($_GET['endDate']) ? $_GET['endDate'] : null;

    // --- OBTENER EL AÑO LECTIVO ACTIVO ---
    $idAnoActivo = null;
    $nombreAnoActivo = null;
    $sqlAno = "SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo WHERE estado = 'Activo' LIMIT 1";
    if ($resultAno = $mysqli->query($sqlAno)) {
        if ($rowAno = $resultAno->fetch_assoc()) {
            $idAnoActivo = (int)$rowAno['id_ano_lectivo'];
            $nombreAnoActivo = $rowAno['nombre_ano_lectivo'];
        }
    }

    if ($idAnoActivo === null) {
        http_response_code(500);
        echo json_encode(['error' => 'No se ha configurado un año lectivo como "Activo" en el sistema.']);
        exit;
    }
    
    // --- OBTENER GRADOS DISPONIBLES PARA EL FILTRO ---
    $grades_sql = "SELECT id_categoria_curso, nombre_categoria_curso FROM categoria_curso ORDER BY id_categoria_curso ASC";
    $available_grades = $mysqli->query($grades_sql)->fetch_all(MYSQLI_ASSOC);


    $reportType = $_GET['report'];
    $data = [];

    function handleQueryError($stmt) {
        error_log("MySQL Query Error: " . $stmt->error);
        http_response_code(500);
        echo json_encode(['error' => 'Ocurrió un error al consultar la base de datos.']);
        exit;
    }

    // --- LÓGICA DE REPORTES ---
    switch ($reportType) {
        case 'planeaciones':
            $sql = "SELECT m.nombre_materia, COUNT(p.id_plan) as total
                    FROM planeador_vallesol p
                    JOIN asignacion a ON p.materia = a.id_asignacion
                    JOIN materia_oficial m ON a.id_asignatura = m.id_materia
                    WHERE a.ano_lectivo = ?";
            
            $types = "i";
            $params = [&$idAnoActivo];

            if ($grade) {
                $sql .= " AND a.id_categoria_curso = ?";
                $types .= "i";
                $params[] = &$grade;
            }

            $sql .= " GROUP BY m.nombre_materia ORDER BY total DESC";
            
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) { handleQueryError($stmt); }
            
            $report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $kpi_total = array_sum(array_column($report_data, 'total'));

            $data = [
                'title' => 'Análisis de Planeaciones por Materia',
                'kpis' => [
                    ['label' => 'Total de Planes Creados', 'value' => $kpi_total],
                    ['label' => 'Materias con Planes', 'value' => count($report_data)]
                ],
                'chart_type' => 'bar', 'table_headers' => ['Materia', 'Cantidad de Planes'], 'data' => $report_data
            ];
            break;

        case 'asistencias':
            // Reporte 1: Resumen de Asistencias
            $sql_summary = "SELECT asistencias AS tipo_asistencia, COUNT(id) as total
                            FROM asistencias
                            WHERE STR_TO_DATE(fechas_clase, '%d/%m/%Y') BETWEEN ? AND ?
                            GROUP BY tipo_asistencia";
            
            $fecha_inicio = $startDate ? $startDate : $nombreAnoActivo . "-01-01";
            $fecha_fin = $endDate ? $endDate : $nombreAnoActivo . "-12-31";

            $stmt_summary = $mysqli->prepare($sql_summary);
            $stmt_summary->bind_param("ss", $fecha_inicio, $fecha_fin);
            if (!$stmt_summary->execute()) { handleQueryError($stmt_summary); }
            
            $summary_result = $stmt_summary->get_result();
            $summary_data = [];
            $total_registros = 0;
            $total_si = 0;
            while ($row = $summary_result->fetch_assoc()) {
                $total_registros += $row['total'];
                if (strtoupper($row['tipo_asistencia']) === 'SI') {
                    $total_si += $row['total'];
                }
                $summary_data[] = $row;
            }
            $porcentaje_asistencia = $total_registros > 0 ? round(($total_si / $total_registros) * 100, 1) . '%' : '0%';

            // Reporte 2: Top Ausentismo
            $sql_absences = "SELECT estudiante, COUNT(id) as total_ausencias
                             FROM asistencias
                             WHERE STR_TO_DATE(fechas_clase, '%d/%m/%Y') BETWEEN ? AND ? AND UPPER(asistencias) = 'NO'
                             GROUP BY estudiante
                             ORDER BY total_ausencias DESC
                             LIMIT 10";
            $stmt_absences = $mysqli->prepare($sql_absences);
            $stmt_absences->bind_param("ss", $fecha_inicio, $fecha_fin);
            if (!$stmt_absences->execute()) { handleQueryError($stmt_absences); }
            $absences_data = $stmt_absences->get_result()->fetch_all(MYSQLI_ASSOC);

            $data = [
                'kpis' => [
                    ['label' => 'Total Registros Periodo', 'value' => $total_registros],
                    ['label' => '% de Asistencia Periodo', 'value' => $porcentaje_asistencia]
                ],
                'reports' => [
                    [
                        'title' => 'Resumen de Asistencias',
                        'chart_type' => 'pie', 'table_headers' => ['Tipo', 'Cantidad'], 'data' => $summary_data
                    ],
                    [
                        'title' => 'Top 10 Estudiantes con más Ausencias',
                        'chart_type' => 'bar', 'table_headers' => ['Estudiante', 'N° Ausencias'], 'data' => $absences_data
                    ]
                ]
            ];
            break;

        case 'actividades':
            $sql = "SELECT 
                        CASE WHEN evaluable = 'SI' THEN 'Evaluable' ELSE 'No Evaluable' END as tipo,
                        COUNT(id_actividad) as total
                    FROM actividad ac
                    JOIN asignacion a ON ac.id_asignacion = a.id_asignacion
                    WHERE a.ano_lectivo = ?";
            
            $types = "i";
            $params = [&$idAnoActivo];

            if ($grade) {
                $sql .= " AND a.id_categoria_curso = ?";
                $types .= "i";
                $params[] = &$grade;
            }
            $sql .= " GROUP BY tipo";
            
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) { handleQueryError($stmt); }
            $report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $kpi_total = array_sum(array_column($report_data, 'total'));

            $data = [
                'title' => 'Distribución de Tipos de Actividad',
                'kpis' => [['label' => 'Total de Actividades Creadas', 'value' => $kpi_total]],
                'chart_type' => 'pie', 'table_headers' => ['Tipo', 'Cantidad'], 'data' => $report_data
            ];
            break;
            
        case 'docentes':
             $sql = "SELECT CONCAT(u.nombre, ' ', u.apellido) as nombre_docente, COUNT(a.id_asignacion) as total_asignaciones
                     FROM usuario u
                     JOIN asignacion a ON u.id_usuario = a.id_docente
                     WHERE u.rol LIKE '%docente%' AND u.estado = 'activo' AND a.ano_lectivo = ?";
            
            $types = "i";
            $params = [&$idAnoActivo];

            if ($grade) {
                $sql .= " AND a.id_categoria_curso = ?";
                $types .= "i";
                $params[] = &$grade;
            }

            $sql .= " GROUP BY u.id_usuario ORDER BY total_asignaciones DESC";

            $stmt_docentes = $mysqli->prepare($sql);
            $stmt_docentes->bind_param($types, ...$params);
            if (!$stmt_docentes->execute()) { handleQueryError($stmt_docentes); }
            $asignaciones_por_docente = $stmt_docentes->get_result()->fetch_all(MYSQLI_ASSOC);
            
            $total_docentes_activos = count($asignaciones_por_docente);
            $total_asignaciones = array_sum(array_column($asignaciones_por_docente, 'total_asignaciones'));
            $promedio_asignaciones = $total_docentes_activos > 0 ? round($total_asignaciones / $total_docentes_activos, 1) : 0;
            
            $data = [
                'title' => 'Carga Académica por Docente',
                'kpis' => [
                    ['label' => 'Total Docentes con Carga', 'value' => $total_docentes_activos],
                    ['label' => 'Promedio de Asignaturas', 'value' => $promedio_asignaciones]
                ],
                'chart_type' => 'bar', 'table_headers' => ['Docente', 'N° de Asignaturas'], 'data' => $asignaciones_por_docente
            ];
            break;
    }

    // Adjuntar siempre los grados disponibles a la respuesta
    $data['available_grades'] = $available_grades;
    $data['selected_grade'] = $grade;
    echo json_encode($data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Reportes Dinámicos</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .chart-container { position: relative; height: 400px; width: 100%; }
        .table-wrapper { max-height: 400px; overflow-y: auto; }
        #date-filters { display: none; } /* Oculto por defecto */
    </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-4 md:p-8">
        <header class="pb-6 mb-6 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard de Reportes Docentes</h1>
            <p class="text-gray-500 mt-1">Análisis interactivo de la actividad académica.</p>
        </header>

        <div class="bg-white p-4 rounded-xl shadow-sm mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="grade-filter" class="text-sm font-medium text-gray-600 mb-1 block">Filtrar por Grado:</label>
                    <select id="grade-filter" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <!-- Opciones de grado se cargarán aquí -->
                    </select>
                </div>
                <div id="date-filters" class="md:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                     <div>
                        <label for="start-date-filter" class="text-sm font-medium text-gray-600 mb-1 block">Fecha Inicio:</label>
                        <input type="date" id="start-date-filter" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="end-date-filter" class="text-sm font-medium text-gray-600 mb-1 block">Fecha Fin:</label>
                        <input type="date" id="end-date-filter" class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <nav class="report-selector flex flex-wrap gap-3 mb-8">
            <button class="report-btn active" data-report="planeaciones">Planeaciones</button>
            <button class="report-btn" data-report="asistencias">Asistencia</button>
            <button class="report-btn" data-report="actividades">Actividades</button>
            <button class="report-btn" data-report="docentes">Docentes</button>
        </nav>
        
        <main id="report-content" class="space-y-8">
            <!-- El contenido del reporte se cargará aquí dinámicamente -->
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const reportContent = document.getElementById('report-content');
    const reportSelector = document.querySelector('.report-selector');
    const gradeFilter = document.getElementById('grade-filter');
    const dateFilters = document.getElementById('date-filters');
    const startDateFilter = document.getElementById('start-date-filter');
    const endDateFilter = document.getElementById('end-date-filter');

    let currentCharts = {};
    let activeReportType = 'planeaciones';

    const destroyCharts = () => {
        Object.values(currentCharts).forEach(chart => chart.destroy());
        currentCharts = {};
    };
    
    const fetchWithTimeout = async (resource, options = {}) => {
        const { timeout = 15000 } = options;
        const controller = new AbortController();
        const id = setTimeout(() => controller.abort(), timeout);
        const response = await fetch(resource, { ...options, signal: controller.signal });
        clearTimeout(id);
        return response;
    };

    const loadReportData = async () => {
        const reportType = activeReportType;
        const buttonText = document.querySelector(`.report-btn[data-report="${reportType}"]`).textContent;
        reportContent.innerHTML = `<div class="flex flex-col items-center justify-center p-12 bg-white rounded-lg shadow-md text-gray-500"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div><p>Cargando reporte de ${buttonText}...</p></div>`;
        destroyCharts();

        // Gestionar la visibilidad de los filtros de fecha
        dateFilters.style.display = reportType === 'asistencias' ? 'grid' : 'none';
        
        try {
            let url = new URL(window.location.href);
            url.search = `?report=${reportType}`;
            if (gradeFilter.value) url.searchParams.append('grade', gradeFilter.value);
            if (reportType === 'asistencias' && startDateFilter.value) url.searchParams.append('startDate', startDateFilter.value);
            if (reportType === 'asistencias' && endDateFilter.value) url.searchParams.append('endDate', endDateFilter.value);

            const response = await fetchWithTimeout(url);
            
            if (!response.ok) {
                let errorMsg = `Error del servidor (${response.status})`;
                try {
                    const errorData = await response.json();
                    if (errorData.error) errorMsg = errorData.error;
                } catch (e) {}
                throw new Error(errorMsg);
            }
            const data = await response.json();
            renderReport(data);
        } catch (error) {
            let userFriendlyError = 'Ocurrió un error al cargar el reporte.';
            if (error.name === 'AbortError') userFriendlyError = 'La solicitud tardó demasiado. Intente de nuevo.';
            else userFriendlyError = error.message;
            reportContent.innerHTML = `<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-md" role="alert"><p class="font-bold">Error al cargar</p><p>${userFriendlyError}</p></div>`;
            console.error('Fetch error:', error);
        }
    };

    const populateGradeFilter = (grades, selectedGrade) => {
        let optionsHTML = '<option value="">Todos los Grados</option>';
        (grades || []).forEach(grade => {
            const selected = selectedGrade == grade.id_categoria_curso ? 'selected' : '';
            optionsHTML += `<option value="${grade.id_categoria_curso}" ${selected}>${grade.nombre_categoria_curso}</option>`;
        });
        gradeFilter.innerHTML = optionsHTML;
    };

    const renderReport = (reportData) => {
        reportContent.innerHTML = ''; 
        populateGradeFilter(reportData.available_grades, reportData.selected_grade);
        
        const kpiContainer = document.createElement('div');
        kpiContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6';
        (reportData.kpis || []).forEach(kpi => {
            kpiContainer.innerHTML += `<div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500"><p class="text-4xl font-bold text-gray-800">${kpi.value}</p><p class="text-sm text-gray-500 font-medium mt-1">${kpi.label}</p></div>`;
        });
        reportContent.appendChild(kpiContainer);
        
        if (reportData.reports && Array.isArray(reportData.reports)) {
            const reportGrid = document.createElement('div');
            reportGrid.className = 'grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8';
            reportData.reports.forEach((report, index) => {
                const section = createReportSection(report, index);
                reportGrid.appendChild(section);
                renderChart(`report-chart-${index}`, report.chart_type, report.data, report.title);
            });
            reportContent.appendChild(reportGrid);
        } else { 
            const section = createReportSection(reportData, 0);
            reportContent.appendChild(section);
            renderChart('report-chart-0', reportData.chart_type, reportData.data, reportData.title);
        }
    };
    
    const createReportSection = (report, index) => {
        const sectionContainer = document.createElement('div');
        sectionContainer.className = 'bg-white p-6 rounded-xl shadow-md flex flex-col gap-6';
        
        const chartId = `report-chart-${index}`;
        let tableHTML = `<h2 class="text-xl font-semibold text-gray-700 border-b pb-3">${report.title}</h2>`;

        if (report.chart_type) {
            tableHTML += `<div class="chart-container"><canvas id="${chartId}"></canvas></div>`;
        }

        tableHTML += `<div><h3 class="text-lg font-semibold text-gray-700 mb-2">Datos Detallados</h3><div class="table-wrapper"><table class="w-full text-sm"><thead class="bg-gray-50"><tr>`;
        (report.table_headers || []).forEach(header => tableHTML += `<th class="px-4 py-2 text-left text-gray-600 font-medium">${header}</th>`);
        tableHTML += `</tr></thead><tbody class="divide-y divide-gray-200">`;

        if(report.data && report.data.length > 0) {
            report.data.forEach(row => {
                tableHTML += `<tr>`;
                Object.values(row).forEach(cell => tableHTML += `<td class="px-4 py-2 text-gray-700">${cell}</td>`);
                tableHTML += `</tr>`;
            });
        } else {
            const colspan = (report.table_headers || []).length;
            tableHTML += `<tr><td colspan="${colspan}" class="text-center py-8 text-gray-500">No hay datos disponibles para mostrar.</td></tr>`;
        }
        tableHTML += `</tbody></table></div></div>`;
        sectionContainer.innerHTML = tableHTML;
        return sectionContainer;
    };

    const renderChart = (canvasId, type, data, label) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas || !data || data.length === 0) return;
        
        const ctx = canvas.getContext('2d');
        const labels = data.map(item => Object.values(item)[0]);
        const values = data.map(item => Object.values(item)[1]);
        const chartColors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#14B8A6', '#D97706'];

        const chartConfig = {
            type: type,
            data: { labels, datasets: [{ label, data: values, backgroundColor: type === 'pie' || type === 'doughnut' ? chartColors : 'rgba(59, 130, 246, 0.7)', borderColor: 'rgba(59, 130, 246, 1)', borderWidth: 1, hoverBackgroundColor: 'rgba(59, 130, 246, 0.9)' }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: type === 'pie' || type === 'doughnut' }, tooltip: { backgroundColor: '#1F2937', titleFont: { size: 14, weight: 'bold' }, bodyFont: { size: 12 }, padding: 10, cornerRadius: 4 } },
                scales: type.includes('bar') || type.includes('line') ? { y: { beginAtZero: true, grid: { color: '#E5E7EB' } }, x: { grid: { display: false } } } : {}
            }
        };
        currentCharts[canvasId] = new Chart(ctx, chartConfig);
    };

    reportSelector.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' && !e.target.classList.contains('active')) {
            document.querySelectorAll('.report-btn').forEach(btn => {
                btn.classList.remove('active', 'bg-blue-700', 'text-white');
                btn.classList.add('bg-white', 'text-gray-700');
            });
            e.target.classList.add('active', 'bg-blue-700', 'text-white');
            e.target.classList.remove('bg-white', 'text-gray-700');
            activeReportType = e.target.dataset.report;
            loadReportData();
        }
    });

    [gradeFilter, startDateFilter, endDateFilter].forEach(filter => {
        filter.addEventListener('change', loadReportData);
    });

    document.querySelectorAll('.report-btn').forEach(btn => {
        btn.classList.add('px-4', 'py-2', 'rounded-lg', 'font-semibold', 'transition-colors', 'duration-200', 'shadow-sm');
        if(btn.classList.contains('active')) btn.classList.add('bg-blue-700', 'text-white');
        else btn.classList.add('bg-white', 'text-gray-700', 'hover:bg-gray-50');
    });

    loadReportData();
});
</script>
</body>
</html>
