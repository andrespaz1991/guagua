<?php
/**
 * Balance anual de valoraciones.
 * Lee el consolidado institucional de los períodos 1 y 2 y conserva la
 * última importación local para que el panel no dependa del archivo original.
 */
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/asistencia/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

const BA_DATA_DIR = __DIR__ . '/data';
const BA_SOURCE_FILE = BA_DATA_DIR . '/consolidado_actual.xlsx';
const BA_CACHE_FILE = BA_DATA_DIR . '/consolidado_actual.json';

function ba_e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function ba_normalize(string $value): string {
    $value = trim($value);
    $value = strtr($value, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n']);
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    return preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
}
function ba_number($value): ?float {
    if ($value === null || trim((string)$value) === '') return null;
    $value = str_replace(',', '.', trim((string)$value));
    if (!is_numeric($value)) return null;
    $number = (float)$value;
    return $number >= 1 && $number <= 5 ? $number : null;
}
function ba_header_map(array $row): array {
    $map = [];
    foreach ($row as $index => $cell) {
        $name = ba_normalize((string)$cell);
        if ($name === 'grado') $map['grado'] = $index;
        elseif (in_array($name, ['codigo', 'codigodelestudiante', 'documento'], true)) $map['codigo'] = $index;
        elseif (in_array($name, ['estudiante', 'nombredelestudiante', 'nombre'], true)) $map['estudiante'] = $index;
        elseif (in_array($name, ['materia', 'asignatura'], true)) $map['materia'] = $index;
        elseif (in_array($name, ['periodo1', 'p1', 'nota1'], true)) $map['p1'] = $index;
        elseif (in_array($name, ['periodo2', 'p2', 'nota2'], true)) $map['p2'] = $index;
    }
    return $map;
}
function ba_status(array $row): string {
    if ($row['p1'] === null || $row['p2'] === null) return 'SIN NOTA';
    return $row['promedio'] < 3 ? 'PERDIDA' : 'APROBADA';
}
function ba_enrich(array $row): array {
    $row['promedio'] = $row['p1'] !== null && $row['p2'] !== null ? round(($row['p1'] + $row['p2']) / 2, 2) : null;
    $row['estado'] = ba_status($row);
    $row['pierde_dos_periodos'] = $row['p1'] !== null && $row['p2'] !== null && $row['p1'] < 3 && $row['p2'] < 3;
    if ($row['p1'] !== null && $row['p2'] !== null) {
        $required = round((12 - $row['p1'] - $row['p2']) / 2, 2);
        $row['promedio_requerido_p3_p4'] = $required;
        $row['proyeccion'] = $required <= 1
            ? 'Asegura el año incluso con 1,0 en P3 y P4'
            : ($required > 5 ? 'No alcanza 3,0 aun con 5,0 en P3 y P4' : 'Debe promediar ' . number_format($required, 2, ',', '.') . ' en P3 y P4');
    } else {
        $row['promedio_requerido_p3_p4'] = null;
        $row['proyeccion'] = 'Faltan notas para proyectar';
    }
    return $row;
}
function ba_parse_workbook(string $file, string $sourceName): array {
    $reader = IOFactory::createReaderForFile($file);
    $reader->setReadDataOnly(true);
    $book = $reader->load($file);
    $records = [];
    $foundHeaders = false;

    foreach ($book->getWorksheetIterator() as $sheet) {
        $rows = $sheet->toArray('', true, true, false);
        foreach ($rows as $rowIndex => $row) {
            $map = ba_header_map($row);
            if (count(array_intersect_key(array_flip(['grado','codigo','estudiante','materia','p1','p2']), $map)) !== 6) continue;
            $foundHeaders = true;
            for ($i = $rowIndex + 1, $total = count($rows); $i < $total; $i++) {
                $data = $rows[$i];
                $grade = trim((string)($data[$map['grado']] ?? ''));
                $code = trim((string)($data[$map['codigo']] ?? ''));
                $student = trim((string)($data[$map['estudiante']] ?? ''));
                $subject = trim((string)($data[$map['materia']] ?? ''));
                if ($grade === '' && $code === '' && $student === '' && $subject === '') continue;
                if ($student === '' || $subject === '') continue;
                $records[] = ba_enrich([
                    'grado' => $grade,
                    'codigo' => $code,
                    'estudiante' => $student,
                    'materia' => $subject,
                    'p1' => ba_number($data[$map['p1']] ?? null),
                    'p2' => ba_number($data[$map['p2']] ?? null),
                ]);
            }
            break;
        }
    }
    if (!$foundHeaders) throw new RuntimeException('No se encontraron las columnas requeridas: Grado, Código, Estudiante, Materia, Periodo 1 y Periodo 2.');
    if (!$records) throw new RuntimeException('El archivo no contiene registros de notas válidos.');
    return ['version' => 1, 'source_name' => $sourceName, 'imported_at' => date('c'), 'records' => $records];
}
function ba_save_data(array $data): void {
    if (!is_dir(BA_DATA_DIR) && !mkdir(BA_DATA_DIR, 0775, true) && !is_dir(BA_DATA_DIR)) throw new RuntimeException('No fue posible preparar el directorio de datos.');
    $temporary = BA_CACHE_FILE . '.tmp';
    if (file_put_contents($temporary, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) throw new RuntimeException('No fue posible guardar los datos importados.');
    if (file_exists(BA_CACHE_FILE)) unlink(BA_CACHE_FILE);
    if (!rename($temporary, BA_CACHE_FILE)) throw new RuntimeException('No fue posible actualizar los datos importados.');
}
function ba_load_data(): array {
    if (is_file(BA_CACHE_FILE)) {
        $cached = json_decode((string)file_get_contents(BA_CACHE_FILE), true);
        if (is_array($cached) && !empty($cached['records'])) return $cached;
    }
    if (!is_file(BA_SOURCE_FILE)) throw new RuntimeException('Aún no hay un consolidado importado. Sube el archivo de períodos 1 y 2.');
    $data = ba_parse_workbook(BA_SOURCE_FILE, basename(BA_SOURCE_FILE));
    ba_save_data($data);
    return $data;
}
function ba_grade_sort($grade): array {
    $grade = (string)$grade;
    return [is_numeric($grade) ? (int)$grade : 999, $grade];
}
function ba_average(array $records): ?float {
    $values = array_filter(array_column($records, 'promedio'), static fn($value) => $value !== null);
    return $values ? round(array_sum($values) / count($values), 2) : null;
}
function ba_badge_class(string $status): string {
    return $status === 'APROBADA' ? 'success' : ($status === 'PERDIDA' ? 'danger' : 'secondary');
}

$message = ''; $error = '';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['importar'])) {
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Selecciona un archivo .xlsx válido.');
        if ((int)$_FILES['archivo']['size'] > 10 * 1024 * 1024) throw new RuntimeException('El archivo supera el límite de 10 MB.');
        $originalName = (string)$_FILES['archivo']['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension !== 'xlsx') throw new RuntimeException('Solo se permiten archivos de Excel .xlsx.');
        $pending = BA_DATA_DIR . '/pendiente.' . $extension;
        if (!move_uploaded_file($_FILES['archivo']['tmp_name'], $pending)) throw new RuntimeException('No fue posible recibir el archivo.');
        try {
            $newData = ba_parse_workbook($pending, $originalName);
            if (!copy($pending, BA_SOURCE_FILE)) throw new RuntimeException('No fue posible conservar el archivo importado.');
            ba_save_data($newData);
            $message = 'Consolidado actualizado: ' . count($newData['records']) . ' registros procesados.';
        } finally {
            if (is_file($pending)) unlink($pending);
        }
    }
    $data = ba_load_data();
} catch (Throwable $exception) {
    $data = ['records' => []];
    $error = $exception->getMessage();
}

$records = $data['records'] ?? [];
$grades = array_values(array_unique(array_column($records, 'grado')));
usort($grades, static fn($a, $b) => ba_grade_sort($a) <=> ba_grade_sort($b));
$subjects = array_values(array_unique(array_column($records, 'materia'))); natcasesort($subjects); $subjects = array_values($subjects);
$filters = [
    'grado' => trim((string)($_GET['grado'] ?? '')),
    'materia' => trim((string)($_GET['materia'] ?? '')),
    'estado' => trim((string)($_GET['estado'] ?? '')),
    'buscar' => trim((string)($_GET['buscar'] ?? '')),
    'riesgo' => isset($_GET['riesgo']) && $_GET['riesgo'] === '1',
];
$visible = array_values(array_filter($records, static function(array $row) use ($filters): bool {
    if ($filters['grado'] !== '' && $row['grado'] !== $filters['grado']) return false;
    if ($filters['materia'] !== '' && $row['materia'] !== $filters['materia']) return false;
    if ($filters['estado'] !== '' && $row['estado'] !== $filters['estado']) return false;
    if ($filters['riesgo'] && !$row['pierde_dos_periodos']) return false;
    if ($filters['buscar'] !== '') {
        $needle = ba_normalize($filters['buscar']);
        $haystack = ba_normalize($row['codigo'] . ' ' . $row['estudiante'] . ' ' . $row['materia']);
        if (strpos($haystack, $needle) === false) return false;
    }
    return true;
}));
usort($visible, static function(array $a, array $b): int {
    return ba_grade_sort($a['grado']) <=> ba_grade_sort($b['grado']) ?: strcasecmp($a['estudiante'], $b['estudiante']) ?: strcasecmp($a['materia'], $b['materia']);
});
$alerts = array_values(array_filter($visible, static fn(array $row): bool => $row['pierde_dos_periodos']));
$lost = array_values(array_filter($visible, static fn(array $row): bool => $row['estado'] === 'PERDIDA'));
$students = array_values(array_unique(array_filter(array_column($visible, 'codigo'))));
$byGrade = [];
foreach ($visible as $row) {
    $grade = $row['grado'] === '' ? 'Sin grado' : $row['grado'];
    if (!isset($byGrade[$grade])) $byGrade[$grade] = ['total'=>0, 'perdidas'=>0, 'riesgo'=>0];
    $byGrade[$grade]['total']++;
    if ($row['estado'] === 'PERDIDA') $byGrade[$grade]['perdidas']++;
    if ($row['pierde_dos_periodos']) $byGrade[$grade]['riesgo']++;
}
uksort($byGrade, static fn($a, $b) => ba_grade_sort($a) <=> ba_grade_sort($b));
$chart = ['labels'=>array_keys($byGrade), 'perdidas'=>array_column($byGrade, 'perdidas'), 'riesgo'=>array_column($byGrade, 'riesgo')];
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Balance anual | Guagua</title>
<link rel="stylesheet" href="../../comun/vendor/bootstrap/bootstrap.min.css"><link rel="stylesheet" href="../../comun/vendor/bootstrap-icons/font/bootstrap-icons.min.css"><script src="../../comun/js/chart.js"></script>
<style>
:root{--navy:#13224d;--indigo:#5046e5;--aqua:#0c9388;--bg:#f4f7fc;--ink:#18233d;--muted:#64748b}*{box-sizing:border-box}body{background:var(--bg);color:var(--ink);font-family:Inter,system-ui,-apple-system,sans-serif}.hero{background:linear-gradient(120deg,#111d44,#263f85 57%,#0d827c);color:#fff}.hero p{color:#dbeafe}.panel{background:#fff;border:1px solid #e5eaf3;border-radius:18px;box-shadow:0 8px 25px rgba(15,23,42,.055)}.eyebrow{color:var(--muted);font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase}.metric{font-size:1.75rem;font-weight:800;line-height:1.1}.metric-card{border-top:4px solid var(--indigo)}.metric-card.alert{border-color:#dc3545}.metric-card.good{border-color:#0c9388}.metric-card.warning{border-color:#f59e0b}.table thead th{background:#eff3fb;color:#405072;font-size:.73rem;letter-spacing:.035em;text-transform:uppercase;white-space:nowrap}.table td{vertical-align:middle}.table-responsive{max-height:610px}.risk-row td{background:#fff5f5!important}.projection{font-size:.82rem;min-width:205px}.nav-pills .nav-link{color:#43526e;font-weight:700}.nav-pills .nav-link.active{background:var(--indigo)}.form-label{font-size:.78rem;font-weight:800;color:#52627d;margin-bottom:.28rem}.sticky-filter{position:sticky;top:0;z-index:3}.empty{padding:3rem 1rem;color:var(--muted);text-align:center}.source-note{font-size:.8rem;color:#dbeafe}@media(max-width:768px){.hero{padding-top:2rem!important;padding-bottom:2rem!important}.table-responsive{max-height:none}.metric{font-size:1.45rem}}
</style></head><body>
<header class="hero py-4 py-md-5"><div class="container-fluid px-3 px-md-4"><div class="d-flex flex-wrap align-items-end justify-content-between gap-3"><div><div class="text-white-50 small fw-semibold mb-2"><i class="bi bi-graph-up-arrow me-2"></i>Guagua · seguimiento académico</div><h1 class="display-6 fw-bold mb-2">Balance anual de notas</h1><p class="mb-1">Lectura de períodos 1 y 2, alertas tempranas y meta para aprobar el año con 3,0.</p><?php if (!empty($data['source_name'])): ?><div class="source-note"><i class="bi bi-file-earmark-excel me-1"></i><?php echo ba_e($data['source_name']); ?> · actualizado <?php echo ba_e(date('d/m/Y H:i', strtotime((string)($data['imported_at'] ?? 'now')))); ?></div><?php endif; ?></div><button class="btn btn-light fw-semibold" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-cloud-arrow-up me-2"></i>Importar consolidado</button></div></div></header>
<main class="container-fluid px-3 px-md-4 py-4 pb-5">
<?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?php echo ba_e($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><strong>No fue posible cargar el balance.</strong> <?php echo ba_e($error); ?></div><?php else: ?>
<form class="panel p-3 p-md-4 mb-4 sticky-filter" method="get"><div class="row g-3 align-items-end"><div class="col-sm-6 col-lg-2"><label class="form-label">Grado</label><select class="form-select" name="grado"><option value="">Todos</option><?php foreach ($grades as $grade): ?><option value="<?php echo ba_e($grade); ?>" <?php echo $filters['grado'] === $grade ? 'selected' : ''; ?>><?php echo ba_e($grade); ?></option><?php endforeach; ?></select></div><div class="col-sm-6 col-lg-3"><label class="form-label">Materia</label><select class="form-select" name="materia"><option value="">Todas</option><?php foreach ($subjects as $subject): ?><option value="<?php echo ba_e($subject); ?>" <?php echo $filters['materia'] === $subject ? 'selected' : ''; ?>><?php echo ba_e($subject); ?></option><?php endforeach; ?></select></div><div class="col-sm-6 col-lg-2"><label class="form-label">Estado P1 + P2</label><select class="form-select" name="estado"><option value="">Todos</option><?php foreach (['APROBADA','PERDIDA','SIN NOTA'] as $status): ?><option value="<?php echo $status; ?>" <?php echo $filters['estado'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option><?php endforeach; ?></select></div><div class="col-sm-6 col-lg-3"><label class="form-label">Estudiante, código o materia</label><input class="form-control" name="buscar" value="<?php echo ba_e($filters['buscar']); ?>" placeholder="Buscar…"></div><div class="col-sm-8 col-lg-1"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="riesgo" value="1" id="riesgo" <?php echo $filters['riesgo'] ? 'checked' : ''; ?>><label class="form-check-label small fw-semibold" for="riesgo">Solo doble pérdida</label></div></div><div class="col-sm-4 col-lg-1 d-grid"><button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filtrar</button></div></div></form>
<section class="row g-3 mb-4"><div class="col-6 col-lg-3"><article class="panel metric-card p-3 p-md-4 h-100"><div class="eyebrow">Registros visibles</div><div class="metric mt-2"><?php echo count($visible); ?></div><small class="text-muted">de <?php echo count($records); ?> asignaturas</small></article></div><div class="col-6 col-lg-3"><article class="panel metric-card good p-3 p-md-4 h-100"><div class="eyebrow">Estudiantes</div><div class="metric mt-2"><?php echo count($students); ?></div><small class="text-muted">con el filtro aplicado</small></article></div><div class="col-6 col-lg-3"><article class="panel metric-card warning p-3 p-md-4 h-100"><div class="eyebrow">Promedio P1 + P2</div><div class="metric mt-2"><?php echo ba_average($visible) === null ? '—' : number_format((float)ba_average($visible), 2, ',', '.'); ?></div><small class="text-muted">sobre 5,0</small></article></div><div class="col-6 col-lg-3"><article class="panel metric-card alert p-3 p-md-4 h-100"><div class="eyebrow">Doble pérdida</div><div class="metric mt-2 text-danger"><?php echo count($alerts); ?></div><small class="text-muted"><?php echo count($lost); ?> materias perdidas en total</small></article></div></section>
<section class="row g-4 mb-4"><div class="col-xl-7"><div class="panel p-3 p-md-4 h-100"><div class="d-flex justify-content-between align-items-start gap-3 mb-2"><div><div class="eyebrow">Distribución</div><h2 class="h5 mb-0">Alertas por grado</h2></div><span class="badge text-bg-light border">P1 + P2</span></div><div style="height:290px"><canvas id="gradeChart"></canvas></div></div></div><div class="col-xl-5"><aside class="panel p-3 p-md-4 h-100"><div class="eyebrow">Cómo se calcula</div><h2 class="h5">Proyección de aprobación</h2><p class="small text-muted mb-3">Se usan cuatro períodos de igual peso. Para cerrar con 3,0: <strong>P1 + P2 + P3 + P4 = 12,0</strong>.</p><div class="p-3 rounded-3" style="background:#eef6ff"><div class="fw-bold text-primary mb-1">Meta de P3 y P4</div><div class="small">El sistema muestra el promedio mínimo que debe obtener entre ambos períodos. Si supera 5,0, ya no es matemáticamente posible cerrar en 3,0 sin una regla institucional adicional.</div></div><div class="mt-3 small"><span class="badge text-bg-danger me-1">Doble pérdida</span> nota menor a 3,0 tanto en P1 como en P2 para la misma materia.</div></aside></div></section>
<section class="panel overflow-hidden"><div class="p-3 p-md-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3"><div><div class="eyebrow">Detalle académico</div><h2 class="h5 mb-0">Consolidado y alertas de seguimiento</h2></div><span class="text-muted small"><?php echo count($visible); ?> filas filtradas</span></div><div class="px-3 pt-3"><ul class="nav nav-pills gap-2" id="balanceTabs" role="tablist"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#allRows" type="button"><i class="bi bi-table me-1"></i>Tabla completa</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#riskRows" type="button"><i class="bi bi-exclamation-triangle me-1"></i>Dos períodos perdidos <span class="badge text-bg-light ms-1"><?php echo count($alerts); ?></span></button></li></ul></div><div class="tab-content"><div class="tab-pane fade show active" id="allRows"><?php if ($visible): ?><div class="table-responsive mt-3"><table class="table table-hover table-sm mb-0"><thead><tr><th>Grado</th><th>Código</th><th>Estudiante</th><th>Materia</th><th class="text-center">P1</th><th class="text-center">P2</th><th class="text-center">Promedio</th><th>Estado</th><th>Proyección P3 y P4</th></tr></thead><tbody><?php foreach ($visible as $row): ?><tr class="<?php echo $row['pierde_dos_periodos'] ? 'risk-row' : ''; ?>"><td class="fw-semibold"><?php echo ba_e($row['grado']); ?></td><td><?php echo ba_e($row['codigo']); ?></td><td class="fw-semibold"><?php echo ba_e($row['estudiante']); ?></td><td><?php echo ba_e($row['materia']); ?></td><td class="text-center"><?php echo $row['p1'] === null ? '—' : number_format($row['p1'], 1, ',', '.'); ?></td><td class="text-center"><?php echo $row['p2'] === null ? '—' : number_format($row['p2'], 1, ',', '.'); ?></td><td class="text-center fw-bold"><?php echo $row['promedio'] === null ? '—' : number_format($row['promedio'], 2, ',', '.'); ?></td><td><span class="badge text-bg-<?php echo ba_badge_class($row['estado']); ?>"><?php echo ba_e($row['estado']); ?></span><?php if ($row['pierde_dos_periodos']): ?><i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="Pierde ambos períodos"></i><?php endif; ?></td><td class="projection"><?php echo ba_e($row['proyeccion']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty"><i class="bi bi-search fs-2"></i><p class="mb-0 mt-2">No hay registros con los filtros seleccionados.</p></div><?php endif; ?></div><div class="tab-pane fade" id="riskRows"><?php if ($alerts): ?><div class="table-responsive mt-3"><table class="table table-hover mb-0"><thead><tr><th>Grado</th><th>Código</th><th>Estudiante</th><th>Materia</th><th class="text-center">P1</th><th class="text-center">P2</th><th class="text-center">Promedio actual</th><th class="text-center">Promedio mínimo P3 + P4</th><th>Lectura</th></tr></thead><tbody><?php foreach ($alerts as $row): ?><tr class="risk-row"><td class="fw-semibold"><?php echo ba_e($row['grado']); ?></td><td><?php echo ba_e($row['codigo']); ?></td><td class="fw-semibold"><?php echo ba_e($row['estudiante']); ?></td><td><?php echo ba_e($row['materia']); ?></td><td class="text-center text-danger fw-bold"><?php echo number_format($row['p1'], 1, ',', '.'); ?></td><td class="text-center text-danger fw-bold"><?php echo number_format($row['p2'], 1, ',', '.'); ?></td><td class="text-center fw-bold"><?php echo number_format($row['promedio'], 2, ',', '.'); ?></td><td class="text-center fw-bold <?php echo $row['promedio_requerido_p3_p4'] > 5 ? 'text-danger' : 'text-primary'; ?>"><?php echo number_format($row['promedio_requerido_p3_p4'], 2, ',', '.'); ?></td><td class="projection"><?php echo ba_e($row['proyeccion']); ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="empty"><i class="bi bi-shield-check fs-2 text-success"></i><p class="mb-0 mt-2">No hay estudiantes que pierdan la misma materia en los dos períodos bajo los filtros actuales.</p></div><?php endif; ?></div></div></section>
<?php endif; ?></main>
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form method="post" enctype="multipart/form-data" class="modal-content"><div class="modal-header"><div><div class="eyebrow">Actualizar datos</div><h2 class="modal-title h5">Importar consolidado</h2></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="small text-muted">La importación reemplaza el consolidado usado por este módulo. Debe contener una hoja con: <strong>Grado, Código, Estudiante, Materia, Periodo 1 y Periodo 2</strong>.</p><label class="form-label">Archivo Excel</label><input class="form-control" type="file" name="archivo" accept=".xlsx" required><div class="form-text">Tamaño máximo: 10 MB. No modifica las notas almacenadas en otros módulos.</div></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" name="importar" value="1"><i class="bi bi-arrow-repeat me-1"></i>Actualizar balance</button></div></form></div></div>
<script src="../../comun/vendor/bootstrap/bootstrap.bundle.min.js"></script><script>
const chartData=<?php echo json_encode($chart, JSON_UNESCAPED_UNICODE); ?>;
if(document.getElementById('gradeChart')&&window.Chart){new Chart(document.getElementById('gradeChart'),{type:'bar',data:{labels:chartData.labels,datasets:[{label:'Materias perdidas',data:chartData.perdidas,backgroundColor:'#ef4444',borderRadius:6},{label:'Doble pérdida',data:chartData.riesgo,backgroundColor:'#f59e0b',borderRadius:6}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}},scales:{x:{grid:{display:false}},y:{beginAtZero:true,ticks:{precision:0}}}}});}
</script></body></html>
