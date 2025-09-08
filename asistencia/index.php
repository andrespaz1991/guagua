<?php
// Iniciar sesión y cargar clases necesarias.
require_once("../comun/autoload.php");
require_once('../clases/Academico.Class.php');
require_once('../clases/Persona.Class.php');
date_default_timezone_set('America/Bogota');

// --- DETALLES DE CONEXIÓN A LA BASE DE DATOS ---
$db_host = '127.0.0.1:7000';
$db_name = 'guagua';
$db_user = 'root';
$db_pass = '';

// --- LÓGICA DE BACKEND (MANEJO DE AJAX Y CSV) ---

// Endpoint para OBTENER REPORTE DE INASISTENCIAS DE UN ESTUDIANTE
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'getStudentReport') {
    header('Content-Type: application/json');
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $student_id = $_GET['student_id'] ?? 0;
        $id_asignacion = $_GET['asignacion'] ?? 0;
        $start_date = $_GET['start_date'] ?? date('Y-m-01');
        $end_date = $_GET['end_date'] ?? date('Y-m-t');

        $academico_temp = new Academico();
        $info_asignatura_temp = $academico_temp->consultar_materia($id_asignacion);
        $nombre_materia = !empty($info_asignatura_temp) ? $info_asignatura_temp[0]->nombre_materia : '';

        $report_data = [];
        $summary = ['presente' => 0, 'ausente' => 0, 'permiso' => 0, 'retardo' => 0, 'total' => 0];

        if ($nombre_materia && $student_id) {
            $sql = "SELECT fechas_clase, asistencias FROM asistencias 
                    WHERE documento = :student_id 
                    AND materia = :materia 
                    AND STR_TO_DATE(TRIM(fechas_clase), '%d/%m/%Y') BETWEEN :start_date AND :end_date
                    ORDER BY STR_TO_DATE(TRIM(fechas_clase), '%d/%m/%Y') ASC";

            $params = [
                ':student_id' => $student_id,
                ':materia' => $nombre_materia,
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $report_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calcular el resumen
            foreach ($report_data as $record) {
                $summary['total']++;
                switch ($record['asistencias']) {
                    case 'SI': $summary['presente']++; break;
                    case 'NO': $summary['ausente']++; break;
                    case 'P': $summary['permiso']++; break;
                    case 'R': $summary['retardo']++; break;
                }
            }
        }
        echo json_encode(['success' => true, 'data' => $report_data, 'summary' => $summary]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al generar reporte: ' . $e->getMessage()]);
    }
    exit;
}


// Endpoint para IMPORTAR DESDE CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'import_csv') {
    header('Content-Type: application/json');
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $id_asignacion = $_POST['asignacion'] ?? 0;
        $file_tmp_path = $_FILES['csv_file']['tmp_name'];
        
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $academico_temp = new Academico();
            $info_asignatura_temp = $academico_temp->consultar_materia($id_asignacion);
            $nombre_materia = !empty($info_asignatura_temp) ? $info_asignatura_temp[0]->nombre_materia : 'Materia Desconocida';

            $rowCount = 0;
            if (($handle = fopen($file_tmp_path, "r")) !== FALSE) {
                fgetcsv($handle); 

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if(count($data) >= 5) {
                        $id_estudiante = $data[0];
                        $nombre_estudiante = $data[1];
                        $estado = $data[2];
                        $fecha_csv = $data[3];
                        
                        $fecha_db_formato = date('d/m/Y', strtotime($fecha_csv));
                        
                        $estado_db = 'SI';
                        if (strcasecmp($estado, 'Ausente') == 0) $estado_db = 'NO';
                        if (strcasecmp($estado, 'Permiso') == 0) $estado_db = 'P';
                        
                        $check_sql = "SELECT id FROM asistencias WHERE documento = :documento AND materia = :materia AND fechas_clase = :fechas_clase";
                        $check_stmt = $pdo->prepare($check_sql);
                        $check_stmt->execute([':documento' => $id_estudiante, ':materia' => $nombre_materia, ':fechas_clase' => $fecha_db_formato]);
                        $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);

                        if ($existing_record) {
                            $update_sql = "UPDATE asistencias SET asistencias = :asistencias, estudiante = :estudiante WHERE id = :id";
                            $update_stmt = $pdo->prepare($update_sql);
                            $update_stmt->execute([':asistencias' => $estado_db, ':estudiante' => $nombre_estudiante, ':id' => $existing_record['id']]);
                        } else {
                            $insert_sql = "INSERT INTO asistencias (documento, estudiante, materia, fechas_clase, asistencias) VALUES (:documento, :estudiante, :materia, :fechas_clase, :asistencias)";
                            $insert_stmt = $pdo->prepare($insert_sql);
                            $insert_stmt->execute([':documento' => $id_estudiante, ':estudiante' => $nombre_estudiante, ':materia' => $nombre_materia, ':fechas_clase' => $fecha_db_formato, ':asistencias' => $estado_db]);
                        }
                        $rowCount++;
                    }
                }
                fclose($handle);
            }
            echo json_encode(['success' => true, 'message' => "Se procesaron $rowCount registros exitosamente."]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
    }
    exit;
}


// Endpoint para EXPORTAR A CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'export_csv') {
    $asistencias_json = $_POST['asistencias_data'];
    $data = json_decode($asistencias_json, true);
    $nombre_asignatura = $_POST['nombre_asignatura'] ?? 'curso';
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    
    $filename = "asistencia_" . preg_replace('/[^a-z0-9_]+/i', '_', $nombre_asignatura) . "_" . $fecha . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID Estudiante', 'Nombre Completo', 'Estado', 'Fecha', 'Materia']);

    foreach ($data as $asistencia) {
        $row = [
            $asistencia['id_estudiante'],
            $asistencia['nombre_estudiante_completo'],
            $asistencia['estado'],
            $fecha,
            $nombre_asignatura
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}


// Endpoint para OBTENER la asistencia de una fecha específica
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'getAsistencia') {
    header('Content-Type: application/json');
    $response_data = [];
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        $id_asignacion = $_GET['asignacion'] ?? 0;

        $academico_temp = new Academico();
        $info_asignatura_temp = $academico_temp->consultar_materia($id_asignacion);
        $nombre_materia = !empty($info_asignatura_temp) ? $info_asignatura_temp[0]->nombre_materia : '';
        $fecha_db_formato = date('d/m/Y', strtotime($fecha));
        
        if ($nombre_materia) {
            $sql = "SELECT documento, asistencias FROM asistencias WHERE materia = :materia AND fechas_clase = :fechas_clase";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':materia' => $nombre_materia, ':fechas_clase' => $fecha_db_formato]);
            $asistencias_existentes = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $response_data = $asistencias_existentes;
        }

        echo json_encode(['success' => true, 'data' => $response_data]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener datos: ' . $e->getMessage()]);
    }
    exit;
}

// Endpoint para GUARDAR la asistencia (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');

    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['asistencias'], $data['fecha'], $data['asignacion'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }

        $asistencias_data = $data['asistencias'];
        $fecha = $data['fecha'];
        $id_asignacion = $data['asignacion'];
        
        $academico_temp = new Academico();
        $info_asignatura_temp = $academico_temp->consultar_materia($id_asignacion);
        $nombre_materia = !empty($info_asignatura_temp) ? $info_asignatura_temp[0]->nombre_materia : 'Materia Desconocida';

        $fecha_db_formato = date('d/m/Y', strtotime($fecha));

        foreach ($asistencias_data as $asistencia) {
            $estado_db = 'SI';
            switch ($asistencia['estado']) {
                case 'Ausente': $estado_db = 'NO'; break;
                case 'Permiso': $estado_db = 'P'; break;
                case 'Retardo': $estado_db = 'R'; break;
            }
            
            $check_sql = "SELECT id FROM asistencias WHERE documento = :documento AND materia = :materia AND fechas_clase = :fechas_clase";
            $check_stmt = $pdo->prepare($check_sql);
            $check_stmt->execute([
                ':documento' => $asistencia['id_estudiante'],
                ':materia' => $nombre_materia,
                ':fechas_clase' => $fecha_db_formato
            ]);
            $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_record) {
                $update_sql = "UPDATE asistencias SET asistencias = :asistencias, estudiante = :estudiante WHERE id = :id";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([
                    ':asistencias' => $estado_db,
                    ':estudiante' => $asistencia['nombre_estudiante_completo'],
                    ':id' => $existing_record['id']
                ]);
            } else {
                $insert_sql = "INSERT INTO asistencias (documento, estudiante, materia, fechas_clase, asistencias) VALUES (:documento, :estudiante, :materia, :fechas_clase, :asistencias)";
                $insert_stmt = $pdo->prepare($insert_sql);
                $insert_stmt->execute([
                    ':documento' => $asistencia['id_estudiante'],
                    ':estudiante' => $asistencia['nombre_estudiante_completo'],
                    ':materia' => $nombre_materia,
                    ':fechas_clase' => $fecha_db_formato,
                    ':asistencias' => $estado_db
                ]);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Asistencia guardada correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * Lógica para la carga inicial de la página.
 */
$academico = new Academico();
$asignacion = isset($_GET['asignacion']) ? (int)$_GET['asignacion'] : 0;
$listado_estudiantes = [];
$nombre_asignatura = "Curso No Encontrado";

if ($asignacion) {
    $listado_estudiantes = $academico->listar_estudiantes_asignacion($asignacion);
    $info_asignatura = $academico->consultar_materia($asignacion);
    if (!empty($info_asignatura)) {
        $nombre_asignatura = $info_asignatura[0]->nombre_materia;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Asistencia Mejorado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .toast { visibility: hidden; opacity: 0; transition: opacity 0.5s, top 0.5s; }
        .toast.show { visibility: visible; opacity: 1; }
        .loader { border-top-color: #3498db; -webkit-animation: spin 1s linear infinite; animation: spin 1s linear infinite; }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .modal-bg { transition: opacity 0.3s ease; }
        .modal-box { transition: transform 0.3s ease; }
        .drag-over { border-color: #4f46e5; background-color: #e0e7ff; }
        .student-name-link { cursor: pointer; text-decoration: underline; color: #3b82f6; }
        .student-name-link:hover { color: #1d4ed8; }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-500 to-indigo-600">
            <h1 class="text-2xl md:text-3xl font-bold text-white">Toma de Asistencia</h1>
            <p class="text-indigo-100 mt-1"><?php echo htmlspecialchars($nombre_asignatura, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <label for="fecha" class="font-semibold text-gray-700">Fecha:</label>
                    <input id="fecha" type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <div id="statusIconContainer" class="ml-2"></div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                    <button id="abrirModalImportar" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-white text-gray-700 font-semibold py-2 px-6 rounded-lg border border-gray-300 hover:bg-gray-100 transition duration-300 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                        <span>Importar</span>
                    </button>
                    <button id="exportarCSV" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-gray-700 transition duration-300 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        <span>Exportar</span>
                    </button>
                    <button id="guardarAsistencia" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 transition duration-300 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                        <span>Guardar</span>
                        <div id="loader" class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-6 w-6 hidden"></div>
                    </button>
                </div>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-lg flex flex-col sm:flex-row justify-between items-center gap-4">
                <div id="stats-panel" class="flex flex-wrap gap-x-4 gap-y-2 text-sm font-medium"></div>
                <div class="relative w-full sm:w-auto">
                    <input type="text" id="search-student" placeholder="🔍 Buscar estudiante..." class="w-full sm:w-64 pl-8 pr-2 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="border-t border-gray-200"></div>

            <?php if (!empty($listado_estudiantes)): ?>
            <div class="p-3 bg-gray-100 rounded-lg flex flex-col sm:flex-row justify-between items-center gap-2">
                 <h3 class="text-sm font-semibold text-gray-600">Marcar a Todos:</h3>
                 <div class="flex flex-wrap gap-2">
                    <button class="mark-all-btn px-3 py-1 text-xs rounded-md font-medium bg-green-200 text-green-800 hover:bg-green-300" data-value="Presente">Presente</button>
                    <button class="mark-all-btn px-3 py-1 text-xs rounded-md font-medium bg-red-200 text-red-800 hover:bg-red-300" data-value="Ausente">Ausente</button>
                    <button class="mark-all-btn px-3 py-1 text-xs rounded-md font-medium bg-yellow-200 text-yellow-800 hover:bg-yellow-300" data-value="Permiso">Permiso</button>
                    <button class="mark-all-btn px-3 py-1 text-xs rounded-md font-medium bg-blue-200 text-blue-800 hover:bg-blue-300" data-value="Retardo">Retardo</button>
                 </div>
            </div>
            <?php endif; ?>

            <div id="listaEstudiantes" class="space-y-4">
                <?php if (empty($listado_estudiantes)): ?>
                    <p class="text-center text-gray-500 py-8">No hay estudiantes matriculados en este curso.</p>
                <?php else: ?>
                    <?php 
                    $contador = 1;
                    foreach ($listado_estudiantes as $info_estudiante):
                        $persona = new Persona($info_estudiante['id_estudiante']);
                        $foto = ($persona->genero == "F") ? "user-iconf.png" : "user-icon.png";
                        $ruta_foto = (defined('SGA_COMUN_SOLOSGA_DATA') ? SGA_COMUN_SOLOSGA_DATA : '../comun/sga-data') . '/' . $persona->foto;
                    ?>
                    <div class="student-row flex flex-col md:flex-row items-start md:items-center p-4 border border-gray-200 rounded-lg transition-all duration-300 hover:shadow-md" 
                         data-id-estudiante="<?php echo htmlspecialchars($persona->id_usuario, ENT_QUOTES, 'UTF-8'); ?>" 
                         data-nombre-completo="<?php echo htmlspecialchars($persona->nombre . ' ' . $persona->apellido, ENT_QUOTES, 'UTF-8'); ?>"
                         data-estado="Presente">
                        <div class="flex items-center w-full md:w-1/3 mb-4 md:mb-0">
                            <span class="text-lg font-medium text-gray-500 mr-4 w-8 text-center"><?php echo $contador++; ?>.</span>
                            <img class="h-12 w-12 rounded-full object-cover mr-4" src="<?php echo htmlspecialchars($ruta_foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de estudiante" onerror="this.onerror=null;this.src='https://placehold.co/48x48/E2E8F0/A0AEC0?text=Foto';">
                            <div>
                                <p class="student-name font-semibold text-gray-800">
                                    <a href="#" class="student-name-link"><?php echo htmlspecialchars($persona->nombre . ' ' . $persona->apellido, ENT_QUOTES, 'UTF-8'); ?></a>
                                </p>
                                <p class="text-sm text-gray-500">ID: <?php echo htmlspecialchars($persona->id_usuario, ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </div>
                        <div class="flex-grow w-full space-y-3">
                            <div class="flex flex-wrap gap-2 justify-start md:justify-end">
                                <button class="asistencia-btn px-4 py-2 text-sm rounded-md font-medium" data-value="Presente">Presente</button>
                                <button class="asistencia-btn px-4 py-2 text-sm rounded-md font-medium" data-value="Ausente">Ausente</button>
                                <button class="asistencia-btn px-4 py-2 text-sm rounded-md font-medium" data-value="Permiso">Permiso</button>
                                <button class="asistencia-btn px-4 py-2 text-sm rounded-md font-medium" data-value="Retardo">Retardo</button>
                            </div>
                            <textarea class="justificacion-area w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-2 hidden" placeholder="Añadir justificación..."></textarea>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de Importación -->
    <div id="importarModal" class="modal-bg fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="modal-box bg-white rounded-lg shadow-xl p-6 w-full max-w-md transform -translate-y-10">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Importar Asistencia desde CSV</h3>
            <div class="mt-4">
                <p class="text-sm text-gray-500">Seleccione o arrastre un archivo CSV con las columnas: <strong>ID Estudiante, Nombre Completo, Estado, Fecha, Materia</strong>.</p>
                <div id="drop-zone" class="mt-4 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="csvFileInput" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                <span>Subir un archivo</span>
                                <input id="csvFileInput" name="csv_file" type="file" class="sr-only" accept=".csv">
                            </label>
                            <p class="pl-1">o arrastrar y soltar</p>
                        </div>
                        <p id="fileName" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button id="importarCSVBtnModal" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:col-start-2 sm:text-sm">Importar</button>
                <button id="cancelarImportarBtn" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:col-start-1 sm:text-sm">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- Modal de Reporte de Estudiante -->
    <div id="reporteModal" class="modal-bg fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="modal-box bg-white rounded-lg shadow-xl p-6 w-full max-w-lg transform -translate-y-10">
            <h3 class="text-lg font-medium leading-6 text-gray-900">Reporte de Asistencia</h3>
            <p id="reporteStudentName" class="text-sm text-gray-500 mt-1"></p>
            <div class="mt-4 flex flex-col sm:flex-row gap-4 items-center">
                <div>
                    <label for="reportStartDate" class="block text-sm font-medium text-gray-700">Desde</label>
                    <input type="date" id="reportStartDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <label for="reportEndDate" class="block text-sm font-medium text-gray-700">Hasta</label>
                    <input type="date" id="reportEndDate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                <button id="generarReporteBtn" class="self-end w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Generar</button>
            </div>
            <div id="reporteResultados" class="mt-4 max-h-80 overflow-y-auto">
                <!-- Los resultados del reporte se insertarán aquí -->
            </div>
             <div class="mt-5 text-right">
                <button id="cerrarReporteBtn" type="button" class="mt-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50">Cerrar</button>
            </div>
        </div>
    </div>


    <!-- Notificación (Toast) -->
    <div id="toast" class="toast fixed top-5 right-5 p-4 rounded-lg shadow-lg text-white z-50">
        <span id="toast-message"></span>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const studentRows = document.querySelectorAll('.student-row');
        const guardarBtn = document.getElementById('guardarAsistencia');
        const loader = document.getElementById('loader');
        const fechaInput = document.getElementById('fecha');
        const statusIconContainer = document.getElementById('statusIconContainer');
        const asignacion = <?php echo json_encode($asignacion); ?>;
        const nombreAsignatura = <?php echo json_encode($nombre_asignatura); ?>;

        // --- Nuevas Mejoras UX/UI ---
        const statsPanel = document.getElementById('stats-panel');
        const searchInput = document.getElementById('search-student');
        const markAllBtns = document.querySelectorAll('.mark-all-btn');
        const exportarBtn = document.getElementById('exportarCSV');
        
        // --- Modal de importación ---
        const abrirModalBtn = document.getElementById('abrirModalImportar');
        const importarModal = document.getElementById('importarModal');
        const cancelarImportarBtn = document.getElementById('cancelarImportarBtn');
        const importarCSVBtnModal = document.getElementById('importarCSVBtnModal');
        const csvFileInput = document.getElementById('csvFileInput');
        const fileNameDisplay = document.getElementById('fileName');
        const dropZone = document.getElementById('drop-zone');

        // --- Modal de Reporte ---
        const reporteModal = document.getElementById('reporteModal');
        const reporteStudentName = document.getElementById('reporteStudentName');
        const reportStartDate = document.getElementById('reportStartDate');
        const reportEndDate = document.getElementById('reportEndDate');
        const generarReporteBtn = document.getElementById('generarReporteBtn');
        const reporteResultados = document.getElementById('reporteResultados');
        const cerrarReporteBtn = document.getElementById('cerrarReporteBtn');
        let currentStudentIdForReport = null;
        
        const statusMap = { 'SI': 'Presente', 'NO': 'Ausente', 'P': 'Permiso', 'R': 'Retardo' };
        
        // --- Lógica del Panel de Estadísticas ---
        function updateStats() {
            const stats = { 'Presente': 0, 'Ausente': 0, 'Permiso': 0, 'Retardo': 0, 'Total': 0 };
            studentRows.forEach(row => {
                if(row.style.display !== 'none') {
                    stats.Total++;
                    const status = row.dataset.estado;
                    if (stats.hasOwnProperty(status)) {
                        stats[status]++;
                    }
                }
            });
            statsPanel.innerHTML = `
                <span class="flex items-center text-gray-700"><strong>Total: ${stats.Total}</strong></span>
                <span class="flex items-center text-green-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>Presentes: ${stats['Presente']}</span>
                <span class="flex items-center text-red-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>Ausentes: ${stats['Ausente']}</span>
                <span class="flex items-center text-yellow-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>Permisos: ${stats['Permiso']}</span>
                <span class="flex items-center text-blue-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>Retardos: ${stats['Retardo']}</span>
            `;
        }
        
        async function cargarAsistencia(fecha) {
            statusIconContainer.innerHTML = '';
            try {
                const response = await fetch(`?action=getAsistencia&fecha=${fecha}&asignacion=${asignacion}`);
                const result = await response.json();

                if (result.success) {
                    if (Object.keys(result.data).length === 0) {
                        statusIconContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800" title="No hay registros para esta fecha."><svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>Disponible</span>`;
                        studentRows.forEach(row => setStudentStatus(row, 'Presente'));
                    } else {
                         statusIconContainer.innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800" title="Asistencia registrada para esta fecha."><svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Registrada</span>`;
                        studentRows.forEach(row => {
                            const studentId = row.dataset.idEstudiante;
                            const dbStatus = result.data[studentId];
                            const uiStatus = statusMap[dbStatus] || 'Presente';
                            setStudentStatus(row, uiStatus);
                        });
                    }
                    updateStats();
                } else {
                    showToast(result.message || 'Error al cargar datos.', 'error');
                }
            } catch (error) {
                showToast('Error de conexión al cargar datos.', 'error');
            }
        }
        
        // --- Lógica de Búsqueda de Estudiantes ---
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            studentRows.forEach(row => {
                const studentName = row.dataset.nombreCompleto.toLowerCase();
                row.style.display = studentName.includes(searchTerm) ? 'flex' : 'none';
            });
            updateStats();
        });

        // --- Lógica de "Marcar a Todos" ---
        markAllBtns.forEach(button => {
            button.addEventListener('click', () => {
                const status = button.dataset.value;
                studentRows.forEach(row => {
                    if (row.style.display !== 'none') {
                        setStudentStatus(row, status);
                    }
                });
            });
        });

        // --- Lógica de UI Refactorizada ---
        function updateButtonStyles(row, newState) {
            const buttons = row.querySelectorAll('.asistencia-btn');
            const justificacionArea = row.querySelector('.justificacion-area');
            
            buttons.forEach(btn => {
                btn.classList.remove('bg-green-500', 'bg-red-500', 'bg-yellow-500', 'bg-blue-500', 'text-white', 'font-semibold');
                btn.classList.add('bg-gray-200', 'text-gray-600');
            });

            const activeButton = row.querySelector(`.asistencia-btn[data-value="${newState}"]`);
            if (activeButton) {
                activeButton.classList.remove('bg-gray-200', 'text-gray-600');
                activeButton.classList.add('text-white', 'font-semibold');
                const colors = {'Presente': 'bg-green-500', 'Ausente': 'bg-red-500', 'Permiso': 'bg-yellow-500', 'Retardo': 'bg-blue-500'};
                if (colors[newState]) activeButton.classList.add(colors[newState]);
            }
            if (justificacionArea) {
                justificacionArea.classList.toggle('hidden', !(newState === 'Ausente' || newState === 'Permiso'));
            }
        }

        function setStudentStatus(row, status) {
            row.dataset.estado = status;
            updateButtonStyles(row, status);
            updateStats();
        }

        studentRows.forEach(row => {
            row.querySelectorAll('.asistencia-btn').forEach(button => {
                button.addEventListener('click', () => setStudentStatus(row, button.dataset.value));
            });
            row.querySelector('.student-name-link').addEventListener('click', (e) => {
                e.preventDefault();
                openReporteModal(row);
            });
        });
        
        guardarBtn.addEventListener('click', async function() {
            this.disabled = true;
            loader.classList.remove('hidden');
            guardarBtn.querySelector('span').textContent = 'Guardando...';
            
            const asistenciaData = Array.from(studentRows).map(row => ({
                id_estudiante: row.dataset.idEstudiante,
                nombre_estudiante_completo: row.dataset.nombreCompleto,
                estado: row.dataset.estado
            }));
            
            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
                    body: JSON.stringify({asistencias: asistenciaData, fecha: fechaInput.value, asignacion: asignacion})
                });
                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
                if(result.success) {
                   await cargarAsistencia(fechaInput.value);
                }
            } catch (error) {
                showToast('Error de conexión con el servidor.', 'error');
            } finally {
                this.disabled = false;
                loader.classList.remove('hidden');
                guardarBtn.querySelector('span').textContent = 'Guardar';
            }
        });

        // --- Lógica de Exportación e Importación ---
        
        exportarBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const asistenciaData = Array.from(studentRows).map(row => ({
                id_estudiante: row.dataset.idEstudiante,
                nombre_estudiante_completo: row.dataset.nombreCompleto,
                estado: row.dataset.estado,
            }));
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            ['action', 'asistencias_data', 'fecha', 'nombre_asignatura'].forEach(name => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = {
                    action: 'export_csv',
                    asistencias_data: JSON.stringify(asistenciaData),
                    fecha: fechaInput.value,
                    nombre_asignatura: nombreAsignatura
                }[name];
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        });
        
        // Modal de Importación...
        abrirModalBtn.addEventListener('click', (e) => { e.preventDefault(); importarModal.classList.remove('hidden'); });
        function closeModal() {
            importarModal.classList.add('hidden');
            csvFileInput.value = '';
            fileNameDisplay.textContent = '';
        }
        cancelarImportarBtn.addEventListener('click', closeModal);
        importarModal.addEventListener('click', (e) => { if (e.target === importarModal) closeModal(); });
        csvFileInput.addEventListener('change', () => { fileNameDisplay.textContent = csvFileInput.files[0] ? csvFileInput.files[0].name : ''; });
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => dropZone.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }));
        ['dragenter', 'dragover'].forEach(eventName => dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over')));
        ['dragleave', 'drop'].forEach(eventName => dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over')));
        dropZone.addEventListener('drop', (e) => {
            csvFileInput.files = e.dataTransfer.files;
            fileNameDisplay.textContent = csvFileInput.files[0] ? csvFileInput.files[0].name : '';
        });
        importarCSVBtnModal.addEventListener('click', async () => {
             if (csvFileInput.files.length === 0) {
                showToast('Por favor, seleccione un archivo CSV.', 'error');
                return;
            }
            const formData = new FormData();
            formData.append('csv_file', csvFileInput.files[0]);
            formData.append('action', 'import_csv');
            formData.append('asignacion', asignacion);
            importarCSVBtnModal.disabled = true;
            importarCSVBtnModal.innerHTML = `<div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-6 w-6 mx-auto"></div>`;
            try {
                const response = await fetch('', { method: 'POST', body: formData });
                const result = await response.json();
                showToast(result.message, result.success ? 'success' : 'error');
                if (result.success) {
                    closeModal();
                    await cargarAsistencia(fechaInput.value);
                }
            } catch (error) {
                showToast('Error de conexión al importar.', 'error');
            } finally {
                importarCSVBtnModal.disabled = false;
                importarCSVBtnModal.textContent = 'Importar';
            }
        });

        // --- Lógica del Modal de Reporte ---
        function openReporteModal(row) {
            currentStudentIdForReport = row.dataset.idEstudiante;
            const studentName = row.dataset.nombreCompleto;
            reporteStudentName.textContent = studentName;
            
            const today = new Date();
            const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().split('T')[0];
            reportStartDate.value = firstDayOfMonth;
            reportEndDate.value = lastDayOfMonth;

            reporteResultados.innerHTML = '<p class="text-gray-500">Seleccione un rango de fechas y genere el reporte.</p>';
            reporteModal.classList.remove('hidden');
        }

        function closeReporteModal() {
            reporteModal.classList.add('hidden');
            currentStudentIdForReport = null;
        }

        cerrarReporteBtn.addEventListener('click', closeReporteModal);
        reporteModal.addEventListener('click', (e) => { if(e.target === reporteModal) closeReporteModal(); });

        generarReporteBtn.addEventListener('click', async () => {
            if (!currentStudentIdForReport) return;
            
            const startDate = reportStartDate.value;
            const endDate = reportEndDate.value;
            if (!startDate || !endDate) {
                showToast('Debe seleccionar ambas fechas.', 'error');
                return;
            }

            reporteResultados.innerHTML = `<div class="flex justify-center items-center p-4"><div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-8 w-8"></div></div>`;

            try {
                const response = await fetch(`?action=getStudentReport&student_id=${currentStudentIdForReport}&asignacion=${asignacion}&start_date=${startDate}&end_date=${endDate}`);
                const result = await response.json();
                
                if (result.success) {
                    let summaryHTML = '';
                    if (result.summary) {
                        summaryHTML = `
                            <div class="p-4 mb-4 bg-gray-50 rounded-lg flex flex-wrap justify-center gap-x-4 gap-y-2 text-sm font-medium border">
                                <span class="flex items-center text-gray-700"><strong>Total: ${result.summary.total}</strong></span>
                                <span class="flex items-center text-green-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>P: ${result.summary.presente}</span>
                                <span class="flex items-center text-red-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>A: ${result.summary.ausente}</span>
                                <span class="flex items-center text-yellow-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>Prm: ${result.summary.permiso}</span>
                                <span class="flex items-center text-blue-600"><svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="10"/></svg>R: ${result.summary.retardo}</span>
                            </div>
                        `;
                    }

                    if (result.data.length === 0) {
                        reporteResultados.innerHTML = summaryHTML + '<p class="text-center text-gray-500 p-4">No se encontraron registros de asistencia en el rango de fechas seleccionado.</p>';
                    } else {
                        const statusLabels = { 'SI': 'Presente', 'NO': 'Ausente', 'P': 'Permiso', 'R': 'Retardo' };
                        const statusColors = { 
                            'SI': 'bg-green-100 text-green-800',
                            'NO': 'bg-red-100 text-red-800', 
                            'P': 'bg-yellow-100 text-yellow-800',
                            'R': 'bg-blue-100 text-blue-800'
                        };
                        let tableHTML = '<table class="min-w-full divide-y divide-gray-200"><thead><tr><th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th><th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th></tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                        result.data.forEach(item => {
                            const statusLabel = statusLabels[item.asistencias] || 'Desconocido';
                            const statusColor = statusColors[item.asistencias] || 'bg-gray-100 text-gray-800';
                            tableHTML += `<tr><td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${item.fechas_clase}</td><td class="px-6 py-4 whitespace-nowrap text-sm"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor}">${statusLabel}</span></td></tr>`;
                        });
                        tableHTML += '</tbody></table>';
                        reporteResultados.innerHTML = summaryHTML + tableHTML;
                    }
                } else {
                    reporteResultados.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                }

            } catch(error) {
                reporteResultados.innerHTML = `<p class="text-red-500">Error de conexión al generar el reporte.</p>`;
            }
        });


        // --- Carga inicial y otros listeners ---
        
        function showToast(message, type) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            toastMessage.textContent = message;
            toast.className = `toast fixed top-5 right-5 p-4 rounded-lg shadow-lg text-white z-50 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        fechaInput.addEventListener('change', () => cargarAsistencia(fechaInput.value));
        cargarAsistencia(fechaInput.value);
    });
    </script>
</body>
</html>

