<?php
/** Usa una tabla TEMPORARY que oculta la real solo en esta conexión. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/database.php';
require_once dirname(__DIR__) . '/comun/lib/GuaguaAppStore.php';
require_once dirname(__DIR__) . '/comun/lib/GuaguaMenuRepository.php';
require_once dirname(__DIR__) . '/comun/tienda_rutas.php';

$db = guagua_db_mysqli();
$fixture = dirname(__DIR__) . '/tmp/tienda-menu-crud-' . bin2hex(random_bytes(8));
mkdir($fixture . '/apps/Leccion/data', 0700, true);
mkdir($fixture . '/apps/Otra App', 0700, true);
file_put_contents($fixture . '/apps/Leccion/index.php', '<?php echo "Prueba";');
file_put_contents($fixture . '/apps/Leccion/data/ejemplo.json', '{"prueba":true}');
file_put_contents($fixture . '/apps/Otra App/index.html', 'Prueba');
$checks = 0;
function crud_check(bool $ok, string $message): void {
    global $checks;
    if (!$ok) throw new RuntimeException($message);
    $checks++;
}
function crud_reject(callable $action, string $message): void {
    try { $action(); } catch (RuntimeException | InvalidArgumentException $error) { crud_check(true, $message); return; }
    throw new RuntimeException($message);
}
try {
    $realCount = (int)$db->query('SELECT COUNT(*) FROM menu_items2')->fetch_row()[0];
    if ($realCount === 0) throw new RuntimeException('Esta comprobación requiere accesos existentes para verificar el aislamiento de la tabla temporal.');
    if (!$db->query('CREATE TEMPORARY TABLE menu_items2 (menu_item_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, menu_item_name VARCHAR(255) NOT NULL, menu_description MEDIUMTEXT NOT NULL, menu_url VARCHAR(255) NOT NULL, menu_parent_id INT NULL, url_target VARCHAR(255) NOT NULL, categoria VARCHAR(255) NOT NULL, icono VARCHAR(25) NOT NULL, fav INT NOT NULL DEFAULT 0, is_cabecera TINYINT NOT NULL DEFAULT 0) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4')) throw new RuntimeException('No se pudo crear la tabla temporal: ' . $db->error);
    if ((int)$db->query('SELECT COUNT(*) FROM menu_items2')->fetch_row()[0] !== 0) throw new RuntimeException('Se canceló la prueba: no hay una tabla temporal vacía y aislada.');
    $menu = new GuaguaMenuRepository($db);
    $store = new GuaguaAppStore($fixture);
    $item = ['menu_item_name'=>"Lección de 'prueba' 🧩", 'menu_description'=>'Descripción con acentos y "comillas".', 'menu_url'=>'apps/Leccion/index.php', 'categoria'=>'Educación', 'icono'=>'📚', 'fav'=>false, 'is_cabecera'=>false, 'url_target'=>'_self'];
    $id = $menu->save($item);
    $rows = $menu->items();
    crud_check(count($rows) === 1 && $rows[0]['menu_item_id'] === $id, 'No se creó el acceso con su identificador.');
    crud_check($rows[0]['menu_item_name'] === $item['menu_item_name'] && $rows[0]['menu_description'] === $item['menu_description'], 'Se alteraron textos UTF-8 o comillas.');
    crud_check($rows[0]['menu_parent_id'] === null && !$rows[0]['fav'], 'Los valores predeterminados no se conservaron.');
    $item['menu_item_name'] = 'Lección actualizada';
    $menu->save($item, $id);
    crud_check($menu->items()[0]['menu_item_name'] === 'Lección actualizada', 'La edición no actualizó el mismo acceso.');
    $menu->setFlag($id, 'is_cabecera', true);
    crud_check($menu->items()[0]['fav'] && $menu->items()[0]['is_cabecera'], 'Anclar debe marcar favorita.');
    $apps = array_column($store->catalog($menu->items()), null, 'carpeta');
    crud_check($apps['Leccion']['menu_id'] === $id && $apps['Leccion']['favorita'], 'El catálogo no recibió los cambios del menú.');
    $menu->setFlag($id, 'fav', false);
    crud_check(!$menu->items()[0]['fav'] && !$menu->items()[0]['is_cabecera'], 'Quitar favorita debe desanclar.');
    $child = $item; $child['menu_item_name'] = 'Acceso dependiente'; $child['menu_parent_id'] = $id;
    $childId = $menu->save($child);
    crud_reject(fn() => $menu->delete($id), 'No puede quitar un acceso con hijos.');
    $cycle = $item; $cycle['menu_parent_id'] = $childId;
    crud_reject(fn() => $menu->save($cycle, $id), 'No puede crear un ciclo.');
    crud_check(count($menu->items()) === 2 && $menu->items()[0]['menu_parent_id'] === null, 'Fallar debe revertir completamente la edición.');
    foreach (['javascript:alert(1)', 'data:text/html,prueba', "https://example.com/\r\nrecurso", 'https://user:password@example.com/'] as $url) {
        $invalid = $item; $invalid['menu_url'] = $url;
        crud_reject(fn() => $menu->save($invalid), 'Debe rechazar enlaces inseguros.');
    }
    $invalid = $item; $invalid['url_target'] = '_parent';
    crud_reject(fn() => $menu->save($invalid), 'No puede cambiar el destino a un valor no admitido.');
    $invalid = $item; $invalid['icono'] = str_repeat('x', 26);
    crud_reject(fn() => $menu->save($invalid), 'No puede truncar silenciosamente un icono.');
    crud_reject(fn() => $menu->setFlag($id, 'menu_url', true), 'No puede inyectar una columna.');
    crud_reject(fn() => $menu->setFlag($id, 'fav', '2'), 'No puede guardar estados arbitrarios.');
    $otherId = $apps['Otra App']['id'];
    $countBefore = count($menu->items());
    crud_reject(fn() => $menu->sync([$otherId, str_repeat('a', 24)], $store), 'Una selección inválida debe revertir toda la sincronización.');
    crud_check(count($menu->items()) === $countBefore, 'La sincronización fallida dejó accesos parciales.');
    crud_check($menu->sync([$otherId, $otherId], $store) === 1, 'Debe añadir cada app una sola vez.');
    crud_check($menu->sync([$otherId], $store) === 0, 'Repetir la sincronización no debe duplicar accesos.');
    $newApps = array_column($store->catalog($menu->items()), null, 'carpeta');
    crud_check(!empty($newApps['Otra App']['menu_id']) && !$newApps['Otra App']['favorita'], 'La sincronización no respetó los favoritos predeterminados.');
    $menu->delete($childId); $menu->delete($id);
    crud_check(is_file($fixture . '/apps/Leccion/index.php') && is_file($fixture . '/apps/Leccion/data/ejemplo.json'), 'Quitar un acceso no puede retirar archivos o datos de la app.');
    crud_reject(fn() => $menu->delete($id), 'No puede volver a quitar un acceso inexistente.');
    foreach (['comun/gestor_menu.php', '../comun/gestor_menu.php?Actualizar=1', '/guagua/comun/gestor_menu.php'] as $url) crud_check(guagua_tienda_es_gestor_antiguo($url), 'No se reconoció una ruta antigua.');
    foreach (['https://example.com/comun/gestor_menu.php', 'comun/tienda.php', 'comun/gestor_menu.php.extra'] as $url) crud_check(!guagua_tienda_es_gestor_antiguo($url), 'Se confundió otra ruta con el gestor antiguo.');
    echo "OK: $checks comprobaciones de edición, favoritos, cabecera, relaciones, seguridad, sincronización y rutas. Solo una tabla temporal y apps ficticias.\n";
} finally {
    $db->query('DROP TEMPORARY TABLE IF EXISTS menu_items2'); $db->close();
    unlink($fixture . '/apps/Leccion/index.php'); unlink($fixture . '/apps/Leccion/data/ejemplo.json'); unlink($fixture . '/apps/Otra App/index.html');
    rmdir($fixture . '/apps/Leccion/data'); rmdir($fixture . '/apps/Leccion'); rmdir($fixture . '/apps/Otra App'); rmdir($fixture . '/apps'); rmdir($fixture);
}
