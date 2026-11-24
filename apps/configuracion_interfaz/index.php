<?php
/**
 * Configuración visual global de Guagua.
 * La configuración se guarda en comun/css para que el tema se cargue desde el menú principal.
 */
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();

$allowedRoles = array('admin', 'docente');
$role = isset($_SESSION['rol']) ? strtolower((string) $_SESSION['rol']) : '';
if (!in_array($role, $allowedRoles, true)) {
    http_response_code(403);
    exit('No tienes permisos para configurar la apariencia de la plataforma.');
}

$rootPath = dirname(__DIR__, 2);
$configPath = $rootPath . '/comun/css/interface_custom_config.json';
$cssPath = $rootPath . '/comun/css/interface_custom.css';
$fonts = array(
    'Inter' => 'Inter, "Segoe UI", Arial, sans-serif',
    'Nunito Sans' => '"Nunito Sans", "Segoe UI", Arial, sans-serif',
    'Poppins' => 'Poppins, "Segoe UI", Arial, sans-serif',
    'Roboto' => 'Roboto, Arial, sans-serif',
    'Open Sans' => '"Open Sans", Arial, sans-serif'
);
$defaults = array(
    'primary' => '#2563eb',
    'secondary' => '#0f766e',
    'background' => '#f4f7fb',
    'homeBackground' => '#31708f',
    'surface' => '#ffffff',
    'text' => '#1e293b',
    'font' => 'Inter',
    'fontSize' => 15,
    'radius' => 12,
    'density' => 'comfortable',
    'shadow' => true,
    'animations' => true
);

function interface_color($value, $fallback) {
    return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : $fallback;
}

function interface_css($settings, $fonts) {
    $density = $settings['density'] === 'compact' ? '7px 10px' : '11px 16px';
    $shadow = $settings['shadow'] ? '0 8px 24px rgba(15, 23, 42, .08)' : 'none';
    $transition = $settings['animations'] ? 'all .18s ease' : 'none';
    $fontFamily = $fonts[$settings['font']];

    return "/* Tema generado desde Configuración de interfaz. No editar manualmente. */\n"
        . ":root {\n"
        . "  --guagua-primary: {$settings['primary']};\n"
        . "  --guagua-secondary: {$settings['secondary']};\n"
        . "  --guagua-bg: {$settings['background']};\n"
        . "  --guagua-home-bg: {$settings['homeBackground']};\n"
        . "  --guagua-surface: {$settings['surface']};\n"
        . "  --guagua-text: {$settings['text']};\n"
        . "  --guagua-radius: {$settings['radius']}px;\n"
        . "  --guagua-shadow: {$shadow};\n"
        . "}\n\n"
        . "body, button, input, select, textarea { font-family: {$fontFamily} !important; }\n"
        . "body { background-color: var(--guagua-bg) !important; color: var(--guagua-text); font-size: {$settings['fontSize']}px; }\n"
        . "body.home-page { background-color: var(--guagua-home-bg) !important; }\n"
        . "a { color: var(--guagua-primary); }\n"
        . ".btn-primary, .bg-primary, .label-primary { background-color: var(--guagua-primary) !important; border-color: var(--guagua-primary) !important; }\n"
        . ".btn-success, .label-success { background-color: var(--guagua-secondary) !important; border-color: var(--guagua-secondary) !important; }\n"
        . ".panel, .well, .thumbnail, .card, .modal-content, .dropdown-menu { background-color: var(--guagua-surface); border-radius: var(--guagua-radius) !important; box-shadow: var(--guagua-shadow); }\n"
        . ".btn, .form-control, select, input, textarea { border-radius: calc(var(--guagua-radius) * .65) !important; transition: {$transition}; }\n"
        . ".panel-body, .well, .card-body { padding: {$density}; }\n"
        . ".navbar.navbar-default { background-color: var(--guagua-surface) !important; }\n"
        . ".navbar.navbar-default .navbar-nav > li > a:hover, .navbar.navbar-default .navbar-nav > .active > a { color: var(--guagua-primary) !important; }\n"
        . ".nav-tabs > li.active > a, .nav-tabs > li.active > a:hover { color: var(--guagua-primary) !important; border-bottom-color: var(--guagua-primary) !important; }\n"
        . "\n/* Módulo Cursos */\n"
        . "#jumbotron.mis-cursos-hero { background-color: var(--guagua-primary) !important; }\n"
        . "#jumbotron.mis-cursos-hero::before { background: linear-gradient(110deg, var(--guagua-primary), var(--guagua-secondary)) !important; opacity: .88; }\n"
        . ".mis-cursos-hero__eyebrow { color: #fff !important; }\n"
        . ".mis-cursos-hero__action .btn { color: var(--guagua-primary) !important; background: var(--guagua-surface) !important; border-radius: var(--guagua-radius) !important; }\n"
        . ".mis-cursos-hero__action .btn:hover, .mis-cursos-hero__action .btn:focus, .curso-acciones .btn:hover, .curso-acciones .btn:focus { color: #fff !important; background: var(--guagua-secondary) !important; border-color: var(--guagua-secondary) !important; }\n"
        . ".mis-cursos-library { border-color: var(--guagua-primary) !important; }\n"
        . ".mis-cursos-library__icon { color: var(--guagua-primary) !important; background: var(--guagua-bg) !important; border-radius: var(--guagua-radius) !important; }\n"
        . ".mis-cursos-library__heading h2, .curso-card h3, .curso-card__link:hover, .curso-card__link:focus { color: var(--guagua-text) !important; }\n"
        . ".mis-cursos-summary, .curso-year, .curso-card { background: var(--guagua-surface) !important; border-color: color-mix(in srgb, var(--guagua-primary) 24%, #dfe9e3) !important; box-shadow: var(--guagua-shadow) !important; }\n"
        . ".mis-cursos-summary strong, .curso-year__toggle::before, .curso-category__toggle::before, .curso-card__open { color: var(--guagua-primary) !important; }\n"
        . ".curso-year__toggle { color: var(--guagua-text) !important; background: linear-gradient(90deg, var(--guagua-bg), var(--guagua-surface)) !important; }\n"
        . ".curso-category__toggle { color: var(--guagua-secondary) !important; }\n"
        . ".curso-card__icon { background: linear-gradient(145deg, var(--guagua-bg), var(--guagua-surface)) !important; border-color: color-mix(in srgb, var(--guagua-primary) 18%, #e1efe6) !important; border-radius: var(--guagua-radius) !important; }\n"
        . ".curso-card:hover { border-color: var(--guagua-primary) !important; }\n"
        . "@media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; animation: none !important; } }\n"
        . ($settings['animations'] ? '' : "*, *::before, *::after { transition: none !important; animation: none !important; }\n");
}

function interface_normalize($input, $defaults, $fonts) {
    $result = $defaults;
    foreach (array('primary', 'secondary', 'background', 'homeBackground', 'surface', 'text') as $key) {
        $result[$key] = interface_color(isset($input[$key]) ? $input[$key] : null, $defaults[$key]);
    }
    $result['font'] = isset($input['font']) && isset($fonts[$input['font']]) ? $input['font'] : $defaults['font'];
    $result['fontSize'] = max(13, min(18, (int) (isset($input['fontSize']) ? $input['fontSize'] : $defaults['fontSize'])));
    $result['radius'] = max(0, min(24, (int) (isset($input['radius']) ? $input['radius'] : $defaults['radius'])));
    $result['density'] = isset($input['density']) && $input['density'] === 'compact' ? 'compact' : 'comfortable';
    $result['shadow'] = !empty($input['shadow']);
    $result['animations'] = !empty($input['animations']);
    return $result;
}

if (empty($_SESSION['interface_config_csrf'])) {
    $_SESSION['interface_config_csrf'] = bin2hex(random_bytes(32));
}

if ((isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET') === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $payload = json_decode(isset($_POST['settings']) ? $_POST['settings'] : '', true);
    $csrf = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    if (!hash_equals($_SESSION['interface_config_csrf'], $csrf) || !is_array($payload)) {
        http_response_code(422);
        echo json_encode(array('success' => false, 'message' => 'La solicitud no es válida. Recarga la página e inténtalo nuevamente.'));
        exit;
    }

    $settings = interface_normalize($payload, $defaults, $fonts);
    $configSaved = file_put_contents($configPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    $cssSaved = file_put_contents($cssPath, interface_css($settings, $fonts), LOCK_EX);
    if ($configSaved === false || $cssSaved === false) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'message' => 'No fue posible guardar la configuración. Verifica los permisos de la carpeta comun/css.'));
        exit;
    }
    echo json_encode(array('success' => true, 'message' => 'La apariencia se guardó y ya está activa en la plataforma.'));
    exit;
}

$stored = array();
if (is_file($configPath)) {
    $decoded = json_decode((string) file_get_contents($configPath), true);
    if (is_array($decoded)) {
        $stored = $decoded;
    }
}
$settings = interface_normalize($stored, $defaults, $fonts);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apariencia de la plataforma | Guagua</title>
    <link rel="stylesheet" href="/guagua/comun/vendor/fontawesome/css/all.min.css">
    <style>
        :root { --ink:#172033; --muted:#64748b; --line:#e2e8f0; --brand:#2563eb; --bg:#f3f6fb; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--ink); background:var(--bg); font-family:Inter,Segoe UI,Arial,sans-serif; }
        .topbar { padding:22px clamp(20px,4vw,56px); color:#fff; background:linear-gradient(115deg,#172554,#2563eb); display:flex; align-items:center; justify-content:space-between; gap:18px; }
        .topbar h1 { margin:0; font-size:clamp(21px,3vw,29px); } .topbar p { margin:5px 0 0; color:#dbeafe; }
        .back { color:#fff; text-decoration:none; font-size:14px; font-weight:600; white-space:nowrap; }
        .layout { max-width:1320px; margin:0 auto; padding:28px clamp(18px,4vw,56px) 48px; display:grid; grid-template-columns:minmax(300px, .92fr) minmax(380px,1.35fr); gap:26px; }
        .panel { background:#fff; border:1px solid var(--line); border-radius:18px; box-shadow:0 12px 32px rgba(15,23,42,.06); }
        .controls { padding:24px; } .section + .section { border-top:1px solid var(--line); margin-top:23px; padding-top:23px; }
        .section h2 { margin:0 0 5px; font-size:16px; } .section > p { margin:0 0 16px; color:var(--muted); font-size:13px; }
        .fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:13px; }
        label { display:block; color:#475569; font-size:12px; font-weight:700; } label span { display:block; margin-bottom:6px; }
        input[type=color] { width:100%; height:39px; padding:3px; cursor:pointer; border:1px solid var(--line); border-radius:9px; background:#fff; }
        select { width:100%; border:1px solid var(--line); border-radius:9px; padding:10px; color:var(--ink); background:#fff; }
        .range-row { display:grid; grid-template-columns:1fr 42px; align-items:center; gap:10px; margin-top:12px; } input[type=range] { width:100%; accent-color:var(--brand); } output { text-align:center; color:var(--brand); font-size:12px; font-weight:700; }
        .toggle { display:flex; justify-content:space-between; align-items:center; border:1px solid var(--line); border-radius:10px; padding:12px; font-size:13px; margin-top:10px; } .toggle small { display:block; margin-top:3px; color:var(--muted); }
        input[type=checkbox] { width:18px; height:18px; accent-color:var(--brand); }
        .actions { display:flex; gap:10px; margin-top:24px; } button { flex:1; border:0; border-radius:10px; padding:12px 14px; cursor:pointer; font-weight:700; font-size:14px; } .save { background:var(--brand); color:#fff; } .reset { background:#eaf0ff; color:#1e40af; }
        .preview-wrap { padding:24px; min-width:0; } .preview-label { color:var(--muted); font-size:13px; font-weight:700; margin-bottom:14px; }
        .preview { --p:#2563eb; --s:#0f766e; --b:#f4f7fb; --card:#fff; --t:#1e293b; --r:12px; overflow:hidden; min-height:510px; color:var(--t); border:1px solid #dce4f0; border-radius:18px; background:var(--b); font-family:Inter,Arial,sans-serif; }
        .pnav { color:#fff; background:var(--p); padding:16px 22px; display:flex; align-items:center; justify-content:space-between; } .pnav strong { font-size:17px; } .pnav span { opacity:.85; font-size:12px; }
        .pbody { padding:28px; } .pbody h3 { margin:0; font-size:24px; } .pbody > p { color:#64748b; margin:8px 0 22px; }
        .pcards { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; } .pcard { padding:16px; background:var(--card); border-radius:var(--r); box-shadow:0 8px 18px rgba(15,23,42,.08); } .pcard i { color:var(--s); font-size:18px; } .pcard strong { display:block; margin-top:14px; font-size:20px; } .pcard span { color:#64748b; font-size:12px; }
        .pcontent { margin-top:18px; padding:20px; border-radius:var(--r); background:var(--card); box-shadow:0 8px 18px rgba(15,23,42,.08); } .pcontent h4 { margin:0 0 7px; } .pcontent p { color:#64748b; font-size:13px; } .pbutton { border-radius:calc(var(--r) * .65); padding:9px 13px; display:inline-block; background:var(--p); color:#fff; font-size:12px; font-weight:700; }
        .toast { position:fixed; right:22px; bottom:22px; max-width:360px; padding:13px 16px; color:#fff; background:#0f766e; border-radius:10px; box-shadow:0 10px 28px rgba(15,23,42,.22); transform:translateY(100px); opacity:0; transition:.2s; } .toast.show { transform:translateY(0); opacity:1; }
        @media(max-width:900px) { .layout { grid-template-columns:1fr; } } @media(max-width:480px) { .fields,.pcards { grid-template-columns:1fr; } .topbar { align-items:flex-start; flex-direction:column; } }
    </style>
</head>
<body>
    <header class="topbar"><div><h1><i class="fa-solid fa-wand-magic-sparkles"></i> Apariencia de la plataforma</h1><p>Personaliza el tema visual que verán los usuarios de Guagua.</p></div><a class="back" href="../../index.php"><i class="fa-solid fa-arrow-left"></i> Volver al inicio</a></header>
    <main class="layout">
        <section class="panel controls">
            <div class="section"><h2>Colores</h2><p>Define la identidad visual de la interfaz.</p><div class="fields">
                <label><span>Color principal</span><input id="primary" type="color" value="<?= htmlspecialchars($settings['primary']) ?>"></label>
                <label><span>Color secundario</span><input id="secondary" type="color" value="<?= htmlspecialchars($settings['secondary']) ?>"></label>
                <label><span>Fondo general</span><input id="background" type="color" value="<?= htmlspecialchars($settings['background']) ?>"></label>
                <label><span>Fondo del inicio</span><input id="homeBackground" type="color" value="<?= htmlspecialchars($settings['homeBackground']) ?>"></label>
                <label><span>Superficies y tarjetas</span><input id="surface" type="color" value="<?= htmlspecialchars($settings['surface']) ?>"></label>
                <label><span>Texto principal</span><input id="text" type="color" value="<?= htmlspecialchars($settings['text']) ?>"></label>
            </div></div>
            <div class="section"><h2>Tipografía y forma</h2><p>Ajustes de lectura y estilo de los controles.</p>
                <label><span>Fuente de la plataforma</span><select id="font"><?php foreach ($fonts as $name => $value): ?><option value="<?= htmlspecialchars($name) ?>" <?= $settings['font'] === $name ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option><?php endforeach; ?></select></label>
                <div class="range-row"><label><span>Tamaño base de texto</span><input id="fontSize" type="range" min="13" max="18" value="<?= $settings['fontSize'] ?>"></label><output id="fontSizeOut"></output></div>
                <div class="range-row"><label><span>Redondeado de los componentes</span><input id="radius" type="range" min="0" max="24" value="<?= $settings['radius'] ?>"></label><output id="radiusOut"></output></div>
            </div>
            <div class="section"><h2>Comportamiento</h2><p>Pequeños detalles de presentación para la interfaz.</p>
                <label><span>Densidad de contenido</span><select id="density"><option value="comfortable" <?= $settings['density'] === 'comfortable' ? 'selected' : '' ?>>Cómoda</option><option value="compact" <?= $settings['density'] === 'compact' ? 'selected' : '' ?>>Compacta</option></select></label>
                <label class="toggle"><span>Sombras en tarjetas<small>Da profundidad a paneles y ventanas.</small></span><input id="shadow" type="checkbox" <?= $settings['shadow'] ? 'checked' : '' ?>></label>
                <label class="toggle"><span>Animaciones suaves<small>Transiciones al interactuar con controles.</small></span><input id="animations" type="checkbox" <?= $settings['animations'] ? 'checked' : '' ?>></label>
            </div>
            <div class="actions"><button class="reset" type="button" id="reset"><i class="fa-solid fa-rotate-left"></i> Restaurar</button><button class="save" type="button" id="save"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button></div>
        </section>
        <section class="panel preview-wrap"><div class="preview-label"><i class="fa-solid fa-eye"></i> Vista previa en tiempo real</div><div class="preview" id="preview"><div class="pnav"><strong>Guagua</strong><span>Inicio &nbsp; Cursos &nbsp; Apps</span></div><div class="pbody"><h3>Buen día, docente</h3><p>Así se verá la interfaz general de la plataforma.</p><div class="pcards"><div class="pcard"><i class="fa-solid fa-users"></i><strong>328</strong><span>Estudiantes</span></div><div class="pcard"><i class="fa-solid fa-book-open"></i><strong>14</strong><span>Cursos activos</span></div><div class="pcard"><i class="fa-solid fa-chart-line"></i><strong>92%</strong><span>Asistencia</span></div></div><div class="pcontent"><h4>Actividad reciente</h4><p>La configuración se aplica a botones, formularios, paneles y al menú principal.</p><span class="pbutton">Ver detalles</span></div></div></div></section>
    </main>
    <div class="toast" id="toast" role="status"></div>
    <script>
        const defaults = <?= json_encode($defaults, JSON_UNESCAPED_UNICODE) ?>;
        const inputs = ['primary','secondary','background','homeBackground','surface','text','font','fontSize','radius','density','shadow','animations'];
        const preview = document.getElementById('preview');
        const getSettings = () => Object.fromEntries(inputs.map(id => [id, document.getElementById(id).type === 'checkbox' ? document.getElementById(id).checked : document.getElementById(id).value]));
        function updatePreview() {
            const s = getSettings();
            preview.style.setProperty('--p', s.primary); preview.style.setProperty('--s', s.secondary); preview.style.setProperty('--b', s.background); preview.style.setProperty('--card', s.surface); preview.style.setProperty('--t', s.text); preview.style.setProperty('--r', s.radius + 'px'); preview.style.fontFamily = s.font + ', Arial, sans-serif'; preview.style.fontSize = s.fontSize + 'px';
            preview.querySelectorAll('.pcard,.pcontent').forEach(el => { el.style.boxShadow = s.shadow ? '0 8px 18px rgba(15,23,42,.08)' : 'none'; });
            document.getElementById('fontSizeOut').textContent = s.fontSize + ' px'; document.getElementById('radiusOut').textContent = s.radius + ' px';
        }
        inputs.forEach(id => document.getElementById(id).addEventListener('input', updatePreview)); updatePreview();
        document.getElementById('reset').addEventListener('click', () => { Object.entries(defaults).forEach(([id, value]) => { const input = document.getElementById(id); if (input.type === 'checkbox') input.checked = value; else input.value = value; }); updatePreview(); });
        const toast = document.getElementById('toast'); function showToast(message, error = false) { toast.textContent = message; toast.style.background = error ? '#b91c1c' : '#0f766e'; toast.classList.add('show'); setTimeout(() => toast.classList.remove('show'), 4000); }
        document.getElementById('save').addEventListener('click', async () => { const button = document.getElementById('save'); button.disabled = true; button.textContent = 'Guardando…'; try { const body = new FormData(); body.append('settings', JSON.stringify(getSettings())); body.append('csrf', '<?= $_SESSION['interface_config_csrf'] ?>'); const response = await fetch('index.php', {method:'POST', body}); const result = await response.json(); showToast(result.message, !result.success); } catch (error) { showToast('No fue posible guardar la configuración.', true); } finally { button.disabled = false; button.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Guardar cambios'; } });
    </script>
</body>
</html>
