
<?php
// =========================================================================
// BACKEND PHP - CONSTRUCTOR DE RÚBRICAS
// =========================================================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guagua';

mysqli_report(MYSQLI_REPORT_OFF);
$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name, "7000");

if (!$mysqli->connect_error) {
    $mysqli->set_charset("utf8mb4");

    $mysqli->query("CREATE TABLE IF NOT EXISTS custom_rubrics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rubric_key VARCHAR(50) UNIQUE NOT NULL,
        rubric_name VARCHAR(150) NOT NULL,
        icon_class VARCHAR(100) DEFAULT 'fa-solid fa-star',
        color_hex VARCHAR(7) DEFAULT '#6366f1',
        criteria JSON NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
}

// API endpoints
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($mysqli->connect_error) {
        echo json_encode(['error' => 'Sin conexión a MySQL']);
        exit;
    }

    if ($_GET['action'] == 'list') {
        $result = $mysqli->query("SELECT * FROM custom_rubrics WHERE is_active = 1 ORDER BY created_at DESC");
        $rubrics = [];
        while ($row = $result->fetch_assoc()) {
            $row['criteria'] = json_decode($row['criteria'], true);
            $rubrics[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $rubrics]);
        exit;
    }

    if ($_GET['action'] == 'save' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $criteriaJson = json_encode($data['criteria'], JSON_UNESCAPED_UNICODE);
        $stmt = $mysqli->prepare("INSERT INTO custom_rubrics (rubric_key, rubric_name, icon_class, color_hex, criteria) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $data['rubricKey'], $data['rubricName'], $data['iconClass'], $data['colorHex'], $criteriaJson);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $mysqli->insert_id]);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        $stmt->close();
        exit;
    }

    if ($_GET['action'] == 'delete' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $mysqli->prepare("UPDATE custom_rubrics SET is_active = 0 WHERE rubric_key = ?");
        $stmt->bind_param("s", $data['rubricKey']);
        $stmt->execute();
        echo json_encode(['success' => $stmt->affected_rows > 0]);
        $stmt->close();
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Constructor de Rúbricas — Guagua</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
        }

        /* ===== BACKGROUND ===== */
        .bg-mesh {
            background-image:
                radial-gradient(at 20% 20%, rgba(99,102,241,0.15) 0, transparent 50%),
                radial-gradient(at 80% 80%, rgba(139,92,246,0.12) 0, transparent 50%),
                radial-gradient(at 50% 0%, rgba(6,182,212,0.08) 0, transparent 50%);
        }

        /* ===== GLASS CARDS ===== */
        .glass {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .glass-light {
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* ===== RUBRIC CARD ===== */
        .rubric-card {
            transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            position: relative;
            overflow: hidden;
        }
        .rubric-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--card-color);
            opacity: 0.6;
            transition: opacity 0.3s;
        }
        .rubric-card:hover {
            transform: translateY(-4px);
            border-color: rgba(148,163,184,0.2);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }
        .rubric-card:hover::before { opacity: 1; }
        .rubric-card .card-actions {
            opacity: 0;
            transition: opacity 0.2s;
        }
        .rubric-card:hover .card-actions { opacity: 1; }

        /* ===== CREATE CARD ===== */
        .create-card {
            border: 2px dashed rgba(99,102,241,0.3);
            transition: all 0.3s;
            cursor: pointer;
        }
        .create-card:hover {
            border-color: rgba(99,102,241,0.6);
            background: rgba(99,102,241,0.08);
            transform: translateY(-4px);
        }

        /* ===== WIZARD ===== */
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.4); }
            50% { box-shadow: 0 0 0 8px rgba(99,102,241,0); }
        }
        .wizard-enter { animation: fadeSlideIn 0.5s cubic-bezier(0.16,1,0.3,1); }

        /* Step Bar */
        .step-bar { display: flex; align-items: center; gap: 0; }
        .step-dot {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px;
            border: 2px solid rgba(148,163,184,0.2);
            color: rgba(148,163,184,0.4);
            background: rgba(15,23,42,0.5);
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            position: relative; z-index: 2;
        }
        .step-dot.active {
            border-color: #6366f1; color: #fff; background: #6366f1;
            animation: pulse-glow 2s infinite;
        }
        .step-dot.done {
            border-color: #22c55e; color: #fff; background: #22c55e;
        }
        .step-connector {
            flex: 1; height: 2px; background: rgba(148,163,184,0.15);
            transition: background 0.4s;
            z-index: 1;
        }
        .step-connector.done { background: #22c55e; }

        .step-label {
            font-size: 11px; color: rgba(148,163,184,0.4);
            transition: color 0.3s;
        }
        .step-label.active { color: #c7d2fe; }
        .step-label.done { color: #86efac; }

        /* Wizard Step Content */
        .wiz-panel {
            position: relative; overflow: hidden;
        }
        .wiz-slide {
            transition: transform 0.45s cubic-bezier(0.4,0,0.2,1), opacity 0.3s;
        }
        .wiz-slide.active { transform: translateX(0); opacity: 1; pointer-events: auto; position: relative; }
        .wiz-slide.left { transform: translateX(-100%); opacity: 0; pointer-events: none; position: absolute; top: 0; left: 0; width: 100%; }
        .wiz-slide.right { transform: translateX(100%); opacity: 0; pointer-events: none; position: absolute; top: 0; left: 0; width: 100%; }

        /* Icon Picker */
        .ico-btn {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; cursor: pointer;
            border: 2px solid rgba(148,163,184,0.12);
            color: rgba(148,163,184,0.5);
            background: rgba(15,23,42,0.4);
            transition: all 0.2s;
        }
        .ico-btn:hover { border-color: rgba(165,180,252,0.4); color: #a5b4fc; background: rgba(99,102,241,0.1); transform: scale(1.08); }
        .ico-btn.picked { border-color: #6366f1; color: #fff; background: #6366f1; transform: scale(1.08); box-shadow: 0 4px 16px rgba(99,102,241,0.4); }

        /* Color Picker */
        .clr-btn {
            width: 36px; height: 36px; border-radius: 50%; cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.2s;
        }
        .clr-btn:hover { transform: scale(1.15); }
        .clr-btn.picked { border-color: #fff; transform: scale(1.2); box-shadow: 0 0 16px rgba(255,255,255,0.2); }

        /* Criterion Row */
        .crit-row {
            background: rgba(30,41,59,0.5);
            border: 1px solid rgba(148,163,184,0.1);
            border-radius: 14px;
            padding: 16px;
            transition: border-color 0.2s;
        }
        .crit-row:hover { border-color: rgba(148,163,184,0.2); }

        /* Weight Bar */
        .wt-track { height: 6px; background: rgba(148,163,184,0.12); border-radius: 999px; overflow: hidden; }
        .wt-fill { height: 100%; border-radius: 999px; transition: width 0.4s cubic-bezier(0.4,0,0.2,1), background 0.3s; }

        /* Inputs (dark themed) */
        .inp {
            background: rgba(15,23,42,0.6);
            border: 1px solid rgba(148,163,184,0.15);
            color: #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            width: 100%;
        }
        .inp::placeholder { color: rgba(148,163,184,0.35); }
        .inp:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }

        .inp-sm { padding: 8px 12px; font-size: 13px; text-align: center; }

        .txt {
            background: rgba(15,23,42,0.6);
            border: 1px solid rgba(148,163,184,0.15);
            color: #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px;
            font-size: 13px;
            resize: none;
            width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .txt::placeholder { color: rgba(148,163,184,0.3); }
        .txt:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }

        /* Accordion */
        .acc-body { max-height: 0; overflow: hidden; transition: max-height 0.35s cubic-bezier(0.4,0,0.2,1), padding 0.3s; }
        .acc-body.open { max-height: 800px; }

        /* Toast */
        @keyframes toastIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .toast { animation: toastIn 0.4s cubic-bezier(0.16,1,0.3,1); }

        /* Delete Modal */
        .overlay { backdrop-filter: blur(6px); background: rgba(0,0,0,0.5); animation: fadeIn 0.2s; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .modal-pop { animation: fadeSlideIn 0.3s cubic-bezier(0.16,1,0.3,1); }

        /* Level dots */
        .lvl-sup { color: #22c55e; }
        .lvl-alt { color: #3b82f6; }
        .lvl-bas { color: #eab308; }
        .lvl-baj { color: #ef4444; }

        /* Empty state */
        .empty-illustration {
            opacity: 0.12;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.15); border-radius: 3px; }

        /* Print View */
        .print-tpl { display: none; }
        @media print {
            body { background: white !important; }
            nav, #view-dashboard, #view-builder, #del-modal, #detail-modal, .toast { display: none !important; }
            .print-tpl { display: block !important; color: #000; padding: 20px; }
            .print-tpl h2 { font-size: 20px; font-weight: 700; text-align: center; margin: 0 0 4px; }
            .print-tpl .sub { font-size: 12px; text-align: center; color: #555; margin-bottom: 16px; }
            .print-tpl table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 12px; }
            .print-tpl th, .print-tpl td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
            .print-tpl th { background: #f3f4f6 !important; font-weight: 700; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .print-tpl .th-sup { background: #f0fdf4 !important; }
            .print-tpl .th-alt { background: #eff6ff !important; }
            .print-tpl .th-bas { background: #fefce8 !important; }
            .print-tpl .th-baj { background: #fef2f2 !important; }
            .print-tpl .scale { font-size: 10px; color: #666; margin-top: 8px; }
            .print-tpl .score-box { text-align: right; font-size: 14px; font-weight: 700; margin-top: 8px; }
            .print-tpl .obs-box { border: 1px solid #ccc; min-height: 60px; padding: 8px; margin-top: 8px; font-size: 11px; }
            .print-tpl .info-row { display: flex; gap: 16px; margin-bottom: 12px; font-size: 12px; }
            .print-tpl .info-row .fld { border-bottom: 1px solid #000; flex: 1; padding: 2px 4px; }
            .print-tpl .info-row label { font-weight: 600; margin-right: 4px; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body class="bg-mesh">

    <!-- ===== TOP NAV ===== -->
    <nav class="glass sticky top-0 z-40 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="index.php" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2 text-sm font-medium">
                <i class="fa-solid fa-arrow-left"></i> Evaluaciones
            </a>
            <div class="w-px h-5 bg-slate-700"></div>
            <h1 class="text-white font-bold text-lg flex items-center gap-2">
                <span class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-sm"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                Constructor de Rúbricas
            </h1>
        </div>
        <div class="text-xs text-slate-500">Las rúbricas creadas aparecerán como pestañas en Evaluaciones</div>
    </nav>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="max-w-6xl mx-auto px-4 py-8">

        <!-- VIEW: Dashboard (lista de rúbricas) -->
        <div id="view-dashboard">
            <div class="flex items-end justify-between mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-white mb-1">Mis Rúbricas</h2>
                    <p class="text-sm text-slate-400">Crea, visualiza y gestiona tus instrumentos de evaluación personalizados.</p>
                </div>
                <button onclick="showBuilder()" class="px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition-all text-sm flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-plus"></i> Nueva Rúbrica
                </button>
            </div>

            <!-- Grid de rúbricas -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="rubrics-grid">
                <!-- JS rendered -->
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-20">
                <div class="empty-illustration text-8xl mb-6">
                    <i class="fa-solid fa-clipboard-list text-slate-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-slate-300 mb-2">No tienes rúbricas personalizadas</h3>
                <p class="text-sm text-slate-500 mb-6 max-w-md mx-auto">Crea tu primera rúbrica con el constructor paso a paso. Podrás usarla inmediatamente en la sección de evaluaciones.</p>
                <button onclick="showBuilder()" class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition-all text-sm inline-flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Crear mi primera rúbrica
                </button>
            </div>
        </div>

        <!-- VIEW: Builder (wizard de construcción) -->
        <div id="view-builder" class="hidden">

            <button onclick="showDashboard()" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2 text-sm font-medium mb-6">
                <i class="fa-solid fa-arrow-left"></i> Volver a Mis Rúbricas
            </button>

            <div class="glass rounded-2xl overflow-hidden wizard-enter">

                <!-- Wizard Header + Steps -->
                <div class="px-6 pt-6 pb-5 border-b border-slate-700/50">
                    <h3 class="text-lg font-bold text-white mb-5">Construir Rúbrica</h3>
                    <!-- Step Bar -->
                    <div class="step-bar max-w-lg mx-auto" id="step-bar">
                        <div class="text-center">
                            <div class="step-dot active" id="sd-1">1</div>
                            <div class="step-label active mt-1" id="sl-1">Información</div>
                        </div>
                        <div class="step-connector" id="sc-1"></div>
                        <div class="text-center">
                            <div class="step-dot" id="sd-2">2</div>
                            <div class="step-label mt-1" id="sl-2">Criterios</div>
                        </div>
                        <div class="step-connector" id="sc-2"></div>
                        <div class="text-center">
                            <div class="step-dot" id="sd-3">3</div>
                            <div class="step-label mt-1" id="sl-3">Niveles</div>
                        </div>
                        <div class="step-connector" id="sc-3"></div>
                        <div class="text-center">
                            <div class="step-dot" id="sd-4">4</div>
                            <div class="step-label mt-1" id="sl-4">Confirmar</div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Body -->
                <div class="wiz-panel px-6 py-6" style="min-height:420px;">

                    <!-- === PASO 1: Info Básica === -->
                    <div class="wiz-slide active" id="ws-1">
                        <p class="text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-1">Paso 1 de 4</p>
                        <h4 class="text-xl font-bold text-white mb-1">Información Básica</h4>
                        <p class="text-sm text-slate-400 mb-6">Dale identidad a tu rúbrica: nombre, ícono y color.</p>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-300 mb-2">Nombre de la Rúbrica <span class="text-red-400">*</span></label>
                            <input type="text" id="w-name" class="inp" placeholder="Ej: Marcado de Cuadernos, Portafolio, Separador...">
                            <p class="text-xs text-slate-500 mt-1.5" id="w-name-msg">Se usará como título de pestaña en Evaluaciones</p>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-slate-300 mb-3">Ícono</label>
                            <div class="flex flex-wrap gap-2" id="icons-grid"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-3">Color</label>
                            <div class="flex flex-wrap gap-3" id="colors-grid"></div>
                        </div>
                    </div>

                    <!-- === PASO 2: Criterios === -->
                    <div class="wiz-slide right" id="ws-2">
                        <p class="text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-1">Paso 2 de 4</p>
                        <h4 class="text-xl font-bold text-white mb-1">Criterios de Evaluación</h4>
                        <p class="text-sm text-slate-400 mb-5">Define qué aspectos evaluarás y cuánto pesa cada uno. Los pesos deben sumar <strong class="text-slate-200">100%</strong>.</p>

                        <!-- Weight bar -->
                        <div class="glass-light rounded-xl p-4 mb-5">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs font-medium text-slate-400">Peso total</span>
                                <span class="text-sm font-bold text-slate-200" id="w-wt-label">0%</span>
                            </div>
                            <div class="wt-track">
                                <div class="wt-fill" id="w-wt-bar" style="width:0%; background:#6366f1;"></div>
                            </div>
                            <p class="text-xs mt-1.5" id="w-wt-msg"><span class="text-slate-500">Distribuye el 100% entre tus criterios</span></p>
                        </div>

                        <div class="space-y-3" id="w-crit-list"></div>

                        <button onclick="addCrit()" class="mt-4 w-full py-3 border-2 border-dashed border-slate-700 rounded-xl text-slate-500 hover:border-indigo-500 hover:text-indigo-400 hover:bg-indigo-500/5 transition-all font-medium text-sm flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i> Agregar Criterio
                        </button>
                    </div>

                    <!-- === PASO 3: Niveles / Descriptores === -->
                    <div class="wiz-slide right" id="ws-3">
                        <p class="text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-1">Paso 3 de 4</p>
                        <h4 class="text-xl font-bold text-white mb-1">Niveles de Desempeño</h4>
                        <p class="text-sm text-slate-400 mb-5">Describe el desempeño esperado en cada nivel para cada criterio. Puntajes alineados al sistema institucional (1–5).</p>

                        <div class="space-y-3" id="w-desc-list"></div>
                    </div>

                    <!-- === PASO 4: Preview / Confirmar === -->
                    <div class="wiz-slide right" id="ws-4">
                        <p class="text-green-400 text-xs font-semibold uppercase tracking-wider mb-1">Paso Final</p>
                        <h4 class="text-xl font-bold text-white mb-1">Vista Previa</h4>
                        <p class="text-sm text-slate-400 mb-5">Así se verá tu rúbrica. Revisa y confirma para crearla.</p>

                        <div id="w-preview" class="space-y-4"></div>
                    </div>

                </div>

                <!-- Wizard Footer -->
                <div class="px-6 py-4 border-t border-slate-700/50 flex items-center justify-between bg-slate-900/40">
                    <button onclick="prevStep()" id="btn-prev" class="px-5 py-2.5 text-slate-400 hover:text-white font-medium rounded-xl hover:bg-slate-800 transition-colors text-sm hidden items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Anterior
                    </button>
                    <div class="flex-1"></div>
                    <button onclick="nextStep()" id="btn-next" class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-500 transition-all text-sm flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                        Siguiente <i class="fa-solid fa-arrow-right"></i>
                    </button>
                    <button onclick="doSave()" id="btn-save" class="px-6 py-2.5 bg-green-600 text-white font-semibold rounded-xl hover:bg-green-500 transition-all text-sm items-center gap-2 shadow-lg shadow-green-500/20 hidden">
                        <i class="fa-solid fa-check"></i> Crear Rúbrica
                    </button>
                </div>
            </div>
        </div>

    </main>

    <!-- ===== DELETE MODAL ===== -->
    <div id="del-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 overlay">
        <div class="modal-pop w-full max-w-sm glass rounded-2xl p-6 text-center">
            <div class="w-14 h-14 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-red-400 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">¿Eliminar rúbrica?</h3>
            <p class="text-sm text-slate-400 mb-1">Vas a eliminar:</p>
            <p class="text-base font-semibold text-white mb-4" id="del-name">—</p>
            <p class="text-xs text-slate-500 mb-6">Las evaluaciones ya guardadas se mantienen.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="closeDel()" class="px-5 py-2.5 text-slate-300 bg-slate-800 border border-slate-700 font-medium rounded-xl hover:bg-slate-700 transition-colors text-sm">Cancelar</button>
                <button onclick="confirmDel()" class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-500 transition-colors text-sm"><i class="fa-solid fa-trash-can mr-1"></i> Eliminar</button>
            </div>
        </div>
    </div>

    <!-- ===== DETAIL MODAL (ver rúbrica) ===== -->
    <div id="detail-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4 py-6 overlay" style="overflow-y:auto;">
        <div class="modal-pop w-full max-w-3xl glass rounded-2xl overflow-hidden my-auto">
            <div class="px-6 py-4 border-b border-slate-700/50 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white flex items-center gap-2" id="detail-title"><i class="fa-solid fa-star"></i> Rúbrica</h3>
                <button onclick="closeDetail()" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="px-6 py-5 max-h-[70vh] overflow-y-auto" id="detail-body"></div>
            <div class="px-6 py-3 border-t border-slate-700/50 flex justify-between items-center bg-slate-900/40">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="text-sm text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1"><i class="fa-solid fa-arrow-right"></i> Usar en Evaluaciones</a>
                    <button onclick="printRubric()" class="text-sm text-emerald-400 hover:text-emerald-300 font-medium flex items-center gap-1"><i class="fa-solid fa-print"></i> Imprimir</button>
                </div>
                <button onclick="closeDetail()" class="px-4 py-2 text-slate-400 hover:text-white text-sm font-medium">Cerrar</button>
            </div>
        </div>
    </div>

<!-- Print Template -->
<div class="print-tpl" id="print-tpl">
    <h2>RÚBRICA DE EVALUACIÓN</h2>
    <p class="sub">Institución Educativa Rural La Josefina</p>
    <p class="sub" id="pt-name" style="font-weight:600;font-size:14px;color:#000"></p>
    <div class="info-row">
        <div><label>Estudiante:</label><span class="fld">_________________________</span></div>
        <div><label>Grado:</label><span class="fld">________</span></div>
        <div><label>Asignatura:</label><span class="fld">___________________</span></div>
        <div><label>Fecha:</label><span class="fld">___________</span></div>
    </div>
    <div id="pt-table"></div>
    <p class="scale"><strong>Escala de Valoración:</strong> Superior (4.6 – 5.0) | Alto (4.0 – 4.5) | Básico (3.0 – 3.9) | Bajo (1.0 – 2.9)</p>
    <div class="score-box">Calificación Final: ______ / 5.0 &nbsp;&nbsp; Desempeño: __________________</div>
    <div class="obs-box"><strong>Observaciones:</strong></div>
</div>

<script>
// ================================================================
// STATE
// ================================================================
let allRubrics = [];
let step = 1;
const TOTAL = 4;
let delKey = null;
let critCount = 2;

let wiz = {
    name: '', icon: 'fa-solid fa-star', color: '#6366f1',
    criteria: [
        { id:'c_1', title:'', weight:50, descs:['','','',''] },
        { id:'c_2', title:'', weight:50, descs:['','','',''] }
    ]
};

const ICONS = [
    'fa-solid fa-book','fa-solid fa-book-open','fa-solid fa-pen','fa-solid fa-pencil',
    'fa-solid fa-star','fa-solid fa-bookmark','fa-solid fa-graduation-cap','fa-solid fa-school',
    'fa-solid fa-chalkboard','fa-solid fa-clipboard','fa-solid fa-file','fa-solid fa-folder-open',
    'fa-solid fa-microscope','fa-solid fa-flask','fa-solid fa-calculator','fa-solid fa-paintbrush',
    'fa-solid fa-music','fa-solid fa-globe','fa-solid fa-heart','fa-solid fa-lightbulb',
    'fa-solid fa-puzzle-piece','fa-solid fa-trophy','fa-solid fa-medal','fa-solid fa-handshake',
    'fa-solid fa-brain','fa-solid fa-palette','fa-solid fa-ruler','fa-solid fa-laptop',
    'fa-solid fa-list-check','fa-solid fa-scissors','fa-solid fa-marker','fa-solid fa-highlighter',
    'fa-solid fa-note-sticky','fa-solid fa-swatchbook','fa-solid fa-eye','fa-solid fa-chart-simple'
];

const COLORS = ['#6366f1','#8b5cf6','#ec4899','#f43f5e','#f97316','#eab308','#22c55e','#06b6d4','#3b82f6','#14b8a6','#a855f7','#64748b'];

const LEVELS = ['Superior (5.0)','Alto (4.2)','Básico (3.5)','Bajo (2.5)'];
const LEVEL_COLORS = ['#22c55e','#3b82f6','#eab308','#ef4444'];
const LEVEL_NAMES = ['Superior','Alto','Básico','Bajo'];
const LEVEL_SCORES = [5.0,4.2,3.5,2.5];
const LEVEL_PH = ['Describe el desempeño superior...','Describe el desempeño alto...','Describe el desempeño básico...','Describe el desempeño bajo...'];

// ================================================================
// DASHBOARD
// ================================================================
async function loadRubrics() {
    try {
        const r = await fetch(location.pathname+'?action=list');
        const j = await r.json();
        if (j.success) allRubrics = j.data;
    } catch(e) { console.log('Sin servidor PHP'); }
    renderGrid();
}

function renderGrid() {
    const grid = document.getElementById('rubrics-grid');
    const empty = document.getElementById('empty-state');

    if (allRubrics.length === 0) {
        grid.innerHTML = '';
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    grid.innerHTML = allRubrics.map(r => {
        const c = r.color_hex || '#6366f1';
        const critCount = r.criteria ? r.criteria.length : 0;
        return `
        <div class="rubric-card glass rounded-2xl p-5 cursor-pointer" style="--card-color:${c}" onclick="openDetail('${r.rubric_key}')">
            <div class="card-actions absolute top-3 right-3 flex gap-1">
                <button onclick="event.stopPropagation(); printFromCard('${r.rubric_key}')" class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/40 flex items-center justify-center text-xs transition-colors" title="Imprimir">
                    <i class="fa-solid fa-print"></i>
                </button>
                <button onclick="event.stopPropagation(); openDel('${r.rubric_key}','${r.rubric_name.replace(/'/g,"\\'")}')" class="w-8 h-8 rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500/40 flex items-center justify-center text-xs transition-colors" title="Eliminar">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg" style="background:${c}">
                    <i class="${r.icon_class || 'fa-solid fa-star'}"></i>
                </div>
                <div>
                    <h4 class="font-bold text-white text-base">${r.rubric_name}</h4>
                    <p class="text-xs text-slate-500">${critCount} criterio${critCount!==1?'s':''}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5 mb-3">
                ${r.criteria ? r.criteria.map(cr => `<span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:${c}20;color:${c}">${cr.title} · ${cr.weight}%</span>`).join('') : ''}
            </div>
            <div class="flex items-center justify-between text-xs text-slate-500">
                <span>Escala 1–5</span>
                <span>${new Date(r.created_at).toLocaleDateString('es-CO')}</span>
            </div>
        </div>`;
    }).join('');
}

function openDetail(key) {
    const r = allRubrics.find(x => x.rubric_key === key);
    if (!r) return;
    const c = r.color_hex || '#6366f1';
    document.getElementById('detail-title').innerHTML = `<i class="${r.icon_class}" style="color:${c}"></i> ${r.rubric_name}`;

    let html = '';
    r.criteria.forEach(cr => {
        html += `<div class="glass-light rounded-xl overflow-hidden mb-4">
            <div class="px-4 py-3 border-b border-slate-700/30 flex items-center justify-between">
                <span class="font-semibold text-white text-sm">${cr.title}</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:${c}20;color:${c}">Peso: ${cr.weight}%</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 p-4">
                ${cr.levels.map((lv,i) => `
                <div class="bg-slate-900/40 rounded-lg p-3 border-t-2" style="border-color:${LEVEL_COLORS[i]}">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="font-bold text-xs text-slate-200">${LEVEL_NAMES[i]}</span>
                        <span class="text-[10px] text-slate-500">${LEVEL_SCORES[i]}</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">${lv.desc || '<em class="text-slate-600">Sin descripción</em>'}</p>
                </div>`).join('')}
            </div>
        </div>`;
    });
    document.getElementById('detail-body').innerHTML = html;
    const m = document.getElementById('detail-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeDetail() {
    const m = document.getElementById('detail-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
}

// ================================================================
// VIEWS
// ================================================================
function showDashboard() {
    document.getElementById('view-builder').classList.add('hidden');
    document.getElementById('view-dashboard').classList.remove('hidden');
}

function showBuilder() {
    step = 1;
    critCount = 2;
    wiz = {
        name: '', icon: 'fa-solid fa-star', color: '#6366f1',
        criteria: [
            { id:'c_1', title:'', weight:50, descs:['','','',''] },
            { id:'c_2', title:'', weight:50, descs:['','','',''] }
        ]
    };
    document.getElementById('view-dashboard').classList.add('hidden');
    document.getElementById('view-builder').classList.remove('hidden');
    renderStep();
    renderIcons();
    renderColors();
    renderCrits();
    updateBar();
    document.getElementById('w-name').value = '';
}

// ================================================================
// WIZARD NAVIGATION
// ================================================================
function renderStep() {
    for (let i=1;i<=TOTAL;i++) {
        const s = document.getElementById('ws-'+i);
        s.className = 'wiz-slide ' + (i===step?'active':i<step?'left':'right');
        const d = document.getElementById('sd-'+i);
        const l = document.getElementById('sl-'+i);
        if (i<step) { d.className='step-dot done'; d.innerHTML='<i class="fa-solid fa-check text-xs"></i>'; l.className='step-label done'; }
        else if (i===step) { d.className='step-dot active'; d.innerHTML=i; l.className='step-label active'; }
        else { d.className='step-dot'; d.innerHTML=i; l.className='step-label'; }
        if (i<TOTAL) document.getElementById('sc-'+i).className = 'step-connector'+(i<step?' done':'');
    }
    const bp = document.getElementById('btn-prev');
    bp.classList.toggle('hidden', step===1);
    bp.classList.toggle('flex', step!==1);
    document.getElementById('btn-next').classList.toggle('hidden', step===TOTAL);
    const bs = document.getElementById('btn-save');
    bs.classList.toggle('hidden', step!==TOTAL);
    bs.classList.toggle('flex', step===TOTAL);
}

function nextStep() {
    if (!validate()) return;
    if (step===2) { syncCrits(); renderDescs(); }
    if (step===3) { syncDescs(); renderPreview(); }
    if (step<TOTAL) { step++; renderStep(); }
}
function prevStep() {
    if (step===2) syncCrits();
    if (step===3) syncDescs();
    if (step>1) { step--; renderStep(); }
}

function validate() {
    if (step===1) {
        const n = document.getElementById('w-name').value.trim();
        if (!n) {
            document.getElementById('w-name-msg').innerHTML='<span class="text-red-400">⚠ El nombre es obligatorio</span>';
            document.getElementById('w-name').focus();
            return false;
        }
        const k = slugify(n);
        if (allRubrics.find(r=>r.rubric_key===k) || ['oral','escrita','equipo','tertulia','tarea','exposicion'].includes(k)) {
            document.getElementById('w-name-msg').innerHTML='<span class="text-red-400">⚠ Ya existe una rúbrica con ese nombre</span>';
            return false;
        }
        wiz.name = n;
        document.getElementById('w-name-msg').innerHTML='<span class="text-slate-500">Se usará como título de pestaña en Evaluaciones</span>';
        return true;
    }
    if (step===2) {
        syncCrits();
        const valid = wiz.criteria.filter(c=>c.title.trim());
        if (!valid.length) { toast('Agrega al menos un criterio con nombre','#f43f5e'); return false; }
        wiz.criteria = wiz.criteria.filter(c=>c.title.trim());
        const tw = wiz.criteria.reduce((s,c)=>s+(parseFloat(c.weight)||0),0);
        if (Math.abs(tw-100)>1) { toast(`Los pesos deben sumar 100% (actual: ${tw}%)`,'#f43f5e'); return false; }
        if (tw!==100) wiz.criteria[wiz.criteria.length-1].weight += (100-tw);
        return true;
    }
    if (step===3) { syncDescs(); return true; }
    return true;
}

// ================================================================
// STEP 1: Icons & Colors
// ================================================================
function renderIcons() {
    const g = document.getElementById('icons-grid');
    g.innerHTML = ICONS.map(ic => `<div class="ico-btn${ic===wiz.icon?' picked':''}" data-ic="${ic}" onclick="pickIcon(this,'${ic}')"><i class="${ic}"></i></div>`).join('');
}
function pickIcon(el, ic) {
    wiz.icon = ic;
    document.querySelectorAll('.ico-btn').forEach(b=>b.classList.remove('picked'));
    el.classList.add('picked');
}
function renderColors() {
    const g = document.getElementById('colors-grid');
    g.innerHTML = COLORS.map(c => `<div class="clr-btn${c===wiz.color?' picked':''}" style="background:${c}" onclick="pickColor(this,'${c}')"></div>`).join('');
}
function pickColor(el, c) {
    wiz.color = c;
    document.querySelectorAll('.clr-btn').forEach(b=>b.classList.remove('picked'));
    el.classList.add('picked');
}

// ================================================================
// STEP 2: Criteria
// ================================================================
function renderCrits() {
    const list = document.getElementById('w-crit-list');
    list.innerHTML = wiz.criteria.map((c,i) => `
    <div class="crit-row">
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <label class="text-xs font-medium text-slate-500 mb-1 block">Criterio ${i+1}</label>
                <input type="text" class="inp cr-name" data-i="${i}" value="${c.title}" placeholder="Ej: Limpieza y Orden, Caligrafía...">
            </div>
            <div class="w-24">
                <label class="text-xs font-medium text-slate-500 mb-1 block">Peso %</label>
                <input type="number" class="inp inp-sm cr-wt" data-i="${i}" value="${c.weight}" min="1" max="100" oninput="updateBar()">
            </div>
            <button onclick="removeCrit(${i})" class="mt-5 w-8 h-8 rounded-full text-slate-600 hover:text-red-400 hover:bg-red-500/10 flex items-center justify-center transition-colors${wiz.criteria.length<=1?' opacity-20 pointer-events-none':''}" title="Eliminar"><i class="fa-solid fa-trash-can text-xs"></i></button>
        </div>
    </div>`).join('');
    updateBar();
}

function addCrit() {
    critCount++;
    syncCrits();
    wiz.criteria.push({ id:'c_'+critCount, title:'', weight:0, descs:['','','',''] });
    renderCrits();
}
function removeCrit(i) {
    if (wiz.criteria.length<=1) return;
    syncCrits();
    wiz.criteria.splice(i,1);
    renderCrits();
}
function syncCrits() {
    document.querySelectorAll('.cr-name').forEach(el => { wiz.criteria[+el.dataset.i].title = el.value.trim(); });
    document.querySelectorAll('.cr-wt').forEach(el => { wiz.criteria[+el.dataset.i].weight = parseFloat(el.value)||0; });
}
function updateBar() {
    let t=0;
    document.querySelectorAll('.cr-wt').forEach(el => t+=parseFloat(el.value)||0);
    const bar = document.getElementById('w-wt-bar');
    const lbl = document.getElementById('w-wt-label');
    const msg = document.getElementById('w-wt-msg');
    bar.style.width = Math.min(t,100)+'%';
    lbl.textContent = t+'%';
    if (t===100) { bar.style.background='#22c55e'; lbl.style.color='#86efac'; msg.innerHTML='<span class="text-green-400">✓ Perfecto, suman 100%</span>'; }
    else if (t>100) { bar.style.background='#ef4444'; lbl.style.color='#fca5a5'; msg.innerHTML=`<span class="text-red-400">⚠ Exceso de ${t-100}%</span>`; }
    else { bar.style.background='#6366f1'; lbl.style.color='#c7d2fe'; msg.innerHTML=`<span class="text-slate-500">Faltan ${100-t}% por asignar</span>`; }
}

// ================================================================
// STEP 3: Descriptors
// ================================================================
function renderDescs() {
    const list = document.getElementById('w-desc-list');
    list.innerHTML = wiz.criteria.map((c,ci) => `
    <div class="glass-light rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700/30 flex items-center justify-between cursor-pointer" onclick="toggleAcc(this)">
            <div class="flex items-center gap-2">
                <h5 class="font-semibold text-white text-sm">${c.title}</h5>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:${wiz.color}30;color:${wiz.color}">${c.weight}%</span>
            </div>
            <i class="fa-solid fa-chevron-down text-slate-500 text-xs transition-transform acc-arrow"></i>
        </div>
        <div class="acc-body open">
            <div class="p-4 space-y-3">
                ${LEVELS.map((lv,li) => `
                <div class="flex items-start gap-3">
                    <span class="w-2.5 h-2.5 rounded-full mt-3 flex-shrink-0" style="background:${LEVEL_COLORS[li]}"></span>
                    <div class="flex-1">
                        <label class="text-xs font-medium text-slate-500 mb-1 block">${lv}</label>
                        <textarea class="txt dsc" rows="2" data-c="${ci}" data-l="${li}" placeholder="${LEVEL_PH[li]}">${c.descs[li]||''}</textarea>
                    </div>
                </div>`).join('')}
            </div>
        </div>
    </div>`).join('');
}
function toggleAcc(h) {
    const b = h.nextElementSibling;
    const a = h.querySelector('.acc-arrow');
    b.classList.toggle('open');
    a.style.transform = b.classList.contains('open')?'':'rotate(-90deg)';
}
function syncDescs() {
    document.querySelectorAll('.dsc').forEach(el => {
        const ci=+el.dataset.c, li=+el.dataset.l;
        if (wiz.criteria[ci]) wiz.criteria[ci].descs[li] = el.value.trim();
    });
}

// ================================================================
// STEP 4: Preview
// ================================================================
function renderPreview() {
    const c = wiz.color;
    let h = `
    <div class="glass-light rounded-xl p-4 flex items-center gap-3 mb-4" style="border-color:${c}40">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-white text-lg" style="background:${c}"><i class="${wiz.icon}"></i></div>
        <div>
            <h4 class="font-bold text-white">${wiz.name}</h4>
            <p class="text-xs text-slate-500">${wiz.criteria.length} criterio(s) · Escala 1–5 · Promedio ponderado</p>
        </div>
    </div>`;

    wiz.criteria.forEach(cr => {
        h += `<div class="glass-light rounded-xl overflow-hidden">
            <div class="px-4 py-2.5 border-b border-slate-700/30 flex items-center justify-between">
                <span class="font-semibold text-white text-sm">${cr.title}</span>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium" style="background:${c}20;color:${c}">Peso: ${cr.weight}%</span>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 p-3">
                ${cr.descs.map((d,i) => `
                <div class="bg-slate-900/40 rounded-lg p-3 border-t-2" style="border-color:${LEVEL_COLORS[i]}">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-xs text-slate-200">${LEVEL_NAMES[i]}</span>
                        <span class="text-[10px] text-slate-500">${LEVEL_SCORES[i]}</span>
                    </div>
                    <p class="text-xs text-slate-400">${d||'<em class="text-slate-600">Sin descripción</em>'}</p>
                </div>`).join('')}
            </div>
        </div>`;
    });
    document.getElementById('w-preview').innerHTML = h;
}

// ================================================================
// SAVE
// ================================================================
async function doSave() {
    const key = slugify(wiz.name);
    const payload = {
        rubricKey: key,
        rubricName: wiz.name,
        iconClass: wiz.icon,
        colorHex: wiz.color,
        criteria: wiz.criteria.map((c,i) => ({
            id: key+'_'+(i+1),
            title: c.title,
            weight: c.weight,
            levels: [
                { name:'Superior', score:5.0, desc: c.descs[0]||'Desempeño superior.' },
                { name:'Alto', score:4.2, desc: c.descs[1]||'Desempeño alto.' },
                { name:'Básico', score:3.5, desc: c.descs[2]||'Desempeño básico.' },
                { name:'Bajo', score:2.5, desc: c.descs[3]||'Desempeño bajo.' }
            ]
        }))
    };
    try {
        const r = await fetch(location.pathname+'?action=save',{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
        const j = await r.json();
        if (j.success) {
            toast('¡Rúbrica creada exitosamente!','#22c55e');
            await loadRubrics();
            showDashboard();
        } else {
            toast('Error: '+(j.error||'desconocido'),'#ef4444');
        }
    } catch(e) {
        toast('Servidor no disponible. Inicia XAMPP.','#f97316');
    }
}

// ================================================================
// DELETE
// ================================================================
function openDel(key,name) {
    delKey = key;
    document.getElementById('del-name').textContent = name;
    const m = document.getElementById('del-modal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeDel() {
    const m = document.getElementById('del-modal');
    m.classList.add('hidden'); m.classList.remove('flex');
    delKey = null;
}
async function confirmDel() {
    if (!delKey) return;
    try {
        const r = await fetch(location.pathname+'?action=delete',{ method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({rubricKey:delKey}) });
        const j = await r.json();
        if (j.success) { toast('Rúbrica eliminada','#22c55e'); await loadRubrics(); }
        else toast('Error al eliminar','#ef4444');
    } catch(e) {
        allRubrics = allRubrics.filter(r=>r.rubric_key!==delKey);
        renderGrid();
        toast('Eliminada localmente','#f97316');
    }
    closeDel();
}

// ================================================================
// UTILS
// ================================================================
function slugify(t) {
    return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');
}
function toast(msg,bg='#22c55e') {
    const t = document.createElement('div');
    t.className = 'toast fixed bottom-6 right-6 z-[80] px-5 py-3 rounded-xl shadow-2xl flex items-center gap-3 text-sm font-medium text-white';
    t.style.background = bg;
    t.innerHTML = `<i class="fa-solid fa-${bg==='#22c55e'?'circle-check':bg==='#ef4444'?'circle-xmark':'triangle-exclamation'}"></i> ${msg}`;
    document.body.appendChild(t);
    setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity 0.3s'; setTimeout(()=>t.remove(),300); },3000);
}

// ================================================================
// PRINT
// ================================================================
let printKey = null;

function printFromCard(key) {
    printKey = key;
    doPrint();
}

function printRubric() {
    // Called from detail modal - use the currently open rubric
    const titleEl = document.getElementById('detail-title');
    const r = allRubrics.find(x => titleEl.textContent.includes(x.rubric_name));
    if (r) { printKey = r.rubric_key; doPrint(); }
}

function doPrint() {
    const r = allRubrics.find(x => x.rubric_key === printKey);
    if (!r) return;
    
    document.getElementById('pt-name').textContent = r.rubric_name;
    
    let html = '<table><thead><tr><th>Criterio</th><th>Peso</th>';
    html += '<th class="th-sup">Superior (5.0)</th><th class="th-alt">Alto (4.2)</th>';
    html += '<th class="th-bas">Básico (3.5)</th><th class="th-baj">Bajo (2.5)</th><th>Nota</th></tr></thead><tbody>';
    
    r.criteria.forEach(cr => {
        html += '<tr>';
        html += `<td style="font-weight:600">${cr.title}</td>`;
        html += `<td style="text-align:center">${cr.weight}%</td>`;
        cr.levels.forEach(lv => { html += `<td>${lv.desc || ''}</td>`; });
        html += '<td></td></tr>';
    });
    html += '</tbody></table>';
    document.getElementById('pt-table').innerHTML = html;
    
    setTimeout(() => window.print(), 100);
}

// ================================================================
// INIT
// ================================================================
loadRubrics();
</script>
</body>
</html>
