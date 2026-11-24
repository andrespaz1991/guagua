<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__,3).'/comun/database.php';
$db=guagua_db_request();
$url='apps/reporte_actividades/';
$query=$db->prepare('SELECT menu_item_id FROM menu_items2 WHERE menu_url IN (?, ?) LIMIT 1');
$legacyUrl='../'.$url;
$query->bind_param('ss',$url,$legacyUrl); $query->execute();
$existing=$query->get_result()->fetch_assoc(); $query->close();
if ($existing) { echo 'El módulo ya está registrado: #'.$existing['menu_item_id']."\n"; exit; }
$name='Reporte de actividades';
$description='Seguimiento de actividades y notas desde los Excel de valoraciones. Filtros por materia, grado y estudiante, alertas, matriz, impresión y CSV.';
$target='_self'; $category='Reportes'; $icon='📊';
$stmt=$db->prepare('INSERT INTO menu_items2 (menu_item_name,menu_description,menu_url,menu_parent_id,url_target,categoria,icono,fav,is_cabecera) VALUES (?,?,?,NULL,?,?,?,0,0)');
$stmt->bind_param('ssssss',$name,$description,$url,$target,$category,$icon);
$stmt->execute();
echo 'Reporte de actividades registrado en el menú: #'.$db->insert_id."\n";
