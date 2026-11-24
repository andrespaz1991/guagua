<?php 
ob_start();
require_once __DIR__ . '/../comun/seguridad.php';
guagua_exigir_rol(['admin','directivo','docente']);
require("conexion.php");
require("../comun/autoload.php");
require_once __DIR__ . '/contexto.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') guagua_exigir_origen_legado();
$contextoAno = guagua_ano_activo($mysqli);
$contextoPeriodo = guagua_periodo_activo($mysqli);

require_once __DIR__ . '/listado.php';

// Rutas GET
if (isset($_GET['buscar'])) {
    buscar_seguimiento(isset($_POST['datos']) ? $_POST['datos'] : '');
    exit();
}
if (isset($_GET['xls'])) {
    buscar_seguimiento('', 'xls');
    exit();
}
if (isset($_GET['imprimir'])) {
    echo '<meta charset="utf-8"><h1>Seguimiento estudiantil</h1>';
    buscar_seguimiento('', 'imprimir');
    echo '<script>window.print();</script>';
    exit;
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
    ';
}

// Validar inscripción, año, período y alcance antes de cualquier escritura.
$errorValidacion = '';
try {
    if (isset($_POST['del']) || in_array($_POST['submit'] ?? '', ['Actualizar', 'Modificar'], true)) {
        $idRegistro = (int)($_POST['del'] ?? $_POST['id_seguimiento'] ?? $_POST['cod'] ?? 0);
        $registro = guagua_sql($mysqli, 'SELECT s.id_seguimiento FROM seguimiento s
            INNER JOIN inscripcion i ON i.id_inscripcion=s.id_inscripcion
            INNER JOIN asignacion a ON a.id_asignacion=i.id_asignacion
            WHERE s.id_seguimiento=? AND ' . seguimiento_condicion($mysqli), [$idRegistro], 'i')->fetch_assoc();
        if (!$registro) throw new InvalidArgumentException('El registro no pertenece al año, período o cursos activos.');
    }
    if (in_array($_POST['submit'] ?? '', ['Registrar', 'Actualizar'], true)) {
        $permitidas = array_column(seguimiento_inscripciones($mysqli), 'id_inscripcion');
        if (!in_array((string)($_POST['id_inscripcion'] ?? ''), array_map('strval', $permitidas), true)) {
            throw new InvalidArgumentException('Seleccione una inscripción del año lectivo activo.');
        }
        $fecha = (string)($_POST['fecha_seguimiento'] ?? '');
        $fechaReal = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$contextoPeriodo || !$fechaReal || $fechaReal->format('Y-m-d') !== $fecha
            || $fecha < $contextoPeriodo['fecha_inicio'] || $fecha > $contextoPeriodo['fecha_fin']) {
            throw new InvalidArgumentException('La fecha debe pertenecer al período activo.');
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/D', (string)($_POST['hora_seguimiento'] ?? ''))
            || trim((string)($_POST['observaciones'] ?? '')) === '') {
            throw new InvalidArgumentException('Complete una hora válida y las observaciones.');
        }
    }
} catch (InvalidArgumentException $e) {
    $errorValidacion = $e->getMessage();
    $alert_html = generar_toast('error', 'Revise los datos', htmlspecialchars($errorValidacion, ENT_QUOTES, 'UTF-8'));
    unset($_POST['del'], $_POST['submit']);
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
<script src="../apps/PlanMind/tailwindcss.js"></script>
<link rel="stylesheet" href="../comun/css/all.min.css">
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-slate-800">
    
    <!-- Renderizar Alertas -->
    <?= $alert_html ?>
    <p class="mb-5 text-sm font-semibold text-slate-600">Año lectivo: <?= htmlspecialchars((string)($contextoAno['nombre_ano_lectivo'] ?? 'Sin configurar'), ENT_QUOTES, 'UTF-8') ?> · Período: <?= htmlspecialchars((string)($contextoPeriodo['nombre_periodo'] ?? 'Sin configurar'), ENT_QUOTES, 'UTF-8') ?></p>
    <?php if (!$contextoPeriodo): ?><p role="alert">Configure un período activo correspondiente al año lectivo activo para registrar seguimientos.</p><?php endif; ?>

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
                                foreach (seguimiento_inscripciones($mysqli) as $inscripcion) {
                                    if ((string)$inscripcion['id_estudiante'] === (string)$_POST['estudiante']) {
                                        $val_inscripcion = $inscripcion['id_inscripcion']; break;
                                    }
                                }
                            }
                        ?>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-600 transition-colors">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <select name="id_inscripcion" class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-white" required>
                                <option value="">Seleccione estudiante e inscripción</option>
                                <?php foreach (seguimiento_inscripciones($mysqli) as $inscripcion): ?>
                                <option value="<?= (int)$inscripcion['id_inscripcion'] ?>" <?= (string)$val_inscripcion === (string)$inscripcion['id_inscripcion'] ? 'selected' : '' ?>><?= htmlspecialchars($inscripcion['etiqueta'], ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
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
<script>
function grabarcookie(nombre, valor) {
    document.cookie = encodeURIComponent(nombre) + '=' + encodeURIComponent(valor) + '; path=/; SameSite=Lax';
}
let busquedaControlador;
async function buscar(datos = document.getElementById('buscar')?.value || '') {
    if (busquedaControlador) busquedaControlador.abort();
    busquedaControlador = new AbortController();
    try {
        const respuesta = await fetch('citas.php?buscar=1', {method: 'POST', body: new URLSearchParams({datos}), signal: busquedaControlador.signal});
        if (!respuesta.ok) throw new Error('No se pudo cargar la búsqueda.');
        document.getElementById('txtsugerencias').innerHTML = await respuesta.text();
    } catch (error) {
        if (error.name !== 'AbortError') alert(error.message);
    }
}
function confirmeliminar(url, datos, id) {
    if (!confirm('¿Eliminar el seguimiento ' + id + '?')) return;
    const formulario = document.createElement('form');
    formulario.method = 'POST'; formulario.action = url;
    for (const [name, value] of Object.entries(datos)) {
        const campo = document.createElement('input'); campo.type = 'hidden'; campo.name = name; campo.value = value;
        formulario.appendChild(campo);
    }
    document.body.appendChild(formulario); formulario.submit();
}
</script>
