<?php
/** Adaptador compatible: conserva $mysqli y las funciones usadas por el módulo. */
require_once __DIR__ . '/../comun/config.php';
require_once __DIR__ . '/../comun/funciones.php';
$mysqli = guagua_db_mysqli();
