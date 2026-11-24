<?php
declare(strict_types=1);
require_once __DIR__.'/../apps/plan_diario/lib/Planner.php';
$path = tempnam(sys_get_temp_dir(),'pd-concurrency-');
$options = [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
$writer = new PDO('sqlite:'.$path,null,null,$options);
$writer->exec('PRAGMA journal_mode=WAL');
$writer->exec('PRAGMA busy_timeout=50');
DailyPlanner::install($writer);
$planner = new DailyPlanner($writer,'reader-test');
$before = $planner->run('2026-11-22');
$reader = new PDO('sqlite:'.$path,null,null,$options);
$reader->exec('PRAGMA busy_timeout=50');
try {
    $writer->beginTransaction();
    $writer->exec('UPDATE pd_accounts SET revision=revision+1');
    DailyPlanner::install($reader);
    $after = (new DailyPlanner($reader,'reader-test'))->run('2026-11-22');
    if ($after['revision'] !== $before['revision'] || count($after['categories']) !== 4 || $reader->inTransaction()) throw new RuntimeException('The reader did not return the committed plan or left a transaction open.');
    $writer->rollBack();
    echo "OK: la carga y la comprobación del esquema no solicitan un bloqueo de escritura; dos conexiones simultáneas conservan una lectura consistente.\n";
} finally {
    if ($writer->inTransaction()) $writer->rollBack();
    unset($planner,$writer,$reader);
    foreach ([$path,$path.'-wal',$path.'-shm'] as $file) if (is_file($file)) unlink($file);
}
