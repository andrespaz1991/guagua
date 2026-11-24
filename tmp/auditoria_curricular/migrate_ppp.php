<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require __DIR__.'/../../comun/database.php';
$folder=__DIR__.'/../../output/auditoria-emprendimiento-ppp-2026-11-01';
$main=__DIR__.'/../../output/auditoria-curricular-2026-11-01';
$mode=$argv[1]??'--dry-run';
if(!in_array($mode,['--dry-run','--apply','--verify'],true))throw new RuntimeException('Modo inválido.');
$keys=['grado'=>'id_grado','materia_oficial'=>'id_materia','estandar'=>'id_estandar','dba'=>'id_dba','eje_tematico'=>'id_eje_tematico','evidencia_de_aprendizaje'=>'id_evidencia_aprendizaje','periodo'=>'id_periodo'];
$db=GuaguaDatabase::pdo();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
function ppp_snapshot($db,$keys,$lock=false){$out=[];foreach($keys as $t=>$k)$out[$t]=$db->query("SELECT * FROM `$t` ORDER BY `$k`".($lock?' FOR UPDATE':''))->fetchAll(PDO::FETCH_ASSOC);return $out;}
function ppp_assert($condition,$message){if(!$condition)throw new RuntimeException($message);}
function ppp_write($db,$table,$key,$row,$id=null){
    if($id!==null){$parts=array_map(fn($col)=>"`$col`=?",array_keys($row));$db->prepare("UPDATE `$table` SET ".implode(',',$parts)." WHERE `$key`=?")->execute(array_merge(array_values($row),[$id]));return $id;}
    $db->prepare('INSERT INTO `'.$table.'` (`'.implode('`,`',array_keys($row)).'`) VALUES ('.implode(',',array_fill(0,count($row),'?')).')')->execute(array_values($row));return (int)$db->lastInsertId();
}
function ppp_save($path,$value){ppp_assert(file_put_contents($path,json_encode($value,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))!==false,'No se pudo guardar '.$path);}
$before=json_decode(file_get_contents($folder.'/respaldo-anterior.json'),true,512,JSON_THROW_ON_ERROR);
$plan=json_decode(file_get_contents($folder.'/plan-ppp.json'),true,512,JSON_THROW_ON_ERROR)['registros'];
if($mode==='--verify'){
    $expected=json_decode(file_get_contents($folder.'/snapshot-aplicado.json'),true,512,JSON_THROW_ON_ERROR);
    ppp_assert(ppp_snapshot($db,$keys)===$expected,'El estado actual difiere del resultado guardado.');
    echo "Verificación exacta correcta: Emprendimiento PPP persistido y Educación Física conservada según la malla.\n";exit;
}
ppp_assert(!is_file($folder.'/resultado-aplicacion.json'),'El ajuste ya fue aplicado.');
$lock=(int)$db->query("SELECT GET_LOCK('guagua_revision_curricular_6_9',10)")->fetchColumn();ppp_assert($lock===1,'Otra revisión está en ejecución.');
try{
    $db->beginTransaction();
    ppp_assert(ppp_snapshot($db,$keys,true)===$before,'La base cambió desde el respaldo; no se aplicó el ajuste.');
    $scope=['estandar'=>[],'dba'=>[],'eje_tematico'=>[],'evidencia_de_aprendizaje'=>[]];$contexts=[];
    foreach($before['estandar'] as $s)if((int)$s['id_materia_oficial']===9&&in_array((int)$s['grado'],[6,7,8,9],true)){$scope['estandar'][]=(int)$s['id_estandar'];$contexts[$s['grado'].'-'.$s['id_periodo']]=$s;}
    ppp_assert(count($contexts)===16&&count($plan)===16,'No existen los dieciséis contextos esperados.');
    foreach($before['dba'] as $d)if(in_array((int)$d['id_estandar'],$scope['estandar'],true))$scope['dba'][]=(int)$d['id_dba'];
    foreach(['eje_tematico','evidencia_de_aprendizaje'] as $t)foreach($before[$t] as $r)if(in_array((int)$r['id_dba'],$scope['dba'],true))$scope[$t][]=(int)$r[$keys[$t]];
    $afterIds=['estandar'=>[],'dba'=>[],'eje_tematico'=>[],'evidencia_de_aprendizaje'=>[]];$mapping=[];
    foreach($plan as $r){
        ppp_assert($r['materia']===9&&in_array($r['grado'],[6,7,8,9],true),'Contenido fuera de alcance.');
        $weeks=[];foreach($r['dbas'] as $d)foreach($d['ejes'] as $a)for($w=$a['inicio'];$w<=$a['fin'];$w++)$weeks[]=$w;
        sort($weeks);ppp_assert($weeks===range(1,10),'El periodo no contiene exactamente diez semanas.');
        $sid=(int)$contexts[$r['grado'].'-'.$r['periodo']]['id_estandar'];
        ppp_write($db,'estandar','id_estandar',['nombre_estandar'=>$r['estandar'],'descripcion_estandar'=>$r['descripcion_estandar']],$sid);$afterIds['estandar'][]=$sid;
        $oldD=array_values(array_filter($before['dba'],fn($d)=>(int)$d['id_estandar']===$sid));
        ppp_assert(count($oldD)===1&&count($r['dbas'])===1,'La relación DBA cambió; se requiere revisión.');
        $did=(int)$oldD[0]['id_dba'];$d=$r['dbas'][0];
        ppp_write($db,'dba','id_dba',['nombre_dba'=>$d['nombre'],'descripcion_dba'=>$d['descripcion'],'palabras_clave'=>$d['palabras_clave']],$did);$afterIds['dba'][]=$did;
        $context=['area'=>'emprendimiento','grado'=>$r['grado'],'periodo'=>$r['periodo'],'id_estandar'=>$sid,'dbas'=>[['id_dba'=>$did,'ejes'=>[],'evidencias'=>[]]]];
        foreach(['eje_tematico','evidencia_de_aprendizaje'] as $t){
            $old=array_values(array_filter($before[$t],fn($v)=>(int)$v['id_dba']===$did));
            $items=$t==='eje_tematico'?$d['ejes']:$d['evidencias'];
            foreach($items as $i=>$item){
                if($t==='eje_tematico'){$row=['nombre_eje_tematico'=>$item['nombre'],'descripcion_eje_tematico'=>$item['descripcion'],'id_dba'=>$did];ppp_assert(mb_strlen($item['nombre'],'UTF-8')<=255,'Eje demasiado largo.');}
                else{$row=['descripcion_evidencia'=>$item,'id_dba'=>$did,'origen'=>'revision_documental','modelo'=>null];}
                $id=ppp_write($db,$t,$keys[$t],$row,isset($old[$i])?(int)$old[$i][$keys[$t]]:null);$afterIds[$t][]=$id;
                $context['dbas'][0][$t==='eje_tematico'?'ejes':'evidencias'][]=$id;
            }
            for($i=count($items);$i<count($old);$i++)$db->prepare("DELETE FROM `$t` WHERE `{$keys[$t]}`=?")->execute([$old[$i][$keys[$t]]]);
        }$mapping[]=$context;
    }
    $after=ppp_snapshot($db,$keys);
    foreach($keys as $t=>$key){
        $a=array_values(array_filter($before[$t],fn($r)=>!in_array((int)$r[$key],$scope[$t]??[],true)));
        $b=array_values(array_filter($after[$t],fn($r)=>!in_array((int)$r[$key],$afterIds[$t]??[],true)));
        ppp_assert($a===$b,"Se modificaron filas fuera de Emprendimiento 6-9 en $t.");
    }
    $pe=array_values(array_filter($after['estandar'],fn($r)=>(int)$r['id_materia_oficial']===4&&in_array((int)$r['grado'],[6,7,8,9],true)));
    ppp_assert(count($pe)===16,'Falta algún periodo de Educación Física.');
    foreach($pe as $s)ppp_assert(strpos($s['descripcion_estandar'],'Educación física secundaria 6-11.docx')!==false,'Una fuente de Educación Física no es su malla.');
    $receipt=['fecha'=>date(DATE_ATOM),'alcance'=>'Emprendimiento 6 a 9; Educación Física verificada contra malla y conservada','antes'=>array_map('count',$scope),'despues'=>array_map('count',$afterIds),'ids_anteriores'=>$scope,'ids_resultado'=>$afterIds,'contextos'=>$mapping,'educacion_fisica_periodos_malla'=>16,'otras_materias'=>'sin cambios','plan_sha256'=>hash_file('sha256',$folder.'/plan-ppp.json')];
    if($mode==='--apply'){
        ppp_save($folder.'/snapshot-aplicado.json',$after);ppp_save($folder.'/resultado-aplicacion.json',$receipt);$db->commit();
    }else{$db->rollBack();ppp_assert(ppp_snapshot($db,$keys)===$before,'La simulación no revirtió correctamente.');ppp_save($folder.'/resultado-simulacion.json',$receipt);}
    echo json_encode(['modo'=>$mode,'antes'=>$receipt['antes'],'despues'=>$receipt['despues'],'educacion_fisica'=>'16 periodos según malla, sin cambios','otras_materias'=>'sin cambios'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}finally{$db->query("SELECT RELEASE_LOCK('guagua_revision_curricular_6_9')");}
