<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../../../comun/database.php';
require_once __DIR__.'/../database.php';
daily_planner_database();
$db = guagua_db_pdo();
$url = 'apps/plan_diario/';
$query = $db->prepare('SELECT menu_item_id FROM menu_items2 WHERE menu_url IN (?,?) LIMIT 1');
$query->execute([$url,'../'.$url]);
if ($query->fetchColumn()) { echo "Mi plan diario ya está disponible en el menú.\n"; exit; }
$query = $db->prepare('INSERT INTO menu_items2(menu_item_name,menu_description,menu_url,menu_parent_id,url_target,categoria,icono,fav,is_cabecera) VALUES(?,?,?,NULL,?,?,?,0,0)');
$query->execute(['Mi plan diario','Tareas diarias del colegio con categorías, colores e historial.',$url,'_self','Productividad','☑️']);
echo "Mi plan diario registrado en Apps y en el menú.\n";
