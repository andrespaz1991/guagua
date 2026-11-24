<?php
/** Tarjeta privada con autorización persistente en el servidor. */
require_once __DIR__ . '/database.php';
$calendarClientId = trim((string) GuaguaDatabase::environment('GUAGUA_GOOGLE_CLIENT_ID'));
?>
<div class="col-xs-12 col-lg-4 widget-header-item home-card-widget" data-card-id="google-calendar">
  <div class="panel panel-info" id="google-calendar-card"
       data-client-id="<?php echo guagua_home_escape($calendarClientId); ?>"
       data-endpoint="<?php echo guagua_home_escape(SGA_URL . '/comun/google_calendar_api.php'); ?>"
       data-connection="<?php echo guagua_home_escape(SGA_URL . '/apps/salud/connection.php?service=calendar'); ?>"
       data-csrf="<?php echo guagua_home_escape(guagua_csrf_token()); ?>"
       data-timezone="<?php echo guagua_home_escape(date_default_timezone_get()); ?>">
    <div class="panel-heading">
      <h4><span class="glyphicon glyphicon-move home-card-drag-handle" title="Arrastrar para reordenar" aria-hidden="true"></span><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span> Mi calendario</h4>
    </div>
    <div class="panel-body tarjeta" style="overflow:auto">
      <p class="text-muted">Próximos eventos de tu calendario principal de Google.</p>
      <p data-calendar-status role="status" aria-live="polite"><?php echo $calendarClientId === ''
          ? 'La conexión con Google Calendar todavía no está configurada.'
          : 'Recuperando tu calendario guardado…'; ?></p>
      <div data-calendar-events></div>
      <button type="button" class="btn btn-info btn-sm" data-calendar-connect disabled>Conectar Google Calendar</button>
      <button type="button" class="btn btn-default btn-sm" data-calendar-refresh hidden>Actualizar</button>
      <button type="button" class="btn btn-link btn-sm" data-calendar-disconnect hidden>Desconectar</button>
    </div>
    <div class="panel-footer"><a class="btn btn-info" href="https://calendar.google.com/calendar/u/0/r/agenda" target="_blank" rel="noopener noreferrer">Abrir Google Calendar</a></div>
  </div>
</div>
<style>#google-calendar-card [hidden]{display:none!important}</style>
<script src="<?php echo guagua_home_escape(SGA_COMUN_URL); ?>/js/google_calendar_card.js?v=<?php echo filemtime(__DIR__ . '/js/google_calendar_card.js'); ?>" defer></script>
