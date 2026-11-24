<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin', 'directivo', 'docente']);
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../asistencia/lib/registros.php';

require_once __DIR__ . "/../comun/config.php";
date_default_timezone_set('America/Bogota');
$periodoConfigurado = guagua_periodo_activo(guagua_db_request());
$anoConfigurado = guagua_ano_configurado(guagua_db_request());
$inicioConfigurado = $periodoConfigurado['fecha_inicio'] ?? $anoConfigurado . '-01-01';
$finConfigurado = $periodoConfigurado['fecha_fin'] ?? $anoConfigurado . '-12-31';


function informe_asistencia_fecha($fecha, $predeterminada)
{
    $fecha = is_string($fecha) ? trim($fecha) : '';
    $objetoFecha = DateTime::createFromFormat('Y-m-d', $fecha);
    return $objetoFecha && $objetoFecha->format('Y-m-d') === $fecha ? $fecha : $predeterminada;
}

if (isset($_GET['action']) && $_GET['action'] === 'fetch_report_data') {
    header('Content-Type: application/json; charset=utf-8');
    $inicio = informe_asistencia_fecha($_GET['start_date'] ?? '', $inicioConfigurado);
    $fin = informe_asistencia_fecha($_GET['end_date'] ?? '', $finConfigurado);
    $materiaFiltro = isset($_GET['subject']) ? trim((string) $_GET['subject']) : '';

    if ($inicio > $fin) {
        echo json_encode(['success' => false, 'message' => 'La fecha inicial no puede ser posterior a la fecha final.']);
        exit;
    }

    try {
        $pdo = guagua_db_pdo('default', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        $fechaSql = guagua_asistencia_fecha_sql();
        $condicionFecha = "($fechaSql) BETWEEN :fecha_inicio AND :fecha_fin";
        $parametros = [':fecha_inicio' => $inicio, ':fecha_fin' => $fin];
        $condicionMateria = '';
        if ($materiaFiltro !== '') {
            $condicionMateria = ' AND TRIM(a.materia) = :materia';
            $parametros[':materia'] = $materiaFiltro;
        }

        // Fuente única: asistencias, la misma tabla escrita por asistencia/index.php.
        $sql = "SELECT
                    a.id, a.fecha_actualizacion,
                    TRIM(a.documento) AS documento,
                    CAST(u.id_usuario AS CHAR) AS documento_real,
                    CONCAT_WS(' ', TRIM(u.apellido), TRIM(u.nombre)) AS nombre_usuario,
                    COALESCE(NULLIF(TRIM(a.estudiante), ''), CONCAT_WS(' ', NULLIF(TRIM(u.apellido), ''), NULLIF(TRIM(u.nombre), '')), 'Estudiante sin identificar') AS estudiante,
                    COALESCE(NULLIF(TRIM(u.observaciones), ''), 'Sin grado') AS grado,
                    TRIM(a.materia) AS materia,
                    UPPER(TRIM(a.asistencias)) AS estado,
                    $fechaSql AS fecha
                FROM asistencias a
                LEFT JOIN usuario u ON CAST(u.id_usuario AS CHAR) = TRIM(a.documento)
                WHERE $condicionFecha $condicionMateria
                ORDER BY a.fecha_actualizacion DESC, a.id DESC";
        $sentencia = $pdo->prepare($sql);
        $sentencia->execute($parametros);
        $registros = guagua_asistencia_registros_unicos($sentencia->fetchAll(PDO::FETCH_ASSOC));

        $sqlMaterias = "SELECT DISTINCT TRIM(a.materia) AS materia FROM asistencias a WHERE $condicionFecha AND TRIM(a.materia) <> '' ORDER BY materia ASC";
        $sentenciaMaterias = $pdo->prepare($sqlMaterias);
        $sentenciaMaterias->execute([':fecha_inicio' => $inicio, ':fecha_fin' => $fin]);
        $materias = $sentenciaMaterias->fetchAll(PDO::FETCH_COLUMN);

        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $mapaDias = ['Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado', 'Sunday' => 'Domingo'];
        $analitica = [
            'totalRecords' => 0, 'totalPresences' => 0, 'totalAbsences' => 0, 'totalPermissions' => 0, 'totalLates' => 0,
            'studentsWithAttendance' => 0, 'absencesBySubject' => [], 'absencesByDay' => array_fill_keys($dias, 0), 'topStudents' => []
        ];
        $estudiantes = [];
        $etiquetasMaterias = [];

        foreach ($registros as $registro) {
            $documento = $registro['documento'] !== '' ? $registro['documento'] : 'sin-documento-' . count($estudiantes);
            $claveEstudiante = $registro['clave_estudiante'];
            $estado = in_array($registro['estado'], ['SI', 'NO', 'P', 'R'], true) ? $registro['estado'] : 'SI';
            if (!isset($estudiantes[$claveEstudiante])) {
                $estudiantes[$claveEstudiante] = [
                    'id' => $documento, 'name' => $registro['estudiante'], 'grade' => $registro['grado'],
                    'records' => 0, 'presences' => 0, 'absences' => 0, 'permissions' => 0, 'lates' => 0
                ];
            } elseif ($estudiantes[$claveEstudiante]['grade'] === 'Sin grado' && $registro['grado'] !== 'Sin grado') {
                $estudiantes[$claveEstudiante]['grade'] = $registro['grado'];
            }
            $estudiantes[$claveEstudiante]['records']++;
            $analitica['totalRecords']++;
            if ($estado === 'NO') {
                $estudiantes[$claveEstudiante]['absences']++;
                $analitica['totalAbsences']++;
                $claveMateria = guagua_asistencia_clave_texto($registro['materia']) ?: 'sin-asignatura';
                $etiquetasMaterias[$claveMateria] = $etiquetasMaterias[$claveMateria] ?? ($registro['materia'] ?: 'Sin asignatura');
                $analitica['absencesBySubject'][$claveMateria] = ($analitica['absencesBySubject'][$claveMateria] ?? 0) + 1;
                $fecha = DateTime::createFromFormat('Y-m-d', $registro['fecha']);
                if ($fecha) $analitica['absencesByDay'][$mapaDias[$fecha->format('l')]]++;
            } elseif ($estado === 'P') {
                $estudiantes[$claveEstudiante]['permissions']++;
                $analitica['totalPermissions']++;
            } elseif ($estado === 'R') {
                $estudiantes[$claveEstudiante]['lates']++;
                $analitica['totalLates']++;
            } else {
                $estudiantes[$claveEstudiante]['presences']++;
                $analitica['totalPresences']++;
            }
        }

        $estudiantes = array_values($estudiantes);
        foreach ($estudiantes as &$estudiante) {
            $estudiante['attendanceRate'] = $estudiante['records'] > 0 ? round((($estudiante['presences'] + $estudiante['lates']) / $estudiante['records']) * 100, 1) : 0;
        }
        unset($estudiante);
        usort($estudiantes, static function ($a, $b) {
            $gradoA = is_numeric($a['grade']) ? (int) $a['grade'] : PHP_INT_MAX;
            $gradoB = is_numeric($b['grade']) ? (int) $b['grade'] : PHP_INT_MAX;
            return $gradoA === $gradoB ? strcasecmp($a['name'], $b['name']) : $gradoA <=> $gradoB;
        });
        $estudiantesPorFaltas = $estudiantes;
        usort($estudiantesPorFaltas, static function ($a, $b) {
            return $b['absences'] === $a['absences'] ? strcasecmp($a['name'], $b['name']) : $b['absences'] <=> $a['absences'];
        });
        foreach ($estudiantesPorFaltas as $estudiante) {
            if ($estudiante['absences'] > 0) $analitica['topStudents'][] = ['name' => $estudiante['name'], 'grade' => $estudiante['grade'], 'count' => $estudiante['absences']];
        }
        $ausenciasPorMateria = [];
        foreach ($analitica['absencesBySubject'] as $claveMateria => $cantidad) {
            $ausenciasPorMateria[$etiquetasMaterias[$claveMateria]] = $cantidad;
        }
        arsort($ausenciasPorMateria);
        $analitica['absencesBySubject'] = $ausenciasPorMateria;
        $analitica['studentsWithAttendance'] = count($estudiantes);

        echo json_encode(['success' => true, 'data' => ['students' => $estudiantes, 'subjects' => $materias, 'analytics' => $analitica, 'period' => ['start' => $inicio, 'end' => $fin]]], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $error) {
        error_log('Informe de asistencia: ' . $error->getMessage());
        echo json_encode(['success' => false, 'message' => 'No fue posible consultar la información de asistencia.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de asistencia</title>
    <script src="/guagua/comun/js/tailwindcss.js"></script>
    <script src="/guagua/comun/js/chart.js"></script>
    <style>
        body { font-family: Inter, sans-serif; background: #f4f7fb; color: #172033; }
        .loader { border-top-color: #2563eb; animation: spin .8s linear infinite; } @keyframes spin { to { transform: rotate(360deg); } }
        .table-container { max-height: 66vh; overflow: auto; } thead th { position: sticky; top: 0; z-index: 10; }
        .chart-card { background: #fff; border: 1px solid #e7edf6; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 12px 28px rgba(30, 41, 59, .05); }
    </style>
</head>
<body class="p-4 md:p-8">
    <main class="max-w-7xl mx-auto space-y-6">
        <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-700 px-6 py-7 md:px-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-blue-200">Seguimiento académico</p><h1 class="mt-2 text-2xl font-extrabold text-white md:text-3xl">Informe de asistencia</h1><p class="mt-2 max-w-2xl text-sm text-blue-100">Información consolidada desde el registro de asistencia vigente: presentes, ausencias, permisos y retardos.</p></div><a href="../asistencia/index.php" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-white/10 px-4 py-2 text-sm font-semibold text-white ring-1 ring-inset ring-white/25 transition hover:bg-white/20">Ir a tomar asistencia</a></div>
            </div>
            <div class="p-5 md:p-6"><div class="grid gap-4 lg:grid-cols-[1fr_1fr_1.35fr_auto] lg:items-end">
                <label class="block text-sm font-semibold text-slate-700">Desde<input type="date" id="start_date" value="<?php echo htmlspecialchars($inicioConfigurado, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                <label class="block text-sm font-semibold text-slate-700">Hasta<input type="date" id="end_date" value="<?php echo htmlspecialchars($finConfigurado, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"></label>
                <label class="block text-sm font-semibold text-slate-700">Asignatura<select id="subject" class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"><option value="">Todas las asignaturas con registros</option></select></label>
                <button id="filter_btn" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700">Actualizar informe</button>
            </div><div class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between"><p id="period-label" class="text-sm text-slate-500">Seleccione un período para consultar.</p><input type="search" id="search-student" placeholder="Buscar estudiante…" class="block w-full rounded-lg border-slate-300 py-2 px-3 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:w-72"></div></div>
        </section>
        <div id="loader-container" class="hidden py-12 text-center"><div class="loader mx-auto h-12 w-12 rounded-full border-4 border-slate-200"></div><p class="mt-4 text-sm font-medium text-slate-600">Actualizando información de asistencia…</p></div>
        <div id="feedback" class="hidden rounded-xl border px-4 py-3 text-sm" role="status"></div>
        <div id="main-content" class="hidden space-y-6">
            <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <article class="chart-card"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Registros</p><p id="total-records-kpi" class="mt-2 text-3xl font-extrabold text-slate-900">0</p></article><article class="chart-card"><p class="text-xs font-bold uppercase tracking-wide text-slate-500">Estudiantes</p><p id="students-kpi" class="mt-2 text-3xl font-extrabold text-slate-900">0</p></article><article class="chart-card"><p class="text-xs font-bold uppercase tracking-wide text-emerald-600">Presentes</p><p id="presences-kpi" class="mt-2 text-3xl font-extrabold text-emerald-600">0</p></article><article class="chart-card"><p class="text-xs font-bold uppercase tracking-wide text-rose-600">Ausencias</p><p id="absences-kpi" class="mt-2 text-3xl font-extrabold text-rose-600">0</p></article><article class="chart-card"><p class="text-xs font-bold uppercase tracking-wide text-amber-600">Permisos</p><p id="permissions-kpi" class="mt-2 text-3xl font-extrabold text-amber-600">0</p></article><article class="chart-card"><p class="text-xs font-bold uppercase tracking-wide text-blue-600">Retardos</p><p id="lates-kpi" class="mt-2 text-3xl font-extrabold text-blue-600">0</p></article>
            </section>
            <section class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-200"><div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between"><div><h2 class="text-lg font-extrabold text-slate-900">Resumen por estudiante</h2><p class="mt-1 text-sm text-slate-500">Cada fila proviene de los registros guardados en el módulo de asistencia.</p></div><span id="student-count-container" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700"></span></div><div class="table-container"><table class="min-w-full divide-y divide-slate-200"><thead id="report-thead" class="bg-slate-50"></thead><tbody id="report-tbody" class="divide-y divide-slate-100 bg-white"></tbody></table><div id="empty-state" class="hidden px-6 py-16 text-center"><h3 class="text-base font-bold text-slate-800">No hay registros para este período</h3><p class="mt-1 text-sm text-slate-500">Ajuste las fechas o registre asistencia desde el módulo correspondiente.</p></div></div></section>
            <section class="grid gap-6 lg:grid-cols-2"><article class="chart-card"><h2 class="text-base font-extrabold text-slate-900">Ausencias por asignatura</h2><div class="mt-4 h-72"><canvas id="subjectsChart"></canvas></div></article><article class="chart-card"><h2 class="text-base font-extrabold text-slate-900">Ausencias por día de la semana</h2><div class="mt-4 h-72"><canvas id="daysChart"></canvas></div></article></section>
            <section class="chart-card"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><h2 class="text-base font-extrabold text-slate-900">Estudiantes con más ausencias</h2><label class="text-sm font-medium text-slate-600">Mostrar <input type="number" id="top-n-students" value="5" min="1" max="50" class="ml-2 w-16 rounded-md border-slate-300 text-sm"></label></div><div id="top-students-list" class="mt-3"></div></section>
        </div>
    </main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const e = { start: document.getElementById('start_date'), end: document.getElementById('end_date'), subject: document.getElementById('subject'), filter: document.getElementById('filter_btn'), search: document.getElementById('search-student'), loader: document.getElementById('loader-container'), content: document.getElementById('main-content'), feedback: document.getElementById('feedback'), period: document.getElementById('period-label'), thead: document.getElementById('report-thead'), tbody: document.getElementById('report-tbody'), empty: document.getElementById('empty-state'), count: document.getElementById('student-count-container'), topN: document.getElementById('top-n-students'), topList: document.getElementById('top-students-list') };
    let subjectsChart, daysChart, analytics = { topStudents: [] };
    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' }[char]));
    const number = value => new Intl.NumberFormat('es-CO').format(value || 0);
    function feedback(message) { e.feedback.textContent = message; e.feedback.className = 'rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700'; e.feedback.classList.remove('hidden'); }
    function setKpi(id, value) { document.getElementById(id).textContent = number(value); }
    function updateSubjects(subjects) { const selected = e.subject.value; e.subject.innerHTML = '<option value="">Todas las asignaturas con registros</option>' + subjects.map(subject => `<option value="${escapeHtml(subject)}">${escapeHtml(subject)}</option>`).join(''); if (subjects.includes(selected)) e.subject.value = selected; }
    function renderTable(students) {
        e.thead.innerHTML = '<tr><th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Estudiante</th><th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-slate-500">Registros</th><th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-emerald-600">Presente</th><th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-rose-600">Ausente</th><th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-amber-600">Permiso</th><th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-wide text-blue-600">Retardo</th><th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Asistencia</th></tr>';
        if (!students.length) { e.tbody.innerHTML = ''; e.empty.classList.remove('hidden'); e.count.textContent = '0 estudiantes'; return; }
        e.empty.classList.add('hidden'); e.count.textContent = `${number(students.length)} estudiante${students.length === 1 ? '' : 's'}`;
        e.tbody.innerHTML = students.map(student => `<tr class="student-row transition hover:bg-slate-50" data-name="${escapeHtml(student.name.toLowerCase())}"><td class="px-5 py-3"><p class="text-sm font-bold text-slate-800">${escapeHtml(student.name)}</p><p class="mt-0.5 text-xs text-slate-500">Grado: ${escapeHtml(student.grade)}</p></td><td class="px-4 py-3 text-center text-sm font-semibold text-slate-700">${number(student.records)}</td><td class="px-4 py-3 text-center text-sm font-bold text-emerald-600">${number(student.presences)}</td><td class="px-4 py-3 text-center text-sm font-bold text-rose-600">${number(student.absences)}</td><td class="px-4 py-3 text-center text-sm font-bold text-amber-600">${number(student.permissions)}</td><td class="px-4 py-3 text-center text-sm font-bold text-blue-600">${number(student.lates)}</td><td class="px-5 py-3 text-right"><span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-extrabold text-slate-700">${student.attendanceRate}%</span></td></tr>`).join('');
    }
    function renderCharts(data) {
        if (subjectsChart) subjectsChart.destroy(); if (daysChart) daysChart.destroy();
        const options = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { precision: 0 }, grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } };
        subjectsChart = new Chart(document.getElementById('subjectsChart'), { type: 'bar', data: { labels: Object.keys(data.absencesBySubject), datasets: [{ data: Object.values(data.absencesBySubject), backgroundColor: '#fb7185', borderRadius: 6, maxBarThickness: 42 }] }, options: { ...options, indexAxis: 'y' } });
        daysChart = new Chart(document.getElementById('daysChart'), { type: 'bar', data: { labels: Object.keys(data.absencesByDay), datasets: [{ data: Object.values(data.absencesByDay), backgroundColor: '#60a5fa', borderRadius: 6, maxBarThickness: 42 }] }, options });
    }
    function renderTop() { const maximum = Math.max(1, parseInt(e.topN.value, 10) || 5), students = (analytics.topStudents || []).slice(0, maximum); e.topList.innerHTML = students.length ? `<ol class="divide-y divide-slate-100">${students.map((student, index) => `<li class="flex items-center justify-between gap-4 py-3"><div class="flex items-center gap-3"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-extrabold text-slate-600">${index + 1}</span><div><p class="text-sm font-bold text-slate-800">${escapeHtml(student.name)}</p><p class="text-xs text-slate-500">Grado: ${escapeHtml(student.grade)}</p></div></div><span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-extrabold text-rose-700">${number(student.count)} falta${student.count === 1 ? '' : 's'}</span></li>`).join('')}</ol>` : '<p class="py-4 text-center text-sm text-slate-500">No hay ausencias en el período seleccionado.</p>'; }
    function render(data) { analytics = data.analytics; updateSubjects(data.subjects); renderTable(data.students); setKpi('total-records-kpi', analytics.totalRecords); setKpi('students-kpi', analytics.studentsWithAttendance); setKpi('presences-kpi', analytics.totalPresences); setKpi('absences-kpi', analytics.totalAbsences); setKpi('permissions-kpi', analytics.totalPermissions); setKpi('lates-kpi', analytics.totalLates); e.period.textContent = `Período consultado: ${data.period.start} a ${data.period.end}.`; renderCharts(analytics); renderTop(); }
    async function load() {
        if (e.start.value > e.end.value) { feedback('La fecha inicial no puede ser posterior a la fecha final.'); return; }
        e.loader.classList.remove('hidden'); e.content.classList.add('hidden'); e.feedback.classList.add('hidden');
        const url = new URL(window.location.href);
        url.searchParams.set('guagua_embedded', '1');
        url.searchParams.set('action', 'fetch_report_data');
        url.searchParams.set('start_date', e.start.value); url.searchParams.set('end_date', e.end.value);
        if (e.subject.value) url.searchParams.set('subject', e.subject.value); else url.searchParams.delete('subject');
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' }), result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'No fue posible actualizar el informe.');
            render(result.data); e.content.classList.remove('hidden');
        } catch (error) { feedback(error.message || 'Error de conexión al actualizar el informe.'); }
        finally { e.loader.classList.add('hidden'); }
    }
    e.filter.addEventListener('click', load); e.subject.addEventListener('change', load); e.topN.addEventListener('input', renderTop); e.search.addEventListener('input', () => { const term = e.search.value.trim().toLowerCase(); let visible = 0; document.querySelectorAll('.student-row').forEach(row => { const show = row.dataset.name.includes(term); row.classList.toggle('hidden', !show); if (show) visible++; }); const total = document.querySelectorAll('.student-row').length; if (total) e.count.textContent = `Mostrando ${visible} de ${total} estudiantes`; }); load();
});
</script>
</body>
</html>
