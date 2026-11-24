<?php
require __DIR__ . '/../usuario/lib/hoja_vida_periodos.php';
function hv_expect($condition, $message) { if (!$condition) throw new RuntimeException($message); }
hv_expect(hv_fecha_en_rango('2026-03-27 23:59:59', '2026-01-13', '2026-03-27'), 'The full final day must be included');
hv_expect(hv_fecha_en_rango('2026-01-13', '2026-01-13', '2026-03-27'), 'The first day must be included');
hv_expect(!hv_fecha_en_rango('2026-03-28', '2026-01-13', '2026-03-27'), 'The next day must be excluded');
hv_expect(!hv_fecha_en_rango('', '2026-01-13', '2026-03-27'), 'Missing dates must not enter a period');
hv_expect(!hv_fecha_en_rango('0000-00-00', '2026-01-13', '2026-03-27'), 'Zero dates must be excluded');
hv_expect(hv_fecha_actividad(['fecha_entrega'=>'0000-00-00','fecha_publicacion'=>'2026-02-02']) === '2026-02-02', 'Publication is the fallback');
hv_expect(hv_fecha_actividad(['fecha_entrega'=>'2026-03-01','fecha_publicacion'=>'2026-02-02']) === '2026-03-01', 'Delivery takes precedence');
$summary=hv_resumir_actividades([['valoracion'=>'4,5'], ['valoracion'=>'3.5'], ['valoracion'=>''], ['valoracion'=>'Pendiente'], ['valoracion'=>'0']]);
hv_expect($summary['actividades_valoradas'] === 3 && $summary['nota_seguimiento'] === 2.67, 'Ignore missing/text grades but retain zero and decimal commas');
hv_expect(hv_resumir_actividades([['valoracion'=>'']])['nota_seguimiento'] === null, 'No grades is not a zero average');
hv_expect(hv_nota_numerica('4..2') === null, 'Malformed grades must be ignored');
echo "OK: 10 checks for date ranges and averages.\n";
