<?php
/** Vista de QA sin autenticación ni datos personales reales. Solo se ejecuta por CLI. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('SN_VIEW_AUTHORIZED', true);
function sn_e($value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function guagua_csrf_token(): string { return 'preview-no-submit'; }
function guagua_menu_estilos(): void {
    echo '<link rel="stylesheet" href="../../comun/css/bootstrap.min.css">';
    echo '<link rel="stylesheet" href="../../comun/css/estilos_guagua.css">';
}
function guagua_menu_renderizar(): void {}
$names = ['Alejandra Martínez González','Andrés Felipe Ramírez Torres','Camila Sofía Valencia Pérez','Daniel Santiago Muñoz Díaz','Emilia Rodríguez Salazar','Gabriel Esteban Castro López','Isabella Juliana Moreno Ruiz','Juan Sebastián Méndez García','Laura Valentina Herrera Álvarez','Mariana Isabel Cortés Ríos','Mateo Alejandro Gómez Silva','Nicolás Antonio Vargas Peña','Sara Lucía Mendoza Ospina','Sofía Gabriela Sánchez Mora','Tomás Eduardo Jiménez Arias'];
$subjects = ['Ciencias sociales, hist, Geo,Const.Pol y Demo','Educación Física, Recreación y Deportes','Matemáticas','Tecnología e Informática','Educación Ética y en Valores Humanos','Ciencias Naturales y Educación Ambiental','Lengua Castellana','Idioma Extranjero: Inglés','Educación Artística'];
$records = [];
foreach ($names as $n => $name) foreach ($subjects as $s => $subject) {
    $records[] = ['id'=>hash('sha256',"fixture-$n-$s"), 'year'=>'2026', 'group'=>'02 Completa '.($n<8?'6 6':'7 7').' 2026', 'code'=>'DEMO'.str_pad((string)($n+1),3,'0',STR_PAD_LEFT), 'student'=>$name,'subject'=>$subject, 'teacher'=>'Docente de ejemplo', 'p1'=>$s===0?2.5:3.1+($s%4)*0.3,'p2'=>$s===0?3.3:3.2+($s%3)*0.3,'p3'=>$s===0?3.9:($n===1?null:3.5+($s%3)*0.2),'p4'=>$n===1?null:3.1+($s%5)*0.1,'source_page'=>$s+1];
    $last=count($records)-1;
    if($n%3===0 && $s===2) { $records[$last]['p1']=2.4; $records[$last]['p2']=2.7; $records[$last]['p3']=2.6; }
    if($n%4===0 && $s===6) { $records[$last]['p1']=1.0; $records[$last]['p2']=1.0; $records[$last]['p3']=1.0; }
    if($n===2 && $s<6) $records[$last]['p1']=null;
}
$data=['source'=>'ResumenEvaluacionAsignaturaDocente_20-11-2026 (ejemplo).pdf','source_type'=>'pdf','report_date'=>'2026-11-20','imported_at'=>'2026-11-20T15:00:00-05:00','records'=>in_array('--empty',$argv,true)?[]:$records];
$payload=['data'=>$data,'scope'=>'qa-preview','fingerprint'=>hash('sha256',json_encode($data))];
$error=''; $message='';
ob_start(); require dirname(__DIR__).'/apps/simulador_notas/view.php'; $html=(string)ob_get_clean();
$html=str_replace('<head>','<head><base href="http://127.0.0.1:8769/">',$html);
$directory=dirname(__DIR__).'/tmp/simulador-ux-review';
if(!is_dir($directory)) mkdir($directory,0775,true);
$name=in_array('--empty',$argv,true)?'empty.html':'index.html';
file_put_contents($directory.'/'.$name,$html);
echo "Vista de QA generada: $directory/$name\n";
