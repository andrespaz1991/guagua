<?php
require_once __DIR__ . '/metrics.php';
final class GuaguaSalud
{
    public function __construct(private PDO $db)
    {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA busy_timeout=5000');
        $db->exec('CREATE TABLE IF NOT EXISTS devices (owner TEXT PRIMARY KEY, token_hash TEXT UNIQUE NOT NULL)');
        $db->exec('CREATE TABLE IF NOT EXISTS daily (owner TEXT NOT NULL, day TEXT NOT NULL, steps INTEGER, distance_km REAL, active_kcal REAL, updated TEXT NOT NULL, PRIMARY KEY(owner,day))');
        $columns = array_column($db->query('PRAGMA table_info(daily)')->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('metrics_json', $columns, true)) $db->exec('ALTER TABLE daily ADD COLUMN metrics_json TEXT');
    }
    public function paired(string $owner): bool
    {
        $q = $this->db->prepare('SELECT 1 FROM devices WHERE owner=?'); $q->execute([$owner]);
        return (bool) $q->fetchColumn();
    }
    public function pair(string $owner): string
    {
        $token = bin2hex(random_bytes(32));
        $q = $this->db->prepare('INSERT INTO devices(owner,token_hash) VALUES(?,?) ON CONFLICT(owner) DO UPDATE SET token_hash=excluded.token_hash');
        $q->execute([$owner, hash('sha256', $token)]);
        return $token;
    }
    public function revoke(string $owner): void
    {
        $q = $this->db->prepare('DELETE FROM devices WHERE owner=?'); $q->execute([$owner]);
    }
    public function owner(string $token): ?string
    {
        $q = $this->db->prepare('SELECT owner FROM devices WHERE token_hash=?');
        $q->execute([hash('sha256', $token)]);
        $owner = $q->fetchColumn();
        return $owner === false ? null : (string) $owner;
    }
    public function import(string $owner, array $input): int
    {
        $days = isset($input['days']) ? $input['days'] : [$input];
        if (!is_array($days) || count($days) < 1 || count($days) > 3660 || array_keys($days) !== range(0, count($days)-1)) throw new InvalidArgumentException('Envía entre 1 y 3660 días.');
        $valid = []; $seen = [];
        foreach ($days as $day) {
            $date = is_array($day) ? ($day['date'] ?? null) : null;
            if (!is_string($date) || !preg_match('/\A(20[0-9]{2})-([0-9]{2})-([0-9]{2})\z/', $date, $m)
                || !checkdate((int)$m[2], (int)$m[3], (int)$m[1]) || $date > date('Y-m-d') || isset($seen[$date])) throw new InvalidArgumentException('Fecha no válida, futura o repetida. Usa yyyy-MM-dd.');
            $seen[$date] = true; $values = [$owner, $date]; $present = false;
            $extra = [];
            foreach (guagua_salud_metrics() as $key => $definition) {
                $value = $day[$key] ?? null;
                if ($value !== null) {
                    if ((!is_int($value) && !is_float($value)) || !is_finite((float)$value) || $value < 0 || $value > $definition['max'] || (!empty($definition['integer']) && floor($value) != $value)) throw new InvalidArgumentException('Medida no válida: ' . $key . '. Usa números en las unidades indicadas.');
                    $present = true;
                }
                if (in_array($key, ['steps','distance_km','active_kcal'], true)) $values[] = $value;
                elseif ($value !== null) $extra[$key] = $value;
            }
            if (!$present) throw new InvalidArgumentException('Cada día necesita al menos una medida.');
            $values[] = gmdate('c'); $values[] = $extra; $valid[] = $values;
        }
        $this->db->beginTransaction();
        try {
            $q = $this->db->prepare('INSERT INTO daily(owner,day,steps,distance_km,active_kcal,updated,metrics_json) VALUES(?,?,?,?,?,?,?) ON CONFLICT(owner,day) DO UPDATE SET steps=COALESCE(excluded.steps,daily.steps), distance_km=COALESCE(excluded.distance_km,daily.distance_km), active_kcal=COALESCE(excluded.active_kcal,daily.active_kcal), updated=excluded.updated,metrics_json=excluded.metrics_json');
            $previous = $this->db->prepare('SELECT metrics_json FROM daily WHERE owner=? AND day=?');
            foreach ($valid as $row) {
                $extra = array_pop($row);
                $previous->execute([$row[0],$row[1]]);
                $stored = json_decode((string)$previous->fetchColumn(), true) ?: [];
                $row[] = json_encode(array_replace($stored, $extra), JSON_THROW_ON_ERROR);
                $q->execute($row);
            }
            $this->db->commit();
        } catch (Throwable $e) { $this->db->rollBack(); throw $e; }
        return count($valid);
    }
    public function history(string $owner, string $from, string $to): array
    {
        $q = $this->db->prepare('SELECT day AS date, steps, distance_km, active_kcal, updated, metrics_json FROM daily WHERE owner=? AND day BETWEEN ? AND ? ORDER BY day DESC');
        $q->execute([$owner, $from, $to]);
        $rows = $q->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $extra = json_decode((string)$row['metrics_json'], true) ?: [];
            unset($row['metrics_json']);
            foreach (guagua_salud_metrics() as $key => $definition) if (!array_key_exists($key,$row)) $row[$key] = isset($extra[$key]) ? (float)$extra[$key] : null;
            $row['steps'] = $row['steps'] === null ? null : (int)$row['steps'];
            foreach (['distance_km','active_kcal'] as $key) $row[$key] = $row[$key] === null ? null : (float)$row[$key];
        }
        unset($row);
        return $rows;
    }
}
