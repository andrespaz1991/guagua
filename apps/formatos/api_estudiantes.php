<?php
require_once dirname(__DIR__, 2) . '/comun/autoload.php';
require_once SGA_COMUN_SERVER . '/conexion.php';
require_once __DIR__ . '/lib/FormatosService.php';

formatos_require_access();
header('Content-Type: application/json; charset=utf-8');

try {
    formatos_ensure_schema($mysqli);
    $query = trim((string)($_GET['q'] ?? ''));
    if (mb_strlen($query, 'UTF-8') < 2) {
        echo json_encode(['items' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $needle = mb_strtolower($query, 'UTF-8');
    $items = [];
    foreach (formatos_students($mysqli, formatos_context($mysqli)) as $student) {
        $searchable = mb_strtolower(formatos_full_name($student) . ' ' . $student['id_usuario'] . ' ' . ($student['grado'] ?? ''), 'UTF-8');
        if (mb_strpos($searchable, $needle) === false) continue;
        $items[] = [
            'id' => (string)$student['id_usuario'],
            'nombre' => formatos_full_name($student),
            'grado' => (string)($student['grado'] ?: 'Sin grado'),
            'grupo' => (string)($student['grupo'] ?: ''),
        ];
        if (count($items) === 20) break;
    }
    echo json_encode(['items' => $items], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'No fue posible consultar los estudiantes.'], JSON_UNESCAPED_UNICODE);
}
