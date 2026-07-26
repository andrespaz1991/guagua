<?php 
ob_start();
require("conexion.php");
require("../comun/autoload.php");

// Función principal de búsqueda y renderizado de tabla
function buscar_seguimiento($datos = '', $reporte = '') {
    global $mysqli;
    require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
    
    // Validación básica de resultados por página
    $resultados = (isset($_COOKIE['numeroresultadosseguimiento']) && is_numeric($_COOKIE['numeroresultadosseguimiento'])) ? intval($_COOKIE['numeroresultadosseguimiento']) : 10;
    
    $paginacion = new Zebra_Pagination();
    $paginacion->records_per_page($resultados);
    $cookiepage = "page_numeroresultadosseguimiento";
    $funcionjs = "buscar();";
    $paginacion->fn_js_page("$funcionjs");
    $paginacion->cookie_page($cookiepage);
    $paginacion->padding(true);
    
    if (isset($_COOKIE[$cookiepage]) && is_numeric($_COOKIE[$cookiepage])) {
        $_GET['page'] = intval($_COOKIE[$cookiepage]);
    }

    if ($reporte == "xls" || isset($_GET['xls'])) {
        header("Content-type: application/vnd.ms-excel");
        $filename = !empty($_GET['xls']) ? intval($_GET['xls']) . ".xls" : "seguimiento.xls";
        header("Content-Disposition: attachment; Filename=" . $filename);
    }

    $sql = 'SELECT * FROM seguimiento';
    $consulta = $mysqli->query($sql);
    
    // Si la tabla está vacía o hay error, evitamos error fatal
    if (!$consulta) {
        $paginacion->records(0);
    } else {
        $paginacion->records($consulta->num_rows);
    }

    $datos_array = array_filter(explode(" ", trim($datos))); // Filtramos espacios vacíos
    
    $sql .= ' WHERE 1=1 '; // Facilita la concatenación dinámica de condiciones
    
    if (!empty($_GET['xls'])) {
        // Aseguramos que sea entero para evitar inyección en el GET
        $id_xls = intval($_GET['xls']);
        $sql .= " AND seguimiento.id_seguimiento = " . $id_xls;
    } else {
        if (!empty($datos_array)) {
            $sql .= " AND (";
            $condiciones = [];
            foreach ($datos_array as $dato) {
                // Escapamos el dato para la consulta dinámica LIKE
                $dato_limpio = $mysqli->real_escape_string(mb_strtolower($dato, 'UTF-8'));
                $condiciones[] = 'concat(LOWER(seguimiento.id_seguimiento),"", LOWER(seguimiento.id_inscripcion),"", LOWER(seguimiento.fecha_seguimiento),"", LOWER(seguimiento.hora_seguimiento),"", LOWER(seguimiento.observaciones)) LIKE "%' . $dato_limpio . '%"';
            }
            $sql .= implode(' AND ', $condiciones) . ") ";
        }
        
        $sql .= ' ORDER BY seguimiento.id_seguimiento DESC ';
        
        if (!isset($_GET['xls'])) {
            $page_num = $paginacion->get_page() > 0 ? $paginacion->get_page() : 1;
            $offset = ($page_num - 1) * $resultados;
            $sql .= " LIMIT " . intval($offset) . ", " . intval($resultados);
        }
    }

    $consulta = $mysqli->query($sql);
    $numero_usuario = $consulta ? $consulta->num_rows : 0;
    
    $page_num = $paginacion->get_page() > 0 ? $paginacion->get_page() : 1;
    $minimo_usuario = (($page_num - 1) * $resultados) + ($numero_usuario > 0 ? 1 : 0);
    $maximo_usuario = (($page_num - 1) * $resultados) + $numero_usuario;
    ?>
    
    <style>
        /* Estilos para integrar Zebra_Pagination con Tailwind */
        .Zebra_Pagination { display: flex; justify-content: center; gap: 0.375rem; padding: 1.5rem; }
        .Zebra_Pagination li { list-style-type: none; }
        .Zebra_Pagination a, .Zebra_Pagination span { display: flex; align-items: center; justify-content: center; min-width: 2.25rem; height: 2.25rem; padding: 0 0.5rem; font-size: 0.875rem; border-radius: 0.5rem; transition: all 0.2s; font-weight: 500; }
        .Zebra_Pagination a { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; text-decoration: none; }
        .Zebra_Pagination a:hover { background-color: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }
        .Zebra_Pagination .navigation a { font-weight: 600; color: #475569; }
        .Zebra_Pagination .current { background-color: #4f46e5; color: white; border: 1px solid #4f46e5; box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2); }
    </style>

    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="text-sm text-slate-500 font-medium bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm w-full sm:w-auto text-center sm:text-left">
            Mostrando <span class="text-indigo-600 font-bold"><?= $minimo_usuario ?></span> a <span class="text-indigo-600 font-bold"><?= $maximo_usuario ?></span> de resultados (Pág. <?= $page_num ?>)
        </div>
        
        <?php if ($reporte == ''): ?>
        <div class="flex gap-2 w-full sm:w-auto">
            <form class="m-0 flex-1 sm:flex-none" id="formNuevo" name="formNuevo" method="post" action="citas.php">
                <input name="cod" type="hidden" id="cod" value="0">
                <button type="submit" name="submit" value="Nuevo" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <i class="fa-solid fa-plus"></i> Nuevo Registro
                </button>
            </form>
            <form class="flex items-center gap-2 m-0 flex-1 sm:flex-none" id="formExportar" name="formExportar" method="post" action="citas.php?xls">
                <input name="cod" type="hidden" id="cod" value="0">
                <button type="submit" name="submit" value="XLS" class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 rounded-xl text-sm font-bold transition-colors shadow-sm" title="Exportar a Excel">
                    <i class="fa-solid fa-file-excel"></i> XLS
                </button>
                <a target="_blank" href="reporte_seguimiento.php" class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-sm font-bold transition-colors shadow-sm" title="Exportar a PDF">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden ring-1 ring-slate-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200" id="tbseguimiento">
                <thead class="bg-slate-50/80 backdrop-blur-sm">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Detalles de Inscripción</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Fecha y Hora</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Observaciones</th>
                        <?php if ($reporte == ''): ?>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    <?php if($consulta && $consulta->num_rows > 0): ?>
                        <?php while ($row = $consulta->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold border border-indigo-100">
                                            #<?= htmlspecialchars($row['id_seguimiento'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                        <div>
                                            <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">ID Estudiante</div>
                                            <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($row['id_inscripcion'], ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                            <i class="fa-regular fa-calendar-days text-indigo-400 w-4"></i> 
                                            <?= htmlspecialchars($row['fecha_seguimiento'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="inline-flex items-center gap-2 text-sm text-slate-500">
                                            <i class="fa-regular fa-clock text-slate-400 w-4"></i> 
                                            <?= htmlspecialchars($row['hora_seguimiento'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-slate-600 line-clamp-2 max-w-md leading-relaxed" title="<?= htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                </td>
                                
                                <?php if ($reporte == ''): ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-1 opacity-100 lg:opacity-0 group-hover:opacity-100 transition-opacity">
                                            <form class="m-0" id="formModificar_<?= $row['id_seguimiento'] ?>" name="formModificar" method="post" action="citas.php">
                                                <input name="cod" type="hidden" id="cod_mod_<?= $row['id_seguimiento'] ?>" value="<?= htmlspecialchars($row['id_seguimiento'], ENT_QUOTES, 'UTF-8') ?>">
                                                <button type="submit" name="submit" value="Modificar" class="p-2.5 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors" title="Editar">
                                                    <i class="fa-solid fa-pen-to-square text-lg"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="p-2.5 text-rose-500 hover:bg-rose-50 rounded-xl transition-colors" title="Eliminar" onClick="confirmeliminar('citas.php',{'del':'<?= $row['id_seguimiento']; ?>'},'<?= $row['id_seguimiento']; ?>');">
                                                <i class="fa-solid fa-trash-can text-lg"></i>
                                            </button>
                                            <div class="w-px h-6 bg-slate-200 mx-2"></div>
                                            <a target="_blank" href="citas.php?xls=<?= $row['id_seguimiento'] ?>" class="p-2.5 text-emerald-600 hover:bg-emerald-50 rounded-xl transition-colors" title="Descargar Excel">
                                                <i class="fa-solid fa-file-excel text-lg"></i>
                                            </a>
                                            <a target="_blank" href="citas.php?id=<?= $row['id_seguimiento'] ?>" class="p-2.5 text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Descargar PDF">
                                                <i class="fa-solid fa-file-pdf text-lg"></i>
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-5 ring-8 ring-white shadow-sm border border-slate-100">
                                        <i class="fa-solid fa-folder-open text-3xl text-indigo-300"></i>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 mb-1">Sin registros</h3>
                                    <p class="text-sm text-slate-500 max-w-sm">No se han encontrado seguimientos estudiantiles que coincidan con los criterios de búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!isset($_GET['xls'])): ?>
            <div class="border-t border-slate-200 bg-slate-50/30">
                <?= $paginacion->render2(); ?>
            </div>
        <?php endif; ?>
    </div>
    <?php 
} // fin function buscar

// Rutas GET
if (isset($_GET['buscar'])) {
    buscar_seguimiento(isset($_POST['datos']) ? $_POST['datos'] : '');
    exit();
}
if (isset($_GET['xls'])) {
    buscar_seguimiento('', 'xls');
    exit();
}

$alert_html = '';

// Helper para generar notificaciones Toast animadas
function generar_toast($tipo, $titulo, $mensaje) {
    $colores = [
        'success' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => 'fa-check-circle', 'border' => 'border-emerald-500'],
        'error' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-600', 'icon' => 'fa-circle-xmark', 'border' => 'border-rose-500'],
        'info' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'fa-circle-info', 'border' => 'border-blue-500']
    ];
    $c = $colores[$tipo];
    
    return '
    <div id="toast-message" class="fixed top-8 right-8 z-50 flex items-center w-full max-w-sm p-4 text-slate-700 bg-white rounded-2xl shadow-2xl ring-1 ring-slate-900/5 border-l-4 ' . $c['border'] . ' transform transition-all duration-500 translate-x-0 opacity-100">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-12 h-12 ' . $c['bg'] . ' ' . $c['text'] . ' rounded-xl">
            <i class="fa-solid ' . $c['icon'] . ' text-xl"></i>
        </div>
        <div class="ms-4 mr-2">
            <h4 class="text-sm font-bold text-slate-900">' . $titulo . '</h4>
            <div class="text-sm font-medium text-slate-500 mt-0.5">' . $mensaje . '</div>
        </div>
        <button type="button" onclick="this.parentElement.remove()" class="ms-auto -mx-1.5 -my-1.5 bg-white text-slate-400 hover:text-slate-900 rounded-lg focus:ring-2 focus:ring-slate-300 p-1.5 hover:bg-slate-100 inline-flex items-center justify-center h-8 w-8 transition-colors">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    <script>setTimeout(() => { let t = document.getElementById("toast-message"); if(t) { t.style.opacity = "0"; t.style.transform = "translateX(20px)"; setTimeout(() => t.remove(), 500); } }, 3500);</script>
    <meta http-equiv="refresh" content="2; url=citas.php" />';
}

// Lógica de Eliminación con Consultas Preparadas
if (isset($_POST['del']) && is_numeric($_POST['del'])) {
    $stmt = $mysqli->prepare('DELETE FROM seguimiento WHERE id_seguimiento=?');
    if ($stmt) {
        $stmt->bind_param('i', $_POST['del']);
        if ($stmt->execute()) {
            $alert_html = generar_toast('success', 'Eliminado Exitosamente', 'El registro fue borrado de la base de datos.');
        } else {
            $alert_html = generar_toast('error', 'Fallo Crítico', 'No se pudo eliminar el registro seleccionado.');
        }
        $stmt->close();
    }
}

// Lógica de Inserción con Consultas Preparadas
if (isset($_POST['submit']) && $_POST['submit'] == "Registrar") {
    $stmt = $mysqli->prepare("INSERT INTO seguimiento(id_inscripcion, fecha_seguimiento, hora_seguimiento, observaciones) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('ssss', $_POST["id_inscripcion"], $_POST["fecha_seguimiento"], $_POST["hora_seguimiento"], $_POST["observaciones"]);
        if ($stmt->execute()) {
            $alert_html = generar_toast('success', 'Registro Guardado', 'Los datos del seguimiento se registraron con éxito.');
        } else {
            $alert_html = generar_toast('error', 'Error de Inserción', 'Ocurrió un problema al guardar los datos.');
        }
        $stmt->close();
    }
}

// Lógica de Actualización con Consultas Preparadas
if (isset($_POST['submit']) && $_POST['submit'] == "Actualizar" && !empty($_POST['id_seguimiento'])) {
    $stmt = $mysqli->prepare("UPDATE seguimiento SET id_inscripcion=?, fecha_seguimiento=?, hora_seguimiento=?, observaciones=? WHERE id_seguimiento=?");
    if ($stmt) {
        $stmt->bind_param('ssssi', $_POST["id_inscripcion"], $_POST["fecha_seguimiento"], $_POST["hora_seguimiento"], $_POST["observaciones"], $_POST['id_seguimiento']);
        if ($stmt->execute()) {
            $alert_html = generar_toast('info', 'Registro Actualizado', 'Las modificaciones se han aplicado correctamente.');
        } else {
            $alert_html = generar_toast('error', 'Error de Actualización', 'No se pudieron sobreescribir los datos.');
        }
        $stmt->close();
    }
}
?>

<!-- ======================= INICIO DE LA VISTA (HTML) ======================= -->
<script src="../comun/css/tailwindcss.css"></script>
<link rel="stylesheet" href="../comun/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-slate-800">
    
    <!-- Renderizar Alertas -->
    <?= $alert_html ?>

    <?php 
    // Mostrar Formulario de Nuevo o Edición
    if (isset($_POST['submit']) && ($_POST['submit'] == "Nuevo" || $_POST['submit'] == "Modificar")) {
        $row = [];
        $textoh1 = "Registrar";
        $textobtn = "Guardar Registro";
        $btn_class = "bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200";
        $icon_class = "fa-floppy-disk";
        $bg_header = "bg-indigo-50/50";
        $icon_bg = "bg-indigo-100 text-indigo-600";

        if ($_POST['submit'] == "Modificar" && isset($_POST['cod'])) {
            $stmt = $mysqli->prepare('SELECT * FROM seguimiento WHERE id_seguimiento = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $_POST['cod']);
                $stmt->execute();
                $resultado = $stmt->get_result();
                if($resultado->num_rows > 0) {
                    $row = $resultado->fetch_assoc();
                }
                $stmt->close();
            }
            $textoh1 = "Actualizar";
            $textobtn = "Guardar Cambios";
            $btn_class = "bg-blue-600 hover:bg-blue-700 shadow-blue-200";
            $icon_class = "fa-cloud-arrow-up";
            $bg_header = "bg-blue-50/50";
            $icon_bg = "bg-blue-100 text-blue-600";
        }

        $nombre = 'Nueva Anotación / Cita';
        if (!empty($_POST['estudiante'])) {
            if(class_exists('Persona')){
                $persona = new Persona($_POST['estudiante']);
                $nombre = $persona->nombre;
            } else {
                $nombre = $_POST['estudiante'];
            }
        }
    ?>
        
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/40 border border-slate-100 max-w-3xl mx-auto overflow-hidden ring-1 ring-slate-900/5">
            <div class="<?= $bg_header ?> border-b border-slate-100 px-8 py-6 flex items-center gap-5">
                <div class="h-14 w-14 rounded-2xl flex items-center justify-center shadow-sm <?= $icon_bg ?>">
                    <i class="fa-solid fa-user-pen text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900"><?= htmlspecialchars($textoh1, ENT_QUOTES, 'UTF-8') ?> Seguimiento</h1>
                    <p class="text-sm font-semibold text-slate-500 mt-1"><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>

            <form id="form1" name="form1" method="post" action="citas.php" class="p-8 space-y-7">
                <!-- Inputs Ocultos -->
                <input type="hidden" name="cod" value="<?= htmlspecialchars($_POST['submit'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id_seguimiento" value="<?= isset($row['id_seguimiento']) ? htmlspecialchars($row['id_seguimiento'], ENT_QUOTES, 'UTF-8') : '' ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-7">
                    <!-- ID Inscripción -->
                    <div class="space-y-2 md:col-span-2 lg:col-span-1">
                        <label class="block text-sm font-bold text-slate-700">ID Inscripción</label>
                        <?php 
                            $val_inscripcion = '';
                            if(isset($row['id_inscripcion'])) {
                                $val_inscripcion = $row['id_inscripcion'];
                            } else if (isset($_POST['estudiante'])) {
                                $val_inscripcion = $_POST['estudiante'];
                            }
                        ?>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <input type="text" name="id_inscripcion" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all bg-slate-50 focus:bg-white text-slate-800 font-semibold placeholder-slate-400 shadow-sm" value="<?= htmlspecialchars($val_inscripcion, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ej. 1085..." required>
                        </div>
                    </div>

                    <div class="hidden lg:block"></div>

                    <!-- Fecha -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Fecha del Seguimiento</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                <i class="fa-regular fa-calendar-days"></i>
                            </div>
                            <input type="date" name="fecha_seguimiento" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all bg-slate-50 focus:bg-white text-slate-800 font-semibold shadow-sm" value="<?= !empty($row['fecha_seguimiento']) ? htmlspecialchars($row['fecha_seguimiento'], ENT_QUOTES, 'UTF-8') : date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <!-- Hora -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Hora del Seguimiento</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <input type="time" name="hora_seguimiento" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all bg-slate-50 focus:bg-white text-slate-800 font-semibold shadow-sm" value="<?= !empty($row['hora_seguimiento']) ? htmlspecialchars($row['hora_seguimiento'], ENT_QUOTES, 'UTF-8') : date('H:i') ?>" required>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-bold text-slate-700">Observaciones Académicas/Disciplinarias</label>
                        <div class="relative group">
                            <textarea name="observaciones" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition-all bg-slate-50 focus:bg-white resize-none text-slate-800 font-medium placeholder-slate-400 shadow-sm" placeholder="Detalle los apuntes correspondientes a la cita..." required><?= isset($row['observaciones']) ? htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') : '' ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 border-t border-slate-100 mt-4">
                    <a href="citas.php" class="w-full sm:w-auto px-6 py-3 rounded-xl text-slate-500 font-bold hover:bg-slate-100 hover:text-slate-800 transition-colors text-center">Descartar</a>
                    <button type="submit" name="submit" value="<?= ($_POST['submit'] == 'Nuevo') ? 'Registrar' : 'Actualizar' ?>" class="w-full sm:w-auto px-8 py-3 <?= $btn_class ?> text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2">
                        <i class="fa-solid <?= $icon_class ?>"></i> <?= htmlspecialchars($textobtn, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>
            </form>
        </div>

    <?php 
    // Mostrar la Tabla Principal
    } else { 
    ?>
        
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-2xl text-white flex items-center justify-center shadow-lg shadow-indigo-200">
                    <i class="fa-solid fa-users-viewfinder text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Seguimiento Estudiantil</h1>
                    <p class="text-slate-500 font-medium mt-1">Directorio y gestión del historial de citas.</p>
                </div>
            </div>
        </div>

        <!-- Panel de Búsqueda y Filtros -->
        <div class="bg-white p-2.5 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-3 ring-1 ring-slate-900/5">
            
            <div class="relative w-full md:w-2/3 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i>
                </div>
                <input type="search" id="buscar" placeholder="Buscar registros por ID, fechas u observaciones..." class="w-full pl-12 pr-4 py-3 bg-transparent border-none focus:ring-0 outline-none text-slate-800 font-medium placeholder-slate-400" onkeyup="buscar(this.value);" onchange="buscar(this.value);">
            </div>
            
            <div class="h-px w-full md:h-8 md:w-px bg-slate-200 hidden md:block"></div>
            
            <div class="flex items-center gap-3 px-3 py-1 w-full md:w-auto justify-between md:justify-end">
                <label for="numeroresultadosseguimiento" class="text-sm font-bold text-slate-500 whitespace-nowrap">Visualizar</label>
                <div class="relative">
                    <select id="numeroresultadosseguimiento" class="pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none font-bold text-slate-700 cursor-pointer appearance-none shadow-sm transition-all hover:bg-slate-100" style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2364748B%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 1rem top 50%; background-size: .65rem auto;" onchange="grabarcookie('numeroresultadosseguimiento',this.value); buscar(document.getElementById('buscar').value);">
                        <option value="5" <?= (isset($_COOKIE['numeroresultadosseguimiento']) && $_COOKIE['numeroresultadosseguimiento'] == 5) ? 'selected' : '' ?>>5 filas</option>
                        <option value="10" <?= (!isset($_COOKIE['numeroresultadosseguimiento']) || $_COOKIE['numeroresultadosseguimiento'] == 10) ? 'selected' : '' ?>>10 filas</option>
                        <option value="20" <?= (isset($_COOKIE['numeroresultadosseguimiento']) && $_COOKIE['numeroresultadosseguimiento'] == 20) ? 'selected' : '' ?>>20 filas</option>
                        <option value="50" <?= (isset($_COOKIE['numeroresultadosseguimiento']) && $_COOKIE['numeroresultadosseguimiento'] == 50) ? 'selected' : '' ?>>50 filas</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Contenedor dinámico de la tabla -->
        <div id="txtsugerencias" class="transition-opacity duration-300">
            <?php buscar_seguimiento(); ?>
        </div>

    <?php 
    } // fin else (si no se está editando/creando)
    ?>

</div>

<!-- Script nativo para activar menú activo (Mantiene compatibilidad con tu sistema) -->