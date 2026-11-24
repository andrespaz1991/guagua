<?php
/** Fuente compartida del año y del período seleccionados en Configuración. */
function guagua_ano_activo_sql(): string
{
    return "SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo
            WHERE LOWER(TRIM(estado)) = 'activo' ORDER BY id_ano_lectivo DESC LIMIT 1";
}

function guagua_periodos_sql(bool $soloActivo = false): string
{
    // asignacion guarda el ID del año; periodo guarda su nombre (p. ej. 2026).
    // Se admite también el ID para instalaciones antiguas.
    return "SELECT p.* FROM periodo p INNER JOIN (" . guagua_ano_activo_sql() . ") al
            ON (CAST(p.ano_lectivo AS BINARY) = CAST(al.nombre_ano_lectivo AS BINARY)
                OR CAST(p.ano_lectivo AS BINARY) = CAST(al.id_ano_lectivo AS BINARY))
            WHERE p.fecha_inicio > '0000-00-00' AND p.fecha_fin >= p.fecha_inicio"
        . ($soloActivo ? " AND p.estado_periodo = '1'" : '')
        . " ORDER BY p.fecha_inicio, p.id_periodo";
}

function guagua_ano_activo(mysqli $db): ?array
{
    $resultado = $db->query(guagua_ano_activo_sql());
    if (!$resultado) throw new RuntimeException('No fue posible consultar el año lectivo activo: ' . $db->error);
    return $resultado->fetch_assoc() ?: null;
}

function guagua_periodo_activo(mysqli $db): ?array
{
    $resultado = $db->query(guagua_periodos_sql(true));
    if (!$resultado) throw new RuntimeException('No fue posible consultar el período activo: ' . $db->error);
    return $resultado->fetch_assoc() ?: null;
}

function guagua_ano_configurado(mysqli $db): string
{
    $ano = guagua_ano_activo($db);
    if (!$ano) throw new RuntimeException('Seleccione un año lectivo activo en Configuración.');
    return (string)$ano['nombre_ano_lectivo'];
}
