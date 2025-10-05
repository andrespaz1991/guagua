<?php
ob_start();
// --- INCLUDES Y CONFIGURACIÓN INICIAL ---
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua' . '/' . "/comun/autoload.php");
require(SGA_COMUN_SERVER . '/conexion.php');

// --- DECLARACIÓN DE CLASES Y FUNCIONES ---
// (En un proyecto más grande, estas clases estarían en sus propios archivos)
if (!class_exists('Curso')) { class Curso { public function deadeline_curso($id) { /* Lógica simulada */ return 75; } } }
if (!class_exists('Academico')) { class Academico { public function consultar_horario_simple($id) { return ['fecha_inicio' => '2025-01-01', 'fecha_fin' => '2025-11-30']; } public function misestudiantes(){} public function home_recursos(){} public function notasdeclase($id){ return []; }} }
if (!class_exists('Planeacion')) { class Planeacion { public $id_plan, $orden, $contenido_plan, $objetivos_plan, $estrategias, $recursoa, $tiempo_plan; public function mostrar_todas_planeaciones($asig, $grado){ return []; } public function intensidad_horaria($id){ return 0; } } }
if (!class_exists('Fecha')) { class Fecha { public static function formato_fecha($date) { if(empty($date) || $date == '0000-00-00') return 'N/A'; return date('d/m/Y', strtotime($date)); } } }
if (!class_exists('Comun')) { class Comun { public static function puntos_suspensivos($str, $len){ return strlen($str) > $len ? substr($str, 0, $len-3) . '...' : $str; } public static function remover_ultimo_caracter($str) { return $str; } } }

// --- LÓGICA PRINCIPAL Y OBTENCIÓN DE DATOS ---
$id_asignacion = isset($_GET['asignacion']) ? (int)$_GET['asignacion'] : 0;
$curso_info = null;
$estadisticas = [
    'total_estudiantes' => 0,
    'total_actividades' => 0,
    'total_planes' => 0,
    'total_notas' => 0,
    'estudiantes_por_estado' => [],
    'progreso_curso' => 0,
    'tasa_asistencia' => 0,
    'promedio_general' => 0
];
$horario = ['fecha_inicio' => null, 'fecha_fin' => null];
$planes_clase = [];


if ($id_asignacion > 0) {
    // 1. Obtener información principal del curso (Refactorizado a una sola consulta)
    $sql_curso = "SELECT 
                    a.descripcion, a.portada_asignacion,
                    mo.nombre_materia, a.id_asignatura as id_materia,
                    u.nombre as nombre_docente, u.apellido as apellido_docente, u.foto as foto_docente, u.id_usuario as id_docente,
                    cc.nombre_categoria_curso as grado, cc.id_categoria_curso
                  FROM asignacion a
                  JOIN materia_oficial mo ON a.id_asignatura = mo.id_materia
                  JOIN usuario u ON a.id_docente = u.id_usuario
                  JOIN categoria_curso cc ON a.id_categoria_curso = cc.id_categoria_curso
                  WHERE a.id_asignacion = ?";
    $stmt_curso = $mysqli->prepare($sql_curso);
    $stmt_curso->bind_param("i", $id_asignacion);
    $stmt_curso->execute();
    $result_curso = $stmt_curso->get_result();
    if ($result_curso->num_rows > 0) {
        $curso_info = $result_curso->fetch_assoc();
    }
    $stmt_curso->close();

    // 2. Obtener Estadísticas y Planes si el curso existe
    if ($curso_info) {
        // Total estudiantes
        $stmt_total = $mysqli->prepare("SELECT COUNT(DISTINCT id_estudiante) as total FROM inscripcion WHERE id_asignacion = ?");
        $stmt_total->bind_param("i", $id_asignacion);
        $stmt_total->execute();
        $estadisticas['total_estudiantes'] = $stmt_total->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt_total->close();

        // Estudiantes por estado
        $stmt_estados = $mysqli->prepare("SELECT estado_inscripcion, COUNT(id_inscripcion) as total FROM inscripcion WHERE id_asignacion = ? GROUP BY estado_inscripcion");
        $stmt_estados->bind_param("i", $id_asignacion);
        $stmt_estados->execute();
        $result_estados = $stmt_estados->get_result();
        while($row = $result_estados->fetch_assoc()){
            $estadisticas['estudiantes_por_estado'][$row['estado_inscripcion']] = $row['total'];
        }
        $stmt_estados->close();
        
        // Total Actividades
        $stmt_act = $mysqli->prepare("SELECT COUNT(id_actividad) as total FROM actividad WHERE id_asignacion = ?");
        $stmt_act->bind_param("i", $id_asignacion);
        $stmt_act->execute();
        $estadisticas['total_actividades'] = $stmt_act->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt_act->close();
        
        // Total Notas de Clase
        $stmt_notas = $mysqli->prepare("SELECT COUNT(id_nota) as total FROM edunotas WHERE id_asignacion = ?");
        $stmt_notas->bind_param("i", $id_asignacion);
        $stmt_notas->execute();
        $estadisticas['total_notas'] = $stmt_notas->get_result()->fetch_assoc()['total'] ?? 0;
        $stmt_notas->close();
        
        // Total Planes de Clase
        $stmt_planes_count = $mysqli->prepare("SELECT COUNT(id_plan) as total FROM planeador_vallesol WHERE materia = ? AND grado = ?");
        if ($stmt_planes_count) {
            $stmt_planes_count->bind_param("is", $curso_info['id_materia'], $curso_info['grado']);
            $stmt_planes_count->execute();
            $estadisticas['total_planes'] = $stmt_planes_count->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt_planes_count->close();
        }

        // Progreso del curso basado en fechas
        $stmt_horario = $mysqli->prepare("SELECT fecha_inicio, fecha_fin FROM horario WHERE id_asignacion = ? ORDER BY fecha_inicio ASC LIMIT 1");
        $stmt_horario->bind_param("i", $id_asignacion);
        $stmt_horario->execute();
        $result_horario = $stmt_horario->get_result();
        if($result_horario->num_rows > 0) {
            $horario = $result_horario->fetch_assoc();
        }
        $stmt_horario->close();

        if ($horario['fecha_inicio'] && $horario['fecha_fin']) {
            try {
                $fecha_inicio = new DateTime($horario['fecha_inicio']);
                $fecha_fin = new DateTime($horario['fecha_fin']);
                $hoy = new DateTime();
                if ($hoy >= $fecha_inicio && $hoy <= $fecha_fin) {
                    $total_dias = $fecha_inicio->diff($fecha_fin)->days;
                    if ($total_dias == 0) $total_dias = 1;
                    $dias_transcurridos = $fecha_inicio->diff($hoy)->days;
                    $estadisticas['progreso_curso'] = round(($dias_transcurridos / $total_dias) * 100);
                } elseif ($hoy > $fecha_fin) {
                    $estadisticas['progreso_curso'] = 100;
                }
            } catch (Exception $e) {
                $estadisticas['progreso_curso'] = 0;
            }
        }
        
        // Promedio General del curso
        $sql_promedio = "SELECT AVG(CAST(REPLACE(s.valoracion, ',', '.') AS DECIMAL(5,2))) as promedio 
                         FROM seguimiento_es s
                         JOIN actividad a ON s.id_actividad = a.id_actividad
                         WHERE a.id_asignacion = ? AND s.valoracion REGEXP '^[0-9,.]+$'";
        $stmt_promedio = $mysqli->prepare($sql_promedio);
        if ($stmt_promedio) {
            $stmt_promedio->bind_param("i", $id_asignacion);
            $stmt_promedio->execute();
            $result_promedio = $stmt_promedio->get_result()->fetch_assoc();
            if($result_promedio && $result_promedio['promedio'] !== null){
                $estadisticas['promedio_general'] = round($result_promedio['promedio'], 2);
            }
            $stmt_promedio->close();
        }
        
        // Obtener Planes de Clase
        $sql_planes = "SELECT id_plan, fecha_inicio, fecha_fin, objetivo, eje_tematico, estrategias, periodo, grado 
                       FROM planeador_vallesol 
                       WHERE materia = ? AND grado = ? 
                       ORDER BY fecha_inicio DESC";
        $stmt_planes = $mysqli->prepare($sql_planes);
        if ($stmt_planes) {
            $stmt_planes->bind_param("is", $curso_info['id_materia'], $curso_info['grado']);
            $stmt_planes->execute();
            $result_planes = $stmt_planes->get_result();
            while($row = $result_planes->fetch_assoc()) {
                $planes_clase[] = $row;
            }
            $stmt_planes->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso: <?php echo htmlspecialchars($curso_info['nombre_materia'] ?? 'Curso'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
        }
        .jumbotron-curso {
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 2rem;
            position: relative;
            border-radius: 0.5rem;
            margin-bottom: 2rem;
        }
        .jumbotron-curso::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 0.5rem;
        }
        .jumbotron-content {
            position: relative;
            z-index: 1;
        }
        .header-actions {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 2;
        }
        .stat-card {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }
        .stat-card .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: #0d6efd;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
        }
        .stat-card .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .progress-bar {
            font-size: 1rem;
        }
        .accordion-button:focus {
            box-shadow: none;
        }
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <?php if ($curso_info): ?>
        <!-- ENCABEZADO DEL CURSO -->
        <div class="jumbotron jumbotron-curso" style="background-image: url('<?php echo SGA_CURSOS_URL . '/' . htmlspecialchars($curso_info['portada_asignacion'] ?? ''); ?>');">
            <div class="header-actions">
                <button class="btn btn-warning">Opciones</button>
            </div>
            <div class="jumbotron-content">
                <h1 class="display-4"><?php echo htmlspecialchars($curso_info['nombre_materia']); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($curso_info['grado']); ?></p>
                <hr class="my-4">
                <p><?php echo htmlspecialchars($curso_info['descripcion']); ?></p>
                <div class="d-flex align-items-center">
                    <img src="<?php echo SGA_COMUN_SGA_DATA . '/' . htmlspecialchars($curso_info['foto_docente']); ?>" class="rounded-circle" width="50" height="50" alt="Docente">
                    <span class="ms-3"><?php echo htmlspecialchars($curso_info['nombre_docente'] . ' ' . $curso_info['apellido_docente']); ?></span>
                </div>
            </div>
        </div>

        <!-- DASHBOARD DE ESTADÍSTICAS -->
        <div class="accordion" id="accordionEstadisticas">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEstadisticas" aria-expanded="true" aria-controls="collapseEstadisticas">
                        <strong>Dashboard de Estadísticas del Curso</strong>
                    </button>
                </h2>
                <div id="collapseEstadisticas" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionEstadisticas">
                    <div class="accordion-body">
                        <div class="row">
                            <!-- Métricas Principales -->
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card text-center">
                                    <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                                    <div class="stat-number"><?php echo $estadisticas['total_estudiantes']; ?></div>
                                    <div class="stat-label">Estudiantes Inscritos</div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card text-center">
                                    <div class="stat-icon"><i class="bi bi-journal-check"></i></div>
                                    <div class="stat-number"><?php echo $estadisticas['total_actividades']; ?></div>
                                    <div class="stat-label">Actividades Publicadas</div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card text-center">
                                    <div class="stat-icon"><i class="bi bi-patch-check-fill text-success"></i></div>
                                    <div class="stat-number"><?php echo $estadisticas['tasa_asistencia']; ?>%</div>
                                    <div class="stat-label">Tasa de Asistencia</div>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="stat-card text-center">
                                     <div class="stat-icon"><i class="bi bi-trophy-fill text-warning"></i></div>
                                    <div class="stat-number"><?php echo number_format($estadisticas['promedio_general'], 2); ?></div>
                                    <div class="stat-label">Promedio General</div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <!-- Progreso del Curso y Gráfico -->
                            <div class="col-lg-8">
                                <div class="stat-card">
                                     <div class="stat-label mb-2">Avance del Curso (Temporal)</div>
                                     <div class="progress" style="height: 30px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $estadisticas['progreso_curso']; ?>%;" aria-valuenow="<?php echo $estadisticas['progreso_curso']; ?>" aria-valuemin="0" aria-valuemax="100">
                                            <strong><?php echo $estadisticas['progreso_curso']; ?>%</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 text-muted small">
                                        <span><?php echo Fecha::formato_fecha($horario['fecha_inicio'] ?? 'N/A'); ?></span>
                                        <span><?php echo Fecha::formato_fecha($horario['fecha_fin'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="stat-card">
                                     <div class="stat-label mb-2">Distribución de Estudiantes</div>
                                     <canvas id="estadosChart" style="max-height: 180px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO DEL CURSO: ACTIVIDADES, PLANES, NOTAS (en acordeones) -->
        <div class="accordion mt-4" id="accordionCurso">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActividades" aria-expanded="false" aria-controls="collapseActividades">
                        Actividades del Curso
                    </button>
                </h2>
                <div id="collapseActividades" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                    <div class="accordion-body">
                        <!-- Aquí iría la lógica PHP para mostrar las actividades -->
                        <p>Contenido de actividades...</p>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlanes" aria-expanded="false" aria-controls="collapsePlanes">
                        Planes de Clase (<?php echo $estadisticas['total_planes']; ?>)
                    </button>
                </h2>
                <div id="collapsePlanes" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                    <div class="accordion-body">
                         <?php if (!empty($planes_clase)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Periodo</th>

                                        <th scope="col">Semana</th>
                                            <th scope="col">Objetivo de Aprendizaje</th>
                                            <th scope="col">Eje Temático</th>
                                            <th scope="col">Estrategia</th>
                                            <th scope="col">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($planes_clase as $plan): ?>

                                            <tr>
                                                <td class="text-nowrap">
<?php echo ($plan['periodo']); ?>
                                                   </td>
                                            <td class="text-nowrap">
                                                    <i class="bi bi-calendar-week me-2"></i>
                                                    <?php echo Fecha::formato_fecha($plan['fecha_inicio']); ?><br>
                                                    <small class="text-muted">a <?php echo Fecha::formato_fecha($plan['fecha_fin']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars(Comun::puntos_suspensivos($plan['objetivo']),100); ?></td>
                                                <td><span class="badge bg-secondary-subtle text-secondary-emphasis rounded-pill"><?php echo htmlspecialchars($plan['eje_tematico']); ?></span></td>
                                                <td><?php echo htmlspecialchars(Comun::puntos_suspensivos(strip_tags($plan['estrategias']), 100)); ?></td>
                                                <td>
                                                    <a target='_blank'  href='../Planeador/planeador.php?pdf=1&idplan=<?php echo $plan['id_plan']?>'  class="btn btn-sm btn-outline-primary" title="Ver Detalles"><i class="bi bi-eye-fill"></i></a>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Editar"><i class="bi bi-pencil-fill"></i></button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-light text-center" role="alert">
                                <i class="bi bi-info-circle me-2"></i> No se han encontrado planes de clase registrados para este curso.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotas" aria-expanded="false" aria-controls="collapseNotas">
                        Notas de Clase
                    </button>
                </h2>
                <div id="collapseNotas" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                    <div class="accordion-body">
                        <!-- Aquí iría la lógica PHP para mostrar las notas -->
                        <p>Contenido de notas de clase...</p>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="alert alert-danger">No se encontró la información del curso. Verifique el ID de la asignación.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script para el gráfico de estadísticas
    const ctx = document.getElementById('estadosChart');
    const estadosData = <?php echo json_encode($estadisticas['estudiantes_por_estado']); ?>;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(estadosData),
            datasets: [{
                label: 'N° de Estudiantes',
                data: Object.values(estadosData),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: false,
                }
            }
        }
    });
</script>

</body>
</html>
<?php
ob_end_flush();
?>

