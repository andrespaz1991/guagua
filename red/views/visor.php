<?php // Presentación del visor. Los datos se preparan en visor_red.php. ?>
<link rel="stylesheet" href="assets/visor.css?v=2">
<section class="red-viewer" aria-label="Visor de recursos educativos">
<header class="appbar">
    <div class="appbar-inner">
        <a class="brand" href="index.php" aria-label="Volver a la biblioteca digital">
            <span class="brand-mark" aria-hidden="true">▣</span>
            <span>Biblioteca digital</span>
        </a>
        <span class="appbar-status">Recurso educativo</span>
    </div>
</header>

<div class="visor-container">
    
    <main class="visor-principal">
        <div class="visor-header">
            <a href="index.php" class="btn-volver" aria-label="Volver a la biblioteca">← Volver a la biblioteca</a>
            <h1><?= $esc($recurso['titulo_red']) ?></h1>
            <p class="visor-subtitle">
                <span class="pill"><?= $esc($recurso['formato'] ?: 'Recurso digital') ?></span>
                <span>Explora el contenido y consulta sus datos académicos.</span>
            </p>
        </div>
        <div class="viewer-toolbar" role="group" aria-label="Controles de visualización">
            <strong>Vista del recurso</strong>
            <button type="button" class="viewer-button" data-action="wide" aria-pressed="false">Ampliar vista</button>
            <button type="button" class="viewer-button" data-action="fullscreen" hidden>Pantalla completa</button>
            <button type="button" class="viewer-button" data-action="copy">Copiar enlace</button>
        </div>
        <p class="viewer-notice" role="status" aria-live="polite"></p>
        <div id="red-stage" class="visor-contenido" aria-label="Vista previa del recurso">
            <button type="button" class="viewer-button viewer-exit" data-action="exit-fullscreen">Salir de pantalla completa</button>
            <?php require __DIR__ . '/reproductor.php'; ?>
        </div>
        <p class="viewer-help">Si la vista previa no está disponible, utiliza «Abrir recurso» o descarga el archivo.</p>
        <?php if (trim((string) ($recurso['descripcion'] ?? '')) !== ''): ?>
        <section class="viewer-description" aria-labelledby="red-description">
            <h2 id="red-description">Acerca de este recurso</h2>
            <p><?= $esc(strip_tags($recurso['descripcion'])) ?></p>
        </section>
        <?php endif; ?>
    </main>

    <aside class="visor-metadata">
        <div class="metadata-section">
            <h2>Detalles del Recurso</h2>
            <div class="metadata-item">
                <strong>ID:</strong>
                <span><?= $esc($recurso['id_red']) ?></span>
            </div>
             <div class="metadata-item">
                <strong>Autor:</strong>
                <span><?= $esc($recurso['autor'] ?: 'No especificado') ?></span>
            </div>
             <div class="metadata-item">
                <strong>Responsable:</strong>
                <span><?= $esc(trim($recurso['nombre'] . ' ' . $recurso['apellido']) ?: 'No especificado') ?></span>
            </div>
             <div class="metadata-item">
                <strong>Publicado:</strong>
                <span><?= $esc($recurso['fecha_formateada']) ?></span>
            </div>
        </div>

        <div class="metadata-section">
            <h2>Información Académica</h2>
            <div class="metadata-item">
                <strong>Nivel Educativo:</strong>
                <span><?= $esc($recurso['nivel_eductivo_formateado'] ?: 'No especificado') ?></span>
            </div>
            <div class="metadata-item">
                <strong>Palabras Clave:</strong>
                <span><?= $esc($recurso['palabras_clave'] ?: 'No especificadas') ?></span>
            </div>
             <div class="metadata-item">
                <strong>Dificultad:</strong>
                <span><?= $esc($recurso['dificultad'] ?: 'No especificada') ?><?= $recurso['dificultad'] ? ' de 5' : '' ?></span>
            </div>
        </div>

        <div class="metadata-section">
            <h2>Interacción</h2>
            <div class="metadata-item">
                <strong>Visitas:</strong>
                <span><?= $esc($recurso['visitas']) ?></span>
            </div>
            <div class="metadata-item">
                <strong>Valoración:</strong>
                <div class="rating">
                    <span class="rating-mark" aria-hidden="true">★</span>
                    <span id="num_fav_red"><?= $esc($total_estrellas) ?></span>
                    <span>estrellas</span>
                    <span class="fav_visor"><?= mis_red_favoritos($recurso['id_red'], $recurso['cantidad_estrellas']); ?></span>
                </div>
            </div>
        </div>

        <div class="metadata-section">
             <h2>Acciones</h2>
            <div class="acciones">
                <?php if ($enlace_accesible): ?>
                    <a target="_blank"<?= $es_enlace_externo ? ' rel="noopener noreferrer"' : '' ?> href="<?= $esc($enlace_original) ?>" class="btn-descargar primario">↗ Abrir recurso</a>
                <?php endif; ?>

                <?php if (!empty($recurso['adjunto']) && $recurso['adjunto'] !== 'no'): ?>
                    <a href="../comun/funciones.php?ruta_red=<?= $esc($recurso['id_red']) ?>" class="btn-descargar <?= $es_enlace_externo ? 'secundario' : 'primario' ?>">↓ Descargar archivo</a>
                <?php endif; ?>
            </div>

            <?php if ($es_enlace_externo): ?>
                <p class="nota-enlace">El enlace se abrirá en una pestaña nueva: <?= $esc(parse_url($enlace_original, PHP_URL_HOST)) ?></p>
            <?php endif; ?>
        </div>

    </aside>
</div>

</section>
<script src="assets/visor.js?v=2" defer></script>
