<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/comun/conexion.php';
require_once dirname(__DIR__) . '/asistencia/lib/registros.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$anio = isset($_GET['anio'])
    ? filter_var($_GET['anio'], FILTER_VALIDATE_INT)
    : (int) guagua_ano_configurado($mysqli);
if ($anio === false || $anio < 2000 || $anio > 2200) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => 'Año no válido.']);
    exit;
}

// Consultar todos los estados: una corrección posterior (SI/P/R) reemplaza
// la falta histórica antes del conteo, con la misma regla del informe.
$fechaSql = guagua_asistencia_fecha_sql();
$sql = "SELECT a.id, a.fecha_actualizacion, TRIM(a.documento) AS documento,
               CAST(u.id_usuario AS CHAR) AS documento_real,
               CONCAT_WS(' ', TRIM(u.apellido), TRIM(u.nombre)) AS nombre_usuario,
               a.estudiante, TRIM(a.materia) AS materia,
               UPPER(TRIM(a.asistencias)) AS estado, $fechaSql AS fecha
        FROM asistencias a
        LEFT JOIN usuario u ON CAST(u.id_usuario AS CHAR) = TRIM(a.documento)
        WHERE ($fechaSql) BETWEEN ? AND ?
        ORDER BY a.fecha_actualizacion DESC, a.id DESC";
try {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) throw new RuntimeException('No fue posible preparar la consulta.');
    $inicio = $anio . '-01-01';
    $fin = $anio . '-12-31';
    $stmt->bind_param('ss', $inicio, $fin);
    if (!$stmt->execute()) throw new RuntimeException('No fue posible ejecutar la consulta.');
    $resultado = $stmt->get_result();
    $registros = [];
    while ($fila = $resultado->fetch_assoc()) {
        $registros[] = $fila;
    }
    $faltas = guagua_asistencia_faltas($registros);
    $stmt->close();
    echo json_encode(['ok' => true, 'anio' => $anio, 'faltas' => $faltas], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log('Inasistencias SISGA: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No fue posible consultar las inasistencias.']);
}
