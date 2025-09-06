<?php
/**
 * =================================================================
 * INICIO DE LA LÓGICA DE PHP
 * =================================================================
 *
 * Todas las operaciones de servidor, como la gestión de sesiones,
 * la conexión a la base de datos y la obtención de datos, se realizan aquí,
 * antes de enviar cualquier salida al navegador.
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN
// Iniciar la sesión una sola vez al principio del script.
// Es una buena práctica evitar el operador de supresión de errores (@).
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de errores para el entorno de desarrollo.
// En producción, esto debería registrar errores en un archivo en lugar de mostrarlos.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. INCLUSIÓN DE ARCHIVOS
// Incluir todos los archivos necesarios una sola vez.
// Usar rutas absolutas basadas en __DIR__ hace que el código sea más portable.
require_once __DIR__ . '/../comun/conexion.php'; // Se asume que $mysqli se crea aquí.
require_once __DIR__ . '/../comun/funciones.php';
require_once __DIR__ . '/../comun/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/autoload.php';

// 3. VERIFICACIÓN DE SEGURIDAD Y ENTRADA DE USUARIO
// Descomentar para requerir un rol específico para acceder a la página.
/*
if (!isset($_SESSION['rol'])) {
    header("Location: /login.php"); // Redirigir a la página de inicio de sesión
    exit();
}
*/
$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die("Error: El usuario no ha iniciado sesión.");
}

// Obtener parámetros de búsqueda de forma segura.
$parametro_busqueda = $_POST['datos'] ?? $_GET['buscar_red'] ?? '';
$campo_busqueda = $_GET['campo'] ?? 'titulo_red'; // Campo por defecto

// 4. DEFINICIÓN DE FUNCIONES
// Agrupar las funciones en un solo lugar.

/**
 * Obtiene los recursos educativos digitales para una materia específica, con opción de filtrado.
 *
 * @param mysqli $db Conexión a la base de datos.
 * @param int $id_materia ID de la materia a buscar.
 * @param string $termino_busqueda Término para filtrar los resultados.
 * @param string $campo_busqueda Campo de la base de datos en el que se buscará.
 * @return array Lista de recursos encontrados.
 */
function obtener_recursos_por_materia(mysqli $db, int $id_materia, string $termino_busqueda, string $campo_busqueda): array {
    // CORRECCIÓN FINAL: Se reemplaza JSON_CONTAINS por un LIKE para mayor compatibilidad.
    // Esto busca el ID de la materia entre comillas (ej: '%"1"%') dentro del campo materia_red.
    // Es una búsqueda más flexible que la de JSON y debería encontrar todos los recursos.
    $sql = "SELECT id_red, titulo_red, nivel_eductivo, formato, enlace, scorm, icono_red 
            FROM red 
            WHERE materia_red LIKE ?";

    $params = ['%\"' . $id_materia . '\"%'];
    $types = 's';

    if (!empty($termino_busqueda)) {
        // Mapeo seguro de campos para evitar inyección SQL en nombres de columna.
        $campos_permitidos = ['titulo_red', 'descripcion', 'palabras_clave'];
        if (in_array($campo_busqueda, $campos_permitidos)) {
            $terminos = explode(' ', $termino_busqueda);
            foreach ($terminos as $termino) {
                if (!empty($termino)) {
                    $sql .= " AND LOWER({$campo_busqueda}) LIKE ?";
                    $params[] = '%' . mb_strtolower($termino, 'UTF-8') . '%';
                    $types .= 's';
                }
            }
        }
    }

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        // En producción, registrar este error en lugar de mostrarlo.
        error_log("Error en la preparación de la consulta: " . $db->error);
        return [];
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $recursos = [];
    if ($resultado) {
        $recursos = $resultado->fetch_all(MYSQLI_ASSOC);
    }
    $stmt->close();
    
    return $recursos;
}

/**
 * Obtiene todas las materias y sus recursos asociados.
 *
 * @param mysqli $db Conexión a la base de datos.
 * @param string $parametro_busqueda Término de búsqueda.
 * @param string $campo_busqueda Campo de búsqueda.
 * @return array Lista de materias con sus recursos.
 */
function obtener_datos_red(mysqli $db, string $parametro_busqueda, string $campo_busqueda): array {
    $sql_materias = 'SELECT DISTINCT id_materia, nombre_materia FROM materia';
    $consulta_materias = $db->query($sql_materias);
    
    $datos_completos = [];
    if ($consulta_materias) {
        while ($materia = $consulta_materias->fetch_assoc()) {
            $recursos = obtener_recursos_por_materia($db, $materia['id_materia'], $parametro_busqueda, $campo_busqueda);
            // Solo añadir la materia si tiene recursos que coincidan con la búsqueda.
            if (!empty($recursos)) {
                $datos_completos[] = [
                    'id_materia' => $materia['id_materia'],
                    'nombre_materia' => $materia['nombre_materia'],
                    'recursos' => $recursos
                ];
            }
        }
    }
    return $datos_completos;
}


// 5. PROCESAMIENTO PRINCIPAL
// El script ahora llama a la función principal para obtener todos los datos necesarios.
$datos_para_vista = obtener_datos_red($mysqli, $parametro_busqueda, $campo_busqueda);
$rol_usuario = $_SESSION['rol'] ?? 'invitado';

// Se define el título de la página según el rol.
$titulo_pagina = ($rol_usuario === "estudiante") ? 'Entretenimiento' : 'Recursos Educativos Digitales';

// Finalizar la ejecución de PHP si es una llamada AJAX para buscar.
if (isset($_GET['buscar_red'])) {
    // Si fuera una llamada AJAX, aquí se imprimiría el resultado como JSON y se saldría.
    // echo json_encode($datos_para_vista);
    // exit();
}


// Buffer de salida para la plantilla
ob_start();

/**
 * =================================================================
 * INICIO DE LA VISTA (HTML, CSS, JS)
 * =================================================================
 *
 * Esta sección se encarga únicamente de mostrar los datos que
 * la lógica de PHP ya ha preparado en la variable $datos_para_vista.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo_pagina) ?></title>
    <!-- Aquí irían tus enlaces a CSS, Bootstrap, jQuery, etc. -->
    <!-- <link rel="stylesheet" href="path/to/bootstrap.css"> -->
    <!-- <link rel="stylesheet" href="path/to/jquery.contextMenu.css"> -->
    <style>
        /* 7. MEJORA DE ESTILOS UI/UX */
        :root {
            --primary-color: #4A90E2; /* Un azul moderno */
            --accent-color: #f2721d;   /* Mantenemos el naranja como acento */
            --background-color: #f4f7f9;
            --card-background: #ffffff;
            --text-color: #333333;
            --light-gray: #e0e0e0;
            --shadow-color: rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .jumbotron {
            background: linear-gradient(135deg, var(--primary-color), #357ABD);
            color: white;
            border-radius: 12px;
            padding: 30px; /* Reducido para dar espacio al search bar */
            margin-bottom: 40px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.4);
        }
        .jumbotron h1 {
            margin: 0;
            margin-bottom: 20px; /* Espacio para el search bar */
            font-size: 2.5em;
            font-weight: 700;
        }
        
        .search-wrapper {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }
        
        #recurso-search-input {
            width: 100%;
            padding: 12px 20px 12px 45px; /* Espacio para el ícono */
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 1em;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        #recurso-search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        #recurso-search-input:focus {
            outline: none;
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .search-wrapper::before {
            content: '🔍';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.7;
        }


        .btn-opciones {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
            background-color: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.5);
            color: white;
        }

        .materia-header {
            background-color: var(--card-background);
            color: var(--primary-color);
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 5px var(--shadow-color);
            font-size: 1.3em;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid var(--accent-color);
            transition: box-shadow 0.2s ease;
        }
        .materia-header:hover {
            box-shadow: 0 4px 10px var(--shadow-color);
        }
        .materia-header::after {
            content: '▼'; /* Icono para indicar que es desplegable */
            font-size: 0.8em;
            transition: transform 0.3s ease;
        }
        .materia-header.collapsed::after {
             transform: rotate(-90deg);
        }
        
        .recursos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 25px;
            overflow: hidden;
            transition: max-height 0.5s ease-in-out, opacity 0.5s ease-in-out;
            opacity: 1;
        }

        .grid-hidden {
            max-height: 0;
            opacity: 0;
            padding-bottom: 0;
        }
        
        .recurso-card {
            background-color: var(--card-background);
            border: 1px solid var(--light-gray);
            border-radius: 12px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 4px 6px var(--shadow-color);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        .recurso-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px var(--shadow-color);
        }

        .recurso-card h3 {
            font-size: 1em;
            font-weight: 600;
            margin: 0 0 15px 0;
            min-height: 45px; /* Para alinear tarjetas con títulos de 1, 2 o 3 líneas */
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recurso-card img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-top: auto; /* Empuja la imagen hacia abajo */
        }
        
        .no-recursos {
            text-align: center;
            padding: 50px;
            font-size: 1.2em;
            color: #777;
        }

        /* Estilos para Paginación */
        .pagination-controls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 25px; /* Espacio después de la rejilla de recursos */
            margin-bottom: 40px;
            padding: 10px 0;
        }

        .pagination-button {
            background-color: var(--card-background);
            border: 1px solid var(--light-gray);
            color: var(--primary-color);
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s, color 0.2s;
        }

        .pagination-button:hover:not(:disabled) {
            background-color: var(--primary-color);
            color: white;
        }

        .pagination-button.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-button:disabled {
            background-color: #f0f0f0;
            color: #b0b0b0;
            cursor: not-allowed;
            border-color: var(--light-gray);
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="jumbotron">
        <?php if ($rol_usuario === 'admin' || $rol_usuario === 'docente'): ?>
            <button type="button" class="btn btn-warning btn-opciones" onclick="window.location.href='nuevo_red.php'">Nuevo</button>
        <?php endif; ?>
        <h1><?= htmlspecialchars($titulo_pagina) ?></h1>
        <div class="search-wrapper">
             <input type="search" id="recurso-search-input" placeholder="Buscar recursos por título...">
        </div>
    </div>
    
    <div id="recursos-container">
        <?php if (empty($datos_para_vista)): ?>
            <p class="no-recursos">Aún no hay recursos o no se encontraron coincidencias.</p>
        <?php else: ?>
            <?php foreach ($datos_para_vista as $materia): ?>
                <div class="materia-wrapper">
                    <p class="materia-header" data-toggle-target="#materia-<?= $materia['id_materia'] ?>">
                        <span><?= htmlspecialchars($materia['nombre_materia']) ?></span>
                    </p>
                    
                    <div class="recursos-grid" id="materia-<?= $materia['id_materia'] ?>">
                        <?php foreach ($materia['recursos'] as $recurso): ?>
                            <?php
                                $titulo_seguro = htmlspecialchars($recurso['titulo_red'], ENT_QUOTES, 'UTF-8');
                                $id_red_seguro = htmlspecialchars($recurso['id_red'], ENT_QUOTES, 'UTF-8');
                                
                                $url_visor = sprintf(
                                    '../red/visor_red.php?red=%s&formato=%s&enlace=%s&scorm=%s',
                                    $id_red_seguro,
                                    htmlspecialchars($recurso['formato']),
                                    urlencode($recurso['enlace']),
                                    htmlspecialchars($recurso['scorm'])
                                );
                            ?>
                            <div class="recurso-card context-menu-red" 
                                 data-url="<?= $url_visor ?>" 
                                 data-id-red="<?= $id_red_seguro ?>" 
                                 data-titulo-red="<?= $titulo_seguro ?>">
                                
                                <h3 title="<?= $titulo_seguro ?>">
                                    <strong><?= htmlspecialchars(puntos_suspensivos($recurso['titulo_red'], 50)) ?></strong>
                                </h3>
                                <img src="<?= consultar_link_icono($recurso['icono_red']) ?>" alt="Icono de recurso">
                            </div>
                        <?php endforeach; ?>
                    </div>
                     <div class="no-recursos" style="display: none;">No se encontraron resultados en esta materia.</div>
                    <!-- El contenedor para la paginación se creará aquí con JS -->
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <p class="no-recursos" id="no-recursos-global" style="display: none;">No se encontraron recursos con ese término de búsqueda.</p>
    </div>
</div>

<!-- Scripts al final del body para un mejor rendimiento de carga -->
<!-- <script src="path/to/jquery.js"></script> -->
<!-- <script src="path/to/jquery.contextMenu.js"></script> -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    
    // --- LÓGICA DE PAGINACIÓN Y BÚSQUEDA ---
    const RESOURCES_PER_PAGE = 8;
    const searchInput = document.getElementById('recurso-search-input');
    const noRecursosGlobalMessage = document.getElementById('no-recursos-global');

    const setupPagination = (materiaWrapper) => {
        let currentPage = 1;
        const grid = materiaWrapper.querySelector('.recursos-grid');
        const paginationControlsContainer = materiaWrapper.querySelector('.pagination-controls') || document.createElement('div');
        if (!paginationControlsContainer.classList.contains('pagination-controls')) {
            paginationControlsContainer.className = 'pagination-controls';
            materiaWrapper.appendChild(paginationControlsContainer);
        }

        const showPage = (page) => {
            const visibleResources = Array.from(grid.querySelectorAll('.recurso-card:not([style*="display: none"])'));
            
            const startIndex = (page - 1) * RESOURCES_PER_PAGE;
            const endIndex = startIndex + RESOURCES_PER_PAGE;

            visibleResources.forEach((resource, index) => {
                resource.style.display = (index >= startIndex && index < endIndex) ? 'flex' : 'none';
            });
            updatePaginationUI();
        };

        const updatePaginationUI = () => {
             const visibleResources = Array.from(grid.querySelectorAll('.recurso-card:not([style*="display: none"])'));
            const totalResources = visibleResources.length;
            const totalPages = Math.ceil(totalResources / RESOURCES_PER_PAGE);

            paginationControlsContainer.innerHTML = ''; // Limpiar

            if (totalPages <= 1) {
                return; // No mostrar controles si no son necesarios
            }

            // Botón "Anterior"
            const prevButton = document.createElement('button');
            prevButton.textContent = 'Anterior';
            prevButton.className = 'pagination-button';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    showPage(currentPage);
                }
            });
            paginationControlsContainer.appendChild(prevButton);

            // Botones de número
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.className = 'pagination-button';
                if (i === currentPage) pageButton.classList.add('active');
                pageButton.addEventListener('click', () => {
                    currentPage = i;
                    showPage(currentPage);
                });
                paginationControlsContainer.appendChild(pageButton);
            }

            // Botón "Siguiente"
            const nextButton = document.createElement('button');
            nextButton.textContent = 'Siguiente';
            nextButton.className = 'pagination-button';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    showPage(currentPage);
                }
            });
            paginationControlsContainer.appendChild(nextButton);
        };
        
        const resetAndShowPage = (page) => {
             const allResources = grid.querySelectorAll('.recurso-card');
             allResources.forEach(r => r.style.display = 'flex'); // Restaurar la visibilidad antes de filtrar
             showPage(page);
        };
        
        // Initial setup
        resetAndShowPage(1); // Mostrar la primera página por defecto

        // Devolver la función para poder llamarla desde el filtro
        return { update: updatePaginationUI, show: showPage, reset: resetAndShowPage };
    };

    const materiaPaginationSystems = new Map();
    document.querySelectorAll('.materia-wrapper').forEach(materiaWrapper => {
        const system = setupPagination(materiaWrapper);
        materiaPaginationSystems.set(materiaWrapper, system);
    });

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        let totalVisibleResources = 0;

        document.querySelectorAll('.materia-wrapper').forEach(materiaWrapper => {
            const grid = materiaWrapper.querySelector('.recursos-grid');
            const resources = grid.querySelectorAll('.recurso-card');
            let visibleInSection = 0;
            
            resources.forEach(resource => {
                const title = resource.dataset.tituloRed.toLowerCase();
                const isMatch = title.includes(searchTerm);
                resource.style.display = isMatch ? 'flex' : 'none'; // Se oculta aquí si no coincide
                if (isMatch) {
                    visibleInSection++;
                    totalVisibleResources++;
                }
            });

            materiaWrapper.style.display = visibleInSection > 0 ? 'block' : 'none';

            // Re-paginar los elementos ahora visibles
            const paginationSystem = materiaPaginationSystems.get(materiaWrapper);
            if(paginationSystem) {
                 paginationSystem.reset(1); // Reiniciar y mostrar la página 1 con los resultados filtrados
            }
        });

        noRecursosGlobalMessage.style.display = totalVisibleResources === 0 ? 'block' : 'none';
    });


    // --- Lógica de UI existente ---
    if (typeof $.contextMenu === 'function') {
        // Menú contextual para cada recurso individual
        $.contextMenu({
            selector: '.context-menu-red',
            build: function($trigger, e) {
                // Usar .data() de jQuery es más robusto que .dataset
                const idRed = $trigger.data('id-red');
                const tituloRed = $trigger.data('titulo-red');
                
                return {
                    callback: function(key, options) {
                        switch(key) {
                            case 'descargar':
                                window.location.href = `../comun/funciones.php?ruta_red=${idRed}`;
                                break;
                            case 'modificar':
                                window.location.href = `nuevo_red.php?id_red=${idRed}`;
                                break;
                            case 'eliminar':
                                if (confirm(`¿Está seguro que desea eliminar "${tituloRed}"?`)) {
                                    window.location.href = `../comun/funciones.php?elred=${idRed}`;
                                }
                                break;
                        }
                    },
                    items: {
                        "info": { name: tituloRed, icon: "info", disabled: true },
                        "sep1": "---------",
                        "descargar": { name: "Descargar", icon: "download" },
                        "modificar": { name: "Modificar", icon: "edit" },
                        "eliminar": { name: "Eliminar", icon: "delete" },
                        "sep2": "---------",
                        "salir": { name: "Salir", icon: "quit" }
                    }
                };
            }
        });
    }

    // Lógica para mostrar/ocultar recursos de una materia
    document.querySelectorAll('.materia-header').forEach(header => {
        header.addEventListener('click', function() {
            const targetSelector = this.getAttribute('data-toggle-target');
            const gridElement = document.querySelector(targetSelector);
            const paginationElement = this.parentElement.querySelector('.pagination-controls');
            
            if (gridElement) {
                const isCollapsed = gridElement.classList.toggle('grid-hidden');
                this.classList.toggle('collapsed', isCollapsed);
                if (paginationElement) {
                    paginationElement.style.display = isCollapsed ? 'none' : 'flex';
                }
            }
        });
    });

    // Lógica para el clic en una tarjeta de recurso
    document.querySelectorAll('.recurso-card').forEach(card => {
        card.addEventListener('mousedown', function(event) {
            if (event.button === 0) { // Botón izquierdo
                const url = this.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });
});
</script>

</body>
</html>
<?php
// Recoger el contenido del buffer y enviarlo a la plantilla principal.
$contenido = ob_get_clean();
require __DIR__ . '/../comun/plantilla.php'; // Se asume que esta plantilla imprime la variable $contenido.
?>

