<?php
require_once __DIR__ . '/../comun/academico.php';
require_once __DIR__ . '/../comun/sql.php';

function seguimiento_filtro(mysqli $db): string
{
    $ano = guagua_ano_activo($db);
    $filtro = 'a.ano_lectivo = ' . (int)($ano['id_ano_lectivo'] ?? 0);
    $institucion = (int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0);
    if ($institucion > 0) $filtro .= ' AND a.institucion_educativa = ' . $institucion;
    $roles = array_map('trim', explode(',', strtolower((string)($_SESSION['rol'] ?? ''))));
    if (!array_intersect($roles, ['admin', 'directivo'])) {
        $filtro .= ' AND a.id_docente = ' . (int)($_SESSION['id_usuario'] ?? 0);
    }
    return $filtro;
}

function seguimiento_inscripciones(mysqli $db): array
{
    return $db->query("SELECT i.id_inscripcion, i.id_estudiante,
        CONCAT(u.apellido, ' ', u.nombre, ' · Grado ', cc.nombre_categoria_curso, ' · Inscripción ', i.id_inscripcion) AS etiqueta
        FROM inscripcion i INNER JOIN asignacion a ON a.id_asignacion = i.id_asignacion
        INNER JOIN usuario u ON u.id_usuario = i.id_estudiante
        INNER JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
        WHERE " . seguimiento_filtro($db) . " ORDER BY u.apellido, u.nombre, i.id_inscripcion")->fetch_all(MYSQLI_ASSOC);
}

function seguimiento_condicion(mysqli $db): string
{
    $periodo = guagua_periodo_activo($db);
    if (!$periodo) return '0=1';
    return seguimiento_filtro($db) . " AND s.fecha_seguimiento BETWEEN '"
        . $db->real_escape_string($periodo['fecha_inicio']) . "' AND '"
        . $db->real_escape_string($periodo['fecha_fin']) . "'";
}
