<?php
// Migración explícita por consola; nunca disponible como acción web.
declare(strict_types=1);
require_once __DIR__ . '/../../comun/database.php';

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = guagua_db_mysqli();

$criteria = ['oral' => [
    ['id' => 'o_fluidez', 'title' => 'Fluidez y Pronunciación', 'weight' => 50],
    ['id' => 'o_dominio', 'title' => 'Dominio del Tema', 'weight' => 50]
]];
foreach ($db->query('SELECT rubric_key, criteria FROM custom_rubrics')->fetch_all(MYSQLI_ASSOC) as $rubric) {
    $criteria[$rubric['rubric_key']] = json_decode($rubric['criteria'], true, 512, JSON_THROW_ON_ERROR);
}
$db->begin_transaction();
try {
    $rows = $db->query('SELECT id, rubric_type, final_score, criterion_scores FROM evaluations WHERE id BETWEEN 1 AND 15 FOR UPDATE')->fetch_all(MYSQLI_ASSOC);
    // Copia recuperable de los campos que se actualizarán, sin nombres de estudiantes.
    $backupDir = dirname(__DIR__, 2) . '/tmp/rubrica-backups';
    if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
    file_put_contents($backupDir . '/.htaccess', "Require all denied\n");
    $backup = $backupDir . '/antes-estimacion-' . date('Ymd-His') . '.json';
    if (file_put_contents($backup, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)) === false) throw new RuntimeException('No se pudo guardar la copia previa.');
    $updated = 0;
    foreach ($rows as $row) {
        if ($row['criterion_scores'] !== null) continue;
        $items = $criteria[$row['rubric_type']] ?? [];
        $n = count($items);
        if ($n < 2) throw new RuntimeException('Se necesitan al menos dos criterios.');
        $weight = (float) $items[0]['weight'];
        foreach ($items as $item) if (abs((float) $item['weight'] - $weight) > 0.00001) throw new RuntimeException('Esta migración requiere pesos iguales.');
        if (abs($weight * $n - 100) > 0.00001) throw new RuntimeException('Los pesos no suman 100.');
        $target = (int) round((float) $row['final_score'] * 10);
        // Desviaciones balanceadas en décimas: suma cero, notas dentro de 1–5.
        $offsets = $n === 2 ? [-1, 1] : range(-intdiv($n, 2), intdiv($n, 2));
        if (count($offsets) !== $n) throw new RuntimeException('Cantidad de criterios no soportada.');
        $capacity = min($target - 10, 50 - $target);
        $maxOffset = max(array_map('abs', $offsets));
        $factor = min(1 + ((int) $row['id'] % 3), intdiv($capacity, $maxOffset));
        if ($factor < 1) throw new RuntimeException('No se puede variar esta nota respetando la escala.');
        mt_srand(20261008 + (int) $row['id']); shuffle($offsets);
        $details = []; $weighted = 0;
        foreach ($items as $i => $item) {
            $score = ($target + $offsets[$i] * $factor) / 10;
            $weighted += $score * (float) $item['weight'] / 100;
            $details[] = ['id' => $item['id'], 'title' => $item['title'], 'weight' => (float) $item['weight'], 'score' => $score,
                'estimated' => true, 'estimation_method' => 'Desviaciones balanceadas; conserva promedio ponderado original', 'estimated_at' => date(DATE_ATOM)];
        }
        if (abs($weighted - (float) $row['final_score']) > 0.000001) throw new RuntimeException('El promedio no coincide.');
        $json = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $stmt = $db->prepare('UPDATE evaluations SET criterion_scores = ? WHERE id = ? AND criterion_scores IS NULL');
        $stmt->bind_param('si', $json, $row['id']); $stmt->execute(); $updated += $stmt->affected_rows;
        echo 'Registro #' . $row['id'] . ': ' . implode(', ', array_column($details, 'score')) . ' => ' . round($weighted, 1) . "\n";
    }
    $db->commit(); echo "Actualizados: $updated. Copia previa: $backup\n";
} catch (Throwable $error) { $db->rollback(); fwrite(STDERR, $error->getMessage() . "\n"); exit(1); }
