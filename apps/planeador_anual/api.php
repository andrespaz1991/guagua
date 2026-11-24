<?php
/** API del Planeador anual institucional. */
@session_start();
header('Content-Type: application/json; charset=utf-8');
require_once '../../comun/conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Tu sesión ha finalizado. Inicia sesión para usar el planeador.']);
    exit;
}

$userId = (string) $_SESSION['id_usuario'];
$institutionId = (string) ($_SESSION['id_institucion'] ?? '0');
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

function respond($payload) { echo json_encode($payload, JSON_UNESCAPED_UNICODE); exit; }
function clean($value) { return trim(strip_tags((string) $value)); }

function setupPlanner($db) {
    $db->query("CREATE TABLE IF NOT EXISTS planner_annual_plans (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, institution_id VARCHAR(40) NOT NULL, user_id VARCHAR(40) NOT NULL,
        name VARCHAR(160) NOT NULL, academic_year INT NOT NULL, color VARCHAR(12) NOT NULL DEFAULT '#635BFF',
        description TEXT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_planner_owner (institution_id, user_id, academic_year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS planner_annual_items (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, plan_id INT UNSIGNED NOT NULL, title VARCHAR(200) NOT NULL, description TEXT NULL,
        start_date DATE NOT NULL, due_date DATE NOT NULL, phase VARCHAR(80) NOT NULL DEFAULT 'General', priority ENUM('alta','media','baja') NOT NULL DEFAULT 'media',
        status ENUM('pendiente','en_progreso','completado','bloqueado') NOT NULL DEFAULT 'pendiente', color VARCHAR(12) NOT NULL DEFAULT '#635BFF',
        resource_url VARCHAR(500) NULL, progress TINYINT UNSIGNED NOT NULL DEFAULT 0, sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_planner_items (plan_id, due_date), CONSTRAINT fk_planner_plan FOREIGN KEY (plan_id) REFERENCES planner_annual_plans(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS planner_annual_checklist (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, item_id INT UNSIGNED NOT NULL, title VARCHAR(200) NOT NULL, is_done TINYINT(1) NOT NULL DEFAULT 0, sort_order INT NOT NULL DEFAULT 0,
        CONSTRAINT fk_planner_item FOREIGN KEY (item_id) REFERENCES planner_annual_items(id) ON DELETE CASCADE,
        INDEX idx_planner_checklist (item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $db->query("CREATE TABLE IF NOT EXISTS planner_annual_phases (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, plan_id INT UNSIGNED NOT NULL, name VARCHAR(80) NOT NULL, color VARCHAR(12) NOT NULL DEFAULT '#635BFF', sort_order INT NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_planner_phase (plan_id, name),
        CONSTRAINT fk_planner_phase_plan FOREIGN KEY (plan_id) REFERENCES planner_annual_plans(id) ON DELETE CASCADE,
        INDEX idx_planner_phase (plan_id, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function syncPlannerPhases($db, $institutionId, $userId) {
    $sql = "INSERT IGNORE INTO planner_annual_phases (plan_id, name, color, sort_order) SELECT i.plan_id, i.phase, COALESCE(MIN(i.color), '#635BFF'), 0 FROM planner_annual_items i INNER JOIN planner_annual_plans p ON p.id=i.plan_id WHERE p.institution_id=? AND p.user_id=? AND TRIM(i.phase)<>'' GROUP BY i.plan_id, i.phase";
    $s=$db->prepare($sql); $s->bind_param('ss',$institutionId,$userId); $s->execute(); $s->close();
}
function ensurePhase($db, $planId, $name, $color='#635BFF') {
    $name=trim($name) ?: 'General';
    $s=$db->prepare('INSERT IGNORE INTO planner_annual_phases (plan_id,name,color) VALUES (?,?,?)');$s->bind_param('iss',$planId,$name,$color);$s->execute();$s->close();
}
function normalizeInitialItemOrder($db, $institutionId, $userId) {
    $plans=$db->prepare('SELECT id FROM planner_annual_plans WHERE institution_id=? AND user_id=?');$plans->bind_param('ss',$institutionId,$userId);$plans->execute();$planRows=$plans->get_result()->fetch_all(MYSQLI_ASSOC);$plans->close();
    foreach($planRows as $plan){$planId=(int)$plan['id'];$check=$db->prepare('SELECT MAX(sort_order) AS max_order FROM planner_annual_items WHERE plan_id=?');$check->bind_param('i',$planId);$check->execute();$max=(int)($check->get_result()->fetch_assoc()['max_order']??0);$check->close();if($max>0)continue;$items=$db->prepare('SELECT id FROM planner_annual_items WHERE plan_id=? ORDER BY phase,start_date,due_date,id');$items->bind_param('i',$planId);$items->execute();$rows=$items->get_result()->fetch_all(MYSQLI_ASSOC);$items->close();$update=$db->prepare('UPDATE planner_annual_items SET sort_order=? WHERE id=?');foreach($rows as $index=>$row){$order=($index+1)*10;$id=(int)$row['id'];$update->bind_param('ii',$order,$id);$update->execute();}$update->close();}
}
function normalizeInitialProcessOrder($db, $institutionId, $userId) {
    $plans=$db->prepare('SELECT id FROM planner_annual_plans WHERE institution_id=? AND user_id=?');$plans->bind_param('ss',$institutionId,$userId);$plans->execute();$planRows=$plans->get_result()->fetch_all(MYSQLI_ASSOC);$plans->close();
    foreach($planRows as $plan){$planId=(int)$plan['id'];$check=$db->prepare('SELECT MAX(sort_order) AS max_order FROM planner_annual_phases WHERE plan_id=?');$check->bind_param('i',$planId);$check->execute();$max=(int)($check->get_result()->fetch_assoc()['max_order']??0);$check->close();if($max>0)continue;$rowsQuery=$db->prepare('SELECT ph.id FROM planner_annual_phases ph LEFT JOIN planner_annual_items i ON i.plan_id=ph.plan_id AND i.phase=ph.name WHERE ph.plan_id=? GROUP BY ph.id ORDER BY COALESCE(MIN(i.start_date),"9999-12-31"),ph.id');$rowsQuery->bind_param('i',$planId);$rowsQuery->execute();$rows=$rowsQuery->get_result()->fetch_all(MYSQLI_ASSOC);$rowsQuery->close();$update=$db->prepare('UPDATE planner_annual_phases SET sort_order=? WHERE id=?');foreach($rows as $index=>$row){$order=($index+1)*10;$id=(int)$row['id'];$update->bind_param('ii',$order,$id);$update->execute();}$update->close();}
}

function belongsToPlan($db, $planId, $institutionId, $userId) {
    $s = $db->prepare('SELECT id FROM planner_annual_plans WHERE id=? AND institution_id=? AND user_id=?');
    $s->bind_param('iss', $planId, $institutionId, $userId); $s->execute();
    $ok = (bool) $s->get_result()->fetch_assoc(); $s->close(); return $ok;
}
function seedPlanner($db, $institutionId, $userId) {
    $year = (int) guagua_ano_configurado($db);
    $check = $db->prepare('SELECT id FROM planner_annual_plans WHERE institution_id=? AND user_id=? AND academic_year=? LIMIT 1');
    $check->bind_param('ssi', $institutionId, $userId, $year); $check->execute(); $exists = $check->get_result()->fetch_assoc(); $check->close();
    if ($exists) return;
    $name = 'Ruta institucional ' . $year; $color = '#635BFF'; $description = 'Seguimiento de los hitos clave del año escolar.';
    $s = $db->prepare('INSERT INTO planner_annual_plans (institution_id,user_id,name,academic_year,color,description) VALUES (?,?,?,?,?,?)');
    $s->bind_param('sssiss', $institutionId, $userId, $name, $year, $color, $description); $s->execute(); $planId = $s->insert_id; $s->close();
    $events = [
      ['Entrega de matrículas', "$year-01-15", "$year-01-31", 'Inicio de año', 'alta', 'completado', '#22C55E', ['Validar documentos', 'Entregar carpeta de matrícula', 'Registrar novedades']],
      ['Entrega de útiles escolares', "$year-02-03", "$year-02-14", 'Inicio de año', 'alta', 'completado', '#3B82F6', ['Confirmar inventario', 'Preparar kits por grado', 'Registrar entregas']],
      ['Diagnóstico y caracterización', "$year-02-17", "$year-03-07", 'Gestión académica', 'media', 'en_progreso', '#F59E0B', ['Publicar instrumentos', 'Consolidar resultados', 'Socializar hallazgos']],
      ['Primer informe a familias', "$year-04-06", "$year-04-17", 'Seguimiento', 'alta', 'pendiente', '#8B5CF6', ['Cerrar valoraciones', 'Revisar observaciones', 'Enviar informes']],
      ['Feria institucional', "$year-09-14", "$year-09-25", 'Comunidad', 'media', 'pendiente', '#EC4899', ['Definir responsables', 'Gestionar recursos', 'Confirmar programación']],
      ['Cierre académico', "$year-11-16", "$year-12-04", 'Cierre de año', 'alta', 'pendiente', '#EF4444', ['Validar promociones', 'Cerrar plataformas', 'Entregar informes finales']]
    ];
    foreach ($events as $order => $event) {
      [$title,$start,$due,$phase,$priority,$status,$color,$checks] = $event;
      $progress = $status === 'completado' ? 100 : ($status === 'en_progreso' ? 45 : 0);
      $stmt = $db->prepare('INSERT INTO planner_annual_items (plan_id,title,start_date,due_date,phase,priority,status,color,progress,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?)');
      $stmt->bind_param('isssssssii', $planId,$title,$start,$due,$phase,$priority,$status,$color,$progress,$order); $stmt->execute(); $itemId=$stmt->insert_id; $stmt->close();
      foreach ($checks as $checkOrder => $checkTitle) { $done = $status === 'completado' ? 1 : 0; $c=$db->prepare('INSERT INTO planner_annual_checklist (item_id,title,is_done,sort_order) VALUES (?,?,?,?)'); $c->bind_param('isii',$itemId,$checkTitle,$done,$checkOrder); $c->execute(); $c->close(); }
    }
}
function dashboard($db, $institutionId, $userId) {
    $planId = max(0, intval($_GET['plan_id'] ?? 0));
    $plans = [];
    $s=$db->prepare('SELECT * FROM planner_annual_plans WHERE institution_id=? AND user_id=? ORDER BY academic_year DESC, id DESC'); $s->bind_param('ss',$institutionId,$userId); $s->execute(); $plans=$s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();
    if (!$plans) return ['plans'=>[], 'active_plan'=>null, 'items'=>[], 'stats'=>[]];
    $anoConfigurado = (int)guagua_ano_configurado($db);
    $predeterminado = array_values(array_filter($plans, fn($p) => (int)$p['academic_year'] === $anoConfigurado));
    if (!$planId || !belongsToPlan($db,$planId,$institutionId,$userId)) $planId = intval(($predeterminado[0] ?? $plans[0])['id']);
    $s=$db->prepare('SELECT * FROM planner_annual_items WHERE plan_id=? ORDER BY phase, sort_order, start_date, id'); $s->bind_param('i',$planId); $s->execute(); $items=$s->get_result()->fetch_all(MYSQLI_ASSOC); $s->close();
    foreach ($items as &$item) { $item['id']=(int)$item['id']; $item['progress']=(int)$item['progress']; $q=$db->prepare('SELECT id,title,is_done FROM planner_annual_checklist WHERE item_id=? ORDER BY sort_order,id'); $q->bind_param('i',$item['id']); $q->execute(); $item['checklist']=$q->get_result()->fetch_all(MYSQLI_ASSOC); $q->close(); }
    unset($item);
    $today=date('Y-m-d'); $total=count($items); $done=count(array_filter($items,fn($i)=>$i['status']==='completado')); $blocked=count(array_filter($items,fn($i)=>$i['status']==='bloqueado')); $late=count(array_filter($items,fn($i)=>$i['due_date']<$today && $i['status']!=='completado'));
    $progress=$total ? round(array_sum(array_column($items,'progress'))/$total) : 0;
    $phaseStmt=$db->prepare('SELECT ph.*, COUNT(i.id) AS item_count FROM planner_annual_phases ph LEFT JOIN planner_annual_items i ON i.plan_id=ph.plan_id AND i.phase=ph.name WHERE ph.plan_id=? GROUP BY ph.id ORDER BY ph.sort_order,ph.id');$phaseStmt->bind_param('i',$planId);$phaseStmt->execute();$phases=$phaseStmt->get_result()->fetch_all(MYSQLI_ASSOC);$phaseStmt->close();
    $active=array_values(array_filter($plans,fn($p)=>(int)$p['id']===$planId))[0];
    return compact('plans','items','active','phases','total','done','blocked','late','progress') + ['academic_year'=>$anoConfigurado, 'active_plan'=>$active, 'stats'=>compact('total','done','blocked','late','progress')];
}

try { setupPlanner($mysqli); seedPlanner($mysqli,$institutionId,$userId); syncPlannerPhases($mysqli,$institutionId,$userId); normalizeInitialProcessOrder($mysqli,$institutionId,$userId); normalizeInitialItemOrder($mysqli,$institutionId,$userId); } catch (Throwable $e) { respond(['success'=>false,'message'=>'No fue posible preparar el planeador: '.$e->getMessage()]); }

if ($action === 'dashboard') respond(['success'=>true,'data'=>dashboard($mysqli,$institutionId,$userId)]);
if ($action === 'save_plan') {
    $id=intval($input['id']??0); $name=clean($input['name']??''); $year=max(2020,min(2100,intval($input['academic_year']??guagua_ano_configurado($mysqli)))); $color=clean($input['color']??'#635BFF'); $description=clean($input['description']??'');
    if (!$name) respond(['success'=>false,'message'=>'El nombre del plan es obligatorio.']);
    if ($id && !belongsToPlan($mysqli,$id,$institutionId,$userId)) respond(['success'=>false,'message'=>'No tienes permiso para editar este plan.']);
    if ($id) { $s=$mysqli->prepare('UPDATE planner_annual_plans SET name=?,academic_year=?,color=?,description=? WHERE id=?'); $s->bind_param('sissi',$name,$year,$color,$description,$id); $s->execute(); $s->close(); }
    else { $s=$mysqli->prepare('INSERT INTO planner_annual_plans (institution_id,user_id,name,academic_year,color,description) VALUES (?,?,?,?,?,?)'); $s->bind_param('sssiss',$institutionId,$userId,$name,$year,$color,$description); $s->execute(); $id=$s->insert_id; $s->close(); }
    respond(['success'=>true,'id'=>$id,'message'=>'Plan guardado correctamente.']);
}
if ($action === 'delete_plan') { $id=intval($input['id']??0); if (!belongsToPlan($mysqli,$id,$institutionId,$userId)) respond(['success'=>false,'message'=>'Plan no encontrado.']); $s=$mysqli->prepare('DELETE FROM planner_annual_plans WHERE id=?');$s->bind_param('i',$id);$s->execute();$s->close();respond(['success'=>true]); }
if ($action === 'save_item') {
    $id=intval($input['id']??0); $planId=intval($input['plan_id']??0); $title=clean($input['title']??''); $description=clean($input['description']??''); $start=clean($input['start_date']??''); $due=clean($input['due_date']??''); $phase=clean($input['phase']??'General'); $priority=clean($input['priority']??'media'); $status=clean($input['status']??'pendiente'); $color=clean($input['color']??'#635BFF'); $url=filter_var(trim($input['resource_url']??''),FILTER_VALIDATE_URL)?trim($input['resource_url']):''; $progress=max(0,min(100,intval($input['progress']??0))); $checks=$input['checklist']??[];
    if (!$title || !$start || !$due || !belongsToPlan($mysqli,$planId,$institutionId,$userId)) respond(['success'=>false,'message'=>'Completa título, fechas y plan válido.']);
    if ($due<$start) respond(['success'=>false,'message'=>'La fecha final debe ser igual o posterior a la inicial.']);
    if ($status==='completado') $progress=100;
    if ($id) { $own=$mysqli->prepare('SELECT i.id FROM planner_annual_items i JOIN planner_annual_plans p ON p.id=i.plan_id WHERE i.id=? AND p.institution_id=? AND p.user_id=?');$own->bind_param('iss',$id,$institutionId,$userId);$own->execute();$ok=$own->get_result()->fetch_assoc();$own->close();if(!$ok) respond(['success'=>false,'message'=>'Elemento no encontrado.']); $s=$mysqli->prepare('UPDATE planner_annual_items SET title=?,description=?,start_date=?,due_date=?,phase=?,priority=?,status=?,color=?,resource_url=?,progress=? WHERE id=?');$s->bind_param('sssssssssii',$title,$description,$start,$due,$phase,$priority,$status,$color,$url,$progress,$id);$s->execute();$s->close(); $d=$mysqli->prepare('DELETE FROM planner_annual_checklist WHERE item_id=?');$d->bind_param('i',$id);$d->execute();$d->close(); }
    else { $last=$mysqli->prepare('SELECT COALESCE(MAX(sort_order),0)+10 AS next_order FROM planner_annual_items WHERE plan_id=? AND phase=?');$last->bind_param('is',$planId,$phase);$last->execute();$sortOrder=(int)($last->get_result()->fetch_assoc()['next_order']??10);$last->close();$s=$mysqli->prepare('INSERT INTO planner_annual_items (plan_id,title,description,start_date,due_date,phase,priority,status,color,resource_url,progress,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');$s->bind_param('isssssssssii',$planId,$title,$description,$start,$due,$phase,$priority,$status,$color,$url,$progress,$sortOrder);$s->execute();$id=$s->insert_id;$s->close(); }
    ensurePhase($mysqli,$planId,$phase,$color);
    foreach ($checks as $order=>$check) { $ct=clean(is_array($check)?($check['title']??''):$check); $done=is_array($check)&&!empty($check['is_done'])?1:0; if($ct){$c=$mysqli->prepare('INSERT INTO planner_annual_checklist (item_id,title,is_done,sort_order) VALUES (?,?,?,?)');$c->bind_param('isii',$id,$ct,$done,$order);$c->execute();$c->close();} }
    respond(['success'=>true,'id'=>$id,'message'=>'Elemento guardado.']);
}
if ($action === 'delete_item') { $id=intval($input['id']??0);$s=$mysqli->prepare('DELETE i FROM planner_annual_items i JOIN planner_annual_plans p ON p.id=i.plan_id WHERE i.id=? AND p.institution_id=? AND p.user_id=?');$s->bind_param('iss',$id,$institutionId,$userId);$s->execute();$s->close();respond(['success'=>true]); }
if ($action === 'reorder_items') {
    $planId=intval($input['plan_id']??0);$ids=array_values(array_unique(array_filter(array_map('intval',$input['ids']??[]))));
    if(!$ids||!belongsToPlan($mysqli,$planId,$institutionId,$userId))respond(['success'=>false,'message'=>'No fue posible ordenar los elementos.']);
    $holders=implode(',',array_fill(0,count($ids),'?'));$types='i'.str_repeat('i',count($ids));$params=array_merge([$planId],$ids);$s=$mysqli->prepare("SELECT id FROM planner_annual_items WHERE plan_id=? AND id IN ($holders)");$s->bind_param($types,...$params);$s->execute();$found=$s->get_result()->fetch_all(MYSQLI_ASSOC);$s->close();if(count($found)!==count($ids))respond(['success'=>false,'message'=>'Uno o más elementos no pertenecen a este plan.']);
    $mysqli->begin_transaction();try{$update=$mysqli->prepare('UPDATE planner_annual_items SET sort_order=? WHERE id=? AND plan_id=?');foreach($ids as $index=>$id){$order=($index+1)*10;$update->bind_param('iii',$order,$id,$planId);$update->execute();}$update->close();$mysqli->commit();respond(['success'=>true]);}catch(Throwable $e){$mysqli->rollback();respond(['success'=>false,'message'=>'No se pudo guardar el nuevo orden.']);}
}
if ($action === 'toggle_check') { $id=intval($input['id']??0);$done=!empty($input['is_done'])?1:0;$s=$mysqli->prepare('UPDATE planner_annual_checklist c JOIN planner_annual_items i ON i.id=c.item_id JOIN planner_annual_plans p ON p.id=i.plan_id SET c.is_done=? WHERE c.id=? AND p.institution_id=? AND p.user_id=?');$s->bind_param('iiss',$done,$id,$institutionId,$userId);$s->execute();$s->close();respond(['success'=>true]); }
if ($action === 'save_phase') {
    $id=intval($input['id']??0);$planId=intval($input['plan_id']??0);$name=clean($input['name']??'');$color=clean($input['color']??'#635BFF');$order=max(0,intval($input['sort_order']??0));
    if(!$name||!belongsToPlan($mysqli,$planId,$institutionId,$userId))respond(['success'=>false,'message'=>'Completa el nombre del proceso.']);
    if($id){
        $own=$mysqli->prepare('SELECT ph.name FROM planner_annual_phases ph JOIN planner_annual_plans p ON p.id=ph.plan_id WHERE ph.id=? AND ph.plan_id=? AND p.institution_id=? AND p.user_id=?');$own->bind_param('iiss',$id,$planId,$institutionId,$userId);$own->execute();$old=$own->get_result()->fetch_assoc();$own->close();if(!$old)respond(['success'=>false,'message'=>'Proceso no encontrado.']);
        $s=$mysqli->prepare('UPDATE planner_annual_phases SET name=?,color=?,sort_order=? WHERE id=?');$s->bind_param('ssii',$name,$color,$order,$id);if(!$s->execute())respond(['success'=>false,'message'=>'Ya existe una etapa con ese nombre.']);$s->close();
        $u=$mysqli->prepare('UPDATE planner_annual_items SET phase=?,color=? WHERE plan_id=? AND phase=?');$u->bind_param('ssis',$name,$color,$planId,$old['name']);$u->execute();$u->close();
    } else {
        $s=$mysqli->prepare('INSERT INTO planner_annual_phases (plan_id,name,color,sort_order) VALUES (?,?,?,?)');$s->bind_param('issi',$planId,$name,$color,$order);if(!$s->execute())respond(['success'=>false,'message'=>'Ya existe una etapa con ese nombre.']);$id=$s->insert_id;$s->close();
    }
    respond(['success'=>true,'id'=>$id,'message'=>'Proceso guardado.']);
}
if ($action === 'delete_phase') {
    $id=intval($input['id']??0);$s=$mysqli->prepare('SELECT ph.plan_id,ph.name FROM planner_annual_phases ph JOIN planner_annual_plans p ON p.id=ph.plan_id WHERE ph.id=? AND p.institution_id=? AND p.user_id=?');$s->bind_param('iss',$id,$institutionId,$userId);$s->execute();$phase=$s->get_result()->fetch_assoc();$s->close();if(!$phase)respond(['success'=>false,'message'=>'Proceso no encontrado.']);
    ensurePhase($mysqli,$phase['plan_id'],'General','#64748B');$u=$mysqli->prepare("UPDATE planner_annual_items SET phase='General',color='#64748B' WHERE plan_id=? AND phase=?");$u->bind_param('is',$phase['plan_id'],$phase['name']);$u->execute();$u->close();$d=$mysqli->prepare('DELETE FROM planner_annual_phases WHERE id=?');$d->bind_param('i',$id);$d->execute();$d->close();respond(['success'=>true]);
}
respond(['success'=>false,'message'=>'Acción no válida.']);
?>
