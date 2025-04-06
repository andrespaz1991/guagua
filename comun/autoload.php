<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
@session_start();

require_once 'config.php';
require_once 'funciones.php';

// Detectar entorno
$basePath = dirname(__FILE__) . '/Clases/';

// Autoload dinámico para clases
spl_autoload_register(function ($clase) use ($basePath) {
    $file = $basePath . ucwords($clase) . '.Class.php';
    
    if (file_exists($file)) {
        include $file;
    } else {
        die("Error: No se encontró la clase $clase en $file");
    }
});
?>
