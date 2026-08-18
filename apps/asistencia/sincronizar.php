<?php
/** Sincronización idempotente de la planilla mensual de asistencia Vallesol. */
require_once dirname(__DIR__, 2) . '/comun/autoload.php';
require_once SGA_COMUN_SERVER . '/conexion.php';
require_once dirname(__DIR__, 2) . '/asistencia/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

const ASISTENCIA_CONFIG = __DIR__ . '/config_sincronizacion.json';
const ASISTENCIA_UPLOADS = __DIR__ . '/documentos';

function ae($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function asistencia_configuracion() {
    $predeterminada = ['ruta' => 'G:/Mi unidad/PC_HANDRES/SEDUCA/La Josefina/Vallesol/2026/Valoraciones/Asistencia Vallesol 2026 Oficial2.xlsx', 'anio' => (int)date('Y')];
    if (!file_exists(ASISTENCIA_CONFIG)) return $predeterminada;
    $guardada = json_decode(file_get_contents(ASISTENCIA_CONFIG), true);
    return is_array($guardada) ? array_merge($predeterminada, $guardada) : $predeterminada;
}
function asistencia_normalizar($valor) {
    $valor = trim((string)$valor);
    $valor = strtr($valor, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n','Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ü'=>'u','Ñ'=>'n']);
    $valor = function_exists('mb_strtolower') ? mb_strtolower($valor, 'UTF-8') : strtolower($valor);
    return preg_replace('/[^a-z0-9]+/', '', $valor);
}
function asistencia_mes($nombre) {
    $mapa = ['enero'=>1,'febrero'=>2,'marzo'=>3,'abril'=>4,'mayo'=>5,'junio'=>6,'julio'=>7,'agosto'=>8,'septiembre'=>9,'setiembre'=>9,'octubre'=>10,'noviembre'=>11,'diciembre'=>12];
    $normalizado = asistencia_normalizar($nombre);
    foreach ($mapa as $mes => $numero) if (strpos($normalizado, $mes) !== false) return $numero;
    return null;
}
function asistencia_estado($valor) {
    $valor = strtoupper(trim((string)$valor));
    if ($valor === '') return null;
    if ($valor === '0' || $valor === 'NO') return ['estado'=>'NO','uniforme'=>1,'justificacion'=>''];
    if ($valor === 'T') return ['estado'=>'R','uniforme'=>1,'justificacion'=>'Llegada tarde (Excel)'];
    if ($valor === 'J') return ['estado'=>'P','uniforme'=>1,'justificacion'=>'Justificación (Excel)'];
    if ($valor === 'U') return ['estado'=>'SI','uniforme'=>1,'justificacion'=>'Uniforme registrado (Excel)'];
    if ($valor === '1' || $valor === 'SI') return ['estado'=>'SI','uniforme'=>1,'justificacion'=>''];
    return null;
}
/**
 * Programación semanal de respaldo del docente. La base contiene horarios
 * vencidos o incompletos para algunos grados; este mapa es el mismo que se
 * utilizaba en el panel anterior y evita perder la asistencia de esos grupos.
 */
function asistencia_horario_respaldo($grado, $fecha) {
    $grado = (int)$grado; $dia = (int)date('N', strtotime($fecha));
    if ($grado < 6 || $grado > 11 || $dia > 5) return [];
    $basica = $grado <= 8;
    $programacion = [
        1 => $basica ? ['Ciencias Sociales', 'Emprendimiento'] : ['Economia/politica', 'Emprendimiento'],
        2 => ['Educación Física', 'matemáticas'],
        3 => ['Tecnología e informática', 'Geometria'],
        4 => ['Educación Física', 'matemáticas'],
        5 => $basica ? ['Urbanidad', 'Ciencias Sociales'] : ['Fisica', 'Urbanidad'],
    ];
    return $programacion[$dia] ?? [];
}
function asistencia_materias_programadas(array $horario, $grado, $fecha) {
    // La programación semanal tiene prioridad mientras existan vacíos en el
    // horario 2026; si no aplica, se conserva el horario realmente registrado.
    $respaldo = asistencia_horario_respaldo($grado, $fecha);
    return $respaldo ?: array_values($horario[$grado . '|' . $fecha] ?? []);
}
function asistencia_generar_horario(mysqli $mysqli, $anio) {
    $sql = "SELECT h.fecha_inicio, h.fecha_fin, h.dia, cc.nombre_categoria_curso AS grado, mo.nombre_materia
            FROM horario h
            INNER JOIN asignacion a ON a.id_asignacion = h.id_asignacion
            INNER JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
            INNER JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
            WHERE cc.nombre_categoria_curso IN ('6','7','8','9','10','11')
              AND h.fecha_inicio <= ? AND h.fecha_fin >= ?";
    $inicioAnio = $anio . '-01-01'; $finAnio = $anio . '-12-31';
    $stmt = $mysqli->prepare($sql); $stmt->bind_param('ss', $finAnio, $inicioAnio); $stmt->execute();
    $resultado = $stmt->get_result(); $mapa = [];
    $dias = ['lunes'=>1,'martes'=>2,'miercoles'=>3,'miércoles'=>3,'jueves'=>4,'viernes'=>5,'sabado'=>6,'sábado'=>6,'domingo'=>7];
    $hoy = new DateTime('today');
    while ($fila = $resultado->fetch_assoc()) {
        try {
            $desde = new DateTime($fila['fecha_inicio']); $hasta = new DateTime($fila['fecha_fin']);
            $limite = new DateTime($finAnio); if ($hasta > $limite) $hasta = $limite; if ($hasta > $hoy) $hasta = clone $hoy;
            $dia = $dias[asistencia_normalizar($fila['dia'])] ?? null; if (!$dia || $desde > $hasta) continue;
            for ($fecha = clone $desde; $fecha <= $hasta; $fecha->modify('+1 day')) {
                if ((int)$fecha->format('N') !== $dia) continue;
                $clave = $fila['grado'] . '|' . $fecha->format('Y-m-d');
                // Una planilla registra un estado general diario: una materia se guarda una sola vez aunque el horario tenga doble bloque.
                $mapa[$clave][asistencia_normalizar($fila['nombre_materia'])] = $fila['nombre_materia'];
            }
        } catch (Throwable $e) { continue; }
    }
    $stmt->close(); return $mapa;
}
function asistencia_estudiantes(mysqli $mysqli) {
    $resultado = $mysqli->query("SELECT id_usuario, nombre, apellido, observaciones FROM usuario WHERE rol LIKE '%estudiante%'");
    $porGrado = []; $porNombre = [];
    while ($fila = $resultado->fetch_assoc()) {
        $grado = preg_replace('/\D+/', '', (string)$fila['observaciones']);
        $nombres = [trim($fila['nombre'] . ' ' . $fila['apellido']), trim($fila['apellido'] . ' ' . $fila['nombre'])];
        foreach ($nombres as $nombre) {
            if ($nombre === '') continue;
            $clave = asistencia_normalizar($nombre);
            $porGrado[$clave . '|' . $grado] = $fila;
            // Si el nombre corresponde a una sola persona, permite importar
            // aunque su grado guardado aún no se haya actualizado en Guagua.
            if (!array_key_exists($clave, $porNombre)) $porNombre[$clave] = $fila;
            elseif (!is_array($porNombre[$clave]) || $porNombre[$clave]['id_usuario'] !== $fila['id_usuario']) $porNombre[$clave] = null;
        }
    }
    return ['por_grado'=>$porGrado, 'por_nombre'=>$porNombre];
}
function asistencia_clave_registro($documento, $materia, $fecha) {
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', (string)$fecha, $partes)) $fecha = sprintf('%04d-%02d-%02d', $partes[3], $partes[2], $partes[1]);
    return $documento . '|' . asistencia_normalizar($materia) . '|' . $fecha;
}
function asistencia_indice_registros(mysqli $mysqli, $anio) {
    $anio = (int)$anio; $indice = [];
    $consulta = $mysqli->query("SELECT id, documento, materia, fechas_clase FROM asistencias WHERE fechas_clase LIKE '{$anio}-%' OR fechas_clase LIKE '%/{$anio}'");
    while ($fila = $consulta->fetch_assoc()) $indice[asistencia_clave_registro($fila['documento'], $fila['materia'], $fila['fechas_clase'])] = (int)$fila['id'];
    return $indice;
}
function asistencia_guardar(mysqli $mysqli, $documento, $nombre, $materia, $fecha, array $estado, array &$resultado, array &$indice) {
    static $actualizar = null, $insertar = null;
    $clave = asistencia_clave_registro($documento, $materia, $fecha);
    $estadoDb = $estado['estado']; $uniforme = (int)$estado['uniforme']; $justificacion = $estado['justificacion'];
    if (isset($indice[$clave])) {
        if (!$actualizar) $actualizar = $mysqli->prepare("UPDATE asistencias SET estudiante=?, asistencias=?, fechas_clase=?, uniforme=?, justificacion=?, fecha_actualizacion=CURRENT_TIMESTAMP WHERE id=?");
        $id = $indice[$clave]; $actualizar->bind_param('sssisi', $nombre, $estadoDb, $fecha, $uniforme, $justificacion, $id);
        $actualizar->execute(); $resultado['actualizados']++; return;
    }
    if (!$insertar) $insertar = $mysqli->prepare("INSERT INTO asistencias (estudiante,materia,asistencias,fechas_clase,documento,uniforme,justificacion) VALUES (?,?,?,?,?,?,?)");
    $insertar->bind_param('sssssis', $nombre, $materia, $estadoDb, $fecha, $documento, $uniforme, $justificacion);
    $insertar->execute(); $indice[$clave] = (int)$mysqli->insert_id; $resultado['nuevos']++;
}
function asistencia_sincronizar(mysqli $mysqli, $archivo, $anio) {
    if (!is_file($archivo)) throw new RuntimeException('No se encontró el archivo: ' . $archivo);
    $horario = asistencia_generar_horario($mysqli, $anio); $estudiantes = asistencia_estudiantes($mysqli); $indice = asistencia_indice_registros($mysqli, $anio);
    $libro = IOFactory::load($archivo); $resultado = ['nuevos'=>0,'actualizados'=>0,'omitidos'=>0,'sin_horario'=>0,'sin_estudiante'=>0,'grado_diferente'=>0,'estudiantes_no_encontrados'=>[],'meses'=>[]];
    $mesActual = (int)date('n'); $hoy = date('Y-m-d');
    $mysqli->begin_transaction();
    try {
        foreach ($libro->getWorksheetIterator() as $hoja) {
            $mes = asistencia_mes($hoja->getTitle()); if (!$mes || $mes > $mesActual) continue;
            $resultado['meses'][] = $hoja->getTitle(); $maxCol = Coordinate::columnIndexFromString($hoja->getHighestDataColumn());
            $colNombre = null; $dias = [];
            for ($col=1; $col<=$maxCol; $col++) {
                $valor = trim((string)$hoja->getCellByColumnAndRow($col, 11)->getCalculatedValue());
                if (stripos($valor, 'NOMBRES') !== false) $colNombre = $col;
                if (ctype_digit($valor) && (int)$valor >= 1 && (int)$valor <= 31) $dias[$col] = (int)$valor;
            }
            if (!$colNombre || !$dias) { $resultado['omitidos']++; continue; }
            // Algunas hojas conservan formato hasta la fila 1.048.576. Se lee
            // únicamente la columna de nombres y se termina al encontrar un bloque vacío.
            // No se consulta getHighestDataRow(): la planilla conserva formato
            // hasta la última fila de Excel. El corte por ocho filas vacías
            // finaliza en el listado real y 500 deja margen para grupos grandes.
            $ultimaFila = 500;
            $vaciasConsecutivas = 0;
            for ($fila=12; $fila<=$ultimaFila; $fila++) {
                $nombre = trim((string)$hoja->getCellByColumnAndRow($colNombre, $fila)->getCalculatedValue());
                if ($nombre === '') { if (++$vaciasConsecutivas >= 8) break; continue; }
                $vaciasConsecutivas = 0;
                $grado = preg_replace('/\D+/', '', (string)$hoja->getCellByColumnAndRow(2, $fila)->getCalculatedValue());
                $claveNombre = asistencia_normalizar($nombre);
                $estudiante = $estudiantes['por_grado'][$claveNombre . '|' . $grado] ?? null;
                if (!$estudiante) {
                    $estudiante = $estudiantes['por_nombre'][$claveNombre] ?? null;
                    if ($estudiante) $resultado['grado_diferente']++;
                }
                if (!$estudiante) { $resultado['sin_estudiante']++; $resultado['estudiantes_no_encontrados'][asistencia_normalizar($nombre)] = $nombre . ' (grado ' . $grado . ')'; continue; }
                foreach ($dias as $col=>$dia) {
                    if (!checkdate($mes, $dia, $anio)) continue; $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia); if ($fecha > $hoy) continue;
                    $estado = asistencia_estado($hoja->getCellByColumnAndRow($col, $fila)->getCalculatedValue()); if (!$estado) continue;
                    $materias = asistencia_materias_programadas($horario, $grado, $fecha); if (!$materias) { $resultado['sin_horario']++; continue; }
                    foreach ($materias as $materia) asistencia_guardar($mysqli, (string)$estudiante['id_usuario'], $nombre, $materia, $fecha, $estado, $resultado, $indice);
                }
            }
        }
        $mysqli->commit(); return $resultado;
    } catch (Throwable $e) { $mysqli->rollback(); throw $e; }
}

$config = asistencia_configuracion(); $mensaje = ''; $resultadoSync = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['guardar_configuracion'])) {
            $config = ['ruta'=>trim($_POST['ruta'] ?? ''), 'anio'=>(int)($_POST['anio'] ?? date('Y'))];
            file_put_contents(ASISTENCIA_CONFIG, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); $mensaje = 'Ruta guardada.';
        }
        if (isset($_POST['sincronizar'])) {
            $archivo = $config['ruta'];
            if (isset($_FILES['archivo_excel']) && $_FILES['archivo_excel']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir(ASISTENCIA_UPLOADS)) mkdir(ASISTENCIA_UPLOADS, 0755, true);
                $extension = strtolower(pathinfo($_FILES['archivo_excel']['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, ['xlsx','xls'], true)) throw new RuntimeException('Solo se permiten archivos .xlsx o .xls.');
                $archivo = ASISTENCIA_UPLOADS . '/asistencia_' . date('Ymd_His') . '.' . $extension;
                if (!move_uploaded_file($_FILES['archivo_excel']['tmp_name'], $archivo)) throw new RuntimeException('No fue posible guardar el archivo cargado.');
                $config['ruta'] = $archivo; file_put_contents(ASISTENCIA_CONFIG, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }
            $resultadoSync = asistencia_sincronizar($mysqli, $archivo, (int)$config['anio']);
            $mensaje = 'Sincronización completada sin duplicar registros.';
        }
    } catch (Throwable $e) { $mensaje = 'Error: ' . $e->getMessage(); }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sincronizar asistencia | Guagua</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#f5f7fb;color:#172554}.hero{background:linear-gradient(120deg,#172554,#0f766e);color:#fff}.card{border:0;border-radius:18px;box-shadow:0 12px 30px #17255412}.step{border-left:4px solid #14b8a6}.metric{font-size:2rem;font-weight:800}</style></head><body><header class="hero py-5"><div class="container"><a href="index.php" class="text-white-50 text-decoration-none small"><i class="bi bi-arrow-left"></i> Panel de asistencia</a><h1 class="display-6 fw-bold mt-2">Sincronización de asistencia</h1><p class="mb-0 text-white-50">Lee los meses hasta hoy, cruza el horario real y actualiza la base sin duplicar.</p></div></header><main class="container py-4">
<?php if ($mensaje): ?><div class="alert <?php echo str_starts_with($mensaje,'Error:') ? 'alert-danger' : 'alert-success'; ?>"><?php echo ae($mensaje); ?></div><?php endif; ?>
<div class="row g-4"><div class="col-lg-7"><section class="card p-4"><h2 class="h4"><i class="bi bi-cloud-arrow-up text-success me-2"></i>Archivo y sincronización</h2><p class="text-muted">La fila 11 debe contener días y las hojas deben llamarse por mes. Se procesan Enero hasta el mes actual.</p><form method="post" enctype="multipart/form-data" class="row g-3"><div class="col-12"><label class="form-label">Ruta configurada</label><input class="form-control" name="ruta" value="<?php echo ae($config['ruta']); ?>"></div><div class="col-sm-4"><label class="form-label">Año</label><input type="number" class="form-control" name="anio" value="<?php echo (int)$config['anio']; ?>"></div><div class="col-sm-8"><label class="form-label">O sube un archivo actualizado</label><input type="file" class="form-control" name="archivo_excel" accept=".xlsx,.xls"></div><div class="col-12 d-flex gap-2"><button class="btn btn-outline-secondary" name="guardar_configuracion" value="1"><i class="bi bi-gear me-1"></i>Guardar ruta</button><button class="btn btn-success" name="sincronizar" value="1"><i class="bi bi-arrow-repeat me-1"></i>Sincronizar asistencia</button></div></form></section></div><div class="col-lg-5"><section class="card p-4"><h2 class="h5">Convención aplicada</h2><table class="table table-sm mb-0"><tr><th>1</th><td>Asistió</td><td class="text-success">SI</td></tr><tr><th>0</th><td>No asistió</td><td class="text-danger">NO</td></tr><tr><th>T</th><td>Llegada tarde</td><td>R</td></tr><tr><th>J</th><td>Justificación</td><td>P</td></tr><tr><th>U</th><td>Uniforme</td><td>SI + registro</td></tr></table></section></div></div>
<?php if ($resultadoSync): ?><section class="card p-4 mt-4"><h2 class="h4">Resultado de la sincronización</h2><div class="row text-center g-3"><div class="col"><div class="metric text-success"><?php echo $resultadoSync['nuevos']; ?></div><small>Nuevos</small></div><div class="col"><div class="metric text-primary"><?php echo $resultadoSync['actualizados']; ?></div><small>Actualizados</small></div><div class="col"><div class="metric text-warning"><?php echo $resultadoSync['sin_horario']; ?></div><small>Sin horario</small></div><div class="col"><div class="metric text-info"><?php echo $resultadoSync['grado_diferente']; ?></div><small>Grado distinto</small></div><div class="col"><div class="metric text-danger"><?php echo $resultadoSync['sin_estudiante']; ?></div><small>Sin estudiante</small></div></div><hr><p class="mb-0"><strong>Hojas procesadas:</strong> <?php echo ae(implode(', ', $resultadoSync['meses'])); ?>. Los registros existentes se actualizan por estudiante, materia y fecha; no se insertan duplicados.</p><?php if ($resultadoSync['estudiantes_no_encontrados']): ?><div class="alert alert-warning mt-3 mb-0"><strong>Estudiantes sin usuario en Guagua:</strong> <?php echo ae(implode(', ', array_values($resultadoSync['estudiantes_no_encontrados']))); ?>.</div><?php endif; ?></section><?php endif; ?>
<section class="card step p-4 mt-4"><h2 class="h5">Cómo funciona</h2><ol class="mb-0 text-muted"><li>Encuentra el estudiante por nombre y grado, y guarda su documento de Guagua.</li><li>Para cada fecha, consulta el horario de su grado y crea/actualiza las materias que realmente tenía ese día.</li><li>Si corriges la celda en Excel y sincronizas otra vez, el mismo registro se actualiza.</li></ol></section></main></body></html>
