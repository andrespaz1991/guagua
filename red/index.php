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
<style>
    :root {
        --red-primary: #3d7fc2;
        --red-primary-dark: #2f6aa4;
        --red-accent: #f2721d;
        --red-bg: #f4f7f9;
        --red-card: #ffffff;
        --red-text: #333333;
        --red-line: #e0e0e0;
        --red-shadow: rgba(0, 0, 0, 0.08);
    }

    body {
        background-color: var(--red-bg);
        color: var(--red-text);
    }

    .red-main-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 20px;
    }

    .red-jumbotron {
        background: linear-gradient(135deg, var(--red-primary), var(--red-primary-dark));
        color: white;
        border-radius: 8px;
        padding: 30px;
        margin-bottom: 34px;
        text-align: center;
        position: relative;
        box-shadow: 0 4px 15px rgba(61, 127, 194, 0.35);
    }

    .red-jumbotron h1 {
        margin: 0 0 20px;
        font-size: 2.4em;
        font-weight: 700;
    }

    .red-search-wrapper {
        position: relative;
        max-width: 520px;
        margin: 0 auto;
    }

    .red-search-wrapper::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 50%;
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.75);
        border-radius: 50%;
        transform: translateY(-55%);
    }

    .red-search-wrapper::after {
        content: '';
        position: absolute;
        left: 31px;
        top: 56%;
        width: 8px;
        height: 2px;
        background: rgba(255, 255, 255, 0.75);
        transform: rotate(45deg);
        transform-origin: left center;
    }

    #recurso-search-input {
        width: 100%;
        padding: 12px 20px 12px 48px;
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.55);
        background-color: rgba(255, 255, 255, 0.18);
        color: white;
        font-size: 1em;
        transition: background-color 0.2s, box-shadow 0.2s;
    }

    #recurso-search-input::placeholder {
        color: rgba(255, 255, 255, 0.72);
    }

    #recurso-search-input:focus {
        outline: none;
        background-color: rgba(255, 255, 255, 0.28);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.28);
    }

    .red-btn-opciones {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 10;
        background-color: rgba(255, 255, 255, 0.22);
        border: 1px solid rgba(255, 255, 255, 0.55);
        color: white;
    }

    .materia-wrapper {
        margin-bottom: 26px;
    }

    .materia-header {
        background-color: var(--red-card);
        color: var(--red-primary);
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 5px var(--red-shadow);
        font-size: 1.3em;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 5px solid var(--red-accent);
        transition: box-shadow 0.2s ease;
    }

    .materia-header:hover {
        box-shadow: 0 4px 10px var(--red-shadow);
    }

    .materia-header::after {
        content: 'v';
        font-size: 0.8em;
        transition: transform 0.25s ease;
    }

    .materia-header.collapsed::after {
        transform: rotate(-90deg);
    }

    .recursos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 22px;
        overflow: hidden;
        opacity: 1;
        transition: max-height 0.35s ease, opacity 0.35s ease;
    }

    .grid-hidden {
        max-height: 0;
        opacity: 0;
        padding-bottom: 0;
    }

    .recurso-card {
        background-color: var(--red-card);
        border: 1px solid var(--red-line);
        border-radius: 8px;
        padding: 18px 14px;
        min-height: 155px;
        text-align: center;
        cursor: pointer;
        box-shadow: 0 4px 6px var(--red-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .recurso-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 15px var(--red-shadow);
    }

    .recurso-card h3 {
        font-size: 1em;
        font-weight: 600;
        margin: 0 0 15px;
        min-height: 45px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .recurso-card img {
        width: 60px;
        height: 60px;
        object-fit: contain;
        margin-top: auto;
    }

    .no-recursos {
        text-align: center;
        padding: 42px;
        font-size: 1.15em;
        color: #777;
    }

    .pagination-controls {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 22px 0 36px;
        padding: 10px 0;
    }

    .pagination-button {
        background-color: var(--red-card);
        border: 1px solid var(--red-line);
        color: var(--red-primary);
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: background-color 0.2s, color 0.2s;
    }

    .pagination-button:hover:not(:disabled),
    .pagination-button.active {
        background-color: var(--red-primary);
        color: white;
        border-color: var(--red-primary);
    }

    .pagination-button:disabled {
        background-color: #f0f0f0;
        color: #aaa;
        cursor: not-allowed;
    }
</style>

<div class="red-main-container">
    <div class="red-jumbotron">
        <?php if ($rol_usuario === 'admin' || $rol_usuario === 'docente'): ?>
            <button type="button" class="btn btn-warning red-btn-opciones" onclick="window.location.href='nuevo_red.php'">Nuevo</button>
        <?php endif; ?>
        <h1><?php echo red_e($titulo_pagina); ?></h1>
        <div class="red-search-wrapper">
            <input type="search" id="recurso-search-input" placeholder="Buscar recursos por titulo...">
        </div>
    </div>

    <div id="recursos-container">
        <?php if (empty($datos_para_vista)): ?>
            <p class="no-recursos">Aun no hay recursos o no se encontraron coincidencias.</p>
        <?php else: ?>
            <?php foreach ($datos_para_vista as $materia): ?>
                <?php $materia_id = (int) $materia['id_materia']; ?>
                <div class="materia-wrapper">
                    <p class="materia-header" data-toggle-target="#materia-<?php echo $materia_id; ?>">
                        <span><?php echo red_e($materia['nombre_materia']); ?></span>
                    </p>

                    <div class="recursos-grid" id="materia-<?php echo $materia_id; ?>">
                        <?php foreach ($materia['recursos'] as $recurso): ?>
                            <?php
                            $id_red = (int) $recurso['id_red'];
                            $titulo_red = $recurso['titulo_red'] ?? '';
                            $titulo_seguro = red_e($titulo_red);
                            $url_visor = '../red/visor_red.php?red=' . $id_red .
                                '&formato=' . rawurlencode((string) ($recurso['formato'] ?? '')) .
                                '&enlace=' . rawurlencode((string) ($recurso['enlace'] ?? '')) .
                                '&scorm=' . rawurlencode((string) ($recurso['scorm'] ?? ''));
                            ?>
                            <div class="recurso-card context-menu-red"
                                 data-url="<?php echo red_e($url_visor); ?>"
                                 data-id-red="<?php echo $id_red; ?>"
                                 data-titulo-red="<?php echo $titulo_seguro; ?>">
                                <h3 title="<?php echo $titulo_seguro; ?>">
                                    <strong><?php echo red_e(puntos_suspensivos($titulo_red, 50)); ?></strong>
                                </h3>
                                <img src="<?php echo red_e(red_icono_url($recurso['imagen_icono'] ?? '')); ?>" alt="Icono de recurso">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <p class="no-recursos" id="no-recursos-global" style="display: none;">No se encontraron recursos con ese termino de busqueda.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var RESOURCES_PER_PAGE = 8;
    var searchInput = document.getElementById('recurso-search-input');
    var noRecursosGlobalMessage = document.getElementById('no-recursos-global');
    var canManageRed = <?php echo ($rol_usuario === 'admin' || $rol_usuario === 'docente') ? 'true' : 'false'; ?>;
    var paginationSystems = new Map();

    function getFilteredCards(grid) {
        return Array.prototype.slice.call(grid.querySelectorAll('.recurso-card')).filter(function(card) {
            return card.dataset.searchMatch !== '0';
        });
    }

    function setupPagination(materiaWrapper) {
        var currentPage = 1;
        var grid = materiaWrapper.querySelector('.recursos-grid');
        var controls = materiaWrapper.querySelector('.pagination-controls') || document.createElement('div');

        if (!controls.classList.contains('pagination-controls')) {
            controls.className = 'pagination-controls';
            materiaWrapper.appendChild(controls);
        }

        function showPage(page) {
            var visibleResources = getFilteredCards(grid);
            var totalPages = Math.max(1, Math.ceil(visibleResources.length / RESOURCES_PER_PAGE));
            currentPage = Math.min(Math.max(page, 1), totalPages);
            var startIndex = (currentPage - 1) * RESOURCES_PER_PAGE;
            var endIndex = startIndex + RESOURCES_PER_PAGE;

            Array.prototype.forEach.call(grid.querySelectorAll('.recurso-card'), function(resource) {
                resource.style.display = 'none';
            });

            visibleResources.forEach(function(resource, index) {
                resource.style.display = (index >= startIndex && index < endIndex) ? 'flex' : 'none';
            });

            updateControls(visibleResources.length, totalPages);
        }

        function updateControls(totalResources, totalPages) {
            controls.innerHTML = '';
            if (totalResources <= RESOURCES_PER_PAGE) {
                return;
            }

            var prevButton = document.createElement('button');
            prevButton.textContent = 'Anterior';
            prevButton.className = 'pagination-button';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', function() {
                showPage(currentPage - 1);
            });
            controls.appendChild(prevButton);

            for (var i = 1; i <= totalPages; i++) {
                var pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.className = 'pagination-button';
                if (i === currentPage) {
                    pageButton.classList.add('active');
                }
                pageButton.addEventListener('click', (function(pageNumber) {
                    return function() {
                        showPage(pageNumber);
                    };
                })(i));
                controls.appendChild(pageButton);
            }

            var nextButton = document.createElement('button');
            nextButton.textContent = 'Siguiente';
            nextButton.className = 'pagination-button';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', function() {
                showPage(currentPage + 1);
            });
            controls.appendChild(nextButton);
        }

        showPage(1);
        return {
            show: showPage
        };
    }

    document.querySelectorAll('.materia-wrapper').forEach(function(materiaWrapper) {
        materiaWrapper.querySelectorAll('.recurso-card').forEach(function(card) {
            card.dataset.searchMatch = '1';
        });
        paginationSystems.set(materiaWrapper, setupPagination(materiaWrapper));
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.trim().toLowerCase();
            var totalVisibleResources = 0;

            document.querySelectorAll('.materia-wrapper').forEach(function(materiaWrapper) {
                var visibleInSection = 0;

                materiaWrapper.querySelectorAll('.recurso-card').forEach(function(resource) {
                    var title = (resource.dataset.tituloRed || '').toLowerCase();
                    var isMatch = title.indexOf(searchTerm) !== -1;
                    resource.dataset.searchMatch = isMatch ? '1' : '0';
                    if (isMatch) {
                        visibleInSection++;
                        totalVisibleResources++;
                    }
                });

                materiaWrapper.style.display = visibleInSection > 0 ? 'block' : 'none';
                var system = paginationSystems.get(materiaWrapper);
                if (system) {
                    system.show(1);
                }
            });

            if (noRecursosGlobalMessage) {
                noRecursosGlobalMessage.style.display = totalVisibleResources === 0 ? 'block' : 'none';
            }
        });
    }

    if (typeof $ !== 'undefined' && typeof $.contextMenu === 'function') {
        $.contextMenu({
            selector: '.context-menu-red',
            build: function($trigger) {
                var idRed = $trigger.data('id-red');
                var tituloRed = $trigger.data('titulo-red');
                var items = {
                    info: { name: tituloRed, icon: 'info', disabled: true },
                    sep1: '---------',
                    descargar: { name: 'Descargar', icon: 'download' }
                };

                if (canManageRed) {
                    items.modificar = { name: 'Modificar', icon: 'edit' };
                    items.eliminar = { name: 'Eliminar', icon: 'delete' };
                }

                items.sep2 = '---------';
                items.salir = { name: 'Salir', icon: 'quit' };

                return {
                    callback: function(key) {
                        if (key === 'descargar') {
                            window.location.href = '../comun/funciones.php?ruta_red=' + encodeURIComponent(idRed);
                        } else if (key === 'modificar' && canManageRed) {
                            window.location.href = 'nuevo_red.php?id_red=' + encodeURIComponent(idRed);
                        } else if (key === 'eliminar' && canManageRed && confirm('Esta seguro que desea eliminar "' + tituloRed + '"?')) {
                            window.location.href = '../comun/funciones.php?elred=' + encodeURIComponent(idRed);
                        }
                    },
                    items: items
                };
            }
        });
    }

    document.querySelectorAll('.materia-header').forEach(function(header) {
        header.addEventListener('click', function() {
            var targetSelector = this.getAttribute('data-toggle-target');
            var gridElement = document.querySelector(targetSelector);
            var paginationElement = this.parentElement.querySelector('.pagination-controls');

            if (gridElement) {
                var isCollapsed = gridElement.classList.toggle('grid-hidden');
                this.classList.toggle('collapsed', isCollapsed);
                if (paginationElement) {
                    paginationElement.style.display = isCollapsed ? 'none' : 'flex';
                }
            }
        });
    });

    document.querySelectorAll('.recurso-card').forEach(function(card) {
        card.addEventListener('mousedown', function(event) {
            if (event.button === 0) {
                var url = this.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            }
        });
    });
});
</script>
<?php
$contenido = ob_get_clean();
require __DIR__ . '/../comun/plantilla.php';
?>
