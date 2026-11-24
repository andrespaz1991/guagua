<?php
declare(strict_types=1);
require_once __DIR__ . '/../../comun/database.php';

// Importación solicitada a partir de la imagen de notas. Solo por consola.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
date_default_timezone_set('America/Bogota');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$catalog = require __DIR__ . '/rubricas_sie.php';
$criteria = $catalog['criteria']['exposicion'];
$students = [
    ['DUQUE ARISTIZABAL ÁNGEL GABRIEL', '9', 2.5],
    ['GIRALDO GIRALDO JUAN CAMILO', '9', 4.5],
    ['GÓMEZ VERGARA MICHAEL STIVEN', '9', 1.0],
    ['LÓPEZ AGUIRRE ALEXIS', '9', 1.0],
    ['DUQUE ARISTIZABAL YENNIFER TATIANA', '10', 5.0],
    ['FRANCO BARRAZA GYSHET JOHANNA', '10', 5.0],
    ['GARCÍA NAVARRO SEBASTIAN CAMILO', '10', 4.5],
    ['GALLEGO GIRALDO MARLON', '10', 5.0],
    ['GÓMEZ PAMPLONA JULIETA', '10', 5.0],
    ['GONZÁLEZ MARTÍNEZ SALOMÉ', '10', 4.7],
    ['LEZCANO MORILLO EDWIN ALEXANDER', '10', 4.0],
    ['VALENCIA VÁSQUEZ ANDERSON', '10', 3.0],
    ['CAVIEDES HERNÁNDEZ CRISTINA', '11', 5.0]
];
$batch = 'exposicion-emprendimiento-imagen-78107f01-20261008';
$subject = 'Emprendimiento';
$type = 'exposicion';
$date = null; // La imagen suministrada no contiene fecha de evaluación.
$observations = '';
$db = guagua_db_mysqli();

$db->begin_transaction();
try {
    $original = $db->query('SELECT id, student_name, student_grade, final_score, criterion_scores FROM evaluations WHERE id BETWEEN 1 AND 15 ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    $check = $db->prepare("SELECT id, final_score, criterion_scores FROM evaluations WHERE rubric_type = ? AND subject = ? AND student_name = ? AND JSON_UNQUOTE(JSON_EXTRACT(criterion_scores, '$[0].import_batch')) = ? FOR UPDATE");
    $insert = $db->prepare('INSERT INTO evaluations (student_name, student_grade, subject, eval_date, rubric_type, final_score, final_level, observations, criterion_scores) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $created = 0; $verified = 0;
    foreach ($students as $index => [$name, $grade, $target]) {
        $targetTenths = (int) round($target * 10);
        $capacity = min($targetTenths - 10, 50 - $targetTenths);
        $factor = min(1 + $index % 3, intdiv($capacity, 2));
        $offsets = [-2, -1, 0, 1, 2];
        mt_srand(20261008 + $index * 17); shuffle($offsets);
        $details = [];
        foreach ($criteria as $i => $criterion) {
            $score = ($targetTenths + $offsets[$i] * $factor) / 10;
            $levelIndex = $score >= 4.6 ? 0 : ($score >= 4.0 ? 1 : ($score >= 3.0 ? 2 : 3));
            $details[] = ['id' => $criterion['id'], 'title' => $criterion['title'], 'competence' => $criterion['competence'],
                'weight' => 20, 'score' => $score, 'level' => $criterion['levels'][$levelIndex]['name'],
                'description' => $criterion['levels'][$levelIndex]['desc'],
                'rubric_version' => $catalog['version'], 'source' => $catalog['source'], 'source_pages' => $catalog['pages']['exposicion'],
                'estimated' => true, 'estimation_method' => $capacity === 0 ? 'Extremo de escala: única combinación posible' : 'Desviaciones balanceadas en décimas; conserva promedio original',
                'estimated_at' => date(DATE_ATOM), 'import_batch' => $batch,
                'final_score_source' => 'Columna Examen de la imagen suministrada por el docente'];
        }
        $check->bind_param('ssss', $type, $subject, $name, $batch); $check->execute();
        $existing = $check->get_result()->fetch_all(MYSQLI_ASSOC);
        if (count($existing) > 1) throw new RuntimeException('Lote duplicado');
        if ($existing) {
            $details = json_decode($existing[0]['criterion_scores'], true, 512, JSON_THROW_ON_ERROR);
            if (abs((float) $existing[0]['final_score'] - $target) > 0.000001) throw new RuntimeException('Nota final existente distinta de la imagen');
        }
        if (count($details) !== count($criteria)) throw new RuntimeException('Faltan criterios');
        $average = 0;
        foreach ($details as $i => $detail) {
            if ($detail['id'] !== $criteria[$i]['id'] || $detail['score'] < 1 || $detail['score'] > 5 || $detail['weight'] != 20) throw new RuntimeException('Criterio o nota inválida');
            $average += $detail['score'] * $detail['weight'] / 100;
        }
        if (abs($average - $target) > 0.000001) throw new RuntimeException('El promedio no coincide con la nota de examen');
        if ($capacity > 0 && count(array_unique(array_column($details, 'score'))) < 2) throw new RuntimeException('Se requieren valores variables para notas intermedias');
        if (!$existing) {
            $level = $target >= 4.6 ? 'Desempeño Superior' : ($target >= 4 ? 'Desempeño Alto' : ($target >= 3 ? 'Desempeño Básico' : 'Desempeño Bajo'));
            $json = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            $insert->bind_param('sssssdsss', $name, $grade, $subject, $date, $type, $target, $level, $observations, $json);
            $insert->execute(); $created++;
        }
        $verified++;
        echo ($index + 1) . '. Grado ' . $grade . ': ' . implode(' / ', array_column($details, 'score')) . ' -> ' . number_format($average, 1) . "\n";
    }
    $after = $db->query('SELECT id, student_name, student_grade, final_score, criterion_scores FROM evaluations WHERE id BETWEEN 1 AND 15 ORDER BY id')->fetch_all(MYSQLI_ASSOC);
    if ($original !== $after) throw new RuntimeException('Se modificaron registros anteriores');
    $db->commit();
    echo "Guardados: $created; estudiantes verificados: $verified; valoraciones por ítem: " . ($verified * count($criteria)) . ". Registros anteriores intactos.\n";
} catch (Throwable $error) { $db->rollback(); fwrite(STDERR, $error->getMessage() . "\n"); exit(1); }
