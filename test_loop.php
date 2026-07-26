<?php
$periodo_inicio = '2026-07-05';
$periodo_fin = '2026-09-13';
$fecha_inicio_h = new DateTime('2026-01-01');
$fecha_fin_h = new DateTime('2026-12-31');

$render_start_h = clone $fecha_inicio_h;
$render_end_h = clone $fecha_fin_h;

if ($periodo_inicio) {
    $p_start = new DateTime($periodo_inicio);
    if ($render_start_h < $p_start) $render_start_h = clone $p_start;
}
if ($periodo_fin) {
    $p_end = new DateTime($periodo_fin);
    if ($render_end_h > $p_end) $render_end_h = clone $p_end;
}

$fecha_actual_h = clone $render_start_h;
$iters = 0;
while ($fecha_actual_h <= $render_end_h) {
    echo $fecha_actual_h->format('Y-m-d') . "\n";
    $fecha_actual_h->modify('+1 day');
    $iters++;
    if ($iters > 1000) {
        echo "Infinite loop detected!\n";
        break;
    }
}
echo "Done. $iters iterations.\n";
