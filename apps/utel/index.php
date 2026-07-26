<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctorado UTEL - Andrés Paz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <!-- Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .ql-container { height: 180px; font-family: inherit; font-size: 0.875rem; border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
        .ql-toolbar { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; background: #f8fafc; }
        .task-card, .subject-card { cursor: pointer; transition: 0.2s; border-left-width: 6px; }
        .task-card:hover, .subject-card:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden font-sans">

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 bg-slate-900 text-white flex flex-col transition-transform duration-300 absolute inset-y-0 left-0 z-20 md:relative md:translate-x-0 -translate-x-full">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <h1 class="text-xl font-bold tracking-tight"><i class="fas fa-graduation-cap text-blue-400 mr-2"></i>UTEL Docs</h1>
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400"><i class="fas fa-times"></i></button>
        </div>
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <button onclick="changeView('dashboard')" id="nav-dashboard" class="w-full text-left px-4 py-2 rounded-lg bg-blue-600 font-medium transition-colors"><i class="fas fa-chart-pie w-6"></i> Dashboard</button>
            <button onclick="changeView('tasks')" id="nav-tasks" class="w-full text-left px-4 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-colors"><i class="fas fa-tasks w-6"></i> Tareas & Clases</button>
            <button onclick="changeView('subjects')" id="nav-subjects" class="w-full text-left px-4 py-2 rounded-lg text-slate-300 hover:bg-slate-800 font-medium transition-colors"><i class="fas fa-book w-6"></i> Materias</button>
        </nav>
        <div class="p-4 border-t border-slate-800 text-xs text-slate-400 text-center">
            Andrés Paz &copy; 2026
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50 relative w-full">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center z-10">
            <div class="flex items-center">
                <button onclick="toggleSidebar()" class="md:hidden text-slate-600 mr-4"><i class="fas fa-bars text-xl"></i></button>
                <h2 id="view-title" class="text-2xl font-bold text-slate-800">Dashboard</h2>
            </div>
            <div class="flex space-x-2">
                <button onclick="openConfigModal()" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center shadow-sm">
                    <i class="fas fa-cog mr-2"></i> Config.
                </button>
                <button onclick="openImportModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center">
                    <i class="fas fa-file-import mr-2"></i> Importar
                </button>
                <button onclick="openTaskModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center shadow-sm">
                    <i class="fas fa-plus mr-2"></i> Tarea
                </button>
                <button onclick="openSubjectModal()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center shadow-sm hidden md:flex">
                    <i class="fas fa-folder-plus mr-2"></i> Materia
                </button>
            </div>
        </header>

        <div id="content-area" class="flex-1 overflow-y-auto p-6">
            <!-- Dynamic Content Injected Here -->
        </div>
    </main>

    <!-- Modals -->
    
    <!-- Config Modal -->
    <div id="config-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800"><i class="fas fa-cogs text-slate-600 mr-2"></i>Configuración del Sistema</h3>
                <button onclick="closeConfigModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-blue-50 border border-blue-200 text-blue-800 p-3 rounded-lg text-xs mb-2">
                    Para sincronizar directamente con Google Calendar mediante API, ingresa el <strong>Client ID</strong> generado en tu Google Cloud Console. <br><br>Asegúrate de haber añadido la URI de este sistema en "Orígenes de JavaScript autorizados".
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Google OAuth Client ID</label>
                    <input type="text" id="cfg-client-id" placeholder="ej. 123456789-abc...apps.googleusercontent.com" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none font-mono text-xs">
                </div>
            </div>
            <div class="p-4 border-t bg-slate-50 flex justify-end space-x-3">
                <button onclick="closeConfigModal()" class="text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-lg font-medium transition-colors">Cerrar</button>
                <button onclick="saveConfig()" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2 rounded-lg font-medium transition-colors">Guardar Configuración</button>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="task-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 id="t-modal-title" class="text-xl font-bold text-slate-800">Nueva Tarea/Clase</h3>
                <button onclick="closeTaskModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <input type="hidden" id="t-id">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Título</label>
                    <input type="text" id="t-title" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Materia</label>
                        <select id="t-subject" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none"></select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Tipo</label>
                        <select id="t-type" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="tarea">Tarea</option>
                            <option value="clase">Clase (OpenClass)</option>
                            <option value="examen">Examen</option>
                            <option value="foro">Foro</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Fecha Límite / Cita</label>
                        <input type="datetime-local" id="t-date" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Estado</label>
                        <select id="t-status" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="pendiente">Pendiente</option>
                            <option value="completado">Completado</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Apuntes / Enlaces</label>
                    <div id="t-editor"></div>
                </div>
            </div>
            <div class="p-4 border-t bg-slate-50 flex justify-end space-x-3">
                <button id="t-delete-btn" onclick="deleteTask()" class="hidden bg-red-100 hover:bg-red-200 text-red-600 px-4 py-2 rounded-lg font-medium transition-colors mr-auto">Eliminar</button>
                <button onclick="closeTaskModal()" class="text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-lg font-medium transition-colors">Cancelar</button>
                <button onclick="saveTask()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Subject Modal -->
    <div id="subject-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50">
                <h3 class="text-xl font-bold text-slate-800">Nueva Materia</h3>
                <button onclick="closeSubjectModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>
            <div class="p-6 space-y-4">
                <div id="subject-error" class="hidden bg-red-100 text-red-600 p-3 rounded-lg text-sm"></div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Nombre de la Materia</label>
                    <input type="text" id="s-name" class="w-full border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Color Identificador</label>
                    <input type="color" id="s-color" value="#3b82f6" class="w-full h-12 rounded cursor-pointer border-0 p-0">
                </div>
            </div>
            <div class="p-4 border-t bg-slate-50 flex justify-end space-x-3">
                <button onclick="closeSubjectModal()" class="text-slate-600 hover:bg-slate-200 px-4 py-2 rounded-lg font-medium transition-colors">Cancelar</button>
                <button onclick="saveSubject()" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-2 rounded-lg font-medium transition-colors">Crear</button>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div id="import-modal" class="hidden fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[95vh]">
            <div class="p-6 border-b flex justify-between items-center bg-slate-50 flex-shrink-0">
                <h3 class="text-xl font-bold text-slate-800"><i class="fas fa-file-import text-emerald-600 mr-2"></i>Importar Eventos UTEL</h3>
                <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 transition-colors"><i class="fas fa-times text-xl"></i></button>
            </div>
            
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 overflow-y-auto">
                <!-- Import via Google API -->
                <div class="border border-purple-200 rounded-xl p-4 bg-purple-50 flex flex-col md:col-span-2">
                    <h4 class="font-bold text-purple-800 mb-2"><i class="fas fa-cloud-download-alt mr-2"></i>Sincronización Directa (Google API)</h4>
                    <p class="text-xs text-purple-600 mb-3">Conecta tu cuenta de Google mediante OAuth 2.0 y extrae automáticamente los eventos que contengan "UTEL". Requiere configuración previa del Client ID en el panel.</p>
                    <button onclick="triggerGoogleSync()" class="w-full bg-purple-600 hover:bg-purple-700 text-white rounded-lg py-3 text-sm font-bold transition-colors mt-auto shadow-sm">
                        <i class="fab fa-google mr-2"></i> Autenticar y Sincronizar
                    </button>
                </div>

                <!-- Import TXT -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 flex flex-col">
                    <h4 class="font-bold text-slate-800 mb-2"><i class="fas fa-paste text-blue-500 mr-2"></i>Desde Consola / TXT</h4>
                    <p class="text-xs text-slate-500 mb-3">Pega el texto generado por el script scraper.</p>
                    <textarea id="import-txt" class="w-full flex-1 min-h-[120px] border border-slate-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none mb-3" placeholder="Título \t Semana \t Profesor..."></textarea>
                    <button onclick="importTxtData()" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2 text-sm font-semibold transition-colors mt-auto">Importar TXT</button>
                </div>

                <!-- Import ICS -->
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 flex flex-col">
                    <h4 class="font-bold text-slate-800 mb-2"><i class="far fa-calendar-alt text-red-500 mr-2"></i>Archivo Local (.ics)</h4>
                    <p class="text-xs text-slate-500 mb-3">Sube un archivo de Google Calendar (.ics).</p>
                    <div class="flex-1 flex flex-col justify-center mb-3">
                        <input type="file" id="import-ics" accept=".ics" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                    </div>
                    <button onclick="importIcsData()" class="w-full bg-red-600 hover:bg-red-700 text-white rounded-lg py-2 text-sm font-semibold transition-colors mt-auto">Importar ICS</button>
                </div>
            </div>
            
            <div class="px-6 pb-6 flex-shrink-0">
                <div id="import-log" class="bg-slate-900 rounded-lg p-3 text-sm text-green-400 max-h-32 overflow-y-auto empty:hidden font-mono text-xs"></div>
            </div>
        </div>
    </div>

    <script>
        // State Management
        let state = {
            view: 'dashboard',
            tasks: JSON.parse(localStorage.getItem('utel_tasks')) || [],
            subjects: JSON.parse(localStorage.getItem('utel_subjects')) || []
        };

        let quill;
        let tokenClient; // Google Token Client

        // Initialization
        function init() {
            quill = new Quill('#t-editor', {
                theme: 'snow',
                modules: { toolbar: [ ['bold', 'italic', 'underline'], [{'list': 'bullet'}], ['link'] ] }
            });
            updateSubjectDropdowns();
            renderView();
            initGoogleAPI(); // Inicializar GIS si existe Client ID
        }

        // Data Persistence
        const saveTasks = () => localStorage.setItem('utel_tasks', JSON.stringify(state.tasks));
        const saveSubjects = () => localStorage.setItem('utel_subjects', JSON.stringify(state.subjects));

        // Navigation
        function changeView(view) {
            state.view = view;
            ['dashboard', 'tasks', 'subjects'].forEach(v => {
                document.getElementById(`nav-${v}`).classList.remove('bg-blue-600', 'text-white');
                document.getElementById(`nav-${v}`).classList.add('text-slate-300', 'hover:bg-slate-800');
            });
            const activeNav = document.getElementById(`nav-${view}`);
            activeNav.classList.remove('text-slate-300', 'hover:bg-slate-800');
            activeNav.classList.add('bg-blue-600', 'text-white');
            
            const titles = { 'dashboard': 'Dashboard', 'tasks': 'Tareas & Clases', 'subjects': 'Materias' };
            document.getElementById('view-title').innerText = titles[view];
            
            if(window.innerWidth < 768) toggleSidebar();
            renderView();
        }

        // Rendering Logic
        function renderView() {
            const content = document.getElementById('content-area');
            if (state.view === 'dashboard') content.innerHTML = renderDashboard();
            else if (state.view === 'tasks') content.innerHTML = renderTasks();
            else if (state.view === 'subjects') content.innerHTML = renderSubjects();
        }

        // ------------------ Settings & Config ------------------ //
        function openConfigModal() {
            document.getElementById('cfg-client-id').value = localStorage.getItem('utel_gapi_client_id') || '';
            document.getElementById('config-modal').classList.remove('hidden');
        }

        function closeConfigModal() {
            document.getElementById('config-modal').classList.add('hidden');
        }

        function saveConfig() {
            const clientId = document.getElementById('cfg-client-id').value.trim();
            if (clientId) {
                localStorage.setItem('utel_gapi_client_id', clientId);
                initGoogleAPI(); // Reinicializar el cliente tras cambiar
            } else {
                localStorage.removeItem('utel_gapi_client_id');
            }
            closeConfigModal();
            alert('Configuración guardada.');
        }

        // ------------------ Google OAuth & API Logic ------------------ //
        function initGoogleAPI() {
            const clientId = localStorage.getItem('utel_gapi_client_id');
            if (clientId && typeof google !== 'undefined') {
                tokenClient = google.accounts.oauth2.initTokenClient({
                    client_id: clientId,
                    scope: 'https://www.googleapis.com/auth/calendar.readonly',
                    callback: (tokenResponse) => {
                        if (tokenResponse && tokenResponse.access_token) {
                            fetchCalendarEventsAPI(tokenResponse.access_token);
                        }
                    },
                });
            }
        }

        function triggerGoogleSync() {
            const clientId = localStorage.getItem('utel_gapi_client_id');
            if (!clientId || !tokenClient) {
                logImport("Error: No se ha configurado el Client ID de Google. Ve a Configuración.", true);
                return;
            }
            logImport("Solicitando permisos a Google...", false);
            tokenClient.requestAccessToken();
        }

        async function fetchCalendarEventsAPI(token) {
            logImport("Conexión OAuth exitosa. Consultando eventos...", false);
            try {
                // Limitamos la búsqueda a 250 eventos para no saturar, buscando la query "UTEL"
                const endpoint = `https://www.googleapis.com/calendar/v3/calendars/primary/events?q=UTEL&singleEvents=true&orderBy=startTime&maxResults=250`;
                
                const response = await fetch(endpoint, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
                
                const data = await response.json();
                const events = data.items || [];
                
                let imported = 0;
                let skipped = 0;
                
                events.forEach(ev => {
                    const isUtel = (ev.summary && ev.summary.toLowerCase().includes('utel')) || 
                                   (ev.description && ev.description.toLowerCase().includes('utel'));
                    
                    if (isUtel) {
                        const materiaName = "Sincronización API - UTEL"; 
                        // Formatear fecha al estándar esperado
                        let dueDate = '';
                        if (ev.start.dateTime) {
                            dueDate = ev.start.dateTime.substring(0, 16); // Formato YYYY-MM-DDTHH:mm
                        } else if (ev.start.date) {
                            dueDate = ev.start.date + 'T00:00';
                        }

                        const notes = ev.description ? ev.description.replace(/\n/g, '<br>') : '';
                        const extUrl = ev.htmlLink ? `<br><br><a href="${ev.htmlLink}" target="_blank">Ver en Calendar</a>` : '';
                        
                        if (processSingleImport(ev.summary, materiaName, dueDate, notes + extUrl, 'clase')) {
                            imported++;
                        } else {
                            skipped++;
                        }
                    }
                });
                
                logImport(`[API] Ejecución completada. <strong>Importados: ${imported}</strong> | Duplicados/Omitidos: ${skipped}`);
                renderView();
            } catch (err) {
                logImport(`Error al consultar API: ${err.message}`, true);
            }
        }

        // ------------------ Import Logic ------------------ //
        function openImportModal() {
            document.getElementById('import-modal').classList.remove('hidden');
            document.getElementById('import-log').innerHTML = '';
            document.getElementById('import-txt').value = '';
            document.getElementById('import-ics').value = '';
        }

        function closeImportModal() {
            document.getElementById('import-modal').classList.add('hidden');
        }

        function logImport(msg, isError = false) {
            const logDiv = document.getElementById('import-log');
            const color = isError ? 'text-red-400' : 'text-green-400';
            logDiv.innerHTML += `<div class="${color} mb-1 border-b border-slate-700 pb-1 last:border-0">> ${msg}</div>`;
            logDiv.scrollTop = logDiv.scrollHeight;
        }

        function parseTxtDate(fechaStr, horaStr) {
            try {
                const [d, m, y] = fechaStr.split('/');
                if(!d || !m || !y) return '';

                const timeClean = horaStr.trim().toLowerCase();
                let [time, ...rest] = timeClean.split(' ');
                let [hours, minutes] = time.split(':');
                
                let isPm = timeClean.includes('p. m.') || timeClean.includes('pm') || timeClean.includes('p.');
                
                hours = parseInt(hours, 10);
                if (hours === 12) { hours = isPm ? 12 : 0; }
                else if (isPm) { hours += 12; }
                
                const pad = n => n.toString().padStart(2, '0');
                return `${y}-${pad(m)}-${pad(d)}T${pad(hours)}:${minutes || '00'}`;
            } catch(e) {
                return '';
            }
        }

        function processSingleImport(title, subjectName, dueDate, notes, type = 'clase') {
            if (!title || !dueDate) return false;
            
            let subject = state.subjects.find(s => s.name.toLowerCase() === subjectName.toLowerCase());
            if (!subject) {
                subject = { 
                    id: 'sub_' + Date.now() + Math.random().toString(36).substr(2, 5), 
                    name: subjectName, 
                    color: '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0') 
                };
                state.subjects.push(subject);
                saveSubjects();
                updateSubjectDropdowns();
            }
            
            const isDuplicate = state.tasks.some(t => 
                t.title.toLowerCase() === title.toLowerCase() && 
                t.dueDate === dueDate
            );
            
            if (isDuplicate) return false;
            
            const newTask = {
                id: 'task_' + Date.now() + Math.random().toString(36).substr(2, 5),
                title: title,
                subjectId: subject.id,
                type: type,
                dueDate: dueDate,
                status: 'pendiente',
                notes: notes || ''
            };
            
            state.tasks.push(newTask);
            saveTasks();
            return true;
        }

        function importTxtData() {
            const text = document.getElementById('import-txt').value;
            if (!text.trim()) {
                logImport("El cuadro de texto está vacío.", true);
                return;
            }

            const lines = text.split('\n');
            let imported = 0;
            let skipped = 0;
            
            lines.forEach((line, index) => {
                if (index === 0 && line.toLowerCase().includes('título')) return;
                if (!line.trim()) return;
                
                const cols = line.split('\t');
                if (cols.length >= 6) {
                    const title = cols[0];
                    const materiaName = cols[3] || 'Materia General';
                    const fecha = cols[4];
                    const hora = cols[5];
                    const url = cols[6] || '';
                    
                    const dueDate = parseTxtDate(fecha, hora);
                    const notes = url ? `<a href="${url}" target="_blank">${url}</a>` : '';
                    
                    if (processSingleImport(title, materiaName, dueDate, notes, 'clase')) {
                        imported++;
                    } else {
                        skipped++;
                    }
                }
            });
            
            logImport(`[TXT] Proceso finalizado. <strong>Importados: ${imported}</strong> | Omitidos/Duplicados: ${skipped}`);
            renderView();
        }

        function importIcsData() {
            const fileInput = document.getElementById('import-ics');
            if (!fileInput.files.length) {
                logImport("Por favor selecciona un archivo .ics primero.", true);
                return;
            }
            
            const file = fileInput.files[0];
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const icsData = e.target.result;
                const events = parseICS(icsData);
                let imported = 0;
                let skipped = 0;
                
                events.forEach(ev => {
                    const isUtel = (ev.summary && ev.summary.toLowerCase().includes('utel')) || 
                                   (ev.description && ev.description.toLowerCase().includes('utel'));
                    
                    if (isUtel) {
                        const materiaName = "Google Calendar (.ics) - UTEL";
                        const dueDate = ev.startDate; 
                        
                        if (processSingleImport(ev.summary, materiaName, dueDate, ev.description, 'clase')) {
                            imported++;
                        } else {
                            skipped++;
                        }
                    }
                });
                
                logImport(`[ICS] Proceso finalizado. <strong>Importados: ${imported}</strong> | Omitidos/Duplicados: ${skipped}`);
                renderView();
            };
            reader.readAsText(file);
        }

        function parseICS(icsString) {
            const lines = icsString.split(/\r?\n/);
            const events = [];
            let currentEvent = null;
            
            for (let i = 0; i < lines.length; i++) {
                let line = lines[i];
                while (i + 1 < lines.length && lines[i+1].startsWith(' ')) {
                    i++;
                    line += lines[i].substring(1);
                }
                
                if (line === 'BEGIN:VEVENT') {
                    currentEvent = {};
                } else if (line === 'END:VEVENT') {
                    if (currentEvent) events.push(currentEvent);
                    currentEvent = null;
                } else if (currentEvent) {
                    if (line.startsWith('SUMMARY:')) {
                        currentEvent.summary = line.substring(8);
                    } else if (line.startsWith('DESCRIPTION:')) {
                        currentEvent.description = line.substring(12).replace(/\\n/g, '<br>');
                    } else if (line.startsWith('DTSTART')) {
                        const match = line.match(/:(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})/);
                        if (match) {
                            currentEvent.startDate = `${match[1]}-${match[2]}-${match[3]}T${match[4]}:${match[5]}`;
                        }
                    }
                }
            }
            return events;
        }

        // ------------------ Component Renderers ------------------ //
        function renderDashboard() {
            const pending = state.tasks.filter(t => t.status === 'pendiente');
            const completed = state.tasks.filter(t => t.status === 'completado');
            
            const upcoming = [...pending]
                .filter(t => t.dueDate)
                .sort((a,b) => new Date(a.dueDate) - new Date(b.dueDate))
                .slice(0, 5);

            return `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl mr-4"><i class="fas fa-clock"></i></div>
                        <div><p class="text-slate-500 text-sm font-semibold">Pendientes</p><h3 class="text-3xl font-bold text-slate-800">${pending.length}</h3></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mr-4"><i class="fas fa-check"></i></div>
                        <div><p class="text-slate-500 text-sm font-semibold">Completadas</p><h3 class="text-3xl font-bold text-slate-800">${completed.length}</h3></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 flex items-center">
                        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl mr-4"><i class="fas fa-book-open"></i></div>
                        <div><p class="text-slate-500 text-sm font-semibold">Materias</p><h3 class="text-3xl font-bold text-slate-800">${state.subjects.length}</h3></div>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-4">Próximos Vencimientos / Clases</h3>
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    ${upcoming.length ? upcoming.map(t => renderTaskRow(t)).join('') : '<div class="p-6 text-center text-slate-500">No hay tareas o clases próximas.</div>'}
                </div>
            `;
        }

        function renderTasks() {
            let html = `
                <div class="flex flex-col md:flex-row gap-4 mb-6">
                    <select id="f-subject" onchange="filterTasks()" class="border border-slate-300 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-blue-500 bg-white min-w-[200px]">
                        <option value="all">Todas las materias</option>
                        ${state.subjects.map(s => `<option value="${s.id}">${s.name}</option>`).join('')}
                    </select>
                    <select id="f-status" onchange="filterTasks()" class="border border-slate-300 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="all">Todos los estados</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="completado">Completados</option>
                    </select>
                </div>
                <div id="tasks-container" class="space-y-3">
                    ${generateTasksList(state.tasks)}
                </div>
            `;
            return html;
        }

        function generateTasksList(tasksList) {
            if(!tasksList.length) return `<div class="p-8 text-center text-slate-500 bg-white rounded-xl border border-slate-200 shadow-sm">No hay registros que coincidan con la búsqueda.</div>`;
            return tasksList.sort((a,b) => new Date(a.dueDate) - new Date(b.dueDate)).map(t => renderTaskRow(t)).join('');
        }

        function filterTasks() {
            const sFilter = document.getElementById('f-subject').value;
            const stFilter = document.getElementById('f-status').value;
            
            const filtered = state.tasks.filter(t => {
                const matchS = sFilter === 'all' || t.subjectId === sFilter;
                const matchSt = stFilter === 'all' || t.status === stFilter;
                return matchS && matchSt;
            });
            
            document.getElementById('tasks-container').innerHTML = generateTasksList(filtered);
        }

        function renderSubjects() {
            if(!state.subjects.length) return `<div class="p-8 text-center text-slate-500 bg-white rounded-xl border border-slate-200 shadow-sm">Aún no has registrado materias.</div>`;
            
            return `<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                ${state.subjects.map(s => {
                    const taskCount = state.tasks.filter(t => t.subjectId === s.id).length;
                    return `
                    <div class="subject-card bg-white rounded-xl border border-slate-200 shadow-sm p-5" style="border-left-color: ${s.color}">
                        <h4 class="font-bold text-slate-800 text-lg mb-1">${s.name}</h4>
                        <p class="text-sm text-slate-500 mb-4">${taskCount} registros asociados</p>
                        <button onclick="deleteSubject('${s.id}')" class="text-red-500 hover:text-red-700 text-sm font-medium"><i class="fas fa-trash-alt mr-1"></i> Eliminar</button>
                    </div>`;
                }).join('')}
            </div>`;
        }

        function renderTaskRow(task) {
            const subject = state.subjects.find(s => s.id === task.subjectId);
            const subColor = subject ? subject.color : '#cbd5e1';
            const subName = subject ? subject.name : 'Sin Materia';
            
            const icons = { 'tarea': 'fa-pen-to-square', 'clase': 'fa-video', 'examen': 'fa-file-signature', 'foro': 'fa-comments' };
            const icon = icons[task.type] || 'fa-tasks';
            
            const dateStr = task.dueDate ? new Date(task.dueDate).toLocaleString('es-CO', {day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit', hour12:true}) : 'Sin fecha';
            const isCompleted = task.status === 'completado';
            
            return `
                <div class="task-card flex flex-col sm:flex-row sm:items-center bg-white border border-slate-200 p-4 hover:bg-slate-50 ${isCompleted ? 'opacity-60' : ''}" style="border-left-color: ${subColor}" onclick="editTask('${task.id}')">
                    <div class="flex-1 min-w-0 mb-2 sm:mb-0">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold" style="background-color: ${subColor}20; color: ${subColor}">${subName}</span>
                            <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold"><i class="fas ${icon} mr-1"></i>${task.type}</span>
                        </div>
                        <h4 class="font-bold text-slate-800 truncate ${isCompleted ? 'line-through text-slate-500' : ''}">${task.title}</h4>
                    </div>
                    <div class="flex items-center sm:ml-4 text-sm text-slate-600 whitespace-nowrap">
                        <i class="far fa-calendar-alt mr-2 text-slate-400"></i> ${dateStr}
                    </div>
                </div>
            `;
        }

        // ------------------ Modal Controls ------------------ //
        function openTaskModal() {
            if(!state.subjects.length) return alert('Primero debes crear una materia.');
            document.getElementById('t-modal-title').innerText = 'Nueva Tarea/Clase';
            document.getElementById('t-id').value = '';
            document.getElementById('t-title').value = '';
            document.getElementById('t-date').value = '';
            document.getElementById('t-status').value = 'pendiente';
            document.getElementById('t-delete-btn').classList.add('hidden');
            quill.root.innerHTML = '';
            document.getElementById('task-modal').classList.remove('hidden');
        }

        function closeTaskModal() { document.getElementById('task-modal').classList.add('hidden'); }

        function saveTask() {
            const id = document.getElementById('t-id').value;
            const task = {
                id: id || 'task_' + Date.now(),
                title: document.getElementById('t-title').value.trim(),
                subjectId: document.getElementById('t-subject').value,
                type: document.getElementById('t-type').value,
                dueDate: document.getElementById('t-date').value,
                status: document.getElementById('t-status').value,
                notes: quill.root.innerHTML
            };

            if(!task.title) return alert('El título es obligatorio.');

            if(id) {
                const idx = state.tasks.findIndex(t => t.id === id);
                state.tasks[idx] = task;
            } else {
                state.tasks.push(task);
            }
            
            saveTasks();
            closeTaskModal();
            renderView();
        }

        function editTask(id) {
            const task = state.tasks.find(t => t.id === id);
            if(!task) return;
            
            document.getElementById('t-modal-title').innerText = 'Editar Registro';
            document.getElementById('t-id').value = task.id;
            document.getElementById('t-title').value = task.title;
            document.getElementById('t-subject').value = task.subjectId;
            document.getElementById('t-type').value = task.type;
            document.getElementById('t-date').value = task.dueDate;
            document.getElementById('t-status').value = task.status;
            quill.root.innerHTML = task.notes || '';
            
            document.getElementById('t-delete-btn').classList.remove('hidden');
            document.getElementById('task-modal').classList.remove('hidden');
        }

        function deleteTask() {
            const id = document.getElementById('t-id').value;
            if(confirm('¿Seguro que deseas eliminar este registro?')) {
                state.tasks = state.tasks.filter(t => t.id !== id);
                saveTasks();
                closeTaskModal();
                renderView();
            }
        }

        function openSubjectModal() {
            document.getElementById('s-name').value = '';
            document.getElementById('subject-error').classList.add('hidden');
            document.getElementById('subject-modal').classList.remove('hidden');
        }

        function closeSubjectModal() { document.getElementById('subject-modal').classList.add('hidden'); }

        function saveSubject() {
            const name = document.getElementById('s-name').value.trim();
            const errorDiv = document.getElementById('subject-error');
            
            if(!name) return;
            
            if(state.subjects.some(s => s.name.toLowerCase() === name.toLowerCase())) {
                errorDiv.innerText = "Ya existe una materia con ese nombre.";
                errorDiv.classList.remove('hidden');
                return;
            }

            const subData = {
                id: 'sub_' + Date.now(),
                name: name,
                color: document.getElementById('s-color').value
            };
            
            state.subjects.push(subData);
            saveSubjects();
            
            closeSubjectModal();
            renderView();
            updateSubjectDropdowns();
            document.getElementById('s-name').value = '';
        }

        function deleteSubject(id) {
            const taskCount = state.tasks.filter(t => t.subjectId === id).length;
            if(taskCount > 0) {
                alert(`No puedes eliminar esta materia porque tiene ${taskCount} registros asociados. Elimina las tareas primero.`);
                return;
            }
            if(confirm('¿Eliminar esta materia?')) {
                state.subjects = state.subjects.filter(s => s.id !== id);
                saveSubjects();
                updateSubjectDropdowns();
                renderView();
            }
        }

        function updateSubjectDropdowns() {
            const html = state.subjects.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
            const tSubject = document.getElementById('t-subject');
            const fSubject = document.getElementById('f-subject');
            
            if(tSubject) tSubject.innerHTML = html;
            if(fSubject) fSubject.innerHTML = '<option value="all">Todas las materias</option>' + html;
        }

        function toggleSidebar() { 
            document.getElementById('sidebar').classList.toggle('-translate-x-full'); 
        }

        window.onload = init;
    </script>
</body>
</html>