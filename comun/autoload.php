<?php
// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Inicio de sesión seguro
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_httponly' => true
    ]);
}

// Carga de archivos esenciales
require_once 'config.php';
require_once 'funciones.php';

// Función de autoload mejorada
spl_autoload_register(function ($clase) {
    // Definir posibles ubicaciones de las clases
    $ubicaciones = [
        // Para entorno local (XAMPP, WAMP, etc.)
        $_SERVER['DOCUMENT_ROOT'].'/guagua/Clases/'.ucwords($clase).'.Class.php',
        
        // Para hosting (estructura común)
        $_SERVER['DOCUMENT_ROOT'].'/Clases/'.ucwords($clase).'.Class.php',
        
        // Alternativa sin document_root (para algunos hostings)
        dirname(__DIR__).'/Clases/'.ucwords($clase).'.Class.php',
        
        // Otra posible estructura
        __DIR__.'/../Clases/'.ucwords($clase).'.Class.php'
    ];
    
    // Intentar cargar desde cada ubicación posible
    foreach ($ubicaciones as $ubicacion) {
        if (file_exists($ubicacion)) {
            require_once $ubicacion;
            return;
        }
    }
    
    // Si no se encontró la clase
    throw new Exception("No se pudo cargar la clase: ".ucwords($clase));
});

// Función para verificar el entorno (opcional)
function esLocal() {
    return in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);
}
?>