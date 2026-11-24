<?php
require_once __DIR__ . '/../apps/salud/store.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$db = new PDO('sqlite::memory:');
$store = new GuaguaSalud($db);
$key = $store->pair('user-a');
check($store->owner($key) === 'user-a', 'Clave identifica al propietario');
check($store->owner(str_repeat('0',64)) === null, 'Rechaza clave desconocida');
$day = ['date'=>'2026-01-01','steps'=>1000,'distance_km'=>0.8];
$store->import('user-a',$day);
$store->import('user-a',['date'=>'2026-01-01','steps'=>1500]);
$rows = $store->history('user-a','2026-01-01','2026-01-31');
check(count($rows) === 1 && $rows[0]['steps'] === 1500 && $rows[0]['distance_km'] == 0.8, 'Reenvío reemplaza sin duplicar y conserva métricas omitidas');
check($store->history('user-b','2026-01-01','2026-01-31') === [], 'Aislamiento entre cuentas');
foreach ([['date'=>'2026-02-30','steps'=>2],['date'=>'2026-01-01','steps'=>-1],['date'=>'2026-01-01','steps'=>'4'],['date'=>'2026-01-01','steps'=>1.5],['date'=>'2026-01-01'],['days'=>[$day,$day]], ['days'=>[$day,['date'=>'invalid','steps'=>8]]]] as $bad) {
    try { $store->import('user-a',$bad); throw new RuntimeException('Aceptó datos inválidos'); }
    catch (InvalidArgumentException $expected) {}
}
check($store->history('user-a','2026-01-01','2026-01-31')[0]['steps'] === 1500, 'Lote inválido no modifica el historial');
$newKey = $store->pair('user-a');
check($store->owner($key) === null && $store->owner($newKey) === 'user-a', 'Rotación invalida clave anterior');
$store->revoke('user-a');
check(!$store->paired('user-a') && $store->owner($newKey) === null, 'Revocación');
check(count($store->history('user-a','2026-01-01','2026-01-31')) === 1, 'Revocar conserva historial');
echo "Salud: validación, aislamiento, reenvíos, atomicidad y claves OK.\n";
