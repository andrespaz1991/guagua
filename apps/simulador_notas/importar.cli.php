<?php
// Configuración inicial local; nunca recibe solicitudes HTTP.
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404); exit; }
require __DIR__.'/lib.php';
if(empty($argv[1])) { fwrite(STDERR,"Uso: php importar.cli.php archivo.xls [--menu]\n");exit(1); }
$data=sn_import($argv[1],basename($argv[1]));
sn_save(__DIR__.'/data/inicial.php',$data);
echo count($data['records'])." registros; ".count(array_unique(array_column($data['records'],'student')))." estudiantes; ".count(array_unique(array_column($data['records'],'subject')))." materias.\n";
if(in_array('--menu',$argv,true)) {
 require_once __DIR__.'/../../comun/database.php';
 $db=guagua_db_request();
 $url='apps/simulador_notas/index.php';
 $existing=$db->query("SELECT menu_item_id FROM menu_items2 WHERE menu_url LIKE '%apps/simulador_notas/index.php%' LIMIT 1")->fetch_assoc();
 if(!$existing){
   $stmt=$db->prepare("INSERT INTO menu_items2 (menu_item_name,menu_description,menu_url,menu_parent_id,url_target,categoria,icono,fav) VALUES ('Simulador de notas','Consulta P1, P2 y P3 y simula la nota final con el cuarto periodo.',?,NULL,'_self','Académico','fa-solid fa-chart-line',0)");
   $stmt->bind_param('s',$url);$stmt->execute();
   echo 'Menú creado: '.$stmt->insert_id."\n";
 }else echo 'Menú existente: '.$existing['menu_item_id']."\n";
}
