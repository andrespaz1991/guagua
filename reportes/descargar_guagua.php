<?php
declare(strict_types=1);
ini_set('display_errors', '0');
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin', 'docente']);
require_once __DIR__ . '/../comun/lib/GuaguaPortableBackup.php';
require_once __DIR__ . '/../comun/lib/GuaguaPortableProgress.php';

header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    guagua_error_acceso(405, 'Método no permitido.');
}
$backup = new GuaguaPortableBackup(dirname(__DIR__));
$requirements = $backup->requirements();
$errorMessage = '';
$additional = $method !== 'POST' || isset($_POST['adicionales']);
$csrf = guagua_csrf_token();
$progress = null;
if ($method === 'POST') {
    guagua_exigir_csrf();
    $downloadId = $_POST['descarga'] ?? '';
    if (!is_string($downloadId) || !preg_match('/^[a-f0-9]{32}$/D', $downloadId)) guagua_error_acceso(400, 'Solicitud de descarga no válida.');
    $owner = session_id();
    session_write_close();
    try {
        if ($requirements) throw new RuntimeException(implode(' ', $requirements));
        set_time_limit(0);
        $progress = new GuaguaPortableProgress(dirname(__DIR__), $downloadId, $owner);
        $progress->begin();
        $notify = [$progress, 'report'];
        $databases = $backup->databases($additional, $notify);
        $file = $backup->create($databases['included'], $databases['omitted'], null, $notify);
        $input = fopen($file, 'rb');
        if ($input === false) throw new RuntimeException('No se pudo abrir el paquete para descargarlo.');
        while (ob_get_level() > 0) ob_end_clean();
        ini_set('zlib.output_compression', '0');
        $progress->complete();
        setcookie('guagua_descarga', $downloadId, ['expires'=>time() + 3600, 'path'=>'/', 'secure'=>guagua_es_https(), 'samesite'=>'Lax']);
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="guagua-xampp-' . date('Ymd-His') . '.zip"');
        header('Content-Length: ' . filesize($file));
        try { fpassthru($input); } finally { fclose($input); $backup->cleanup(); }
        exit;
    } catch (Throwable $error) {
        if ($progress !== null) $progress->fail();
        $backup->cleanup();
        error_log('[Guagua portátil] ' . get_class($error) . ' (' . $error->getCode() . ')');
        http_response_code(500);
        $errorMessage = $error instanceof RuntimeException && !$error instanceof mysqli_sql_exception
            ? $error->getMessage() : 'No se pudo preparar la descarga. Revisa que MySQL esté iniciado y que la conexión de Guagua sea correcta.';
    }
} else { session_write_close(); }
$downloadId = bin2hex(random_bytes(16));
function portable_e(string $text): string { return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Descargar Guagua para XAMPP</title>
    <link rel="stylesheet" href="descargar_guagua.css?v=2">
</head>
<body>
<main class="portable">
    <header><span class="eyebrow">GUAGUA / COPIA COMPLETA</span><h1>Lleva Guagua contigo</h1><p>Descarga los archivos y las bases de datos en un solo ZIP para instalarlos en otro equipo con XAMPP.</p></header>
    <?php if ($errorMessage !== ''): ?><div class="notice error" role="alert"><?= portable_e($errorMessage) ?></div><?php endif; ?>
    <?php if ($requirements): ?><div class="notice error" role="alert"><?php foreach ($requirements as $requirement): ?><p><?= portable_e($requirement) ?></p><?php endforeach; ?></div><?php endif; ?>
    <section class="download-card" aria-labelledby="package-title">
        <div class="package-icon" aria-hidden="true">ZIP</div>
        <div><h2 id="package-title">Guagua completo para XAMPP</h2><p>Incluye la carpeta <strong>guagua</strong>, los SQL, una configuración local y una guía para restaurar la copia.</p></div>
        <form method="post" action="descargar_guagua.php" id="portable-form">
            <input type="hidden" name="csrf_token" value="<?= portable_e($csrf) ?>">
            <input type="hidden" name="descarga" value="<?= portable_e($downloadId) ?>">
            <label class="additional"><input type="checkbox" name="adicionales" value="1" <?= $additional ? 'checked' : '' ?>> <span>Incluir también las bases de EduTask, Prompt y Moodle que estén disponibles.</span></label>
            <p class="hint">La base principal siempre se incluye. La guía indica qué bases adicionales se encontraron.</p>
            <button type="submit" <?= $requirements ? 'disabled' : '' ?>>Descargar Guagua en ZIP</button>
            <div id="portable-progress" class="preparation" hidden>
                <div class="progress-heading"><span>Preparación del ZIP <span class="hint-inline">(aprox.)</span></span><strong id="portable-percent">0 %</strong></div>
                <progress id="portable-bar" max="100" value="0" aria-label="Avance de la preparación del ZIP">0 %</progress>
                <div class="progress-times"><span>Transcurrido: <strong id="portable-elapsed">0 s</strong></span><span>Tiempo restante aprox.: <strong id="portable-remaining">Calculando…</strong></span></div>
            </div>
            <p id="portable-status" class="status" role="status" aria-live="polite">La primera preparación puede tardar más. Las siguientes reutilizan los archivos intactos y exportan las bases de datos al momento.</p>
        </form>
    </section>
    <section class="restore-card" aria-labelledby="restore-title"><h2 id="restore-title">Para abrirlo en otro equipo</h2><ol>
        <li><strong>Extrae el ZIP</strong> y copia la carpeta <code>guagua</code> a <code>C:\xampp\htdocs\</code>.</li>
        <li><strong>Inicia Apache y MySQL</strong> desde el panel de XAMPP.</li>
        <li><strong>Ejecuta <code>IMPORTAR_BD.bat</code></strong> o importa los SQL de <code>base_de_datos</code> desde phpMyAdmin. Usa una instalación nueva: la importación reemplaza las tablas del mismo nombre.</li>
        <li><strong>Abre <code>http://localhost/guagua</code></strong> e ingresa con tus mismas cuentas.</li>
    </ol><p>La conexión viene preparada para MySQL en el puerto <strong>3306</strong>, usuario <strong>root</strong> y contraseña vacía. Si tu XAMPP usa otros valores, ajústalos en <code>guagua/.env</code>.</p></section>
    <aside class="scope"><p>Se conservan archivos subidos y aplicaciones locales. Se omiten temporales, herramientas de desarrollo y copias SQL antiguas. Los archivos externos, los servicios remotos y los datos guardados solo en el navegador se trasladan por separado.</p><p>El ZIP contiene datos y claves privadas de tu instalación. Guárdalo en un lugar privado.</p></aside>
    <a class="back" href="../comun/configuracion.php" target="_top">Volver a configuración</a>
</main>
<script src="descargar_guagua.js?v=2"></script>
</body>
</html>
