<?php
// Comprueba el control real en procesos aislados, incluidos los exit de denegación.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = dirname(__DIR__);
$probe = <<<'PHP'
$case = json_decode(base64_decode($argv[1]), true);
session_save_path(sys_get_temp_dir());
session_start();
$_SESSION = $case['session'];
$_SERVER['SCRIPT_FILENAME'] = $case['root'] . '/' . $case['path'];
define('SGA_SERVER', $case['root']);
register_shutdown_function(static function () {
    echo "\nSTATUS:" . (http_response_code() ?: 200);
    session_destroy();
});
require $case['root'] . '/comun/permisos.php';
echo 'ALLOWED';
PHP;
$paths = ['apps/sopas/index.php', 'sisga/api_inasistencias.php', 'apps/asistencia/index.php', 'apps/simulador_notas/index.php', 'apps/simulador_notas/p4_actual.php', 'apps/mapa_procesos/index.php', 'usuario/usuario.php'];
$count = 0;
foreach (['admin', 'directivo', 'docente', 'estudiante', 'acudiente', 'sinrol'] as $role) {
    foreach ($paths as $path) {
        $session = $role === 'sinrol' ? [] : ['id_usuario'=>'test-permisos', 'rol'=>$role];
        $input = ['root'=>$root, 'path'=>$path, 'session'=>$session];
        $process = proc_open([PHP_BINARY, '-r', $probe, base64_encode(json_encode($input))], [1=>['pipe','w'], 2=>['pipe','w']], $pipes, $root);
        if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar la comprobación de permisos.');
        $output = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $code = proc_close($process);
        $allowed = in_array($role, ['admin','directivo','docente'], true) || ($path === 'usuario/usuario.php' && $role !== 'sinrol');
        // El acceso efectivo al CRUD de usuarios tiene además guagua_exigir_rol.
        if ($code !== 0 || $error !== '' || (str_contains($output, 'ALLOWED') !== $allowed)) {
            throw new RuntimeException("Permisos incorrectos para $role en $path: $output $error");
        }
        if (!$allowed && !str_contains($output, $role === 'sinrol' ? 'STATUS:302' : 'STATUS:403')) {
            throw new RuntimeException("Respuesta de denegación incorrecta para $role en $path: $output");
        }
        $count++;
    }
}
echo "OK: $count combinaciones de rol y módulo (sopas, SISGA, inasistencias, simulador de notas, mapa de procesos y usuarios).\n";
