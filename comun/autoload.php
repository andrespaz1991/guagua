<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
@session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/funciones.php';

// Autoload dinámico para clases — compatible con Linux (case-sensitive) y Windows
spl_autoload_register(function ($clase) {
    $basePath = dirname(__DIR__) . '/clases/';

    // Intentar varias combinaciones de capitalización para compatibilidad Linux/Windows:
    // 1. Como viene (ej: COMUN, Academico)
    // 2. ucwords → primera letra de cada palabra en mayúscula (ej: Comun)
    // 3. ucfirst → solo primera letra mayúscula, resto minúsculas (ej: Comun)
    $intentos = [
        $clase,
        ucwords(strtolower($clase)),
        ucfirst(strtolower($clase)),
        strtolower($clase),
        strtoupper($clase),
    ];

    foreach ($intentos as $nombre) {
        $file = $basePath . $nombre . '.Class.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }

    die("❌ Error: No se encontró la clase $clase en $basePath");
});
?>
