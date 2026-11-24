<div class="col-xs-12 col-lg-4 widget-header-item home-card-widget" data-card-id="fitness">
  <div class="panel panel-success" id="fitness-card" data-api="<?php echo guagua_home_escape(SGA_URL . '/apps/salud/api.php'); ?>">
    <div class="panel-heading"><h4><span class="glyphicon glyphicon-move home-card-drag-handle" title="Arrastrar para reordenar" aria-hidden="true"></span>Mi actividad · Salud</h4></div>
    <div class="panel-body tarjeta" style="overflow:auto">
      <p data-fitness-status role="status">Cargando actividad…</p>
      <div data-fitness-summary></div>
      <p class="text-muted small">Pasos de Google Health o datos enviados desde tu iPhone.</p>
      <?php $healthEndpoint = SGA_URL . '/apps/salud/google.php'; require __DIR__ . '/google_health_controls.php'; ?>
      <button class="btn btn-default btn-sm" type="button" data-fitness-refresh>Actualizar</button>
    </div>
    <div class="panel-footer"><a class="btn btn-success" href="<?php echo guagua_home_escape(SGA_URL . '/apps/salud/index.php'); ?>">Historial y conectar iPhone</a></div>
  </div>
</div>
<script src="<?php echo guagua_home_escape(SGA_COMUN_URL . '/js/fitness_card.js'); ?>" defer></script>
<script src="<?php echo guagua_home_escape(SGA_COMUN_URL . '/js/google_health.js'); ?>" defer></script>
