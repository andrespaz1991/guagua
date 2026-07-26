<?php
ob_start();

// ==================================================================
// == BLOQUE PARA MANEJAR PETICIONES ASÍNCRONAS (PAGINACIÓN JSON)  ==
// ==================================================================
if (isset($_GET['accion']) && $_GET['accion'] === 'obtener_planes') {
    
    // --- INCLUDES Y CONFIGURACIÓN ---
    require_once(dirname(__DIR__) . '/comun/autoload.php');
    require(SGA_COMUN_SERVER . '/conexion.php');

    header('Content-Type: application/json');

    // --- VALIDACIÓN DE PARÁMETROS ---
    $id_asignacion = isset($_GET['asignacion']) ? (int)$_GET['asignacion'] : 0;
    $grado = isset($_GET['grado']) ? urldecode($_GET['grado']) : '';
    $pagina_actual = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

    if ($id_asignacion <= 0 || empty($grado)) {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Parámetros inválidos o faltantes.']);
        exit();
    }

    // --- CONFIGURACIÓN DE PAGINACIÓN ---
    $registros_por_pagina = 5; 

    // --- CÁLCULO TOTAL DE REGISTROS ---
    $sql_total = "SELECT COUNT(id_plan) as total FROM planeador_vallesol WHERE materia = ? AND grado = ?";
    $stmt_total = $mysqli->prepare($sql_total);
    $stmt_total->bind_param("is", $id_asignacion, $grado);
    $stmt_total->execute();
    $resultado_total = $stmt_total->get_result();
    $total_registros = (int)$resultado_total->fetch_assoc()['total'];
    $stmt_total->close();

    $total_paginas = ceil($total_registros / $registros_por_pagina);
    $offset = ($pagina_actual - 1) * $registros_por_pagina;

    // --- CONSULTA DE REGISTROS PARA LA PÁGINA ACTUAL ---
    $sql_planes = "SELECT id_plan, fecha_inicio, fecha_fin, objetivo, eje_tematico, estrategias, periodo 
                   FROM planeador_vallesol 
                   WHERE materia = ? AND grado = ? 
                   ORDER BY fecha_inicio DESC
                   LIMIT ? OFFSET ?";
                   
    $stmt_planes = $mysqli->prepare($sql_planes);
    $stmt_planes->bind_param("isii", $id_asignacion, $grado, $registros_por_pagina, $offset);
    $stmt_planes->execute();
    $result_planes = $stmt_planes->get_result();

    $registros = [];
    if (!class_exists('Fecha')) { class Fecha { public static function formato_fecha($date) { if(empty($date) || $date == '0000-00-00') return 'N/A'; return date('d/m/Y', strtotime($date)); } } }
    if (!class_exists('Comun')) { class Comun { public static function puntos_suspensivos($str, $len){ return strlen($str) > $len ? substr(strip_tags($str), 0, $len-3) . '...' : strip_tags($str); } } }

    while($row = $result_planes->fetch_assoc()) {
        $row['fecha_inicio_f'] = Fecha::formato_fecha($row['fecha_inicio']);
        $row['fecha_fin_f'] = Fecha::formato_fecha($row['fecha_fin']);
        $row['objetivo_corto'] = htmlspecialchars(Comun::puntos_suspensivos($row['objetivo'], 100));
        $row['estrategias_cortas'] = htmlspecialchars(Comun::puntos_suspensivos($row['estrategias'], 100));
        $row['eje_tematico'] = htmlspecialchars($row['eje_tematico']);
        $registros[] = $row;
    }
    $stmt_planes->close();
    $mysqli->close();

    // --- RESPUESTA JSON ---
    echo json_encode([
        'registros' => $registros,
        'total_paginas' => $total_paginas,
        'pagina_actual' => (int)$pagina_actual
    ]);
    
    exit();
}


// ==============================================
// == BLOQUE PARA LA CARGA NORMAL DE LA PÁGINA ==
// ==============================================

// --- INCLUDES Y CONFIGURACIÓN INICIAL ---
require_once(dirname(__DIR__) . '/comun/autoload.php');
require(SGA_COMUN_SERVER . '/conexion.php');

// --- DECLARACIÓN DE CLASES Y FUNCIONES ---
if (!class_exists('Curso')) { class Curso { public function deadeline_curso($id) { return 75; } } }
if (!class_exists('Academico')) { class Academico { public function consultar_horario_simple($id) { return ['fecha_inicio' => '2025-01-01', 'fecha_fin' => '2025-11-30']; } } }
if (!class_exists('Planeacion')) { class Planeacion { } }
if (!class_exists('Fecha')) { class Fecha { public static function formato_fecha($date) { if(empty($date) || $date == '0000-00-00') return 'N/A'; return date('d/m/Y', strtotime($date)); } } }
if (!class_exists('Comun')) { class Comun { public static function puntos_suspensivos($str, $len){ return strlen($str) > $len ? substr($str, 0, $len-3) . '...' : $str; } } }

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

if ($id_asignacion > 0) {
    // 1. Obtener información principal del curso
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

    // 2. Obtener Estadísticas si el curso existe
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
            $stmt_planes_count->bind_param("is", $_GET['asignacion'], $curso_info['grado']);
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
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso: <?php echo htmlspecialchars($curso_info['nombre_materia'] ?? 'Curso'); ?></title>
    <!-- CSS Bootstrap y Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-body: #f4f7fe;
            --surface: #ffffff;
            --primary: #4318FF;
            --secondary: #A3AED0;
            --text-main: #2B3674;
            --text-light: #707EAE;
            --radius-lg: 20px;
            --radius-md: 14px;
            --shadow-soft: 0px 18px 40px rgba(112, 144, 176, 0.12);
            --shadow-hover: 0px 20px 40px rgba(112, 144, 176, 0.22);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            padding-bottom: 3rem;
        }

        /* Hero / Header Section */
        .hero-course {
            position: relative;
            border-radius: var(--radius-lg);
            overflow: hidden;
            padding: 4rem 3rem;
            margin-bottom: 2.5rem;
            background-size: cover;
            background-position: center;
            box-shadow: var(--shadow-soft);
            color: white;
        }
        .hero-course::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(11, 20, 55, 0.85) 0%, rgba(43, 54, 116, 0.7) 100%);
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
        }
        .hero-title {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }
        .badge-grado {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            font-weight: 500;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }
        .docente-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(5px);
            padding: 0.4rem 1rem 0.4rem 0.4rem;
            border-radius: 50px;
            margin-top: 1.5rem;
        }
        .docente-badge img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
            object-fit: cover;
        }

        /* Tarjetas de Estadísticas */
        .stat-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
            height: 100%;
            border: none;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 1.2rem;
            flex-shrink: 0;
        }
        .icon-blue { background: #F4F7FE; color: var(--primary); }
        .icon-green { background: #E6F8ED; color: #05CD99; }
        .icon-orange { background: #FFF4E5; color: #FFB547; }
        .icon-purple { background: #F3E8FF; color: #8B5CF6; }
        
        .stat-details h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
        }
        .stat-details p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-light);
            font-weight: 500;
        }

        /* Contenedores Gráficos y Barras */
        .chart-card {
            background: var(--surface);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            box-shadow: var(--shadow-soft);
            height: 100%;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 1.2rem;
        }
        .progress {
            height: 10px;
            border-radius: 10px;
            background-color: #E2E8F0;
            margin-bottom: 0.5rem;
        }
        .progress-bar {
            background-color: var(--primary);
            border-radius: 10px;
        }

        /* Acordeones Modernos */
        .custom-accordion .accordion-item {
            background: var(--surface);
            border: none;
            border-radius: var(--radius-md) !important;
            box-shadow: var(--shadow-soft);
            margin-bottom: 1rem;
            overflow: hidden;
        }
        .custom-accordion .accordion-button {
            background: var(--surface);
            color: var(--text-main);
            font-weight: 600;
            font-size: 1.1rem;
            padding: 1.2rem 1.5rem;
            box-shadow: none !important;
        }
        .custom-accordion .accordion-button:not(.collapsed) {
            color: var(--primary);
            background: #F8FAFC;
        }
        .custom-accordion .accordion-button::after {
            background-size: 1rem;
        }
        
        /* Tablas */
        .table-custom {
            margin-bottom: 0;
        }
        .table-custom thead th {
            border-bottom: 1px solid #E2E8F0;
            color: var(--text-light);
            font-weight: 500;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            background: #F8FAFC;
        }
        .table-custom tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--text-main);
            border-bottom: 1px solid #F1F5F9;
            font-size: 0.95rem;
        }
        .table-custom tbody tr:hover {
            background-color: #F8FAFC;
        }
        .badge-tematico {
            background-color: #EFF4FF;
            color: var(--primary);
            font-weight: 500;
            padding: 0.4em 0.8em;
        }
        .btn-action {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s;
        }

        /* Skeleton Loader Personalizado */
        .skeleton {
            background: #eee;
            background: linear-gradient(110deg, #ececec 8%, #f5f5f5 18%, #ececec 33%);
            border-radius: 5px;
            background-size: 200% 100%;
            animation: 1.5s shine linear infinite;
        }
        @keyframes shine {
            to { background-position-x: -200%; }
        }
        .skeleton-row { height: 40px; margin-bottom: 10px; width: 100%; }
    </style>
</head>
<body>

<div class="container mt-4">
    <?php if ($curso_info): ?>
        
        <!-- ENCABEZADO DEL CURSO (HERO) -->
        <div class="hero-course" style="background-image: url('<?php echo SGA_CURSOS_URL . '/' . htmlspecialchars($curso_info['portada_asignacion'] ?? ''); ?>');">
            <div class="position-absolute top-0 end-0 p-4 z-3 dropdown">
                <button class="btn btn-light rounded-pill px-4 fw-semibold shadow-sm text-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear-fill me-2"></i> Opciones
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" style="border-radius: 12px; overflow: hidden;">
                    <li>
                        <a class="dropdown-item py-2 fw-medium text-secondary" href="../asistencia/horario.php?asignacion=<?php echo $id_asignacion; ?>">
                            <i class="bi bi-calendar-range me-2 text-primary"></i> Configurar Horario
                        </a>
                    </li>
                </ul>
            </div>
            <div class="hero-content">
                <span class="badge badge-grado mb-3 rounded-pill"><?php echo htmlspecialchars($curso_info['grado']); ?></span>
                <h1 class="hero-title"><?php echo htmlspecialchars($curso_info['nombre_materia']); ?></h1>
                <p class="text-light opacity-75 fs-5 max-w-75 mb-0" style="max-width: 700px;">
                    <?php echo htmlspecialchars($curso_info['descripcion']); ?>
                </p>
                
                <div class="docente-badge shadow-sm">
                    <img src="<?php echo SGA_COMUN_SGA_DATA . '/' . htmlspecialchars($curso_info['foto_docente']); ?>" alt="Docente">
                    <div class="ms-3 pe-3">
                        <small class="d-block text-white-50 lh-1" style="font-size: 0.75rem;">Docente Encargado</small>
                        <span class="fw-semibold text-white"><?php echo htmlspecialchars($curso_info['nombre_docente'] . ' ' . $curso_info['apellido_docente']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DASHBOARD DE ESTADÍSTICAS -->
        <div class="row g-4 mb-4">
            <!-- KPIs -->
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon icon-blue"><i class="bi bi-people-fill"></i></div>
                    <div class="stat-details">
                        <p>Estudiantes Inscritos</p>
                        <h3><?php echo $estadisticas['total_estudiantes']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon icon-purple"><i class="bi bi-journal-check"></i></div>
                    <div class="stat-details">
                        <p>Actividades Públicas</p>
                        <h3><?php echo $estadisticas['total_actividades']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="bi bi-patch-check-fill"></i></div>
                    <div class="stat-details">
                        <p>Tasa Asistencia</p>
                        <h3><?php echo $estadisticas['tasa_asistencia']; ?>%</h3>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="stat-card">
                    <div class="stat-icon icon-orange"><i class="bi bi-trophy-fill"></i></div>
                    <div class="stat-details">
                        <p>Promedio General</p>
                        <h3><?php echo number_format($estadisticas['promedio_general'], 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- Progreso del Curso -->
            <div class="col-lg-8">
                <div class="chart-card">
                    <h5 class="card-title">Avance Temporal del Curso</h5>
                    <div class="d-flex justify-content-between align-items-end mb-2">
                        <h2 class="text-primary fw-bold mb-0"><?php echo $estadisticas['progreso_curso']; ?>%</h2>
                        <span class="text-muted small">Progreso actual</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $estadisticas['progreso_curso']; ?>%;" aria-valuenow="<?php echo $estadisticas['progreso_curso']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 text-muted small fw-medium">
                        <span><i class="bi bi-play-circle me-1"></i> <?php echo Fecha::formato_fecha($horario['fecha_inicio'] ?? 'N/A'); ?></span>
                        <span><i class="bi bi-flag me-1"></i> <?php echo Fecha::formato_fecha($horario['fecha_fin'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
            <!-- Gráfico Donut -->
            <div class="col-lg-4">
                <div class="chart-card d-flex flex-column">
                    <h5 class="card-title mb-0">Distribución de Estudiantes</h5>
                    <div class="flex-grow-1 position-relative mt-3" style="min-height: 160px;">
                        <canvas id="estadosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONTENIDO DEL CURSO (ACORDEONES MODERNIZADOS) -->
        <h4 class="fw-bold mb-3 text-main">Gestión Académica</h4>
        <div class="accordion custom-accordion" id="accordionCurso">
            
            <!-- Actividades -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActividades" aria-expanded="false" aria-controls="collapseActividades">
                        <i class="bi bi-list-task me-3 fs-5 text-primary"></i> Actividades del Curso
                    </button>
                </h2>
                <div id="collapseActividades" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                    <div class="accordion-body p-4 text-muted">
                        <!-- Lógica PHP Actividades -->
                        <p class="mb-0"><i class="bi bi-info-circle me-2"></i> Módulo de actividades en desarrollo...</p>
                    </div>
                </div>
            </div>

            <!-- Planes de Clase (Modificado con JS) -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlanes" aria-expanded="false" aria-controls="collapsePlanes">
                        <i class="bi bi-file-earmark-text me-3 fs-5 text-success"></i> Planes de Clase 
                        <span class="badge bg-success ms-3 rounded-pill"><?php echo $estadisticas['total_planes']; ?></span>
                    </button>
                </h2>
                <div id="collapsePlanes" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                    <div class="accordion-body p-0">
                        
                        <!-- Loader Skeleton -->
                        <div id="planes-loader" class="p-4" style="display: none;">
                            <div class="skeleton skeleton-row"></div>
                            <div class="skeleton skeleton-row"></div>
                            <div class="skeleton skeleton-row"></div>
                            <div class="skeleton skeleton-row"></div>
                        </div>

                        <!-- Contenido Tabla -->
                        <div id="planes-contenido" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-custom align-middle">
                                    <thead>
                                        <tr>
                                            <th>Periodo</th>
                                            <th>Semana</th>
                                            <th style="width: 25%">Objetivo</th>
                                            <th>Eje Temático</th>
                                            <th style="width: 25%">Estrategia</th>
                                            <th class="text-end">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="planes-tbody">
                                        <!-- Inserción dinámica JS -->
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Paginación -->
                            <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
                                <span class="text-muted small">Mostrando resultados paginados</span>
                                <nav id="planes-paginacion" aria-label="Paginación de planes"></nav>
                            </div>
                        </div>

                        <!-- Estado Vacío -->
                        <div id="planes-sin-resultados" class="text-center py-5" style="display: none;">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-folder-x fs-1 text-muted"></i>
                            </div>
                            <h5 class="fw-semibold text-main">No hay planes registrados</h5>
                            <p class="text-muted">Aún no se han creado planes de clase para esta materia y grado.</p>
                            <button class="btn btn-primary mt-2 rounded-pill px-4"><i class="bi bi-plus-lg me-1"></i> Crear Primer Plan</button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Notas de Clase -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotas" aria-expanded="false" aria-controls="collapseNotas">
                        <i class="bi bi-journal-bookmark-fill me-3 fs-5 text-warning"></i> Notas de Clase
                    </button>
                </h2>
                <div id="collapseNotas" class="accordion-collapse collapse" data-bs-parent="#accordionCurso">
                    <div class="accordion-body p-4 text-muted">
                        <!-- Lógica PHP Notas -->
                        <p class="mb-0"><i class="bi bi-info-circle me-2"></i> Módulo de notas en desarrollo...</p>
                    </div>
                </div>
            </div>

        </div>

    <?php else: ?>
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
            <div>
                <strong>Error de acceso:</strong> No se encontró la información del curso. Verifique el ID de la asignación.
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Configuración general Chart.js para aspecto moderno
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#8d99ae';

    // Gráfico de estadísticas
    const chartElement = document.getElementById('estadosChart');
    if (chartElement) {
        const ctx = chartElement.getContext('2d');
        const estadosData = <?php echo json_encode($estadisticas['estudiantes_por_estado'] ?? []); ?>;
        if (Object.keys(estadosData).length > 0) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(estadosData),
                    datasets: [{
                        data: Object.values(estadosData),
                        backgroundColor: ['#4318FF', '#05CD99', '#FFB547', '#E31A1A', '#8B5CF6'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    cutout: '75%',
                    plugins: { 
                        legend: { 
                            position: 'right',
                            labels: { usePointStyle: true, padding: 15, font: { size: 11, weight: '500' } }
                        } 
                    } 
                }
            });
        } else {
            chartElement.parentElement.innerHTML = '<div class="h-100 d-flex align-items-center justify-content-center text-muted small"><i class="bi bi-pie-chart text-light me-2 fs-4"></i> Sin datos para graficar</div>';
        }
    }
    
    // Paginación de planes
    const collapsePlanes = document.getElementById('collapsePlanes');
    const loader = document.getElementById('planes-loader');
    const contenido = document.getElementById('planes-contenido');
    const tbody = document.getElementById('planes-tbody');
    const paginacionContainer = document.getElementById('planes-paginacion');
    const sinResultados = document.getElementById('planes-sin-resultados');
    
    let planesCargados = false;
    const idAsignacion = <?php echo json_encode($id_asignacion); ?>;
    const grado = '<?php echo urlencode($curso_info['grado'] ?? ''); ?>';

    async function cargarPlanes(page = 1) {
        if (!idAsignacion || !grado) return;

        loader.style.display = 'block';
        contenido.style.display = 'none';
        sinResultados.style.display = 'none';
        tbody.innerHTML = '';
        paginacionContainer.innerHTML = '';

        try {
            const response = await fetch(`curso.php?accion=obtener_planes&asignacion=${idAsignacion}&grado=${grado}&page=${page}`);
            if (!response.ok) throw new Error('Error de conexión al servidor.');
            
            const data = await response.json();
            if (data.error) throw new Error(data.error);

            if (data.registros && data.registros.length > 0) {
                data.registros.forEach(plan => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="fw-semibold text-secondary">${plan.periodo || '-'}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-2 text-primary"><i class="bi bi-calendar2-week"></i></div>
                                <div>
                                    <span class="d-block fw-medium text-dark">${plan.fecha_inicio_f}</span>
                                    <small class="text-muted">al ${plan.fecha_fin_f}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-truncate d-inline-block" style="max-width: 250px;" title="${plan.objetivo_corto}">${plan.objetivo_corto}</span></td>
                        <td><span class="badge badge-tematico rounded-pill">${plan.eje_tematico}</span></td>
                        <td><span class="text-truncate d-inline-block text-muted" style="max-width: 200px;">${plan.estrategias_cortas}</span></td>
                        <td class="text-end">
                            <a target="_blank" href="../Planeador/planeador.php?pdf=1&idplan=${plan.id_plan}" class="btn btn-action btn-light text-primary me-1" title="Ver Detalles"><i class="bi bi-eye"></i></a>
                            <button class="btn btn-action btn-light text-secondary" title="Editar"><i class="bi bi-pencil"></i></button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                actualizarPaginacion(data.total_paginas, data.pagina_actual);
                contenido.style.display = 'block';
            } else {
                sinResultados.style.display = 'block';
            }
        } catch (error) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle me-2"></i>Error al cargar los planes: ${error.message}</td></tr>`;
             contenido.style.display = 'block';
        } finally {
            loader.style.display = 'none';
        }
    }

    function actualizarPaginacion(totalPages, currentPage) {
        paginacionContainer.innerHTML = '';
        if (totalPages <= 1) return;

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm mb-0';
        
        currentPage = parseInt(currentPage);
        totalPages = parseInt(totalPages);

        let liPrev = `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link shadow-none" href="#" data-page="${currentPage - 1}">&laquo;</a></li>`;
        let liNext = `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link shadow-none" href="#" data-page="${currentPage + 1}">&raquo;</a></li>`;
        
        let pageLinks = '';
        for (let i = 1; i <= totalPages; i++) {
            pageLinks += `<li class="page-item ${i === currentPage ? 'active' : ''}"><a class="page-link shadow-none" href="#" data-page="${i}">${i}</a></li>`;
        }
        
        ul.innerHTML = liPrev + pageLinks + liNext;
        paginacionContainer.appendChild(ul);
    }
    
    paginacionContainer.addEventListener('click', (e) => {
        e.preventDefault();
        const link = e.target.closest('a.page-link');
        if (link && !link.parentElement.classList.contains('disabled')) {
            const page = link.dataset.page;
            if(page) cargarPlanes(parseInt(page));
        }
    });

    if(collapsePlanes){
      collapsePlanes.addEventListener('shown.bs.collapse', () => {
          if (!planesCargados) {
              cargarPlanes(1);
              planesCargados = true;
          }
      });
    }
});
</script>

</body>
</html>
<?php
ob_end_flush();
?>