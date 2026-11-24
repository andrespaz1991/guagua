<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';
require_once __DIR__ . '/../comun/config.php';
require_once __DIR__ . '/../comun/autoload.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario) {
    die('Error: El usuario no ha iniciado sesion.');
}

$parametro_busqueda = trim((string) ($_POST['datos'] ?? $_GET['buscar_red'] ?? ''));
$campo_busqueda = (string) ($_POST['campo'] ?? $_GET['campo'] ?? 'titulo_red');
$rol_usuario = $_SESSION['rol'] ?? 'invitado';
$titulo_pagina = ($rol_usuario === 'estudiante') ? 'Entretenimiento' : 'Recursos Educativos Digitales';

function red_e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function red_lower($valor)
{
    $valor = (string) $valor;
    return function_exists('mb_strtolower') ? mb_strtolower($valor, 'UTF-8') : strtolower($valor);
}

function red_bind_params(mysqli_stmt $stmt, $types, array $params)
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

function red_fetch_all(mysqli $db, $sql, $types = '', array $params = [])
{
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        error_log('RED SQL prepare error: ' . $db->error);
        return ['ok' => false, 'rows' => [], 'error' => $db->error];
    }

    red_bind_params($stmt, $types, $params);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
    }

    $stmt->close();
    return ['ok' => true, 'rows' => $rows, 'error' => ''];
}

function red_materia_ids($materia_red)
{
    $materia_red = trim((string) $materia_red);
    if ($materia_red === '') {
        return [];
    }

    $decoded = json_decode($materia_red, true);
    if (is_array($decoded)) {
        return array_values(array_unique(array_filter(array_map('intval', $decoded))));
    }

    preg_match_all('/\d+/', $materia_red, $matches);
    return array_values(array_unique(array_filter(array_map('intval', $matches[0] ?? []))));
}

function red_obtener_materias(mysqli $db)
{
    $sql = 'SELECT id_materia, nombre_materia FROM materia ORDER BY nombre_materia ASC';
    $result = $db->query($sql);
    $materias = [];

    if (!$result) {
        error_log('RED materias query error: ' . $db->error);
        return [];
    }

    while ($row = $result->fetch_assoc()) {
        $materias[(int) $row['id_materia']] = [
            'id_materia' => (int) $row['id_materia'],
            'nombre_materia' => $row['nombre_materia'],
            'recursos' => [],
        ];
    }

    return $materias;
}

function red_obtener_recursos(mysqli $db, $termino_busqueda = '', $campo_busqueda = 'titulo_red')
{
    $campos_permitidos = [
        'titulo_red' => 'r.titulo_red',
        'descripcion' => 'r.descripcion',
        'palabras_clave' => 'r.palabras_clave',
    ];

    $campo_sql = $campos_permitidos[$campo_busqueda] ?? $campos_permitidos['titulo_red'];
    $sql = "SELECT
                r.id_red,
                r.titulo_red,
                r.nivel_eductivo,
                r.formato,
                r.enlace,
                r.scorm,
                r.icono_red,
                r.materia_red,
                i.imagen_icono
            FROM red r
            LEFT JOIN iconos i ON i.id_iconos = r.icono_red";

    $conditions = [];
    $types = '';
    $params = [];
    $terminos = preg_split('/\s+/', trim((string) $termino_busqueda));

    foreach ($terminos as $termino) {
        if ($termino === '') {
            continue;
        }
        $conditions[] = 'LOWER(' . $campo_sql . ') LIKE ?';
        $types .= 's';
        $params[] = '%' . red_lower($termino) . '%';
    }

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY r.titulo_red ASC';
    return red_fetch_all($db, $sql, $types, $params);
}

function obtener_datos_red(mysqli $db, $parametro_busqueda = '', $campo_busqueda = 'titulo_red')
{
    $materias = red_obtener_materias($db);
    $recursos_data = red_obtener_recursos($db, $parametro_busqueda, $campo_busqueda);

    if (!$recursos_data['ok']) {
        return [];
    }

    foreach ($recursos_data['rows'] as $recurso) {
        foreach (red_materia_ids($recurso['materia_red'] ?? '') as $id_materia) {
            if (!isset($materias[$id_materia])) {
                continue;
            }
            $materias[$id_materia]['recursos'][] = $recurso;
        }
    }

    return array_values(array_filter($materias, function ($materia) {
        return !empty($materia['recursos']);
    }));
}

function red_icono_url($imagen_icono)
{
    $imagen = $imagen_icono ?: 'folder-10.png';
    return SGA_COMUN_URL . '/img/png/' . $imagen;
}

$datos_para_vista = obtener_datos_red($mysqli, $parametro_busqueda, $campo_busqueda);

ob_start();
?>
<link rel="stylesheet" href="assets/biblioteca.css?v=2">

<div class="red-main-container" data-can-manage="<?= ($rol_usuario === 'admin' || $rol_usuario === 'docente') ? 'true' : 'false' ?>">
    <div class="red-jumbotron">
        <?php if ($rol_usuario === 'admin' || $rol_usuario === 'docente'): ?>
            <button type="button" class="btn btn-warning red-btn-opciones" onclick="window.location.href='nuevo_red.php'">Nuevo</button>
        <?php endif; ?>
        <h1><?php echo red_e($titulo_pagina); ?></h1>
        <div class="red-search-wrapper">
            <input type="search" id="recurso-search-input" aria-label="Buscar recursos por título" placeholder="Buscar recursos por titulo...">
        </div>
    </div>

    <div id="recursos-container">
        <?php if (empty($datos_para_vista)): ?>
            <p class="no-recursos">Aun no hay recursos o no se encontraron coincidencias.</p>
        <?php else: ?>
            <?php foreach ($datos_para_vista as $materia): ?>
                <?php $materia_id = (int) $materia['id_materia']; ?>
                <div class="materia-wrapper">
                    <p role="button" tabindex="0" aria-expanded="true" class="materia-header" data-toggle-target="#materia-<?php echo $materia_id; ?>">
                        <span><?php echo red_e($materia['nombre_materia']); ?></span>
                    </p>

                    <div class="recursos-grid" id="materia-<?php echo $materia_id; ?>">
                        <?php foreach ($materia['recursos'] as $recurso): ?>
                            <?php
                            $id_red = (int) $recurso['id_red'];
                            $titulo_red = $recurso['titulo_red'] ?? '';
                            $titulo_seguro = red_e($titulo_red);
                            $url_visor = '../red/visor_red.php?red=' . $id_red;
                            ?>
                            <a class="recurso-card context-menu-red" href="<?= red_e($url_visor) ?>"
                                 data-url="<?php echo red_e($url_visor); ?>"
                                 data-id-red="<?php echo $id_red; ?>"
                                 data-titulo-red="<?php echo $titulo_seguro; ?>">
                                <h3 title="<?php echo $titulo_seguro; ?>">
                                    <strong><?php echo red_e(puntos_suspensivos($titulo_red, 50)); ?></strong>
                                </h3>
                                <img src="<?php echo red_e(red_icono_url($recurso['imagen_icono'] ?? '')); ?>" alt="">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <p class="no-recursos" id="no-recursos-global" style="display: none;">No se encontraron recursos con ese termino de busqueda.</p>
    </div>
</div>

<script src="assets/biblioteca.js?v=2" defer></script>
<?php
$contenido = ob_get_clean();
require __DIR__ . '/../comun/plantilla.php';
?>
