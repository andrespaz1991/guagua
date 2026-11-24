<form class="period-filter" method="get">
    <input type="hidden" name="id_estudiante" value="<?php echo hv_escape($idEstudiante); ?>">
    <div><label for="hv-periodo">Periodo académico</label><select name="periodo" id="hv-periodo">
        <option value="todos" <?php echo $periodoSeleccionado === 'todos' ? 'selected' : ''; ?>>Todo el año lectivo activo</option>
        <?php foreach ($periodos as $periodo): ?><option value="<?php echo hv_escape($periodo['id_periodo']); ?>" <?php echo $periodoSeleccionado === (string)$periodo['id_periodo'] ? 'selected' : ''; ?>><?php echo hv_escape('Periodo ' . $periodo['nombre_periodo'] . ' · ' . $periodo['ano_lectivo'] . ' · ' . $periodo['fecha_inicio'] . ' a ' . $periodo['fecha_fin']); ?></option><?php endforeach; ?>
    </select></div><button class="print-button" type="submit">Aplicar periodo</button>
    <div class="period-range"><strong><?php echo hv_escape($etiquetaPeriodo); ?></strong><span><?php echo $fechaInicio && $fechaFin ? hv_fecha($fechaInicio) . ' — ' . hv_fecha($fechaFin) . ' (inclusive)' : 'No hay un rango de fechas configurado.'; ?></span></div>
</form>
<p class="section-note period-note">La asistencia usa la fecha de clase. Las actividades usan la fecha de entrega o, si no existe, la de publicación. El promedio usa la última nota numérica de cada actividad.</p>
<div class="export-feedback" id="hv-export-status" role="status"></div>
