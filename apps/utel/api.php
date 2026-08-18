<?php
/**
 * Fuente de materias activas de Guagua para el organizador UTEL.
 * Las tareas personales siguen siendo locales; esta ruta solo expone las
 * asignaciones a las que tiene acceso la sesión actual.
 */
header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__, 2) . '/comun/autoload.php';
require_once SGA_COMUN_SERVER . '/conexion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function responder($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_GET['accion'] ?? '') !== 'asignaciones_activas') {
    responder(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
}

$rol = $_SESSION['rol'] ?? '';
$idUsuario = (string) ($_SESSION['id_usuario'] ?? '');
$idInstitucion = (int) ($_SESSION['id_institucion'] ?? 0);

// UTEL no debe publicar la carga académica de todos los docentes cuando no
// hay una sesión de Guagua activa.
if ($idUsuario === '') {
    responder(['ok' => true, 'sesion' => false, 'asignaciones' => [], 'mensaje' => 'Inicia sesión en Guagua para sincronizar tus asignaciones.']);
}

$sql = "SELECT DISTINCT a.id_asignacion, mo.nombre_materia, cc.nombre_categoria_curso,
               a.ano_lectivo, a.descripcion
        FROM asignacion a
        INNER JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
        INNER JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
        LEFT JOIN ano_lectivo al ON al.id_ano_lectivo = a.ano_lectivo
        WHERE a.id_docente = ?
          AND LOWER(COALESCE(a.visible, 'si')) = 'si'";
$types = 's';
$params = [$idUsuario];

if ($idInstitucion > 0) {
    $sql .= ' AND a.institucion_educativa = ?';
    $types .= 'i';
    $params[] = $idInstitucion;
}

// Cuando la institución marca el año lectivo, se prioriza. Se dejan pasar
// registros sin estado para mantener compatibilidad con instalaciones antiguas.
$sql .= " AND (al.estado IS NULL OR LOWER(al.estado) IN ('activo', 'activa', 'en curso'))
          ORDER BY mo.nombre_materia, cc.nombre_categoria_curso";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    responder(['ok' => false, 'mensaje' => 'No fue posible consultar las asignaciones.'], 500);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$asignaciones = [];
while ($fila = $result->fetch_assoc()) {
    $asignaciones[] = [
        'id' => 'guagua_' . $fila['id_asignacion'],
        'assignmentId' => (int) $fila['id_asignacion'],
        'name' => $fila['nombre_materia'] . ' · ' . $fila['nombre_categoria_curso'],
        'shortName' => $fila['nombre_materia'],
        'group' => $fila['nombre_categoria_curso'],
        'description' => $fila['descripcion'] ?? '',
        'source' => 'guagua',
        'color' => '#0f766e'
    ];
}
$stmt->close();

responder(['ok' => true, 'sesion' => true, 'asignaciones' => $asignaciones]);
