<?php
/**
 * =================================================================
 * MÓDULO DE PERSONALIZACIÓN VISUAL DE LA BARRA DE NAVEGACIÓN COMPLETA
 * (Barra Superior, Slider con Config Integrado, Avatar de Usuario y Ajuste de Plantilla)
 * =================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$css_file_path = __DIR__ . '/../../comun/css/menu_custom.css';
$json_file_path = __DIR__ . '/../../comun/css/menu_custom_config.json';

// ENDPOINT AJAX DE GUARDADO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_css') {
    header('Content-Type: application/json');
    $custom_css = isset($_POST['css_content']) ? $_POST['css_content'] : '';
    $config_json = isset($_POST['config_json']) ? $_POST['config_json'] : '{}';
    
    $saved_css = file_put_contents($css_file_path, $custom_css);
    $saved_json = file_put_contents($json_file_path, $config_json);
    
    if ($saved_css !== false && $saved_json !== false) {
        echo json_encode(['success' => true, 'message' => '¡Configuración guardada exitosamente! Se ha aplicado a toda la plataforma Guagua.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al escribir el archivo CSS de personalización.']);
    }
    exit;
}

// Cargar configuración previa si existe
$saved_config = [];
if (file_exists($json_file_path)) {
    $saved_config = json_decode(file_get_contents($json_file_path), true);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizador Completo de la Barra del Menú - Guagua</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos base de Guagua -->
    <link rel="stylesheet" href="../../comun/css/estilos_guagua.css">
    
    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --bg-card-hover: #334155;
            --accent: #38bdf8;
            --accent-hover: #0284c7;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* PANEL IZQUIERDO DE CONTROLES */
        .controls-sidebar {
            width: 440px;
            min-width: 440px;
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100%;
            z-index: 100;
            box-shadow: 4px 0 20px rgba(0,0,0,0.3);
        }

        .sidebar-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            background: rgba(15, 23, 42, 0.7);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-header h1 {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header h1 i { color: var(--accent); }

        .btn-back {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: var(--accent); }

        .controls-scroll {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
        }

        /* TITULOS DE SECCION */
        .control-group-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            margin: 22px 0 12px 0;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px dashed rgba(56, 189, 248, 0.2);
            padding-bottom: 6px;
        }
        .control-group-title:first-child { margin-top: 0; }

        .control-item {
            margin-bottom: 14px;
            background: rgba(15, 23, 42, 0.4);
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.05);
        }

        .control-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .control-value {
            font-family: monospace;
            background: #0f172a;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--accent);
            font-weight: bold;
        }

        input[type="range"] {
            width: 100%;
            height: 6px;
            border-radius: 3px;
            background: #334155;
            outline: none;
            -webkit-appearance: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--accent);
            cursor: pointer;
            transition: transform 0.1s, background 0.2s;
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            background: #7dd3fc;
        }

        select, input[type="color"] {
            background: #0f172a;
            color: #fff;
            border: 1px solid #334155;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
        }

        /* PRESETS Y GUARDAR */
        .preset-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-bottom: 16px;
        }

        .btn-preset {
            background: #334155;
            color: #f1f5f9;
            border: none;
            padding: 8px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-preset:hover {
            background: var(--accent);
            color: #0f172a;
        }

        .sidebar-footer {
            padding: 16px 24px;
            background: rgba(15, 23, 42, 0.8);
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 12px;
        }

        .btn-save {
            flex: 1;
            background: var(--accent);
            color: #0f172a;
            border: none;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.1s;
        }
        .btn-save:hover {
            background: #7dd3fc;
            transform: translateY(-1px);
        }

        .btn-reset {
            background: #475569;
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-reset:hover { background: #e11d48; }

        /* AREA DE VISTA PREVIA (DERECHA) */
        .preview-workspace {
            flex: 1;
            background: #cbd5e1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .preview-header-bar {
            background: #0f172a;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--text-muted);
            border-bottom: 1px solid #334155;
        }

        .preview-header-bar .badge {
            background: #10b981;
            color: #064e3b;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .preview-canvas {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .preview-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: visible;
        }

        .preview-card-title {
            padding: 12px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        /* ESTILOS DE LA VISTA PREVIA */
        .preview-navbar-container {
            width: 100%;
            background: #ffffff;
            border-bottom: 2px solid #e8edf3;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            position: relative;
            box-sizing: border-box;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            overflow: visible;
            transition: height 0.2s ease, min-height 0.2s ease;
        }

        .preview-nav-slider-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            flex: 1;
            min-width: 0;
            height: 100%;
            overflow: visible;
        }

        .preview-slider-track {
            display: flex;
            flex-direction: row;
            align-items: center;
            list-style: none;
            height: 100%;
            margin: 0;
            padding: 0;
            gap: 4px;
        }

        .preview-slider-track > li {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            flex-shrink: 0;
        }

        .preview-slider-track > li > a {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-decoration: none;
            box-sizing: border-box;
        }

        .preview-slider-track > li > a > span[class*="icon-sga"] {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            background-position: center top;
            background-repeat: no-repeat;
            margin: 0 auto;
            position: relative;
            box-sizing: border-box;
            transition: transform 0.2s;
        }

        .preview-slider-track > li > a > span[class*="icon-sga"]::after {
            content: attr(data-text);
            display: block;
            font-family: "Soft_Marshmallow", sans-serif;
            font-weight: 700;
            text-align: center;
            white-space: normal;
            word-break: break-word;
            line-height: 1.15;
            width: max-content;
        }

        .preview-slider-track li:nth-child(1n) span[class*="icon-sga"]::after { color: #de3153; }
        .preview-slider-track li:nth-child(2n) span[class*="icon-sga"]::after { color: #faae16; }
        .preview-slider-track li:nth-child(3n) span[class*="icon-sga"]::after { color: #00aceb; }
        .preview-slider-track li:nth-child(4n) span[class*="icon-sga"]::after { color: #f06232; }
        .preview-slider-track li:nth-child(5n) span[class*="icon-sga"]::after { color: #7bba3f; }
        .preview-slider-track li:nth-child(6n) span[class*="icon-sga"]::after { color: #894094; }

        .preview-right-zone {
            display: flex;
            align-items: center;
            height: 100%;
            flex-shrink: 0;
            padding: 0 8px;
        }

        .preview-user-zone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .preview-user-avatar-img {
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #38bdf8;
            box-shadow: 0 3px 8px rgba(0,0,0,0.15);
        }

        .preview-role-badge {
            font-family: "Soft_Marshmallow", sans-serif;
            font-size: 12px;
            font-weight: 700;
            color: #38bdf8;
            margin-top: 3px;
            text-transform: uppercase;
        }

        .preview-body-content {
            padding: 24px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px dashed #cbd5e1;
            margin-top: 10px;
            transition: margin-top 0.2s ease;
        }

        /* TOAST NOTIFICATION */
        #toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #10b981;
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 1000;
        }

        #toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>

    <!-- ESTILOS DINÁMICOS EN TIEMPO REAL -->
    <style id="dynamic-preview-styles"></style>
</head>
<body>

    <!-- PANEL DE CONTROLES -->
    <div class="controls-sidebar">
        <div class="sidebar-header">
            <h1><i class="fa-solid fa-palette"></i> Personalizador de Barra</h1>
            <a href="../../index.php" class="btn-back" title="Volver a Guagua"><i class="fa-solid fa-house"></i> Inicio</a>
        </div>

        <div class="controls-scroll">
            
            <!-- PRESETS -->
            <div class="control-group-title"><i class="fa-solid fa-wand-magic-sparkles"></i> Tamaños Predefinidos</div>
            <div class="preset-buttons">
                <button class="btn-preset" onclick="applyPreset('compact')">Compacto (85px)</button>
                <button class="btn-preset" onclick="applyPreset('standard')">Estándar (115px)</button>
                <button class="btn-preset" onclick="applyPreset('large')">Grande (145px)</button>
                <button class="btn-preset" onclick="applyPreset('giant')">Gigante (175px)</button>
            </div>

            <!-- 1. BARRA PRINCIPAL COMPLETA -->
            <div class="control-group-title"><i class="fa-solid fa-ruler-vertical"></i> 1. Reducción y Altura Real de la Franja Blanca</div>
            
            <div class="control-item">
                <div class="control-label">
                    <span>Altura Real de la Sección Blanca (`.navbar-default`)</span>
                    <span class="control-value" id="val-navbar-height">115px</span>
                </div>
                <input type="range" id="navbar-height" min="40" max="220" value="<?php echo isset($saved_config['navbar_height']) ? $saved_config['navbar_height'] : '115'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Color de Fondo de la Barra</span>
                    <input type="color" id="navbar-bg-color" value="<?php echo isset($saved_config['navbar_bg_color']) ? $saved_config['navbar_bg_color'] : '#ffffff'; ?>" onchange="updatePreview()">
                </div>
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Grosor del Borde Inferior</span>
                    <span class="control-value" id="val-navbar-border">2px</span>
                </div>
                <input type="range" id="navbar-border" min="0" max="10" value="<?php echo isset($saved_config['navbar_border']) ? $saved_config['navbar_border'] : '2'; ?>" oninput="updatePreview()">
            </div>

            <!-- 2. ZONA CENTRO: SLIDER DE ÍCONOS (INCLUYE CONFIG) -->
            <div class="control-group-title"><i class="fa-solid fa-icons"></i> 2. Slider de Íconos (Incluye Configuración)</div>
            
            <div class="control-item">
                <div class="control-label">
                    <span>Alto Contenedor Slider (`.nav-slider-container`)</span>
                    <span class="control-value" id="val-container-height">100%</span>
                </div>
                <input type="range" id="container-height" min="40" max="100" value="<?php echo isset($saved_config['container_height']) ? $saved_config['container_height'] : '100'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Tamaño Imagen de Ícono (`background-size`)</span>
                    <span class="control-value" id="val-icon-img-size">54px</span>
                </div>
                <input type="range" id="icon-img-size" min="16" max="140" value="<?php echo isset($saved_config['icon_img_size']) ? $saved_config['icon_img_size'] : '54'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Padding Superior de Ícono</span>
                    <span class="control-value" id="val-icon-padding-top">56px</span>
                </div>
                <input type="range" id="icon-padding-top" min="16" max="150" value="<?php echo isset($saved_config['icon_padding_top']) ? $saved_config['icon_padding_top'] : '56'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Tamaño de Texto de Opciones (`font-size`)</span>
                    <span class="control-value" id="val-text-size">13px</span>
                </div>
                <input type="range" id="text-size" min="9" max="32" value="<?php echo isset($saved_config['text_size']) ? $saved_config['text_size'] : '13'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Ancho Mínimo de Botón (`min-width`)</span>
                    <span class="control-value" id="val-item-min-width">90px</span>
                </div>
                <input type="range" id="item-min-width" min="40" max="220" value="<?php echo isset($saved_config['item_min_width']) ? $saved_config['item_min_width'] : '90'; ?>" oninput="updatePreview()">
            </div>

            <!-- 3. FOTO DE USUARIO Y ZONA DERECHA REGULABLE -->
            <div class="control-group-title"><i class="fa-solid fa-user"></i> 3. Foto de Usuario y Rol (Regulable por Slider)</div>

            <div class="control-item">
                <div class="control-label">
                    <span>Tamaño Foto de Usuario (`#foto_usuario`)</span>
                    <span class="control-value" id="val-user-photo-size">54px</span>
                </div>
                <input type="range" id="user-photo-size" min="16" max="160" value="<?php echo isset($saved_config['user_photo_size']) ? $saved_config['user_photo_size'] : '54'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Ancho Mínimo del Contenedor de Usuario</span>
                    <span class="control-value" id="val-user-container-min-width">90px</span>
                </div>
                <input type="range" id="user-container-min-width" min="30" max="220" value="<?php echo isset($saved_config['user_container_min_width']) ? $saved_config['user_container_min_width'] : '90'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Grosor del Borde de Foto Usuario</span>
                    <span class="control-value" id="val-user-photo-border">2px</span>
                </div>
                <input type="range" id="user-photo-border" min="0" max="8" value="<?php echo isset($saved_config['user_photo_border']) ? $saved_config['user_photo_border'] : '2'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Color de Borde de Foto Usuario</span>
                    <input type="color" id="user-photo-border-color" value="<?php echo isset($saved_config['user_photo_border_color']) ? $saved_config['user_photo_border_color'] : '#38bdf8'; ?>" onchange="updatePreview()">
                </div>
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Tamaño Texto Etiqueta de Rol (`#area_rol`)</span>
                    <span class="control-value" id="val-user-role-size">12px</span>
                </div>
                <input type="range" id="user-role-size" min="8" max="28" value="<?php echo isset($saved_config['user_role_size']) ? $saved_config['user_role_size'] : '12'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Separación Superior del Rol (`margin-top`)</span>
                    <span class="control-value" id="val-user-role-margin-top">4px</span>
                </div>
                <input type="range" id="user-role-margin-top" min="0" max="20" value="<?php echo isset($saved_config['user_role_margin_top']) ? $saved_config['user_role_margin_top'] : '4'; ?>" oninput="updatePreview()">
            </div>

            <div class="control-item">
                <div class="control-label">
                    <span>Alineación Vertical de Foto Usuario</span>
                    <select id="right-zone-align" onchange="updatePreview()">
                        <option value="center" <?php if(isset($saved_config['right_zone_align']) && $saved_config['right_zone_align']=='center') echo 'selected'; ?>>Centrado</option>
                        <option value="flex-start" <?php if(isset($saved_config['right_zone_align']) && $saved_config['right_zone_align']=='flex-start') echo 'selected'; ?>>Arriba (Top)</option>
                        <option value="flex-end" <?php if(isset($saved_config['right_zone_align']) && $saved_config['right_zone_align']=='flex-end') echo 'selected'; ?>>Abajo (Bottom)</option>
                    </select>
                </div>
            </div>

            <!-- 4. SUBIR/BAJAR PLANTILLA RESTANTE -->
            <div class="control-group-title"><i class="fa-solid fa-up-down"></i> 4. Subir / Bajar Contenido de Plantilla Restante</div>

            <div class="control-item">
                <div class="control-label">
                    <span>Desplazamiento Vertical del Contenido Restante</span>
                    <span class="control-value" id="val-content-offset">0px</span>
                </div>
                <input type="range" id="content-offset" min="-100" max="100" value="<?php echo isset($saved_config['content_offset']) ? $saved_config['content_offset'] : '0'; ?>" oninput="updatePreview()">
            </div>

        </div>

        <div class="sidebar-footer">
            <button class="btn-reset" onclick="applyPreset('standard')" title="Restaurar a Estándar (115px)"><i class="fa-solid fa-rotate-left"></i></button>
            <button class="btn-save" onclick="saveConfiguration()"><i class="fa-solid fa-floppy-disk"></i> Guardar en Guagua</button>
        </div>
    </div>

    <!-- AREA DE VISTA PREVIA -->
    <div class="preview-workspace">
        <div class="preview-header-bar">
            <span><i class="fa-solid fa-eye"></i> Vista Previa en Tiempo Real de Toda la Barra del Menú</span>
            <span class="badge"><i class="fa-solid fa-circle-check"></i> En Vivo</span>
        </div>

        <div class="preview-canvas">
            <div class="preview-card">
                <div class="preview-card-title">Previsualización Completa Regulable (Ícono CONFIG dentro del Slider + Foto Usuario)</div>
                
                <!-- RENDER REAL DEL MENU COMPLETO -->
                <div class="preview-navbar-container" id="preview-navbar">
                    <!-- ZONA IZQUIERDA / CENTRO -->
                    <div class="preview-nav-slider-container" id="preview-slider-container">
                        <ul class="preview-slider-track">
                            <li><a href="#"><span data-text="INICIO" class="icon-sga-house"></span></a></li>
                            <li><a href="#"><span data-text="CURSOS" class="icon-sga-notebook"></span></a></li>
                            <li><a href="#"><span data-text="RED" class="icon-sga-app"></span></a></li>
                            <li><a href="#"><span data-text="APPS" class="icon-sga-smartphone-7"></span></a></li>
                            <li><a href="#"><span data-text="DATOS" class="icon-sga-time"></span></a></li>
                            <!-- CONFIG DENTRO DEL SLIDER -->
                            <li><a href="#"><span data-text="CONFIG" class="icon-sga-settings-4"></span></a></li>
                        </ul>
                    </div>

                    <!-- ZONA DERECHA (FOTO DE USUARIO Y ROL REGULABLE POR SLIDER) -->
                    <div class="preview-right-zone" id="preview-right-zone">
                        <div class="preview-user-zone" id="preview-user-zone">
                            <img src="../../comun/img/png/icono-usuario-default.png" onerror="this.src='https://ui-avatars.com/api/?name=Andres+Paz&background=38bdf8&color=fff';" id="preview-user-img" class="preview-user-avatar-img" alt="Foto Usuario">
                            <span class="preview-role-badge" id="preview-role-badge">docente</span>
                        </div>
                    </div>
                </div>

                <div class="preview-body-content" id="preview-body-content">
                    <p style="color:#64748b;font-weight:600;font-size:14px;"><i class="fa-solid fa-layer-group"></i> Contenido Principal de la Plantilla de Guagua</p>
                    <p style="color:#94a3b8;font-size:12px;margin-top:4px;">Este bloque simula el resto de la página subiendo o bajando en tiempo real al reducir la franja blanca.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div id="toast"><i class="fa-solid fa-circle-check"></i> <span id="toast-message">Guardado con éxito</span></div>

    <script>
        // PRESETS RÁPIDOS
        const presets = {
            compact: {
                navbar_height: 85,
                navbar_bg_color: '#ffffff',
                navbar_border: 2,
                container_height: 100,
                icon_img_size: 38,
                icon_padding_top: 40,
                text_size: 11,
                item_min_width: 75,
                user_photo_size: 38,
                user_container_min_width: 75,
                user_photo_border: 2,
                user_photo_border_color: '#38bdf8',
                user_role_size: 11,
                user_role_margin_top: 3,
                right_zone_align: 'center',
                content_offset: 0
            },
            standard: {
                navbar_height: 115,
                navbar_bg_color: '#ffffff',
                navbar_border: 2,
                container_height: 100,
                icon_img_size: 54,
                icon_padding_top: 56,
                text_size: 13,
                item_min_width: 90,
                user_photo_size: 54,
                user_container_min_width: 90,
                user_photo_border: 2,
                user_photo_border_color: '#38bdf8',
                user_role_size: 12,
                user_role_margin_top: 4,
                right_zone_align: 'center',
                content_offset: 0
            },
            large: {
                navbar_height: 145,
                navbar_bg_color: '#ffffff',
                navbar_border: 2,
                container_height: 100,
                icon_img_size: 76,
                icon_padding_top: 80,
                text_size: 16,
                item_min_width: 115,
                user_photo_size: 76,
                user_container_min_width: 115,
                user_photo_border: 3,
                user_photo_border_color: '#38bdf8',
                user_role_size: 14,
                user_role_margin_top: 5,
                right_zone_align: 'center',
                content_offset: 0
            },
            giant: {
                navbar_height: 175,
                navbar_bg_color: '#ffffff',
                navbar_border: 3,
                container_height: 100,
                icon_img_size: 96,
                icon_padding_top: 100,
                text_size: 20,
                item_min_width: 140,
                user_photo_size: 96,
                user_container_min_width: 140,
                user_photo_border: 4,
                user_photo_border_color: '#38bdf8',
                user_role_size: 17,
                user_role_margin_top: 6,
                right_zone_align: 'center',
                content_offset: 0
            }
        };

        function applyPreset(name) {
            const p = presets[name];
            if (!p) return;
            document.getElementById('navbar-height').value = p.navbar_height;
            document.getElementById('navbar-bg-color').value = p.navbar_bg_color;
            document.getElementById('navbar-border').value = p.navbar_border;
            document.getElementById('container-height').value = p.container_height;
            document.getElementById('icon-img-size').value = p.icon_img_size;
            document.getElementById('icon-padding-top').value = p.icon_padding_top;
            document.getElementById('text-size').value = p.text_size;
            document.getElementById('item-min-width').value = p.item_min_width;
            document.getElementById('user-photo-size').value = p.user_photo_size;
            document.getElementById('user-container-min-width').value = p.user_container_min_width;
            document.getElementById('user-photo-border').value = p.user_photo_border;
            document.getElementById('user-photo-border-color').value = p.user_photo_border_color;
            document.getElementById('user-role-size').value = p.user_role_size;
            document.getElementById('user-role-margin-top').value = p.user_role_margin_top;
            document.getElementById('right-zone-align').value = p.right_zone_align;
            document.getElementById('content-offset').value = p.content_offset;
            
            updatePreview();
        }

        function generateCSS() {
            const navHeight = parseInt(document.getElementById('navbar-height').value);
            const navBgColor = document.getElementById('navbar-bg-color').value;
            const navBorder = document.getElementById('navbar-border').value;
            const containerHeight = document.getElementById('container-height').value;
            let iconImgSize = parseInt(document.getElementById('icon-img-size').value);
            let iconPaddingTop = parseInt(document.getElementById('icon-padding-top').value);
            const textSize = parseInt(document.getElementById('text-size').value);
            const itemMinWidth = document.getElementById('item-min-width').value;
            const userPhotoSize = document.getElementById('user-photo-size').value;
            const userContainerMinWidth = document.getElementById('user-container-min-width').value;
            const userPhotoBorder = document.getElementById('user-photo-border').value;
            const userPhotoBorderColor = document.getElementById('user-photo-border-color').value;
            const userRoleSize = document.getElementById('user-role-size').value;
            const userRoleMarginTop = document.getElementById('user-role-margin-top').value;
            const rightZoneAlign = document.getElementById('right-zone-align').value;
            const contentOffset = document.getElementById('content-offset').value;

            // ADAPTACIÓN INTELIGENTE DE SEGURIDAD PARA REDUCIR SIEMPRE LA SECCIÓN BLANCA:
            const maxAllowedPadding = Math.max(16, navHeight - textSize - 25);
            if (iconPaddingTop > maxAllowedPadding) {
                iconPaddingTop = maxAllowedPadding;
                iconImgSize = Math.max(16, iconPaddingTop - 2);
            }

            // Actualizar etiquetas numéricas
            document.getElementById('val-navbar-height').textContent = navHeight + 'px';
            document.getElementById('val-navbar-border').textContent = navBorder + 'px';
            document.getElementById('val-container-height').textContent = containerHeight + '%';
            document.getElementById('val-icon-img-size').textContent = iconImgSize + 'px';
            document.getElementById('val-icon-padding-top').textContent = iconPaddingTop + 'px';
            document.getElementById('val-text-size').textContent = textSize + 'px';
            document.getElementById('val-item-min-width').textContent = itemMinWidth + 'px';
            document.getElementById('val-user-photo-size').textContent = userPhotoSize + 'px';
            document.getElementById('val-user-container-min-width').textContent = userContainerMinWidth + 'px';
            document.getElementById('val-user-photo-border').textContent = userPhotoBorder + 'px';
            document.getElementById('val-user-role-size').textContent = userRoleSize + 'px';
            document.getElementById('val-user-role-margin-top').textContent = userRoleMarginTop + 'px';
            document.getElementById('val-content-offset').textContent = contentOffset + 'px';

            const css = `
/* =========================================================
   ESTILOS DINÁMICOS COMPLETOS DEL MENÚ GUAGUA
   ========================================================= */

/* 1. REDUCCIÓN REAL Y ALTURA DE LA BARRA BLANCA SUPERIOR */
.navbar.navbar-default,
.navbar.navbar-default .navbar-collapse.navbar-ex1-collapse {
    height: ${navHeight}px !important;
    min-height: ${navHeight}px !important;
    max-height: ${navHeight}px !important;
    background-color: ${navBgColor} !important;
    border-bottom: ${navBorder}px solid #e8edf3 !important;
}

/* 2. ZONA SLIDER DE ÍCONOS (INCLUYE ÍCONO CONFIG DESLIZABLE) */
.nav-slider-container {
    height: ${containerHeight}% !important;
    max-height: ${containerHeight}% !important;
}

.nav.navbar-nav > li > a,
.slider-track > li > a {
    min-height: calc(${navHeight}px - 10px) !important;
    padding: 2px 6px !important;
    min-width: ${itemMinWidth}px !important;
}

.nav.navbar-nav > li > a > span[class*="icon-sga"],
.slider-track > li > a > span[class*="icon-sga"] {
    width: calc(${iconImgSize}px + 4px) !important;
    min-height: ${iconPaddingTop}px !important;
    padding-top: ${iconPaddingTop}px !important;
    background-size: ${iconImgSize}px ${iconImgSize}px !important;
}

.nav.navbar-nav > li > a > span[class*="icon-sga"]::after,
.slider-track > li > a > span[class*="icon-sga"]::after {
    font-size: ${textSize}px !important;
}

/* 3. ZONA DERECHA COMPLETA (FOTO DE USUARIO Y ROL REGULABLE POR SLIDER) */
.nav.navbar-nav.navbar-right,
.nav.navbar-nav.navbar-right > li {
    height: 100% !important;
    align-items: ${rightZoneAlign} !important;
    padding: 0 6px !important;
}

#estilo_foto_usuario_menu {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: ${userContainerMinWidth}px !important;
    height: 100% !important;
}

#foto_usuario, #foto_usuario_hijo {
    width: ${userPhotoSize}px !important;
    height: ${userPhotoSize}px !important;
    max-width: ${userPhotoSize}px !important;
    border: ${userPhotoBorder}px solid ${userPhotoBorderColor} !important;
    border-radius: 50% !important;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15) !important;
}

#area_rol {
    font-family: "Soft_Marshmallow", sans-serif !important;
    font-size: ${userRoleSize}px !important;
    font-weight: 700 !important;
    text-align: center !important;
    color: ${userPhotoBorderColor} !important;
    margin-top: ${userRoleMarginTop}px !important;
    display: block !important;
    visibility: visible !important;
}

/* 4. DESPLAZAMIENTO Y SUBIDA DEL CONTENIDO RESTANTE DE LA PLANTILLA */
body > .container, 
body > .container-fluid,
.main-content,
#contenido {
    margin-top: ${contentOffset}px !important;
}
`;
            return { css, config: {
                navbar_height: navHeight,
                navbar_bg_color: navBgColor,
                navbar_border: navBorder,
                container_height: containerHeight,
                icon_img_size: iconImgSize,
                icon_padding_top: iconPaddingTop,
                text_size: textSize,
                item_min_width: itemMinWidth,
                user_photo_size: userPhotoSize,
                user_container_min_width: userContainerMinWidth,
                user_photo_border: userPhotoBorder,
                user_photo_border_color: userPhotoBorderColor,
                user_role_size: userRoleSize,
                user_role_margin_top: userRoleMarginTop,
                right_zone_align: rightZoneAlign,
                content_offset: contentOffset
            }};
        }

        function updatePreview() {
            const data = generateCSS();
            
            // Inyectar CSS dinámico en la vista previa
            const previewCSS = `
                .preview-navbar-container { 
                    height: ${data.config.navbar_height}px !important; 
                    background-color: ${data.config.navbar_bg_color} !important;
                    border-bottom-width: ${data.config.navbar_border}px !important;
                }
                .preview-nav-slider-container { height: ${data.config.container_height}% !important; }
                .preview-slider-track > li > a { 
                    min-width: ${data.config.item_min_width}px !important;
                }
                .preview-slider-track > li > a > span[class*="icon-sga"] {
                    width: ${parseInt(data.config.icon_img_size) + 4}px !important;
                    min-height: ${data.config.icon_padding_top}px !important;
                    padding-top: ${data.config.icon_padding_top}px !important;
                    background-size: ${data.config.icon_img_size}px ${data.config.icon_img_size}px !important;
                }
                .preview-slider-track > li > a > span[class*="icon-sga"]::after {
                    font-size: ${data.config.text_size}px !important;
                }
                .preview-right-zone {
                    height: 100% !important;
                    align-items: ${data.config.right_zone_align} !important;
                }
                #preview-user-zone {
                    min-width: ${data.config.user_container_min_width}px !important;
                }
                #preview-user-img {
                    width: ${data.config.user_photo_size}px !important;
                    height: ${data.config.user_photo_size}px !important;
                    border-width: ${data.config.user_photo_border}px !important;
                    border-color: ${data.config.user_photo_border_color} !important;
                }
                #preview-role-badge {
                    font-size: ${data.config.user_role_size}px !important;
                    margin-top: ${data.config.user_role_margin_top}px !important;
                    color: ${data.config.user_photo_border_color} !important;
                }
                #preview-body-content {
                    margin-top: ${data.config.content_offset}px !important;
                }
            `;
            document.getElementById('dynamic-preview-styles').textContent = previewCSS;
        }

        function saveConfiguration() {
            const data = generateCSS();
            const formData = new FormData();
            formData.append('action', 'save_css');
            formData.append('css_content', data.css);
            formData.append('config_json', JSON.stringify(data.config));

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    showToast(res.message);
                } else {
                    alert('Error: ' + res.message);
                }
            })
            .catch(err => {
                alert('Error al guardar la configuración');
                console.error(err);
            });
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            document.getElementById('toast-message').textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3500);
        }

        document.addEventListener('DOMContentLoaded', updatePreview);
    </script>
</body>
</html>
