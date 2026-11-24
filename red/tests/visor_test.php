<?php
// Ejecutar: php red/tests/visor_test.php. No requiere sesión ni base de datos.
require __DIR__ . '/../lib/visor.php';
function check($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
check(red_ruta_relativa_valida('banco_red/mi archivo.pdf'), 'Admite nombres con espacios');
foreach (['javascript:alert(1)', '//example.com/a', chr(92) . chr(92) . 'example.com/a', "java\tscript:alert(1)"] as $url) {
    check(!red_ruta_relativa_valida($url), 'Rechaza rutas peligrosas');
}
$actividades = red_actividades_scorm('tests/fixtures/scorm');
check(count($actividades) === 1, 'SCORM con namespace y filtrado de rutas');
check(red_actividades_scorm('../comun') === [], 'SCORM fuera del módulo');
check(red_actividades_scorm('no-existe') === [], 'SCORM inexistente');
$esc = static function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$recurso = ['titulo_red' => '<script>alert(1)</script>', 'scorm' => 'no'];
$enlace_original = 'banco_red/a" onload="alert(1).pdf';
$enlace_accesible = true;
$es_recurso_enlace = false;
foreach (['pdf' => '<iframe', 'png' => '<img', 'mp4' => '<video', 'mp3' => '<audio', 'docx' => 'otra aplicación'] as $formato => $expected) {
    ob_start(); require __DIR__ . '/../views/reproductor.php'; $html = ob_get_clean();
    check(strpos($html, $expected) !== false, 'Renderiza formato ' . $formato);
    check(strpos($html, '<script>') === false, 'Escapa el título');
    check(strpos($html, 'src="banco_red/a" onload=') === false, 'Escapa la URL');
}
$formato = 'zip'; $recurso['scorm'] = 'si';
ob_start(); require __DIR__ . '/../views/reproductor.php'; $html = ob_get_clean();
check(strpos($html, 'lesson/index.html') !== false, 'Primera actividad SCORM cargada');
$enlace_accesible = false;
ob_start(); require __DIR__ . '/../views/reproductor.php'; $html = ob_get_clean();
check(strpos($html, '<iframe') === false, 'No incrusta enlaces inválidos');
echo "OK: formatos, escape HTML, rutas y SCORM.\n";
