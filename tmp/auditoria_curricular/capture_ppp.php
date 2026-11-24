<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../../comun/database.php';
$db=GuaguaDatabase::pdo();
$out=__DIR__.'/../../output/auditoria-emprendimiento-ppp-2026-11-01';
if(!is_dir($out))mkdir($out,0777,true);
if(is_file($out.'/respaldo-anterior.json'))throw new RuntimeException('El respaldo ya existe; no se sobrescribe.');
$keys=['grado'=>'id_grado','materia_oficial'=>'id_materia','estandar'=>'id_estandar','dba'=>'id_dba','eje_tematico'=>'id_eje_tematico','evidencia_de_aprendizaje'=>'id_evidencia_aprendizaje','periodo'=>'id_periodo'];
$snapshot=[];foreach($keys as $t=>$k)$snapshot[$t]=$db->query("SELECT * FROM `$t` ORDER BY `$k`")->fetchAll(PDO::FETCH_ASSOC);
file_put_contents($out.'/respaldo-anterior.json',json_encode($snapshot,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
echo "Respaldo previo guardado.\n";
$rows=$db->query('SELECT e.grado,e.id_periodo,e.nombre_estandar,e.descripcion_estandar FROM estandar e WHERE e.id_materia_oficial=4 AND e.grado BETWEEN 6 AND 9 ORDER BY e.grado,e.id_periodo')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['educacion_fisica'=>count($rows),'fuentes_malla'=>count(array_filter($rows,fn($r)=>strpos($r['descripcion_estandar'],'Educación física secundaria 6-11.docx')!==false))],JSON_UNESCAPED_UNICODE);
