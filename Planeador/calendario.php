<?php
/**
 * =================================================================
 * MÓDULO DE CALENDARIO DE PLANEACIÓN
 * =================================================================
 * v4 – Búsqueda de texto asíncrona + filtros correctos
 *
 * Correcciones:
 * - Endpoint AJAX reparado (bloque estaba roto).
 * - Materias del filtro provienen de materia_oficial (nombre real).
 * - Grados del filtro provienen de planeador_vallesol (valores reales).
 * - Nueva caja de búsqueda de texto libre con debounce asíncrono.
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';

// ---- Helpers ----

function calendario_normalizar_texto($valor)
{
    $texto = trim((string)$valor);
    $convertido = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    if ($convertido !== false) {
        $texto = $convertido;
    }
    return strtolower((string)preg_replace('/[^a-z0-9]+/i', '', $texto));
}

function calendario_grado_clave($grado)
{
    $g = calendario_normalizar_texto($grado);
    $equiv = [
        'preescolar' => '0', 'transicion' => '0', 'primero' => '1', 'segundo' => '2',
        'tercero' => '3', 'cuarto' => '4', 'quinto' => '5', 'sexto' => '6',
        'septimo' => '7', 'octavo' => '8', 'noveno' => '9', 'decimo' => '10',
        'once' => '11', 'undecimo' => '11',
    ];
    if (isset($equiv[$g])) return $equiv[$g];
    if (preg_match('/\d+/', $g, $m)) return (string)(int)$m[0];
    return $g;
}

function calendario_buscar_planeacion(array $planeaciones, array $evento)
{
    $fecha = $evento['fecha'];
    $mes   = substr($fecha, 0, 7);
    $gradoEvento  = calendario_grado_clave($evento['grado']);
    $nombreEvento = calendario_normalizar_texto($evento['nombre_materia']);
    $mejor = null;

    foreach ($planeaciones as $plan) {
        $gradoPlan    = calendario_grado_clave($plan['grado']);
        $coincideGrado = $gradoEvento === '' || $gradoPlan === '' || $gradoEvento === $gradoPlan;
        $materiaPlan   = trim((string)$plan['materia']);
        $coincideMateria = $materiaPlan !== '' && (
            $materiaPlan === (string)$evento['id_asignacion'] ||
            $materiaPlan === (string)$evento['id_asignatura'] ||
            calendario_normalizar_texto($materiaPlan) === $nombreEvento
        );
        if (!$coincideMateria) continue;

        $inicio = $plan['fecha_inicio'];
        $fin    = $plan['fecha_fin'];
        if ($inicio === '0000-00-00' || $fin === '0000-00-00' || $inicio > $fin) continue;

        if ($inicio === $fecha) {
            $prioridad = 1; $tipo = 'fecha exacta';
        } elseif ($inicio <= $fecha && $fin >= $fecha) {
            $prioridad = 2; $tipo = 'rango de fechas';
        } elseif (substr($inicio, 0, 7) <= $mes && substr($fin, 0, 7) >= $mes) {
            $prioridad = 3; $tipo = 'mismo mes';
        } else {
            continue;
        }

        $priGrado = $coincideGrado ? 0 : 1;
        if (!$coincideGrado) $tipo .= ' (misma materia, grado ' . $plan['grado'] . ')';

        if ($mejor === null ||
            $priGrado < $mejor['prioridad_grado'] ||
            ($priGrado === $mejor['prioridad_grado'] && $prioridad < $mejor['prioridad']) ||
            ($priGrado === $mejor['prioridad_grado'] && $prioridad === $mejor['prioridad'] && $inicio > $mejor['plan']['fecha_inicio'])
        ) {
            $mejor = ['plan' => $plan, 'tipo' => $tipo, 'prioridad' => $prioridad, 'prioridad_grado' => $priGrado];
        }
    }
    return $mejor;
}

// =================================================================
// 2. ENDPOINT AJAX: buscar_planeaciones
//    Soporta: q (texto libre), materia, grado, startDate, endDate,
//    createdDate, limit, page
// =================================================================
if (isset($_GET['action']) && $_GET['action'] === 'buscar_planeaciones') {
    header('Content-Type: application/json; charset=utf-8');

    $recordsPerPage = max(1, min(100, (int)($_GET['limit'] ?? 10)));
    $page           = max(1, (int)($_GET['page'] ?? 1));
    $offset         = ($page - 1) * $recordsPerPage;

    $baseSql = "FROM planeador_vallesol p
                LEFT JOIN materia_oficial mo ON mo.id_materia = CAST(TRIM(p.materia) AS UNSIGNED)
                LEFT JOIN materia m2 ON mo.id_materia IS NULL AND m2.id_materia = CAST(TRIM(p.materia) AS UNSIGNED)";

    $where  = [];
    $params = [];
    $types  = '';

    // — Búsqueda de texto libre —
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $like = '%' . $mysqli->real_escape_string($q) . '%';   // solo para construir el %…%
        $where[] = '(
            COALESCE(mo.nombre_materia, m2.nombre_materia, p.materia) LIKE ?
            OR p.grado      LIKE ?
            OR p.dba        LIKE ?
            OR p.objetivo   LIKE ?
            OR p.eje_tematico LIKE ?
            OR p.observaciones LIKE ?
            OR p.estrategias LIKE ?
            OR p.reflexion  LIKE ?
        )';
        $searchTerm = '%' . $q . '%';
        for ($i = 0; $i < 8; $i++) {
            $params[] = $searchTerm;
            $types   .= 's';
        }
    }

    // — Filtro materia (nombre de materia_oficial) —
    $materiaFiltro = trim($_GET['materia'] ?? '');
    if ($materiaFiltro !== '') {
        $where[]  = 'COALESCE(mo.nombre_materia, m2.nombre_materia, p.materia) = ?';
        $params[] = $materiaFiltro;
        $types   .= 's';
    }

    // — Filtro grado —
    $gradoFiltro = trim($_GET['grado'] ?? '');
    if ($gradoFiltro !== '') {
        $gradoClave = calendario_grado_clave($gradoFiltro);
        // coincide con el valor exacto o con la clave numérica equivalente
        $where[]  = '(TRIM(p.grado) = ? OR CAST(TRIM(p.grado) AS UNSIGNED) = ? OR TRIM(p.grado) = ?)';
        $params[] = $gradoFiltro;
        $params[] = $gradoClave;
        $params[] = $gradoClave;
        $types   .= 'sss';
    }

    // — Rango de fechas —
    $startDate = trim($_GET['startDate'] ?? '');
    if ($startDate !== '') {
        $where[]  = 'p.fecha_fin >= ?';
        $params[] = $startDate;
        $types   .= 's';
    }
    $endDate = trim($_GET['endDate'] ?? '');
    if ($endDate !== '') {
        $where[]  = 'p.fecha_inicio <= ?';
        $params[] = $endDate;
        $types   .= 's';
    }

    // — Fecha de creación —
    $createdDate = trim($_GET['createdDate'] ?? '');
    if ($createdDate !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $createdDate)) {
            http_response_code(400);
            echo json_encode(['error' => 'La fecha de creación no es válida.', 'data' => [], 'total' => 0], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $where[]  = 'DATE(p.fecha_creacion) = ?';
        $params[] = $createdDate;
        $types   .= 's';
    }

    $whereClause = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    // — Contar total —
    $totalRecords = 0;
    $stmtTotal    = $mysqli->prepare("SELECT COUNT(DISTINCT p.id_plan) AS total $baseSql$whereClause");
    if ($stmtTotal) {
        if ($params) $stmtTotal->bind_param($types, ...$params);
        $stmtTotal->execute();
        $totalRecords = (int)($stmtTotal->get_result()->fetch_assoc()['total'] ?? 0);
        $stmtTotal->close();
    }

    // — Datos de la página —
    $dataSql = "SELECT DISTINCT
                    p.id_plan,
                    COALESCE(mo.nombre_materia, m2.nombre_materia, p.materia) AS title,
                    p.grado,
                    p.fecha_inicio AS start,
                    p.fecha_fin    AS end,
                    DATE(p.fecha_creacion) AS fecha_creacion,
                    p.objetivo,
                    p.eje_tematico
                $baseSql$whereClause
                ORDER BY p.fecha_inicio DESC, p.id_plan DESC
                LIMIT ? OFFSET ?";

    $dataParams = array_merge($params, [$recordsPerPage, $offset]);
    $dataTypes  = $types . 'ii';
    $planes     = [];

    $stmtData = $mysqli->prepare($dataSql);
    if ($stmtData) {
        $stmtData->bind_param($dataTypes, ...$dataParams);
        $stmtData->execute();
        $planes = $stmtData->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtData->close();
    } else {
        http_response_code(500);
        echo json_encode(['error' => $mysqli->error, 'data' => [], 'total' => 0]);
        exit;
    }

    echo json_encode(['data' => $planes, 'total' => $totalRecords], JSON_UNESCAPED_UNICODE);
    exit;
}

// =================================================================
// 3. ENDPOINT AJAX: planeaciones_por_fecha_creacion
//    Datos completos para construir el ZIP de planeaciones del día.
// =================================================================
if (isset($_GET['action']) && $_GET['action'] === 'planeaciones_por_fecha_creacion') {
    header('Content-Type: application/json; charset=utf-8');

    $fechaCreacion = trim((string)($_GET['fechaCreacion'] ?? ''));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCreacion)) {
        http_response_code(400);
        echo json_encode(['error' => 'Seleccione una fecha de creación válida.', 'data' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $sql = "SELECT p.id_plan, p.fecha_creacion, p.fecha_inicio, p.fecha_fin, p.grado, p.materia,
                   p.periodo, p.tiempo_plan, p.dba, p.estrategias, p.observaciones, p.reflexion,
                   p.objetivo, p.eje_tematico, p.evaluacion,
                   COALESCE(mo.nombre_materia, m2.nombre_materia, p.materia) AS materia_nombre
            FROM planeador_vallesol p
            LEFT JOIN materia_oficial mo ON mo.id_materia = CAST(TRIM(p.materia) AS UNSIGNED)
            LEFT JOIN materia m2 ON mo.id_materia IS NULL AND m2.id_materia = CAST(TRIM(p.materia) AS UNSIGNED)
            WHERE DATE(p.fecha_creacion) = ?
            ORDER BY p.id_plan ASC";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => 'No fue posible consultar las planeaciones.', 'data' => []], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $stmt->bind_param('s', $fechaCreacion);
    $stmt->execute();
    $planes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    echo json_encode(['data' => $planes, 'total' => count($planes)], JSON_UNESCAPED_UNICODE);
    exit;
}

// =================================================================
// 4. ENDPOINT AJAX: obtener_filtros
//    Devuelve materias (de materia_oficial) y grados reales del planeador
// =================================================================
if (isset($_GET['action']) && $_GET['action'] === 'obtener_filtros') {
    header('Content-Type: application/json; charset=utf-8');

    // Materias: solo las que existen en planeador_vallesol, nombre desde materia_oficial
    $sqlMaterias = "SELECT DISTINCT COALESCE(mo.nombre_materia, p.materia) AS nombre
                    FROM planeador_vallesol p
                    LEFT JOIN materia_oficial mo ON mo.id_materia = CAST(TRIM(p.materia) AS UNSIGNED)
                    WHERE COALESCE(mo.nombre_materia, NULLIF(TRIM(p.materia), '')) IS NOT NULL
                    ORDER BY nombre ASC";
    $resMaterias = $mysqli->query($sqlMaterias);
    $materias    = $resMaterias ? $resMaterias->fetch_all(MYSQLI_ASSOC) : [];

    // Grados: valores distintos reales de planeador_vallesol
    $sqlGrados = "SELECT DISTINCT TRIM(grado) AS grado
                  FROM planeador_vallesol
                  WHERE TRIM(grado) IS NOT NULL AND TRIM(grado) != ''
                  ORDER BY CHAR_LENGTH(TRIM(grado)) ASC, grado ASC";
    $resGrados = $mysqli->query($sqlGrados);
    $grados    = $resGrados ? array_column($resGrados->fetch_all(MYSQLI_ASSOC), 'grado') : [];

    echo json_encode([
        'materias' => array_column($materias, 'nombre'),
        'grados'   => $grados,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =================================================================
// 4. ENDPOINT AJAX: materias_sin_planeacion
//    Combinaciones materia/grado activas que no tienen plan vigente
//    durante el mes solicitado.
// =================================================================
if (isset($_GET['action']) && $_GET['action'] === 'materias_sin_planeacion') {
    header('Content-Type: application/json; charset=utf-8');

    $mes = trim((string)($_GET['month'] ?? ''));
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Seleccione un mes válido.', 'data' => [], 'total' => 0], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $inicioMes = $mes . '-01';
    $finMes    = date('Y-m-t', strtotime($inicioMes));

    // Las asignaciones del año lectivo activo definen las materias y grados
    // que deben contar con planeación. Si no hay año activo, se conservan
    // todas las asignaciones para no ocultar información.
    $anoActivo = $mysqli->query("SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo WHERE estado = 'Activo' LIMIT 1");
    $datosAno  = $anoActivo ? $anoActivo->fetch_assoc() : null;
    $filtroAno = '';
    if ($datosAno) {
        $idAno     = (int)$datosAno['id_ano_lectivo'];
        $nombreAno = (int)$datosAno['nombre_ano_lectivo'];
        $filtroAno = " WHERE (a.ano_lectivo = $idAno OR a.ano_lectivo = $nombreAno)";
    }

    $sqlAsignaciones = "SELECT DISTINCT
                            a.id_asignatura,
                            a.id_categoria_curso,
                            COALESCE(NULLIF(TRIM(mo.nombre_materia), ''), NULLIF(TRIM(m2.nombre_materia), ''), CONCAT('Materia #', a.id_asignatura)) AS materia,
                            COALESCE(NULLIF(TRIM(cc.nombre_categoria_curso), ''), CAST(a.id_categoria_curso AS CHAR)) AS grado
                        FROM asignacion a
                        LEFT JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
                        LEFT JOIN materia m2 ON m2.id_materia = a.id_asignatura
                        LEFT JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso"
                     . $filtroAno
                     . " ORDER BY grado ASC, materia ASC";
    $resAsignaciones = $mysqli->query($sqlAsignaciones);
    $asignaciones = $resAsignaciones ? $resAsignaciones->fetch_all(MYSQLI_ASSOC) : [];

    $stmtPlanes = $mysqli->prepare(
        'SELECT id_plan, materia, grado, fecha_inicio, fecha_fin
         FROM planeador_vallesol
         WHERE fecha_inicio <= ? AND fecha_fin >= ?'
    );
    $planes = [];
    if ($stmtPlanes) {
        $stmtPlanes->bind_param('ss', $finMes, $inicioMes);
        $stmtPlanes->execute();
        $planes = $stmtPlanes->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtPlanes->close();
    }

    $sinPlaneacion = [];
    foreach ($asignaciones as $asignacion) {
        $materiaAsignacion = calendario_normalizar_texto($asignacion['materia']);
        $gradoAsignacion   = calendario_grado_clave($asignacion['grado']);
        $tienePlaneacion   = false;

        foreach ($planes as $plan) {
            $coincideMateria = (string)$plan['materia'] === (string)$asignacion['id_asignatura']
                || ($materiaAsignacion !== '' && calendario_normalizar_texto($plan['materia']) === $materiaAsignacion);
            $coincideGrado = $gradoAsignacion !== ''
                && calendario_grado_clave($plan['grado']) === $gradoAsignacion;
            if ($coincideMateria && $coincideGrado) {
                $tienePlaneacion = true;
                break;
            }
        }

        if (!$tienePlaneacion) {
            $sinPlaneacion[] = [
                'materia' => $asignacion['materia'],
                'grado'   => $asignacion['grado'],
            ];
        }
    }

    echo json_encode([
        'month' => $mes,
        'data'  => $sinPlaneacion,
        'total' => count($sinPlaneacion),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// =================================================================
// 5. LÓGICA PARA LA CARGA INICIAL DE LA PÁGINA (calendario visual)
// =================================================================
$eventos_calendario = [];

$diasSemana = [
    'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4,
    'viernes' => 5, 'sabado' => 6, 'domingo' => 0,
];

$anoLectivo     = $mysqli->query("SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo WHERE estado = 'Activo' LIMIT 1");
$datosAnoLectivo = $anoLectivo ? $anoLectivo->fetch_assoc() : null;
$anioCalendario = (int)guagua_ano_configurado($mysqli);

$inicioCalendario = $anioCalendario . '-01-01';
$finCalendario    = $anioCalendario . '-12-31';
$idAnoLectivo     = isset($datosAnoLectivo['id_ano_lectivo']) ? (int)$datosAnoLectivo['id_ano_lectivo'] : 0;

// Planeaciones del año para asociar al calendario
$planeaciones = [];
$stmtP = $mysqli->prepare(
    'SELECT id_plan, materia, grado, fecha_inicio, fecha_fin
     FROM planeador_vallesol
     WHERE fecha_inicio <= ? AND fecha_fin >= ?'
);
if ($stmtP) {
    $stmtP->bind_param('ss', $finCalendario, $inicioCalendario);
    $stmtP->execute();
    $planeaciones = $stmtP->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtP->close();
}

$filtroAno = '';
if ($idAnoLectivo > 0) {
    $filtroAno = ' AND (a.ano_lectivo = ' . $idAnoLectivo . ' OR a.ano_lectivo = ' . $anioCalendario . ')';
}

$iEsc = $mysqli->real_escape_string($inicioCalendario);
$fEsc = $mysqli->real_escape_string($finCalendario);

$sqlHorario = "SELECT h.id_horario, h.id_asignacion, h.fecha_inicio, h.fecha_fin,
                       h.hora_inicio, h.hora_fin, h.dia,
                       a.id_asignatura, a.id_categoria_curso,
                       COALESCE(cc.nombre_categoria_curso, a.id_categoria_curso) AS grado,
                       COALESCE(m.nombre_materia, CONCAT('Materia ID ', a.id_asignatura)) AS nombre_materia
               FROM horario h
               INNER JOIN asignacion a ON a.id_asignacion = h.id_asignacion
               LEFT JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
               LEFT JOIN materia_oficial m ON m.id_materia = a.id_asignatura
               WHERE (h.fecha_inicio <= '$fEsc' OR h.fecha_inicio = '0000-00-00' OR h.fecha_inicio IS NULL)
                 AND (h.fecha_fin >= '$iEsc' OR h.fecha_fin = '0000-00-00' OR h.fecha_fin IS NULL)"
             . $filtroAno
             . " ORDER BY h.dia, h.hora_inicio, nombre_materia";

$resHorario = $mysqli->query($sqlHorario);
if ($resHorario) {
    while ($fila = $resHorario->fetch_assoc()) {
        $nombreMateria = trim((string)$fila['nombre_materia']);
        $grado         = trim((string)$fila['grado']);
        $dia           = calendario_normalizar_texto($fila['dia']);
        if (!isset($diasSemana[$dia])) continue;

        $fInicio = ($fila['fecha_inicio'] && $fila['fecha_inicio'] !== '0000-00-00') ? $fila['fecha_inicio'] : $inicioCalendario;
        $fFin    = ($fila['fecha_fin']    && $fila['fecha_fin']    !== '0000-00-00') ? $fila['fecha_fin']    : $finCalendario;
        $fInicio = max($fInicio, $inicioCalendario);
        $fFin    = min($fFin, $finCalendario);
        if ($fInicio > $fFin) continue;

        $dActual = new DateTime($fInicio);
        $limite  = new DateTime($fFin);
        while ($dActual <= $limite) {
            if ((int)$dActual->format('w') === $diasSemana[$dia]) {
                $fechaEvento = $dActual->format('Y-m-d');
                $coincidencia = calendario_buscar_planeacion($planeaciones, [
                    'fecha'         => $fechaEvento,
                    'id_asignacion' => $fila['id_asignacion'],
                    'id_asignatura' => $fila['id_asignatura'],
                    'nombre_materia'=> $nombreMateria,
                    'grado'         => $grado,
                ]);
                $plan = $coincidencia['plan'] ?? null;

                $eventos_calendario[] = [
                    'title'                  => $nombreMateria,
                    'start'                  => $fechaEvento . 'T' . $fila['hora_inicio'],
                    'end'                    => $fechaEvento . 'T' . $fila['hora_fin'],
                    'description'            => 'Horario programado',
                    'id_horario'             => (int)$fila['id_horario'],
                    'id_asignacion'          => (int)$fila['id_asignacion'],
                    'id_asignatura'          => (int)$fila['id_asignatura'],
                    'grado'                  => $grado,
                    'dia'                    => ucfirst($dia),
                    'hora_inicio'            => substr($fila['hora_inicio'], 0, 5),
                    'hora_fin'               => substr($fila['hora_fin'], 0, 5),
                    'fecha_inicio_horario'   => $fInicio,
                    'fecha_fin_horario'      => $fFin,
                    'id_plan'                => $plan ? (int)$plan['id_plan'] : null,
                    'fecha_inicio_planeacion'=> $plan['fecha_inicio'] ?? null,
                    'fecha_fin_planeacion'   => $plan['fecha_fin'] ?? null,
                    'coincidencia_planeacion'=> $coincidencia['tipo'] ?? null,
                    'className'              => 'evento-' . (calendario_normalizar_texto($nombreMateria) ?: 'materia'),
                ];
            }
            $dActual->modify('+1 day');
        }
    }
}

usort($eventos_calendario, static fn($a, $b) => strcmp($a['start'], $b['start']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Planeación</title>
    <style>
        :root {
            --primary:   #2c5282;
            --accent:    #ed8936;
            --bg:        #f7fafc;
            --card:      #ffffff;
            --text:      #2d3748;
            --gray:      #e2e8f0;
            --today-bg:  #fffde7;
            --evt-text:  #ffffff;
        }
        * { box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
               background: var(--bg); color: var(--text); margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; }

        /* ---- Calendario ---- */
        .calendar-container { background: var(--card); padding: 25px; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.1); margin-bottom: 30px; }
        .calendar-header { display: flex; justify-content: space-between; align-items: center;
            padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--gray); }
        .calendar-header .title-group { text-align: right; }
        .calendar-header .title-group h1 { margin: 0; font-size: 1.8em; color: var(--primary); }
        .calendar-nav button { background: none; border: 1px solid var(--gray); padding: 8px 14px;
            margin: 0 4px; cursor: pointer; border-radius: 8px; transition: all .2s; font-weight: 500; }
        .calendar-nav button:hover { background: #edf2f7; border-color: #cbd5e0; }
        .calendar-nav button#today-btn { background: var(--accent); color: #fff; border-color: var(--accent); }
        .calendar-nav button#today-btn:hover { background: #dd6b20; }
        #month-year-display { font-size: 1.5em; font-weight: 600; color: var(--primary);
            text-transform: capitalize; min-width: 200px; text-align: right; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr);
            gap: 1px; background: var(--gray); border: 1px solid var(--gray); }
        .day-header, .day { background: var(--card); padding: 8px; }
        .day-header { text-align: center; font-weight: 600; color: #718096; padding: 12px 5px; font-size: .9em; }
        .day { min-height: 120px; position: relative; }
        .day-number { font-size: .85em; font-weight: 600; color: #4a5568; }
        .day.outside-month .day-number { color: #a0aec0; }
        .day.today { background: var(--today-bg); }
        .day.today .day-number { color: var(--accent); background: #feebc8; border-radius: 50%;
            width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; }
        .events { margin-top: 5px; }
        .event { font-size: .75em; padding: 3px 6px; border-radius: 4px; margin-bottom: 4px;
            background: #718096; color: var(--evt-text); cursor: pointer;
            overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        /* Colores de materia */
        .evento-matematicas { background: #4299e1; }
        .evento-cienciassociales { background: #f56565; }
        .evento-educacionfisica { background: #48bb78; }
        .evento-emprendimiento { background: #dd6b20; }
        .evento-tecnologiaeinformatica { background: #718096; }
        .evento-urbanidad { background: #d69e2e; }
        .evento-fisica { background: #805ad5; }
        .evento-economiapolitica { background: #319795; }
        .evento-geometria { background: #d53f8c; }
        #calendar-legend { margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--gray);
            display: flex; flex-wrap: wrap; gap: 15px; }
        .legend-item { display: flex; align-items: center; font-size: .85em; }
        .legend-color-box { width: 15px; height: 15px; border-radius: 4px; margin-right: 8px; }

        /* ---- Modal ---- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,.6); display: flex; justify-content: center;
            align-items: center; z-index: 1000; }
        .modal-content { background: #fff; padding: 30px; border-radius: 12px;
            width: 90%; max-width: 500px; position: relative; }
        .modal-close-btn { position: absolute; top: 15px; right: 15px; background: none;
            border: none; font-size: 1.8em; cursor: pointer; color: #a0aec0; }
        #modal-title { margin-top: 0; color: var(--primary); }
        #modal-description { font-size: .95em; line-height: 1.6; color: #4a5568; white-space: pre-line; }
        .modal-button { display: inline-block; margin-top: 20px; padding: 10px 20px;
            background: var(--accent); color: #fff; text-decoration: none; border-radius: 8px; }
        .modal-button.secondary { background: var(--primary); margin-left: 8px; }

        /* ---- Sección de búsqueda ---- */
        .search-container { background: var(--card); padding: 25px; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.1); }
        .search-container h2 { margin-top: 0; color: var(--primary);
            border-bottom: 1px solid var(--gray); padding-bottom: 15px; margin-bottom: 20px; }

        /* Caja de búsqueda de texto */
        .search-box-wrap { position: relative; margin-bottom: 20px; }
        .search-box-wrap input[type="search"] {
            width: 100%; padding: 12px 16px 12px 44px; font-size: 1em;
            border: 2px solid var(--gray); border-radius: 10px; background: #fdfdfd;
            transition: border-color .2s;
        }
        .search-box-wrap input[type="search"]:focus { outline: none; border-color: var(--primary); }
        .search-box-wrap .search-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #a0aec0; font-size: 1.1em; pointer-events: none;
        }
        .search-box-wrap .search-clear {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #a0aec0; font-size: 1.2em;
            display: none;
        }

        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px; margin-bottom: 25px; }
        .filter-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: .88em; color: #4a5568; }
        .filter-group select, .filter-group input[type="date"] {
            width: 100%; padding: 10px; border: 1px solid var(--gray);
            border-radius: 8px; background: #fdfdfd; font-size: .9em;
        }

        /* Resultados */
        #search-results { list-style: none; padding: 0; min-height: 60px; }
        .result-item { display: block; padding: 14px 16px; border-radius: 8px; margin-bottom: 10px;
            background: #f7fafc; border: 1px solid var(--gray); text-decoration: none;
            color: var(--text); transition: all .2s; }
        .result-item:hover { border-color: var(--accent); background: #fff;
            transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,.05); }
        .result-item strong { color: var(--primary); font-size: 1em; }
        .result-item .result-meta { color: #718096; font-size: .88em; margin-top: 4px; }
        .result-item .result-objetivo { color: #4a5568; font-size: .85em; margin-top: 4px;
            overflow: hidden; text-overflow: ellipsis; display: -webkit-box;
            -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .result-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .result-action, .bulk-download-button { border: 1px solid var(--primary); border-radius: 7px;
            background: #fff; color: var(--primary); padding: 7px 10px; cursor: pointer; font-size: .82em;
            font-weight: 600; text-decoration: none; }
        .result-action:hover, .bulk-download-button:hover { background: #ebf8ff; }
        .result-action.save { border-color: #2f855a; color: #2f855a; }
        .result-action.save:hover { background: #f0fff4; }
        .bulk-export { display: flex; align-items: end; gap: 12px; flex-wrap: wrap; margin: -4px 0 22px;
            padding: 14px; border: 1px solid #bee3f8; border-radius: 9px; background: #ebf8ff; }
        .bulk-export label { display: block; font-weight: 600; margin-bottom: 6px; font-size: .88em; color: #2c5282; }
        .bulk-export input { padding: 8px; border: 1px solid #90cdf4; border-radius: 7px; font-size: .9em; }
        .bulk-download-button { background: var(--primary); color: #fff; padding: 9px 13px; }
        .bulk-download-button:hover { background: #1f436d; }
        #bulk-export-status { width: 100%; color: #2c5282; font-size: .88em; min-height: 1.15em; }
        #no-results-message { text-align: center; padding: 40px; color: #718096; }

        /* Paginación */
        #pagination-controls { display: flex; justify-content: center; align-items: center;
            gap: 8px; margin-top: 20px; flex-wrap: wrap; }
        .page-link { padding: 8px 14px; border: 1px solid var(--gray); border-radius: 8px;
            background: #fff; color: var(--primary); text-decoration: none;
            cursor: pointer; transition: all .2s; font-weight: 500; }
        .page-link:hover { background: #edf2f7; border-color: #cbd5e0; }
        .page-link.active { background: var(--primary); color: #fff; border-color: var(--primary); cursor: default; }
        .page-link.disabled { color: #a0aec0; cursor: not-allowed; background: #f7fafc; pointer-events: none; }
        .page-info { font-size: .88em; color: #718096; padding: 8px; }

        /* Spinner */
        #loading-spinner { text-align: center; padding: 20px; color: #718096; }
        .spinner { display: inline-block; width: 24px; height: 24px; border: 3px solid var(--gray);
            border-top-color: var(--primary); border-radius: 50%; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---- Pestañas y materias sin planeación ---- */
        .module-tabs { display: flex; gap: 8px; margin-bottom: 18px; border-bottom: 1px solid var(--gray); }
        .module-tab { border: 0; background: transparent; color: #718096; cursor: pointer; font-size: .95em;
            font-weight: 600; padding: 11px 16px; border-bottom: 3px solid transparent; }
        .module-tab:hover { color: var(--primary); background: #edf2f7; }
        .module-tab.active { color: var(--primary); border-bottom-color: var(--accent); }
        .missing-container { background: var(--card); padding: 25px; border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,.1); }
        .missing-container h2 { margin-top: 0; color: var(--primary); }
        .missing-description { color: #718096; margin: -6px 0 20px; }
        .missing-controls { display: flex; align-items: end; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
        .missing-controls label { display: block; font-weight: 600; margin-bottom: 6px; font-size: .88em; color: #4a5568; }
        .missing-controls input { display: block; padding: 10px; border: 1px solid var(--gray); border-radius: 8px; font-size: .95em; }
        .missing-controls button { padding: 10px 16px; border: 0; border-radius: 8px; background: var(--primary); color: #fff;
            font-weight: 600; cursor: pointer; }
        .missing-controls button:hover { background: #1f436d; }
        #missing-summary { color: #4a5568; font-weight: 600; margin-bottom: 12px; }
        #missing-results { list-style: none; margin: 0; padding: 0; display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 12px; }
        .missing-item { background: #fffaf0; border: 1px solid #fbd38d; border-left: 5px solid var(--accent);
            border-radius: 8px; padding: 14px; }
        .missing-item strong { color: var(--primary); display: block; }
        .missing-item span { color: #718096; font-size: .9em; display: block; margin-top: 4px; }
        #missing-empty { color: #2f855a; background: #f0fff4; border: 1px solid #9ae6b4; border-radius: 8px; padding: 16px; }
        #missing-error { color: #c53030; background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; padding: 16px; }
    </style>
</head>
<body>

<div class="container">

    <div class="module-tabs" role="tablist" aria-label="Vistas de planeación">
        <button class="module-tab active" type="button" data-panel="planning-panel" role="tab" aria-selected="true">Calendario y búsqueda</button>
        <button class="module-tab" type="button" data-panel="missing-panel" role="tab" aria-selected="false">Materias sin planeación</button>
    </div>

    <div id="planning-panel" role="tabpanel">

    <!-- ===================== CALENDARIO ===================== -->
    <div class="calendar-container">
        <div class="calendar-header">
            <div class="calendar-nav">
                <button id="prev-month-btn">&lt; Anterior</button>
                <button id="today-btn">Hoy</button>
                <button id="next-month-btn">Siguiente &gt;</button>
            </div>
            <div class="title-group">
                <h1 id="month-year-display"></h1>
            </div>
        </div>
        <div class="calendar-grid day-headers">
            <div class="day-header">Domingo</div>
            <div class="day-header">Lunes</div>
            <div class="day-header">Martes</div>
            <div class="day-header">Miércoles</div>
            <div class="day-header">Jueves</div>
            <div class="day-header">Viernes</div>
            <div class="day-header">Sábado</div>
        </div>
        <div id="calendar-body" class="calendar-grid"></div>
        <div id="calendar-legend"></div>
    </div>

    <!-- ===================== BÚSQUEDA ===================== -->
    <div class="search-container">
        <h2>📋 Búsqueda de Planeaciones (Vallesol)</h2>

        <div class="bulk-export">
            <div>
                <label for="created-date-filter">Fecha de creación</label>
                <input type="date" id="created-date-filter">
            </div>
            <button type="button" id="bulk-download-btn" class="bulk-download-button">Descargar todas las planeaciones del día</button>
            <span id="bulk-export-status" role="status" aria-live="polite"></span>
        </div>

        <!-- Caja de búsqueda por texto libre -->
        <div class="search-box-wrap">
            <span class="search-icon">🔍</span>
            <input type="search" id="q-input" placeholder="Buscar por objetivo, DBA, eje temático, materia, grado…" autocomplete="off">
            <button class="search-clear" id="q-clear-btn" title="Limpiar búsqueda">✕</button>
        </div>

        <!-- Filtros adicionales -->
        <div class="filters">
            <div class="filter-group">
                <label for="materia-filter">Materia</label>
                <select id="materia-filter"><option value="">Todas</option></select>
            </div>
            <div class="filter-group">
                <label for="grado-filter">Grado</label>
                <select id="grado-filter"><option value="">Todos</option></select>
            </div>
            <div class="filter-group">
                <label for="start-date-filter">Desde</label>
                <input type="date" id="start-date-filter">
            </div>
            <div class="filter-group">
                <label for="end-date-filter">Hasta</label>
                <input type="date" id="end-date-filter">
            </div>
            <div class="filter-group">
                <label for="created-date-search-filter">Creada el</label>
                <input type="date" id="created-date-search-filter">
            </div>
            <div class="filter-group">
                <label for="limit-filter">Registros por página</label>
                <select id="limit-filter">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <ul id="search-results"></ul>
        <div id="loading-spinner" style="display:none;"><span class="spinner"></span></div>
        <p id="no-results-message" style="display:none;">No se encontraron planeaciones con los filtros seleccionados.</p>
        <div id="pagination-controls"></div>
    </div>

    </div><!-- /#planning-panel -->

    <!-- ===================== MATERIAS SIN PLANEACIÓN ===================== -->
    <div id="missing-panel" class="missing-container" role="tabpanel" hidden>
        <h2>Materias y grados sin planeación</h2>
        <p class="missing-description">Consulta las asignaciones del año lectivo activo que no tienen una planeación vigente durante el mes seleccionado.</p>
        <div class="missing-controls">
            <div>
                <label for="missing-month">Mes a revisar</label>
                <input type="month" id="missing-month">
            </div>
            <button type="button" id="load-missing-btn">Consultar</button>
        </div>
        <div id="missing-summary" aria-live="polite"></div>
        <div id="missing-loading" style="display:none;"><span class="spinner"></span> Consultando asignaciones…</div>
        <p id="missing-empty" style="display:none;">Todas las materias y grados tienen planeación en este mes.</p>
        <p id="missing-error" style="display:none;">No se pudo cargar la consulta.</p>
        <ul id="missing-results"></ul>
    </div>

</div><!-- /.container -->

<!-- ===================== MODAL ===================== -->
<div id="event-modal" class="modal-overlay" style="display:none;">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2 id="modal-title"></h2>
        <p id="modal-description"></p>
        <a id="modal-link" href="#" target="_blank" class="modal-button" hidden>Ver Planeación Completa</a>
        <a id="modal-course-link" href="#" target="_blank" class="modal-button secondary" hidden>Ver Curso</a>
    </div>
</div>

<script src="../apps/PlanMind/pizzip.min.js"></script>
<script src="../apps/PlanMind/docxtemplater.js"></script>
<script src="../apps/PlanMind/FileSaver.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===================== DATOS PHP =====================
    const eventos_calendario = <?php echo json_encode($eventos_calendario, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

    // ===================== CALENDARIO =====================
    const calendarBody     = document.getElementById('calendar-body');
    const monthYearDisplay = document.getElementById('month-year-display');
    const legendContainer  = document.getElementById('calendar-legend');
    const anoConfigurado = <?php echo $anioCalendario; ?>;
    let currentDate = new Date(anoConfigurado, new Date().getMonth(), 1);

    function renderCalendar() {
        calendarBody.innerHTML = '';
        const year  = currentDate.getFullYear();
        const month = currentDate.getMonth();
        monthYearDisplay.textContent = new Date(year, month)
            .toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });

        const firstDay = new Date(year, month, 1).getDay();
        const lastDay  = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++)
            calendarBody.insertAdjacentHTML('beforeend', '<div class="day outside-month"></div>');

        const today = new Date();
        for (let d = 1; d <= lastDay; d++) {
            const div = document.createElement('div');
            div.classList.add('day');
            if (d === today.getDate() && month === today.getMonth() && year === today.getFullYear())
                div.classList.add('today');

            const num = document.createElement('div');
            num.classList.add('day-number');
            num.textContent = d;
            div.appendChild(num);

            const fechaStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const evDiv = document.createElement('div');
            evDiv.classList.add('events');
            eventos_calendario
                .filter(e => e.start.startsWith(fechaStr))
                .forEach(ev => {
                    const b = document.createElement('div');
                    b.classList.add('event', ev.className);
                    b.textContent = ev.title;
                    b.title = ev.description;
                    b.addEventListener('click', () => showModal(ev));
                    evDiv.appendChild(b);
                });
            div.appendChild(evDiv);
            calendarBody.appendChild(div);
        }

        const total     = firstDay + lastDay;
        const remaining = (7 - (total % 7)) % 7;
        for (let i = 0; i < remaining; i++)
            calendarBody.insertAdjacentHTML('beforeend', '<div class="day outside-month"></div>');
    }

    function renderLegend() {
        const used = {};
        eventos_calendario.forEach(e => { if (!used[e.className]) used[e.className] = e.title; });
        legendContainer.innerHTML = '';
        for (const cls in used) {
            legendContainer.innerHTML +=
                `<div class="legend-item"><div class="legend-color-box ${cls}"></div><span>${used[cls]}</span></div>`;
        }
    }

    document.getElementById('prev-month-btn').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar();
    });
    document.getElementById('next-month-btn').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar();
    });
    document.getElementById('today-btn').addEventListener('click', () => {
        currentDate = new Date(); renderCalendar();
    });

    // ===================== MODAL =====================
    const modal          = document.getElementById('event-modal');
    const modalTitle     = document.getElementById('modal-title');
    const modalDesc      = document.getElementById('modal-description');
    const modalLink      = document.getElementById('modal-link');
    const modalCourse    = document.getElementById('modal-course-link');
    const modalCloseBtn  = modal.querySelector('.modal-close-btn');

    function showModal(ev) {
        modalTitle.textContent = ev.title;
        const lines = [
            `Asignación: #${ev.id_asignacion}`,
            `Horario: ${ev.dia}, ${ev.hora_inicio} a ${ev.hora_fin}`,
            `Grado: ${ev.grado}`,
            `Vigencia del horario: ${ev.fecha_inicio_horario} al ${ev.fecha_fin_horario}`,
        ];
        if (ev.id_plan) {
            lines.push(`Planeación: #${ev.id_plan} (${ev.coincidencia_planeacion})`);
            lines.push(`Vigencia planeación: ${ev.fecha_inicio_planeacion} al ${ev.fecha_fin_planeacion}`);
            modalLink.href   = `../apps/PlanMind/index.php?id=${encodeURIComponent(ev.id_plan)}`;
            modalLink.hidden = false;
        } else {
            lines.push('Sin planeación asociada para esta materia, grado y fecha.');
            modalLink.hidden = true;
        }
        if (ev.id_asignacion) {
            modalCourse.href   = `../cursos/curso.php?asignacion=${encodeURIComponent(ev.id_asignacion)}`;
            modalCourse.hidden = false;
        } else {
            modalCourse.hidden = true;
        }
        modalDesc.textContent = lines.join('\n');
        modal.style.display = 'flex';
    }
    function hideModal() { modal.style.display = 'none'; }
    modalCloseBtn.addEventListener('click', hideModal);
    modal.addEventListener('click', e => e.target === modal && hideModal());
    document.addEventListener('keydown', e => e.key === 'Escape' && hideModal());

    // ===================== FILTROS (carga asíncrona) =====================
    const materiaFilter  = document.getElementById('materia-filter');
    const gradoFilter    = document.getElementById('grado-filter');

    // El módulo se muestra dentro de comun/modulo.php. Esta marca evita que
    // las llamadas AJAX sean redirigidas de nuevo al contenedor en .htaccess.
    function apiUrl(params) {
        const query = new URLSearchParams({ ...params, guagua_embedded: '1' });
        return `?${query.toString()}`;
    }

    // ===================== MATERIAS SIN PLANEACIÓN =====================
    const panelTabs        = document.querySelectorAll('.module-tab');
    const planningPanel    = document.getElementById('planning-panel');
    const missingPanel     = document.getElementById('missing-panel');
    const missingMonth     = document.getElementById('missing-month');
    const loadMissingBtn   = document.getElementById('load-missing-btn');
    const missingSummary   = document.getElementById('missing-summary');
    const missingResults   = document.getElementById('missing-results');
    const missingLoading   = document.getElementById('missing-loading');
    const missingEmpty     = document.getElementById('missing-empty');
    const missingError     = document.getElementById('missing-error');

    const hoy = new Date();
    missingMonth.value = `${anoConfigurado}-${String(hoy.getMonth() + 1).padStart(2, '0')}`;

    function nombreMes(mes) {
        return new Date(`${mes}-01T12:00:00`).toLocaleDateString('es-CO', {
            month: 'long', year: 'numeric'
        });
    }

    function activarPanel(panelId) {
        const mostrarFaltantes = panelId === 'missing-panel';
        planningPanel.hidden = mostrarFaltantes;
        missingPanel.hidden = !mostrarFaltantes;
        panelTabs.forEach(tab => {
            const activa = tab.dataset.panel === panelId;
            tab.classList.toggle('active', activa);
            tab.setAttribute('aria-selected', String(activa));
        });
        if (mostrarFaltantes) cargarMateriasSinPlaneacion();
    }

    async function cargarMateriasSinPlaneacion() {
        if (!missingMonth.value) return;
        missingResults.innerHTML = '';
        missingSummary.textContent = '';
        missingEmpty.style.display = 'none';
        missingError.style.display = 'none';
        missingLoading.style.display = 'block';
        loadMissingBtn.disabled = true;

        try {
            const res = await fetch(apiUrl({
                action: 'materias_sin_planeacion',
                month: missingMonth.value,
            }));
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            const materias = Array.isArray(json.data) ? json.data : [];
            const mesLegible = nombreMes(json.month || missingMonth.value);
            missingSummary.textContent = `${json.total || 0} materia(s) o grado(s) sin planeación en ${mesLegible}.`;

            if (materias.length === 0) {
                missingEmpty.style.display = 'block';
                return;
            }

            materias.forEach(item => {
                const li = document.createElement('li');
                li.className = 'missing-item';
                const materia = document.createElement('strong');
                materia.textContent = item.materia;
                const grado = document.createElement('span');
                grado.textContent = `Grado: ${item.grado}`;
                li.append(materia, grado);
                missingResults.appendChild(li);
            });
        } catch (error) {
            console.error('Error consultando materias sin planeación:', error);
            missingError.style.display = 'block';
        } finally {
            missingLoading.style.display = 'none';
            loadMissingBtn.disabled = false;
        }
    }

    panelTabs.forEach(tab => tab.addEventListener('click', () => activarPanel(tab.dataset.panel)));
    loadMissingBtn.addEventListener('click', cargarMateriasSinPlaneacion);
    missingMonth.addEventListener('change', cargarMateriasSinPlaneacion);

    async function loadFilters() {
        try {
            const res  = await fetch(apiUrl({ action: 'obtener_filtros' }));
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            data.materias.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m; opt.textContent = m;
                materiaFilter.appendChild(opt);
            });
            data.grados.forEach(g => {
                const opt = document.createElement('option');
                opt.value = g; opt.textContent = `Grado ${g}`;
                gradoFilter.appendChild(opt);
            });
        } catch (e) {
            console.warn('No se pudieron cargar los filtros:', e);
        }
    }

    // ===================== BÚSQUEDA =====================
    const qInput           = document.getElementById('q-input');
    const qClearBtn        = document.getElementById('q-clear-btn');
    const startDateFilter  = document.getElementById('start-date-filter');
    const endDateFilter    = document.getElementById('end-date-filter');
    const createdDateSearchFilter = document.getElementById('created-date-search-filter');
    const createdDateFilter = document.getElementById('created-date-filter');
    const bulkDownloadBtn   = document.getElementById('bulk-download-btn');
    const bulkExportStatus  = document.getElementById('bulk-export-status');
    const limitFilter      = document.getElementById('limit-filter');
    const searchResults    = document.getElementById('search-results');
    const noResultsMsg     = document.getElementById('no-results-message');
    const paginationCtrl   = document.getElementById('pagination-controls');
    const loadingSpinner   = document.getElementById('loading-spinner');

    let isLoading = false;
    let debounceTimer = null;
    let templateContentPromise = null;

    const DEFAULT_OUTPUT_PATH = 'G:\\Mi unidad\\Josefina\\Vallesol2026\\Planeaciones';
    const MONTHS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    function formatDateForDoc(value) {
        const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value || ''));
        return match ? `${match[3]}/${match[2]}/${match[1]}` : '';
    }

    function getMonthName(value) {
        const match = /^\d{4}-(\d{2})/.exec(String(value || ''));
        return match ? MONTHS[Number(match[1]) - 1] || '' : '';
    }

    function safeFilename(value) {
        return String(value || 'Vacio').normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/gi, '_').replace(/^_+|_+$/g, '') || 'Vacio';
    }

    function documentFilename(plan) {
        return `planeador_vallesol_${safeFilename(getMonthName(plan.fecha_inicio))}_${safeFilename(plan.grado)}_${safeFilename(plan.materia_nombre || plan.materia)}_${plan.id_plan}.docx`;
    }

    function sanitizeXml(value) {
        return String(value ?? '').replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g, '')
            .replace(/\uFFFE|\uFFFF/g, '').replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim();
    }

    function getDocumentData(plan) {
        const materia = plan.materia_nombre || plan.materia || '';
        const estrategia = plan.estrategia ?? plan.estrategias ?? '';
        const momentos = plan.momentos ?? plan.observaciones ?? '';
        const ejes = plan.ejes_tematicos ?? plan.eje_tematico ?? '';
        const data = {
            mes: getMonthName(plan.fecha_inicio),
            periodo: plan.periodo || '',
            docente: <?php echo json_encode((string)($_SESSION['nombre_usu'] ?? ''), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
            grado: plan.grado || '',
            materia,
            materia_id: plan.materia_id || plan.materia || '',
            materia_nombre: materia,
            nombre_materia: materia,
            tiempo: plan.tiempo || plan.tiempo_plan || '',
            tiempo_plan: plan.tiempo_plan || plan.tiempo || '',
            fecha_inicio: formatDateForDoc(plan.fecha_inicio),
            fecha_fin: formatDateForDoc(plan.fecha_fin),
            dba: plan.dba || '',
            ejes_tematicos: ejes,
            eje_tematico: ejes,
            observaciones: momentos,
            reflexion: plan.reflexion || '',
            objetivo: plan.objetivo || '',
            estrategia,
            estrategias: estrategia,
            momentos,
            evaluacion: plan.evaluacion || '',
            tema: '',
            objetivos: plan.objetivo || '',
            estrategias_educativas: estrategia,
            estrategia_educativa: estrategia,
            estrategia_metodologica: estrategia,
            ejes,
            reflexion_pedagogica: plan.reflexion || '',
            observaciones_adicionales: momentos,
            momentos_clase: momentos,
        };
        return Object.fromEntries(Object.entries(data).map(([key, value]) => [key, sanitizeXml(value)]));
    }

    async function getTemplateContent() {
        if (!templateContentPromise) {
            const templateMode = localStorage.getItem('planmindTemplateMode') || 'upload';
            const configuredPath = localStorage.getItem('planmindTemplatePath') || '';
            const url = templateMode === 'path' && configuredPath
                ? `../apps/PlanMind/load_template.php?path=${encodeURIComponent(configuredPath)}`
                : 'plantilla2026/PLANEADOR2026.docx';
            templateContentPromise = fetch(url, { cache: 'no-store' }).then(async response => {
                if (!response.ok) {
                    const error = await response.json().catch(() => null);
                    throw new Error(error?.message || 'No se pudo cargar la plantilla Word.');
                }
                return response.arrayBuffer();
            });
        }
        return templateContentPromise;
    }

    async function makeDocx(plan) {
        if (typeof window.PizZip === 'undefined' || typeof window.docxtemplater === 'undefined') {
            throw new Error('No cargaron las librerías necesarias para crear el Word.');
        }
        const zip = new window.PizZip(await getTemplateContent());
        const documentTemplate = new window.docxtemplater(zip, {
            paragraphLoop: true,
            linebreaks: true,
            nullGetter: () => '',
        });
        documentTemplate.render(getDocumentData(plan));
        return documentTemplate.getZip().generate({
            type: 'blob',
            mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        });
    }

    async function getPlanForExport(idPlan) {
        const response = await fetch(`../apps/PlanMind/index.php?action=get&id=${encodeURIComponent(idPlan)}`, { cache: 'no-store' });
        const result = await response.json().catch(() => null);
        if (!response.ok || !result?.ok || !result.plan) {
            throw new Error(result?.message || 'No fue posible obtener la planeación.');
        }
        return result.plan;
    }

    async function saveDocxToConfiguredPath(blob, filename) {
        const directory = localStorage.getItem('planmindOutputPath') || DEFAULT_OUTPUT_PATH;
        const contentBase64 = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(String(reader.result).split(',')[1] || '');
            reader.onerror = () => reject(reader.error || new Error('No se pudo preparar el archivo Word.'));
            reader.readAsDataURL(blob);
        });
        const response = await fetch('../apps/PlanMind/save_docx.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ directory, filename, contentBase64 }),
        });
        const result = await response.json().catch(() => null);
        if (!response.ok || !result?.ok) {
            throw new Error(result?.message || 'No fue posible guardar el archivo en la ruta configurada.');
        }
        return result.path;
    }

    async function exportOnePlan(idPlan, mode, button) {
        const previous = button.textContent;
        button.disabled = true;
        button.textContent = mode === 'save' ? 'Guardando…' : 'Generando…';
        try {
            const plan = await getPlanForExport(idPlan);
            const filename = documentFilename(plan);
            const docx = await makeDocx(plan);
            if (mode === 'save') {
                const savedPath = await saveDocxToConfiguredPath(docx, filename);
                button.textContent = 'Guardado ✓';
                button.title = savedPath;
            } else {
                window.saveAs(docx, filename);
                button.textContent = 'Descargado ✓';
            }
        } catch (error) {
            console.error('Error exportando planeación:', error);
            alert(`No se pudo exportar la planeación: ${error.message}`);
            button.textContent = previous;
        } finally {
            button.disabled = false;
            setTimeout(() => { if (button.textContent !== previous) button.textContent = previous; }, 2500);
        }
    }

    async function fetchResults(page = 1) {
        if (isLoading) return;
        isLoading = true;
        searchResults.innerHTML = '';
        noResultsMsg.style.display = 'none';
        paginationCtrl.innerHTML  = '';
        loadingSpinner.style.display = 'block';

        const params = new URLSearchParams({
            action:    'buscar_planeaciones',
            page:       page,
            limit:      limitFilter.value,
            q:          qInput.value.trim(),
            materia:    materiaFilter.value,
            grado:      gradoFilter.value,
            startDate:  startDateFilter.value,
            endDate:    endDateFilter.value,
            createdDate: createdDateSearchFilter.value,
        });

        try {
            const res  = await fetch(apiUrl(Object.fromEntries(params)));
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            const planes = json.data || [];
            const total  = json.total || 0;

            if (planes.length > 0) {
                planes.forEach(plan => {
                    const li = document.createElement('li');
                    const obj = plan.objetivo ? plan.objetivo.replace(/<[^>]*>/g, '').trim() : '';
                    const eje = plan.eje_tematico ? plan.eje_tematico.replace(/<[^>]*>/g, '').trim() : '';
                    li.innerHTML = `
                        <div class="result-item">
                            <strong>${escapeHtml(plan.title)}</strong>
                            <div class="result-meta">
                                Grado: <b>${escapeHtml(plan.grado)}</b> &nbsp;|&nbsp;
                                ${escapeHtml(plan.start)} al ${escapeHtml(plan.end)} &nbsp;|&nbsp;
                                Creada: ${escapeHtml(plan.fecha_creacion || 'Sin dato')}
                            </div>
                            ${obj ? `<div class="result-objetivo">${escapeHtml(obj)}</div>` : ''}
                            ${eje ? `<div class="result-objetivo" style="color:#718096;font-style:italic">${escapeHtml(eje)}</div>` : ''}
                            <div class="result-actions">
                                <a href="../apps/PlanMind/index.php?id=${encodeURIComponent(plan.id_plan)}" target="_blank" class="result-action">Abrir</a>
                                <button type="button" class="result-action download" data-export-plan="${Number(plan.id_plan)}" data-export-mode="download">Descargar Word</button>
                                <button type="button" class="result-action save" data-export-plan="${Number(plan.id_plan)}" data-export-mode="save">Guardar en ruta</button>
                            </div>
                        </div>`;
                    searchResults.appendChild(li);
                });
                renderPagination(total, page);
            } else {
                noResultsMsg.style.display = 'block';
            }
        } catch (err) {
            console.error('Error buscando planeaciones:', err);
            noResultsMsg.textContent = 'Ocurrió un error al cargar los resultados.';
            noResultsMsg.style.display = 'block';
        } finally {
            isLoading = false;
            loadingSpinner.style.display = 'none';
        }
    }

    function renderPagination(total, currentPage) {
        const limit      = parseInt(limitFilter.value, 10);
        const totalPages = Math.ceil(total / limit);
        if (totalPages <= 1) return;

        const info = document.createElement('span');
        info.className   = 'page-info';
        info.textContent = `${total} resultados`;
        paginationCtrl.appendChild(info);

        const prev = document.createElement('a');
        prev.href = '#'; prev.className = 'page-link' + (currentPage === 1 ? ' disabled' : '');
        prev.textContent = '«'; prev.dataset.page = currentPage - 1;
        paginationCtrl.appendChild(prev);

        // Ventana de páginas
        const delta = 2;
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - delta && i <= currentPage + delta)) {
                const a = document.createElement('a');
                a.href = '#'; a.className = 'page-link' + (i === currentPage ? ' active' : '');
                a.textContent = i; a.dataset.page = i;
                paginationCtrl.appendChild(a);
            } else if (i === currentPage - delta - 1 || i === currentPage + delta + 1) {
                const sp = document.createElement('span');
                sp.className = 'page-info'; sp.textContent = '…';
                paginationCtrl.appendChild(sp);
            }
        }

        const next = document.createElement('a');
        next.href = '#'; next.className = 'page-link' + (currentPage === totalPages ? ' disabled' : '');
        next.textContent = '»'; next.dataset.page = currentPage + 1;
        paginationCtrl.appendChild(next);
    }

    paginationCtrl.addEventListener('click', e => {
        e.preventDefault();
        const link = e.target.closest('.page-link');
        if (link && !link.classList.contains('disabled') && !link.classList.contains('active')) {
            fetchResults(parseInt(link.dataset.page, 10));
        }
    });

    // Debounce en la caja de texto
    qInput.addEventListener('input', () => {
        qClearBtn.style.display = qInput.value ? 'block' : 'none';
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchResults(1), 350);
    });
    qClearBtn.addEventListener('click', () => {
        qInput.value = '';
        qClearBtn.style.display = 'none';
        fetchResults(1);
    });

    [materiaFilter, gradoFilter, startDateFilter, endDateFilter, createdDateSearchFilter, limitFilter].forEach(el => {
        el.addEventListener('change', () => fetchResults(1));
    });

    searchResults.addEventListener('click', event => {
        const button = event.target.closest('[data-export-plan]');
        if (!button) return;
        exportOnePlan(Number(button.dataset.exportPlan), button.dataset.exportMode, button);
    });

    createdDateFilter.addEventListener('change', () => {
        if (createdDateFilter.value) createdDateSearchFilter.value = createdDateFilter.value;
    });

    bulkDownloadBtn.addEventListener('click', async () => {
        const fechaCreacion = createdDateFilter.value || createdDateSearchFilter.value;
        if (!fechaCreacion) {
            bulkExportStatus.textContent = 'Selecciona la fecha de creación que deseas exportar.';
            createdDateFilter.focus();
            return;
        }
        bulkDownloadBtn.disabled = true;
        bulkExportStatus.textContent = 'Consultando planeaciones…';
        try {
            const response = await fetch(apiUrl({ action: 'planeaciones_por_fecha_creacion', fechaCreacion }));
            const result = await response.json().catch(() => null);
            const plans = result?.data || [];
            if (!response.ok) throw new Error(result?.error || 'No fue posible consultar las planeaciones.');
            if (!plans.length) {
                bulkExportStatus.textContent = 'No hay planeaciones creadas en la fecha seleccionada.';
                return;
            }
            const archive = new window.PizZip();
            for (let index = 0; index < plans.length; index++) {
                bulkExportStatus.textContent = `Generando ${index + 1} de ${plans.length} planeaciones…`;
                const docx = await makeDocx(plans[index]);
                archive.file(documentFilename(plans[index]), await docx.arrayBuffer());
            }
            const archiveBlob = archive.generate({ type: 'blob', compression: 'DEFLATE' });
            window.saveAs(archiveBlob, `planeaciones_${fechaCreacion}.zip`);
            bulkExportStatus.textContent = `${plans.length} planeación(es) descargada(s) en un archivo ZIP.`;
        } catch (error) {
            console.error('Error exportando planeaciones por fecha:', error);
            bulkExportStatus.textContent = `No se pudieron descargar las planeaciones: ${error.message}`;
        } finally {
            bulkDownloadBtn.disabled = false;
        }
    });

    // ===================== INICIO =====================
    renderCalendar();
    renderLegend();
    loadFilters();        // carga materias y grados desde la BD de forma asíncrona
    fetchResults(1);      // carga inicial de planeaciones
});
</script>
</body>
</html>
<?php
// ob_get_clean() + plantilla si se necesita en el futuro
?>
