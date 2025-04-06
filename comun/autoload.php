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
require_once __DIR__.'/config.php';
require_once __DIR__.'/funciones.php';

// Función de autoload mejorada
spl_autoload_register(function ($clase) {
    // Definir posibles ubicaciones de las clases
    $ubicaciones = [
        // Para tu hosting (basado en el error)
        '/home/u417538463/domains/pcomputacional.space/public_html/Clases/'.ucfirst($clase).'.Class.php',
        '/home/u417538463/domains/pcomputacional.space/public_html/clases/'.ucfirst($clase).'.Class.php',
        
        // Para entorno local
        $_SERVER['DOCUMENT_ROOT'].'/guagua/Clases/'.ucfirst($clase).'.Class.php',
        
        // Rutas relativas
        __DIR__.'/../Clases/'.ucfirst($clase).'.Class.php',
        __DIR__.'/../../Clases/'.ucfirst($clase).'.Class.php'
    ];
    
    // Intentar cargar desde cada ubicación posible
    foreach ($ubicaciones as $ubicacion) {
        if (file_exists($ubicacion)) {
            require_once $ubicacion;
            return;
        }
        // Registrar intento (para debugging)
        error_log("Autoload probó: ".$ubicacion);
    }
    
    // Si no se encontró la clase
    throw new Exception("No se pudo cargar la clase: ".ucfirst($clase).". Verifica: ".
                      "1) Que el archivo existe, ".
                      "2) Que el nombre coincide (mayúsculas/minúsculas), ".
                      "3) Que la ruta es correcta");
});

// Función para verificar el entorno
function esLocal() {
    return in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);
}
?>