<?php
declare(strict_types=1);
require_once __DIR__.'/Planner.php';

final class DailyPlannerToday
{
    /** Read saved tasks without preparing categories or consulting the timetable. */
    public static function load(PDO $db, string $owner, string $date): array
    {
        if ($owner === '' || strlen($owner) > 64) throw new InvalidArgumentException('Usuario no válido.');
        DailyPlanner::date($date);
        $db->beginTransaction();
        try {
            $query = $db->prepare('SELECT revision FROM pd_accounts WHERE owner=?');
            $query->execute([$owner]);
            $revision = (int)$query->fetchColumn();
            $query = $db->prepare('SELECT t.id,t.category_id,t.title,t.task_time,t.priority,t.highlight,t.done,c.name AS category
                FROM pd_tasks t JOIN pd_categories c ON c.id=t.category_id AND c.owner=t.owner
                WHERE t.owner=? AND t.plan_date=? ORDER BY c.position,c.id,t.position,t.id');
            $query->execute([$owner,$date]);
            $tasks = $query->fetchAll(PDO::FETCH_ASSOC);
            $done = 0;
            foreach ($tasks as &$task) {
                $task['id'] = (int)$task['id'];
                $task['category_id'] = (int)$task['category_id'];
                $task['done'] = (bool)$task['done'];
                $task['task_time'] = $task['task_time'] ? substr($task['task_time'],0,5) : '';
                if ($task['done']) $done++;
            }
            unset($task);
            $db->commit();
            return ['date'=>$date,'revision'=>$revision,'tasks'=>$tasks,'total'=>count($tasks),'done'=>$done];
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }
}
