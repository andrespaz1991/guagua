<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require_once dirname(__DIR__).'/apps/reporte_actividades/lib/ValoracionesReader.php';
function ra_assert(bool $condition,string $message): void { if (!$condition) throw new RuntimeException($message); }
$reader=new ValoracionesReader();
$rows=[];
for ($r=1;$r<=30;$r++) $rows[$r]=array_fill_keys(range('A','Z'),null);
$rows[8]['V']='Actividades 6';
$rows[9]=array_replace($rows[9],['C'=>'Matemáticas','S'=>3,'T'=>'Periodo']);
$rows[10]=array_replace($rows[10],['B'=>'Nombre','C'=>'Documento','D'=>'Grado','F'=>'Talleres','L'=>'Examenes','T'=>'Valoración']);
$rows[11]=array_replace($rows[11],['F'=>1,'G'=>2,'H'=>3,'L'=>1,'M'=>2]);
$rows[10]['V']=1; $rows[10]['W']='Portada';
$rows[11]['V']=2; $rows[11]['W']='Giros';
$rows[12]=array_replace($rows[12],['B'=>'ESTUDIANTE UNO','C'=>'123','D'=>6,'F'=>null,'G'=>1]);
$rows[13]=array_replace($rows[13],['B'=>'ESTUDIANTE DOS','C'=>'123','D'=>7,'F'=>5,'G'=>2.5,'H'=>4]);
$rows[17]['V']='Actividades 7';
$rows[18]['V']='#'; $rows[18]['W']='Actividad';
$rows[19]['V']=1; $rows[19]['W']='Portada de séptimo';
$rows[20]['V']='E2'; $rows[20]['W']='Geometría';
$rows[13]['M']='4,5';
$parsed=$reader->parseGrid($rows,'Valoraciones Matematicas.xlsx','6-8','valoraciones');
ra_assert($parsed['period']==='3','Período incorrecto');
ra_assert(count($parsed['students'])===2,'Se fusionaron estudiantes por documento repetido');
ra_assert(count($parsed['activities'])===4,'No se reconocieron las actividades por grado o el examen E2');
ra_assert(count($parsed['records'])===4,'Se aplicaron actividades de un grado a otro');
ra_assert($parsed['records'][0]['status']==='missing','Vacío debe ser falta de nota');
ra_assert($parsed['records'][1]['status']==='not_achieved','1 debe ser no alcanzó');
ra_assert($parsed['records'][2]['cell']==='F13','La portada de séptimo se vinculó a otra columna');
ra_assert($parsed['records'][3]['cell']==='M13' && $parsed['records'][3]['score']===4.5,'E2 no enlaza al segundo examen');
ra_assert(count($parsed['warnings'])>0,'Notas en columnas sin nombre deben advertirse');
foreach ([null,'','  '] as $v) ra_assert(ValoracionesReader::grade($v)['status']==='missing','Vacío clasificado como nota');
foreach ([0,'0',6,'ausente','#REF!','=1+1'] as $v) ra_assert(ValoracionesReader::grade($v)['status']==='invalid','Dato no válido convertido a nota: '.$v);
ra_assert(ValoracionesReader::grade('1,0')['status']==='not_achieved','Nota 1 con coma no reconocida');
ra_assert(ValoracionesReader::grade('2,5')['score']===2.5,'Decimal con coma no reconocido');
ra_assert(ValoracionesReader::normalize('GÓMEZ  PÉREZ')===ValoracionesReader::normalize('gomez perez'),'Identidad sensible a acentos');
echo "OK: alcance por grado, encabezados, E2, celdas vacías, nota 1, decimales, datos inválidos e identidad.\n";
if (in_array('--real',$argv,true)) {
    $data=$reader->readDirectory(ValoracionesReader::DEFAULT_ROOT);
    $records=0; $counts=[];
    foreach ($data['sources'] as $source) {
        $records+=count($source['records']);
        $counts[]=['file'=>$source['file'],'group'=>$source['group'],'students'=>count($source['students']),'activities'=>count($source['activities']),'records'=>count($source['records']),'warnings'=>count($source['warnings'])];
        foreach ($source['records'] as $record) {
            ra_assert($record['status']!=='graded' || ($record['score']>=1 && $record['score']<=5),'Nota fuera de rango');
        }
    }
    echo json_encode(['sources'=>$counts,'totalRecords'=>$records,'generalWarnings'=>$data['warnings']],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
    ra_assert(count($data['sources'])===16,'Se esperaban 16 archivos sin copias ni duplicados');
}
