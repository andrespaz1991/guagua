<?php
/** Consultas de lectura a endpoints fijos de actividad, mediciones y sueño. */
final class GuaguaGoogleHealthError extends RuntimeException
{
    public function __construct(public string $reason, string $message, public int $status = 400)
    { parent::__construct($message); }
}
final class GuaguaGoogleHealth
{
    private $transport;
    public function __construct(?callable $transport = null) { $this->transport = $transport; }
    private function call(string $path, string $token, ?array $body = null): array
    {
        if ($this->transport) return ($this->transport)($path, $token, $body);
        $ch = curl_init('https://health.googleapis.com/v4/users/me/' . $path);
        $headers = ['Authorization: Bearer ' . $token, 'Accept: application/json'];
        $options = [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CONNECTTIMEOUT=>8, CURLOPT_TIMEOUT=>25,
            CURLOPT_FOLLOWLOCATION=>false, CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS];
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($body, JSON_THROW_ON_ERROR);
        }
        $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $options);
        $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        if ($raw === false) throw new GuaguaGoogleHealthError('NETWORK', 'No se pudo contactar con Google Health. Intenta de nuevo.', 502);
        $data = json_decode($raw, true);
        if (!is_array($data)) throw new GuaguaGoogleHealthError('RESPONSE', 'Google devolvió una respuesta inesperada.', 502);
        if ($status >= 400) {
            $reasons = array_column($data['error']['details'] ?? [], 'reason');
            if (in_array('ACCOUNT_NOT_LINKED', $reasons, true)) throw new GuaguaGoogleHealthError('ACCOUNT_NOT_LINKED', 'Tu cuenta no está vinculada a Google Health. Inicia sesión en la app Google Health del iPhone con esta misma cuenta y vuelve a probar. Esto no confirma que tu historial de Google Fit haya migrado.');
            if (in_array('SERVICE_DISABLED', $reasons, true)) throw new GuaguaGoogleHealthError('SERVICE_DISABLED', 'Falta habilitar Google Health API en el proyecto de Google Cloud que usa Guagua.');
            if ($status === 401) throw new GuaguaGoogleHealthError('UNAUTHENTICATED', 'La autorización venció. Pulsa Conectar Google Health de nuevo.', 401);
            if ($status === 403) throw new GuaguaGoogleHealthError('PERMISSION_DENIED', 'Google denegó el acceso. Comprueba Google Health API, el permiso de actividad y tu cuenta en Usuarios de prueba.');
            throw new GuaguaGoogleHealthError('GOOGLE_ERROR', 'Google Health rechazó la consulta (HTTP ' . $status . ').', 502);
        }
        return $data;
    }
    public function sync(string $token, string $from, string $to, string $type = 'steps'): array
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $from);
        $end = DateTimeImmutable::createFromFormat('!Y-m-d', $to);
        if (!$start || !$end || $start->format('Y-m-d') !== $from || $end->format('Y-m-d') !== $to || $from > $to || $to > date('Y-m-d') || $start->diff($end)->days > 89) throw new InvalidArgumentException('Selecciona hasta 90 días, sin fechas futuras. Puedes consultar periodos anteriores por separado.');
        $identity = $this->call('identity', $token);
        if (empty($identity['healthUserId'])) throw new GuaguaGoogleHealthError('RESPONSE', 'Google no confirmó una identidad de Health vinculada.', 502);
        if ($type === 'sleep') return $this->sleep($token, $from, $to, $end);
        $types = [
            'steps'=>['steps'=>['steps','countSum',1]],
            'distance'=>['distance_km'=>['distance','millimetersSum',1000000]],
            'active-energy-burned'=>['active_kcal'=>['activeEnergyBurned','kcalSum',1]],
            'floors'=>['floors'=>['floors','countSum',1]],
            'heart-rate'=>['heart_rate_avg'=>['heartRate','beatsPerMinuteAvg',1],'heart_rate_min'=>['heartRate','beatsPerMinuteMin',1],'heart_rate_max'=>['heartRate','beatsPerMinuteMax',1]],
            'weight'=>['weight_kg'=>['weight','weightGramsAvg',1000]],
            'body-fat'=>['body_fat_pct'=>['bodyFat','bodyFatPercentageAvg',1]],
            'active-minutes'=>[],
        ];
        if (!isset($types[$type])) throw new InvalidArgumentException('Métrica de Google no admitida.');
        if (in_array($type,['heart-rate','active-minutes'],true) && $start->diff($end)->days > 13) throw new InvalidArgumentException('Pulso y minutos activos se consultan en lotes de hasta 14 días.');
        $civil = static function (DateTimeImmutable $date): array {
            return ['date'=>['year'=>(int)$date->format('Y'),'month'=>(int)$date->format('m'),'day'=>(int)$date->format('d')], 'time'=>new stdClass()];
        };
        $data = $this->call('dataTypes/' . $type . '/dataPoints:dailyRollUp', $token, [
            'range'=>['start'=>$civil($start), 'end'=>$civil($end->modify('+1 day'))],
            'windowSizeDays'=>1, 'dataSourceFamily'=>'users/me/dataSourceFamilies/all-sources'
        ]);
        $days = [];
        foreach ($data['rollupDataPoints'] ?? [] as $point) {
            $d = $point['civilStartTime']['date'] ?? [];
            if (!isset($d['year'],$d['month'],$d['day'])) continue;
            $date = sprintf('%04d-%02d-%02d', $d['year'],$d['month'],$d['day']);
            if ($date < $from || $date > $to || !checkdate((int)$d['month'],(int)$d['day'],(int)$d['year'])) throw new GuaguaGoogleHealthError('RESPONSE', 'Google devolvió una fecha fuera del rango.', 502);
            // dailyRollUp reconcilia las fuentes; no sumar otra vez los totales.
            if (isset($days[$date])) throw new GuaguaGoogleHealthError('RESPONSE', 'Google devolvió más de un total para un mismo día.', 502);
            $row = ['date'=>$date];
            foreach ($types[$type] as $key => [$field,$aggregation,$divisor]) {
                if (!isset($point[$field][$aggregation])) continue;
                $row[$key] = $this->number($point[$field][$aggregation], $divisor);
                if (in_array($key,['steps','floors'],true)) {
                    if (floor($row[$key]) != $row[$key]) throw new GuaguaGoogleHealthError('RESPONSE','Google devolvió un conteo no entero.',502);
                    $row[$key] = (int)$row[$key];
                }
            }
            if ($type === 'active-minutes') {
                $levels = $point['activeMinutes']['activeMinutesRollupByActivityLevel'] ?? [];
                if ($levels) {
                    $row['active_minutes'] = 0;
                    foreach ($levels as $level) $row['active_minutes'] += $this->number($level['activeMinutesSum'] ?? 0,1);
                }
            }
            if (count($row)>1) $days[$date] = $row;
        }
        return array_values($days);
    }
    private function number($value, float $divisor): float
    {
        if (!is_numeric($value) || !is_finite((float)$value) || (float)$value < 0) throw new GuaguaGoogleHealthError('RESPONSE','Google devolvió una medida no válida.',502);
        return (float)$value / $divisor;
    }
    private function sleep(string $token, string $from, string $to, DateTimeImmutable $end): array
    {
        $filter = 'sleep.interval.civil_end_time >= "' . $from . 'T00:00:00" AND sleep.interval.civil_end_time < "' . $end->modify('+1 day')->format('Y-m-d') . 'T00:00:00"';
        $days = []; $page = ''; $seen = []; $pages = 0;
        do {
            $params = ['filter'=>$filter,'pageSize'=>1000,'dataSourceFamily'=>'users/me/dataSourceFamilies/all-sources'];
            if ($page !== '') $params['pageToken'] = $page;
            $data = $this->call('dataTypes/sleep/dataPoints:reconcile?' . http_build_query($params),$token);
            foreach ($data['dataPoints'] ?? [] as $point) {
                $sleep = $point['sleep'] ?? [];
                $d = $sleep['interval']['civilEndTime']['date'] ?? [];
                if (!isset($d['year'],$d['month'],$d['day'])) continue;
                $date = sprintf('%04d-%02d-%02d',$d['year'],$d['month'],$d['day']);
                if ($date < $from || $date > $to || !checkdate((int)$d['month'],(int)$d['day'],(int)$d['year'])) throw new GuaguaGoogleHealthError('RESPONSE','Google devolvió una fecha de sueño fuera del rango.',502);
                $id = $point['name'] ?? hash('sha256',json_encode($sleep));
                if (isset($seen[$id])) continue;
                $seen[$id] = true;
                $summary = $sleep['summary'] ?? [];
                $metrics = [];
                if (isset($summary['minutesAsleep'])) $metrics['sleep_minutes'] = $this->number($summary['minutesAsleep'],1);
                foreach ($summary['stagesSummary'] ?? [] as $stage) {
                    $key = ['DEEP'=>'sleep_deep_minutes','REM'=>'sleep_rem_minutes','LIGHT'=>'sleep_light_minutes'][$stage['type'] ?? ''] ?? null;
                    if ($key && isset($stage['minutes'])) $metrics[$key] = ($metrics[$key] ?? 0) + $this->number($stage['minutes'],1);
                }
                if ($metrics) {
                    if (!isset($days[$date])) $days[$date] = ['date'=>$date];
                    foreach ($metrics as $key=>$value) $days[$date][$key] = ($days[$date][$key] ?? 0) + $value;
                }
            }
            $next = $data['nextPageToken'] ?? '';
            if (!is_string($next) || ($next !== '' && $next === $page)) throw new GuaguaGoogleHealthError('RESPONSE','Google devolvió una paginación no válida.',502);
            $page = $next;
            if (++$pages >= 10 && $page !== '') throw new GuaguaGoogleHealthError('RANGE','Reduce el rango de fechas del sueño para completar la consulta.',422);
        } while ($page !== '');
        return array_values($days);
    }
}
