<?php
/** Local UI fixture: synthetic timetable, synthetic user, separate SQLite storage. */
declare(strict_types=1);
if (PHP_SAPI!=='cli-server' || !in_array($_SERVER['REMOTE_ADDR']??'',['127.0.0.1','::1'],true)) { http_response_code(404); exit; }
$root=dirname(__DIR__); $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file=match($path) {'/','/index.php'=>'index.php','/api.php'=>'api.php','/app.js'=>'app.js','/style.css'=>'style.css',default=>null};
if (!$file) { http_response_code(404); exit; }
if (in_array($file,['app.js','style.css'],true)) { header('Content-Type: '.($file==='app.js'?'application/javascript':'text/css')); readfile($root.'/apps/plan_diario/'.$file); exit; }
require_once $root.'/apps/plan_diario/lib/SchoolSchedule.php';
$fixture=$root.'/tmp/plan-diario-schedule-qa'; if (!is_dir($fixture)) mkdir($fixture,0700,true);
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
$school=new PDO('sqlite::memory:',null,null,$options);
foreach (['ano_lectivo(id_ano_lectivo INTEGER,nombre_ano_lectivo TEXT)','asignacion(id_asignacion INTEGER,institucion_educativa INTEGER,id_docente TEXT,id_asignatura INTEGER,id_categoria_curso INTEGER,ano_lectivo INTEGER,visible TEXT)','horario(id_asignacion INTEGER,dia TEXT,fecha_inicio TEXT,fecha_fin TEXT,hora_inicio TEXT,hora_fin TEXT)','materia_oficial(id_materia INTEGER,nombre_materia TEXT)','categoria_curso(id_categoria_curso INTEGER,nombre_categoria_curso TEXT)'] as $table) $school->exec('CREATE TABLE '.$table);
$school->exec("INSERT INTO ano_lectivo VALUES(17,'2026')");
$school->exec("INSERT INTO materia_oficial VALUES(1,'Matemáticas'),(2,'Ciencias sociales'),(3,'Emprendimiento')");
$school->exec("INSERT INTO categoria_curso VALUES(6,'6'),(9,'9')");
$school->exec("INSERT INTO asignacion VALUES(1,7,'schedule-preview',1,6,17,'si'),(2,7,'schedule-preview',2,9,17,'si'),(3,7,'schedule-preview',3,6,17,'si')");
$school->exec("INSERT INTO horario VALUES(1,'lunes','2026-01-01','2026-12-31','07:00','08:00'),(2,'lunes','2026-01-01','2026-12-31','09:00','10:00'),(3,'martes','2026-01-01','2026-12-31','08:00','09:00')");
$db=new PDO('sqlite:'.$fixture.'/preview.sqlite',null,null,$options); $db->exec('PRAGMA busy_timeout=5000'); DailyPlanner::install($db);
require_once $root.'/comun/seguridad.php';
ini_set('session.save_path',$fixture); ini_set('session.use_strict_mode','0'); ini_set('session.gc_probability','0');
session_name('guagua_session'); session_id('plan-diario-schedule-preview'); session_start();
$_SESSION['id_usuario']='schedule-preview'; $_SESSION['guagua_csrf']='schedule-preview-token';
if ($file==='index.php') { ob_start(); require $root.'/apps/plan_diario/index.php'; echo str_replace(['MI HOJA DEL DÍA','TU AVANCE EN EL TIEMPO','DALE UN LUGAR EN TU DÍA'],['VISTA DE PRUEBA','VISTA DE PRUEBA · DATOS FICTICIOS','VISTA DE PRUEBA · DATOS FICTICIOS'],ob_get_clean()); exit; }
header('Content-Type: application/json; charset=utf-8'); header('Cache-Control: no-store');
try {
 $input=[]; if ($_SERVER['REQUEST_METHOD']==='POST') { guagua_exigir_csrf(); $input=json_decode(file_get_contents('php://input'),true,32,JSON_THROW_ON_ERROR); }
 session_abort(); $date=DailyPlanner::date($input['date']??$_GET['date']??'2026-11-22');
 if ($_SERVER['REQUEST_METHOD']==='GET' && ($_GET['report']??'')==='compliance') {
  require_once $root.'/apps/plan_diario/lib/Compliance.php';
  echo json_encode(['ok'=>true,'data'=>DailyCompliance::load($db,'schedule-preview',$_GET['from']??$date,$_GET['to']??$date,'2026-11-22')],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR); exit;
 }
 $planner=new DailyPlanner($db,'schedule-preview',DailySchoolSchedule::load($school,'schedule-preview',7,$date));
 echo json_encode(['ok'=>true,'data'=>$planner->run($date,$input['action']??null,$input)],JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
} catch(Throwable $error) { http_response_code($error->getCode()===409?409:422); echo json_encode(['message'=>$error->getMessage()]); }
