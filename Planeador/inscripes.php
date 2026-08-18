<?php
/**
 * Gestor de inscripciones a asignaturas.
 *
 * Incluye dos flujos en una única pantalla:
 * - Gestión individual con creación, edición, consulta y eliminación.
 * - Inscripción masiva por grado, tomando el grado desde usuario.observaciones.
 */

declare(strict_types=1);

session_start();

const INSCRIPES_ESTADOS = ['Aprobado', 'No aprobado', 'En curso', 'Retirado'];

function inscripes_es_solicitud_api(): bool
{
    return isset($_REQUEST['action']);
}

function inscripes_responder(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function inscripes_error(string $mensaje, int $status = 422): void
{
    inscripes_responder(['ok' => false, 'message' => $mensaje], $status);
}

function inscripes_escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function inscripes_es_gestor(array $sesion): bool
{
    $roles = strtolower(trim((string)($sesion['rol'] ?? '') . ',' . (string)($sesion['tipo'] ?? '')));
    return (bool) preg_match('/(^|[\s,;])(superadmin|administrador|admin|directivo|coordinador|docente)(?=$|[\s,;])/', $roles);
}

function inscripes_es_administrador(array $sesion): bool
{
    $roles = strtolower(trim((string)($sesion['rol'] ?? '') . ',' . (string)($sesion['tipo'] ?? '')));
    return (bool) preg_match('/(^|[\s,;])(superadmin|administrador|admin|directivo|coordinador)(?=$|[\s,;])/', $roles);
}

function inscripes_fecha_valida(string $fecha): bool
{
    $objeto = DateTime::createFromFormat('Y-m-d', $fecha);
    return $objeto instanceof DateTime && $objeto->format('Y-m-d') === $fecha;
}

function inscripes_lista_enteros(mixed $valor): array
{
    if (is_string($valor)) {
        $decodificado = json_decode($valor, true);
        $valor = is_array($decodificado) ? $decodificado : [];
    }
    if (!is_array($valor)) {
        return [];
    }

    $resultado = [];
    foreach ($valor as $item) {
        $numero = (int) $item;
        if ($numero > 0) {
            $resultado[$numero] = $numero;
        }
    }
    return array_values($resultado);
}

function inscripes_lista_textos(mixed $valor): array
{
    if (is_string($valor)) {
        $decodificado = json_decode($valor, true);
        $valor = is_array($decodificado) ? $decodificado : [];
    }
    if (!is_array($valor)) {
        return [];
    }

    $resultado = [];
    foreach ($valor as $item) {
        $texto = trim((string) $item);
        if ($texto !== '') {
            $resultado[$texto] = $texto;
        }
    }
    return array_values($resultado);
}

function inscripes_enlazar(mysqli_stmt $sentencia, string $tipos, array $parametros): void
{
    if ($tipos !== '') {
        $sentencia->bind_param($tipos, ...$parametros);
    }
}

function inscripes_obtener_categoria(mysqli $mysqli, int $idGrado): ?array
{
    $sentencia = $mysqli->prepare(
        'SELECT id_categoria_curso, nombre_categoria_curso
         FROM categoria_curso
         WHERE id_categoria_curso = ?
         LIMIT 1'
    );
    $sentencia->bind_param('i', $idGrado);
    $sentencia->execute();
    $categoria = $sentencia->get_result()->fetch_assoc() ?: null;
    $sentencia->close();
    return $categoria;
}

function inscripes_agregar_alcance_asignacion(array &$condiciones, string &$tipos, array &$parametros, string $alias = 'a'): void
{
    global $inscripesContexto;

    $condiciones[] = $alias . '.institucion_educativa = ?';
    $tipos .= 'i';
    $parametros[] = $inscripesContexto['institucion'];

    if ($inscripesContexto['solo_docente']) {
        $condiciones[] = $alias . '.id_docente = ?';
        $tipos .= 's';
        $parametros[] = $inscripesContexto['usuario'];
    }
}

function inscripes_asignacion_permitida(mysqli $mysqli, int $idAsignacion): ?array
{
    $condiciones = ['a.id_asignacion = ?'];
    $tipos = 'i';
    $parametros = [$idAsignacion];
    inscripes_agregar_alcance_asignacion($condiciones, $tipos, $parametros);

    $sql = 'SELECT a.id_asignacion, a.id_asignatura, a.id_categoria_curso, a.ano_lectivo
            FROM asignacion a
            WHERE ' . implode(' AND ', $condiciones) . '
            LIMIT 1';
    $sentencia = $mysqli->prepare($sql);
    inscripes_enlazar($sentencia, $tipos, $parametros);
    $sentencia->execute();
    $asignacion = $sentencia->get_result()->fetch_assoc() ?: null;
    $sentencia->close();
    return $asignacion;
}

function inscripes_estudiante_permitido(mysqli $mysqli, string $idEstudiante): ?array
{
    $sentencia = $mysqli->prepare(
        "SELECT id_usuario, nombre, apellido, observaciones
         FROM usuario
         WHERE id_usuario = ?
           AND rol LIKE '%estudiante%'
         LIMIT 1"
    );
    $sentencia->bind_param('s', $idEstudiante);
    $sentencia->execute();
    $estudiante = $sentencia->get_result()->fetch_assoc() ?: null;
    $sentencia->close();
    return $estudiante;
}

function inscripes_obtener_asignaciones(mysqli $mysqli, int $ano, int $grado = 0): array
{
    $condiciones = ['a.ano_lectivo = ?'];
    $tipos = 'i';
    $parametros = [$ano];
    inscripes_agregar_alcance_asignacion($condiciones, $tipos, $parametros);

    if ($grado > 0) {
        $condiciones[] = 'a.id_categoria_curso = ?';
        $tipos .= 'i';
        $parametros[] = $grado;
    }

    $sql = "SELECT
                a.id_asignacion,
                a.id_asignatura,
                a.id_categoria_curso,
                a.ano_lectivo,
                COALESCE(NULLIF(mo.nombre_materia, ''), NULLIF(m.nombre_materia, ''), CONCAT('Asignatura #', a.id_asignatura)) AS materia,
                c.nombre_categoria_curso AS grado,
                CONCAT(COALESCE(d.nombre, ''), ' ', COALESCE(d.apellido, '')) AS docente
            FROM asignacion a
            INNER JOIN categoria_curso c ON c.id_categoria_curso = a.id_categoria_curso
            LEFT JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
            LEFT JOIN materia m ON m.id_materia = a.id_asignatura
            LEFT JOIN usuario d ON d.id_usuario = a.id_docente
            WHERE " . implode(' AND ', $condiciones) . '
            ORDER BY c.id_categoria_curso, materia, docente, a.id_asignacion';
    $sentencia = $mysqli->prepare($sql);
    inscripes_enlazar($sentencia, $tipos, $parametros);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    $asignaciones = [];
    while ($fila = $resultado->fetch_assoc()) {
        $fila['id_asignacion'] = (int) $fila['id_asignacion'];
        $fila['id_asignatura'] = (int) $fila['id_asignatura'];
        $fila['id_categoria_curso'] = (int) $fila['id_categoria_curso'];
        $fila['ano_lectivo'] = (int) $fila['ano_lectivo'];
        $fila['docente'] = trim((string) $fila['docente']) ?: 'Sin docente asignado';
        $asignaciones[] = $fila;
    }
    $sentencia->close();
    return $asignaciones;
}

function inscripes_obtener_catalogos(mysqli $mysqli): array
{
    $anos = [];
    $resultadoAnos = $mysqli->query(
        'SELECT id_ano_lectivo, nombre_ano_lectivo, estado
         FROM ano_lectivo
         ORDER BY (LOWER(estado) = \'activo\') DESC, nombre_ano_lectivo DESC'
    );
    while ($fila = $resultadoAnos->fetch_assoc()) {
        $fila['id_ano_lectivo'] = (int) $fila['id_ano_lectivo'];
        $anos[] = $fila;
    }

    $grados = [];
    $resultadoGrados = $mysqli->query(
        'SELECT id_categoria_curso, nombre_categoria_curso
         FROM categoria_curso
         ORDER BY id_categoria_curso'
    );
    while ($fila = $resultadoGrados->fetch_assoc()) {
        $fila['id_categoria_curso'] = (int) $fila['id_categoria_curso'];
        $grados[] = $fila;
    }

    $anoActivo = 0;
    foreach ($anos as $ano) {
        if (mb_strtolower((string) $ano['estado'], 'UTF-8') === 'activo') {
            $anoActivo = (int) $ano['id_ano_lectivo'];
            break;
        }
    }
    if ($anoActivo === 0 && $anos) {
        $anoActivo = (int) $anos[0]['id_ano_lectivo'];
    }

    return ['anos' => $anos, 'grados' => $grados, 'ano_activo' => $anoActivo];
}

function inscripes_buscar_estudiantes(mysqli $mysqli, string $busqueda, int $grado = 0, int $limite = 40): array
{
    $condiciones = ["u.rol LIKE '%estudiante%'", "LOWER(COALESCE(u.estado, 'activo')) <> 'inactivo'"];
    $tipos = '';
    $parametros = [];

    if ($grado > 0) {
        $categoria = inscripes_obtener_categoria($mysqli, $grado);
        if (!$categoria) {
            return [];
        }
        $condiciones[] = "TRIM(COALESCE(u.observaciones, '')) IN (?, ?)";
        $tipos .= 'ss';
        $parametros[] = (string) $categoria['id_categoria_curso'];
        $parametros[] = trim((string) $categoria['nombre_categoria_curso']);
    }

    $busqueda = trim($busqueda);
    if ($busqueda !== '') {
        $condiciones[] = "(LOWER(u.id_usuario) LIKE ? OR LOWER(u.nombre) LIKE ? OR LOWER(u.apellido) LIKE ? OR LOWER(CONCAT(u.nombre, ' ', u.apellido)) LIKE ?)";
        $termino = '%' . mb_strtolower($busqueda, 'UTF-8') . '%';
        $tipos .= 'ssss';
        array_push($parametros, $termino, $termino, $termino, $termino);
    }

    $limite = max(1, min(200, $limite));
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido, COALESCE(u.observaciones, '') AS grado
            FROM usuario u
            WHERE " . implode(' AND ', $condiciones) . '
            ORDER BY u.apellido, u.nombre, u.id_usuario
            LIMIT ' . $limite;
    $sentencia = $mysqli->prepare($sql);
    inscripes_enlazar($sentencia, $tipos, $parametros);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    $estudiantes = [];
    while ($fila = $resultado->fetch_assoc()) {
        $estudiantes[] = $fila;
    }
    $sentencia->close();
    return $estudiantes;
}

function inscripes_listar_inscripciones(mysqli $mysqli): array
{
    $ano = max(0, (int) ($_GET['ano'] ?? 0));
    $grado = max(0, (int) ($_GET['grado'] ?? 0));
    $asignacion = max(0, (int) ($_GET['asignacion'] ?? 0));
    $estado = trim((string) ($_GET['estado'] ?? ''));
    $busqueda = trim((string) ($_GET['busqueda'] ?? ''));
    $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
    $porPagina = 12;

    $condiciones = ['1 = 1'];
    $tipos = '';
    $parametros = [];
    inscripes_agregar_alcance_asignacion($condiciones, $tipos, $parametros);

    if ($ano > 0) {
        $condiciones[] = 'a.ano_lectivo = ?';
        $tipos .= 'i';
        $parametros[] = $ano;
    }
    if ($grado > 0) {
        $condiciones[] = 'a.id_categoria_curso = ?';
        $tipos .= 'i';
        $parametros[] = $grado;
    }
    if ($asignacion > 0) {
        $condiciones[] = 'i.id_asignacion = ?';
        $tipos .= 'i';
        $parametros[] = $asignacion;
    }
    if (in_array($estado, INSCRIPES_ESTADOS, true)) {
        $condiciones[] = 'i.estado_inscripcion = ?';
        $tipos .= 's';
        $parametros[] = $estado;
    }
    if ($busqueda !== '') {
        $condiciones[] = "(LOWER(i.id_estudiante) LIKE ? OR LOWER(u.nombre) LIKE ? OR LOWER(u.apellido) LIKE ? OR LOWER(CONCAT(u.nombre, ' ', u.apellido)) LIKE ? OR LOWER(COALESCE(mo.nombre_materia, m.nombre_materia, '')) LIKE ?)";
        $termino = '%' . mb_strtolower($busqueda, 'UTF-8') . '%';
        $tipos .= 'sssss';
        array_push($parametros, $termino, $termino, $termino, $termino, $termino);
    }

    $desde = ' FROM inscripcion i
               INNER JOIN asignacion a ON a.id_asignacion = i.id_asignacion
               INNER JOIN usuario u ON u.id_usuario = i.id_estudiante
               INNER JOIN categoria_curso c ON c.id_categoria_curso = a.id_categoria_curso
               LEFT JOIN materia_oficial mo ON mo.id_materia = a.id_asignatura
               LEFT JOIN materia m ON m.id_materia = a.id_asignatura
               WHERE ' . implode(' AND ', $condiciones);

    $sentenciaTotal = $mysqli->prepare('SELECT COUNT(*) AS total' . $desde);
    inscripes_enlazar($sentenciaTotal, $tipos, $parametros);
    $sentenciaTotal->execute();
    $total = (int) ($sentenciaTotal->get_result()->fetch_assoc()['total'] ?? 0);
    $sentenciaTotal->close();

    $offset = ($pagina - 1) * $porPagina;
    $sql = "SELECT
                i.id_inscripcion, i.id_asignacion, i.id_estudiante, i.fecha_inscripcion,
                i.estado_inscripcion, COALESCE(i.obserbaciones_inscripcion, '') AS observaciones,
                u.nombre, u.apellido, COALESCE(u.observaciones, '') AS grado_estudiante,
                a.id_categoria_curso, a.ano_lectivo,
                c.nombre_categoria_curso AS grado,
                COALESCE(NULLIF(mo.nombre_materia, ''), NULLIF(m.nombre_materia, ''), CONCAT('Asignatura #', a.id_asignatura)) AS materia
            " . $desde . '
            ORDER BY i.id_inscripcion DESC
            LIMIT ?, ?';
    $sentencia = $mysqli->prepare($sql);
    $tiposListado = $tipos . 'ii';
    $parametrosListado = [...$parametros, $offset, $porPagina];
    inscripes_enlazar($sentencia, $tiposListado, $parametrosListado);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    $items = [];
    while ($fila = $resultado->fetch_assoc()) {
        $fila['id_inscripcion'] = (int) $fila['id_inscripcion'];
        $fila['id_asignacion'] = (int) $fila['id_asignacion'];
        $fila['id_categoria_curso'] = (int) $fila['id_categoria_curso'];
        $fila['ano_lectivo'] = (int) $fila['ano_lectivo'];
        $items[] = $fila;
    }
    $sentencia->close();

    return [
        'items' => $items,
        'total' => $total,
        'pagina' => $pagina,
        'por_pagina' => $porPagina,
        'paginas' => max(1, (int) ceil($total / $porPagina)),
    ];
}

function inscripes_obtener_inscripcion_permitida(mysqli $mysqli, int $idInscripcion): ?array
{
    $condiciones = ['i.id_inscripcion = ?'];
    $tipos = 'i';
    $parametros = [$idInscripcion];
    inscripes_agregar_alcance_asignacion($condiciones, $tipos, $parametros);
    $sql = 'SELECT i.id_inscripcion, i.id_asignacion, i.id_estudiante
            FROM inscripcion i
            INNER JOIN asignacion a ON a.id_asignacion = i.id_asignacion
            WHERE ' . implode(' AND ', $condiciones) . '
            LIMIT 1';
    $sentencia = $mysqli->prepare($sql);
    inscripes_enlazar($sentencia, $tipos, $parametros);
    $sentencia->execute();
    $inscripcion = $sentencia->get_result()->fetch_assoc() ?: null;
    $sentencia->close();
    return $inscripcion;
}

function inscripes_existe_duplicado(mysqli $mysqli, int $idAsignacion, string $idEstudiante, int $ignorar = 0): bool
{
    $sql = 'SELECT id_inscripcion
            FROM inscripcion
            WHERE id_asignacion = ? AND id_estudiante = ?';
    $tipos = 'is';
    $parametros = [$idAsignacion, $idEstudiante];
    if ($ignorar > 0) {
        $sql .= ' AND id_inscripcion <> ?';
        $tipos .= 'i';
        $parametros[] = $ignorar;
    }
    $sql .= ' LIMIT 1';
    $sentencia = $mysqli->prepare($sql);
    inscripes_enlazar($sentencia, $tipos, $parametros);
    $sentencia->execute();
    $existe = (bool) $sentencia->get_result()->fetch_assoc();
    $sentencia->close();
    return $existe;
}

function inscripes_datos_formulario(): array
{
    $idAsignacion = max(0, (int) ($_POST['id_asignacion'] ?? 0));
    $idEstudiante = trim((string) ($_POST['id_estudiante'] ?? ''));
    $fecha = trim((string) ($_POST['fecha_inscripcion'] ?? date('Y-m-d')));
    $estado = trim((string) ($_POST['estado_inscripcion'] ?? 'En curso'));
    $observaciones = trim((string) ($_POST['observaciones'] ?? ''));

    if ($idAsignacion <= 0 || $idEstudiante === '') {
        throw new InvalidArgumentException('Seleccione un estudiante y una asignatura.');
    }
    if (!inscripes_fecha_valida($fecha)) {
        throw new InvalidArgumentException('La fecha de inscripción no es válida.');
    }
    if (!in_array($estado, INSCRIPES_ESTADOS, true)) {
        throw new InvalidArgumentException('Seleccione un estado de inscripción válido.');
    }
    if (mb_strlen($observaciones, 'UTF-8') > 255) {
        throw new InvalidArgumentException('Las observaciones pueden tener máximo 255 caracteres.');
    }
    return [$idAsignacion, $idEstudiante, $fecha, $estado, $observaciones];
}

function inscripes_crear_inscripcion(mysqli $mysqli): array
{
    [$idAsignacion, $idEstudiante, $fecha, $estado, $observaciones] = inscripes_datos_formulario();
    if (!inscripes_asignacion_permitida($mysqli, $idAsignacion)) {
        throw new InvalidArgumentException('La asignatura seleccionada no está disponible para esta institución.');
    }
    if (!inscripes_estudiante_permitido($mysqli, $idEstudiante)) {
        throw new InvalidArgumentException('El estudiante seleccionado no existe o no está habilitado.');
    }
    if (inscripes_existe_duplicado($mysqli, $idAsignacion, $idEstudiante)) {
        throw new InvalidArgumentException('El estudiante ya tiene una inscripción para esta asignatura. Puede editar la inscripción existente.');
    }

    $fechaRetiro = $estado === 'Retirado' ? date('Y-m-d H:i:s') : null;
    $sentencia = $mysqli->prepare(
        'INSERT INTO inscripcion
            (id_asignacion, id_estudiante, fecha_inscripcion, estado_inscripcion, obserbaciones_inscripcion, fecha_retiro)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $sentencia->bind_param('isssss', $idAsignacion, $idEstudiante, $fecha, $estado, $observaciones, $fechaRetiro);
    $sentencia->execute();
    $id = (int) $mysqli->insert_id;
    $sentencia->close();
    return ['id' => $id];
}

function inscripes_actualizar_inscripcion(mysqli $mysqli): void
{
    $idInscripcion = max(0, (int) ($_POST['id_inscripcion'] ?? 0));
    if ($idInscripcion <= 0 || !inscripes_obtener_inscripcion_permitida($mysqli, $idInscripcion)) {
        throw new InvalidArgumentException('No se encontró la inscripción solicitada.');
    }
    [$idAsignacion, $idEstudiante, $fecha, $estado, $observaciones] = inscripes_datos_formulario();
    if (!inscripes_asignacion_permitida($mysqli, $idAsignacion)) {
        throw new InvalidArgumentException('La asignatura seleccionada no está disponible para esta institución.');
    }
    if (!inscripes_estudiante_permitido($mysqli, $idEstudiante)) {
        throw new InvalidArgumentException('El estudiante seleccionado no existe o no está habilitado.');
    }
    if (inscripes_existe_duplicado($mysqli, $idAsignacion, $idEstudiante, $idInscripcion)) {
        throw new InvalidArgumentException('Ya existe otra inscripción para esta combinación de estudiante y asignatura.');
    }

    $fechaRetiro = $estado === 'Retirado' ? date('Y-m-d H:i:s') : null;
    $sentencia = $mysqli->prepare(
        'UPDATE inscripcion
         SET id_asignacion = ?, id_estudiante = ?, fecha_inscripcion = ?,
             estado_inscripcion = ?, obserbaciones_inscripcion = ?, fecha_retiro = ?
         WHERE id_inscripcion = ?'
    );
    $sentencia->bind_param('isssssi', $idAsignacion, $idEstudiante, $fecha, $estado, $observaciones, $fechaRetiro, $idInscripcion);
    $sentencia->execute();
    $sentencia->close();
}

function inscripes_eliminar_inscripcion(mysqli $mysqli): void
{
    $idInscripcion = max(0, (int) ($_POST['id_inscripcion'] ?? 0));
    if ($idInscripcion <= 0 || !inscripes_obtener_inscripcion_permitida($mysqli, $idInscripcion)) {
        throw new InvalidArgumentException('No se encontró la inscripción solicitada.');
    }
    $sentencia = $mysqli->prepare('DELETE FROM inscripcion WHERE id_inscripcion = ?');
    $sentencia->bind_param('i', $idInscripcion);
    $sentencia->execute();
    $sentencia->close();
}

function inscripes_contexto_masivo(mysqli $mysqli, int $ano, int $grado): array
{
    if ($ano <= 0 || $grado <= 0) {
        throw new InvalidArgumentException('Seleccione un año lectivo y un grado.');
    }
    $categoria = inscripes_obtener_categoria($mysqli, $grado);
    if (!$categoria) {
        throw new InvalidArgumentException('El grado seleccionado no existe.');
    }

    $asignaciones = inscripes_obtener_asignaciones($mysqli, $ano, $grado);
    $idsAsignaciones = array_map(static fn (array $fila): int => (int) $fila['id_asignacion'], $asignaciones);
    $estudiantes = inscripes_buscar_estudiantes($mysqli, '', $grado, 2000);

    if (!$idsAsignaciones || !$estudiantes) {
        foreach ($estudiantes as &$estudiante) {
            $estudiante['inscripciones_activas'] = 0;
        }
        unset($estudiante);
        return ['categoria' => $categoria, 'asignaciones' => $asignaciones, 'estudiantes' => $estudiantes];
    }

    $marcadores = implode(',', array_fill(0, count($idsAsignaciones), '?'));
    $tipos = 'ss' . str_repeat('i', count($idsAsignaciones));
    $parametros = [(string) $categoria['id_categoria_curso'], trim((string) $categoria['nombre_categoria_curso']), ...$idsAsignaciones];
    $sql = "SELECT
                u.id_usuario,
                COUNT(DISTINCT CASE WHEN i.estado_inscripcion <> 'Retirado' THEN i.id_asignacion END) AS inscripciones_activas
            FROM usuario u
            LEFT JOIN inscripcion i
                ON i.id_estudiante = u.id_usuario
               AND i.id_asignacion IN ($marcadores)
            WHERE u.rol LIKE '%estudiante%'
              AND LOWER(COALESCE(u.estado, 'activo')) <> 'inactivo'
              AND TRIM(COALESCE(u.observaciones, '')) IN (?, ?)
            GROUP BY u.id_usuario";

    // Los dos valores del grado van al final porque pertenecen a la cláusula WHERE.
    $tipos = str_repeat('i', count($idsAsignaciones)) . 'ss';
    $parametros = [...$idsAsignaciones, (string) $categoria['id_categoria_curso'], trim((string) $categoria['nombre_categoria_curso'])];
    $sentencia = $mysqli->prepare($sql);
    inscripes_enlazar($sentencia, $tipos, $parametros);
    $sentencia->execute();
    $resultado = $sentencia->get_result();
    $conteos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $conteos[(string) $fila['id_usuario']] = (int) $fila['inscripciones_activas'];
    }
    $sentencia->close();

    foreach ($estudiantes as &$estudiante) {
        $estudiante['inscripciones_activas'] = $conteos[(string) $estudiante['id_usuario']] ?? 0;
    }
    unset($estudiante);
    return ['categoria' => $categoria, 'asignaciones' => $asignaciones, 'estudiantes' => $estudiantes];
}

function inscripes_inscribir_masivo(mysqli $mysqli): array
{
    $ano = max(0, (int) ($_POST['ano'] ?? 0));
    $grado = max(0, (int) ($_POST['grado'] ?? 0));
    $idsEstudiantes = inscripes_lista_textos($_POST['estudiantes'] ?? []);
    $idsAsignacionesSolicitadas = inscripes_lista_enteros($_POST['asignaciones'] ?? []);
    $estado = trim((string) ($_POST['estado_inscripcion'] ?? 'En curso'));
    $observaciones = trim((string) ($_POST['observaciones'] ?? ''));

    if ($ano <= 0 || $grado <= 0 || !$idsEstudiantes || !$idsAsignacionesSolicitadas) {
        throw new InvalidArgumentException('Seleccione año, grado, por lo menos un estudiante y una asignatura.');
    }
    if (!in_array($estado, INSCRIPES_ESTADOS, true)) {
        throw new InvalidArgumentException('Seleccione un estado de inscripción válido.');
    }
    if (mb_strlen($observaciones, 'UTF-8') > 255) {
        throw new InvalidArgumentException('Las observaciones pueden tener máximo 255 caracteres.');
    }
    $categoria = inscripes_obtener_categoria($mysqli, $grado);
    if (!$categoria) {
        throw new InvalidArgumentException('El grado seleccionado no existe.');
    }

    $asignacionesDisponibles = inscripes_obtener_asignaciones($mysqli, $ano, $grado);
    $permitidas = [];
    foreach ($asignacionesDisponibles as $asignacion) {
        $permitidas[(int) $asignacion['id_asignacion']] = true;
    }
    $idsAsignaciones = array_values(array_filter(
        $idsAsignacionesSolicitadas,
        static fn (int $id): bool => isset($permitidas[$id])
    ));
    if (!$idsAsignaciones) {
        throw new InvalidArgumentException('Las asignaturas seleccionadas no corresponden al año y grado indicados.');
    }

    $marcadores = implode(',', array_fill(0, count($idsEstudiantes), '?'));
    $tipos = str_repeat('s', count($idsEstudiantes)) . 'ss';
    $parametros = [...$idsEstudiantes, (string) $categoria['id_categoria_curso'], trim((string) $categoria['nombre_categoria_curso'])];
    $sqlEstudiantes = "SELECT id_usuario
                        FROM usuario
                        WHERE id_usuario IN ($marcadores)
                          AND rol LIKE '%estudiante%'
                          AND LOWER(COALESCE(estado, 'activo')) <> 'inactivo'
                          AND TRIM(COALESCE(observaciones, '')) IN (?, ?)";
    $sentenciaEstudiantes = $mysqli->prepare($sqlEstudiantes);
    inscripes_enlazar($sentenciaEstudiantes, $tipos, $parametros);
    $sentenciaEstudiantes->execute();
    $resultadoEstudiantes = $sentenciaEstudiantes->get_result();
    $estudiantesValidos = [];
    while ($fila = $resultadoEstudiantes->fetch_assoc()) {
        $estudiantesValidos[] = (string) $fila['id_usuario'];
    }
    $sentenciaEstudiantes->close();
    if (!$estudiantesValidos) {
        throw new InvalidArgumentException('Ninguno de los estudiantes seleccionados pertenece actualmente a este grado.');
    }

    $creadas = 0;
    $existentes = 0;
    $fecha = date('Y-m-d');
    $fechaRetiro = $estado === 'Retirado' ? date('Y-m-d H:i:s') : null;

    $mysqli->begin_transaction();
    try {
        $verificar = $mysqli->prepare(
            'SELECT id_inscripcion FROM inscripcion WHERE id_asignacion = ? AND id_estudiante = ? LIMIT 1'
        );
        $insertar = $mysqli->prepare(
            'INSERT INTO inscripcion
                (id_asignacion, id_estudiante, fecha_inscripcion, estado_inscripcion, obserbaciones_inscripcion, fecha_retiro)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        foreach ($estudiantesValidos as $idEstudiante) {
            foreach ($idsAsignaciones as $idAsignacion) {
                $verificar->bind_param('is', $idAsignacion, $idEstudiante);
                $verificar->execute();
                if ($verificar->get_result()->fetch_assoc()) {
                    $existentes++;
                    continue;
                }
                $insertar->bind_param('isssss', $idAsignacion, $idEstudiante, $fecha, $estado, $observaciones, $fechaRetiro);
                $insertar->execute();
                $creadas++;
            }
        }
        $verificar->close();
        $insertar->close();
        $mysqli->commit();
    } catch (Throwable $error) {
        $mysqli->rollback();
        throw $error;
    }

    return [
        'creadas' => $creadas,
        'existentes' => $existentes,
        'estudiantes_validos' => count($estudiantesValidos),
        'estudiantes_omitidos' => count($idsEstudiantes) - count($estudiantesValidos),
    ];
}

$inscripesAutorizado = inscripes_es_gestor($_SESSION);
if (!$inscripesAutorizado) {
    if (inscripes_es_solicitud_api()) {
        inscripes_error('Tu sesión no tiene permisos para gestionar inscripciones.', 403);
    }
} else {
    require_once __DIR__ . '/../comun/conexion.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $mysqli->set_charset('utf8mb4');

    $inscripesContexto = [
        'institucion' => max(1, (int) ($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 1)),
        'usuario' => trim((string) ($_SESSION['id_usuario'] ?? $_SESSION['identificacion_usu'] ?? '')),
        'solo_docente' => !inscripes_es_administrador($_SESSION),
    ];

    if ($inscripesContexto['solo_docente'] && $inscripesContexto['usuario'] === '') {
        if (inscripes_es_solicitud_api()) {
            inscripes_error('No se pudo identificar el docente de la sesión.', 403);
        }
        $inscripesAutorizado = false;
    }

    if (!$inscripesAutorizado) {
        // La página muestra el mensaje de acceso al final del archivo.
    } elseif (inscripes_es_solicitud_api()) {
        $action = trim((string) ($_REQUEST['action'] ?? ''));
        $accionesEscritura = ['create', 'update', 'delete', 'bulk_enroll'];
        try {
            if (in_array($action, $accionesEscritura, true)) {
                $token = (string) ($_POST['csrf_token'] ?? '');
                if (!isset($_SESSION['inscripes_csrf']) || !hash_equals($_SESSION['inscripes_csrf'], $token)) {
                    inscripes_error('La solicitud expiró. Recarga la página e inténtalo de nuevo.', 419);
                }
            }

            switch ($action) {
                case 'bootstrap':
                    if (!isset($_SESSION['inscripes_csrf'])) {
                        $_SESSION['inscripes_csrf'] = bin2hex(random_bytes(32));
                    }
                    $catalogos = inscripes_obtener_catalogos($mysqli);
                    inscripes_responder([
                        'ok' => true,
                        'data' => $catalogos + [
                            'csrf_token' => $_SESSION['inscripes_csrf'],
                            'institucion' => $inscripesContexto['institucion'],
                        ],
                    ]);
                    break;

                case 'assignments':
                    $ano = max(0, (int) ($_GET['ano'] ?? 0));
                    if ($ano <= 0) {
                        throw new InvalidArgumentException('Seleccione un año lectivo.');
                    }
                    inscripes_responder(['ok' => true, 'data' => inscripes_obtener_asignaciones($mysqli, $ano, max(0, (int) ($_GET['grado'] ?? 0)))]);
                    break;

                case 'students':
                    $busqueda = trim((string) ($_GET['busqueda'] ?? ''));
                    if (mb_strlen($busqueda, 'UTF-8') < 2) {
                        inscripes_responder(['ok' => true, 'data' => [], 'hint' => 'Escribe al menos dos caracteres para buscar.']);
                    }
                    inscripes_responder(['ok' => true, 'data' => inscripes_buscar_estudiantes($mysqli, $busqueda, max(0, (int) ($_GET['grado'] ?? 0)))]);
                    break;

                case 'list':
                    inscripes_responder(['ok' => true, 'data' => inscripes_listar_inscripciones($mysqli)]);
                    break;

                case 'bulk_context':
                    inscripes_responder(['ok' => true, 'data' => inscripes_contexto_masivo($mysqli, max(0, (int) ($_GET['ano'] ?? 0)), max(0, (int) ($_GET['grado'] ?? 0)))]);
                    break;

                case 'create':
                    $resultado = inscripes_crear_inscripcion($mysqli);
                    inscripes_responder(['ok' => true, 'message' => 'Inscripción creada correctamente.', 'data' => $resultado]);
                    break;

                case 'update':
                    inscripes_actualizar_inscripcion($mysqli);
                    inscripes_responder(['ok' => true, 'message' => 'Inscripción actualizada correctamente.']);
                    break;

                case 'delete':
                    inscripes_eliminar_inscripcion($mysqli);
                    inscripes_responder(['ok' => true, 'message' => 'La inscripción se eliminó de forma permanente.']);
                    break;

                case 'bulk_enroll':
                    $resultado = inscripes_inscribir_masivo($mysqli);
                    $mensaje = $resultado['creadas'] . ' inscripciones creadas';
                    if ($resultado['existentes'] > 0) {
                        $mensaje .= ' · ' . $resultado['existentes'] . ' combinaciones ya existían';
                    }
                    inscripes_responder(['ok' => true, 'message' => $mensaje . '.', 'data' => $resultado]);
                    break;

                default:
                    inscripes_error('Acción no válida.', 404);
            }
        } catch (InvalidArgumentException $error) {
            inscripes_error($error->getMessage());
        } catch (Throwable $error) {
            error_log('inscripes.php: ' . $error->getMessage());
            inscripes_error('No fue posible completar la operación. Inténtalo de nuevo.', 500);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones académicas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak], .hidden { display: none !important; }
        :root { color-scheme: light; }
        body { background: #f5f7fb; }
        .app-shell { max-width: 1440px; }
        .hero-surface { background: radial-gradient(circle at top right, rgba(147, 197, 253, .38), transparent 35%), linear-gradient(115deg, #0f172a, #1d4ed8 62%, #0ea5e9); }
        .tab-button[aria-selected="true"] { color: #1d4ed8; border-color: #2563eb; background: #eff6ff; }
        .tab-button[aria-selected="false"] { color: #64748b; border-color: transparent; }
        .tab-button[aria-selected="false"]:hover { color: #1e40af; background: #f8fafc; }
        .status-En-curso { color: #1d4ed8; background: #dbeafe; }
        .status-Aprobado { color: #047857; background: #d1fae5; }
        .status-No-aprobado { color: #b45309; background: #fef3c7; }
        .status-Retirado { color: #b91c1c; background: #fee2e2; }
        .soft-card { box-shadow: 0 12px 28px rgba(15, 23, 42, .055); }
        .focus-ring:focus { outline: 0; box-shadow: 0 0 0 4px rgba(37, 99, 235, .15); border-color: #3b82f6; }
        .modal-backdrop { background: rgba(15, 23, 42, .56); backdrop-filter: blur(4px); }
        .toast-show { transform: translateY(0); opacity: 1; }
        .toast-hide { transform: translateY(-1rem); opacity: 0; pointer-events: none; }
        input[type="checkbox"] { accent-color: #2563eb; }
        .students-scroll { max-height: 430px; overflow: auto; }
        .loader { width: 20px; height: 20px; border: 3px solid #dbeafe; border-top-color: #2563eb; border-radius: 9999px; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="min-h-screen font-sans text-slate-800">
<?php if (!$inscripesAutorizado): ?>
    <main class="mx-auto flex min-h-screen max-w-xl items-center px-6">
        <section class="w-full rounded-3xl border border-slate-200 bg-white p-9 text-center shadow-xl shadow-slate-200/60">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-3xl">🔒</div>
            <h1 class="mt-6 text-2xl font-bold text-slate-900">Acceso restringido</h1>
            <p class="mt-3 text-slate-600">Este módulo está disponible para administradores, directivos y docentes con sesión iniciada.</p>
            <a href="../index.php" class="mt-7 inline-flex rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white transition hover:bg-blue-700">Volver al inicio</a>
        </section>
    </main>
<?php else: ?>
    <main class="app-shell mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <section class="hero-surface overflow-hidden rounded-3xl px-6 py-8 text-white shadow-xl shadow-blue-900/15 sm:px-8">
            <div class="flex flex-col gap-7 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-blue-50">
                        <span class="h-2 w-2 rounded-full bg-emerald-300"></span> Gestión académica
                    </div>
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Inscripciones a asignaturas</h1>
                    <p class="mt-3 max-w-xl text-sm leading-6 text-blue-100 sm:text-base">Administra matrículas individuales o registra de una vez a cada estudiante en las materias de su grado.</p>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center text-xs sm:w-[390px]">
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur-sm"><strong id="summary-total" class="block text-xl text-white">—</strong><span class="text-blue-100">Resultados</span></div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur-sm"><strong id="summary-subjects" class="block text-xl text-white">—</strong><span class="text-blue-100">Asignaturas</span></div>
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-3 py-3 backdrop-blur-sm"><strong id="summary-grade" class="block text-xl text-white">—</strong><span class="text-blue-100">Grado activo</span></div>
                </div>
            </div>
        </section>

        <section class="soft-card mt-6 rounded-3xl border border-slate-200 bg-white p-2">
            <div class="flex gap-1 overflow-x-auto" role="tablist" aria-label="Modos de inscripción">
                <button id="tab-individual" class="tab-button min-w-max rounded-2xl border px-5 py-3 text-sm font-semibold transition" type="button" role="tab" aria-controls="panel-individual" aria-selected="true">Gestión individual</button>
                <button id="tab-grade" class="tab-button min-w-max rounded-2xl border px-5 py-3 text-sm font-semibold transition" type="button" role="tab" aria-controls="panel-grade" aria-selected="false">Inscripción por grado</button>
            </div>
        </section>

        <section id="panel-individual" role="tabpanel" aria-labelledby="tab-individual" class="mt-6 space-y-5">
            <div class="soft-card rounded-3xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <label class="block text-sm font-medium text-slate-700">Año lectivo
                            <select id="filter-year" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Grado
                            <select id="filter-grade" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Asignatura
                            <select id="filter-assignment" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="0">Todas las asignaturas</option></select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Estado
                            <select id="filter-status" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm">
                                <option value="">Todos los estados</option>
                                <option value="En curso">En curso</option>
                                <option value="Aprobado">Aprobado</option>
                                <option value="No aprobado">No aprobado</option>
                                <option value="Retirado">Retirado</option>
                            </select>
                        </label>
                    </div>
                    <button id="new-enrollment" type="button" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                        <span class="text-lg leading-none">+</span> Nueva inscripción
                    </button>
                </div>
                <div class="mt-4 flex items-center gap-3 border-t border-slate-100 pt-4">
                    <div class="relative min-w-0 flex-1">
                        <span class="pointer-events-none absolute left-3 top-2.5 text-slate-400">⌕</span>
                        <input id="filter-search" type="search" placeholder="Busca por estudiante, identificación o asignatura" class="focus-ring w-full rounded-xl border border-slate-300 py-2.5 pl-9 pr-3 text-sm" autocomplete="off">
                    </div>
                    <button id="clear-filters" type="button" class="rounded-xl px-3 py-2.5 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-700">Limpiar</button>
                </div>
            </div>

            <div class="soft-card overflow-hidden rounded-3xl border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div><h2 class="font-bold text-slate-900">Inscripciones registradas</h2><p id="table-caption" class="mt-0.5 text-sm text-slate-500">Cargando información…</p></div>
                    <div id="table-loader" class="loader"></div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3 font-semibold sm:px-6">Estudiante</th><th class="px-5 py-3 font-semibold">Asignatura</th><th class="px-5 py-3 font-semibold">Grado</th><th class="px-5 py-3 font-semibold">Estado</th><th class="px-5 py-3 font-semibold">Fecha</th><th class="px-5 py-3 text-right font-semibold sm:px-6">Acciones</th></tr></thead>
                        <tbody id="enrollment-table" class="divide-y divide-slate-100 bg-white"></tbody>
                    </table>
                </div>
                <div id="pagination" class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4 sm:px-6"></div>
            </div>
        </section>

        <section id="panel-grade" role="tabpanel" aria-labelledby="tab-grade" class="mt-6 hidden space-y-5">
            <div class="soft-card rounded-3xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-end">
                    <div class="grid flex-1 grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-medium text-slate-700">Año lectivo
                            <select id="bulk-year" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></select>
                        </label>
                        <label class="block text-sm font-medium text-slate-700">Grado de los estudiantes
                            <select id="bulk-grade" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></select>
                        </label>
                    </div>
                    <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800 lg:max-w-md">El grado se toma del campo <code class="rounded bg-white px-1.5 py-0.5 text-xs">usuario.observaciones</code>. Solo se listan estudiantes activos.</div>
                </div>
            </div>

            <div id="bulk-empty" class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center text-slate-500">Selecciona un año y un grado para preparar la inscripción masiva.</div>
            <div id="bulk-content" class="hidden space-y-5">
                <div class="soft-card rounded-3xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                        <div><h2 class="font-bold text-slate-900">1. Materias que se asignarán</h2><p id="bulk-subtitle" class="mt-1 text-sm text-slate-500"></p></div>
                        <label class="flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700"><input id="check-all-subjects" type="checkbox" checked> Todas</label>
                    </div>
                    <div id="bulk-subjects" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3"></div>
                </div>

                <div class="soft-card overflow-hidden rounded-3xl border border-slate-200 bg-white">
                    <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div><h2 class="font-bold text-slate-900">2. Estudiantes del grado</h2><p id="bulk-student-caption" class="mt-1 text-sm text-slate-500"></p></div>
                        <div class="flex items-center gap-2"><button id="bulk-select-visible" type="button" class="rounded-lg px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50">Seleccionar todos</button><button id="bulk-unselect" type="button" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Quitar selección</button></div>
                    </div>
                    <div class="border-b border-slate-100 px-5 py-3 sm:px-6"><input id="bulk-search" type="search" placeholder="Filtrar estudiantes por nombre o identificación" class="focus-ring w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm" autocomplete="off"></div>
                    <div id="bulk-students" class="students-scroll divide-y divide-slate-100"></div>
                </div>

                <div class="soft-card rounded-3xl border border-blue-100 bg-white p-5 sm:p-6">
                    <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block text-sm font-medium text-slate-700">Estado inicial
                                <select id="bulk-status" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="En curso">En curso</option><option value="Aprobado">Aprobado</option><option value="No aprobado">No aprobado</option><option value="Retirado">Retirado</option></select>
                            </label>
                            <label class="block text-sm font-medium text-slate-700">Observación común <span class="font-normal text-slate-400">(opcional)</span>
                                <input id="bulk-note" maxlength="255" type="text" placeholder="Ej. Matrícula inicio de año" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                            </label>
                        </div>
                        <button id="bulk-enroll" type="button" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:shadow-none">Inscribir seleccionados</button>
                    </div>
                    <p id="bulk-summary" class="mt-4 text-sm text-slate-500">Las combinaciones existentes se conservarán; no se crearán duplicados.</p>
                </div>
            </div>
        </section>
    </main>

    <div id="enrollment-modal" class="modal-backdrop fixed inset-0 z-40 hidden overflow-y-auto p-4 sm:p-8" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="mx-auto flex min-h-full max-w-3xl items-center">
            <form id="enrollment-form" class="w-full overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-5 py-5 sm:px-7"><div><h2 id="modal-title" class="text-xl font-bold text-slate-900">Nueva inscripción</h2><p class="mt-1 text-sm text-slate-500">Relaciona un estudiante con una asignatura.</p></div><button id="close-modal" type="button" class="rounded-xl p-2 text-xl leading-none text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Cerrar">×</button></div>
                <div class="space-y-5 px-5 py-6 sm:px-7">
                    <input id="modal-id" type="hidden">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end"><label class="block flex-1 text-sm font-medium text-slate-700">Buscar estudiante<input id="student-search" type="search" autocomplete="off" placeholder="Nombre, apellido o identificación" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></label><label class="block text-sm font-medium text-slate-700">Filtrar por grado<select id="modal-grade" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></select></label></div>
                        <input id="modal-student-id" type="hidden" required>
                        <div id="selected-student" class="mt-3 hidden rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5 text-sm text-emerald-900"></div>
                        <div id="student-results" class="mt-3 hidden max-h-48 overflow-auto rounded-xl border border-slate-200 bg-white"></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-medium text-slate-700">Año lectivo<select id="modal-year" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"></select></label><label class="block text-sm font-medium text-slate-700">Asignatura<select id="modal-assignment" required class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="">Selecciona una asignatura</option></select></label></div>
                    <div class="grid gap-4 sm:grid-cols-2"><label class="block text-sm font-medium text-slate-700">Fecha de inscripción<input id="modal-date" type="date" required class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></label><label class="block text-sm font-medium text-slate-700">Estado<select id="modal-status" class="focus-ring mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="En curso">En curso</option><option value="Aprobado">Aprobado</option><option value="No aprobado">No aprobado</option><option value="Retirado">Retirado</option></select></label></div>
                    <label class="block text-sm font-medium text-slate-700">Observaciones <span class="font-normal text-slate-400">(opcional)</span><textarea id="modal-note" maxlength="255" rows="3" placeholder="Información relevante sobre la inscripción" class="focus-ring mt-1.5 w-full resize-y rounded-xl border border-slate-300 px-3 py-2.5 text-sm"></textarea></label>
                </div>
                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50 px-5 py-4 sm:flex-row sm:justify-end sm:px-7"><button id="cancel-modal" type="button" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-200">Cancelar</button><button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700">Guardar inscripción</button></div>
            </form>
        </div>
    </div>

    <div id="toast" class="toast-hide fixed inset-x-4 top-4 z-50 mx-auto flex max-w-md items-start gap-3 rounded-2xl border bg-white px-4 py-3 shadow-xl transition duration-300 sm:left-auto sm:right-5 sm:mx-0" aria-live="polite"><span id="toast-icon" class="mt-0.5">✓</span><p id="toast-message" class="flex-1 text-sm font-medium"></p><button id="toast-close" type="button" class="text-lg leading-none text-slate-400">×</button></div>

    <script>
        (() => {
            'use strict';
            const apiUrl = new URL(window.location.href);
            apiUrl.search = '';
            apiUrl.hash = '';
            const state = { csrf: '', years: [], grades: [], assignments: [], currentItems: [], bulk: null, bulkStudents: new Set(), searchTimer: null, tableTimer: null };
            const $ = (id) => document.getElementById(id);
            const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char]);
            const today = () => new Date().toISOString().slice(0, 10);
            const statusClass = (status) => 'status-' + String(status).replaceAll(' ', '-');
            const query = (params) => {
                const url = new URL(apiUrl.href);
                Object.entries(params).forEach(([key, value]) => url.searchParams.set(key, value));
                return url.href;
            };

            function requestWithXhr(url, method, body = null) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open(method, url, true);
                    xhr.withCredentials = true;
                    xhr.setRequestHeader('Accept', 'application/json');
                    if (method !== 'GET') xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    xhr.onload = () => {
                        let payload;
                        try { payload = JSON.parse(xhr.responseText); }
                        catch (_) { reject(new Error('El módulo devolvió una respuesta no válida.')); return; }
                        if (xhr.status < 200 || xhr.status >= 300 || !payload.ok) {
                            reject(new Error(payload.message || 'No fue posible completar la solicitud.'));
                            return;
                        }
                        resolve(payload);
                    };
                    xhr.onerror = () => reject(new Error('No se pudo conectar con el módulo de inscripciones.'));
                    xhr.ontimeout = () => reject(new Error('La solicitud tardó demasiado. Inténtalo de nuevo.'));
                    xhr.timeout = 15000;
                    xhr.send(body);
                });
            }

            async function request(action, options = {}) {
                const method = options.method || 'GET';
                const data = method === 'GET'
                    ? { action, ...(options.data || {}) }
                    : { action, csrf_token: state.csrf, ...(options.data || {}) };
                const url = method === 'GET' ? query(data) : apiUrl.href;
                const body = method === 'GET' ? null : new URLSearchParams(data).toString();

                try {
                    const response = await fetch(url, {
                        method,
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: method === 'GET'
                            ? { Accept: 'application/json' }
                            : { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', Accept: 'application/json' },
                        body
                    });
                    const payload = await response.json();
                    if (!response.ok || !payload.ok) throw new Error(payload.message || 'No fue posible completar la solicitud.');
                    return payload;
                } catch (fetchError) {
                    // Algunos navegadores integrados bloquean fetch aunque permiten XHR.
                    // La alternativa conserva la sesión y evita dejar la interfaz cargando.
                    return requestWithXhr(url, method, body);
                }
            }

            let toastTimer;
            function notify(message, type = 'success') {
                clearTimeout(toastTimer);
                const toast = $('toast');
                $('toast-message').textContent = message;
                $('toast-icon').textContent = type === 'error' ? '!' : '✓';
                toast.className = `fixed inset-x-4 top-4 z-50 mx-auto flex max-w-md items-start gap-3 rounded-2xl border px-4 py-3 shadow-xl transition duration-300 sm:left-auto sm:right-5 sm:mx-0 ${type === 'error' ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'} toast-show`;
                toastTimer = setTimeout(hideToast, 5000);
            }
            function hideToast() { $('toast').classList.remove('toast-show'); $('toast').classList.add('toast-hide'); }

            function populateSelect(select, options, emptyLabel, valueKey, labelFn, selectedValue = '') {
                const selected = String(selectedValue);
                select.innerHTML = `${emptyLabel !== null ? `<option value="">${esc(emptyLabel)}</option>` : ''}` + options.map(item => `<option value="${esc(item[valueKey])}" ${String(item[valueKey]) === selected ? 'selected' : ''}>${esc(labelFn(item))}</option>`).join('');
            }
            function assignmentLabel(item) { return `${item.materia} · ${item.grado}${item.docente && item.docente !== 'Sin docente asignado' ? ` · ${item.docente}` : ''}`; }
            function selectedYear() { return Number($('filter-year').value || 0); }
            function selectedGrade() { return Number($('filter-grade').value || 0); }

            async function loadAssignments(year, grade, select, selected = '') {
                if (!year) { select.innerHTML = '<option value="">Selecciona un año lectivo</option>'; return []; }
                const payload = await request('assignments', { data: { ano: year, grado: grade || 0 } });
                const entries = payload.data;
                const blank = select.id === 'filter-assignment' ? 'Todas las asignaturas' : 'Selecciona una asignatura';
                populateSelect(select, entries, blank, 'id_asignacion', assignmentLabel, selected);
                return entries;
            }

            async function refreshFilterAssignments(preserve = true) {
                const previous = preserve ? $('filter-assignment').value : '';
                try {
                    state.assignments = await loadAssignments(selectedYear(), selectedGrade(), $('filter-assignment'), previous);
                    if (previous && !$('filter-assignment').value) $('filter-assignment').value = '';
                    $('summary-subjects').textContent = state.assignments.length;
                    $('summary-grade').textContent = selectedGrade() ? `${$('filter-grade').selectedOptions[0].text}` : 'Todos';
                } catch (error) { notify(error.message, 'error'); }
            }

            function pagination(data) {
                const host = $('pagination');
                if (!data.total) { host.innerHTML = ''; return; }
                const prev = data.pagina > 1 ? `<button data-page="${data.pagina - 1}" class="page-button rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold hover:bg-slate-50">Anterior</button>` : '';
                const next = data.pagina < data.paginas ? `<button data-page="${data.pagina + 1}" class="page-button rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold hover:bg-slate-50">Siguiente</button>` : '';
                host.innerHTML = `<span class="text-sm text-slate-500">Página ${data.pagina} de ${data.paginas}</span><div class="flex items-center gap-2">${prev}${next}</div>`;
            }
            function renderEnrollments(data) {
                state.currentItems = data.items;
                $('summary-total').textContent = data.total;
                $('table-caption').textContent = data.total ? `${data.total} inscripción${data.total === 1 ? '' : 'es'} encontrada${data.total === 1 ? '' : 's'}.` : 'No hay inscripciones para estos filtros.';
                const table = $('enrollment-table');
                if (!data.items.length) {
                    table.innerHTML = '<tr><td colspan="6" class="px-6 py-14 text-center"><div class="mx-auto max-w-sm"><div class="text-3xl">◌</div><p class="mt-3 font-semibold text-slate-700">No hay resultados</p><p class="mt-1 text-sm text-slate-500">Ajusta los filtros o crea una nueva inscripción.</p></div></td></tr>';
                    pagination(data); return;
                }
                table.innerHTML = data.items.map(item => `<tr class="hover:bg-slate-50/80"><td class="px-5 py-4 sm:px-6"><p class="font-semibold text-slate-800">${esc(`${item.nombre} ${item.apellido}`.trim())}</p><p class="mt-0.5 text-xs text-slate-500">ID ${esc(item.id_estudiante)}${item.grado_estudiante ? ` · Observación: ${esc(item.grado_estudiante)}` : ''}</p></td><td class="px-5 py-4 text-sm font-medium text-slate-700">${esc(item.materia)}</td><td class="px-5 py-4 text-sm text-slate-600">${esc(item.grado)}</td><td class="px-5 py-4"><span class="${statusClass(item.estado_inscripcion)} inline-flex whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold">${esc(item.estado_inscripcion)}</span></td><td class="px-5 py-4 text-sm text-slate-600">${esc(item.fecha_inscripcion)}</td><td class="px-5 py-4 text-right sm:px-6"><div class="inline-flex items-center gap-1"><button class="edit-button rounded-lg px-2.5 py-2 text-sm font-bold text-blue-700 hover:bg-blue-50" data-id="${item.id_inscripcion}">Editar</button><button class="delete-button rounded-lg px-2.5 py-2 text-sm font-bold text-red-600 hover:bg-red-50" data-id="${item.id_inscripcion}">Eliminar</button></div></td></tr>`).join('');
                pagination(data);
            }
            async function loadEnrollments(page = 1) {
                $('table-loader').classList.remove('hidden');
                try {
                    const payload = await request('list', { data: { ano: selectedYear(), grado: selectedGrade(), asignacion: $('filter-assignment').value || 0, estado: $('filter-status').value, busqueda: $('filter-search').value.trim(), pagina: page } });
                    renderEnrollments(payload.data);
                } catch (error) { notify(error.message, 'error'); }
                finally { $('table-loader').classList.add('hidden'); }
            }

            function openModal(item = null) {
                $('enrollment-form').reset();
                $('modal-id').value = item ? item.id_inscripcion : '';
                $('modal-title').textContent = item ? 'Editar inscripción' : 'Nueva inscripción';
                $('modal-date').value = item ? item.fecha_inscripcion : today();
                $('modal-status').value = item ? item.estado_inscripcion : 'En curso';
                $('modal-note').value = item ? item.observaciones : '';
                const modalGrade = $('modal-grade');
                populateSelect(modalGrade, state.grades, 'Todos los grados', 'id_categoria_curso', x => `Grado ${x.nombre_categoria_curso}`, item ? item.id_categoria_curso : selectedGrade());
                populateSelect($('modal-year'), state.years, null, 'id_ano_lectivo', x => `${x.nombre_ano_lectivo}${String(x.estado).toLowerCase() === 'activo' ? ' · Activo' : ''}`, item ? item.ano_lectivo : selectedYear());
                setSelectedStudent(item ? { id_usuario: item.id_estudiante, nombre: item.nombre, apellido: item.apellido, grado: item.grado_estudiante } : null);
                $('student-search').value = '';
                $('student-results').classList.add('hidden');
                $('enrollment-modal').classList.remove('hidden');
                loadAssignments(Number($('modal-year').value), Number(modalGrade.value || 0), $('modal-assignment'), item ? item.id_asignacion : '').catch(error => notify(error.message, 'error'));
                setTimeout(() => $('student-search').focus(), 30);
            }
            function closeModal() { $('enrollment-modal').classList.add('hidden'); }
            function setSelectedStudent(student) {
                const target = $('selected-student');
                if (!student) { $('modal-student-id').value = ''; target.textContent = ''; target.classList.add('hidden'); return; }
                $('modal-student-id').value = student.id_usuario;
                target.innerHTML = `<strong>Estudiante seleccionado:</strong> ${esc(`${student.nombre} ${student.apellido}`.trim())} <span class="text-emerald-700/70">· ID ${esc(student.id_usuario)}${student.grado ? ` · Grado ${esc(student.grado)}` : ''}</span>`;
                target.classList.remove('hidden');
            }
            async function searchStudents() {
                const text = $('student-search').value.trim();
                const host = $('student-results');
                if (text.length < 2) { host.innerHTML = ''; host.classList.add('hidden'); return; }
                try {
                    const payload = await request('students', { data: { busqueda: text, grado: $('modal-grade').value || 0 } });
                    host.classList.remove('hidden');
                    host.innerHTML = payload.data.length ? payload.data.map(student => `<button type="button" class="student-option flex w-full items-center justify-between gap-3 px-3 py-3 text-left text-sm hover:bg-blue-50" data-id="${esc(student.id_usuario)}"><span><strong class="block text-slate-800">${esc(`${student.nombre} ${student.apellido}`.trim())}</strong><span class="text-xs text-slate-500">ID ${esc(student.id_usuario)}</span></span><span class="rounded-full bg-slate-100 px-2 py-1 text-xs text-slate-600">Grado ${esc(student.grado || '—')}</span></button>`).join('') : '<p class="px-3 py-4 text-sm text-slate-500">No se encontraron estudiantes activos.</p>';
                    host.querySelectorAll('.student-option').forEach((button, index) => button.addEventListener('click', () => { setSelectedStudent(payload.data[index]); host.classList.add('hidden'); $('student-search').value = ''; }));
                } catch (error) { notify(error.message, 'error'); }
            }
            async function submitEnrollment(event) {
                event.preventDefault();
                const id = $('modal-id').value;
                const data = { id_inscripcion: id, id_asignacion: $('modal-assignment').value, id_estudiante: $('modal-student-id').value, fecha_inscripcion: $('modal-date').value, estado_inscripcion: $('modal-status').value, observaciones: $('modal-note').value.trim() };
                if (!data.id_asignacion || !data.id_estudiante) { notify('Selecciona un estudiante y una asignatura.', 'error'); return; }
                const submit = $('enrollment-form').querySelector('[type="submit"]'); submit.disabled = true; submit.textContent = 'Guardando…';
                try { const payload = await request(id ? 'update' : 'create', { method: 'POST', data }); notify(payload.message); closeModal(); await loadEnrollments(); }
                catch (error) { notify(error.message, 'error'); }
                finally { submit.disabled = false; submit.textContent = 'Guardar inscripción'; }
            }

            function renderBulk() {
                const bulk = state.bulk;
                const subjectHost = $('bulk-subjects');
                $('bulk-empty').classList.toggle('hidden', !!bulk);
                $('bulk-content').classList.toggle('hidden', !bulk);
                if (!bulk) return;
                $('bulk-subtitle').textContent = `${bulk.asignaciones.length} asignatura${bulk.asignaciones.length === 1 ? '' : 's'} disponible${bulk.asignaciones.length === 1 ? '' : 's'} para grado ${bulk.categoria.nombre_categoria_curso}.`;
                subjectHost.innerHTML = bulk.asignaciones.length ? bulk.asignaciones.map(subject => `<label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-3 transition hover:border-blue-300 hover:bg-blue-50"><input class="bulk-subject mt-1" type="checkbox" value="${subject.id_asignacion}" checked><span><strong class="block text-sm text-slate-800">${esc(subject.materia)}</strong><span class="mt-0.5 block text-xs text-slate-500">${esc(subject.docente)}</span></span></label>`).join('') : '<p class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 md:col-span-2 xl:col-span-3">No hay asignaturas configuradas para este año y grado.</p>';
                state.bulkStudents = new Set(bulk.estudiantes.map(student => String(student.id_usuario)));
                $('check-all-subjects').checked = true;
                $('bulk-search').value = '';
                renderBulkStudents();
                subjectHost.querySelectorAll('.bulk-subject').forEach(box => box.addEventListener('change', updateBulkSummary));
                updateBulkSummary();
            }
            function visibleBulkStudents() {
                const text = $('bulk-search').value.trim().toLowerCase();
                return (state.bulk?.estudiantes || []).filter(student => !text || `${student.id_usuario} ${student.nombre} ${student.apellido}`.toLowerCase().includes(text));
            }
            function renderBulkStudents() {
                const students = visibleBulkStudents(); const totalSubjects = state.bulk?.asignaciones.length || 0;
                $('bulk-student-caption').textContent = `${state.bulk?.estudiantes.length || 0} estudiante${(state.bulk?.estudiantes.length || 0) === 1 ? '' : 's'} detectado${(state.bulk?.estudiantes.length || 0) === 1 ? '' : 's'} en este grado.`;
                $('bulk-students').innerHTML = students.length ? students.map(student => { const selected = state.bulkStudents.has(String(student.id_usuario)); const complete = Number(student.inscripciones_activas) >= totalSubjects && totalSubjects > 0; return `<label class="flex cursor-pointer items-center gap-3 px-5 py-3 transition hover:bg-slate-50 sm:px-6"><input class="bulk-student" type="checkbox" value="${esc(student.id_usuario)}" ${selected ? 'checked' : ''}><span class="min-w-0 flex-1"><strong class="block truncate text-sm text-slate-800">${esc(`${student.nombre} ${student.apellido}`.trim())}</strong><span class="text-xs text-slate-500">ID ${esc(student.id_usuario)} · observación: ${esc(student.grado || 'sin grado')}</span></span><span class="rounded-full px-2.5 py-1 text-xs font-semibold ${complete ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'}">${student.inscripciones_activas}/${totalSubjects} activas</span></label>`; }).join('') : '<p class="px-6 py-10 text-center text-sm text-slate-500">No hay estudiantes que coincidan con el filtro.</p>';
                $('bulk-students').querySelectorAll('.bulk-student').forEach(box => box.addEventListener('change', () => { if (box.checked) state.bulkStudents.add(box.value); else state.bulkStudents.delete(box.value); updateBulkSummary(); }));
                updateBulkSummary();
            }
            function selectedSubjects() { return [...document.querySelectorAll('.bulk-subject:checked')].map(box => Number(box.value)); }
            function updateBulkSummary() {
                const students = state.bulkStudents.size, subjects = selectedSubjects().length, total = students * subjects;
                $('bulk-summary').textContent = students && subjects ? `Se revisarán ${total} combinaciones (${students} estudiantes × ${subjects} asignaturas). Las ya existentes se omitirán.` : 'Selecciona al menos un estudiante y una asignatura para continuar.';
                $('bulk-enroll').disabled = !(students && subjects);
                $('check-all-subjects').checked = subjects === (state.bulk?.asignaciones.length || 0) && subjects > 0;
            }
            async function loadBulk() {
                const year = Number($('bulk-year').value || 0), grade = Number($('bulk-grade').value || 0);
                if (!year || !grade) { state.bulk = null; renderBulk(); return; }
                $('bulk-empty').textContent = 'Preparando estudiantes y asignaturas…'; $('bulk-empty').classList.remove('hidden');
                try { const payload = await request('bulk_context', { data: { ano: year, grado: grade } }); state.bulk = payload.data; renderBulk(); }
                catch (error) { state.bulk = null; renderBulk(); $('bulk-empty').textContent = error.message; notify(error.message, 'error'); }
            }
            async function submitBulk() {
                const students = [...state.bulkStudents], subjects = selectedSubjects();
                if (!students.length || !subjects.length) return;
                const message = `¿Inscribir ${students.length} estudiante${students.length === 1 ? '' : 's'} en ${subjects.length} asignatura${subjects.length === 1 ? '' : 's'}? Las inscripciones existentes no se duplicarán.`;
                if (!window.confirm(message)) return;
                const button = $('bulk-enroll'); button.disabled = true; button.textContent = 'Inscribiendo…';
                try {
                    const payload = await request('bulk_enroll', { method: 'POST', data: { ano: $('bulk-year').value, grado: $('bulk-grade').value, estudiantes: JSON.stringify(students), asignaciones: JSON.stringify(subjects), estado_inscripcion: $('bulk-status').value, observaciones: $('bulk-note').value.trim() } });
                    notify(payload.message); await Promise.all([loadBulk(), loadEnrollments()]);
                } catch (error) { notify(error.message, 'error'); }
                finally { button.textContent = 'Inscribir seleccionados'; updateBulkSummary(); }
            }

            function bindEvents() {
                $('toast-close').addEventListener('click', hideToast);
                $('tab-individual').addEventListener('click', () => switchTab('individual'));
                $('tab-grade').addEventListener('click', () => switchTab('grade'));
                $('filter-year').addEventListener('change', async () => { await refreshFilterAssignments(false); loadEnrollments(); });
                $('filter-grade').addEventListener('change', async () => { await refreshFilterAssignments(false); loadEnrollments(); });
                $('filter-assignment').addEventListener('change', () => loadEnrollments());
                $('filter-status').addEventListener('change', () => loadEnrollments());
                $('filter-search').addEventListener('input', () => { clearTimeout(state.tableTimer); state.tableTimer = setTimeout(() => loadEnrollments(), 300); });
                $('clear-filters').addEventListener('click', async () => { $('filter-grade').value = ''; $('filter-status').value = ''; $('filter-search').value = ''; await refreshFilterAssignments(false); loadEnrollments(); });
                $('new-enrollment').addEventListener('click', () => openModal());
                $('close-modal').addEventListener('click', closeModal); $('cancel-modal').addEventListener('click', closeModal);
                $('enrollment-modal').addEventListener('click', event => { if (event.target === $('enrollment-modal')) closeModal(); });
                document.addEventListener('keydown', event => { if (event.key === 'Escape') closeModal(); });
                $('enrollment-form').addEventListener('submit', submitEnrollment);
                $('student-search').addEventListener('input', () => { clearTimeout(state.searchTimer); state.searchTimer = setTimeout(searchStudents, 260); });
                $('modal-grade').addEventListener('change', () => { setSelectedStudent(null); searchStudents(); loadAssignments(Number($('modal-year').value), Number($('modal-grade').value || 0), $('modal-assignment')).catch(error => notify(error.message, 'error')); });
                $('modal-year').addEventListener('change', () => loadAssignments(Number($('modal-year').value), Number($('modal-grade').value || 0), $('modal-assignment')).catch(error => notify(error.message, 'error')));
                $('enrollment-table').addEventListener('click', async event => { const edit = event.target.closest('.edit-button'), remove = event.target.closest('.delete-button'); if (edit) { const item = state.currentItems.find(x => Number(x.id_inscripcion) === Number(edit.dataset.id)); if (item) openModal(item); } if (remove) { const item = state.currentItems.find(x => Number(x.id_inscripcion) === Number(remove.dataset.id)); if (item && window.confirm(`¿Eliminar permanentemente la inscripción de ${item.nombre} ${item.apellido} en ${item.materia}?`)) { try { const payload = await request('delete', { method: 'POST', data: { id_inscripcion: item.id_inscripcion } }); notify(payload.message); loadEnrollments(); } catch (error) { notify(error.message, 'error'); } } } });
                $('pagination').addEventListener('click', event => { const button = event.target.closest('.page-button'); if (button) loadEnrollments(Number(button.dataset.page)); });
                $('bulk-year').addEventListener('change', loadBulk); $('bulk-grade').addEventListener('change', loadBulk);
                $('bulk-search').addEventListener('input', renderBulkStudents);
                $('bulk-select-visible').addEventListener('click', () => { visibleBulkStudents().forEach(student => state.bulkStudents.add(String(student.id_usuario))); renderBulkStudents(); });
                $('bulk-unselect').addEventListener('click', () => { state.bulkStudents.clear(); renderBulkStudents(); });
                $('check-all-subjects').addEventListener('change', event => { document.querySelectorAll('.bulk-subject').forEach(box => { box.checked = event.target.checked; }); updateBulkSummary(); });
                $('bulk-enroll').addEventListener('click', submitBulk);
            }
            function switchTab(tab) {
                const individual = tab === 'individual';
                $('panel-individual').classList.toggle('hidden', !individual); $('panel-grade').classList.toggle('hidden', individual);
                $('tab-individual').setAttribute('aria-selected', String(individual)); $('tab-grade').setAttribute('aria-selected', String(!individual));
                if (!individual && !state.bulk) loadBulk();
            }
            async function boot() {
                try {
                    const payload = await request('bootstrap');
                    const data = payload.data; state.csrf = data.csrf_token; state.years = data.anos; state.grades = data.grados;
                    populateSelect($('filter-year'), state.years, null, 'id_ano_lectivo', x => `${x.nombre_ano_lectivo}${String(x.estado).toLowerCase() === 'activo' ? ' · Activo' : ''}`, data.ano_activo);
                    populateSelect($('filter-grade'), state.grades, 'Todos los grados', 'id_categoria_curso', x => `Grado ${x.nombre_categoria_curso}`);
                    populateSelect($('bulk-year'), state.years, null, 'id_ano_lectivo', x => `${x.nombre_ano_lectivo}${String(x.estado).toLowerCase() === 'activo' ? ' · Activo' : ''}`, data.ano_activo);
                    populateSelect($('bulk-grade'), state.grades, 'Selecciona un grado', 'id_categoria_curso', x => `Grado ${x.nombre_categoria_curso}`);
                    await refreshFilterAssignments(false); await loadEnrollments();
                } catch (error) { notify(error.message, 'error'); $('table-caption').textContent = 'No fue posible cargar los datos.'; }
            }
            bindEvents(); boot();
        })();
    </script>
<?php endif; ?>
</body>
</html>
