<?php foreach ($campos as $campo => $config): ?>
<div>
    <label for="users-<?= $campo ?>"><?= usuarios_escape($config[0]) ?></label>
    <?php if ($config[1] === 'select'): ?>
    <select id="users-<?= $campo ?>" name="<?= $campo ?>">
        <?php foreach ($config[2] as $valor => $etiqueta): ?><option value="<?= usuarios_escape($valor) ?>" <?= (string)$datos_editar[$campo] === (string)$valor ? 'selected' : '' ?>><?= usuarios_escape($etiqueta) ?></option><?php endforeach; ?>
    </select>
    <?php else: ?>
    <input id="users-<?= $campo ?>" type="<?= $config[1] ?>" name="<?= $campo ?>" value="<?= usuarios_escape($datos_editar[$campo]) ?>" <?= in_array($campo, ['id_usuario','nombre','apellido','usuario'], true) ? 'required' : '' ?> <?= $campo === 'id_usuario' ? 'inputmode="numeric" pattern="[0-9]{1,20}" maxlength="20" title="Entre 1 y 20 dígitos"' : '' ?> <?= $campo === 'usuario' ? 'autocomplete="off"' : '' ?>>
    <?php endif; ?>
</div>
<?php endforeach; ?>
