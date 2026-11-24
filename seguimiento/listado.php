<?php
// Función principal de búsqueda y renderizado de tabla
function buscar_seguimiento($datos = '', $reporte = '') {
    global $mysqli;
    require_once __DIR__ . '/../comun/lib/Zebra_Pagination/Zebra_Pagination.php';
    
    // Validación básica de resultados por página
    $resultados = max(1, min(100, (int)($_COOKIE['numeroresultadosseguimiento'] ?? 10)));
    
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

    $from = ' FROM seguimiento s INNER JOIN inscripcion i ON i.id_inscripcion = s.id_inscripcion
        INNER JOIN asignacion a ON a.id_asignacion = i.id_asignacion
        INNER JOIN usuario u ON u.id_usuario = i.id_estudiante';
    $where = ' WHERE ' . seguimiento_condicion($mysqli);
    $sql = 'SELECT s.*, u.nombre, u.apellido, i.id_estudiante' . $from;
    $consulta = $mysqli->query('SELECT s.id_seguimiento' . $from . $where);
    
    // Si la tabla está vacía o hay error, evitamos error fatal
    if (!$consulta) {
        $paginacion->records(0);
    } else {
        $paginacion->records($consulta->num_rows);
    }

    $datos_array = array_filter(explode(" ", trim($datos))); // Filtramos espacios vacíos
    
    $sql .= $where;
    
    if (!empty($_GET['xls'])) {
        // Aseguramos que sea entero para evitar inyección en el GET
        $id_xls = intval($_GET['xls']);
        $sql .= " AND s.id_seguimiento = " . $id_xls;
    } else {
        if (!empty($datos_array)) {
            $sql .= " AND (";
            $condiciones = [];
            foreach ($datos_array as $dato) {
                // Escapamos el dato para la consulta dinámica LIKE
                $dato_limpio = $mysqli->real_escape_string(mb_strtolower($dato, 'UTF-8'));
                $condiciones[] = 'LOWER(CONCAT_WS(" ", s.id_seguimiento, s.id_inscripcion, s.fecha_seguimiento, s.hora_seguimiento, s.observaciones, u.nombre, u.apellido, i.id_estudiante)) LIKE "%' . $dato_limpio . '%"';
            }
            $sql .= implode(' AND ', $condiciones) . ") ";
        }
        
        $paginacion->records($mysqli->query('SELECT s.id_seguimiento' . $from . substr($sql, strpos($sql, ' WHERE ')))->num_rows);
        $sql .= ' ORDER BY s.id_seguimiento DESC ';
        
        if ($reporte === '' && !isset($_GET['xls'])) {
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
                <a target="_blank" href="citas.php?imprimir=1" class="w-full sm:w-auto flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-xl text-sm font-bold transition-colors shadow-sm" title="Imprimir o guardar como PDF">
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
                                            <div class="text-sm font-bold text-slate-900"><?= htmlspecialchars($row['apellido'] . ' ' . $row['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="text-xs text-slate-500"><?= htmlspecialchars($row['id_estudiante'], ENT_QUOTES, 'UTF-8') ?> · Inscripción <?= (int)$row['id_inscripcion'] ?></div>
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

