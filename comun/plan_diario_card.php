<?php
require_once __DIR__ . '/database.php';
$planCardToday = (new DateTimeImmutable('now', new DateTimeZone(GuaguaDatabase::settings()['timezone'])))->format('Y-m-d');
?>
<div class="col-xs-12 col-lg-4 widget-header-item home-card-widget" data-card-id="plan-diario">
  <section class="panel panel-info" id="plan-diario-card" aria-labelledby="plan-diario-card-title"
       data-api="<?php echo guagua_home_escape(SGA_URL . '/apps/plan_diario/api.php'); ?>"
       data-today="<?php echo guagua_home_escape($planCardToday); ?>"
       data-csrf="<?php echo guagua_home_escape(guagua_csrf_token()); ?>">
    <div class="panel-heading">
      <h4><span class="glyphicon glyphicon-move home-card-drag-handle" title="Arrastrar para reordenar" aria-hidden="true"></span><a id="plan-diario-card-title" href="<?php echo guagua_home_escape(SGA_URL . '/apps/plan_diario/'); ?>">Mi plan diario · Hoy</a></h4>
    </div>
    <div class="panel-body tarjeta">
      <div class="pd-home-date-controls">
        <label for="plan-diario-card-date">Fecha del plan
          <input type="date" id="plan-diario-card-date" class="form-control" data-plan-picker min="2000-01-01" max="2100-12-31" value="<?php echo guagua_home_escape($planCardToday); ?>" required>
        </label>
        <button class="btn btn-default" type="button" data-plan-today>Hoy</button>
      </div>
      <p class="text-muted" data-plan-date></p>
      <div data-plan-summary hidden>
        <p data-plan-count></p>
        <progress data-plan-progress max="1" value="0" aria-label="Avance de las tareas de la fecha elegida"></progress>
      </div>
      <p data-plan-status role="status" aria-live="polite">Cargando las tareas de hoy…</p>
      <p data-plan-error role="alert" hidden></p>
      <div data-plan-tasks></div>
    </div>
    <div class="panel-footer"><button class="btn btn-default btn-sm" type="button" data-plan-refresh>Actualizar</button></div>
  </section>
</div>
<link rel="stylesheet" href="<?php echo guagua_home_escape(SGA_COMUN_URL); ?>/css/plan_diario_card.css?v=<?php echo filemtime(__DIR__ . '/css/plan_diario_card.css'); ?>">
<script src="<?php echo guagua_home_escape(SGA_COMUN_URL); ?>/js/plan_diario_card.js?v=<?php echo filemtime(__DIR__ . '/js/plan_diario_card.js'); ?>" defer></script>
