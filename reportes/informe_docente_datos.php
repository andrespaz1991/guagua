<?php
// SIE 2024, Criterio de extraedad, página impresa 12.
function informe_extraedad(string $grado, ?int $edad): array {
 $texto=mb_strtolower(trim($grado),'UTF-8'); $numero=null;
 if(preg_match('/^(?:grado\s*)?(10|11|[1-9])\s*(?:[°ºo.]|°\.)?$/u',$texto,$m)) $numero=(int)$m[1];
 $nombres=['segundo'=>2,'tercero'=>3,'cuarto'=>4,'quinto'=>5,'sexto'=>6,'septimo'=>7,'séptimo'=>7,'octavo'=>8,'noveno'=>9];
 if(isset($nombres[$texto])) $numero=$nombres[$texto];
 $umbral=$numero!==null && $numero>=2 && $numero<=9?$numero+8:null;
 return ['umbral_extraedad'=>$umbral,'extraedad'=>$umbral===null || $edad===null?null:$edad>=$umbral,
 'estado_extraedad'=>$umbral===null?'Sin umbral SIE':($edad===null?'Sin edad válida':($edad>=$umbral?'En extraedad':'Bajo umbral SIE'))];
}
function informe_query(mysqli $db, string $sql, string $types='', array $params=[]): array {
 $stmt=$db->prepare($sql); if($types) $stmt->bind_param($types,...$params); $stmt->execute(); return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
function informe_nombre($s): string { return preg_replace('/\s+/u',' ',mb_strtoupper(trim($s),'UTF-8')); }
function informe_fecha($s): ?string {
 foreach(['Y-m-d','d/m/Y'] as $format) { $d=DateTimeImmutable::createFromFormat('!'.$format,trim($s)); if($d && $d->format($format)===trim($s)) return $d->format('Y-m-d'); } return null;
}
function informe_docente_datos(mysqli $db,array $input,int $institution): array {
 require_once __DIR__ . '/../comun/academico.php';
 $year=filter_var($input['year']??guagua_ano_configurado($db),FILTER_VALIDATE_INT); if(!$year || $year<2000 || $year>2100) throw new InvalidArgumentException('Año inválido');
 $periodo=guagua_periodo_activo($db);
 $from=informe_fecha($input['from']??$periodo['fecha_inicio']??"$year-01-01"); $to=informe_fecha($input['to']??$periodo['fecha_fin']??"$year-12-31"); if(!$from || !$to || $from>$to) throw new InvalidArgumentException('Fechas inválidas');
 $scope=($input['scope']??'inscripciones')==='registro'?'registro':'inscripciones';
 $where=$scope==='registro'?'u.fecha_creacion >= ? AND u.fecha_creacion < ?':'EXISTS (SELECT 1 FROM inscripcion i JOIN asignacion a ON a.id_asignacion=i.id_asignacion JOIN ano_lectivo al ON al.id_ano_lectivo=a.ano_lectivo WHERE i.id_estudiante=u.id_usuario AND al.nombre_ano_lectivo=? AND a.institucion_educativa=? AND i.estado_inscripcion<>\'Retirado\')';
 $users=informe_query($db,"SELECT u.id_usuario,u.nombre,u.apellido,u.genero,u.observaciones,u.estado,u.fecha_nacimiento,u.telefono,u.eps FROM usuario u WHERE u.rol='estudiante' AND $where",$scope==='registro'?'ss':'ii',$scope==='registro'?["$year-01-01",($year+1).'-01-01']:[$year,$institution]);
 $names=[];$people=[];$grades=[];$inactive=0;
 foreach($users as $u) {
  if(mb_strtolower(trim($u['estado']))!=='activo') {$inactive++;continue;}
  $id=trim($u['id_usuario']); $name=informe_nombre($u['nombre'].' '.$u['apellido']); $names[$name][]=$id;
  $grade=trim($u['observaciones']??'')?:'Sin grado'; $grades[$grade]=true;
  $birth=informe_fecha($u['fecha_nacimiento']??''); $age=null;
  if($birth && $birth<=$to) { $a=(new DateTimeImmutable($birth))->diff(new DateTimeImmutable($to))->y; if($a>=3 && $a<=100) $age=$a; }
  $people[$id]=['id'=>$id,'nombre'=>trim($u['nombre'].' '.$u['apellido']),'grado'=>$grade,'genero'=>strtoupper(trim($u['genero'])),'edad'=>$age,'sin_telefono'=>!preg_match('/\d{7}/',$u['telefono']??''),'sin_eps'=>!trim($u['eps']??''),'registros'=>0,'ausencias'=>0,'dias'=>[]];
 }
 $issues=['sin_vinculo'=>0,'fecha_invalida'=>0,'no_interpretados'=>0,'duplicados'=>0,'conflictos'=>0];$events=[];
 // Solo se leen columnas necesarias; el filtro temporal se valida sin convertir fechas inválidas.
 $records=informe_query($db,"SELECT documento,estudiante,materia,fechas_clase,asistencias FROM asistencias WHERE fechas_clase BETWEEN ? AND ? OR STR_TO_DATE(fechas_clase,'%d/%m/%Y') BETWEEN ? AND ?",'ssss',[$from,$to,$from,$to]);
 foreach($records as $r) {
  $date=informe_fecha($r['fechas_clase']); if(!$date) {$issues['fecha_invalida']++;continue;} if($date<$from || $date>$to) continue;
  $id=trim($r['documento']); if(!isset($people[$id])) { $matches=$names[informe_nombre($r['estudiante'])]??[]; if(count($matches)!==1) {$issues['sin_vinculo']++;continue;} $id=$matches[0]; }
  $state=mb_strtoupper(trim($r['asistencias']),'UTF-8'); $value=in_array($state,['NO','FALTA','AUSENTE'],true)?1:(in_array($state,['SI','SÍ','PRESENTE'],true)?0:null);
  if($value===null) {$issues['no_interpretados']++;continue;}
  $key=json_encode([$id,informe_nombre($r['materia']),$date]);
  if(isset($events[$key])) {$issues['duplicados']++;if($events[$key]['value']!==$value && $events[$key]['value']!==null) {$events[$key]['value']=null;$issues['conflictos']++;} }
  else $events[$key]=['id'=>$id,'date'=>$date,'value'=>$value];
 }
 $month=[];
 foreach($events as $e) {if($e['value']===null) continue; $p=&$people[$e['id']];$p['registros']++;if($e['value']) {$p['ausencias']++;$p['dias'][$e['date']]=true;} unset($p);}
 $selected=(string)($input['grade']??''); $people=array_filter($people,fn($p)=>!$selected || $p['grado']===$selected);
 foreach($events as $e) {if($e['value']===null || !isset($people[$e['id']])) continue;$m=substr($e['date'],0,7);if(!isset($month[$m]))$month[$m]=['registros'=>0,'ausencias'=>0];$month[$m]['registros']++;$month[$m]['ausencias']+=$e['value'];} ksort($month);
 $extra=['total'=>0,'evaluables'=>0,'sin_edad'=>0,'sin_umbral'=>0];
 $quality=['sin_edad'=>0,'sin_genero'=>0,'sin_grado'=>0,'sin_telefono'=>0,'sin_eps'=>0,'sin_asistencia'=>0];$groups=[];$covered=0;$alerts=0;$absences=0;$totalRecords=0;
 foreach($people as &$p) {
  $p=array_merge($p,informe_extraedad($p['grado'],$p['edad']));
  $extra['total']+=(int)($p['extraedad']===true);$extra['evaluables']+=(int)($p['extraedad']!==null);
  $extra['sin_umbral']+=(int)($p['umbral_extraedad']===null);$extra['sin_edad']+=(int)($p['umbral_extraedad']!==null && $p['edad']===null);
  $p['dias']=count($p['dias']); $p['porcentaje']=$p['registros']?round(100*$p['ausencias']/$p['registros'],1):null;
  $p['alerta']=$p['dias']>=3 || ($p['registros']>=10 && $p['porcentaje']>=20);
  $covered+=(int)($p['registros']>0);$alerts+=(int)$p['alerta'];$absences+=$p['ausencias'];$totalRecords+=$p['registros'];
  $quality['sin_edad']+=(int)($p['edad']===null);$quality['sin_genero']+=(int)!in_array($p['genero'],['M','F']);$quality['sin_grado']+=(int)($p['grado']==='Sin grado');$quality['sin_telefono']+=(int)$p['sin_telefono'];$quality['sin_eps']+=(int)$p['sin_eps'];$quality['sin_asistencia']+=(int)!$p['registros'];
  $g=$p['grado'];if(!isset($groups[$g]))$groups[$g]=['grado'=>$g,'total'=>0,'M'=>0,'F'=>0,'otro'=>0,'edades'=>[],'cobertura'=>0,'ausencias'=>0,'dias'=>0,'alertas'=>0,'extraedad'=>0,'evaluables_extraedad'=>0];
  $groups[$g]['extraedad']+=(int)($p['extraedad']===true);$groups[$g]['evaluables_extraedad']+=(int)($p['extraedad']!==null);
  $groups[$g]['total']++;$groups[$g][in_array($p['genero'],['M','F'])?$p['genero']:'otro']++;if($p['edad']!==null)$groups[$g]['edades'][]=$p['edad'];$groups[$g]['cobertura']+=(int)($p['registros']>0);$groups[$g]['ausencias']+=$p['ausencias'];$groups[$g]['dias']+=$p['dias'];$groups[$g]['alertas']+=(int)$p['alerta'];
 } unset($p);
 foreach($groups as &$g){$g['edad_media']=count($g['edades'])?round(array_sum($g['edades'])/count($g['edades']),1):null;unset($g['edades']);}unset($g);uksort($groups,'strnatcasecmp');$gradeList=array_keys($grades);natcasesort($gradeList);
 $students=array_values($people);usort($students,fn($a,$b)=>($b['alerta']<=>$a['alerta'])?:($b['dias']<=>$a['dias'])?:strcmp($a['nombre'],$b['nombre']));
 return ['extraedad'=>$extra,'total'=>count($people),'cobertura'=>$covered,'alertas'=>$alerts,'ausencias'=>$absences,'registros'=>$totalRecords,'inactivos'=>$inactive,'grados'=>array_values($gradeList),'por_grado'=>array_values($groups),'estudiantes'=>$students,'meses'=>$month,'calidad'=>$quality,'incidencias'=>$issues,'scope'=>$scope,'from'=>$from,'to'=>$to,'actualizado'=>date('Y-m-d H:i'),'institucion'=>$institution];
}
