<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/../../comun/database.php';
$db = GuaguaDatabase::pdo();
$tables = ['grado','materia_oficial','estandar','dba','eje_tematico','evidencia_de_aprendizaje','periodo'];
$out = ['database' => $db->query('SELECT DATABASE()')->fetchColumn(), 'tables' => [], 'references' => []];
foreach ($tables as $table) {
    $out['tables'][$table] = ['schema' => $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1], 'rows' => $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC)];
}
$out['references'] = $db->query("SELECT TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IN ('estandar','dba','eje_tematico','evidencia_de_aprendizaje')")->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/snapshot-original.json', json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
foreach ($out['tables'] as $name=>$data) echo $name, ': ', count($data['rows']), "\n", $data['schema'], "\n";
echo json_encode($out['references'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
