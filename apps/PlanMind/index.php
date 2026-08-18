<?php
$planmindRequestedId = isset($_GET['id']) ? max(0, (int)$_GET['id']) : 0;
$planmindRequestedAsignacion = isset($_GET['asignacion']) ? max(0, (int)$_GET['asignacion']) : 0;
$planmindInitialPlan = null;
$planmindInitialStatus = [
    'requestedId' => $planmindRequestedId,
    'exists' => false,
    'message' => '',
];
$planmindOfficialMaterias = [];
$planmindAsignacionData = null;
$planmindActivePeriodo = null;

function planmind_send_json(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function planmind_db(): mysqli
{
    require_once __DIR__ . '/../../comun/config.php';

    $mysqli = new mysqli(SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);
    if ($mysqli->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $mysqli->connect_error);
    }

    if (!$mysqli->set_charset('utf8mb4')) {
        $mysqli->set_charset('utf8');
    }

    if (defined('TIME_ZONE')) {
        date_default_timezone_set(TIME_ZONE);
    }
    if (defined('TIME_ZONE_OFFSET')) {
        $offset = $mysqli->real_escape_string(TIME_ZONE_OFFSET);
        $mysqli->query("SET time_zone = '$offset'");
    }

    return $mysqli;
}

function planmind_clean_value($value): string
{
    return trim(str_replace("\0", '', (string)($value ?? '')));
}

function planmind_quote(mysqli $mysqli, $value): string
{
    return "'" . $mysqli->real_escape_string(planmind_clean_value($value)) . "'";
}

function planmind_fetch_active_periodo(mysqli $mysqli): ?array
{
    $result = $mysqli->query("SELECT `id_periodo`, `nombre_periodo`, `descripcion_periodo`, `fecha_inicio`, `fecha_fin`, `ano_lectivo`, `estado_periodo` FROM `periodo` WHERE `estado_periodo` = '1' LIMIT 1");
    if (!$result) {
        return null;
    }

    $row = $result->fetch_assoc();
    if (!$row) {
        return null;
    }

    return [
        'id_periodo' => (int)$row['id_periodo'],
        'nombre_periodo' => (string)($row['nombre_periodo'] ?? ''),
        'descripcion_periodo' => (string)($row['descripcion_periodo'] ?? ''),
        'fecha_inicio' => substr((string)($row['fecha_inicio'] ?? ''), 0, 10),
        'fecha_fin' => substr((string)($row['fecha_fin'] ?? ''), 0, 10),
        'ano_lectivo' => (string)($row['ano_lectivo'] ?? ''),
    ];
}

function planmind_fetch_official_materias(mysqli $mysqli): array
{
    $result = $mysqli->query("SELECT `id_materia`, `nombre_materia` FROM `materia_oficial` ORDER BY `nombre_materia` ASC");
    if (!$result) {
        return [];
    }

    $materias = [];
    while ($row = $result->fetch_assoc()) {
        $materias[] = [
            'id_materia' => (string)($row['id_materia'] ?? ''),
            'nombre_materia' => (string)($row['nombre_materia'] ?? ''),
        ];
    }

    return $materias;
}

function planmind_fetch_asignacion_data(mysqli $mysqli, int $idAsignacion): ?array
{
    if ($idAsignacion <= 0) {
        return null;
    }

    $sql = "SELECT a.id_asignacion, a.id_asignatura, a.id_categoria_curso,
                   mo.id_materia, mo.nombre_materia,
                   cc.nombre_categoria_curso AS grado_numero
            FROM `asignacion` a
            LEFT JOIN `materia_oficial` mo ON mo.id_materia = a.id_asignatura
            LEFT JOIN `categoria_curso` cc ON cc.id_categoria_curso = a.id_categoria_curso
            WHERE a.id_asignacion = $idAsignacion
            LIMIT 1";
    $result = $mysqli->query($sql);
    if (!$result) {
        return null;
    }

    $row = $result->fetch_assoc();
    if (!$row) {
        return null;
    }

    $gradoNumero = trim((string)($row['grado_numero'] ?? ''));
    $gradoMap = [
        '0' => 'Preescolar', '1' => 'Primero', '2' => 'Segundo', '3' => 'Tercero',
        '4' => 'Cuarto', '5' => 'Quinto', '6' => 'Sexto', '7' => 'Séptimo',
        '8' => 'Octavo', '9' => 'Noveno', '10' => 'Décimo', '11' => 'Undécimo',
    ];
    $gradoTexto = $gradoMap[$gradoNumero] ?? $gradoNumero;

    return [
        'id_asignacion' => (int)$row['id_asignacion'],
        'id_materia' => (string)($row['id_materia'] ?? ''),
        'nombre_materia' => (string)($row['nombre_materia'] ?? ''),
        'grado_numero' => $gradoNumero,
        'grado_texto' => $gradoTexto,
    ];
}

function planmind_read_json_body(): array
{
    $payload = json_decode(file_get_contents('php://input'), true);
    return is_array($payload) ? $payload : [];
}

function planmind_map_row(array $row): array
{
    $materiaRaw = (string)($row['materia'] ?? '');
    $materiaNombre = planmind_clean_value($row['materia_oficial_nombre'] ?? '');
    if ($materiaNombre === '') {
        $materiaNombre = planmind_clean_value($row['materia_general_nombre'] ?? '');
    }
    if ($materiaNombre === '') {
        $materiaNombre = $materiaRaw;
    }

    return [
        'id_plan' => (int)($row['id_plan'] ?? 0),
        'fecha_inicio' => substr((string)($row['fecha_inicio'] ?? ''), 0, 10),
        'fecha_fin' => substr((string)($row['fecha_fin'] ?? ''), 0, 10),
        'grado' => (string)($row['grado'] ?? ''),
        'materia' => $materiaRaw,
        'materia_id' => $materiaRaw,
        'materia_nombre' => $materiaNombre,
        'periodo' => (string)($row['periodo'] ?? ''),
        'tiempo' => (string)($row['tiempo_plan'] ?? ''),
        'tiempo_plan' => (string)($row['tiempo_plan'] ?? ''),
        'dba' => (string)($row['dba'] ?? ''),
        'estrategia' => (string)($row['estrategias'] ?? ''),
        'estrategias' => (string)($row['estrategias'] ?? ''),
        'momentos' => (string)($row['observaciones'] ?? ''),
        'observaciones_db' => (string)($row['observaciones'] ?? ''),
        'reflexion' => (string)($row['reflexion'] ?? ''),
        'objetivo' => (string)($row['objetivo'] ?? ''),
        'ejes_tematicos' => (string)($row['eje_tematico'] ?? ''),
        'eje_tematico' => (string)($row['eje_tematico'] ?? ''),
        'evaluacion' => (string)($row['evaluacion'] ?? ''),
    ];
}

function planmind_fetch_plan(mysqli $mysqli, int $id): ?array
{
    $id = max(0, $id);
    if ($id <= 0) {
        return null;
    }

    $sql = "SELECT p.`id_plan`, p.`fecha_inicio`, p.`fecha_fin`, p.`grado`, p.`materia`, p.`periodo`, p.`tiempo_plan`, p.`dba`, p.`estrategias`, p.`observaciones`, p.`reflexion`, p.`objetivo`, p.`eje_tematico`, p.`evaluacion`,
                   mo.`nombre_materia` AS `materia_oficial_nombre`,
                   m.`nombre_materia` AS `materia_general_nombre`
            FROM `planeador_vallesol` p
            LEFT JOIN `materia_oficial` mo ON mo.`id_materia` = CAST(TRIM(p.`materia`) AS UNSIGNED)
            LEFT JOIN `materia` m ON mo.`id_materia` IS NULL AND m.`id_materia` = CAST(TRIM(p.`materia`) AS UNSIGNED)
            WHERE p.`id_plan` = $id
            LIMIT 1";
    $result = $mysqli->query($sql);
    if (!$result) {
        throw new RuntimeException('Error SQL: ' . $mysqli->error);
    }

    $row = $result->fetch_assoc();
    return $row ? planmind_map_row($row) : null;
}

function planmind_payload_fields(array $payload): array
{
    $momentos = planmind_clean_value($payload['momentos'] ?? '');
    $observacionesFormulario = planmind_clean_value($payload['observaciones'] ?? '');

    return [
        'fecha_inicio' => planmind_clean_value($payload['fecha_inicio'] ?? ''),
        'fecha_fin' => planmind_clean_value($payload['fecha_fin'] ?? ''),
        'grado' => planmind_clean_value($payload['grado'] ?? ''),
        'materia' => planmind_clean_value($payload['materia'] ?? ''),
        'periodo' => planmind_clean_value($payload['periodo'] ?? ''),
        'tiempo_plan' => planmind_clean_value($payload['tiempo_plan'] ?? ($payload['tiempo'] ?? '')),
        'dba' => planmind_clean_value($payload['dba'] ?? ''),
        'estrategias' => planmind_clean_value($payload['estrategias'] ?? ($payload['estrategia'] ?? '')),
        'observaciones' => $momentos !== '' ? $momentos : $observacionesFormulario,
        'reflexion' => planmind_clean_value($payload['reflexion'] ?? ''),
        'objetivo' => planmind_clean_value($payload['objetivo'] ?? ''),
        'eje_tematico' => planmind_clean_value($payload['eje_tematico'] ?? ($payload['ejes_tematicos'] ?? '')),
        'evaluacion' => planmind_clean_value($payload['evaluacion'] ?? ''),
    ];
}

function planmind_validate_fields(array $fields): array
{
    $errors = [];
    foreach (['fecha_inicio' => 'Fecha inicio', 'fecha_fin' => 'Fecha fin'] as $field => $label) {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fields[$field] ?? '')) {
            $errors[] = "$label no es válida.";
        }
    }
    foreach (['grado' => 'Grado', 'materia' => 'Materia', 'periodo' => 'Periodo'] as $field => $label) {
        if (($fields[$field] ?? '') === '') {
            $errors[] = "$label es obligatorio.";
        }
    }
    return $errors;
}

function planmind_insert_plan(mysqli $mysqli, array $fields): int
{
    $columns = array_keys($fields);
    $quotedColumns = '`' . implode('`, `', $columns) . '`';
    $values = [];
    foreach ($columns as $column) {
        $values[] = planmind_quote($mysqli, $fields[$column]);
    }

    $sql = "INSERT INTO `planeador_vallesol` ($quotedColumns) VALUES (" . implode(', ', $values) . ")";
    if (!$mysqli->query($sql)) {
        throw new RuntimeException('Error al guardar: ' . $mysqli->error);
    }

    return (int)$mysqli->insert_id;
}

function planmind_update_plan(mysqli $mysqli, int $id, array $fields): void
{
    $updates = [];
    foreach ($fields as $column => $value) {
        $updates[] = "`$column` = " . planmind_quote($mysqli, $value);
    }

    $sql = "UPDATE `planeador_vallesol` SET " . implode(', ', $updates) . " WHERE `id_plan` = $id LIMIT 1";
    if (!$mysqli->query($sql)) {
        throw new RuntimeException('Error al actualizar: ' . $mysqli->error);
    }
}

$planmindAction = $_GET['action'] ?? '';
if ($planmindAction !== '') {
    try {
        $mysqli = planmind_db();

        if ($planmindAction === 'save') {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                planmind_send_json(['ok' => false, 'message' => 'Método no permitido.'], 405);
            }

            $payload = planmind_read_json_body();
            $fields = planmind_payload_fields($payload);
            $errors = planmind_validate_fields($fields);
            if ($errors) {
                planmind_send_json(['ok' => false, 'message' => implode(' ', $errors)], 422);
            }

            $id = max(0, (int)($payload['id_plan'] ?? 0));
            $existingPlan = $id > 0 ? planmind_fetch_plan($mysqli, $id) : null;

            if ($existingPlan) {
                planmind_update_plan($mysqli, $id, $fields);
                planmind_send_json(['ok' => true, 'mode' => 'updated', 'id_plan' => $id, 'plan' => planmind_fetch_plan($mysqli, $id)]);
            }

            $newId = planmind_insert_plan($mysqli, $fields);
            planmind_send_json(['ok' => true, 'mode' => 'created', 'id_plan' => $newId, 'plan' => planmind_fetch_plan($mysqli, $newId)], 201);
        }

        if ($planmindAction === 'get') {
            $id = max(0, (int)($_GET['id'] ?? 0));
            $plan = planmind_fetch_plan($mysqli, $id);
            if (!$plan) {
                planmind_send_json(['ok' => false, 'message' => 'Planeación no encontrada.'], 404);
            }
            planmind_send_json(['ok' => true, 'plan' => $plan]);
        }

        planmind_send_json(['ok' => false, 'message' => 'Acción no encontrada.'], 404);
    } catch (Throwable $error) {
        planmind_send_json(['ok' => false, 'message' => $error->getMessage()], 500);
    }
}

try {
    $mysqli = planmind_db();
    $planmindOfficialMaterias = planmind_fetch_official_materias($mysqli);
    $planmindActivePeriodo = planmind_fetch_active_periodo($mysqli);

    if ($planmindRequestedId > 0) {
        $planmindInitialPlan = planmind_fetch_plan($mysqli, $planmindRequestedId);
        if ($planmindInitialPlan) {
            $planmindInitialStatus['exists'] = true;
            $planmindInitialStatus['message'] = 'Planeación cargada.';
        } else {
            $planmindInitialStatus['message'] = 'No se encontró una planeación con ese ID. Se guardará como nueva.';
        }
    }

    if ($planmindRequestedAsignacion > 0) {
        $planmindAsignacionData = planmind_fetch_asignacion_data($mysqli, $planmindRequestedAsignacion);
    }
} catch (Throwable $error) {
    if ($planmindRequestedId > 0) {
        $planmindInitialStatus['message'] = $error->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeador DOCX IA - IER Josefina (Vallesol)</title>
    <script>
        if (location.protocol !== 'file:' && !/\/index(?:2)?\.php$/i.test(location.pathname)) {
            location.replace('index.php' + location.search + location.hash);
        }
    </script>
    
    <script src="tailwindcss.js"></script>
    <script src="pizzip.min.js"></script>
    <script src="docxtemplater.js"></script>
    <script src="FileSaver.min.js"></script>
    
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f8fafc; }
        .resizing { cursor: col-resize !important; user-select: none; }
        .loader { border-top-color: #9333ea; -webkit-animation: spinner 1.5s linear infinite; animation: spinner 1.5s linear infinite; }
        @keyframes spinner { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* Modales y Animaciones */
        #configModal, #customConfirmModal { transition: opacity 0.2s ease; }
        .modal-hidden { display: none !important; }
        .modal-visible { display: flex !important; }
        .modal-box { animation: modalIn 0.2s ease; }
        @keyframes modalIn { from { transform: scale(0.95); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* Materias list items */
        .materia-item { display:flex; align-items:center; justify-content:space-between; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:6px 10px; font-size:0.8rem; color:#334155; }
        .materia-item button { color:#ef4444; font-size:0.75rem; font-weight:700; background:none; border:none; cursor:pointer; padding:0 4px; }
        .materia-item button:hover { color:#b91c1c; }
    </style>
</head>
<body class="h-screen flex overflow-hidden relative">

    <div id="customConfirmModal" class="modal-hidden fixed inset-0 bg-black/50 z-[100] items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm border border-slate-200 p-6 flex flex-col modal-box">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Confirmar acción</h3>
            <p id="customConfirmMessage" class="text-sm text-slate-600 mb-6"></p>
            <div class="flex justify-end gap-3">
                <button id="btnConfirmNo" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-md text-sm font-medium hover:bg-slate-200 transition-colors">Cancelar</button>
                <button id="btnConfirmYes" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 transition-colors">Sí, continuar</button>
            </div>
        </div>
    </div>

    <div id="configModal" class="modal-hidden fixed inset-0 bg-black/50 z-50 items-center justify-center p-4">
        <div id="configModalBox" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl border border-slate-200 max-h-[90vh] flex flex-col modal-box">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 flex-shrink-0">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    Configuración de PlanMind (Modo Offline)
                </h2>
                <button id="btnCloseModal" class="text-slate-400 hover:text-slate-700 transition-colors" aria-label="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <div class="flex border-b border-slate-200 flex-shrink-0 px-6">
                <button data-tab="conexion" class="config-tab tab-active text-xs font-semibold px-4 py-3 border-b-2 border-purple-600 text-purple-700 transition-colors">🔌 Conexión IA</button>
                <button data-tab="archivos" class="config-tab text-xs font-semibold px-4 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors">📁 Archivos</button>
                <button data-tab="materias" class="config-tab text-xs font-semibold px-4 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors">📚 Materias</button>
                <button data-tab="prompt" class="config-tab text-xs font-semibold px-4 py-3 border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-colors">🤖 Prompt IA</button>
            </div>

            <div class="overflow-y-auto flex-1">
                <div id="tab-conexion" class="config-tab-panel px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de API</label>
                        <select id="apiType" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="local" selected>Local (LM Studio / Sin Internet)</option>
                            <option value="gemini">Gemini (Nube - Google)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">URL del Endpoint</label>
                        <input type="text" id="apiUrl" value="http://localhost:1234/v1/chat/completions" class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs font-mono">
                    </div>
                    <div id="localModelConfig">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Modelo (LM Studio)</label>
                        <div class="grid grid-cols-[1fr_auto] gap-2">
                            <select id="apiModel" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Detectar modelo cargado</option>
                            </select>
                            <button type="button" id="btnRefreshModels" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Actualizar</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Periodos totales</label>
                        <select id="periodCount" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4" selected>4</option>
                        </select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-700">Docente</label>
                            <button type="button" data-clear-field="docente" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                        </div>
                        <input type="text" id="docente" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div class="flex items-center gap-2 bg-slate-50 p-2 rounded border border-slate-200">
                        <input type="checkbox" id="showHtmlTags" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                        <label for="showHtmlTags" class="text-xs font-semibold text-slate-700 cursor-pointer">Mostrar etiquetas HTML en Observaciones al cargar</label>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">API Key (Obligatorio solo en Nube)</label>
                        <input type="password" id="apiKey" placeholder="Pega tu API Key aquí..." class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <details class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <summary class="cursor-pointer text-xs font-bold text-slate-700">Convenciones para la plantilla Word</summary>
                        <div id="templateTags" class="mt-3 grid grid-cols-2 gap-2 text-xs text-slate-700">
                            <code>{mes}</code><code>{periodo}</code>
                            <code>{fecha_inicio}</code><code>{fecha_fin}</code>
                            <code>{docente}</code><code>{grado}</code>
                            <code>{materia}</code><code>{tiempo}</code>
                            <code>{dba}</code><code>{ejes_tematicos}</code>
                            <code>{objetivo}</code><code>{estrategia}</code>
                            <code>{momentos}</code><code>{evaluacion}</code>
                            <code>{observaciones}</code><code>{reflexion}</code>
                        </div>
                    </details>
                </div>

                <div id="tab-archivos" class="config-tab-panel hidden px-6 py-5 space-y-4">
                    <div class="bg-blue-50 p-4 rounded-md border border-blue-100">
                        <label class="block text-sm font-semibold text-blue-800 mb-2">Plantilla Word (.docx)</label>
                        <p class="text-xs text-blue-700 mb-3">Puedes subir la plantilla desde tu equipo o configurar una ruta local/URL para usarla al descargar.</p>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <button type="button" data-template-mode="upload" class="template-mode-btn bg-blue-600 text-white text-sm font-semibold py-2 px-3 rounded-md border border-blue-600 transition-colors">Subir archivo</button>
                            <button type="button" data-template-mode="path" class="template-mode-btn bg-white text-blue-700 text-sm font-semibold py-2 px-3 rounded-md border border-blue-200 hover:bg-blue-50 transition-colors">Usar ruta</button>
                        </div>
                        <input type="file" id="templateDocx" accept=".docx" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-white file:text-blue-700 hover:file:bg-blue-50 border border-blue-200 rounded-md p-1 bg-white">
                        <div id="templatePathContainer" class="hidden mt-3">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-blue-800">Ruta de plantilla</label>
                                <button type="button" data-clear-field="templatePath" class="text-[11px] font-semibold text-blue-500 hover:text-red-600">Limpiar</button>
                            </div>
                            <input type="text" id="templatePath" placeholder="C:/ruta/a/mi/plantilla.docx o http://localhost/guagua/..." class="w-full rounded-md border border-blue-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-md border border-green-100">
                        <label class="block text-sm font-semibold text-green-800 mb-2">Salida del documento generado</label>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <button type="button" data-output-mode="download" class="output-mode-btn bg-green-600 text-white text-sm font-semibold py-2 px-3 rounded-md border border-green-600 transition-colors">Descargar</button>
                            <button type="button" data-output-mode="path" class="output-mode-btn bg-white text-green-700 text-sm font-semibold py-2 px-3 rounded-md border border-green-200 hover:bg-green-50 transition-colors">Guardar en ruta</button>
                        </div>
                        <div id="outputPathContainer" class="hidden">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-green-800">Ruta de guardado</label>
                                <button type="button" data-clear-field="outputPath" class="text-[11px] font-semibold text-green-600 hover:text-red-600">Limpiar</button>
                            </div>
                            <input type="text" id="outputPath" class="w-full rounded-md border border-green-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-white">
                            <p class="mt-2 text-xs text-green-700">Si eliges guardar en ruta, XAMPP debe tener permiso para escribir en esa carpeta.</p>
                        </div>
                    </div>
                </div>

                <div id="tab-materias" class="config-tab-panel hidden px-6 py-5 space-y-4">
                    <p class="text-xs text-slate-500">Agrega o elimina las materias que aparecen en el selector del formulario.</p>
                    <div class="flex gap-2">
                        <input type="text" id="nuevaMateria" placeholder="Ej: Español" class="flex-1 rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <button type="button" id="btnAgregarMateria" class="bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold px-4 py-2 rounded-md transition-colors">+ Agregar</button>
                    </div>
                    <ul id="listaMaterias" class="space-y-2 max-h-64 overflow-y-auto"></ul>
                </div>

                <div id="tab-prompt" class="config-tab-panel hidden px-6 py-5 space-y-3">
                    <p class="text-xs text-slate-500">Personaliza el texto de contexto que se envía a la IA. Puedes usar variables entre llaves como <code class="bg-slate-100 px-1 rounded">{grado}</code>.</p>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Contexto / rol de la IA</label>
                        <textarea id="customPromptContext" rows="5" class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-purple-500 resize-y"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Instrucciones pedagógicas</label>
                        <textarea id="customPromptInstructions" rows="10" class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-purple-500 resize-y"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="button" id="btnResetPrompt" class="text-xs text-red-500 hover:text-red-700 underline">↺ Restablecer prompt por defecto</button>
                        <button type="button" id="btnPreviewPrompt" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-1.5 rounded-md border border-slate-200 transition-colors">👁 Ver prompt completo</button>
                    </div>
                    <div id="promptPreviewBox" class="hidden">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Vista previa:</label>
                        <pre id="promptPreviewText" class="w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-mono whitespace-pre-wrap overflow-x-auto max-h-64 overflow-y-auto"></pre>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex justify-end flex-shrink-0">
                <button id="btnSaveModal" class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-5 py-2 rounded-md transition-colors">Guardar y cerrar</button>
            </div>
        </div>
    </div>

    <aside id="sidebar" class="w-[45%] min-w-[380px] max-w-[60%] bg-white border-r border-slate-200 flex flex-col h-full relative shadow-sm z-10">
        <div class="p-6 overflow-y-auto flex-1">
            <!-- Enlaces adicionales solicitados -->
            <div class="flex gap-3 mb-6">
                <a href="../../Planeador/calendario.php" target="_blank" class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-2 rounded-md transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Ver Calendario
                </a>
                <a href="../../Planeador/img/TAXONOMiA.jpg" target="_blank" class="flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-3 py-2 rounded-md transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    Taxonomía Bloom
                </a>
                             <a href="../../apps/dba/index.php" target="_blank" class="flex items-center gap-1.5 text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-2 rounded-md transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                 DBA
                </a>
            </div>

            <div class="flex items-center justify-between mb-2">
                <h1 class="text-xl font-bold text-slate-800">Paso 1: Tus Datos Base</h1>
                <div class="flex items-center gap-2">
                    <button id="btnClearAll" type="button" class="flex items-center gap-2 text-xs font-semibold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-md transition-colors">
                        Limpiar todo
                    </button>
                    <button id="btnOpenModal" class="flex items-center gap-2 text-xs font-semibold text-purple-700 border border-purple-200 bg-purple-50 hover:bg-purple-100 px-3 py-2 rounded-md transition-colors">
                        Configurar IA
                    </button>
                </div>
            </div>
            <p class="text-sm text-slate-500 mb-6">Diligencia tus aportes. La IA generará el resto.</p>

            <div id="dbPlanPanel" class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p id="dbPlanTitle" class="text-sm font-bold text-emerald-900">Nueva planeación</p>
                        <p id="dbPlanHint" class="text-xs text-emerald-700 mt-0.5">Lista para guardar en la base de datos.</p>
                    </div>
                    <span id="dbPlanBadge" class="shrink-0 rounded-full border border-emerald-200 bg-white px-2 py-1 text-[11px] font-semibold text-emerald-700">BD</span>
                </div>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Mes</label>
                        <select id="mes" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Periodo</label>
                        <select id="periodo" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Grado</label>
                        <select id="grado" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="Sexto">Sexto</option>
                            <option value="Séptimo">Séptimo</option>
                            <option value="Octavo">Octavo</option>
                            <option value="Noveno">Noveno</option>
                            <option value="Décimo">Décimo</option>
                            <option value="Undécimo">Undécimo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Materia</label>
                        <select id="materia" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></select>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-slate-700">Tiempo </label>
                            <button type="button" data-clear-field="tiempo" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                        </div>
                        <input type="number" id="tiempo" placeholder="Ej: 2 horas" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-slate-700">Fecha inicio</label>
                            <button type="button" data-clear-field="fecha_inicio" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                        </div>
                        <input type="date" id="fecha_inicio" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-slate-700">Fecha fin</label>
                            <button type="button" data-clear-field="fecha_fin" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                        </div>
                        <input type="date" id="fecha_fin" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Tú asignas: Referente de Calidad (DBA)</label>
                        <button type="button" data-clear-field="dba" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="dba" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Tú asignas: Ejes temáticos</label>
                        <button type="button" data-clear-field="ejes_tematicos" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="ejes_tematicos" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700" style='color:blue'>Tú asignas: Observaciones Adicionales</label>
                        <button type="button" data-clear-field="observaciones" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="observaciones" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Objetivo de Aprendizaje (opcional)</label>
                        <button type="button" data-clear-field="objetivo" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="objetivo" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
               
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Reflexión pedagógica</label>
                        <button type="button" data-clear-field="reflexion" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="reflexion" rows="3" class="w-full rounded-md border border-emerald-300 bg-emerald-50/40 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Estrategias</label>
                        <button type="button" data-clear-field="estrategia" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="estrategia" rows="3" class="w-full rounded-md border border-purple-300 bg-purple-50/40 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Observaciones / momentos de clase</label>
                        <button type="button" data-clear-field="momentos" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="momentos" rows="5" class="w-full rounded-md border border-purple-300 bg-purple-50/40 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Evaluación</label>
                        <button type="button" data-clear-field="evaluacion" class="text-[11px] font-semibold text-slate-400 hover:text-red-600">Limpiar</button>
                    </div>
                    <textarea id="evaluacion" rows="3" class="w-full rounded-md border border-purple-300 bg-purple-50/40 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>
<button id="btnGenerarLM" class="w-full mt-4 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-md transition-colors shadow-sm flex justify-center items-center gap-2">
                    Generar con IA
                </button>
                <button id="btnGuardarBD" class="w-full mt-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 px-4 rounded-md transition-colors shadow-sm flex justify-center items-center gap-2">
                    Guardar planeación
                </button>
                <button id="btnDescargarWordSidebar" class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-md transition-colors shadow-sm flex justify-center items-center gap-2">
                    Descargar Planeador Word
                </button>
                
    
                <div id="loadingIndicator" class="hidden flex-col items-center justify-center p-4">
                    <div class="loader border-4 border-slate-200 border-t-purple-600 rounded-full w-8 h-8 mb-2"></div>
                    <p class="text-sm text-slate-600 font-medium text-center">IA procesando la solicitud...</p>
                </div>

                <div id="aiFieldsContainer" class="hidden border-t border-slate-200 pt-4 mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <button id="btnTogglePromptViewer" type="button" aria-expanded="false" class="text-sm font-bold text-purple-700 flex items-center gap-2 hover:text-purple-900 transition-colors">
                            <span id="promptToggleIcon">▶</span>
                            Prompt enviado a la IA
                        </button>
                        <button id="btnCopyPrompt" title="Copiar prompt" class="text-xs text-slate-400 hover:text-purple-600 transition-colors px-2 py-1 rounded border border-slate-200 hover:border-purple-300">📋 Copiar</button>
                    </div>
                    <pre id="promptViewer" class="hidden w-full rounded-md border border-purple-100 bg-purple-50 px-3 py-2 text-xs font-mono whitespace-pre-wrap overflow-x-auto max-h-72 overflow-y-auto text-slate-700 leading-relaxed">El prompt aparecerá aquí tras generar.</pre>
                </div>
                
                <div id="statusMessage" class="hidden rounded-md p-3 text-sm font-medium mt-4"></div>
            </div>
        </div>
        <div id="resizer" class="absolute top-0 right-0 w-1.5 h-full cursor-col-resize hover:bg-blue-400 opacity-50 z-20"></div>
    </aside>

    <main class="flex-1 bg-slate-200 p-8 overflow-y-auto flex flex-col items-center relative">
        <div class="w-full max-w-4xl bg-white rounded-lg shadow-md border border-slate-300 flex flex-col mb-8 relative">
            <div class="p-8 pb-6">
                <h2 class="text-xl font-bold text-slate-800 border-b pb-2 mb-6 flex flex-col gap-2 2xl:flex-row 2xl:items-center 2xl:justify-between">
                    Paso 2: Vista Previa del Documento Completo
                    <span id="badgeEstado" class="text-xs font-medium bg-amber-100 text-amber-800 px-2 py-1 rounded-full border border-amber-200">Falta la IA</span>
                </h2>
                
                <div class="grid grid-cols-2 gap-2 mb-6 border-b border-slate-100 pb-4" style="grid-template-columns: repeat(4, 1fr);">
                    <div class="bg-slate-50 rounded-md px-3 py-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Mes</span> <span id="prev-mes" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Periodo</span> <span id="prev-periodo" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Inicio</span> <span id="prev-fecha_inicio" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Fin</span> <span id="prev-fecha_fin" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2 col-span-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Docente</span> <span id="prev-docente" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Grado</span> <span id="prev-grado" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Tiempo</span> <span id="prev-tiempo" class="text-slate-800 text-sm font-medium"></span></div>
                    <div class="bg-slate-50 rounded-md px-3 py-2 col-span-4"><span class="font-semibold text-slate-500 block text-xs uppercase tracking-wide">Materia</span> <span id="prev-materia" class="text-slate-800 text-sm font-medium"></span></div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-slate-600 text-sm">DBA:</h3>
                        <p id="prev-dba" class="text-slate-800 text-sm whitespace-pre-wrap bg-slate-50 p-2 rounded border border-slate-200 min-h-[2rem]"></p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-600 text-sm">Ejes temáticos:</h3>
                        <p id="prev-ejes_tematicos" class="text-slate-800 text-sm whitespace-pre-wrap bg-slate-50 p-2 rounded border border-slate-200 min-h-[2rem]"></p>
                    </div>
                    <div class="border-l-4 border-purple-400 pl-3">
                        <h3 class="font-semibold text-purple-700 text-sm">✨ Objetivo:</h3>
                        <p id="prev-objetivo" class="text-slate-800 text-sm whitespace-pre-wrap bg-purple-50 p-2 rounded border border-purple-100 min-h-[2rem]"></p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-600 text-sm">Observaciones:</h3>
                        <p id="prev-observaciones" class="text-slate-800 text-sm whitespace-pre-wrap bg-slate-50 p-2 rounded border border-slate-200 min-h-[2rem]"></p>
                    </div>
                    <div class="border-l-4 border-emerald-400 pl-3">
                        <h3 class="font-semibold text-emerald-700 text-sm">Reflexión pedagógica:</h3>
                        <p id="prev-reflexion" class="text-slate-800 text-sm whitespace-pre-wrap bg-emerald-50 p-2 rounded border border-emerald-100 min-h-[2rem]"></p>
                    </div>
                    <div class="border-l-4 border-purple-400 pl-3">
                        <h3 class="font-semibold text-purple-700 text-sm">✨ Estrategia Educativa:</h3>
                        <p id="prev-estrategia" class="text-slate-800 text-sm whitespace-pre-wrap bg-purple-50 p-2 rounded border border-purple-100 min-h-[2rem]"></p>
                    </div>
                    <div class="border-l-4 border-purple-400 pl-3">
                        <h3 class="font-semibold text-purple-700 text-sm">✨ Momentos de Clase:</h3>
                        <p id="prev-momentos" class="text-slate-800 text-sm whitespace-pre-wrap bg-purple-50 p-2 rounded border border-purple-100 min-h-[2rem]"></p>
                    </div>
                    <div class="border-l-4 border-purple-400 pl-3">
                        <h3 class="font-semibold text-purple-700 text-sm">✨ Evaluación:</h3>
                        <p id="prev-evaluacion" class="text-slate-800 text-sm whitespace-pre-wrap bg-purple-50 p-2 rounded border border-purple-100 min-h-[2rem]"></p>
                    </div>
                </div>
            </div>

            <!-- Panel de refinamiento IA -->
            <div id="refinePanel" class="hidden border-t border-amber-200 bg-amber-50 px-6 py-4">
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-amber-800 mb-1">✏️ ¿Qué quieres ajustar?</label>
                        <textarea id="refineInstruction" rows="2" placeholder="Ej: Cambia los momentos para incluir trabajo colaborativo. Mantén la estrategia y evaluación como están." class="w-full rounded-md border border-amber-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none placeholder-amber-300"></textarea>
                    </div>
                    <button id="btnRefinar" class="mt-5 flex-shrink-0 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-md shadow-sm flex items-center gap-2 transition-colors">
                        <svg id="refineSpinner" class="hidden animate-spin" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                        🔄 Actualizar
                    </button>
                </div>
                <p id="refineStatus" class="hidden text-xs text-amber-700 mt-2 font-medium"></p>
            </div>

            <div id="contenedorDescarga" class="hidden bg-slate-50 p-6 rounded-b-lg border-t border-slate-200 flex items-center justify-between transition-all duration-500 ease-in-out">
                <p id="downloadStatus" class="text-sm text-slate-600 font-medium">El planeador está completo. Verifica y descarga.</p>
                <button id="btnDescargarWord" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-md shadow-sm flex items-center gap-2 transform transition-transform hover:scale-105">
                    Descargar Planeador Word
                </button>
            </div>
        </div>
    </main>

    <script>
        const DEFAULT_LOCAL_URL = 'http://localhost:1234/v1/chat/completions';
        const DEFAULT_GEMINI_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
        const DEFAULT_OUTPUT_PATH = 'G:\\Mi unidad\\Josefina\\Vallesol2026\\Planeaciones';
        const MONTHS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const PLANMIND_INITIAL_PLAN = <?php echo json_encode($planmindInitialPlan, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const PLANMIND_INITIAL_STATUS = <?php echo json_encode($planmindInitialStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const PLANMIND_OFFICIAL_MATERIAS = <?php echo json_encode($planmindOfficialMaterias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const PLANMIND_ASIGNACION_DATA = <?php echo json_encode($planmindAsignacionData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const PLANMIND_ACTIVE_PERIODO = <?php echo json_encode($planmindActivePeriodo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        const apiTypeSelect      = document.getElementById('apiType');
        const apiUrlInput        = document.getElementById('apiUrl');
        const apiKeyInput        = document.getElementById('apiKey');
        const apiModelSelect     = document.getElementById('apiModel');
        const btnRefreshModels   = document.getElementById('btnRefreshModels');
        const localModelConfig   = document.getElementById('localModelConfig');
        const periodCountSelect  = document.getElementById('periodCount');
        const mesInput           = document.getElementById('mes');
        const periodoInput       = document.getElementById('periodo');
        const fechaInicioInput   = document.getElementById('fecha_inicio');
        const fechaFinInput      = document.getElementById('fecha_fin');
        const btnGenerarLM       = document.getElementById('btnGenerarLM');
        const loadingIndicator   = document.getElementById('loadingIndicator');
        const contenedorDescarga = document.getElementById('contenedorDescarga');
        const badgeEstado        = document.getElementById('badgeEstado');
        const aiFieldsContainer  = document.getElementById('aiFieldsContainer');
        const templateDocxInput  = document.getElementById('templateDocx');
        const templatePathInput  = document.getElementById('templatePath');
        const templatePathContainer = document.getElementById('templatePathContainer');
        const templateModeButtons   = document.querySelectorAll('[data-template-mode]');
        const outputPathInput       = document.getElementById('outputPath');
        const outputPathContainer   = document.getElementById('outputPathContainer');
        const outputModeButtons     = document.querySelectorAll('[data-output-mode]');
        const btnGuardarBD          = document.getElementById('btnGuardarBD');
        const dbPlanTitle           = document.getElementById('dbPlanTitle');
        const dbPlanHint            = document.getElementById('dbPlanHint');
        const dbPlanBadge           = document.getElementById('dbPlanBadge');

        const configModal   = document.getElementById('configModal');
        const btnOpenModal  = document.getElementById('btnOpenModal');
        const btnCloseModal = document.getElementById('btnCloseModal');
        const btnSaveModal  = document.getElementById('btnSaveModal');

        const DEFAULT_MATERIAS = ['Matemáticas', 'Ciencias Sociales', 'Geometría', 'Física', 'Urbanidad', 'Educación Física', 'Tecnología', 'Ciencias Políticas', 'Emprendimiento'];
        const baseFields = ['mes', 'periodo', 'docente', 'grado', 'materia', 'tiempo', 'fecha_inicio', 'fecha_fin', 'dba', 'ejes_tematicos', 'observaciones', 'reflexion'];
        const aiFields   = ['objetivo', 'estrategia', 'momentos', 'evaluacion'];
        const allFields  = [...baseFields, ...aiFields];

        let templateMode    = localStorage.getItem('planmindTemplateMode') || 'upload';
        if (!['upload', 'path'].includes(templateMode)) templateMode = 'upload';
        let outputMode = localStorage.getItem('planmindOutputMode') || 'download';
        if (!['download', 'path'].includes(outputMode)) outputMode = 'download';
        let syncingCalendar = false;
        let lastPromptSent  = ''; // Guarda el último prompt enviado a la IA
        let currentPlanId   = PLANMIND_INITIAL_PLAN?.id_plan ? Number(PLANMIND_INITIAL_PLAN.id_plan) : 0;

        const resizer = document.getElementById('resizer');
        const sidebar = document.getElementById('sidebar');
        let isResizing = false;
        resizer.addEventListener('mousedown', () => { isResizing = true; document.body.classList.add('resizing'); });
        document.addEventListener('mousemove', (e) => {
            if (!isResizing) return;
            const newWidth = Math.max(380, Math.min(e.clientX, window.innerWidth * 0.6));
            sidebar.style.width = `${newWidth}px`;
        });
        document.addEventListener('mouseup', () => {
            if (isResizing) { isResizing = false; document.body.classList.remove('resizing'); }
        });

        // Sistema de Modal Personalizado para evitar bloqueos del navegador
        let confirmCallback = null;
        function showConfirm(msg, callback) {
            document.getElementById('customConfirmMessage').textContent = msg;
            confirmCallback = callback;
            const modal = document.getElementById('customConfirmModal');
            modal.classList.remove('modal-hidden');
            modal.classList.add('modal-visible');
        }
        document.getElementById('btnConfirmNo').addEventListener('click', () => {
            document.getElementById('customConfirmModal').classList.add('modal-hidden');
            document.getElementById('customConfirmModal').classList.remove('modal-visible');
            confirmCallback = null;
        });
        document.getElementById('btnConfirmYes').addEventListener('click', () => {
            document.getElementById('customConfirmModal').classList.add('modal-hidden');
            document.getElementById('customConfirmModal').classList.remove('modal-visible');
            if (confirmCallback) confirmCallback();
        });

        document.querySelectorAll('.config-tab').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.config-tab').forEach(b => {
                    b.classList.remove('tab-active', 'border-purple-600', 'text-purple-700');
                    b.classList.add('border-transparent', 'text-slate-500');
                });
                btn.classList.add('tab-active', 'border-purple-600', 'text-purple-700');
                btn.classList.remove('border-transparent', 'text-slate-500');
                document.querySelectorAll('.config-tab-panel').forEach(p => p.classList.add('hidden'));
                document.getElementById(`tab-${btn.dataset.tab}`).classList.remove('hidden');
            });
        });

        function normalizeMateriaText(value) {
            return String(value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/&/g, ' y ')
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, ' ')
                .trim();
        }

        function materiaTokens(value) {
            return normalizeMateriaText(value)
                .split(/\s+/)
                .filter(token => token.length >= 4 && !/^\d+$/.test(token));
        }

        function getOfficialMateriaRows() {
            const rows = Array.isArray(PLANMIND_OFFICIAL_MATERIAS) ? PLANMIND_OFFICIAL_MATERIAS : [];
            const filtered = rows
                .map(row => ({
                    id_materia: String(row.id_materia || '').trim(),
                    nombre_materia: String(row.nombre_materia || '').trim()
                }))
                .filter(row => row.id_materia && row.nombre_materia);

            if (filtered.length > 0) return filtered;
            return DEFAULT_MATERIAS.map(name => ({ id_materia: name, nombre_materia: name }));
        }

        function findOfficialMateria(value) {
            const text = String(value ?? '').trim();
            if (!text) return null;

            const rows = getOfficialMateriaRows();
            const byId = rows.find(row => String(row.id_materia) === text);
            if (byId) return byId;

            const normalized = normalizeMateriaText(text);
            if (!normalized) return null;

            let best = null;
            let bestScore = 0;
            const candidateTokens = materiaTokens(text);

            rows.forEach(row => {
                const officialNorm = normalizeMateriaText(row.nombre_materia);
                if (!officialNorm) return;

                let score = 0;
                if (normalized === officialNorm) {
                    score = 1;
                } else if (normalized.includes(officialNorm) || officialNorm.includes(normalized)) {
                    score = 0.9;
                } else {
                    const officialTokens = materiaTokens(row.nombre_materia);
                    const matches = officialTokens.filter(token => candidateTokens.includes(token)).length;
                    score = officialTokens.length ? matches / officialTokens.length : 0;
                }

                if (score > bestScore) {
                    bestScore = score;
                    best = row;
                }
            });

            return bestScore >= 0.45 ? best : null;
        }

        function getSavedMaterias() {
            try {
                const saved = localStorage.getItem('planmindMaterias');
                const list = saved ? JSON.parse(saved) : [];
                return Array.isArray(list) ? list.map(item => String(item || '').trim()).filter(Boolean) : [];
            } catch {
                return [];
            }
        }
        function saveMaterias(list) { localStorage.setItem('planmindMaterias', JSON.stringify(list)); }
        function ensureSelectOption(select, value, label = value) {
            if (!select || value === undefined || value === null || value === '') return;
            const valueText = String(value);
            const exists = Array.from(select.options).some(option => option.value === valueText);
            if (!exists) {
                const option = document.createElement('option');
                option.value = valueText;
                option.textContent = label || valueText;
                select.appendChild(option);
            }
            return valueText;
        }
        function ensureMateriaOption(value, label = value) {
            const select = document.getElementById('materia');
            if (!select) return '';

            const official = findOfficialMateria(value) || findOfficialMateria(label);
            if (official) return String(official.id_materia);

            const rawValue = String(value ?? label ?? '').trim();
            const rawLabel = String(label ?? value ?? '').trim();
            if (!rawValue && !rawLabel) return '';

            return ensureSelectOption(select, rawValue || rawLabel, rawLabel || rawValue);
        }
        function getMateriaDisplayName(value = document.getElementById('materia')?.value, fallback = '') {
            const select = document.getElementById('materia');
            if (select && select.value === String(value ?? '') && select.selectedOptions[0]) {
                return select.selectedOptions[0].textContent.trim();
            }

            const official = findOfficialMateria(value) || findOfficialMateria(fallback);
            if (official) return official.nombre_materia;
            return String(fallback || value || '').trim();
        }
        function renderMateriasList() {
            const ul = document.getElementById('listaMaterias');
            ul.innerHTML = '';
            const officialRows = getOfficialMateriaRows();
            officialRows.forEach(row => {
                const li = document.createElement('li');
                li.className = 'materia-item';
                const span = document.createElement('span');
                span.textContent = row.nombre_materia;
                const badge = document.createElement('span');
                badge.className = 'text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-0.5';
                badge.textContent = 'BD';
                li.append(span, badge);
                ul.appendChild(li);
            });

            const customList = getSavedMaterias().filter(m => !findOfficialMateria(m));
            customList.forEach((m, i) => {
                const li = document.createElement('li');
                li.className = 'materia-item';
                const span = document.createElement('span');
                span.textContent = m;
                const button = document.createElement('button');
                button.type = 'button';
                button.title = 'Eliminar';
                button.textContent = '✕';
                button.addEventListener('click', () => {
                    customList.splice(i, 1); saveMaterias(customList); renderMateriasList(); populateMateriaSelect();
                });
                li.append(span, button);
                ul.appendChild(li);
            });
        }
        function populateMateriaSelect() {
            const ms = document.getElementById('materia');
            const current = ms.value;
            ms.innerHTML = '';

            const used = new Set();
            getOfficialMateriaRows().forEach(row => {
                const option = document.createElement('option');
                option.value = String(row.id_materia);
                option.textContent = row.nombre_materia;
                option.dataset.official = '1';
                ms.appendChild(option);
                used.add(option.value);
            });

            getSavedMaterias().forEach(m => {
                if (findOfficialMateria(m) || used.has(m)) return;
                const option = document.createElement('option');
                option.value = m;
                option.textContent = m;
                ms.appendChild(option);
                used.add(m);
            });

            const resolved = ensureMateriaOption(current);
            if (resolved && Array.from(ms.options).some(option => option.value === resolved)) {
                ms.value = resolved;
            }
        }
        document.getElementById('btnAgregarMateria').addEventListener('click', () => {
            const input = document.getElementById('nuevaMateria');
            const val = input.value.trim();
            if (!val) return;
            const list = getSavedMaterias();
            if (!findOfficialMateria(val) && !list.includes(val)) { list.push(val); saveMaterias(list); }
            input.value = ''; renderMateriasList(); populateMateriaSelect();
        });

        const DEFAULT_PROMPT_CONTEXT = `Eres un docente experto en escuela nueva y postprimaria rural colombiana.`;
        const DEFAULT_PROMPT_INSTRUCTIONS = `INSTRUCCIONES PEDAGÓGICAS:
1. OBJETIVO: redacta una propuesta concreta, clara y breve.
2. ESTRATEGIA: usa lenguaje pedagógico sencillo y contextualizado a ruralidad.
3. MOMENTOS: organiza Inicio, Desarrollo y Cierre.
4. EVALUACIÓN: incluye criterios observables y formativos.
5. FORMATO: devuelve únicamente JSON válido con claves exactas: "objetivo", "estrategia", "momentos", "evaluacion".`;

        function loadPromptFields() {
            document.getElementById('customPromptContext').value = localStorage.getItem('planmindPromptContext') || DEFAULT_PROMPT_CONTEXT;
            document.getElementById('customPromptInstructions').value = localStorage.getItem('planmindPromptInstructions') || DEFAULT_PROMPT_INSTRUCTIONS;
        }
        function savePromptFields() {
            localStorage.setItem('planmindPromptContext', document.getElementById('customPromptContext').value);
            localStorage.setItem('planmindPromptInstructions', document.getElementById('customPromptInstructions').value);
        }
        document.getElementById('btnResetPrompt').addEventListener('click', () => {
            showConfirm('¿Restablecer el prompt a los textos de fábrica?', () => {
                localStorage.removeItem('planmindPromptContext');
                localStorage.removeItem('planmindPromptInstructions');
                loadPromptFields();
                showMessage("Prompt restablecido.");
            });
        });
        document.getElementById('btnPreviewPrompt').addEventListener('click', () => {
            const previewBox = document.getElementById('promptPreviewBox');
            const previewText = document.getElementById('promptPreviewText');
            previewText.textContent = buildPrompt();
            previewBox.classList.remove('hidden');
        });
        function updatePromptPreviewIfVisible() {
            const previewBox = document.getElementById('promptPreviewBox');
            if (previewBox.classList.contains('hidden')) return;
            document.getElementById('promptPreviewText').textContent = buildPrompt();
        }

        function openModal() {
            renderMateriasList(); loadPromptFields();
            configModal.classList.remove('modal-hidden'); configModal.classList.add('modal-visible');
        }
        function closeModal() {
            savePromptFields();
            configModal.classList.add('modal-hidden'); configModal.classList.remove('modal-visible');
        }
        btnOpenModal.addEventListener('click', openModal);
        btnCloseModal.addEventListener('click', closeModal);
        btnSaveModal.addEventListener('click', closeModal);
        configModal.addEventListener('click', (e) => { if (e.target === configModal) closeModal(); });

        function showMessage(msg, isError = false) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.textContent = msg;
            statusDiv.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
            statusDiv.classList.add(isError ? 'bg-red-100' : 'bg-green-100', isError ? 'text-red-700' : 'text-green-700');
        }

        function showDownloadStatus(msg, isError = false) {
            const status = document.getElementById('downloadStatus');
            if (!status) return;
            status.textContent = msg;
            status.classList.toggle('text-red-700', isError);
            status.classList.toggle('text-slate-600', !isError);
            status.classList.toggle('text-green-700', !isError && /guardado|descargado|listo/i.test(msg));
        }

        function setDatabaseState(mode, detail = '') {
            if (!dbPlanTitle || !dbPlanHint || !dbPlanBadge) return;

            const states = {
                loaded: {
                    title: `Editando planeación #${currentPlanId}`,
                    hint: detail || 'Los cambios actualizarán este registro.',
                    badge: 'Cargada',
                    classes: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-700']
                },
                saved: {
                    title: `Planeación #${currentPlanId}`,
                    hint: detail || 'Guardada en la base de datos.',
                    badge: 'Guardada',
                    classes: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-700']
                },
                missing: {
                    title: 'Nueva planeación',
                    hint: detail || 'El ID consultado no existe.',
                    badge: 'Nueva',
                    classes: ['border-amber-200', 'bg-amber-50', 'text-amber-700']
                },
                error: {
                    title: 'Base de datos no disponible',
                    hint: detail || 'No se pudo consultar el registro.',
                    badge: 'Error',
                    classes: ['border-red-200', 'bg-red-50', 'text-red-700']
                },
                saveError: {
                    title: 'No se pudo guardar',
                    hint: detail || 'Revisa los campos de la planeación.',
                    badge: 'Revisar',
                    classes: ['border-red-200', 'bg-red-50', 'text-red-700']
                },
                fresh: {
                    title: 'Nueva planeación',
                    hint: detail || 'Lista para guardar en la base de datos.',
                    badge: 'BD',
                    classes: ['border-emerald-200', 'bg-emerald-50', 'text-emerald-700']
                }
            };

            const state = states[mode] || states.fresh;
            const panel = document.getElementById('dbPlanPanel');
            panel.className = `mb-5 rounded-md border p-3 ${state.classes[0]} ${state.classes[1]}`;
            dbPlanTitle.textContent = state.title;
            dbPlanHint.textContent = state.hint;
            dbPlanBadge.textContent = state.badge;
            dbPlanBadge.className = `shrink-0 rounded-full border bg-white px-2 py-1 text-[11px] font-semibold ${state.classes[0]} ${state.classes[2]}`;
        }

        function setFieldValue(id, value) {
            const input = document.getElementById(id);
            if (!input) return;
            input.value = value ?? '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function applyPlanData(plan) {
            if (!plan) return;

            if (plan.fecha_inicio) fechaInicioInput.value = plan.fecha_inicio;
            if (plan.fecha_fin) fechaFinInput.value = plan.fecha_fin;
            if (plan.fecha_inicio) syncCalendarFromDate(plan.fecha_inicio);

            ensureSelectOption(periodoInput, plan.periodo);
            ensureSelectOption(document.getElementById('grado'), plan.grado);
            const materiaValue = ensureMateriaOption(plan.materia, plan.materia_nombre || plan.materia);

            setFieldValue('periodo', plan.periodo || getPeriodValue());
            setFieldValue('grado', plan.grado || '');
            setFieldValue('materia', materiaValue || plan.materia || '');
            setFieldValue('tiempo', plan.tiempo || plan.tiempo_plan || '');
            setFieldValue('dba', plan.dba || '');
            setFieldValue('ejes_tematicos', plan.ejes_tematicos || plan.eje_tematico || '');
            setFieldValue('objetivo', plan.objetivo || '');
            setFieldValue('observaciones', '');
            setFieldValue('reflexion', plan.reflexion || '');
            setFieldValue('estrategia', plan.estrategia || plan.estrategias || '');
            
            let momentosStr = plan.momentos || plan.observaciones_db || '';
            const showHtmlTagsCheckbox = document.getElementById('showHtmlTags');
            if (showHtmlTagsCheckbox && !showHtmlTagsCheckbox.checked && (plan.id_plan || plan.id_plan > 0)) {
                // Eliminar etiquetas HTML
                const tmp = document.createElement("DIV");
                tmp.innerHTML = momentosStr;
                momentosStr = tmp.textContent || tmp.innerText || "";
            }
            setFieldValue('momentos', momentosStr);
            
            setFieldValue('evaluacion', plan.evaluacion || '');

            const hasAiContent = ['objetivo', 'estrategia', 'momentos', 'evaluacion'].some(id => document.getElementById(id)?.value.trim());
            aiFieldsContainer.classList.toggle('hidden', !hasAiContent);
            document.getElementById('refinePanel').classList.toggle('hidden', !hasAiContent);
            contenedorDescarga.classList.toggle('hidden', !hasAiContent);
            badgeEstado.textContent = hasAiContent ? 'Cargado' : 'Falta la IA';
            badgeEstado.classList.toggle('bg-green-100', hasAiContent);
            badgeEstado.classList.toggle('text-green-800', hasAiContent);
            badgeEstado.classList.toggle('bg-amber-100', !hasAiContent);
            badgeEstado.classList.toggle('text-amber-800', !hasAiContent);
            updatePreview();
        }

        function collectPlanPayload() {
            const value = (id) => document.getElementById(id)?.value || '';
            return {
                id_plan: currentPlanId,
                fecha_inicio: fechaInicioInput.value,
                fecha_fin: fechaFinInput.value,
                grado: value('grado'),
                materia: value('materia'),
                periodo: getPeriodValue(),
                tiempo: value('tiempo'),
                tiempo_plan: value('tiempo'),
                dba: value('dba'),
                ejes_tematicos: value('ejes_tematicos'),
                eje_tematico: value('ejes_tematicos'),
                objetivo: value('objetivo'),
                observaciones: value('observaciones'),
                reflexion: value('reflexion'),
                estrategia: value('estrategia'),
                estrategias: value('estrategia'),
                momentos: value('momentos'),
                evaluacion: value('evaluacion')
            };
        }

        async function savePlanToDatabase() {
            btnGuardarBD.disabled = true;
            btnGuardarBD.classList.add('opacity-60');
            const originalText = btnGuardarBD.textContent;
            btnGuardarBD.textContent = 'Guardando...';
            try {
                const response = await fetch('index.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(collectPlanPayload())
                });
                const result = await response.json().catch(() => null);
                if (!response.ok || !result?.ok) {
                    throw new Error(result?.message || 'No se pudo guardar la planeación.');
                }

                currentPlanId = Number(result.id_plan || currentPlanId);
                const url = new URL(window.location.href);
                url.searchParams.set('id', String(currentPlanId));
                url.searchParams.delete('action');
                window.history.replaceState({}, '', url);

                setDatabaseState('saved', result.mode === 'updated' ? 'Registro actualizado correctamente.' : 'Registro creado correctamente.');
                showMessage(result.mode === 'updated' ? 'Planeación actualizada en base de datos.' : 'Planeación guardada en base de datos.', false);
            } catch (error) {
                setDatabaseState('saveError', error.message);
                showMessage(`Error al guardar: ${error.message}`, true);
            } finally {
                btnGuardarBD.disabled = false;
                btnGuardarBD.classList.remove('opacity-60');
                btnGuardarBD.textContent = originalText;
            }
        }

        btnGuardarBD.addEventListener('click', savePlanToDatabase);

        function pad(value) { return String(value).padStart(2, '0'); }
        function toISODate(year, monthIndex, day) { return `${year}-${pad(monthIndex + 1)}-${pad(day)}`; }
        function parseISODate(value) {
            const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value || '');
            return match ? { year: Number(match[1]), monthIndex: Number(match[2]) - 1, day: Number(match[3]) } : null;
        }
        function formatDateForDoc(value) {
            const parsed = parseISODate(value);
            return parsed ? `${pad(parsed.day)}/${pad(parsed.monthIndex + 1)}/${parsed.year}` : '';
        }

        function getActiveYear() {
            const parsed = parseISODate(fechaInicioInput.value) || parseISODate(fechaFinInput.value);
            return parsed ? parsed.year : new Date().getFullYear();
        }
        function getMonthIndex() {
            const option = mesInput.selectedOptions[0];
            return option ? Number(option.dataset.monthIndex) : new Date().getMonth();
        }
        function getPeriodCount() { return Math.max(1, Math.min(4, Number(periodCountSelect.value) || 4)); }
        function getDefaultPeriodForMonth(monthIndex = getMonthIndex()) {
            return String(Math.min(getPeriodCount(), Math.max(1, Math.ceil((monthIndex + 1) / (12 / getPeriodCount())))));
        }

        function populateMonths() {
            mesInput.innerHTML = MONTHS.map((m, i) => `<option value="${m}" data-month-index="${i}">${m}</option>`).join('');
        }
        function populatePeriodOptions(preferredValue = periodoInput.value) {
            const count = getPeriodCount();
            periodoInput.innerHTML = Array.from({ length: count }, (_, i) => `<option value="${i+1}">${i+1}</option>`).join('');
            periodoInput.value = (preferredValue && Number(preferredValue) <= count) ? preferredValue : getDefaultPeriodForMonth();
        }
        function setDateRangeForMonth(monthIndex, year = getActiveYear(), updatePeriod = true) {
            const lastDay = new Date(year, monthIndex + 1, 0).getDate();
            fechaInicioInput.value = toISODate(year, monthIndex, 1);
            fechaFinInput.value   = toISODate(year, monthIndex, lastDay);
            mesInput.value = MONTHS[monthIndex];
            if (updatePeriod) periodoInput.value = getDefaultPeriodForMonth(monthIndex);
        }
        function syncCalendarFromMonth() {
            if (syncingCalendar) return; syncingCalendar = true; setDateRangeForMonth(getMonthIndex()); syncingCalendar = false; updatePreview();
        }
        function syncCalendarFromDate(value) {
            if (syncingCalendar) return; const parsed = parseISODate(value);
            if (!parsed) return;
            syncingCalendar = true;
            mesInput.value = MONTHS[parsed.monthIndex];
            periodoInput.value = getDefaultPeriodForMonth(parsed.monthIndex);
            syncingCalendar = false;
            updatePreview();
        }

        function getPeriodValue() { return periodoInput.value || getDefaultPeriodForMonth(); }
        function getPreviewValue(id) {
            const input = document.getElementById(id);
            if (!input) return '';
            if (id === 'periodo') return getPeriodValue();
            if (id === 'materia') return getMateriaDisplayName(input.value);
            if (id === 'fecha_inicio' || id === 'fecha_fin') return formatDateForDoc(input.value);
            return input.value.trim();
        }
        function updatePreview() {
            allFields.forEach(id => {
                const preview = document.getElementById(`prev-${id}`);
                if (preview) {
                    const value = getPreviewValue(id);
                    if (value !== '') { preview.textContent = value; preview.classList.remove('italic', 'text-slate-400'); }
                    else { preview.textContent = aiFields.includes(id) ? 'Esperando a la IA...' : 'Pendiente...'; preview.classList.add('italic', 'text-slate-400'); }
                }
            });
            updatePromptPreviewIfVisible();
        }

        function setApiType(type) {
            if (type === 'local') {
                apiUrlInput.value = DEFAULT_LOCAL_URL;
                localModelConfig.classList.remove('hidden'); loadLocalModels(false);
            } else {
                apiUrlInput.value = DEFAULT_GEMINI_URL;
                localModelConfig.classList.add('hidden');
            }
        }
        async function loadLocalModels(showStatus = true) {
            if (apiTypeSelect.value !== 'local') return [];
            try {
                const endpoint = apiUrlInput.value.replace(/\/chat\/completions\/?$/i, '/models');
                const response = await fetch(endpoint, { cache: 'no-store' });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                const models = Array.isArray(data.data) ? data.data.filter(m => m && m.id) : [];
                
                apiModelSelect.innerHTML = '<option value="">Selecciona tu modelo</option>';
                models.forEach(m => { const opt = document.createElement('option'); opt.value = m.id; opt.textContent = m.id; apiModelSelect.appendChild(opt); });
                
                const saved = localStorage.getItem('planmindApiModel');
                if (saved && models.some(m => m.id === saved)) apiModelSelect.value = saved;
                else if (models.length > 0) apiModelSelect.value = models[0].id;
                
                if (showStatus) showMessage("Modelos locales actualizados.", false);
                return models;
            } catch (error) {
                if (showStatus) showMessage(`Aviso: Error conectando al servidor local (${error.message}).`, true);
                return [];
            }
        }

        // ── Parser JSON robusto con múltiples capas de recuperación ─────────────

        /** Extrae el bloque JSON más externo del texto bruto de la IA */
        function extractJsonCandidate(rawText) {
            let text = String(rawText || '')
                .replace(/```json[\s\S]*?```/gi, m => m.replace(/```json/i,'').replace(/```/,''))
                .replace(/```[\s\S]*?```/g, m => m.replace(/```/g,''))
                .replace(/^\uFEFF/, '')
                .trim();
            const start = text.indexOf('{');
            if (start < 0) throw new Error('La IA no devolvió un objeto JSON.');
            let depth = 0, inString = false, escaping = false;
            for (let i = start; i < text.length; i++) {
                const c = text[i];
                if (inString) {
                    if (escaping) escaping = false;
                    else if (c === '\\') escaping = true;
                    else if (c === '"') inString = false;
                    continue;
                }
                if (c === '"') inString = true;
                if (c === '{') depth++;
                if (c === '}') { depth--; if (depth === 0) return text.slice(start, i + 1); }
            }
            return text.slice(start); // sin cerrar — lo repara repairJson
        }

        /** Aplica múltiples reparaciones al texto JSON antes de parsearlo */
        function repairJson(text) {
            return text
                // Comillas tipográficas → rectas
                .replace(/[\u201C\u201D\u00AB\u00BB]/g, '"')
                .replace(/[\u2018\u2019\u201A\u201B]/g, "'")
                // Caracteres de control prohibidos (salvo \t \n)
                .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g, '')
                // Comas antes de ] o } (trailing commas)
                .replace(/,\s*([\]}])/g, '$1')
                // Comas dobles
                .replace(/,\s*,/g, ',')
                // Cierra arrays/objetos que quedaron abiertos al final
                .replace(/([^,{\[])\s*$/, (m) => {
                    const open = (m.match(/{/g)||[]).length - (m.match(/}/g)||[]).length;
                    const openArr = (m.match(/\[/g)||[]).length - (m.match(/\]/g)||[]).length;
                    return m + ']'.repeat(Math.max(0, openArr)) + '}'.repeat(Math.max(0, open));
                })
                .trim();
        }

        /** Extrae campos clave por regex cuando el JSON está demasiado roto */
        function extractFieldsByRegex(rawText) {
            const result = {};
            const fields = ['objetivo', 'objetivos', 'estrategia', 'estrategia_educativa',
                            'momentos', 'evaluacion'];
            fields.forEach(f => {
                // Busca "campo": "valor" o "campo": { ... } o "campo": [ ... ]
                const re = new RegExp(`"${f}"\\s*:\\s*("(?:[^"\\\\]|\\\\.)*"|\\{[^}]*\\}|\\[[^\\]]*\\])`, 'i');
                const m = rawText.match(re);
                if (m) {
                    try { result[f] = JSON.parse(m[1]); } catch { result[f] = m[1].replace(/^"|"$/g,'').trim(); }
                }
            });
            return result;
        }

        /**
         * Parsea el JSON devuelto por la IA con 3 capas de recuperación:
         *  1. Extraer + reparar + parsear
         *  2. Reparar el texto completo + parsear
         *  3. Extraer campos por regex (fallback sin errores para el usuario)
         */
        function parseGeneratedJson(rawText) {
            // Capa 1: flujo normal
            try {
                const candidate = extractJsonCandidate(rawText);
                return JSON.parse(repairJson(candidate));
            } catch (e1) { /* sigue */ }

            // Capa 2: reparar todo el texto crudo y volver a intentar
            try {
                const cleaned = repairJson(String(rawText || '')
                    .replace(/```json/gi, '').replace(/```/g, '').replace(/^\uFEFF/, '').trim());
                const start = cleaned.indexOf('{');
                if (start >= 0) return JSON.parse(cleaned.slice(start));
            } catch (e2) { /* sigue */ }

            // Capa 3: extracción por regex (resultado parcial pero usable)
            const partial = extractFieldsByRegex(rawText);
            if (Object.keys(partial).length > 0) {
                console.warn('PlanMind: JSON malformado, se usó extracción por regex.', partial);
                return partial;
            }

            // Si todo falla, error claro
            throw new Error('La IA devolvió una respuesta que no se pudo interpretar. Intenta de nuevo.');
        }
        // ─────────────────────────────────────────────────────────────────────────

        // ── Sanitización de caracteres XML inválidos ──────────────────────────
        function sanitizeXml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/[\x00-\x08\x0B\x0C\x0E-\x1F]/g, '') // Caracteres de control prohibidos en XML
                .replace(/\uFFFE|\uFFFF/g, '')                  // Bytes no-carácter Unicode
                .replace(/\r\n/g, '\n').replace(/\r/g, '\n')   // Normalizar saltos de línea
                .trim();
        }
        function sanitizeObject(obj) {
            if (obj === null || obj === undefined) return obj;
            if (typeof obj === 'string') return sanitizeXml(obj);
            if (Array.isArray(obj)) return obj.map(item => sanitizeObject(item));
            if (typeof obj === 'object') {
                const clean = {};
                Object.keys(obj).forEach(key => { clean[key] = sanitizeObject(obj[key]); });
                return clean;
            }
            return obj;
        }
        // ─────────────────────────────────────────────────────────────────────

        /**
         * Convierte CUALQUIER valor devuelto por la IA a texto plano legible.
         * Nunca produce [object Object]. Completamente recursiva.
         */
        function deepToText(val, nivel) {
            nivel = nivel || 0;
            if (val === null || val === undefined) return '';
            if (typeof val === 'boolean') return val ? 'Sí' : 'No';
            if (typeof val === 'number') return String(val);
            if (typeof val === 'string') return val.trim();

            if (Array.isArray(val)) {
                // Array de primitivos → viñetas
                if (val.every(i => typeof i !== 'object' || i === null)) {
                    return val.filter(i => i !== null && i !== undefined && i !== '').map(i => `• ${String(i).trim()}`).join('\n');
                }
                // Array de objetos → cada uno separado
                return val.map(item => deepToText(item, nivel)).filter(Boolean).join('\n\n');
            }

            if (typeof val === 'object') {
                const indent = nivel > 0 ? '  '.repeat(nivel) : '';
                return Object.entries(val)
                    .map(([k, v]) => {
                        if (v === null || v === undefined || v === '' || (typeof v === 'object' && !Array.isArray(v) && Object.keys(v).length === 0)) return '';
                        const titulo = capitalize(k);
                        const contenido = deepToText(v, nivel + 1);
                        if (!contenido) return '';
                        // Si el contenido tiene saltos de línea, va debajo del título
                        if (contenido.includes('\n')) return `${indent}${titulo}:\n${contenido}`;
                        return `${indent}${titulo}: ${contenido}`;
                    })
                    .filter(Boolean)
                    .join('\n');
            }
            return String(val);
        }

        function toReadableText(val) {
            if (val === null || val === undefined) return '';
            // Log para depuración: ver en consola qué estructura exacta devuelve la IA
            console.log('[PlanMind] toReadableText input:', JSON.stringify(val, null, 2));
            const result = deepToText(val, 0);
            // Guardia final: si aún aparece [object Object] forzar stringify legible
            if (result.includes('[object')) {
                console.warn('[PlanMind] Fallback: estructura inesperada, usando JSON.stringify');
                return JSON.stringify(val, null, 2)
                    .replace(/[{}"]/g, '')
                    .replace(/,\s*$/gm, '')
                    .replace(/^\s*\n/gm, '')
                    .trim();
            }
            return result;
        }

        function capitalize(str) {
            return String(str).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
        }
        // ─────────────────────────────────────────────────────────────────────

        function formatDocxError(error) {
            if (!error) return 'Error desconocido al procesar la plantilla Word.';
            const props = error.properties || {};
            const pieces = [];

            if (Array.isArray(props.errors) && props.errors.length > 0) {
                pieces.push(`La plantilla tiene ${props.errors.length} error(es).`);
                props.errors.slice(0, 6).forEach((err, index) => {
                    const eprops = err?.properties || {};
                    const tag = eprops.xtag || eprops.tag || eprops.id || 'etiqueta no identificada';
                    const explanation = eprops.explanation || err.message || 'Sin explicación.';
                    const context = eprops.context ? ` Contexto: ${eprops.context}.` : '';
                    pieces.push(`${index + 1}) ${tag}. ${explanation}.${context}`);
                });
                if (props.errors.length > 6) {
                    pieces.push(`Y ${props.errors.length - 6} error(es) más.`);
                }
                pieces.push('Revisa que en Word todas las llaves estén bien cerradas y que cada etiqueta exista exactamente igual a como la envía el código.');
                return pieces.join(' ');
            }

            if (props.explanation) pieces.push(props.explanation);
            if (props.context) pieces.push(`Contexto: ${props.context}`);
            if (props.xtag) pieces.push(`Etiqueta: ${props.xtag}`);
            if (error.message) pieces.unshift(error.message);
            return pieces.filter(Boolean).join(' | ') || 'Error al procesar la plantilla Word.';
        }

        function getPromptVars() {
            return {
                mes: mesInput.value.trim() || 'No definido',
                periodo: getPeriodValue() || 'No definido',
                docente: document.getElementById('docente').value.trim() || 'No definido',
                grado: document.getElementById('grado').value || 'No definido',
                materia: getMateriaDisplayName() || 'Sin asignar',
                tiempo: document.getElementById('tiempo').value.trim() || 'No definido',
                fecha_inicio: formatDateForDoc(fechaInicioInput.value) || fechaInicioInput.value || 'No definida',
                fecha_fin: formatDateForDoc(fechaFinInput.value) || fechaFinInput.value || 'No definida',
                dba: document.getElementById('dba').value.trim() || 'No definido',
                ejes_tematicos: document.getElementById('ejes_tematicos').value.trim() || 'No definidos',
                objetivo: document.getElementById('objetivo').value.trim() || 'VACÍO: la IA debe generar el objetivo desde cero',
                observaciones: document.getElementById('observaciones').value.trim() || 'Ninguna',
                reflexion: document.getElementById('reflexion').value.trim() || 'Por diligenciar'
            };
        }

        function buildPlannerDataSection(vars) {
            return `DATOS DEL PLANEADOR:
- Mes: ${vars.mes}
- Periodo: ${vars.periodo}
- Docente: ${vars.docente}
- Grado: ${vars.grado}
- Materia: ${vars.materia}
- Tiempo disponible: ${vars.tiempo}
- Fecha de inicio: ${vars.fecha_inicio}
- Fecha de fin: ${vars.fecha_fin}
- Referente de Calidad (DBA): ${vars.dba}
- Tú asignas: Ejes temáticos: ${vars.ejes_tematicos}
- Objetivo de aprendizaje escrito por el docente: ${vars.objetivo}
- Observaciones Adicionales: ${vars.observaciones}
- Reflexión pedagógica registrada: ${vars.reflexion}`;
        }

        function buildPrompt() {
            const vars = getPromptVars();
            const ctx = document.getElementById('customPromptContext').value || DEFAULT_PROMPT_CONTEXT;
            const inst = document.getElementById('customPromptInstructions').value || DEFAULT_PROMPT_INSTRUCTIONS;
            const interpolate = (txt) => txt.replace(/\{(\w+)\}/g, (_, k) => vars[k] !== undefined ? vars[k] : `{${k}}`);

            return `${interpolate(ctx)}\n\n${buildPlannerDataSection(vars)}\n\n${interpolate(inst)}\n\nREGLA: Usa obligatoriamente el prompt de configuración y todos los datos del planeador anteriores, especialmente Referente de Calidad (DBA), Tú asignas: Ejes temáticos y Observaciones Adicionales. Si el objetivo de aprendizaje está vacío, genera uno coherente con el DBA, los ejes temáticos, las observaciones y el tiempo disponible; si el docente escribió un objetivo, consérvalo y mejóralo solo cuando aporte claridad. Devuelve ÚNICAMENTE un JSON válido con las claves: "objetivo", "estrategia", "momentos", "evaluacion". Sin comentarios adicionales.`;
        }

        btnGenerarLM.addEventListener('click', async () => {
            btnGenerarLM.disabled = true; btnGenerarLM.classList.add('opacity-50');
            loadingIndicator.classList.remove('hidden'); loadingIndicator.classList.add('flex');
            showMessage("Conectando con la IA...", false);
            try {
                const prompt = buildPrompt();
                const headers = { 'Content-Type': 'application/json' };
                let responseText = "";

                if (apiTypeSelect.value === 'gemini') {
                    const key = apiKeyInput.value.trim();
                    if(!key) throw new Error("Falta API Key de Google Gemini");
                    const res = await fetch(`${apiUrlInput.value}?key=${key}`, { method: 'POST', headers, body: JSON.stringify({ contents: [{ parts: [{ text: prompt }] }], generationConfig: { temperature: 0.2, responseMimeType: "application/json" } }) });
                    const data = await res.json();
                    responseText = data?.candidates?.[0]?.content?.parts?.[0]?.text || '';
                } else {
                    const model = apiModelSelect.value || (await loadLocalModels(false))[0]?.id;
                    const res = await fetch(apiUrlInput.value, { method: 'POST', headers, body: JSON.stringify({ model, messages: [{ role: "system", content: "Genera JSON únicamente. No uses formato Markdown." }, { role: "user", content: prompt }], temperature: 0.2 }) });
                    const data = await res.json();
                    responseText = data?.choices?.[0]?.message?.content || '';
                }

                if (!responseText) throw new Error("La IA no retornó información.");
                const gen = parseGeneratedJson(responseText);

                // Sanitizar antes de asignar para eliminar caracteres de control (e.g. \u0002)
                if (!document.getElementById('objetivo').value.trim()) document.getElementById('objetivo').value = sanitizeXml(gen.objetivo || gen.objetivos || '');
                document.getElementById('estrategia').value = sanitizeXml(gen.estrategia || gen.estrategia_educativa || gen.estrategias || '');
                document.getElementById('momentos').value = sanitizeXml(toReadableText(gen.momentos));
                document.getElementById('evaluacion').value = sanitizeXml(toReadableText(gen.evaluacion));

                updatePreview();
                aiFieldsContainer.classList.remove('hidden');
                document.getElementById('promptViewer').textContent = prompt;
                lastPromptSent = prompt;
                document.getElementById('refinePanel').classList.remove('hidden');
                badgeEstado.textContent = "¡Completado!"; badgeEstado.classList.replace('bg-amber-100', 'bg-green-100'); badgeEstado.classList.replace('text-amber-800', 'text-green-800');
                contenedorDescarga.classList.remove('hidden');
                showMessage("Planeador generado con éxito.", false);
            } catch (error) {
                showMessage(`Error: ${error.message}`, true);
            } finally {
                btnGenerarLM.disabled = false; btnGenerarLM.classList.remove('opacity-50');
                loadingIndicator.classList.add('hidden'); loadingIndicator.classList.remove('flex');
            }
        });

        // ── Copiar prompt ──
        document.getElementById('btnTogglePromptViewer').addEventListener('click', () => {
            const viewer = document.getElementById('promptViewer');
            const button = document.getElementById('btnTogglePromptViewer');
            const icon = document.getElementById('promptToggleIcon');
            const isHidden = viewer.classList.toggle('hidden');
            button.setAttribute('aria-expanded', String(!isHidden));
            icon.textContent = isHidden ? '▶' : '▼';
        });

        document.getElementById('btnCopyPrompt').addEventListener('click', () => {
            if (!lastPromptSent) return;
            navigator.clipboard.writeText(lastPromptSent)
                .then(() => { const b = document.getElementById('btnCopyPrompt'); b.textContent = '✅ Copiado'; setTimeout(() => b.textContent = '📋 Copiar', 2000); })
                .catch(() => {});
        });

        // ── Refinamiento con IA ──
        document.getElementById('btnRefinar').addEventListener('click', async () => {
            const instruccion = document.getElementById('refineInstruction').value.trim();
            if (!instruccion) { document.getElementById('refineInstruction').focus(); return; }

            const btn = document.getElementById('btnRefinar');
            const spinner = document.getElementById('refineSpinner');
            const status = document.getElementById('refineStatus');
            btn.disabled = true; spinner.classList.remove('hidden');
            status.textContent = 'La IA está actualizando...'; status.classList.remove('hidden');

            try {
                const actual = {
                    objetivo:   document.getElementById('objetivo').value,
                    estrategia: document.getElementById('estrategia').value,
                    momentos:   document.getElementById('momentos').value,
                    evaluacion: document.getElementById('evaluacion').value
                };

                const vars = getPromptVars();
                const ctx = document.getElementById('customPromptContext').value || DEFAULT_PROMPT_CONTEXT;
                const inst = document.getElementById('customPromptInstructions').value || DEFAULT_PROMPT_INSTRUCTIONS;
                const interpolate = (txt) => txt.replace(/\{(\w+)\}/g, (_, k) => vars[k] !== undefined ? vars[k] : `{${k}}`);

                const refinePrompt = `${interpolate(ctx)}\n\n${buildPlannerDataSection(vars)}\n\n${interpolate(inst)}\n\n` +
                    `Tienes el siguiente planeador de clase ya generado:\n\n` +
                    `OBJETIVO ACTUAL:\n${actual.objetivo}\n\n` +
                    `ESTRATEGIA ACTUAL:\n${actual.estrategia}\n\n` +
                    `MOMENTOS DE CLASE ACTUALES:\n${actual.momentos}\n\n` +
                    `EVALUACIÓN ACTUAL:\n${actual.evaluacion}\n\n` +
                    `INSTRUCCIÓN DEL DOCENTE:\n${instruccion}\n\n` +
                    `REGLA ABSOLUTA: Aplica SOLO el cambio pedido por el docente, respetando el prompt de configuración y los datos del planeador. Mantén el resto EXACTAMENTE igual. ` +
                    `Devuelve Única y exclusivamente un JSON válido con las claves: ` +
                    `"objetivo", "estrategia", "momentos", "evaluacion". Sin comentarios, sin markdown.`;

                const headers = { 'Content-Type': 'application/json' };
                let responseText = '';

                if (apiTypeSelect.value === 'gemini') {
                    const key = apiKeyInput.value.trim();
                    if (!key) throw new Error('Falta API Key de Gemini');
                    const res = await fetch(`${apiUrlInput.value}?key=${key}`, { method: 'POST', headers,
                        body: JSON.stringify({ contents: [{ parts: [{ text: refinePrompt }] }], generationConfig: { temperature: 0.1, responseMimeType: 'application/json' } }) });
                    const data = await res.json();
                    responseText = data?.candidates?.[0]?.content?.parts?.[0]?.text || '';
                } else {
                    const model = apiModelSelect.value || (await loadLocalModels(false))[0]?.id;
                    const res = await fetch(apiUrlInput.value, { method: 'POST', headers,
                        body: JSON.stringify({ model, messages: [
                            { role: 'system', content: 'Eres un asistente que modifica planeadores de clase. Devuelve SOLO JSON válido.' },
                            { role: 'user', content: refinePrompt }
                        ], temperature: 0.1 }) });
                    const data = await res.json();
                    responseText = data?.choices?.[0]?.message?.content || '';
                }

                if (!responseText) throw new Error('La IA no retornó respuesta.');
                const gen = parseGeneratedJson(responseText);

                if (gen.objetivo)   document.getElementById('objetivo').value   = sanitizeXml(gen.objetivo);
                if (gen.estrategia) document.getElementById('estrategia').value = sanitizeXml(gen.estrategia);
                if (gen.momentos)   document.getElementById('momentos').value   = sanitizeXml(toReadableText(gen.momentos));
                if (gen.evaluacion) document.getElementById('evaluacion').value = sanitizeXml(toReadableText(gen.evaluacion));

                updatePreview();
                document.getElementById('promptViewer').textContent = refinePrompt;
                lastPromptSent = refinePrompt;
                document.getElementById('refineInstruction').value = '';
                status.textContent = '✅ ¡Actualizado con éxito!';
                setTimeout(() => status.classList.add('hidden'), 3000);

            } catch (err) {
                status.textContent = `❌ Error: ${err.message}`;
            } finally {
                btn.disabled = false; spinner.classList.add('hidden');
            }
        });

        async function getTemplateContent() {
            if (templateMode === 'path') {
                const path = templatePathInput.value.trim();
                if (!path) throw new Error("Configura la ruta de la plantilla en Configurar IA > Archivos.");
                const isLocalPath = /^(file:\/\/|[a-zA-Z]:[\\/]|\\\\)/.test(path);
                const url = isLocalPath ? `load_template.php?path=${encodeURIComponent(path)}` : path;
                const res = await fetch(url, { cache: 'no-store' });
                if (!res.ok) {
                    const errorInfo = await res.json().catch(() => null);
                    throw new Error(errorInfo?.message || "Ruta de plantilla no encontrada o no accesible.");
                }
                return await res.arrayBuffer();
            } else {
                const file = templateDocxInput.files[0];
                if (!file) throw new Error("Selecciona el archivo plantilla .docx.");
                return await file.arrayBuffer();
            }
        }

        function blobToBase64(blob) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(String(reader.result).split(',')[1] || '');
                reader.onerror = () => reject(reader.error || new Error('No se pudo leer el documento generado.'));
                reader.readAsDataURL(blob);
            });
        }

        async function saveDocxToConfiguredPath(blob, filename) {
            const directory = outputPathInput.value.trim() || DEFAULT_OUTPUT_PATH;
            const contentBase64 = await blobToBase64(blob);
            let response;
            try {
                response = await fetch('save_docx.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ directory, filename, contentBase64 })
                });
            } catch (error) {
                throw new Error(`No se pudo contactar el guardador local save_docx.php. Verifica que XAMPP esté sirviendo PlanMind. ${error.message}`);
            }
            const result = await response.json().catch(() => null);
            if (!response.ok || !result?.ok) {
                throw new Error(result?.message || `No se pudo guardar el documento en la ruta configurada (${directory}).`);
            }
            return result.path;
        }

        async function downloadPlannerWord() {
            if (typeof window.PizZip === 'undefined' || typeof window.docxtemplater === 'undefined' || typeof window.saveAs === 'undefined') {
                showMessage("Las librerías locales (pizzip.min.js, docxtemplater.js o FileSaver.min.js) no cargaron. Asegúrate de tenerlas en la misma carpeta.", true);
                return;
            }
            try {
                showMessage(outputMode === 'path' ? "Generando y guardando el planeador..." : "Generando el archivo Word...", false);
                const content = await getTemplateContent();
                const zip = new window.PizZip(content);
                const doc = new window.docxtemplater(zip, { paragraphLoop: true, linebreaks: true, nullGetter: () => '' });
                
                // Creación robusta de datos con alias para plantillas variadas
                let data = {};
                allFields.forEach(id => { data[id] = getPreviewValue(id); });
                data.periodo = getPeriodValue();
                data.fecha_inicio = formatDateForDoc(fechaInicioInput.value);
                data.fecha_fin = formatDateForDoc(fechaFinInput.value);
                data.materia_id = document.getElementById('materia').value;
                data.materia_nombre = data.materia;
                data.nombre_materia = data.materia;
                
                // Agregamos múltiples alias para máxima compatibilidad con las etiquetas de Word
                data.tema = '';
                data.objetivos = data.objetivo;
                data.estrategias = data.estrategia;
                data.estrategias_educativas = data.estrategia;
                data.estrategia_educativa = data.estrategia;
                data.estrategia_metodologica = data.estrategia;
                data.ejes = data.ejes_tematicos;
                data.eje_tematico = data.ejes_tematicos;
                data.reflexion_pedagogica = data.reflexion;
                data.observaciones_adicionales = data.observaciones;
                data.observaciones = data.momentos || data.observaciones;
                data.momentos_clase = data.momentos;

                // ▶ Limpiar TODOS los valores antes de enviarlos a Docxtemplater
                // Elimina caracteres de control XML inválidos (\u0000-\u001F salvo \t\n)
                data = sanitizeObject(data);

                doc.render(data);
                const out = doc.getZip().generate({ type: "blob", mimeType: "application/vnd.openxmlformats-officedocument.wordprocessingml.document" });

                const safeName = (val) => (val || 'Vacio').replace(/[^a-z0-9]/gi, '_');
                const filename = `planeador_vallesol_${safeName(data.mes)}_${safeName(data.grado)}_${safeName(data.materia)}.docx`;
                if (outputMode === 'path') {
                    showMessage(`Guardando en ruta: ${outputPathInput.value.trim() || DEFAULT_OUTPUT_PATH}`, false);
                    const savedPath = await saveDocxToConfiguredPath(out, filename);
                    showMessage(`¡Planeador guardado en: ${savedPath}!`);
                } else {
                    saveAs(out, filename);
                    showMessage("¡Planeador descargado!");
                }
            } catch (error) {
                console.error("ERROR DOCX:", error);
                // Mostrar detalles por campo si es un MultiError de Docxtemplater
                if (error.properties && Array.isArray(error.properties.errors) && error.properties.errors.length > 0) {
                    const detalles = error.properties.errors.map((err, i) => {
                        const p = err.properties || {};
                        return `${i+1}) Campo: ${p.xtag || p.tag || 'desconocido'} → ${p.explanation || err.message || 'Error sin descripción'}`;
                    }).join('\n');
                    showMessage(`Error en la plantilla Word:\n${detalles}`, true);
                } else if (error.properties && error.properties.explanation) {
                    const p = error.properties;
                    showMessage(`Error en campo "${p.xtag || '?'}": ${p.explanation}`, true);
                } else {
                    showMessage(error.message || "Error al procesar el documento Word. Verifica tu archivo plantilla.", true);
                }
            }
        }

        document.getElementById('btnDescargarWord')?.addEventListener('click', downloadPlannerWord);
        document.getElementById('btnDescargarWordSidebar')?.addEventListener('click', downloadPlannerWord);

        allFields.forEach(id => {
            const input = document.getElementById(id);
            if (input) { input.addEventListener('input', updatePreview); input.addEventListener('change', updatePreview); }
        });
        document.querySelectorAll('[data-clear-field]').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = document.getElementById(btn.dataset.clearField);
                if (!input) return;
                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                if (input.id === 'templatePath') localStorage.removeItem('planmindTemplatePath');
                if (input.id === 'outputPath') {
                    localStorage.removeItem('planmindOutputPath');
                    outputPathInput.value = DEFAULT_OUTPUT_PATH;
                }
                if (input.id === 'docente') localStorage.removeItem('planmindDocente');
            });
        });
        document.getElementById('btnClearAll').addEventListener('click', () => {
            showConfirm('¿Limpiar todos los campos del planeador actual?', () => {
                ['tiempo', 'dba', 'ejes_tematicos', 'objetivo', 'observaciones', 'reflexion', 'estrategia', 'momentos', 'evaluacion'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.value = '';
                });
                fechaInicioInput.value = '';
                fechaFinInput.value = '';
                aiFieldsContainer.classList.add('hidden');
                document.getElementById('promptViewer').classList.add('hidden');
                document.getElementById('btnTogglePromptViewer').setAttribute('aria-expanded', 'false');
                document.getElementById('promptToggleIcon').textContent = '▶';
                document.getElementById('promptViewer').textContent = 'El prompt aparecerá aquí tras generar.';
                document.getElementById('refinePanel').classList.add('hidden');
                contenedorDescarga.classList.add('hidden');
                lastPromptSent = '';
                badgeEstado.textContent = 'Falta la IA';
                badgeEstado.classList.remove('bg-green-100', 'text-green-800');
                badgeEstado.classList.add('bg-amber-100', 'text-amber-800');
                updatePreview();
                showMessage('Campos limpiados.', false);
            });
        });
        ['customPromptContext', 'customPromptInstructions'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.addEventListener('input', updatePromptPreviewIfVisible);
        });

        // Limpiar caracteres de control al perder el foco (texto pegado desde Gemini, PDFs, Word, etc.)
        ['dba', 'objetivo', 'objetivos', 'ejes_tematicos', 'observaciones', 'reflexion', 'estrategia', 'momentos', 'evaluacion'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('blur', () => {
                const clean = sanitizeXml(el.value);
                if (clean !== el.value) { el.value = clean; updatePreview(); }
            });
        });
        apiTypeSelect.addEventListener('change', (e) => setApiType(e.target.value));
        btnRefreshModels.addEventListener('click', () => loadLocalModels(true));
        apiModelSelect.addEventListener('change', () => localStorage.setItem('planmindApiModel', apiModelSelect.value));
        document.getElementById('docente').addEventListener('input', () => {
            localStorage.setItem('planmindDocente', document.getElementById('docente').value);
        });
        mesInput.addEventListener('change', syncCalendarFromMonth);
        fechaInicioInput.addEventListener('change', () => syncCalendarFromDate(fechaInicioInput.value));
        fechaFinInput.addEventListener('change', () => syncCalendarFromDate(fechaFinInput.value));
        templatePathInput.addEventListener('input', () => localStorage.setItem('planmindTemplatePath', templatePathInput.value));
        outputPathInput.addEventListener('input', () => localStorage.setItem('planmindOutputPath', outputPathInput.value));
        templateModeButtons.forEach(btn => btn.addEventListener('click', () => {
            templateMode = btn.dataset.templateMode;
            localStorage.setItem('planmindTemplateMode', templateMode);
            setTemplateMode(templateMode);
        }));
        outputModeButtons.forEach(btn => btn.addEventListener('click', () => {
            outputMode = btn.dataset.outputMode;
            localStorage.setItem('planmindOutputMode', outputMode);
            setOutputMode(outputMode);
        }));
        
        function setTemplateMode(mode) {
            templateDocxInput.classList.toggle('hidden', mode !== 'upload');
            templatePathContainer.classList.toggle('hidden', mode !== 'path');
            templateModeButtons.forEach(b => {
                const active = b.dataset.templateMode === mode;
                b.classList.toggle('bg-blue-600', active); b.classList.toggle('text-white', active);
                b.classList.toggle('bg-white', !active); b.classList.toggle('text-blue-700', !active);
            });
        }

        function setOutputMode(mode) {
            outputPathContainer.classList.toggle('hidden', mode !== 'path');
            outputModeButtons.forEach(b => {
                const active = b.dataset.outputMode === mode;
                b.classList.toggle('bg-green-600', active); b.classList.toggle('text-white', active);
                b.classList.toggle('border-green-600', active);
                b.classList.toggle('bg-white', !active); b.classList.toggle('text-green-700', !active);
                b.classList.toggle('border-green-200', !active);
            });
        }

        populateMonths();
        populatePeriodOptions();
        setDateRangeForMonth(new Date().getMonth(), new Date().getFullYear());

        // Auto-seleccionar el periodo activo desde la base de datos
        if (PLANMIND_ACTIVE_PERIODO && PLANMIND_ACTIVE_PERIODO.nombre_periodo) {
            ensureSelectOption(periodoInput, PLANMIND_ACTIVE_PERIODO.nombre_periodo);
            periodoInput.value = PLANMIND_ACTIVE_PERIODO.nombre_periodo;
        }
        document.getElementById('docente').value = localStorage.getItem('planmindDocente') || "Andrés Paz";
        templatePathInput.value = localStorage.getItem('planmindTemplatePath') || '';
        outputPathInput.value = localStorage.getItem('planmindOutputPath') || DEFAULT_OUTPUT_PATH;
        
        const showHtmlTagsCheckbox = document.getElementById('showHtmlTags');
        if (showHtmlTagsCheckbox) {
            // Por defecto activo si no hay configuración
            showHtmlTagsCheckbox.checked = localStorage.getItem('planmindShowHtmlTags') !== 'false';
            showHtmlTagsCheckbox.addEventListener('change', () => {
                localStorage.setItem('planmindShowHtmlTags', showHtmlTagsCheckbox.checked);
            });
        }
        
        loadPromptFields();
        setTemplateMode(templateMode);
        setOutputMode(outputMode);
        setApiType(apiTypeSelect.value);
        populateMateriaSelect();
        if (PLANMIND_INITIAL_PLAN) {
            applyPlanData(PLANMIND_INITIAL_PLAN);
            setDatabaseState('loaded', PLANMIND_INITIAL_STATUS.message);
        } else if (PLANMIND_INITIAL_STATUS?.requestedId) {
            setDatabaseState(PLANMIND_INITIAL_STATUS.message && /conexión|SQL|Error/i.test(PLANMIND_INITIAL_STATUS.message) ? 'error' : 'missing', PLANMIND_INITIAL_STATUS.message);
        } else {
            setDatabaseState('fresh');
        }

        // Pre-seleccionar grado y materia desde parámetro ?asignacion=
        if (PLANMIND_ASIGNACION_DATA && !PLANMIND_INITIAL_PLAN) {
            const asigData = PLANMIND_ASIGNACION_DATA;
            // Seleccionar grado
            if (asigData.grado_texto) {
                ensureSelectOption(document.getElementById('grado'), asigData.grado_texto);
                setFieldValue('grado', asigData.grado_texto);
            }
            // Seleccionar materia
            if (asigData.id_materia) {
                const materiaVal = ensureMateriaOption(asigData.id_materia, asigData.nombre_materia);
                setFieldValue('materia', materiaVal || asigData.id_materia);
            }
            updatePreview();
        }
        updatePreview();
    </script>
</body>
</html>
