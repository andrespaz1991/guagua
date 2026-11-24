<?php

/** Utilidades y consultas compartidas del módulo de combinación de correspondencia. */
function formatos_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function formatos_bind(mysqli_stmt $stmt, string $types, array $params): void {
    if ($types === '') return;
    $refs = [];
    foreach ($params as $key => $value) $refs[$key] = &$params[$key];
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function formatos_rows(mysqli $mysqli, string $sql, string $types = '', array $params = []): array {
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) throw new RuntimeException('No se pudo preparar la consulta: ' . $mysqli->error);
    formatos_bind($stmt, $types, $params);
    if (!$stmt->execute()) throw new RuntimeException('No se pudo ejecutar la consulta: ' . $stmt->error);
    $result = $stmt->get_result();
    $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    return $rows;
}

function formatos_row(mysqli $mysqli, string $sql, string $types = '', array $params = []): ?array {
    $rows = formatos_rows($mysqli, $sql, $types, $params);
    return $rows[0] ?? null;
}

function formatos_table_exists(mysqli $mysqli, string $table): bool {
    $table = $mysqli->real_escape_string($table);
    $result = $mysqli->query("SHOW TABLES LIKE '{$table}'");
    return $result && $result->num_rows > 0;
}

function formatos_column_exists(mysqli $mysqli, string $table, string $column): bool {
    $table = $mysqli->real_escape_string($table);
    $column = $mysqli->real_escape_string($column);
    $result = $mysqli->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
    return $result && $result->num_rows > 0;
}

/** Instala solo los campos que este módulo necesita, sin tocar información existente. */
function formatos_ensure_schema(mysqli $mysqli): void {
    foreach ([
        'condicion_fisica' => "ALTER TABLE usuario ADD COLUMN condicion_fisica VARCHAR(255) NULL AFTER tipo_sangre",
        'grupo' => "ALTER TABLE usuario ADD COLUMN grupo VARCHAR(50) NULL AFTER condicion_fisica",
        'jornada' => "ALTER TABLE usuario ADD COLUMN jornada VARCHAR(80) NULL AFTER grupo",
    ] as $column => $sql) {
        if (!formatos_column_exists($mysqli, 'usuario', $column) && !$mysqli->query($sql)) {
            throw new RuntimeException('No fue posible preparar el campo ' . $column . ': ' . $mysqli->error);
        }
    }

    $tables = [
        "CREATE TABLE IF NOT EXISTS formatos_observador (
            id_formato_observador BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_estudiante VARCHAR(255) NOT NULL,
            ano_lectivo YEAR NOT NULL,
            periodo TINYINT UNSIGNED NOT NULL DEFAULT 1,
            comportamiento VARCHAR(80) NOT NULL,
            valor ENUM('N','AV','S') NULL,
            observaciones TEXT NULL,
            actualizado_por VARCHAR(255) NULL,
            fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_observador_item (id_estudiante, ano_lectivo, periodo, comportamiento)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        "CREATE TABLE IF NOT EXISTS formatos_desercion_acciones (
            id_formato_desercion BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            id_estudiante VARCHAR(255) NOT NULL,
            ano_lectivo YEAR NOT NULL,
            acciones TEXT NULL,
            actualizado_por VARCHAR(255) NULL,
            fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_desercion_estudiante_ano (id_estudiante, ano_lectivo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
    foreach ($tables as $sql) {
        if (!$mysqli->query($sql)) throw new RuntimeException('No fue posible preparar las tablas del módulo: ' . $mysqli->error);
    }
}

function formatos_can_manage(): bool {
    $roles = array_map('strtolower', array_filter(array_map('trim', explode(',', (string)($_SESSION['rol'] ?? '')))));
    return (bool)array_intersect($roles, ['admin', 'directivo', 'docente']);
}

function formatos_require_access(): void {
    if (empty($_SESSION['id_usuario'])) {
        header('Location: ../../usuario/login.php');
        exit;
    }
    if (!formatos_can_manage()) {
        http_response_code(403);
        exit('No tienes permisos para gestionar formatos institucionales.');
    }
}

function formatos_context(mysqli $mysqli): array {
    require_once __DIR__ . '/../../../comun/academico.php';
    $year = guagua_ano_activo($mysqli);
    $period = guagua_periodo_activo($mysqli);
    if (!$year || !$period) throw new RuntimeException('Configure un año lectivo y un período activos del mismo año.');
    $yearNumber = (int)$year['nombre_ano_lectivo'];
    return [
        'id_ano' => (int)($year['id_ano_lectivo'] ?? 0),
        'ano' => $yearNumber,
        'id_periodo' => (int)($period['id_periodo'] ?? 0),
        'periodo' => (int)preg_replace('/\D+/', '', (string)($period['nombre_periodo'] ?? '1')) ?: 1,
        'nombre_periodo' => (string)($period['nombre_periodo'] ?? '1'),
    ];
}

function formatos_students(mysqli $mysqli, array $ctx, bool $activeOnly = true): array {
    $status = $activeOnly ? "AND LOWER(TRIM(COALESCE(u.estado, 'activo'))) = 'activo'" : '';
    $yearCondition = $ctx['id_ano'] > 0 ? ' AND a.ano_lectivo = ' . (int)$ctx['id_ano'] : '';
    return formatos_rows($mysqli,
        "SELECT u.id_usuario, u.nombre, u.apellido, u.tipo_sangre, u.condicion_fisica, u.grupo, u.jornada,
                u.direccion, u.telefono, u.fecha_retiro, u.estado,
                MAX(cc.nombre_categoria_curso) AS grado,
                MAX(CONCAT(TRIM(COALESCE(ac.nombre, '')), ' ', TRIM(COALESCE(ac.apellido, '')))) AS acudiente
         FROM usuario u
         LEFT JOIN inscripcion i ON i.id_estudiante = u.id_usuario
         LEFT JOIN asignacion a ON a.id_asignacion = i.id_asignacion {$yearCondition}
         LEFT JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
         LEFT JOIN acudiente_estudiante ae ON ae.id_estudiante = u.id_usuario
         LEFT JOIN usuario ac ON ac.id_usuario = ae.id_acudiente
         WHERE u.rol LIKE '%estudiante%' AND a.id_asignacion IS NOT NULL {$status}
         GROUP BY u.id_usuario
         ORDER BY u.apellido, u.nombre");
}

function formatos_student(mysqli $mysqli, array $ctx, string $id): ?array {
    foreach (formatos_students($mysqli, $ctx, false) as $student) {
        if ((string)$student['id_usuario'] === $id) return $student;
    }
    return null;
}

function formatos_full_name(array $student): string {
    return trim(($student['apellido'] ?? '') . ' ' . ($student['nombre'] ?? ''));
}

function formatos_observer_data(mysqli $mysqli, array $ctx, string $studentId): array {
    $rows = formatos_rows($mysqli,
        'SELECT comportamiento, valor, observaciones FROM formatos_observador WHERE id_estudiante = ? AND ano_lectivo = ? AND periodo = ?',
        'sii', [$studentId, $ctx['ano'], $ctx['periodo']]);
    $data = ['items' => [], 'observaciones' => ''];
    foreach ($rows as $row) {
        $data['items'][$row['comportamiento']] = $row['valor'];
        if (trim((string)$row['observaciones']) !== '') $data['observaciones'] = $row['observaciones'];
    }
    return $data;
}

function formatos_observer_items(): array {
    return [
        'puntualidad' => 'Asisto con puntualidad a la jornada escolar',
        'uniforme' => 'Porto adecuadamente el uniforme',
        'tareas' => 'Cumplo con las tareas y porto los útiles escolares',
        'excusas' => 'Presento las excusas oportunamente y generan credibilidad',
        'comportamiento' => 'Me comporto adecuadamente en las actividades escolares',
        'sustancias' => 'Porto, distribuyo o consumo sustancias psicoactivas dentro de la institución',
        'armas' => 'Porto armas corto punzantes u otras de alto peligro en la institución',
        'conducto_regular' => 'Utilizo el conducto regular para solucionar dificultades',
        'reincidencia' => 'Reincido en las faltas estipuladas en el manual de convivencia',
        'informacion_acudiente' => 'Llevo al acudiente la información enviada por la institución a tiempo',
        'llamados_atencion' => 'Atiendo positivamente los llamados de atención',
        'trato_respetuoso' => 'Demuestro un trato respetuoso frente al acudiente',
        'agresion' => 'Agredo físicamente a compañeros, educadores o directivos',
        'fraude' => 'Presento comportamientos fraudulentos en evaluaciones, actividades o tareas',
        'cuidado_bienes' => 'Cuido los bienes institucionales y cumplo con el aseo',
        'basura' => 'Arrojo basura o rayo paredes, pupitres, puertas u otros',
        'rendimiento' => 'Presento bajo rendimiento académico',
    ];
}

function formatos_dropouts(mysqli $mysqli, array $ctx): array {
    $start = $ctx['ano'] . '-01-01';
    $end = $ctx['ano'] . '-12-31';
    return formatos_rows($mysqli,
        "SELECT u.id_usuario, u.nombre, u.apellido, u.fecha_retiro, MAX(cc.nombre_categoria_curso) AS grado,
                MAX(fda.acciones) AS acciones
         FROM usuario u
         LEFT JOIN inscripcion i ON i.id_estudiante = u.id_usuario
         LEFT JOIN asignacion a ON a.id_asignacion = i.id_asignacion
         LEFT JOIN categoria_curso cc ON cc.id_categoria_curso = a.id_categoria_curso
         LEFT JOIN formatos_desercion_acciones fda ON fda.id_estudiante = u.id_usuario AND fda.ano_lectivo = ?
         WHERE u.rol LIKE '%estudiante%' AND u.fecha_retiro BETWEEN ? AND ?
         GROUP BY u.id_usuario, u.fecha_retiro
         ORDER BY u.fecha_retiro DESC, u.apellido, u.nombre",
        'iss', [$ctx['ano'], $start, $end]);
}

function formatos_recovery_rows(mysqli $mysqli, array $ctx): array {
    if (!formatos_table_exists($mysqli, 'notas_auditoria') || !$ctx['id_periodo']) return [];
    // Los DBA institucionales se registran en las planeaciones. La materia de
    // la auditoría conserva tanto el nombre como el id de la materia, por eso
    // se prioriza el id y se deja el nombre como compatibilidad con historiales
    // antiguos.
    $dbaSelect = "'' AS dba";
    if (formatos_table_exists($mysqli, 'planeador_vallesol')) {
        $period = (int)$ctx['periodo'];
        $dbaSelect = "COALESCE((
            SELECT p.dba
            FROM planeador_vallesol p
            LEFT JOIN materia_oficial mo ON mo.id_materia = CAST(TRIM(p.materia) AS UNSIGNED)
            WHERE CONVERT(TRIM(p.grado) USING utf8mb4) COLLATE utf8mb4_unicode_ci = CONVERT(TRIM(n.grado) USING utf8mb4) COLLATE utf8mb4_unicode_ci
              AND (CAST(TRIM(p.materia) AS UNSIGNED) = n.id_materia
                   OR LOWER(CONVERT(TRIM(COALESCE(mo.nombre_materia, p.materia)) USING utf8mb4)) COLLATE utf8mb4_unicode_ci = LOWER(CONVERT(TRIM(n.materia) USING utf8mb4)) COLLATE utf8mb4_unicode_ci)
              AND (CAST(TRIM(COALESCE(p.periodo, '0')) AS UNSIGNED) = {$period} OR CAST(TRIM(COALESCE(p.periodo, '0')) AS UNSIGNED) = 0)
              AND NULLIF(TRIM(p.dba), '') IS NOT NULL
            ORDER BY p.fecha_inicio DESC, p.id_plan DESC
            LIMIT 1
        ), '') AS dba";
    }
    return formatos_rows($mysqli,
        "SELECT n.id_estudiante, CONCAT_WS(' ', u.apellido, u.nombre) AS estudiante,
                MAX(n.grado) AS grado, MAX(u.grupo) AS grupo, MAX(u.jornada) AS jornada,
                n.materia, MIN(n.nota) AS nota, {$dbaSelect}
         FROM notas_auditoria n
         INNER JOIN usuario u ON u.id_usuario = n.id_estudiante
         WHERE n.id_periodo = ? AND n.nota < 3
           AND LOWER(TRIM(COALESCE(u.estado, 'activo'))) = 'activo'
         GROUP BY n.id_estudiante, n.materia, n.id_materia, n.grado
         ORDER BY estudiante, n.materia",
        'i', [$ctx['id_periodo']]);
}

function formatos_save_profile(mysqli $mysqli, string $studentId, array $data): void {
    $condition = trim((string)($data['condicion_fisica'] ?? ''));
    $group = trim((string)($data['grupo'] ?? ''));
    $jornada = trim((string)($data['jornada'] ?? ''));
    $blood = trim((string)($data['tipo_sangre'] ?? ''));
    $stmt = $mysqli->prepare('UPDATE usuario SET condicion_fisica = ?, grupo = ?, jornada = ?, tipo_sangre = ? WHERE id_usuario = ? AND rol LIKE \'%estudiante%\'');
    if (!$stmt) throw new RuntimeException($mysqli->error);
    $stmt->bind_param('sssss', $condition, $group, $jornada, $blood, $studentId);
    if (!$stmt->execute()) throw new RuntimeException($stmt->error);
    $stmt->close();
}

function formatos_save_observer(mysqli $mysqli, array $ctx, string $studentId, array $values, string $observations, string $userId): void {
    $stmt = $mysqli->prepare("INSERT INTO formatos_observador (id_estudiante, ano_lectivo, periodo, comportamiento, valor, observaciones, actualizado_por)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE valor = VALUES(valor), observaciones = VALUES(observaciones), actualizado_por = VALUES(actualizado_por)");
    if (!$stmt) throw new RuntimeException($mysqli->error);
    foreach (formatos_observer_items() as $key => $label) {
        $value = strtoupper(trim((string)($values[$key] ?? '')));
        if (!in_array($value, ['N', 'AV', 'S'], true)) $value = null;
        $period = $ctx['periodo']; $year = $ctx['ano'];
        $stmt->bind_param('siissss', $studentId, $year, $period, $key, $value, $observations, $userId);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
    }
    $stmt->close();
}

function formatos_save_dropout_actions(mysqli $mysqli, array $ctx, array $actions, string $userId): void {
    $stmt = $mysqli->prepare("INSERT INTO formatos_desercion_acciones (id_estudiante, ano_lectivo, acciones, actualizado_por)
        VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE acciones = VALUES(acciones), actualizado_por = VALUES(actualizado_por)");
    if (!$stmt) throw new RuntimeException($mysqli->error);
    foreach ($actions as $studentId => $action) {
        $action = trim((string)$action);
        $studentId = trim((string)$studentId);
        if ($studentId === '') continue;
        $year = $ctx['ano'];
        $stmt->bind_param('siss', $studentId, $year, $action, $userId);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
    }
    $stmt->close();
}
