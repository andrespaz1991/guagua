<?php
/**
 * Generador de asignaciones académicas.
 * Crea únicamente las combinaciones materia/grado que no existan para el
 * año lectivo, docente e institución actuales.
 */

require_once __DIR__ . '/../comun/config.php';

function genasig_escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function genasig_conectar(): mysqli
{
    $conexion = new mysqli(SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);
    if ($conexion->connect_errno) {
        throw new RuntimeException('No fue posible conectar con la base de datos.');
    }

    $conexion->set_charset('utf8mb4');
    return $conexion;
}

function genasig_obtener_seleccion(array $valores, array $materias, array $grados): array
{
    $seleccion = [];
    foreach ($valores as $valor) {
        $partes = explode(':', (string)$valor, 2);
        $idMateria = max(0, (int)($partes[0] ?? 0));
        $idGrado = max(0, (int)($partes[1] ?? 0));
        if ($idMateria > 0 && $idGrado > 0 && isset($materias[$idMateria], $grados[$idGrado])) {
            $seleccion[$idMateria . ':' . $idGrado] = [$idMateria, $idGrado];
        }
    }

    return array_values($seleccion);
}

$mensajeError = '';
$resultados = [];
$materias = [];
$grados = [];
$anosLectivos = [];
$idAnoPredeterminado = 0;
$idDocente = '';
$idInstitucion = 1;
$nombreInstitucion = 'Institución no encontrada';
$idAnoActivo = 0;
$asignacionesActivas = [];
$resultadosEliminacion = [];
$resultadosBloqueados = [];
$accionSolicitada = '';

try {
    $mysqli = genasig_conectar();

    $materias = [];
    $resultadoMaterias = $mysqli->query('SELECT id_materia, nombre_materia FROM materia_oficial ORDER BY nombre_materia ASC');
    if ($resultadoMaterias) {
        while ($fila = $resultadoMaterias->fetch_assoc()) {
            $materias[(int)$fila['id_materia']] = (string)$fila['nombre_materia'];
        }
    }

    $grados = [];
    $resultadoGrados = $mysqli->query('SELECT id_categoria_curso, nombre_categoria_curso FROM categoria_curso ORDER BY id_categoria_curso ASC');
    if ($resultadoGrados) {
        while ($fila = $resultadoGrados->fetch_assoc()) {
            $grados[(int)$fila['id_categoria_curso']] = (string)$fila['nombre_categoria_curso'];
        }
    }

    $anosLectivos = [];
    $resultadoAnos = $mysqli->query("SELECT id_ano_lectivo, nombre_ano_lectivo, estado FROM ano_lectivo ORDER BY nombre_ano_lectivo DESC");
    if ($resultadoAnos) {
        while ($fila = $resultadoAnos->fetch_assoc()) {
            $anosLectivos[(int)$fila['id_ano_lectivo']] = [
                'nombre' => (string)$fila['nombre_ano_lectivo'],
                'estado' => (string)$fila['estado'],
            ];
        }
    }

    $anoActual = (string)date('Y');
    $idAnoPredeterminado = 0;
    foreach ($anosLectivos as $idAno => $ano) {
        if ($ano['nombre'] === $anoActual) {
            $idAnoPredeterminado = $idAno;
            break;
        }
    }
    if ($idAnoPredeterminado === 0) {
        foreach ($anosLectivos as $idAno => $ano) {
            if (strcasecmp($ano['estado'], 'Activo') === 0) {
                $idAnoPredeterminado = $idAno;
                break;
            }
        }
    }
    foreach ($anosLectivos as $idAno => $ano) {
        if (strcasecmp($ano['estado'], 'Activo') === 0) {
            $idAnoActivo = $idAno;
            break;
        }
    }

    if (!isset($_SESSION['genasig_csrf'])) {
        $_SESSION['genasig_csrf'] = bin2hex(random_bytes(32));
    }

    $idDocente = trim((string)($_SESSION['id_usuario'] ?? $_SESSION['identificacion_usu'] ?? ''));
    $idInstitucion = max(1, (int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 1));
    $consultaInstitucion = $mysqli->prepare(
        'SELECT nombre_institucion FROM institucion_educativa WHERE id_institucion_educativa = ? LIMIT 1'
    );
    if ($consultaInstitucion) {
        $consultaInstitucion->bind_param('i', $idInstitucion);
        $consultaInstitucion->execute();
        $institucion = $consultaInstitucion->get_result()->fetch_assoc();
        $nombreInstitucion = trim((string)($institucion['nombre_institucion'] ?? $nombreInstitucion));
        $consultaInstitucion->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accionSolicitada = (string)($_POST['accion'] ?? 'generar');
        $tokenEnviado = (string)($_POST['csrf_token'] ?? '');

        if (!hash_equals($_SESSION['genasig_csrf'], $tokenEnviado)) {
            throw new RuntimeException('La solicitud ya no es válida. Recargue la página e inténtelo de nuevo.');
        }
        if ($idDocente === '') {
            throw new RuntimeException('No se encontró el docente de la sesión actual; no se realizaron cambios.');
        }

        if ($accionSolicitada === 'generar') {
            $idAno = max(0, (int)($_POST['ano_lectivo'] ?? 0));
            $seleccion = genasig_obtener_seleccion($_POST['combinaciones'] ?? [], $materias, $grados);
            if (!isset($anosLectivos[$idAno])) {
                throw new RuntimeException('Seleccione un año lectivo válido.');
            }
            if (!$seleccion) {
                throw new RuntimeException('Seleccione por lo menos una combinación de materia y grado.');
            }

            $consultaExistente = $mysqli->prepare(
                'SELECT id_asignacion
                 FROM asignacion
                 WHERE id_asignatura = ?
                   AND id_categoria_curso = ?
                   AND ano_lectivo = ?
                   AND id_docente = ?
                   AND institucion_educativa = ?
                 LIMIT 1'
            );
            $insertarAsignacion = $mysqli->prepare(
                "INSERT INTO asignacion
                    (institucion_educativa, id_curso, id_asignatura, id_docente, ano_lectivo, descripcion, id_categoria_curso, visible)
                 VALUES (?, ?, ?, ?, ?, NULL, ?, 'si')"
            );
            if (!$consultaExistente || !$insertarAsignacion) {
                throw new RuntimeException('No fue posible preparar las consultas de asignaciones.');
            }

            foreach ($seleccion as [$idMateria, $idGrado]) {
                $consultaExistente->bind_param('iiisi', $idMateria, $idGrado, $idAno, $idDocente, $idInstitucion);
                $consultaExistente->execute();
                $existente = $consultaExistente->get_result()->fetch_assoc();

                $resultado = [
                    'materia' => $materias[$idMateria],
                    'grado' => $grados[$idGrado],
                ];
                if ($existente) {
                    $resultado['estado'] = 'existente';
                    $resultado['id_asignacion'] = (int)$existente['id_asignacion'];
                    $resultados[] = $resultado;
                    continue;
                }

                // id_curso e id_asignatura conservan el identificador de la materia oficial.
                $insertarAsignacion->bind_param('iiisii', $idInstitucion, $idMateria, $idMateria, $idDocente, $idAno, $idGrado);
                if (!$insertarAsignacion->execute()) {
                    throw new RuntimeException('No fue posible crear la asignación de ' . $materias[$idMateria] . ' para ' . $grados[$idGrado] . '.');
                }

                $resultado['estado'] = 'creada';
                $resultado['id_asignacion'] = (int)$mysqli->insert_id;
                $resultados[] = $resultado;
            }

            $consultaExistente->close();
            $insertarAsignacion->close();
        } elseif ($accionSolicitada === 'eliminar_asignaciones') {
            if ($idAnoActivo <= 0) {
                throw new RuntimeException('No hay un año lectivo activo para consultar las asignaciones.');
            }
            if (($_POST['confirmar_eliminacion'] ?? '') !== '1') {
                throw new RuntimeException('Debe confirmar la eliminación de las asignaciones desmarcadas.');
            }

            $mantener = [];
            foreach ($_POST['asignaciones_mantener'] ?? [] as $idsAsignacion) {
                foreach (explode(',', (string)$idsAsignacion) as $idAsignacion) {
                    $idAsignacion = max(0, (int)$idAsignacion);
                    if ($idAsignacion > 0) {
                        $mantener[$idAsignacion] = true;
                    }
                }
            }

            $consultaActivas = $mysqli->prepare(
                'SELECT id_asignacion, id_asignatura, id_categoria_curso
                 FROM asignacion
                 WHERE ano_lectivo = ? AND id_docente = ? AND institucion_educativa = ?'
            );
            $consultaActivas->bind_param('isi', $idAnoActivo, $idDocente, $idInstitucion);
            $consultaActivas->execute();
            $asignacionesParaEliminar = [];
            $resultadoActivas = $consultaActivas->get_result();
            while ($asignacion = $resultadoActivas->fetch_assoc()) {
                $idAsignacion = (int)$asignacion['id_asignacion'];
                if (!isset($mantener[$idAsignacion])) {
                    $asignacionesParaEliminar[] = $asignacion;
                }
            }
            $consultaActivas->close();

            if (!$asignacionesParaEliminar) {
                throw new RuntimeException('No se desmarcó ninguna asignación activa.');
            }

            $consultaDependencias = $mysqli->prepare(
                'SELECT
                    (SELECT COUNT(*) FROM actividad WHERE id_asignacion = ?) AS actividades,
                    (SELECT COUNT(*) FROM edunotas WHERE id_asignacion = ?) AS notas,
                    (SELECT COUNT(*) FROM horario WHERE id_asignacion = ?) AS horarios,
                    (SELECT COUNT(*) FROM inscripcion WHERE id_asignacion = ?) AS inscripciones,
                    (SELECT COUNT(*) FROM respuesta_estudiante WHERE id_asignacion = ?) AS respuestas'
            );
            $eliminarAsignacion = $mysqli->prepare(
                'DELETE FROM asignacion
                 WHERE id_asignacion = ? AND ano_lectivo = ? AND id_docente = ? AND institucion_educativa = ?
                 LIMIT 1'
            );
            if (!$consultaDependencias || !$eliminarAsignacion) {
                throw new RuntimeException('No fue posible preparar la eliminación de las asignaciones.');
            }

            foreach ($asignacionesParaEliminar as $asignacion) {
                $idAsignacion = (int)$asignacion['id_asignacion'];
                $consultaDependencias->bind_param('iiiii', $idAsignacion, $idAsignacion, $idAsignacion, $idAsignacion, $idAsignacion);
                $consultaDependencias->execute();
                $dependencias = $consultaDependencias->get_result()->fetch_assoc() ?: [];
                $detalle = [];
                foreach (['actividades' => 'actividades', 'notas' => 'notas', 'horarios' => 'horarios', 'inscripciones' => 'inscripciones', 'respuestas' => 'respuestas'] as $campo => $etiqueta) {
                    if ((int)($dependencias[$campo] ?? 0) > 0) {
                        $detalle[] = (int)$dependencias[$campo] . ' ' . $etiqueta;
                    }
                }

                $resultado = [
                    'id_asignacion' => $idAsignacion,
                    'materia' => $materias[(int)$asignacion['id_asignatura']] ?? ('Materia #' . $asignacion['id_asignatura']),
                    'grado' => $grados[(int)$asignacion['id_categoria_curso']] ?? ('Grado #' . $asignacion['id_categoria_curso']),
                ];
                if ($detalle) {
                    $resultado['detalle'] = implode(', ', $detalle);
                    $resultadosBloqueados[] = $resultado;
                    continue;
                }

                $eliminarAsignacion->bind_param('iisi', $idAsignacion, $idAnoActivo, $idDocente, $idInstitucion);
                if (!$eliminarAsignacion->execute()) {
                    throw new RuntimeException('No fue posible eliminar la asignación #' . $idAsignacion . '.');
                }
                if ($eliminarAsignacion->affected_rows > 0) {
                    $resultadosEliminacion[] = $resultado;
                }
            }

            $consultaDependencias->close();
            $eliminarAsignacion->close();
        } else {
            throw new RuntimeException('Acción no reconocida.');
        }

        $_SESSION['genasig_csrf'] = bin2hex(random_bytes(32));
    }

    if ($idAnoActivo > 0 && $idDocente !== '') {
        $consultaAsignacionesActivas = $mysqli->prepare(
            'SELECT id_asignacion, id_asignatura, id_categoria_curso
             FROM asignacion
             WHERE ano_lectivo = ? AND id_docente = ? AND institucion_educativa = ?'
        );
        $consultaAsignacionesActivas->bind_param('isi', $idAnoActivo, $idDocente, $idInstitucion);
        $consultaAsignacionesActivas->execute();
        $resultadoAsignacionesActivas = $consultaAsignacionesActivas->get_result();
        while ($asignacion = $resultadoAsignacionesActivas->fetch_assoc()) {
            $clave = (int)$asignacion['id_asignatura'] . ':' . (int)$asignacion['id_categoria_curso'];
            $asignacionesActivas[$clave] ??= [];
            $asignacionesActivas[$clave][] = (int)$asignacion['id_asignacion'];
        }
        $consultaAsignacionesActivas->close();
    }
} catch (Throwable $error) {
    $mensajeError = $error->getMessage();
}

$idAnoSeleccionado = max(0, (int)($_POST['ano_lectivo'] ?? $idAnoPredeterminado ?? 0));
$cantidadCreadas = count(array_filter($resultados, static fn(array $resultado): bool => $resultado['estado'] === 'creada'));
$cantidadExistentes = count(array_filter($resultados, static fn(array $resultado): bool => $resultado['estado'] === 'existente'));
$cantidadAsignacionesActivas = array_sum(array_map(static fn(array $ids): int => count($ids), $asignacionesActivas));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar asignaciones académicas</title>
    <style>
        :root { color-scheme: light; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; color: #172033; background: #f5f7fb; }
        * { box-sizing: border-box; }
        body { margin: 0; }
        main { width: min(1440px, calc(100% - 32px)); margin: 32px auto 48px; }
        h1 { margin: 0; font-size: clamp(1.5rem, 3vw, 2rem); }
        h2 { margin: 0; font-size: 1rem; }
        .encabezado, .panel { background: #fff; border: 1px solid #dce3ef; border-radius: 14px; box-shadow: 0 8px 24px rgba(26, 42, 72, .06); }
        .encabezado { padding: 24px; margin-bottom: 18px; }
        .encabezado p { margin: 8px 0 0; color: #58657a; line-height: 1.5; }
        .panel { padding: 22px; margin-top: 18px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 16px; align-items: end; justify-content: space-between; margin-bottom: 20px; }
        label { display: block; color: #354158; font-size: .88rem; font-weight: 700; margin-bottom: 6px; }
        select { min-width: 230px; padding: 10px 12px; border: 1px solid #bfcadd; border-radius: 8px; font: inherit; color: #172033; background: white; }
        .acciones { display: flex; flex-wrap: wrap; gap: 8px; }
        button { border: 0; border-radius: 8px; padding: 10px 14px; font: inherit; font-weight: 700; cursor: pointer; }
        .secundario { color: #244a8c; background: #eaf1ff; }
        .principal { color: #fff; background: #135dc6; }
        .principal:hover { background: #0e4a9f; }
        .principal:disabled { cursor: not-allowed; opacity: .55; }
        .matriz-contenedor { overflow: auto; border: 1px solid #dce3ef; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; min-width: 760px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e8edf5; text-align: center; }
        th { position: sticky; top: 0; background: #f7f9fd; color: #36435a; font-size: .78rem; }
        th:first-child, td:first-child { position: sticky; left: 0; z-index: 1; text-align: left; background: #fff; min-width: 220px; }
        th:first-child { z-index: 2; background: #f7f9fd; }
        td:first-child { font-weight: 650; color: #25324a; }
        tr:last-child td { border-bottom: 0; }
        input[type="checkbox"] { width: 17px; height: 17px; accent-color: #135dc6; cursor: pointer; }
        .ids-asignacion { display: block; margin-top: 3px; color: #647189; font-size: .68rem; line-height: 1.1; white-space: nowrap; }
        .aviso { padding: 14px 16px; margin: 18px 0; border-radius: 10px; line-height: 1.45; }
        .aviso-error { color: #8a1924; border: 1px solid #f3bdc3; background: #fff1f2; }
        .aviso-info { color: #1d4d89; border: 1px solid #bed7fb; background: #eff6ff; }
        .resumen { display: flex; flex-wrap: wrap; gap: 12px; margin: 16px 0; }
        .contador { border-radius: 999px; padding: 7px 12px; font-size: .86rem; font-weight: 700; }
        .contador-creado { color: #137146; background: #e9faef; }
        .contador-existente { color: #8c5a04; background: #fff5dc; }
        .estado { display: inline-block; border-radius: 999px; padding: 4px 8px; font-size: .78rem; font-weight: 700; }
        .estado-creada { color: #137146; background: #e9faef; }
        .estado-existente { color: #8c5a04; background: #fff5dc; }
        .estado-eliminada { color: #8a1924; background: #fff1f2; }
        .estado-bloqueada { color: #8c5a04; background: #fff5dc; }
        .contexto { color: #647189; font-size: .85rem; }
        .tabs { display: flex; gap: 8px; margin: 0 0 18px; border-bottom: 1px solid #dce3ef; }
        .tab { color: #52617a; background: transparent; border-radius: 8px 8px 0 0; padding: 11px 15px; }
        .tab.activa { color: #0d4fae; background: #eaf1ff; }
        .panel-tab[hidden] { display: none; }
        .peligro { color: #fff; background: #bd2330; }
        .peligro:hover { background: #961a25; }
        .sin-asignacion { color: #9aa5b5; }
        @media (max-width: 620px) { main { width: min(100% - 20px, 1440px); margin-top: 10px; } .panel, .encabezado { padding: 16px; } select { width: 100%; } }
    </style>
</head>
<body>
<main>
    <section class="encabezado">
        <h1>Generar asignaciones académicas</h1>
        <p>Marque las combinaciones de materia y grado que desea generar. Las ya existentes para el mismo año lectivo, docente e institución se conservarán y se reportará su <code>id_asignacion</code>.</p>
    </section>

    <?php if ($mensajeError !== ''): ?>
        <div class="aviso aviso-error"><?php echo genasig_escapar($mensajeError); ?></div>
    <?php endif; ?>

    <div class="tabs" role="tablist" aria-label="Gestión de asignaciones">
        <button type="button" class="tab activa" data-tab="generar" role="tab" aria-selected="true">Generar asignaciones</button>
        <button type="button" class="tab" data-tab="activas" role="tab" aria-selected="false">Asignaciones activas</button>
    </div>

    <div class="panel-tab" data-panel="generar">
    <?php if ($resultados): ?>
        <section class="panel">
            <h2>Resultado de la generación</h2>
            <div class="resumen">
                <span class="contador contador-creado">Creadas: <?php echo $cantidadCreadas; ?></span>
                <span class="contador contador-existente">Ya existentes: <?php echo $cantidadExistentes; ?></span>
            </div>
            <div class="matriz-contenedor">
                <table>
                    <thead><tr><th>Materia</th><th>Grado</th><th>Resultado</th><th>id_asignacion</th></tr></thead>
                    <tbody>
                    <?php foreach ($resultados as $resultado): ?>
                        <tr>
                            <td><?php echo genasig_escapar($resultado['materia']); ?></td>
                            <td><?php echo genasig_escapar($resultado['grado']); ?></td>
                            <td><span class="estado estado-<?php echo $resultado['estado']; ?>"><?php echo $resultado['estado'] === 'creada' ? 'Creada' : 'Ya existía'; ?></span></td>
                            <td>#<?php echo (int)$resultado['id_asignacion']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <form method="post" class="panel" id="formGenerar">
        <input type="hidden" name="csrf_token" value="<?php echo genasig_escapar((string)($_SESSION['genasig_csrf'] ?? '')); ?>">
        <div class="toolbar">
            <div>
                <label for="ano_lectivo">Año lectivo</label>
                <select id="ano_lectivo" name="ano_lectivo" required>
                    <?php foreach ($anosLectivos as $idAno => $ano): ?>
                        <option value="<?php echo $idAno; ?>" <?php echo $idAno === $idAnoSeleccionado ? 'selected' : ''; ?>>
                            <?php echo genasig_escapar($ano['nombre'] . (strcasecmp($ano['estado'], 'Activo') === 0 ? ' (Activo)' : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="acciones">
                <button type="button" class="secundario" id="marcarTodo">Marcar todo</button>
                <button type="button" class="secundario" id="desmarcarTodo">Desmarcar todo</button>
                <button type="submit" class="principal" <?php echo (!$materias || !$grados || !$anosLectivos) ? 'disabled' : ''; ?>>Generar asignaciones seleccionadas</button>
            </div>
        </div>

        <p class="contexto">Docente de la sesión: <strong><?php echo genasig_escapar($idDocente !== '' ? $idDocente : 'No identificado'); ?></strong> · Institución: <strong><?php echo genasig_escapar($nombreInstitucion); ?></strong> (ID <?php echo $idInstitucion; ?>)</p>
        <div class="aviso aviso-info">Todas las materias aparecen seleccionadas para los grados 6.º a 11.º. Puede marcar o desmarcar individualmente cualquier casilla antes de generar.</div>

        <div class="matriz-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Materia / Grado</th>
                        <?php foreach ($grados as $nombreGrado): ?><th><?php echo genasig_escapar($nombreGrado); ?></th><?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($materias as $idMateria => $nombreMateria): ?>
                        <tr>
                            <td><?php echo genasig_escapar($nombreMateria); ?></td>
                            <?php foreach ($grados as $idGrado => $nombreGrado): ?>
                                <td>
                                    <input type="checkbox" name="combinaciones[]" value="<?php echo $idMateria . ':' . $idGrado; ?>" <?php echo in_array(trim($nombreGrado), ['6', '7', '8', '9', '10', '11'], true) ? 'checked' : ''; ?> aria-label="<?php echo genasig_escapar($nombreMateria . ' - ' . $nombreGrado); ?>">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
    </div>

    <div class="panel-tab" data-panel="activas" hidden>
        <?php if ($resultadosEliminacion || $resultadosBloqueados): ?>
            <section class="panel">
                <h2>Resultado de la actualización</h2>
                <div class="resumen">
                    <span class="contador contador-creado">Eliminadas: <?php echo count($resultadosEliminacion); ?></span>
                    <span class="contador contador-existente">Bloqueadas por datos relacionados: <?php echo count($resultadosBloqueados); ?></span>
                </div>
                <div class="matriz-contenedor">
                    <table>
                        <thead><tr><th>Materia</th><th>Grado</th><th>id_asignacion</th><th>Resultado</th></tr></thead>
                        <tbody>
                        <?php foreach ($resultadosEliminacion as $resultado): ?>
                            <tr>
                                <td><?php echo genasig_escapar($resultado['materia']); ?></td>
                                <td><?php echo genasig_escapar($resultado['grado']); ?></td>
                                <td>#<?php echo (int)$resultado['id_asignacion']; ?></td>
                                <td><span class="estado estado-eliminada">Eliminada</span></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php foreach ($resultadosBloqueados as $resultado): ?>
                            <tr>
                                <td><?php echo genasig_escapar($resultado['materia']); ?></td>
                                <td><?php echo genasig_escapar($resultado['grado']); ?></td>
                                <td>#<?php echo (int)$resultado['id_asignacion']; ?></td>
                                <td><span class="estado estado-bloqueada">No eliminada: <?php echo genasig_escapar($resultado['detalle']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <form method="post" class="panel" id="formEliminar">
            <input type="hidden" name="csrf_token" value="<?php echo genasig_escapar((string)($_SESSION['genasig_csrf'] ?? '')); ?>">
            <input type="hidden" name="accion" value="eliminar_asignaciones">
            <input type="hidden" name="confirmar_eliminacion" id="confirmarEliminacion" value="">
            <div class="toolbar">
                <div>
                    <h2>Asignaciones del año lectivo activo</h2>
                    <p class="contexto">Año: <strong><?php echo genasig_escapar((string)($anosLectivos[$idAnoActivo]['nombre'] ?? 'No disponible')); ?></strong> · <?php echo count($asignacionesActivas); ?> combinación(es) y <?php echo $cantidadAsignacionesActivas; ?> asignación(es) de <?php echo genasig_escapar($nombreInstitucion); ?>.</p>
                </div>
                <div class="acciones">
                    <button type="submit" class="peligro" <?php echo (!$asignacionesActivas || $idAnoActivo <= 0) ? 'disabled' : ''; ?>>Eliminar las asignaciones desmarcadas</button>
                </div>
            </div>

            <?php if ($idAnoActivo <= 0): ?>
                <div class="aviso aviso-error">No hay un año lectivo activo configurado.</div>
            <?php elseif ($idDocente === ''): ?>
                <div class="aviso aviso-error">No se identificó el docente de la sesión actual.</div>
            <?php else: ?>
                <div class="aviso aviso-info">Las casillas marcadas son las asignaciones activas existentes. Desmarque una y confirme para solicitar su eliminación. Si tiene horarios, actividades, notas, inscripciones o respuestas, el sistema la conservará para proteger esos datos.</div>
                <div class="matriz-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Materia / Grado</th>
                                <?php foreach ($grados as $nombreGrado): ?><th><?php echo genasig_escapar($nombreGrado); ?></th><?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materias as $idMateria => $nombreMateria): ?>
                                <tr>
                                    <td><?php echo genasig_escapar($nombreMateria); ?></td>
                                    <?php foreach ($grados as $idGrado => $nombreGrado): ?>
                                        <?php $idsAsignacion = $asignacionesActivas[$idMateria . ':' . $idGrado] ?? []; ?>
                                        <td>
                                            <?php if ($idsAsignacion): ?>
                                                <?php $listaIds = implode(',', $idsAsignacion); $etiquetaIds = '#' . implode(', #', $idsAsignacion); ?>
                                                <input type="checkbox" name="asignaciones_mantener[]" value="<?php echo $listaIds; ?>" data-ids="<?php echo $listaIds; ?>" checked title="Asignación(es) <?php echo $etiquetaIds; ?>" aria-label="<?php echo genasig_escapar($nombreMateria . ' - ' . $nombreGrado . ', asignaciones ' . $etiquetaIds); ?>">
                                                <small class="ids-asignacion"><?php echo genasig_escapar($etiquetaIds); ?></small>
                                            <?php else: ?>
                                                <span class="sin-asignacion">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </form>
    </div>
</main>
<script>
(() => {
    const checks = () => document.querySelectorAll('input[name="combinaciones[]"]');
    document.getElementById('marcarTodo')?.addEventListener('click', () => checks().forEach(check => { check.checked = true; }));
    document.getElementById('desmarcarTodo')?.addEventListener('click', () => checks().forEach(check => { check.checked = false; }));

    const tabs = document.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('[data-panel]');
    function abrirPestana(nombre) {
        tabs.forEach(tab => {
            const activa = tab.dataset.tab === nombre;
            tab.classList.toggle('activa', activa);
            tab.setAttribute('aria-selected', activa ? 'true' : 'false');
        });
        panels.forEach(panel => { panel.hidden = panel.dataset.panel !== nombre; });
    }
    tabs.forEach(tab => tab.addEventListener('click', () => abrirPestana(tab.dataset.tab)));
    abrirPestana(<?php echo json_encode($accionSolicitada === 'eliminar_asignaciones' ? 'activas' : 'generar'); ?>);

    document.getElementById('formEliminar')?.addEventListener('submit', event => {
        const casillas = Array.from(document.querySelectorAll('input[name="asignaciones_mantener[]"]'));
        const eliminables = casillas.filter(casilla => !casilla.checked);
        if (eliminables.length === 0) {
            event.preventDefault();
            window.alert('No ha desmarcado ninguna asignación activa.');
            return;
        }
        const cantidadAsignaciones = eliminables.reduce((total, casilla) => total + (casilla.dataset.ids || '').split(',').filter(Boolean).length, 0);
        const confirmar = window.confirm(`¿Confirma eliminar ${cantidadAsignaciones} asignación(es) en ${eliminables.length} combinación(es) desmarcada(s)? Las que tengan datos relacionados no se eliminarán.`);
        if (!confirmar) {
            event.preventDefault();
            return;
        }
        document.getElementById('confirmarEliminacion').value = '1';
    });
})();
</script>
</body>
</html>
