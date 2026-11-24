<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../../comun/database.php';
require __DIR__.'/../../comun/academico.php';
require __DIR__.'/../../apps/dba/lib/periodos.php';
$db=GuaguaDatabase::mysqli();
$periodos=$db->query(guagua_periodos_sql())->fetch_all(MYSQLI_ASSOC);
$fecha=date('Y-m-d');
$result=['periodos'=>array_values(array_filter(array_map(fn($p)=>['id_periodo'=>$p['id_periodo'],'numero_periodo'=>dba_numero_periodo($p),'fecha_inicio'=>$p['fecha_inicio'],'fecha_fin'=>$p['fecha_fin']],$periodos),fn($p)=>$p['numero_periodo']!==null)),'periodo_actual'=>dba_periodo_por_fecha($periodos,$fecha),'fecha_actual'=>$fecha];
file_put_contents(__DIR__.'/calendario.json',json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['fecha'=>$fecha,'periodo_actual'=>$result['periodo_actual'],'periodos_configurados'=>count($result['periodos'])],JSON_UNESCAPED_UNICODE),"\n";
