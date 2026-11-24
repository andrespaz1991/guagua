<?php
/** Genera una vista de QA con datos simulados, sin autenticación ni notas reales. */
declare(strict_types=1);
if (PHP_SAPI!=='cli') exit;
$root=dirname(__DIR__); $target=$root.'/tmp/reporte_actividades_qa';
if (!is_dir($target.'/assets')) mkdir($target.'/assets',0777,true);
$html=file_get_contents($root.'/apps/reporte_actividades/index.php');
$html=preg_replace('/<\?php[\s\S]*?\?>/','',$html);
$html=str_replace('VALLESOL · VALORACIONES','VISTA DE PRUEBA · DATOS SIMULADOS',$html);
// Incluir los estilos compartidos reales: sus reglas globales de tablas
// también afectan al módulo en producción y deben estar presentes en QA.
$sharedCss=[
    'bootstrap.min.css'=>'guagua-bootstrap.css',
    'estilos_guagua.css'=>'guagua-estilos.css',
    'interface_custom.css'=>'guagua-interface.css',
];
$sharedLinks='';
foreach ($sharedCss as $original=>$copy) {
    copy($root.'/comun/css/'.$original,$target.'/assets/'.$copy);
    $sharedLinks.='<link rel="stylesheet" href="assets/'.$copy.'">'."\n";
}
$html=preg_replace('/(<head\b[^>]*>)/i','$1'."\n".$sharedLinks,$html,1);
file_put_contents($target.'/index.html',$html);
foreach (['core.js','app.js','app.css'] as $asset) copy($root.'/apps/reporte_actividades/assets/'.$asset,$target.'/assets/'.$asset);
$sources=[];
$subjects=['Matemáticas','Geometría','Educación Física','Sociales','Emprendimiento','Tecnología','Urbanidad','Economía','Física'];
foreach (['6-8','9-11'] as $gi=>$group) foreach ($subjects as $si=>$subject) {
    $sourceId='src'.$gi.'-'.$si;
    $activities=[]; $students=[]; $records=[];
    foreach (['Portada','Taller de aplicación','Trabajo en equipo','Sustentación'] as $ai=>$name) $activities[]=['id'=>$sourceId.'|T'.($ai+1),'sourceId'=>$sourceId,'name'=>$name,'key'=>'T'.($ai+1),'type'=>'Taller','number'=>(string)($ai+1),'grades'=>[],'date'=>'','column'=>chr(70+$ai),'listCell'=>'V'.(10+$ai)];
    for ($i=0;$i<12;$i++) {
        $id='s'.$gi.'-'.$i; $grade=(string)(($gi===0?6:9)+$i%3);
        $students[]=['id'=>$id,'name'=>['ANA','CAMILO','DANIELA','DAVID','ELENA','FELIPE','GABRIELA','HUGO','ISABEL','JULIÁN','LAURA','MATEO'][$i].' ESTUDIANTE DE PRUEBA','document'=>'DEMO-'.($i+1),'grade'=>$grade,'group'=>$group,'row'=>12+$i];
        foreach ($activities as $ai=>$activity) {
            $seed=($i*3+$ai+$si+$gi)%10;
            $score=$seed<2?null:($seed===2?1:($seed===3?2.5:($seed<7?4:5)));
            $records[]=['studentId'=>$id,'activityId'=>$activity['id'],'sourceId'=>$sourceId,'score'=>$score,'status'=>$score===null?'missing':($score===1?'not_achieved':'graded'),'raw'=>$score===null?'':(string)$score,'cell'=>chr(70+$ai).(12+$i)];
        }
    }
    $sources[]=['id'=>$sourceId,'subject'=>$subject,'subjectKey'=>'materia'.$si,'period'=>'3','group'=>$group,'file'=>'Valoraciones '.$subject.' '.$group.'.xlsx','sheet'=>'valoraciones','modified'=>date(DATE_ATOM),'students'=>$students,'activities'=>$activities,'records'=>$records,'warnings'=>[]];
}
$json=json_encode(['ok'=>true,'data'=>['sources'=>$sources,'warnings'=>[],'readAt'=>date(DATE_ATOM),'schemaVersion'=>1]],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
file_put_contents($target.'/api.php',"<?php header('Content-Type: application/json; charset=utf-8'); ?>".$json);
echo 'Vista de QA generada en '.$target."\n";
