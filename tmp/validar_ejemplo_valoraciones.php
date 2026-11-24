<?php
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__).'/apps/reporte_actividades/lib/ValoracionesReader.php';
$file=$argv[1];
$reader=new ValoracionesReader();
$data=$reader->readFile($file,'6-8');
$counts=array_count_values(array_column($data['records'],'status'));
if (count($data['students'])!==4 || count($data['activities'])!==8 || count($data['records'])!==18 || count($data['warnings'])!==0) {
    throw new RuntimeException(json_encode($data['warnings']).' Estructura inesperada: '.count($data['activities']).' actividades / '.count($data['records']).' registros.');
}
if (($counts['missing'] ?? 0)!==3 || ($counts['not_achieved'] ?? 0)!==3) throw new RuntimeException('No se conservaron las celdas vacías o las notas de 1.');
foreach ($data['activities'] as $activity) {
    if (str_starts_with($activity['key'],'E') && !in_array($activity['column'],['H','I'],true)) throw new RuntimeException('Examen mal enlazado');
}
echo json_encode(['students'=>count($data['students']),'activities'=>count($data['activities']),'records'=>count($data['records']),'statuses'=>$counts,'dates'=>array_column($data['activities'],'date'),'warnings'=>$data['warnings']],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
