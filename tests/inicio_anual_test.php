<?php
if (PHP_SAPI!=='cli') exit;
require_once __DIR__.'/../comun/database.php';
require_once __DIR__.'/../Planeador/lib/InicioAnual.php';
require_once __DIR__.'/../Planeador/lib/InicioAnualArchivos.php';
$db=guagua_db_mysqli();
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
foreach (['ano_lectivo','periodo','usuario','institucion_educativa','categoria_curso','materia_oficial','asignacion','horario','inscripcion'] as $table) {
    $definition=$db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
    $definition=preg_replace('/^CREATE TABLE `[^`]+`/','CREATE TEMPORARY TABLE `'.$table.'`',$definition);
    $definition=preg_replace('/^.*CONSTRAINT .*FOREIGN KEY.*\n/m','',$definition);
    $definition=preg_replace('/^.*FULLTEXT KEY.*\n/m','',$definition);
    $definition=preg_replace('/,\s*\)/',"\n)",$definition);
    $definition=preg_replace('/AUTO_INCREMENT=\d+\s*/','',$definition);
    $db->query($definition);
}
$db->query(str_replace('CREATE TABLE IF NOT EXISTS','CREATE TEMPORARY TABLE',InicioAnual::schema()));
function annual_check($value,string $message): void { if (!$value) throw new RuntimeException($message);echo "OK: $message\n"; }
function annual_reject(callable $call,string $message): void { try {$call();} catch(InvalidArgumentException $e) {annual_check(true,$message);return;}throw new RuntimeException($message); }
$db->query("INSERT INTO institucion_educativa (id_institucion_educativa,nombre_institucion,plantilla,autoregistrarse,tamano_maximo_adjunto) VALUES (7,'Institución temporal',1,'NO','2048')");
$db->query("INSERT INTO usuario (id_usuario,usuario,clave,nombre,apellido,rol,estado,genero,foto,observaciones) VALUES ('101','docente-test','temporal','Docente','Prueba','docente,admin','activo','f','user-icon.png',''),('102','docente-dos','temporal','Otra','Docente','docente','activo','f','user-icon.png',''),('201','estudiante-test','temporal','Estudiante','Prueba','estudiante','activo','f','user-icon.png','6')");
$db->query("INSERT INTO ano_lectivo (id_ano_lectivo,nombre_ano_lectivo,estado) VALUES (55,2030,'Activo')");
$db->query("INSERT INTO periodo (id_periodo,nombre_periodo,descripcion_periodo,fecha_inicio,fecha_fin,ano_lectivo,estado_periodo) VALUES (4,'Anterior','','2030-01-14','2030-11-30',2030,'1')");
$db->query("INSERT INTO categoria_curso (id_categoria_curso,nombre_categoria_curso,descripcion_categoria_curso,nivel_educativo) VALUES (6,'Sexto','',1)");
$db->query("INSERT INTO materia_oficial (id_materia,nombre_materia) VALUES (10,'Ciencias')");
$db->query("INSERT INTO asignacion (id_asignacion,institucion_educativa,id_curso,id_asignatura,id_docente,ano_lectivo,id_categoria_curso,visible) VALUES (100,7,10,10,101,55,6,'SI')");
$db->query("INSERT INTO horario (id_asignacion,fecha_inicio,fecha_fin,dia,hora_inicio,hora_fin) VALUES (100,'2030-01-14','2030-11-30','lunes','08:00','09:00')");
$db->query("INSERT INTO inscripcion (id_asignacion,id_estudiante,fecha_inscripcion,estado_inscripcion,fecha_retiro) VALUES (100,'201','2030-01-14','En curso',NULL)");
$actor=['id_usuario'=>'101','rol'=>'admin','id_institucion'=>7];
$service=new InicioAnual($db);
$p=$service->blank(2031,$actor);
$saved=$service->save($p,0,0,$actor);
annual_check((int)$db->query('SELECT COUNT(*) n FROM ano_lectivo')->fetch_assoc()['n']===1,'Guardar un borrador no crea ni activa años escolares');
annual_check($service->load(2031,$actor)['revision']===1,'El borrador se puede retomar');
$p['periodos']=[['nombre'=>'1','descripcion'=>'','inicio'=>'2031-01-13','fin'=>'2031-03-28','activo'=>true],['nombre'=>'2','descripcion'=>'','inicio'=>'2031-03-29','fin'=>'2031-06-30','activo'=>false]];
$p['grados']=[['key'=>'g6','id'=>6,'nombre'=>'Sexto','nivel'=>1]];
$p['materias']=[['key'=>'m10','id'=>10,'nombre'=>'Ciencias'],['key'=>'mnueva','id'=>0,'nombre'=>'Robótica temporal']];
$p['asignaciones']=[['key'=>'a1','grado'=>'g6','materia'=>'m10','docente'=>'101','visible'=>true],['key'=>'a2','grado'=>'g6','materia'=>'mnueva','docente'=>'101','visible'=>true]];
$p['horarios']=[['asignacion'=>'a1','dia'=>'lunes','inicio'=>'08:00','fin'=>'09:00','desde'=>'2031-01-13','hasta'=>'2031-06-30'],['asignacion'=>'a2','dia'=>'lunes','inicio'=>'09:00','fin'=>'10:00','desde'=>'2031-01-13','hasta'=>'2031-06-30']];
$p['estudiantes']=[['id'=>'201','grado'=>'g6']];$p['fecha_inscripcion']='2031-01-13';
annual_check($service->validate($p,$actor)===[],'Un plan completo utiliza el esquema real de Guagua');
$invalid=$p;$invalid['periodos'][1]['inicio']='2031-03-28';
annual_check(count($service->validate($invalid,$actor))>0,'Se rechazan períodos que comparten fechas');
$invalid=$p;$invalid['horarios'][1]['inicio']='08:30';
annual_check(count($service->validate($invalid,$actor))>0,'Se rechazan cruces de horario del mismo docente o grado');
$teacher=$actor;$teacher['rol']='docente';$invalid=$p;$invalid['asignaciones'][0]['docente']='102';
annual_check(count($service->validate($invalid,$teacher))>0,'Un docente no instala asignaciones de otro docente');
annual_reject(fn()=>$service->install(2031,1,true,$teacher),'Un docente no activa el año global');
$saved=$service->save($p,1,7,$actor);
annual_reject(fn()=>$service->save($p,1,7,$actor),'Una pestaña antigua no sobrescribe un borrador actualizado');
try {$service->install(2031,$saved['revision'],true,$actor,static function(){throw new RuntimeException('Fallo de archivos simulado');});throw new LogicException('No se produjo el fallo esperado');}
catch(RuntimeException $e) {if($e instanceof LogicException)throw $e;}
annual_check((int)$db->query('SELECT COUNT(*) n FROM ano_lectivo')->fetch_assoc()['n']===1 && (int)$db->query('SELECT COUNT(*) n FROM asignacion')->fetch_assoc()['n']===1,'Un fallo final revierte el año, períodos, cursos e inscripciones');
annual_check(guagua_ano_configurado($db)==='2030','Un fallo mantiene el año anterior activo');
$result=$service->install(2031,$saved['revision'],true,$actor);
annual_check($result['asignaciones']===2 && $result['horarios']===2 && $result['inscripciones']===2,'La instalación crea cursos, horarios e inscripciones planificados');
annual_check(guagua_ano_configurado($db)==='2031' && guagua_periodo_activo($db)['nombre_periodo']==='1','La activación usa la configuración académica compartida');
annual_check((int)$db->query('SELECT MAX(id_periodo) n FROM periodo')->fetch_assoc()['n']>4,'Los períodos reciben identificadores manuales válidos');
annual_check((int)$db->query('SELECT COUNT(*) n FROM inscripcion WHERE id_asignacion=100')->fetch_assoc()['n']===1 && $db->query("SELECT observaciones FROM usuario WHERE id_usuario='201'")->fetch_assoc()['observaciones']==='6','La instalación conserva inscripciones históricas y el perfil del estudiante');
$again=$service->install(2031,$saved['revision'],true,$actor);
annual_check($again===$result && (int)$db->query('SELECT COUNT(*) n FROM asignacion')->fetch_assoc()['n']===3,'Repetir una instalación terminada no duplica registros');
$copy=$service->copy(2032,55,$actor);
annual_check(count($copy['asignaciones'])===1 && count($copy['horarios'])===1 && $copy['horarios'][0]['desde']==='2032-01-14' && $copy['estudiantes']===[],'Copiar un año traslada la estructura y deja la matrícula para selección explícita');
$p2=json_decode(str_replace('2031','2032',json_encode($p)),true);
$p2['ano']=2032;$s2=$service->save($p2,0,7,$actor);
$fileCalls=0;
$r2=$service->install(2032,$s2['revision'],false,$actor,static function() use (&$fileCalls){$fileCalls++;return static function(){};});
annual_check(!$r2['activado'] && guagua_ano_configurado($db)==='2031' && $fileCalls===0,'Instalar sin activar conserva el año y las rutas en uso');
$prepared=$service->load(2032,$actor);
annual_check($prepared['estado']==='preparado','Un año preparado puede retomarse para activarlo después');
$service->install(2032,$prepared['revision'],true,$actor);
annual_check(guagua_ano_configurado($db)==='2032' && (int)$db->query('SELECT COUNT(*) n FROM asignacion')->fetch_assoc()['n']===5,'Activar posteriormente reutiliza los cursos ya preparados');

$dir=sys_get_temp_dir().'/guagua-annual-'.bin2hex(random_bytes(6));mkdir($dir,0700);
$attendance=$dir.'/asistencia.json';$reports=$dir.'/reportes.json';
file_put_contents($attendance,json_encode(['ruta'=>'antes.xlsx','anio'=>2030]));file_put_contents($reports,json_encode(['mensaje_whatsapp'=>'Texto guardado']));
try {
    $files=new InicioAnualArchivos($attendance,$reports);
    $options=$files->current([['id'=>1,'nombre'=>'Grupo','ruta_excel'=>'reporte.xlsx']]);
    $options['asistencia']='nuevo.xlsx';$undo=$files->apply($options,2032);
    annual_check(json_decode(file_get_contents($attendance),true)['anio']===2032 && json_decode(file_get_contents($reports),true)['mensaje_whatsapp']==='Texto guardado','Las rutas se aplican preservando otras configuraciones');
    $undo();annual_check(json_decode(file_get_contents($attendance),true)['ruta']==='antes.xlsx','Los archivos admiten restauración si falla la instalación');
    $options=$files->current([]);file_put_contents($reports,'{"mensaje_whatsapp":"Cambio concurrente"}');
    annual_reject(fn()=>$files->apply($options,2032),'Una configuración modificada por otra persona no se sobrescribe');
} finally {unlink($attendance);unlink($reports);rmdir($dir);}
echo "Pruebas completadas con tablas temporales; no se modificaron registros reales.\n";
