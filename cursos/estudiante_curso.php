<?php
// Muestra todos los errores de PHP. Útil para depuración.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
$servername = "localhost:7000";
$username = "root";
$password = "";
$dbname = "guagua";

// --- LÓGICA DE API ASINCRÓNICA (Responde con JSON) ---
if (isset($_GET['action']) && $_GET['action'] === 'search_students') {
    header('Content-Type: application/json; charset=utf-8');
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        // Enviar error en JSON y salir
        echo json_encode(['error' => 'Error de Conexión: ' . $conn->connect_error]);
        exit();
    }
    $conn->set_charset("utf8mb4");

    $id_asignacion = isset($_GET['id_asignacion']) ? (int)$_GET['id_asignacion'] : 0;
    if ($id_asignacion === 0 && isset($_GET['asignacion'])) {
        $id_asignacion = (int)$_GET['asignacion'];
    }

    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $results_per_page = 8;
    $offset = ($page - 1) * $results_per_page;

    $response = ['students' => [], 'totalPages' => 0, 'currentPage' => $page, 'error' => null];

    if ($id_asignacion > 0) {
        // Contar total de resultados
        $count_sql_base = "SELECT COUNT(u.id_usuario) FROM usuario u JOIN inscripcion i ON u.id_usuario = i.id_estudiante WHERE i.id_asignacion = ? AND u.rol = 'estudiante'";
        $search_like = '%' . $search_term . '%';
        if (!empty($search_term)) {
            $stmt_count = $conn->prepare($count_sql_base . " AND (u.nombre LIKE ? OR u.apellido LIKE ?)");
            if ($stmt_count === false) { $response['error'] = "Error al preparar conteo: " . $conn->error; } 
            else { $stmt_count->bind_param('iss', $id_asignacion, $search_like, $search_like); }
        } else {
            $stmt_count = $conn->prepare($count_sql_base);
            if ($stmt_count === false) { $response['error'] = "Error al preparar conteo: " . $conn->error; }
            else { $stmt_count->bind_param('i', $id_asignacion); }
        }
        
        if (!$response['error'] && $stmt_count->execute()) {
            $stmt_count->bind_result($total_results);
            $stmt_count->fetch();
            $stmt_count->close();
            $response['totalPages'] = ceil($total_results / $results_per_page);

            // Obtener estudiantes para la página actual
            $students_sql_base = "SELECT u.nombre, u.apellido, u.foto, u.correo, u.telefono FROM usuario u JOIN inscripcion i ON u.id_usuario = i.id_estudiante WHERE i.id_asignacion = ? AND u.rol = 'estudiante'";
            if (!empty($search_term)) {
                $stmt_students = $conn->prepare($students_sql_base . " AND (u.nombre LIKE ? OR u.apellido LIKE ?) ORDER BY u.apellido, u.nombre LIMIT ? OFFSET ?");
                if ($stmt_students === false) { $response['error'] = "Error al preparar búsqueda: " . $conn->error; }
                else { $stmt_students->bind_param('issii', $id_asignacion, $search_like, $search_like, $results_per_page, $offset); }
            } else {
                $stmt_students = $conn->prepare($students_sql_base . " ORDER BY u.apellido, u.nombre LIMIT ? OFFSET ?");
                 if ($stmt_students === false) { $response['error'] = "Error al preparar búsqueda: " . $conn->error; }
                else { $stmt_students->bind_param('iii', $id_asignacion, $results_per_page, $offset); }
            }

            if (!$response['error'] && $stmt_students->execute()) {
                 $result_students = $stmt_students->get_result();
                 $response['students'] = $result_students->fetch_all(MYSQLI_ASSOC);
                 $stmt_students->close();
            } elseif(!$response['error']) {
                $response['error'] = "Error al ejecutar la búsqueda: " . $stmt_students->error;
            }
        } elseif(!$response['error']) {
            $response['error'] = "Error al ejecutar el conteo: " . $stmt_count->error;
        }
    } else {
        $response['error'] = "ID de asignación no válido.";
    }
    
    $conn->close();
    echo json_encode($response);
    exit(); 
}

// --- LÓGICA PARA LA CARGA INICIAL DE LA PÁGINA (Renderiza HTML) ---
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Error de Conexión: " . $conn->connect_error); }
$conn->set_charset("utf8mb4");

$id_asignacion = 0;
if (isset($_GET['id_asignacion'])) { $id_asignacion = (int)$_GET['id_asignacion']; } 
elseif (isset($_GET['asignacion'])) { $id_asignacion = (int)$_GET['asignacion']; }

$titulo_lista = isset($_GET['Lista_de_Estudiantes']) ? htmlspecialchars($_GET['Lista_de_Estudiantes'], ENT_QUOTES, 'UTF-8') : 'Estudiantes de la Asignación';
$asignacion_info = ['curso' => 'Desconocido', 'materia' => 'Desconocida'];

if ($id_asignacion > 0) {
    $info_sql = "SELECT c.nombre_categoria_curso, m.nombre_materia FROM asignacion a LEFT JOIN materia m ON a.id_asignatura = m.id_materia LEFT JOIN categoria_curso c ON a.id_categoria_curso = c.id_categoria_curso WHERE a.id_asignacion = ?";
    $stmt_info = $conn->prepare($info_sql);
    if ($stmt_info) {
        $stmt_info->bind_param('i', $id_asignacion);
        $stmt_info->execute();
        $result_info = $stmt_info->get_result();
        if ($result_info->num_rows > 0) {
            $row_info = $result_info->fetch_assoc();
            $asignacion_info['curso'] = $row_info['nombre_categoria_curso'];
            $asignacion_info['materia'] = $row_info['nombre_materia'];
        }
        $stmt_info->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_lista; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .loader {
            border: 4px solid #f3f3f3; border-top: 4px solid #3498db;
            border-radius: 50%; width: 40px; height: 40px;
            animation: spin 1s linear infinite;
            margin: 2rem auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="antialiased">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Encabezado y Barra de Búsqueda -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800" id="page-title"><?php echo $titulo_lista; ?></h1>
                    <p class="text-gray-500 mt-1">
                        Curso: <?php echo htmlspecialchars($asignacion_info['curso']); ?> | 
                        Materia: <?php echo htmlspecialchars($asignacion_info['materia']); ?> | 
                        Asignación ID: <span id="asignacion-id-display"><?php echo htmlspecialchars($id_asignacion); ?></span>
                    </p>
                </div>
                <div class="w-full md:w-1/3 mt-4 md:mt-0">
                    <div class="relative">
                         <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21L15 15M17 10C17 13.866 13.866 17 10 17C6.13401 17 3 13.866 3 10C3 6.13401 6.13401 3 10 3C13.866 3 17 6.13401 17 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <input type="text" id="search-input" placeholder="Buscar por nombre o apellido..." class="w-full pl-10 pr-4 py-2 border rounded-md text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <div id="student-grid-container">
            <!-- El contenido de los estudiantes y el loader se insertará aquí -->
        </div>
        
        <div id="pagination-controls" class="mt-10 flex justify-center items-center space-x-2">
            <!-- Los controles de paginación se insertarán aquí -->
        </div>

        <?php if ($id_asignacion == 0): ?>
            <div id="param-missing-error" class="text-center bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 rounded-lg shadow p-8">
                <h2 class="text-xl font-medium">Parámetro Faltante</h2>
                <p class="mt-2">Por favor, especifica un `id_asignacion` o `asignacion` en la URL para ver los estudiantes.</p>
                <p class="mt-1 text-sm">Ejemplo: <code class="bg-yellow-200 p-1 rounded">listar_estudiantes.php?id_asignacion=1</code></p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const studentGridContainer = document.getElementById('student-grid-container');
            const paginationControls = document.getElementById('pagination-controls');
            const errorContainer = document.getElementById('param-missing-error');

            // Función para obtener parámetros de la URL de forma segura
            function getUrlParam(name) {
                const params = new URLSearchParams(window.location.search);
                return params.get(name);
            }
            
            const idAsignacion = getUrlParam('id_asignacion') || getUrlParam('asignacion');
            const tituloLista = getUrlParam('Lista_de_Estudiantes') || 'Estudiantes de la Asignación';

            let debounceTimer;

            async function fetchStudents(page = 1, searchTerm = '') {
                // Solo proceder si tenemos un id de asignación válido
                if (!idAsignacion || idAsignacion === '0') {
                    if (errorContainer) errorContainer.style.display = 'block';
                    studentGridContainer.innerHTML = '';
                    paginationControls.innerHTML = '';
                    return;
                }
                if (errorContainer) errorContainer.style.display = 'none';

                showLoader();
                
                const params = new URLSearchParams({
                    action: 'search_students',
                    id_asignacion: idAsignacion,
                    search: searchTerm,
                    page: page
                });

                try {
                    // CORRECCIÓN: La llamada fetch ahora es a la URL actual (`?`), no a un nombre de archivo fijo.
                    const response = await fetch(`?${params.toString()}`);
                    if (!response.ok) {
                        throw new Error(`Error de red (${response.status}): ${response.statusText}`);
                    }
                    
                    const data = await response.json();
                    
                    if (data.error) {
                         throw new Error(`Error del servidor: ${data.error}`);
                    }
                    
                    renderStudents(data.students, searchTerm);
                    renderPagination(data.currentPage, data.totalPages, searchTerm);

                    // Actualizar URL sin recargar la página
                    const newUrl = `?id_asignacion=${idAsignacion}&Lista_de_Estudiantes=${encodeURIComponent(tituloLista)}&search=${encodeURIComponent(searchTerm)}&page=${page}`;
                    window.history.pushState({ path: newUrl }, '', newUrl);

                } catch (error) {
                    studentGridContainer.innerHTML = `<div class="text-center bg-red-100 text-red-700 p-4 rounded-lg"><strong>Error al cargar los datos.</strong><p class="mt-2 text-sm">${error.message}</p></div>`;
                    console.error('Fetch error:', error);
                }
            }
            
            function showLoader() {
                studentGridContainer.innerHTML = '<div class="loader"></div>';
                paginationControls.innerHTML = '';
            }

            function renderStudents(students, searchTerm) {
                if (students.length === 0) {
                    const message = searchTerm 
                        ? `No hay estudiantes que coincidan con la búsqueda "${searchTerm}".`
                        : `No hay estudiantes inscritos en esta asignación.`;
                    studentGridContainer.innerHTML = `
                        <div class="text-center bg-white rounded-lg shadow p-8">
                            <h2 class="text-xl font-medium text-gray-700">No se encontraron estudiantes</h2>
                            <p class="text-gray-500 mt-2">${message}</p>
                        </div>`;
                    return;
                }

                let studentsHTML = '<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">';
                students.forEach(student => {
                    const studentName = `${student.nombre || ''} ${student.apellido || ''}`.trim();
                    const studentPhoto = student.foto ? `assets/${student.foto}` : 'https://placehold.co/100x100/EBF4FF/7F9CF5?text=Sin+Foto';
                    const studentEmail = student.correo || 'No disponible';
                    const studentPhone = student.telefono || 'No disponible';

                    studentsHTML += `
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                            <div class="p-6 flex flex-col items-center text-center">
                                <img class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 shadow-md" src="${studentPhoto}" alt="Foto de ${studentName}" onerror="this.onerror=null;this.src='https://placehold.co/100x100/EBF4FF/7F9CF5?text=Sin+Foto';">
                                <h3 class="mt-4 text-xl font-semibold text-gray-800">${studentName}</h3>
                                <div class="mt-4 w-full space-y-2 text-sm text-gray-600">
                                    <div class="flex items-center justify-center break-all">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        <span>${studentEmail}</span>
                                    </div>
                                    <div class="flex items-center justify-center">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                        <span>${studentPhone}</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                });
                studentsHTML += '</div>';
                studentGridContainer.innerHTML = studentsHTML;
            }

            function renderPagination(currentPage, totalPages, searchTerm) {
                if (totalPages <= 1) {
                    paginationControls.innerHTML = '';
                    return;
                }

                let paginationHTML = '';
                if (currentPage > 1) {
                    paginationHTML += `<button data-page="${currentPage - 1}" class="page-link px-4 py-2 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-100">Anterior</button>`;
                }

                for (let i = 1; i <= totalPages; i++) {
                    const activeClass = (i == currentPage) ? 'bg-blue-500 text-white' : 'bg-white text-gray-700';
                    paginationHTML += `<button data-page="${i}" class="page-link px-4 py-2 text-sm ${activeClass} border rounded-md hover:bg-gray-100">${i}</button>`;
                }

                if (currentPage < totalPages) {
                    paginationHTML += `<button data-page="${currentPage + 1}" class="page-link px-4 py-2 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-100">Siguiente</button>`;
                }
                paginationControls.innerHTML = paginationHTML;
            }

            searchInput.addEventListener('keyup', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchStudents(1, e.target.value);
                }, 300); 
            });

            paginationControls.addEventListener('click', (e) => {
                if (e.target.classList.contains('page-link')) {
                    e.preventDefault();
                    const page = e.target.dataset.page;
                    fetchStudents(page, searchInput.value);
                }
            });

            // Carga inicial
            if (idAsignacion && idAsignacion !== '0') {
                const initialSearch = getUrlParam('search') || '';
                const initialPage = getUrlParam('page') || 1;
                searchInput.value = initialSearch;
                fetchStudents(initialPage, initialSearch);
            }
        });
    </script>
</body>
</html>

