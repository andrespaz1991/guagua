<?php
ini_set('display_errors', '0');

require_once __DIR__ . "/../../../comun/conexion.php";
guagua_exigir_rol(guagua_roles_gestion_curricular());
require_once __DIR__ . '/../../../comun/academico.php';
require_once __DIR__ . '/../lib/periodos.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'status' => 'success',
    'data' => [
        'grados' => [],
        'materias' => [],
        'estandares' => [],
        'dbas' => [],
        'ejes_tematicos' => []
    ]
];

try {
    $resPeriodos = $mysqli->query(guagua_periodos_sql());
    if (!$resPeriodos) throw new RuntimeException('No fue posible consultar las fechas de los periodos.');
    $periodos = array_values(array_filter($resPeriodos->fetch_all(MYSQLI_ASSOC), static function ($periodo) {
        return dba_numero_periodo($periodo) !== null;
    }));
    $response['data']['periodos'] = array_map(static function ($periodo) {
        return ['numero_periodo' => dba_numero_periodo($periodo)]
            + array_intersect_key($periodo, array_flip(['id_periodo', 'nombre_periodo', 'fecha_inicio', 'fecha_fin']));
    }, $periodos);
    // La conexión central establece la zona horaria configurada de Guagua.
    $response['data']['fecha_actual'] = date('Y-m-d');
    $response['data']['periodo_actual'] = dba_periodo_por_fecha($periodos, $response['data']['fecha_actual']);

    // 1. Grados
    $resGrados = $mysqli->query("SELECT id_grado, nombre FROM grado ORDER BY CAST(nombre AS UNSIGNED) ASC");
    if ($resGrados) {
        while ($row = $resGrados->fetch_assoc()) {
            $response['data']['grados'][] = $row;
        }
    }

    // 2. Materias
    $resMaterias = $mysqli->query("SELECT id_materia, nombre_materia FROM materia_oficial ORDER BY nombre_materia ASC");
    if ($resMaterias) {
        while ($row = $resMaterias->fetch_assoc()) {
            $response['data']['materias'][] = $row;
        }
    }

    // 3. Estándares
    $resEstandares = $mysqli->query("SELECT id_estandar, nombre_estandar, descripcion_estandar, grado, id_periodo, id_materia_oficial FROM estandar ORDER BY grado, id_materia_oficial, id_periodo, id_estandar");
    if (!$resEstandares) {
        throw new RuntimeException('No fue posible consultar los estándares.');
    }
    while ($row = $resEstandares->fetch_assoc()) {
        $response['data']['estandares'][] = $row;
    }

    // 4. DBAs
    $resDbas = $mysqli->query("SELECT id_dba, nombre_dba, descripcion_dba, palabras_clave, id_estandar FROM dba ORDER BY id_estandar, id_dba");
    if (!$resDbas) {
        throw new RuntimeException('No fue posible consultar los DBA.');
    }
    while ($row = $resDbas->fetch_assoc()) {
        $response['data']['dbas'][] = $row;
    }

    // 5. Ejes Temáticos
    $resEjes = $mysqli->query("SELECT id_eje_tematico, nombre_eje_tematico, descripcion_eje_tematico, id_dba FROM eje_tematico ORDER BY id_dba, id_eje_tematico");
    if (!$resEjes) {
        throw new RuntimeException('No fue posible consultar los ejes temáticos.');
    }
    while ($row = $resEjes->fetch_assoc()) {
        $response['data']['ejes_tematicos'][] = $row;
    }
    $response['data']['evidencias'] = $mysqli->query('SELECT e.* FROM evidencia_de_aprendizaje e INNER JOIN dba d ON d.id_dba=e.id_dba ORDER BY e.id_dba, e.id_evidencia_aprendizaje')->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    http_response_code(500);
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
