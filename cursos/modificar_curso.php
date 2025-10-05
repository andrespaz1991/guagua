<?php
/**
 * modificar_curso_mejorado.php
 *
 * Módulo completamente refactorizado con mejoras en seguridad, lógica y UI/UX.
 *
 * Mejoras Clave:
 * 1.  SEGURIDAD: Implementación de sentencias preparadas para prevenir inyección SQL.
 * 2.  LÓGICA CORREGIDA: Se actualizan los registros existentes en lugar de crear nuevos. Se usa el patrón PRG.
 * 3.  TRANSACCIONES DB: Se utilizan transacciones para garantizar la integridad de los datos.
 * 4.  UI/UX MEJORADA: Layout más limpio y mensajes de feedback integrados en la página.
 * 5.  CÓDIGO LIMPIO: Lógica de procesamiento separada de la vista y código comentado.
 */

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requerir archivos necesarios
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/conexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/funciones.php');

// Validar rol de usuario
if (!isset($_SESSION['rol']) || $_SESSION['rol'] == "estudiante") {
    die("Acceso denegado.");
}

// --- MANEJO DEL FORMULARIO (LÓGICA) ---
$mensaje = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['asignacion'])) {
    $id_asignacion = (int)$_GET['asignacion'];
    $nombre_curso = trim($_POST['curso'] ?? '');
    $id_docente = $_POST['doc'] ?? $_SESSION['id_usuario']; // Asumir docente actual si no se especifica
    $id_categoria_curso = (int)$_POST['categoria_curso'];
    $ano_lectivo = (int)$_POST['ano_lectivo'];
    $id_area = (int)$_POST['area'];
    $descripcion = trim($_POST['descripcion'] ?? '');
    $visible = in_array($_POST['visible'], ['SI', 'NO']) ? $_POST['visible'] : 'SI';
    $id_icono = trim($_POST['icon'] ?? '11');
    $id_institucion = (int)$_POST['institucion'];
    $id_materia_original = (int)$_POST['id_materia']; // Necesitamos el ID original de la materia

    $mysqli->begin_transaction();

    try {
        // 1. Actualizar la tabla `materia`
        $sql_materia = "UPDATE materia SET nombre_materia = ?, area = ? WHERE id_materia = ?";
        $stmt_materia = $mysqli->prepare($sql_materia);
        $stmt_materia->bind_param("sii", $nombre_curso, $id_area, $id_materia_original);
        $stmt_materia->execute();
        $stmt_materia->close();
        
        // 2. Manejar la subida de la portada
        $ruta_destino = null;
        if (isset($_FILES['portada']) && $_FILES['portada']['error'][0] == UPLOAD_ERR_OK) {
            $nombre_archivo = $_FILES['portada']['name'][0];
            $ext = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
            $ruta_destino = "portada/" . $id_asignacion . '.' . $ext;
            if (!move_uploaded_file($_FILES['portada']['tmp_name'][0], $ruta_destino)) {
                throw new Exception("Error al subir el archivo de portada.");
            }
        }
        
        // 3. Actualizar la tabla `asignacion`
        $sql_asignacion = "UPDATE asignacion SET id_docente = ?, id_categoria_curso = ?, ano_lectivo = ?, descripcion = ?, visible = ?, icono_asignacion = ?, institucion_educativa = ?";
        if ($ruta_destino) {
            $sql_asignacion .= ", portada_asignacion = ?";
        }
        $sql_asignacion .= " WHERE id_asignacion = ?";

        $stmt_asignacion = $mysqli->prepare($sql_asignacion);

        if ($ruta_destino) {
            $stmt_asignacion->bind_param("siisssisi", $id_docente, $id_categoria_curso, $ano_lectivo, $descripcion, $visible, $id_icono, $id_institucion, $ruta_destino, $id_asignacion);
        } else {
            $stmt_asignacion->bind_param("siissisi", $id_docente, $id_categoria_curso, $ano_lectivo, $descripcion, $visible, $id_icono, $id_institucion, $id_asignacion);
        }

        $stmt_asignacion->execute();
        $stmt_asignacion->close();

        // Si todo fue bien, confirmar la transacción
        $mysqli->commit();
        $_SESSION['mensaje'] = ['tipo' => 'success', 'texto' => 'Curso modificado correctamente.'];

    } catch (Exception $e) {
        // Si algo falló, revertir la transacción
        $mysqli->rollback();
        $_SESSION['mensaje'] = ['tipo' => 'danger', 'texto' => 'Error al modificar el curso: ' . $e->getMessage()];
    }
    
    // Redireccionar para evitar reenvío del formulario (Patrón PRG)
    header("Location: modificar_curso.php?asignacion=" . $id_asignacion);
    exit();
}

// --- CARGA DE DATOS PARA LA VISTA ---
if (!isset($_GET['asignacion']) || !is_numeric($_GET['asignacion'])) {
    die("Asignación no válida.");
}
$curso = new Curso((int)$_GET['asignacion']);
$persona = new Persona();
$academico = new Academico();
$institucion = new Institucion();

// Obtener mensaje de la sesión si existe
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    unset($_SESSION['mensaje']);
}
?>

<!-- ================================================================= -->
<!-- INICIO DE LA VISTA (HTML)                                         -->
<!-- ================================================================= -->

<div id="jumbotron" class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">MODIFICAR CURSO</h1>      
  </div>
</div>

<div class="container">    
    <div class="row">
        <div class="col-md-8 col-md-offset-2">

            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo htmlspecialchars($mensaje['tipo']); ?>" role="alert">
                <?php echo htmlspecialchars($mensaje['texto']); ?>
            </div>
            <?php endif; ?>

            <form action="modificar_curso.php?asignacion=<?php echo $curso->id_asignacion; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_materia" value="<?php echo $curso->id_materia; ?>">

                <div class="form-group">
                    <label for="curso">Nombre del Curso</label>
                    <input id="curso" type="text" class="form-control" name="curso" value="<?php echo htmlspecialchars($curso->nombre_materia); ?>" required />
                </div>

                <?php if (in_array($_SESSION['rol'], ["admin"])): ?>
                <div class="form-group">
                    <label for="answerInput">Docente</label>
                    <input class="form-control" placeholder="Seleccione el docente" value="<?php echo htmlspecialchars($curso->id_docente); ?>" autocomplete="off" list="suggestionList" id="answerInput">
                    <?php $persona->listado_rol('docente'); ?>
                    <input value="<?php echo htmlspecialchars($curso->id_docente); ?>" type="hidden" name="doc" id="answerInput-hidden">
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categoria_curso">Categoría</label>
                            <select class="form-control" name="categoria_curso" id="categoria_curso">
                                <?php $categorias = $curso->todas_categoria_curso(); ?>
                                <?php foreach ($categorias as $rowcategoria): ?>
                                <option value="<?php echo $rowcategoria['id_categoria_curso']; ?>" <?php if($rowcategoria['id_categoria_curso'] == $curso->id_categoria_curso) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($rowcategoria['nombre_categoria_curso']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="form-group">
                            <label for="ano_lectivo">Año Lectivo</label>
                            <select class="form-control" name="ano_lectivo" id="ano_lectivo">
                                <?php $info_anos_lectivos = $academico->ano_lectivo(); ?>
                                <?php foreach ($info_anos_lectivos as $info_ano_lectivo): ?>
                                <option value="<?php echo $info_ano_lectivo['id_ano_lectivo']; ?>" <?php if ($info_ano_lectivo['id_ano_lectivo'] == $curso->ano_lectivo) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($info_ano_lectivo['nombre_ano_lectivo']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="institucion">Institución</label>
                            <select class="form-control" id="institucion" name="institucion">
                                <?php $instituciones = $institucion->datos_institucion(); ?>
                                <?php foreach ($instituciones as $value): ?>
                                <option value="<?php echo $value['id_institucion_educativa']; ?>" <?php if($curso->institucion_educativa == $value['id_institucion_educativa']) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($value['nombre_institucion']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="area">Área</label>
                            <select class="form-control" name="area" id="area" required>
                                <option value="">Seleccione una opción</option>
                                <?php $info_areas = $academico->area(); ?>
                                <?php foreach($info_areas as $row_area): ?>
                                <option value="<?php echo $row_area['id_area']; ?>" <?php if ($row_area['id_area'] == $curso->area) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($row_area['nombre_area']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción del Curso</label>
                    <textarea id="descripcion" placeholder="Una breve descripción..." class="form-control" name="descripcion" rows="4"><?php echo htmlspecialchars($curso->descripcion); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visible">Visible para Estudiantes</label>
                    <select class="form-control" id="visible" name="visible">
                        <option value="SI" <?php if(strtolower($curso->visible) != "no") echo 'selected'; ?>>SI</option>
                        <option value="NO" <?php if(strtolower($curso->visible) == "no") echo 'selected'; ?>>NO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subirportada">Imagen Portada del Curso</label>
                    <input id="subirportada" name="portada[]" class="form-control" type="file" />
                    <?php if(!empty($curso->portada_asignacion)): ?>
                        <small>Portada actual: <a href="<?php echo htmlspecialchars($curso->portada_asignacion); ?>" target="_blank">Ver imagen</a></small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="icono_seleccionado_img">Icono Representativo</label><br/>
                    <input type="hidden" name="icon" id="icono_seleccionado" value="<?php echo htmlspecialchars($curso->icono_asignacion); ?>">
                    <img title="Pulse aqui para cambiar el icono" width="50px" id="icono_seleccionado_img" src="<?php echo htmlspecialchars(consultar_link_icono($curso->icono_asignacion)); ?>" >
                    <small>(Funcionalidad de cambio de icono no implementada en este módulo)</small>
                </div>

                <br>
                <button type="submit" class="btn btn-success btn-lg">Modificar Curso</button>
                <a href="cursos/" class="btn btn-default btn-lg">Cancelar</a>
                <br><br>
            </form>
        </div>
    </div>
</div>

<script>
    // Script para manejar la selección del docente con datalist
    document.querySelector('input[list]').addEventListener('input', function(e) {
        var input = e.target,
            list = input.getAttribute('list'),
            options = document.querySelectorAll('#' + list + ' option'),
            hiddenInput = document.getElementById(input.id + '-hidden'),
            inputValue = input.value;

        hiddenInput.value = inputValue;

        for(var i = 0; i < options.length; i++) {
            var option = options[i];
            if(option.innerText === inputValue) {
                hiddenInput.value = option.getAttribute('data-value');
                break;
            }
        }
    });
</script>

<?php 
$contenido = ob_get_contents();
ob_end_clean();
include("../comun/plantilla.php");
?>
