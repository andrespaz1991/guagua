<?php
require_once __DIR__ . '/../apps/salud/google_health.php';
require_once __DIR__ . '/../apps/salud/store.php';
function expectHealth($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$calls = [];
$client = new GuaguaGoogleHealth(function($path,$token,$body) use (&$calls) {
    $calls[] = [$path,$body];
    if ($path === 'identity') return ['healthUserId'=>'test'];
    return ['rollupDataPoints'=>[
        ['civilStartTime'=>['date'=>['year'=>2026,'month'=>1,'day'=>1]],'steps'=>['countSum'=>'1234']],
        ['civilStartTime'=>['date'=>['year'=>2026,'month'=>1,'day'=>2]],'steps'=>['countSum'=>'0']]
    ]];
});
$days = $client->sync('test-token','2026-01-01','2026-01-02');
expectHealth($calls[0][0] === 'identity', 'Verifica identidad antes de leer actividad');
expectHealth($calls[1][0] === 'dataTypes/steps/dataPoints:dailyRollUp', 'Usa agregación diaria reconciliada');
expectHealth($calls[1][1]['range']['end']['date']['day'] === 3, 'Fin exclusivo incluye el último día');
expectHealth($days === [['date'=>'2026-01-01','steps'=>1234],['date'=>'2026-01-02','steps'=>0]], 'Interpreta conteos y conserva ceros reales');
$store = new GuaguaSalud(new PDO('sqlite::memory:'));
$store->import('user',['date'=>'2026-01-01','steps'=>62]);
$store->import('google:user',['days'=>$days]);
expectHealth($store->history('user','2026-01-01','2026-01-02')[0]['steps'] === 62, 'No modifica actividad del iPhone');
expectHealth(count($store->history('google:user','2026-01-01','2026-01-02')) === 2, 'Guarda historial Google independiente');
foreach ([['2026-02-30','2026-03-01'],['2026-01-01','2026-04-02'],['2026-01-03','2026-01-01']] as $range) {
    try { $client->sync('test-token',...$range); throw new RuntimeException('Aceptó rango inválido'); }
    catch (InvalidArgumentException $expected) {}
}
$empty = new GuaguaGoogleHealth(fn($path) => $path === 'identity' ? ['healthUserId'=>'test'] : []);
expectHealth($empty->sync('test-token','2026-01-01','2026-01-02') === [], 'Sin registros no inventa ceros');
$unlinked = new GuaguaGoogleHealth(fn() => []);
try { $unlinked->sync('test-token','2026-01-01','2026-01-02'); throw new RuntimeException('Aceptó identidad ausente'); }
catch (GuaguaGoogleHealthError $expected) {}
echo "Google Health: identidad, rangos, fechas, conteos, ausencia de datos y separación de fuentes OK.\n";

$responses = [
    'distance'=>['distance'=>['millimetersSum'=>'4500000']],
    'active-energy-burned'=>['activeEnergyBurned'=>['kcalSum'=>345.5]],
    'heart-rate'=>['heartRate'=>['beatsPerMinuteAvg'=>72,'beatsPerMinuteMin'=>50,'beatsPerMinuteMax'=>155]],
    'weight'=>['weight'=>['weightGramsAvg'=>72500]],
    'body-fat'=>['bodyFat'=>['bodyFatPercentageAvg'=>18.2]],
    'floors'=>['floors'=>['countSum'=>'12']],
    'active-minutes'=>['activeMinutes'=>['activeMinutesRollupByActivityLevel'=>[['activeMinutesSum'=>'20'],['activeMinutesSum'=>'15']]]],
];
$many = new GuaguaGoogleHealth(function($path,$token,$body) use ($responses) {
    if ($path === 'identity') return ['healthUserId'=>'test'];
    preg_match('~dataTypes/([^/]+)/~',$path,$match);
    return ['rollupDataPoints'=>[array_merge(['civilStartTime'=>['date'=>['year'=>2026,'month'=>1,'day'=>1]]],$responses[$match[1]])]];
});
foreach (array_keys($responses) as $type) {
    $store->import('google:user',['days'=>$many->sync('test-token','2026-01-01','2026-01-01',$type)]);
}
$row=$store->history('google:user','2026-01-01','2026-01-01')[0];
expectHealth($row['steps']===1234 && $row['distance_km']===4.5 && $row['weight_kg']===72.5, 'Convierte mm a km y gramos a kg sin perder pasos');
expectHealth($row['heart_rate_avg']===72.0 && $row['heart_rate_min']===50.0 && $row['heart_rate_max']===155.0, 'Conserva promedio y extremos del pulso');
expectHealth($row['active_minutes']===35.0 && $row['active_kcal']===345.5 && $row['floors']===12.0, 'Agrega minutos y conserva unidades de actividad');
$sleepCalls=0;
$sleep = new GuaguaGoogleHealth(function($path) use (&$sleepCalls) {
    if($path==='identity') return ['healthUserId'=>'test'];
    $sleepCalls++;
    return ['dataPoints'=>[['name'=>'session-'.$sleepCalls,'sleep'=>[
        'interval'=>['civilEndTime'=>['date'=>['year'=>2026,'month'=>1,'day'=>1]]],
        'summary'=>['minutesAsleep'=>$sleepCalls===1 ? '400':'30','stagesSummary'=>[['type'=>'DEEP','minutes'=>'50'],['type'=>'REM','minutes'=>'40']]]
    ]]],'nextPageToken'=>$sleepCalls===1 ? 'next-page':''];
});
$sleepDays=$sleep->sync('test-token','2026-01-01','2026-01-01','sleep');
expectHealth($sleepCalls===2 && $sleepDays[0]['sleep_minutes']===430.0, 'Lee todas las páginas y suma sesiones por día de despertar');
$store->import('google:user',['days'=>$sleepDays]);
$store->import('google:user',['days'=>$sleepDays]);
$row=$store->history('google:user','2026-01-01','2026-01-01')[0];
expectHealth($row['sleep_minutes']===430.0 && $row['sleep_deep_minutes']===100.0 && $row['heart_rate_avg']===72.0, 'Reenvía sueño sin duplicar ni perder otras métricas');
try {$many->sync('test-token','2026-01-01','2026-01-15','heart-rate');throw new RuntimeException('Aceptó rango de pulso excesivo');}
catch(InvalidArgumentException $expected) {}
expectHealth($row['sleep_light_minutes']===null, 'Una fase ausente no se inventa como cero');
echo "Métricas ampliadas: unidades, pulso, actividad, sueño paginado y actualización parcial OK.\n";
