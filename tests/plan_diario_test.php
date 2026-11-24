<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
date_default_timezone_set('America/Bogota');
require_once __DIR__.'/../apps/plan_diario/lib/Planner.php';
$db = new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
DailyPlanner::install($db); // An isolated in-memory database, never real user data.
$checks = 0;
function check(bool $condition,string $message): void { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function reject(callable $callback,int $code = 0): void { try { $callback(); } catch (InvalidArgumentException | RuntimeException $e) { check($code === 0 || $e->getCode() === $code,'Wrong rejection.'); return; } throw new RuntimeException('Operation should have been rejected.'); }
$a = new DailyPlanner($db,'qa-a'); $b = new DailyPlanner($db,'qa-b');
$day = '2026-11-21'; $next = '2026-11-22';
$state = $a->run($day);
check(count($state['categories']) === 4 && $state['tasks'] === [],'Initial categories or empty state.');
$cat = $state['categories'][0]['id'];
$save = function(string $action,array $input = [],string $date = '2026-11-21') use ($a,&$state): array { return $state = $a->run($date,$action,['revision'=>$state['revision']]+$input); };
$save('save_task',['title'=>"Preparar O'Neil <script> 🚌",'category_id'=>$cat,'task_time'=>'07:30','priority'=>'alta','description'=>"Materiales\nSegunda línea",'highlight'=>'pink']);
$id = $state['tasks'][0]['id'];
check($state['tasks'][0]['title'] === "Preparar O'Neil <script> 🚌" && $state['tasks'][0]['task_time'] === '07:30','Text or time did not persist.');
check($b->run($day)['tasks'] === [],'Owner isolation failed.');
$other = $b->run($day);
reject(fn() => $b->run($day,'complete',['id'=>$id,'done'=>true,'revision'=>$other['revision']]));
reject(fn() => $a->run($day,'save_task',['revision'=>$state['revision'],'title'=>'Invalid category','category_id'=>$other['categories'][0]['id']]));
reject(fn() => $a->run($day,'complete',['revision'=>0,'id'=>$id,'done'=>true]),409);
$save('complete',['id'=>$id,'done'=>true]);
check($state['tasks'][0]['done'] && $state['tasks'][0]['completed_at'] !== null,'Completion not recorded.');
$save('focus',['focus'=>'Preparar una clase tranquila.']);
check($a->run($day)['focus'] === 'Preparar una clase tranquila.','Focus persistence.');
$copyRevision = $state['revision'];
$save('copy',['source'=>$day,'pending_only'=>true],$next);
check(count($state['tasks']) === 0,'Copied a completed task with pending filter.');
$save('copy',['source'=>$day,'pending_only'=>false],$next);
check(count($state['tasks']) === 1 && !$state['tasks'][0]['done'],'Copy should reset completion.');
$copyId = $state['tasks'][0]['id'];
$save('copy',['source'=>$day,'pending_only'=>false],$next);
check(count($state['tasks']) === 1,'Copy duplicated tasks.');
$save('save_task',['id'=>$copyId,'title'=>'Copia editada','category_id'=>$cat,'task_date'=>$next],$next);
$save('copy',['source'=>$day,'pending_only'=>false],$next);
check(count($state['tasks']) === 1,'Editing a copy broke duplicate protection.');
$state = $a->run($day);
check($state['tasks'][0]['done'] && $state['tasks'][0]['title'] === "Preparar O'Neil <script> 🚌",'Copy altered original history.');
$save('complete',['id'=>$id,'done'=>false]);
check(!$state['tasks'][0]['done'] && $state['tasks'][0]['completed_at'] === null,'Reopening did not clear timestamp.');
$save('save_task',['title'=>'Segunda tarea','category_id'=>$cat]);
$secondId = $state['tasks'][1]['id'];
$save('swap',['id'=>$id,'other_id'=>$secondId]);
check($state['tasks'][0]['id'] === $secondId,'Order did not persist.');
$target = $state['categories'][1]['id'];
$save('delete_category',['id'=>$cat,'target_id'=>$target]);
check(count($state['categories']) === 3 && $state['tasks'][0]['category_id'] === $target,'Category deletion lost tasks.');
check($a->run($next)['tasks'][0]['category_id'] === $target,'Category reassignment missed another date.');
reject(fn() => DailyPlanner::date('2026-02-30'));
reject(fn() => DailyPlanner::date('2026-2-3'));
reject(fn() => $a->run($day,'save_task',['revision'=>$state['revision'],'title'=>' ','category_id'=>$target]));
reject(fn() => $a->run($day,'save_task',['revision'=>$state['revision'],'title'=>'bad time','category_id'=>$target,'task_time'=>'25:00']));
reject(fn() => $a->run($day,'save_task',['revision'=>$state['revision'],'title'=>'bad color','category_id'=>$target,'highlight'=>'<script>']));
check($a->run($day)['revision'] === $state['revision'],'Rejected mutation changed revision.');
$save('delete_task',['id'=>$id]);
check(count($state['tasks']) === 1,'Task deletion failed.');
$file = tempnam(sys_get_temp_dir(),'plan-diario-test-');
try {
    $disk = new PDO('sqlite:'.$file,null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    DailyPlanner::install($disk);
    $planner = new DailyPlanner($disk,'disk-user');
    $diskState = $planner->run($day);
    $diskState = $planner->run($day,'save_task',['revision'=>$diskState['revision'],'title'=>'Persistencia en disco','category_id'=>$diskState['categories'][0]['id']]);
    $diskState = $planner->run($day,'complete',['revision'=>$diskState['revision'],'id'=>$diskState['tasks'][0]['id'],'done'=>true]);
    unset($planner,$disk);
    $reopened = new PDO('sqlite:'.$file,null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $saved = (new DailyPlanner($reopened,'disk-user'))->run($day);
    check($saved['tasks'][0]['title'] === 'Persistencia en disco','Data did not survive closing the connection.');
    check($saved['tasks'][0]['done'] && $saved['tasks'][0]['completed_at'] !== null,'Completion did not persist on disk.');
    unset($reopened);
} finally { if (is_file($file)) unlink($file); }
echo "OK: $checks comprobaciones de persistencia, aislamiento, historial, conflictos, copia, orden y validación.\n";
