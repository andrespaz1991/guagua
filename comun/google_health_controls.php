<?php
require_once __DIR__ . '/database.php';
$healthClientId = (string) GuaguaDatabase::environment('GUAGUA_GOOGLE_CLIENT_ID');
?>
<div id="google-health-controls" data-client-id="<?= htmlspecialchars($healthClientId, ENT_QUOTES, 'UTF-8') ?>" data-endpoint="<?= htmlspecialchars((string)$healthEndpoint, ENT_QUOTES, 'UTF-8') ?>" data-csrf="<?= htmlspecialchars(guagua_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
  <button type="button" class="btn btn-success btn-sm" data-health-connect disabled>Conectar Google Health</button>
  <button type="button" class="btn btn-default btn-sm" data-health-sync hidden>Sincronizar Google</button>
  <button type="button" class="btn btn-link btn-sm" data-health-disconnect hidden>Desconectar Google</button>
  <p data-health-status role="status" aria-live="polite" style="margin-top:8px">Conecta Google para consultar tu actividad, mediciones y sueño disponibles.</p>
  <a data-health-setup href="https://console.cloud.google.com/apis/library/health.googleapis.com" target="_blank" rel="noopener noreferrer" hidden>Habilitar Google Health API</a>
</div>
