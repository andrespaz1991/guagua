<?php
/** Shared rules for the profile, printed report and Excel export. */
function hv_nota_numerica($valor) {
    $valor = str_replace(',', '.', trim((string)$valor));
    return preg_match('/^\d+(?:\.\d+)?$/D', $valor) ? (float)$valor : null;
}

function hv_fecha_actividad(array $actividad) {
    foreach (['fecha_entrega', 'fecha_publicacion'] as $campo) {
        $fecha = substr(trim((string)($actividad[$campo] ?? '')), 0, 10);
        if ($fecha !== '' && $fecha !== '0000-00-00') return $fecha;
    }
    return '';
}

function hv_fecha_en_rango($fecha, $inicio, $fin) {
    $fecha = substr(trim((string)$fecha), 0, 10);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $fecha) || $fecha === '0000-00-00') return false;
    return (!$inicio || $fecha >= $inicio) && (!$fin || $fecha <= $fin);
}

function hv_resumir_actividades(array $actividades) {
    $notas = [];
    foreach ($actividades as $actividad) {
        $nota = hv_nota_numerica($actividad['valoracion'] ?? '');
        if ($nota !== null) $notas[] = $nota;
    }
    return ['actividades_valoradas' => count($notas),
        'nota_seguimiento' => $notas ? round(array_sum($notas) / count($notas), 2) : null];
}

/** Parse both current ISO dates and historical DD/MM/YYYY attendance dates. */
function hv_fecha_asistencia_sql() {
    return "CASE
        WHEN TRIM(fechas_clase) REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$'
            THEN STR_TO_DATE(TRIM(fechas_clase), '%Y-%m-%d')
        WHEN TRIM(fechas_clase) REGEXP '^[0-9]{2}/[0-9]{2}/[0-9]{4}$'
            THEN STR_TO_DATE(TRIM(fechas_clase), '%d/%m/%Y')
        ELSE NULL END";
}
