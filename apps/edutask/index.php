<?php
// ==========================================
// CONFIGURACIÓN DE BASE DE DATOS Y BACKEND
// ==========================================
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'edutask_db';
$port = 7000;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $mysqli = new mysqli($host, $user, $pass, "", $port);
    $mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $mysqli->select_db($dbname);

    // Tabla de Materias
    $mysqli->query("CREATE TABLE IF NOT EXISTS subjects (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        color VARCHAR(20) NOT NULL
    )");

    // Tabla de Tareas
    $mysqli->query("CREATE TABLE IF NOT EXISTS tasks (
        id VARCHAR(50) PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        subjectId VARCHAR(50),
        ownerType VARCHAR(50),
        ownerName VARCHAR(100),
        grades TEXT,
        due_date DATE,
        description LONGTEXT,
        completed TINYINT(1) DEFAULT 0
    )");

    // Materias por defecto
    $result = $mysqli->query("SELECT COUNT(*) as count FROM subjects");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $default_subjects = [
            ['1', 'Matemáticas', '#EF4444'], ['2', 'Ciencias Sociales', '#F59E0B'],
            ['3', 'Geometría', '#10B981'], ['4', 'Física', '#3B82F6'],
            ['5', 'Urbanidad', '#8B5CF6'], ['6', 'Educación Física', '#EC4899'],
            ['7', 'Tecnología', '#6366F1'], ['8', 'Ciencias Políticas', '#14B8A6'],
            ['9', 'Emprendimiento', '#F97316']
        ];
        $stmt = $mysqli->prepare("INSERT INTO subjects (id, name, color) VALUES (?, ?, ?)");
        foreach ($default_subjects as $s) {
            $stmt->bind_param("sss", $s[0], $s[1], $s[2]);
            $stmt->execute();
        }
    }
} catch (Exception $e) {
    if (!isset($_GET['action'])) die("Error de conexión: " . $e->getMessage());
}

// API HANDLER
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];
    try {
        if ($action === 'get_all') {
            $subjects = [];
            $res = $mysqli->query("SELECT * FROM subjects ORDER BY name ASC");
            while ($r = $res->fetch_assoc()) $subjects[] = $r;
            $tasks = [];
            $res = $mysqli->query("SELECT id, title, subjectId, ownerType, ownerName, grades, due_date as date, description, completed FROM tasks");
            while ($r = $res->fetch_assoc()) {
                $r['completed'] = (bool)$r['completed'];
                $r['grades'] = json_decode($r['grades'], true) ?: [];
                $tasks[] = $r;
            }
            echo json_encode(['subjects' => $subjects, 'tasks' => $tasks]);
        }
        if ($action === 'save_task') {
            $data = json_decode(file_get_contents('php://input'), true);
            $grades = json_encode($data['grades']);
            $comp = $data['completed'] ? 1 : 0;
            $stmt = $mysqli->prepare("INSERT INTO tasks (id, title, subjectId, ownerType, ownerName, grades, due_date, description, completed) VALUES (?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE title=VALUES(title), subjectId=VALUES(subjectId), ownerType=VALUES(ownerType), ownerName=VALUES(ownerName), grades=VALUES(grades), due_date=VALUES(due_date), description=VALUES(description), completed=VALUES(completed)");
            $stmt->bind_param("ssssssssi", $data['id'], $data['title'], $data['subjectId'], $data['ownerType'], $data['ownerName'], $grades, $data['date'], $data['description'], $comp);
            echo json_encode(['success' => $stmt->execute()]);
        }
        if ($action === 'delete_task') {
            $stmt = $mysqli->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->bind_param("s", $_GET['id']);
            echo json_encode(['success' => $stmt->execute()]);
        }
        if ($action === 'toggle_task') {
            $stmt = $mysqli->prepare("UPDATE tasks SET completed = NOT completed WHERE id = ?");
            $stmt->bind_param("s", $_GET['id']);
            echo json_encode(['success' => $stmt->execute()]);
        }
        if ($action === 'save_subject') {
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $mysqli->prepare("INSERT INTO subjects (id, name, color) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $data['id'], $data['name'], $data['color']);
            echo json_encode(['success' => $stmt->execute()]);
        }
        if ($action === 'delete_subject') {
            $stmt = $mysqli->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->bind_param("s", $_GET['id']);
            echo json_encode(['success' => $stmt->execute()]);
        }
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduTask Pro - Andrés Paz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .ql-container { height: 180px; font-family: inherit; font-size: 0.875rem; border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
        .ql-toolbar { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; background: #f8fafc; }
        .task-card, .subject-card { cursor: pointer; transition: 0.2s; border-left-width: 6px; }
        .task-card:hover, .subject-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .nav-active { background-color: #4f46e5; color: white; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden">

    <!-- Pantalla de Carga -->
    <div id="loading-overlay" class="fixed inset-0 bg-white z-[100] flex flex-col items-center justify-center">
        <i class="fa-solid fa-graduation-cap fa-bounce text-5xl text-indigo-600 mb-4"></i>
        <p class="text-slate-500 font-medium">Sincronizando EduTask Pro...</p>
    </div>

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col shrink-0 md:translate-x-0 -translate-x-full absolute md:relative z-40 h-full transition-transform" id="sidebar">
        <div class="p-6">
            <h1 class="text-2xl font-bold flex items-center gap-2"><i class="fa-solid fa-layer-group text-indigo-400"></i> EduTask</h1>
        </div>
        <nav class="flex-1 px-4 space-y-1">
            <button onclick="switchView('dashboard')" id="nav-dashboard" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800"><i class="fa-solid fa-home w-5"></i> Diario</button>
            <button onclick="switchView('weekly')" id="nav-weekly" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800"><i class="fa-solid fa-calendar-week w-5"></i> Semanal</button>
            <button onclick="switchView('monthly')" id="nav-monthly" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800"><i class="fa-solid fa-calendar-alt w-5"></i> Mensual</button>
            <button onclick="switchView('range')" id="nav-range" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800"><i class="fa-solid fa-arrows-left-right w-5"></i> Rango</button>
            <button onclick="switchView('completed')" id="nav-completed" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800"><i class="fa-solid fa-check-circle w-5 text-green-400"></i> Completados</button>
            <hr class="border-slate-800 my-4">
            <button onclick="switchView('subjects')" id="nav-subjects" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-lg text-slate-400 hover:bg-slate-800"><i class="fa-solid fa-book w-5"></i> Materias</button>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="bg-white h-16 border-b flex items-center justify-between px-6 shrink-0">
            <h2 id="view-title" class="text-xl font-bold text-slate-700">Resumen Diario</h2>
            <div class="flex items-center gap-3">
                <button onclick="openTaskModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> <span class="hidden sm:inline">Nueva Tarea</span>
                </button>
                <button class="md:hidden text-slate-500" onclick="toggleSidebar()"><i class="fa-solid fa-bars text-xl"></i></button>
            </div>
        </header>

        <!-- Filtros Globales -->
        <div id="filter-bar" class="bg-white border-b px-6 py-3 flex flex-wrap gap-4 items-center shrink-0">
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-search absolute left-3 top-3 text-slate-400"></i>
                <input type="text" id="search" onkeyup="updateFilters('query', this.value)" placeholder="Buscar por nombre o descripción..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
            </div>
            <select id="f-subject" onchange="updateFilters('subject', this.value)" class="border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                <option value="all">Todas las materias</option>
            </select>
            <div id="range-picker" class="hidden flex items-center gap-2">
                <input type="date" id="r-start" onchange="updateFilters('start', this.value)" class="border rounded-lg px-2 py-1.5 text-sm">
                <span class="text-slate-400">-</span>
                <input type="date" id="r-end" onchange="updateFilters('end', this.value)" class="border rounded-lg px-2 py-1.5 text-sm">
            </div>
        </div>

        <!-- Contenedor de Contenido -->
        <div id="content" class="flex-1 overflow-y-auto p-6"></div>
    </main>

    <!-- Modal Tarea -->
    <div id="modal-task" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[95vh]">
            <div class="p-4 border-b bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-lg" id="modal-title">Nueva Tarea</h3>
                <button onclick="closeTaskModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form id="task-form" class="p-6 space-y-4 overflow-y-auto">
                <input type="hidden" id="t-id">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-xs font-black uppercase text-slate-400 mb-1">Título de la actividad</label>
                        <input type="text" id="t-title" required class="w-full border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-1">Materia</label>
                        <select id="t-subject" required class="w-full border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500"></select>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-1">Fecha Límite</label>
                        <input type="date" id="t-date" required class="w-full border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-1">Responsable</label>
                        <select id="t-owner" class="w-full border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="Docente">Docente</option>
                            <option value="Estudiante">Estudiante</option>
                            <option value="Ambos">Ambos</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-1">Grados Aplicables</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <?php for($i=6; $i<=11; $i++) echo "<label class='flex items-center gap-1 text-xs cursor-pointer'><input type='checkbox' value='{$i}' class='t-grade w-4 h-4 text-indigo-600'> {$i}°</label>"; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-1">Descripción Detallada</label>
                    <div id="editor-container"></div>
                </div>
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <button type="button" onclick="closeTaskModal()" class="px-4 py-2 text-slate-500 hover:text-slate-700 font-bold">Cancelar</button>
                    <button type="submit" class="bg-indigo-600 text-white px-8 py-2 rounded-lg font-bold shadow-lg hover:bg-indigo-700 transition">Guardar Actividad</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Materia -->
    <div id="modal-subject" class="fixed inset-0 bg-black/50 z-[55] hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-sm rounded-xl shadow-2xl overflow-hidden">
            <div class="p-4 border-b bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-lg">Nueva Materia</h3>
                <button onclick="document.getElementById('modal-subject').classList.add('hidden')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-times text-xl"></i></button>
            </div>
            <form id="subject-form" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-1">Nombre</label>
                    <input type="text" id="s-name" required class="w-full border rounded-lg px-3 py-2 outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Ej. Geopolítica">
                </div>
                <div>
                    <label class="block text-xs font-black uppercase text-slate-400 mb-1">Color Identificador</label>
                    <input type="color" id="s-color" value="#6366f1" class="w-full h-10 border rounded-lg cursor-pointer">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg font-bold shadow-lg hover:bg-indigo-700 transition">Crear Materia</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detalle -->
    <div id="modal-view" class="fixed inset-0 bg-black/60 z-[60] hidden flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div id="view-header" class="p-8 text-white relative">
                <button onclick="document.getElementById('modal-view').classList.add('hidden')" class="absolute top-4 right-4 text-white/60 hover:text-white transition"><i class="fa-solid fa-times text-2xl"></i></button>
                <div id="v-subject-badge" class="inline-block text-[10px] font-black uppercase tracking-widest bg-white/20 px-3 py-1 rounded-full mb-3">MATERIA</div>
                <h3 id="v-title" class="text-3xl font-bold leading-tight">Título</h3>
            </div>
            <div class="p-8 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-slate-400 font-black uppercase text-[10px] mb-1">Fecha Entrega</p>
                        <p id="v-date" class="font-bold text-slate-700 text-base"></p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-slate-400 font-black uppercase text-[10px] mb-1">Responsable</p>
                        <p id="v-owner" class="font-bold text-slate-700 text-base"></p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <p class="text-slate-400 font-black uppercase text-[10px] mb-1">Grados</p>
                        <p id="v-grades" class="font-bold text-slate-700 text-base"></p>
                    </div>
                </div>
                <div>
                    <h4 class="font-black text-slate-400 uppercase text-[10px] mb-3 border-b pb-1">Instrucciones / Descripción</h4>
                    <div id="v-desc" class="prose prose-sm max-w-none text-slate-600 max-h-60 overflow-y-auto"></div>
                </div>
                <div class="pt-6 flex justify-between items-center border-t border-slate-100">
                    <button id="v-btn-delete" class="text-red-400 hover:text-red-600 font-bold text-sm flex items-center gap-2 transition"><i class="fa-solid fa-trash"></i> Eliminar Actividad</button>
                    <button id="v-btn-edit" class="bg-indigo-50 text-indigo-600 px-6 py-2 rounded-xl font-bold text-sm hover:bg-indigo-100 transition border border-indigo-100">Editar Información</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let state = { subjects: [], tasks: [], view: 'dashboard', filters: { query: '', subject: 'all', start: '', end: '' } };
        let quill;

        async function init() {
            quill = new Quill('#editor-container', { 
                theme: 'snow', 
                placeholder: 'Describe las actividades y materiales...',
                modules: { toolbar: [['bold', 'italic'], [{ list: 'ordered' }, { list: 'bullet' }], ['clean']] } 
            });
            await refreshData();
            switchView('dashboard');
            document.getElementById('loading-overlay').classList.add('hidden');
        }

        async function refreshData() {
            const data = await fetchAPI('get_all');
            state.subjects = data.subjects;
            state.tasks = data.tasks;
            updateSubjectDropdowns();
        }

        async function fetchAPI(action, data = null) {
            try {
                const opt = { method: data ? 'POST' : 'GET', body: data ? JSON.stringify(data) : null };
                const res = await fetch(`?action=${action}`, opt);
                if (!res.ok) throw new Error("Error en servidor");
                return res.json();
            } catch (e) {
                console.error(e);
                return { success: false, subjects: [], tasks: [] };
            }
        }

        function switchView(v) {
            state.view = v;
            document.querySelectorAll('.nav-btn').forEach(b => {
                b.classList.remove('nav-active', 'text-white');
                b.classList.add('text-slate-400');
            });
            const active = document.getElementById(`nav-${v}`);
            active.classList.add('nav-active', 'text-white');
            active.classList.remove('text-slate-400');
            
            document.getElementById('view-title').innerText = active.innerText.trim();
            document.getElementById('filter-bar').classList.toggle('hidden', v === 'subjects');
            renderView();
        }

        function updateFilters(key, val) { 
            state.filters[key] = val; 
            renderView(); 
        }

        function renderView() {
            const container = document.getElementById('content');
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('range-picker').classList.toggle('hidden', state.view !== 'range');
            
            if (state.view === 'subjects') return renderSubjects();

            let tasks = state.tasks.filter(t => {
                const matchSearch = t.title.toLowerCase().includes(state.filters.query.toLowerCase()) || 
                                    (t.description || '').toLowerCase().includes(state.filters.query.toLowerCase());
                const matchSubject = state.filters.subject === 'all' || t.subjectId === state.filters.subject;
                const matchStatus = (state.view === 'completed') ? t.completed : !t.completed;
                
                if (!matchSearch || !matchSubject || !matchStatus) return false;

                if (state.view === 'dashboard') return t.date === today;
                if (state.view === 'weekly') {
                    const d = new Date(t.date);
                    const now = new Date();
                    const weekStart = new Date(now.setDate(now.getDate() - now.getDay() + 1));
                    const weekEnd = new Date(now.setDate(now.getDate() - now.getDay() + 7));
                    return d >= weekStart && d <= weekEnd;
                }
                if (state.view === 'monthly') {
                    const d = new Date(t.date);
                    const now = new Date();
                    return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
                }
                if (state.view === 'range') {
                    return (!state.filters.start || t.date >= state.filters.start) && (!state.filters.end || t.date <= state.filters.end);
                }
                return true; 
            });

            if (!tasks.length) {
                container.innerHTML = `<div class="flex flex-col items-center justify-center h-full text-slate-300 animate-pulse"><i class="fa-solid fa-folder-open text-6xl mb-4"></i><p class="font-bold">No hay actividades programadas</p></div>`;
                return;
            }

            container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">${tasks.map(t => {
                const sub = state.subjects.find(s => s.id === t.subjectId) || {color: '#cbd5e1', name: 'S/N'};
                return `
                <div class="task-card bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-4 animate-in slide-in-from-bottom-2 duration-300" style="border-left-color: ${sub.color}" onclick="viewTask('${t.id}')">
                    <div class="flex justify-between items-center">
                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-full" style="background: ${sub.color}20; color: ${sub.color}">${sub.name}</span>
                        <input type="checkbox" ${t.completed ? 'checked' : ''} onclick="event.stopPropagation(); toggleTask('${t.id}')" class="w-6 h-6 rounded-lg border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </div>
                    <h4 class="font-bold text-slate-800 text-lg leading-snug line-clamp-2">${t.title}</h4>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 mt-auto pt-4 border-t border-slate-50">
                        <span class="flex items-center gap-1.5 font-bold"><i class="fa-regular fa-calendar-check text-indigo-400"></i> ${t.date}</span>
                        <span class="flex items-center gap-1.5"><i class="fa-solid fa-user-graduate"></i> ${t.ownerType}</span>
                    </div>
                </div>`;
            }).join('')}</div>`;
        }

        function renderSubjects() {
            const container = document.getElementById('content');
            container.innerHTML = `
                <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-700">Asignaturas Registradas</h3>
                        <p class="text-sm text-slate-400">Gestiona las materias y visualiza la carga académica.</p>
                    </div>
                    <button onclick="document.getElementById('modal-subject').classList.remove('hidden')" class="bg-indigo-50 text-indigo-600 border border-indigo-100 px-4 py-2 rounded-xl font-bold flex items-center gap-2 hover:bg-indigo-100 transition shadow-sm w-full sm:w-auto justify-center">
                        <i class="fa-solid fa-plus-circle"></i> Nueva Materia
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    ${state.subjects.map(s => {
                        const count = state.tasks.filter(t => t.subjectId === s.id).length;
                        return `
                        <div class="subject-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden flex flex-col items-center text-center animate-in zoom-in-95 duration-200" style="border-left-color: ${s.color}">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4" style="background: ${s.color}15">
                                <i class="fa-solid fa-book-open text-2xl" style="color: ${s.color}"></i>
                            </div>
                            <h4 class="font-bold text-slate-800 text-lg mb-1">${s.name}</h4>
                            <p class="text-xs text-slate-400 font-medium">${count} Actividades Totales</p>
                            <button onclick="deleteSubject('${s.id}')" class="absolute top-3 right-3 text-slate-300 hover:text-red-400 transition">
                                <i class="fa-solid fa-trash-can text-sm"></i>
                            </button>
                        </div>`;
                    }).join('')}
                </div>
            `;
        }

        async function toggleTask(id) {
            await fetchAPI(`toggle_task&id=${id}`);
            const task = state.tasks.find(t => t.id === id);
            if (task) {
                task.completed = !task.completed;
                renderView();
            }
        }

        function viewTask(id) {
            const t = state.tasks.find(x => x.id === id);
            const sub = state.subjects.find(s => s.id === t.subjectId) || {color: '#6366f1', name: 'Indefinida'};
            
            document.getElementById('v-title').innerText = t.title;
            document.getElementById('v-subject-badge').innerText = sub.name;
            document.getElementById('v-date').innerText = t.date;
            document.getElementById('v-owner').innerText = t.ownerName || t.ownerType;
            document.getElementById('v-grades').innerText = (t.grades || []).length ? t.grades.join(', ') + '°' : 'Todos';
            document.getElementById('v-desc').innerHTML = t.description || '<p class="italic text-slate-400">Sin descripción adicional.</p>';
            
            document.getElementById('view-header').style.backgroundColor = sub.color;
            document.getElementById('modal-view').classList.remove('hidden');
            
            document.getElementById('v-btn-delete').onclick = () => deleteTask(id);
            document.getElementById('v-btn-edit').onclick = () => { 
                document.getElementById('modal-view').classList.add('hidden'); 
                openTaskModal(id); 
            };
        }

        async function deleteTask(id) {
            if (confirm('¿Estás seguro de eliminar esta actividad definitivamente?')) {
                await fetchAPI(`delete_task&id=${id}`);
                state.tasks = state.tasks.filter(t => t.id !== id);
                document.getElementById('modal-view').classList.add('hidden');
                renderView();
            }
        }

        async function deleteSubject(id) {
            const hasTasks = state.tasks.some(t => t.subjectId === id);
            if (hasTasks) return alert("No puedes eliminar una materia con tareas asignadas. Elimina las tareas primero.");
            
            if (confirm('¿Eliminar esta asignatura?')) {
                await fetchAPI(`delete_subject&id=${id}`);
                state.subjects = state.subjects.filter(s => s.id !== id);
                renderView();
                updateSubjectDropdowns();
            }
        }

        function openTaskModal(id = null) {
            document.getElementById('modal-task').classList.remove('hidden');
            const form = document.getElementById('task-form');
            form.reset();
            quill.root.innerHTML = '';
            document.getElementById('t-id').value = id || '';
            document.getElementById('modal-title').innerText = id ? 'Editar Actividad' : 'Nueva Actividad Académica';
            
            if (id) {
                const t = state.tasks.find(x => x.id === id);
                document.getElementById('t-title').value = t.title;
                document.getElementById('t-subject').value = t.subjectId;
                document.getElementById('t-date').value = t.date;
                document.getElementById('t-owner').value = t.ownerType;
                quill.root.innerHTML = t.description;
                document.querySelectorAll('.t-grade').forEach(cb => cb.checked = t.grades.includes(cb.value));
            }
        }

        function closeTaskModal() { 
            document.getElementById('modal-task').classList.add('hidden'); 
        }

        document.getElementById('task-form').onsubmit = async (e) => {
            e.preventDefault();
            const id = document.getElementById('t-id').value || Math.random().toString(36).substr(2, 9);
            const taskData = {
                id,
                title: document.getElementById('t-title').value,
                subjectId: document.getElementById('t-subject').value,
                date: document.getElementById('t-date').value,
                ownerType: document.getElementById('t-owner').value,
                ownerName: '',
                description: quill.root.innerHTML,
                grades: Array.from(document.querySelectorAll('.t-grade:checked')).map(cb => cb.value),
                completed: false
            };
            
            const idx = state.tasks.findIndex(t => t.id === id);
            if (idx > -1) {
                taskData.completed = state.tasks[idx].completed;
                state.tasks[idx] = taskData;
            } else state.tasks.push(taskData);
            
            await fetchAPI('save_task', taskData);
            closeTaskModal();
            renderView();
        };

        document.getElementById('subject-form').onsubmit = async (e) => {
            e.preventDefault();
            const subData = {
                id: Math.random().toString(36).substr(2, 9),
                name: document.getElementById('s-name').value,
                color: document.getElementById('s-color').value
            };
            await fetchAPI('save_subject', subData);
            state.subjects.push(subData);
            document.getElementById('modal-subject').classList.add('hidden');
            renderView();
            updateSubjectDropdowns();
        };

        function updateSubjectDropdowns() {
            const html = state.subjects.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
            document.getElementById('t-subject').innerHTML = html;
            document.getElementById('f-subject').innerHTML = '<option value="all">Todas las materias</option>' + html;
        }

        function toggleSidebar() { 
            document.getElementById('sidebar').classList.toggle('-translate-x-full'); 
        }

        window.onload = init;
    </script>
</body>
</html>