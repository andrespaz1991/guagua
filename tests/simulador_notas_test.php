<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
require dirname(__DIR__).'/apps/simulador_notas/lib.php';
function sn_check($condition,$message) { if(!$condition) throw new RuntimeException($message); }
$rows=[['Año','Grupo','Código','Estudiante','Materia','P1','P2','P3'],[2026,'6','001','Estudiante','Matemáticas','2,5',null,'3,9']];
$parsed=sn_parse_rows($rows);
sn_check(count($parsed)===1,'Importa tabla');
sn_check($parsed[0]['p1']===2.5 && $parsed[0]['p2']===null && $parsed[0]['p4']===null,'Preserva decimales y faltantes');
$bad=$rows;$bad[1][5]='6';
try {sn_parse_rows($bad);throw new LogicException('Aceptó nota inválida');}catch(RuntimeException $e){sn_check(str_contains($e->getMessage(),'Fila 2'),'Indica fila inválida');}
$bad=$rows;$bad[]=$rows[1];$bad[2][5]='4';
try {sn_parse_rows($bad);throw new LogicException('Aceptó duplicado contradictorio');}catch(RuntimeException $e){sn_check(str_contains($e->getMessage(),'contradictorias'),'Detecta contradicción');}
$data=require dirname(__DIR__).'/apps/simulador_notas/data/inicial.php';
sn_check(count($data['records'])===218,'Cantidad de registros iniciales');
$first=$data['records'][0];
sn_check($first['p1']===2.5 && $first['p2']===3.3 && $first['p3']===3.9 && $first['p4']===3.1,'Coincide con los cuatro periodos de la primera fila del PDF');
sn_check(count(array_unique(array_column($data['records'],'id')))===218,'Identidades únicas');
foreach($data['records'] as $r)foreach(['p1','p2','p3','p4'] as $p)sn_check($r[$p]===null || ($r[$p]>=1 && $r[$p]<=5),'Escala válida');
sn_check($data['source_type']==='pdf' && $data['report_date']==='2026-11-20','La fuente es el PDF vigente');
sn_check(count($data['source_pages'])===40,'Se importaron las 40 páginas');
sn_check(count(array_unique(array_map(static fn($r)=>$r['year'].'|'.$r['group'].'|'.$r['code'],$data['records'])))===33,'Se importaron los 33 estudiantes');
sn_check(count(array_filter(array_column($data['records'],'p4'),static fn($v)=>$v!==null))===174,'Hay 174 notas de P4, incluida la actualización de Física');
sn_check($data['records'][1]['p1']===2.9 && $data['records'][1]['p2']===null && $data['records'][1]['p3']===null && $data['records'][1]['p4']===null,'Las celdas vacías conservan su periodo');
sn_check(sn_load_data_for_user('cuenta-con-excel-antiguo')===$data,'El PDF compartido es la fuente de consulta');
$urbanity=array_values(array_filter($data['records'],static fn($r)=>$r['id']==='d94731bd2a62b8485c58a9c353f75cc87333ad176d81a40f37f1cbe68de6babd'));
sn_check(count($urbanity)===1 && $urbanity[0]['p4']===4.5,'La actualización permanente de P4 en Urbanidad está registrada');
sn_check($urbanity[0]['p4_correction']['previous']===4.1,'Se conserva la nota anterior para identificar la actualización');
$physics=array_values(array_filter($data['records'],static fn($r)=>$r['id']==='b0500ac34338f4053c999bb89da51d1630455b34a40f93ec9e56e817e17f0266'));
sn_check(count($physics)===1 && $physics[0]['p4']===3.9,'La P4 de Cristina en Física está guardada en 3,9');
sn_check(abs(($physics[0]['p1']+$physics[0]['p2']+$physics[0]['p3']+$physics[0]['p4'])/4-4.35)<1e-10,'La final de Física es 4,35');
echo "OK: tabla, decimales, vacíos y base vigente: 40 páginas, 33 estudiantes, 218 registros y 174 notas de P4.\n";
