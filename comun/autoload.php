<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
@session_start();

require_once 'config.php';
require_once 'funciones.php';

// Autoload dinámico para clases
spl_autoload_register(function ($clase) {
    // Detectar la ubicación del autoload.php y subir un nivel para buscar Clases/
    $basePath = dirname(__DIR__) . '/Clases/'; // dirname(__DIR__) sube un nivel desde comun/

    $file = $basePath . ucwords($clase) . '.Class.php';

    if (file_exists($file)) {
        include $file;
    } else {
        die("❌ Error: No se encontró la clase $clase en $file");
    }
});
?>
