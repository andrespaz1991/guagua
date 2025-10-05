<?php
// --- API para gestionar la configuración de reportes ---
// Este archivo actúa como un puente entre la interfaz (config_module.php) y la base de datos.

header('Content-Type: application/json'); // Asegura que la respuesta sea siempre JSON

// --- Configuración de la Conexión a la Base de Datos ---
$servername = "127.0.0.1:7000"; // o el host de tu BD, ej: "localhost"
$username   = "root";           // tu usuario de la BD
$password   = "";               // tu contraseña de la BD
$dbname     = "guagua";         // el nombre de tu base de datos

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'get_config':
        getConfig($conn);
        break;
    case 'save_docente':
        saveDocente($conn);
        break;
    case 'save_sede':
        saveSede($conn);
        break;
    case 'save_group':
        saveGroup($conn);
        break;
    case 'delete_group':
        deleteGroup($conn);
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
}

$conn = null;

// --- FUNCIONES DE LA API ---

function getConfig($conn) {
    $config = [
        'docente' => [],
        'sede'    => '',
        'grupos'  => []
    ];
    
    // 1. Obtener Docente
    $stmt_docente = $conn->prepare("SELECT id, nombre, telefono FROM config_docentes LIMIT 1");
    $stmt_docente->execute();
    $docente = $stmt_docente->fetch(PDO::FETCH_ASSOC);
    if ($docente) {
        $config['docente'] = $docente;
    }

    // 2. Obtener Sede
    $stmt_sede = $conn->prepare("SELECT id, nombre FROM config_sedes LIMIT 1");
    $stmt_sede->execute();
    $sede = $stmt_sede->fetch(PDO::FETCH_ASSOC);
    if ($sede) {
        $config['sede'] = $sede;
    }

    // 3. Obtener Grupos y Materias
    $stmt_grupos = $conn->prepare("SELECT id, nombre, min_grado, max_grado, ruta_excel, ultimafila FROM config_grupos ORDER BY id");
    $stmt_grupos->execute();
    $grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_materias = $conn->prepare("SELECT grupo_id, nombre_materia, columna_excel FROM config_materias");
    $stmt_materias->execute();
    $all_materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);
    
    $materias_por_grupo = [];
    foreach ($all_materias as $materia) {
        $materias_por_grupo[$materia['grupo_id']][] = ['nombre' => $materia['nombre_materia'], 'columna' => $materia['columna_excel']];
    }
    
    foreach ($grupos as $grupo) {
        $grupo['materias'] = $materias_por_grupo[$grupo['id']] ?? [];
        $config['grupos'][] = $grupo;
    }

    echo json_encode(['status' => 'success', 'data' => $config]);
}

function saveDocente($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'], $data['nombre'], $data['telefono'])) {
        $stmt = $conn->prepare("UPDATE config_docentes SET nombre = :nombre, telefono = :telefono WHERE id = :id");
        $stmt->execute([':nombre' => $data['nombre'], ':telefono' => $data['telefono'], ':id' => $data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Docente actualizado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    }
}

function saveSede($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'], $data['nombre'])) {
        $stmt = $conn->prepare("UPDATE config_sedes SET nombre = :nombre WHERE id = :id");
        $stmt->execute([':nombre' => $data['nombre'], ':id' => $data['id']]);
        echo json_encode(['status' => 'success', 'message' => 'Sede actualizada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos.']);
    }
}

function saveGroup($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $is_new = empty($data['id']);

    if ($is_new) {
        // --- Crear nuevo grupo ---
        $stmt = $conn->prepare("INSERT INTO config_grupos (nombre, min_grado, max_grado, ruta_excel, ultimafila) VALUES (:nombre, :min_grado, :max_grado, :ruta_excel, :ultimafila)");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':min_grado' => $data['min_grado'],
            ':max_grado' => $data['max_grado'],
            ':ruta_excel' => $data['ruta_excel'],
            ':ultimafila' => $data['ultimafila']
        ]);
        $groupId = $conn->lastInsertId();
    } else {
        // --- Actualizar grupo existente ---
        $groupId = $data['id'];
        $stmt = $conn->prepare("UPDATE config_grupos SET nombre = :nombre, min_grado = :min_grado, max_grado = :max_grado, ruta_excel = :ruta_excel, ultimafila = :ultimafila WHERE id = :id");
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':min_grado' => $data['min_grado'],
            ':max_grado' => $data['max_grado'],
            ':ruta_excel' => $data['ruta_excel'],
            ':ultimafila' => $data['ultimafila'],
            ':id' => $groupId
        ]);
        
        // Borrar materias antiguas para reinsertarlas
        $stmt_delete = $conn->prepare("DELETE FROM config_materias WHERE grupo_id = :grupo_id");
        $stmt_delete->execute([':grupo_id' => $groupId]);
    }

    // --- Insertar/Reinsertar materias ---
    if (!empty($data['materias'])) {
        $stmt_materia = $conn->prepare("INSERT INTO config_materias (grupo_id, nombre_materia, columna_excel) VALUES (:grupo_id, :nombre_materia, :columna_excel)");
        foreach ($data['materias'] as $materia) {
            $stmt_materia->execute([
                ':grupo_id' => $groupId,
                ':nombre_materia' => $materia['nombre'],
                ':columna_excel' => $materia['columna']
            ]);
        }
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Grupo guardado correctamente.']);
}

function deleteGroup($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'])) {
        $groupId = $data['id'];
        
        // Primero borrar las materias asociadas
        $stmt_delete_materias = $conn->prepare("DELETE FROM config_materias WHERE grupo_id = :grupo_id");
        $stmt_delete_materias->execute([':grupo_id' => $groupId]);

        // Luego borrar el grupo
        $stmt_delete_grupo = $conn->prepare("DELETE FROM config_grupos WHERE id = :id");
        $stmt_delete_grupo->execute([':id' => $groupId]);

        echo json_encode(['status' => 'success', 'message' => 'Grupo eliminado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID de grupo no proporcionado.']);
    }
}
?>
