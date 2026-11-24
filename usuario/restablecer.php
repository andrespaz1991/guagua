<?php
require_once __DIR__ . '/../comun/seguridad.php';
guagua_iniciar_sesion_segura();
require_once __DIR__ . '/lib/password_reset.php';
$token = $_POST['token'] ?? $_GET['token'] ?? '';
$message = ''; $success = false;
if (!is_string($token)) $token = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    guagua_exigir_csrf();
    $password = $_POST['password'] ?? ''; $confirmation = $_POST['confirmation'] ?? '';
    if (!is_string($password) || !is_string($confirmation) || $password!==$confirmation || strlen($password)<8 || strlen($password)>72) {
        $message = 'Las contraseñas deben coincidir y tener entre 8 y 72 bytes.';
    } else {
        try { $success = reset_apply(guagua_db_mysqli(),$token,$password,(string)GuaguaDatabase::environment('GUAGUA_RESET_SECRET')); }
        catch (Throwable $error) { error_log('[Guagua restablecer] ' . get_class($error)); }
        $message = $success ? 'Contraseña actualizada. Ya puede iniciar sesión.' : 'El enlace no es válido o ha expirado. Solicite una nueva recuperación.';
    }
}
header('Cache-Control: no-store'); header('Referrer-Policy: no-referrer');
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Restablecer acceso · Guagua</title>
<style>body{font:16px system-ui;background:#f1f5f9;color:#0f172a;margin:0;padding:24px}main{max-width:430px;margin:8vh auto;background:white;padding:32px;border-radius:20px;box-shadow:0 12px 40px #0f172a12}label{display:block;margin:16px 0 8px}input,button{box-sizing:border-box;width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:10px;font:inherit}button{background:#2563eb;color:white;margin:20px 0;cursor:pointer}p{line-height:1.6;color:#475569}a{color:#1d4ed8}</style></head><body><main><h1>Restablecer contraseña</h1>
<?php if ($message): ?><p role="status"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
<?php if (!$success && reset_payload($token)): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(guagua_csrf_token(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="token" value="<?= htmlspecialchars($token,ENT_QUOTES,'UTF-8') ?>"><label for="password">Nueva contraseña</label><input id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required><label for="confirmation">Repita la contraseña</label><input id="confirmation" name="confirmation" type="password" minlength="8" maxlength="72" autocomplete="new-password" required><button>Guardar contraseña</button></form>
<?php elseif (!$success): ?><p>El enlace no es válido. Solicite una nueva recuperación.</p><?php endif; ?>
<a href="login.php">Iniciar sesión</a></main></body></html>
