<?php
/**
 * =================================================================
 * MÓDULO PARA UNIR ARCHIVOS .TXT - VERSIÓN OPTIMIZADA Y CON INTERFAZ
 * =================================================================
 *
 * Este módulo proporciona una interfaz web para que los usuarios puedan
 * unir múltiples archivos de texto (.txt) de un directorio en uno solo.
 *
 * Mejoras:
 * 1.  Interfaz Gráfica (UI/UX): Se ha creado una interfaz limpia y moderna
 * para que el usuario interactúe con el script.
 * 2.  Funcionalidad Interactiva: El usuario puede especificar las rutas de
 * entrada y salida a través de un formulario.
 * 3.  Opciones de Salida: Se permite al usuario elegir entre guardar el
 * archivo combinado en el servidor, descargarlo directamente o ambas.
 * 4.  Seguridad: Se han añadido validaciones para las rutas y se ha
 * mejorado el manejo de errores.
 * 5.  Código Estructurado: Se ha separado la lógica de PHP de la
 * presentación (HTML/CSS) para mayor claridad.
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Incluir archivos comunes si es necesario
// require_once __DIR__ . '/../comun/funciones.php';

// 2. PROCESAMIENTO DEL FORMULARIO (LÓGICA DE PHP)
$mensaje_resultado = '';
$error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $directorio_entrada = trim($_POST['directorio_entrada'] ?? '');
    $directorio_salida = trim($_POST['directorio_salida'] ?? '');
    // Usamos un array para las acciones, si no se selecciona ninguna, se mostrará un error.
    $acciones = $_POST['acciones'] ?? [];

    // Validaciones básicas de las rutas
    if (empty($directorio_entrada) || empty($directorio_salida)) {
        $mensaje_resultado = 'Error: Las rutas de entrada y salida son obligatorias.';
        $error = true;
    } elseif (!is_dir($directorio_entrada)) {
        $mensaje_resultado = 'Error: La ruta de entrada no es un directorio válido.';
        $error = true;
    } elseif (in_array('guardar', $acciones) && !is_dir($directorio_salida)) {
        // Intentar crear el directorio de salida solo si se va a guardar y no existe
        if (!mkdir($directorio_salida, 0777, true)) {
            $mensaje_resultado = 'Error: La ruta de salida no es válida y no se pudo crear.';
            $error = true;
        }
    } elseif (empty($acciones)) {
        $mensaje_resultado = 'Error: Debes seleccionar al menos una acción (Guardar o Descargar).';
        $error = true;
    }


    if (!$error) {
        $nombre_salida = "unido_" . date("Ymd_His") . ".txt";
        $ruta_completa_salida = rtrim($directorio_salida, '/\\') . DIRECTORY_SEPARATOR . $nombre_salida;
        $contenido_unido = "";
        $archivos_unidos = 0;

        $gestor = opendir($directorio_entrada);
        if ($gestor) {
            // Usamos scandir para poder ordenar los archivos alfabéticamente
            $archivos = scandir($directorio_entrada);
            sort($archivos, SORT_NATURAL); // Ordenamiento natural

            foreach ($archivos as $archivo) {
                if (pathinfo($archivo, PATHINFO_EXTENSION) === 'txt') {
                    $ruta_archivo = $directorio_entrada . DIRECTORY_SEPARATOR . $archivo;
                    $contenido = file_get_contents($ruta_archivo);
                    
                    $contenido_unido .= "----- Inicio de " . htmlspecialchars($archivo) . " -----\n";
                    $contenido_unido .= $contenido . "\n";
                    $contenido_unido .= "----- Fin de " . htmlspecialchars($archivo) . " -----\n\n";
                    $archivos_unidos++;
                }
            }
            closedir($gestor);
        }

        if ($archivos_unidos > 0) {
            // Acción 1: Guardar el archivo si está seleccionado
            if (in_array('guardar', $acciones)) {
                if (file_put_contents($ruta_completa_salida, $contenido_unido) !== false) {
                    $mensaje_resultado = "Éxito: Se han unido $archivos_unidos archivos. El resultado se guardó en: " . htmlspecialchars($ruta_completa_salida);
                } else {
                    $mensaje_resultado = 'Error: No se pudo escribir en el archivo de salida.';
                    $error = true;
                }
            }

            // Acción 2: Descargar el archivo si está seleccionado
            if (in_array('descargar', $acciones) && !$error) {
                // Preparamos las cabeceras para la descarga.
                header('Content-Type: text/plain; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . $nombre_salida . '"');
                header('Content-Length: ' . strlen($contenido_unido));
                ob_clean(); // Limpiar el buffer de salida para evitar contenido no deseado
                flush();
                echo $contenido_unido;
                exit; // Terminar la ejecución del script para forzar la descarga
            }

        } else {
            $mensaje_resultado = 'Información: No se encontraron archivos .txt en el directorio de entrada.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unir Archivos de Texto</title>
    <style>
        :root {
            --primary-color: #2c5282;
            --accent-color: #ed8936;
            --background-color: #f7fafc;
            --card-background: #ffffff;
            --text-color: #2d3748;
            --light-gray: #e2e8f0;
            --shadow-color: rgba(0, 0, 0, 0.08);
            --success-color: #48bb78;
            --error-color: #f56565;
            --info-color: #63b3ed;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
        }

        .main-container {
            max-width: 800px;
            margin: 40px auto;
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 8px 25px var(--shadow-color);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), #1a365d);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 2em;
        }
        .header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }

        .form-container {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #4a5568;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1em;
            box-sizing: border-box;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 82, 130, 0.2);
        }

        .action-options {
            border: 1px solid var(--light-gray);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .action-options strong {
            display: block;
            margin-bottom: 15px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .checkbox-label:not(:last-child) {
            margin-bottom: 10px;
        }
        .checkbox-label:hover {
            background-color: #edf2f7;
        }
        .checkbox-label input[type="checkbox"] {
            -webkit-appearance: none;
            appearance: none;
            background-color: #fff;
            margin: 0;
            font: inherit;
            color: currentColor;
            width: 1.15em;
            height: 1.15em;
            border: 0.15em solid currentColor;
            border-radius: 0.15em;
            transform: translateY(-0.075em);
            display: grid;
            place-content: center;
            margin-right: 0.75em;
        }
        .checkbox-label input[type="checkbox"]::before {
            content: "";
            width: 0.65em;
            height: 0.65em;
            transform: scale(0);
            transition: 120ms transform ease-in-out;
            box-shadow: inset 1em 1em var(--primary-color);
            transform-origin: bottom left;
            clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
        }
        .checkbox-label input[type="checkbox"]:checked::before {
            transform: scale(1);
        }
        
        .submit-button {
            display: block;
            width: 100%;
            padding: 15px;
            background-color: var(--accent-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.2s;
        }
        .submit-button:hover {
            background-color: #dd6b20;
            transform: translateY(-2px);
        }

        .resultado-mensaje {
            padding: 15px;
            margin: 0 30px 30px 30px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
            border: 1px solid transparent;
        }
        .resultado-mensaje.exito {
            background-color: #c6f6d5;
            color: #2f855a;
            border-color: #9ae6b4;
        }
        .resultado-mensaje.error {
            background-color: #fed7d7;
            color: #c53030;
            border-color: #feb2b2;
        }
         .resultado-mensaje.info {
            background-color: #bee3f8;
            color: #2b6cb0;
            border-color: #90cdf4;
        }
    </style>
</head>
<body>

<div class="main-container">
    <header class="header">
        <h1>Unificador de Archivos .txt</h1>
        <p>Introduce las rutas y elige una acción para combinar tus documentos.</p>
    </header>

    <form class="form-container" action="unir.php" method="POST">
        <div class="form-group">
            <label for="directorio_entrada">Ruta de la Carpeta de Entrada</label>
            <input value='C:\Windows\System32\input' type="text" id="directorio_entrada" name="directorio_entrada" placeholder="Ej: C:\MisDocumentos\Textos" required value="<?= htmlspecialchars($_POST['directorio_entrada'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="directorio_salida">Ruta de la Carpeta de Salida</label>
            <input value='D:\xampp\htdocs\guagua\Planeador\unir' type="text" id="directorio_salida" name="directorio_salida" placeholder="Ej: C:\MisDocumentos\Combinados" required value="<?= htmlspecialchars($_POST['directorio_salida'] ?? '') ?>">
        </div>

        <div class="action-options">
             <strong>Acciones a realizar:</strong>
            <label class="checkbox-label">
                <input type="checkbox" name="acciones[]" value="guardar" checked>
                Guardar en la ruta de salida
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="acciones[]" value="descargar">
                Descargar el archivo combinado
            </label>
        </div>

        <button type="submit" class="submit-button">Unir Archivos</button>
    </form>
    
    <?php if (!empty($mensaje_resultado)): ?>
        <?php 
            $clase_resultado = 'info'; // Clase por defecto para mensajes informativos
            if (strpos(strtolower($mensaje_resultado), 'éxito') !== false) {
                $clase_resultado = 'exito';
            } elseif (strpos(strtolower($mensaje_resultado), 'error') !== false) {
                $clase_resultado = 'error';
            }
        ?>
        <div class="resultado-mensaje <?= $clase_resultado ?>">
            <?= $mensaje_resultado ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>

