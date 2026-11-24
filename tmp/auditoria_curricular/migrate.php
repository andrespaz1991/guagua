<?php
/** Revisión documental acotada a los grados 6-9. No se puede invocar desde HTTP. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../../comun/database.php';
$folder=__DIR__.'/../../output/auditoria-curricular-2026-11-01';
$original=json_decode(file_get_contents($folder.'/respaldo-original.json'),true,512,JSON_THROW_ON_ERROR);
$plan=json_decode(file_get_contents($folder.'/plan-curricular.json'),true,512,JSON_THROW_ON_ERROR);
$db=GuaguaDatabase::pdo();
$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$keys=['grado'=>'id_grado','materia_oficial'=>'id_materia','estandar'=>'id_estandar','dba'=>'id_dba','eje_tematico'=>'id_eje_tematico','evidencia_de_aprendizaje'=>'id_evidencia_aprendizaje','periodo'=>'id_periodo'];
function rows(PDO $db,string $table,string $key,bool $lock=false): array {
    return $db->query("SELECT * FROM `$table` ORDER BY `$key`".($lock?' FOR UPDATE':''))->fetchAll(PDO::FETCH_ASSOC);
}
function sortedRows(array $rows,string $key): array {
    usort($rows,fn($a,$b)=>(int)$a[$key]<=>(int)$b[$key]); return $rows;
}
function assertTrue(bool $value,string $message): void { if (!$value) throw new RuntimeException($message); }
function insert(PDO $db,string $table,array $row): int {
    $cols=array_keys($row);
    $query='INSERT INTO `'.$table.'` (`'.implode('`,`',$cols).'`) VALUES ('.implode(',',array_fill(0,count($cols),'?')).')';
    $db->prepare($query)->execute(array_values($row));
    return (int)$db->lastInsertId();
}
function countQuery(PDO $db,string $sql): int { return (int)$db->query($sql)->fetchColumn(); }
function deleteIds(PDO $db,string $table,string $key,array $ids): void {
    if ($ids) $db->exec("DELETE FROM `$table` WHERE `$key` IN (".implode(',',array_map('intval',$ids)).')');
}
function snapshot(PDO $db,array $keys): array {
    $out=[]; foreach($keys as $table=>$key) $out[$table]=rows($db,$table,$key); return $out;
}

$mode=$argv[1]??'--dry-run';
assertTrue(in_array($mode,['--dry-run','--apply','--verify','--restore'],true),'Modo inválido.');
if ($mode==='--verify') {
    $expected=json_decode(file_get_contents($folder.'/snapshot-aplicado.json'),true,512,JSON_THROW_ON_ERROR);
    $current=snapshot($db,$keys);
    foreach($keys as $table=>$key) assertTrue($current[$table]===$expected[$table],"Cambios posteriores en $table: verificar antes de restaurar.");
    echo "Verificación exacta correcta: catálogo y jerarquía curricular coinciden con el resultado guardado.\n";
    exit;
}

assertTrue((int)$db->query("SELECT GET_LOCK('guagua_revision_curricular_6_9',10)")->fetchColumn()===1,'Hay otra revisión curricular en ejecución.');
try {
    $db->beginTransaction();
    if ($mode==='--restore') {
        $receipt=json_decode(file_get_contents($folder.'/resultado-aplicacion.json'),true,512,JSON_THROW_ON_ERROR);
        $after=json_decode(file_get_contents($folder.'/snapshot-aplicado.json'),true,512,JSON_THROW_ON_ERROR);
        foreach($keys as $table=>$key) assertTrue(rows($db,$table,$key,true)===$after[$table],"No se puede restaurar: $table cambió después de la revisión.");
        foreach(['evidencia_de_aprendizaje','eje_tematico','dba','estandar'] as $table) deleteIds($db,$table,$keys[$table],$receipt['ids_resultado'][$table]);
        foreach(['estandar','dba','eje_tematico','evidencia_de_aprendizaje'] as $table)
            foreach($original['tables'][$table]['rows'] as $row)
                if (in_array((int)$row[$keys[$table]],$receipt['ids_originales'][$table],true)) insert($db,$table,$row);
        foreach($plan['areas'] as [$id,$name]) {
            $old=null; foreach($original['tables']['materia_oficial']['rows'] as $row) if ((int)$row['id_materia']===$id) $old=$row;
            if ($old) $db->prepare('UPDATE materia_oficial SET nombre_materia=?,descripcion=? WHERE id_materia=?')->execute([$old['nombre_materia'],$old['descripcion'],$id]);
            else $db->prepare('DELETE FROM materia_oficial WHERE id_materia=?')->execute([$id]);
        }
        $restored=snapshot($db,$keys);
        foreach($keys as $table=>$key) assertTrue($restored[$table]===sortedRows($original['tables'][$table]['rows'],$key),"La restauración de $table no coincide.");
        $db->commit();
        echo "Restauración exacta completada.\n"; exit;
    }

    assertTrue(!is_file($folder.'/resultado-aplicacion.json'),'La revisión ya fue aplicada; use --verify.');
    foreach($keys as $table=>$key) {
        $actual=rows($db,$table,$key,true);
        assertTrue($actual===sortedRows($original['tables'][$table]['rows'],$key),"La tabla $table cambió desde el respaldo. No se aplicó ningún cambio.");
    }
    foreach(['estandar','dba','eje_tematico','evidencia_de_aprendizaje','materia_oficial'] as $table) {
        $s=$db->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?'); $s->execute([$table]);
        assertTrue($s->fetchColumn()==='InnoDB',"La tabla $table debe permitir transacciones.");
    }
    $fks=countQuery($db,"SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IN ('estandar','dba','eje_tematico','evidencia_de_aprendizaje')");
    assertTrue($fks===0,'Aparecieron referencias nuevas: revisar antes de corregir la jerarquía.');
    $before=snapshot($db,$keys);
    $historicalOrphans=countQuery($db,'SELECT COUNT(*) FROM evidencia_de_aprendizaje x LEFT JOIN dba d ON d.id_dba=x.id_dba WHERE d.id_dba IS NULL');
    $ids=['estandar'=>[],'dba'=>[],'eje_tematico'=>[],'evidencia_de_aprendizaje'=>[]];
    $canonical=[]; $oldDba=[];
    foreach($before['estandar'] as $r) {
        if ((int)$r['grado']<6||(int)$r['grado']>9) continue;
        $ids['estandar'][]=(int)$r['id_estandar'];
        $subject=(int)$r['id_materia_oficial']===6?10:(int)$r['id_materia_oficial'];
        $k=$subject.'-'.$r['grado'].'-'.$r['id_periodo'];
        if (!isset($canonical[$k])) $canonical[$k]=(int)$r['id_estandar'];
    }
    foreach($before['dba'] as $r) if(in_array((int)$r['id_estandar'],$ids['estandar'],true)) {
        $ids['dba'][]=(int)$r['id_dba']; $oldDba[(int)$r['id_estandar']][]=(int)$r['id_dba'];
    }
    foreach(['eje_tematico','evidencia_de_aprendizaje'] as $table)
        foreach($before[$table] as $r) if(in_array((int)$r['id_dba'],$ids['dba'],true)) $ids[$table][]=(int)$r[$keys[$table]];
    // Los adjuntos no usan actualmente las evidencias curriculares: se comprueba antes de tocarlas.
    assertTrue(countQuery($db,'SELECT COUNT(*) FROM evidencias_adjuntos_evidencia')===0,'Existen adjuntos: revisar su relación antes de sustituir evidencias.');

    foreach($plan['registros'] as $r) {
        assertTrue(in_array((int)$r['grado'],[6,7,8,9],true)&&in_array((int)$r['periodo'],[1,2,3,4],true),'Grado o periodo fuera de alcance.');
        $weeks=[];
        foreach($r['dbas'] as $d) {
            assertTrue(trim($d['nombre'])!==''&&!empty($d['evidencias'])&&!empty($d['ejes']),'Aprendizaje incompleto.');
            foreach($d['ejes'] as $a) {
                assertTrue(mb_strlen($a['nombre'],'UTF-8')<=255,'Nombre de eje demasiado largo.');
                if (empty($a['transversal'])) for($w=$a['inicio'];$w<=$a['fin'];$w++) $weeks[]=$w;
            }
        }
        sort($weeks); assertTrue($weeks===range(1,10),'La secuencia no cubre exactamente diez semanas.');
    }

    foreach($plan['areas'] as [$id,$name]) {
        $exists=false; foreach($before['materia_oficial'] as $row) if ((int)$row['id_materia']===$id) $exists=true;
        if($exists) $db->prepare('UPDATE materia_oficial SET nombre_materia=? WHERE id_materia=?')->execute([$name,$id]);
        else insert($db,'materia_oficial',['id_materia'=>$id,'nombre_materia'=>$name,'descripcion'=>'Área curricular incorporada a partir de las mallas institucionales de grados 6 a 9.']);
    }
    foreach(['evidencia_de_aprendizaje','eje_tematico','dba','estandar'] as $table) deleteIds($db,$table,$keys[$table],$ids[$table]);
    $newIds=['estandar'=>[],'dba'=>[],'eje_tematico'=>[],'evidencia_de_aprendizaje'=>[]];
    $mapping=[];
    foreach($plan['registros'] as $r) {
        $key=$r['materia'].'-'.$r['grado'].'-'.$r['periodo'];
        $row=['nombre_estandar'=>$r['estandar'],'descripcion_estandar'=>$r['descripcion_estandar'],'grado'=>$r['grado'],'id_periodo'=>$r['periodo'],'id_materia_oficial'=>$r['materia']];
        if(isset($canonical[$key])) $row=['id_estandar'=>$canonical[$key]]+$row;
        $sid=insert($db,'estandar',$row); if(isset($row['id_estandar'])) $sid=$row['id_estandar'];
        $newIds['estandar'][]=$sid;
        $context=['area'=>$r['area'],'grado'=>$r['grado'],'periodo'=>$r['periodo'],'id_estandar'=>$sid,'dbas'=>[]];
        foreach($r['dbas'] as $i=>$d) {
            $row=['nombre_dba'=>$d['nombre'],'descripcion_dba'=>$d['descripcion'],'id_estandar'=>$sid,'palabras_clave'=>$d['palabras_clave']];
            if($i===0&&!empty($oldDba[$sid])) $row=['id_dba'=>$oldDba[$sid][0]]+$row;
            $did=insert($db,'dba',$row); if(isset($row['id_dba'])) $did=$row['id_dba'];
            $newIds['dba'][]=$did; $di=['id_dba'=>$did,'ejes'=>[],'evidencias'=>[]];
            foreach($d['ejes'] as $a) {
                $aid=insert($db,'eje_tematico',['nombre_eje_tematico'=>$a['nombre'],'descripcion_eje_tematico'=>$a['descripcion'],'id_dba'=>$did]);
                $newIds['eje_tematico'][]=$aid; $di['ejes'][]=$aid;
            }
            foreach($d['evidencias'] as $e) {
                $eid=insert($db,'evidencia_de_aprendizaje',['descripcion_evidencia'=>$e,'id_dba'=>$did,'origen'=>'revision_documental','modelo'=>null]);
                $newIds['evidencia_de_aprendizaje'][]=$eid; $di['evidencias'][]=$eid;
            }
            $context['dbas'][]=$di;
        }
        $mapping[]=$context;
    }
    $after=snapshot($db,$keys);
    foreach(['estandar','dba','eje_tematico','evidencia_de_aprendizaje'] as $table) {
        $unaffectedBefore=array_values(array_filter($before[$table],fn($r)=>!in_array((int)$r[$keys[$table]],$ids[$table],true)));
        $unaffectedAfter=array_values(array_filter($after[$table],fn($r)=>!in_array((int)$r[$keys[$table]],$newIds[$table],true)));
        assertTrue($unaffectedBefore===$unaffectedAfter,"Se alteraron registros fuera de alcance en $table.");
    }
    $checks=[
      'estandares_sin_materia'=>countQuery($db,'SELECT COUNT(*) FROM estandar e LEFT JOIN materia_oficial m ON m.id_materia=e.id_materia_oficial WHERE e.grado BETWEEN 6 AND 9 AND m.id_materia IS NULL'),
      'dba_huerfanos'=>countQuery($db,'SELECT COUNT(*) FROM dba d LEFT JOIN estandar e ON e.id_estandar=d.id_estandar WHERE e.id_estandar IS NULL'),
      'ejes_huerfanos'=>countQuery($db,'SELECT COUNT(*) FROM eje_tematico x LEFT JOIN dba d ON d.id_dba=x.id_dba WHERE d.id_dba IS NULL'),
      'evidencias_huerfanas_nuevas'=>countQuery($db,'SELECT COUNT(*) FROM evidencia_de_aprendizaje x LEFT JOIN dba d ON d.id_dba=x.id_dba WHERE d.id_dba IS NULL')-$historicalOrphans,
      'dba_sin_evidencias'=>countQuery($db,'SELECT COUNT(*) FROM dba d JOIN estandar e ON e.id_estandar=d.id_estandar WHERE e.grado BETWEEN 6 AND 9 AND NOT EXISTS (SELECT 1 FROM evidencia_de_aprendizaje x WHERE x.id_dba=d.id_dba)'),
      'dba_sin_ejes'=>countQuery($db,'SELECT COUNT(*) FROM dba d JOIN estandar e ON e.id_estandar=d.id_estandar WHERE e.grado BETWEEN 6 AND 9 AND NOT EXISTS (SELECT 1 FROM eje_tematico x WHERE x.id_dba=d.id_dba)')
    ];
    foreach($checks as $label=>$n) assertTrue($n===0,"Falló $label: $n.");
    assertTrue(countQuery($db,'SELECT COUNT(*) FROM estandar WHERE grado BETWEEN 6 AND 9')===count($plan['registros']),'No coincide la cantidad de contextos de materia, grado y periodo.');
    $counts=array_map('count',$newIds); $oldCounts=array_map('count',$ids);
    $receipt=['database'=>$original['database'],'fecha'=>date(DATE_ATOM),'modo'=>$mode,'conteos_originales'=>$oldCounts,'conteos_resultado'=>$counts,'validaciones'=>$checks,'evidencias_historicas_sin_dba_conservadas'=>$historicalOrphans,'ids_originales'=>$ids,'ids_resultado'=>$newIds,'contextos'=>$mapping,'plan_sha256'=>hash_file('sha256',$folder.'/plan-curricular.json')];
    if($mode==='--apply') {
        // Escribe el resultado verificable antes del COMMIT; un fallo de escritura revierte los cambios.
        assertTrue(file_put_contents($folder.'/snapshot-aplicado.json',json_encode($after,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))!==false,'No se pudo guardar el resultado.');
        assertTrue(file_put_contents($folder.'/resultado-aplicacion.json',json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR))!==false,'No se pudo guardar la bitácora.');
        $db->commit();
    } else {
        $db->rollBack();
        assertTrue(snapshot($db,$keys)===$before,'La simulación no revirtió sus cambios.');
        file_put_contents($folder.'/resultado-simulacion.json',json_encode($receipt,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
    }
    echo json_encode(['modo'=>$mode,'originales'=>$oldCounts,'resultado'=>$counts,'validaciones'=>$checks,'fuera_de_alcance'=>'sin cambios'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE),"\n";
} catch(Throwable $error) {
    if($db->inTransaction()) $db->rollBack();
    fwrite(STDERR,$error->getMessage()."\n"); exit(1);
} finally { $db->query("SELECT RELEASE_LOCK('guagua_revision_curricular_6_9')"); }
