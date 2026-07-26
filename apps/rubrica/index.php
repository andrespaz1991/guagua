
<?php
// =========================================================================
// BACKEND PHP - CONEXIÓN A MYSQL Y API REST (AJAX)
// =========================================================================
$db_host = 'localhost';
$db_user = 'root'; // Cambiar si es necesario
$db_pass = '';     // Cambiar si es necesario
$db_name = 'guagua'; // Asegúrate de crear esta base de datos en MySQL o phpMyAdmin

// Suprimir warnings en el preview, reportar de forma controlada
mysqli_report(MYSQLI_REPORT_OFF);

// Intentar conexión (solo si se ejecuta en un servidor web real, no en el preview estático)
$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name,"7000");

if (!$mysqli->connect_error) {
    $mysqli->set_charset("utf8mb4");

    // Creación automática de la tabla si no existe
    $sql_create_table = "CREATE TABLE IF NOT EXISTS evaluations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_name VARCHAR(150) NOT NULL,
        student_grade VARCHAR(50),
        subject VARCHAR(150),
        eval_date DATE,
        rubric_type VARCHAR(50) NOT NULL,
        final_score DECIMAL(3,1) NOT NULL,
        final_level VARCHAR(50),
        observations TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $mysqli->query($sql_create_table);

    // Tabla para rúbricas personalizadas
    $sql_custom_rubrics = "CREATE TABLE IF NOT EXISTS custom_rubrics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rubric_key VARCHAR(50) UNIQUE NOT NULL,
        rubric_name VARCHAR(150) NOT NULL,
        icon_class VARCHAR(100) DEFAULT 'fa-solid fa-star',
        color_hex VARCHAR(7) DEFAULT '#6366f1',
        criteria JSON NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $mysqli->query($sql_custom_rubrics);
}

// Enrutador de peticiones AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($mysqli->connect_error) {
        echo json_encode(['error' => 'Error de conexión MySQL: ' . $mysqli->connect_error]);
        exit;
    }

    // 1. Guardar evaluación
    if ($_GET['action'] == 'save' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $stmt = $mysqli->prepare("INSERT INTO evaluations (student_name, student_grade, subject, eval_date, rubric_type, final_score, final_level, observations) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssdss", 
            $data['studentName'], $data['studentGrade'], $data['subject'], 
            $data['evalDate'], $data['rubricType'], $data['finalScore'], 
            $data['finalLevel'], $data['observations']
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    // 2. Obtener registros con paginación y búsqueda
    if ($_GET['action'] == 'get_records') {
        $type = $_GET['type'] ?? 'oral';
        $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 5; // Registros por página
        $offset = ($page - 1) * $limit;

        // Contar total para paginación
        $stmt_count = $mysqli->prepare("SELECT COUNT(*) as total FROM evaluations WHERE rubric_type = ? AND student_name LIKE ?");
        $stmt_count->bind_param("ss", $type, $search);
        $stmt_count->execute();
        $total_result = $stmt_count->get_result();
        $total = $total_result->fetch_assoc()['total'];
        $stmt_count->close();

        // Obtener datos
        $stmt_data = $mysqli->prepare("SELECT * FROM evaluations WHERE rubric_type = ? AND student_name LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt_data->bind_param("ssii", $type, $search, $limit, $offset);
        $stmt_data->execute();
        $result = $stmt_data->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt_data->close();

        echo json_encode([
            'data' => $data,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ]);
        exit;
    }

    // 3. Obtener rúbricas personalizadas
    if ($_GET['action'] == 'get_custom_rubrics') {
        $result = $mysqli->query("SELECT * FROM custom_rubrics WHERE is_active = 1 ORDER BY created_at ASC");
        $rubrics = [];
        while ($row = $result->fetch_assoc()) {
            $row['criteria'] = json_decode($row['criteria'], true);
            $rubrics[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $rubrics]);
        exit;
    }

    // 4. Guardar nueva rúbrica personalizada
    if ($_GET['action'] == 'save_custom_rubric' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        
        $stmt = $mysqli->prepare("INSERT INTO custom_rubrics (rubric_key, rubric_name, icon_class, color_hex, criteria) VALUES (?, ?, ?, ?, ?)");
        $criteriaJson = json_encode($data['criteria'], JSON_UNESCAPED_UNICODE);
        $stmt->bind_param("sssss", 
            $data['rubricKey'], $data['rubricName'], $data['iconClass'],
            $data['colorHex'], $criteriaJson
        );
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    // 5. Eliminar rúbrica personalizada (soft delete)
    if ($_GET['action'] == 'delete_custom_rubric' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $mysqli->prepare("UPDATE custom_rubrics SET is_active = 0 WHERE rubric_key = ?");
        $stmt->bind_param("s", $data['rubricKey']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Evaluación y Registros - IER La Josefina</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        
        .level-card { transition: all 0.2s ease-in-out; cursor: pointer; border: 2px solid transparent; }
        .level-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        
        .selected-superior { border-color: #16a34a; background-color: #f0fdf4; }
        .selected-alto { border-color: #2563eb; background-color: #eff6ff; }
        .selected-basico { border-color: #ca8a04; background-color: #fefce8; }
        .selected-bajo { border-color: #dc2626; background-color: #fef2f2; }

        .tabs-container::-webkit-scrollbar { height: 6px; }
        .tabs-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        .tabs-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        
        @media print {
            body { background-color: white; }
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            .rubric-container { box-shadow: none; border: none; padding: 0; }
            .level-card { border: 1px solid #e5e7eb; break-inside: avoid; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        /* ===== WIZARD / BUILDER STYLES ===== */
        .wizard-overlay {
            backdrop-filter: blur(8px);
            background: rgba(15, 23, 42, 0.6);
            animation: fadeIn 0.3s ease;
        }
        .wizard-panel {
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3);
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(99,102,241,0.4); } 70% { box-shadow: 0 0 0 8px rgba(99,102,241,0); } 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); } }
        @keyframes checkmark { 0% { transform: scale(0) rotate(-45deg); } 60% { transform: scale(1.2) rotate(-45deg); } 100% { transform: scale(1) rotate(0); } }
        @keyframes confetti { 0% { transform: translateY(0) rotate(0); opacity: 1; } 100% { transform: translateY(-60px) rotate(360deg); opacity: 0; } }

        /* Step Indicator */
        .step-indicator { display: flex; align-items: center; justify-content: center; gap: 0; }
        .step-circle {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid #e2e8f0; color: #94a3b8; background: white; position: relative; z-index: 2;
        }
        .step-circle.active { border-color: #6366f1; color: white; background: #6366f1; animation: pulse-ring 1.5s infinite; }
        .step-circle.completed { border-color: #22c55e; color: white; background: #22c55e; }
        .step-line { width: 60px; height: 3px; background: #e2e8f0; position: relative; z-index: 1; transition: background 0.4s; }
        .step-line.completed { background: #22c55e; }

        /* Wizard Steps Content */
        .wizard-body { position: relative; overflow: hidden; min-height: 380px; }
        .wizard-step-content {
            position: absolute; top: 0; left: 0; width: 100%; padding: 4px;
            transition: transform 0.45s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.35s;
        }
        .wizard-step-content.active { transform: translateX(0); opacity: 1; pointer-events: auto; }
        .wizard-step-content.to-left { transform: translateX(-105%); opacity: 0; pointer-events: none; }
        .wizard-step-content.to-right { transform: translateX(105%); opacity: 0; pointer-events: none; }

        /* Icon Picker */
        .icon-option {
            width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
            border-radius: 12px; border: 2px solid #e2e8f0; cursor: pointer;
            transition: all 0.2s; font-size: 18px; color: #64748b; background: #f8fafc;
        }
        .icon-option:hover { border-color: #a5b4fc; background: #eef2ff; color: #6366f1; transform: scale(1.1); }
        .icon-option.selected { border-color: #6366f1; background: #6366f1; color: white; transform: scale(1.1); box-shadow: 0 4px 12px rgba(99,102,241,0.3); }

        /* Color Picker */
        .color-option {
            width: 40px; height: 40px; border-radius: 50%; cursor: pointer;
            transition: all 0.2s; border: 3px solid transparent;
        }
        .color-option:hover { transform: scale(1.15); }
        .color-option.selected { border-color: #1e293b; transform: scale(1.2); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

        /* Criterion Card in Wizard */
        .criterion-card {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 16px; transition: all 0.3s; position: relative;
        }
        .criterion-card:hover { border-color: #c7d2fe; box-shadow: 0 2px 8px rgba(99,102,241,0.08); }

        /* Weight Bar */
        .weight-bar-track { height: 8px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; }
        .weight-bar-fill { height: 100%; border-radius: 9999px; transition: width 0.4s cubic-bezier(0.4,0,0.2,1), background 0.3s; }

        /* Custom Tab Badge */
        .custom-tab { position: relative; }
        .custom-tab .delete-badge {
            position: absolute; top: -6px; right: -6px; width: 20px; height: 20px;
            border-radius: 50%; background: #ef4444; color: white; display: flex;
            align-items: center; justify-content: center; font-size: 10px;
            opacity: 0; transition: all 0.2s; cursor: pointer; z-index: 10;
            box-shadow: 0 2px 4px rgba(239,68,68,0.4);
        }
        .custom-tab:hover .delete-badge { opacity: 1; transform: scale(1); }
        .custom-tab .delete-badge:hover { background: #dc2626; transform: scale(1.15) !important; }

        .new-rubric-btn {
            border: 2px dashed #c7d2fe; color: #6366f1; background: #eef2ff;
            transition: all 0.3s;
        }
        .new-rubric-btn:hover { border-color: #6366f1; background: #e0e7ff; transform: translateY(-1px); }

        /* Delete Confirmation Modal */
        .delete-modal-panel { animation: slideUp 0.3s cubic-bezier(0.16,1,0.3,1); }

        /* Descriptor textarea */
        .desc-textarea {
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .desc-textarea:focus {
            border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        /* Accordion for step 3 */
        .accordion-header { cursor: pointer; transition: background 0.2s; }
        .accordion-header:hover { background: #f1f5f9; }
        .accordion-body { max-height: 0; overflow: hidden; transition: max-height 0.35s cubic-bezier(0.4,0,0.2,1); }
        .accordion-body.open { max-height: 600px; }

        /* Success animation */
        .success-checkmark {
            width: 64px; height: 64px; border-radius: 50%; background: #22c55e;
            display: flex; align-items: center; justify-content: center; color: white;
            animation: checkmark 0.5s cubic-bezier(0.4,0,0.2,1);
        }

        /* Tab Reorder Modal */
        .reorder-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; transition: all 0.2s; }
        .reorder-item:hover { border-color: #c7d2fe; background: #f1f5f9; }
        .reorder-item .drag-handle { color: #94a3b8; }
        .reorder-arrow { width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e2e8f0; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #64748b; transition: all 0.15s; font-size: 12px; }
        .reorder-arrow:hover { background: #eef2ff; color: #6366f1; border-color: #c7d2fe; }
        .reorder-arrow:disabled { opacity: 0.3; cursor: not-allowed; }

        /* Print rubric template */
        .print-rubric-template { display: none; }
        @media print {
            .print-rubric-template { display: block !important; page-break-before: always; }
            .print-rubric-template table { width: 100%; border-collapse: collapse; font-size: 11px; }
            .print-rubric-template th, .print-rubric-template td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
            .print-rubric-template th { background: #f3f4f6; font-weight: 700; }
            .print-rubric-template .print-header { text-align: center; margin-bottom: 16px; }
            .print-rubric-template .print-header h2 { font-size: 18px; font-weight: 700; margin: 0; }
            .print-rubric-template .print-header p { font-size: 12px; color: #6b7280; margin: 2px 0; }
            .print-student-info { display: flex; gap: 16px; margin-bottom: 12px; font-size: 12px; }
            .print-student-info .field { border-bottom: 1px solid #000; min-width: 140px; padding: 2px 4px; flex: 1; }
            .print-student-info label { font-weight: 600; margin-right: 4px; }
            .print-scale { margin-top: 12px; font-size: 10px; color: #6b7280; }
            .print-obs { margin-top: 12px; border: 1px solid #d1d5db; min-height: 60px; padding: 8px; font-size: 11px; }
            .print-score-box { margin-top: 8px; text-align: right; font-size: 14px; font-weight: 700; }
        }
    </style>
</head>
<body class="p-4 md:p-8">

    <div class="max-w-7xl mx-auto bg-white rounded-xl shadow-lg p-6 md:p-10 rubric-container">
        
        <!-- Encabezado Institucional -->
        <div class="text-center mb-8 border-b pb-6">
            <h1 class="text-3xl font-bold text-gray-800 uppercase tracking-wide">Rúbrica de Evaluación</h1>
            <h2 class="text-xl text-gray-600 mt-2">Instrumentos de Evaluación de Aprendizajes</h2>
            <p class="text-sm text-gray-500 mt-1">Institución Educativa Rural La Josefina</p>
            <div class="mt-4 no-print">
                <a href="constructor.php" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg text-sm font-semibold transition-colors border border-indigo-100">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Constructor de Rúbricas
                </a>
            </div>
        </div>

        <!-- Datos del Estudiante -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-semibold text-gray-700">Estudiante / Grupo</label>
                    <button onclick="openImportModal()" class="text-xs text-blue-600 hover:text-blue-800 no-print font-semibold flex items-center" title="Importar lista de estudiantes">
                        <i class="fa-solid fa-file-import mr-1"></i> Importar
                    </button>
                </div>
                <input type="text" id="studentName" list="students-datalist" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" placeholder="Nombres o nombre del equipo">
                <datalist id="students-datalist"></datalist>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Grado</label>
                <input type="text" id="studentGrade" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" placeholder="Ej: 10°">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Asignatura</label>
                <input type="text" id="subject" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border" placeholder="Área/Asignatura">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
                <input type="date" id="evalDate" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border">
            </div>
        </div>

        <!-- Selector de Tipo de Rúbrica -->
        <div class="flex items-center space-x-2 md:space-x-4 mb-6 border-b no-print overflow-x-auto tabs-container pb-2" id="main-tabs">
            <button onclick="setRubricType('oral')" id="tab-oral" class="px-4 md:px-6 py-3 font-semibold text-blue-600 border-b-2 border-blue-600 hover:bg-gray-50 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-microphone-lines mr-2"></i> Evaluación Oral
            </button>
            <button onclick="setRubricType('escrita')" id="tab-escrita" class="px-4 md:px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-pen-nib mr-2"></i> Evaluación Escrita
            </button>
            <button onclick="setRubricType('equipo')" id="tab-equipo" class="px-4 md:px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-users mr-2"></i> Trabajo en Equipo
            </button>
            <button onclick="setRubricType('tertulia')" id="tab-tertulia" class="px-4 md:px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-comments mr-2"></i> Tertulias y Debates
            </button>
            <button onclick="setRubricType('tarea')" id="tab-tarea" class="px-4 md:px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-book-open mr-2"></i> Tareas
            </button>
            <button onclick="setRubricType('exposicion')" id="tab-exposicion" class="px-4 md:px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap">
                <i class="fa-solid fa-person-chalkboard mr-2"></i> Exposiciones
            </button>
            <!-- Custom rubric tabs injected here -->
            <span id="custom-tabs-container" class="contents"></span>
            <!-- Add new rubric button -->
            <button onclick="openRubricBuilder()" id="btn-new-rubric" class="new-rubric-btn px-4 py-3 font-semibold rounded-lg whitespace-nowrap flex items-center gap-2" title="Crear nueva rúbrica">
                <i class="fa-solid fa-plus"></i> <span class="hidden md:inline">Nueva Rúbrica</span>
            </button>
            <button onclick="openReorderModal()" id="btn-reorder" class="px-3 py-3 text-gray-400 hover:text-indigo-600 transition-colors" title="Reordenar pestañas">
                <i class="fa-solid fa-arrows-up-down"></i>
            </button>
        </div>

        <h3 id="current-rubric-title" class="text-2xl font-bold text-gray-800 mb-4 print-only hidden">Evaluación Oral</h3>

        <!-- Contenedor de la Rúbrica -->
        <div id="rubric-content" class="space-y-6"></div>

        <!-- Resultados y Acciones -->
        <div class="mt-10 bg-gray-50 p-6 rounded-lg border flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="text-center md:text-left w-full md:w-auto">
                <p class="text-sm text-gray-600 font-semibold uppercase tracking-wider">Calificación Final</p>
                <div class="flex items-end justify-center md:justify-start gap-4 mt-2">
                    <span id="final-score" class="text-5xl font-bold text-gray-900">0.0</span>
                    <span id="final-level" class="text-xl font-medium text-gray-500 pb-1">Sin evaluar</span>
                </div>
                <div class="mt-4 w-full bg-gray-200 rounded-full h-2.5">
                    <div id="score-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
            </div>

            <div class="flex flex-wrap justify-center md:justify-end gap-3 w-full md:w-auto no-print">
                <button onclick="resetRubric()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none">
                    <i class="fa-solid fa-rotate-right mr-2"></i> Limpiar
                </button>
                <button onclick="printRubric()" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg shadow-sm hover:bg-indigo-700 focus:outline-none">
                    <i class="fa-solid fa-print mr-2"></i> Imprimir Rúbrica
                </button>
                <button onclick="saveEvaluation()" class="px-5 py-2.5 bg-green-600 text-white font-semibold rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar en BD
                </button>
            </div>
        </div>

        <!-- Observaciones -->
        <div class="mt-8">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Observaciones / Retroalimentación</label>
            <textarea id="observations" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border" placeholder="Escribe aquí los comentarios adicionales para el estudiante o equipo..."></textarea>
        </div>

        <!-- Plantilla Imprimible de la Rúbrica -->
        <div class="print-rubric-template" id="print-rubric-tpl">
            <div class="print-header">
                <h2>RÚBRICA DE EVALUACIÓN</h2>
                <p>Institución Educativa Rural La Josefina</p>
                <p id="print-rubric-type-title"></p>
            </div>
            <div class="print-student-info">
                <div><label>Estudiante:</label><span class="field" id="print-student"></span></div>
                <div><label>Grado:</label><span class="field" id="print-grade"></span></div>
                <div><label>Asignatura:</label><span class="field" id="print-subject"></span></div>
                <div><label>Fecha:</label><span class="field" id="print-date"></span></div>
            </div>
            <div id="print-rubric-table"></div>
            <div class="print-scale">
                <strong>Escala de Valoración:</strong> Superior (4.6 – 5.0) | Alto (4.0 – 4.5) | Básico (3.0 – 3.9) | Bajo (1.0 – 2.9)
            </div>
            <div class="print-score-box">Calificación Final: ______ / 5.0 &nbsp;&nbsp; Desempeño: __________________</div>
            <div class="print-obs"><strong>Observaciones:</strong></div>
        </div>

        <!-- SECCIÓN BASE DE DATOS: Registros Guardados -->
        <div class="mt-16 pt-10 border-t-2 border-dashed border-gray-300 no-print">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><i class="fa-solid fa-database text-blue-600 mr-2"></i>Registros Guardados</h3>
                    <p class="text-sm text-gray-500 mt-1">Historial de evaluaciones de la base de datos MySQL</p>
                </div>
                <button onclick="fetchRecords()" class="text-sm text-gray-600 hover:text-blue-600 border p-2 rounded-md bg-gray-50">
                    <i class="fa-solid fa-sync-alt"></i> Recargar
                </button>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
                <!-- Pestañas de Historial -->
                <div class="flex space-x-1 overflow-x-auto tabs-container bg-gray-100 p-1 rounded-lg border w-full lg:w-auto" id="history-tabs">
                    <!-- Dinamico vía JS -->
                </div>

                <!-- Buscador Asíncrono -->
                <div class="relative w-full lg:w-80">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="searchInput" placeholder="Buscar por nombre..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm text-sm" oninput="debounceSearch()">
                </div>
            </div>

            <!-- Tabla de Registros -->
            <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Estudiante</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Materia / Grado</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Nota</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Desempeño</th>
                                <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                            </tr>
                        </thead>
                        <tbody id="recordsTableBody" class="bg-white divide-y divide-gray-200">
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Cargando registros... o el servidor PHP no está activo.</td></tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
                    <div class="text-sm text-gray-700" id="paginationInfo">Mostrando 0 resultados</div>
                    <div class="flex space-x-1" id="paginationControls"></div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal de Importación -->
    <div id="importModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full px-4 flex items-center justify-center no-print">
        <div class="relative mx-auto p-5 border w-full max-w-lg shadow-xl rounded-lg bg-white">
            <div class="mt-2">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <h3 class="text-lg leading-6 font-bold text-gray-900"><i class="fa-solid fa-users mr-2 text-blue-600"></i>Importar Estudiantes</h3>
                    <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i class="fa-solid fa-xmark text-xl"></i></button>
                </div>
                <div class="text-sm text-gray-600 mb-4">
                    <p>Pega tu lista desde Excel (separado por tabulación) o separados por coma.</p>
                </div>
                <textarea id="importDataText" rows="6" class="w-full border-gray-300 rounded-md shadow-sm p-3 border text-sm" placeholder="Ej: Juan Pérez, 10°"></textarea>
                <div class="items-center py-3 mt-2 flex justify-end gap-3">
                    <button onclick="closeImportModal()" class="px-4 py-2 bg-white border text-gray-700 rounded-md">Cancelar</button>
                    <button onclick="processImport()" class="px-4 py-2 bg-blue-600 text-white rounded-md">Importar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- WIZARD: Constructor de Rúbricas (4 pasos) -->
    <!-- ============================================================ -->
    <div id="rubricBuilderModal" class="fixed inset-0 z-[60] hidden items-center justify-center px-4 py-6 no-print wizard-overlay">
        <div class="wizard-panel w-full max-w-3xl max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">
            
            <!-- Header del Wizard -->
            <div class="px-6 pt-6 pb-4 border-b border-gray-100">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center"><i class="fa-solid fa-wand-magic-sparkles text-sm"></i></span>
                            Crear Nueva Rúbrica
                        </h3>
                        <p class="text-sm text-gray-500 mt-1" id="wizard-subtitle">Define una rúbrica personalizada paso a paso</p>
                    </div>
                    <button onclick="closeRubricBuilder()" class="w-9 h-9 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <!-- Step Indicator -->
                <div class="step-indicator" id="step-indicator">
                    <div class="step-circle active" id="step-dot-1">1</div>
                    <div class="step-line" id="step-line-1"></div>
                    <div class="step-circle" id="step-dot-2">2</div>
                    <div class="step-line" id="step-line-2"></div>
                    <div class="step-circle" id="step-dot-3">3</div>
                    <div class="step-line" id="step-line-3"></div>
                    <div class="step-circle" id="step-dot-4">4</div>
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-2 px-1">
                    <span class="w-10 text-center">Info</span>
                    <span class="w-10 text-center">Criterios</span>
                    <span class="w-10 text-center">Niveles</span>
                    <span class="w-10 text-center">Preview</span>
                </div>
            </div>

            <!-- Body del Wizard (pasos) -->
            <div class="wizard-body flex-1 overflow-y-auto px-6 py-5">

                <!-- PASO 1: Información Básica -->
                <div class="wizard-step-content active" id="wizard-step-1">
                    <h4 class="text-lg font-semibold text-gray-800 mb-1">Información Básica</h4>
                    <p class="text-sm text-gray-500 mb-5">Dale un nombre, ícono y color a tu nueva rúbrica.</p>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre de la Rúbrica <span class="text-red-400">*</span></label>
                        <input type="text" id="wiz-name" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 transition-all" placeholder="Ej: Marcado de Cuadernos, Separador, Portafolio...">
                        <p class="text-xs text-gray-400 mt-1" id="wiz-name-hint">Se usará como pestaña en el menú principal</p>
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ícono</label>
                        <div class="flex flex-wrap gap-2" id="icon-picker-grid">
                            <!-- Icons rendered by JS -->
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Color Identificador</label>
                        <div class="flex flex-wrap gap-3" id="color-picker-grid">
                            <!-- Colors rendered by JS -->
                        </div>
                    </div>
                </div>

                <!-- PASO 2: Criterios de Evaluación -->
                <div class="wizard-step-content to-right" id="wizard-step-2">
                    <h4 class="text-lg font-semibold text-gray-800 mb-1">Criterios de Evaluación</h4>
                    <p class="text-sm text-gray-500 mb-4">Agrega los criterios y asigna el peso porcentual de cada uno. Deben sumar 100%.</p>

                    <!-- Weight progress -->
                    <div class="mb-5 bg-gray-50 rounded-xl p-4 border">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-600">Peso total asignado</span>
                            <span class="text-sm font-bold" id="wiz-weight-label">0%</span>
                        </div>
                        <div class="weight-bar-track">
                            <div class="weight-bar-fill bg-indigo-500" id="wiz-weight-bar" style="width: 0%"></div>
                        </div>
                        <p class="text-xs mt-1.5" id="wiz-weight-msg"><span class="text-gray-400">Distribuye el 100% entre los criterios</span></p>
                    </div>

                    <!-- Criteria list -->
                    <div class="space-y-3" id="wiz-criteria-list">
                        <!-- Criterion cards injected by JS -->
                    </div>

                    <button onclick="wizAddCriterion()" class="mt-4 w-full py-3 border-2 border-dashed border-gray-300 rounded-xl text-gray-500 hover:border-indigo-300 hover:text-indigo-600 hover:bg-indigo-50 transition-all font-medium text-sm flex items-center justify-center gap-2">
                        <i class="fa-solid fa-plus"></i> Agregar Criterio
                    </button>
                </div>

                <!-- PASO 3: Niveles de Desempeño (Descriptores) -->
                <div class="wizard-step-content to-right" id="wizard-step-3">
                    <h4 class="text-lg font-semibold text-gray-800 mb-1">Niveles de Desempeño</h4>
                    <p class="text-sm text-gray-500 mb-4">Describe cada nivel para cada criterio. Los puntajes están alineados al sistema institucional.</p>
                    
                    <div class="space-y-3" id="wiz-descriptors-list">
                        <!-- Accordion cards injected by JS -->
                    </div>
                </div>

                <!-- PASO 4: Vista Previa -->
                <div class="wizard-step-content to-right" id="wizard-step-4">
                    <h4 class="text-lg font-semibold text-gray-800 mb-1">Vista Previa</h4>
                    <p class="text-sm text-gray-500 mb-4">Así se verá tu rúbrica. Revisa y confirma para crearla.</p>
                    
                    <div id="wiz-preview-container" class="space-y-4">
                        <!-- Preview rendered by JS -->
                    </div>
                </div>

            </div>

            <!-- Footer del Wizard -->
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/80 flex items-center justify-between">
                <button onclick="wizardPrevStep()" id="wiz-btn-prev" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-600 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm hidden">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Anterior
                </button>
                <div class="flex-1"></div>
                <button onclick="wizardNextStep()" id="wiz-btn-next" class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors text-sm shadow-sm shadow-indigo-200 flex items-center gap-2">
                    Siguiente <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button onclick="saveNewRubric()" id="wiz-btn-save" class="px-6 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors text-sm shadow-sm shadow-green-200 flex items-center gap-2 hidden">
                    <i class="fa-solid fa-check mr-1"></i> Crear Rúbrica
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL: Confirmación de Eliminación -->
    <!-- ============================================================ -->
    <div id="deleteConfirmModal" class="fixed inset-0 z-[70] hidden items-center justify-center px-4 no-print wizard-overlay">
        <div class="delete-modal-panel w-full max-w-md rounded-2xl shadow-2xl bg-white p-6 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-red-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">¿Eliminar esta rúbrica?</h3>
            <p class="text-sm text-gray-500 mb-1">Estás a punto de eliminar la rúbrica:</p>
            <p class="text-base font-semibold text-gray-800 mb-4" id="delete-rubric-name">—</p>
            <p class="text-xs text-gray-400 mb-6">Las evaluaciones ya guardadas con esta rúbrica se mantendrán en la base de datos.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDeleteModal()" class="px-5 py-2.5 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors text-sm">
                    Cancelar
                </button>
                <button onclick="confirmDeleteRubric()" class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition-colors text-sm shadow-sm">
                    <i class="fa-solid fa-trash-can mr-1"></i> Sí, Eliminar
                </button>
            </div>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL: Reordenar Pestañas -->
    <!-- ============================================================ -->
    <div id="reorderModal" class="fixed inset-0 z-[70] hidden items-center justify-center px-4 no-print wizard-overlay">
        <div class="wizard-panel w-full max-w-md rounded-2xl shadow-2xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-up-down text-indigo-600"></i> Ordenar Pestañas
                </h3>
                <button onclick="closeReorderModal()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <p class="text-sm text-gray-500 mb-4">Usa las flechas para cambiar el orden de las rúbricas.</p>
            <div class="space-y-2 max-h-[50vh] overflow-y-auto" id="reorder-list"></div>
            <div class="flex justify-end gap-3 mt-5">
                <button onclick="closeReorderModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50">Cancelar</button>
                <button onclick="saveTabOrder()" class="px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700"><i class="fa-solid fa-check mr-1"></i> Guardar Orden</button>
            </div>
        </div>
    </div>

    <script>
        // ================================================================
        // DATOS DE RÚBRICAS PREDEFINIDAS (SIN CAMBIOS)
        // ================================================================
        const rubricData = {
            oral: [
                { id: 'o_fluidez', title: 'Fluidez y Pronunciación', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Habla con fluidez natural. Pronunciación clara.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Buena fluidez con pausas ocasionales.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Fluidez limitada, pausas frecuentes.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Lectura o habla entrecortada.' } ] },
                { id: 'o_dominio', title: 'Dominio del Tema', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Conocimiento profundo, responde con seguridad.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Conoce bien el tema principal.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Conocimiento superficial, depende de notas.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'No demuestra conocimiento del tema.' } ] }
            ],
            escrita: [
                { id: 'e_estructura', title: 'Estructura y Organización', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Introducción, desarrollo y conclusión contundente.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Estructura general evidente.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Falta introducción o conclusión.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'No hay estructura evidente.' } ] },
                { id: 'e_contenido', title: 'Profundidad del Contenido', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Argumentos sólidos y sustentados.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Ideas claras con soporte adecuado.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Ideas superficiales y poca argumentación.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Contenido no responde al propósito.' } ] }
            ],
            equipo: [
                { id: 'eq_participacion', title: 'Contribución y Participación', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Aporta ideas significativas y lidera.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Participa de manera constante.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Participa lo mínimo necesario.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Rara vez aporta ideas.' } ] },
                { id: 'eq_actitud', title: 'Actitud y Colaboración', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Escucha activamente y fomenta respeto.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Trabaja bien con los demás.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Le cuesta escuchar a otros.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Actitud negativa o conflictiva.' } ] }
            ],
            tertulia: [
                { id: 'ter_argumentacion', title: 'Argumentación', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Argumentos claros basados en evidencia.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Argumentos con soporte adecuado.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Opiniones con poca evidencia.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'No presenta argumentos coherentes.' } ] },
                { id: 'ter_escucha', title: 'Escucha Activa', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Respeta turnos y valora opiniones.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Mantiene el respeto en general.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Interrumpe con frecuencia.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Falta de respeto evidente.' } ] }
            ],
            tarea: [
                { id: 'tar_puntualidad', title: 'Puntualidad', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Entrega en fecha y hora.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Retraso mínimo justificado.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Retraso considerable.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'No entrega la tarea.' } ] },
                { id: 'tar_desarrollo', title: 'Desarrollo', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Resuelve todo exhaustivamente.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Resuelve la mayoría de puntos.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Trabajo incompleto.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Trabajo insuficiente.' } ] }
            ],
            exposicion: [
                { id: 'exp_dominio', title: 'Dominio', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Conocimiento profundo, sin leer.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Buen conocimiento, apoyo ocasional.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Lee constantemente el material.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Se limita a leer sin comprender.' } ] },
                { id: 'exp_expresion', title: 'Manejo del Público', levels: [ { name: 'Superior', score: 5.0, class: 'selected-superior', desc: 'Excelente contacto visual y voz.' }, { name: 'Alto', score: 4.2, class: 'selected-alto', desc: 'Buen tono, contacto intermitente.' }, { name: 'Básico', score: 3.5, class: 'selected-basico', desc: 'Voz baja, sin contacto visual.' }, { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: 'Inaudible o da la espalda.' } ] }
            ]
        };

        // Etiquetas para las rúbricas predefinidas (inmutables)
        const predefinedLabels = {
            'oral': 'Oral', 'escrita': 'Escrita', 'equipo': 'Equipo', 
            'tertulia': 'Tertulias', 'tarea': 'Tareas', 'exposicion': 'Exposición'
        };
        // Etiquetas dinámicas (predefinidas + custom)
        let rubricLabels = { ...predefinedLabels };

        // Set para trackear keys de rúbricas custom
        let customRubricKeys = new Set();
        // Metadata de custom rubrics (icon, color)
        let customRubricMeta = {};

        let currentType = 'oral';
        let historyType = 'oral';
        let currentPage = 1;
        let searchTimeout;
        let scores = {}; 
        let studentList = [];

        document.getElementById('evalDate').valueAsDate = new Date();

        // ================================================================
        // LÓGICA DE LA RÚBRICA FRONTEND (EXISTENTE - MODIFICADA)
        // ================================================================
        function setRubricType(type) {
            currentType = type;
            scores = {}; 
            
            // Update all tabs (predefined)
            Object.keys(predefinedLabels).forEach(t => {
                const tabEl = document.getElementById(`tab-${t}`);
                if(tabEl) {
                    tabEl.className = t === type 
                        ? "px-4 md:px-6 py-3 font-semibold text-blue-600 border-b-2 border-blue-600 transition-colors whitespace-nowrap"
                        : "px-4 md:px-6 py-3 font-semibold text-gray-500 border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap";
                }
            });

            // Update custom tabs
            customRubricKeys.forEach(key => {
                const tabEl = document.getElementById(`tab-${key}`);
                if(tabEl) {
                    const meta = customRubricMeta[key] || {};
                    if (key === type) {
                        tabEl.style.borderBottomColor = meta.color || '#6366f1';
                        tabEl.style.color = meta.color || '#6366f1';
                        tabEl.classList.add('border-b-2');
                        tabEl.classList.remove('border-transparent');
                    } else {
                        tabEl.style.borderBottomColor = 'transparent';
                        tabEl.style.color = '#6b7280';
                        tabEl.classList.remove('border-b-2');
                        tabEl.classList.add('border-transparent');
                    }
                }
            });

            const label = rubricLabels[type] || type;
            document.getElementById('current-rubric-title').innerText = `Evaluación: ${label}`;
            renderRubric();
            calculateFinalScore();
        }

        function renderRubric() {
            const container = document.getElementById('rubric-content');
            container.innerHTML = ''; 
            const data = rubricData[currentType];
            if (!data) return;

            data.forEach(criterion => {
                const critDiv = document.createElement('div');
                critDiv.className = 'bg-white border rounded-lg overflow-hidden';
                
                // Show weight badge for custom rubrics
                const isCustom = customRubricKeys.has(currentType);
                const weightBadge = isCustom && criterion.weight 
                    ? `<span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium">Peso: ${criterion.weight}%</span>` 
                    : '';

                const headerHtml = `
                    <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-gray-800 text-lg">${criterion.title}</h4>
                            ${weightBadge}
                        </div>
                        <div class="flex items-center space-x-2">
                            <input type="number" step="0.1" min="1.0" max="5.0" id="input-${criterion.id}" 
                                class="w-20 p-1 border rounded text-center font-bold text-blue-600 focus:ring-blue-500" 
                                placeholder="--" onchange="manualScoreUpdate('${criterion.id}', this.value)">
                        </div>
                    </div>
                `;
                
                let levelsHtml = `<div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4">`;
                criterion.levels.forEach((level, index) => {
                    const bColor = ['border-green-500', 'border-blue-500', 'border-yellow-500', 'border-red-500'][index];
                    levelsHtml += `
                        <div id="card-${criterion.id}-${index}" onclick="selectLevel('${criterion.id}', ${index}, ${level.score}, '${level.class}')"
                             class="level-card bg-gray-50 p-4 rounded-md border-t-4 ${bColor} flex flex-col h-full">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-bold text-gray-800">${level.name}</span>
                                <span class="text-xs bg-gray-200 px-2 py-1 rounded-full font-semibold">Max ${level.score.toFixed(1)}</span>
                            </div>
                            <p class="text-sm text-gray-600 flex-grow">${level.desc}</p>
                        </div>
                    `;
                });
                levelsHtml += `</div>`;

                critDiv.innerHTML = headerHtml + levelsHtml;
                container.appendChild(critDiv);
            });
        }

        function selectLevel(critId, levelIndex, score, activeClass) {
            scores[critId] = score;
            document.getElementById(`input-${critId}`).value = score.toFixed(1);
            const criterion = rubricData[currentType].find(c => c.id === critId);
            criterion.levels.forEach((lvl, idx) => document.getElementById(`card-${critId}-${idx}`).classList.remove('selected-superior', 'selected-alto', 'selected-basico', 'selected-bajo'));
            document.getElementById(`card-${critId}-${levelIndex}`).classList.add(activeClass);
            calculateFinalScore();
        }

        function manualScoreUpdate(critId, value) {
            let numValue = Math.min(Math.max(parseFloat(value) || 1.0, 1.0), 5.0);
            document.getElementById(`input-${critId}`).value = numValue.toFixed(1);
            scores[critId] = numValue;
            const criterion = rubricData[currentType].find(c => c.id === critId);
            criterion.levels.forEach((lvl, idx) => document.getElementById(`card-${critId}-${idx}`).classList.remove('selected-superior', 'selected-alto', 'selected-basico', 'selected-bajo'));
            calculateFinalScore();
        }

        function calculateFinalScore() {
            const data = rubricData[currentType];
            if (!data) { updateScoreUI(0); return; }

            const isCustom = customRubricKeys.has(currentType);
            let finalScore = 0;

            if (isCustom) {
                // Weighted average for custom rubrics
                let totalWeighted = 0;
                data.forEach(crit => {
                    if(scores[crit.id]) {
                        totalWeighted += scores[crit.id] * ((crit.weight || 0) / 100);
                    }
                });
                finalScore = Object.keys(scores).length > 0 ? totalWeighted : 0;
            } else {
                // Simple average for predefined rubrics (original behavior)
                let total = 0, count = data.length;
                data.forEach(crit => { if(scores[crit.id]) total += scores[crit.id]; });
                finalScore = Object.keys(scores).length > 0 ? (total / count) : 0;
            }

            updateScoreUI(finalScore);
        }

        function updateScoreUI(score) {
            const scoreEl = document.getElementById('final-score');
            const levelEl = document.getElementById('final-level');
            const barEl = document.getElementById('score-bar');

            if (score === 0) {
                scoreEl.innerText = "0.0"; levelEl.innerText = "Sin evaluar";
                levelEl.className = "text-xl font-medium text-gray-500 pb-1";
                barEl.style.width = "0%"; barEl.className = "h-2.5 rounded-full bg-gray-300";
                return;
            }

            scoreEl.innerText = score.toFixed(1);
            let colorClass = score >= 4.6 ? "text-green-600" : score >= 4.0 ? "text-blue-600" : score >= 3.0 ? "text-yellow-600" : "text-red-600";
            let bgClass = score >= 4.6 ? "bg-green-500" : score >= 4.0 ? "bg-blue-500" : score >= 3.0 ? "bg-yellow-500" : "bg-red-500";
            let text = score >= 4.6 ? "Desempeño Superior" : score >= 4.0 ? "Desempeño Alto" : score >= 3.0 ? "Desempeño Básico" : "Desempeño Bajo";

            levelEl.innerText = text; levelEl.className = `text-xl font-medium pb-1 ${colorClass}`;
            barEl.className = `h-2.5 rounded-full ${bgClass}`; barEl.style.width = `${(score / 5.0) * 100}%`;
        }

        function resetRubric() {
            scores = {};
            ['studentName','studentGrade','subject','observations'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('evalDate').valueAsDate = new Date();
            renderRubric(); calculateFinalScore();
        }

        // ================================================================
        // LÓGICA DE BASE DE DATOS (AJAX) - SIN CAMBIOS
        // ================================================================
        async function saveEvaluation() {
            const finalScore = parseFloat(document.getElementById('final-score').innerText);
            if(finalScore === 0) { alert("Debes evaluar al menos un criterio antes de guardar."); return; }
            
            const payload = {
                studentName: document.getElementById('studentName').value.trim(),
                studentGrade: document.getElementById('studentGrade').value.trim(),
                subject: document.getElementById('subject').value.trim(),
                evalDate: document.getElementById('evalDate').value,
                rubricType: currentType,
                finalScore: finalScore,
                finalLevel: document.getElementById('final-level').innerText,
                observations: document.getElementById('observations').value.trim()
            };

            if(!payload.studentName) { alert("El nombre del estudiante es obligatorio."); return; }

            try {
                // Al ejecutarse como archivo local, el fetch a PHP fallará. Lo capturamos para evitar ruptura en UI visual.
                const response = await fetch(window.location.pathname + '?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                
                if(data.success) {
                    alert("¡Registro guardado en la base de datos correctamente!");
                    // Si estábamos en otra pestaña del historial, la forzamos a la actual
                    setHistoryType(currentType); 
                } else {
                    alert("Error al guardar: " + data.error);
                }
            } catch (error) {
                alert("Atención: No se detecta un servidor PHP activo. Para probar el guardado en MySQL debes montar este archivo .php en XAMPP, Laragon, o tu hosting.");
            }
        }

        // Generar pestañas del historial (MODIFICADO para incluir custom)
        function buildHistoryTabs() {
            const container = document.getElementById('history-tabs');
            container.innerHTML = '';
            const allLabels = { ...rubricLabels };
            Object.keys(allLabels).forEach(t => {
                const btn = document.createElement('button');
                btn.onclick = () => setHistoryType(t);
                btn.className = `px-4 py-2 text-sm font-medium rounded-md whitespace-nowrap transition-colors ${t === historyType ? 'bg-white text-blue-600 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-700'}`;
                btn.innerText = allLabels[t];
                container.appendChild(btn);
            });
        }

        function setHistoryType(type) {
            historyType = type;
            currentPage = 1;
            buildHistoryTabs();
            fetchRecords();
        }

        function debounceSearch() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                fetchRecords();
            }, 400); // 400ms delay para no saturar BD
        }

        async function fetchRecords(page = currentPage) {
            currentPage = page;
            const query = document.getElementById('searchInput').value.trim();
            const tbody = document.getElementById('recordsTableBody');
            
            try {
                const response = await fetch(`${window.location.pathname}?action=get_records&type=${historyType}&search=${encodeURIComponent(query)}&page=${page}`);
                if (!response.ok) throw new Error("Network response not ok");
                const result = await response.json();
                
                if(result.error) { throw new Error(result.error); }

                tbody.innerHTML = '';
                if(result.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No se encontraron registros para esta categoría.</td></tr>`;
                    document.getElementById('paginationInfo').innerText = 'Mostrando 0 resultados';
                    document.getElementById('paginationControls').innerHTML = '';
                    return;
                }

                result.data.forEach(row => {
                    // Colores del badge según nota
                    const score = parseFloat(row.final_score);
                    let badgeClass = score >= 4.6 ? "bg-green-100 text-green-800" : score >= 4.0 ? "bg-blue-100 text-blue-800" : score >= 3.0 ? "bg-yellow-100 text-yellow-800" : "bg-red-100 text-red-800";

                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-gray-50 transition-colors';
                    tr.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${row.student_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${row.subject || '-'} <span class="text-xs text-gray-400">(${row.student_grade || '-'})</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">${row.final_score}</td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}">${row.final_level}</span></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${row.eval_date}</td>
                    `;
                    tbody.appendChild(tr);
                });

                // Render Paginación
                document.getElementById('paginationInfo').innerText = `Mostrando página ${result.current_page} de ${result.pages} (${result.total} registros)`;
                
                let pagControls = '';
                if (result.current_page > 1) {
                    pagControls += `<button onclick="fetchRecords(${result.current_page - 1})" class="px-3 py-1 border rounded-md bg-white text-gray-600 hover:bg-gray-50">Anterior</button>`;
                }
                if (result.current_page < result.pages) {
                    pagControls += `<button onclick="fetchRecords(${result.current_page + 1})" class="px-3 py-1 border rounded-md bg-white text-gray-600 hover:bg-gray-50">Siguiente</button>`;
                }
                document.getElementById('paginationControls').innerHTML = pagControls;

            } catch (error) {
                // Silenciamos en consola pero avisamos visualmente si falla por falta de PHP
                tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-8 text-center text-red-500 bg-red-50"><i class="fa-solid fa-triangle-exclamation mr-2"></i> Para consultar la base de datos debes ejecutar este archivo en un servidor PHP (XAMPP/Hosting).</td></tr>`;
                document.getElementById('paginationInfo').innerText = '';
                document.getElementById('paginationControls').innerHTML = '';
            }
        }

        // ---- LÓGICA DE IMPORTACIÓN ---- (Se mantiene idéntica al requerimiento anterior)
        function openImportModal() { document.getElementById('importModal').classList.remove('hidden'); }
        function closeImportModal() { document.getElementById('importModal').classList.add('hidden'); document.getElementById('importDataText').value = ''; }
        function processImport() {
            const text = document.getElementById('importDataText').value;
            if (!text.trim()) { closeImportModal(); return; }
            text.split('\n').forEach(line => {
                const parts = line.split(line.includes('\t') ? '\t' : ',');
                if (parts[0].trim()) {
                    const name = parts[0].trim(), grade = parts.length > 1 ? parts[1].trim() : '';
                    const idx = studentList.findIndex(s => s.name.toLowerCase() === name.toLowerCase());
                    if (idx >= 0) { if(grade) studentList[idx].grade = grade; } 
                    else studentList.push({ name, grade });
                }
            });
            const dl = document.getElementById('students-datalist'); dl.innerHTML = '';
            studentList.sort((a,b) => a.name.localeCompare(b.name)).forEach(s => { dl.appendChild(new Option(s.name)); });
            closeImportModal(); alert("Importación local completada para el autocompletado.");
        }
        document.getElementById('studentName').addEventListener('input', e => {
            const s = studentList.find(x => x.name.toLowerCase() === e.target.value.trim().toLowerCase());
            if (s && s.grade) document.getElementById('studentGrade').value = s.grade;
        });

        // ================================================================
        // CUSTOM RUBRICS — CARGA Y GESTIÓN
        // ================================================================

        async function loadCustomRubrics() {
            try {
                const res = await fetch(window.location.pathname + '?action=get_custom_rubrics');
                const result = await res.json();
                if (result.success && result.data) {
                    // Clear previous custom data
                    customRubricKeys.forEach(key => {
                        delete rubricData[key];
                        delete rubricLabels[key];
                    });
                    customRubricKeys.clear();
                    customRubricMeta = {};

                    result.data.forEach(r => {
                        customRubricKeys.add(r.rubric_key);
                        rubricLabels[r.rubric_key] = r.rubric_name;
                        customRubricMeta[r.rubric_key] = { icon: r.icon_class, color: r.color_hex };

                        // Build rubricData entry from criteria JSON
                        rubricData[r.rubric_key] = r.criteria.map(crit => ({
                            id: crit.id,
                            title: crit.title,
                            weight: crit.weight,
                            levels: [
                                { name: 'Superior', score: 5.0, class: 'selected-superior', desc: crit.levels[0]?.desc || 'Desempeño superior.' },
                                { name: 'Alto', score: 4.2, class: 'selected-alto', desc: crit.levels[1]?.desc || 'Desempeño alto.' },
                                { name: 'Básico', score: 3.5, class: 'selected-basico', desc: crit.levels[2]?.desc || 'Desempeño básico.' },
                                { name: 'Bajo', score: 2.5, class: 'selected-bajo', desc: crit.levels[3]?.desc || 'Desempeño bajo.' }
                            ]
                        }));
                    });

                    renderCustomTabs();
                    buildHistoryTabs();
                }
            } catch(e) {
                // Server not available - silent fail
                console.log('Custom rubrics: servidor no disponible');
            }
        }

        function renderCustomTabs() {
            const container = document.getElementById('custom-tabs-container');
            container.innerHTML = '';

            customRubricKeys.forEach(key => {
                const meta = customRubricMeta[key] || {};
                const label = rubricLabels[key] || key;
                const icon = meta.icon || 'fa-solid fa-star';
                const color = meta.color || '#6366f1';

                const wrapper = document.createElement('span');
                wrapper.className = 'custom-tab relative inline-block';
                wrapper.innerHTML = `
                    <button onclick="setRubricType('${key}')" id="tab-${key}" 
                        class="px-4 md:px-6 py-3 font-semibold border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap" 
                        style="color: #6b7280;">
                        <i class="${icon} mr-2"></i> ${label}
                    </button>
                    <span class="delete-badge" onclick="event.stopPropagation(); openDeleteModal('${key}', '${label.replace(/'/g, "\\'")}')" title="Eliminar rúbrica">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                `;
                container.appendChild(wrapper);
            });
        }

        // ================================================================
        // WIZARD — CONSTRUCTOR DE RÚBRICAS
        // ================================================================

        let wizCurrentStep = 1;
        const wizTotalSteps = 4;

        // Wizard data model
        let wizData = {
            name: '',
            icon: 'fa-solid fa-star',
            color: '#6366f1',
            criteria: [] // { id, title, weight, levels: [{desc}x4] }
        };

        // Available icons for picker
        const availableIcons = [
            'fa-solid fa-book', 'fa-solid fa-book-open', 'fa-solid fa-pen',
            'fa-solid fa-pencil', 'fa-solid fa-star', 'fa-solid fa-bookmark',
            'fa-solid fa-graduation-cap', 'fa-solid fa-school', 'fa-solid fa-chalkboard',
            'fa-solid fa-clipboard', 'fa-solid fa-file', 'fa-solid fa-folder-open',
            'fa-solid fa-microscope', 'fa-solid fa-flask', 'fa-solid fa-calculator',
            'fa-solid fa-paintbrush', 'fa-solid fa-music', 'fa-solid fa-globe',
            'fa-solid fa-heart', 'fa-solid fa-lightbulb', 'fa-solid fa-puzzle-piece',
            'fa-solid fa-trophy', 'fa-solid fa-medal', 'fa-solid fa-handshake',
            'fa-solid fa-brain', 'fa-solid fa-palette', 'fa-solid fa-ruler',
            'fa-solid fa-laptop', 'fa-solid fa-list-check', 'fa-solid fa-scissors',
            'fa-solid fa-marker', 'fa-solid fa-highlighter', 'fa-solid fa-note-sticky',
            'fa-solid fa-swatchbook', 'fa-solid fa-diagram-project', 'fa-solid fa-eye'
        ];

        // Available colors for picker
        const availableColors = [
            '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e',
            '#f97316', '#eab308', '#22c55e', '#06b6d4',
            '#3b82f6', '#14b8a6', '#a855f7', '#64748b'
        ];

        function openRubricBuilder() {
            wizCurrentStep = 1;
            wizData = {
                name: '',
                icon: 'fa-solid fa-star',
                color: '#6366f1',
                criteria: [
                    { id: 'c_1', title: '', weight: 50, levels: [{ desc: '' }, { desc: '' }, { desc: '' }, { desc: '' }] },
                    { id: 'c_2', title: '', weight: 50, levels: [{ desc: '' }, { desc: '' }, { desc: '' }, { desc: '' }] }
                ]
            };

            const modal = document.getElementById('rubricBuilderModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            renderWizardStep();
            renderIconPicker();
            renderColorPicker();
            renderCriteriaList();
            updateStepIndicator();
        }

        function closeRubricBuilder() {
            const modal = document.getElementById('rubricBuilderModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function updateStepIndicator() {
            for (let i = 1; i <= wizTotalSteps; i++) {
                const dot = document.getElementById(`step-dot-${i}`);
                dot.className = 'step-circle';
                if (i < wizCurrentStep) {
                    dot.className = 'step-circle completed';
                    dot.innerHTML = '<i class="fa-solid fa-check text-xs"></i>';
                } else if (i === wizCurrentStep) {
                    dot.className = 'step-circle active';
                    dot.innerHTML = i;
                } else {
                    dot.innerHTML = i;
                }

                if (i < wizTotalSteps) {
                    const line = document.getElementById(`step-line-${i}`);
                    line.className = i < wizCurrentStep ? 'step-line completed' : 'step-line';
                }
            }

            // Show/hide prev button
            document.getElementById('wiz-btn-prev').classList.toggle('hidden', wizCurrentStep === 1);
            // Show next or save
            document.getElementById('wiz-btn-next').classList.toggle('hidden', wizCurrentStep === wizTotalSteps);
            document.getElementById('wiz-btn-save').classList.toggle('hidden', wizCurrentStep !== wizTotalSteps);
        }

        function renderWizardStep() {
            for (let i = 1; i <= wizTotalSteps; i++) {
                const el = document.getElementById(`wizard-step-${i}`);
                if (i === wizCurrentStep) {
                    el.className = 'wizard-step-content active';
                } else if (i < wizCurrentStep) {
                    el.className = 'wizard-step-content to-left';
                } else {
                    el.className = 'wizard-step-content to-right';
                }
            }
        }

        function wizardNextStep() {
            if (!validateWizardStep()) return;
            if (wizCurrentStep < wizTotalSteps) {
                // If going to step 3, build descriptor UI
                if (wizCurrentStep === 2) {
                    syncCriteriaFromInputs();
                    renderDescriptors();
                }
                // If going to step 4, build preview
                if (wizCurrentStep === 3) {
                    syncDescriptorsFromInputs();
                    renderPreview();
                }
                wizCurrentStep++;
                renderWizardStep();
                updateStepIndicator();
            }
        }

        function wizardPrevStep() {
            if (wizCurrentStep > 1) {
                // Save current inputs before going back
                if (wizCurrentStep === 2) syncCriteriaFromInputs();
                if (wizCurrentStep === 3) syncDescriptorsFromInputs();
                wizCurrentStep--;
                renderWizardStep();
                updateStepIndicator();
            }
        }

        function validateWizardStep() {
            if (wizCurrentStep === 1) {
                const name = document.getElementById('wiz-name').value.trim();
                if (!name) {
                    document.getElementById('wiz-name').classList.add('border-red-400');
                    document.getElementById('wiz-name-hint').innerHTML = '<span class="text-red-500">⚠ El nombre es obligatorio</span>';
                    document.getElementById('wiz-name').focus();
                    return false;
                }
                // Check for duplicate key
                const key = slugify(name);
                if (rubricData[key] || predefinedLabels[key]) {
                    document.getElementById('wiz-name-hint').innerHTML = '<span class="text-red-500">⚠ Ya existe una rúbrica con este nombre</span>';
                    return false;
                }
                wizData.name = name;
                document.getElementById('wiz-name').classList.remove('border-red-400');
                document.getElementById('wiz-name-hint').innerHTML = '<span class="text-gray-400">Se usará como pestaña en el menú principal</span>';
                return true;
            }

            if (wizCurrentStep === 2) {
                syncCriteriaFromInputs();
                // Validate at least 1 criterion with name
                const valid = wizData.criteria.filter(c => c.title.trim() !== '');
                if (valid.length === 0) {
                    alert('Agrega al menos un criterio con nombre.');
                    return false;
                }
                // Remove empty criteria
                wizData.criteria = wizData.criteria.filter(c => c.title.trim() !== '');
                // Validate weights sum to 100
                const totalWeight = wizData.criteria.reduce((sum, c) => sum + (parseFloat(c.weight) || 0), 0);
                if (Math.abs(totalWeight - 100) > 1) {
                    alert(`Los pesos deben sumar 100%. Actualmente suman ${totalWeight}%.`);
                    return false;
                }
                // Normalize weights to exactly 100
                if (totalWeight !== 100) {
                    const diff = 100 - totalWeight;
                    wizData.criteria[wizData.criteria.length - 1].weight += diff;
                }
                return true;
            }

            if (wizCurrentStep === 3) {
                syncDescriptorsFromInputs();
                return true;
            }

            return true;
        }

        // ---- Step 1 Renderers ----
        function renderIconPicker() {
            const grid = document.getElementById('icon-picker-grid');
            grid.innerHTML = '';
            availableIcons.forEach(icon => {
                const div = document.createElement('div');
                div.className = `icon-option ${icon === wizData.icon ? 'selected' : ''}`;
                div.innerHTML = `<i class="${icon}"></i>`;
                div.onclick = () => {
                    wizData.icon = icon;
                    grid.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
                    div.classList.add('selected');
                };
                grid.appendChild(div);
            });
        }

        function renderColorPicker() {
            const grid = document.getElementById('color-picker-grid');
            grid.innerHTML = '';
            availableColors.forEach(color => {
                const div = document.createElement('div');
                div.className = `color-option ${color === wizData.color ? 'selected' : ''}`;
                div.style.backgroundColor = color;
                div.onclick = () => {
                    wizData.color = color;
                    grid.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
                    div.classList.add('selected');
                };
                grid.appendChild(div);
            });
        }

        // ---- Step 2: Criteria ----
        let criterionCounter = 2;

        function renderCriteriaList() {
            const container = document.getElementById('wiz-criteria-list');
            container.innerHTML = '';

            wizData.criteria.forEach((crit, idx) => {
                const card = document.createElement('div');
                card.className = 'criterion-card';
                card.innerHTML = `
                    <div class="flex items-start gap-3">
                        <div class="flex-1">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">Criterio ${idx + 1}</label>
                            <input type="text" class="wiz-crit-name w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" 
                                data-idx="${idx}" value="${crit.title}" placeholder="Ej: Limpieza y Orden">
                        </div>
                        <div class="w-28">
                            <label class="text-xs font-medium text-gray-500 mb-1 block">Peso %</label>
                            <input type="number" class="wiz-crit-weight w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-center font-semibold focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500" 
                                data-idx="${idx}" value="${crit.weight}" min="1" max="100" oninput="updateWeightBar()">
                        </div>
                        <button onclick="wizRemoveCriterion(${idx})" class="mt-5 w-8 h-8 rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 flex items-center justify-center transition-colors ${wizData.criteria.length <= 1 ? 'opacity-30 pointer-events-none' : ''}" title="Eliminar criterio">
                            <i class="fa-solid fa-trash-can text-xs"></i>
                        </button>
                    </div>
                `;
                container.appendChild(card);
            });

            updateWeightBar();
        }

        function wizAddCriterion() {
            criterionCounter++;
            wizData.criteria.push({
                id: `c_${criterionCounter}`,
                title: '',
                weight: 0,
                levels: [{ desc: '' }, { desc: '' }, { desc: '' }, { desc: '' }]
            });
            syncCriteriaFromInputs();
            renderCriteriaList();
        }

        function wizRemoveCriterion(idx) {
            if (wizData.criteria.length <= 1) return;
            syncCriteriaFromInputs();
            wizData.criteria.splice(idx, 1);
            renderCriteriaList();
        }

        function syncCriteriaFromInputs() {
            document.querySelectorAll('.wiz-crit-name').forEach(input => {
                const idx = parseInt(input.dataset.idx);
                if (wizData.criteria[idx]) wizData.criteria[idx].title = input.value.trim();
            });
            document.querySelectorAll('.wiz-crit-weight').forEach(input => {
                const idx = parseInt(input.dataset.idx);
                if (wizData.criteria[idx]) wizData.criteria[idx].weight = parseFloat(input.value) || 0;
            });
        }

        function updateWeightBar() {
            let total = 0;
            document.querySelectorAll('.wiz-crit-weight').forEach(input => {
                total += parseFloat(input.value) || 0;
            });

            const bar = document.getElementById('wiz-weight-bar');
            const label = document.getElementById('wiz-weight-label');
            const msg = document.getElementById('wiz-weight-msg');

            const pct = Math.min(total, 100);
            bar.style.width = pct + '%';
            label.textContent = total + '%';

            if (total === 100) {
                bar.style.background = '#22c55e';
                label.style.color = '#16a34a';
                msg.innerHTML = '<span class="text-green-600 font-medium">✓ Perfecto, los pesos suman 100%</span>';
            } else if (total > 100) {
                bar.style.background = '#ef4444';
                label.style.color = '#dc2626';
                msg.innerHTML = `<span class="text-red-500 font-medium">⚠ Exceso de ${total - 100}% — reduce algún peso</span>`;
            } else {
                bar.style.background = '#6366f1';
                label.style.color = '#4f46e5';
                msg.innerHTML = `<span class="text-gray-400">Faltan ${100 - total}% por asignar</span>`;
            }
        }

        // ---- Step 3: Descriptors ----
        function renderDescriptors() {
            const container = document.getElementById('wiz-descriptors-list');
            container.innerHTML = '';

            const levelNames = ['Superior (5.0)', 'Alto (4.2)', 'Básico (3.5)', 'Bajo (2.5)'];
            const levelColors = ['#16a34a', '#2563eb', '#ca8a04', '#dc2626'];
            const placeholders = [
                'Describe el desempeño superior...',
                'Describe el desempeño alto...',
                'Describe el desempeño básico...',
                'Describe el desempeño bajo...'
            ];

            wizData.criteria.forEach((crit, cIdx) => {
                const card = document.createElement('div');
                card.className = 'bg-white border rounded-xl overflow-hidden';
                
                let descriptorsHtml = '';
                levelNames.forEach((lName, lIdx) => {
                    descriptorsHtml += `
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-3 h-3 rounded-full mt-2.5 flex-shrink-0" style="background:${levelColors[lIdx]}"></span>
                            <div class="flex-1">
                                <label class="text-xs font-medium text-gray-500 mb-1 block">${lName}</label>
                                <textarea class="desc-textarea w-full border border-gray-200 rounded-lg px-3 py-2 text-sm resize-none focus:outline-none" 
                                    rows="2" data-crit="${cIdx}" data-level="${lIdx}" 
                                    placeholder="${placeholders[lIdx]}">${crit.levels[lIdx]?.desc || ''}</textarea>
                            </div>
                        </div>
                    `;
                });

                card.innerHTML = `
                    <div class="accordion-header px-4 py-3 bg-gray-50 border-b flex items-center justify-between" onclick="toggleAccordion(this)">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-grip-vertical text-gray-300 text-xs"></i>
                            <h5 class="font-semibold text-gray-800 text-sm">${crit.title}</h5>
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">${crit.weight}%</span>
                        </div>
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform accordion-arrow"></i>
                    </div>
                    <div class="accordion-body open">
                        <div class="p-4">${descriptorsHtml}</div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function toggleAccordion(header) {
            const body = header.nextElementSibling;
            const arrow = header.querySelector('.accordion-arrow');
            body.classList.toggle('open');
            arrow.style.transform = body.classList.contains('open') ? '' : 'rotate(-90deg)';
        }

        function syncDescriptorsFromInputs() {
            document.querySelectorAll('.desc-textarea').forEach(textarea => {
                const cIdx = parseInt(textarea.dataset.crit);
                const lIdx = parseInt(textarea.dataset.level);
                if (wizData.criteria[cIdx] && wizData.criteria[cIdx].levels[lIdx]) {
                    wizData.criteria[cIdx].levels[lIdx].desc = textarea.value.trim();
                }
            });
        }

        // ---- Step 4: Preview ----
        function renderPreview() {
            const container = document.getElementById('wiz-preview-container');
            const meta = { icon: wizData.icon, color: wizData.color };
            
            let html = `
                <div class="flex items-center gap-3 mb-4 p-3 rounded-xl border" style="background: ${meta.color}10; border-color: ${meta.color}30;">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white" style="background: ${meta.color}">
                        <i class="${meta.icon}"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900">${wizData.name}</h4>
                        <p class="text-xs text-gray-500">${wizData.criteria.length} criterio(s) · Escala 1-5</p>
                    </div>
                </div>
            `;

            wizData.criteria.forEach(crit => {
                html += `
                    <div class="bg-white border rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-4 py-2.5 border-b flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-gray-800 text-sm">${crit.title}</span>
                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Peso: ${crit.weight}%</span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-3">
                            ${crit.levels.map((lvl, i) => {
                                const names = ['Superior', 'Alto', 'Básico', 'Bajo'];
                                const scores = [5.0, 4.2, 3.5, 2.5];
                                const bColors = ['border-green-500', 'border-blue-500', 'border-yellow-500', 'border-red-500'];
                                return `
                                    <div class="bg-gray-50 p-3 rounded-md border-t-4 ${bColors[i]}">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-bold text-gray-800 text-xs">${names[i]}</span>
                                            <span class="text-xs text-gray-400">${scores[i]}</span>
                                        </div>
                                        <p class="text-xs text-gray-600">${lvl.desc || '<span class=\'italic text-gray-400\'>Sin descripción</span>'}</p>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // ---- Save New Rubric ----
        async function saveNewRubric() {
            const rubricKey = slugify(wizData.name);
            
            // Build criteria JSON for backend
            const criteriaForDB = wizData.criteria.map((crit, idx) => ({
                id: `${rubricKey}_${idx + 1}`,
                title: crit.title,
                weight: crit.weight,
                levels: [
                    { name: 'Superior', score: 5.0, desc: crit.levels[0]?.desc || 'Desempeño superior.' },
                    { name: 'Alto', score: 4.2, desc: crit.levels[1]?.desc || 'Desempeño alto.' },
                    { name: 'Básico', score: 3.5, desc: crit.levels[2]?.desc || 'Desempeño básico.' },
                    { name: 'Bajo', score: 2.5, desc: crit.levels[3]?.desc || 'Desempeño bajo.' }
                ]
            }));

            const payload = {
                rubricKey: rubricKey,
                rubricName: wizData.name,
                iconClass: wizData.icon,
                colorHex: wizData.color,
                criteria: criteriaForDB
            };

            try {
                const res = await fetch(window.location.pathname + '?action=save_custom_rubric', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (result.success) {
                    closeRubricBuilder();
                    await loadCustomRubrics();
                    setRubricType(rubricKey);
                    showSuccessToast('¡Rúbrica creada exitosamente!');
                } else {
                    alert('Error al guardar: ' + (result.error || 'Error desconocido'));
                }
            } catch(e) {
                // Fallback: save locally in memory
                customRubricKeys.add(rubricKey);
                rubricLabels[rubricKey] = wizData.name;
                customRubricMeta[rubricKey] = { icon: wizData.icon, color: wizData.color };
                rubricData[rubricKey] = criteriaForDB.map(crit => ({
                    id: crit.id,
                    title: crit.title,
                    weight: crit.weight,
                    levels: crit.levels.map(lvl => ({
                        name: lvl.name, score: lvl.score,
                        class: `selected-${lvl.name.toLowerCase()}`,
                        desc: lvl.desc
                    }))
                }));
                renderCustomTabs();
                buildHistoryTabs();
                closeRubricBuilder();
                setRubricType(rubricKey);
                showSuccessToast('Rúbrica creada localmente (sin servidor PHP)');
            }
        }

        // ---- Delete Rubric ----
        let pendingDeleteKey = null;

        function openDeleteModal(key, name) {
            pendingDeleteKey = key;
            document.getElementById('delete-rubric-name').textContent = name;
            const modal = document.getElementById('deleteConfirmModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteConfirmModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingDeleteKey = null;
        }

        async function confirmDeleteRubric() {
            if (!pendingDeleteKey) return;
            const key = pendingDeleteKey;

            try {
                const res = await fetch(window.location.pathname + '?action=delete_custom_rubric', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ rubricKey: key })
                });
                const result = await res.json();

                if (result.success) {
                    removeRubricLocally(key);
                    showSuccessToast('Rúbrica eliminada correctamente');
                } else {
                    alert('Error: ' + (result.error || 'No se pudo eliminar'));
                }
            } catch(e) {
                // Remove locally anyway
                removeRubricLocally(key);
                showSuccessToast('Rúbrica eliminada localmente');
            }

            closeDeleteModal();
        }

        function removeRubricLocally(key) {
            customRubricKeys.delete(key);
            delete rubricData[key];
            delete rubricLabels[key];
            delete customRubricMeta[key];
            renderCustomTabs();
            buildHistoryTabs();
            if (currentType === key) {
                setRubricType('oral');
            }
        }

        // ---- Utilities ----
        function slugify(text) {
            return text.toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
        }

        function showSuccessToast(msg) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 z-[80] bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 text-sm font-medium';
            toast.style.animation = 'slideUp 0.4s cubic-bezier(0.16,1,0.3,1)';
            toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> ${msg}`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // ================================================================
        // PRINT VIEW
        // ================================================================
        function printRubric() {
            const data = rubricData[currentType];
            if (!data) return;
            const label = rubricLabels[currentType] || currentType;
            const isCustom = customRubricKeys.has(currentType);

            // Fill student info
            document.getElementById('print-rubric-type-title').textContent = label;
            document.getElementById('print-student').textContent = document.getElementById('studentName').value || '_________________';
            document.getElementById('print-grade').textContent = document.getElementById('studentGrade').value || '______';
            document.getElementById('print-subject').textContent = document.getElementById('subject').value || '_______________';
            document.getElementById('print-date').textContent = document.getElementById('evalDate').value || '___________';

            // Build table
            let html = '<table>';
            html += '<thead><tr><th style="width:20%">Criterio</th>';
            if (isCustom) html += '<th style="width:8%">Peso</th>';
            html += '<th style="width:18%; background:#f0fdf4;">Superior (5.0)</th>';
            html += '<th style="width:18%; background:#eff6ff;">Alto (4.2)</th>';
            html += '<th style="width:18%; background:#fefce8;">Básico (3.5)</th>';
            html += '<th style="width:18%; background:#fef2f2;">Bajo (2.5)</th>';
            html += '<th style="width:8%">Nota</th></tr></thead><tbody>';

            data.forEach(crit => {
                html += '<tr>';
                html += `<td style="font-weight:600">${crit.title}</td>`;
                if (isCustom) html += `<td style="text-align:center">${crit.weight || ''}%</td>`;
                crit.levels.forEach(lvl => {
                    html += `<td>${lvl.desc}</td>`;
                });
                html += '<td style="text-align:center"></td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            document.getElementById('print-rubric-table').innerHTML = html;

            window.print();
        }

        // ================================================================
        // TAB REORDER
        // ================================================================
        let reorderList = [];

        function getTabOrder() {
            const saved = localStorage.getItem('rubric_tab_order');
            if (saved) {
                try { return JSON.parse(saved); } catch(e) {}
            }
            return null;
        }

        function openReorderModal() {
            const savedOrder = getTabOrder();
            const allKeys = getAllTabKeys();

            if (savedOrder) {
                reorderList = savedOrder.filter(k => allKeys.includes(k));
                allKeys.forEach(k => { if (!reorderList.includes(k)) reorderList.push(k); });
            } else {
                reorderList = [...allKeys];
            }

            renderReorderList();
            const modal = document.getElementById('reorderModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeReorderModal() {
            const modal = document.getElementById('reorderModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function getAllTabKeys() {
            const keys = Object.keys(predefinedLabels);
            customRubricKeys.forEach(k => keys.push(k));
            return keys;
        }

        function renderReorderList() {
            const container = document.getElementById('reorder-list');
            container.innerHTML = '';
            reorderList.forEach((key, idx) => {
                const label = rubricLabels[key] || key;
                const isCustom = customRubricKeys.has(key);
                const meta = customRubricMeta[key] || {};
                const icon = isCustom ? (meta.icon || 'fa-solid fa-star') : getPredefIcon(key);
                const badge = isCustom ? '<span class="text-xs bg-indigo-100 text-indigo-700 px-1.5 py-0.5 rounded-full">Custom</span>' : '';

                const div = document.createElement('div');
                div.className = 'reorder-item';
                div.innerHTML = `
                    <i class="fa-solid fa-grip-vertical drag-handle"></i>
                    <i class="${icon} text-gray-600"></i>
                    <span class="flex-1 font-medium text-gray-800 text-sm">${label}</span>
                    ${badge}
                    <div class="flex gap-1">
                        <button class="reorder-arrow" onclick="moveTab(${idx},-1)" ${idx===0?'disabled':''}><i class="fa-solid fa-chevron-up"></i></button>
                        <button class="reorder-arrow" onclick="moveTab(${idx},1)" ${idx===reorderList.length-1?'disabled':''}><i class="fa-solid fa-chevron-down"></i></button>
                    </div>
                `;
                container.appendChild(div);
            });
        }

        function getPredefIcon(key) {
            const icons = { oral:'fa-solid fa-microphone-lines', escrita:'fa-solid fa-pen-nib', equipo:'fa-solid fa-users', tertulia:'fa-solid fa-comments', tarea:'fa-solid fa-book-open', exposicion:'fa-solid fa-person-chalkboard' };
            return icons[key] || 'fa-solid fa-star';
        }

        function moveTab(idx, dir) {
            const newIdx = idx + dir;
            if (newIdx < 0 || newIdx >= reorderList.length) return;
            [reorderList[idx], reorderList[newIdx]] = [reorderList[newIdx], reorderList[idx]];
            renderReorderList();
        }

        function saveTabOrder() {
            localStorage.setItem('rubric_tab_order', JSON.stringify(reorderList));
            applyTabOrder();
            closeReorderModal();
            showSuccessToast('Orden de pestañas guardado');
        }

        function applyTabOrder() {
            const savedOrder = getTabOrder();
            if (!savedOrder) return;

            const tabsContainer = document.getElementById('main-tabs');
            const newRubricBtn = document.getElementById('btn-new-rubric');

            // Collect all tab elements
            const tabEls = {};
            savedOrder.forEach(key => {
                const el = document.getElementById('tab-' + key);
                if (el) {
                    const wrapper = el.closest('.custom-tab');
                    tabEls[key] = wrapper || el;
                }
            });

            // Remove all tab buttons
            Object.values(tabEls).forEach(el => {
                if (el.parentNode) el.parentNode.removeChild(el);
            });

            // Clear custom tabs container
            const customContainer = document.getElementById('custom-tabs-container');
            customContainer.innerHTML = '';

            // Re-insert all tabs in saved order
            savedOrder.forEach(key => {
                if (customRubricKeys.has(key)) {
                    const meta = customRubricMeta[key] || {};
                    const label = rubricLabels[key] || key;
                    const icon = meta.icon || 'fa-solid fa-star';
                    const wrapper = document.createElement('span');
                    wrapper.className = 'custom-tab relative inline-block';
                    wrapper.innerHTML = `
                        <button onclick="setRubricType('${key}')" id="tab-${key}"
                            class="px-4 md:px-6 py-3 font-semibold border-b-2 border-transparent hover:bg-gray-50 transition-colors whitespace-nowrap"
                            style="color: #6b7280;">
                            <i class="${icon} mr-2"></i> ${label}
                        </button>
                        <span class="delete-badge" onclick="event.stopPropagation(); openDeleteModal('${key}', '${label.replace(/'/g, "\\\'")}')" title="Eliminar rúbrica">
                            <i class="fa-solid fa-xmark"></i>
                        </span>
                    `;
                    tabsContainer.insertBefore(wrapper, newRubricBtn);
                } else if (tabEls[key]) {
                    tabsContainer.insertBefore(tabEls[key], newRubricBtn);
                }
            });

            // Re-apply active state
            setRubricType(currentType);
        }

        // ================================================================
        // INICIALIZACIÓN GENERAL
        // ================================================================
        setRubricType('oral');
        buildHistoryTabs();
        // Disparar carga inicial de la tabla (intentará buscar el backend)
        fetchRecords();
        // Cargar rúbricas personalizadas desde la BD
        loadCustomRubrics().then(() => { applyTabOrder(); });

    </script>
</body>
</html>