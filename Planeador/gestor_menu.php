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
// 1. ENDPOINT PARA BÚSQUEDA ASÍNCRONA Y PAGINACIÓN (AJAX)
// =================================================================
if (isset($_GET['ajax_search'])) {
    $busqueda = trim($_GET['ajax_search']);
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    renderizar_tabla_menu($busqueda, $pagina, $mysqli);
    exit;
}

// =================================================================
// 2. FUNCIÓN DE RENDERIZADO DE TABLA Y PAGINACIÓN
// =================================================================
function renderizar_tabla_menu($busqueda, $pagina, $mysqli) {
    $limite = 10; // Cantidad de registros por página
    if ($pagina < 1) $pagina = 1;
    $offset = ($pagina - 1) * $limite;
    
    // Sanitización exhaustiva
    $busqueda_segura = $mysqli->real_escape_string($busqueda);
    $termino = "%" . $busqueda_segura . "%";
    
    // Consulta para contar el total de registros
    $sql_count = "SELECT COUNT(*) as total 
                  FROM menu_items2 
                  WHERE LOWER(menu_item_name) LIKE LOWER('$termino') 
                     OR LOWER(categoria) LIKE LOWER('$termino') 
                     OR LOWER(menu_description) LIKE LOWER('$termino')";
    
    $res_count = $mysqli->query($sql_count);
    $total_registros = $res_count->fetch_assoc()['total'];
    $total_paginas = ceil($total_registros / $limite);

    // Consulta principal con LIMIT y OFFSET
    $sql = "SELECT * FROM menu_items2 
            WHERE LOWER(menu_item_name) LIKE LOWER('$termino') 
               OR LOWER(categoria) LIKE LOWER('$termino') 
               OR LOWER(menu_description) LIKE LOWER('$termino') 
            ORDER BY categoria ASC, menu_item_name ASC 
            LIMIT $limite OFFSET $offset";
            
    $resultado = $mysqli->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        echo '<div class="overflow-x-auto w-full min-h-[400px] flex flex-col justify-between">';
        echo '<table class="w-full text-sm text-left text-gray-600 relative">';
        echo '<thead class="text-xs text-slate-700 uppercase bg-slate-100 shadow-sm z-10">';
        echo '<tr>
                <th class="px-6 py-4">ID</th>
                <th class="px-6 py-4">Ícono / Nombre</th>
                <th class="px-6 py-4">Categoría</th>
                <th class="px-6 py-4">Enlace (URL)</th>
                <th class="px-6 py-4">Destino</th>
                <th class="px-6 py-4">fav</th>
                <th class="px-6 py-4 text-right">Acciones</th>
              </tr>';
        echo '</thead><tbody class="divide-y divide-gray-200">';
        
        while ($fila = $resultado->fetch_assoc()) {
            $badgeColor = ($fila['fav'] == 1 || $fila['fav'] == '1') ? 'bg-green-100 text-green-800 border-green-200' : 'bg-slate-100 text-slate-800 border-slate-200';
            $fav_texto = ($fila['fav'] == 1 || $fila['fav'] == '1') ? 'Activo' : 'Inactivo';
            $icono = !empty($fila['icono']) ? $fila['icono'] : '📁';
            
            echo '<tr class="bg-white hover:bg-blue-50 transition-colors duration-200 group">';
            echo '<td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">' . htmlspecialchars($fila['menu_item_id']) . '</td>';
            echo '<td class="px-6 py-4 font-semibold text-slate-800"><span class="mr-2 text-lg">' . htmlspecialchars($icono) . '</span> ' . htmlspecialchars($fila['menu_item_name']) . '</td>';
            echo '<td class="px-6 py-4 uppercase text-[10px] tracking-wider font-bold text-slate-500">' . htmlspecialchars($fila['categoria']) . '</td>';
            echo '<td class="px-6 py-4 text-slate-500 text-xs truncate max-w-[200px]" title="'.htmlspecialchars($fila['menu_url']).'">' . htmlspecialchars($fila['menu_url']) . '</td>';
            echo '<td class="px-6 py-4 text-slate-500 text-xs">' . htmlspecialchars($fila['url_target']) . '</td>';
            echo '<td class="px-6 py-4"><span class="px-3 py-1 rounded-full text-xs font-semibold border ' . $badgeColor . '">' . $fav_texto . '</span></td>';
            echo '<td class="px-6 py-4 text-right flex justify-end gap-4 opacity-0 group-hover:opacity-100 transition-opacity">';
            echo '<a href="?Actualizar=' . urlencode($fila['menu_item_id']) . '" class="text-blue-600 hover:text-blue-800 flex items-center gap-1" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>';
            echo '<button onclick="confirmarEliminacion(\'' . htmlspecialchars($fila['menu_item_id']) . '\')" class="text-red-500 hover:text-red-700 flex items-center gap-1" title="Eliminar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>';
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

            // Números de Página (Lógica de ventana simple)
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
    $fav           = (int)$_POST['fav'];
    $menu_parent_id   = (int)$_POST['menu_parent_id'];

    $sql = "INSERT INTO menu_items2 (menu_item_name, menu_description, menu_url, menu_parent_id, url_target, categoria, icono, fav) 
            VALUES ('$menu_item_name', '$menu_description', '$menu_url', $menu_parent_id, '$url_target', '$categoria', '$icono', $fav)";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Registrado!', 'El ítem de menú ha sido creado con éxito.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'Ocurrió un error al procesar la solicitud: ".$mysqli->error."', 'error');";
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
    $fav           = (int)$_POST['fav'];
    $menu_parent_id   = (int)$_POST['menu_parent_id'];
    
    $sql = "UPDATE menu_items2 
            SET menu_item_name='$menu_item_name', 
                menu_description='$menu_description', 
                menu_url='$menu_url', 
                menu_parent_id=$menu_parent_id, 
                url_target='$url_target', 
                categoria='$categoria', 
                icono='$icono', 
                fav=$fav 
            WHERE menu_item_id=$id_original";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Actualizado!', 'Los datos del menú se modificaron correctamente.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'No se pudo actualizar la información.', 'error');";
    }
}

// C. ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM menu_items2 WHERE menu_item_id = $id";
    
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Eliminado!', 'El ítem de menú ha sido retirado del sistema.', 'success');";
    }
}

// =================================================================
// 4. RECUPERAR DATOS PARA EDICIÓN
// =================================================================
$modo_edicion = false;
$datos_editar = [
    'menu_item_id' => '', 'menu_item_name' => '', 'menu_description' => '', 'menu_url' => '', 
    'categoria' => '', 'icono' => '', 'url_target' => '_self', 'fav' => 1, 'menu_parent_id' => 0
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
                
                <form method="POST" action="gestor_menu.php" class="space-y-6">
                    <?php if($modo_edicion): ?>
                        <input type="hidden" name="menu_item_id_original" value="<?php echo htmlspecialchars($datos_editar['menu_item_id']); ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Fila 1 -->
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre del Ítem <span class="text-red-500">*</span></label>
                            <input type="text" name="menu_item_name" required placeholder="Ej. Reporte de horas"
                                   value="<?php echo htmlspecialchars($datos_editar['menu_item_name']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">URL / Enlace <span class="text-red-500">*</span></label>
                            <input type="text" name="menu_url" required placeholder="/asistencia/reporte.php"
                                   value="<?php echo htmlspecialchars($datos_editar['menu_url']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <!-- Fila 2 -->
                        <div class="lg:col-span-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Descripción (Opcional)</label>
                            <input type="text" name="menu_description" placeholder="Breve descripción del módulo para accesibilidad"
                                   value="<?php echo htmlspecialchars($datos_editar['menu_description']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <!-- Fila 3 -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Categoría <span class="text-red-500">*</span></label>
                            <input type="text" name="categoria" required placeholder="Ej. Reportes, Usuarios"
                                   value="<?php echo htmlspecialchars($datos_editar['categoria']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Ícono (Emoji/Clase)</label>
                            <input type="text" name="icono" placeholder="Ej. 📄 o fa-solid fa-user"
                                   value="<?php echo htmlspecialchars($datos_editar['icono']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Destino (Target)</label>
                            <select name="url_target" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                <option value="_self" <?php echo ($datos_editar['url_target'] == '_self') ? 'selected' : ''; ?>>Misma Pestaña (_self)</option>
                                <option value="_blank" <?php echo ($datos_editar['url_target'] == '_blank') ? 'selected' : ''; ?>>Nueva Pestaña (_blank)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Padre ID</label>
                                <input type="number" name="menu_parent_id" placeholder="0 = Raíz"
                                       value="<?php echo htmlspecialchars($datos_editar['menu_parent_id']); ?>"
                                       class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">fav</label>
                                <select name="fav" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer">
                                    <option value="1" <?php echo ($datos_editar['fav'] == 1 || $datos_editar['fav'] == '1') ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo ($datos_editar['fav'] == 0 || $datos_editar['fav'] == '0' && $datos_editar['fav'] != '') ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <?php if($modo_edicion): ?>
                            <a href="gestor_menu.php" class="text-slate-700 bg-slate-100 hover:bg-slate-200 font-semibold rounded-xl text-sm px-6 py-3 transition-all">Cancelar</a>
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
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            Directorio de Menús
                            <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-0.5 rounded-full font-semibold">En tiempo real</span>
                        </h2>
                        
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
                
                <!-- Linea de carga decorativa -->
                <div id="loaderLine" class="loader-line hidden"></div>

                <!-- Contenedor dinámico de la tabla y paginación -->
                <div id="tableContainer" class="flex-1 bg-slate-50/50 p-6">
                    <?php renderizar_tabla_menu('', 1, $mysqli); ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        // 1. Ejecución de Alertas SweetAlert2 (desde PHP)
        <?php echo $alerta_js; ?>

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
                    window.location.href = `gestor_menu.php?action=delete&id=${encodeURIComponent(id)}`;
                }
            })
        }

        // 3. Lógica de Búsqueda Asíncrona y Paginación
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const tableContainer = document.getElementById('tableContainer');
            const searchSpinner = document.getElementById('searchSpinner');
            const loaderLine = document.getElementById('loaderLine');
            let debounceTimer;
            let currentPage = 1;

            // Función principal para obtener los datos
            const fetchData = async (query, page) => {
                // Mostrar UI de carga
                searchSpinner.classList.remove('hidden');
                loaderLine.classList.remove('hidden');
                tableContainer.style.opacity = '0.6'; 

                try {
                    const response = await fetch(`gestor_menu.php?ajax_search=${encodeURIComponent(query)}&page=${page}`);
                    if (!response.ok) throw new Error('Error en la red');
                    
                    const htmlString = await response.text();
                    tableContainer.innerHTML = htmlString;
                } catch (error) {
                    console.error('Error durante la solicitud:', error);
                    Swal.fire({
                        toast: true, position: 'top-end', showConfirmButton: false, 
                        timer: 3000, icon: 'error', title: 'Error de conexión'
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
                currentPage = 1; // Reiniciar a la página 1 al buscar
                
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchData(query, currentPage);
                }, 400); 
            });

            // Delegación de eventos para la paginación (ya que los botones se re-renderizan)
            tableContainer.addEventListener('click', function(e) {
                const btn = e.target.closest('.page-link');
                if (btn) {
                    e.preventDefault();
                    const targetPage = btn.getAttribute('data-page');
                    if (targetPage) {
                        currentPage = targetPage;
                        fetchData(searchInput.value.trim(), currentPage);
                    }
                }
            });
        });
    </script>
</body>
</html>