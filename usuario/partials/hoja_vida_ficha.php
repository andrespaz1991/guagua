<dialog id="hv-card-dialog" aria-labelledby="hv-card-title">
    <div class="card-controls"><h2 id="hv-card-title">Ficha de identificación</h2><p>Ficha compacta de 140 mm de ancho para imprimir y llevar en las salidas.</p><button class="print-button" type="button" id="hv-card-print">Imprimir ficha</button><button class="print-button" type="button" id="hv-card-close">Cerrar</button></div>
    <article class="student-id-card" aria-label="Ficha del estudiante">
        <header><strong><?php echo hv_escape(defined('NOMBRE_INSTITUCION') ? NOMBRE_INSTITUCION : 'Guagua Educativa'); ?></strong><span>Identificación y contacto · <?php echo hv_escape($anoTexto); ?></span></header>
        <div class="id-card-person"><img src="<?php echo hv_escape(hv_foto_url($estudiante['foto'], $estudiante['genero'])); ?>" alt="Foto del estudiante"><div><h2><?php echo hv_escape($nombreCompleto); ?></h2><p><?php echo hv_escape(($estudiante['tipo_documento'] ?: 'Documento') . ' ' . $idEstudiante); ?></p><p>Grado <?php echo hv_escape($gradoActual); ?></p><p>Nacimiento: <?php echo hv_fecha($estudiante['fecha_nacimiento']); ?></p></div></div>
        <dl class="id-card-data"><div><dt>EPS</dt><dd><?php echo hv_mostrar($estudiante['eps']); ?></dd></div><div><dt>Tipo de sangre</dt><dd><?php echo hv_mostrar($estudiante['tipo_sangre']); ?></dd></div><div><dt>Teléfono del estudiante</dt><dd><?php echo hv_mostrar($estudiante['telefono']); ?></dd></div><div><dt>Dirección</dt><dd><?php echo hv_mostrar($estudiante['direccion']); ?></dd></div></dl>
        <section class="id-card-guardians" aria-labelledby="id-card-guardians-title">
            <h3 id="id-card-guardians-title">Acudientes y contactos</h3>
            <?php if (!$acudientes): ?>
                <p class="empty-value">Sin acudiente asociado al estudiante.</p>
            <?php endif; ?>
            <?php foreach ($acudientes as $acudiente): ?>
            <article class="id-card-guardian">
                <div class="id-card-guardian-heading">
                    <strong><?php echo hv_mostrar(trim(($acudiente['nombre'] ?? '') . ' ' . ($acudiente['apellido'] ?? '')), 'Nombre sin registrar'); ?></strong>
                    <span><?php echo hv_escape($acudiente['parentesco'] ?: 'Acudiente'); ?><?php if (strtolower(trim((string)$acudiente['estado'])) !== 'activo'): ?> · <?php echo hv_escape($acudiente['estado'] ?: 'Sin estado'); ?><?php endif; ?></span>
                </div>
                <dl class="id-card-guardian-data">
                    <div><dt>Documento</dt><dd><?php echo hv_escape($acudiente['tipo_documento'] ?: 'Documento'); ?> <?php echo hv_mostrar($acudiente['id_usuario']); ?></dd></div>
                    <div><dt>Teléfono de contacto</dt><dd><?php echo hv_mostrar($acudiente['telefono']); ?></dd></div>
                    <div class="id-card-guardian-wide"><dt>Correo electrónico</dt><dd><?php echo hv_mostrar($acudiente['correo']); ?></dd></div>
                    <div class="id-card-guardian-wide"><dt>Dirección</dt><dd><?php echo hv_mostrar($acudiente['direccion']); ?></dd></div>
                </dl>
            </article>
            <?php endforeach; ?>
        </section>
        <footer>Ficha generada el <?php echo date('d/m/Y'); ?> · Información registrada en la institución</footer>
    </article>
</dialog>
