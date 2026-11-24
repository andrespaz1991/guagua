<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function fail(int $code, string $message): never { http_response_code($code); echo json_encode(['error'=>$message], JSON_UNESCAPED_UNICODE); exit; }
$file=__DIR__.'/inventario.json';
$_SESSION['inventory_token'] ??= bin2hex(random_bytes(24));
if ($_SERVER['REQUEST_METHOD']==='GET') {
    $raw=file_get_contents($file);
    echo json_encode(['inventario'=>json_decode($raw,true),'revision'=>hash('sha256',$raw),'token'=>$_SESSION['inventory_token']],JSON_UNESCAPED_UNICODE); exit;
}
if ($_SERVER['REQUEST_METHOD']!=='POST') fail(405,'Método no permitido.');
if (!hash_equals($_SESSION['inventory_token'],$_SERVER['HTTP_X_INVENTORY_TOKEN']??'')) fail(403,'Vuelve a abrir la galería para guardar.');
if ((int)($_SERVER['CONTENT_LENGTH']??0)>2000000) fail(413,'El inventario es demasiado grande.');
$request=json_decode(file_get_contents('php://input'),true);
$data=$request['inventario']??null;
if (!is_array($data)||!is_array($data['equipos']??null)) fail(422,'Inventario inválido.');
$ids=[]; $members=[]; $total=0;
foreach ($data['equipos'] as $item) {
    $id=(string)($item['id']??'');
    if (!preg_match('/^\d[\w-]*$/D',$id)||isset($ids[$id])||!is_string($item['nombre']??null)||trim($item['nombre'])==='') fail(422,'Revisa los IDs y nombres.');
    $ids[$id]=true;
    if (!in_array($item['estado']??'', ['BUENO','REGULAR','MALO','FUERA DE SERVICIO','MIXTO'],true)) fail(422,'Estado inválido.');
    if (!is_numeric($item['cantidad']??null)||$item['cantidad']<=0) fail(422,'La cantidad debe ser mayor que cero.');
    if (!is_array($item['imagenes']??null)) fail(422,'Fotografías inválidas.');
    foreach ($item['imagenes'] as $path) if (!is_string($path)||!preg_match('~^imagenes/'.preg_quote($id,'~').'/[\w .-]+\.(jpg|jpeg|png|webp)$~iD',$path)||str_contains($path,'..')) fail(422,'Ruta de fotografía inválida.');
    $sum=0;
    foreach (($item['registros']??[]) as $r) {
        $rid=(string)($r['id']??'');
        if (!preg_match('/^\d[\w-]*$/D',$rid)||isset($members[$rid])||!is_numeric($r['cantidad']??null)||$r['cantidad']<=0) fail(422,'Registro individual inválido.');
        if (!in_array($r['estado']??$item['estado'], ['BUENO','REGULAR','MALO','FUERA DE SERVICIO'],true)) fail(422,'Estado individual inválido.');
        $members[$rid]=true; $sum+=$r['cantidad'];
    }
    if ($sum && abs($sum-$item['cantidad'])>0.00001) fail(422,'La cantidad del grupo no coincide con sus registros.');
    $total+=$item['cantidad'];
}
$data['cantidad_total']=$total;
$data['registros_origen']=count($members);
$data['actualizado']=date(DATE_ATOM);
$lock=fopen(__DIR__.'/inventario.lock','c');
if (!$lock||!flock($lock,LOCK_EX)) fail(500,'No se pudo bloquear el inventario.');
$raw=file_get_contents($file);
if (!hash_equals(hash('sha256',$raw),(string)($request['revision']??''))) fail(409,'Otra ventana modificó el inventario. Recarga antes de guardar.');
$backup=__DIR__.'/respaldos';
if (!is_dir($backup)) mkdir($backup,0775,true);
if (file_put_contents($backup.'/inventario-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json',$raw)===false) fail(500,'No se pudo crear el respaldo.');
$encoded=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
$temp=tempnam(__DIR__,'inventory-');
if (file_put_contents($temp,$encoded)===false||!rename($temp,$file)) fail(500,'No se pudo guardar el inventario.');
flock($lock,LOCK_UN);fclose($lock);
echo json_encode(['inventario'=>$data,'revision'=>hash('sha256',$encoded)],JSON_UNESCAPED_UNICODE);
