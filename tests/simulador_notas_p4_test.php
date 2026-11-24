<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/asistencia/vendor/autoload.php';
require dirname(__DIR__).'/apps/simulador_notas/lib.php';
require dirname(__DIR__).'/apps/simulador_notas/P4ResumenReader.php';
function p4_check(bool $condition, string $message): void { if (!$condition) throw new RuntimeException($message); }
$reader = new P4ResumenReader();
$rows = [['Nombre','Documento','Grado','Matemáticas','Economía/Ciencias Sociales','Urbanidad','Fisica'],['PÉREZ ANA','DOC1',10,2.855,3.125,4,null],['OTRO ESTUDIANTE','DOC2',11,'#REF!',1,0,5]];
$parsed = $reader->parseRows($rows,'resumen_9-11.xlsx','Resumen 9-11',[9,10,11]);
$base = ['year'=>'2026','group'=>'02 Completa 10 10 2026','code'=>'001','student'=>'Perez Ana','p1'=>3.0,'p2'=>3.0,'p3'=>3.0,'p4'=>null];
$records = [];
foreach (['Matemáticas','Ciencias Económicas','Ciencias Políticas','Civica y Urbanismo','Fisica'] as $subject) $records[] = sn_record($base + ['subject'=>$subject]);
$matched = $reader->match($records,$parsed['students']);
p4_check(count($matched['matches'])===4,'Deben coincidir matemáticas, economía, política y urbanidad.');
p4_check($matched['matches'][$records[0]['id']]['value']===2.855,'Conservar precisión original sin redondear.');
p4_check($matched['matches'][$records[1]['id']]['value']===3.125 && $matched['matches'][$records[2]['id']]['cell']==='E2','Economía y Política deben compartir la columna autorizada.');
p4_check($matched['matches'][$records[0]['id']]['match_by']==='nombre y grado','Reconocer identidad sin tildes.');
p4_check(count($matched['unmatched'])===1,'Una nota vacía debe quedar pendiente.');
p4_check(count($parsed['invalid_cells'])===2,'Los errores de fórmula y cero no deben convertirse en notas.');
p4_check($parsed['students'][1]['values']['economia']['value']===1.0,'La nota 1 es válida.');
p4_check(P4ResumenReader::subjectKeys('Economia/Ciencias Sociales',9)===['sociales'],'La columna combinada de noveno corresponde a Sociales.');
p4_check(P4ResumenReader::subjectKeys('Geometria',10)!==P4ResumenReader::subjectKeys('Matemáticas',10),'No mezclar geometría y matemáticas.');
$duplicates=array_merge($parsed['students'],[$parsed['students'][0]]);
p4_check(count($reader->match($records,$duplicates)['matches'])===0,'No elegir una fila de estudiante duplicado.');
$otherGroup=$records[0];$otherGroup['group']='10-B';$otherGroup=sn_record($otherGroup);
p4_check(count($reader->match([$records[0],$otherGroup],$parsed['students'])['matches'])===0,'No unir grupos ambiguos.');
$nextYear=$records[0];$nextYear['year']='2027';
p4_check(count($reader->match([$nextYear],$parsed['students'])['matches'])===0,'No aplicar el resumen de 2026 a otro año.');
$documentRecord=$records[0];$documentRecord['student']='Nombre distinto';$documentRecord['document']='DOC1';
p4_check(count($reader->match([$documentRecord],$parsed['students'])['matches'])===1,'El documento permite una coincidencia exacta por grado.');
$documentRecord['document']='DOC3';$documentRecord['student']='Perez Ana';
p4_check(count($reader->match([$documentRecord],$parsed['students'])['matches'])===0,'No ignorar un documento contradictorio.');
$public=['source'=>'test','records'=>[$records[0]]];$private=$public;$private['records'][0]['document']='DOC1';
p4_check(sn_public_data($private)===$public,'El documento debe usarse solo en el servidor, conservando la huella pública y los escenarios.');
$newRows=$rows;$newRows[]=['ESTUDIANTE NUEVO','DOC-NUEVO',10,4,3.6,4.1,4.2];
$newParsed=$reader->parseRows($newRows,'resumen_9-11.xlsx','Resumen 9-11',[9,10,11]);
$newMatch=$reader->match($records,$newParsed['students']);
p4_check(count($newMatch['source_only_records'])===5,'Un alumno nuevo debe tener el diagnóstico de las materias del grado.');
p4_check($newMatch['source_only_records'][0]['p1']===null && $newMatch['source_only_records'][0]['source_only']===true,'No inventar las notas históricas del alumno sin consolidado.');
p4_check(!str_contains(json_encode($newMatch['source_only_records']),'DOC-NUEVO'),'No enviar el documento de alumnos nuevos al navegador.');
p4_check(count($reader->match($records,$duplicates)['source_only_records'])===0,'No anunciar una identidad duplicada del consolidado como un estudiante nuevo.');
p4_check(count($reader->match([$documentRecord],$parsed['students'])['source_only_records'])===0,'Un documento contradictorio no debe producir un falso alumno nuevo.');
echo "OK: identidad, grado, materias, Económicas/Políticas, precisión, vacíos, duplicados y año.\n";
echo "OK: lector histórico con datos ficticios y privacidad; la consulta actual utiliza el PDF.\n";
if (in_array('--real',$argv,true)) {
    $data=require dirname(__DIR__).'/apps/simulador_notas/data/inicial.php';
    if (($data['source_type'] ?? '') === 'pdf') { echo "La fuente vigente es PDF; no se consultan archivos Excel.\n"; exit; }
    $live=$reader->read($data['records']);
    $reasons=[];foreach($live['unmatched'] as $row)$reasons[$row['reason']]=($reasons[$row['reason']]??0)+1;
    echo json_encode(['status'=>$live['status'],'sources'=>$live['sources'],'matched'=>$live['matched_count'],'total'=>$live['total_count'],'unmatched_reasons'=>$reasons],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)."\n";
    p4_check($live['status']==='ready','Los dos resúmenes deben estar disponibles.');
    p4_check($live['matched_count']>0,'Deben existir notas actuales reconocidas.');
    foreach($live['matches'] as $match)p4_check($match['value']>=1 && $match['value']<=5,'Nota fuera de rango.');
}
