<?php
// Solo SELECT: el conjunto de prueba es una tabla derivada, sin escribir en BD.
require_once __DIR__ . '/../comun/database.php';
require_once __DIR__ . '/../asistencia/lib/registros.php';

$db = guagua_db_mysqli();
$filas = [
    [1, '2026-02-02 08:00:00', 'ana_perez', 'ANA PEREZ', 'MATEMATICAS', '02/02/2026', 'NO'],
    [2, '2026-02-05 09:00:00', '101', 'Ana Pérez', 'Matemáticas', '2026-02-02', 'P'],
    [3, '2026-02-03 08:00:00', 'ana_perez', 'ANA PEREZ', 'MATEMATICAS', '03/02/2026', 'NO'],
    [4, '2026-02-05 09:00:00', '101', 'Ana Pérez', 'Matemáticas', '2026-02-03', 'SI'],
    [5, '2026-02-04 08:00:00', '101', 'Ana Pérez', 'Matemáticas', '2026-02-04', 'NO'],
    [6, '2026-02-04 08:00:00', 'ana_perez', 'ANA PEREZ', 'MATEMATICAS', '04/02/2026', 'NO'],
    [7, '2026-02-02 08:00:00', '101', 'Ana Pérez', 'Geometría', '2026-02-02', 'NO'],
    [8, '2026-02-02 08:00:00', '101', 'Ana Pérez', 'Geometría', 'sin fecha', 'NO'],
    [9, '2025-02-02 08:00:00', '101', 'Ana Pérez', 'Geometría', '2025-02-02', 'NO'],
];
$select = 'SELECT ? AS id, ? AS fecha_actualizacion, ? AS documento, ? AS estudiante, ? AS materia, ? AS fechas_clase, ? AS estado';
$origen = implode(' UNION ALL ', array_fill(0, count($filas), $select));
$fecha = guagua_asistencia_fecha_sql();
$sql = "SELECT a.*, ($fecha) AS fecha FROM ($origen) a WHERE ($fecha) BETWEEN ? AND ? ORDER BY a.fecha_actualizacion DESC, a.id DESC";
$valores = array_merge(...$filas);
$valores[] = '2026-01-01'; $valores[] = '2026-12-31';
$consulta = $db->prepare($sql);
$consulta->bind_param(str_repeat('s', count($valores)), ...$valores);
$consulta->execute();
$registros = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
$unicos = guagua_asistencia_registros_unicos($registros);
$faltas = guagua_asistencia_faltas($registros);
if (count($registros) !== 7 || count($unicos) !== 4 || array_sum(array_column($faltas, 'faltas')) !== 2) {
    throw new RuntimeException('El filtro ISO/histórico o la corrección de faltas no coincide entre informe y SISGA.');
}
$estados = array_count_values(array_column($unicos, 'estado'));
if (($estados['P'] ?? 0) !== 1 || ($estados['SI'] ?? 0) !== 1 || ($estados['NO'] ?? 0) !== 2) {
    throw new RuntimeException('El último permiso/presente no reemplazó la falta histórica.');
}
echo "OK: SELECT ISO/histórico, identidad documento/nombre, correcciones NO→P/SI y faltas sin duplicar; sin escrituras en BD.\n";
