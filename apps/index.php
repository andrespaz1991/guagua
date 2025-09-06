<?php
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/guagua'.'/'."/comun/autoload.php");

// --- Conexión y Consulta a Base de Datos ---
// Configuración de la conexión a la base de datos
require '../comun/conexion.php';

// La consulta SQL para obtener las aplicaciones.
// Se recomienda tener una columna como 'display_order' para controlar el orden.
$sql = "SELECT name, icon, url FROM educational_apps ORDER BY name ASC";

// Ejecutar la consulta usando query()
$result = $mysqli->query($sql);

// Inicializar el array para guardar las aplicaciones
$educationalApps = [];
if ($result && $result->num_rows > 0) {
  // Guardar los datos de cada fila en nuestro array
  while($row = $result->fetch_assoc()) {
    $educationalApps[] = $row;
  }
}

// Cerrar la conexión a la base de datos
$mysqli->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Mundo de Aprendizaje (PHP)</title>
    

    <!-- Tailwind CSS para un diseño moderno y adaptable -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts para una tipografía amigable -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos personalizados y tipografía amigable para niños */
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f0f9ff; /* Un azul cielo muy claro */
        }
        h1 {
            font-family: 'Fredoka One', cursive;
        }
        /* Estilo para un sutil efecto de "presionado" en los íconos */
        .app-card:active {
            transform: scale(0.95);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="text-gray-800">

    <div class="container mx-auto px-4 py-8 md:py-12">
        
        <!-- Encabezado colorido y amigable -->
        <header class="text-center mb-8 md:mb-12">
            <h1 class="text-4xl md:text-6xl text-cyan-600 drop-shadow-lg">
                🚀 Mi Mundo de Aprendizaje 🚀
            </h1>
            <p class="text-lg md:text-xl text-gray-500 mt-2">
    <a type="button"  target='_blank' class="btn btn-primary" href='apps.php'>Nuevo</a>
        </p>
        </header>

        <!-- Contenedor de la cuadrícula de aplicaciones -->
        <main>
            <div id="apps-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
                
                <?php
                // Verificamos si hay aplicaciones para mostrar
                if (!empty($educationalApps)) {
                    // Iteramos sobre el array de aplicaciones y generamos el HTML para cada una
                    foreach ($educationalApps as $app) {
                        // Usamos htmlspecialchars para prevenir ataques XSS al mostrar datos
                        $appName = htmlspecialchars($app['name']);
                        $appIcon = htmlspecialchars($app['icon']);
                        $appUrl = htmlspecialchars($app['url']);

                        echo <<<HTML
                        <a href="{$appUrl}" target="_blank" rel="noopener noreferrer" class="app-card bg-white rounded-2xl p-4 shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out flex flex-col items-center justify-center text-center cursor-pointer">
                            <div class="text-6xl md:text-7xl mb-3">{$appIcon}</div>
                            <span class="font-bold text-gray-700 text-sm md:text-base">{$appName}</span>
                        </a>
                        HTML;
                    }
                } else {
                    // Mensaje por si no se encuentran aplicaciones en la base de datos
                    echo "<p class='col-span-full text-center text-gray-500'>No hay aplicaciones para mostrar en este momento.</p>";
                }
                ?>

            </div>
        </main>
        
    </div>

    <!-- Ya no se necesita el bloque <script> para renderizar, PHP lo hace en el servidor -->

</body>
</html>
<?php 

$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>

