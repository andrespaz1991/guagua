<?php
require_once __DIR__.'/../reportes/informe_docente_datos.php';
function verificar_extraedad($condicion, $mensaje) { if (!$condicion) throw new RuntimeException($mensaje); }
foreach (range(2,9) as $grado) {
 verificar_extraedad(informe_extraedad((string)$grado,$grado+7)['extraedad']===false,"Debajo del umbral: $grado");
 verificar_extraedad(informe_extraedad((string)$grado,$grado+8)['extraedad']===true,"En el umbral: $grado");
 verificar_extraedad(informe_extraedad((string)$grado,$grado+9)['extraedad']===true,"Sobre el umbral: $grado");
 verificar_extraedad(informe_extraedad((string)$grado,null)['extraedad']===null,'Edad desconocida');
}
foreach (['1','10','11','Transición','Sin grado','6-8','6A','16'] as $grado) {
 verificar_extraedad(informe_extraedad($grado,19)['extraedad']===null,"No inventar umbral: $grado");
}
foreach (['6','6°','6º','Grado 6','Sexto'] as $grado) verificar_extraedad(informe_extraedad($grado,14)['extraedad']===true,"Formato: $grado");
verificar_extraedad(informe_extraedad('Séptimo',15)['umbral_extraedad']===15,'Grado escrito');
$nacimiento=new DateTimeImmutable('2012-10-18');
verificar_extraedad(informe_extraedad('6',$nacimiento->diff(new DateTimeImmutable('2026-10-17'))->y)['extraedad']===false,'Día anterior al cumpleaños');
verificar_extraedad(informe_extraedad('6',$nacimiento->diff(new DateTimeImmutable('2026-10-18'))->y)['extraedad']===true,'Cumpleaños');
echo "OK: umbrales SIE, formatos, datos faltantes y cumpleaños.\n";
