<?php
/** Browser QA with a synthetic account and a separate SQLite file. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '',['127.0.0.1','::1'],true)) { http_response_code(404); exit; }
$root=dirname(__DIR__); $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=match($path) {'/','/index.php'=>'index.php','/api.php'=>'api.php','/app.js'=>'app.js','/style.css'=>'style.css',default=>null};
if (!$file) { http_response_code(404); exit; }
if (in_array($file,['app.js','style.css'],true)) { header('Content-Type: '.($file==='app.js'?'application/javascript':'text/css')); readfile($root.'/apps/plan_diario/'.$file); exit; }
require_once $root.'/apps/plan_diario/lib/Planner.php';
$fixture=$root.'/tmp/plan-diario-document-qa'; if (!is_dir($fixture)) mkdir($fixture,0700,true);
$db=new PDO('sqlite:'.$fixture.'/preview.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$db->exec('PRAGMA busy_timeout=5000'); DailyPlanner::install($db);
$owner='document-preview'; $planner=new DailyPlanner($db,$owner); $date='2026-11-22';
if (!(int)$db->query('SELECT COUNT(*) FROM pd_accounts')->fetchColumn()) {
    $state=$planner->run($date); $ids=array_column($state['categories'],'id');
    $state=$planner->run($date,'edit_document',['revision'=>$state['revision'],'categories'=>[['id'=>$ids[0],'name'=>'Ciencias sociales'],['id'=>$ids[1],'name'=>'Emprendimiento']]]);
    foreach ([$ids[2],$ids[3]] as $id) $state=$planner->run($date,'delete_category',['revision'=>$state['revision'],'id'=>$id,'target_id'=>$ids[0]]);
    foreach ([[$ids[0],'Presentación de derechos humanos'],[$ids[0],'Examen de paz'],[$ids[1],'Entrega de guía'],[$ids[1],'Notas']] as [$id,$title]) $state=$planner->run($date,'save_task',['revision'=>$state['revision'],'category_id'=>$id,'title'=>$title]);
}
require_once $root.'/comun/seguridad.php';
ini_set('session.save_path',$fixture); ini_set('session.use_strict_mode','0'); ini_set('session.gc_probability','0');
session_name('guagua_session'); session_id('plan-diario-document-preview'); session_start();
$_SESSION['id_usuario']=$owner; $_SESSION['guagua_csrf']='document-preview-token';
if ($file==='index.php') {
    ob_start(); require $root.'/apps/plan_diario/index.php'; $html=ob_get_clean();
    echo str_replace('MI HOJA DEL DÍA','VISTA DE PRUEBA',$html); exit;
}
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
try {
    $input=[];
    if ($_SERVER['REQUEST_METHOD']==='POST') { guagua_exigir_csrf(); $input=json_decode(file_get_contents('php://input'),true,32,JSON_THROW_ON_ERROR); }
    session_abort();
    $date=DailyPlanner::date($input['date'] ?? $_GET['date'] ?? $date);
    echo json_encode(['ok'=>true,'data'=>$planner->run($date,$input['action'] ?? null,$input)],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
} catch (Throwable $error) { http_response_code($error->getCode()===409?409:422); echo json_encode(['message'=>$error->getMessage()]); }
