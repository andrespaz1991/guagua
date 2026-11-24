<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin', 'directivo', 'docente']);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST'); guagua_error_acceso(405, 'Use POST para modificar la visibilidad.');
}
guagua_exigir_csrf();
$data = json_decode(file_get_contents('php://input'), true);
$id = is_array($data) ? filter_var($data['id_asignacion'] ?? null, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) : false;
$visible = is_array($data) ? ($data['visible'] ?? null) : null;
if (!$id || !in_array($visible, ['si','no'], true)) guagua_error_acceso(422, 'Curso o visibilidad inválidos.');
require_once __DIR__ . '/../comun/database.php';
try {
    $db = guagua_db_mysqli();
    $where = ' WHERE id_asignacion = ? AND institucion_educativa = ?';
    $institution = (int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0);
    if ($institution <= 0) guagua_error_acceso(403, 'Seleccione una institución.');
    $params = [$id,$institution]; $types = 'ii';
    if ($_SESSION['rol'] === 'docente') {
        $where .= ' AND id_docente = ?'; $params[] = (string)$_SESSION['id_usuario']; $types .= 's';
    }
    $check = $db->prepare('SELECT id_asignacion FROM asignacion' . $where);
    $check->bind_param($types,...$params); $check->execute();
    if (!$check->get_result()->fetch_row()) guagua_error_acceso(403, 'No tiene permiso para modificar este curso.');
    $check->close();
    // Repetir el permiso en UPDATE también evita cambios si la propiedad se modifica.
    $statement = $db->prepare('UPDATE asignacion SET visible = ?' . $where);
    $updateParams = [$visible,...$params]; $updateTypes = 's' . $types;
    $statement->bind_param($updateTypes, ...$updateParams);
    if (!$statement->execute()) throw new RuntimeException('No se pudo actualizar la visibilidad.');
    $statement->close();
    echo json_encode(['success'=>true]);
} catch (Throwable $error) {
    error_log('[Guagua visibilidad] ' . get_class($error) . ' (' . $error->getCode() . ')');
    http_response_code(500);
    echo json_encode(['success'=>false, 'message'=>'No se pudo guardar la visibilidad.']);
}
