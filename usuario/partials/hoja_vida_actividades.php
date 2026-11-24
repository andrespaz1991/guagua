<details class="activity-detail">
    <summary>Ver actividades y notas (<?php echo count($materia['actividades']); ?>)</summary>
    <p class="section-note"><?php echo hv_escape($materia['fuente_nota']); ?><?php if ($materia['nota_oficial'] !== null): ?> · Nota consolidada: <?php echo number_format($materia['nota_oficial'], 2); ?><?php endif; ?></p>
    <?php if ($materia['actividades']): ?><div class="table-scroll"><table class="activity-table"><thead><tr><th>Actividad</th><th>Fecha del periodo</th><th>Nota</th><th>Valorada el</th></tr></thead><tbody>
    <?php foreach ($materia['actividades'] as $actividad): $notaActividad = hv_nota_numerica($actividad['valoracion']); ?><tr><td><?php echo hv_escape($actividad['nombre_actividad']); ?></td><td><?php echo hv_fecha($actividad['fecha_periodo']); ?></td><td><?php echo $notaActividad !== null ? number_format($notaActividad, 2) : 'Sin nota'; ?></td><td><?php echo hv_fecha($actividad['fechayhora_valoracion']); ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><p class="section-note">No hay actividades evaluables visibles en este rango.</p><?php endif; ?>
</details>
