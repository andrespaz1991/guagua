<?php
require_once __DIR__ . '/comun/conexion.php';

$periodo_activo = $mysqli->query("SELECT fecha_inicio, fecha_fin FROM periodo WHERE estado_periodo = '1' LIMIT 1");
$periodo_inicio = null;
$periodo_fin = null;
if ($periodo_activo && $periodo_activo->num_rows > 0) {
    $pd_data = $periodo_activo->fetch_assoc();
    $periodo_inicio = $pd_data['fecha_inicio'];
    $periodo_fin = $pd_data['fecha_fin'];
}

$filtro_periodo_horario = " WHERE (h.fecha_inicio <= '" . $mysqli->real_escape_string($periodo_fin) . "' OR h.fecha_inicio = '0000-00-00' OR h.fecha_inicio IS NULL)
    AND (h.fecha_fin >= '" . $mysqli->real_escape_string($periodo_inicio) . "' OR h.fecha_fin = '0000-00-00' OR h.fecha_fin IS NULL)";

$sql_horario = "SELECT 
        h.id_asignacion,
        h.fecha_inicio,
        h.fecha_fin,
        h.hora_inicio,
        h.hora_fin,
        h.dia,
        a.id_categoria_curso AS grado,
        COALESCE(m.nombre_materia, CONCAT('Materia ID ', a.id_asignatura)) AS nombre_materia
    FROM horario AS h
    JOIN asignacion AS a ON a.id_asignacion = h.id_asignacion
    LEFT JOIN materia_oficial AS m ON m.id_materia = a.id_asignatura
    " . $filtro_periodo_horario;

$res_horario = $mysqli->query($sql_horario);
$eventos_calendario = [];
$diasSemana = [
    'lunes' => 1, 'martes' => 2, 'miercoles' => 3, 'jueves' => 4,
    'viernes' => 5, 'sabado' => 6, 'domingo' => 0
];
if ($res_horario) {
    while ($fila_h = $res_horario->fetch_assoc()) {
        try {
            $fecha_ini_h_str = !empty($fila_h['fecha_inicio']) && $fila_h['fecha_inicio'] !== '0000-00-00' ? $fila_h['fecha_inicio'] : date('Y-01-01');
            $fecha_fin_h_str = !empty($fila_h['fecha_fin']) && $fila_h['fecha_fin'] !== '0000-00-00' ? $fila_h['fecha_fin'] : date('Y-12-31');
            
            $fecha_inicio_h = new DateTime($fecha_ini_h_str);
            $fecha_fin_h = new DateTime($fecha_fin_h_str);
            $dia_semana_str = strtolower(trim($fila_h['dia'] ?? ''));
            $nombre_materia_limpio = strtolower(preg_replace('/[^a-z0-9]/i', '', $fila_h['nombre_materia']));

            if (isset($diasSemana[$dia_semana_str])) {
                $numero_dia = $diasSemana[$dia_semana_str];
                
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

                $fecha_actual_h = clone $render_start_h;

                while ($fecha_actual_h <= $render_end_h) {
                    if ((int)$fecha_actual_h->format('w') === $numero_dia) {
                        $eventos_calendario[] = [
                            'title' => $fila_h['nombre_materia'] . ' (Clase Base)',
                            'start' => $fecha_actual_h->format('Y-m-d') . 'T' . $fila_h['hora_inicio'],
                            'end' => $fecha_actual_h->format('Y-m-d') . 'T' . $fila_h['hora_fin'],
                            'description' => 'Grado: ' . htmlspecialchars($fila_h['grado']) . ' - Horario programado',
                            'id_plan' => 'horario_' . $fila_h['id_asignacion'],
                            'className' => 'evento-' . $nombre_materia_limpio,
                            'color' => '#a0aec0'
                        ];
                    }
                    $fecha_actual_h->modify('+1 day');
                }
            }
        } catch (Exception $e) {}
    }
}

echo "Total eventos generados: " . count($eventos_calendario) . "<br>";
$first_few = array_slice($eventos_calendario, 0, 5);
echo "<pre>" . print_r($first_few, true) . "</pre>";
