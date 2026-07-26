<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/Database.php';

function runUninstallScript(mysqli $database, string $path): void
{
    $script = file_get_contents($path);
    if ($script === false || trim($script) === '') {
        throw new RuntimeException('No fue posible leer el script de desinstalación.');
    }
    if (!$database->multi_query($script)) {
        throw new RuntimeException('No fue posible eliminar las tablas: ' . $database->error);
    }
    do {
        $result = $database->store_result();
        if ($result instanceof mysqli_result) {
            $result->free();
        }
    } while ($database->more_results() && $database->next_result());

    if ($database->errno !== 0) {
        throw new RuntimeException('Error de SQL: ' . $database->error);
    }
}

$message = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (trim((string) ($_POST['confirmacion'] ?? '')) !== 'ELIMINAR EVIDENCIAS 2026') {
            throw new InvalidArgumentException('Escriba exactamente ELIMINAR EVIDENCIAS 2026 para confirmar.');
        }
        $database = EvidenceDatabase::connect();
        runUninstallScript($database, dirname(__DIR__) . '/database/uninstall.sql');
        $message = 'Las tablas evidencias_ y sus adjuntos registrados fueron eliminados.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Desinstalar Evidencias Docente</title>
    <style>body{font-family:system-ui,sans-serif;max-width:720px;margin:4rem auto;padding:0 1rem;color:#172033;background:#fff7ed}main{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 8px 30px #7c2d1216}input,button{font:inherit;padding:.75rem;border-radius:8px;border:1px solid #cbd5e1;width:100%;box-sizing:border-box}button{margin-top:1rem;background:#b91c1c;color:#fff;border:0;font-weight:700;cursor:pointer}.notice{padding:1rem;border-radius:8px}.ok{background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}code{background:#fee2e2;padding:.1rem .3rem;border-radius:4px}</style>
</head>
<body><main>
    <h1>Desinstalador del módulo Evidencias</h1>
    <p>Esta operación borra de forma irreversible las tablas <code>evidencias_*</code>, los registros de la evaluación y los metadatos de adjuntos. No modifica ninguna otra tabla de Guagua.</p>
    <?php if ($message !== null): ?><p class="notice ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <?php if ($error !== null): ?><p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post">
        <label for="confirmacion">Confirmación irreversible</label>
        <input id="confirmacion" name="confirmacion" required placeholder="ELIMINAR EVIDENCIAS 2026" autocomplete="off">
        <button type="submit">Eliminar tablas del módulo</button>
    </form>
    <p><small>Los archivos físicos existentes en <code>uploads/</code> no se borran automáticamente para evitar pérdida accidental de soportes.</small></p>
</main></body>
</html>
