<?php
/** Reporte PDF del flujo del plan anual. */
@session_start();
require_once '../../comun/conexion.php';
require_once '../../comun/lib/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    exit('Tu sesión ha finalizado. Inicia sesión para descargar el reporte.');
}

$userId=(string)$_SESSION['id_usuario'];
$institutionId=(string)($_SESSION['id_institucion']??'0');
$planId=max(0,(int)($_GET['plan_id']??0));
$phase=trim($_GET['phase']??'');

$plans=$mysqli->prepare('SELECT * FROM planner_annual_plans WHERE id=? AND institution_id=? AND user_id=? LIMIT 1');
$plans->bind_param('iss',$planId,$institutionId,$userId);$plans->execute();$plan=$plans->get_result()->fetch_assoc();$plans->close();
if(!$plan){http_response_code(404);exit('Plan no encontrado.');}

$sql='SELECT * FROM planner_annual_items WHERE plan_id=?';$types='i';$params=[$planId];
if($phase!==''){$sql.=' AND phase=?';$types.='s';$params[]=$phase;}
$sql.=' ORDER BY phase,start_date,due_date,id';
$itemsQuery=$mysqli->prepare($sql);$itemsQuery->bind_param($types,...$params);$itemsQuery->execute();$items=$itemsQuery->get_result()->fetch_all(MYSQLI_ASSOC);$itemsQuery->close();

$grouped=[];
foreach($items as $item){
    $item['checklist']=[];$check=$mysqli->prepare('SELECT title,is_done FROM planner_annual_checklist WHERE item_id=? ORDER BY sort_order,id');$check->bind_param('i',$item['id']);$check->execute();$item['checklist']=$check->get_result()->fetch_all(MYSQLI_ASSOC);$check->close();
    $grouped[$item['phase']][]=$item;
}
$phaseOrder=[];$phases=$mysqli->prepare('SELECT name,color FROM planner_annual_phases WHERE plan_id=? ORDER BY sort_order,id');$phases->bind_param('i',$planId);$phases->execute();foreach($phases->get_result()->fetch_all(MYSQLI_ASSOC) as $p){$phaseOrder[$p['name']]=$p['color'];}$phases->close();
foreach(array_keys($grouped) as $name){if(!isset($phaseOrder[$name]))$phaseOrder[$name]='#635BFF';}

function e($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function estado($status){return ['pendiente'=>'Pendiente','en_progreso'=>'En progreso','completado'=>'Completado','bloqueado'=>'Bloqueado'][$status]??ucfirst($status);}
function fecha($date){$time=strtotime($date);return $time?date('d/m/Y',$time):'';}

$flow='';$num=1;foreach($phaseOrder as $name=>$color){if(!isset($grouped[$name]))continue;$flow.='<span class="flow-step"><b>'.$num.'</b>'.e($name).'</span>';if($num<count($grouped))$flow.='<span class="flow-arrow">→</span>';$num++;}
$content='';$num=1;
foreach($phaseOrder as $name=>$color){
    if(!isset($grouped[$name]))continue;
    $content.='<section class="process"><div class="process-title"><span class="number">'.$num.'</span><span class="color" style="background:'.e($color).'"></span><div><h2>'.e($name).'</h2><p>'.count($grouped[$name]).' elemento(s) del proceso</p></div></div><ol type="a">';
    foreach($grouped[$name] as $item){
        $content.='<li><div class="item-head"><strong>'.e($item['title']).'</strong><span class="badge '.e($item['status']).'">'.e(estado($item['status'])).'</span></div><div class="meta">'.fecha($item['start_date']).' - '.fecha($item['due_date']).' &nbsp; · &nbsp; Avance: '.(int)$item['progress'].'%</div>';
        if(trim($item['description'])!=='')$content.='<p class="description">'.nl2br(e($item['description'])).'</p>';
        if($item['checklist']){$content.='<ul class="checklist">';foreach($item['checklist'] as $check){$content.='<li class="'.((int)$check['is_done']?'done':'').'">'.((int)$check['is_done']?'✓':'□').' '.e($check['title']).'</li>';}$content.='</ul>';}
        if(trim($item['resource_url'])!=='')$content.='<p class="resource">Recurso: '.e($item['resource_url']).'</p>';
        $content.='</li>';
    }
    $content.='</ol></section>';$num++;
}
if($content==='')$content='<div class="empty">No hay elementos registrados para este filtro.</div>';

$filterText=$phase!==''?'Proceso seleccionado: '.e($phase):'Todos los procesos del plan';
$html='<!doctype html><html lang="es"><head><meta charset="utf-8"><style>
@page{margin:25mm 18mm 20mm}body{font-family:DejaVu Sans,Arial,sans-serif;color:#20213e;font-size:10pt;line-height:1.45}.head{border-bottom:3px solid #635BFF;padding-bottom:12px;margin-bottom:16px}.eyebrow{font-size:8pt;color:#635BFF;font-weight:bold;letter-spacing:1px}.head h1{font-size:22pt;margin:5px 0}.head p{margin:0;color:#676a80}.summary{background:#f5f4ff;border:1px solid #e4e1ff;padding:11px 13px;margin:14px 0;border-radius:6px}.summary b{color:#5148e4}.flow{padding:10px 0 17px;border-bottom:1px solid #e6e7ef;margin-bottom:14px}.flow-label{font-size:8pt;font-weight:bold;color:#77798c;letter-spacing:.7px;margin-bottom:6px}.flow-step{display:inline-block;background:#f1f0ff;color:#5148e4;padding:5px 8px;margin:2px;border-radius:12px;font-size:8.5pt}.flow-step b{background:#635BFF;color:#fff;border-radius:50%;padding:1px 5px;margin-right:4px}.flow-arrow{color:#a2a4b5}.process{page-break-inside:avoid;margin:17px 0}.process-title{display:flex;align-items:center;gap:8px;border-bottom:1px solid #dddfea;padding-bottom:7px}.number{background:#29264f;color:#fff;width:22px;height:22px;text-align:center;line-height:22px;border-radius:50%;font-weight:bold}.color{width:8px;height:26px;border-radius:4px}.process-title h2{font-size:13pt;margin:0}.process-title p{font-size:8.5pt;color:#797c91;margin:0}ol{margin:10px 0 0 22px;padding-left:14px}ol>li{padding:8px 0 10px 5px;border-bottom:1px solid #ececf2}.item-head strong{font-size:10.5pt}.badge{float:right;border-radius:10px;padding:2px 7px;font-size:7.5pt}.pendiente{background:#eef0f5;color:#697084}.en_progreso{background:#fff1d7;color:#9c6608}.completado{background:#e5f8ec;color:#187749}.bloqueado{background:#ffe8ec;color:#c33e53}.meta,.resource{font-size:8pt;color:#777a8c;margin:3px 0}.description{margin:5px 0;font-size:9pt}.checklist{list-style:none;margin:6px 0;padding:0}.checklist li{font-size:8.5pt;margin:3px 0}.checklist .done{color:#198653}.empty{padding:25px;background:#f7f7fa;text-align:center;color:#777a8c}.footer{position:fixed;bottom:-10mm;left:0;right:0;border-top:1px solid #dddfea;padding-top:5px;color:#86889a;font-size:8pt}.footer .page:after{content:counter(page)}</style></head><body>
<div class="head"><div class="eyebrow">PLANEACIÓN INSTITUCIONAL · REPORTE DE FLUJO</div><h1>'.e($plan['name']).'</h1><p>Año lectivo '.e($plan['academic_year']).' · Generado el '.date('d/m/Y H:i').'</p></div><div class="summary"><b>'.$filterText.'</b><br>Listado ordenado de los elementos que se deben ejecutar dentro de cada proceso.</div><div class="flow"><div class="flow-label">FLUJO DEL PROCESO</div>'.$flow.'</div>'.$content.'<div class="footer">Planeador anual · Página <span class="page"></span></div></body></html>';

$options=new Options();$options->set('isRemoteEnabled',false);$options->set('defaultFont','DejaVu Sans');$dompdf=new Dompdf($options);$dompdf->loadHtml($html,'UTF-8');$dompdf->setPaper('letter','portrait');$dompdf->render();
$fileName='reporte-flujo-'.preg_replace('/[^a-z0-9]+/i','-',strtolower($plan['name'])).'.pdf';$dompdf->stream($fileName,['Attachment'=>true]);
?>
