<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../apps/plan_diario/lib/Today.php';
$db = new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
DailyPlanner::install($db);
$today = '2026-11-23';
$planner = new DailyPlanner($db,'teacher-a');
$state = $planner->run($today);
[$school,,, $personal] = array_column($state['categories'],'id');
$add = static function(string $date, int $category, string $title) use ($planner): array {
    $state = $planner->run($date);
    return $planner->run($date,'save_task',['revision'=>$state['revision'],'category_id'=>$category,'title'=>$title,'task_time'=>'08:30','priority'=>'alta']);
};
$state = $add($today,$school,'Preparar evaluación'); $first = $state['tasks'][0]['id'];
$state = $add($today,$personal,'Leer'); $second = $state['tasks'][1]['id'];
$yesterday = $add('2026-11-22',$school,'Tarea de ayer')['tasks'][0]['id'];
$add('2026-11-24',$school,'Tarea de mañana');
$other = new DailyPlanner($db,'teacher-b'); $otherState = $other->run($today);
$otherState = $other->run($today,'save_task',['revision'=>$otherState['revision'],'category_id'=>$otherState['categories'][0]['id'],'title'=>'Tarea privada']);
$foreign = $otherState['tasks'][0]['id'];
$checks = 0;
function today_check(bool $ok, string $message): void { global $checks; if (!$ok) throw new RuntimeException($message); $checks++; }
function today_reject(callable $callback, int $code = 0): void {
    try { $callback(); } catch (InvalidArgumentException | RuntimeException $error) {
        today_check($error->getCode() === $code,'Unexpected rejection: '.$error->getMessage()); return;
    }
    throw new RuntimeException('Invalid write accepted.');
}
$before = (int)$db->query('SELECT total_changes()')->fetchColumn();
$load = fn()=>DailyPlannerToday::load($db,'teacher-a',$today);
$data = $load();
today_check($data['total']===2 && $data['done']===0 && $data['date']===$today,'Incorrect day totals.');
today_check(array_column($data['tasks'],'id')===[$first,$second],'Includes another date or user.');
today_check($data['tasks'][0]['category']==='Clases' && $data['tasks'][0]['task_time']==='08:30' && $data['tasks'][0]['done']===false,'Category, time or checkbox type is incorrect.');
$empty = DailyPlannerToday::load($db,'unused-account',$today);
today_check($empty===['date'=>$today,'revision'=>0,'tasks'=>[],'total'=>0,'done'=>0],'Empty account should have no tasks.');
today_check((int)$db->query('SELECT total_changes()')->fetchColumn()===$before,'Loading the home card writes or seeds an account.');
$past = DailyPlannerToday::load($db,'teacher-a','2026-11-22');
$future = DailyPlannerToday::load($db,'teacher-a','2026-11-24');
today_check($past['date']==='2026-11-22' && array_column($past['tasks'],'id')===[$yesterday],'Selecting a past day shows tasks from a different date.');
today_check($future['total']===1 && $future['tasks'][0]['title']==='Tarea de mañana','Selecting a future day loses saved tasks.');
$planner->run('2026-11-22','complete',['revision'=>$past['revision'],'id'=>$yesterday,'done'=>true]);
today_check(DailyPlannerToday::load($db,'teacher-a','2026-11-22')['done']===1 && $load()['done']===0,'Checking a selected date changes another day.');
$planner->run('2026-11-22','complete',['revision'=>$load()['revision'],'id'=>$yesterday,'done'=>false]);
$data = $load();
$revision = $data['revision'];
$planner->run($today,'complete',['revision'=>$revision,'id'=>$first,'done'=>true]);
$data = $load();
today_check($data['done']===1 && $data['tasks'][0]['done']===true && $data['revision']===$revision+1,'Checkbox state is not shared with the planner.');
today_check(!empty($planner->run($today)['tasks'][0]['completed_at']),'Completion timestamp missing.');
today_reject(fn()=> $planner->run($today,'complete',['revision'=>$revision,'id'=>$second,'done'=>true]),409);
today_check($load()['done']===1,'Stale home card overwrote changes from another tab.');
$revision = $load()['revision'];
today_reject(fn()=> $planner->run($today,'complete',['revision'=>$revision,'id'=>$yesterday,'done'=>true]));
today_reject(fn()=> $planner->run($today,'complete',['revision'=>$revision,'id'=>$foreign,'done'=>true]));
today_check($load()['revision']===$revision,'Rejected writes changed the revision.');
$planner->run($today,'complete',['revision'=>$revision,'id'=>$first,'done'=>false]);
today_check($load()['done']===0 && $planner->run($today)['tasks'][0]['completed_at']===null,'Unchecking did not persist.');
$state = $planner->run($today);
$planner->run($today,'save_task',['revision'=>$state['revision'],'id'=>$second,'category_id'=>$personal,'title'=>'Leer','task_date'=>'2026-11-24']);
today_check($load()['total']===1,'Moved task remains in today’s card.');
today_reject(fn()=>DailyPlannerToday::load($db,'teacher-a','2026-02-30'));
echo "OK: $checks comprobaciones de tarjeta de hoy, lectura sin cambios, aislamiento, cumplimiento y conflictos.\n";
