<?php
require_once __DIR__ . '/../comun/persona_imagen.php';
require_once __DIR__ . '/../comun/config.php';
require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/lib/hoja_vida_periodos.php';

function hv_escape($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function hv_mostrar($valor, $vacio = 'Sin registrar') {
    $valor = trim((string)$valor);
    return $valor === '' || $valor === '0000-00-00' ? '<span class="empty-value">' . hv_escape($vacio) . '</span>' : hv_escape($valor);
}

function hv_consultar($mysqli, $sql, $tipos = '', $parametros = []) {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) return [];

    if ($tipos !== '') {
        $argumentos = [$tipos];
        foreach ($parametros as $indice => $valor) {
            $argumentos[] = &$parametros[$indice];
        }
        call_user_func_array([$stmt, 'bind_param'], $argumentos);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $resultado = $stmt->get_result();
    $filas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $filas;
}

function hv_una_fila($mysqli, $sql, $tipos = '', $parametros = []) {
    $filas = hv_consultar($mysqli, $sql, $tipos, $parametros);
    return $filas[0] ?? null;
}

function hv_tabla_existe($mysqli, $tabla) {
    static $cache = [];
    if (array_key_exists($tabla, $cache)) return $cache[$tabla];
    $tablaSegura = $mysqli->real_escape_string($tabla);
    $resultado = $mysqli->query("SHOW TABLES LIKE '{$tablaSegura}'");
    $cache[$tabla] = $resultado && $resultado->num_rows > 0;
    return $cache[$tabla];
}

function hv_clave_materia($materia) {
    $materia = trim((string)$materia);
    $materia = strtr($materia, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n'
    ]);
    $materia = function_exists('mb_strtolower') ? mb_strtolower($materia, 'UTF-8') : strtolower($materia);
    $materia = preg_replace('/[^a-z0-9]+/', '', $materia);
    if (strpos($materia, 'matemat') !== false) return 'matematicas';
    if (strpos($materia, 'tecnolog') !== false) return 'tecnologia';
    if (strpos($materia, 'geometr') !== false) return 'geometria';
    if (strpos($materia, 'educacionfisica') !== false) return 'educacionfisica';
    if (strpos($materia, 'emprend') !== false) return 'emprendimiento';
    if (strpos($materia, 'urbanidad') !== false) return 'urbanidad';
    if (strpos($materia, 'fisica') !== false) return 'fisica';
    if (strpos($materia, 'econom') !== false) return 'economiapolitica';
    return $materia;
}

function hv_es_presente($valor) {
    $valor = strtoupper(trim((string)$valor));
    return in_array($valor, ['SI', 'SÍ', 'P', 'PRESENTE', 'ASISTIO', 'ASISTIÓ'], true);
}

function hv_es_ausencia($valor) {
    return strtoupper(trim((string)$valor)) === 'NO';
}

function hv_nivel_nota($nota) {
    if ($nota === null || $nota === '') return ['texto' => 'Sin nota', 'clase' => 'neutral'];
    $nota = (float)$nota;
    if ($nota >= 4.6) return ['texto' => 'Superior', 'clase' => 'superior'];
    if ($nota >= 4.0) return ['texto' => 'Alto', 'clase' => 'alto'];
    if ($nota >= 3.0) return ['texto' => 'Básico', 'clase' => 'basico'];
    return ['texto' => 'Bajo', 'clase' => 'bajo'];
}

function hv_foto_url($foto, $genero = '') {
    return rtrim(READFILE_URL, '/') . '/foto/' . rawurlencode(guagua_persona_foto($foto, $genero));
}

function hv_edad($fecha) {
    if (!$fecha || $fecha === '0000-00-00') return null;
    try {
        return (new DateTime($fecha))->diff(new DateTime('today'))->y;
    } catch (Throwable $e) {
        return null;
    }
}

function hv_fecha($fecha, $vacio = 'Sin registrar') {
    $fecha = trim((string)$fecha);
    if ($fecha === '' || $fecha === '0000-00-00') return '<span class="empty-value">' . hv_escape($vacio) . '</span>';
    try {
        $formato = strpos($fecha, ':') !== false ? 'd/m/Y H:i' : 'd/m/Y';
        return (new DateTime($fecha))->format($formato);
    } catch (Throwable $e) {
        return hv_mostrar($fecha, $vacio);
    }
}

function hv_error($titulo, $mensaje, $codigo = 400) {
    http_response_code($codigo);
    ?>
    <!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?php echo hv_escape($titulo); ?></title>
    <style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3f6fb;color:#172033;font-family:Inter,Arial,sans-serif}.box{max-width:460px;margin:24px;padding:36px;border-radius:24px;background:#fff;box-shadow:0 24px 60px #263b621f;text-align:center}.icon{font-size:42px}.box h1{margin:14px 0 8px;font-size:24px}.box p{color:#64748b;line-height:1.6}.box a{display:inline-block;margin-top:12px;padding:11px 16px;border-radius:10px;background:#155eef;color:#fff;text-decoration:none;font-weight:700}</style></head><body><main class="box"><div class="icon">⚠️</div><h1><?php echo hv_escape($titulo); ?></h1><p><?php echo hv_escape($mensaje); ?></p><a href="javascript:history.back()">Volver</a></main></body></html>
    <?php
    exit;
}

if (empty($_SESSION['id_usuario'])) {
    header('Location: login.php');
    exit;
}

$idEstudiante = trim((string)($_GET['estudiante'] ?? $_GET['id_estudiante'] ?? ''));
if ($idEstudiante === '') hv_error('Estudiante no seleccionado', 'Selecciona un estudiante para consultar su hoja de vida.');

$idUsuarioActual = (string)$_SESSION['id_usuario'];
$rolesActuales = array_filter(array_map('trim', explode(',', (string)($_SESSION['rol'] ?? ''))));
$rolesActuales = array_map('strtolower', $rolesActuales);
$puedeVerCualquierEstudiante = (bool)array_intersect($rolesActuales, ['admin', 'directivo', 'docente']);
$esMismoEstudiante = $idUsuarioActual === $idEstudiante;
$esAcudienteRelacionado = false;

if (in_array('acudiente', $rolesActuales, true)) {
    $esAcudienteRelacionado = hv_una_fila(
        $mysqli,
        'SELECT id_acudiente_estudiante FROM acudiente_estudiante WHERE id_acudiente = ? AND id_estudiante = ? LIMIT 1',
        'ss',
        [$idUsuarioActual, $idEstudiante]
    ) !== null;
}

if (!$puedeVerCualquierEstudiante && !$esMismoEstudiante && !$esAcudienteRelacionado) {
    hv_error('Acceso no autorizado', 'Solo puedes consultar tu propia hoja de vida o la de un estudiante a tu cargo.', 403);
}

$columnasUsuario = hv_consultar($mysqli, 'SHOW COLUMNS FROM usuario');
$tieneEps = in_array('eps', array_column($columnasUsuario, 'Field'), true);
$campoEps = $tieneEps ? 'eps' : "NULL AS eps";

$estudiante = hv_una_fila(
    $mysqli,
    "SELECT id_usuario, nombre, apellido, foto, direccion, telefono, correo, estado, tipo_sangre, genero,
            observaciones, fecha_nacimiento, fecha_creacion, fecha_retiro, tipo_documento, {$campoEps}
     FROM usuario
     WHERE id_usuario = ? AND rol LIKE '%estudiante%'
     LIMIT 1",
    's',
    [$idEstudiante]
);
if (!$estudiante) hv_error('Estudiante no encontrado', 'No existe un perfil de estudiante asociado a este documento.', 404);

$anoActual = hv_una_fila(
    $mysqli,
    "SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo WHERE LOWER(TRIM(estado)) = 'activo' ORDER BY id_ano_lectivo DESC LIMIT 1"
);
$periodos = hv_consultar($mysqli,
    "SELECT id_periodo, nombre_periodo, descripcion_periodo, fecha_inicio, fecha_fin, ano_lectivo, estado_periodo
     FROM periodo WHERE fecha_inicio > '0000-00-00' AND fecha_fin >= fecha_inicio
     ORDER BY fecha_inicio DESC, estado_periodo DESC, id_periodo DESC");
$periodoActual = null;
$periodoSolicitado = $_GET['periodo'] ?? null;
if ($periodoSolicitado !== null && !is_string($periodoSolicitado)) hv_error('Periodo inválido', 'Seleccione un periodo configurado.');
if ($periodoSolicitado !== null && $periodoSolicitado !== 'todos') {
    foreach ($periodos as $periodo) {
        if ((string)$periodo['id_periodo'] === $periodoSolicitado) { $periodoActual = $periodo; break; }
    }
    if (!$periodoActual) hv_error('Periodo no encontrado', 'El periodo solicitado no está configurado.', 400);
} elseif ($periodoSolicitado === null) {
    require_once __DIR__ . '/../comun/academico.php';
    $periodoConfigurado = guagua_periodo_activo($mysqli);
    foreach ($periodos as $periodo) {
        if ($periodoConfigurado && (string)$periodo['id_periodo'] === (string)$periodoConfigurado['id_periodo']) { $periodoActual = $periodo; break; }
    }
}
if ($periodoActual) {
    $anoPeriodo = hv_una_fila($mysqli,
        'SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo WHERE nombre_ano_lectivo = ? OR id_ano_lectivo = ? ORDER BY (nombre_ano_lectivo = ?) DESC LIMIT 1',
        'sis', [(string)$periodoActual['ano_lectivo'], (int)$periodoActual['ano_lectivo'], (string)$periodoActual['ano_lectivo']]);
    if (!$anoPeriodo) hv_error('Año no configurado', 'El periodo no está asociado a un año lectivo válido.');
    $anoActual = $anoPeriodo;
}
$fechaInicio = $periodoActual['fecha_inicio'] ?? '';
$fechaFin = $periodoActual['fecha_fin'] ?? '';
if (!$periodoActual && preg_match('/^\d{4}$/D', (string)($anoActual['nombre_ano_lectivo'] ?? ''))) {
    $fechaInicio = $anoActual['nombre_ano_lectivo'] . '-01-01';
    $fechaFin = $anoActual['nombre_ano_lectivo'] . '-12-31';
}
$etiquetaPeriodo = $periodoActual ? 'Periodo ' . $periodoActual['nombre_periodo'] . ' · ' . $periodoActual['ano_lectivo'] : 'Todo el año lectivo';
$periodoSeleccionado = $periodoActual ? (string)$periodoActual['id_periodo'] : 'todos';
$mensajeEps = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_eps'])) {
    if (!$puedeVerCualquierEstudiante || !$tieneEps || !guagua_validar_csrf()) hv_error('Acceso no autorizado', 'No se pudo autorizar la actualización de EPS.', 403);
    $eps = $_POST['eps'] ?? '';
    if (!is_string($eps) || mb_strlen($eps, 'UTF-8') > 150) hv_error('EPS inválida', 'La EPS admite hasta 150 caracteres.');
    $eps = trim($eps);
    $stmt = $mysqli->prepare('UPDATE usuario SET eps = ? WHERE id_usuario = ?');
    $stmt->bind_param('ss', $eps, $idEstudiante);
    if (!$stmt->execute()) hv_error('No se pudo guardar', 'Intente nuevamente la actualización de EPS.', 500);
    $stmt->close();
    $estudiante['eps'] = $eps;
    $mensajeEps = 'EPS actualizada.';
}

$sqlMaterias = "SELECT MAX(i.id_inscripcion) AS id_inscripcion, MAX(i.estado_inscripcion) AS estado_inscripcion,
                        MAX(i.fecha_inscripcion) AS fecha_inscripcion, MAX(a.id_asignacion) AS id_asignacion,
                        GROUP_CONCAT(DISTINCT a.id_asignacion) AS ids_asignacion, MAX(a.id_asignatura) AS id_materia,
                        MAX(a.ano_lectivo) AS ano_lectivo, MAX(al.nombre_ano_lectivo) AS ano_nombre,
                        MAX(mo.nombre_materia) AS nombre_materia, MAX(cc.nombre_categoria_curso) AS grado,
                        MAX(CONCAT(TRIM(COALESCE(doc.nombre, '')), ' ', TRIM(COALESCE(doc.apellido, '')))) AS docente
                 FROM inscripcion i
                 INNER JOIN asignacion a ON a.id_asignacion = i.id_asignacion
                 LEFT JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
                 LEFT JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
                 LEFT JOIN ano_lectivo al ON al.id_ano_lectivo = a.ano_lectivo
                 LEFT JOIN usuario doc ON doc.id_usuario = a.id_docente
                 WHERE i.id_estudiante = ?
                   AND LOWER(TRIM(COALESCE(a.visible, 'si'))) = 'si'
                   AND LOWER(TRIM(COALESCE(i.estado_inscripcion, 'en curso'))) NOT IN ('retirado', 'inactivo', 'cancelado')";
$tiposMaterias = 's';
$parametrosMaterias = [$idEstudiante];
if ($anoActual) {
    $sqlMaterias .= ' AND a.ano_lectivo = ?';
    $tiposMaterias .= 'i';
    $parametrosMaterias[] = (int)$anoActual['id_ano_lectivo'];
}
$sqlMaterias .= ' GROUP BY a.id_asignatura ORDER BY nombre_materia ASC';
$materias = hv_consultar($mysqli, $sqlMaterias, $tiposMaterias, $parametrosMaterias);

$actividadesPorAsignacion = [];
if ($materias) {
    $idsAsignacion = [];
    foreach ($materias as $materia) $idsAsignacion = array_merge($idsAsignacion, array_map('intval', explode(',', $materia['ids_asignacion'])));
    $idsAsignacion = array_values(array_unique($idsAsignacion));
    $marcas = implode(',', array_fill(0, count($idsAsignacion), '?'));
    $sqlActividades = "SELECT a.id_actividad, a.id_asignacion, a.nombre_actividad, a.fecha_entrega, a.fecha_publicacion,
            s.id_seguimiento, s.valoracion, s.fechayhora_valoracion
        FROM actividad a
        INNER JOIN inscripcion i ON i.id_asignacion = a.id_asignacion AND i.id_estudiante = ?
        LEFT JOIN seguimiento_es s ON s.id_inscripcion = i.id_inscripcion AND s.id_actividad = a.id_actividad
            AND s.id_seguimiento = (SELECT MAX(s2.id_seguimiento) FROM seguimiento_es s2
                WHERE s2.id_inscripcion = i.id_inscripcion AND s2.id_actividad = a.id_actividad)
        WHERE a.id_asignacion IN ({$marcas}) AND UPPER(TRIM(a.evaluable)) = 'SI'
            AND UPPER(TRIM(a.visible)) = 'SI'
        ORDER BY a.fecha_entrega, a.fecha_publicacion, a.id_actividad, s.id_seguimiento DESC";
    $actividadesUnicas = [];
    foreach (hv_consultar($mysqli, $sqlActividades, 's' . str_repeat('i', count($idsAsignacion)), array_merge([$idEstudiante], $idsAsignacion)) as $actividad) {
        $fecha = hv_fecha_actividad($actividad);
        if (($fechaInicio || $fechaFin) && !hv_fecha_en_rango($fecha, $fechaInicio, $fechaFin)) continue;
        $id = (string)$actividad['id_actividad'];
        if (!isset($actividadesUnicas[$id]) || (int)$actividad['id_seguimiento'] > (int)$actividadesUnicas[$id]['id_seguimiento']) {
            $actividad['fecha_periodo'] = $fecha;
            $actividadesUnicas[$id] = $actividad;
        }
    }
    foreach ($actividadesUnicas as $actividad) $actividadesPorAsignacion[(string)$actividad['id_asignacion']][] = $actividad;
}

$notasAuditoria = [];
if (hv_tabla_existe($mysqli, 'notas_auditoria')) {
    $sqlNotas = 'SELECT id_asignacion, id_materia, materia, nota, fecha_nota, id_nota_auditoria FROM notas_auditoria WHERE id_estudiante = ?';
    $tiposNotas = 's'; $parametrosNotas = [$idEstudiante];
    if ($fechaInicio && $fechaFin) {
        $sqlNotas .= ' AND fecha_nota >= ? AND fecha_nota < DATE_ADD(?, INTERVAL 1 DAY)';
        $tiposNotas .= 'ss'; $parametrosNotas[] = $fechaInicio; $parametrosNotas[] = $fechaFin;
    }
    $sqlNotas .= ' ORDER BY fecha_nota DESC, id_nota_auditoria DESC';
    foreach (hv_consultar($mysqli, $sqlNotas, $tiposNotas, $parametrosNotas) as $fila) {
        if (hv_nota_numerica($fila['nota']) === null) continue;
        $clave = (int)$fila['id_asignacion'] > 0 ? 'asignacion:' . $fila['id_asignacion'] : ((int)$fila['id_materia'] > 0 ? 'oficial:' . $fila['id_materia'] : 'materia:' . hv_clave_materia($fila['materia']));
        if (!isset($notasAuditoria[$clave])) $notasAuditoria[$clave] = $fila;
    }
}

$asistenciaPorMateria = [];
$filasAsistencia = [];
if (hv_tabla_existe($mysqli, 'asistencias')) {
    $fechaAsistenciaSql = hv_fecha_asistencia_sql();
    $sqlAsistencia = "SELECT materia, asistencias, {$fechaAsistenciaSql} AS fechas_clase, justificacion, fecha_actualizacion
         FROM asistencias WHERE REPLACE(REPLACE(REPLACE(TRIM(documento), ' ', ''), CHAR(10), ''), CHAR(13), '') = ?";
    $tiposAsistencia = 's'; $parametrosAsistencia = [preg_replace('/\s+/', '', $idEstudiante)];
    if ($fechaInicio && $fechaFin) {
        $sqlAsistencia .= " AND ({$fechaAsistenciaSql}) >= ? AND ({$fechaAsistenciaSql}) < DATE_ADD(?, INTERVAL 1 DAY)";
        $tiposAsistencia .= 'ss'; $parametrosAsistencia[] = $fechaInicio; $parametrosAsistencia[] = $fechaFin;
    }
    $sqlAsistencia .= ' ORDER BY fechas_clase DESC, fecha_actualizacion DESC';
    $filasAsistencia = hv_consultar($mysqli, $sqlAsistencia, $tiposAsistencia, $parametrosAsistencia);
    foreach ($filasAsistencia as $fila) {
        $clave = hv_clave_materia($fila['materia']);
        if (!isset($asistenciaPorMateria[$clave])) $asistenciaPorMateria[$clave] = ['total' => 0, 'presentes' => 0, 'ausencias' => 0, 'ultima_fecha' => '', 'justificaciones' => 0];
        if (hv_es_presente($fila['asistencias']) || hv_es_ausencia($fila['asistencias'])) {
            $asistenciaPorMateria[$clave]['total']++;
            if (hv_es_presente($fila['asistencias'])) $asistenciaPorMateria[$clave]['presentes']++;
            if (hv_es_ausencia($fila['asistencias'])) $asistenciaPorMateria[$clave]['ausencias']++;
        }
        if (trim((string)$fila['justificacion']) !== '' && trim((string)$fila['justificacion']) !== '0') $asistenciaPorMateria[$clave]['justificaciones']++;
        if ($asistenciaPorMateria[$clave]['ultima_fecha'] === '') $asistenciaPorMateria[$clave]['ultima_fecha'] = $fila['fechas_clase'];
    }
}

$totalAsistencia = 0;
$presentes = 0;
$ausencias = 0;
$notasValidas = [];
$materiasEnRiesgo = 0;
$actividadesValoradas = 0;

foreach ($materias as &$materia) {
    $clave = hv_clave_materia($materia['nombre_materia']);
    $nota = null; $fechaNota = '';
    $materia['actividades'] = [];
    $notaOficial = null;
    foreach (explode(',', $materia['ids_asignacion']) as $idAsignacion) {
        $materia['actividades'] = array_merge($materia['actividades'], $actividadesPorAsignacion[(string)$idAsignacion] ?? []);
        $candidata = $notasAuditoria['asignacion:' . $idAsignacion] ?? null;
        if ($candidata && (!$notaOficial || $candidata['fecha_nota'] > $notaOficial['fecha_nota'])) $notaOficial = $candidata;
    }
    $notaOficial = $notaOficial ?? ($notasAuditoria['oficial:' . $materia['id_materia']] ?? ($notasAuditoria['materia:' . $clave] ?? null));
    $seguimiento = hv_resumir_actividades($materia['actividades']);
    $materia['nota_oficial'] = $notaOficial ? hv_nota_numerica($notaOficial['nota']) : null;
    $materia['fuente_nota'] = 'Sin nota';
    if ($seguimiento['nota_seguimiento'] !== null) {
        $nota = $seguimiento['nota_seguimiento'];
        $materia['fuente_nota'] = 'Promedio de actividades';
    } elseif ($materia['nota_oficial'] !== null) {
        $nota = $materia['nota_oficial'];
        $fechaNota = $notaOficial['fecha_nota'];
        $materia['fuente_nota'] = 'Nota consolidada (sin actividades valoradas)';
    }
    $materia['nota'] = $nota;
    $materia['fecha_nota'] = $fechaNota;
    $materia['nivel'] = hv_nivel_nota($nota);
    $materia['actividades_valoradas'] = (int)($seguimiento['actividades_valoradas'] ?? 0);
    $materia['asistencia'] = $asistenciaPorMateria[$clave] ?? ['total' => 0, 'presentes' => 0, 'ausencias' => 0, 'ultima_fecha' => '', 'justificaciones' => 0];
    $materia['porcentaje_asistencia'] = $materia['asistencia']['total'] > 0
        ? round(($materia['asistencia']['presentes'] / $materia['asistencia']['total']) * 100)
        : null;

    if ($nota !== null) {
        $notasValidas[] = $nota;
        if ($nota < 3) $materiasEnRiesgo++;
    }
    $actividadesValoradas += $materia['actividades_valoradas'];
}
unset($materia);

$totalAsistencia = array_sum(array_column($asistenciaPorMateria, 'total'));
$presentes = array_sum(array_column($asistenciaPorMateria, 'presentes'));
$ausencias = array_sum(array_column($asistenciaPorMateria, 'ausencias'));
$promedioGeneral = $notasValidas ? round(array_sum($notasValidas) / count($notasValidas), 2) : null;
$nivelGeneral = hv_nivel_nota($promedioGeneral);
$porcentajeAsistencia = $totalAsistencia > 0 ? round(($presentes / $totalAsistencia) * 100) : null;
$edad = hv_edad($estudiante['fecha_nacimiento']);
$acudientes = hv_consultar(
    $mysqli,
    "SELECT u.id_usuario, u.nombre, u.apellido, u.telefono, u.correo, u.direccion, u.estado,
            u.fecha_creacion, u.fecha_retiro, u.tipo_documento, ae.parentesco
     FROM acudiente_estudiante ae
     INNER JOIN usuario u ON u.id_usuario = ae.id_acudiente
     WHERE ae.id_estudiante = ?
     ORDER BY FIELD(LOWER(ae.parentesco), 'madre', 'padre'), u.nombre, u.apellido",
    's',
    [$idEstudiante]
);

$nombreCompleto = trim($estudiante['nombre'] . ' ' . $estudiante['apellido']);
$gradoPerfil = trim((string)$estudiante['observaciones']);
$gradoActual = $materias[0]['grado'] ?? (preg_match('/^\d{1,2}[A-Za-z-]*$/', $gradoPerfil) ? $gradoPerfil : 'Sin grado asignado');
$anoTexto = $materias[0]['ano_nombre'] ?? ($anoActual['nombre_ano_lectivo'] ?? ($materias[0]['ano_lectivo'] ?? 'Sin año lectivo'));
$estadoActivo = strtolower(trim((string)$estudiante['estado'])) === 'activo';
$estadoTexto = trim((string)$estudiante['estado']) !== '' ? ucfirst(trim((string)$estudiante['estado'])) : 'Sin registrar';
$asistenciaColor = $porcentajeAsistencia === null ? '#94a3b8' : ($porcentajeAsistencia >= 90 ? '#16a34a' : ($porcentajeAsistencia >= 80 ? '#f59e0b' : '#dc2626'));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hoja de vida · <?php echo hv_escape($nombreCompleto); ?></title>
    <link rel="stylesheet" href="/guagua/comun/vendor/bootstrap-icons/font/bootstrap-icons.min.css">
    <style>
        :root{--navy:#102a43;--ink:#172b4d;--muted:#66758d;--line:#e6ebf2;--bg:#f4f7fb;--card:#fff;--brand:#2563eb;--green:#16a34a;--amber:#d97706;--red:#dc2626}
        *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--ink);font-family:Inter,Arial,sans-serif;font-size:14px}.page{max-width:1240px;margin:0 auto;padding:24px 20px 48px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px}.brand{display:flex;align-items:center;gap:10px;color:var(--navy);font-weight:800;letter-spacing:-.02em}.brand-mark{width:35px;height:35px;display:grid;place-items:center;border-radius:11px;background:linear-gradient(135deg,#2563eb,#0ea5e9);color:#fff;font-size:18px}.back-button,.print-button{border:1px solid var(--line);border-radius:10px;padding:10px 13px;background:#fff;color:#40516c;font:inherit;font-weight:700;cursor:pointer;text-decoration:none;transition:.2s}.back-button:hover,.print-button:hover{border-color:#b8c7dc;color:var(--brand);box-shadow:0 5px 15px #1e3a5f12}.hero{position:relative;overflow:hidden;display:flex;align-items:center;gap:24px;padding:30px;border-radius:24px;background:linear-gradient(120deg,#102a43,#164e8d 58%,#1672bd);color:#fff;box-shadow:0 18px 48px #163f6e2e}.hero:after{content:"";position:absolute;width:330px;height:330px;border:52px solid #ffffff12;border-radius:50%;right:-98px;top:-170px}.student-photo{position:relative;z-index:1;width:118px;height:118px;border:4px solid #ffffffb8;border-radius:22px;object-fit:cover;background:#dbeafe;box-shadow:0 12px 30px #001d3b55}.hero-content{position:relative;z-index:1;min-width:0;flex:1}.eyebrow{display:inline-flex;align-items:center;gap:7px;color:#cce9ff;font-size:11px;font-weight:800;letter-spacing:.11em;text-transform:uppercase}.hero h1{margin:8px 0 11px;font-size:clamp(25px,3vw,36px);letter-spacing:-.045em}.hero-meta{display:flex;gap:9px;flex-wrap:wrap}.hero-meta span{padding:7px 10px;border:1px solid #ffffff33;border-radius:999px;background:#ffffff14;font-size:12px}.status{position:relative;z-index:1;align-self:flex-start;display:inline-flex;gap:7px;align-items:center;padding:8px 10px;border-radius:999px;background:#dcfce7;color:#166534;font-size:12px;font-weight:800}.status.inactive{background:#fee2e2;color:#b91c1c}.metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin:20px 0}.metric{padding:19px;border:1px solid var(--line);border-radius:17px;background:var(--card);box-shadow:0 5px 18px #1a365d08}.metric-top{display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--muted);font-size:12px;font-weight:700}.metric-icon{width:32px;height:32px;display:grid;place-items:center;border-radius:10px;background:#eff6ff;color:var(--brand);font-size:16px}.metric strong{display:block;margin-top:11px;font-size:25px;letter-spacing:-.04em;color:var(--ink)}.metric small{display:block;margin-top:4px;color:var(--muted);font-size:11px}.layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(290px,.85fr);gap:20px}.stack{display:grid;gap:20px}.panel{border:1px solid var(--line);border-radius:19px;background:var(--card);box-shadow:0 7px 22px #1a365d08;overflow:hidden}.panel-header{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:21px 22px 15px;border-bottom:1px solid #eff2f6}.panel-title{margin:0;font-size:16px;letter-spacing:-.025em}.panel-subtitle{margin:5px 0 0;color:var(--muted);font-size:12px}.panel-body{padding:20px 22px}.summary-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.average-box{padding:20px;border-radius:15px;background:linear-gradient(135deg,#eff6ff,#f8fbff);border:1px solid #dbeafe}.average-label{color:#4d6381;font-size:12px;font-weight:700}.average-number{margin:5px 0;font-size:43px;font-weight:800;line-height:1;letter-spacing:-.06em}.grade-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border-radius:999px;font-size:11px;font-weight:800}.grade-badge.superior{background:#dbeafe;color:#1d4ed8}.grade-badge.alto{background:#dcfce7;color:#15803d}.grade-badge.basico{background:#fef3c7;color:#b45309}.grade-badge.bajo{background:#fee2e2;color:#b91c1c}.grade-badge.neutral{background:#eef2f7;color:#64748b}.attendance-overview{display:flex;align-items:center;gap:16px;padding:9px 0}.attendance-ring{--progress:0;--ring-color:#94a3b8;width:90px;height:90px;flex:0 0 90px;display:grid;place-items:center;border-radius:50%;background:conic-gradient(var(--ring-color) calc(var(--progress)*1%),#e9eef5 0)}.attendance-ring:before{content:"";position:absolute;width:70px;height:70px;border-radius:50%;background:#fff}.attendance-ring span{position:relative;font-size:18px;font-weight:800}.attendance-copy strong{display:block;font-size:15px}.attendance-copy p{margin:5px 0;color:var(--muted);font-size:12px;line-height:1.45}.subject-list{display:grid;gap:11px}.subject-card{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:15px;align-items:center;padding:15px;border:1px solid #e9eef4;border-radius:14px;transition:transform .18s,box-shadow .18s}.subject-card:hover{transform:translateY(-2px);box-shadow:0 10px 20px #1a365d0b}.subject-main{min-width:0}.subject-title{display:flex;align-items:center;gap:8px;font-weight:800}.subject-title i{display:grid;place-items:center;width:27px;height:27px;border-radius:8px;background:#eff6ff;color:var(--brand);font-size:13px}.teacher{margin:5px 0 0;padding-left:35px;color:var(--muted);font-size:12px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.subject-details{display:flex;align-items:center;gap:13px}.subject-progress{min-width:120px}.subject-progress .progress-label{display:flex;justify-content:space-between;gap:8px;margin-bottom:5px;color:var(--muted);font-size:10px;font-weight:700}.bar{height:6px;overflow:hidden;border-radius:99px;background:#edf1f5}.bar span{display:block;height:100%;border-radius:inherit;background:var(--green)}.subject-note{text-align:right;min-width:62px}.subject-note strong{display:block;font-size:23px;letter-spacing:-.05em}.subject-note small{color:var(--muted);font-size:10px}.empty-state{padding:30px 15px;text-align:center;color:var(--muted)}.empty-state i{display:block;margin-bottom:10px;color:#94a3b8;font-size:27px}.personal-list{display:grid;gap:0}.personal-row{display:grid;grid-template-columns:32px minmax(98px,.7fr) minmax(0,1.3fr);align-items:center;gap:9px;padding:11px 0;border-bottom:1px solid #eff2f5}.personal-row:last-child{border-bottom:0}.personal-row i{color:#5980bc;font-size:15px;text-align:center}.personal-row span{color:var(--muted);font-size:12px}.personal-row strong{font-size:12px;text-align:right;overflow-wrap:anywhere}.empty-value{color:#94a3b8;font-weight:500}.guardian-list{display:grid;gap:11px}.guardian{padding:13px;border:1px solid #e7edf5;border-radius:13px;background:#fbfdff}.guardian-head{display:flex;justify-content:space-between;gap:10px;align-items:center}.guardian-head-left{display:grid;gap:3px;min-width:0}.guardian strong{font-size:13px}.relationship{padding:4px 7px;border-radius:7px;background:#eaf2ff;color:#2761ac;font-size:10px;font-weight:800;text-transform:capitalize}.guardian-status{padding:4px 7px;border-radius:7px;background:#dcfce7;color:#15803d;font-size:10px;font-weight:800;text-transform:capitalize}.guardian-status.inactive{background:#fee2e2;color:#b91c1c}.guardian-data{display:grid;gap:4px;margin-top:8px;color:var(--muted);font-size:11px}.guardian-data i{width:14px;color:#6991c9}.attendance-table{width:100%;border-collapse:collapse}.attendance-table th{padding:0 0 11px;color:#74839b;text-align:left;font-size:10px;letter-spacing:.07em;text-transform:uppercase}.attendance-table td{padding:12px 0;border-top:1px solid #eef2f6;font-size:12px}.attendance-table td:not(:first-child),.attendance-table th:not(:first-child){text-align:right}.attendance-number{font-weight:800}.attendance-good{color:#15803d}.attendance-alert{color:#c2410c}.observation{margin:0;color:#53647c;line-height:1.65;white-space:pre-line}.section-note{margin:0;color:#8794a8;font-size:11px;line-height:1.5}.print-only{display:none}@media(max-width:940px){.metrics{grid-template-columns:repeat(2,1fr)}.layout{grid-template-columns:1fr}.summary-grid{grid-template-columns:1fr 1fr}}@media(max-width:610px){.page{padding:15px 12px 32px}.topbar{margin-bottom:13px}.brand span{display:none}.print-button{display:none}.hero{align-items:flex-start;padding:22px 18px;gap:16px;border-radius:19px}.student-photo{width:80px;height:80px;border-radius:17px}.hero h1{font-size:25px}.status{position:absolute;right:15px;top:15px;font-size:0;padding:8px}.status i{font-size:13px}.metrics{gap:9px;margin:13px 0}.metric{padding:14px}.metric strong{font-size:21px}.metric small{font-size:10px}.panel{border-radius:15px}.panel-header,.panel-body{padding-left:16px;padding-right:16px}.summary-grid{grid-template-columns:1fr}.subject-card{grid-template-columns:1fr}.subject-details{justify-content:space-between}.subject-progress{flex:1}.personal-row{grid-template-columns:26px 1fr}.personal-row span{grid-column:2}.personal-row strong{grid-column:2;text-align:left}.attendance-table{font-size:11px}.attendance-table th:nth-child(4),.attendance-table td:nth-child(4){display:none}}@media print{body{background:#fff}.page{max-width:none;padding:0}.topbar,.print-button{display:none}.hero{box-shadow:none;border-radius:0}.metrics,.layout{break-inside:avoid}.panel,.metric{box-shadow:none}.print-only{display:block}}
    </style>
    <link rel="stylesheet" href="<?php echo hv_escape(SGA_USUARIO_URL); ?>/assets/hoja-vida.css?v=<?php echo filemtime(__DIR__ . '/assets/hoja-vida.css'); ?>">
</head>
<body>
<main class="page">
    <header class="topbar">
        <div class="brand"><span class="brand-mark"><i class="bi bi-mortarboard-fill"></i></span><span>Hoja de vida estudiantil</span></div>
        <div>
            <button class="print-button" type="button" data-print="report"><i class="bi bi-printer"></i> Imprimir informe</button>
            <button class="print-button" type="button" data-print="card"><i class="bi bi-person-vcard"></i> Imprimir ficha</button>
            <button class="print-button" type="button" id="hv-export"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</button>
            <a class="back-button" href="javascript:history.back()"><i class="bi bi-arrow-left"></i> Volver</a>
        </div>
    </header>

    <section class="hero" aria-label="Perfil del estudiante">
        <img class="student-photo" src="<?php echo hv_escape(hv_foto_url($estudiante['foto'], $estudiante['genero'])); ?>" alt="Foto de <?php echo hv_escape($nombreCompleto); ?>" onerror="this.src='<?php echo hv_escape(hv_foto_url('', $estudiante['genero'])); ?>'">
        <div class="hero-content">
            <span class="eyebrow"><i class="bi bi-person-vcard"></i> Perfil académico integral</span>
            <h1><?php echo hv_escape($nombreCompleto); ?></h1>
            <div class="hero-meta">
                <span><i class="bi bi-person-badge"></i> <?php echo hv_escape(($estudiante['tipo_documento'] ?: 'Documento') . ' ' . $estudiante['id_usuario']); ?></span>
                <span><i class="bi bi-book"></i> Grado <?php echo hv_escape($gradoActual); ?></span>
                <span><i class="bi bi-calendar3"></i> Año lectivo <?php echo hv_escape($anoTexto); ?></span>
            </div>
        </div>
        <span class="status <?php echo $estadoActivo ? '' : 'inactive'; ?>"><i class="bi bi-circle-fill"></i> <?php echo $estadoActivo ? 'Activo' : 'Inactivo'; ?></span>
    </section>

    <?php require __DIR__ . '/partials/hoja_vida_filtro.php'; ?>
    <section class="metrics" aria-label="Métricas académicas">
        <article class="metric"><div class="metric-top"><span>Promedio general</span><i class="bi bi-graph-up-arrow metric-icon"></i></div><strong><?php echo $promedioGeneral !== null ? number_format($promedioGeneral, 2) : '—'; ?></strong><small><?php echo $promedioGeneral !== null ? hv_escape($nivelGeneral['texto']) : 'Aún no hay notas disponibles'; ?></small></article>
        <article class="metric"><div class="metric-top"><span>Asistencia global</span><i class="bi bi-calendar-check metric-icon"></i></div><strong><?php echo $porcentajeAsistencia !== null ? $porcentajeAsistencia . '%' : '—'; ?></strong><small><?php echo $totalAsistencia ? $presentes . ' presentes de ' . $totalAsistencia . ' registros' : 'Sin registros de asistencia'; ?></small></article>
        <article class="metric"><div class="metric-top"><span>Materias activas</span><i class="bi bi-journals metric-icon"></i></div><strong><?php echo count($materias); ?></strong><small><?php echo $actividadesValoradas; ?> actividad(es) valorada(s)</small></article>
        <article class="metric"><div class="metric-top"><span>Alertas académicas</span><i class="bi bi-exclamation-triangle metric-icon"></i></div><strong><?php echo $materiasEnRiesgo; ?></strong><small><?php echo $materiasEnRiesgo ? 'Materia(s) con promedio inferior a 3,0' : 'Sin materias en riesgo'; ?></small></article>
    </section>

    <div class="layout">
        <div class="stack">
            <section class="panel">
                <header class="panel-header"><div><h2 class="panel-title">Resumen de desempeño</h2><p class="panel-subtitle">Promedio de las materias con nota y asistencia por fecha de clase en el rango seleccionado.</p></div></header>
                <div class="panel-body"><div class="summary-grid">
                    <div class="average-box"><span class="average-label">Promedio del rango seleccionado</span><div class="average-number"><?php echo $promedioGeneral !== null ? number_format($promedioGeneral, 2) : '—'; ?></div><span class="grade-badge <?php echo hv_escape($nivelGeneral['clase']); ?>"><i class="bi bi-award"></i> <?php echo hv_escape($nivelGeneral['texto']); ?></span></div>
                    <div class="attendance-overview"><div class="attendance-ring" style="--progress:<?php echo (int)($porcentajeAsistencia ?? 0); ?>;--ring-color:<?php echo hv_escape($asistenciaColor); ?>"><span><?php echo $porcentajeAsistencia !== null ? $porcentajeAsistencia . '%' : '—'; ?></span></div><div class="attendance-copy"><strong>Compromiso de asistencia</strong><p><?php echo $totalAsistencia ? $presentes . ' asistencias y ' . $ausencias . ' inasistencia(s) registradas.' : 'No hay asistencia registrada para este estudiante.'; ?></p></div></div>
                </div></div>
            </section>

            <section class="panel">
                <header class="panel-header"><div><h2 class="panel-title">Materias activas</h2><p class="panel-subtitle">Promedio simple de las actividades valoradas del rango seleccionado; las actividades sin nota no se cuentan.</p></div><span class="relationship"><?php echo count($materias); ?> activas</span></header>
                <div class="panel-body">
                    <?php if ($materias): ?><div class="subject-list">
                        <?php foreach ($materias as $materia): ?>
                            <article class="subject-card">
                                <div class="subject-main"><div class="subject-title"><i class="bi bi-book-half"></i><span><?php echo hv_escape($materia['nombre_materia'] ?: 'Materia sin nombre'); ?></span></div><p class="teacher"><i class="bi bi-person-workspace"></i> <?php echo hv_escape(trim($materia['docente']) ?: 'Docente sin asignar'); ?></p></div>
                                <div class="subject-details">
                                    <div class="subject-progress"><div class="progress-label"><span>Asistencia</span><strong><?php echo $materia['porcentaje_asistencia'] !== null ? $materia['porcentaje_asistencia'] . '%' : 'Sin datos'; ?></strong></div><div class="bar"><span style="width:<?php echo (int)($materia['porcentaje_asistencia'] ?? 0); ?>%;background:<?php echo $materia['porcentaje_asistencia'] !== null && $materia['porcentaje_asistencia'] < 80 ? '#f59e0b' : '#16a34a'; ?>"></span></div></div>
                                    <div class="subject-note"><strong><?php echo $materia['nota'] !== null ? number_format($materia['nota'], 2) : '—'; ?></strong><small><?php echo hv_escape($materia['nivel']['texto']); ?> · <?php echo (int)$materia['actividades_valoradas']; ?> act.</small></div>
                                </div>
                                <?php require __DIR__ . '/partials/hoja_vida_actividades.php'; ?>
                            </article>
                        <?php endforeach; ?>
                    </div><?php else: ?><div class="empty-state"><i class="bi bi-journal-x"></i><strong>No hay materias activas</strong><p>Cuando el estudiante tenga inscripciones visibles, aparecerán aquí.</p></div><?php endif; ?>
                </div>
            </section>

            <section class="panel">
                <header class="panel-header"><div><h2 class="panel-title">Detalle de asistencia</h2><p class="panel-subtitle">Controles por fecha de clase · <?php echo hv_escape($etiquetaPeriodo); ?></p></div></header>
                <div class="panel-body">
                    <?php require __DIR__ . '/partials/hoja_vida_asistencia.php'; ?>
                </div>
            </section>
        </div>

        <aside class="stack">
            <section class="panel"><header class="panel-header"><div><h2 class="panel-title">Información personal</h2><p class="panel-subtitle">Datos registrados en el perfil institucional.</p></div></header><div class="panel-body"><div class="personal-list">
                <div class="personal-row"><i class="bi bi-card-text"></i><span>Documento</span><strong><?php echo hv_mostrar(($estudiante['tipo_documento'] ?: 'Documento') . ' ' . $estudiante['id_usuario']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-patch-check"></i><span>Estado</span><strong><?php echo hv_escape($estadoTexto); ?></strong></div>
                <div class="personal-row"><i class="bi bi-mortarboard"></i><span>Grado actual</span><strong><?php echo hv_escape($gradoActual); ?></strong></div>
                <div class="personal-row"><i class="bi bi-box-arrow-in-right"></i><span>Fecha de ingreso</span><strong><?php echo hv_fecha($estudiante['fecha_creacion']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-box-arrow-right"></i><span>Fecha de retiro</span><strong><?php echo hv_fecha($estudiante['fecha_retiro'], 'Sin retiro registrado'); ?></strong></div>
                <div class="personal-row"><i class="bi bi-cake2"></i><span>Nacimiento</span><strong><?php echo hv_mostrar($estudiante['fecha_nacimiento']); ?><?php echo $edad !== null ? ' · ' . $edad . ' años' : ''; ?></strong></div>
                <div class="personal-row"><i class="bi bi-gender-ambiguous"></i><span>Género</span><strong><?php echo hv_mostrar($estudiante['genero']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-hospital"></i><span>EPS</span><strong><?php echo hv_mostrar($estudiante['eps']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-droplet-half"></i><span>Tipo de sangre</span><strong><?php echo hv_mostrar($estudiante['tipo_sangre']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-geo-alt"></i><span>Dirección</span><strong><?php echo hv_mostrar($estudiante['direccion']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-telephone"></i><span>Teléfono</span><strong><?php echo hv_mostrar($estudiante['telefono']); ?></strong></div>
                <div class="personal-row"><i class="bi bi-envelope"></i><span>Correo</span><strong><?php echo hv_mostrar($estudiante['correo']); ?></strong></div>
            </div><?php require __DIR__ . '/partials/hoja_vida_eps.php'; ?></div></section>

            <section class="panel"><header class="panel-header"><div><h2 class="panel-title">Acudientes vinculados</h2><p class="panel-subtitle">Contactos familiares registrados.</p></div></header><div class="panel-body">
                <?php if ($acudientes): ?><div class="guardian-list"><?php foreach ($acudientes as $acudiente): $acudienteActivo = strtolower(trim((string)$acudiente['estado'])) === 'activo'; ?><article class="guardian"><div class="guardian-head"><div class="guardian-head-left"><strong><?php echo hv_escape(trim($acudiente['nombre'] . ' ' . $acudiente['apellido'])); ?></strong><span class="relationship"><?php echo hv_escape($acudiente['parentesco'] ?: 'Acudiente'); ?></span></div><span class="guardian-status <?php echo $acudienteActivo ? '' : 'inactive'; ?>"><?php echo hv_escape(trim((string)$acudiente['estado']) !== '' ? ucfirst(trim((string)$acudiente['estado'])) : 'Sin estado'); ?></span></div><div class="guardian-data"><span><i class="bi bi-card-text"></i> <?php echo hv_escape(($acudiente['tipo_documento'] ?: 'Documento') . ' ' . $acudiente['id_usuario']); ?></span><?php if (trim((string)$acudiente['telefono']) !== ''): ?><span><i class="bi bi-telephone"></i> <?php echo hv_escape($acudiente['telefono']); ?></span><?php endif; ?><?php if (trim((string)$acudiente['correo']) !== ''): ?><span><i class="bi bi-envelope"></i> <?php echo hv_escape($acudiente['correo']); ?></span><?php endif; ?><?php if (trim((string)$acudiente['direccion']) !== ''): ?><span><i class="bi bi-geo-alt"></i> <?php echo hv_escape($acudiente['direccion']); ?></span><?php endif; ?><span><i class="bi bi-calendar-plus"></i> Registrado: <?php echo hv_fecha($acudiente['fecha_creacion']); ?></span><?php if (trim((string)$acudiente['fecha_retiro']) !== '' && $acudiente['fecha_retiro'] !== '0000-00-00'): ?><span><i class="bi bi-calendar-x"></i> Retiro: <?php echo hv_fecha($acudiente['fecha_retiro']); ?></span><?php endif; ?></div></article><?php endforeach; ?></div><?php else: ?><p class="section-note">No hay acudientes asociados al perfil del estudiante.</p><?php endif; ?>
            </div></section>

            <section class="panel"><header class="panel-header"><div><h2 class="panel-title">Observaciones</h2><p class="panel-subtitle">Anotaciones del perfil institucional.</p></div></header><div class="panel-body"><p class="observation"><?php echo hv_mostrar($estudiante['observaciones'], 'No hay observaciones registradas.'); ?></p></div></section>
        </aside>
    </div>
    <p class="print-only section-note">Hoja de vida académica generada el <?php echo date('d/m/Y H:i'); ?>.</p>
    <?php require __DIR__ . '/partials/hoja_vida_ficha.php'; ?>
</main>
<?php require __DIR__ . '/partials/hoja_vida_export.php'; ?>
</body>
</html>
