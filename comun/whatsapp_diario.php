<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/seguridad.php';

function diario_disponible(): bool {
    $socket = @fsockopen('127.0.0.1', 3000, $errno, $error, 1);
    if (!$socket) { return false; }
    fwrite($socket, "GET /socket.io/?EIO=4&transport=polling HTTP/1.0\r\nHost: localhost\r\nConnection: close\r\n\r\n");
    stream_set_timeout($socket, 2);
    $response = stream_get_contents($socket);
    fclose($socket);
    return strpos($response, '200 OK') !== false && strpos($response, '"sid"') !== false;
}

if (isset($_GET['estado'])) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode(['ready' => diario_disponible()]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    if (!guagua_validar_csrf()) { http_response_code(403); echo json_encode(['error' => 'Recargue el módulo para iniciar.']); exit; }
    session_write_close();
    if (!diario_disponible()) {
        if (PHP_OS_FAMILY !== 'Windows' || !is_callable('exec')) {
            http_response_code(503); echo json_encode(['error' => 'El arranque automático requiere Windows y exec habilitado en PHP.']); exit;
        }
        $script = __DIR__ . '/iniciar_whatsapp.ps1';
        exec('powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' . escapeshellarg($script) . ' 2>&1', $output, $code);
        if ($code !== 0) {
            error_log('Diario: ' . implode("\n", $output));
            http_response_code(503); echo json_encode(['error' => 'No se pudo iniciar Diario. Revise el registro de Apache y que Node.js esté instalado.']); exit;
        }
    }
    echo json_encode(['started' => true]); exit;
}
$token = guagua_csrf_token();
session_write_close();
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Iniciando Diario</title>
<style>body{margin:0;background:#f8fafc;color:#16352b;font:16px system-ui;display:grid;min-height:100vh;place-items:center}.card{padding:32px;max-width:540px;text-align:center}button{background:#087f5b;color:white;border:0;border-radius:8px;padding:12px 20px;cursor:pointer}</style></head>
<body><div class="card"><h1>Diario de WhatsApp</h1><p id="status" role="status">Iniciando el servidor…</p><button id="retry" hidden>Reintentar</button></div>
<script>
const statusText = document.getElementById('status');
const retry = document.getElementById('retry');
const endpoint = location.pathname;
async function start() {
    retry.hidden = true;
    statusText.textContent = 'Iniciando el servidor…';
    try {
        const result = await fetch(endpoint, {method:'POST', body:new URLSearchParams({csrf_token:<?= json_encode($token) ?>})});
        const data = await result.json();
        if (!result.ok || data.error) throw new Error(data.error || 'No se pudo iniciar el servidor.');
        statusText.textContent = 'Esperando a que Diario esté disponible…';
        for (let i=0; i<90; i++) {
            const response = await fetch(endpoint+'?estado=1', {cache:'no-store'});
            if (!response.ok) throw new Error('La sesión expiró. Recargue Guagua.');
            if ((await response.json()).ready) {
                const target = new URL(location.href);
                target.port = '3000'; target.pathname = '/'; target.search = '?guagua_embedded=1'; target.hash = '';
                location.replace(target.href); return;
            }
            await new Promise(resolve=>setTimeout(resolve,1000));
        }
        throw new Error('El servidor no respondió. Revise tmp/whatsapp-diario/error.log y reintente.');
    } catch(error) {statusText.textContent = error.message; retry.hidden = false;}
}
retry.addEventListener('click',start);
start();
</script></body></html>
