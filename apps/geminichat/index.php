<?php
/**
 * Módulo Gemini Chat Premium - Guagua 2026
 * Stack: PHP + Tailwind CSS + Vanilla JS (ES6) + Gemini API REST
 * Autor: Antigravity AI
 */

require '../../comun/conexion.php'; // Incluido para mantener la consistencia del sistema

// API key por defecto del servidor
$default_api_key = 'AIzaSyB1ZbitpmioDkWOPWOlHJ-p_SORmxUYUrM';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guagua Gemini Chat - Asistente Inteligente</title>
    
    <!-- Google Fonts - Outfit para el cuerpo y JetBrains Mono para código -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS para el diseño ágil -->
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    
    <!-- FontAwesome 6 para iconos modernos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Marked.js para renderizar Markdown -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <!-- Highlight.js para colorear sintaxis de código -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/github-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        'neon-indigo': '0 0 20px rgba(99, 102, 241, 0.15)',
                        'neon-emerald': '0 0 20px rgba(16, 185, 129, 0.15)',
                        'glass': 'inset 0 1px 0 0 rgba(255, 255, 255, 0.05)',
                    }
                }
            }
        }
    </script>

    <style>
        /* Personalizaciones premium que escapan a Tailwind standard */
        body {
            background-color: #0b0f19;
            scrollbar-color: #312e81 transparent;
            scrollbar-width: thin;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e1b4b;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #312e81;
        }

        /* Efecto de cristalización (Glassmorphism) */
        .glass-card {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-input {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Animaciones */
        @keyframes message-pop {
            0% { opacity: 0; transform: translateY(12px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .animate-message {
            animation: message-pop 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* Bouncing Dots Loading Animation */
        .dot-pulse span {
            width: 8px;
            height: 8px;
            margin: 0 3px;
            background-color: #a5b4fc;
            border-radius: 50%;
            display: inline-block;
            animation: dot-pulse-anim 1.4s infinite ease-in-out both;
        }
        .dot-pulse span:nth-child(1) { animation-delay: -0.32s; }
        .dot-pulse span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes dot-pulse-anim {
            0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* Markdown styling overrides para combinar con el tema oscuro */
        .markdown-content p { margin-bottom: 0.85rem; line-height: 1.625; }
        .markdown-content p:last-child { margin-bottom: 0; }
        .markdown-content ul, .markdown-content ol { margin-left: 1.5rem; margin-bottom: 0.85rem; list-style-type: decimal; }
        .markdown-content ul { list-style-type: disc; }
        .markdown-content li { margin-bottom: 0.25rem; }
        .markdown-content strong { color: #fff; font-weight: 600; }
        .markdown-content code { font-family: 'JetBrains Mono', monospace; font-size: 0.875rem; background: rgba(255, 255, 255, 0.1); padding: 0.15rem 0.35rem; border-radius: 0.25rem; color: #a5b4fc; }
        .markdown-content pre code { background: transparent; padding: 0; color: inherit; }
        .markdown-content h1, .markdown-content h2, .markdown-content h3, .markdown-content h4 { color: #fff; font-weight: 700; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .markdown-content h1 { font-size: 1.5rem; }
        .markdown-content h2 { font-size: 1.3rem; }
        .markdown-content h3 { font-size: 1.15rem; }
        .markdown-content table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .markdown-content th, .markdown-content td { border: 1px solid rgba(255, 255, 255, 0.1); padding: 0.5rem 0.75rem; text-align: left; }
        .markdown-content th { background: rgba(99, 102, 241, 0.1); color: #fff; }
    </style>
</head>
<body class="text-slate-200 h-screen flex flex-col md:flex-row overflow-hidden antialiased">

    <!-- BARRA LATERAL (GESTIÓN DE CONVERSACIONES) -->
    <aside id="sidebar" class="w-full md:w-80 shrink-0 flex flex-col h-[30vh] md:h-full bg-slate-950/90 border-b md:border-b-0 md:border-r border-slate-900 z-30 transition-all duration-300">
        
        <!-- Header del Sidebar -->
        <div class="p-4 flex items-center justify-between border-b border-slate-900 bg-slate-950">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white shadow-neon-indigo">
                    <i class="fa-solid fa-wand-magic-sparkles text-lg animate-pulse"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-white leading-none tracking-tight">Guagua Gemini</h1>
                    <span class="text-[10px] text-indigo-400 font-semibold tracking-wider uppercase">Estudio Cognitivo</span>
                </div>
            </div>
            <!-- Botón Colapsar Sidebar en Escritorio (Visual) -->
            <button id="btn-toggle-sidebar" class="hidden md:flex p-1.5 rounded-lg text-slate-500 hover:bg-slate-900 hover:text-white transition" title="Ocultar menú">
                <i class="fa-solid fa-bars-staggered"></i>
            </button>
        </div>

        <!-- Botón Nueva Conversación -->
        <div class="p-4">
            <button id="btn-new-chat" class="w-full py-3 px-4 bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 hover:from-violet-500 hover:to-indigo-600 text-white rounded-xl font-semibold text-sm shadow-neon-indigo flex items-center justify-center gap-2 transition-all duration-300 active:scale-95 group">
                <i class="fa-solid fa-plus transition-transform group-hover:rotate-90"></i>
                Nueva Conversación
            </button>
        </div>

        <!-- Lista de Chats (Scrollable) -->
        <div class="flex-1 overflow-y-auto px-3 space-y-1.5 no-scrollbar" id="chat-list-container">
            <!-- Cargado dinámicamente desde JS -->
        </div>

        <!-- Footer del Sidebar -->
        <div class="p-4 border-t border-slate-900 bg-slate-950 flex flex-col gap-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 rounded-full bg-slate-800 flex items-center justify-center border border-slate-700">
                        <i class="fa-solid fa-user text-slate-400 text-sm"></i>
                    </div>
                    <div class="leading-none">
                        <span class="text-xs font-bold text-slate-200">Docente</span>
                        <span class="block text-[9px] text-slate-500">Sesión Activa</span>
                    </div>
                </div>
                <button id="btn-open-settings-footer" class="p-2 rounded-lg text-slate-400 hover:bg-slate-900 hover:text-indigo-400 transition" title="Configurar API">
                    <i class="fa-solid fa-cog"></i>
                </button>
            </div>
            
            <a href="../../index.php" class="text-center py-2 px-3 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs font-medium text-slate-400 hover:text-white transition flex items-center justify-center gap-1.5 border border-slate-800">
                <i class="fa-solid fa-arrow-left"></i> Volver al Portal SGA
            </a>
        </div>
    </aside>

    <!-- VENTANA PRINCIPAL (CHAT) -->
    <main class="flex-1 flex flex-col h-[70vh] md:h-full bg-gradient-to-b from-slate-950 to-slate-900 relative">
        
        <!-- HEADER DEL CHAT ACTIVO -->
        <header class="h-16 border-b border-slate-900/60 flex items-center justify-between px-6 z-10 bg-slate-950/80 backdrop-blur-md">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="md:hidden flex h-8 w-8 rounded-lg bg-indigo-950 text-indigo-400 items-center justify-center" id="btn-mobile-sidebar-toggle">
                    <i class="fa-solid fa-comments"></i>
                </div>
                <div class="leading-none overflow-hidden">
                    <h2 id="active-chat-title" class="text-sm md:text-base font-bold text-white truncate max-w-[200px] md:max-w-md">Conversación</h2>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <span id="active-chat-model-badge" class="text-[10px] text-indigo-400 font-bold uppercase tracking-wider">gemini-2.0-flash</span>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción Superior -->
            <div class="flex items-center gap-2">
                <button id="btn-clear-chat" class="p-2 rounded-lg text-slate-400 hover:bg-slate-900 hover:text-red-400 transition" title="Limpiar conversación actual">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
                <button id="btn-open-settings" class="px-3.5 py-1.5 bg-indigo-950/60 hover:bg-indigo-900 text-indigo-300 hover:text-white rounded-lg text-xs font-bold transition flex items-center gap-2 border border-indigo-900/40">
                    <i class="fa-solid fa-sliders"></i>
                    Configurar IA
                </button>
            </div>
        </header>

        <!-- ESPACIO DE MENSAJES -->
        <div id="messages-viewport" class="flex-1 overflow-y-auto px-4 md:px-8 py-6 space-y-6">
            
            <!-- VISTA DE BIENVENIDA (Si no hay mensajes) -->
            <div id="welcome-view" class="h-full flex flex-col items-center justify-center max-w-2xl mx-auto text-center py-10 space-y-8 select-none">
                <div class="relative flex items-center justify-center">
                    <div class="absolute inset-0 h-20 w-20 bg-indigo-500/20 rounded-full filter blur-xl animate-pulse"></div>
                    <i class="fa-solid fa-sparkles text-5xl text-transparent bg-clip-text bg-gradient-to-tr from-violet-400 to-indigo-400 animate-bounce"></i>
                </div>
                
                <div class="space-y-2">
                    <h3 class="text-xl md:text-2xl font-black text-white">¿En qué puedo ayudarte hoy?</h3>
                    <p class="text-xs md:text-sm text-slate-400 max-w-md mx-auto leading-relaxed">
                        Pregúntame sobre diseño de guías didácticas, resúmenes, exámenes, resolución de dudas o lo que necesites para tu labor docente.
                    </p>
                </div>

                <!-- Tarjetas Rápidas / Presets -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 w-full pt-4">
                    <button class="preset-card glass-card p-4 rounded-xl text-left hover:border-indigo-500/30 hover:bg-indigo-950/20 hover:scale-[1.02] transition-all group duration-300">
                        <div class="text-indigo-400 mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-file-invoice text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white mb-1">Planificador de Clases</h4>
                        <p class="text-[10px] text-slate-400 leading-normal">"Crea una estructura de clase sobre Fracciones para grado 6 en el sector rural..."</p>
                    </button>
                    
                    <button class="preset-card glass-card p-4 rounded-xl text-left hover:border-violet-500/30 hover:bg-violet-950/20 hover:scale-[1.02] transition-all group duration-300">
                        <div class="text-violet-400 mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-lightbulb text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white mb-1">Ideas Didácticas</h4>
                        <p class="text-[10px] text-slate-400 leading-normal">"Dame 3 proyectos interactivos y lúdicos para enseñar geografía sin recursos..."</p>
                    </button>
                    
                    <button class="preset-card glass-card p-4 rounded-xl text-left hover:border-emerald-500/30 hover:bg-emerald-950/20 hover:scale-[1.02] transition-all group duration-300">
                        <div class="text-emerald-400 mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-circle-question text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white mb-1">Generar Examen ICFES</h4>
                        <p class="text-[10px] text-slate-400 leading-normal">"Crea 2 preguntas ICFES con opciones y retroalimentación sobre física de fluidos..."</p>
                    </button>
                    
                    <button class="preset-card glass-card p-4 rounded-xl text-left hover:border-amber-500/30 hover:bg-amber-950/20 hover:scale-[1.02] transition-all group duration-300">
                        <div class="text-amber-400 mb-2 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-code text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-white mb-1">Consultar Programación</h4>
                        <p class="text-[10px] text-slate-400 leading-normal">"Escribe un script en PHP para validar correos y explícame su lógica..."</p>
                    </button>
                </div>
            </div>

            <!-- CONTENEDOR DE MENSAJES ACTIVOS -->
            <div id="messages-container" class="space-y-6 max-w-4xl mx-auto">
                <!-- Burbujas renderizadas dinámicamente -->
            </div>

            <!-- INDICADOR DE CARGA (AI Pensando) -->
            <div id="ai-typing-indicator" class="hidden flex items-start gap-4 animate-message max-w-4xl mx-auto">
                <div class="h-9 w-9 rounded-xl bg-indigo-950 text-indigo-400 border border-indigo-900/50 flex items-center justify-center flex-shrink-0 shadow-neon-indigo">
                    <i class="fa-solid fa-brain"></i>
                </div>
                <div class="flex-1 bg-indigo-950/20 border border-indigo-900/30 p-4 rounded-2xl max-w-[85%]">
                    <div class="dot-pulse">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- ÁREA DE ESCRITURA -->
        <footer class="p-4 md:p-6 border-t border-slate-900/60 bg-slate-950/50 backdrop-blur-md">
            <div class="max-w-4xl mx-auto relative">
                
                <!-- Input principal del chat -->
                <div class="glass-input rounded-2xl flex items-end p-2 pr-3 focus-within:ring-2 focus-within:ring-indigo-600/50 focus-within:border-indigo-500/30 transition-all duration-300">
                    <textarea 
                        id="chat-textarea" 
                        rows="1" 
                        class="flex-1 bg-transparent border-0 outline-none focus:ring-0 focus:outline-none py-2 px-3 text-slate-100 placeholder-slate-500 text-sm md:text-base resize-none max-h-48 overflow-y-auto"
                        placeholder="Escribe tu mensaje a Gemini..."
                    ></textarea>
                    
                    <div class="flex items-center gap-1.5 pb-1">
                        <button id="btn-send-message" class="h-10 w-10 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white flex items-center justify-center shadow-neon-indigo disabled:opacity-30 disabled:scale-100 transition-all active:scale-90" disabled>
                            <i class="fa-solid fa-paper-plane text-sm"></i>
                        </button>
                    </div>
                </div>
                
                <div class="flex items-center justify-between mt-2 px-2">
                    <span class="text-[10px] text-slate-500">
                        Shift + Enter para salto de línea
                    </span>
                    
                    <span id="char-indicator" class="text-[10px] text-slate-500">
                        0 caracteres
                    </span>
                </div>
            </div>
        </footer>

    </main>

    <!-- MODAL DE CONFIGURACIÓN DE IA -->
    <div id="settings-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden p-4">
        <!-- Backdrop Blur -->
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" id="settings-modal-backdrop"></div>
        
        <!-- Contenido del Modal -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-xl p-6 relative z-10 max-h-[90vh] overflow-y-auto animate-message">
            
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="h-9 w-9 rounded-xl bg-indigo-950 text-indigo-400 flex items-center justify-center">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-white">Configuración del Motor Gemini</h3>
                        <p class="text-[10px] text-slate-400">Ajusta los parámetros y la personalidad de la IA</p>
                    </div>
                </div>
                <button id="btn-close-settings" class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Formulario de Configuración -->
            <div class="space-y-4">
                
                <!-- API Key -->
                <div class="bg-indigo-950/20 border border-indigo-900/30 p-4 rounded-xl">
                    <label class="block text-xs font-bold text-indigo-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        API Key de Gemini
                        <span class="text-[9px] text-slate-500 capitalize font-medium">Requerida para solicitudes</span>
                    </label>
                    <div class="relative flex items-center">
                        <input 
                            type="password" 
                            id="input-api-key" 
                            class="w-full bg-slate-950/60 border border-slate-800 rounded-lg py-2 px-3 pr-10 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-slate-200 outline-none"
                            placeholder="AIzaSy..."
                        >
                        <button id="btn-toggle-apikey-visibility" class="absolute right-3 text-slate-500 hover:text-slate-300 transition text-sm">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <p class="text-[9px] text-slate-500 mt-1">
                        Por defecto se utiliza la clave centralizada del servidor. Si tienes tu propia clave y la ingresas aquí, se guardará en tu navegador de forma segura.
                    </p>
                </div>

                <!-- Selección de Modelo -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Modelo de Inteligencia Artificial
                    </label>
                    <select 
                        id="select-model" 
                        class="w-full bg-slate-950/60 border border-slate-800 rounded-lg py-2.5 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-slate-200 outline-none"
                    >
                        <option value="gemini-2.0-flash">gemini-2.0-flash (Recomendado - Ultra rápido e inteligente)</option>
                        <option value="gemini-1.5-flash">gemini-1.5-flash (Balanceado - Respuestas eficientes)</option>
                        <option value="gemini-1.5-pro">gemini-1.5-pro (Avanzado - Razonamiento de alta complejidad)</option>
                    </select>
                </div>

                <!-- System Instruction (Rol del Asistente) -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                        Instrucción del Sistema (Rol)
                        <span class="text-[10px] text-indigo-400 font-semibold cursor-pointer hover:underline" id="btn-clear-system-prompt">Restaurar</span>
                    </label>
                    <textarea 
                        id="textarea-system-prompt" 
                        rows="4" 
                        class="w-full bg-slate-950/60 border border-slate-800 rounded-lg py-2.5 px-3 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 text-slate-200 outline-none resize-y"
                        placeholder="Define las pautas generales del comportamiento de Gemini..."
                    ></textarea>
                    
                    <!-- Accesos Rápidos para System Prompt -->
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="text-[9px] text-slate-400 font-bold uppercase mr-1 self-center">Presets:</span>
                        <button class="preset-sys-btn px-2 py-1 bg-slate-950/50 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded text-[10px] font-medium text-slate-300 transition" data-prompt="Actúa como un docente experto en diseño curricular. Estructura guías didácticas detalladas de forma pedagógica, incluyendo objetivos, secuencias y metodologías interactivas.">
                            Docente Experto
                        </button>
                        <button class="preset-sys-btn px-2 py-1 bg-slate-950/50 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded text-[10px] font-medium text-slate-300 transition" data-prompt="Eres un asistente enfocado en redactar evaluaciones y exámenes académicos de alta calidad. Utiliza estándares como el estilo ICFES colombiano (preguntas de selección múltiple con única respuesta, enunciados analíticos y justificaciones pedagógicas).">
                            Redactor ICFES
                        </button>
                        <button class="preset-sys-btn px-2 py-1 bg-slate-950/50 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded text-[10px] font-medium text-slate-300 transition" data-prompt="Eres un programador senior experto en PHP, SQL, Javascript y maquetación web. Responde escribiendo códigos limpios, modulares y seguros, y explica el funcionamiento de forma concisa.">
                            Programador Senior
                        </button>
                        <button class="preset-sys-btn px-2 py-1 bg-slate-950/50 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 rounded text-[10px] font-medium text-slate-300 transition" data-prompt="Eres un asistente virtual general e inteligente. Responde de forma clara, amable, servicial y resumida en español.">
                            Asistente General
                        </button>
                    </div>
                </div>

            </div>

            <!-- Footer del Modal -->
            <div class="flex justify-end gap-3 border-t border-slate-800 pt-4 mt-6">
                <button id="btn-save-settings" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl text-xs font-extrabold shadow-neon-indigo transition-all duration-300">
                    Guardar y Aplicar
                </button>
            </div>
        </div>
    </div>

    <!-- JS LOGIC - TOTALMENTE ROBUSTA -->
    <script>
        // Clave del servidor (PHP)
        const DEFAULT_API_KEY = "<?php echo $default_api_key; ?>";
        
        // Configuración por defecto de un chat
        const DEFAULT_SYSTEM_PROMPT = "Eres un asistente virtual general e inteligente. Responde de forma clara, amable, servicial y precisa en español.";
        const DEFAULT_MODEL = "gemini-2.0-flash";

        // Variables de Estado global del cliente
        let conversations = [];
        let activeChatId = null;
        let isWaitingForAI = false;

        // Elementos DOM
        const chatListContainer = document.getElementById('chat-list-container');
        const activeChatTitle = document.getElementById('active-chat-title');
        const activeChatModelBadge = document.getElementById('active-chat-model-badge');
        
        const btnNewChat = document.getElementById('btn-new-chat');
        const btnClearChat = document.getElementById('btn-clear-chat');
        
        const welcomeView = document.getElementById('welcome-view');
        const messagesViewport = document.getElementById('messages-viewport');
        const messagesContainer = document.getElementById('messages-container');
        
        const chatTextarea = document.getElementById('chat-textarea');
        const btnSendMessage = document.getElementById('btn-send-message');
        const charIndicator = document.getElementById('char-indicator');
        
        const aiTypingIndicator = document.getElementById('ai-typing-indicator');

        // Config Modal DOM
        const settingsModal = document.getElementById('settings-modal');
        const settingsModalBackdrop = document.getElementById('settings-modal-backdrop');
        const btnOpenSettings = document.getElementById('btn-open-settings');
        const btnOpenSettingsFooter = document.getElementById('btn-open-settings-footer');
        const btnCloseSettings = document.getElementById('btn-close-settings');
        const btnSaveSettings = document.getElementById('btn-save-settings');
        
        const inputApiKey = document.getElementById('input-api-key');
        const btnToggleApiKeyVisibility = document.getElementById('btn-toggle-apikey-visibility');
        const selectModel = document.getElementById('select-model');
        const textareaSystemPrompt = document.getElementById('textarea-system-prompt');
        const btnClearSystemPrompt = document.getElementById('btn-clear-system-prompt');

        // Sidebar responsive
        const sidebar = document.getElementById('sidebar');
        const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
        const btnMobileSidebarToggle = document.getElementById('btn-mobile-sidebar-toggle');

        // Inicialización al cargar la página
        window.addEventListener('DOMContentLoaded', () => {
            loadConversations();
            
            // Si no hay conversaciones previas, crear una inicial
            if (conversations.length === 0) {
                createConversation("Conversación Inicial");
            } else {
                // Seleccionar la última conversación activa o la primera de la lista
                const savedActiveId = localStorage.getItem('gemini_active_chat_id');
                const activeExists = conversations.some(c => c.id === savedActiveId);
                selectConversation(activeExists ? savedActiveId : conversations[0].id);
            }

            // Setup de sintaxis de resaltado
            hljs.configure({ ignoreUnescapedHTML: true });
        });

        // ==========================================
        // 1. CARGA Y PERSISTENCIA (LOCALSTORAGE)
        // ==========================================
        
        function loadConversations() {
            try {
                const stored = localStorage.getItem('gemini_conversations');
                if (stored) {
                    conversations = JSON.parse(stored);
                } else {
                    conversations = [];
                }
            } catch (err) {
                console.error("Error al cargar chats de localStorage:", err);
                conversations = [];
            }
        }

        function saveConversations() {
            try {
                localStorage.setItem('gemini_conversations', JSON.stringify(conversations));
                if (activeChatId) {
                    localStorage.setItem('gemini_active_chat_id', activeChatId);
                }
            } catch (err) {
                console.error("Error al guardar chats en localStorage:", err);
            }
        }

        // ==========================================
        // 2. GESTIÓN DE CONVERSACIONES
        // ==========================================

        function createConversation(title = "Nueva Conversación") {
            const newChat = {
                id: 'chat_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                title: title,
                systemPrompt: DEFAULT_SYSTEM_PROMPT,
                model: DEFAULT_MODEL,
                messages: []
            };

            conversations.unshift(newChat); // Colocar al inicio
            saveConversations();
            renderChatList();
            selectConversation(newChat.id);
        }

        function selectConversation(id) {
            activeChatId = id;
            localStorage.setItem('gemini_active_chat_id', id);
            
            const activeChat = conversations.find(c => c.id === id);
            if (!activeChat) return;

            // Actualizar datos del header
            activeChatTitle.textContent = activeChat.title;
            activeChatModelBadge.textContent = activeChat.model;

            // Renderizar la lista de chats para actualizar el estilo "activo"
            renderChatList();

            // Renderizar los mensajes del chat activo
            renderMessages();
            
            // Cerrar menú responsivo en móviles al seleccionar
            if (window.innerWidth < 768) {
                // Opcional: ocultar sidebar
            }
        }

        function renameConversation(id, newTitle) {
            const chat = conversations.find(c => c.id === id);
            if (chat && newTitle.trim()) {
                chat.title = newTitle.trim();
                saveConversations();
                renderChatList();
                if (id === activeChatId) {
                    activeChatTitle.textContent = chat.title;
                }
            }
        }

        function deleteConversation(id, event) {
            if (event) event.stopPropagation(); // Evitar que seleccione la conversación al hacer click en borrar
            
            const index = conversations.findIndex(c => c.id === id);
            if (index === -1) return;

            if (confirm(`¿Estás seguro de que deseas eliminar la conversación "${conversations[index].title}"?`)) {
                conversations.splice(index, 1);
                saveConversations();
                
                if (conversations.length === 0) {
                    createConversation("Conversación Inicial");
                } else if (activeChatId === id) {
                    // Si eliminamos la activa, seleccionar otra
                    selectConversation(conversations[0].id);
                } else {
                    renderChatList();
                }
            }
        }

        function clearActiveChatHistory() {
            const activeChat = conversations.find(c => c.id === activeChatId);
            if (activeChat) {
                if (confirm("¿Deseas vaciar todos los mensajes de esta conversación?")) {
                    activeChat.messages = [];
                    saveConversations();
                    renderMessages();
                }
            }
        }

        // ==========================================
        // 3. RENDERIZACIÓN DE VISTAS (UI)
        // ==========================================

        function renderChatList() {
            chatListContainer.innerHTML = '';
            
            conversations.forEach(chat => {
                const isActive = chat.id === activeChatId;
                
                const chatBtn = document.createElement('div');
                chatBtn.className = `group/item w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl cursor-pointer transition-all duration-200 border ${
                    isActive 
                    ? 'bg-indigo-950/50 border-indigo-500/25 text-white shadow-neon-indigo' 
                    : 'bg-transparent border-transparent text-slate-400 hover:bg-slate-900/40 hover:text-slate-200'
                }`;
                
                chatBtn.innerHTML = `
                    <div class="flex items-center gap-3 overflow-hidden flex-1">
                        <i class="fa-regular fa-comment-dots text-sm shrink-0 ${isActive ? 'text-indigo-400' : 'text-slate-500 group-hover/item:text-slate-400'}"></i>
                        <span class="text-xs font-semibold truncate select-none leading-none pr-2" id="title-span-${chat.id}">${escapeHTML(chat.title)}</span>
                        <input type="text" id="edit-input-${chat.id}" class="hidden text-xs font-semibold bg-slate-900 border border-indigo-500 text-white rounded px-1 py-0.5 outline-none w-full" value="${escapeHTML(chat.title)}">
                    </div>
                    <div class="flex items-center gap-1.5 opacity-0 group-hover/item:opacity-100 transition-opacity">
                        <button onclick="enableRenameInline('${chat.id}', event)" class="p-1 rounded text-slate-500 hover:bg-slate-800 hover:text-indigo-400 transition" title="Renombrar">
                            <i class="fa-solid fa-pen text-[10px]"></i>
                        </button>
                        <button onclick="deleteConversation('${chat.id}', event)" class="p-1 rounded text-slate-500 hover:bg-slate-800 hover:text-red-400 transition" title="Borrar">
                            <i class="fa-solid fa-trash-can text-[10px]"></i>
                        </button>
                    </div>
                `;
                
                // Evento para seleccionar la conversación
                chatBtn.addEventListener('click', () => {
                    const inputField = document.getElementById(`edit-input-${chat.id}`);
                    // Solo seleccionar si no está editando
                    if (inputField.classList.contains('hidden')) {
                        selectConversation(chat.id);
                    }
                });

                chatListContainer.appendChild(chatBtn);
            });
        }

        // Renombrar inline
        function enableRenameInline(id, event) {
            event.stopPropagation();
            const span = document.getElementById(`title-span-${id}`);
            const input = document.getElementById(`edit-input-${id}`);
            
            span.classList.add('hidden');
            input.classList.remove('hidden');
            input.focus();
            input.select();
            
            // Guardar cambios al presionar Enter o perder foco
            const saveRename = () => {
                const newTitle = input.value.trim();
                if (newTitle && newTitle !== conversations.find(c => c.id === id).title) {
                    renameConversation(id, newTitle);
                } else {
                    span.classList.remove('hidden');
                    input.classList.add('hidden');
                }
            };

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') saveRename();
                if (e.key === 'Escape') {
                    span.classList.remove('hidden');
                    input.classList.add('hidden');
                }
            });

            input.addEventListener('blur', saveRename);
        }

        function renderMessages() {
            const activeChat = conversations.find(c => c.id === activeChatId);
            if (!activeChat) return;

            messagesContainer.innerHTML = '';

            if (activeChat.messages.length === 0) {
                welcomeView.classList.remove('hidden');
                messagesContainer.classList.add('hidden');
            } else {
                welcomeView.classList.add('hidden');
                messagesContainer.classList.remove('hidden');

                activeChat.messages.forEach((msg, index) => {
                    const isUser = msg.role === 'user';
                    const text = msg.parts[0].text;
                    
                    const bubble = document.createElement('div');
                    bubble.className = `flex items-start gap-4 animate-message ${isUser ? 'justify-end' : 'justify-start'}`;

                    if (isUser) {
                        bubble.innerHTML = `
                            <div class="flex flex-col items-end max-w-[85%]">
                                <div class="bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 text-white px-4 py-3 rounded-2xl rounded-tr-none text-sm md:text-base font-normal shadow-neon-indigo select-text leading-relaxed whitespace-pre-wrap">
                                    ${escapeHTML(text)}
                                </div>
                                <span class="text-[9px] text-slate-500 mt-1 select-none font-semibold uppercase tracking-wider">Tú</span>
                            </div>
                            <div class="h-9 w-9 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 flex items-center justify-center flex-shrink-0 select-none shadow">
                                <i class="fa-solid fa-user text-xs"></i>
                            </div>
                        `;
                    } else {
                        // Renderizado HTML de Markdown
                        const renderedHTML = marked.parse(text);

                        bubble.innerHTML = `
                            <div class="h-9 w-9 rounded-xl bg-indigo-950 text-indigo-400 border border-indigo-900/50 flex items-center justify-center flex-shrink-0 select-none shadow-neon-indigo">
                                <i class="fa-solid fa-brain text-xs"></i>
                            </div>
                            <div class="flex flex-col items-start max-w-[85%]">
                                <div class="glass-card text-slate-100 px-4 py-3.5 rounded-2xl rounded-tl-none text-sm md:text-base font-normal select-text leading-relaxed markdown-content w-full shadow-neon-indigo/5">
                                    ${renderedHTML}
                                </div>
                                <div class="flex items-center gap-3 mt-1.5 select-none px-1">
                                    <span class="text-[9px] text-indigo-400 font-bold uppercase tracking-wider">Gemini</span>
                                    <span class="h-1 w-1 rounded-full bg-slate-700"></span>
                                    <!-- TTS Speaker Button -->
                                    <button onclick="speakMessage(${index}, this)" class="text-slate-500 hover:text-indigo-400 transition" title="Escuchar respuesta">
                                        <i class="fa-solid fa-volume-high text-[10px]"></i>
                                    </button>
                                    <span class="h-1 w-1 rounded-full bg-slate-700"></span>
                                    <!-- Copy Button -->
                                    <button onclick="copyMessage(${index}, this)" class="text-slate-500 hover:text-emerald-400 transition" title="Copiar respuesta">
                                        <i class="fa-solid fa-copy text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        `;
                    }

                    messagesContainer.appendChild(bubble);
                });

                // Resaltado de código e inserción de botones en bloques de código
                messagesContainer.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightElement(block);
                    addCopyButtonToCodeBlock(block);
                });
            }

            scrollToBottom();
        }

        // Añadir botón de copiar rápido en bloques de código de respuesta
        function addCopyButtonToCodeBlock(codeBlock) {
            const pre = codeBlock.parentNode;
            if (pre.tagName !== 'PRE' || pre.querySelector('.btn-copy-code')) return;

            pre.style.position = 'relative';
            pre.className += ' rounded-xl overflow-hidden my-3 border border-slate-800 shadow';

            const header = document.createElement('div');
            header.className = 'bg-slate-950 px-4 py-1.5 flex justify-between items-center text-[10px] text-slate-500 font-mono select-none border-b border-slate-800';
            
            // Extraer lenguaje
            let lang = 'Código';
            const classes = codeBlock.className.split(' ');
            const langClass = classes.find(c => c.startsWith('language-'));
            if (langClass) {
                lang = langClass.replace('language-', '').toUpperCase();
            }
            
            header.innerHTML = `
                <span>${lang}</span>
                <button class="btn-copy-code text-slate-500 hover:text-white transition flex items-center gap-1.5">
                    <i class="fa-solid fa-copy"></i> Copiar
                </button>
            `;

            pre.prepend(header);

            // Funcionalidad de copiado
            const copyBtn = header.querySelector('.btn-copy-code');
            copyBtn.addEventListener('click', async () => {
                const code = codeBlock.textContent;
                try {
                    await navigator.clipboard.writeText(code);
                    copyBtn.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i> <span class="text-emerald-400">¡Copiado!</span>';
                    setTimeout(() => {
                        copyBtn.innerHTML = '<i class="fa-solid fa-copy"></i> Copiar';
                    }, 2000);
                } catch (err) {
                    alert("No se pudo copiar el código.");
                }
            });
        }

        function scrollToBottom() {
            setTimeout(() => {
                messagesViewport.scrollTo({
                    top: messagesViewport.scrollHeight,
                    behavior: 'smooth'
                });
            }, 50);
        }

        // ==========================================
        // 4. LÓGICA DE API (GOOGLE GEMINI INTERACTOR)
        // ==========================================

        async function handleSendUserMessage() {
            const text = chatTextarea.value.trim();
            if (!text || isWaitingForAI) return;

            const activeChat = conversations.find(c => c.id === activeChatId);
            if (!activeChat) return;

            // 1. Agregar a historial local
            activeChat.messages.push({
                role: "user",
                parts: [{ text: text }]
            });

            // Si es el primer mensaje, cambiar título del chat
            if (activeChat.title === "Conversación Inicial" || activeChat.title === "Nueva Conversación") {
                const generatedTitle = text.length > 25 ? text.substring(0, 25) + "..." : text;
                activeChat.title = generatedTitle;
            }

            chatTextarea.value = '';
            updateTextareaHeight();
            saveConversations();
            
            // Renderizar de inmediato el mensaje del usuario
            renderMessages();
            renderChatList();

            // 2. Ejecutar consulta a Gemini
            await queryGeminiAPI(activeChat);
        }

        async function queryGeminiAPI(chat) {
            isWaitingForAI = true;
            aiTypingIndicator.classList.remove('hidden');
            scrollToBottom();

            // Obtener API Key de localStorage o del fallback del servidor
            let apiKey = localStorage.getItem('gemini_custom_apikey');
            if (!apiKey || apiKey.trim() === '') {
                apiKey = DEFAULT_API_KEY;
            }

            // Endpoint de Google Gemini REST
            const endpoint = `https://generativelanguage.googleapis.com/v1beta/models/${chat.model}:generateContent?key=${apiKey}`;

            // Configurar el payload multi-turno con las instrucciones de sistema
            const payload = {
                contents: chat.messages,
                systemInstruction: {
                    parts: [{ text: chat.systemPrompt || DEFAULT_SYSTEM_PROMPT }]
                }
            };

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    if (response.status === 400 || response.status === 403) {
                        throw new Error(`Error en API Key (Código ${response.status}). Verifica tus credenciales.`);
                    }
                    if (response.status === 429) {
                        throw new Error(`Exceso de peticiones (Código 429). Espera un momento antes de volver a consultar.`);
                    }
                    throw new Error(`Error HTTP del servidor (Código ${response.status} - ${response.statusText})`);
                }

                const data = await response.json();
                
                let aiResponseText = "";
                if (data.candidates && data.candidates.length > 0 && data.candidates[0].content) {
                    aiResponseText = data.candidates[0].content.parts[0].text;
                } else {
                    aiResponseText = "*No se recibió una respuesta estructurada de Gemini. Por favor intenta reformular tu pregunta.*";
                }

                // Guardar respuesta del modelo
                chat.messages.push({
                    role: "model",
                    parts: [{ text: aiResponseText }]
                });

                saveConversations();
                renderMessages();

            } catch (err) {
                console.error("Error en API Gemini:", err);
                
                // Mostrar tarjeta de error en el chat
                chat.messages.push({
                    role: "model",
                    parts: [{ text: `❌ **Error de Conexión:**\n\nNo fue posible obtener respuesta de Gemini.\n\n*Detalles del error:* ${err.message}\n\n*Recomendación:* Abre la opción **Configurar IA** en la parte superior derecha y valida tu clave de API.` }]
                });
                
                saveConversations();
                renderMessages();
            } finally {
                isWaitingForAI = false;
                aiTypingIndicator.classList.add('hidden');
                scrollToBottom();
            }
        }

        // ==========================================
        // 5. UTILIDADES (TTS, COPY, ESCAPES)
        // ==========================================

        function escapeHTML(str) {
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Copiar mensaje completo al portapapeles
        async function copyMessage(index, button) {
            const activeChat = conversations.find(c => c.id === activeChatId);
            if (!activeChat || !activeChat.messages[index]) return;

            const text = activeChat.messages[index].parts[0].text;

            try {
                await navigator.clipboard.writeText(text);
                button.innerHTML = '<i class="fa-solid fa-check text-emerald-400"></i>';
                setTimeout(() => {
                    button.innerHTML = '<i class="fa-solid fa-copy text-[10px]"></i>';
                }, 2000);
            } catch (err) {
                alert("No se pudo copiar el mensaje.");
            }
        }

        // Síntesis de voz (Text-to-Speech)
        let speechSynthInstance = window.speechSynthesis;
        let activeSpeechUtterance = null;

        function speakMessage(index, button) {
            if (!speechSynthInstance) {
                alert("Tu navegador no soporta síntesis de voz.");
                return;
            }

            // Si está reproduciendo, cancelarlo
            if (speechSynthInstance.speaking) {
                speechSynthInstance.cancel();
                if (button.querySelector('i').classList.contains('fa-stop')) {
                    button.innerHTML = '<i class="fa-solid fa-volume-high text-[10px]"></i>';
                    return;
                }
            }

            const activeChat = conversations.find(c => c.id === activeChatId);
            if (!activeChat || !activeChat.messages[index]) return;

            // Limpiar texto para una voz más natural (quitar Markdown)
            let rawText = activeChat.messages[index].parts[0].text
                .replace(/[*#`_\-]/g, '') // Quitar negritas, títulos
                .replace(/\[.*?\]\(.*?\)/g, '') // Quitar enlaces
                .substring(0, 800); // Límite de dictado para evitar bloqueos

            activeSpeechUtterance = new SpeechSynthesisUtterance(rawText);
            activeSpeechUtterance.lang = 'es-ES';

            // Cambiar icono a stop durante lectura
            button.innerHTML = '<i class="fa-solid fa-stop text-[10px] text-red-500 animate-pulse"></i>';

            activeSpeechUtterance.onend = () => {
                button.innerHTML = '<i class="fa-solid fa-volume-high text-[10px]"></i>';
            };

            activeSpeechUtterance.onerror = () => {
                button.innerHTML = '<i class="fa-solid fa-volume-high text-[10px]"></i>';
            };

            speechSynthInstance.speak(activeSpeechUtterance);
        }

        // ==========================================
        // 6. GESTIÓN DEL MODAL DE CONFIGURACIÓN
        // ==========================================

        function openSettingsModal() {
            const activeChat = conversations.find(c => c.id === activeChatId);
            if (!activeChat) return;

            // Rellenar campos del modal
            const customApiKey = localStorage.getItem('gemini_custom_apikey') || '';
            inputApiKey.value = customApiKey;
            selectModel.value = activeChat.model;
            textareaSystemPrompt.value = activeChat.systemPrompt;

            settingsModal.classList.remove('hidden');
        }

        function closeSettingsModal() {
            settingsModal.classList.add('hidden');
        }

        function saveSettings() {
            const activeChat = conversations.find(c => c.id === activeChatId);
            if (!activeChat) return;

            // 1. Guardar API Key local
            const customKeyVal = inputApiKey.value.trim();
            if (customKeyVal) {
                localStorage.setItem('gemini_custom_apikey', customKeyVal);
            } else {
                localStorage.removeItem('gemini_custom_apikey');
            }

            // 2. Guardar propiedades de la conversación activa
            activeChat.model = selectModel.value;
            activeChat.systemPrompt = textareaSystemPrompt.value.trim() || DEFAULT_SYSTEM_PROMPT;

            saveConversations();
            
            // Actualizar la vista actual
            activeChatModelBadge.textContent = activeChat.model;

            closeSettingsModal();
            
            // Notificación rápida
            alert("Configuración de IA guardada y aplicada a este chat.");
        }

        // ==========================================
        // 7. EVENT LISTENERS & CONTROL DE ALTURAS
        // ==========================================

        // Auto-redimensionar altura del textarea de escritura
        function updateTextareaHeight() {
            chatTextarea.style.height = 'auto';
            chatTextarea.style.height = (chatTextarea.scrollHeight) + 'px';
            
            const textLength = chatTextarea.value.length;
            charIndicator.textContent = `${textLength} caracteres`;
            
            // Habilitar o deshabilitar botón de enviar
            btnSendMessage.disabled = textLength === 0 || isWaitingForAI;
        }

        chatTextarea.addEventListener('input', updateTextareaHeight);
        
        chatTextarea.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault(); // Evitar salto de línea estándar
                handleSendUserMessage();
            }
        });

        btnSendMessage.addEventListener('click', handleSendUserMessage);

        btnNewChat.addEventListener('click', () => {
            createConversation();
        });

        btnClearChat.addEventListener('click', clearActiveChatHistory);

        // API Key visibilidad toggle
        btnToggleApiKeyVisibility.addEventListener('click', () => {
            const isPassword = inputApiKey.type === 'password';
            inputApiKey.type = isPassword ? 'text' : 'password';
            btnToggleApiKeyVisibility.querySelector('i').className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });

        // Configuración de system prompt presets
        document.querySelectorAll('.preset-sys-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                textareaSystemPrompt.value = btn.getAttribute('data-prompt');
            });
        });

        btnClearSystemPrompt.addEventListener('click', () => {
            textareaSystemPrompt.value = DEFAULT_SYSTEM_PROMPT;
        });

        // Modales
        btnOpenSettings.addEventListener('click', openSettingsModal);
        btnOpenSettingsFooter.addEventListener('click', openSettingsModal);
        btnCloseSettings.addEventListener('click', closeSettingsModal);
        settingsModalBackdrop.addEventListener('click', closeSettingsModal);
        btnSaveSettings.addEventListener('click', saveSettings);

        // Clic en tarjetas rápidas / presets de bienvenida
        document.querySelectorAll('.preset-card').forEach(card => {
            card.addEventListener('click', () => {
                const text = card.querySelector('p').textContent.replace(/"/g, '');
                chatTextarea.value = text;
                updateTextareaHeight();
                chatTextarea.focus();
            });
        });

        // Colapso/Toggle de Sidebar
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('w-0');
            sidebar.classList.toggle('md:w-80');
            sidebar.classList.toggle('border-r');
            sidebar.classList.toggle('overflow-hidden');
        });

        btnMobileSidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('hidden');
        });

    </script>
</body>
</html>
