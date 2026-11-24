<?php
declare(strict_types=1);

final class DailyPlanner
{
    public const COLORS = ['purple', 'blue', 'green', 'orange', 'pink'];

    public function __construct(private PDO $db, private string $owner, private ?array $schedule = null)
    {
        if ($owner === '' || strlen($owner) > 64) throw new InvalidArgumentException('Usuario no válido.');
    }

    public static function install(PDO $db): void
    {
        // Existing databases need only a metadata read, even with another writer.
        $objects = (int)$db->query("SELECT COUNT(*) FROM sqlite_master WHERE name IN ('pd_accounts','pd_categories','pd_categories_owner','pd_tasks','pd_tasks_day','pd_days','pd_category_sources','pd_category_sources_category')")->fetchColumn();
        if ($objects === 8) return;
        $ownsTransaction = !$db->inTransaction();
        if ($ownsTransaction) $db->beginTransaction();
        try {
        foreach ([
            'CREATE TABLE IF NOT EXISTS pd_accounts (owner TEXT PRIMARY KEY, revision INTEGER NOT NULL DEFAULT 0, seeded INTEGER NOT NULL DEFAULT 0)',
            'CREATE TABLE IF NOT EXISTS pd_categories (id INTEGER PRIMARY KEY AUTOINCREMENT, owner TEXT NOT NULL, name TEXT NOT NULL, color TEXT NOT NULL, position INTEGER NOT NULL)',
            'CREATE INDEX IF NOT EXISTS pd_categories_owner ON pd_categories(owner)',
            'CREATE TABLE IF NOT EXISTS pd_tasks (id INTEGER PRIMARY KEY AUTOINCREMENT, owner TEXT NOT NULL, plan_date TEXT NOT NULL, category_id INTEGER NOT NULL, title TEXT NOT NULL, description TEXT NOT NULL, task_time TEXT, priority TEXT NOT NULL DEFAULT \'normal\', highlight TEXT NOT NULL DEFAULT \'green\', done INTEGER NOT NULL DEFAULT 0, completed_at TEXT, position INTEGER NOT NULL, copied_from INTEGER, UNIQUE(owner,plan_date,copied_from))',
            'CREATE INDEX IF NOT EXISTS pd_tasks_day ON pd_tasks(owner,plan_date,position)',
            'CREATE TABLE IF NOT EXISTS pd_days (owner TEXT NOT NULL, plan_date TEXT NOT NULL, focus TEXT NOT NULL, PRIMARY KEY(owner,plan_date))',
            'CREATE TABLE IF NOT EXISTS pd_category_sources (owner TEXT NOT NULL, source_key TEXT NOT NULL, category_id INTEGER NOT NULL, PRIMARY KEY(owner,source_key))',
            'CREATE INDEX IF NOT EXISTS pd_category_sources_category ON pd_category_sources(owner,category_id)',
        ] as $sql) $db->exec($sql);
            if ($ownsTransaction) $db->commit();
        } catch (Throwable $error) {
            if ($ownsTransaction && $db->inTransaction()) $db->rollBack();
            throw $error;
        }
    }
    public static function date(mixed $value): string
    {
        if (!is_string($value)) throw new InvalidArgumentException('Elige una fecha válida.');
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value || $value < '2000-01-01' || $value > '2100-12-31') throw new InvalidArgumentException('Elige una fecha entre 2000 y 2100.');
        return $value;
    }

    private function query(string $sql, array $values = []): PDOStatement
    {
        $query = $this->db->prepare($sql);
        $query->execute($values);
        return $query;
    }

    private static function text(mixed $value, int $max, string $label, bool $required = false): string
    {
        if (!is_string($value) || !preg_match('//u', $value)) throw new InvalidArgumentException("Revisa $label.");
        $value = trim($value);
        if (mb_strlen($value, 'UTF-8') > $max || ($required && $value === '')) throw new InvalidArgumentException("Revisa $label (máximo $max caracteres).");
        return $value;
    }

    private function category(mixed $id): array
    {
        $category = $this->query('SELECT * FROM pd_categories WHERE owner=? AND id=?', [$this->owner, self::id($id)])->fetch();
        if (!$category) throw new InvalidArgumentException('La categoría ya no está disponible.');
        return $category;
    }

    private function task(mixed $id): array
    {
        $task = $this->query('SELECT * FROM pd_tasks WHERE owner=? AND id=?', [$this->owner, self::id($id)])->fetch();
        if (!$task) throw new InvalidArgumentException('La tarea ya no está disponible.');
        return $task;
    }

    private static function id(mixed $id): int
    {
        if ((!is_int($id) && !is_string($id)) || !preg_match('/^[1-9][0-9]{0,14}$/D', (string)$id)) throw new InvalidArgumentException('Identificador no válido.');
        return (int)$id;
    }

    private static function color(mixed $color): string
    {
        if (!in_array($color, self::COLORS, true)) throw new InvalidArgumentException('Elige un color disponible.');
        return $color;
    }

    private function sources(): array
    {
        if ($this->schedule === null) return [];
        return array_merge($this->schedule['subjects'],[['source_key'=>'personal','name'=>'Personal','color'=>'green','blocks'=>[]]]);
    }

    private function sourceCategory(string $key): ?array
    {
        return $this->query('SELECT c.* FROM pd_category_sources s JOIN pd_categories c ON c.id=s.category_id AND c.owner=s.owner WHERE s.owner=? AND s.source_key=?',[$this->owner,$key])->fetch() ?: null;
    }

    private function needsScheduleSync(): bool
    {
        foreach ($this->sources() as $source) {
            $category = $this->sourceCategory($source['source_key']);
            if (!$category || $category['name'] !== $source['name']) return true;
        }
        return false;
    }

    private function syncSchedule(): bool
    {
        $changed = false;
        foreach ($this->sources() as $source) {
            $category = $this->sourceCategory($source['source_key']);
            if (!$category) {
                // Keep previous personal tasks attached to the original Personal section.
                if ($source['source_key'] === 'personal') $category = $this->query("SELECT * FROM pd_categories WHERE owner=? AND LOWER(TRIM(name))='personal' ORDER BY id LIMIT 1",[$this->owner])->fetch() ?: null;
                if (!$category) {
                    $position = (int)$this->query('SELECT COALESCE(MAX(position),0)+1 FROM pd_categories WHERE owner=?',[$this->owner])->fetchColumn();
                    $this->query('INSERT INTO pd_categories(owner,name,color,position) VALUES(?,?,?,?)',[$this->owner,$source['name'],$source['color'],$position]);
                    $category = ['id'=>(int)$this->db->lastInsertId(),'name'=>$source['name']];
                }
                $this->query('INSERT INTO pd_category_sources(owner,source_key,category_id) VALUES(?,?,?)',[$this->owner,$source['source_key'],$category['id']]);
                $changed = true;
            }
            if ($category['name'] !== $source['name']) {
                $this->query('UPDATE pd_categories SET name=? WHERE owner=? AND id=?',[$source['name'],$this->owner,$category['id']]);
                $changed = true;
            }
        }
        return $changed;
    }

    private function managedCategory(int $id): bool
    {
        return (bool)$this->query("SELECT 1 FROM pd_category_sources WHERE owner=? AND category_id=? AND source_key NOT LIKE 'manual:%' LIMIT 1",[$this->owner,$id])->fetchColumn();
    }

    private function attachManualCategory(string $date, int $id): void
    {
        if ($this->schedule !== null) $this->query('INSERT OR IGNORE INTO pd_category_sources(owner,source_key,category_id) VALUES(?,?,?)',[$this->owner,'manual:'.$date.':'.$id,$id]);
    }

    /** The first write locks the transaction; revisions prevent silent overwrites. */
    public function run(string $date, ?string $action = null, array $input = []): array
    {
        self::date($date);
        if ($this->schedule !== null && ($this->schedule['date'] ?? null) !== $date) throw new InvalidArgumentException('El horario no corresponde a la fecha seleccionada.');
        if ($action === null) {
            $this->db->beginTransaction();
            try {
                $account = $this->query('SELECT seeded FROM pd_accounts WHERE owner=?', [$this->owner])->fetch();
                if ($account && (int)$account['seeded'] && !$this->needsScheduleSync()) {
                    $result = $this->snapshot($date);
                    $this->db->commit();
                    return $result;
                }
                $this->db->commit();
            } catch (Throwable $error) {
                if ($this->db->inTransaction()) $this->db->rollBack();
                throw $error;
            }
        }
        $this->db->beginTransaction();
        try {
            $this->query('INSERT OR IGNORE INTO pd_accounts(owner) VALUES(?)', [$this->owner]);
            $account = $this->query('SELECT * FROM pd_accounts WHERE owner=?', [$this->owner])->fetch();
            if (!(int)$account['seeded']) {
                foreach ($this->schedule === null ? [['Clases','blue'],['Planeación','purple'],['Gestión escolar','orange'],['Personal','green']] : [] as $position => [$name,$color]) {
                    $this->query('INSERT INTO pd_categories(owner,name,color,position) VALUES(?,?,?,?)', [$this->owner,$name,$color,$position]);
                }
                $this->query('UPDATE pd_accounts SET seeded=1 WHERE owner=?', [$this->owner]);
            }
            if ($action !== null && (!isset($input['revision']) || !is_int($input['revision']) || $input['revision'] !== (int)$account['revision'])) throw new RuntimeException('El plan cambió en otra pestaña. Actualizamos la lista; revisa e intenta guardar de nuevo.', 409);
            $scheduleChanged = $this->syncSchedule();
            if ($action !== null) {
                $this->mutate($date, $action, $input);
            }
            if ($action !== null || $scheduleChanged) {
                $this->query('UPDATE pd_accounts SET revision=revision+1 WHERE owner=?', [$this->owner]);
            }
            $result = $this->snapshot($date);
            $this->db->commit();
            return $result;
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    private function checkCapacity(string $date, int $extra = 1): void
    {
        $count = (int)$this->query('SELECT COUNT(*) FROM pd_tasks WHERE owner=? AND plan_date=?', [$this->owner,$date])->fetchColumn();
        if ($count + $extra > 300) throw new InvalidArgumentException('Puedes organizar hasta 300 tareas por día.');
    }

    private function mutate(string $date, string $action, array $input): void
    {
        switch ($action) {
            case 'edit_document':
                $tasks = $input['tasks'] ?? [];
                $categories = $input['categories'] ?? [];
                if (!is_array($tasks) || !is_array($categories) || count($tasks) > 300 || count($categories) > 20) throw new InvalidArgumentException('La edición de la hoja no es válida.');
                $seen = [];
                foreach ($tasks as $edit) {
                    if (!is_array($edit)) throw new InvalidArgumentException('Revisa las tareas de la hoja.');
                    $task = $this->task($edit['id'] ?? null);
                    if ($task['plan_date'] !== $date || isset($seen[$task['id']])) throw new InvalidArgumentException('Solo puedes editar una vez cada tarea de este día.');
                    $seen[$task['id']] = true;
                    $title = self::text($edit['title'] ?? '',240,'el texto de la tarea',true);
                    $this->query('UPDATE pd_tasks SET title=? WHERE owner=? AND id=?',[$title,$this->owner,$task['id']]);
                }
                $seen = [];
                foreach ($categories as $edit) {
                    if (!is_array($edit)) throw new InvalidArgumentException('Revisa los títulos de la hoja.');
                    $category = $this->category($edit['id'] ?? null);
                    if ($this->managedCategory((int)$category['id'])) throw new InvalidArgumentException('Los títulos del horario se actualizan desde tus asignaciones. Puedes editar las tareas debajo de cada materia.');
                    if (isset($seen[$category['id']])) throw new InvalidArgumentException('Hay un título repetido en la edición.');
                    $seen[$category['id']] = true;
                    $name = self::text($edit['name'] ?? '',100,'el título de la sección',true);
                    $this->query('UPDATE pd_categories SET name=? WHERE owner=? AND id=?',[$name,$this->owner,$category['id']]);
                }
                break;
            case 'save_task':
                $current = empty($input['id']) ? null : $this->task($input['id']);
                $taskDate = self::date($input['task_date'] ?? $date);
                $category = $this->category($input['category_id'] ?? null);
                $title = self::text($input['title'] ?? '',240,'el título',true);
                $description = self::text($input['description'] ?? '',4000,'las notas');
                $priority = $input['priority'] ?? 'normal';
                if (!in_array($priority,['normal','alta'],true)) throw new InvalidArgumentException('Prioridad no válida.');
                $highlight = self::color($input['highlight'] ?? 'green');
                $time = $input['task_time'] ?? '';
                if (!is_string($time) || ($time !== '' && !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D',$time))) throw new InvalidArgumentException('Hora no válida.');
                $position = (int)$this->query('SELECT COALESCE(MAX(position),0)+1 FROM pd_tasks WHERE owner=? AND plan_date=?',[$this->owner,$taskDate])->fetchColumn();
                if (!$current || $current['plan_date'] !== $taskDate) $this->checkCapacity($taskDate);
                if ($current) {
                    $this->query('UPDATE pd_tasks SET plan_date=?,category_id=?,title=?,description=?,task_time=?,priority=?,highlight=?,position=?,copied_from=? WHERE owner=? AND id=?',[$taskDate,$category['id'],$title,$description,$time ?: null,$priority,$highlight,$current['plan_date'] === $taskDate ? $current['position'] : $position,$current['plan_date'] === $taskDate ? $current['copied_from'] : null,$this->owner,$current['id']]);
                } else {
                    $this->query('INSERT INTO pd_tasks(owner,plan_date,category_id,title,description,task_time,priority,highlight,position) VALUES(?,?,?,?,?,?,?,?,?)',[$this->owner,$taskDate,$category['id'],$title,$description,$time ?: null,$priority,$highlight,$position]);
                }
                break;
            case 'complete':
                $task = $this->task($input['id'] ?? null);
                if ($task['plan_date'] !== $date) throw new InvalidArgumentException('La tarea ya no corresponde a este día. Actualiza la lista.');
                if (!is_bool($input['done'] ?? null)) throw new InvalidArgumentException('Estado no válido.');
                $this->query('UPDATE pd_tasks SET done=?,completed_at=? WHERE owner=? AND id=?',[$input['done'] ? 1 : 0,$input['done'] ? ($task['completed_at'] ?? date('Y-m-d H:i:s')) : null,$this->owner,$task['id']]);
                break;
            case 'delete_task':
                $task = $this->task($input['id'] ?? null);
                $this->query('DELETE FROM pd_tasks WHERE owner=? AND id=?',[$this->owner,$task['id']]);
                break;
            case 'swap':
                $a = $this->task($input['id'] ?? null);
                $b = $this->task($input['other_id'] ?? null);
                if ($a['plan_date'] !== $b['plan_date']) throw new InvalidArgumentException('Solo puedes ordenar tareas del mismo día.');
                $this->query('UPDATE pd_tasks SET position=? WHERE owner=? AND id=?',[$b['position'],$this->owner,$a['id']]);
                $this->query('UPDATE pd_tasks SET position=? WHERE owner=? AND id=?',[$a['position'],$this->owner,$b['id']]);
                break;
            case 'save_category':
                $name = self::text($input['name'] ?? '',100,'el nombre',true);
                $color = self::color($input['color'] ?? 'purple');
                if (!empty($input['id'])) {
                    $category = $this->category($input['id']);
                    if ($this->managedCategory((int)$category['id'])) throw new InvalidArgumentException('Esta sección viene del horario y conserva el nombre de la materia.');
                    $this->query('UPDATE pd_categories SET name=?,color=? WHERE owner=? AND id=?',[$name,$color,$this->owner,$category['id']]);
                    $this->attachManualCategory($date,(int)$category['id']);
                } else {
                    $count = (int)$this->query("SELECT COUNT(*) FROM pd_categories c WHERE c.owner=? AND NOT EXISTS (SELECT 1 FROM pd_category_sources s WHERE s.owner=c.owner AND s.category_id=c.id AND s.source_key NOT LIKE 'manual:%')",[$this->owner])->fetchColumn();
                    if ($count >= 20) throw new InvalidArgumentException('Puedes tener hasta 20 categorías.');
                    $this->query('INSERT INTO pd_categories(owner,name,color,position) VALUES(?,?,?,?)',[$this->owner,$name,$color,$count]);
                    $this->attachManualCategory($date,(int)$this->db->lastInsertId());
                }
                break;
            case 'delete_category':
                $category = $this->category($input['id'] ?? null);
                if ($this->managedCategory((int)$category['id'])) throw new InvalidArgumentException('Las materias del horario y la sección Personal no se eliminan desde esta hoja.');
                $target = $this->category($input['target_id'] ?? null);
                if ($target['id'] === $category['id']) throw new InvalidArgumentException('Elige otra categoría para conservar las tareas.');
                $this->query('UPDATE pd_tasks SET category_id=? WHERE owner=? AND category_id=?',[$target['id'],$this->owner,$category['id']]);
                $this->query('DELETE FROM pd_category_sources WHERE owner=? AND category_id=?',[$this->owner,$category['id']]);
                $this->query('DELETE FROM pd_categories WHERE owner=? AND id=?',[$this->owner,$category['id']]);
                break;
            case 'focus':
                $focus = self::text($input['focus'] ?? '',1000,'tu enfoque');
                $this->query('INSERT INTO pd_days(owner,plan_date,focus) VALUES(?,?,?) ON CONFLICT(owner,plan_date) DO UPDATE SET focus=excluded.focus',[$this->owner,$date,$focus]);
                break;
            case 'copy':
                $source = self::date($input['source'] ?? null);
                if ($source === $date) throw new InvalidArgumentException('Elige un día diferente al actual.');
                if (!is_bool($input['pending_only'] ?? null)) throw new InvalidArgumentException('Selecciona qué tareas copiar.');
                $tasks = $this->query('SELECT * FROM pd_tasks WHERE owner=? AND plan_date=? ORDER BY position,id',[$this->owner,$source])->fetchAll();
                $copied = $this->query('SELECT copied_from FROM pd_tasks WHERE owner=? AND plan_date=? AND copied_from IS NOT NULL',[$this->owner,$date])->fetchAll(PDO::FETCH_COLUMN);
                $tasks = array_values(array_filter($tasks,fn($t) => (!$input['pending_only'] || !$t['done']) && !in_array($t['id'],$copied)));
                $this->checkCapacity($date,count($tasks));
                $position = (int)$this->query('SELECT COALESCE(MAX(position),0) FROM pd_tasks WHERE owner=? AND plan_date=?',[$this->owner,$date])->fetchColumn();
                foreach ($tasks as $task) $this->query('INSERT INTO pd_tasks(owner,plan_date,category_id,title,description,task_time,priority,highlight,position,copied_from) VALUES(?,?,?,?,?,?,?,?,?,?)',[$this->owner,$date,$task['category_id'],$task['title'],$task['description'],$task['task_time'],$task['priority'],$task['highlight'],++$position,$task['id']]);
                break;
            default:
                throw new InvalidArgumentException('Operación no disponible.');
        }
    }

    private function snapshot(string $date): array
    {
        $day = new DateTimeImmutable($date);
        $monday = $day->modify('monday this week')->format('Y-m-d');
        $sunday = (new DateTimeImmutable($monday))->modify('+6 days')->format('Y-m-d');
        $categories = $this->query('SELECT id,name,color FROM pd_categories WHERE owner=? ORDER BY position,id',[$this->owner])->fetchAll();
        $tasks = $this->query('SELECT id,plan_date,category_id,title,description,task_time,priority,highlight,done,completed_at FROM pd_tasks WHERE owner=? AND plan_date=? ORDER BY position,id',[$this->owner,$date])->fetchAll();
        foreach ($categories as &$category) $category['id'] = (int)$category['id'];
        unset($category);
        foreach ($tasks as &$task) {
            $task['id'] = (int)$task['id']; $task['category_id'] = (int)$task['category_id']; $task['done'] = (bool)$task['done'];
            $task['task_time'] = $task['task_time'] ? substr($task['task_time'],0,5) : '';
        }
        unset($task);
        $week = $this->query('SELECT plan_date,COUNT(*) AS total,SUM(done) AS done FROM pd_tasks WHERE owner=? AND plan_date BETWEEN ? AND ? GROUP BY plan_date',[$this->owner,$monday,$sunday])->fetchAll();
        foreach ($week as &$item) { $item['total'] = (int)$item['total']; $item['done'] = (int)$item['done']; }
        unset($item);
        $scheduleSummary = null;
        if ($this->schedule !== null) {
            $links = $this->query('SELECT source_key,category_id FROM pd_category_sources WHERE owner=?',[$this->owner])->fetchAll();
            $byKey = []; $managed = []; $manualIds = [];
            foreach ($links as $link) {
                $id = (int)$link['category_id']; $key = $link['source_key']; $byKey[$key] = $id;
                if (!str_starts_with($key,'manual:')) $managed[$id] = $key;
                if (str_starts_with($key,'manual:'.$date.':')) $manualIds[] = $id;
            }
            $daySources = []; $scheduledIds = [];
            foreach ($this->sources() as $source) {
                $id = $byKey[$source['source_key']] ?? null;
                if ($id !== null) { $scheduledIds[] = $id; $daySources[$id] = $source; }
            }
            $byId = [];
            $manualIds = array_values(array_filter(array_column($categories,'id'),fn($id)=>in_array($id,$manualIds,true)));
            foreach ($categories as $category) {
                $id = $category['id']; $source = $daySources[$id] ?? [];
                $category['managed'] = isset($managed[$id]);
                $category['scheduled'] = isset($source['assignment_id']);
                $category['schedule_blocks'] = $source['blocks'] ?? [];
                $category['grade'] = $source['grade'] ?? '';
                $category['assignment_id'] = $source['assignment_id'] ?? null;
                $category['saved_only'] = !isset($daySources[$id]) && !in_array($id,$manualIds,true);
                $byId[$id] = $category;
            }
            // A changed timetable must never hide tasks already recorded for this date.
            $visibleIds = array_unique(array_merge($scheduledIds,$manualIds,array_column($tasks,'category_id')));
            $categories = [];
            foreach ($visibleIds as $id) if (isset($byId[$id])) $categories[] = $byId[$id];
            $scheduleSummary = ['weekday'=>$this->schedule['weekday'] ?? '',
                'mode'=>$this->schedule['subjects'] ? 'school' : 'personal',
                'subject_count'=>count($this->schedule['subjects']), 'message'=>$this->schedule['message'] ?? ''];
        }
        $result = ['date'=>$date,'revision'=>(int)$this->query('SELECT revision FROM pd_accounts WHERE owner=?',[$this->owner])->fetchColumn(),'categories'=>$categories,'tasks'=>$tasks,'week'=>$week,'focus'=>(string)($this->query('SELECT focus FROM pd_days WHERE owner=? AND plan_date=?',[$this->owner,$date])->fetchColumn() ?: '')];
        if ($scheduleSummary !== null) $result['schedule'] = $scheduleSummary;
        return $result;
    }
}
