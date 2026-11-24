<?php
declare(strict_types=1);
require_once __DIR__ . '/lib.php';
dng_session();
dng_cleanup();
$engine = dng_engine();
$canSaveToServer = dng_server_output_allowed();
$embedded = isset($_GET['guagua_embedded']);
if (!$embedded) {
    require_once dirname(__DIR__, 2) . '/comun/conexion.php';
    require_once dirname(__DIR__, 2) . '/comun/cabecera_modulo.php';
}
$token = guagua_csrf_token();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DNG a Word | Guagua</title>
<?php if (!$embedded) guagua_menu_estilos(); ?>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php if (!$embedded) require dirname(__DIR__, 2) . '/comun/menu.php'; ?>
<main class="dng-app">
  <div class="dng-shell">
    <header class="hero">
      <div class="hero-copy">
        <span class="eyebrow">GUAGUA · HERRAMIENTAS DE IMAGEN</span>
        <h1>De ProRAW a Word,<br><em>con calidad que se nota.</em></h1>
        <p>Convierte una foto o un lote de archivos .dng de iPhone a imágenes listas para insertar en documentos profesionales.</p>
        <div class="hero-points"><span>✓ Color sRGB</span><span>✓ Orientación original</span><span>✓ Descarga individual o ZIP</span></div>
      </div>
      <div class="hero-art" aria-hidden="true"><div class="lens"><div class="lens-inner"></div></div><div class="format-chip chip-raw">.DNG</div><div class="format-chip chip-jpg">.JPG / .PNG</div></div>
    </header>

    <?php if (!$engine): ?>
    <div class="alert" role="alert"><strong>Falta el motor de conversión.</strong> Instala <a href="https://www.rawtherapee.com/downloads/" target="_blank" rel="noopener">RawTherapee</a> en el servidor y asegúrate de que <code>rawtherapee-cli.exe</code> esté en PATH o define <code>GUAGUA_RAWTHERAPEE_CLI</code> con su ruta completa. Después reinicia Apache y recarga esta página.</div>
    <?php endif; ?>

    <div class="workspace">
      <section class="panel upload-panel" aria-labelledby="upload-title">
        <div class="section-heading"><span class="step">01</span><div><h2 id="upload-title">Selecciona tus DNG</h2><p>Hasta 20 fotografías por lote, 200 MB cada una.</p></div></div>
        <div id="dropzone" class="dropzone" tabindex="0" role="button" aria-label="Seleccionar archivos DNG">
          <div class="upload-icon">↑</div><strong>Arrastra las fotos aquí</strong><span>o <u>explora tus archivos</u></span>
          <small>Solo .dng · tus originales permanecen intactos</small>
          <input type="file" id="files" accept=".dng,image/x-adobe-dng" multiple hidden>
        </div>
        <div id="file-list" class="file-list" aria-live="polite"></div>
      </section>

      <section class="panel settings-panel" aria-labelledby="settings-title">
        <div class="section-heading"><span class="step">02</span><div><h2 id="settings-title">Prepara la salida</h2><p>Opciones pensadas para imágenes dentro de Word.</p></div></div>
        <fieldset><legend>Formato</legend>
          <label class="choice"><input type="radio" name="format" value="jpg" checked><span><strong>JPEG</strong><small>Fotografías nítidas y archivos livianos · recomendado</small></span><b>.JPG</b></label>
          <label class="choice"><input type="radio" name="format" value="png"><span><strong>PNG</strong><small>Sin pérdida, pero normalmente más pesado</small></span><b>.PNG</b></label>
        </fieldset>
        <div class="field-row">
          <label>Dimensión máxima
            <select id="edge"><option value="2800">2800 px · documento normal</option><option value="3600" selected>3600 px · impresión</option><option value="0">Resolución original</option></select>
          </label>
          <label id="quality-field">Calidad JPEG
            <select id="quality"><option value="90" selected>90 · equilibrada</option><option value="95">95 · máxima</option></select>
          </label>
        </div>
        <p class="hint">El tamaño limita el lado mayor sin recortar ni ampliar. Para una página A4, 2800–3600 px suele ser suficiente. Si un DNG usa compresión RAW no compatible, se emplea su JPEG integrado de alta resolución cuando está disponible; el resultado lo indica.</p>
        <?php if ($canSaveToServer): ?>
        <div class="output-setting">
          <label for="output-dir">Carpeta de salida en este equipo / servidor XAMPP</label>
          <div class="output-row">
            <input id="output-dir" type="text" placeholder="Ejemplo: D:\MisImagenes\Word" autocomplete="off" spellcheck="false">
            <button id="validate-dir" type="button">Comprobar ruta</button>
          </div>
          <p id="output-status" class="output-status" role="status">La carpeta debe existir y estar fuera de la raíz pública de Apache. Si dejas el campo vacío, usa las descargas manuales.</p>
        </div>
        <?php endif; ?>
        <button id="convert" type="button" <?php echo $engine ? '' : 'disabled'; ?>>Convertir imágenes <span aria-hidden="true">↗</span></button>
        <p class="privacy">Los archivos temporales se guardan fuera de la carpeta web y caducan en 2 horas.</p>
      </section>
    </div>
    <section id="results" class="results panel" hidden aria-live="polite">
      <div class="section-heading"><span class="step">03</span><div><h2>Resultados</h2><p id="result-summary">Procesando…</p></div></div>
      <div id="result-list"></div>
      <a id="zip-link" class="zip-link" hidden>Descargar lote ZIP ↓</a>
    </section>
  </div>
</main>
<script>window.DNG_CONFIG = <?php echo json_encode(['csrf' => $token, 'ready' => $engine !== null, 'userKey' => hash('sha256', (string)$_SESSION['id_usuario'])], JSON_UNESCAPED_UNICODE); ?>;</script>
<script src="app.js?v=20260926-2"></script>
</body>
</html>





