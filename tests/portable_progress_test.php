<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/comun/lib/GuaguaPortableProgress.php';
require_once dirname(__DIR__) . '/comun/lib/GuaguaPortableBackup.php';
$checks = 0;
function progress_check(bool $condition, string $message): void {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
$root = sys_get_temp_dir() . '/guagua-progress-test-' . bin2hex(random_bytes(8));
mkdir($root, 0700);
$progress = new GuaguaPortableProgress($root, str_repeat('a', 32), 'sesion-docente-fixture');
$directoryProperty = new ReflectionProperty($progress, 'directory');
$directoryProperty->setAccessible(true);
$directory = $directoryProperty->getValue($progress);
try {
    progress_check($progress->read() === null, 'Una preparación sin iniciar debe seguir pendiente.');
    $progress->begin();
    $initial = $progress->read();
    progress_check($initial['status'] === 'preparing' && $initial['percent'] === 0 && $initial['remaining_seconds'] === null, 'Estado inicial incorrecto.');
    progress_check((new GuaguaPortableProgress($root, str_repeat('a', 32), 'otra-sesion'))->read() === null, 'Otra sesión puede consultar el avance.');
    progress_check((new GuaguaPortableProgress($root, str_repeat('b', 32), 'sesion-docente-fixture'))->read() === null, 'Se mezclan preparaciones de una misma sesión.');
    $startedProperty = new ReflectionProperty($progress, 'startedAt');
    $startedProperty->setAccessible(true);
    $startedProperty->setValue($progress, microtime(true) - 10);
    $progress->report(50, 'Exportando las bases de datos…');
    $state = $progress->read();
    progress_check($state['percent'] === 50 && $state['elapsed_seconds'] >= 10, 'No se informa el porcentaje y el tiempo transcurrido.');
    progress_check($state['remaining_seconds'] >= 10 && $state['remaining_seconds'] <= 12, 'Estimación de tiempo restante incorrecta.');
    $progress->report(30, 'Comprobando el avance…');
    progress_check($progress->read()['percent'] === 50, 'El avance retrocede.');
    $progress->report(150, 'Terminando…');
    progress_check($progress->read()['percent'] === 99, 'Se muestra 100 antes de que el ZIP esté listo.');
    $progress->complete();
    $state = $progress->read();
    progress_check($state['status'] === 'ready' && $state['percent'] === 100 && $state['remaining_seconds'] === 0, 'No se anuncia el ZIP listo.');
    $progress->fail();
    progress_check($progress->read()['status'] === 'ready', 'Un estado final ha sido sobrescrito.');
    progress_check(array_keys($state) === ['status','percent','stage','elapsed_seconds','remaining_seconds','updated_at'], 'El estado contiene información ajena al avance.');

    $failed = new GuaguaPortableProgress($root, str_repeat('b', 32), 'sesion-docente-fixture');
    $failed->begin();
    $failed->report(20, 'Exportando…');
    $failed->fail();
    $state = $failed->read();
    progress_check($state['status'] === 'error' && $state['percent'] === 20 && $state['remaining_seconds'] === null, 'El fallo no detiene la estimación.');
    try {
        new GuaguaPortableProgress($root, '../invalido', 'sesion-docente-fixture');
        throw new LogicException('Se aceptó un identificador inseguro.');
    } catch (InvalidArgumentException $expected) { $checks++; }

    // Comprueba el seguimiento de un proceso real sin conectarse a las bases del usuario.
    $backup = new GuaguaPortableBackup($root);
    $wait = new ReflectionMethod($backup, 'waitForProcess');
    $wait->setAccessible(true);
    foreach ([0, 7] as $exit) {
        $process = proc_open([PHP_BINARY, '-r', 'usleep(250000); exit(' . $exit . ');'], [0=>['pipe','r'], 1=>['file',$root . '/stdout','wb'], 2=>['file',$root . '/stderr','wb']], $pipes, null, null, ['bypass_shell'=>true]);
        fclose($pipes[0]);
        $ticks = 0;
        $actual = $wait->invoke($backup, $process, static function () use (&$ticks): void { $ticks++; });
        progress_check($actual === $exit && $ticks >= 2, 'No se conservó el código de salida o se dejó de informar durante la exportación.');
    }

    // Ejecuta el endpoint real con sesiones aisladas, sin abrir MySQL ni una sesión del usuario.
    $sessionDirectory = $root . '/sessions';
    mkdir($sessionDirectory, 0700);
    $previousSessionPath = session_save_path();
    session_save_path($sessionDirectory);
    session_name('guagua_session');
    $sessions = [];
    foreach (['docente', 'admin', 'estudiante', 'anonimo'] as $role) {
        session_id(bin2hex(random_bytes(16)));
        session_start();
        $sessions[$role] = session_id();
        $_SESSION = $role === 'anonimo' ? [] : ['id_usuario'=>42, 'rol'=>$role];
        session_write_close();
    }
    session_save_path($previousSessionPath);
    $apiId = bin2hex(random_bytes(16));
    $apiProgress = new GuaguaPortableProgress(dirname(__DIR__), $apiId, $sessions['docente']);
    $apiProgress->begin();
    $apiProgress->report(35, 'Exportando…');
    $wrapper = $root . '/endpoint.php';
    file_put_contents($wrapper, '<?php if (PHP_SAPI !== "cli") { http_response_code(404); exit; } session_save_path($argv[2]); session_name("guagua_session"); session_id($argv[3]); $_SERVER["REQUEST_METHOD"]="GET"; $_GET=["descarga"=>$argv[4]]; register_shutdown_function(static function (): void { echo "\nHTTP:" . (http_response_code() ?: 200); }); require $argv[1];');
    foreach (['docente'=>200, 'admin'=>202, 'estudiante'=>403, 'anonimo'=>401, 'invalido'=>400] as $role => $expectedStatus) {
        $process = proc_open([PHP_BINARY, $wrapper, dirname(__DIR__) . '/reportes/descargar_guagua_progreso.php', $sessionDirectory, $sessions[$role === 'invalido' ? 'docente' : $role], $role === 'invalido' ? '../invalido' : $apiId], [0=>['pipe','r'], 1=>['pipe','w'], 2=>['file',$root . '/stderr','wb']], $pipes, null, null, ['bypass_shell'=>true]);
        fclose($pipes[0]);
        $response = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        progress_check(proc_close($process) === 0, 'Falló la consulta del endpoint de avance.');
        [$json, $http] = explode("\nHTTP:", $response);
        $body = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        progress_check((int)$http === $expectedStatus, 'Autorización o validación incorrecta en el endpoint: ' . $role);
        if ($role === 'docente') progress_check($body['percent'] === 35, 'El docente no ve el avance de su preparación.');
        if ($role === 'admin') progress_check($body['status'] === 'pending', 'El administrador ve el avance de otra sesión.');
    }
    $apiProgress->complete();
    echo "OK: $checks comprobaciones de progreso, estimación, privacidad entre sesiones y seguimiento de procesos.\n";
} finally {
    $progress->fail();
    if (isset($failed)) $failed->fail();
    if (isset($apiProgress)) {
        $apiProgress->fail();
        $pathProperty = new ReflectionProperty($apiProgress, 'path');
        $pathProperty->setAccessible(true);
        unlink($pathProperty->getValue($apiProgress));
    }
    if (isset($sessionDirectory) && is_dir($sessionDirectory)) {
        foreach (glob($sessionDirectory . '/*') ?: [] as $file) unlink($file);
        rmdir($sessionDirectory);
    }
    foreach (glob($directory . '/*.json') ?: [] as $file) unlink($file);
    if (is_dir($directory)) rmdir($directory);
    foreach (glob($root . '/*') ?: [] as $file) unlink($file);
    rmdir($root);
}
