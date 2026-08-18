<?php
require_once dirname(__DIR__, 2) . '/comun/autoload.php';
require_once SGA_COMUN_SERVER . '/conexion.php';
require_once __DIR__ . '/lib/NotasAuditoriaService.php';

function notas_e($valor) { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }
function notas_bind(mysqli_stmt $stmt, $types, array $params) {
    if ($types === '') return;
    $refs = [];
    foreach ($params as $key => $value) $refs[$key] = &$params[$key];
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}
function notas_query(mysqli $mysqli, $sql, $types = '', array $params = []) {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) throw new RuntimeException($mysqli->error);
    notas_bind($stmt, $types, $params);
    if (!$stmt->execute()) throw new RuntimeException($stmt->error);
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    $stmt->close();
    return $rows;
}

$servicio = new NotasAuditoriaService($mysqli);
$error = '';
$periodo = null;
$resumen = $eventos = $materias = $detalleEstudiante = [];
try {
    $servicio->asegurarTabla();
    $periodo = $servicio->obtenerPeriodoActual();
    if ($periodo) {
        $grupo = $_GET['grupo'] ?? '6-8';
        if (!in_array($grupo, ['6-8', '9-11', 'todos'], true)) $grupo = '6-8';
        $materiaFiltro = trim($_GET['materia'] ?? '');
        $estudianteId = trim($_GET['estudiante'] ?? '');
        $grados = $grupo === '6-8' ? ['6','7','8'] : ($grupo === '9-11' ? ['9','10','11'] : ['6','7','8','9','10','11']);
        $marcas = implode(',', array_fill(0, count($grados), '?'));
        $tiposGrado = str_repeat('s', count($grados));

        $resumen = notas_query($mysqli,
            "SELECT grado, materia, MAX(id_asignacion) AS id_asignacion, COUNT(DISTINCT id_estudiante) AS estudiantes,
                    COUNT(*) AS registros, ROUND(AVG(nota), 2) AS promedio, MIN(nota) AS minima, MAX(nota) AS maxima,
                    MAX(fecha_nota) AS ultima_fecha
             FROM notas_auditoria WHERE id_periodo = ? AND grado IN ($marcas)
             GROUP BY grado, materia ORDER BY CAST(grado AS UNSIGNED), materia",
            'i' . $tiposGrado, array_merge([(int)$periodo['id_periodo']], $grados));

        $materias = array_values(array_unique(array_column($resumen, 'materia')));
        $where = " WHERE n.id_periodo = ? AND n.grado IN ($marcas)";
        $types = 'i' . $tiposGrado;
        $params = array_merge([(int)$periodo['id_periodo']], $grados);
        if ($materiaFiltro !== '') { $where .= ' AND n.materia = ?'; $types .= 's'; $params[] = $materiaFiltro; }

        $eventos = notas_query($mysqli,
            "SELECT n.*, CONCAT_WS(' ', u.nombre, u.apellido) AS estudiante
             FROM notas_auditoria n LEFT JOIN usuario u ON u.id_usuario = n.id_estudiante
             $where ORDER BY n.fecha_registro DESC, n.id_nota_auditoria DESC LIMIT 80",
            $types, $params);

        if ($estudianteId !== '') {
            $detalleEstudiante = notas_query($mysqli,
                "SELECT n.*, CONCAT_WS(' ', u.nombre, u.apellido) AS estudiante, u.foto
                 FROM notas_auditoria n LEFT JOIN usuario u ON u.id_usuario = n.id_estudiante
                 WHERE n.id_periodo = ? AND n.id_estudiante = ? ORDER BY n.fecha_nota, n.fecha_registro",
                'is', [(int)$periodo['id_periodo'], $estudianteId]);
        }
    }
} catch (Throwable $e) { $error = $e->getMessage(); }
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Auditoría de notas | Guagua</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{--ink:#172554;--muted:#64748b;--surface:#fff;--bg:#f5f7fb;--accent:#4f46e5}body{background:var(--bg);color:var(--ink);font-family:Inter,system-ui,sans-serif}.top{background:linear-gradient(115deg,#172554,#312e81 55%,#0f766e);color:white}.panel,.note-card{background:var(--surface);border:1px solid #e7edf5;border-radius:18px;box-shadow:0 12px 28px rgba(15,23,42,.06)}.metric{font-size:1.65rem;font-weight:800}.note-card{transition:.2s;cursor:pointer;border-left:5px solid #4f46e5}.note-card:hover{transform:translateY(-3px);box-shadow:0 18px 35px rgba(15,23,42,.12)}.range-btn.active{background:#312e81!important;color:#fff!important;border-color:#312e81!important}.timeline{border-left:2px solid #c7d2fe;margin-left:.65rem;padding-left:1.5rem}.timeline-item{position:relative}.timeline-item:before{content:'';position:absolute;width:11px;height:11px;border-radius:50%;background:#4f46e5;left:-1.87rem;top:.45rem}.grade{font-weight:800;font-size:1.1rem}.grade.bad{color:#dc2626}.grade.good{color:#059669}.smallcaps{font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);font-weight:700}.student-link{text-decoration:none;color:inherit}.student-link:hover{color:#4338ca}@media(max-width:768px){.top .display-6{font-size:1.8rem}}
</style></head><body>
<header class="top py-5"><div class="container"><div class="d-flex flex-wrap gap-3 justify-content-between align-items-end"><div><p class="mb-2 text-white-50 fw-semibold"><i class="bi bi-shield-check me-2"></i>Guagua · historial verificable</p><h1 class="display-6 fw-bold mb-2">Auditoría de notas</h1><p class="mb-0 text-white-50">Cada importación conserva una instantánea de las valoraciones del período vigente.</p></div><a href="../../ia/reporte.php" class="btn btn-light fw-semibold"><i class="bi bi-arrow-repeat me-2"></i>Sincronizar desde reporte</a></div></div></header>
<main class="container py-4 pb-5">
<?php if ($error): ?><div class="alert alert-danger"><strong>No fue posible abrir la auditoría.</strong> <?php echo notas_e($error); ?></div>
<?php elseif (!$periodo): ?><div class="panel p-5 text-center"><i class="bi bi-calendar-x fs-1 text-warning"></i><h2 class="h4 mt-3">No hay un período vigente hoy</h2><p class="text-muted mb-0">Configura y activa un período con fechas que incluyan la fecha actual en <a href="../../Planeador/periodo.php">Gestión de períodos</a>. Por seguridad, no se muestran ni guardan notas fuera de ese rango.</p></div>
<?php else: ?>
<section class="panel p-3 p-md-4 mb-4"><div class="d-flex flex-wrap align-items-center gap-3 justify-content-between"><div><div class="smallcaps">Filtro temporal aplicado</div><strong>Período <?php echo notas_e($periodo['nombre_periodo']); ?></strong> <span class="text-muted">· <?php echo notas_e($periodo['fecha_inicio']); ?> al <?php echo notas_e($periodo['fecha_fin']); ?></span></div><div class="btn-group range-btn"><a href="?grupo=6-8" class="btn btn-outline-primary <?php echo $grupo === '6-8' ? 'active' : ''; ?>">6° a 8°</a><a href="?grupo=9-11" class="btn btn-outline-primary <?php echo $grupo === '9-11' ? 'active' : ''; ?>">9° a 11°</a><a href="?grupo=todos" class="btn btn-outline-primary <?php echo $grupo === 'todos' ? 'active' : ''; ?>">Todos</a></div></div></section>
<form class="panel p-3 mb-4 row g-2 align-items-end" method="get"><input type="hidden" name="grupo" value="<?php echo notas_e($grupo); ?>"><div class="col-md-5"><label class="smallcaps mb-1">Materia</label><select class="form-select" name="materia"><option value="">Todas las materias</option><?php foreach ($materias as $materia): ?><option value="<?php echo notas_e($materia); ?>" <?php echo $materiaFiltro === $materia ? 'selected' : ''; ?>><?php echo notas_e($materia); ?></option><?php endforeach; ?></select></div><div class="col-md-5"><label class="smallcaps mb-1">Buscar estudiante</label><input class="form-control" name="estudiante" value="<?php echo notas_e($estudianteId); ?>" placeholder="Documento del estudiante"></div><div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Filtrar</button></div></form>

<?php if ($detalleEstudiante): $primero = $detalleEstudiante[0]; $series = []; foreach ($detalleEstudiante as $fila) $series[$fila['materia']][] = ['x'=>$fila['fecha_nota'], 'y'=>(float)$fila['nota']]; ?>
<section class="panel p-4 mb-4"><div class="d-flex justify-content-between gap-3 flex-wrap"><div><div class="smallcaps">Evolución individual · período <?php echo notas_e($periodo['nombre_periodo']); ?></div><h2 class="h4 mb-1"><?php echo notas_e($primero['estudiante'] ?: $primero['id_estudiante']); ?></h2><span class="text-muted">Documento <?php echo notas_e($primero['id_estudiante']); ?> · <?php echo count($detalleEstudiante); ?> registros auditados</span></div><a href="?grupo=<?php echo notas_e($grupo); ?>" class="btn btn-outline-secondary align-self-start">Cerrar detalle</a></div><div style="height:300px" class="mt-3"><canvas id="studentChart"></canvas></div><div class="timeline mt-4"><?php foreach (array_reverse($detalleEstudiante) as $fila): ?><div class="timeline-item pb-3"><div class="d-flex justify-content-between"><strong><?php echo notas_e($fila['materia']); ?></strong><span class="grade <?php echo $fila['nota'] < 3 ? 'bad' : 'good'; ?>"><?php echo number_format($fila['nota'], 2); ?></span></div><small class="text-muted"><?php echo notas_e($fila['fecha_nota']); ?> · importado <?php echo notas_e($fila['fecha_registro']); ?></small></div><?php endforeach; ?></div></section>
<script>document.addEventListener('DOMContentLoaded',()=>{const sets=<?php echo json_encode($series, JSON_UNESCAPED_UNICODE); ?>;new Chart(document.getElementById('studentChart'),{type:'line',data:{datasets:Object.entries(sets).map(([label,data],i)=>({label,data,borderColor:['#4f46e5','#059669','#ea580c','#db2777','#0284c7','#7c3aed'][i%6],backgroundColor:'transparent',tension:.25,pointRadius:4}))},options:{responsive:true,maintainAspectRatio:false,scales:{x:{type:'category'},y:{min:0,max:5,ticks:{stepSize:1}}}}});});</script>
<?php elseif ($estudianteId !== ''): ?><div class="alert alert-warning">No hay notas de este estudiante dentro del período actual.</div><?php endif; ?>

<div class="d-flex align-items-end justify-content-between mb-3"><div><div class="smallcaps">Resumen actual</div><h2 class="h4 mb-0">Asignaciones por grado y materia</h2></div><span class="text-muted small"><?php echo count($resumen); ?> asignaciones visibles</span></div>
<div class="row g-3 mb-5"><?php foreach ($resumen as $fila): $clase = $fila['promedio'] < 3 ? 'bad' : 'good'; ?><div class="col-md-6 col-xl-4"><a class="student-link" href="?grupo=<?php echo notas_e($grupo); ?>&materia=<?php echo urlencode($fila['materia']); ?>"><article class="note-card p-4 h-100"><div class="d-flex justify-content-between gap-2"><span class="badge text-bg-light border">Grado <?php echo notas_e($fila['grado']); ?></span><span class="small text-muted">Actualizado <?php echo notas_e($fila['ultima_fecha']); ?></span></div><h3 class="h5 mt-3 mb-3"><?php echo notas_e($fila['materia']); ?></h3><div class="row g-2"><div class="col-5"><div class="smallcaps">Promedio</div><div class="metric <?php echo $clase; ?>"><?php echo number_format($fila['promedio'], 2); ?></div></div><div class="col-7"><div class="smallcaps">Estudiantes</div><strong><?php echo (int)$fila['estudiantes']; ?></strong><span class="text-muted"> · <?php echo (int)$fila['registros']; ?> eventos</span><div class="small text-muted mt-1">Rango <?php echo number_format($fila['minima'],1); ?> — <?php echo number_format($fila['maxima'],1); ?></div></div></div><?php if ($fila['id_asignacion']): ?><div class="mt-3 small text-primary"><i class="bi bi-link-45deg me-1"></i>Asignación vinculada #<?php echo (int)$fila['id_asignacion']; ?></div><?php endif; ?></article></a></div><?php endforeach; if (!$resumen): ?><div class="col-12"><div class="panel p-5 text-center text-muted"><i class="bi bi-inbox fs-2"></i><p class="mt-2 mb-0">Todavía no hay notas auditadas para este período. Abre el reporte de IA para sincronizarlas.</p></div></div><?php endif; ?></div>

<section class="panel overflow-hidden"><div class="p-4 border-bottom"><div class="smallcaps">Línea de tiempo</div><h2 class="h4 mb-0">Últimos cambios registrados</h2></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">Momento</th><th>Estudiante</th><th>Grado</th><th>Materia</th><th class="text-end pe-4">Nota</th></tr></thead><tbody><?php foreach ($eventos as $evento): ?><tr><td class="ps-4 small text-muted"><?php echo notas_e($evento['fecha_registro']); ?></td><td><a class="student-link fw-semibold" href="?grupo=<?php echo notas_e($grupo); ?>&estudiante=<?php echo urlencode($evento['id_estudiante']); ?>"><?php echo notas_e($evento['estudiante'] ?: $evento['id_estudiante']); ?></a><div class="small text-muted"><?php echo notas_e($evento['id_estudiante']); ?></div></td><td><?php echo notas_e($evento['grado']); ?></td><td><?php echo notas_e($evento['materia']); ?></td><td class="text-end pe-4 grade <?php echo $evento['nota'] < 3 ? 'bad' : 'good'; ?>"><?php echo number_format($evento['nota'], 2); ?></td></tr><?php endforeach; if (!$eventos): ?><tr><td colspan="5" class="text-center text-muted py-4">Sin eventos para los filtros seleccionados.</td></tr><?php endif; ?></tbody></table></div></section>
<?php endif; ?></main></body></html>
