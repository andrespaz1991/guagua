<?php
/**
 * =================================================================
 * VISOR DE RECURSOS EDUCATIVOS DIGITALES (RED) - VERSIÓN OPTIMIZADA
 * =================================================================
 *
 * Mejoras realizadas:
 * 1.  Seguridad: Se usan sentencias preparadas para prevenir inyección SQL.
 * 2.  Eficiencia: Se unificaron las consultas a la base de datos y se eliminó código redundante.
 * 3.  UI/UX: Se rediseñó la interfaz con un layout moderno, adaptable (responsive) y centrado en el usuario.
 * 4.  Código Limpio: Se separó la lógica de PHP de la presentación (HTML/CSS/JS) para mayor claridad.
 * 5.  Manejo de Errores: Se añadieron validaciones para el ID del recurso.
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Es una buena práctica usar require_once para evitar inclusiones múltiples.
require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';
require_once __DIR__ . '/../comun/autoload.php';
require_once __DIR__ . '/../clases/Red.Class.php';

// 2. OBTENCIÓN Y VALIDACIÓN DE DATOS
// Validar que el ID del recurso sea un número entero para mayor seguridad.
$id_red = filter_input(INPUT_GET, 'red', FILTER_VALIDATE_INT);

if (!$id_red) {
    die("Error: Identificador de recurso no válido.");
}

// 3. LÓGICA DE BASE DE DATOS
// Incrementar el contador de visitas de forma segura con una sentencia preparada.
$sql_visita = "UPDATE `red` SET `visitas` = `visitas` + 1 WHERE `id_red` = ?";
if ($stmt_visita = $mysqli->prepare($sql_visita)) {
    $stmt_visita->bind_param("i", $id_red);
    $stmt_visita->execute();
    $stmt_visita->close();
}

// Unificar la obtención de toda la información del recurso en una sola consulta.
$sql_red = "SELECT r.*, u.nombre, u.apellido 
            FROM red r
            LEFT JOIN usuario u ON r.responsable = u.id_usuario
            WHERE r.id_red = ?";

$recurso = null;
if ($stmt_red = $mysqli->prepare($sql_red)) {
    $stmt_red->bind_param("i", $id_red);
    $stmt_red->execute();
    $resultado = $stmt_red->get_result();
    if ($resultado->num_rows === 1) {
        $recurso = $resultado->fetch_assoc();
    }
    $stmt_red->close();
}

// Si no se encontró el recurso, mostrar un mensaje y salir.
if (!$recurso) {
    die("Error: El recurso solicitado no existe.");
}

// Limpieza de datos para la vista
$recurso['nivel_eductivo_formateado'] = str_replace(['[', ']', '"'], '', $recurso['nivel_eductivo']);
$recurso['fecha_formateada'] = formatofecha2($recurso['fecha']);

// Instancia de la clase Red para usar sus métodos (si aún son necesarios)
// $miredinstancia = new Red($id_red); // Esta línea ya no es necesaria para obtener los datos básicos.

/**
 * =================================================================
 * INICIO DE LA VISTA (HTML, CSS, JS)
 * =================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visor: <?= htmlspecialchars($recurso['titulo_red']) ?></title>
    <style>
        :root {
            --primary-color: #4A90E2;
            --accent-color: #f2721d;
            --background-color: #f4f7f9;
            --card-background: #ffffff;
            --text-color: #333;
            --light-gray: #e0e0e0;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
        }

        .visor-container {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            padding: 2rem;
            max-width: 1400px;
            margin: auto;
        }

        .visor-principal {
            flex: 3; /* Ocupa 3 partes del espacio */
            min-width: 300px;
            display: flex;
            flex-direction: column;
        }
        
        .visor-header {
            margin-bottom: 1.5rem;
        }

        .btn-volver {
            display: inline-block;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            background-color: var(--light-gray);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-volver:hover {
            background-color: #ccc;
        }

        .visor-header h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 2.2rem;
            line-height: 1.2;
        }

        .visor-contenido {
            flex-grow: 1;
            position: relative;
            background-color: #000;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-color);
            overflow: hidden; /* Para que el iframe no se desborde */
        }
        
        #visor_red_iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .visor-metadata {
            flex: 1; /* Ocupa 1 parte del espacio */
            min-width: 280px;
            background-color: var(--card-background);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-color);
            align-self: flex-start; /* Se alinea al inicio */
        }
        
        .metadata-section {
            border-bottom: 1px solid var(--light-gray);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .metadata-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .metadata-section h2 {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin: 0 0 1rem 0;
        }

        .metadata-item {
            margin-bottom: 0.75rem;
        }
        .metadata-item strong {
            display: block;
            color: #555;
            margin-bottom: 0.25rem;
        }
        .metadata-item span {
            color: #333;
        }

        .btn-descargar {
            display: block;
            width: 100%;
            padding: 0.8rem;
            margin-top: 0.5rem;
            text-align: center;
            text-decoration: none;
            color: white;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.2s, transform 0.2s;
        }
        
        .btn-descargar.primario {
            background-color: var(--primary-color);
        }
        .btn-descargar.secundario {
            background-color: var(--accent-color);
        }
        .btn-descargar:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .visor-container {
                padding: 1rem;
                gap: 1.5rem;
            }
            .visor-principal { order: 2; }
            .visor-metadata { order: 1; }
            .visor-header h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

<div class="visor-container">
    
    <main class="visor-principal">
        <div class="visor-header">
            <a href="javascript:history.back()" class="btn-volver">&larr; Volver a la lista</a>
            <h1><?= htmlspecialchars($recurso['titulo_red']) ?></h1>
        </div>
        <div class="visor-contenido">
            <?php
            // Se llama a la función que renderiza el contenido del recurso.
            reproductor($recurso['formato'], $recurso['enlace'], $recurso['scorm']);
            ?>
        </div>
    </main>

    <aside class="visor-metadata">
        <div class="metadata-section">
            <h2>Detalles del Recurso</h2>
            <div class="metadata-item">
                <strong>ID:</strong>
                <span><?= htmlspecialchars($recurso['id_red']) ?></span>
            </div>
             <div class="metadata-item">
                <strong>Autor:</strong>
                <span><?= htmlspecialchars($recurso['autor'] ?: 'No especificado') ?></span>
            </div>
             <div class="metadata-item">
                <strong>Responsable:</strong>
                <span><?= htmlspecialchars($recurso['nombre'] . ' ' . $recurso['apellido']) ?></span>
            </div>
             <div class="metadata-item">
                <strong>Publicado:</strong>
                <span><?= htmlspecialchars($recurso['fecha_formateada']) ?></span>
            </div>
        </div>

        <div class="metadata-section">
            <h2>Información Académica</h2>
            <div class="metadata-item">
                <strong>Nivel Educativo:</strong>
                <span><?= htmlspecialchars($recurso['nivel_eductivo_formateado']) ?></span>
            </div>
            <div class="metadata-item">
                <strong>Palabras Clave:</strong>
                <span><?= htmlspecialchars($recurso['palabras_clave'] ?: 'No especificadas') ?></span>
            </div>
             <div class="metadata-item">
                <strong>Dificultad:</strong>
                <span><?= htmlspecialchars($recurso['dificultad']) ?> de 5</span>
            </div>
        </div>

        <div class="metadata-section">
            <h2>Interacción</h2>
            <div class="metadata-item">
                <strong>Visitas:</strong>
                <span><?= htmlspecialchars($recurso['visitas']) ?></span>
            </div>
            <div class="metadata-item">
                <strong>Valoración:</strong>
                <span id="num_fav_red">
                    <?php 
                    $array_estrellas = json_decode($recurso['cantidad_estrellas'], true); 
                    echo sumar_valores($array_estrellas);
                    ?>
                </span> estrellas
                <span class="fav_visor"><?= mis_red_favoritos($recurso['id_red'], $recurso['cantidad_estrellas']); ?></span>
            </div>
        </div>

        <div class="metadata-section">
             <h2>Acciones</h2>
            <?php if ($recurso['adjunto'] == 'no'): ?>
                <a target="_blank" href="<?= htmlspecialchars($recurso['enlace']) ?>" class="btn-descargar primario">Abrir Enlace Original</a>
            <?php else: ?>
                 <a href="../comun/funciones.php?ruta_red=<?= htmlspecialchars($recurso['id_red']) ?>" class="btn-descargar primario">Descargar</a>
            <?php endif; ?>

            <?php if ($recurso['scorm'] == 'SI'): ?>
                <a href="#" class="btn-descargar secundario">Descargar Scorm</a>
            <?php endif; ?>
        </div>

    </aside>
</div>

</body>
</html>
<?php
// Recoger el contenido del buffer y enviarlo a la plantilla principal.
$contenido = ob_get_clean();
require __DIR__ . "/../comun/plantilla.php"; // Se asume que esta plantilla imprime la variable $contenido.
?>
