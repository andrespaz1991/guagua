<?php
require_once __DIR__ . '/seguridad.php';
guagua_iniciar_sesion_segura();
require_once __DIR__ . '/config.php';
if (empty($_SESSION['id_usuario'])) {
    header('Location: ' . SGA_USUARIO_URL . '/login.php');
    exit;
}
$role = (string)($_SESSION['rol'] ?? '');
$all = require __DIR__ . '/configuracion_opciones.php';
$options = array_filter($all, static fn($item) => in_array($role, $item['roles'], true));
$requestedSection = $_GET['seccion'] ?? '';
$section = is_string($requestedSection) ? $requestedSection : '';
if ($section !== '' && !isset($options[$section])) {
    http_response_code(403);
    exit('Esta opción de configuración no está disponible para tu rol.');
}
function config_e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function config_item_url(array $item, bool $frame = false): string {
    $params = [];
    if (isset($item['vista'])) $params['vista'] = $item['vista'];
    if ($frame && !empty($item['embebido'])) $params['guagua_embedded'] = '1';
    return SGA_URL . '/' . $item['ruta'] . ($params ? '?' . http_build_query($params) : '');
}
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/cabecera_modulo.php';
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Configuración de Guagua</title>
<?php guagua_menu_estilos(); ?>
<link rel="stylesheet" href="<?= config_e(SGA_COMUN_URL) ?>/css/configuracion.css">
</head><body>
<?php require __DIR__ . '/menu.php'; ?>
<main class="guagua-settings">
<header class="settings-heading"><div><span class="settings-eyebrow">GUAGUA / ADMINISTRACIÓN</span><h1>Centro de configuración</h1><p>Encuentra y administra las opciones de la plataforma en un solo lugar.</p></div><div class="settings-actions"><a class="settings-button" href="<?= config_e(SGA_URL) ?>/index.php">Volver al inicio</a><a class="settings-button" href="<?= config_e(SGA_USUARIO_URL) ?>/login.php?logout">Cerrar sesión</a></div></header>
<?php if ($section !== ''): $item=$options[$section]; $frameUrl=config_item_url($item, true); ?>
<section class="settings-workspace"><div class="settings-workspace-heading"><div><h2><?= config_e($item['titulo']) ?></h2><p><?= config_e($item['descripcion']) ?></p></div><div class="settings-actions"><a class="settings-button" href="configuracion.php">Todas las opciones</a><a class="settings-button" href="<?= config_e(config_item_url($item)) ?>" target="_blank" rel="noopener">Abrir en otra pestaña</a></div></div><iframe title="<?= config_e($item['titulo']) ?>" src="<?= config_e($frameUrl) ?>"></iframe></section>
<?php else: ?>
<section class="settings-personal"><div><h2>Mi menú en este navegador</h2><p>Recupera los iconos que ocultaste y restablece su orden. Los colores y tamaños se editan en las opciones de diseño.</p></div><button type="button" class="settings-button" id="settings-restore">Restaurar iconos y orden</button><p id="settings-status" role="status" aria-live="polite"></p></section>
<?php if ($options): ?>
<div class="settings-search"><label for="settings-search">Buscar una configuración</label><input type="search" id="settings-search" placeholder="Ej.: iconos, colores, períodos, usuarios…" autocomplete="off"><span id="settings-count" role="status" aria-live="polite"></span></div>
<?php $groups=[]; foreach($options as $id=>$item) $groups[$item['grupo']][$id]=$item; foreach($groups as $group=>$items): ?>
<section class="settings-group"><h2><?= config_e($group) ?></h2><div class="settings-grid">
<?php foreach($items as $id=>$item): ?>
<a class="settings-card" href="configuracion.php?seccion=<?= config_e($id) ?>"><span class="settings-icon" aria-hidden="true"><i class="fa-solid fa-<?= config_e($item['icono']) ?>"></i></span><h3><?= config_e($item['titulo']) ?></h3><p><?= config_e($item['descripcion']) ?></p><span class="settings-open">Configurar →</span></a>
<?php endforeach; ?></div></section>
<?php endforeach; ?>
<p id="settings-empty" hidden>No hay opciones que coincidan con la búsqueda.</p>
<aside class="settings-note"><h2>Conexión y servidor</h2><p>La conexión a la base de datos se configura centralmente en el archivo privado <code>.env</code> del servidor. Las opciones técnicas y credenciales requieren acceso al servidor; no se muestran en este panel.</p></aside>
<?php else: ?><p class="settings-note">Tu rol dispone de las preferencias personales del menú. La configuración general está disponible para docentes y administradores.</p><?php endif; ?>
<?php endif; ?>
</main><script src="<?= config_e(SGA_COMUN_URL) ?>/js/configuracion.js"></script></body></html>
