<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../comun/database.php';

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$catalog = require dirname(__DIR__) . '/rubricas_sie.php';
function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException($message);
}
$expected = ['oral' => 5, 'escrita' => 5, 'equipo' => 5, 'tertulia' => 7, 'tarea' => 5, 'exposicion' => 5, 'autoevaluacion' => 7, 'coevaluacion' => 6];
foreach ($expected as $type => $count) {
    $criteria = $catalog['criteria'][$type];
    check(count($criteria) === $count, 'Cantidad de criterios incorrecta: ' . $type);
    check(count(array_unique(array_column($criteria, 'id'))) === $count, 'IDs duplicados: ' . $type);
    foreach ($criteria as $criterion) {
        check(in_array($criterion['competence'], ['SABER', 'HACER', 'SER'], true), 'Competencia inválida');
        check(count($criterion['levels']) === 4, 'Faltan niveles');
        foreach ($criterion['levels'] as $level) check(strlen($level['desc']) > 10, 'Descripción vacía');
    }
}
check($catalog['criteria']['oral'] === $catalog['criteria']['escrita'], 'La rúbrica oral y escrita del SIE debe coincidir');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = guagua_db_mysqli();

$before = $db->query('SELECT id, student_grade, final_score, criterion_scores FROM evaluations WHERE id BETWEEN 1 AND 15 ORDER BY id')->fetch_all(MYSQLI_ASSOC);
$marker = '__SIE_TEST_' . bin2hex(random_bytes(8));
function request(array $payload): array {
    $context = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => json_encode($payload), 'ignore_errors' => true, 'timeout' => 20]]);
    $text = file_get_contents('http://localhost/guagua/apps/rubrica/index.php?action=save&guagua_embedded=1', false, $context);
    return json_decode($text, true, 512, JSON_THROW_ON_ERROR);
}
$payload = ['studentName' => $marker, 'studentGrade' => '11', 'subject' => 'Prueba temporal SIE', 'evalDate' => '2026-10-08', 'rubricType' => 'oral', 'finalScore' => 1, 'finalLevel' => 'incorrecto', 'observations' => '', 'criterionScores' => []];
try {
    check(isset(request($payload)['error']), 'Se guardó una evaluación incompleta');
    foreach ($catalog['criteria']['oral'] as $i => $criterion) {
        $payload['criterionScores'][] = ['id' => $criterion['id'], 'title' => 'Título cliente que debe ser reemplazado', 'score' => [4.5, 4.6, 4.6, 4.6, 4.5][$i], 'weight' => 0];
    }
    $invalid = $payload; $invalid['criterionScores'][0]['score'] = 5.1;
    check(isset(request($invalid)['error']), 'Se aceptó una nota fuera de escala');
    $unknown = $payload; $unknown['criterionScores'][0]['id'] = 'no_existe';
    check(isset(request($unknown)['error']), 'Se aceptó un criterio desconocido');
    check(!empty(request($payload)['success']), 'No se pudo guardar el detalle SIE');
    $stmt = $db->prepare('SELECT * FROM evaluations WHERE student_name = ?');
    $stmt->bind_param('s', $marker); $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    check($row !== null && (float) $row['final_score'] === 4.6 && $row['final_level'] === 'Desempeño Superior', 'Promedio o desempeño incorrecto en límite de redondeo');
    $details = json_decode($row['criterion_scores'], true);
    check($details[0]['title'] === 'Habilidad argumentativa' && $details[0]['competence'] === 'SABER' && $details[0]['weight'] == 20, 'El servidor no conserva el criterio oficial');
    check($details[0]['rubric_version'] === $catalog['version'], 'No se guardó la versión del SIE');
    $report = file_get_contents('http://localhost/guagua/apps/rubrica/reporte.php?type=oral&id=' . (int) $row['id']);
    check(str_contains($report, 'SIE 2024') && str_contains($report, 'Habilidad argumentativa') && str_contains($report, 'SABER'), 'El reporte no muestra fuente, ítems y competencia');
    check(!str_contains($report, 'Notas estimadas / 5,0'), 'Una evaluación nueva fue marcada como estimada');
} finally {
    // Solo se elimina el registro temporal identificado por un marcador único.
    $stmt = $db->prepare('DELETE FROM evaluations WHERE student_name = ?');
    $stmt->bind_param('s', $marker); $stmt->execute();
}
$after = $db->query('SELECT id, student_grade, final_score, criterion_scores FROM evaluations WHERE id BETWEEN 1 AND 15 ORDER BY id')->fetch_all(MYSQLI_ASSOC);
check($before === $after, 'Se alteraron evaluaciones históricas');
echo "OK: 8 categorías SIE, validación de notas e ítems, promedio, versión y reporte. Los 15 registros históricos siguen iguales. Prueba temporal eliminada.\n";
