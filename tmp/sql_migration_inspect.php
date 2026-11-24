<?php
require __DIR__ . '/../comun/database.php';
$db=guagua_db_mysqli();
$out=['version'=>$db->server_info,'tables'=>[]];
$tables=$db->query('SHOW TABLES')->fetch_all(MYSQLI_NUM);
foreach ($tables as [$table]) {
    $name='`'.str_replace('`','``',$table).'`';
    $columns=$db->query('SHOW COLUMNS FROM '.$name)->fetch_all(MYSQLI_ASSOC);
    foreach($columns as &$column) unset($column['Default']); unset($column);
    $out['tables'][$table]=['columns'=>$columns,'indexes'=>$db->query('SHOW INDEX FROM '.$name)->fetch_all(MYSQLI_ASSOC)];
}
file_put_contents(__DIR__.'/../output/esquema-optimizacion-sql.json',json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo 'OK: metadatos de '.count($tables)." tablas guardados; no se consultaron registros personales.\n";
require __DIR__ . '/../comun/context.php';
echo 'Institución seleccionada: '.(guagua_institution_context($db,7) ? 'contexto válido' : 'sin registro')."\n";
