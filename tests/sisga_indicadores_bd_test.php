<?php
declare(strict_types=1);
require_once __DIR__ . '/../sisga/lib/Indicadores.php';
require_once __DIR__ . '/../comun/database.php';
function comprobar_sisga(bool $condicion, string $mensaje): void { if (!$condicion) throw new RuntimeException($mensaje); }
function rechazar_sisga(callable $accion): void {
    try { $accion(); } catch (InvalidArgumentException $e) { return; }
    throw new RuntimeException('Se aceptaron datos inválidos.');
}
$perfil = [
    'contexto' => [
        'origen' => 'https://www.sisga.com.co', 'institucion' => 'prueba-' . bin2hex(random_bytes(6)),
        'materia' => ['codigo' => '0401', 'nombre' => 'Materia de prueba'],
        'grado' => ['codigo' => '74', 'nombre' => '6'], 'periodo' => ['codigo' => '4', 'nombre' => '4'],
        'nivel' => '0', 'grupoOrigen' => ['codigo' => '1', 'nombre' => 'Prueba'],
    ],
    'guardadoEn' => '2026-11-20T12:00:00.000Z',
    'indicadores' => [['codigo' => '60318', 'texto' => 'Comprende el contenido.', 'categoria' => ['codigo' => '1', 'nombre' => 'FORTALEZAS']]],
];
$payload = json_encode(['modo' => 'aplicar', 'perfiles' => [$perfil]], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
$script = '(function(){ throw new Error("Este código nunca debe ejecutarse"); })(' . $payload . ').catch(console.error);';
$leidos = SisgaIndicadores::extraer($script);
comprobar_sisga($leidos[0]['contexto']['materia']['codigo'] === '0401', 'Se perdieron los ceros iniciales.');
comprobar_sisga(SisgaIndicadores::extraer(json_encode($perfil))[0] === $leidos[0], 'No se admitió el JSON de un perfil.');
$malo = $perfil; $malo['indicadores'][] = $malo['indicadores'][0]; rechazar_sisga(fn() => SisgaIndicadores::validar($malo));
$malo = $perfil; $malo['contexto']['origen'] = 'https://sisga.com.co.ejemplo.com'; rechazar_sisga(fn() => SisgaIndicadores::validar($malo));
$malo = $perfil; $malo['contexto']['origen'] = 'https://usuario@www.sisga.com.co'; rechazar_sisga(fn() => SisgaIndicadores::validar($malo));
$malo = $perfil; $malo['guardadoEn'] = '2026-02-31T12:00:00Z'; rechazar_sisga(fn() => SisgaIndicadores::validar($malo));
rechazar_sisga(fn() => SisgaIndicadores::extraer('alert("No es una captura")'));

$db = guagua_db_pdo(); $repo = new SisgaIndicadores($db);
$db->beginTransaction();
try {
    $archivo = ['nombre' => 'asignar-indicadores-prueba.js', 'contenido' => $script];
    $r = $repo->importar([$archivo, $archivo]);
    comprobar_sisga($r['nuevos'] === 1 && $r['combinaciones'] === 1, 'Se duplicaron perfiles de archivos acumulados.');
    $r = $repo->importar([$archivo]); comprobar_sisga($r['sinCambios'] === 1 && $r['nuevos'] === 0, 'La importación no es idempotente.');
    $nuevo = $perfil; $nuevo['guardadoEn'] = '2026-11-20T13:00:00.000Z'; $nuevo['indicadores'][0]['texto'] = 'Texto actualizado.';
    $r = $repo->importar([['nombre' => 'nuevo.json', 'contenido' => json_encode($nuevo)]]);
    comprobar_sisga($r['actualizados'] === 1, 'No se actualizó la captura más reciente.');
    $r = $repo->importar([$archivo]); comprobar_sisga($r['sinCambios'] === 1, 'Una captura antigua reemplazó la nueva.');
    $filas = array_values(array_filter($repo->listar(), fn($r) => $r['perfil']['contexto']['institucion'] === $perfil['contexto']['institucion']));
    comprobar_sisga(count($filas) === 1 && $filas[0]['perfil']['indicadores'][0]['texto'] === 'Texto actualizado.', 'No se recuperó el perfil esperado.');
    rechazar_sisga(fn() => $repo->importar([['nombre' => '../invalido.js', 'contenido' => $script]]));
    $otro = $perfil; $otro['contexto']['periodo'] = ['codigo' => '3', 'nombre' => '3'];
    rechazar_sisga(fn() => $repo->importar([['nombre' => 'valido.json', 'contenido' => json_encode($otro)], ['nombre' => 'invalido.js', 'contenido' => 'sin datos']]));
    $filas = array_filter($repo->listar(), fn($r) => $r['perfil']['contexto']['institucion'] === $perfil['contexto']['institucion']);
    comprobar_sisga(count($filas) === 1, 'Una importación inválida dejó registros parciales.');
    $nuevosGrados = [];
    foreach ([9, 10, 11] as $grado) {
        $captura = $perfil;
        $captura['contexto']['grado'] = ['codigo' => (string)(68 + $grado), 'nombre' => (string)$grado];
        $nuevosGrados[] = ['nombre' => 'grado-' . $grado . '.json', 'contenido' => json_encode($captura)];
    }
    $r = $repo->importar($nuevosGrados);
    comprobar_sisga($r['nuevos'] === 3, 'No se guardaron los nuevos grados 9, 10 y 11.');
    $filas = array_values(array_filter($repo->listar(), fn($r) => $r['perfil']['contexto']['institucion'] === $perfil['contexto']['institucion']));
    $grados = array_map(fn($r) => $r['perfil']['contexto']['grado']['nombre'], $filas);
    foreach (['9', '10', '11'] as $grado) comprobar_sisga(in_array($grado, $grados, true), 'No se recuperó el grado ' . $grado . ' de MySQL.');
    $r = $repo->importar($nuevosGrados);
    comprobar_sisga($r['sinCambios'] === 3 && $r['nuevos'] === 0, 'Reimportar los nuevos grados creó duplicados.');
} finally { $db->rollBack(); }
echo "OK: JSON sin ejecución de JS, validación, deduplicación, actualización, importación atómica y guardado/consulta de grados 9, 10 y 11 en MySQL. Datos de prueba revertidos.\n";
