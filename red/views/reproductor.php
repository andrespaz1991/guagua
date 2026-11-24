<?php
/** Renderizado nativo por formato; no depende de los reproductores globales antiguos. */
$imagenes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'];
$videos = ['mp4', 'webm', 'ogg'];
$audios = ['mp3', 'wav', 'm4a', 'oga'];
$es_scorm = $formato === 'zip' && ($recurso['scorm'] ?? '') === 'si';
?>
<?php if (!$enlace_accesible): ?>
    <div class="viewer-empty"><h2>No hay una vista previa disponible</h2><p>Consulta los detalles o descarga el archivo adjunto.</p></div>
<?php elseif ($es_scorm): ?>
    <?php if ($actividades): ?>
        <nav class="viewer-toolbar" aria-label="Actividades del paquete SCORM">
        <?php foreach ($actividades as $numero => $actividad): ?>
            <a class="viewer-button" href="<?= $esc($actividad['url']) ?>" target="red-scorm">Actividad <?= $numero + 1 ?> · <?= $esc($actividad['nombre']) ?></a>
        <?php endforeach; ?>
        </nav>
        <div class="resource-renderer"><iframe name="red-scorm" src="<?= $esc($actividades[0]['url']) ?>" title="Contenido SCORM: <?= $esc($recurso['titulo_red']) ?>" allowfullscreen></iframe></div>
    <?php else: ?>
        <div class="viewer-empty"><h2>No se pudo abrir el paquete SCORM</h2><p>El manifiesto o sus actividades no están disponibles. Puedes descargar el archivo.</p></div>
    <?php endif; ?>
<?php elseif (in_array($formato, $imagenes, true)): ?>
    <div class="resource-renderer"><img src="<?= $esc($enlace_original) ?>" alt="<?= $esc($recurso['titulo_red']) ?>" data-media></div>
<?php elseif (in_array($formato, $videos, true)): ?>
    <div class="resource-renderer"><video src="<?= $esc($enlace_original) ?>" controls playsinline preload="metadata" aria-label="<?= $esc($recurso['titulo_red']) ?>" data-media></video></div>
<?php elseif (in_array($formato, $audios, true)): ?>
    <div class="resource-renderer"><div class="viewer-empty"><h2>Recurso de audio</h2><p><?= $esc($recurso['titulo_red']) ?></p><audio src="<?= $esc($enlace_original) ?>" controls preload="metadata" aria-label="<?= $esc($recurso['titulo_red']) ?>" data-media></audio></div></div>
<?php elseif ($es_recurso_enlace || in_array($formato, ['pdf', 'txt', 'html', 'htm'], true)): ?>
    <div class="enlace-preview"><iframe src="<?= $esc($enlace_original) ?>" title="Vista previa: <?= $esc($recurso['titulo_red']) ?>" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe></div>
<?php else: ?>
    <div class="viewer-empty"><h2>Este formato se abre en otra aplicación</h2><p>Utiliza «Abrir recurso» o «Descargar archivo» para consultar el contenido.</p></div>
<?php endif; ?>
