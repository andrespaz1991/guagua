<?php
declare(strict_types=1);
require_once __DIR__.'/Planner.php';

final class DailyCompliance
{
    /** Read the current state by planning date, without changing tasks or timetable data. */
    public static function load(PDO $db, string $owner, mixed $from, mixed $to, string $today): array
    {
        if ($owner === '' || strlen($owner) > 64) throw new InvalidArgumentException('Usuario no válido.');
        $from = DailyPlanner::date($from); $to = DailyPlanner::date($to); DailyPlanner::date($today);
        $start = new DateTimeImmutable($from); $end = new DateTimeImmutable($to);
        if ($from > $to || $start->diff($end)->days > 365) throw new InvalidArgumentException('Elige un período de hasta 366 días, con la fecha inicial antes de la final.');
        $query = static function (string $sql, array $values) use ($db): array {
            $statement = $db->prepare($sql); $statement->execute($values); return $statement->fetchAll(PDO::FETCH_ASSOC);
        };
        $counts = static function (array $row): array {
            $total = (int)$row['total']; $done = (int)$row['done'];
            return ['total'=>$total,'done'=>$done,'pending'=>$total-$done,'overdue'=>(int)$row['overdue'],
                'rate'=>$total ? (int)round(100*$done/$total) : null];
        };
        $aggregate = 'COUNT(*) AS total, SUM(done) AS done, SUM(CASE WHEN done=0 AND plan_date<? THEN 1 ELSE 0 END) AS overdue';
        $db->beginTransaction();
        try {
            $rows = $query("SELECT plan_date, $aggregate FROM pd_tasks WHERE owner=? AND plan_date BETWEEN ? AND ? GROUP BY plan_date ORDER BY plan_date",[$today,$owner,$from,$to]);
            $byDate = []; $summary = ['total'=>0,'done'=>0,'overdue'=>0];
            foreach ($rows as $row) {
                $byDate[$row['plan_date']] = $counts($row);
                foreach ($summary as $key=>$value) $summary[$key] += (int)$row[$key];
            }
            $days = [];
            for ($day=$start; $day<=$end; $day=$day->modify('+1 day')) {
                $key = $day->format('Y-m-d');
                $days[] = ['date'=>$key]+($byDate[$key] ?? $counts(['total'=>0,'done'=>0,'overdue'=>0]));
            }
            $categories = $query("SELECT t.category_id, c.name, c.color, COUNT(*) AS total, SUM(t.done) AS done,
                    SUM(CASE WHEN t.done=0 AND t.plan_date<? THEN 1 ELSE 0 END) AS overdue
                FROM pd_tasks t JOIN pd_categories c ON c.id=t.category_id AND c.owner=t.owner
                WHERE t.owner=? AND t.plan_date BETWEEN ? AND ? GROUP BY t.category_id,c.name,c.color
                ORDER BY total DESC,c.name,t.category_id",[$today,$owner,$from,$to]);
            $categories = array_map(static fn($row)=>['id'=>(int)$row['category_id'],'name'=>$row['name'],'color'=>$row['color']]+$counts($row),$categories);
            $pending = $query("SELECT t.id,t.plan_date,t.title,t.task_time,t.priority,c.name AS category,
                    CASE WHEN t.plan_date<? THEN 1 ELSE 0 END AS overdue
                FROM pd_tasks t JOIN pd_categories c ON c.id=t.category_id AND c.owner=t.owner
                WHERE t.owner=? AND t.plan_date BETWEEN ? AND ? AND t.done=0
                ORDER BY t.plan_date,CASE WHEN t.priority='alta' THEN 0 ELSE 1 END,t.task_time,t.position,t.id LIMIT 50",[$today,$owner,$from,$to]);
            foreach ($pending as &$task) { $task['id']=(int)$task['id']; $task['overdue']=(bool)$task['overdue']; }
            unset($task);
            $db->commit();
            return ['from'=>$from,'to'=>$to,'today'=>$today,'summary'=>$counts($summary),
                'days'=>$days,'categories'=>$categories,'pending'=>$pending,'pending_limit'=>50];
        } catch (Throwable $error) {
            if ($db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }
}
