<?php
declare(strict_types=1);

require_once __DIR__ . '/../comun/conexion.php';
guagua_exigir_rol(['admin', 'directivo', 'docente']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Use una solicitud POST.');
}
guagua_exigir_csrf();

$assignmentId = filter_input(INPUT_POST, 'asignacion', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$studentId = trim((string) ($_POST['estudiante'] ?? ''));
if (!$assignmentId || $studentId === '' || !preg_match('/^[A-Za-z0-9._-]{1,50}$/', $studentId)) {
    http_response_code(422);
    exit('Datos de inscripción no válidos.');
}

// Evita inscripciones duplicadas y nunca concatena datos de la solicitud en SQL.
$exists = $mysqli->prepare('SELECT 1 FROM inscripcion WHERE id_asignacion = ? AND id_estudiante = ? LIMIT 1');
$exists->bind_param('is', $assignmentId, $studentId);
$exists->execute();
$alreadyEnrolled = $exists->get_result()->num_rows > 0;
$exists->close();
if ($alreadyEnrolled) {
    http_response_code(409);
    exit('El estudiante ya está inscrito en esta asignación.');
}

$statement = $mysqli->prepare("INSERT INTO inscripcion (id_asignacion, id_estudiante, fecha_inscripcion, estado_inscripcion) VALUES (?, ?, CURDATE(), 'Aprobado')");
$statement->bind_param('is', $assignmentId, $studentId);
$saved = $statement->execute();
$statement->close();
if (!$saved) {
    http_response_code(500);
    exit('No fue posible registrar la inscripción.');
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
