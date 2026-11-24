<?php
if (PHP_SAPI !== 'cli') exit;
require_once dirname(__DIR__, 3) . '/comun/database.php';
$db = guagua_db_mysqli();
$db->query('SET SESSION TRANSACTION READ ONLY');
$db->begin_transaction();
function rows(string $sql): array {
    global $db;
    if (!preg_match('/^\s*(SELECT|SHOW)\b/i', $sql)) throw new RuntimeException('Solo lectura');
    return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}
$tables = ['ano_lectivo','periodo','institucion_educativa','categoria_curso','materia','materia_oficial','asignacion','curso','horario','inscripcion','usuario','acudiente_estudiante','menu_items2','inicio_anual_borradores','actividad','seguimiento_es','asistencia'];
$all = rows('SHOW TABLE STATUS');
$known = array_column($all, 'Name');
$out = ['fecha'=>'2026-11-19','tablas_totales'=>count($all),'tablas'=>[]];
foreach ($tables as $table) {
    if (!in_array($table, $known, true)) continue;
    $status = array_values(array_filter($all, fn($r)=>$r['Name']===$table))[0];
    $out['tablas'][$table] = ['motor'=>$status['Engine'],'columnas'=>rows("SHOW COLUMNS FROM `$table`"),'indices'=>rows("SHOW INDEX FROM `$table`"),'total'=>rows("SELECT COUNT(*) AS n FROM `$table`")[0]['n']];
}
$out['nombres_tablas'] = $known;
$out['menu'] = rows('SELECT * FROM menu_items2 ORDER BY menu_item_id');
$out['anos'] = rows('SELECT * FROM ano_lectivo ORDER BY nombre_ano_lectivo');
$out['periodos'] = rows('SELECT * FROM periodo ORDER BY ano_lectivo,fecha_inicio');
$out['grados'] = rows('SELECT * FROM categoria_curso ORDER BY id_categoria_curso');
$out['vinculos_duplicados'] = rows('SELECT COUNT(*) AS n FROM (SELECT id_estudiante,id_acudiente FROM acudiente_estudiante GROUP BY id_estudiante,id_acudiente HAVING COUNT(*)>1) t');
$out['asignaciones_duplicadas'] = rows('SELECT COUNT(*) AS n FROM (SELECT institucion_educativa,ano_lectivo,id_categoria_curso,id_asignatura,id_docente FROM asignacion GROUP BY institucion_educativa,ano_lectivo,id_categoria_curso,id_asignatura,id_docente HAVING COUNT(*)>1) t');
$out['inscripciones_duplicadas'] = rows('SELECT COUNT(*) AS n FROM (SELECT id_asignacion,id_estudiante FROM inscripcion GROUP BY id_asignacion,id_estudiante HAVING COUNT(*)>1) t');
$out['vinculos_huerfanos'] = rows('SELECT COUNT(*) AS n FROM acudiente_estudiante ae LEFT JOIN usuario e ON e.id_usuario=ae.id_estudiante LEFT JOIN usuario a ON a.id_usuario=ae.id_acudiente WHERE e.id_usuario IS NULL OR a.id_usuario IS NULL');
$out['inscripciones_huerfanas'] = rows('SELECT COUNT(*) AS n FROM inscripcion i LEFT JOIN usuario u ON u.id_usuario=i.id_estudiante LEFT JOIN asignacion a ON a.id_asignacion=i.id_asignacion WHERE u.id_usuario IS NULL OR a.id_asignacion IS NULL');
$out['roles_totales'] = rows('SELECT rol,COUNT(*) AS n FROM usuario GROUP BY rol');
$db->rollback();
file_put_contents(__DIR__.'/evidencia_bd.json', json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo json_encode(['tablas'=>$out['tablas_totales'],'menu'=>count($out['menu']),'motores'=>array_map(fn($r)=>$r['motor'],$out['tablas']),'anomalias'=>array_intersect_key($out,array_flip(['vinculos_duplicados','asignaciones_duplicadas','inscripciones_duplicadas','vinculos_huerfanos','inscripciones_huerfanas']))],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
