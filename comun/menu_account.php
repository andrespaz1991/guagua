<?php
/** Zona de cuenta común a todas las pantallas que usan el menú. */
$nombreCuenta = trim((string)($_SESSION['nombre'] ?? '') . ' ' . (string)($_SESSION['apellido'] ?? ''));
if ($nombreCuenta === '' && $persona) $nombreCuenta = trim($persona->nombre . ' ' . $persona->apellido);
$nombreCuenta = $nombreCuenta ?: (string)($_SESSION['usuario'] ?? 'Mi cuenta');
$etiquetaRol = ['admin'=>'Administrador','directivo'=>'Directivo','docente'=>'Docente','estudiante'=>'Estudiante','acudiente'=>'Acudiente'][$rol_actual] ?? ucfirst($rol_actual);
$escapeCuenta = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$urlLoginCuenta = rtrim(SGA_USUARIO_URL, '/') . '/login.php';
$fotoCuenta = guagua_persona_foto($persona ? $persona->foto : '', $persona ? ($persona->genero ?? '') : '');
?>
<?php if (in_array($rol_actual, ['admin', 'docente'], true)): ?>
<li class="guagua-backup-item">
    <a class="guagua-backup-link" href="<?= $escapeCuenta(rtrim(SGA_URL, '/') . '/reportes/descargar_guagua.php') ?>" target="_top" aria-label="Descargar Guagua en ZIP" title="Descargar Guagua: archivos y bases de datos en ZIP">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="5" rx="1.5"/><path d="M5 8v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8M10 12h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
</li>
<?php endif; ?>
<li class="guagua-account-item">
    <div class="guagua-account" aria-label="Cuenta actual">
        <img class="guagua-account-avatar" src="<?= $escapeCuenta(rtrim(READFILE_URL, '/') . '/foto/' . rawurlencode($fotoCuenta)) ?>" alt="" width="38" height="38">
        <div class="guagua-account-identity">
            <span class="guagua-account-name" title="<?= $escapeCuenta($nombreCuenta) ?>"><?= $escapeCuenta($nombreCuenta) ?></span>
            <a class="guagua-account-role" href="<?= $escapeCuenta($urlLoginCuenta) ?>" target="_top" aria-label="Cambiar perfil. Perfil actual: <?= $escapeCuenta($etiquetaRol) ?>" title="Cambiar perfil">
                <span class="guagua-account-dot" aria-hidden="true"></span><?= $escapeCuenta($etiquetaRol) ?>
                <svg width="10" height="10" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 5h11m-3-3 3 3-3 3M14 11H3m3-3-3 3 3 3"/></svg>
            </a>
        </div>
        <a class="guagua-account-exit" href="<?= $escapeCuenta($urlLoginCuenta . '?logout=1') ?>" target="_top" aria-label="Cerrar sesión en Guagua" title="Cerrar sesión">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M9 4H5v16h4M14 8l4 4-4 4M9 12h11" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Cerrar sesión</span>
        </a>
    </div>
</li>
