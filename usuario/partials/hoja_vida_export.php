<?php
$exportacionHdv = [
    'archivo' => 'Hoja_vida_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $idEstudiante) . '_' . ($periodoActual ? 'periodo_' . $periodoActual['id_periodo'] : 'ano') . '.xlsx',
    'periodo' => $etiquetaPeriodo, 'inicio' => $fechaInicio, 'fin' => $fechaFin,
    'hojas' => [
        'Perfil' => [['Campo', 'Valor'], ['Nombre', $nombreCompleto], ['Documento', $idEstudiante], ['Tipo de documento', $estudiante['tipo_documento']], ['Grado', $gradoActual], ['Año lectivo', $anoTexto], ['EPS', $estudiante['eps']], ['Tipo de sangre', $estudiante['tipo_sangre']], ['Teléfono', $estudiante['telefono']], ['Dirección', $estudiante['direccion']], ['Correo', $estudiante['correo']], ['Nacimiento', $estudiante['fecha_nacimiento']], ['Estado', $estadoTexto], ['Género', $estudiante['genero']], ['Fecha de ingreso', $estudiante['fecha_creacion']], ['Fecha de retiro', $estudiante['fecha_retiro']], ['Observaciones', $estudiante['observaciones']], ['Foto', hv_foto_url($estudiante['foto'], $estudiante['genero'])], ['Promedio general del rango', $promedioGeneral], ['Asistencia del rango (%)', $porcentajeAsistencia]],
        'Acudientes' => [['Nombre', 'Documento', 'Tipo de documento', 'Parentesco', 'Teléfono', 'Correo', 'Dirección', 'Estado', 'Fecha de ingreso', 'Fecha de retiro']],
        'Resumen académico' => [['Materia', 'Docente', 'Promedio', 'Fuente de promedio', 'Nota consolidada', 'Actividades valoradas', 'Presentes', 'Ausencias', 'Total controles', 'Asistencia (%)']],
        'Actividades' => [['Materia', 'Actividad', 'Fecha del periodo', 'Nota', 'Fecha de valoración', 'ID actividad', 'ID asignación']],
        'Asistencia' => [['Fecha de clase', 'Materia', 'Asistencia', 'Justificación', 'Actualizado el']]
    ]
];
foreach ($acudientes as $acudiente) $exportacionHdv['hojas']['Acudientes'][] = [trim($acudiente['nombre'] . ' ' . $acudiente['apellido']), (string)$acudiente['id_usuario'], $acudiente['tipo_documento'], $acudiente['parentesco'], (string)$acudiente['telefono'], $acudiente['correo'], $acudiente['direccion'], $acudiente['estado'], $acudiente['fecha_creacion'], $acudiente['fecha_retiro']];
foreach ($materias as $materia) {
    $exportacionHdv['hojas']['Resumen académico'][] = [$materia['nombre_materia'], $materia['docente'], $materia['nota'], $materia['fuente_nota'], $materia['nota_oficial'], $materia['actividades_valoradas'], $materia['asistencia']['presentes'], $materia['asistencia']['ausencias'], $materia['asistencia']['total'], $materia['porcentaje_asistencia']];
    foreach ($materia['actividades'] as $actividad) $exportacionHdv['hojas']['Actividades'][] = [$materia['nombre_materia'], $actividad['nombre_actividad'], $actividad['fecha_periodo'], hv_nota_numerica($actividad['valoracion']), $actividad['fechayhora_valoracion'], (int)$actividad['id_actividad'], (int)$actividad['id_asignacion']];
}
foreach ($filasAsistencia as $registro) $exportacionHdv['hojas']['Asistencia'][] = [$registro['fechas_clase'], $registro['materia'], $registro['asistencias'], $registro['justificacion'] === '0' ? '' : $registro['justificacion'], $registro['fecha_actualizacion'] ?? ''];
?>
<script type="application/json" id="hv-export-data"><?php echo json_encode($exportacionHdv, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_UNICODE); ?></script>
<script src="<?php echo hv_escape(SGA_COMUN_URL); ?>/vendor/xlsx.full.min.js"></script>
<script src="<?php echo hv_escape(SGA_USUARIO_URL); ?>/assets/hoja-vida.js?v=<?php echo filemtime(__DIR__ . '/../assets/hoja-vida.js'); ?>"></script>
