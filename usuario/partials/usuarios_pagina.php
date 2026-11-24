<?php $textos = usuarios_directorio_textos($contexto['u']); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= usuarios_escape($titulo) ?> · Guagua</title>
    <link rel="stylesheet" href="assets/usuarios.css?v=1">
    <script src="assets/usuarios.js?v=1" defer></script>
</head>
<body class="users-body">
<main class="users-app">
    <header class="users-header">
        <div><p class="users-eyebrow">Comunidad educativa / Directorio</p><h1><?= usuarios_escape($titulo) ?></h1><p><?= usuarios_escape($textos['descripcion']) ?></p></div>
        <a href="<?= usuarios_escape(usuarios_page_url($contexto, ['nuevo'=>1])) ?>#usuario-form" class="users-button users-button-primary" data-open-form>+ <?= usuarios_escape($textos['nuevo']) ?></a>
    </header>
    <?php if (isset($politica) && !$politica['restricted']): ?>
    <nav class="users-directories" aria-label="Directorios de usuarios">
        <?php foreach ([''=>'Todos'] + usuarios_roles() as $rol_directorio => $etiqueta_directorio): ?>
        <a href="<?= usuarios_escape(usuarios_page_url($contexto, ['u'=>$rol_directorio,'buscar'=>'','estado_filtro'=>'','page'=>1])) ?>" <?= $contexto['u'] === $rol_directorio ? 'aria-current="page"' : '' ?>><?= usuarios_escape($etiqueta_directorio) ?></a>
        <?php endforeach; ?>
    </nav>
    <?php endif; ?>
    <?php if ($mensaje !== ''): ?><div class="users-notice" role="status"><?= usuarios_escape($mensaje) ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div class="users-notice users-error" role="alert"><strong>Revise la operación</strong><p><?= usuarios_escape($error) ?></p></div><?php endif; ?>

    <section class="users-panel" aria-labelledby="directorio-titulo">
        <div class="users-panel-heading"><div><h2 id="directorio-titulo">Directorio de <?= usuarios_escape(mb_strtolower($titulo)) ?></h2><p>Busque por nombre completo, documento, usuario, correo o teléfono.</p></div><span class="users-scope"><?= usuarios_escape($titulo) ?></span></div>
        <form method="get" action="usuario.php" class="users-filters" id="users-search">
            <?php foreach (['u','guagua_embedded'] as $parametro): ?><?php if ($contexto[$parametro] !== ''): ?><input type="hidden" name="<?= $parametro ?>" value="<?= usuarios_escape($contexto[$parametro]) ?>"><?php endif; ?><?php endforeach; ?>
            <div class="users-search-field"><label for="users-query">Buscar en el directorio</label><input id="users-query" type="search" name="buscar" maxlength="150" placeholder="Nombre, documento o contacto…" value="<?= usuarios_escape($contexto['buscar']) ?>" autocomplete="off"></div>
            <div><label for="users-state">Estado de la cuenta</label><select id="users-state" name="estado_filtro"><option value="">Todos los estados</option><option value="activo" <?= $contexto['estado_filtro'] === 'activo' ? 'selected' : '' ?>>Activos</option><option value="inactivo" <?= $contexto['estado_filtro'] === 'inactivo' ? 'selected' : '' ?>>Inactivos</option></select></div>
            <button type="submit" class="users-button">Buscar</button>
            <a class="users-clear" href="<?= usuarios_escape(usuarios_page_url($contexto, ['buscar'=>'','estado_filtro'=>'','page'=>1])) ?>">Limpiar</a>
        </form>
        <p class="users-search-status" id="users-search-status" role="status" aria-live="polite"></p>
        <div id="users-results" aria-busy="false"><?php require __DIR__ . '/usuarios_tabla.php'; ?></div>
    </section>

    <details class="users-panel users-editor" id="usuario-form" <?= $modo_edicion || ($error !== '' && ($_POST['action'] ?? '') === 'save') || isset($_GET['nuevo']) ? 'open' : '' ?>>
        <summary><span><?= usuarios_escape($modo_edicion ? $textos['editar'] : $textos['crear']) ?></span><span class="users-secondary"><?= $modo_edicion ? usuarios_escape(trim($datos_editar['nombre'] . ' ' . $datos_editar['apellido'])) : 'Datos personales, contacto y acceso' ?></span></summary>
        <form method="post" action="<?= usuarios_escape(usuarios_page_url($contexto)) ?>" class="users-edit-form" id="users-edit-form">
            <input type="hidden" name="csrf_token" value="<?= usuarios_escape(guagua_csrf_token()) ?>"><input type="hidden" name="action" value="save">
            <?php if ($modo_edicion): ?><input type="hidden" name="id_usuario_original" value="<?= usuarios_escape($original) ?>"><?php endif; ?>
            <p class="users-form-hint">Los campos marcados con * son obligatorios.<?= $modo_edicion ? ' La contraseña actual se conserva.' : '' ?></p>
            <fieldset><legend>Información personal</legend><div class="users-form-grid">
            <?php
            $campos = [
                'tipo_documento'=>['Tipo de documento','select',['CC'=>'Cédula de ciudadanía','TI'=>'Tarjeta de identidad','CE'=>'Cédula de extranjería','RC'=>'Registro civil']],
                'id_usuario'=>['Documento *','text'], 'nombre'=>['Nombres *','text'], 'apellido'=>['Apellidos *','text'],
                'genero'=>['Género','select',[''=>'Seleccione…','f'=>'Femenino','m'=>'Masculino']], 'fecha_nacimiento'=>['Fecha de nacimiento','date'],
            ];
            require __DIR__ . '/usuarios_campos.php';
            ?>
            </div></fieldset>
            <fieldset><legend>Datos de contacto</legend><div class="users-form-grid">
            <?php $campos = ['telefono'=>['Teléfono','tel'], 'correo'=>['Correo electrónico','email'], 'direccion'=>['Dirección','text']]; require __DIR__ . '/usuarios_campos.php'; ?>
            </div></fieldset>
            <fieldset><legend>Acceso a la plataforma</legend><div class="users-form-grid">
            <?php
            $opciones_rol = usuarios_roles();
            if (strpos((string)$datos_editar['rol'], ',') !== false) $opciones_rol[$datos_editar['rol']] = ucwords(str_replace(',', ', ', $datos_editar['rol']));
            if ($contexto['u'] !== '') $opciones_rol = array_filter($opciones_rol, static fn($key) => usuarios_has_role((string)$key, $contexto['u']), ARRAY_FILTER_USE_KEY);
            $campos = ['usuario'=>['Nombre de usuario *','text'], 'rol'=>['Rol','select',$opciones_rol], 'estado'=>['Estado','select',['activo'=>'Activo','inactivo'=>'Inactivo']]];
            require __DIR__ . '/usuarios_campos.php';
            ?>
            <?php if (!$modo_edicion): ?>
            <div><label for="users-password">Contraseña inicial *</label><input id="users-password" type="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password" aria-describedby="users-password-help"><small id="users-password-help">Entre 8 y 72 bytes. No se conserva si falla el guardado.</small></div>
            <?php endif; ?>
            </div></fieldset>
            <details class="users-additional"><summary>Información adicional</summary><div class="users-form-grid">
            <?php
            $sangre = array_combine(['O+','O-','A+','A-','B+','B-','AB+','AB-'], ['O+','O-','A+','A-','B+','B-','AB+','AB-']);
            $campos = ['tipo_sangre'=>['Tipo de sangre','select',[''=>'Sin especificar'] + $sangre], 'fecha_retiro'=>['Fecha de retiro','date']];
            if ($modo_edicion && !($politica['restricted'] ?? false)) $campos['mascota'] = ['Mascota institucional','select',['NO'=>'No','SI'=>'Sí']];
            require __DIR__ . '/usuarios_campos.php';
            ?>
            <?php if (!$modo_edicion || ($politica['restricted'] ?? false)): ?><input type="hidden" name="mascota" value="<?= usuarios_escape($modo_edicion ? $datos_editar['mascota'] : 'NO') ?>"><?php endif; ?>
            <div class="users-span-all"><label for="users-observaciones">Observaciones</label><textarea id="users-observaciones" name="observaciones" rows="3"><?= usuarios_escape($datos_editar['observaciones']) ?></textarea></div>
            </div></details>
            <footer class="users-form-actions"><a href="<?= usuarios_escape(usuarios_page_url($contexto)) ?>" class="users-button">Cancelar</a><button type="submit" class="users-button users-button-primary"><?= usuarios_escape($modo_edicion ? 'Guardar cambios' : $textos['crear']) ?></button></footer>
        </form>
        <?php if ($modo_edicion && ($politica['can_delete'] ?? false) && $original !== (string)$_SESSION['id_usuario']): ?>
        <details class="users-delete-panel">
            <summary>Eliminar este perfil</summary>
            <p>Esta operación es permanente. Para suspender el acceso conservando los datos y vínculos, cambie el estado a inactivo.</p>
            <form method="post" action="<?= usuarios_escape(usuarios_page_url($contexto)) ?>" data-delete-form data-name="<?= usuarios_escape(trim($datos_editar['nombre'] . ' ' . $datos_editar['apellido'])) ?>">
                <input type="hidden" name="csrf_token" value="<?= usuarios_escape(guagua_csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= usuarios_escape($original) ?>">
                <button type="submit" class="users-button users-button-danger">Eliminar permanentemente</button>
            </form>
        </details>
        <?php endif; ?>
    </details>
</main>
</body></html>
