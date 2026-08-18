<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();

require_once(__DIR__ . '/../comun/autoload.php');
require_once(__DIR__ . '/../comun/conexion.php');
require_once(__DIR__ . '/../comun/config.php');
require_once(__DIR__ . '/../comun/funciones.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$academico = new Academico();
$persona = new Persona();
$institucion = new Institucion($_SESSION['id_institucion'] ?? 7);

if (isset($_SESSION['rol'])) {
    $persona->validar_acudiente();
}

$_SESSION['modulo'] = 'cursos';
$_SESSION['barra_busqueda'] = 'cursos';

function mis_cursos_e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function mis_cursos_bind_params(mysqli_stmt $stmt, $types, array $params)
{
    if ($types === '' || empty($params)) {
        return;
    }

    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }

    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function mis_cursos_fetch_all(mysqli $mysqli, $sql, $types = '', array $params = [])
{
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        return ['ok' => false, 'error' => $mysqli->error, 'rows' => []];
    }

    mis_cursos_bind_params($stmt, $types, $params);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['ok' => false, 'error' => $error, 'rows' => []];
    }

    $result = $stmt->get_result();
    if ($result === false) {
        $error = $stmt->error;
        $stmt->close();
        return ['ok' => false, 'error' => $error, 'rows' => []];
    }

    $rows = [];

    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $stmt->close();
    return ['ok' => true, 'error' => '', 'rows' => $rows];
}

function mis_cursos_placeholders(array $items)
{
    return implode(',', array_fill(0, count($items), '?'));
}

function mis_cursos_obtener_anos(mysqli $mysqli, Academico $academico)
{
    $sql = "SELECT id_ano_lectivo, nombre_ano_lectivo, estado FROM ano_lectivo ORDER BY id_ano_lectivo DESC";
    $result = $mysqli->query($sql);
    $anos = [];

    if (!$result) {
        return ['ok' => false, 'error' => $mysqli->error, 'rows' => []];
    }

    while ($row = $result->fetch_assoc()) {
        $anos[] = $row;
    }

    return ['ok' => true, 'error' => '', 'rows' => $anos];
}

function mis_cursos_obtener_categorias(mysqli $mysqli)
{
    $sql = 'SELECT id_categoria_curso, nombre_categoria_curso FROM categoria_curso ORDER BY id_categoria_curso DESC';
    $result = $mysqli->query($sql);
    $categorias = [];

    if (!$result) {
        return ['ok' => false, 'error' => $mysqli->error, 'rows' => []];
    }

    while ($row = $result->fetch_assoc()) {
        $categorias[(int) $row['id_categoria_curso']] = $row['nombre_categoria_curso'];
    }

    return ['ok' => true, 'error' => '', 'rows' => $categorias];
}

function mis_cursos_obtener_iconos(mysqli $mysqli, array $cursos)
{
    $ids = [];
    foreach ($cursos as $curso) {
        $id = (int) ($curso['icono_asignacion'] ?? 0);
        if ($id > 0) {
            $ids[$id] = $id;
        }
    }

    if (empty($ids)) {
        return [];
    }

    $sql = 'SELECT id_iconos, imagen_icono FROM iconos WHERE id_iconos IN (' . mis_cursos_placeholders($ids) . ')';
    $data = mis_cursos_fetch_all($mysqli, $sql, str_repeat('i', count($ids)), array_values($ids));
    $iconos = [];

    if ($data['ok']) {
        foreach ($data['rows'] as $row) {
            $iconos[(int) $row['id_iconos']] = $row['imagen_icono'];
        }
    }

    return $iconos;
}

function mis_cursos_icono_url(array $iconos, $id_icono)
{
    $imagen = $iconos[(int) $id_icono] ?? 'folder-10.png';
    return SGA_COMUN_URL . '/img/png/' . $imagen;
}

function mis_cursos_obtener_cursos(mysqli $mysqli, array $anos, array $categorias, $busqueda = '', $campo = 'nombre_materia')
{
    if (empty($anos) || empty($categorias)) {
        return ['ok' => true, 'error' => '', 'rows' => []];
    }

    $rol = $_SESSION['rol'] ?? 'invitado';
    $id_usuario = $_SESSION['id_usuario'] ?? '';
    $id_hijo = $_SESSION['id_hijo_seleccionado'] ?? ($_SESSION['hijo'] ?? '');
    $id_institucion = $_SESSION['id_institucion'] ?? '';
    $ano_ids = array_map('intval', array_column($anos, 'id_ano_lectivo'));
    $cat_ids = array_map('intval', array_keys($categorias));

    // Una asignación sigue siendo válida aunque todavía no tenga horario. Por eso la
    // consulta no debe unir ni filtrar por la tabla horario.
    $sql = "SELECT DISTINCT
                a.id_asignacion,
                a.descripcion,
                a.visible,
                a.icono_asignacion,
                a.id_categoria_curso,
                a.ano_lectivo,
                mo.nombre_materia,
                u.nombre AS nombre_docente,
                u.apellido AS apellido_docente,
                u.foto
            FROM asignacion a
            INNER JOIN materia_oficial mo ON a.id_asignatura = mo.id_materia
            INNER JOIN usuario u ON a.id_docente = u.id_usuario";

    $types = '';
    $params = [];

    if ($rol === 'estudiante' || $rol === 'acudiente') {
        $sql .= ' INNER JOIN inscripcion i ON a.id_asignacion = i.id_asignacion';
    }

    $conditions = [];
    $conditions[] = 'a.ano_lectivo IN (' . mis_cursos_placeholders($ano_ids) . ')';
    $types .= str_repeat('i', count($ano_ids));
    $params = array_merge($params, $ano_ids);

    $conditions[] = 'a.id_categoria_curso IN (' . mis_cursos_placeholders($cat_ids) . ')';
    $types .= str_repeat('i', count($cat_ids));
    $params = array_merge($params, $cat_ids);

    if ($id_institucion !== '') {
        $conditions[] = 'a.institucion_educativa = ?';
        $types .= 'i';
        $params[] = (int) $id_institucion;
    }

    if ($rol === 'docente') {
        $conditions[] = 'a.id_docente = ?';
        $types .= 's';
        $params[] = $id_usuario;
    } elseif ($rol === 'estudiante') {
        $conditions[] = 'i.id_estudiante = ?';
        $types .= 's';
        $params[] = $id_usuario;
        $conditions[] = "LOWER(TRIM(a.visible)) = 'si'";
    } elseif ($rol === 'acudiente') {
        $conditions[] = 'i.id_estudiante = ?';
        $types .= 's';
        $params[] = $id_hijo;
        $conditions[] = "LOWER(TRIM(a.visible)) = 'si'";
    }

    $busqueda = trim((string) $busqueda);
    if ($busqueda !== '') {
        $campos_permitidos = [
            'nombre_materia' => 'mo.nombre_materia',
            'docente' => "CONCAT(u.nombre, ' ', u.apellido)",
            'descripcion' => 'a.descripcion',
        ];
        $campo_sql = $campos_permitidos[$campo] ?? $campos_permitidos['nombre_materia'];
        $conditions[] = 'LOWER(' . $campo_sql . ') LIKE ?';
        $types .= 's';
        $params[] = '%' . mb_strtolower($busqueda, 'UTF-8') . '%';
    }

    $sql .= ' WHERE ' . implode(' AND ', $conditions);
    $sql .= ' ORDER BY a.ano_lectivo DESC, a.id_categoria_curso DESC, mo.nombre_materia ASC';

    return mis_cursos_fetch_all($mysqli, $sql, $types, $params);
}

function mis_cursos_agrupar_cursos(array $cursos)
{
    $agrupados = [];
    $conteos_por_ano = [];

    foreach ($cursos as $curso) {
        $ano = (int) $curso['ano_lectivo'];
        $categoria = (int) $curso['id_categoria_curso'];
        $agrupados[$ano][$categoria][] = $curso;
        $conteos_por_ano[$ano][$categoria] = true;
    }

    foreach ($conteos_por_ano as $ano => $categorias) {
        $conteos_por_ano[$ano] = count($categorias);
    }

    return [$agrupados, $conteos_por_ano];
}

function mis_cursos_context_menu($id_asignacion, $nombre_materia, $rol)
{
    $id = (int) $id_asignacion;
    $nombre = mis_cursos_e($nombre_materia);
    $html = '<menu id="menu_curso' . $id . '" style="display:none" class="showcase">';
    $html .= '<command label="' . $nombre . '" onclick="document.location=\'' . SGA_CURSOS_URL . '/curso.php?asignacion=' . $id . '\'">';
    $html .= '<command label="Nueva planeación" onclick="document.location=\'' . SGA_URL . '/apps/PlanMind/index2.php?asignacion=' . $id . '\'">';
    $html .= '<hr>';

    if ($rol !== 'estudiante' && $rol !== 'acudiente') {
        $html .= '<command label="Nuevo RED" onclick="document.location=\'' . SGA_RED_URL . '/nuevo_red.php?asignacion=' . $id . '\'">';
        $html .= '<command label="Planeaciones" onclick="document.location=\'' . SGA_PLANEADOR_URL . '/planeador.php?idasignacion=' . $id . '\'">';
        $html .= '<command label="Nueva Actividad" onclick="document.location=\'' . SGA_CURSOS_URL . '/actividad.php?asignacion=' . $id . '\'">';
        $html .= '<command label="Nueva Edunota" onclick="document.location=\'' . SGA_CURSOS_URL . '/../cursos/edunotas.php?asignacion=' . $id . '\'">';
        $html .= '<command label="horario" onclick="document.location=\'' . SGA_CURSOS_URL . '/../asistencia/horario.php?asignacion=' . $id . '\'">';
        $html .= '<command label="asistencia" onclick="document.location=\'' . SGA_CURSOS_URL . '/../asistencia/index.php?asignacion=' . $id . '\'">';
        $html .= '<command label="Estudiantes del curso" onclick="document.location=\'' . SGA_CURSOS_URL . '/estudiante_curso.php?asignacion=' . $id . '\'">';
        $html .= '<command label="Modificar curso" onclick="document.location=\'' . SGA_CURSOS_URL . '/modificar_curso.php?asignacion=' . $id . '\'">';
        $html .= '<command label="Duplicar curso" onclick="document.location=\'' . SGA_COMUN_URL . '/funciones.rutas.php?clonar_curso=' . $id . '\'">';
    }

    $html .= '<command target="_BLANK" label="Reporte Valorativo" onclick="window.open(\'' . SGA_REPORTES_URL . '/informe_valorativo.php?asignacion=' . $id . '\',\'_blank\')">';
    $html .= '<command target="_BLANK" label="Estadisticas" onclick="window.open(\'' . SGA_REPORTES_URL . '/cursos/usuarios.php?id_asignacion=' . $id . '\',\'_blank\');">';
    $html .= '<command target="_BLANK" label="Salir" onclick="return \'context-menu-icon context-menu-icon-quit\'">';
    $html .= '</menu>';

    return $html;
}

function buscar_mis_cursos_html($mysqli_conn, $parametro_buqueda = '', $campo = 'nombre_materia')
{
    $academico = new Academico();
    $anos_data = mis_cursos_obtener_anos($mysqli_conn, $academico);
    if (!$anos_data['ok']) {
        return "<p class='text-danger'>Error al cargar los años académicos.</p>";
    }

    $categorias_data = mis_cursos_obtener_categorias($mysqli_conn);
    if (!$categorias_data['ok']) {
        return "<p class='text-danger'>Error al cargar las categorías.</p>";
    }

    $anos = $anos_data['rows'];
    $categorias = $categorias_data['rows'];
    $cursos_data = mis_cursos_obtener_cursos($mysqli_conn, $anos, $categorias, $parametro_buqueda, $campo);
    if (!$cursos_data['ok']) {
        return "<p class='text-danger'>Error al cargar los cursos.</p>";
    }

    $iconos = mis_cursos_obtener_iconos($mysqli_conn, $cursos_data['rows']);
    [$cursos_por_ano_categoria, $conteos_por_ano] = mis_cursos_agrupar_cursos($cursos_data['rows']);

    $rol = $_SESSION['rol'] ?? 'invitado';
    $cookie_col_class = (isset($_COOKIE['checked_lista_docentes']) && $_COOKIE['checked_lista_docentes'] === 'true') ? '10' : '12';
    $output = '<div class="container-fluid bg-3 text-center"><div class="row">';
    $output .= '<div class="col-md-' . $cookie_col_class . ' espacio_curso">';

    foreach ($anos as $row_ano) {
        $id_ano_lectivo = (int) $row_ano['id_ano_lectivo'];
        $nombre_ano_lectivo = mis_cursos_e($row_ano['nombre_ano_lectivo']);
        $num_cat_ano = $conteos_por_ano[$id_ano_lectivo] ?? 0;

        $output .= "<div id='estilo_ano'></div>";
        $output .= "<p id='pid_{$id_ano_lectivo}' onclick=\"mitoogle('#id_{$id_ano_lectivo}')\" style='cursor:pointer;'>";
        $output .= "{$nombre_ano_lectivo}";
        $output .= "<span id='estilo_categoria_curso'>Categorías en uso: {$num_cat_ano}</span></p>";
        $output .= "<div class='anos' id='id_{$id_ano_lectivo}'>";

        foreach ($categorias as $id_cat => $nombre_cat) {
            $cursos_categoria = $cursos_por_ano_categoria[$id_ano_lectivo][$id_cat] ?? [];
            if (empty($cursos_categoria)) {
                continue;
            }

            $id_cat_div = 'cat_' . $id_ano_lectivo . $id_cat;
            $total_categoria = count($cursos_categoria);
            $output .= '<div class="row"><div class="col-sm-11 col-sm-offset-1">';
            $output .= '<div id="separador_cursos"></div>';
            $output .= '<p onmouseup="ocultar_ano_cat(\'' . $id_cat_div . '\');" title="Total de Cursos: ' . $total_categoria . '" style="cursor:pointer;" class="Abckids">' . mis_cursos_e($nombre_cat) . '<span id="separador_cursos_encontrados"> Cursos Encontrados: ' . $total_categoria . '</span></p>';
            $output .= '</div></div><div class="cats" id="' . $id_cat_div . '"><div class="row">';

            foreach ($cursos_categoria as $rowa) {
                $id_asignacion = (int) $rowa['id_asignacion'];
                $nombre_materia = $rowa['nombre_materia'] ?? '';
                $nombre_materia_html = mis_cursos_e($nombre_materia);
                $nombre_docente = mis_cursos_e(trim(($rowa['nombre_docente'] ?? '') . ' ' . ($rowa['apellido_docente'] ?? '')));
                $descripcion = mis_cursos_e($rowa['descripcion'] ?? '');
                $icono_url = mis_cursos_e(mis_cursos_icono_url($iconos, $rowa['icono_asignacion'] ?? 0));
                $es_visible = (strtolower(trim($rowa['visible'] ?? 'si')) !== 'no');
                $visible_class = $es_visible ? '' : 'oculto';
                $link_modificar = 'modificar_curso.php?asignacion=' . $id_asignacion;

                $output .= "<div id='curso-{$id_asignacion}' contextmenu='menu_curso{$id_asignacion}' class='col-sm-2 menu_curso{$id_asignacion} droppable curso-card {$visible_class}'>";
                $output .= "<h4 class='Abckids'><strong><span title='{$nombre_materia_html}'>" . mis_cursos_e(mb_strtoupper(puntos_suspensivos($nombre_materia, 20), 'UTF-8')) . '</span></strong></h4>';
                $output .= "<h5 class='Abckids'>Docente: {$nombre_docente}</h5>";
                $output .= "<a href='" . SGA_CURSOS_URL . "/curso.php?asignacion={$id_asignacion}'>";
                $output .= "<img id='iconomateria_{$id_asignacion}' width='70%' height='70%' src='{$icono_url}' title='Descripción: {$descripcion}' class='img-responsive' style='margin-left:30px!important' alt='Imagen del curso'>";
                $output .= '</a>';

                if ($rol === 'admin' || $rol === 'docente') {
                    $output .= mis_cursos_context_menu($id_asignacion, $nombre_materia, $rol);
                    $output .= "<div class='curso-acciones'>";
                    $output .= "<a href='{$link_modificar}' class='btn btn-default btn-xs'>Modificar</a>";
                    $texto_boton = $es_visible ? 'Ocultar' : 'Mostrar';
                    $data_visible = $es_visible ? 'si' : 'no';
                    $output .= "<button class='btn btn-default btn-xs control-visibilidad-btn' data-id='{$id_asignacion}' data-visible='{$data_visible}'>{$texto_boton}</button>";
                    $output .= '</div>';
                }

                $output .= '</div>';
            }

            $output .= '</div></div>';
        }

        $output .= '</div>';
    }

    $output .= '</div></div></div>';
    return $output;
}

if (isset($_GET['buscar_mis_cursos'])) {
    $datos = $_POST['datos'] ?? ($_GET['buscar_mis_cursos'] ?? '');
    $campo = $_POST['campo'] ?? 'nombre_materia';

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
        style="background-size: contain; background-image: url('<?php echo mis_cursos_e(SGA_COMUN_SGA_DATA_BANNER . '/' . $institucion->BANNER_INSTITUCION); ?>')"
    <?php endif; ?>>
    <div class="container text-center">
        <?php if (empty($institucion->BANNER_INSTITUCION)): ?>
            <h1 class="fip">MIS CURSOS</h1>
        <?php endif; ?>

        <?php if (($_SESSION['rol'] ?? '') === 'admin' || ($_SESSION['rol'] ?? '') === 'docente'): ?>
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
    <?php echo buscar_mis_cursos_html($mysqli); ?>
</span>
<br>

<?php
$contenido = ob_get_contents();
ob_end_clean();
$contenido = $estilos_y_scripts . $contenido;
include('../comun/plantilla.php');
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

            $.ajax({
                url: 'actualizar_visibilidad.php',
                type: 'POST',
                data: JSON.stringify({
                    id_asignacion: asignacionId,
                    visible: newState
                }),
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
                        console.error('Error del servidor: ' + response.message);
                        alert('Hubo un error al actualizar el curso. Revise la consola para más detalles.');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error de AJAX: ' + textStatus, errorThrown);
                    alert('Hubo un error de comunicación con el servidor.');
                }
            });
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
                $(this).data('contextMenu', true);
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
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    });
</script>
