<?php
declare(strict_types=1);
require_once __DIR__.'/lib/Planner.php';
function daily_planner_database(): PDO
{
    $directory = __DIR__.'/data';
    if (!is_dir($directory) && !mkdir($directory,0700,true) && !is_dir($directory)) throw new RuntimeException('No se pudo preparar la base del planificador.');
    $db = new PDO('sqlite:'.$directory.'/planner.sqlite',null,null,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $db->exec('PRAGMA busy_timeout=5000');
    $db->exec('PRAGMA synchronous=NORMAL');
    if (strtolower((string)$db->query('PRAGMA journal_mode')->fetchColumn()) !== 'wal') {
        $db->exec('PRAGMA journal_mode=WAL');
    }
    DailyPlanner::install($db);
    return $db;
}
