<?php
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function uploadFail(int $code,string $message): never { http_response_code($code);echo json_encode(['error'=>$message],JSON_UNESCAPED_UNICODE);exit; }
if ($_SERVER['REQUEST_METHOD']!=='POST') uploadFail(405,'Método no permitido.');
if (!isset($_SESSION['inventory_token'])||!hash_equals($_SESSION['inventory_token'],$_SERVER['HTTP_X_INVENTORY_TOKEN']??'')) uploadFail(403,'Recarga la galería antes de subir fotografías.');
$id=(string)($_POST['id']??'');
if (!preg_match('/^\d[\w-]*$/D',$id)) uploadFail(422,'Producto inválido.');
$files=$_FILES['imagenes']??null;
if (!$files||!is_array($files['name'])||count($files['name'])>10) uploadFail(422,'Selecciona entre 1 y 10 fotografías. El tamaño total debe ser menor que el límite de PHP.');
$validated=[];$types=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];$finfo=new finfo(FILEINFO_MIME_TYPE);
foreach ($files['tmp_name'] as $i=>$temp) {
    if ($files['error'][$i]!==UPLOAD_ERR_OK) uploadFail(422,'No se pudo recibir una fotografía. Revisa el tamaño permitido por PHP.');
    if ($files['size'][$i]>8*1024*1024||!is_uploaded_file($temp)) uploadFail(422,'Cada fotografía debe pesar hasta 8 MB.');
    $type=$finfo->file($temp);$size=@getimagesize($temp);
    if (!isset($types[$type])||!$size||$size[0]*$size[1]>40000000) uploadFail(422,'Usa fotografías JPG, PNG o WebP de hasta 40 megapíxeles.');
    $validated[]=[$temp,'foto-'.date('Ymd-His').'-'.bin2hex(random_bytes(6)).'.'.$types[$type]];
}
$lock=fopen(__DIR__.'/inventario.lock','c');if(!$lock||!flock($lock,LOCK_EX)) uploadFail(500,'No se pudo bloquear el inventario.');
$file=__DIR__.'/inventario.json';$raw=file_get_contents($file);
if(!hash_equals(hash('sha256',$raw),(string)($_POST['revision']??''))) uploadFail(409,'Otra ventana modificó el inventario. Recarga antes de subir.');
$data=json_decode($raw,true);$index=null;
foreach($data['equipos'] as $i=>$item) if((string)$item['id']===$id){$index=$i;break;}
if($index===null) uploadFail(404,'El producto ya no existe.');
$folder=__DIR__.'/imagenes/'.$id;if(!is_dir($folder)&&!mkdir($folder,0775,true)) uploadFail(500,'No se pudo crear la carpeta de fotografías.');
$backup=__DIR__.'/respaldos';if(!is_dir($backup))mkdir($backup,0775,true);
if(file_put_contents($backup.'/inventario-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json',$raw)===false) uploadFail(500,'No se pudo crear el respaldo.');
$saved=[];$paths=[];
foreach($validated as [$temp,$name]){
    $target=$folder.'/'.$name;
    if(!move_uploaded_file($temp,$target)){foreach($saved as $path)unlink($path);uploadFail(500,'No se pudieron guardar las fotografías.');}
    $saved[]=$target;$paths[]='imagenes/'.$id.'/'.$name;
}
$data['equipos'][$index]['imagenes']=array_merge($data['equipos'][$index]['imagenes']??[],$paths);
$data['actualizado']=date(DATE_ATOM);$encoded=json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)."\n";$temp=tempnam(__DIR__,'inventory-');
if(file_put_contents($temp,$encoded)===false||!rename($temp,$file)){foreach($saved as $path)unlink($path);uploadFail(500,'No se pudieron asociar las fotografías.');}
flock($lock,LOCK_UN);fclose($lock);
echo json_encode(['inventario'=>$data,'revision'=>hash('sha256',$encoded),'imagenes'=>$paths],JSON_UNESCAPED_UNICODE);
