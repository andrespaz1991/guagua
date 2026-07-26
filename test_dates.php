<?php
require_once __DIR__ . '/comun/conexion.php';

$periodo_activo = $mysqli->query("SELECT fecha_inicio, fecha_fin FROM periodo WHERE estado_periodo = '1' LIMIT 1");
$pd_data = $periodo_activo->fetch_assoc();
$periodo_inicio = $pd_data['fecha_inicio'];
$periodo_fin = $pd_data['fecha_fin'];

$filtro_periodo_horario = " WHERE (h.fecha_inicio <= '" . $mysqli->real_escape_string($periodo_fin) . "' OR h.fecha_inicio = '0000-00-00' OR h.fecha_inicio IS NULL)
    AND (h.fecha_fin >= '" . $mysqli->real_escape_string($periodo_inicio) . "' OR h.fecha_fin = '0000-00-00' OR h.fecha_fin IS NULL)";

$sql_horario = "SELECT h.id_asignacion, h.fecha_inicio, h.fecha_fin, h.hora_inicio, h.hora_fin, h.dia, a.id_categoria_curso AS grado, mo.nombre_materia FROM horario h JOIN asignacion a ON a.id_asignacion = h.id_asignacion LEFT JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura" . $filtro_periodo_horario;

$res_horario = $mysqli->query($sql_horario);
if ($res_horario) {
    while ($fila_h = $res_horario->fetch_assoc()) {
        $fecha_ini_h_str = !empty($fila_h['fecha_inicio']) && $fila_h['fecha_inicio'] !== '0000-00-00' ? $fila_h['fecha_inicio'] : date('Y-01-01');
        $fecha_fin_h_str = !empty($fila_h['fecha_fin']) && $fila_h['fecha_fin'] !== '0000-00-00' ? $fila_h['fecha_fin'] : date('Y-12-31');
        
        $fecha_inicio_h = new DateTime($fecha_ini_h_str);
        $fecha_fin_h = new DateTime($fecha_fin_h_str);
        
        $render_start_h = clone $fecha_inicio_h;
        $render_end_h = clone $fecha_fin_h;
        if ($periodo_inicio) {
            $p_start = new DateTime($periodo_inicio);
            if ($render_start_h < $p_start) $render_start_h = clone $p_start;
        }
        if ($periodo_fin) {
            $p_end = new DateTime($periodo_fin);
            if ($render_end_h > $p_end) $render_end_h = clone $p_end;
        }
        
        echo "{$fila_h['nombre_materia']}: render_start={$render_start_h->format('Y-m-d')} render_end={$render_end_h->format('Y-m-d')} (periodo=$periodo_inicio a $periodo_fin) <br>";
    }
}
