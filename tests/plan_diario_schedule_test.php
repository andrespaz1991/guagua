<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../apps/plan_diario/lib/SchoolSchedule.php';
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
$school=new PDO('sqlite::memory:',null,null,$options);
foreach ([
 'ano_lectivo(id_ano_lectivo INTEGER,nombre_ano_lectivo TEXT,estado TEXT)',
 'asignacion(id_asignacion INTEGER,institucion_educativa INTEGER,id_docente TEXT,id_asignatura INTEGER,id_categoria_curso INTEGER,ano_lectivo INTEGER,visible TEXT)',
 'horario(id_asignacion INTEGER,dia TEXT,fecha_inicio TEXT,fecha_fin TEXT,hora_inicio TEXT,hora_fin TEXT)',
 'materia_oficial(id_materia INTEGER,nombre_materia TEXT)',
 'categoria_curso(id_categoria_curso INTEGER,nombre_categoria_curso TEXT)',
] as $table) $school->exec('CREATE TABLE '.$table);
$school->exec("INSERT INTO ano_lectivo VALUES(17,'2026','Activo'),(16,'2025','Inactivo'),(18,'2027','Inactivo')");
$school->exec("INSERT INTO materia_oficial VALUES(1,'Matemáticas'),(2,'Ciencias sociales')");
$school->exec("INSERT INTO categoria_curso VALUES(6,'6'),(9,'9')");
$school->exec("INSERT INTO asignacion VALUES(10,7,'teacher-a',1,6,17,'SI'),(11,7,'teacher-a',2,9,17,'si'),(12,7,'teacher-b',1,6,17,'si'),(13,8,'teacher-a',1,6,17,'si'),(14,7,'teacher-a',1,6,16,'si'),(15,7,'teacher-a',1,6,17,'NO'),(16,7,'teacher-a',1,9,2026,'si'),(17,7,'teacher-a',2,6,17,'si'),(18,7,'teacher-a',2,6,17,'si'),(19,7,'teacher-a',1,6,18,'si')");
$q=$school->prepare('INSERT INTO horario VALUES(?,?,?,?,?,?)');
foreach ([
 [10,'lunes','2026-11-01','2026-11-30','07:00:00','08:00:00'],
 [10,'LUNES','2026-11-01','2026-11-30','10:00:00','11:00:00'],
 [10,'lunes','2026-11-01','2026-11-30','07:00:00','08:00:00'],
 [11,'lunes','2026-11-01','2026-11-30','08:00:00','09:00:00'],
 // A current-year assignment can retain block dates from a previous school period.
 [11,'martes','2023-09-04','2023-11-30','08:00:00','09:00:00'],
 [12,'lunes','2026-11-01','2026-11-30','07:00:00','08:00:00'],
 [13,'lunes','2026-11-01','2026-11-30','07:00:00','08:00:00'],
 [14,'lunes','2025-11-01','2025-11-30','07:00:00','08:00:00'],
 [15,'lunes','2026-11-01','2026-11-30','07:00:00','08:00:00'],
 [16,'lunes','2026-11-01','2026-11-30','09:00:00','10:00:00'],
 [17,'MIÉRCOLES','2026-11-01','2026-11-30','07:00:00','08:00:00'],
 [18,'jueves','2026-09-01','2026-10-31','07:00:00','08:00:00'],
 [19,'lunes','2027-11-01','2027-11-30','07:00:00','08:00:00'],
] as $row) $q->execute($row);
$checks=0;
function schedule_check(bool $ok,string $message): void { global $checks; if (!$ok) throw new RuntimeException($message); $checks++; }
function schedule_reject(callable $action): void { try { $action(); } catch(InvalidArgumentException $e) { schedule_check(true,'Rejected'); return; } throw new RuntimeException('Invalid operation was accepted.'); }
$load=fn(string $date)=>DailySchoolSchedule::load($school,'teacher-a',7,$date);
$monday='2026-11-16'; $tuesday='2026-11-17'; $sunday='2026-11-22';
$day=$load($monday);
schedule_check(array_column($day['subjects'],'assignment_id')===[10,11,16],'Subjects are not filtered by owner, institution, visibility and academic year, or not sorted by time.');
schedule_check(count($day['subjects'][0]['blocks'])===2,'Repeated blocks duplicated the subject or its hours.');
schedule_check($day['subjects'][0]['name']==='Matemáticas · 6' && $day['subjects'][2]['name']==='Matemáticas · 9','Separate groups are not distinguishable.');
schedule_check(array_column($load($tuesday)['subjects'],'assignment_id')===[11],'Weekday selection failed.');
schedule_check(array_column($load('2026-11-10')['subjects'],'assignment_id')===[11],'Old block dates hide Tuesday classes from the selected academic year.');
schedule_check(array_column($load('2026-11-24')['subjects'],'assignment_id')===[11],'Weekly classes disappear on a different Tuesday.');
schedule_check(array_column($load('2026-11-12')['subjects'],'assignment_id')===[18],'A previous-period block hides the current-year Thursday assignment.');
schedule_check(array_column($load('2026-11-18')['subjects'],'assignment_id')===[17],'Uppercase accented weekdays are not normalized.');
schedule_check($load('2026-11-21')['subjects']===[] && $load($sunday)['subjects']===[],'Empty weekends did not fall back to personal.');
schedule_check(array_column($load('2027-11-15')['subjects'],'assignment_id')===[19],'The selected year is ignored.');
schedule_check(array_column($load('2025-11-17')['subjects'],'assignment_id')===[14],'Historical academic years cannot be consulted.');
schedule_check(DailySchoolSchedule::load($school,'teacher-b',7,$monday)['subjects'][0]['assignment_id']===12,'Another teacher is not isolated.');
schedule_check(DailySchoolSchedule::load($school,'teacher-a',0,$monday)['subjects']===[],'Missing institutional context exposed subjects.');
schedule_reject(fn()=> $load('2026-02-30'));
$db=new PDO('sqlite::memory:',null,null,$options); DailyPlanner::install($db);
$plan=fn(string $date)=>new DailyPlanner($db,'teacher-a',$load($date));
$state=$plan($sunday)->run($sunday);
schedule_check(count($state['categories'])===1 && $state['categories'][0]['name']==='Personal','Unscheduled day does not show only Personal.');
$personal=$state['categories'][0]['id'];
schedule_check($state['categories'][0]['managed'] && $state['schedule']['mode']==='personal','Personal mode metadata is missing.');
$state=$plan($monday)->run($monday);
schedule_check(count($state['categories'])===4 && $state['schedule']['subject_count']===3,'School day sections must include the scheduled subjects and Personal.');
schedule_check($state['categories'][3]['id']===$personal && $state['categories'][3]['managed'] && !$state['categories'][3]['scheduled'] && !$state['categories'][3]['saved_only'],'Personal is missing, duplicated or classified as a school subject.');
$math=$state['categories'][0]['id']; $social=$state['categories'][1]['id'];
schedule_check($state['categories'][0]['scheduled'] && count($state['categories'][0]['schedule_blocks'])===2,'Class times are not attached to the heading.');
$state=$plan($monday)->run($monday,'save_task',['revision'=>$state['revision'],'category_id'=>$math,'title'=>'Preparar evaluación']);
$task=$state['tasks'][0]['id'];
$state=$plan($monday)->run($monday,'complete',['revision'=>$state['revision'],'id'=>$task,'done'=>true]);
$completion=$state['tasks'][0]['completed_at']; $revision=$state['revision'];
$again=$plan($monday)->run($monday);
schedule_check($again['revision']===$revision && $again['categories'][0]['id']===$math,'Reading duplicates headings or changes revision.');
$next=$plan($tuesday)->run($tuesday);
schedule_check(count($next['categories'])===2 && $next['categories'][0]['id']===$social && $next['categories'][1]['id']===$personal && $next['tasks']===[],'Changing date loses Personal, duplicates subjects or leaks tasks across days.');
$state=$plan($sunday)->run($sunday);
schedule_check($state['categories'][0]['id']===$personal && $state['tasks']===[],'Returning to Personal creates duplicate categories or copies tasks.');
$state=$plan($sunday)->run($sunday,'save_task',['revision'=>$state['revision'],'category_id'=>$personal,'title'=>'Leer un libro']);
schedule_check($plan($sunday)->run($sunday)['tasks'][0]['title']==='Leer un libro','Personal tasks did not persist.');
schedule_check($plan('2026-11-29')->run('2026-11-29')['tasks']===[],'Personal tasks leak into another Sunday.');
$state=$plan($monday)->run($monday); $revision=$state['revision'];
schedule_reject(fn()=> $plan($monday)->run($monday,'edit_document',['revision'=>$revision,'categories'=>[['id'=>$math,'name'=>'Renamed']]]));
schedule_reject(fn()=> $plan($monday)->run($monday,'delete_category',['revision'=>$revision,'id'=>$math,'target_id'=>$social]));
$state=$plan($monday)->run($monday,'edit_document',['revision'=>$revision,'tasks'=>[['id'=>$task,'title'=>'Evaluación de matemáticas']]]);
schedule_check($state['tasks'][0]['done'] && $state['tasks'][0]['completed_at']===$completion,'Editing a scheduled task lost progress.');
$state=$plan($monday)->run($monday,'save_category',['revision'=>$state['revision'],'name'=>'Otros pendientes','color'=>'purple']);
schedule_check(in_array('Otros pendientes',array_column($state['categories'],'name'),true),'Additional heading did not attach to the selected day.');
schedule_check(!in_array('Otros pendientes',array_column($plan($sunday)->run($sunday)['categories'],'name'),true),'An empty custom heading leaks into other dates.');
$school->exec('DELETE FROM horario WHERE id_asignacion=10');
$state=$plan($monday)->run($monday);
$preserved=array_values(array_filter($state['categories'],fn($c)=>$c['id']===$math));
schedule_check(count($preserved)===1 && $preserved[0]['saved_only'] && $state['tasks'][0]['done'],'A timetable change hid or reset existing tasks.');
// A configured Saturday must show its classes; weekends are not hard-coded as personal.
$q->execute([11,'SÁBADO','2026-11-01','2026-11-30','09:00:00','10:00:00']);
schedule_check(array_column($load('2026-11-21')['subjects'],'assignment_id')===[11],'Saturday classes were incorrectly replaced by Personal.');
$saturday=$plan('2026-11-21')->run('2026-11-21');
schedule_check(count($saturday['categories'])===2 && $saturday['categories'][1]['id']===$personal,'A Saturday with classes lost Personal.');
$state=$plan($tuesday)->run($tuesday);
$state=$plan($tuesday)->run($tuesday,'save_task',['revision'=>$state['revision'],'category_id'=>$personal,'title'=>'Comprar materiales personales']);
$personalTask=$state['tasks'][0]['id'];
$state=$plan($tuesday)->run($tuesday,'complete',['revision'=>$state['revision'],'id'=>$personalTask,'done'=>true]);
schedule_check($plan($tuesday)->run($tuesday)['tasks'][0]['done'],'Personal tasks on a school day did not preserve completion.');
schedule_check($plan($sunday)->run($sunday)['tasks'][0]['title']==='Leer un libro','School-day Personal tasks leaked into another date.');
$revision=$state['revision'];
schedule_reject(fn()=> $plan($tuesday)->run($tuesday,'delete_category',['revision'=>$revision,'id'=>$personal,'target_id'=>$social]));
$firstSchoolDay=(new DailyPlanner($db,'new-school-user',$load($tuesday)))->run($tuesday);
schedule_check(count(array_filter($firstSchoolDay['categories'],fn($c)=>$c['name']==='Personal'))===1,'Opening a school day first does not prepare exactly one Personal section.');
// Existing manual Personal data is reused when the new schedule behavior is enabled.
$legacy=new DailyPlanner($db,'legacy-user'); $legacyState=$legacy->run($sunday); $legacyPersonal=$legacyState['categories'][3]['id'];
$legacySchedule=new DailyPlanner($db,'legacy-user',DailySchoolSchedule::personal($sunday));
$legacyState=$legacySchedule->run($sunday);
schedule_check(count($legacyState['categories'])===1 && $legacyState['categories'][0]['id']===$legacyPersonal,'Migration duplicated the existing Personal section.');
echo "OK: $checks comprobaciones de horario semanal por fecha, docente, institución, año lectivo, fechas antiguas, grados, Personal y conservación de tareas.\n";
