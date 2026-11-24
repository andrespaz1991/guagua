<?php if ($puedeVerCualquierEstudiante && $tieneEps): ?>
<form method="post" class="eps-form"><input type="hidden" name="csrf_token" value="<?php echo hv_escape(guagua_csrf_token()); ?>"><label for="hv-eps">Actualizar EPS</label><div><input id="hv-eps" name="eps" type="text" maxlength="150" value="<?php echo hv_escape($estudiante['eps'] ?? ''); ?>" placeholder="Nombre de la EPS"><button class="print-button" name="guardar_eps" value="1" type="submit">Guardar</button></div><?php if ($mensajeEps): ?><p role="status"><?php echo hv_escape($mensajeEps); ?></p><?php endif; ?></form>
<?php endif; ?>
