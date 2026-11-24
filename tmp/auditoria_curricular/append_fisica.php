<?php
if(PHP_SAPI!=='cli') exit;
require __DIR__.'/../../comun/database.php';
$dir=__DIR__.'/../../output/auditoria-curricular-2026-11-01';
$plan=json_decode(file_get_contents($dir.'/plan-curricular.json'),true,512,JSON_THROW_ON_ERROR);
$receipt=json_decode(file_get_contents($dir.'/resultado-aplicacion.json'),true,512,JSON_THROW_ON_ERROR);
$expected=json_decode(file_get_contents($dir.'/snapshot-aplicado.json'),true,512,JSON_THROW_ON_ERROR);
$keys=['grado'=>'id_grado','materia_oficial'=>'id_materia','estandar'=>'id_estandar','dba'=>'id_dba','eje_tematico'=>'id_eje_tematico','evidencia_de_aprendizaje'=>'id_evidencia_aprendizaje','periodo'=>'id_periodo'];
$db=GuaguaDatabase::pdo();
function ok($v,$m){if(!$v)throw new RuntimeException($m);}
function snap($db,$keys,$lock=false){$s=[];foreach($keys as $t=>$k)$s[$t]=$db->query("SELECT * FROM `$t` ORDER BY `$k`".($lock?' FOR UPDATE':''))->fetchAll(PDO::FETCH_ASSOC);return $s;}
function put($db,$t,$r){$db->prepare('INSERT INTO `'.$t.'` (`'.implode('`,`',array_keys($r)).'`) VALUES ('.implode(',',array_fill(0,count($r),'?')).')')->execute(array_values($r));return (int)$db->lastInsertId();}
$apply=($argv[1]??'--dry-run')==='--apply';
try {
    $db->beginTransaction();
    $before=snap($db,$keys,true);ok($before===$expected,'La base cambió después de la fase inicial.');
    ok(!isset($receipt['ampliacion_fisica']),'Física ya fue incorporada.');
    $physics=array_values(array_filter($plan['registros'],fn($r)=>$r['area']==='fisica'));
    ok(count($physics)===16,'No están los 16 periodos de Física.');
    foreach($plan['registros'] as $r){
        if($r['area']==='fisica')continue;
        $context=null;foreach($receipt['contextos'] as $c)if($c['area']===$r['area']&&$c['grado']===$r['grado']&&$c['periodo']===$r['periodo'])$context=$c;
        ok($context!==null,'Falta un contexto de la fase inicial.');
        $sid=$context['id_estandar'];$s=$db->query("SELECT * FROM estandar WHERE id_estandar=$sid")->fetch(PDO::FETCH_ASSOC);
        ok($s['nombre_estandar']===$r['estandar']&&$s['descripcion_estandar']===$r['descripcion_estandar'],'Cambió el plan de un área ya aplicada.');
        foreach($r['dbas'] as $j=>$d){
            $did=$context['dbas'][$j]['id_dba'];$v=$db->query("SELECT * FROM dba WHERE id_dba=$did")->fetch(PDO::FETCH_ASSOC);
            ok($v['nombre_dba']===$d['nombre']&&$v['descripcion_dba']===$d['descripcion']&&$v['palabras_clave']===$d['palabras_clave'],'El plan anterior no coincide con los DBA aplicados.');
            foreach($d['ejes'] as $k=>$a){$id=$context['dbas'][$j]['ejes'][$k];$v=$db->query("SELECT * FROM eje_tematico WHERE id_eje_tematico=$id")->fetch(PDO::FETCH_ASSOC);ok($v['nombre_eje_tematico']===$a['nombre']&&$v['descripcion_eje_tematico']===$a['descripcion'],'Cambió un eje de la fase inicial.');}
            foreach($d['evidencias'] as $k=>$e){$id=$context['dbas'][$j]['evidencias'][$k];$v=$db->query("SELECT descripcion_evidencia FROM evidencia_de_aprendizaje WHERE id_evidencia_aprendizaje=$id")->fetchColumn();ok($v===$e,'Cambió una evidencia de la fase inicial.');}
        }
    }
    ok((int)$db->query('SELECT COUNT(*) FROM estandar WHERE grado BETWEEN 6 AND 9 AND id_materia_oficial=6')->fetchColumn()===0,'Ya existen estándares de Física para estos grados.');
    $db->prepare('UPDATE materia_oficial SET nombre_materia=? WHERE id_materia=6')->execute(['Física']);
    $added=['estandar'=>[],'dba'=>[],'eje_tematico'=>[],'evidencia_de_aprendizaje'=>[]];
    foreach($physics as $r){
        $weeks=[];foreach($r['dbas'] as $d)foreach($d['ejes'] as $a)for($w=$a['inicio'];$w<=$a['fin'];$w++)$weeks[]=$w;
        sort($weeks);ok($weeks===range(1,10),'Periodo de Física sin diez semanas.');
        $sid=put($db,'estandar',['nombre_estandar'=>$r['estandar'],'descripcion_estandar'=>$r['descripcion_estandar'],'grado'=>$r['grado'],'id_periodo'=>$r['periodo'],'id_materia_oficial'=>6]);$added['estandar'][]=$sid;
        $c=['area'=>'fisica','grado'=>$r['grado'],'periodo'=>$r['periodo'],'id_estandar'=>$sid,'dbas'=>[]];
        foreach($r['dbas'] as $d){
            $did=put($db,'dba',['nombre_dba'=>$d['nombre'],'descripcion_dba'=>$d['descripcion'],'palabras_clave'=>$d['palabras_clave'],'id_estandar'=>$sid]);$added['dba'][]=$did;$di=['id_dba'=>$did,'ejes'=>[],'evidencias'=>[]];
            foreach($d['ejes'] as $a){$id=put($db,'eje_tematico',['nombre_eje_tematico'=>$a['nombre'],'descripcion_eje_tematico'=>$a['descripcion'],'id_dba'=>$did]);$added['eje_tematico'][]=$id;$di['ejes'][]=$id;}
            foreach($d['evidencias'] as $e){$id=put($db,'evidencia_de_aprendizaje',['descripcion_evidencia'=>$e,'id_dba'=>$did,'origen'=>'revision_documental','modelo'=>null]);$added['evidencia_de_aprendizaje'][]=$id;$di['evidencias'][]=$id;}
            $c['dbas'][]=$di;
        }$receipt['contextos'][]=$c;
    }
    $after=snap($db,$keys);
    foreach($keys as $t=>$k){
        if($t==='materia_oficial'){$prior=$before[$t];foreach($prior as &$row)if((int)$row['id_materia']===6)$row['nombre_materia']='Física';unset($row);ok($prior===$after[$t],'Se alteró el catálogo fuera de Física.');}
        else{$withoutNew=array_values(array_filter($after[$t],fn($row)=>!in_array((int)$row[$k],$added[$t]??[],true)));ok($withoutNew===$before[$t],"Se modificaron datos anteriores en $t.");}
    }
    ok((int)$db->query('SELECT COUNT(*) FROM estandar WHERE grado BETWEEN 6 AND 9')->fetchColumn()===240,'No hay 240 contextos.');
    foreach($added as $t=>$ids){$receipt['ids_resultado'][$t]=array_merge($receipt['ids_resultado'][$t],$ids);$receipt['conteos_resultado'][$t]=count($receipt['ids_resultado'][$t]);}
    $receipt['fecha_ampliacion']=date(DATE_ATOM);$receipt['ampliacion_fisica']=array_map('count',$added);$receipt['plan_sha256']=hash_file('sha256',$dir.'/plan-curricular.json');
    if($apply){
        ok(copy($dir.'/resultado-aplicacion.json',$dir.'/resultado-fase-inicial.json'),'No se pudo respaldar la bitácora.');
        ok(copy($dir.'/snapshot-aplicado.json',$dir.'/snapshot-fase-inicial.json'),'No se pudo respaldar el estado inicial.');
        ok(file_put_contents($dir.'/resultado-aplicacion.json',json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))!==false,'No se guardó la bitácora final.');
        ok(file_put_contents($dir.'/snapshot-aplicado.json',json_encode($after,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))!==false,'No se guardó el estado final.');
        $db->commit();
    }else{$db->rollBack();ok(snap($db,$keys)===$before,'La prueba no revirtió exactamente.');}
    echo json_encode(['modo'=>$apply?'aplicado':'simulación revertida','fisica_incorporada'=>array_map('count',$added),'totales'=>$receipt['conteos_resultado']],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
}catch(Throwable $e){if($db->inTransaction())$db->rollBack();fwrite(STDERR,$e->getMessage()."\n");exit(1);}
