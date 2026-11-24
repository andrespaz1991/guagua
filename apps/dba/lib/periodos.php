<?php
/** Selecciona por calendario; recibe únicamente los periodos del año configurado. */
function dba_numero_periodo(array $periodo): ?int
{
    if ((int)($periodo['id_periodo'] ?? 0) <= 0) return null;
    $nombre = trim((string)($periodo['nombre_periodo'] ?? ''));
    if (preg_match('/^[1-4]$/D', $nombre)) return (int)$nombre;
    $id = (int)$periodo['id_periodo'];
    return $id >= 1 && $id <= 4 ? $id : null;
}

function dba_periodo_por_fecha(array $periodos, string $fecha): ?int
{
    $fechaValida = static function (string $valor): bool {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        return $date !== false && $date->format('Y-m-d') === $valor;
    };
    if (!$fechaValida($fecha)) return null;
    $vigente = null;
    foreach ($periodos as $periodo) {
        $id = (int)($periodo['id_periodo'] ?? 0);
        $inicio = (string)($periodo['fecha_inicio'] ?? '');
        $fin = (string)($periodo['fecha_fin'] ?? '');
        if (dba_numero_periodo($periodo) === null || !$fechaValida($inicio) || !$fechaValida($fin) || $fin < $inicio) continue;
        if ($fecha < $inicio || $fecha > $fin) continue;
        // Si comparten el día de cierre/inicio, comienza el periodo más reciente.
        if ($vigente === null || $inicio > $vigente['fecha_inicio']
            || ($inicio === $vigente['fecha_inicio'] && $id > (int)$vigente['id_periodo'])) {
            $vigente = $periodo;
        }
    }
    return $vigente === null ? null : dba_numero_periodo($vigente);
}
