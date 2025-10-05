<?php
/**
 * mis_cursos_actualizado.php
 *
 * Versión con AJAX corregido para enviar datos como JSON.
 *
 * Mejoras Clave:
 * 1.  ENVÍO JSON: Se utiliza JSON.stringify() para asegurar que los datos se envíen en un formato robusto.
 * 2.  CONTENTTYPE EXPLÍCITO: Se define el 'Content-Type' para que el servidor sepa cómo interpretar la petición.
 */

// --- INICIO: CONFIGURACIÓN Y LÓGICA DE PHP ---

ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/conexion.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/guagua/comun/funciones.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$academico = new Academico();
$persona = new Persona();
$institucion = new institucion(7);

$persona->validar_acudiente();

$_SESSION['modulo'] = "cursos";
$_SESSION['barra_busqueda'] = "cursos";

function buscar_mis_cursos_html($mysqli_conn, $parametro_buqueda = "", $campo = "nombre_materia") {
    $academico = new Academico();
    $output = '';

    $conteos_por_ano = [];
    $sql_conteos = "SELECT ano_lectivo, COUNT(*) as num_cat_ano FROM `seguimiento_categoria_ano` GROUP BY ano_lectivo";
    if ($result_conteos = $mysqli_conn->query($sql_conteos)) {
        while ($row = $result_conteos->fetch_assoc()) {
            $conteos_por_ano[$row['ano_lectivo']] = $row['num_cat_ano'];
        }
    }

    $sql_anos = $academico->ano_estudiante();
    $consulta_anos = $mysqli_conn->query($sql_anos);

    if (!$consulta_anos) {
        return "<p class='text-danger'>Error al cargar los años académicos.</p>";
    }

    $output .= '<div class="container-fluid bg-3 text-center"><div class="row">';
    $cookie_col_class = (isset($_COOKIE['checked_lista_docentes']) && $_COOKIE['checked_lista_docentes'] == "true") ? '10' : '12';
    $output .= '<div class="col-md-' . $cookie_col_class . ' espacio_curso">';

    while ($row_ano = $consulta_anos->fetch_assoc()) {
        $id_ano_lectivo = $row_ano['id_ano_lectivo'];
        $nombre_ano_lectivo = htmlspecialchars($row_ano['nombre_ano_lectivo'], ENT_QUOTES, 'UTF-8');
        $num_cat_ano = $conteos_por_ano[$id_ano_lectivo] ?? 0;

        $output .= "<div id='estilo_ano'></div>";
        $output .= "<p id='pid_{$id_ano_lectivo}' onclick=\"mitoogle('#id_{$id_ano_lectivo}')\" style='cursor:pointer;'>";
        $output .= "{$nombre_ano_lectivo}";
        $output .= "<span id='estilo_categoria_curso'>Categorías en uso: {$num_cat_ano}</span></p>";
        $output .= "<div class='anos' id='id_{$id_ano_lectivo}'>";

        $categorias = consultar_categoria_curso();

        foreach ($categorias as $id_cat => $nombre_cat) {
            $rol = $_SESSION['rol'] ?? 'invitado';
            $id_usuario = $_SESSION['id_usuario'] ?? null;
            
            $sql_base = "
                SELECT
                    a.id_asignacion, a.descripcion, a.visible, a.icono_asignacion,
                    mo.nombre_materia,
                    u.nombre as nombre_docente,
                    u.apellido as apellido_docente,
                    u.foto
                FROM asignacion a
                JOIN materia m ON a.id_asignatura = m.id_materia
                JOIN usuario u ON a.id_docente = u.id_usuario
                INNER JOIN materia_oficial mo ON m.id_materia = mo.id_materia
            ";
            
            $params = [];
            $types = "";
            $sql_conditions = " WHERE a.id_categoria_curso = ? AND a.ano_lectivo = ?";
            $types .= "ii";
            array_push($params, $id_cat, $id_ano_lectivo);

            if ($rol == 'admin') {
            } elseif ($rol == 'docente') {
                $sql_conditions .= " AND a.id_docente = ?";
                $types .= "s";
                array_push($params, $id_usuario);
            } else {
                $sql_base .= " JOIN inscripcion i ON a.id_asignacion = i.id_asignacion ";
                if ($rol == 'estudiante') {
                    $sql_conditions .= " AND i.id_estudiante = ?";
                    $types .= "s";
                    array_push($params, $id_usuario);
                } elseif ($rol == 'acudiente') {
                    $id_hijo = $_SESSION['id_hijo_seleccionado'] ?? '';
                    $sql_conditions .= " AND i.id_estudiante = ?";
                    $types .= "s";
                    array_push($params, $id_hijo);
                }
            }
            
            if (empty($parametro_buqueda)) {
                if ($rol == 'estudiante' || $rol == 'acudiente') {
                    $sql_conditions .= " AND a.visible = 'si'";
                }
            } else {
                $campo_busqueda = 'LOWER(mo.nombre_materia)';
                $sql_conditions .= " AND {$campo_busqueda} LIKE ?";
                $types .= "s";
                array_push($params, "%" . mb_strtolower($parametro_buqueda, 'UTF-8') . "%");
            }

            $sql = $sql_base . $sql_conditions;
            
            $stmt = $mysqli_conn->prepare($sql);
            if ($stmt) {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result_cursos = $stmt->get_result();

                if ($result_cursos && $result_cursos->num_rows > 0) {
                    $id_cat_div = 'cat_' . $id_ano_lectivo . $id_cat;
                    $output .= '<div class="row"><div class="col-sm-11 col-sm-offset-1">';
                    $output .= '<div id="separador_cursos"></div>';
                    $output .= '<p onmouseup="ocultar_ano_cat(\'' . $id_cat_div . '\');" title="Total de Cursos: ' . $result_cursos->num_rows . '" style="cursor:pointer;" class="Abckids">' . htmlspecialchars($nombre_cat, ENT_QUOTES, 'UTF-8') . '<span id="separador_cursos_encontrados"> Cursos Encontrados: ' . $result_cursos->num_rows . '</span></p>';
                    $output .= '</div></div><div class="cats" id="' . $id_cat_div . '"><div class="row">';

                    while ($rowa = $result_cursos->fetch_assoc()) {
                        $id_asignacion = htmlspecialchars($rowa['id_asignacion'], ENT_QUOTES, 'UTF-8');
                        $nombre_materia = htmlspecialchars($rowa['nombre_materia'], ENT_QUOTES, 'UTF-8');
                        $nombre_docente = htmlspecialchars($rowa['nombre_docente'] . ' ' . $rowa['apellido_docente'], ENT_QUOTES, 'UTF-8');
                        $descripcion = htmlspecialchars($rowa['descripcion'] ?? '', ENT_QUOTES, 'UTF-8');
                        $icono_url = htmlspecialchars(consultar_link_icono($rowa['icono_asignacion']), ENT_QUOTES, 'UTF-8');
                        
                        $es_visible = (strtolower(trim($rowa['visible'] ?? 'si')) !== "no");
                        $visible_class = $es_visible ? '' : 'oculto';
                        $link_modificar = "modificar_curso.php?asignacion={$id_asignacion}";
                        
                        $output .= "<div id='curso-{$id_asignacion}' contextmenu='menu_curso{$id_asignacion}' class='col-sm-2 menu_curso{$id_asignacion} droppable curso-card {$visible_class}'>";
                        
                        $output .= "<h4 class='Abckids'><strong><span title='{$nombre_materia}'>" . mb_strtoupper(puntos_suspensivos($nombre_materia, 20), 'UTF-8') . "</span></strong></h4>";
                        $output .= "<h5 class='Abckids'>Docente: {$nombre_docente}</h5>";
                        $output .= "<a href='" . SGA_CURSOS_URL . "/curso.php?asignacion={$id_asignacion}'>";
                        $output .= "<img id='iconomateria_{$id_asignacion}' width='70%' height='70%' src='{$icono_url}' title='Descripción: {$descripcion}' class='img-responsive' style='margin-left:30px!important' alt='Imagen del curso'>";
                        $output .= "</a>";
                        
                        if ($rol == "admin" || $rol == "docente") {
                            $output .= $academico->componente_context_menu($rowa['id_asignacion'], $rowa['nombre_materia']);

                            $output .= "<div class='curso-acciones'>";
                            $output .= "<a href='{$link_modificar}' class='btn btn-default btn-xs'>Modificar</a>";
                            
                            $texto_boton = $es_visible ? 'Ocultar' : 'Mostrar';
                            $output .= "<button 
                                            class='btn btn-default btn-xs control-visibilidad-btn' 
                                            data-id='{$id_asignacion}' 
                                            data-visible='" . ($es_visible ? 'si' : 'no') . "'>
                                            {$texto_boton}
                                        </button>";

                            $output .= "</div>";
                        }
                        
                        $output .= "</div>";
                    }
                    $output .= '</div></div>';
                }
                 $stmt->close();
            } else {
                 $output .= "<p class='text-danger'>Error al preparar la consulta de cursos: " . htmlspecialchars($mysqli_conn->error) . "</p>";
            }
        }
        $output .= "</div>";
    }
    $output .= '</div></div></div>';

    return $output;
}

if (isset($_GET['buscar_mis_cursos'])) {
    $datos = $_POST['datos'] ?? "";
    $campo = $_POST['campo'] ?? "nombre_materia";
    
    echo buscar_mis_cursos_html($mysqli, $datos, $campo);
    exit();
}

$anos_inactivos_json = json_encode($academico->consultar_anios());

ob_start();
?>
<style>
    .curso-card {
        position: relative;
        text-align: center;
        margin: 2% 5%;
        border: 2px solid #ccc;
        border-radius: 15px;
        transition: all 0.3s ease-in-out;
        padding: 15px 15px 10px 15px;
    }
    .curso-card.oculto {
        opacity: 0.6;
        filter: grayscale(80%);
        border: 2px dashed #e74c3c;
        background-color: #fdf5f5;
    }
    .curso-acciones {
        margin-top: 10px;
    }
    .curso-acciones .btn {
        margin: 0 3px;
    }
</style>
<?php
$estilos_y_scripts = ob_get_clean();
?>
<script>
    $(document).ready(function() {
        if (typeof ocultar_anios_no_vigentes === "function") {
            ocultar_anios_no_vigentes(<?php echo $anos_inactivos_json; ?>);
        }
    });
</script>

<div id="jumbotron" class="jumbotron" 
    <?php if (!empty($institucion->BANNER_INSTITUCION)): ?>
        style="background-size: contain; background-image: url('<?php echo htmlspecialchars(SGA_COMUN_SGA_DATA_BANNER . '/' . $institucion->BANNER_INSTITUCION, ENT_QUOTES, 'UTF-8'); ?>')"
    <?php endif; ?>>
    <div class="container text-center">
        <?php if (empty($institucion->BANNER_INSTITUCION)): ?>
            <h1 class="fip">MIS CURSOS</h1>
        <?php endif; ?>

        <?php if (($_SESSION['rol'] ?? '') == "admin" || ($_SESSION['rol'] ?? '') == "docente"): ?>
            <div class="btn-group" id="boton_opcion_curso">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Opciones <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="crear_curso.php">Nuevo Curso</a></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<span id="span_buscar_mis_cursos">
    <?php 
        echo buscar_mis_cursos_html($mysqli); 
    ?>
</span>
<br>

<?php 
$contenido = ob_get_contents();
ob_end_clean();
$contenido = $estilos_y_scripts . $contenido;
include("../comun/plantilla.php");
?>

<script>
    function inicializarControlVisibilidad() {
        var container = $('#span_buscar_mis_cursos');
        
        container.off('click', '.control-visibilidad-btn');
        
        container.on('click', '.control-visibilidad-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var button = $(this);
            var asignacionId = button.data('id');
            var currentState = button.data('visible');
            var newState = (currentState === 'si') ? 'no' : 'si';

            // --- INICIO DE LA CORRECCIÓN AJAX ---
            $.ajax({
                url: 'actualizar_visibilidad.php',
                type: 'POST',
                // 1. Convertimos el objeto de datos a un string JSON.
                data: JSON.stringify({
                    id_asignacion: asignacionId,
                    visible: newState
                }),
                // 2. Le decimos al servidor que estamos enviando JSON.
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var cursoCard = $('#curso-' + asignacionId);
                        if (newState === 'no') {
                            cursoCard.addClass('oculto');
                            button.text('Mostrar');
                            button.data('visible', 'no');
                        } else {
                            cursoCard.removeClass('oculto');
                            button.text('Ocultar');
                            button.data('visible', 'si');
                        }
                    } else {
                        // Mostramos el error del servidor en la consola para un mejor diagnóstico.
                        console.error('Error del servidor: ' + response.message);
                        alert('Hubo un error al actualizar el curso. Revise la consola para más detalles.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error de AJAX: ' + textStatus, errorThrown);
                    alert('Hubo un error de comunicación con el servidor.');
                }
            });
            // --- FIN DE LA CORRECCIÓN AJAX ---
        });
    }

    function inicializarMenusContextuales() {
        $('[contextmenu]').each(function() {
            var menuId = $(this).attr('contextmenu');
            var selector = '.' + menuId;
            
            if (!$(this).data('contextMenu')) {
                $.contextMenu({
                    selector: selector,
                    items: $.contextMenu.fromMenu($('#' + menuId))
                });
            }
        });
    }

    $(document).ready(function() {
        inicializarMenusContextuales();
        inicializarControlVisibilidad();
        
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    inicializarMenusContextuales();
                }
            });
        });

        var container = document.getElementById('span_buscar_mis_cursos');
        if(container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    });
</script>

