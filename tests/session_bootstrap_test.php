<?php
// Ejecutar con: php tests/session_bootstrap_test.php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/../comun/seguridad.php';

$sessionPath = sys_get_temp_dir() . '/guagua-session-test-' . bin2hex(random_bytes(8));
mkdir($sessionPath, 0700);
session_save_path($sessionPath);

function comprobar($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

try {
    guagua_iniciar_sesion_segura();
    $_SESSION['id_usuario'] = 'test-user';
    $csrf = guagua_csrf_token();
    $guaguaId = session_id();
    guagua_iniciar_sesion_segura();
    comprobar(session_id() === $guaguaId && guagua_csrf_token() === $csrf, 'La sesión activa debe conservarse.');
    session_write_close();

    session_name('PHPSESSID');
    session_id('');
    session_start();
    $_SESSION['id_usuario'] = 'wrong-user';
    $_SESSION['legacy_only'] = true;
    $_COOKIE['guagua_session'] = $guaguaId;
    guagua_iniciar_sesion_segura();
    comprobar(session_name() === 'guagua_session', 'Debe recuperar el nombre de sesión seguro.');
    comprobar(($_SESSION['id_usuario'] ?? '') === 'test-user', 'Debe recuperar la identidad de Guagua.');
    comprobar(!isset($_SESSION['legacy_only']), 'No debe mezclar datos de PHPSESSID.');
    comprobar(guagua_validar_csrf($csrf), 'Debe conservar el token CSRF.');
    session_write_close();

    foreach ([null, str_repeat('a', 40), ['invalid']] as $cookie) {
        session_name('PHPSESSID');
        session_id('');
        session_start();
        $_SESSION['id_usuario'] = 'legacy-admin';
        $_SESSION['rol'] = 'admin';
        $_COOKIE['guagua_session'] = $cookie;
        guagua_iniciar_sesion_segura();
        comprobar(empty($_SESSION['id_usuario']) && empty($_SESSION['rol']), 'Una cookie ausente o inválida no debe autenticar.');
        session_write_close();
    }
    echo "OK: sesión activa, recuperación, CSRF y aislamiento de sesiones anónimas.\n";
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    foreach (glob($sessionPath . '/sess_*') as $file) {
        unlink($file);
    }
    rmdir($sessionPath);
}
