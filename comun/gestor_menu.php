<?php
/**
 * =================================================================
 * MÓDULO CRUD - GESTIÓN DE MENÚ (Sede Vallesol)
 * =================================================================
 * Arquitectura:
 * - Tabla: menu_items2
 * - Búsqueda Asíncrona (Fetch API + Debounce) Case-Insensitive.
 * - Paginación dinámica (Backend + Frontend).
 * - Persistencia: $mysqli->query() con validación real_escape_string.
 * - UX/UI: Layout Stacked (Formulario Superior / Tabla Inferior).
 */

ob_start();
session_start();

// Inclusión de dependencias
require_once("../comun/conexion.php");

// =================================================================
// 0. AUTO-MIGRACIÓN DE TABLAS Y CHARSET PARA SOPORTAR EMOJIS (UTF8MB4)
// =================================================================
// Forzamos la conexión a utf8mb4 para permitir emojis
$mysqli->set_charset("utf8mb4");
// Convertimos la tabla a utf8mb4 si no lo estaba
$mysqli->query("ALTER TABLE menu_items2 CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$check_col = $mysqli->query("SHOW COLUMNS FROM menu_items2 LIKE 'is_cabecera'");
if ($check_col && $check_col->num_rows == 0) {
    $mysqli->query("ALTER TABLE menu_items2 ADD COLUMN is_cabecera TINYINT(1) DEFAULT 0");
    // Migrar los creados previamente como cabecera
    $mysqli->query("UPDATE menu_items2 SET is_cabecera = 1 WHERE categoria = 'cabecera'");
}

// =================================================================
// 1. ENDPOINT PARA BÚSQUEDA ASÍNCRONA Y PAGINACIÓN (AJAX)
// =================================================================
if (isset($_GET['ajax_search'])) {
    $busqueda = trim($_GET['ajax_search']);
    $filtro_cat = isset($_GET['ajax_categoria']) ? trim($_GET['ajax_categoria']) : '';
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    renderizar_tabla_menu($busqueda, $pagina, $filtro_cat, $mysqli);
    exit;
}

// =================================================================
// 1.5. ENDPOINT PARA SINCRONIZAR APPS (AJAX)
// =================================================================
if (isset($_GET['ajax_sync_apps'])) {
    $apps_dir = '../apps/';
    $carpetas_apps = [];
    if (is_dir($apps_dir)) {
        $directorios = scandir($apps_dir);
        foreach ($directorios as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir($apps_dir . $dir)) {
                $carpetas_apps[] = $dir;
            }
        }
    }
    
    // Obtener URLs existentes en el menú
    $urls_menu = [];
    $res_urls = $mysqli->query("SELECT menu_url FROM menu_items2 WHERE menu_url IS NOT NULL");
    if ($res_urls) {
        while ($row = $res_urls->fetch_assoc()) {
            $urls_menu[] = strtolower(trim($row['menu_url']));
        }
    }
    
    // Filtrar carpetas faltantes
    $faltantes = [];
    foreach ($carpetas_apps as $app) {
        $app_lower = strtolower($app);
        $encontrado = false;
        foreach ($urls_menu as $url) {
            if (strpos($url, $app_lower) !== false) {
                $encontrado = true;
                break;
            }
        }
        if (!$encontrado) {
            $faltantes[] = $app;
        }
    }
    
    // Devolver HTML para el modal
    if (empty($faltantes)) {
        echo '<div class="text-center p-6 text-green-600 bg-green-50 rounded-lg"><i class="fa-solid fa-check-circle text-3xl mb-2"></i><p class="font-bold">¡Todo al día!</p><p class="text-sm mt-1">Todas las apps están registradas en el menú.</p></div>';
    } else {
        echo '<div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar p-1">';
        foreach ($faltantes as $faltante) {
            echo '<label class="flex items-center p-3 bg-white border border-slate-200 rounded-lg cursor-pointer hover:bg-blue-50 transition-colors">';
            echo '<input type="checkbox" name="apps_a_sincronizar[]" value="'.htmlspecialchars($faltante).'" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 sync-checkbox">';
            echo '<span class="ml-3 text-sm font-medium text-slate-700">'.htmlspecialchars($faltante).'</span>';
            echo '</label>';
        }
        echo '</div>';
    }
    exit;
}

// =================================================================
// 2. FUNCIÓN DE RENDERIZADO DE TABLA Y PAGINACIÓN
// =================================================================
function renderizar_tabla_menu($busqueda, $pagina, $filtro_cat, $mysqli) {
    $limite = 10; // Cantidad de registros por página
    if ($pagina < 1) $pagina = 1;
    $offset = ($pagina - 1) * $limite;
    
    // Construcción dinámica de filtros
    $condiciones = [];
    
    if ($busqueda !== '') {
        $busqueda_segura = $mysqli->real_escape_string($busqueda);
        $termino = "%" . $busqueda_segura . "%";
        $condiciones[] = "(LOWER(menu_item_name) LIKE LOWER('$termino') 
                          OR LOWER(categoria) LIKE LOWER('$termino') 
                          OR LOWER(menu_description) LIKE LOWER('$termino'))";
    }
    
    if ($filtro_cat !== '') {
        $cat_segura = $mysqli->real_escape_string($filtro_cat);
        $condiciones[] = "categoria = '$cat_segura'";
    }

    $where_clause = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";
    
    // Consulta para contar el total de registros
    $sql_count = "SELECT COUNT(*) as total FROM menu_items2 $where_clause";
    
    $res_count = $mysqli->query($sql_count);
    
    // Control de error en la consulta
    if (!$res_count) {
        echo '<div class="p-6 text-center text-red-500 font-medium bg-red-50 rounded-lg">Error de base de datos al contar registros: ' . htmlspecialchars($mysqli->error) . '</div>';
        return;
    }

    $total_registros = $res_count->fetch_assoc()['total'];
    $total_paginas = ($total_registros > 0) ? ceil($total_registros / $limite) : 1;

    // Consulta principal con LIMIT y OFFSET
    $sql = "SELECT * FROM menu_items2 
            $where_clause 
            ORDER BY categoria ASC, menu_item_name ASC 
            LIMIT $limite OFFSET $offset";
            
    $resultado = $mysqli->query($sql);

    if (!$resultado) {
        echo '<div class="p-6 text-center text-red-500 font-medium bg-red-50 rounded-lg">Error de base de datos en consulta principal: ' . htmlspecialchars($mysqli->error) . '</div>';
        return;
    }

    if ($resultado->num_rows > 0) {
        echo '<div class="overflow-x-auto w-full min-h-[400px] flex flex-col justify-between">';
        echo '<table class="w-full text-sm text-left text-gray-600 relative">';
        echo '<thead class="text-xs text-slate-700 uppercase bg-slate-100 shadow-sm z-10">';
        echo '<tr>
                <th class="px-6 py-4">ID</th>
                <th class="px-6 py-4">Ícono / Nombre</th>
                <th class="px-6 py-4">Categoría</th>
                <th class="px-6 py-4">Enlace (URL)</th>
                <th class="px-6 py-4">Destino</th>
                <th class="px-6 py-4">Estado</th>
                <th class="px-6 py-4 text-right">Acciones</th>
              </tr>';
        echo '</thead><tbody class="divide-y divide-gray-200">';
        
        while ($fila = $resultado->fetch_assoc()) {
            $badgeColor = ($fila['fav'] == 1 || $fila['fav'] == '1') ? 'bg-green-100 text-green-800 border-green-200' : 'bg-slate-100 text-slate-800 border-slate-200';
            $fav_texto = ($fila['fav'] == 1 || $fila['fav'] == '1') ? 'Activo' : 'Inactivo';
            $icono_crudo = !empty($fila['icono']) ? trim($fila['icono']) : '📁';
            $es_clase_css = preg_match('/^[a-zA-Z0-9\-\s_]+$/', $icono_crudo) && (strpos($icono_crudo, 'fa') === 0 || strpos($icono_crudo, 'bx') === 0 || strpos($icono_crudo, 'icon') !== false);
            
            if (strpos($icono_crudo, 'icon-sga-') === 0) {
                $img_name = str_replace('icon-sga-', '', $icono_crudo);
                $icono_renderizado = '<img src="../comun/img/png/' . htmlspecialchars($img_name) . '.png" class="w-6 h-6 object-contain" alt="icono">';
            } else {
                $icono_renderizado = $es_clase_css ? '<i class="' . htmlspecialchars($icono_crudo) . '"></i>' : htmlspecialchars($icono_crudo);
            }
            
            $is_pinned = isset($fila['is_cabecera']) && $fila['is_cabecera'] == 1;
            $pin_color = $is_pinned ? 'text-amber-500 hover:text-slate-400' : 'text-slate-400 hover:text-amber-500';
            $pin_title = $is_pinned ? 'Quitar de Cabecera' : 'Anclar a Cabecera';
            $pin_icon = '<i class="fa-solid fa-thumbtack"></i>';

            $es_cabecera = (strtolower(trim($fila['categoria'] ?? '')) === 'cabecera');
            $row_classes = $es_cabecera ? 'bg-amber-50 hover:bg-amber-100' : 'bg-white hover:bg-blue-50';
            echo '<tr class="' . $row_classes . ' transition-colors duration-200 group">';
            echo '<td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">' . htmlspecialchars($fila['menu_item_id'] ?? '') . '</td>';
            $url_enlace = htmlspecialchars($fila['menu_url'] ?? '#');
            
            // Si está pineado, mostramos un pequeño indicador visual junto al nombre
            $pin_badge = $is_pinned ? '<span class="ml-2 text-amber-500 text-xs" title="Anclado a Cabecera"><i class="fa-solid fa-thumbtack"></i></span>' : '';
            
            echo '<td class="px-6 py-4 font-semibold text-slate-800"><a href="' . $url_enlace . '" target="_blank" class="hover:text-blue-600 hover:underline transition-colors inline-flex items-center" title="Abrir en nueva pestaña"><span class="mr-2 text-lg inline-flex justify-center items-center w-6 h-6">' . $icono_renderizado . '</span> ' . htmlspecialchars($fila['menu_item_name'] ?? '') . $pin_badge . '</a></td>';
            echo '<td class="px-6 py-4 uppercase text-[10px] tracking-wider font-bold text-slate-500">' . htmlspecialchars($fila['categoria'] ?? '') . '</td>';
            echo '<td class="px-6 py-4 text-slate-500 text-xs truncate max-w-[200px]" title="'.htmlspecialchars($fila['menu_url'] ?? '').'">' . htmlspecialchars($fila['menu_url'] ?? '') . '</td>';
            echo '<td class="px-6 py-4 text-slate-500 text-xs">' . htmlspecialchars($fila['url_target'] ?? '') . '</td>';
            echo '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold border ' . $badgeColor . '">' . $fav_texto . '</span></td>';
            echo '<td class="px-6 py-4 text-right flex justify-end gap-4 opacity-0 group-hover:opacity-100 transition-opacity">';
            echo '<button onclick="togglePin(\'' . htmlspecialchars($fila['menu_item_id'] ?? '') . '\')" class="' . $pin_color . ' flex items-center gap-1" title="' . $pin_title . '">' . $pin_icon . '</button>';
            echo '<a href="?Actualizar=' . urlencode($fila['menu_item_id']) . '" class="text-blue-600 hover:text-blue-800 flex items-center gap-1" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>';
            echo '<button onclick="confirmarEliminacion(\'' . htmlspecialchars($fila['menu_item_id'] ?? '') . '\')" class="text-red-500 hover:text-red-700 flex items-center gap-1" title="Eliminar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';

        // Renderizado de Controles de Paginación
        if ($total_paginas > 1) {
            echo '<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">';
            echo '<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">';
            echo '<div><p class="text-sm text-gray-700">Mostrando página <span class="font-medium">'.$pagina.'</span> de <span class="font-medium">'.$total_paginas.'</span> (<span class="font-medium">'.$total_registros.'</span> registros)</p></div>';
            echo '<div><nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">';
            
            // Botón Anterior
            if ($pagina > 1) {
                echo '<button data-page="'.($pagina - 1).'" class="page-link relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"><span class="sr-only">Anterior</span><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg></button>';
            }

            // Números de Página
            $inicio = max(1, $pagina - 2);
            $fin = min($total_paginas, $pagina + 2);
            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $pagina) {
                    echo '<button aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">'.$i.'</button>';
                } else {
                    echo '<button data-page="'.$i.'" class="page-link relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">'.$i.'</button>';
                }
            }

            // Botón Siguiente
            if ($pagina < $total_paginas) {
                echo '<button data-page="'.($pagina + 1).'" class="page-link relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0"><span class="sr-only">Siguiente</span><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg></button>';
            }

            echo '</nav></div></div></div>';
        }
        echo '</div>';
    } else {
        echo '<div class="flex flex-col items-center justify-center p-12 text-center bg-slate-50 rounded-lg border border-dashed border-slate-300">';
        echo '<svg class="w-12 h-12 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        echo '<h3 class="text-lg font-medium text-slate-900">No se encontraron resultados</h3>';
        echo '<p class="text-sm text-slate-500 mt-1">Intenta con otro término de búsqueda o revisa las categorías existentes.</p>';
        echo '</div>';
    }
}

// =================================================================
// 3. LÓGICA DE TRANSACCIONES CRUD (Con $mysqli->query)
// =================================================================
$alerta_js = '';

// A. CREAR
if (isset($_POST['Ingresar'])) {
    $menu_item_name   = $mysqli->real_escape_string(trim($_POST['menu_item_name']));
    $menu_description = $mysqli->real_escape_string(trim($_POST['menu_description']));
    $menu_url         = $mysqli->real_escape_string(trim($_POST['menu_url']));
    $categoria        = $mysqli->real_escape_string(trim($_POST['categoria']));
    $icono            = $mysqli->real_escape_string(trim($_POST['icono']));
    $url_target       = $mysqli->real_escape_string(trim($_POST['url_target']));
    $fav              = (int)$_POST['fav'];
    
    // Control estructural clave: Evitar FK Error si asume 0 como padre inexistente
    $menu_parent_id   = (int)$_POST['menu_parent_id'];
    $parent_id_sql    = ($menu_parent_id > 0) ? $menu_parent_id : "NULL";

    $sql = "INSERT INTO menu_items2 (menu_item_name, menu_description, menu_url, menu_parent_id, url_target, categoria, icono, fav) 
            VALUES ('$menu_item_name', '$menu_description', '$menu_url', $parent_id_sql, '$url_target', '$categoria', '$icono', $fav)";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Registrado!', 'El ítem de menú ha sido creado con éxito.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'Ocurrió un error al procesar la solicitud: ". addslashes($mysqli->error) ."', 'error');";
    }
}

// A.5. SINCRONIZAR MASIVAMENTE
if (isset($_POST['SincronizarCarpetas']) && isset($_POST['apps_a_sincronizar']) && is_array($_POST['apps_a_sincronizar'])) {
    $agregados = 0;
    foreach ($_POST['apps_a_sincronizar'] as $app) {
        $nombre = $mysqli->real_escape_string(trim($app));
        $url = "../apps/" . $nombre . "/";
        $categoria = "Apps";
        
        $sql = "INSERT INTO menu_items2 (menu_item_name, menu_description, menu_url, menu_parent_id, url_target, categoria, icono, fav) 
                VALUES ('$nombre', 'Carpeta sincronizada automáticamente', '$url', NULL, '_self', '$categoria', '📁', 0)";
        if ($mysqli->query($sql)) {
            $agregados++;
        }
    }
    if ($agregados > 0) {
        $alerta_js = "Swal.fire('¡Sincronización Exitosa!', 'Se agregaron $agregados aplicaciones al menú (Inactivas por defecto).', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Atención!', 'No se agregaron nuevas aplicaciones.', 'info');";
    }
}

// B. ACTUALIZAR
if (isset($_POST['Actualizar']) && !empty($_POST['menu_item_id_original'])) {
    $id_original      = (int)$_POST['menu_item_id_original'];
    $menu_item_name   = $mysqli->real_escape_string(trim($_POST['menu_item_name']));
    $menu_description = $mysqli->real_escape_string(trim($_POST['menu_description']));
    $menu_url         = $mysqli->real_escape_string(trim($_POST['menu_url']));
    $categoria        = $mysqli->real_escape_string(trim($_POST['categoria']));
    $icono            = $mysqli->real_escape_string(trim($_POST['icono']));
    $url_target       = $mysqli->real_escape_string(trim($_POST['url_target']));
    $fav              = (int)$_POST['fav'];
    
    // Control estructural clave: Mismo trato para FK al editar
    $menu_parent_id   = (int)$_POST['menu_parent_id'];
    $parent_id_sql    = ($menu_parent_id > 0) ? $menu_parent_id : "NULL";
    
    $sql = "UPDATE menu_items2 
            SET menu_item_name='$menu_item_name', 
                menu_description='$menu_description', 
                menu_url='$menu_url', 
                menu_parent_id=$parent_id_sql, 
                url_target='$url_target', 
                categoria='$categoria', 
                icono='$icono', 
                fav=$fav 
            WHERE menu_item_id=$id_original";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Actualizado!', 'Los datos del menú se modificaron correctamente.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'No se pudo actualizar la información: ". addslashes($mysqli->error) ."', 'error');";
    }
}

// C. ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM menu_items2 WHERE menu_item_id = $id";
    
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Eliminado!', 'El ítem de menú ha sido retirado del sistema.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'Ocurrió un error al intentar eliminar el registro.', 'error');";
    }
}

// D. PIN A CABECERA
if (isset($_GET['action']) && $_GET['action'] == 'toggle_pin' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $res = $mysqli->query("SELECT is_cabecera FROM menu_items2 WHERE menu_item_id = $id");
    if ($res && $res->num_rows > 0) {
        $curr = $res->fetch_assoc()['is_cabecera'];
        $new_val = $curr ? 0 : 1;
        if ($mysqli->query("UPDATE menu_items2 SET is_cabecera = $new_val WHERE menu_item_id = $id")) {
            $accion = $new_val ? "anclado a" : "quitado de";
            $alerta_js = "Swal.fire({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, 
                icon: 'success', title: 'Ítem $accion la cabecera'
            });";
        }
    }
}

// =================================================================
// 4. RECUPERAR DATOS PARA EDICIÓN
// =================================================================
$modo_edicion = false;
$datos_editar = [
    'menu_item_id' => '', 'menu_item_name' => '', 'menu_description' => '', 'menu_url' => '', 
    'categoria' => '', 'icono' => '', 'url_target' => '_self', 'fav' => 1, 'menu_parent_id' => ''
];

if (isset($_GET['Actualizar']) && !empty($_GET['Actualizar'])) {
    $modo_edicion = true;
    $id_editar = (int)$_GET['Actualizar'];
    
    $sql_edit = "SELECT * FROM menu_items2 WHERE menu_item_id = $id_editar";
    $res_edit = $mysqli->query($sql_edit);
    
    if ($res_edit && $res_edit->num_rows > 0) {
        $datos_editar = $res_edit->fetch_assoc();
    }
}

// =================================================================
// 5. RECUPERAR ÍCONOS EXISTENTES PARA SUGERENCIAS
// =================================================================
$iconos_existentes = [];
$sql_icons = "SELECT DISTINCT icono FROM menu_items2 WHERE icono IS NOT NULL AND icono != '' ORDER BY icono ASC";
$res_icons = $mysqli->query($sql_icons);
if ($res_icons) {
    while ($row = $res_icons->fetch_assoc()) {
        if(trim($row['icono']) !== '') {
            $iconos_existentes[] = trim($row['icono']);
        }
    }
}

// Lista de íconos (emojis) sugeridos de internet
$iconos_sugeridos = [
    '📊', '👥', '⚙️', '📅', '📄', '📁', '🏠', '🔔', '✉️', '🔒', 
    '🔍', '✍️', '✅', '❌', '💡', '🛠️', '🛒', '💳', '📦', '📈',
    '🎓', '🏫', '🚌', '🍎', '📝', '🏆', '⚽', '🎨', '💻', '🧪',
    '💬', '🌍', '🚀', '🔥', '⭐', '❤️', '🏥', '⚕️', '🏃', '📱',
    'fa-solid fa-home', 'fa-solid fa-user', 'fa-solid fa-cog', 'fa-solid fa-chart-bar',
    'fa-solid fa-envelope', 'fa-solid fa-bell', 'fa-solid fa-graduation-cap', 'fa-solid fa-book',
    'bx bx-home', 'bx bx-user', 'bx bx-cog', 'bx bx-bar-chart'
];

$iconos_personalizados = [];
$res_iconos_db = $mysqli->query("SELECT id_iconos, icono, imagen_icono FROM iconos ORDER BY icono ASC");
if ($res_iconos_db) {
    while ($row = $res_iconos_db->fetch_assoc()) {
        $img_sin_ext = pathinfo($row['imagen_icono'], PATHINFO_FILENAME);
        $clase_icono = "icon-sga-" . $img_sin_ext;
        $iconos_personalizados[] = [
            'nombre' => $row['icono'],
            'clase' => $clase_icono,
            'imagen' => $row['imagen_icono']
        ];
    }
}

// Función auxiliar para renderizar el ícono en PHP de manera segura
if (!function_exists('renderizar_icono')) {
    function renderizar_icono($ico) {
        $ico = trim($ico);
        if (strpos($ico, 'icon-sga-') === 0) {
            $img_name = str_replace('icon-sga-', '', $ico);
            return '<img src="../comun/img/png/' . htmlspecialchars($img_name) . '.png" class="w-6 h-6 object-contain" alt="icono">';
        }
        if (preg_match('/^[a-zA-Z0-9\-\s_]+$/', $ico) && (strpos($ico, 'fa') === 0 || strpos($ico, 'bx') === 0 || strpos($ico, 'icon') !== false)) {
            return '<i class="' . htmlspecialchars($ico) . '"></i>';
        }
        return htmlspecialchars($ico);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Menú - Vallesol</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .loader-line {
            height: 3px;
            width: 100%;
            background-color: #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        .loader-line::before {
            content: '';
            position: absolute;
            left: -50%;
            height: 3px;
            width: 40%;
            background-color: #3b82f6;
            animation: lineAnim 1s linear infinite;
        }
        @keyframes lineAnim {
            0% { left: -40%; }
            100% { left: 100%; }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="antialiased text-slate-800">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Gestión de Elementos de Menú</h1>
            <p class="text-slate-500 text-sm mt-2">Administre los módulos, enlaces y categorías que componen el menú lateral/principal de la plataforma Vallesol.</p>
        </div>

        <div class="flex flex-col gap-8">
            
            <!-- BLOQUE SUPERIOR: Formulario -->
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 w-full transition-all duration-300">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <div class="p-2 <?php echo $modo_edicion ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'; ?> rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $modo_edicion ? 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' : 'M4 6h16M4 12h16M4 18h16'; ?>"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">
                        <?php echo $modo_edicion ? 'Editar Elemento de Menú' : 'Nuevo Elemento de Menú'; ?>
                    </h2>
                </div>
                
                <form method="POST" action="" class="space-y-6">
                    <?php if($modo_edicion): ?>
                        <input type="hidden" name="menu_item_id_original" value="<?php echo htmlspecialchars($datos_editar['menu_item_id'] ?? ''); ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Fila 1 -->
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre del Ítem <span class="text-red-500">*</span></label>
                            <input type="text" name="menu_item_name" required placeholder="Ej. Reporte de horas"
                                   value="<?php echo htmlspecialchars($datos_editar['menu_item_name'] ?? ''); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">URL / Enlace <span class="text-red-500">*</span></label>
                            <input type="text" name="menu_url" required placeholder="/asistencia/reporte.php"
                                   value="<?php echo htmlspecialchars($datos_editar['menu_url'] ?? ''); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <!-- Fila 2 -->
                        <div class="lg:col-span-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Descripción (Opcional)</label>
                            <input type="text" name="menu_description" placeholder="Breve descripción del módulo para accesibilidad"
                                   value="<?php echo htmlspecialchars($datos_editar['menu_description'] ?? ''); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <!-- Fila 3 -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Categoría <span class="text-red-500">*</span></label>
                            <input type="text" name="categoria" id="categoriaInput" required placeholder="Ej. Reportes, Usuarios"
                                   value="<?php echo htmlspecialchars($datos_editar['categoria'] ?? ''); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Ícono (Emoji/Clase)</label>
                                <button type="button" onclick="toggleIconPanel()" class="text-xs text-blue-600 hover:text-blue-800 font-semibold focus:outline-none flex items-center gap-1">
                                    <i id="iconToggleState" class="fa-solid fa-chevron-down"></i> Mostrar Catálogo
                                </button>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="icono" id="iconoInput" placeholder="Ej. 📄 o fa-solid fa-user"
                                       value="<?php echo htmlspecialchars($datos_editar['icono'] ?? ''); ?>"
                                       class="flex-1 px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                       oninput="actualizarPreviewIcono(this.value)">
                                <div id="previewIcono" class="w-10 h-10 flex items-center justify-center bg-slate-100 border border-slate-200 rounded-xl text-lg shrink-0 transition-all overflow-hidden">
                                    <?php echo renderizar_icono(!empty($datos_editar['icono']) ? $datos_editar['icono'] : '📁'); ?>
                                </div>
                            </div>

                            <div id="panelIconosWrapper" class="hidden mt-3 p-3 bg-white border border-slate-100 rounded-xl shadow-sm">
                                <!-- Emojis Sugeridos -->
                                <p class="text-[10px] text-slate-400 mb-2 uppercase font-bold tracking-wider flex items-center justify-between">
                                    <span>Sugeridos (Internet)</span>
                                </p>
                                <div class="flex flex-wrap gap-1.5 mb-4 max-h-[120px] overflow-y-auto pr-1 custom-scrollbar">
                                    <?php foreach ($iconos_sugeridos as $ico): ?>
                                        <button type="button" onclick="seleccionarIcono('<?php echo htmlspecialchars($ico, ENT_QUOTES); ?>')" 
                                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors text-base overflow-hidden" 
                                                title="Usar ícono: <?php echo htmlspecialchars($ico, ENT_QUOTES); ?>">
                                            <?php echo renderizar_icono($ico); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (!empty($iconos_personalizados)): ?>
                                <!-- Íconos Personalizados (Cabecera) -->
                                <div id="seccionIconosCabecera" style="display: none;">
                                    <p class="text-[10px] text-slate-400 mb-2 uppercase font-bold tracking-wider flex items-center justify-between border-t border-slate-100 pt-2">
                                        <span>Íconos de Cabecera (iconos.php)</span>
                                    </p>
                                    <div class="flex flex-wrap gap-1.5 mb-4 max-h-[120px] overflow-y-auto pr-1 custom-scrollbar">
                                        <?php foreach ($iconos_personalizados as $ico_pers): ?>
                                            <button type="button" onclick="seleccionarIcono('<?php echo htmlspecialchars($ico_pers['clase'], ENT_QUOTES); ?>')" 
                                                    class="w-10 h-10 flex items-center justify-center rounded-lg bg-amber-50 border border-amber-200 hover:bg-amber-100 hover:border-amber-300 hover:text-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 transition-colors text-base overflow-hidden p-1" 
                                                    title="<?php echo htmlspecialchars($ico_pers['nombre'], ENT_QUOTES); ?>">
                                                <img src="../comun/img/png/<?php echo htmlspecialchars($ico_pers['imagen']); ?>" class="w-full h-full object-contain" alt="icono">
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($iconos_existentes)): ?>
                                <!-- Íconos en Base de Datos -->
                                <p class="text-[10px] text-slate-400 mb-2 uppercase font-bold tracking-wider flex items-center justify-between border-t border-slate-100 pt-2">
                                    <span>En uso en el sistema</span>
                                    <span class="bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-semibold"><?php echo count($iconos_existentes); ?></span>
                                </p>
                                <div class="flex flex-wrap gap-1.5 max-h-[88px] overflow-y-auto pr-1 custom-scrollbar">
                                    <?php foreach ($iconos_existentes as $ico): ?>
                                        <button type="button" onclick="seleccionarIcono('<?php echo htmlspecialchars($ico, ENT_QUOTES); ?>')" 
                                                class="w-9 h-9 flex items-center justify-center rounded-lg bg-slate-50 border border-slate-200 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors text-base overflow-hidden" 
                                                title="Usar ícono: <?php echo htmlspecialchars($ico, ENT_QUOTES); ?>">
                                            <?php echo renderizar_icono($ico); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Destino (Target)</label>
                            <select name="url_target" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                <option value="_self" <?php echo (($datos_editar['url_target'] ?? '') == '_self') ? 'selected' : ''; ?>>Misma Pestaña (_self)</option>
                                <option value="_blank" <?php echo (($datos_editar['url_target'] ?? '') == '_blank') ? 'selected' : ''; ?>>Nueva Pestaña (_blank)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Padre ID</label>
                                <input type="number" name="menu_parent_id" placeholder="0 = Raíz"
                                       value="<?php echo htmlspecialchars($datos_editar['menu_parent_id'] ?? ''); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Estado</label>
                                <select name="fav" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                    <option value="1" <?php echo (($datos_editar['fav'] ?? '') == 1 || ($datos_editar['fav'] ?? '') == '1') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo (($datos_editar['fav'] ?? '') == 0 || ($datos_editar['fav'] ?? '') == '0' && ($datos_editar['fav'] ?? '') != '') ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <?php if($modo_edicion): ?>
                            <!-- Ruta limpia para salir del modo edición -->
                            <a href="?" class="text-slate-700 bg-slate-100 hover:bg-slate-200 font-semibold rounded-xl text-sm px-6 py-3 transition-all">Cancelar</a>
                            <button type="submit" name="Actualizar" class="text-white bg-blue-600 hover:bg-blue-700 font-semibold rounded-xl text-sm px-8 py-3 shadow-sm hover:shadow transition-all">Guardar Cambios</button>
                        <?php else: ?>
                            <button type="submit" name="Ingresar" class="text-white bg-slate-900 hover:bg-slate-800 font-semibold rounded-xl text-sm px-8 py-3 shadow-sm hover:shadow transition-all">Crear Ítem</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- BLOQUE INFERIOR: Listado y Búsqueda -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col w-full">
                
                <!-- Header Buscador -->
                <div class="p-6 border-b border-slate-100 bg-white z-20">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                Directorio de Menús
                                <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-0.5 rounded-full font-semibold">En tiempo real</span>
                            </h2>
                            <button type="button" onclick="abrirModalSync()" class="text-xs bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-bold py-1.5 px-3 rounded-lg flex items-center gap-1 transition-colors border border-emerald-200 shadow-sm">
                                <i class="fa-solid fa-rotate"></i> Sincronizar Apps
                            </button>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <!-- Select Filtro de Categoría -->
                            <select id="filterCategory" class="block w-full sm:w-48 py-2.5 px-3 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-blue-500 outline-none transition-all shadow-inner">
                                <option value="">Todas las categorías</option>
                                <?php 
                                    $res_cat = $mysqli->query("SELECT DISTINCT categoria FROM menu_items2 WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC");
                                    if ($res_cat) {
                                        while ($row = $res_cat->fetch_assoc()) {
                                            $cat_op = htmlspecialchars(trim($row['categoria']));
                                            echo "<option value=\"$cat_op\">$cat_op</option>";
                                        }
                                    }
                                ?>
                            </select>

                            <div class="relative w-full sm:w-96">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" id="searchInput" placeholder="Buscar por nombre, categoría o descripción..." 
                                       class="block w-full py-2.5 pl-10 pr-10 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-blue-500 outline-none transition-all shadow-inner">
                                <!-- Spinner interno del buscador -->
                                <div id="searchSpinner" class="absolute inset-y-0 right-0 flex items-center pr-3 hidden">
                                    <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Linea de carga decorativa -->
                <div id="loaderLine" class="loader-line hidden"></div>

                <!-- Contenedor dinámico de la tabla y paginación -->
                <div id="tableContainer" class="flex-1 bg-slate-50/50 p-6">
                    <?php renderizar_tabla_menu('', 1, '', $mysqli); ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Lógica de íconos en el frontend
        function actualizarPreviewIcono(valor) {
            const preview = document.getElementById('previewIcono');
            valor = valor.trim();
            if (valor === '') {
                preview.innerHTML = '📁';
            } else if (valor.startsWith('icon-sga-')) {
                let imgName = valor.replace('icon-sga-', '');
                preview.innerHTML = `<img src="../comun/img/png/${imgName}.png" class="w-6 h-6 object-contain" alt="icono">`;
            } else if (/^[a-zA-Z0-9\-\s_]+$/.test(valor) && (valor.startsWith('fa') || valor.startsWith('bx') || valor.includes('icon'))) {
                preview.innerHTML = `<i class="${valor}"></i>`;
            } else {
                preview.innerHTML = valor;
            }
        }

        function seleccionarIcono(valor) {
            document.getElementById('iconoInput').value = valor;
            actualizarPreviewIcono(valor);
        }

        // Lógica para mostrar/ocultar los iconos de cabecera
        const categoriaInput = document.getElementById('categoriaInput');
        const seccionIconosCabecera = document.getElementById('seccionIconosCabecera');

        function verificarIconosCabecera() {
            if (!seccionIconosCabecera) return;
            const isCabecera = categoriaInput.value.trim().toLowerCase() === 'cabecera';
            seccionIconosCabecera.style.display = isCabecera ? 'block' : 'none';
        }

        if (categoriaInput) {
            categoriaInput.addEventListener('input', verificarIconosCabecera);
            // Ejecutar al cargar por si está prellenado en edición
            verificarIconosCabecera();
        }

        // 1. Ejecución de Alertas SweetAlert2 (desde PHP)
        <?php if (!empty($alerta_js)): ?>
            <?php echo $alerta_js; ?>
            
            // Patrón PRG para UX: Limpiar historial y parámetros POST/GET tras la alerta
            if (window.history.replaceState) {
                // Se extrae la URL completa sin parámetros para evitar el error CORS por dobles slashes
                window.history.replaceState(null, null, window.location.href.split('?')[0]);
            }
        <?php endif; ?>

        // 2. Función de Confirmación para Eliminar (SweetAlert2)
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "El enlace dejará de estar disponible en los menús del sistema.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Rutas dinámicas robustas
                    const url = new URL(window.location.href.split('?')[0]);
                    url.searchParams.set('action', 'delete');
                    url.searchParams.set('id', id);
                    window.location.href = url.toString();
                }
            })
        }

        // Función para alternar el Pin en la cabecera
        function togglePin(id) {
            const url = new URL(window.location.href.split('?')[0]);
            url.searchParams.set('action', 'toggle_pin');
            url.searchParams.set('id', id);
            window.location.href = url.toString();
        }

        // 3. Lógica de Búsqueda Asíncrona y Paginación
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const filterCategory = document.getElementById('filterCategory');
            const tableContainer = document.getElementById('tableContainer');
            const searchSpinner = document.getElementById('searchSpinner');
            const loaderLine = document.getElementById('loaderLine');
            let debounceTimer;
            let currentPage = 1;

            // Función principal para obtener los datos
            const fetchData = async (query, category, page) => {
                // Mostrar UI de carga
                searchSpinner.classList.remove('hidden');
                loaderLine.classList.remove('hidden');
                tableContainer.style.opacity = '0.6'; 

                try {
                    // Construcción dinámica de la URL
                    const requestUrl = new URL(window.location.href.split('?')[0]);
                    requestUrl.searchParams.set('ajax_search', query);
                    requestUrl.searchParams.set('ajax_categoria', category);
                    requestUrl.searchParams.set('page', page);
                    
                    const response = await fetch(requestUrl.toString());
                    if (!response.ok) throw new Error('Error en la red');
                    
                    const htmlString = await response.text();
                    tableContainer.innerHTML = htmlString;
                } catch (error) {
                    console.error('Error durante la solicitud asíncrona:', error);
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false, 
                        timer: 3000, icon: 'error', title: 'Error de conexión en tiempo real'
                    });
                } finally {
                    // Restaurar UI
                    searchSpinner.classList.add('hidden');
                    loaderLine.classList.add('hidden');
                    tableContainer.style.opacity = '1';
                }
            };

            // Evento de escritura en el input (Debounce)
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                const cat = filterCategory.value;
                currentPage = 1; // Reiniciar a la página 1 al buscar
                
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData(query, cat, currentPage);
                }, 400); 
            });

            // Evento de cambio en el selector de categoría
            filterCategory.addEventListener('change', function() {
                const query = searchInput.value.trim();
                const cat = this.value;
                currentPage = 1;
                fetchData(query, cat, currentPage);
            });

            // Delegación de eventos para la paginación
            tableContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.page-link');
                if (btn) {
                    e.preventDefault();
                    const targetPage = btn.getAttribute('data-page');
                    if (targetPage) {
                        currentPage = targetPage;
                        fetchData(searchInput.value.trim(), filterCategory.value, currentPage);
                    }
                }
            });
        });

        // JS - Icon Toggle
        function toggleIconPanel() {
            const panel = document.getElementById('panelIconosWrapper');
            const toggleIcon = document.getElementById('iconToggleState');
            if (panel.classList.contains('hidden')) {
                panel.classList.remove('hidden');
                toggleIcon.classList.remove('fa-chevron-down');
                toggleIcon.classList.add('fa-chevron-up');
            } else {
                panel.classList.add('hidden');
                toggleIcon.classList.remove('fa-chevron-up');
                toggleIcon.classList.add('fa-chevron-down');
            }
        }

        // JS - Sincronización Modal
        function abrirModalSync() {
            document.getElementById('syncModal').classList.remove('hidden');
            const container = document.getElementById('syncListContainer');
            document.getElementById('btnSincronizar').disabled = true;
            document.getElementById('selectAllApps').checked = false;
            
            // Cargar datos
            container.innerHTML = '<div class="flex justify-center py-4"><svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';
            
            fetch('?ajax_sync_apps=1')
                .then(res => res.text())
                .then(html => {
                    container.innerHTML = html;
                    
                    // Manejar lógica de checkboxes
                    const checkboxes = document.querySelectorAll('.sync-checkbox');
                    const btnSync = document.getElementById('btnSincronizar');
                    const selectAll = document.getElementById('selectAllApps');
                    
                    if(checkboxes.length === 0) {
                        selectAll.disabled = true;
                    } else {
                        selectAll.disabled = false;
                        
                        const actualizarBoton = () => {
                            const checkedCount = document.querySelectorAll('.sync-checkbox:checked').length;
                            btnSync.disabled = checkedCount === 0;
                            selectAll.checked = checkedCount === checkboxes.length && checkboxes.length > 0;
                        };
                        
                        checkboxes.forEach(cb => cb.addEventListener('change', actualizarBoton));
                        
                        selectAll.addEventListener('change', function() {
                            checkboxes.forEach(cb => cb.checked = this.checked);
                            actualizarBoton();
                        });
                    }
                })
                .catch(err => {
                    container.innerHTML = '<div class="text-red-500 p-4 text-sm text-center">Error al cargar las carpetas.</div>';
                });
        }
        
        function cerrarModalSync() {
            document.getElementById('syncModal').classList.add('hidden');
        }
    </script>

    <!-- Modal de Sincronización -->
    <div id="syncModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-75" aria-hidden="true" onclick="cerrarModalSync()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-slate-200">
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fa-solid fa-rotate text-blue-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg font-medium leading-6 text-slate-900" id="modal-title">Sincronizar Carpetas (Apps)</h3>
                        <div class="mt-2 text-sm text-slate-500">
                            Selecciona las carpetas existentes en la ruta <code>guagua/apps/</code> que no están registradas en el menú actual.
                        </div>
                        <form method="POST" action="" id="syncForm" class="mt-4">
                            <div class="flex items-center mb-3">
                                <input type="checkbox" id="selectAllApps" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="selectAllApps" class="ml-2 text-sm font-bold text-slate-700 cursor-pointer">Seleccionar Todas</label>
                            </div>
                            <div id="syncListContainer" class="bg-slate-50 rounded-xl border border-slate-200 p-2 min-h-[100px] flex items-center justify-center relative">
                                <!-- Contenido AJAX -->
                                <svg class="animate-spin h-6 w-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="submit" name="SincronizarCarpetas" id="btnSincronizar" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-lg shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors" disabled>
                                    Agregar al Menú
                                </button>
                                <button type="button" onclick="cerrarModalSync()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-slate-700 bg-white border border-slate-300 rounded-lg shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>