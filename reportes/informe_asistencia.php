<?php
// --- CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS ---
require_once "../comun/config.php";
date_default_timezone_set('America/Bogota');

$db_host = SERVIDORBD;
$db_name = BASEDEDATOS;
$db_user = USUARIOBD;
$db_pass = CLAVEBD;

// --- LÓGICA DE BACKEND (MANEJO DE AJAX PARA OBTENER DATOS) ---
if (isset($_GET['action']) && $_GET['action'] == 'fetch_report_data') {
    header('Content-Type: application/json');
    $response = [
        'students' => [],
        'subjects' => [],
        'analytics' => []
    ];

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // --- Filtro de Fechas ---
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');
        $params = [':start_date' => $start_date, ':end_date' => $end_date];

        // 1. Obtener lista de materias (asignaturas)
        $stmt_subjects = $pdo->query("SELECT DISTINCT materia FROM asistencias ORDER BY materia ASC");
        $response['subjects'] = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);

        // 2. Obtener lista de estudiantes activos
        $sql_students = "SELECT DISTINCT u.id_usuario, u.nombre, u.apellido, u.observaciones as grado
                         FROM usuario u
                         JOIN inscripcion i ON u.id_usuario = i.id_estudiante
                         WHERE i.estado_inscripcion = 'En curso'
                         ORDER BY CAST(u.observaciones AS UNSIGNED) ASC, u.apellido ASC, u.nombre ASC";
        $stmt_students = $pdo->query($sql_students);
        $students_raw = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

        // 3. Obtener inasistencias
        $sql_absences = "SELECT documento, materia, fechas_clase
                         FROM asistencias
                         WHERE asistencias = 'NO' 
                         AND STR_TO_DATE(TRIM(fechas_clase), '%d/%m/%Y') BETWEEN :start_date AND :end_date";
        $stmt_absences = $pdo->prepare($sql_absences);
        $stmt_absences->execute($params);
        $absences_raw = $stmt_absences->fetchAll(PDO::FETCH_ASSOC);
        
        // --- PROCESAMIENTO PARA LA MATRIZ PRINCIPAL ---
        $absences_map = [];
        foreach ($absences_raw as $absence) {
            $doc = $absence['documento'];
            $mat = $absence['materia'];
            if (!isset($absences_map[$doc])) $absences_map[$doc] = [];
            if (!isset($absences_map[$doc][$mat])) $absences_map[$doc][$mat] = 0;
            $absences_map[$doc][$mat]++;
        }

        foreach ($students_raw as $student) {
            $student_id = $student['id_usuario'];
            $student_absences = [];
            foreach ($response['subjects'] as $subject) {
                $student_absences[$subject] = $absences_map[$student_id][$subject] ?? 0;
            }
            $response['students'][] = [
                'id' => $student_id,
                'name' => $student['apellido'] . ' ' . $student['nombre'],
                'grade' => $student['grado'],
                'absences' => $student_absences
            ];
        }

        // --- CÁLCULO DE MÉTRICAS PARA EL DASHBOARD DE ANÁLISIS ---
        
        // Total Inasistencias
        $response['analytics']['totalAbsences'] = count($absences_raw);

        // Inasistencias por Materia
        $absences_by_subject = [];
        foreach ($absences_raw as $absence) {
            $subject = $absence['materia'];
            if (!isset($absences_by_subject[$subject])) $absences_by_subject[$subject] = 0;
            $absences_by_subject[$subject]++;
        }
        arsort($absences_by_subject);
        $response['analytics']['absencesBySubject'] = $absences_by_subject;
        
        // Inasistencias por Día de la Semana
        $day_map_es = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];
        $absences_by_day = array_fill_keys(array_values($day_map_es), 0);
        foreach ($absences_raw as $absence) {
            try {
                $date = DateTime::createFromFormat('d/m/Y', trim($absence['fechas_clase']));
                if ($date) {
                    $day_en = $date->format('l');
                    $day_es = $day_map_es[$day_en];
                    $absences_by_day[$day_es]++;
                }
            } catch (Exception $e) { /* Ignorar fechas con formato inválido */ }
        }
        $response['analytics']['absencesByDay'] = $absences_by_day;

        // Top Estudiantes con Inasistencias
        $absences_by_student = [];
        foreach ($absences_raw as $absence) {
            $doc = $absence['documento'];
            if (!isset($absences_by_student[$doc])) $absences_by_student[$doc] = 0;
            $absences_by_student[$doc]++;
        }
        arsort($absences_by_student);
        $top_students = [];
        foreach($absences_by_student as $doc => $count) {
            foreach($students_raw as $student) {
                if ($student['id_usuario'] == $doc) {
                    $top_students[] = ['name' => $student['apellido'] . ' ' . $student['nombre'], 'grade' => $student['grado'], 'count' => $count];
                    break;
                }
            }
        }
        $response['analytics']['topStudents'] = $top_students;

        echo json_encode(['success' => true, 'data' => $response]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inasistencias</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .loader { border-top-color: #3498db; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        .table-container { max-height: 70vh; overflow-y: auto; }
        thead th { position: sticky; top: 0; z-index: 10; }
        .chart-card { background-color: white; border-radius: 0.75rem; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); padding: 1.5rem; }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-7xl mx-auto space-y-8">
        <!-- CABECERA Y FILTROS -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-teal-500 to-cyan-600">
                <h1 class="text-2xl md:text-3xl font-bold text-white">Reporte de Inasistencias por Materia</h1>
                <p class="text-cyan-100 mt-1">Matriz de inasistencias de estudiantes activos.</p>
            </div>
            <div class="p-6">
                <div class="p-4 bg-gray-50 rounded-lg flex flex-col md:flex-row justify-between items-center gap-4 border">
                     <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                        <div>
                            <label for="start_date" class="text-sm font-medium text-gray-700">Desde:</label>
                            <input type="date" id="start_date" value="<?php echo date('Y-m-01'); ?>" class="mt-1 block w-full md:w-auto border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label for="end_date" class="text-sm font-medium text-gray-700">Hasta:</label>
                            <input type="date" id="end_date" value="<?php echo date('Y-m-t'); ?>" class="mt-1 block w-full md:w-auto border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <button id="filter_btn" class="self-end bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-300 text-sm shadow-sm">
                            Aplicar Filtro
                        </button>
                    </div>
                    <div class="relative w-full md:w-auto">
                        <input type="text" id="search-student" placeholder="🔍 Buscar estudiante..." class="w-full md:w-64 pl-8 pr-2 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- ESTADO DE CARGA PRINCIPAL -->
        <div id="loader-container" class="text-center py-10 hidden">
            <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-24 w-24 mx-auto"></div>
            <p class="mt-4 text-gray-600 font-semibold">Cargando datos del reporte...</p>
        </div>

        <!-- CONTENIDO PRINCIPAL: TABLA Y DASHBOARD -->
        <div id="main-content" class="space-y-8 hidden">
             <!-- MATRIZ DE INASISTENCIAS -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                 <div class="p-6">
                    <div id="student-count-container" class="px-4 py-2 mb-4 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 font-semibold text-sm hidden"></div>
                    <div id="report-container" class="table-container border rounded-lg">
                        <table id="report-table" class="min-w-full divide-y divide-gray-200">
                            <thead id="report-thead" class="bg-gray-100"></thead>
                            <tbody id="report-tbody" class="bg-white divide-y divide-gray-200"></tbody>
                        </table>
                        <div id="empty-state" class="text-center py-16 hidden">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron estudiantes</h3>
                            <p class="mt-1 text-sm text-gray-500">Verifica los filtros o el rango de fechas seleccionado.</p>
                        </div>
                    </div>
                 </div>
            </div>

            <!-- DASHBOARD DE ANÁLISIS -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                 <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-3">Análisis de Inasistencias</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                        
                        <!-- Card: Total Inasistencias -->
                        <div class="chart-card col-span-1 xl:col-span-1 flex flex-col items-center justify-center text-center">
                            <h3 class="text-lg font-semibold text-gray-500 uppercase">Total de Inasistencias</h3>
                            <p id="total-absences-kpi" class="text-6xl font-bold text-red-500 mt-2">0</p>
                            <p class="text-sm text-gray-500">en el período seleccionado</p>
                        </div>

                        <!-- Card: Top Estudiantes -->
                        <div class="chart-card col-span-1 lg:col-span-2 xl:col-span-2">
                             <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-700">Top Estudiantes con Inasistencias</h3>
                                <div class="flex items-center gap-2 mt-2 sm:mt-0">
                                    <label for="top-n-students" class="text-sm font-medium">Mostrar Top:</label>
                                    <input type="number" id="top-n-students" value="5" class="w-20 border-gray-300 rounded-md shadow-sm text-sm">
                                </div>
                            </div>
                            <div id="top-students-list" class="max-h-64 overflow-y-auto pr-2"></div>
                        </div>

                        <!-- Card: Gráfico Materias -->
                        <div class="chart-card col-span-1 lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Inasistencias por Materia</h3>
                            <canvas id="subjectsChart"></canvas>
                        </div>

                        <!-- Card: Gráfico Días -->
                        <div class="chart-card col-span-1 lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-700 mb-4">Inasistencias por Día de la Semana</h3>
                            <canvas id="daysChart"></canvas>
                        </div>

                    </div>
                 </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const filterBtn = document.getElementById('filter_btn');
    const searchInput = document.getElementById('search-student');
    const loaderContainer = document.getElementById('loader-container');
    const mainContent = document.getElementById('main-content');
    
    // Elementos de la tabla
    const tableThead = document.getElementById('report-thead');
    const tableTbody = document.getElementById('report-tbody');
    const studentCountContainer = document.getElementById('student-count-container');
    const emptyStateContainer = document.getElementById('empty-state');

    // Elementos del Dashboard
    const totalAbsencesKPI = document.getElementById('total-absences-kpi');
    const topNStudentsInput = document.getElementById('top-n-students');
    const topStudentsList = document.getElementById('top-students-list');

    // Instancias de los gráficos
    let subjectsChart, daysChart;
    let fullAnalyticsData = {};

    async function fetchReportData() {
        loaderContainer.classList.remove('hidden');
        mainContent.classList.add('hidden');
        
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        
        try {
            const response = await fetch(`?action=fetch_report_data&start_date=${startDate}&end_date=${endDate}`);
            const result = await response.json();

            if (result.success) {
                fullAnalyticsData = result.data.analytics;
                renderTable(result.data);
                renderAnalytics(result.data.analytics);
                mainContent.classList.remove('hidden');
            } else {
                 emptyStateContainer.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                 emptyStateContainer.classList.remove('hidden');
            }
        } catch (error) {
            emptyStateContainer.innerHTML = `<p class="text-red-500">Error de conexión. No se pudieron cargar los datos.</p>`;
            emptyStateContainer.classList.remove('hidden');
        } finally {
            loaderContainer.classList.add('hidden');
        }
    }

    function renderTable(data) {
        let theadHTML = '<tr><th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th><th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">Estudiante</th>';
        data.subjects.forEach(subject => {
            theadHTML += `<th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">${subject}</th>`;
        });
        theadHTML += '</tr>';
        tableThead.innerHTML = theadHTML;

        let tbodyHTML = '';
        if (data.students.length === 0) {
            tableTbody.innerHTML = '';
            emptyStateContainer.classList.remove('hidden');
            studentCountContainer.classList.add('hidden');
        } else {
            emptyStateContainer.classList.add('hidden');
            let counter = 1;
            data.students.forEach(student => {
                tbodyHTML += `<tr class="student-row even:bg-gray-50 hover:bg-indigo-50 transition-colors duration-150" data-name="${student.name.toLowerCase()}">`;
                tbodyHTML += `<td class="px-4 py-3 text-center text-sm font-medium text-gray-500">${counter++}</td>`;
                tbodyHTML += `<td class="px-4 py-3 whitespace-nowrap"><div class="text-sm font-medium text-gray-900">${student.name}</div><div class="text-xs text-gray-500">Grado: ${student.grade}</div></td>`;
                data.subjects.forEach(subject => {
                    const absenceCount = student.absences[subject] || 0;
                    const textColor = absenceCount > 0 ? 'text-red-600 font-bold' : 'text-gray-400';
                    tbodyHTML += `<td class="px-4 py-3 text-center text-sm ${textColor}">${absenceCount}</td>`;
                });
                tbodyHTML += `</tr>`;
            });
            tableTbody.innerHTML = tbodyHTML;
            studentCountContainer.textContent = `Total de Estudiantes: ${data.students.length}`;
            studentCountContainer.classList.remove('hidden');
        }
    }

    function renderAnalytics(analytics) {
        // KPI
        totalAbsencesKPI.textContent = analytics.totalAbsences || 0;

        // Gráfico de Materias
        if (subjectsChart) subjectsChart.destroy();
        const subjectsCtx = document.getElementById('subjectsChart').getContext('2d');
        subjectsChart = new Chart(subjectsCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(analytics.absencesBySubject),
                datasets: [{
                    label: 'Inasistencias',
                    data: Object.values(analytics.absencesBySubject),
                    backgroundColor: 'rgba(29, 78, 216, 0.7)',
                    borderColor: 'rgba(29, 78, 216, 1)',
                    borderWidth: 1
                }]
            },
            options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
        });

        // Gráfico de Días
        if (daysChart) daysChart.destroy();
        const daysCtx = document.getElementById('daysChart').getContext('2d');
        daysChart = new Chart(daysCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(analytics.absencesByDay),
                datasets: [{
                    label: 'Inasistencias',
                    data: Object.values(analytics.absencesByDay),
                    backgroundColor: 'rgba(20, 184, 166, 0.7)',
                    borderColor: 'rgba(20, 184, 166, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // Renderizar lista de Top N
        renderTopStudents();
    }
    
    function renderTopStudents() {
        if (!fullAnalyticsData.topStudents) return;
        const topN = parseInt(topNStudentsInput.value) || 5;
        const studentsToRender = fullAnalyticsData.topStudents.slice(0, topN);

        if (studentsToRender.length === 0) {
            topStudentsList.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">No hay inasistencias en el período seleccionado.</p>';
            return;
        }

        let listHTML = '<ol class="divide-y divide-gray-200">';
        studentsToRender.forEach((student, index) => {
            listHTML += `
                <li class="p-3 flex justify-between items-center">
                    <div class="flex items-center">
                        <span class="text-sm font-bold text-gray-600 w-8">${index + 1}.</span>
                        <div>
                            <p class="text-sm font-medium text-gray-900">${student.name}</p>
                            <p class="text-xs text-gray-500">Grado: ${student.grade}</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">${student.count} faltas</span>
                </li>`;
        });
        listHTML += '</ol>';
        topStudentsList.innerHTML = listHTML;
    }


    // --- Event Listeners ---
    filterBtn.addEventListener('click', fetchReportData);
    topNStudentsInput.addEventListener('input', renderTopStudents);
    
    searchInput.addEventListener('input', () => {
        const searchTerm = searchInput.value.toLowerCase();
        let visibleCount = 0;
        document.querySelectorAll('.student-row').forEach(row => {
            const studentName = row.dataset.name;
            if (studentName.includes(searchTerm)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        const total = document.querySelectorAll('.student-row').length;
        studentCountContainer.textContent = `Mostrando ${visibleCount} de ${total} estudiantes`;
    });

    // Carga inicial
    fetchReportData();
});
</script>

</body>
</html>

