<?php
// Conexión a la base de datos requerida
require_once("../comun/conexion.php");

/* * NOTA: Se asume que en "../comun/conexion.php" existe una instancia 
 * válida de mysqli llamada $mysqli.
 */

// 1. Manejo de Peticiones AJAX (Lectura con Búsqueda y Paginación)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax_read'])) {
    header('Content-Type: application/json');
    
    $limit = 10; // Registros por página
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    
    $search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
    
    $whereSQL = "";
    if (!empty($search)) {
        $whereSQL = " WHERE estudiante LIKE '%$search%' 
                      OR documento LIKE '%$search%' 
                      OR materia LIKE '%$search%' 
                      OR uniforme LIKE '%$search%'";
    }

    // Contar total de registros para paginación
    $countQuery = $mysqli->query("SELECT COUNT(id) as total FROM asistencias" . $whereSQL);
    $totalRecords = $countQuery->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $limit);

    // Obtener registros
    $dataQuery = $mysqli->query("SELECT * FROM asistencias" . $whereSQL . " ORDER BY fecha_actualizacion DESC LIMIT $limit OFFSET $offset");
    
    $data = [];
    if ($dataQuery) {
        while ($row = $dataQuery->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode([
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords
        ]
    ]);
    exit;
}

// 2. Manejo de Peticiones AJAX (Crear, Actualizar, Eliminar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    
    // Sanitización
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $estudiante = isset($_POST['estudiante']) ? $mysqli->real_escape_string($_POST['estudiante']) : '';
    $materia = isset($_POST['materia']) ? $mysqli->real_escape_string($_POST['materia']) : '';
    $asistencias = isset($_POST['asistencias']) ? $mysqli->real_escape_string($_POST['asistencias']) : '';
    $fechas_clase = isset($_POST['fechas_clase']) ? $mysqli->real_escape_string($_POST['fechas_clase']) : '';
    $documento = isset($_POST['documento']) ? $mysqli->real_escape_string($_POST['documento']) : '';
    $uniforme = isset($_POST['uniforme']) ? $mysqli->real_escape_string($_POST['uniforme']) : '';
    $justificacion = isset($_POST['justificacion']) ? $mysqli->real_escape_string($_POST['justificacion']) : '';

    $response = ['success' => false, 'message' => 'Acción no válida.'];

    if ($action === 'create') {
        $sql = "INSERT INTO asistencias (estudiante, materia, asistencias, fechas_clase, documento, uniforme, justificacion) 
                VALUES ('$estudiante', '$materia', '$asistencias', '$fechas_clase', '$documento', '$uniforme', '$justificacion')";
        if ($mysqli->query($sql)) {
            $response = ['success' => true, 'message' => 'Registro creado exitosamente.'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . $mysqli->error];
        }
    } elseif ($action === 'update') {
        $sql = "UPDATE asistencias SET 
                estudiante='$estudiante', materia='$materia', asistencias='$asistencias', 
                fechas_clase='$fechas_clase', documento='$documento', uniforme='$uniforme', 
                justificacion='$justificacion', fecha_actualizacion=CURRENT_TIMESTAMP
                WHERE id=$id";
        if ($mysqli->query($sql)) {
            $response = ['success' => true, 'message' => 'Registro actualizado exitosamente.'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . $mysqli->error];
        }
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM asistencias WHERE id=$id";
        if ($mysqli->query($sql)) {
            $response = ['success' => true, 'message' => 'Registro eliminado exitosamente.'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . $mysqli->error];
        }
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
    <title>Gestión de Asistencias</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .modal-active { display: flex !important; }
        /* Animación para el Toast */
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast-enter { animation: slideIn 0.3s ease-out forwards; }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased relative min-h-screen">

<!-- Contenedor Toast para Notificaciones -->
<div id="toastContainer" class="fixed top-5 right-5 z-50 flex flex-col gap-3"></div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Registro de Asistencias</h1>
            <p class="text-sm text-slate-500 mt-1">Administración de asistencia, materias y uniformes.</p>
        </div>
        <button onclick="openModal('create')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-lg shadow-sm font-medium transition-colors flex items-center gap-2 whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Nueva Asistencia
        </button>
    </div>

    <!-- Buscador -->
    <div class="mb-6 bg-white p-4 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row items-center gap-4">
        <div class="relative w-full sm:w-96">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" id="searchInput" placeholder="Buscar por estudiante, doc o materia..." 
                   class="block w-full pl-10 pr-3 py-2 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors sm:text-sm">
        </div>
        <div id="loadingIndicator" class="hidden text-sm text-slate-500 flex items-center gap-2">
            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Cargando...
        </div>
    </div>

    <!-- Tabla de Datos -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha / Estudiante</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Materia</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Asistió</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Uniforme</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tableBody" class="divide-y divide-slate-200 bg-white">
                    <!-- Los datos se inyectarán aquí vía JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Contenedor Paginación -->
    <div id="paginationContainer" class="flex flex-col sm:flex-row justify-between items-center text-sm text-slate-600 bg-white p-4 rounded-xl shadow-sm border border-slate-200">
        <!-- Paginación se inyectará aquí -->
    </div>
</div>

<!-- Modal Formulario CRUD -->
<div id="crudModal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden transform transition-all">
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800" id="modalTitle">Nueva Asistencia</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <form id="crudForm" class="p-6">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="formId" value="">
            <input type="hidden" name="ajax_action" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Documento</label>
                    <input type="text" name="documento" id="doc" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Estudiante</label>
                    <input type="text" name="estudiante" id="est" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Materia</label>
                    <input type="text" name="materia" id="mat" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Fecha de Clase</label>
                    <input type="date" name="fechas_clase" id="fec" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Asistencia</label>
                    <select name="asistencias" id="asi" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white transition-all">
                        <option value="SI">SI</option>
                        <option value="NO">NO</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-medium text-slate-700">Uniforme</label>
                    <input type="text" name="uniforme" id="uni" required placeholder="Ej: 1, 2, Diario, Física" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <div class="space-y-1 md:col-span-2">
                    <label class="text-sm font-medium text-slate-700">Justificación</label>
                    <textarea name="justificacion" id="jus" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all resize-none"></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeModal()" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">Cancelar</button>
                <button type="submit" id="btnSubmit" class="px-5 py-2.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition-colors">Guardar Registro</button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentPage = 1;
    let currentSearch = '';
    const modal = document.getElementById('crudModal');
    
    // --- LÓGICA DE CARGA DE DATOS (AJAX) ---
    function loadData(page = 1) {
        currentPage = page;
        const loading = document.getElementById('loadingIndicator');
        loading.classList.remove('hidden');

        fetch(`?ajax_read=1&page=${page}&search=${encodeURIComponent(currentSearch)}`)
            .then(response => response.json())
            .then(result => {
                renderTable(result.data);
                renderPagination(result.pagination);
            })
            .catch(error => {
                console.error("Error cargando datos:", error);
                showToast("Error de conexión al cargar datos.", "error");
            })
            .finally(() => {
                loading.classList.add('hidden');
            });
    }

    function renderTable(data) {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '';

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm">No se encontraron registros.</td></tr>`;
            return;
        }

        data.forEach(fila => {
            const rowData = JSON.stringify(fila).replace(/"/g, '&quot;');
            const badgeAsistencia = fila.asistencias.toUpperCase() === 'SI' 
                ? `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">SÍ</span>`
                : `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">NO</span>`;

            tbody.innerHTML += `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-slate-900">${fila.estudiante}</div>
                        <div class="text-xs text-slate-500 mt-1 flex gap-2">
                            <span>Doc: ${fila.documento}</span><span>&bull;</span><span>${fila.fechas_clase}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-slate-700">${fila.materia}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">${badgeAsistencia}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-sm text-slate-600 bg-slate-100 px-2 py-1 rounded-md">Tipo: ${fila.uniforme}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick="openModal('update', ${rowData})" class="text-indigo-600 hover:text-indigo-900 mr-4 transition-colors">Editar</button>
                        <button onclick="deleteRecord(${fila.id})" class="text-red-600 hover:text-red-900 transition-colors">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    }

    function renderPagination(info) {
        const container = document.getElementById('paginationContainer');
        if(info.total_records === 0) {
            container.innerHTML = `<div>Mostrando 0 resultados</div>`;
            return;
        }

        let html = `<div>Mostrando página <span class="font-medium">${info.current_page}</span> de <span class="font-medium">${info.total_pages}</span> (Total: ${info.total_records})</div>`;
        html += `<div class="flex gap-2 mt-3 sm:mt-0">`;
        
        // Botón Anterior
        html += `<button onclick="loadData(${info.current_page - 1})" ${info.current_page <= 1 ? 'disabled class="px-3 py-1 border rounded text-slate-400 bg-slate-50 cursor-not-allowed"' : 'class="px-3 py-1 border border-slate-300 rounded hover:bg-slate-50 text-slate-700 transition-colors"'} >Anterior</button>`;
        
        // Botón Siguiente
        html += `<button onclick="loadData(${info.current_page + 1})" ${info.current_page >= info.total_pages ? 'disabled class="px-3 py-1 border rounded text-slate-400 bg-slate-50 cursor-not-allowed"' : 'class="px-3 py-1 border border-slate-300 rounded hover:bg-slate-50 text-slate-700 transition-colors"'} >Siguiente</button>`;
        
        html += `</div>`;
        container.innerHTML = html;
    }

    // --- BÚSQUEDA CON DEBOUNCE ---
    let timeoutId;
    document.getElementById('searchInput').addEventListener('input', (e) => {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            currentSearch = e.target.value.trim();
            loadData(1); // Volver a pag 1 al buscar
        }, 400); // 400ms de retraso para no saturar el servidor
    });

    // --- LÓGICA DEL FORMULARIO Y MODAL ---
    function openModal(action, data = null) {
        document.getElementById('crudForm').reset();
        document.getElementById('formAction').value = action;
        
        if (action === 'update' && data) {
            document.getElementById('modalTitle').textContent = 'Editar Asistencia';
            document.getElementById('btnSubmit').textContent = 'Actualizar Registro';
            
            document.getElementById('formId').value = data.id;
            document.getElementById('doc').value = data.documento;
            document.getElementById('est').value = data.estudiante;
            document.getElementById('mat').value = data.materia;
            document.getElementById('fec').value = data.fechas_clase;
            document.getElementById('asi').value = data.asistencias;
            document.getElementById('uni').value = data.uniforme;
            document.getElementById('jus').value = data.justificacion;
        } else {
            document.getElementById('modalTitle').textContent = 'Nueva Asistencia';
            document.getElementById('btnSubmit').textContent = 'Guardar Registro';
            document.getElementById('fec').value = new Date().toISOString().split('T')[0];
        }
        modal.classList.add('modal-active');
    }

    function closeModal() {
        modal.classList.remove('modal-active');
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    // Procesar envío del formulario (Crear/Editar) vía Fetch
    document.getElementById('crudForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const btnSubmit = document.getElementById('btnSubmit');
        const originalText = btnSubmit.textContent;
        
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Procesando...';

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                showToast(res.message, res.success ? 'success' : 'error');
                if(res.success) {
                    closeModal();
                    loadData(currentPage);
                }
            })
            .catch(error => {
                showToast("Error de conexión.", "error");
                console.error(error);
            })
            .finally(() => {
                btnSubmit.disabled = false;
                btnSubmit.textContent = originalText;
            });
    });

    // Procesar Eliminación vía Fetch
    function deleteRecord(id) {
        if(!confirm('¿Estás seguro de eliminar este registro de asistencia?')) return;
        
        const formData = new FormData();
        formData.append('ajax_action', '1');
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                showToast(res.message, res.success ? 'success' : 'error');
                if(res.success) loadData(currentPage);
            })
            .catch(error => showToast("Error al eliminar.", "error"));
    }

    // --- SISTEMA DE TOAST (Notificaciones UX) ---
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        
        const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800';
        const icon = type === 'success' 
            ? `<svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`
            : `<svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>`;

        toast.className = `toast-enter flex items-center gap-3 p-4 rounded-lg shadow-lg border ${bgColor}`;
        toast.innerHTML = `${icon} <span class="font-medium">${message}</span>`;
        
        container.appendChild(toast);
        
        // Auto-eliminar después de 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Inicializar primera carga
    document.addEventListener('DOMContentLoaded', () => loadData(1));
</script>

</body>
</html>