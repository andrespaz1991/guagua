<?php
/**
 * =================================================================
 * MÓDULO CRUD - GESTIÓN DE INSTITUCIONES EDUCATIVAS (Sede Vallesol)
 * =================================================================
 * Arquitectura:
 * - Tabla maestra: institucion_educativa
 * - Búsqueda Asíncrona + Paginación dinámica (Fetch API)
 * - Ordenamiento de columnas (Click on headers)
 * - Persistencia: $mysqli->query() con validación real_escape_string.
 * - UX/UI: Layout Stacked (Formulario Superior / Tabla Inferior).
 */

ob_start();
session_start();

// Inclusión de dependencias
require_once("../comun/conexion.php");

// =================================================================
// 1. ENDPOINT PARA BÚSQUEDA ASÍNCRONA, ORDENAMIENTO Y PAGINACIÓN
// =================================================================
if (isset($_GET['ajax_search'])) {
    $busqueda = trim($_GET['ajax_search']);
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Variables de ordenamiento
    $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id_institucion_educativa';
    $sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'DESC' ? 'DESC' : 'ASC'; 
    
    renderizar_tabla_instituciones($busqueda, $pagina, $sort_by, $sort_order, $mysqli);
    exit;
}

// =================================================================
// 2. FUNCIÓN DE RENDERIZADO DE TABLA (Paginación + Sortable)
// =================================================================
function renderizar_tabla_instituciones($busqueda, $pagina, $sort_by, $sort_order, $mysqli) {
    $limite = 10; 
    if ($pagina < 1) $pagina = 1;
    $offset = ($pagina - 1) * $limite;
    
    $busqueda_segura = $mysqli->real_escape_string($busqueda);
    $termino = "%" . $busqueda_segura . "%";
    
    // Diccionario de columnas permitidas (Prevención de Inyección SQL en ORDER BY)
    $columnas_permitidas = [
        'id_institucion_educativa' => 'id_institucion_educativa',
        'nombre_institucion'       => 'nombre_institucion',
        'direccion_institucion'    => 'direccion_institucion',
        'telefono_institucion'     => 'telefono_institucion',
        'correo_institucion'       => 'correo_institucion'
    ];

    if (!array_key_exists($sort_by, $columnas_permitidas)) {
        $sort_by = 'id_institucion_educativa';
    }
    
    $columna_orden = $columnas_permitidas[$sort_by];

    // Contar total (usando LIKE puro, aprovechando la insensibilidad del collation de BD)
    $sql_count = "SELECT COUNT(*) as total 
                  FROM institucion_educativa 
                  WHERE CAST(id_institucion_educativa AS CHAR) LIKE '$termino' 
                     OR nombre_institucion LIKE '$termino' 
                     OR direccion_institucion LIKE '$termino'
                     OR CAST(telefono_institucion AS CHAR) LIKE '$termino'
                     OR correo_institucion LIKE '$termino'";
    
    $res_count = $mysqli->query($sql_count);
    
    // Manejo de Errores SQL
    if (!$res_count) {
        echo '<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200"><strong>Error SQL (Count):</strong> ' . htmlspecialchars($mysqli->error) . '</div>';
        return;
    }

    $total_registros = $res_count->fetch_assoc()['total'];
    $total_paginas = ceil($total_registros / $limite);

    // Consulta principal
    $sql = "SELECT id_institucion_educativa, nombre_institucion, logo_institucion, 
                   direccion_institucion, telefono_institucion, correo_institucion
            FROM institucion_educativa 
            WHERE CAST(id_institucion_educativa AS CHAR) LIKE '$termino' 
               OR nombre_institucion LIKE '$termino' 
               OR direccion_institucion LIKE '$termino'
               OR CAST(telefono_institucion AS CHAR) LIKE '$termino'
               OR correo_institucion LIKE '$termino'
            ORDER BY $columna_orden $sort_order 
            LIMIT $limite OFFSET $offset";
            
    $resultado = $mysqli->query($sql);
    
    if (!$resultado) {
        echo '<div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200"><strong>Error SQL (Select):</strong> ' . htmlspecialchars($mysqli->error) . '</div>';
        return;
    }

    // Función auxiliar para renderizar encabezados ordenables
    $crear_header = function($columna_key, $etiqueta) use ($sort_by, $sort_order) {
        $icono = '↕️';
        $claseActiva = 'text-slate-500';
        if ($sort_by === $columna_key) {
            $icono = $sort_order === 'ASC' ? '↑' : '↓';
            $claseActiva = 'text-blue-600 font-bold';
        }
        return "<th class='px-6 py-4 cursor-pointer sortable hover:bg-slate-200 transition-colors select-none $claseActiva' data-sort='$columna_key'>
                    $etiqueta <span class='inline-block ml-1 opacity-70'>$icono</span>
                </th>";
    };

    if ($resultado->num_rows > 0) {
        echo '<div class="overflow-x-auto w-full min-h-[400px] flex flex-col justify-between">';
        echo '<table class="w-full text-sm text-left text-gray-600 relative">';
        echo '<thead class="text-xs uppercase bg-slate-100 shadow-sm z-10 border-b border-slate-200">';
        echo '<tr>';
        echo $crear_header('id_institucion_educativa', 'ID');
        echo '<th class="px-6 py-4">Logo</th>';
        echo $crear_header('nombre_institucion', 'Nombre Institución');
        echo $crear_header('direccion_institucion', 'Dirección');
        echo $crear_header('telefono_institucion', 'Teléfono');
        echo $crear_header('correo_institucion', 'Correo Electrónico');
        echo '<th class="px-6 py-4 text-right">Acciones</th>';
        echo '</tr>';
        echo '</thead><tbody class="divide-y divide-gray-200">';
        
        while ($fila = $resultado->fetch_assoc()) {
            $logo = !empty($fila['logo_institucion']) ? $fila['logo_institucion'] : 'https://via.placeholder.com/40';
            
            echo '<tr class="bg-white hover:bg-blue-50 transition-colors duration-200 group">';
            echo '<td class="px-6 py-4 font-bold text-slate-900 whitespace-nowrap">#' . htmlspecialchars($fila['id_institucion_educativa']) . '</td>';
            echo '<td class="px-6 py-4"><img src="' . htmlspecialchars($logo) . '" alt="Logo" class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm" onerror="this.src=\'https://via.placeholder.com/40\'"></td>';
            echo '<td class="px-6 py-4 font-semibold text-slate-800">' . htmlspecialchars($fila['nombre_institucion']) . '</td>';
            echo '<td class="px-6 py-4 text-slate-600 truncate max-w-[200px]" title="'.htmlspecialchars($fila['direccion_institucion']).'">' . htmlspecialchars($fila['direccion_institucion']) . '</td>';
            echo '<td class="px-6 py-4 text-slate-600">' . htmlspecialchars($fila['telefono_institucion']) . '</td>';
            echo '<td class="px-6 py-4 text-blue-600 font-medium">' . htmlspecialchars($fila['correo_institucion']) . '</td>';
            echo '<td class="px-6 py-4 text-right flex justify-end gap-4 opacity-0 group-hover:opacity-100 transition-opacity">';
            echo '<a href="?Actualizar=' . urlencode($fila['id_institucion_educativa']) . '" class="text-blue-600 hover:text-blue-800 flex items-center gap-1" title="Editar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>';
            echo '<button onclick="confirmarEliminacion(\'' . htmlspecialchars($fila['id_institucion_educativa']) . '\')" class="text-red-500 hover:text-red-700 flex items-center gap-1" title="Eliminar"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';

        // Paginación
        if ($total_paginas > 1) {
            echo '<div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4">';
            echo '<div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">';
            echo '<div><p class="text-sm text-gray-700">Página <span class="font-bold">'.$pagina.'</span> de <span class="font-bold">'.$total_paginas.'</span> (<span class="font-medium">'.$total_registros.'</span> registros)</p></div>';
            echo '<div><nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">';
            
            if ($pagina > 1) {
                echo '<button data-page="'.($pagina - 1).'" class="page-link relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"><span class="sr-only">Anterior</span><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg></button>';
            }

            $inicio = max(1, $pagina - 2);
            $fin = min($total_paginas, $pagina + 2);
            for ($i = $inicio; $i <= $fin; $i++) {
                if ($i == $pagina) {
                    echo '<button aria-current="page" class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white">'.$i.'</button>';
                } else {
                    echo '<button data-page="'.$i.'" class="page-link relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">'.$i.'</button>';
                }
            }

            if ($pagina < $total_paginas) {
                echo '<button data-page="'.($pagina + 1).'" class="page-link relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"><span class="sr-only">Siguiente</span><svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg></button>';
            }

            echo '</nav></div></div></div>';
        }
        echo '</div>';
    } else {
        echo '<div class="flex flex-col items-center justify-center p-12 text-center bg-slate-50 rounded-lg border border-dashed border-slate-300">';
        echo '<svg class="w-12 h-12 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>';
        echo '<h3 class="text-lg font-medium text-slate-900">No se encontraron instituciones</h3>';
        echo '<p class="text-sm text-slate-500 mt-1">Ajuste los criterios de búsqueda o registre una nueva institución educativa.</p>';
        echo '</div>';
    }
}

// =================================================================
// 3. LÓGICA DE TRANSACCIONES CRUD
// =================================================================
$alerta_js = '';

// A. CREAR
if (isset($_POST['Ingresar'])) {
    $nombre    = $mysqli->real_escape_string(trim($_POST['nombre_institucion']));
    $logo      = $mysqli->real_escape_string(trim($_POST['logo_institucion']));
    $direccion = $mysqli->real_escape_string(trim($_POST['direccion_institucion']));
    $telefono  = $mysqli->real_escape_string(trim($_POST['telefono_institucion']));
    $correo    = $mysqli->real_escape_string(trim($_POST['correo_institucion']));

    // ID suele ser Auto-Incrementable, por lo que no se incluye en el INSERT explícitamente a menos que lo desees manual
    $sql = "INSERT INTO institucion_educativa (nombre_institucion, logo_institucion, direccion_institucion, telefono_institucion, correo_institucion) 
            VALUES ('$nombre', '$logo', '$direccion', '$telefono', '$correo')";
            
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Registrado!', 'La institución se guardó con éxito.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'Ocurrió un error al registrar: ".$mysqli->error."', 'error');";
    }
}

// B. ACTUALIZAR
if (isset($_POST['Actualizar']) && !empty($_POST['id_institucion_educativa'])) {
    $id_original = (int)$_POST['id_institucion_educativa'];
    $nombre      = $mysqli->real_escape_string(trim($_POST['nombre_institucion']));
    $logo        = $mysqli->real_escape_string(trim($_POST['logo_institucion']));
    $direccion   = $mysqli->real_escape_string(trim($_POST['direccion_institucion']));
    $telefono    = $mysqli->real_escape_string(trim($_POST['telefono_institucion']));
    $correo      = $mysqli->real_escape_string(trim($_POST['correo_institucion']));
    
    $sql = "UPDATE institucion_educativa 
            SET nombre_institucion='$nombre',
                logo_institucion='$logo',
                direccion_institucion='$direccion', 
                telefono_institucion='$telefono', 
                correo_institucion='$correo'
            WHERE id_institucion_educativa=$id_original";
            #echo $sql;
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Actualizado!', 'La institución se ha modificado correctamente.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Error!', 'No se pudo actualizar la información.', 'error');";
    }
}

// C. ELIMINAR
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM institucion_educativa WHERE id_institucion_educativa = $id";
    
    if ($mysqli->query($sql)) {
        $alerta_js = "Swal.fire('¡Eliminado!', 'La institución ha sido retirada del sistema.', 'success');";
    } else {
        $alerta_js = "Swal.fire('¡Atención!', 'No se puede eliminar porque está referenciada en otras tablas.', 'warning');";
    }
}

// =================================================================
// 4. RECUPERAR DATOS PARA EDICIÓN
// =================================================================
$modo_edicion = false;
$datos_editar = [
    'id_institucion_educativa' => '', 'nombre_institucion' => '', 'logo_institucion' => '', 
    'direccion_institucion' => '', 'telefono_institucion' => '', 'correo_institucion' => ''
];

if (isset($_GET['Actualizar']) && !empty($_GET['Actualizar'])) {
    $modo_edicion = true;
    $id_editar = (int)$_GET['Actualizar'];
    
    $sql_edit = "SELECT * FROM institucion_educativa WHERE id_institucion_educativa = $id_editar";
    $res_edit = $mysqli->query($sql_edit);
    
    if ($res_edit && $res_edit->num_rows > 0) {
        $datos_editar = array_merge($datos_editar, $res_edit->fetch_assoc());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Instituciones - Vallesol</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .loader-line {
            height: 3px; width: 100%; background-color: #e2e8f0; overflow: hidden; position: relative;
        }
        .loader-line::before {
            content: ''; position: absolute; left: -50%; height: 3px; width: 40%;
            background-color: #3b82f6; animation: lineAnim 1s linear infinite;
        }
        @keyframes lineAnim { 0% { left: -40%; } 100% { left: 100%; } }
    </style>
</head>
<body class="antialiased text-slate-800">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Directorio de Instituciones</h1>
            <p class="text-slate-500 text-sm mt-2">Administre las sedes y recintos de formación académica asociados a la red educativa.</p>
        </div>

        <div class="flex flex-col gap-8">
            
            <!-- BLOQUE SUPERIOR: Formulario -->
            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-slate-200 w-full transition-all duration-300">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <div class="p-2 <?php echo $modo_edicion ? 'bg-amber-100 text-amber-600' : 'bg-blue-100 text-blue-600'; ?> rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $modo_edicion ? 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' : 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'; ?>"></path></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">
                        <?php echo $modo_edicion ? 'Modificar Institución #' . $datos_editar['id_institucion_educativa'] : 'Añadir Nueva Institución'; ?>
                    </h2>
                </div>
                
                <form method="POST" action="institucion_educativa.php" class="space-y-6">
                    <?php if($modo_edicion): ?>
                        <input type="hidden" name="id_institucion_educativa" value="<?php echo htmlspecialchars($datos_editar['id_institucion_educativa']); ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre Institución <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_institucion" required placeholder="Ej. IER La Josefina Sede Vallesol"
                                   value="<?php echo htmlspecialchars($datos_editar['nombre_institucion']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">URL Logo (Imagen)</label>
                            <input type="text" name="logo_institucion" placeholder="https://..."
                                   value="<?php echo htmlspecialchars($datos_editar['logo_institucion']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Dirección Física</label>
                            <input type="text" name="direccion_institucion" placeholder="Dirección de la sede..."
                                   value="<?php echo htmlspecialchars($datos_editar['direccion_institucion']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Teléfono de Contacto</label>
                            <input type="number" name="telefono_institucion" placeholder="Ej. 3101234567"
                                   value="<?php echo htmlspecialchars($datos_editar['telefono_institucion']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Correo Electrónico</label>
                            <input type="email" name="correo_institucion" placeholder="contacto@institucion.edu.co"
                                   value="<?php echo htmlspecialchars($datos_editar['correo_institucion']); ?>"
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                        <?php if($modo_edicion): ?>
                            <a href="institucion_educativa.php" class="text-slate-700 bg-slate-100 hover:bg-slate-200 font-semibold rounded-xl text-sm px-6 py-3 transition-all">Cancelar</a>
                            <button type="submit" name="Actualizar" class="text-white bg-blue-600 hover:bg-blue-700 font-semibold rounded-xl text-sm px-8 py-3 shadow-sm hover:shadow transition-all">Guardar Cambios</button>
                        <?php else: ?>
                            <button type="submit" name="Ingresar" class="text-white bg-slate-900 hover:bg-slate-800 font-semibold rounded-xl text-sm px-8 py-3 shadow-sm hover:shadow transition-all">Registrar Institución</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- BLOQUE INFERIOR: Listado, Búsqueda y Ordenamiento -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col w-full">
                
                <!-- Header Buscador -->
                <div class="p-6 border-b border-slate-100 bg-white z-20">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                                Instituciones Registradas
                                <span class="bg-blue-100 text-blue-700 text-xs px-2.5 py-0.5 rounded-full font-semibold">En tiempo real</span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-1">💡 Haz clic en los encabezados de la tabla para ordenar.</p>
                        </div>
                        
                        <div class="relative w-full sm:w-[450px]">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" id="searchInput" placeholder="Buscar por nombre, correo, teléfono o dirección..." 
                                   class="block w-full py-2.5 pl-10 pr-10 text-sm text-slate-900 border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-blue-500 focus:bg-white focus:border-blue-500 outline-none transition-all shadow-inner">
                            <div id="searchSpinner" class="absolute inset-y-0 right-0 flex items-center pr-3 hidden">
                                <svg class="animate-spin h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div id="loaderLine" class="loader-line hidden"></div>

                <!-- Contenedor dinámico de la tabla y paginación -->
                <div id="tableContainer" class="flex-1 bg-slate-50/50 p-6">
                    <?php renderizar_tabla_instituciones('', 1, 'id_institucion_educativa', 'ASC', $mysqli); ?>
                </div>
            </div>

        </div>
    </div>

    <script>
        // 1. Alertas
        <?php echo $alerta_js; ?>

        // 2. Eliminación (Manejo integral de referencias)
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Se borrará permanentemente la institución del registro.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `institucion_educativa.php?action=delete&id=${encodeURIComponent(id)}`;
                }
            })
        }

        // 3. Lógica Frontend AJAX: Búsqueda, Ordenamiento y Paginación
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const tableContainer = document.getElementById('tableContainer');
            const searchSpinner = document.getElementById('searchSpinner');
            const loaderLine = document.getElementById('loaderLine');
            
            let debounceTimer;
            let currentPage = 1;
            let currentSortBy = 'id_institucion_educativa';
            let currentSortOrder = 'ASC';

            const fetchData = async () => {
                searchSpinner.classList.remove('hidden');
                loaderLine.classList.remove('hidden');
                tableContainer.style.opacity = '0.6'; 

                const query = searchInput.value.trim();
                const url = `institucion_educativa.php?ajax_search=${encodeURIComponent(query)}&page=${currentPage}&sort_by=${currentSortBy}&sort_order=${currentSortOrder}`;

                try {
                    const response = await fetch(url);
                    if (!response.ok) throw new Error('Error en la red');
                    tableContainer.innerHTML = await response.text();
                } catch (error) {
                    console.error('Error fetching data:', error);
                    Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, icon: 'error', title: 'Error de conexión' });
                } finally {
                    searchSpinner.classList.add('hidden');
                    loaderLine.classList.add('hidden');
                    tableContainer.style.opacity = '1';
                }
            };

            // Búsqueda (Debounce)
            searchInput.addEventListener('input', function() {
                currentPage = 1; 
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchData, 400); 
            });

            // Delegación de eventos (Clicks en Tabla: Paginación y Headers)
            tableContainer.addEventListener('click', function(e) {
                // Click en Paginación
                const btnPage = e.target.closest('.page-link');
                if (btnPage) {
                    e.preventDefault();
                    const targetPage = btnPage.getAttribute('data-page');
                    if (targetPage) {
                        currentPage = parseInt(targetPage);
                        fetchData();
                    }
                }

                // Click en Encabezado Ordenable
                const thSort = e.target.closest('.sortable');
                if (thSort) {
                    const sortBy = thSort.getAttribute('data-sort');
                    if (currentSortBy === sortBy) {
                        currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
                    } else {
                        currentSortBy = sortBy;
                        currentSortOrder = 'ASC'; // reset a Ascendente al cambiar columna
                    }
                    currentPage = 1; // Volver a pagina 1 al reordenar
                    fetchData();
                }
            });
        });
    </script>
</body>
</html>