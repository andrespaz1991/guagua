<?php
// Ruta compatible; consultas y vista compartidas con los demás catálogos.
$catalog = (require dirname(__DIR__,2) . '/comun/catalog_definitions.php')['eventos'];
require dirname(__DIR__,2) . '/comun/catalog_page.php';
