<?php
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
require_once __DIR__ . '/../lib/password_reset.php';
$message = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    guagua_exigir_csrf();
    $identity = $_POST['email'] ?? '';
    $message = 'Si existe una cuenta activa con correo válido, recibirá las instrucciones. Si no llega el mensaje, contacte a la administración.';
    if (is_string($identity) && strlen($identity)<=150 && time()-(int)($_SESSION['reset_requested_at'] ?? 0)>=60) {
        $_SESSION['reset_requested_at'] = time();
        try { reset_request(guagua_db_mysqli(),trim($identity)); }
        catch (Throwable $error) { error_log('[Guagua recuperación] ' . get_class($error)); }
    }
}
header('Cache-Control: no-store');
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recuperar acceso · Guagua</title>
<style>body{font:16px system-ui;background:#f1f5f9;color:#0f172a;margin:0;padding:24px}main{max-width:430px;margin:8vh auto;background:white;padding:32px;border-radius:20px;box-shadow:0 12px 40px #0f172a12}label{display:block;margin-bottom:8px}input,button{box-sizing:border-box;width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:10px;font:inherit}button{background:#2563eb;color:white;margin:18px 0;cursor:pointer}p{line-height:1.6;color:#475569}a{color:#1d4ed8}</style></head>
<body><main><h1>Recuperar acceso</h1><p>Escriba su documento o nombre de usuario. Le enviaremos un enlace con vigencia de 30 minutos.</p>
<?php if ($message): ?><p role="status"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(guagua_csrf_token(),ENT_QUOTES,'UTF-8') ?>"><label for="email">Documento o usuario</label><input id="email" name="email" maxlength="150" autocomplete="username" required><button>Solicitar recuperación</button></form>
<a href="/guagua/usuario/login.php">Volver al inicio de sesión</a></main></body></html>
