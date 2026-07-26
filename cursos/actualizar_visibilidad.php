<?php
/**
 * actualizar_visibilidad.php
 *
 * Versión final que lee datos desde un payload JSON.
 *
 * Mejoras Clave:
 * 1.  LECTURA DE JSON: Ya no usa $_POST. Lee el cuerpo de la petición raw con file_get_contents.
 * 2.  DECODIFICACIÓN: Utiliza json_decode para convertir el string JSON en un array de PHP.
 * 3.  ROBUSTEZ: Este método es más fiable y menos propenso a problemas de configuración del servidor.
 */

// Habilitar reporte de errores para diagnóstico
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configurar la cabecera para devolver una respuesta JSON
header('Content-Type: application/json');

// --- INICIO DE LA LÓGICA PARA LEER JSON ---
// 1. Leer el cuerpo de la petición raw.
$json_data = file_get_contents('php://input');

// 2. Decodificar el JSON a un array asociativo de PHP.
//    El 'true' es importante para que lo convierta en array y no en objeto.
$data = json_decode($json_data, true);

// 3. Verificar si la decodificación fue exitosa y si los datos están presentes.
if (json_last_error() !== JSON_ERROR_NONE || !isset($data['id_asignacion']) || !isset($data['visible'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error Crítico: El servidor no recibió datos JSON válidos.'
    ]);
    exit;
}
// --- FIN DE LA LÓGICA PARA LEER JSON ---


// Incluir los archivos necesarios para la conexión a la base de datos
require_once(dirname(__DIR__) . '/comun/conexion.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que la conexión a la BD se estableció correctamente
if (!isset($mysqli) || $mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error Crítico: No se pudo establecer la conexión con la base de datos.']);
    exit;
}

// Verificar que el usuario tenga permisos
$rol = $_SESSION['rol'] ?? 'invitado';
if ($rol !== 'admin' && $rol !== 'docente') {
    echo json_encode(['success' => false, 'message' => 'Error de Permiso: Acceso denegado.']);
    exit;
}

// Ahora usamos la variable $data en lugar de $_POST
$id_asignacion = $data['id_asignacion'];
$visible = $data['visible'];

// Validar los datos recibidos
if (!filter_var($id_asignacion, FILTER_VALIDATE_INT) || !in_array($visible, ['si', 'no'])) {
    echo json_encode(['success' => false, 'message' => 'Error de Datos: Los datos recibidos son inválidos.']);
    exit;
}

// Preparar la consulta SQL
$sql = "UPDATE asignacion SET visible = ? WHERE id_asignacion = ?";
$stmt = $mysqli->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $visible, $id_asignacion);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error de Ejecución: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error de Preparación: ' . $mysqli->error]);
}

$mysqli->close();
?>

