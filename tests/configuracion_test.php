<?php
/** Verifica el registro de navegación y sus restricciones sin acceder a la base. */
$root = dirname(__DIR__);
$items = require $root . '/comun/configuracion_opciones.php';
foreach ($items as $id => $item) {
    if (!is_file($root . '/' . $item['ruta'])) throw new RuntimeException("Destino inexistente: $id");
    if (str_contains($item['ruta'], '..') || preg_match('#^[a-z]+:|^/#i', $item['ruta'])) throw new RuntimeException("Destino no interno: $id");
    if (empty($item['roles'])) throw new RuntimeException("Opción sin permisos: $id");
    if (in_array('estudiante', $item['roles'], true) || in_array('acudiente', $item['roles'], true)) throw new RuntimeException("Configuración general expuesta: $id");
}
foreach (['tarjetas', 'importacion'] as $id) {
    if ($items[$id]['roles'] !== ['admin']) throw new RuntimeException("Opción administrativa expuesta: $id");
}
if ($items['copias']['roles'] !== ['admin', 'docente']) throw new RuntimeException('La descarga debe estar disponible para administradores y docentes.');
$menu = file_get_contents($root . '/comun/menu.php');
if (!str_contains($menu, '/comun/configuracion.php') || str_contains($menu, 'data-id="config" class="dropdown"')) throw new RuntimeException('CONFIG no navega directamente al centro.');
if (!str_contains($menu, "id !== 'config' && hiddenItems.includes(id)")) throw new RuntimeException('CONFIG puede quedar oculto.');
echo "Configuración: destinos internos, permisos y acceso de CONFIG correctos.\n";
