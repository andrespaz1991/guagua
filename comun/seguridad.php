<?php
/** Controles de seguridad compartidos para solicitudes web y API. */

function guagua_es_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443');
}

function guagua_iniciar_sesion_segura(): void
{
    if (session_status() === PHP_SESSION_DISABLED) {
        return;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        if (session_name() === 'guagua_session') {
            return;
        }

        // Los módulos antiguos abren PHPSESSID antes de cargar config.php.
        // Cerrar esa sesión y recuperar exclusivamente la cookie de Guagua;
        // nunca copiar identidad, roles ni datos de la sesión ajena.
        session_write_close();
        $_SESSION = [];
        $cookie = $_COOKIE['guagua_session'] ?? '';
        session_id(is_string($cookie) && preg_match('/\A[a-zA-Z0-9,-]{1,256}\z/', $cookie)
            ? $cookie : '');
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('guagua_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => guagua_es_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function guagua_csrf_token(): string
{
    guagua_iniciar_sesion_segura();
    if (empty($_SESSION['guagua_csrf'])) {
        $_SESSION['guagua_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['guagua_csrf'];
}

function guagua_validar_csrf(?string $token = null): bool
{
    guagua_iniciar_sesion_segura();
    $token = $token ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '');
    return is_string($token) && isset($_SESSION['guagua_csrf'])
        && hash_equals($_SESSION['guagua_csrf'], $token);
}

function guagua_error_acceso(int $status, string $message): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Roles que pueden administrar la malla curricular (estándares, DBA y ejes).
 * Mantener esta definición centralizada evita que la página y sus API queden
 * con criterios de acceso distintos.
 */
function guagua_roles_gestion_curricular(): array
{
    return ['admin', 'directivo', 'docente'];
}

function guagua_exigir_rol(array $roles): void
{
    guagua_iniciar_sesion_segura();
    if (!empty($_SESSION['id_usuario'])) {
        require_once __DIR__ . '/database.php';
        require_once __DIR__ . '/sesion_contexto.php';
        guagua_refrescar_contexto_sesion(guagua_db_request());
    }
    if (empty($_SESSION['id_usuario'])) {
        guagua_error_acceso(401, 'Debe iniciar sesión.');
    }
    if (!in_array((string) ($_SESSION['rol'] ?? ''), $roles, true)) {
        guagua_error_acceso(403, 'No tiene permiso para esta operación.');
    }
}

function guagua_exigir_csrf(): void
{
    if (!guagua_validar_csrf()) {
        guagua_error_acceso(403, 'La solicitud no es válida. Actualice la página e intente nuevamente.');
    }
}

/** Compatibilidad de handlers antiguos: exigir token o procedencia del mismo sitio. */
function guagua_origen_coincide(string $source, string $host, bool $https): bool
{
    $expected = parse_url(($https ? 'https://' : 'http://') . $host);
    $actual = parse_url($source);
    return is_array($actual) && is_array($expected) && !isset($actual['user'])
        && strtolower($actual['scheme'] ?? '') === strtolower($expected['scheme'] ?? '')
        && strtolower($actual['host'] ?? '') === strtolower($expected['host'] ?? '')
        && (int)($actual['port'] ?? (($actual['scheme'] ?? '')==='https' ? 443 : 80)) === (int)($expected['port'] ?? ($https ? 443 : 80));
}
function guagua_exigir_origen_legado(): void
{
    if (PHP_SAPI === 'cli' || guagua_validar_csrf()) return;
    if (isset($_POST['csrf_login']) && is_string($_POST['csrf_login']) && guagua_validar_csrf($_POST['csrf_login'])) return;
    $source = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (is_string($source) && guagua_origen_coincide($source,$host,guagua_es_https())) return;
    guagua_error_acceso(403, 'La operación requiere un token válido o proceder de la misma aplicación.');
}

/** Rechazar nombres ejecutables o rutas en cargas de módulos antiguos. */
function guagua_validar_nombres_archivos(array $files): bool
{
    foreach ($files as $file) {
        if (!is_array($file) || !isset($file['name'])) continue;
        $stack = [$file['name']];
        while ($stack) {
            $name = array_pop($stack);
            if (is_array($name)) { foreach ($name as $nested) $stack[]=$nested; continue; }
            if (!is_string($name)) return false;
            if ($name === '') continue; // Campo de archivo opcional vacío.
            if (preg_match('~[\\\\/\x00\r\n:]|^\.|\.(?:php[0-9]*|phtml|phar|cgi|pl|asp|aspx|env|ini|htaccess)(?:\.|$)~i',$name)) return false;
        }
    }
    return true;
}
