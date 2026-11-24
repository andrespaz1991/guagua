<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__.'/../apps/plan_diario/lib/Planner.php';
$db = new PDO('sqlite::memory:',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
DailyPlanner::install($db);
$a = new DailyPlanner($db,'document-user'); $b = new DailyPlanner($db,'other-user');
$date = '2026-11-22'; $checks = 0;
function document_check(bool $condition,string $message): void { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function document_reject(callable $callback): void { try { $callback(); } catch (InvalidArgumentException $expected) { document_check(true,'Rejection'); return; } throw new RuntimeException('Invalid document edit was accepted.'); }
$state=$a->run($date); $category=$state['categories'][0]['id']; $secondCategory=$state['categories'][1]['id'];
$state=$a->run($date,'save_task',['revision'=>$state['revision'],'category_id'=>$category,'title'=>'Presentación','description'=>'Materiales de clase','task_time'=>'08:30','priority'=>'alta','highlight'=>'pink']);
$id=$state['tasks'][0]['id'];
$state=$a->run($date,'complete',['revision'=>$state['revision'],'id'=>$id,'done'=>true]);
$completed=$state['tasks'][0]['completed_at'];
$state=$a->run($date,'edit_document',['revision'=>$state['revision'],'categories'=>[['id'=>$category,'name'=>'Ciencias sociales'],['id'=>$secondCategory,'name'=>'Emprendimiento']],'tasks'=>[['id'=>$id,'title'=>"Presentación de derechos humanos 📝 <texto>"]]]);
$task=$state['tasks'][0];
document_check($task['title']==="Presentación de derechos humanos 📝 <texto>",'Inline text was not saved literally.');
document_check($state['categories'][0]['name']==='Ciencias sociales' && $state['categories'][1]['name']==='Emprendimiento','Section headings were not saved.');
document_check($task['done'] && $task['completed_at']===$completed,'Editing text reset completion.');
document_check($task['description']==='Materiales de clase' && $task['task_time']==='08:30' && $task['priority']==='alta' && $task['highlight']==='pink','Inline editing erased task details.');
$revision=$state['revision']; $snapshot=$a->run($date);
$foreign=$b->run($date); $foreignCategory=$foreign['categories'][0]['id'];
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'tasks'=>[['id'=>$id,'title'=>'Should roll back']],'categories'=>[['id'=>$foreignCategory,'name'=>'Foreign']]]));
document_check($a->run($date)===$snapshot,'Rejected document edit was partially committed.');
document_reject(fn()=> $b->run($date,'edit_document',['revision'=>$foreign['revision'],'tasks'=>[['id'=>$id,'title'=>'Not mine']]]));
document_reject(fn()=> $a->run('2026-11-23','edit_document',['revision'=>$revision,'tasks'=>[['id'=>$id,'title'=>'Other date']]]));
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'tasks'=>[['id'=>$id,'title'=>'']]]));
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'categories'=>[['id'=>$category,'name'=>' ']]]));
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'tasks'=>[['id'=>$id,'title'=>'One'],['id'=>$id,'title'=>'Two']]]));
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'categories'=>[['id'=>$category,'name'=>'One'],['id'=>$category,'name'=>'Two']]]));
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'tasks'=>'invalid']));
document_reject(fn()=> $a->run($date,'edit_document',['revision'=>$revision,'tasks'=>array_fill(0,301,[])]));
document_check($a->run($date)===$snapshot,'Invalid editing changed the plan or revision.');
$state=$a->run($date,'save_task',['revision'=>$revision,'id'=>$id,'category_id'=>$secondCategory,'task_date'=>'2026-11-23','title'=>'Presentación revisada','description'=>'Llevar carteleras','task_time'=>'10:15','priority'=>'normal','highlight'=>'blue']);
$moved=$a->run('2026-11-23')['tasks'][0];
document_check($moved['id']===$id && $moved['title']==='Presentación revisada' && $state['tasks']===[],'Full task editing duplicated the task or did not move it to the chosen date.');
document_check($moved['done'] && $moved['completed_at']===$completed,'Editing details or moving a completed task reset its completion.');
document_check($moved['category_id']===$secondCategory && $moved['description']==='Llevar carteleras' && $moved['task_time']==='10:15' && $moved['priority']==='normal' && $moved['highlight']==='blue','The full task editor did not save all fields.');
echo "OK: $checks comprobaciones de edición en la hoja, conservación de detalles, propiedad, fecha y guardado atómico.\n";
