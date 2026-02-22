<?php

/**
 * Creador de Carpetas Académicas
 * * Este script PHP se conecta a la base de datos 'guagua', obtiene los grados y materias
 * para el año lectivo activo, muestra un formulario con checkboxes (seleccionados por defecto)
 * y, al enviarse el formulario, crea una estructura de carpetas: Grado -> Materia.
 * * @version 1.5 - Agrega una consulta de respaldo a la tabla 'materia' si no se encuentra en 'materia_oficial'.
 * @author Gemini
 */

// --- CONFIGURACIÓN DE LA BASE DE DATOS ---
// Modifica estos valores con los de tu servidor de base de datos.
define('DB_HOST', '127.0.0.1:7000'); // Host y puerto, según tu archivo .sql
define('DB_USER', 'root');             // Usuario de la base de datos
define('DB_PASS', '');                 // Contraseña del usuario
define('DB_NAME', 'guagua');           // Nombre de la base de datos

// --- CONFIGURACIÓN DE CARPETAS ---
// Nombre de la carpeta principal donde se crearán los directorios de los grados.
define('BASE_DIRECTORY', 'Carpetas_Academicas');

/**
 * Establece una conexión con la base de datos usando MySQLi (estilo procedural).
 * Termina la ejecución y muestra un error si la conexión falla.
 *
 * @return mysqli Un recurso de conexión MySQLi.
 */
function conectar_db()
{
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conexion) {
        die("Error de conexión: " . mysqli_connect_error());
    }
    mysqli_set_charset($conexion, "utf8");
    return $conexion;
}

/**
 * Obtiene los grados y sus respectivas materias del año lectivo activo,
 * usando 'asignacion' como enlace y priorizando 'materia_oficial'.
 *
 * @param mysqli $db El recurso de conexión a la base de datos.
 * @return array Un array asociativo con los datos estructurados por año, grado y materias.
 */
function obtener_grados_y_materias($db)
{
    $datos = [];

    // 1. Obtener el año lectivo activo.
    $sql_ano = "SELECT id_ano_lectivo, nombre_ano_lectivo FROM ano_lectivo WHERE estado = 'Activo' LIMIT 1";
    $resultado_ano = mysqli_query($db, $sql_ano);

    if ($resultado_ano && mysqli_num_rows($resultado_ano) > 0) {
        $ano_activo = mysqli_fetch_assoc($resultado_ano);
        $id_ano_activo = $ano_activo['id_ano_lectivo'];
        $nombre_ano_activo = $ano_activo['nombre_ano_lectivo'];
        $datos[$nombre_ano_activo] = [];

        // 2. Obtener los grados solicitados (6 a 11) que tienen asignaciones en el año activo.
        $grados_solicitados = ['6', '7', '8', '9', '10', '11'];
        $grados_placeholders = implode(',', array_fill(0, count($grados_solicitados), '?'));
        
        $sql_grados = "SELECT DISTINCT cc.id_categoria_curso, cc.nombre_categoria_curso 
                       FROM asignacion a 
                       JOIN categoria_curso cc ON a.id_categoria_curso = cc.id_categoria_curso 
                       WHERE a.ano_lectivo = ? AND cc.nombre_categoria_curso IN ($grados_placeholders)
                       ORDER BY CAST(cc.nombre_categoria_curso AS UNSIGNED)";

        $stmt_grados = mysqli_prepare($db, $sql_grados);
        if ($stmt_grados === false) {
            die("Error al preparar la consulta de grados: " . mysqli_error($db));
        }

        $types = 'i' . str_repeat('s', count($grados_solicitados));
        mysqli_stmt_bind_param($stmt_grados, $types, $id_ano_activo, ...$grados_solicitados);
        mysqli_stmt_execute($stmt_grados);
        $resultado_grados = mysqli_stmt_get_result($stmt_grados);

        // 3. Para cada grado, obtener sus materias.
        while ($grado = mysqli_fetch_assoc($resultado_grados)) {
            $id_grado = $grado['id_categoria_curso'];
            $nombre_grado = $grado['nombre_categoria_curso'];
            $datos[$nombre_ano_activo][$nombre_grado] = [];

            // Se utiliza COALESCE para priorizar el nombre de 'materia_oficial'.
            // Si no encuentra una coincidencia, buscará en la tabla 'materia'.
            $sql_materias = "SELECT DISTINCT COALESCE(mo.nombre_materia, m.nombre_materia) AS nombre_materia
                             FROM asignacion a 
                             LEFT JOIN materia_oficial mo ON a.id_asignatura = mo.id_materia
                             LEFT JOIN materia m ON a.id_asignatura = m.id_materia
                             WHERE a.ano_lectivo = ? AND a.id_categoria_curso = ?
                             AND COALESCE(mo.nombre_materia, m.nombre_materia) IS NOT NULL";
            
            $stmt_materias = mysqli_prepare($db, $sql_materias);
            if ($stmt_materias === false) {
                die("Error al preparar la consulta de materias: " . mysqli_error($db));
            }
            
            mysqli_stmt_bind_param($stmt_materias, "ii", $id_ano_activo, $id_grado);
            mysqli_stmt_execute($stmt_materias);
            $resultado_materias = mysqli_stmt_get_result($stmt_materias);
            
            while ($materia = mysqli_fetch_assoc($resultado_materias)) {
                $datos[$nombre_ano_activo][$nombre_grado][] = $materia['nombre_materia'];
            }
            mysqli_stmt_close($stmt_materias);
        }
        mysqli_stmt_close($stmt_grados);
    }
    return $datos;
}

/**
 * Crea las carpetas seleccionadas en el formulario.
 *
 * @param array $grados_seleccionados Array de los nombres de los grados a crear.
 * @param array $materias_seleccionadas Array anidado con las materias de cada grado a crear.
 * @param string $ano_activo El año para el cual se crean las carpetas.
 * @return array Un array de mensajes con el resultado de la operación.
 */
function crear_carpetas_seleccionadas($grados_seleccionados, $materias_seleccionadas, $ano_activo)
{
    $mensajes = [];
    $baseDir = BASE_DIRECTORY . DIRECTORY_SEPARATOR . $ano_activo;

    if (!is_dir($baseDir)) {
        if (mkdir($baseDir, 0777, true)) {
            $mensajes[] = ['tipo' => 'success', 'texto' => "Directorio principal '$baseDir' creado con éxito."];
        } else {
            $mensajes[] = ['tipo' => 'error', 'texto' => "Error al crear el directorio principal '$baseDir'."];
            return $mensajes;
        }
    }

    if (empty($grados_seleccionados)) {
        $mensajes[] = ['tipo' => 'warning', 'texto' => 'No se seleccionó ningún grado para crear.'];
        return $mensajes;
    }

    foreach ($grados_seleccionados as $nombre_grado) {
        $ruta_grado = $baseDir . DIRECTORY_SEPARATOR . $nombre_grado;
        if (!is_dir($ruta_grado)) {
            if (mkdir($ruta_grado, 0777, true)) {
                $mensajes[] = ['tipo' => 'success', 'texto' => "Carpeta para el grado '$nombre_grado' creada."];
            } else {
                $mensajes[] = ['tipo' => 'error', 'texto' => "Error al crear la carpeta para el grado '$nombre_grado'."];
                continue;
            }
        } else {
            $mensajes[] = ['tipo' => 'info', 'texto' => "La carpeta para el grado '$nombre_grado' ya existe."];
        }

        if (isset($materias_seleccionadas[$nombre_grado])) {
            foreach ($materias_seleccionadas[$nombre_grado] as $nombre_materia) {
                // Limpia el nombre de la materia para que sea un nombre de carpeta válido
                $nombre_materia_limpio = preg_replace('/[^A-Za-z0-9\-\_ ]/', '', $nombre_materia);
                $ruta_materia = $ruta_grado . DIRECTORY_SEPARATOR . $nombre_materia_limpio;

                if (!is_dir($ruta_materia)) {
                    if (mkdir($ruta_materia, 0777, true)) {
                        $mensajes[] = ['tipo' => 'success', 'texto' => "Subcarpeta para la materia '$nombre_materia_limpio' creada en Grado '$nombre_grado'."];
                    } else {
                        $mensajes[] = ['tipo' => 'error', 'texto' => "Error al crear la subcarpeta '$nombre_materia_limpio' en Grado '$nombre_grado'."];
                    }
                } else {
                    $mensajes[] = ['tipo' => 'info', 'texto' => "La subcarpeta para la materia '$nombre_materia_limpio' ya existe en Grado '$nombre_grado'."];
                }
            }
        }
    }
    return $mensajes;
}

// Lógica principal
$mensajes = [];
$conexion = conectar_db();
$datos_academicos = obtener_grados_y_materias($conexion);
$ano_activo = !empty($datos_academicos) ? key($datos_academicos) : date('Y');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $grados_a_crear = isset($_POST['grados']) ? $_POST['grados'] : [];
    $materias_a_crear = isset($_POST['materias']) ? $_POST['materias'] : [];
    $ano_post = isset($_POST['ano_activo']) ? $_POST['ano_activo'] : $ano_activo;
    $mensajes = crear_carpetas_seleccionadas($grados_a_crear, $materias_a_crear, $ano_post);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creador de Carpetas Académicas</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #0056b3;
            border-bottom: 2px solid #eef;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            background-color: #fafafa;
        }

        .grado-label {
            font-weight: bold;
            font-size: 1.2em;
            color: #343a40;
            cursor: pointer;
            display: flex;
            align-items: center;
        }

        .materias-container {
            margin-top: 10px;
            padding-left: 25px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .materia-item label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            font-size: 1.1em;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .messages {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }

        .messages .msg {
            padding: 10px;
            margin-bottom: 10px;
            border-left-width: 5px;
            border-left-style: solid;
        }

        .msg.success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .msg.error {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .msg.info {
            background-color: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }

        .msg.warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Creador de Carpetas Académicas</h1>

        <?php if (!empty($mensajes)) : ?>
            <div class="messages">
                <h2>Resultados de la operación:</h2>
                <?php foreach ($mensajes as $msg) : ?>
                    <div class="msg <?= htmlspecialchars($msg['tipo']) ?>"><?= htmlspecialchars($msg['texto']) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($datos_academicos) && !empty($ano_activo)) : ?>
            <form action="" method="post">
                <h2>Seleccione los Grados y Materias (Año Lectivo: <?= htmlspecialchars($ano_activo) ?>)</h2>
                <p>Las carpetas se crearán dentro de un directorio llamado "<strong><?= BASE_DIRECTORY . DIRECTORY_SEPARATOR . htmlspecialchars($ano_activo) ?></strong>" en la misma ubicación que este script.</p>
                <input type="hidden" name="ano_activo" value="<?= htmlspecialchars($ano_activo) ?>">

                <?php foreach ($datos_academicos[$ano_activo] as $nombre_grado => $materias) : ?>
                    <div class="form-group">
                        <label class="grado-label">
                            <input type="checkbox" name="grados[]" value="<?= htmlspecialchars($nombre_grado) ?>" checked>
                            Grado <?= htmlspecialchars($nombre_grado) ?>
                        </label>

                        <?php if (!empty($materias)) : ?>
                            <div class="materias-container">
                                <?php foreach ($materias as $nombre_materia) : ?>
                                    <div class="materia-item">
                                        <label>
                                            <input type="checkbox" name="materias[<?= htmlspecialchars($nombre_grado) ?>][]" value="<?= htmlspecialchars($nombre_materia) ?>" checked>
                                            <?= htmlspecialchars($nombre_materia) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <p>No se encontraron materias asignadas para este grado.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn">Crear Carpetas Seleccionadas</button>
            </form>
        <?php else : ?>
            <div class="messages">
                <div class="msg error">No se pudo obtener información académica de la base de datos. Verifique que haya un año lectivo activo con asignaciones de cursos y materias para los grados 6 a 11.</div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
<?php
// Cerrar la conexión
mysqli_close($conexion);
?>

