<?php
/** Compatibilidad: la gestión del menú ahora forma parte del módulo Apps. */
require_once __DIR__ . '/seguridad.php';
require_once __DIR__ . '/tienda_permisos.php';
guagua_exigir_rol(guagua_tienda_roles_gestion());
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    guagua_error_acceso(410, 'La gestión del menú se trasladó a Aplicaciones. Abre tienda.php?vista=menu y repite allí la operación.');
}
$params = ['vista'=>'menu'];
$id = $_GET['Actualizar'] ?? null;
if (is_string($id) && ctype_digit($id) && (int)$id > 0) $params['editar'] = (int)$id;
header('Location: tienda.php?' . http_build_query($params), true, 302);
exit;
