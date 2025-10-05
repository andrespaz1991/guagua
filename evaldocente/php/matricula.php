<?php
// Iniciar el buffer de salida para capturar todo el HTML y pasarlo a la plantilla.
ob_start();

// Incluir la conexión a la base de datos una sola vez.
require_once("conexion.php");
// La librería de paginación también se requiere una sola vez si se va a usar.
require_once("../../comun/lib/Zebra_Pagination/Zebra_Pagination.php");

/**
 * Muestra la lista de inscripciones con búsqueda y paginación.
 *
 * @param mysqli $mysqli Objeto de conexión a la base de datos.
 * @param string $busqueda Término de búsqueda opcional.
 * @param bool $exportar_xls Si es true, genera un archivo XLS.
 */
function mostrar_lista_inscripciones($mysqli, $busqueda = "", $exportar_xls = false)
{
    if ($exportar_xls) {
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; Filename=inscripciones.xls");
    }

    // --- Configuración de Paginación ---
    $resultados_por_pagina = isset($_COOKIE['numeroresultados_inscripcion']) ? (int)$_COOKIE['numeroresultados_inscripcion'] : 10;
    $paginacion = new Zebra_Pagination();
    $paginacion->records_per_page($resultados_por_pagina);
    $paginacion->fn_js_page('buscar();');
    $paginacion->cookie_page('page_inscripcion');

    // --- Construcción de la Consulta SQL Segura ---
    $sql_base = "
        SELECT 
            i.id_inscripcion, 
            u.nombre AS estudiantenombre, 
            a.id_asignacion AS asignacionid_asignacion, 
            i.fecha_inscripcion 
        FROM `inscripcion` i
        INNER JOIN `usuario` u ON i.id_estudiante = u.id_usuario 
        INNER JOIN `asignacion` a ON i.id_asignacion = a.id_asignacion
    ";
    $sql_where = "";
    $params = [];
    $types = "";

    if (!empty($busqueda)) {
        $terminos = explode(' ', $busqueda);
        $sql_where = " WHERE";
        foreach ($terminos as $index => $termino) {
            if ($index > 0) $sql_where .= " AND";
            $sql_where .= ' CONCAT(LOWER(i.id_inscripcion), " ", LOWER(u.nombre), " ", LOWER(a.id_asignacion), " ", LOWER(i.fecha_inscripcion)) LIKE ?';
            $params[] = '%' . mb_strtolower($termino, 'UTF-8') . '%';
            $types .= 's';
        }
    }

    // Contar total de registros para la paginación
    $sql_count = "SELECT COUNT(i.id_inscripcion) FROM `inscripcion` i INNER JOIN `usuario` u ON i.id_estudiante = u.id_usuario INNER JOIN `asignacion` a ON i.id_asignacion = a.id_asignacion" . $sql_where;
    $stmt_count = $mysqli->prepare($sql_count);
    if (!empty($busqueda)) {
        $stmt_count->bind_param($types, ...$params);
    }
    $stmt_count->execute();
    $total_records = 0;
    $stmt_count->bind_result($total_records);
    $stmt_count->fetch();
    $stmt_count->close();
    $paginacion->records($total_records);

    // Consulta para obtener los registros de la página actual
    $sql_limit = " ORDER BY i.id_inscripcion DESC LIMIT ?, ?";
    $offset = ($paginacion->get_page() - 1) * $resultados_por_pagina;
    $params[] = $offset;
    $params[] = $resultados_por_pagina;
    $types .= 'ii';

    $stmt = $mysqli->prepare($sql_base . $sql_where . $sql_limit);
    if (!empty($busqueda)) {
        // Para la consulta principal, los parámetros son los de búsqueda + offset + limit
        $main_params = array_slice($params, 0, -2);
        $main_params[] = $offset;
        $main_params[] = $resultados_por_pagina;
        $stmt->bind_param($types, ...$main_params);
    } else {
        $stmt->bind_param('ii', $offset, $resultados_por_pagina);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();

    // --- Presentación de la Tabla ---
    ?>
    <div align="center">
        <table border="1" id="tbinscripcion" align="center">
            <thead>
                <tr>
                    <th>ID Inscripción</th>
                    <th>Estudiante</th>
                    <th>Asignación</th>
                    <th>Fecha Inscripción</th>
                    <?php if (!$exportar_xls): ?>
                        <th colspan="2">
                            <form method="POST" action="matricula.php">
                                <button type="submit" name="accion" value="nuevo">Nueva Inscripción</button>
                            </form>
                        </th>
                         <th>
                            <form method="GET" action="matricula.php">
                                <input type="hidden" name="exportar" value="xls">
                                <button type="submit">Exportar a XLS</button>
                            </form>
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['id_inscripcion']) ?></td>
                        <td><?= htmlspecialchars($fila['estudiantenombre']) ?></td>
                        <td><?= htmlspecialchars($fila['asignacionid_asignacion']) ?></td>
                        <td>
                            <?php 
                                $fecha = new DateTime($fila['fecha_inscripcion']);
                                // Formato de fecha más sencillo y localizado
                                setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish');
                                echo strftime("%d de %B de %Y", $fecha->getTimestamp());
                            ?>
                        </td>
                        <?php if (!$exportar_xls): ?>
                            <td>
                                <form method="POST" action="matricula.php">
                                    <input type="hidden" name="cod" value="<?= htmlspecialchars($fila['id_inscripcion']) ?>">
                                    <button type="submit" name="accion" value="modificar">Modificar</button>
                                </form>
                            </td>
                            <td>
                                <input type="image" src="../../comun/img/eliminar.png" 
                                       onclick="confirmeliminar('matricula.php', {'del': '<?= htmlspecialchars($fila['id_inscripcion']) ?>'}, '<?= htmlspecialchars($fila['id_inscripcion']) ?>');"
                                       value="Eliminar">
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="text-center">
            <?php $paginacion->render2(); ?>
        </div>
    </div>
    <?php
    $stmt->close();
}

/**
 * Muestra el formulario para crear o editar una inscripción.
 *
 * @param mysqli $mysqli Objeto de conexión a la base de datos.
 * @param int|null $id El ID de la inscripción a editar, o null para una nueva.
 */
function mostrar_formulario($mysqli, $id = null)
{
    $datos = [
        'id_inscripcion' => '',
        'id_estudiante' => '',
        'id_asignacion' => '',
        'fecha_inscripcion' => date('Y-m-d'), // Fecha actual por defecto
        'obserbaciones_inscripcion' => ''
    ];
    $titulo = "Registrar Inscripción";
    $boton_texto = "Registrar";
    $accion = "guardar_nuevo";

    if ($id !== null) {
        $stmt = $mysqli->prepare("SELECT * FROM inscripcion WHERE id_inscripcion = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $datos = $resultado->fetch_assoc();
        }
        $stmt->close();
        $titulo = "Modificar Inscripción";
        $boton_texto = "Actualizar";
        $accion = "guardar_modificacion";
    }
    ?>
    <form name="form1" method="post" action="matricula.php">
        <h1><?= $titulo ?></h1>
        <input type="hidden" name="cod" value="<?= htmlspecialchars($datos['id_inscripcion']) ?>">

        <p>
            <label for="id_estudiante_display">Estudiante:</label>
            <input type="text" autocomplete="off" list="list_id_estudiante" id="id_estudiante_display" required>
            <datalist id="list_id_estudiante">
                <?php
                $consulta_estudiantes = $mysqli->query("SELECT id_usuario, nombre FROM usuario WHERE rol = 'estudiante'"); // Asumiendo un rol
                while ($est = $consulta_estudiantes->fetch_assoc()) {
                    echo '<option data-value="' . htmlspecialchars($est['id_usuario']) . '">' . htmlspecialchars($est['nombre']) . '</option>';
                }
                ?>
            </datalist>
            <input type="hidden" name="id_estudiante" id="id_estudiante-hidden" value="<?= htmlspecialchars($datos['id_estudiante']) ?>" required>
        </p>
        
        <p>
            <label for="id_asignacion_display">Asignación:</label>
             <input type="text" autocomplete="off" list="list_id_asignacion" id="id_asignacion_display" required>
            <datalist id="list_id_asignacion">
                <?php
                $consulta_asignaciones = $mysqli->query("SELECT id_asignacion FROM asignacion"); 
                while ($asig = $consulta_asignaciones->fetch_assoc()) {
                    echo '<option data-value="' . htmlspecialchars($asig['id_asignacion']) . '">' . htmlspecialchars($asig['id_asignacion']) . '</option>';
                }
                ?>
            </datalist>
            <input type="hidden" name="id_asignacion" id="id_asignacion-hidden" value="<?= htmlspecialchars($datos['id_asignacion']) ?>" required>
        </p>

        <p>
            <label for="fecha_inscripcion">Fecha de Inscripción:</label>
            <input type="date" name="fecha_inscripcion" id="fecha_inscripcion" value="<?= htmlspecialchars($datos['fecha_inscripcion']) ?>" required>
        </p>

        <p>
            <label for="obserbaciones_inscripcion">Observaciones:</label><br>
            <textarea name="obserbaciones_inscripcion" cols="60" rows="10" id="obserbaciones_inscripcion"><?= htmlspecialchars($datos['obserbaciones_inscripcion']) ?></textarea>
        </p>

        <p>
            <button type="submit" name="accion" value="<?= $accion ?>"><?= $boton_texto ?></button>
            <a href="matricula.php">Cancelar</a>
        </p>
    </form>
    <?php
}

/**
 * Guarda una inscripción (nueva o actualizada).
 *
 * @param mysqli $mysqli Objeto de conexión a la base de datos.
 * @param array $datos Los datos del formulario POST.
 * @param bool $es_nuevo True si es un registro nuevo, false si es una actualización.
 */
function guardar_inscripcion($mysqli, $datos, $es_nuevo = true)
{
    if ($es_nuevo) {
        $sql = "INSERT INTO inscripcion (id_estudiante, id_asignacion, fecha_inscripcion, obserbaciones_inscripcion) VALUES (?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iiss', 
            $datos['id_estudiante'], 
            $datos['id_asignacion'], 
            $datos['fecha_inscripcion'], 
            $datos['obserbaciones_inscripcion']
        );
    } else {
        $sql = "UPDATE inscripcion SET id_estudiante = ?, id_asignacion = ?, fecha_inscripcion = ?, obserbaciones_inscripcion = ? WHERE id_inscripcion = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('iissi', 
            $datos['id_estudiante'], 
            $datos['id_asignacion'], 
            $datos['fecha_inscripcion'], 
            $datos['obserbaciones_inscripcion'],
            $datos['cod']
        );
    }

    if ($stmt->execute()) {
        echo $es_nuevo ? 'Registro exitoso' : 'Modificación exitosa';
    } else {
        echo 'Operación fallida: ' . $stmt->error;
    }
    $stmt->close();
    echo '<meta http-equiv="refresh" content="2; url=matricula.php" />';
}

/**
 * Elimina una inscripción.
 *
 * @param mysqli $mysqli Objeto de conexión a la base de datos.
 * @param int $id El ID de la inscripción a eliminar.
 */
function eliminar_inscripcion($mysqli, $id)
{
    $sql = "DELETE FROM inscripcion WHERE id_inscripcion = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo 'Registro eliminado con éxito.';
    } else {
        echo 'Error al eliminar. Verifique que la inscripción no esté en uso.';
    }
    $stmt->close();
    echo '<meta http-equiv="refresh" content="2; url=matricula.php" />';
}

// --- Controlador Principal ---
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';
$cod = $_POST['cod'] ?? $_GET['cod'] ?? null;
$del = $_POST['del'] ?? $_GET['del'] ?? null;
$busqueda = $_POST['datos'] ?? $_GET['datos'] ?? '';

echo '<center>';
echo '<h1>Gestión de Inscripciones</h1>';


if (isset($_GET['exportar']) && $_GET['exportar'] == 'xls') {
    mostrar_lista_inscripciones($mysqli, $busqueda, true);
    exit();
}

if ($del) {
    eliminar_inscripcion($mysqli, $del);
} elseif (isset($_GET['buscar'])) {
    mostrar_lista_inscripciones($mysqli, $busqueda);
    exit(); // AJAX call, no necesita la plantilla completa.
} else {
    switch ($accion) {
        case 'nuevo':
            mostrar_formulario($mysqli);
            break;
        case 'modificar':
            mostrar_formulario($mysqli, $cod);
            break;
        case 'guardar_nuevo':
            guardar_inscripcion($mysqli, $_POST, true);
            break;
        case 'guardar_modificacion':
            guardar_inscripcion($mysqli, $_POST, false);
            break;
        case 'listar':
        default:
            ?>
            <center>
                <b><label>Buscar: </label></b>
                <input type="search" id="buscar" onkeyup="buscar(this.value);" onchange="buscar(this.value);" style="margin: 15px;">
                <b><label>N° de Resultados:</label></b>
                <input type="number" min="1" id="numeroresultados_inscripcion" value="10" 
                       onkeyup="grabarcookie('numeroresultados_inscripcion', this.value); buscar(document.getElementById('buscar').value);" 
                       onchange="grabarcookie('numeroresultados_inscripcion', this.value); buscar(document.getElementById('buscar').value);" 
                       size="4" style="width: 50px;">
            </center>
            <span id="txtsugerencias">
                <?php mostrar_lista_inscripciones($mysqli); ?>
            </span>
            <?php
            break;
    }
}
echo '</center>';

?>
<!-- Este script es necesario para que los datalist funcionen correctamente -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var inputlists = document.querySelectorAll('input[list]');
    for (var j = 0; j < inputlists.length; j++) {
        inputlists[j].addEventListener('input', function(e) {
            var input = e.target,
                list = input.getAttribute('list'),
                options = document.querySelectorAll('#' + list + ' option'),
                hiddenInput = document.getElementById(input.id.replace('_display', '') + '-hidden'),
                inputValue = input.value;

            hiddenInput.value = ''; // Limpiar por si no hay coincidencia
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                if (option.innerText === inputValue) {
                    hiddenInput.value = option.getAttribute('data-value');
                    break;
                }
            }
        });
    }
});

// Activar el menú (si es necesario)
document.getElementById('menu_inscripcion').className += ' active';
</script>

<?php
// --- Final de la Página ---
// Captura el contenido del buffer y lo pasa a la variable $contenido para la plantilla.
$contenido = ob_get_contents();
ob_end_clean();
// Incluir la plantilla principal que mostrará el contenido.
include("../../comun/plantilla.php");
?>
