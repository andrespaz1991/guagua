<?php
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
$institucion = new Institucion($_SESSION['id_institucion'] ?? 0);

if (isset($_SESSION['rol'])) {
    $persona->validar_acudiente();
}

$_SESSION['modulo'] = 'cursos';
$_SESSION['barra_busqueda'] = 'mis_cursos_local';

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

function mis_cursos_obtener_anos(mysqli $mysqli)
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

function mis_cursos_obtener_cursos(mysqli $mysqli, $busqueda = '', $campo = 'nombre_materia')
{
    $rol = $_SESSION['rol'] ?? 'invitado';
    $id_usuario = $_SESSION['id_usuario'] ?? '';
    $id_hijo = $_SESSION['id_hijo_seleccionado'] ?? ($_SESSION['hijo'] ?? '');
    $id_institucion = $_SESSION['id_institucion'] ?? '';

    // Una asignación es válida aunque aún no tenga horario; por eso no se consulta esa tabla.
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
                u.foto,
                cc.nombre_categoria_curso,
                al.nombre_ano_lectivo
            FROM asignacion a
            INNER JOIN materia_oficial mo ON a.id_asignatura = mo.id_materia
            INNER JOIN usuario u ON a.id_docente = u.id_usuario
            INNER JOIN categoria_curso cc ON a.id_categoria_curso = cc.id_categoria_curso
            INNER JOIN ano_lectivo al ON a.ano_lectivo = al.id_ano_lectivo";

    $types = '';
    $params = [];

    if ($rol === 'estudiante' || $rol === 'acudiente') {
        $sql .= ' INNER JOIN inscripcion i ON a.id_asignacion = i.id_asignacion';
    }

    $conditions = ["LOWER(TRIM(al.estado)) = 'activo'"];

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

    $busqueda = mb_substr(trim((string) $busqueda), 0, 100, 'UTF-8');
    if ($busqueda !== '') {
        $campos_permitidos = [
            'nombre_materia' => 'mo.nombre_materia',
            'docente' => "CONCAT_WS(' ', u.nombre, u.apellido)",
            'nombre_docente' => "CONCAT_WS(' ', u.nombre, u.apellido)",
            'descripcion' => 'a.descripcion',
            'nombre_categoria' => 'cc.nombre_categoria_curso',
            'categoria' => 'cc.nombre_categoria_curso',
            'anio' => 'al.nombre_ano_lectivo',
            'todos' => "CONCAT_WS(' ', mo.nombre_materia, u.nombre, u.apellido, a.descripcion, cc.nombre_categoria_curso, al.nombre_ano_lectivo)",
        ];
        $campo_sql = $campos_permitidos[$campo] ?? $campos_permitidos['nombre_materia'];
        $conditions[] = 'LOWER(' . $campo_sql . ') LIKE ?';
        $types .= 's';
        $params[] = '%' . mb_strtolower($busqueda, 'UTF-8') . '%';
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
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

function mis_cursos_renderizar_tarjeta(array $curso, array $iconos, $rol)
{
    $id = (int) $curso['id_asignacion'];
    $materia = $curso['nombre_materia'] ?? '';
    $materia_html = mis_cursos_e($materia);
    $docente = mis_cursos_e(trim(($curso['nombre_docente'] ?? '') . ' ' . ($curso['apellido_docente'] ?? '')));
    $descripcion = mis_cursos_e($curso['descripcion'] ?? '');
    $categoria = mis_cursos_e($curso['nombre_categoria_curso'] ?? 'Curso académico');
    $icono = mis_cursos_e(mis_cursos_icono_url($iconos, $curso['icono_asignacion'] ?? 0));
    $visible = strtolower(trim($curso['visible'] ?? 'si')) !== 'no';
    $estado = $visible ? 'Disponible' : 'Oculto';
    $clase_estado = $visible ? '' : 'oculto';
    $url_curso = SGA_CURSOS_URL . '/curso.php?asignacion=' . $id;

    $html = "<article id='curso-{$id}' id_asignacion='{$id}' contextmenu='menu_curso{$id}' class='menu_curso{$id} droppable curso-card {$clase_estado}'>";
    $html .= "<a class='curso-card__link' href='{$url_curso}' aria-label='Abrir el curso {$materia_html}'>";
    $html .= "<div class='curso-card__meta'><span class='curso-card__category'>{$categoria}</span><span class='curso-card__status'>{$estado}</span></div>";
    $html .= "<div class='curso-card__icon'><img id='iconomateria_{$id}' src='{$icono}' title='Descripción: {$descripcion}' alt='' loading='lazy'></div>";
    $html .= "<h3 class='materia_droppable' title='{$materia_html}'>{$materia_html}</h3>";
    $html .= "<p class='curso-card__teacher'>Docente: <strong class='docente_droppable'>{$docente}</strong></p>";
    if ($descripcion !== '') {
        $html .= "<p class='curso-card__description'>{$descripcion}</p>";
    }
    $html .= "<span class='curso-card__open'>Entrar al curso <span aria-hidden='true'>&rarr;</span></span>";
    $html .= '</a>';

    if ($rol === 'admin' || $rol === 'docente') {
        $html .= mis_cursos_context_menu($id, $materia, $rol);
        $html .= "<footer class='curso-acciones'>";
        $html .= "<a href='modificar_curso.php?asignacion={$id}' class='btn btn-default btn-xs'>Editar</a>";
        $html .= "<button type='button' class='btn btn-default btn-xs control-visibilidad-btn' data-id='{$id}' data-visible='" . ($visible ? 'si' : 'no') . "'>" . ($visible ? 'Ocultar' : 'Mostrar') . '</button>';
        $html .= "<details class='mc-actions'><summary>Más opciones</summary><nav class='mc-actions__links' aria-label='Acciones del curso {$materia_html}'>";
        $acciones = [
            'Planeaciones' => SGA_PLANEADOR_URL . '/planeador.php?idasignacion=',
            'Nueva actividad' => SGA_CURSOS_URL . '/actividad.php?asignacion=',
            'Edunotas' => SGA_CURSOS_URL . '/edunotas.php?asignacion=',
            'Estudiantes' => SGA_CURSOS_URL . '/estudiante_curso.php?asignacion=',
            'Asistencia' => SGA_URL . '/asistencia/index.php?asignacion=',
            'Nuevo RED' => SGA_RED_URL . '/nuevo_red.php?asignacion=',
            'Nueva planeación' => SGA_URL . '/apps/PlanMind/index2.php?asignacion=',
            'Horario' => SGA_URL . '/asistencia/horario.php?asignacion=',
            'Reporte valorativo' => SGA_REPORTES_URL . '/informe_valorativo.php?asignacion=',
            'Estadísticas' => SGA_REPORTES_URL . '/cursos/usuarios.php?id_asignacion=',
        ];
        foreach ($acciones as $etiqueta => $ruta) {
            $html .= '<a href="' . mis_cursos_e($ruta . $id) . '">' . mis_cursos_e($etiqueta) . '</a>';
        }
        $html .= '</nav></details></footer>';
    }

    return $html . '</article>';
}

function buscar_mis_cursos_html($mysqli_conn, $parametro_buqueda = '', $campo = 'nombre_materia')
{
    $anos_data = mis_cursos_obtener_anos($mysqli_conn);
    $categorias_data = mis_cursos_obtener_categorias($mysqli_conn);
    $cursos_data = mis_cursos_obtener_cursos($mysqli_conn, $parametro_buqueda, $campo);

    if (!$anos_data['ok'] || !$categorias_data['ok'] || !$cursos_data['ok']) {
        return "<div class='mis-cursos-empty mis-cursos-error'>No fue posible cargar los cursos. Actualice la página e inténtelo nuevamente.</div>";
    }

    $anos = $anos_data['rows'];
    $categorias = $categorias_data['rows'];
    $cursos = $cursos_data['rows'];
    $iconos = mis_cursos_obtener_iconos($mysqli_conn, $cursos);
    [$cursos_por_ano_categoria, $conteos_por_ano] = mis_cursos_agrupar_cursos($cursos);
    $rol = $_SESSION['rol'] ?? 'invitado';

    if (empty($cursos)) {
        return "<div class='mis-cursos-empty'><span aria-hidden='true'>⌕</span><h3>No encontramos cursos</h3><p>Pruebe con otro término o criterio de búsqueda.</p></div>";
    }

    $total_cursos = count($cursos);
    $output = "<section class='mis-cursos-content' aria-live='polite'>";
    $output .= "<header class='mis-cursos-library'>";
    $output .= "<div class='mis-cursos-library__heading'><span class='mis-cursos-library__icon' aria-hidden='true'>▦</span><div><p>Organización académica</p><h2>Biblioteca de cursos</h2></div></div>";
    $output .= "<div class='mis-cursos-summary'><strong>{$total_cursos}</strong><span>curso" . ($total_cursos === 1 ? '' : 's') . " en el listado" . '</span></div>';
    $output .= '</header>';

    foreach ($anos as $ano) {
        $id_ano = (int) $ano['id_ano_lectivo'];
        $cantidad_categorias = $conteos_por_ano[$id_ano] ?? 0;
        if ($cantidad_categorias === 0) {
            continue;
        }

        $nombre_ano = mis_cursos_e($ano['nombre_ano_lectivo']);
        $output .= "<section class='curso-year'>";
        $output .= "<button type='button' class='curso-year__toggle' aria-expanded='true' aria-controls='id_{$id_ano}'>";
        $output .= "<span>{$nombre_ano}</span><small>{$cantidad_categorias} categoría" . ($cantidad_categorias === 1 ? '' : 's') . '</small></button>';
        $output .= "<div class='anos' id='id_{$id_ano}'>";

        foreach ($categorias as $id_categoria => $nombre_categoria) {
            $cursos_categoria = $cursos_por_ano_categoria[$id_ano][$id_categoria] ?? [];
            if (empty($cursos_categoria)) {
                continue;
            }

            $id_categoria_html = 'cat_' . $id_ano . '_' . (int) $id_categoria;
            $total = count($cursos_categoria);
            $output .= "<section class='curso-category'>";
            $output .= "<button type='button' class='curso-category__toggle' aria-expanded='true' aria-controls='{$id_categoria_html}'>";
            $output .= '<span>' . mis_cursos_e($nombre_categoria) . "</span><small>{$total} curso" . ($total === 1 ? '' : 's') . '</small></button>';
            $output .= "<div class='cats' id='{$id_categoria_html}'><div class='curso-grid'>";

            foreach ($cursos_categoria as $curso) {
                $output .= mis_cursos_renderizar_tarjeta($curso, $iconos, $rol);
            }

            $output .= '</div></div></section>';
        }

        $output .= '</div></section>';
    }

    return $output . '</section>';
}

if (isset($_GET['buscar_mis_cursos'])) {
    header('Content-Type: text/html; charset=utf-8');
    $datos = $_POST['datos'] ?? '';
    $campo = $_POST['campo'] ?? 'todos';
    $datos = is_string($datos) ? $datos : '';
    $campo = is_string($campo) ? $campo : 'todos';

    echo buscar_mis_cursos_html($mysqli, $datos, $campo);
    exit();
}

$anos_inactivos_json = json_encode($academico->consultar_anios() ?: [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

ob_start();
?>
<link rel="stylesheet" href="<?php echo mis_cursos_e(SGA_CURSOS_URL); ?>/assets/mis-cursos.css?v=<?php echo filemtime(__DIR__ . '/assets/mis-cursos.css'); ?>">
<?php
$estilos_y_scripts = ob_get_clean();
?>


<section aria-label="Mis cursos" class="mis-cursos-page" id="mis-cursos-page" data-csrf="<?php echo mis_cursos_e(guagua_csrf_token()); ?>" data-inactive-years="<?php echo mis_cursos_e($anos_inactivos_json); ?>">
<header id="jumbotron" class="jumbotron mis-cursos-hero"
    <?php if (!empty($institucion->BANNER_INSTITUCION)): ?>
        style="background-image: url('<?php echo mis_cursos_e(SGA_COMUN_SGA_DATA_BANNER . '/' . $institucion->BANNER_INSTITUCION); ?>')"
    <?php endif; ?>>
    <div class="container">
        <div class="mis-cursos-hero__content">
            <div>
                <p class="mis-cursos-hero__eyebrow">Espacio académico</p>
                <h1 class="mis-cursos-hero__title">Mis cursos</h1>
                <p class="mis-cursos-hero__subtitle">Acceda a sus asignaturas, recursos y actividades académicas desde un solo lugar.</p>
            </div>
        <?php if (($_SESSION['rol'] ?? '') === 'admin' || ($_SESSION['rol'] ?? '') === 'docente'): ?>
            <div class="mis-cursos-hero__action" id="boton_opcion_curso">
                <a href="crear_curso.php" class="btn btn-default"><span aria-hidden="true">+</span> Crear nuevo curso</a>
            </div>
        <?php endif; ?>
        </div>
    </div>
</header>

<form class="mc-toolbar" id="mc-search-form" role="search">
    <label class="mc-search" for="mc-search"><span aria-hidden="true">⌕</span><span class="mc-sr">Buscar cursos</span><input id="mc-search" type="search" placeholder="Buscar una asignatura, docente o categoría…" autocomplete="off" maxlength="100"></label>
    <label class="mc-sr" for="mc-field">Buscar por</label>
    <select id="mc-field"><option value="todos">Todos los campos</option><option value="nombre_materia">Asignatura</option><option value="nombre_docente">Docente</option><option value="nombre_categoria">Categoría</option><option value="anio">Año lectivo</option></select>
    <button class="mc-reset" type="button" id="mc-reset" title="Limpiar búsqueda">Limpiar</button>
    <div class="mc-views" role="group" aria-label="Vista de cursos"><button type="button" data-view="grid" aria-label="Vista de cuadrícula" aria-pressed="true">▦</button><button type="button" data-view="list" aria-label="Vista de lista" aria-pressed="false">☰</button></div>
</form>
<div class="mc-feedback" role="status" id="mc-feedback"></div>
<div id="span_buscar_mis_cursos" aria-live="polite" aria-busy="false">
    <?php echo buscar_mis_cursos_html($mysqli); ?>
</div>
<script defer src="<?php echo mis_cursos_e(SGA_CURSOS_URL); ?>/assets/mis-cursos.js?v=<?php echo filemtime(__DIR__ . '/assets/mis-cursos.js'); ?>"></script>
</section>

<?php
$contenido = ob_get_contents();
ob_end_clean();
$contenido = $estilos_y_scripts . $contenido;
include('../comun/plantilla.php');
?>
