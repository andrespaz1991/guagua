<?php
/**
 * =================================================================
 * MÓDULO CRUD - GESTIÓN DE PERIODOS ACADÉMICOS (Sede Vallesol)
 * =================================================================
 * Arquitectura:
 * - Tabla maestra: periodo
 * - Búsqueda Asíncrona + Paginación dinámica (Fetch API)
 * - Ordenamiento de columnas (Click on headers)
 * - Persistencia: $mysqli->query()
 * - Regla de Negocio: Estado exclusivo (Solo 1 periodo activo a la vez)
 * - UX/UI: Layout Stacked (Formulario Lateral / Tabla Principal) con Tailwind CSS
 */

ob_start();
session_start();

// Inclusión de dependencias (Ajusta la ruta según tu estructura)
require_once("../comun/conexion.php");

// =================================================================
// 1. ENDPOINT PARA BÚSQUEDA ASÍNCRONA, ORDENAMIENTO Y PAGINACIÓN
// =================================================================
if (isset($_GET['ajax_search'])) {
    $busqueda = trim($_GET['ajax_search']);
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    // Variables de ordenamiento con validación (Whitelist) para evitar Inyección SQL
    $columnas_permitidas = ['id_periodo', 'nombre_periodo', 'descripcion_periodo', 'fecha_inicio', 'fecha_fin', 'ano_lectivo', 'estado_periodo'];
    $sort_by = isset($_GET['sort_by']) && in_array($_GET['sort_by'], $columnas_permitidas) ? $_GET['sort_by'] : 'id_periodo';
    $sort_order = isset($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'DESC' ? 'DESC' : 'ASC'; 
    
    renderizar_tabla_periodos($busqueda, $pagina, $sort_by, $sort_order);
    exit;
}

// =================================================================
// 2. CONTROLADOR DE OPERACIONES CRUD (POST)
// =================================================================
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    
    // Saneamiento de datos de entrada
    $id_periodo     = isset($_POST['id_periodo']) ? (int)$_POST['id_periodo'] : 0;
    $nombre         = isset($_POST['nombre_periodo']) ? $mysqli->real_escape_string(trim($_POST['nombre_periodo'])) : '';
    $descripcion    = isset($_POST['descripcion_periodo']) ? $mysqli->real_escape_string(trim($_POST['descripcion_periodo'])) : '';
    $inicio         = isset($_POST['fecha_inicio']) ? $mysqli->real_escape_string(trim($_POST['fecha_inicio'])) : '';
    $fin            = isset($_POST['fecha_fin']) ? $mysqli->real_escape_string(trim($_POST['fecha_fin'])) : '';
    $ano_lectivo    = isset($_POST['ano_lectivo']) ? $mysqli->real_escape_string(trim($_POST['ano_lectivo'])) : '';
    $estado_periodo = isset($_POST['estado_periodo']) && $_POST['estado_periodo'] === '1' ? '1' : '0';

    // REGLA DE NEGOCIO (EXCLUSIVIDAD): Si este periodo entra como activo, desactivamos todos los demás primero
    if (($accion === 'crear' || $accion === 'editar') && $estado_periodo === '1') {
        $mysqli->query("UPDATE periodo SET estado_periodo = '0'");
    }

    if ($accion === 'crear') {
        $sql = "INSERT INTO periodo (nombre_periodo, descripcion_periodo, fecha_inicio, fecha_fin, ano_lectivo, estado_periodo) 
                VALUES ('$nombre', '$descripcion', '$inicio', '$fin', '$ano_lectivo', '$estado_periodo')";
        if ($mysqli->query($sql)) {
            $mensaje = "Periodo registrado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al registrar el periodo: " . $mysqli->error;
            $tipo_mensaje = "error";
        }
    } elseif ($accion === 'editar' && $id_periodo > 0) {
        $sql = "UPDATE periodo SET 
                nombre_periodo='$nombre', 
                descripcion_periodo='$descripcion', 
                fecha_inicio='$inicio', 
                fecha_fin='$fin', 
                ano_lectivo='$ano_lectivo',
                estado_periodo='$estado_periodo'
                WHERE id_periodo=$id_periodo";
        if ($mysqli->query($sql)) {
            $mensaje = "Periodo actualizado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar el periodo: " . $mysqli->error;
            $tipo_mensaje = "error";
        }
    } elseif ($accion === 'activar' && $id_periodo > 0) {
        // Desactiva todos, y activa solo el seleccionado (Acción rápida)
        $mysqli->query("UPDATE periodo SET estado_periodo = '0'");
        $sql = "UPDATE periodo SET estado_periodo = '1' WHERE id_periodo=$id_periodo";
        if ($mysqli->query($sql)) {
            $mensaje = "Periodo activado como actual correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al activar el periodo: " . $mysqli->error;
            $tipo_mensaje = "error";
        }
    } elseif ($accion === 'eliminar' && $id_periodo > 0) {
        $sql = "DELETE FROM periodo WHERE id_periodo=$id_periodo";
        if ($mysqli->query($sql)) {
            $mensaje = "Periodo eliminado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al eliminar el periodo: " . $mysqli->error;
            $tipo_mensaje = "error";
        }
    }
}

// =================================================================
// 3. FUNCIÓN DE RENDERIZADO ASÍNCRONO DE LA TABLA
// =================================================================
function renderizar_tabla_periodos($busqueda, $pagina, $sort_by, $sort_order) {
    global $mysqli;
    
    $registros_por_pagina = 10;
    $offset = ($pagina - 1) * $registros_por_pagina;
    $busqueda_limpia = $mysqli->real_escape_string($busqueda);
    
    // Condición de búsqueda
    $where = "";
    if (!empty($busqueda_limpia)) {
        $where = "WHERE nombre_periodo LIKE '%$busqueda_limpia%' 
                  OR descripcion_periodo LIKE '%$busqueda_limpia%' 
                  OR ano_lectivo LIKE '%$busqueda_limpia%'";
    }
    
    // Total de registros para paginación
    $sql_total = "SELECT COUNT(*) as total FROM periodo $where";
    $result_total = $mysqli->query($sql_total);
    $fila_total = $result_total->fetch_assoc();
    $total_registros = $fila_total['total'];
    $total_paginas = ceil($total_registros / $registros_por_pagina);
    
    // Consulta principal con ordenamiento y paginación
    $sql = "SELECT * FROM periodo $where ORDER BY $sort_by $sort_order LIMIT $offset, $registros_por_pagina";
    $resultado = $mysqli->query($sql);
    
    // Renderizado de la tabla HTML
    echo '<div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">';
    echo '<table class="min-w-full divide-y divide-slate-200 bg-white">';
    echo '<thead class="bg-slate-50">';
    echo '<tr>';
    
    // Función auxiliar para renderizar cabeceras ordenables
    $crear_cabecera = function($columna, $etiqueta) use ($sort_by, $sort_order) {
        $icon = 'fa-sort text-slate-300';
        if ($sort_by === $columna) {
            $icon = $sort_order === 'ASC' ? 'fa-sort-up text-indigo-500' : 'fa-sort-down text-indigo-500';
        }
        return "<th class='px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer hover:bg-slate-100 transition-colors sortable group' data-sort='$columna'>
                    <div class='flex items-center gap-2'>$etiqueta <i class='fa-solid $icon group-hover:text-indigo-400'></i></div>
                </th>";
    };

    echo $crear_cabecera('id_periodo', 'ID');
    echo $crear_cabecera('estado_periodo', 'Estado');
    echo $crear_cabecera('nombre_periodo', 'Periodo');
    echo $crear_cabecera('fecha_inicio', 'Inicio');
    echo $crear_cabecera('fecha_fin', 'Fin');
    echo $crear_cabecera('ano_lectivo', 'Año');
    echo '<th class="px-4 py-3 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>';
    echo '</tr></thead>';
    echo '<tbody class="divide-y divide-slate-100">';
    
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            // Empaquetar datos para el botón de edición
            $datos_json = htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8');
            $es_activo = (isset($fila['estado_periodo']) && $fila['estado_periodo'] === '1');
            
            // Estilo dinámico de la fila si está activo
            $row_class = $es_activo ? 'bg-emerald-50/30 hover:bg-emerald-50/60' : 'hover:bg-slate-50';

            echo "<tr class='$row_class transition-colors'>";
            echo "<td class='px-4 py-3 text-sm text-slate-600 font-mono'>#{$fila['id_periodo']}</td>";
            
            // Columna Estado
            echo "<td class='px-4 py-3 text-sm'>";
            if ($es_activo) {
                echo "<span class='inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm'>
                        <span class='w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse'></span> Activo
                      </span>";
            } else {
                echo "<span class='inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200'>
                        Inactivo
                      </span>";
            }
            echo "</td>";

            echo "<td class='px-4 py-3 text-sm font-bold text-slate-800'>{$fila['nombre_periodo']}</td>";
            echo "<td class='px-4 py-3 text-sm text-slate-600'><i class='fa-regular fa-calendar text-slate-400 mr-1'></i> {$fila['fecha_inicio']}</td>";
            echo "<td class='px-4 py-3 text-sm text-slate-600'><i class='fa-regular fa-calendar-check text-slate-400 mr-1'></i> {$fila['fecha_fin']}</td>";
            echo "<td class='px-4 py-3 text-sm font-semibold text-indigo-600 bg-indigo-50/50 text-center rounded'>{$fila['ano_lectivo']}</td>";
            
            // Acciones
            echo "<td class='px-4 py-3 text-sm text-right font-medium space-x-1 whitespace-nowrap'>";
            
            // Botón de activación rápida (Solo visible si el periodo está inactivo)
            if (!$es_activo) {
                echo "<button type='button' onclick='activarPeriodo({$fila['id_periodo']})' class='text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 p-1.5 rounded-md transition-colors' title='Establecer como Activo'>
                        <i class='fa-solid fa-power-off'></i>
                      </button>";
            }

            echo "<button type='button' onclick='cargarEdicion({$datos_json})' class='text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors' title='Editar'>
                    <i class='fa-solid fa-pen-to-square'></i>
                  </button>
                  <button type='button' onclick='confirmarEliminacion({$fila['id_periodo']})' class='text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 p-1.5 rounded-md transition-colors' title='Eliminar'>
                    <i class='fa-solid fa-trash-can'></i>
                  </button>";
            echo "</td></tr>";
        }
    } else {
        echo "<tr><td colspan='7' class='px-4 py-8 text-center text-slate-500'>
                <i class='fa-solid fa-inbox text-3xl mb-2 text-slate-300'></i><br>No se encontraron periodos registrados.
              </td></tr>";
    }
    
    echo '</tbody></table></div>';
    
    // Controles de Paginación
    if ($total_paginas > 1) {
        echo '<div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4">';
        echo '<span class="text-sm text-slate-500">Mostrando página <span class="font-bold">'.$pagina.'</span> de <span class="font-bold">'.$total_paginas.'</span></span>';
        echo '<div class="flex gap-1">';
        
        for ($i = 1; $i <= $total_paginas; $i++) {
            $claseActiva = ($i == $pagina) ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200';
            echo "<button class='page-link px-3 py-1 text-sm font-medium rounded-md transition-colors $claseActiva' data-page='$i'>$i</button>";
        }
        
        echo '</div></div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Periodos - Vallesol</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Fuentes Google -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen p-4 md:p-8">

    <div class="max-w-7xl mx-auto">
        
        <!-- Cabecera de Página -->
        <div class="mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 text-white rounded-lg flex items-center justify-center text-lg shadow-md">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-slate-800 leading-none">Gestión de Periodos</h1>
                <p class="text-sm text-slate-500 font-medium mt-1">Configuración del calendario académico anual.</p>
            </div>
        </div>

        <!-- Alertas de Sistema -->
        <?php if (!empty($mensaje)): ?>
            <div class="mb-6 p-4 rounded-lg border-l-4 shadow-sm flex items-center justify-between <?= $tipo_mensaje === 'success' ? 'bg-emerald-50 border-emerald-500 text-emerald-800' : 'bg-rose-50 border-rose-500 text-rose-800' ?>">
                <div class="flex items-center gap-3">
                    <i class="fa-solid <?= $tipo_mensaje === 'success' ? 'fa-circle-check text-emerald-500' : 'fa-triangle-exclamation text-rose-500' ?> text-xl"></i>
                    <span class="font-medium"><?= $mensaje ?></span>
                </div>
                <button onclick="this.parentElement.style.display='none'" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
            </div>
        <?php endif; ?>

        <!-- Grid Principal (Formulario Izquierda / Tabla Derecha) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Panel Izquierdo: Formulario CRUD -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-6">
                    <div class="bg-slate-50 border-b border-slate-200 px-5 py-4 flex justify-between items-center">
                        <h2 class="font-bold text-slate-700 flex items-center gap-2" id="titulo-formulario">
                            <i class="fa-solid fa-plus text-indigo-500"></i> Nuevo Periodo
                        </h2>
                        <button type="button" onclick="limpiarFormulario()" class="text-xs font-semibold text-slate-500 hover:text-indigo-600 transition-colors" id="btn-cancelar" style="display: none;">
                            Cancelar Edición
                        </button>
                    </div>
                    
                    <form method="POST" action="" id="form-periodo" class="p-5 space-y-4">
                        <input type="hidden" name="accion" id="accion" value="crear">
                        <input type="hidden" name="id_periodo" id="id_periodo" value="0">

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Nombre *</label>
                                <input type="text" name="nombre_periodo" id="nombre_periodo" required placeholder="Ej: 1, 2..." 
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Año Lectivo *</label>
                                <input type="number" name="ano_lectivo" id="ano_lectivo" required min="2000" max="2100" value="<?= date('Y') ?>" 
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Fecha Inicio *</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" required 
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Fecha Fin *</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" required 
                                       class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-1">Descripción / Temática</label>
                            <textarea name="descripcion_periodo" id="descripcion_periodo" rows="2" placeholder="Ej: Democracia Seguridad..." 
                                      class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all resize-none"></textarea>
                        </div>
                        
                        <!-- Selector de Estado -->
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg">
                            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wide mb-2">Estado del Periodo</label>
                            <select name="estado_periodo" id="estado_periodo" class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium">
                                <option value="0">Inactivo (Histórico o Futuro)</option>
                                <option value="1">Activo (Periodo Actual)</option>
                            </select>
                            <p class="text-[10px] text-slate-500 mt-1.5 leading-tight"><i class="fa-solid fa-circle-info text-indigo-400"></i> Si estableces este periodo como "Activo", los demás se desactivarán automáticamente.</p>
                        </div>

                        <div class="pt-2">
                            <button type="submit" id="btn-submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md transition-all active:scale-[0.98] flex justify-center items-center gap-2">
                                <i class="fa-solid fa-save"></i> Guardar Periodo
                            </button>
                        </div>
                    </form>

                    <!-- Formularios Ocultos para Acciones de Fila -->
                    <form method="POST" action="" id="form-acciones" style="display: none;">
                        <input type="hidden" name="accion" id="form_accion_rapida" value="">
                        <input type="hidden" name="id_periodo" id="id_accion_rapida" value="0">
                    </form>
                </div>
            </div>

            <!-- Panel Derecho: Data Table Asíncrono -->
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col h-full">
                    
                    <!-- Barra de Búsqueda -->
                    <div class="mb-4 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3.5 text-slate-400"></i>
                        <input type="text" id="buscador" placeholder="Buscar por nombre, descripción o año..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition-all shadow-inner">
                        <div id="loading-indicator" class="absolute right-4 top-3.5 hidden">
                            <i class="fa-solid fa-circle-notch fa-spin text-indigo-500"></i>
                        </div>
                    </div>

                    <!-- Contenedor Dinámico de la Tabla -->
                    <div id="contenedor-tabla" class="flex-1">
                        <!-- El contenido se cargará aquí vía AJAX -->
                        <div class="flex justify-center items-center h-48 text-slate-400">
                            <i class="fa-solid fa-circle-notch fa-spin text-3xl"></i>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- ========================================== -->
    <!-- LÓGICA JAVASCRIPT (AJAX Y UI) -->
    <!-- ========================================== -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentPage = 1;
            let currentSortBy = 'id_periodo';
            let currentSortOrder = 'DESC';
            let debounceTimer;

            const buscador = document.getElementById('buscador');
            const contenedorTabla = document.getElementById('contenedor-tabla');
            const loadingIndicator = document.getElementById('loading-indicator');

            // 1. Fetch API
            const fetchData = () => {
                loadingIndicator.classList.remove('hidden');
                contenedorTabla.style.opacity = '0.6';

                const query = buscador.value.trim();
                const url = `periodo.php?ajax_search=${encodeURIComponent(query)}&page=${currentPage}&sort_by=${currentSortBy}&sort_order=${currentSortOrder}`;

                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        contenedorTabla.innerHTML = html;
                        contenedorTabla.style.opacity = '1';
                        loadingIndicator.classList.add('hidden');
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        contenedorTabla.innerHTML = '<div class="text-rose-500 p-4 text-center font-bold"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Error de conexión. Reintente.</div>';
                        loadingIndicator.classList.add('hidden');
                    });
            };

            // 2. Debounce Buscador
            buscador.addEventListener('input', function() {
                currentPage = 1; 
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchData, 400); 
            });

            // 3. Delegación (Paginación y Orden)
            contenedorTabla.addEventListener('click', function(e) {
                const btnPage = e.target.closest('.page-link');
                if (btnPage) {
                    e.preventDefault();
                    const targetPage = btnPage.getAttribute('data-page');
                    if (targetPage) {
                        currentPage = parseInt(targetPage);
                        fetchData();
                    }
                }

                const thSort = e.target.closest('.sortable');
                if (thSort) {
                    const sortBy = thSort.getAttribute('data-sort');
                    if (currentSortBy === sortBy) {
                        currentSortOrder = currentSortOrder === 'ASC' ? 'DESC' : 'ASC';
                    } else {
                        currentSortBy = sortBy;
                        currentSortOrder = 'ASC'; 
                    }
                    currentPage = 1; 
                    fetchData();
                }
            });

            // 4. Acciones Formularios
            window.cargarEdicion = function(datos) {
                document.getElementById('accion').value = 'editar';
                document.getElementById('id_periodo').value = datos.id_periodo;
                document.getElementById('nombre_periodo').value = datos.nombre_periodo;
                document.getElementById('descripcion_periodo').value = datos.descripcion_periodo;
                document.getElementById('fecha_inicio').value = datos.fecha_inicio;
                document.getElementById('fecha_fin').value = datos.fecha_fin;
                document.getElementById('ano_lectivo').value = datos.ano_lectivo;
                document.getElementById('estado_periodo').value = (datos.estado_periodo == '1') ? '1' : '0';
                
                document.getElementById('titulo-formulario').innerHTML = '<i class="fa-solid fa-pen text-amber-500"></i> Editando Periodo #' + datos.id_periodo;
                const btnSubmit = document.getElementById('btn-submit');
                btnSubmit.innerHTML = '<i class="fa-solid fa-save"></i> Actualizar Cambios';
                btnSubmit.classList.replace('bg-indigo-600', 'bg-amber-500');
                btnSubmit.classList.replace('hover:bg-indigo-700', 'hover:bg-amber-600');
                document.getElementById('btn-cancelar').style.display = 'block';
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            window.limpiarFormulario = function() {
                document.getElementById('form-periodo').reset();
                document.getElementById('accion').value = 'crear';
                document.getElementById('id_periodo').value = '0';
                document.getElementById('estado_periodo').value = '0';
                
                document.getElementById('titulo-formulario').innerHTML = '<i class="fa-solid fa-plus text-indigo-500"></i> Nuevo Periodo';
                const btnSubmit = document.getElementById('btn-submit');
                btnSubmit.innerHTML = '<i class="fa-solid fa-save"></i> Guardar Periodo';
                btnSubmit.classList.replace('bg-amber-500', 'bg-indigo-600');
                btnSubmit.classList.replace('hover:bg-amber-600', 'hover:bg-indigo-700');
                document.getElementById('btn-cancelar').style.display = 'none';
            };

            window.activarPeriodo = function(id) {
                if(confirm('¿Convertir en periodo actual? Los demás periodos pasarán a estado inactivo automáticamente.')) {
                    document.getElementById('id_accion_rapida').value = id;
                    document.getElementById('form_accion_rapida').value = 'activar';
                    document.getElementById('form-acciones').submit();
                }
            };

            window.confirmarEliminacion = function(id) {
                if(confirm('¿Está seguro de que desea eliminar permanentemente el Periodo #' + id + '? Esta acción no se puede deshacer.')) {
                    document.getElementById('id_accion_rapida').value = id;
                    document.getElementById('form_accion_rapida').value = 'eliminar';
                    document.getElementById('form-acciones').submit();
                }
            };

            fetchData();
        });
    </script>
</body>
</html>