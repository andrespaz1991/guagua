<?php
require_once __DIR__ . '/../apps/dba/lib/periodos.php';
function comprobar_periodo($expected, array $periodos, string $fecha, string $caso): void {
    $actual = dba_periodo_por_fecha($periodos, $fecha);
    if ($actual !== $expected) throw new RuntimeException("$caso: resultado inesperado " . var_export($actual, true));
    echo "OK: $caso\n";
}
$periodos = [
    ['id_periodo'=>1,'nombre_periodo'=>'1','fecha_inicio'=>'2026-01-13','fecha_fin'=>'2026-03-27','estado_periodo'=>'1'],
    ['id_periodo'=>2,'nombre_periodo'=>'2','fecha_inicio'=>'2026-03-27','fecha_fin'=>'2026-06-27','estado_periodo'=>'0'],
    ['id_periodo'=>3,'nombre_periodo'=>'3','fecha_inicio'=>'2026-07-05','fecha_fin'=>'2026-09-13','estado_periodo'=>'0'],
    ['id_periodo'=>4,'nombre_periodo'=>'4','fecha_inicio'=>'2026-09-14','fecha_fin'=>'2026-11-20','estado_periodo'=>'0'],
];
comprobar_periodo(1,$periodos,'2026-01-13','Incluye el día de inicio');
comprobar_periodo(2,$periodos,'2026-03-27','En un límite compartido abre el periodo que comienza');
comprobar_periodo(2,array_reverse($periodos),'2026-03-27','La selección no depende del orden de las filas');
comprobar_periodo(4,$periodos,'2026-11-02','Las fechas prevalecen sobre una marca manual desactualizada');
comprobar_periodo(4,$periodos,'2026-11-20','Incluye el día de cierre');
comprobar_periodo(null,$periodos,'2026-11-21','Fuera de calendario no inventa un periodo activo');
comprobar_periodo(null,$periodos,'2026-06-30','Respeta el intervalo entre periodos');
comprobar_periodo(null,[],'2026-11-02','Sin configuración todos quedan cerrados');
comprobar_periodo(null,[['id_periodo'=>0,'nombre_periodo'=>'4','fecha_inicio'=>'2026-09-01','fecha_fin'=>'2026-11-30']],'2026-11-02','Ignora registros sin identificador válido');
comprobar_periodo(null,[['id_periodo'=>1,'fecha_inicio'=>'2026-02-30','fecha_fin'=>'2026-03-30']],'2026-03-01','Ignora fechas de calendario inválidas');
comprobar_periodo(null,[['id_periodo'=>1,'fecha_inicio'=>'2026-03-30','fecha_fin'=>'2026-03-01']],'2026-03-15','Ignora rangos invertidos');
comprobar_periodo(2,[['id_periodo'=>42,'nombre_periodo'=>'2','fecha_inicio'=>'2027-04-01','fecha_fin'=>'2027-06-30']],'2027-05-01','El número curricular no se confunde con el ID de otro año');
comprobar_periodo(null,$periodos,'2026-13-01','Rechaza una fecha de consulta inválida');
