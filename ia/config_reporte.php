<?php
// --- INICIO DEL BACKEND PHP ---

// Configuración y Conexión a la Base de Datos con MySQLi
$servidor = "127.0.0.1:7000"; // O el host de tu base de datos
$usuario = "root";             // Tu usuario de BD
$contrasena = "";              // Tu contraseña de BD
$base_de_datos = "guagua";

// Crear conexión con MySQLi
$conexion = new mysqli($servidor, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8");


// Esta función se encarga de leer los datos de la base de datos
function obtenerConfiguracion($conexion) {
    try {
        // Asumimos un docente y año lectivo fijos para el ejemplo, basado en tus datos.
        $id_docente_actual = 1085290375;
        $ano_lectivo_actual = 16;
        $id_institucion_actual = 7;

        // --- Obtener Docente ---
        $stmt_docente = $conexion->prepare("SELECT nombre, apellido, telefono FROM usuario WHERE id_usuario = ?");
        $stmt_docente->bind_param("s", $id_docente_actual);
        $stmt_docente->execute();
        $docente_data = $stmt_docente->get_result()->fetch_assoc();
        $stmt_docente->close();

        // --- Obtener Sede ---
        $stmt_sede = $conexion->prepare("SELECT nombre_institucion FROM institucion_educativa WHERE id_institucion_educativa = ?");
        $stmt_sede->bind_param("i", $id_institucion_actual);
        $stmt_sede->execute();
        $sede_data = $stmt_sede->get_result()->fetch_assoc();
        $stmt_sede->close();
        
        // --- Obtener Grupos y Materias ---
        $stmt_grupos = $conexion->prepare("
            SELECT 
                (CASE WHEN c.id_categoria_curso <= 8 THEN 1 ELSE 2 END) as id, 
                CONCAT('Grupo (', (CASE WHEN c.id_categoria_curso <= 8 THEN '6° a 8°' ELSE '9° a 11°' END), ')') as nombre,
                MIN(c.nombre_categoria_curso) as min_grado,
                MAX(c.nombre_categoria_curso) as max_grado,
                'ruta/ejemplo.xlsx' as ruta_excel, -- Dato de ejemplo
                20 as ultimafila -- Dato de ejemplo
            FROM asignacion a
            JOIN categoria_curso c ON a.id_categoria_curso = c.id_categoria_curso
            WHERE a.ano_lectivo = ? AND a.id_docente = ? AND c.id_categoria_curso BETWEEN 6 AND 11
            GROUP BY (CASE WHEN c.id_categoria_curso <= 8 THEN 1 ELSE 2 END)
        ");
        $stmt_grupos->bind_param("is", $ano_lectivo_actual, $id_docente_actual);
        $stmt_grupos->execute();
        $grupos = $stmt_grupos->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_grupos->close();

        // Para cada grupo, obtener sus materias (esto es una simplificación)
        foreach ($grupos as $key => &$grupo) {
             if ($grupo['min_grado'] <= 8) {
                 $grupo['materias'] = [
                    ["nombre" => "Geometría", "columna" => "D"],
                    ["nombre" => "Ciencias Sociales", "columna" => "E"],
                    ["nombre" => "Educación Física", "columna" => "F"]
                ];
            } else {
                $grupo['materias'] = [
                    ["nombre" => "Ciencias Sociales/Economia", "columna" => "E"],
                    ["nombre" => "Emprendimiento", "columna" => "G"],
                    ["nombre" => "Tecnología", "columna" => "I"]
                ];
            }
        }
        
        return [
            "docente" => [
                "nombre" => $docente_data ? trim($docente_data['nombre'] . ' ' . $docente_data['apellido']) : 'No encontrado',
                "telefono" => $docente_data ? $docente_data['telefono'] : ''
            ],
            "sede" => $sede_data ? $sede_data['nombre_institucion'] : 'Sede no encontrada',
            "grupos" => $grupos
        ];

    } catch (Exception $e) {
        return ["error" => "Error al obtener la configuración: " . $e->getMessage()];
    }
}


// --- MANEJO DE PETICIONES POST (GUARDAR, EDITAR, ELIMINAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? null;
    $id_docente_actual = 1085290375;
    $id_institucion_actual = 7;

    $response = ["success" => false, "message" => "Acción no reconocida."];

    if ($action === 'save_general') {
        $type = $input['data']['type'];
        if ($type === 'docente') {
            $nombre_partes = explode(' ', $input['data']['nombre'], 2);
            $nombre = $nombre_partes[0];
            $apellido = $nombre_partes[1] ?? '';
            $telefono = $input['data']['telefono'];
            
            $stmt = $conexion->prepare("UPDATE usuario SET nombre = ?, apellido = ?, telefono = ? WHERE id_usuario = ?");
            // SOLUCIÓN: Cambiar "sssi" a "ssss" para tratar el ID como un string y evitar conflictos de tipo.
            $stmt->bind_param("ssss", $nombre, $apellido, $telefono, $id_docente_actual);
            
            if ($stmt->execute()) {
                $response = ["success" => true, "message" => "Docente actualizado con éxito."];
            } else {
                $response = ["success" => false, "message" => "Error al actualizar el docente: " . $stmt->error];
            }
            $stmt->close();

        } elseif ($type === 'sede') {
            $nombre_sede = $input['data']['nombre'];
            $stmt = $conexion->prepare("UPDATE institucion_educativa SET nombre_institucion = ? WHERE id_institucion_educativa = ?");
            $stmt->bind_param("si", $nombre_sede, $id_institucion_actual);

            if ($stmt->execute()) {
                $response = ["success" => true, "message" => "Sede actualizada con éxito."];
            } else {
                $response = ["success" => false, "message" => "Error al actualizar la sede: " . $stmt->error];
            }
            $stmt->close();
        }
    }

    if ($action === 'save_group') {
        $response = ["success" => true, "message" => "Grupo guardado (simulación)."];
    }
    
    if ($action === 'delete_group') {
        $id_grupo = $input['id'] ?? null;
        $response = ["success" => true, "message" => "Grupo " . htmlspecialchars($id_grupo) . " eliminado (simulación)."];
    }

    header("Content-Type: application/json");
    echo json_encode($response);
    $conexion->close();
    exit;
}

// --- OBTENER DATOS PARA LA CARGA INICIAL DE LA PÁGINA ---
$config_data = obtenerConfiguracion($conexion);
$conexion->close();

// --- FIN DEL BACKEND PHP ---
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Configuración de Reportes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .modal { transition: opacity 0.25s ease; }
        .modal-content { transition: transform 0.25s ease; }
        .toast {
            animation: slide-in-out 3s ease-in-out forwards;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        @keyframes slide-in-out {
            0% { transform: translateY(100%); opacity: 0; }
            15% { transform: translateY(-10px); opacity: 1; }
            85% { transform: translateY(-10px); opacity: 1; }
            100% { transform: translateY(100%); opacity: 0; }
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="container mx-auto p-4 md:p-8 max-w-5xl">
        <header class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">Módulo de Configuración de Reportes</h1>
            <p class="text-gray-600 mt-2">Gestiona de forma centralizada la información para la generación de reportes académicos.</p>
        </header>

        <main class="space-y-8">
            <!-- Sección de Información General -->
            <section class="grid md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-sm" id="docente-card">
                    <!-- Contenido del Docente se renderizará aquí -->
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm" id="sede-card">
                    <!-- Contenido de la Sede se renderizará aquí -->
                </div>
            </section>

            <!-- Sección de Grupos -->
            <section>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">Gestión de Grupos</h2>
                    <button onclick="openGroupModal()" class="flex items-center bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm hover:bg-blue-700 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Añadir Grupo
                    </button>
                </div>
                <div id="grupos-container" class="space-y-6">
                    <!-- Los grupos se renderizarán aquí -->
                </div>
            </section>
        </main>
    </div>

    <!-- Modals (sin cambios en su estructura HTML) -->
    <!-- Modal para editar Docente/Sede -->
    <div id="general-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden opacity-0 z-40">
        <div class="modal-content bg-white w-full max-w-md p-6 rounded-xl shadow-lg transform scale-95">
            <h3 id="general-modal-title" class="text-xl font-bold mb-4"></h3>
            <form id="general-form">
                <div id="general-modal-fields" class="space-y-4"></div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeGeneralModal()" class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Cancelar</button>
                    <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para Grupo -->
    <div id="group-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden opacity-0 z-40">
        <div class="modal-content bg-white w-full max-w-2xl p-6 rounded-xl shadow-lg transform scale-95 overflow-y-auto max-h-screen">
             <h3 id="group-modal-title" class="text-xl font-bold mb-6"></h3>
             <form id="group-form">
                 <input type="hidden" id="group-id">
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div>
                         <label for="group-nombre" class="block text-sm font-medium text-gray-700">Nombre del Grupo</label>
                         <input type="text" id="group-nombre" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                     </div>
                     <div>
                         <label for="group-ruta" class="block text-sm font-medium text-gray-700">Ruta del Archivo Excel</label>
                         <input type="text" id="group-ruta" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                     </div>
                     <div class="grid grid-cols-3 gap-2">
                         <div>
                            <label for="group-min-grado" class="block text-sm font-medium text-gray-700">Grado Mín.</label>
                            <input type="number" id="group-min-grado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                         </div>
                         <div>
                            <label for="group-max-grado" class="block text-sm font-medium text-gray-700">Grado Máx.</label>
                            <input type="number" id="group-max-grado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                         </div>
                          <div>
                            <label for="group-ultimafila" class="block text-sm font-medium text-gray-700">Última Fila</label>
                            <input type="number" id="group-ultimafila" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                         </div>
                     </div>
                 </div>
                 
                 <hr class="my-6">

                 <div class="flex justify-between items-center mb-4">
                     <h4 class="text-lg font-semibold">Mapeo de Materias</h4>
                     <button type="button" onclick="addMateriaField()" class="flex items-center bg-green-500 text-white font-semibold py-2 px-3 rounded-lg text-sm hover:bg-green-600 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                         Añadir Materia
                     </button>
                 </div>
                 <div id="materias-fields-container" class="space-y-3">
                     <!-- Campos de materias se añadirán aquí -->
                 </div>

                 <div class="flex justify-end gap-3 mt-8">
                     <button type="button" onclick="closeGroupModal()" class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Cancelar</button>
                     <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">Guardar Grupo</button>
                 </div>
             </form>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <div id="confirm-modal" class="modal fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden opacity-0 z-50">
        <div class="modal-content bg-white w-full max-w-sm p-6 rounded-xl shadow-lg transform scale-95">
            <h3 class="text-lg font-bold">Confirmar Eliminación</h3>
            <p id="confirm-message" class="text-gray-600 my-4"></p>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeConfirmModal()" class="bg-gray-200 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors">Cancelar</button>
                <button id="confirm-delete-btn" class="bg-red-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-700 transition-colors">Eliminar</button>
            </div>
        </div>
    </div>
    
    <!-- Contenedor de Notificaciones (Toast) -->
    <div id="toast-container" class="fixed bottom-4 right-4 w-full max-w-xs z-50"></div>


    <script>
        // --- INICIO DEL FRONTEND JAVASCRIPT ---

        // El objeto 'config' ahora se carga directamente desde PHP.
        let config = <?php echo json_encode($config_data); ?>;

        // Función para recargar la página y obtener datos frescos.
        function refreshPageData() {
            window.location.reload();
        }

        // --- Render Functions ---
        function renderDocente() {
            const card = document.getElementById('docente-card');
            card.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Docente</h3>
                        <p class="mt-2 text-gray-600">${config.docente.nombre}</p>
                        <p class="text-gray-500 text-sm">${config.docente.telefono}</p>
                    </div>
                    <button onclick="openGeneralModal('docente')" class="text-gray-500 hover:text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                    </button>
                </div>`;
        }

        function renderSede() {
            const card = document.getElementById('sede-card');
            card.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Sede</h3>
                        <p class="mt-2 text-gray-600">${config.sede}</p>
                    </div>
                    <button onclick="openGeneralModal('sede')" class="text-gray-500 hover:text-blue-600">
                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                    </button>
                </div>`;
        }
        
        function renderGrupos() {
            const container = document.getElementById('grupos-container');
            if (!config.grupos || config.grupos.length === 0) {
                container.innerHTML = `<div class="bg-white p-6 rounded-xl shadow-sm text-center text-gray-500">No hay grupos para mostrar.</div>`;
                return;
            }
            container.innerHTML = config.grupos.map(grupo => `
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-xl font-bold">${grupo.nombre}</h4>
                            <span class="text-sm bg-gray-200 text-gray-700 font-medium px-2 py-0.5 rounded-full">Grados: ${grupo.min_grado}° - ${grupo.max_grado}°</span>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openGroupModal(${grupo.id})" class="text-gray-500 hover:text-blue-600 p-1 rounded-full">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                            </button>
                            <button onclick="confirmDelete('grupo', ${grupo.id})" class="text-gray-500 hover:text-red-600 p-1 rounded-full">
                               <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 space-y-2 mb-4">
                        <p><strong class="font-medium">Ruta Excel:</strong> ${grupo.ruta_excel || 'No definida'}</p>
                        <p><strong class="font-medium">Última Fila:</strong> ${grupo.ultimafila || 'No definida'}</p>
                    </div>
                    <h5 class="font-semibold mb-2">Materias:</h5>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        ${(grupo.materias || []).map(materia => `
                            <div class="bg-gray-100 p-2 rounded-md text-sm flex justify-between">
                                <span>${materia.nombre}</span>
                                <span class="font-bold text-gray-700">${materia.columna}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `).join('');
        }

        function renderAll() {
            renderDocente();
            renderSede();
            renderGrupos();
        }

        // --- Modal Handlers ---
        function openGeneralModal(type) {
            const modal = document.getElementById('general-modal');
            const title = document.getElementById('general-modal-title');
            const fields = document.getElementById('general-modal-fields');
            
            if (type === 'docente') {
                title.textContent = 'Editar Información del Docente';
                fields.innerHTML = `
                    <input type="hidden" id="general-type" value="docente">
                    <div>
                        <label for="docente-nombre" class="block text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input type="text" id="docente-nombre" value="${config.docente.nombre}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label for="docente-telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" id="docente-telefono" value="${config.docente.telefono}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>`;
            } else if (type === 'sede') {
                title.textContent = 'Editar Sede';
                fields.innerHTML = `
                    <input type="hidden" id="general-type" value="sede">
                    <div>
                        <label for="sede-nombre" class="block text-sm font-medium text-gray-700">Nombre de la Sede</label>
                        <input type="text" id="sede-nombre" value="${config.sede}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    </div>`;
            }
            
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            }, 10);
        }

        function closeGeneralModal() {
            const modal = document.getElementById('general-modal');
            modal.classList.add('opacity-0');
            modal.querySelector('.modal-content').classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 250);
        }
        
        document.getElementById('general-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const type = document.getElementById('general-type').value;
            let dataToSave = { type: type };

            if (type === 'docente') {
                dataToSave.nombre = document.getElementById('docente-nombre').value;
                dataToSave.telefono = document.getElementById('docente-telefono').value;
            } else if (type === 'sede') {
                dataToSave.nombre = document.getElementById('sede-nombre').value;
            }

            fetch('config_reporte.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_general', data: dataToSave })
            })
            .then(res => {
                if (!res.ok) { throw new Error(`Error en el servidor: ${res.status}`); }
                return res.json();
            })
            .then(result => {
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    closeGeneralModal();
                    // Espera 1.5 segundos para que el usuario vea el toast
                    setTimeout(() => {
                        refreshPageData(); 
                    }, 1500);
                }
            })
            .catch(err => {
                console.error("Error en fetch:", err);
                showToast('Error de conexión. Revisa la consola.', 'error');
            });
        });
        
        function openGroupModal(id = null) {
            const modal = document.getElementById('group-modal');
            const title = document.getElementById('group-modal-title');
            const form = document.getElementById('group-form');
            form.reset();
            document.getElementById('materias-fields-container').innerHTML = '';

            if (id) { 
                const grupo = config.grupos.find(g => g.id == id);
                if (grupo) {
                    title.textContent = 'Editar Grupo';
                    document.getElementById('group-id').value = grupo.id;
                    document.getElementById('group-nombre').value = grupo.nombre;
                    document.getElementById('group-ruta').value = grupo.ruta_excel;
                    document.getElementById('group-min-grado').value = grupo.min_grado;
                    document.getElementById('group-max-grado').value = grupo.max_grado;
                    document.getElementById('group-ultimafila').value = grupo.ultimafila;
                    (grupo.materias || []).forEach(m => addMateriaField(m.nombre, m.columna));
                }
            } else { 
                title.textContent = 'Añadir Nuevo Grupo';
                document.getElementById('group-id').value = '';
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            }, 10);
        }
        
        function closeGroupModal() {
            const modal = document.getElementById('group-modal');
            modal.classList.add('opacity-0');
            modal.querySelector('.modal-content').classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 250);
        }
        
        function addMateriaField(nombre = '', columna = '') {
             const container = document.getElementById('materias-fields-container');
            const div = document.createElement('div');
            div.className = 'flex items-center gap-2 animate-fade-in';
            div.innerHTML = `
                <input type="text" placeholder="Nombre de la Materia" class="materia-nombre flex-grow border-gray-300 rounded-md shadow-sm" value="${nombre}" required>
                <input type="text" placeholder="Columna" class="materia-columna w-20 border-gray-300 rounded-md shadow-sm" value="${columna}" required>
                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 p-1 rounded-full bg-red-100 hover:bg-red-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            `;
            container.appendChild(div);
        }
        
        document.getElementById('group-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Lógica para recolectar datos del formulario...

             fetch('config_reporte.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_group', data: {} }) // Simulado
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    showToast('Grupo guardado con éxito.');
                    setTimeout(() => refreshPageData(), 1500);
                } else {
                    showToast('Error al guardar el grupo.', 'error');
                }
            });

            closeGroupModal();
        });
        
        // --- Confirm Modal & Delete Logic ---
        let deleteAction = null;

        function confirmDelete(type, id) {
            deleteAction = () => {
                if (type === 'grupo') {
                    fetch('config_reporte.php', {
                        method: 'POST', 
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete_group', id: id })
                    })
                    .then(res => res.json())
                    .then(result => {
                        if (result.success) {
                            showToast('Elemento eliminado con éxito.', 'success');
                            setTimeout(() => refreshPageData(), 1500);
                        } else {
                             showToast('Error al eliminar.', 'error');
                        }
                    });
                }
                closeConfirmModal();
            };
            
            const modal = document.getElementById('confirm-modal');
            const message = document.getElementById('confirm-message');
            if (type === 'grupo') {
                const grupo = config.grupos.find(g => g.id == id);
                message.textContent = `¿Estás seguro de que quieres eliminar el grupo "${grupo ? grupo.nombre : id}"? Esta acción no se puede deshacer.`;
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            }, 10);
        }

        document.getElementById('confirm-delete-btn').addEventListener('click', () => {
            if (deleteAction) {
                deleteAction();
            }
        });

        function closeConfirmModal() {
            const modal = document.getElementById('confirm-modal');
            modal.classList.add('opacity-0');
            modal.querySelector('.modal-content').classList.add('scale-95');
            setTimeout(() => modal.classList.add('hidden'), 250);
            deleteAction = null;
        }
        
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
            toast.className = `toast ${bgColor} text-white font-semibold py-3 px-5 rounded-lg shadow-lg mb-2`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // --- Carga Inicial ---
        document.addEventListener('DOMContentLoaded', () => {
             if (config.error) {
                showToast(config.error, 'error');
                console.error("Error desde el servidor PHP:", config.error);
            } else {
                renderAll();
            }
        });
    </script>
</body>
</html>

