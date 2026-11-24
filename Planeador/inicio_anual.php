<?php
require_once __DIR__ . '/../comun/config.php';
guagua_exigir_rol(['admin','directivo','docente']);
require_once __DIR__ . '/lib/InicioAnual.php';
require_once __DIR__ . '/lib/InicioAnualArchivos.php';
$db=guagua_db_request();
$service=new InicioAnual($db);
$service->ensureSchema();
$envPath=GuaguaDatabase::environment('GUAGUA_ATTENDANCE_FILE');
$files=new InicioAnualArchivos(__DIR__.'/../apps/asistencia/config_sincronizacion.json',__DIR__.'/../ia/config_rutas.json',is_string($envPath)?trim($envPath):'');
function inicio_anual_json(array $data,int $status=200): never {
    http_response_code($status);header('Content-Type: application/json; charset=utf-8');header('Cache-Control: no-store');
    echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);exit;
}
if (isset($_GET['action'])) {
    try {
        $action=(string)$_GET['action'];
        $groups=guagua_sql($db,'SELECT id,nombre,ruta_excel FROM config_grupos ORDER BY id')->fetch_all(MYSQLI_ASSOC);
        $currentFiles=$files->current($groups);
        if (($_SERVER['REQUEST_METHOD'] ?? '')==='GET') {
            if ($action==='bootstrap') inicio_anual_json(['ok'=>true,'catalogo'=>$service->catalog($_SESSION),'archivos'=>$currentFiles]);
            $year=filter_var($_GET['ano'] ?? null,FILTER_VALIDATE_INT);
            if (!$year) throw new InvalidArgumentException('Selecciona el año que deseas preparar.');
            if ($action==='load') inicio_anual_json(['ok'=>true,'borrador'=>$service->load($year,$_SESSION) ?? ['plan'=>$service->blank($year,$_SESSION,$currentFiles),'revision'=>0,'paso'=>0,'estado'=>'borrador','resultado'=>null]]);
            if ($action==='copy') inicio_anual_json(['ok'=>true,'plan'=>$service->copy($year,(int)($_GET['base'] ?? 0),$_SESSION,$currentFiles)]);
            if ($action==='files') inicio_anual_json(['ok'=>true,'archivos'=>$currentFiles]);
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST') inicio_anual_json(['ok'=>false,'message'=>'Método no permitido.'],405);
        guagua_exigir_csrf();
        $input=json_decode(file_get_contents('php://input'),true,512,JSON_THROW_ON_ERROR);
        if (!is_array($input)) throw new InvalidArgumentException('Los datos del asistente no son válidos.');
        if ($action==='save') {
            $through=max(0,min(7,(int)($input['validar_hasta'] ?? $input['paso'] ?? 0)));
            $errors=!empty($input['validar']) ? $service->validate($input['plan'] ?? [],$_SESSION,$through) : [];
            $draft=$service->save($input['plan'] ?? [],(int)($input['revision'] ?? 0),$errors?$through:(int)($input['paso'] ?? 0),$_SESSION);
            inicio_anual_json(['ok'=>true,'borrador'=>$draft,'errores'=>$errors]);
        }
        if ($action==='install') {
            if (($input['confirmacion'] ?? '')!==(string)($input['ano'] ?? '')) throw new InvalidArgumentException('Escribe el año para confirmar la instalación.');
            if (!is_bool($input['activar'] ?? null)) throw new InvalidArgumentException('Selecciona si deseas activar el año.');
            $result=$service->install((int)($input['ano'] ?? 0),(int)($input['revision'] ?? 0),$input['activar'],$_SESSION,fn($options,$year)=>$files->apply($options,$year));
            guagua_refrescar_contexto_sesion($db);
            inicio_anual_json(['ok'=>true,'resultado'=>$result]);
        }
        inicio_anual_json(['ok'=>false,'message'=>'Acción no encontrada.'],404);
    } catch (InvalidArgumentException|JsonException $e) { inicio_anual_json(['ok'=>false,'message'=>$e->getMessage()],422); }
    catch (Throwable $e) { error_log('[Inicio anual] '.$e->getMessage());inicio_anual_json(['ok'=>false,'message'=>'No fue posible completar la operación. El borrador queda disponible para volver a intentarlo.'],500); }
}
require_once __DIR__ . '/../comun/cabecera_modulo.php';
$embedded=($_GET['guagua_embedded'] ?? '')==='1';
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars(guagua_csrf_token(),ENT_QUOTES,'UTF-8') ?>">
<title>Inicio de año escolar · Guagua</title>
<?php if (!$embedded) guagua_menu_estilos(); ?>
<link rel="stylesheet" href="inicio_anual.css?v=<?= filemtime(__DIR__.'/inicio_anual.css') ?>"></head>
<body class="inicio-anual"><?php if (!$embedded) guagua_menu_renderizar(); ?>
<main id="annual-app">
    <header class="annual-header"><a class="annual-brand" href="../comun/configuracion.php">G<span>GUAGUA / GESTIÓN ACADÉMICA</span></a><div class="annual-context" id="current-context">Leyendo la configuración…</div></header>
    <section class="annual-intro"><div><p class="eyebrow">UN NUEVO CICLO, PASO A PASO</p><h1>Prepara tu año escolar.</h1><p>Organiza la institución, los períodos y los cursos. Guarda tu avance y activa el año cuando esté listo.</p></div><div class="annual-year-picker"><label for="target-year">Año que vas a preparar</label><div><input id="target-year" type="number" min="2000" max="2100" placeholder="Año" aria-label="Año que vas a preparar"><button id="open-year" class="button primary">Abrir asistente <span aria-hidden="true">↗</span></button></div><label class="sr-only" for="saved-drafts">Continuar un borrador</label><select id="saved-drafts"><option value="">Continuar un año guardado…</option></select></div></section>
    <div id="annual-message" role="status" aria-live="polite"></div>
    <section id="annual-empty" class="annual-empty"><span class="empty-symbol" aria-hidden="true">✦</span><h2>Todo empieza con un año.</h2><p>Elige el año de trabajo para comenzar o retomar un borrador. La instalación se realizará al finalizar la revisión.</p><div class="empty-chips"><span>01 · Configura</span><span>02 · Revisa</span><span>03 · Instala</span></div></section>
    <div id="annual-workspace" class="annual-workspace" hidden>
        <aside class="annual-sidebar"><div class="progress-heading"><span>Tu recorrido</span><strong id="step-count"></strong></div><div class="progress-track"><i id="step-progress"></i></div><nav id="annual-steps" aria-label="Pasos del asistente"></nav><p class="sidebar-note">Los pasos se guardan como borrador. El año en uso continúa activo hasta que confirmes la activación final.</p></aside>
        <div class="annual-main"><section class="annual-card"><header class="step-header"><div><p id="step-eyebrow" class="eyebrow"></p><h2 id="step-title"></h2><p id="step-description"></p></div><span id="draft-badge" class="draft-badge">Borrador</span></header><form id="annual-form" novalidate><div id="step-content"></div></form></section><footer class="annual-actions"><button class="button quiet" id="previous-step">← Anterior</button><span id="save-status" aria-live="polite"></span><div><button class="button secondary" id="save-draft">Guardar borrador</button><button class="button primary" id="next-step">Guardar y continuar →</button></div></footer></div>
    </div>
</main><script src="inicio_anual.js?v=<?= filemtime(__DIR__.'/inicio_anual.js') ?>"></script></body></html>
