<?php
/** Reglas comunes del informe y de la exportación de faltas para SISGA. */
function guagua_asistencia_fecha_sql(string $campo = 'a.fechas_clase'): string
{
    return "CASE
        WHEN TRIM($campo) REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(TRIM($campo), '%Y-%m-%d')
        WHEN TRIM($campo) REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$' THEN STR_TO_DATE(TRIM($campo), '%d/%m/%Y')
        ELSE NULL
    END";
}

function guagua_asistencia_clave_texto($texto): string
{
    $original = trim((string) $texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $original);
    return strtolower((string) preg_replace('/[^a-z0-9]+/i', '', $texto === false ? $original : $texto));
}

function guagua_asistencia_documento_es_alias($nombre, $documento): bool
{
    $documento = trim((string) $documento);
    return $documento === '' || preg_match('/^0+$/D', $documento)
        || guagua_asistencia_clave_texto($documento) === guagua_asistencia_clave_texto($nombre);
}

// El documento conserva la identidad aunque cambie el nombre. Un nombre
// importado solo se vincula cuando señala a un único documento real.
function guagua_asistencia_clave_estudiante($nombre, $documento, array $porNombre = [], bool $documentoVerificado = false): string
{
    $documento = trim((string) $documento);
    if ($documentoVerificado || !guagua_asistencia_documento_es_alias($nombre, $documento)) return 'documento-' . $documento;
    $clave = guagua_asistencia_clave_texto($nombre);
    if (isset($porNombre[$clave]) && count($porNombre[$clave]) === 1) return 'documento-' . array_key_first($porNombre[$clave]);
    return $clave !== '' && $nombre !== 'Estudiante sin identificar' ? 'alias-' . $clave : 'sin-identificar-' . $documento;
}

function guagua_asistencia_fecha($fecha): string
{
    $fecha = trim((string) $fecha);
    foreach (['Y-m-d', 'd/m/Y'] as $formato) {
        $objeto = DateTimeImmutable::createFromFormat('!' . $formato, $fecha);
        if ($objeto && $objeto->format($formato) === $fecha) return $objeto->format('Y-m-d');
    }
    return '';
}

/** Elegir el estado más reciente antes de contar presentes, permisos o faltas. */
function guagua_asistencia_registros_unicos(array $registros): array
{
    $porNombre = [];
    foreach ($registros as $registro) {
        $documento = trim((string) $registro['documento']);
        if (empty($registro['documento_real']) && guagua_asistencia_documento_es_alias($registro['estudiante'], $documento)) continue;
        foreach ([$registro['estudiante'], $registro['nombre_usuario'] ?? ''] as $nombre) {
            $claveNombre = guagua_asistencia_clave_texto($nombre);
            if ($claveNombre !== '') $porNombre[$claveNombre][$documento] = true;
        }
    }
    $unicos = [];
    foreach ($registros as $registro) {
        $registro['fecha'] = guagua_asistencia_fecha($registro['fecha'] ?? $registro['fechas_clase'] ?? '');
        if ($registro['fecha'] === '') continue;
        $registro['estado'] = strtoupper(trim((string) $registro['estado']));
        if (in_array($registro['estado'], ['FALTA', 'AUSENTE'], true)) $registro['estado'] = 'NO';
        $registro['clave_estudiante'] = $registro['clave_estudiante'] ?? guagua_asistencia_clave_estudiante($registro['estudiante'], $registro['documento'], $porNombre, !empty($registro['documento_real']));
        $clave = $registro['clave_estudiante']
            . '|' . guagua_asistencia_clave_texto($registro['materia']) . '|' . $registro['fecha'];
        $actual = $unicos[$clave] ?? null;
        $comparacion = $actual ? strcmp((string) ($registro['fecha_actualizacion'] ?? ''), (string) ($actual['fecha_actualizacion'] ?? '')) : 1;
        if (!$actual || $comparacion > 0 || ($comparacion === 0 && (int) ($registro['id'] ?? 0) > (int) ($actual['id'] ?? 0))) {
            $unicos[$clave] = $registro;
        }
    }
    return array_values($unicos);
}

function guagua_asistencia_faltas(array $registros): array
{
    $faltas = [];
    foreach (guagua_asistencia_registros_unicos($registros) as $registro) {
        $estado = strtoupper(trim((string) $registro['estado']));
        if (!in_array($estado, ['SI', 'NO', 'P', 'R'], true)) continue;
        $clave = $registro['clave_estudiante']
            . '|' . guagua_asistencia_clave_texto($registro['materia']);
        if (!isset($faltas[$clave])) $faltas[$clave] = ['estudiante' => (string) $registro['estudiante'], 'materia' => (string) $registro['materia'], 'faltas' => 0];
        if ($estado === 'NO') $faltas[$clave]['faltas']++;
    }
    return array_values($faltas);
}
