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

echo "Periodo Activo: $periodo_inicio a $periodo_fin \n\n";

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
    LIMIT 20";

$res = $mysqli->query($sql_horario);
echo "Todos los horarios (Muestra 20):\n";
while ($row = $res->fetch_assoc()) {
    echo "ID Asignacion: {$row['id_asignacion']}, Materia: {$row['nombre_materia']}, Fechas: {$row['fecha_inicio']} a {$row['fecha_fin']}\n";
}

echo "\nCon filtro:\n";
$sql_horario_filtrado = $sql_horario . " WHERE h.fecha_inicio <= '$periodo_fin' AND h.fecha_fin >= '$periodo_inicio'";
$res2 = $mysqli->query($sql_horario_filtrado);
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        echo "ID Asignacion: {$row['id_asignacion']}, Materia: {$row['nombre_materia']}, Fechas: {$row['fecha_inicio']} a {$row['fecha_fin']}\n";
    }
} else {
    echo "Error: " . $mysqli->error;
}
