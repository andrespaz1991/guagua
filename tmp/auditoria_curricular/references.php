<?php
if (PHP_SAPI !== 'cli') exit;
require __DIR__ . '/../../comun/database.php';
$db=GuaguaDatabase::pdo();
$cols=$db->query("SELECT TABLE_NAME,COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME REGEXP '(estandar|dba|eje_tematico|evidencia)' ORDER BY TABLE_NAME,ORDINAL_POSITION")->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as &$c) {
    $t=$c['TABLE_NAME']; $n=$c['COLUMN_NAME'];
    if (in_array($t,['estandar','dba','eje_tematico','evidencia_de_aprendizaje'])) continue;
    $c['values']=$db->query("SELECT `$n` AS value,COUNT(*) AS n FROM `$t` WHERE `$n` IS NOT NULL AND `$n`<>'' GROUP BY `$n` LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
}
file_put_contents(__DIR__.'/references.json',json_encode($cols,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode($cols,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
