<?php
declare(strict_types=1);
require_once __DIR__ . '/../../comun/database.php';

$sieCatalog = require __DIR__ . '/rubricas_sie.php';
date_default_timezone_set('America/Bogota');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function h($value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function fecha($value): string {
    $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
    return $date ? $date->format('d/m/Y') : 'Sin fecha';
}
$type = $_GET['type'] ?? 'oral';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;
if (!is_string($type) || strlen($type) > 50 || (isset($_GET['id']) && !$id)) {
    http_response_code(400); exit('Solicitud de reporte inválida.');
}
try {
    $db = guagua_db_mysqli();

    $labels = $sieCatalog['labels'];
    $stmt = $db->prepare('SELECT rubric_name FROM custom_rubrics WHERE rubric_key = ?');
    $stmt->bind_param('s', $type); $stmt->execute();
    $custom = $stmt->get_result()->fetch_assoc();
    $label = $custom['rubric_name'] ?? $labels[$type] ?? $type;
    $sql = 'SELECT * FROM evaluations WHERE rubric_type = ?';
    if ($id !== null) $sql .= ' AND id = ?';
    $sql .= ' ORDER BY student_name ASC, eval_date ASC, id ASC';
    $stmt = $db->prepare($sql);
    if ($id !== null) $stmt->bind_param('si', $type, $id);
    else $stmt->bind_param('s', $type);
    $stmt->execute(); $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $error) {
    http_response_code(503); exit('No fue posible consultar las evaluaciones. Intenta nuevamente cuando la base de datos esté disponible.');
}
if (!$rows) { http_response_code(404); exit('No hay evaluaciones guardadas para esta selección. Cierra esta pestaña y selecciona otra categoría.'); }
$count = count($rows);
$average = array_sum(array_column($rows, 'final_score')) / $count;
$dates = array_values(array_filter(array_column($rows, 'eval_date'))); sort($dates);
$levels = [];
foreach ($rows as $row) { $level = $row['final_level'] ?: 'Sin desempeño'; $levels[$level] = ($levels[$level] ?? 0) + 1; }
$title = $id !== null ? 'Reporte individual de evaluación' : 'Reporte de evaluaciones por categoría';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($title . ' - ' . $label) ?></title>
<style>
@page { size: A4; margin: 18mm 15mm; }
* { box-sizing: border-box; }
body { margin: 0; background: #edf1f5; color: #243247; font: 14px/1.5 Arial, sans-serif; }
.tools { max-width: 950px; margin: 20px auto; padding: 0 20px; }
button { background: #334e91; color: white; border: 0; border-radius: 6px; padding: 12px 20px; cursor: pointer; font-weight: bold; }
.tools p { margin: 8px 0; }
main { max-width: 950px; margin: 20px auto; background: white; padding: 42px; }
header { border-bottom: 3px solid #334e91; padding-bottom: 18px; }
.institution { text-transform: uppercase; letter-spacing: 1px; color: #52637b; font-size: 12px; }
h1 { font-size: 25px; line-height: 1.2; margin: 10px 0; } h2 { font-size: 18px; margin: 25px 0 12px; }
.muted { color: #607086; font-size: 12px; } .category { font-size: 17px; margin: 8px 0; }
.summary { width: 100%; border-collapse: collapse; margin: 20px 0; } .summary td { background: #f2f5fa; padding: 12px; border: 1px solid #d7dfeb; width: 33%; }
.summary strong { display: block; font-size: 19px; }
table.list { border-collapse: collapse; width: 100%; font-size: 11px; table-layout: fixed; }
.list th, .list td { border: 1px solid #ccd5e2; padding: 8px; text-align: left; overflow-wrap: anywhere; }
.list th { background: #e9eef7; } thead { display: table-header-group; } tr { break-inside: avoid; }
.detail { border: 1px solid #ccd5e2; border-left: 4px solid #334e91; padding: 16px; margin: 15px 0; break-inside: avoid; }
.detail h3 { margin: 0 0 10px; font-size: 16px; } .fields { width: 100%; border-collapse: collapse; } .fields td { width: 50%; padding: 4px 0; vertical-align: top; overflow-wrap: anywhere; }
.result { margin: 12px 0; padding: 10px; background: #f2f5fa; font-weight: bold; }
.observations { white-space: pre-wrap; overflow-wrap: anywhere; margin: 5px 0 0; }
footer { border-top: 1px solid #ccd5e2; margin-top: 28px; padding-top: 12px; }
@media print { body { background: white; font-size: 11px; } .tools { display: none; } main { padding: 0; margin: 0; max-width: none; } h1 { font-size: 22px; } .detail { break-inside: auto; } .detail h3 { break-after: avoid; } }
@media screen and (max-width: 600px) { main { padding: 18px; } .summary td { padding: 6px; } .summary strong { font-size: 15px; } }
</style>
</head>
<body>
<div class="tools"><button onclick="window.print()">Imprimir / Guardar como PDF</button><p>Para exportar el reporte, selecciona «Guardar como PDF» en el destino de impresión.</p></div>
<main>
<header><div class="institution">IER La Josefina · Guagua</div><h1><?= h($title) ?></h1><p class="category"><strong>Categoría:</strong> <?= h($label) ?></p><div class="muted">Generado el <?= h(date('d/m/Y H:i')) ?> (hora de Colombia) · Ordenado por estudiante y fecha</div></header>
<h2>1. Resumen de resultados</h2>
<table class="summary"><tr><td>Evaluaciones<strong><?= $count ?></strong></td><td>Nota promedio<strong><?= h(number_format($average, 2, ',', '.')) ?> / 5,0</strong></td><td>Periodo evaluado<strong style="font-size:13px"><?= $dates ? h(fecha($dates[0]) . ' – ' . fecha($dates[count($dates)-1])) : 'Sin fechas' ?></strong></td></tr></table>
<p><strong>Distribución por desempeño:</strong> <?php $distribution = []; foreach ($levels as $level => $total) $distribution[] = $level . ': ' . $total; echo h(implode(' · ', $distribution)); ?></p>
<?php if ($id === null): ?>
<h2>2. Consolidado de la categoría</h2>
<table class="list"><thead><tr><th style="width:6%">N.º</th><th style="width:25%">Estudiante</th><th style="width:9%">Grado</th><th style="width:17%">Asignatura</th><th style="width:13%">Fecha</th><th style="width:9%">Nota</th><th style="width:21%">Desempeño</th></tr></thead><tbody>
<?php foreach ($rows as $i => $row): ?><tr><td><?= $i + 1 ?></td><td><?= h($row['student_name']) ?></td><td><?= h($row['student_grade'] ?: '—') ?></td><td><?= h($row['subject'] ?: '—') ?></td><td><?= h(fecha($row['eval_date'])) ?></td><td><?= h($row['final_score']) ?></td><td><?= h($row['final_level'] ?: '—') ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php endif; ?>
<h2><?= $id === null ? '3' : '2' ?>. Detalle de las evaluaciones</h2>
<?php foreach ($rows as $i => $row): ?>
<section class="detail"><h3><?= $i + 1 ?>. <?= h($row['student_name']) ?></h3>
<table class="fields"><tr><td><strong>Grado:</strong> <?= h($row['student_grade'] ?: 'Sin registrar') ?></td><td><strong>Asignatura:</strong> <?= h($row['subject'] ?: 'Sin registrar') ?></td></tr><tr><td><strong>Fecha:</strong> <?= h(fecha($row['eval_date'])) ?></td><td><strong>Registro:</strong> #<?= (int) $row['id'] ?></td></tr></table>
<div class="result">Calificación: <?= h($row['final_score']) ?> / 5,0 · <?= h($row['final_level'] ?: 'Sin desempeño registrado') ?></div>
<strong>Resultados por ítem</strong>
<?php $details = json_decode($row['criterion_scores'] ?? 'null', true); ?>
<?php $estimated = is_array($details) && array_filter($details, static fn($item) => !empty($item['estimated'])); ?>
<?php if (is_array($details) && !empty($details[0]['source'])): ?>
<p class="muted"><?= h($details[0]['source']) ?> · páginas <?= h($details[0]['source_pages'] ?? '') ?> · Promedio de igual peso por ítem.</p>
<?php endif; ?>
<?php if (is_array($details) && count($details) > 0): ?>
<table class="list" style="margin:10px 0"><thead><tr><th style="width:55%">Ítem evaluado</th><th style="width:20%">Peso</th><th style="width:25%"><?= $estimated ? 'Notas estimadas / 5,0' : 'Nota / 5,0' ?></th></tr></thead><tbody>
<?php foreach ($details as $detail): ?><tr><td><?= h($detail['title'] ?? '') ?><?php if (!empty($detail['competence'])): ?><br><span class="muted"><?= h($detail['competence']) ?></span><?php endif; ?></td><td><?= isset($detail['weight']) ? h(number_format((float) $detail['weight'], 2, ',', '.')) . ' %' : '—' ?></td><td><?= isset($detail['score']) ? h(number_format((float) $detail['score'], 1, ',', '.')) : 'Sin evaluar' ?><?php if (!empty($detail['level'])): ?><br><span class="muted"><?= h($detail['level']) ?></span><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php else: ?><p class="muted">Sin detalle por ítem: esta evaluación se guardó sin las notas individuales de sus criterios.</p><?php endif; ?>
<strong>Observaciones</strong><p class="observations"><?= h(trim($row['observations'] ?? '') ?: 'Sin observaciones registradas.') ?></p>
</section>
<?php endforeach; ?>
<footer class="muted">Fuente: evaluaciones guardadas en el módulo Rúbrica de Evaluación. <?= $id === null ? 'Incluye todos los registros de esta categoría, sin filtros de búsqueda ni límites de paginación.' : 'Incluye únicamente la evaluación seleccionada.' ?> El detalle por ítem se muestra cuando fue almacenado con la evaluación; las notas faltantes no se deducen de la nota final.</footer>
</main></body></html>
