<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/Database.php';
require_once __DIR__ . '/lib/EvaluationService.php';

function escaped(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

try {
    $evaluationId = (int) ($_GET['evaluation_id'] ?? 0);
    $service = new EvaluationService(EvidenceDatabase::connect());
    $dashboard = $service->getDashboard($evaluationId);
    $evaluation = $dashboard['evaluacion'];
    $summary = $dashboard['resumen'];
} catch (Throwable $exception) {
    http_response_code(500);
    echo '<h1>No fue posible generar el protocolo</h1><p>' . escaped($exception->getMessage()) . '</p>';
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Protocolo de evaluación <?= escaped($evaluation['ano']) ?></title>
    <style>
        @page{size:letter;margin:15mm}*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#111827;margin:0;font-size:10pt}.toolbar{position:fixed;right:1.5rem;top:1.5rem}.toolbar button{border:0;border-radius:8px;background:#0f766e;color:#fff;padding:.75rem 1rem;font-weight:bold;cursor:pointer}.header{text-align:center;border-bottom:2px solid #0f766e;padding-bottom:10px;margin-bottom:14px}.header h1{font-size:15pt;margin:0 0 4px}.header p{margin:0;color:#475569}.meta{display:grid;grid-template-columns:1fr 1fr;gap:6px 18px;border:1px solid #94a3b8;padding:10px;margin:12px 0}.meta strong{display:inline-block;min-width:122px}.section{margin-top:16px;break-inside:avoid}.section h2{font-size:11pt;color:#0f766e;border-bottom:1px solid #99f6e4;padding-bottom:4px;margin:0 0 8px}table{width:100%;border-collapse:collapse;margin:8px 0}th,td{border:1px solid #94a3b8;padding:6px;vertical-align:top}th{background:#e6fffb;text-align:left;font-size:8.5pt}.score{text-align:center;white-space:nowrap}.criterion{margin:4px 0 2px;font-weight:bold}.evidence{margin:1px 0 1px 14px;color:#334155}.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}.summary div{border:1px solid #94a3b8;padding:8px;text-align:center}.summary strong{display:block;font-size:15pt;color:#0f766e}.signatures{display:grid;grid-template-columns:1fr 1fr;gap:50px;margin-top:64px;text-align:center}.line{border-top:1px solid #111827;padding-top:6px}@media print{.toolbar{display:none}}
    </style>
</head>
<body>
    <div class="toolbar"><button onclick="window.print()">Imprimir o guardar como PDF</button></div>
    <header class="header">
        <h1>Protocolo para la Evaluación Anual de Desempeño Laboral</h1>
        <p>Decreto Ley 1278 de 2002 · Año lectivo <?= escaped($evaluation['ano']) ?></p>
    </header>
    <section class="meta">
        <div><strong>Docente evaluado:</strong> <?= escaped($evaluation['docente_nombre']) ?></div>
        <div><strong>Cédula:</strong> <?= escaped($evaluation['docente_cedula']) ?></div>
        <div><strong>Ciudad:</strong> <?= escaped($evaluation['ciudad_concertacion']) ?></div>
        <div><strong>Fecha de concertación:</strong> <?= escaped($evaluation['fecha_inicio']) ?></div>
        <div><strong>Estado:</strong> <?= escaped($evaluation['estado']) ?></div>
        <div><strong>Días V1 / V2:</strong> <?= escaped($evaluation['dias_valoracion_1']) ?> / <?= escaped($evaluation['dias_valoracion_2']) ?></div>
    </section>
    <?php foreach (['Académica', 'Administrativa', 'Comunitaria'] as $area): ?>
        <section class="section">
            <h2>Competencias funcionales · <?= escaped($area) ?> (<?= escaped($summary['areas'][$area]['ponderacion']) ?>%)</h2>
            <table>
                <thead><tr><th>Competencia y contribución individual</th><th>Criterios y evidencias</th><th>V1</th><th>V2</th><th>Final</th></tr></thead>
                <tbody>
                <?php foreach ($dashboard['competencias'] as $competency): ?>
                    <?php if ($competency['tipo'] !== 'Funcional' || $competency['area_gestion'] !== $area) { continue; } ?>
                    <tr>
                        <td><strong><?= escaped($competency['nombre_competencia']) ?></strong><br><?= escaped($competency['contribucion_individual']) ?></td>
                        <td><?php foreach ($competency['criterios'] as $criterion): ?><p class="criterion"><?= escaped($criterion['orden']) ?>. <?= escaped($criterion['descripcion']) ?></p><?php foreach ($criterion['evidencias'] as $evidence): ?><p class="evidence">□ <?= escaped($evidence['titulo']) ?> (<?= escaped($evidence['tipo']) ?>)</p><?php endforeach; ?><?php endforeach; ?></td>
                        <td class="score"><?= escaped($competency['puntaje_val_1'] ?? '-') ?></td><td class="score"><?= escaped($competency['puntaje_val_2'] ?? '-') ?></td><td class="score"><?= escaped($competency['puntaje_final'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    <?php endforeach; ?>
    <section class="section">
        <h2>Competencias comportamentales (30%)</h2>
        <table><thead><tr><th>Competencia y contribución individual</th><th>Criterios y evidencias</th><th>V1</th><th>V2</th><th>Final</th></tr></thead><tbody>
        <?php foreach ($dashboard['competencias'] as $competency): ?>
            <?php if ($competency['tipo'] !== 'Comportamental') { continue; } ?>
            <tr><td><strong><?= escaped($competency['nombre_competencia']) ?></strong><br><?= escaped($competency['contribucion_individual']) ?></td><td><?php foreach ($competency['criterios'] as $criterion): ?><p class="criterion"><?= escaped($criterion['orden']) ?>. <?= escaped($criterion['descripcion']) ?></p><?php foreach ($criterion['evidencias'] as $evidence): ?><p class="evidence">□ <?= escaped($evidence['titulo']) ?> (<?= escaped($evidence['tipo']) ?>)</p><?php endforeach; ?><?php endforeach; ?></td><td class="score"><?= escaped($competency['puntaje_val_1'] ?? '-') ?></td><td class="score"><?= escaped($competency['puntaje_val_2'] ?? '-') ?></td><td class="score"><?= escaped($competency['puntaje_final'] ?? '-') ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </section>
    <section class="section"><h2>Resultado consolidado</h2><div class="summary"><div>Subtotal funcional<strong><?= escaped($summary['subtotal_funcional']) ?></strong></div><div>Subtotal comportamental<strong><?= escaped($summary['subtotal_comportamental']) ?></strong></div><div>Nota final · <?= escaped($summary['categoria']) ?><strong><?= escaped($summary['nota_proyectada']) ?></strong></div></div></section>
    <section class="signatures"><div class="line">Firma y cédula del evaluado<br><strong><?= escaped($evaluation['docente_nombre']) ?> · <?= escaped($evaluation['docente_cedula']) ?></strong></div><div class="line">Firma y cédula del evaluador</div></section>
</body>
</html>
