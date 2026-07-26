<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/Database.php';
require_once dirname(__DIR__) . '/lib/EvaluationSeeder.php';

function runSqlScript(mysqli $database, string $path): void
{
    $script = file_get_contents($path);
    if ($script === false || trim($script) === '') {
        throw new RuntimeException('No fue posible leer el script SQL.');
    }
    if (!$database->multi_query($script)) {
        throw new RuntimeException('No fue posible crear las tablas: ' . $database->error);
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
        if (trim((string) ($_POST['confirmacion'] ?? '')) !== 'INSTALAR EVIDENCIAS') {
            throw new InvalidArgumentException('Escriba exactamente INSTALAR EVIDENCIAS para confirmar.');
        }
        $database = EvidenceDatabase::connect();
        runSqlScript($database, dirname(__DIR__) . '/database/schema.sql');
        $evaluationId = (new EvaluationSeeder($database))->seed2026();
        $message = 'Instalación terminada. Se creó la plantilla de evaluación 2026 (ID ' . $evaluationId . ').';
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
    <title>Instalar Evidencias Docente</title>
    <style>body{font-family:system-ui,sans-serif;max-width:720px;margin:4rem auto;padding:0 1rem;color:#172033;background:#f5f7fb}main{background:#fff;padding:2rem;border-radius:16px;box-shadow:0 8px 30px #15223b16}input,button{font:inherit;padding:.75rem;border-radius:8px;border:1px solid #cbd5e1;width:100%;box-sizing:border-box}button{margin-top:1rem;background:#155e75;color:#fff;border:0;font-weight:700;cursor:pointer}.notice{padding:1rem;border-radius:8px}.ok{background:#dcfce7;color:#166534}.error{background:#fee2e2;color:#991b1b}code{background:#e2e8f0;padding:.1rem .3rem;border-radius:4px}</style>
</head>
<body><main>
    <h1>Instalador del módulo Evidencias</h1>
    <p>Crea únicamente las tablas con prefijo <code>evidencias_</code> y carga la evaluación de Hugo Andrés Paz Burbano para 2026.</p>
    <?php if ($message !== null): ?><p class="notice ok"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p><p><a href="../index.php">Abrir el sistema</a></p><?php endif; ?>
    <?php if ($error !== null): ?><p class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
    <form method="post">
        <label for="confirmacion">Confirmación</label>
        <input id="confirmacion" name="confirmacion" required placeholder="INSTALAR EVIDENCIAS" autocomplete="off">
        <button type="submit">Crear tablas y cargar plantilla 2026</button>
    </form>
    <p><small>Por seguridad, elimine o restrinja el acceso a esta carpeta después de la instalación.</small></p>
</main></body>
</html>
