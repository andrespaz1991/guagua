<?php
require_once __DIR__ . '/../comun/database.php';

require __DIR__ . '/../usuario/lib/hoja_vida_periodos.php';
$db = guagua_db_mysqli();
$fecha = hv_fecha_asistencia_sql();
$dataset = "(SELECT '2026-01-13' AS fechas_clase UNION ALL SELECT '27/03/2026' UNION ALL SELECT '2026-03-28' UNION ALL SELECT 'sin fecha') f";
$q = $db->query("SELECT ($fecha) fecha FROM $dataset WHERE ($fecha) >= '2026-01-13' AND ($fecha) < DATE_ADD('2026-03-27', INTERVAL 1 DAY) ORDER BY fecha");
$fechas = array_column($q->fetch_all(MYSQLI_ASSOC), 'fecha');
if ($fechas !== ['2026-01-13','2026-03-27']) throw new RuntimeException('Falló el filtro de asistencia ISO/histórica.');
echo "OK: SQL de asistencia incluye ISO e histórico, extremos inclusivos, excluye fuera de rango y fechas inválidas.\n";
