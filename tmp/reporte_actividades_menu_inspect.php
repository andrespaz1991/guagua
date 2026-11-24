<?php
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__).'/comun/database.php';
$db=guagua_db_request();
foreach (['menu_items2','permisos','menu_permisos'] as $table) {
    try { $res=$db->query('SHOW COLUMNS FROM `'.$table.'`'); echo $table.': '.json_encode($res->fetch_all(MYSQLI_ASSOC))."\n"; } catch (Throwable $e) { echo $table.': no disponible'."\n"; }
}
$res=$db->query("SELECT * FROM menu_items2 WHERE menu_url LIKE '%apps/notas%' OR menu_url LIKE '%balance_anual%' OR menu_url LIKE '%reporte_actividades%'");
echo json_encode($res->fetch_all(MYSQLI_ASSOC),JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
