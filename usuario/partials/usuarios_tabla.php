<?php if ($listado_error): ?>
<div class="users-empty" role="alert"><h3>No se pudo cargar el directorio</h3><p>Inténtelo de nuevo. Los datos existentes no se han modificado.</p><a class="users-button" href="<?= usuarios_escape(usuarios_page_url($contexto)) ?>">Reintentar</a></div>
<?php else: ?>
<div class="users-result-summary" data-total="<?= $listado['total'] ?>" data-page="<?= $listado['page'] ?>">
    <span><strong><?= number_format($listado['total'], 0, ',', '.') ?></strong> <?= $listado['total'] === 1 ? 'perfil encontrado' : 'perfiles encontrados' ?></span>
    <span><?= $listado['active'] ?> activos · <?= $listado['inactive'] ?> inactivos</span>
</div>
<?php if ($listado['total'] === 0): ?>
<div class="users-empty"><h3>No hay perfiles para mostrar</h3><p>Pruebe otro nombre o documento, cambie los filtros o cree un nuevo perfil.</p><a href="<?= usuarios_escape(usuarios_page_url($contexto, ['buscar'=>'', 'estado_filtro'=>'', 'page'=>1])) ?>">Limpiar búsqueda y estado</a></div>
<?php else: ?>
<div class="users-table-scroll" tabindex="0" role="region" aria-label="Directorio de <?= usuarios_escape(mb_strtolower($titulo)) ?>">
<table class="users-table">
    <caption class="users-sr-only">Directorio de <?= usuarios_escape($titulo) ?></caption>
    <thead><tr><th scope="col">Nombre y documento</th><th scope="col">Contacto</th><th scope="col">Cuenta</th><th scope="col">Estado</th><th scope="col">Acciones</th></tr></thead>
    <tbody>
    <?php while ($fila = $listado['result']->fetch_assoc()): ?>
    <?php $nombre = trim($fila['nombre'] . ' ' . $fila['apellido']); $activo = $fila['estado'] === 'activo'; ?>
    <tr>
        <td><strong><?= usuarios_escape($nombre ?: 'Sin nombre') ?></strong><?php if ((string)$fila['id_usuario'] === (string)$_SESSION['id_usuario']): ?><span class="users-self">Tu cuenta</span><?php endif; ?><span class="users-secondary"><?= usuarios_escape($fila['tipo_documento']) ?> · <?= usuarios_escape($fila['id_usuario']) ?></span></td>
        <td>
            <?php if (!empty($fila['telefono'])): ?><span><?= usuarios_escape($fila['telefono']) ?></span><?php endif; ?>
            <?php if (!empty($fila['correo'])): ?><span class="users-secondary"><?= usuarios_escape($fila['correo']) ?></span><?php endif; ?>
            <?php if (empty($fila['telefono']) && empty($fila['correo'])): ?><span class="users-secondary">Sin datos de contacto</span><?php endif; ?>
        </td>
        <td><span><?= usuarios_escape($fila['usuario']) ?></span><span class="users-secondary"><?= usuarios_escape(ucwords(str_replace(',', ', ', $fila['rol']))) ?></span></td>
        <td><span class="users-badge <?= $activo ? 'is-active' : 'is-inactive' ?>"><?= $activo ? 'Activo' : 'Inactivo' ?></span></td>
        <td><div class="users-row-actions">
            <a class="users-button users-button-small" href="<?= usuarios_escape(usuarios_page_url($contexto, ['Actualizar'=>$fila['id_usuario']])) ?>#usuario-form" aria-label="Editar a <?= usuarios_escape($nombre) ?>">Editar</a>
            <?php if ((string)$fila['id_usuario'] !== (string)$_SESSION['id_usuario']): ?>
            <form method="post" action="<?= usuarios_escape(usuarios_page_url($contexto)) ?>" data-state-form data-name="<?= usuarios_escape($nombre) ?>">
                <input type="hidden" name="csrf_token" value="<?= usuarios_escape(guagua_csrf_token()) ?>">
                <input type="hidden" name="action" value="set_state"><input type="hidden" name="id" value="<?= usuarios_escape($fila['id_usuario']) ?>">
                <input type="hidden" name="estado" value="<?= $activo ? 'inactivo' : 'activo' ?>">
                <button class="users-button users-button-small users-button-quiet" type="submit"><?= $activo ? 'Desactivar' : 'Activar' ?></button>
            </form>
            <?php endif; ?>
        </div></td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php if ($listado['pages'] > 1): ?>
<nav class="users-pagination" aria-label="Páginas del directorio">
    <span>Página <?= $listado['page'] ?> de <?= $listado['pages'] ?></span>
    <div>
        <?php if ($listado['page'] > 1): ?><a class="users-button users-button-small" data-page="<?= $listado['page'] - 1 ?>" href="<?= usuarios_escape(usuarios_page_url($contexto, ['page'=>$listado['page'] - 1])) ?>">Anterior</a><?php endif; ?>
        <?php for ($numero = max(1, $listado['page'] - 2); $numero <= min($listado['pages'], $listado['page'] + 2); $numero++): ?>
        <a class="users-button users-button-small <?= $numero === $listado['page'] ? 'is-current' : '' ?>" data-page="<?= $numero ?>" <?= $numero === $listado['page'] ? 'aria-current="page"' : '' ?> href="<?= usuarios_escape(usuarios_page_url($contexto, ['page'=>$numero])) ?>"><?= $numero ?></a>
        <?php endfor; ?>
        <?php if ($listado['page'] < $listado['pages']): ?><a class="users-button users-button-small" data-page="<?= $listado['page'] + 1 ?>" href="<?= usuarios_escape(usuarios_page_url($contexto, ['page'=>$listado['page'] + 1])) ?>">Siguiente</a><?php endif; ?>
    </div>
</nav>
<?php endif; ?>
<?php endif; ?>
