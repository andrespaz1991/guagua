<?php
/**
 * Módulo Creador y Jugador de Sopas de Letras - Guagua 2026
 * Stack: PHP + Tailwind CSS + Vanilla JS (Pointer Events + Canvas)
 * Tema: Premium Light Mode (Modo Claro)
 * Autor: Antigravity AI
 */

require '../../comun/conexion.php'; // Consistencia del sistema
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador de Sopa de Letras - Guagua</title>
    
    <!-- Google Fonts - Outfit para lectura limpia y JetBrains Mono para grillas tipográficas -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- FontAwesome 6 para íconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    boxShadow: {
                        'soft-violet': '0 10px 30px -5px rgba(139, 92, 246, 0.12)',
                        'soft-indigo': '0 10px 30px -5px rgba(99, 102, 241, 0.12)',
                        'soft-emerald': '0 10px 30px -5px rgba(16, 185, 129, 0.12)',
                    }
                }
            }
        }
    </script>

    <style>
        /* Estilos Premium en Modo Claro */
        body {
            background-color: #f8fafc; /* Slate 50 */
            color: #1e293b; /* Slate 800 */
            scrollbar-color: #cbd5e1 transparent;
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
            background: #e2e8f0;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        /* Celda de la sopa de letras */
        .letter-cell {
            user-select: none;
            -webkit-user-drag: none;
            touch-action: none;
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ================================================================= */
        /* HOJA DE ESTILOS DE IMPRESIÓN - CORREGIDA Y ROBUSTA */
        /* ================================================================= */
        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                font-family: 'Outfit', sans-serif !important;
            }
            .no-print {
                display: none !important;
            }
            .print-area {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                display: block !important;
                background: white !important;
            }
            
            /* Contenedor de la grilla en impresión física - Borde perfecto */
            .print-grid {
                border: 3px solid #000000 !important;
                background-color: #ffffff !important;
                margin: 1.5rem auto !important;
                width: 480px !important;
                height: 480px !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0px !important;
                padding: 0px !important;
                gap: 0px !important; /* Gaps en cero para bordes internos perfectos */
            }
            
            /* Celdas impresas individuales */
            .print-cell {
                border: 1px solid #000000 !important;
                border-radius: 0px !important;
                color: #000000 !important;
                background-color: transparent !important;
                font-weight: bold !important;
                font-size: 1.25rem !important;
                width: 100% !important;
                height: 100% !important;
                aspect-ratio: 1 / 1 !important;
            }
            
            /* Si mostramos la solución en impresión, marcamos el fondo con un tono gris de alta calidad */
            .print-cell-solved {
                background-color: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .print-word-list {
                color: #000000 !important;
                font-size: 1.15rem !important;
                margin-top: 1.5rem !important;
            }
            
            .print-header {
                display: flex !important;
                justify-content: space-between !important;
                border-bottom: 2px solid #000000 !important;
                padding-bottom: 0.75rem !important;
                margin-bottom: 1.5rem !important;
            }
        }
    </style>
</head>
<body class="h-screen flex flex-col md:flex-row overflow-hidden antialiased">

    <!-- CANVAS PARA ANIMACIÓN DE CONFETI -->
    <canvas id="confetti-canvas" class="fixed inset-0 pointer-events-none z-50 w-full h-full"></canvas>

    <!-- BARRA LATERAL (CREADOR & AJUSTES DEL DOCENTE) -->
    <aside class="no-print w-full md:w-96 shrink-0 flex flex-col h-[40vh] md:h-full bg-white border-b md:border-b-0 md:border-r border-slate-200 z-30 overflow-y-auto shadow-sm">
        
        <!-- Header del Creador -->
        <div class="p-5 border-b border-slate-100 bg-white sticky top-0 z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white shadow-soft-violet">
                    <i class="fa-solid fa-gamepad text-lg animate-pulse"></i>
                </div>
                <div>
                    <h1 class="text-base font-extrabold text-slate-800 leading-none tracking-tight">Guagua Puzzles</h1>
                    <span class="text-[10px] text-violet-600 font-bold tracking-wider uppercase">Generador de Sopa de Letras</span>
                </div>
            </div>
            
            <a href="../../index.php" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-lg text-slate-500 hover:text-slate-800 transition" title="Volver al Portal">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
        </div>

        <!-- Formulario de Configuración -->
        <div class="p-5 space-y-5 flex-1 bg-slate-50/50">
            
            <!-- Título de la Sopa -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                    Título de la Sopa de Letras
                </label>
                <input 
                    type="text" 
                    id="input-title" 
                    class="w-full bg-white border border-slate-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl py-2.5 px-3.5 text-sm outline-none text-slate-700 transition" 
                    placeholder="Ej. Animales de la Granja" 
                    value="Palabras Divertidas"
                >
            </div>

            <!-- Entrada de Palabras -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                    Palabras a Esconder
                    <span class="text-[9px] text-slate-400 font-bold lowercase">Separadas por comas</span>
                </label>
                <textarea 
                    id="input-words" 
                    rows="5" 
                    class="w-full bg-white border border-slate-200 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 rounded-xl py-2.5 px-3.5 text-sm outline-none text-slate-700 transition resize-none leading-relaxed" 
                    placeholder="Perro, Gato, Caballo, Vaca, Cerdo, Gallina, Oveja, Pato"
                >PERRO, GATO, CABALLO, VACA, CERDO, GALLINA, OVEJA, PATO</textarea>
                <p class="text-[10px] text-slate-400 mt-1">
                    Tip: Ingresa palabras cortas y medianas para cuadrículas pequeñas. Se ignorarán acentos de forma automática.
                </p>
            </div>

            <!-- Grid y Dificultad -->
            <div class="grid grid-cols-2 gap-3.5">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Tamaño Grilla
                    </label>
                    <select 
                        id="select-grid-size" 
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-3 text-xs outline-none text-slate-700 focus:border-violet-500 transition"
                    >
                        <option value="10">10 x 10 (Fácil)</option>
                        <option value="12" selected>12 x 12 (Medio)</option>
                        <option value="15">15 x 15 (Normal)</option>
                        <option value="18">18 x 18 (Grande)</option>
                        <option value="20">20 x 20 (Desafío)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Dificultad
                    </label>
                    <select 
                        id="select-difficulty" 
                        class="w-full bg-white border border-slate-200 rounded-xl py-2.5 px-3 text-xs outline-none text-slate-700 focus:border-violet-500 transition"
                    >
                        <option value="facil">Fácil (→ ↓)</option>
                        <option value="medio" selected>Medio (→ ↓ ↘)</option>
                        <option value="dificil">Difícil (→ ↓ ↘ e invertidos)</option>
                    </select>
                </div>
            </div>

            <!-- Botones Principales de Generación -->
            <div class="pt-2">
                <button 
                    id="btn-generate" 
                    class="w-full py-3.5 px-4 bg-gradient-to-r from-violet-600 via-indigo-600 to-indigo-700 hover:from-violet-500 hover:to-indigo-650 text-white rounded-xl font-bold text-sm shadow-soft-violet flex items-center justify-center gap-2 transition-all duration-300 active:scale-95 group"
                >
                    <i class="fa-solid fa-wand-magic-sparkles transition-transform group-hover:rotate-12"></i>
                    Generar Nueva Sopa
                </button>
            </div>

            <!-- Acciones del Docente -->
            <div class="border-t border-slate-200 pt-4 space-y-2">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-2">Herramientas de Docencia</h4>
                
                <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-eye-slash text-slate-400 text-sm" id="solution-icon"></i>
                        <span class="text-xs font-bold text-slate-600">Mostrar Solucionario</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="toggle-solution" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-slate-400 after:border-slate-300 after:border after:rounded-full after:height-4 after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 peer-checked:after:bg-white"></div>
                    </label>
                </div>

                <button 
                    id="btn-print" 
                    class="w-full py-2.5 px-4 bg-white hover:bg-slate-100 text-slate-600 hover:text-slate-800 rounded-xl font-bold text-xs border border-slate-200 shadow-sm transition flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-print"></i>
                    Imprimir en Papel
                </button>
            </div>

        </div>

        <!-- Footer del Sidebar -->
        <div class="p-4 border-t border-slate-100 bg-white text-center text-[10px] text-slate-400">
            Guagua Didáctica &copy; 2026
        </div>
    </aside>

    <!-- TRABAJO PRINCIPAL (SOBRETODO JUGABILIDAD Y FORMATO DE IMPRESIÓN) -->
    <main class="flex-1 flex flex-col h-[60vh] md:h-full bg-slate-50 overflow-y-auto px-4 md:px-10 py-6">
        
        <div class="print-area w-full max-w-4xl mx-auto flex flex-col flex-1 bg-transparent">
            
            <!-- ENCABEZADO ESCOLAR PARA LA VERSIÓN DE IMPRESIÓN (Invisible por defecto en web) -->
            <div class="hidden print-header w-full pb-3 border-b-2 border-black flex-row justify-between items-end mb-6 text-black font-semibold">
                <div class="space-y-1">
                    <h2 class="text-xl font-extrabold text-black uppercase tracking-wide" id="print-school-title">Sopa de Letras Educativa</h2>
                    <p class="text-xs text-gray-700">Tema: <span id="print-theme-name" class="font-bold"></span></p>
                </div>
                <div class="text-xs text-right space-y-1">
                    <p>Estudiante: ____________________________________</p>
                    <div class="flex gap-4 justify-end mt-1">
                        <p>Grado: _________</p>
                        <p>Fecha: ____________</p>
                    </div>
                </div>
            </div>

            <!-- HEADER WEB (Invisible en impresión) -->
            <header class="no-print flex items-center justify-between mb-6">
                <div>
                    <h2 id="web-chat-title" class="text-xl md:text-2xl font-extrabold text-slate-800 tracking-tight">Sopa de Letras</h2>
                    <p class="text-xs text-slate-500 mt-1">Arrastra el mouse o toca las letras para encontrar las palabras de la lista.</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Marcador de Progreso -->
                    <div class="px-4 py-2 bg-white border border-slate-200/80 rounded-xl flex items-center gap-2 text-violet-600 shadow-sm">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span class="text-xs font-bold font-mono" id="score-badge">0 / 0</span>
                    </div>
                </div>
            </header>

            <!-- CONTENEDOR CENTRAL: GRILLA + PALABRAS -->
            <div class="flex flex-col lg:flex-row gap-8 items-center lg:items-start justify-center flex-1">
                
                <!-- GRILLA DEL JUEGO -->
                <div class="flex flex-col items-center justify-center select-none shrink-0 relative">
                    <!-- Sombra decorativa suave del tablero -->
                    <div class="no-print absolute -inset-2 bg-indigo-500/5 rounded-3xl filter blur-xl opacity-75"></div>
                    
                    <!-- Grilla interactiva autoajustable a formato cuadrado perfecto -->
                    <div 
                        id="grid-board" 
                        class="print-grid bg-white p-3 rounded-2xl relative border border-slate-200 shadow-xl shadow-indigo-100/30 select-none cursor-pointer grid gap-1 w-[300px] h-[300px] xs:w-[320px] xs:h-[320px] sm:w-[380px] sm:h-[380px] md:w-[440px] md:h-[440px] lg:w-[480px] lg:h-[480px]"
                        style="touch-action: none;"
                    >
                        <!-- Celdas renderizadas dinámicamente desde JS -->
                    </div>
                </div>

                <!-- LISTADO DE PALABRAS A ENCONTRAR -->
                <div class="w-full max-w-sm flex-1 flex flex-col justify-start">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xl shadow-indigo-100/20 w-full">
                        <h3 class="text-sm font-bold text-violet-600 uppercase tracking-wider mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i class="fa-solid fa-list-check"></i>
                            Palabras a Buscar
                        </h3>

                        <!-- Lista de Palabras -->
                        <div 
                            id="words-list" 
                            class="print-word-list grid grid-cols-2 gap-3 text-xs md:text-sm font-bold text-slate-600"
                        >
                            <!-- Palabras cargadas dinámicamente -->
                        </div>
                    </div>
                    
                    <!-- Tarjeta informativa de Victoria (Web) -->
                    <div id="victory-card" class="no-print hidden mt-4 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex items-center gap-3 animate-pulse text-emerald-700">
                        <div class="h-10 w-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <i class="fa-solid fa-trophy text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider">¡Excelente Trabajo!</h4>
                            <p class="text-[10px] text-emerald-600 leading-normal font-semibold">
                                Encontraste todas las palabras escondidas. ¡Eres todo un experto!
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- JS LOGIC - TOTALMENTE ROBUSTA -->
    <script>
        // Configuración inicial por defecto
        let gridSize = 12;
        let difficulty = 'medio';
        let words = [];
        let grid = [];
        let placedWordsMetadata = []; // { word: '...', cells: [[r, c], ...] }
        let solvedWordsSet = new Set(); // Guarda las palabras ya encontradas en mayúsculas

        // Selección de juego
        let isSelecting = false;
        let selectionStartCell = null; // { row, col }
        let selectionCurrentCell = null; // { row, col }
        let selectedCellsList = []; // Array de { row, col, char, element }
        
        // Elementos DOM
        const gridBoard = document.getElementById('grid-board');
        const wordsListContainer = document.getElementById('words-list');
        const scoreBadge = document.getElementById('score-badge');
        const victoryCard = document.getElementById('victory-card');
        
        const inputTitle = document.getElementById('input-title');
        const inputWords = document.getElementById('input-words');
        const selectGridSize = document.getElementById('select-grid-size');
        const selectDifficulty = document.getElementById('select-difficulty');
        const btnGenerate = document.getElementById('btn-generate');
        const btnPrint = document.getElementById('btn-print');
        const toggleSolution = document.getElementById('toggle-solution');
        const solutionIcon = document.getElementById('solution-icon');

        const printSchoolTitle = document.getElementById('print-school-title');
        const printThemeName = document.getElementById('print-theme-name');

        // Confetti Particle System
        const confettiCanvas = document.getElementById('confetti-canvas');
        const ctx = confettiCanvas.getContext('2d');
        let confettiActive = false;
        let confettiParticles = [];
        const confettiColors = ['#8b5cf6', '#6366f1', '#10b981', '#f59e0b', '#ec4899', '#3b82f6'];

        // Inicialización
        window.addEventListener('DOMContentLoaded', () => {
            handleGenerateWordSearch();
            resizeConfettiCanvas();
            window.addEventListener('resize', resizeConfettiCanvas);
            
            // Pointerup general en el body para evitar que quede atrapada la selección fuera de la grilla
            document.body.addEventListener('pointerup', endSelection);
        });

        // ==========================================
        // 1. ALGORITMO DE GENERACIÓN DE SOPAS
        // ==========================================

        function handleGenerateWordSearch() {
            // Cargar configuraciones
            gridSize = parseInt(selectGridSize.value);
            difficulty = selectDifficulty.value;
            
            // Limpiar título de sopa
            const title = inputTitle.value.trim() || "Palabras Divertidas";
            printSchoolTitle.textContent = title;
            printThemeName.textContent = title;

            // Procesar y normalizar palabras
            words = inputWords.value
                .split(',')
                .map(w => cleanSpanishText(w.trim()))
                .filter(w => w.length > 1 && w.length <= gridSize);

            if (words.length === 0) {
                alert("Por favor ingresa al menos 2 palabras válidas menores o iguales al tamaño de la grilla.");
                return;
            }

            // Generar grilla vacía
            grid = Array(gridSize).fill(null).map(() => Array(gridSize).fill(""));
            placedWordsMetadata = [];
            solvedWordsSet.clear();
            victoryCard.classList.add('hidden');
            
            // Ordenar por longitud de mayor a menor para facilitar colocación
            const sortedWords = [...words].sort((a, b) => b.length - a.length);
            
            // Direcciones posibles en base a dificultad
            // Cada dirección tiene [dRow, dCol]
            let directions = [];
            if (difficulty === 'facil') {
                directions = [
                    [0, 1],  // Derecha
                    [1, 0],  // Abajo
                ];
            } else if (difficulty === 'medio') {
                directions = [
                    [0, 1],   // Derecha
                    [1, 0],   // Abajo
                    [1, 1],   // Diagonal Abajo-Derecha
                    [-1, 1]   // Diagonal Arriba-Derecha
                ];
            } else { // dificil
                directions = [
                    [0, 1],   // Derecha
                    [1, 0],   // Abajo
                    [1, 1],   // Diagonal Abajo-Derecha
                    [-1, 1],  // Diagonal Arriba-Derecha
                    [0, -1],  // Izquierda (Invertida)
                    [-1, 0],  // Arriba (Invertida)
                    [-1, -1], // Diagonal Arriba-Izquierda (Invertida)
                    [1, -1]   // Diagonal Abajo-Izquierda (Invertida)
                ];
            }

            // Colocar cada palabra
            let skippedWords = [];
            sortedWords.forEach(word => {
                let placed = false;
                let attempts = 0;
                
                while (!placed && attempts < 150) {
                    attempts++;
                    const dir = directions[Math.floor(Math.random() * directions.length)];
                    const dRow = dir[0];
                    const dCol = dir[1];
                    
                    // Elegir punto de partida aleatorio
                    const startRow = Math.floor(Math.random() * gridSize);
                    const startCol = Math.floor(Math.random() * gridSize);
                    
                    // Validar si entra espacialmente
                    const endRow = startRow + dRow * (word.length - 1);
                    const endCol = startCol + dCol * (word.length - 1);
                    
                    if (endRow >= 0 && endRow < gridSize && endCol >= 0 && endCol < gridSize) {
                        // Validar colisiones
                        let fits = true;
                        let cellsToUse = [];
                        
                        for (let i = 0; i < word.length; i++) {
                            const r = startRow + dRow * i;
                            const c = startCol + dCol * i;
                            const cellChar = grid[r][c];
                            const targetChar = word[i];
                            
                            if (cellChar !== "" && cellChar !== targetChar) {
                                fits = false;
                                break;
                            }
                            cellsToUse.push([r, c]);
                        }
                        
                        // Si encaja, escribirla
                        if (fits) {
                            cellsToUse.forEach((cell, idx) => {
                                grid[cell[0]][cell[1]] = word[idx];
                            });
                            
                            placedWordsMetadata.push({
                                word: word,
                                cells: cellsToUse
                            });
                            placed = true;
                        }
                    }
                }
                
                if (!placed) {
                    skippedWords.push(word);
                }
            });

            if (skippedWords.length > 0) {
                alert(`Nota: El generador tuvo que saltar algunas palabras por falta de espacio: ${skippedWords.join(', ')}. Intente agrandar el tamaño de la grilla o ingresar palabras más cortas.`);
            }

            // Rellenar espacios vacíos con letras aleatorias
            const abecedario = "ABCDEFGHIJKLMOPQRSTUVWXYZ"; // Omitimos Ñ aquí para evitar problemas, aunque se puede incluir
            for (let r = 0; r < gridSize; r++) {
                for (let c = 0; c < gridSize; c++) {
                    if (grid[r][c] === "") {
                        grid[r][c] = abecedario[Math.floor(Math.random() * abecedario.length)];
                    }
                }
            }

            // Renderizar vistas
            renderGridBoard();
            renderWordsList();
            updateScore();
            toggleSolution.checked = false; // Desactivar solucionario por defecto
        }

        // Limpiar acentos y forzar mayúsculas
        function cleanSpanishText(text) {
            return text
                .toUpperCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "") // Quitar tildes
                .replace(/[^A-ZÑ]/g, ""); // Permitir solo letras y Ñ
        }

        // ==========================================
        // 2. RENDERIZADO DE LA INTERFAZ
        // ==========================================

        function renderGridBoard() {
            gridBoard.innerHTML = '';
            
            // Establecer columnas dinámicas en el grid de Tailwind CSS
            gridBoard.style.gridTemplateColumns = `repeat(${gridSize}, minmax(0, 1fr))`;
            gridBoard.style.gridTemplateRows = `repeat(${gridSize}, minmax(0, 1fr))`;
            
            for (let r = 0; r < gridSize; r++) {
                for (let c = 0; c < gridSize; c++) {
                    const letter = grid[r][c];
                    
                    const cell = document.createElement('div');
                    
                    // Celdas fluidas que estiran al 100% del contenedor.
                    cell.className = "letter-cell print-cell flex items-center justify-center font-bold text-slate-700 bg-slate-50 border border-slate-200/60 rounded-lg select-none hover:bg-violet-50 hover:text-violet-750 active:scale-95 cursor-pointer text-xs sm:text-sm md:text-base lg:text-lg";
                    cell.style.width = "100%";
                    cell.style.height = "100%";
                    cell.style.aspectRatio = "1 / 1";
                    
                    cell.textContent = letter;
                    cell.setAttribute('data-row', r);
                    cell.setAttribute('data-col', c);
                    
                    // Eventos de selección de Pointer
                    cell.addEventListener('pointerdown', startSelection);
                    cell.addEventListener('pointerenter', enterSelection);
                    
                    gridBoard.appendChild(cell);
                }
            }
        }

        function renderWordsList() {
            wordsListContainer.innerHTML = '';
            
            // Mostrar todas las palabras que se lograron colocar en la sopa
            placedWordsMetadata.forEach(meta => {
                const item = document.createElement('div');
                item.className = "flex items-center gap-2 py-1.5 px-3 bg-slate-50 rounded-xl border border-slate-200/60 hover:bg-slate-100 transition duration-300 shadow-sm";
                item.id = `word-item-${meta.word}`;
                item.innerHTML = `
                    <i class="fa-regular fa-square text-violet-500 shrink-0 text-xs" id="word-icon-${meta.word}"></i>
                    <span class="truncate font-mono uppercase tracking-wider text-slate-700" id="word-text-${meta.word}">${meta.word}</span>
                `;
                wordsListContainer.appendChild(item);
            });
        }

        function updateScore() {
            const total = placedWordsMetadata.length;
            const solved = solvedWordsSet.size;
            scoreBadge.textContent = `${solved} / ${total}`;

            if (solved === total && total > 0) {
                victoryCard.classList.remove('hidden');
                triggerVictoryConfetti();
            }
        }

        // ==========================================
        // 3. JUGABILIDAD (SELECCIÓN DIRECTA FLUIDA)
        // ==========================================

        function startSelection(e) {
            e.preventDefault();
            isSelecting = true;
            
            const cell = e.currentTarget;
            const r = parseInt(cell.getAttribute('data-row'));
            const c = parseInt(cell.getAttribute('data-col'));
            
            selectionStartCell = { row: r, col: c };
            selectionCurrentCell = { row: r, col: c };
            
            clearTempHighlight();
            highlightPath(r, c);
        }

        function enterSelection(e) {
            if (!isSelecting) return;
            
            const cell = e.currentTarget;
            const r = parseInt(cell.getAttribute('data-row'));
            const c = parseInt(cell.getAttribute('data-col'));
            
            // Validar si el arrastre es en línea recta (horizontal, vertical o diagonal a 45 grados)
            if (isValidLine(selectionStartCell.row, selectionStartCell.col, r, c)) {
                selectionCurrentCell = { row: r, col: c };
                clearTempHighlight();
                highlightPath(r, c);
            }
        }

        function endSelection() {
            if (!isSelecting) return;
            isSelecting = false;
            
            // Procesar palabra seleccionada
            if (selectedCellsList.length > 0) {
                const selectedWord = selectedCellsList.map(c => c.char).join('');
                const reversedWord = [...selectedWord].reverse().join('');
                
                let foundMatch = null;
                
                // Buscar coincidencia en la metadata
                placedWordsMetadata.forEach(meta => {
                    if (meta.word === selectedWord || meta.word === reversedWord) {
                        foundMatch = meta.word;
                    }
                });
                
                if (foundMatch && !solvedWordsSet.has(foundMatch)) {
                    // ¡Palabra encontrada!
                    solvedWordsSet.add(foundMatch);
                    
                    // Marcar celdas como resueltas permanentemente
                    selectedCellsList.forEach(cellObj => {
                        cellObj.element.classList.add('bg-emerald-100', 'border-emerald-300', 'text-emerald-800', 'print-cell-solved');
                        cellObj.element.classList.remove('hover:bg-violet-50', 'bg-slate-50', 'text-slate-700');
                    });
                    
                    // Tachar palabra en la lista
                    const wordText = document.getElementById(`word-text-${foundMatch}`);
                    const wordIcon = document.getElementById(`word-icon-${foundMatch}`);
                    const wordItem = document.getElementById(`word-item-${foundMatch}`);
                    
                    if (wordText && wordIcon && wordItem) {
                        wordText.className = "line-through text-slate-400 font-mono tracking-wider";
                        wordIcon.className = "fa-solid fa-square-check text-emerald-500 shrink-0 text-xs animate-bounce";
                        wordItem.className = "flex items-center gap-2 py-1.5 px-3 bg-emerald-50 border-emerald-250 rounded-xl shadow-sm transition duration-300";
                    }
                    
                    updateScore();
                }
            }
            
            clearTempHighlight();
        }

        // Validar si dos coordenadas forman una línea horizontal, vertical o diagonal a 45 grados
        function isValidLine(r1, c1, r2, c2) {
            const dRow = Math.abs(r2 - r1);
            const dCol = Math.abs(c2 - c1);
            
            return r1 === r2 || c1 === c2 || dRow === dCol;
        }

        function highlightPath(endRow, endCol) {
            const r1 = selectionStartCell.row;
            const c1 = selectionStartCell.col;
            const r2 = endRow;
            const c2 = endCol;
            
            const stepRow = r2 === r1 ? 0 : (r2 > r1 ? 1 : -1);
            const stepCol = c2 === c1 ? 0 : (c2 > c1 ? 1 : -1);
            
            const length = Math.max(Math.abs(r2 - r1), Math.abs(c2 - c1)) + 1;
            selectedCellsList = [];
            
            for (let i = 0; i < length; i++) {
                const r = r1 + stepRow * i;
                const c = c1 + stepCol * i;
                
                const cellElement = document.querySelector(`.letter-cell[data-row="${r}"][data-col="${c}"]`);
                if (cellElement) {
                    cellElement.classList.add('bg-violet-100', 'border-violet-300', 'text-violet-900', 'scale-105');
                    selectedCellsList.push({
                        row: r,
                        col: c,
                        char: cellElement.textContent,
                        element: cellElement
                    });
                }
            }
        }

        function clearTempHighlight() {
            // Remover colores temporales de selección
            document.querySelectorAll('.letter-cell').forEach(cell => {
                cell.classList.remove('bg-violet-100', 'border-violet-300', 'text-violet-900', 'scale-105');
            });
            selectedCellsList = [];
        }

        // ==========================================
        // 4. ACCIONES (IMPRESIÓN & SOLUCIONARIO)
        // ==========================================

        // Alternar el solucionario en pantalla y configurar la vista de impresión
        toggleSolution.addEventListener('change', () => {
            const isChecked = toggleSolution.checked;
            
            // Modificar ícono de ayuda
            solutionIcon.className = isChecked ? "fa-solid fa-eye text-emerald-500 text-sm animate-pulse" : "fa-solid fa-eye-slash text-slate-400 text-sm";
            
            // Destacar todas las palabras colocadas
            placedWordsMetadata.forEach(meta => {
                meta.cells.forEach(cell => {
                    const r = cell[0];
                    const c = cell[1];
                    const cellElement = document.querySelector(`.letter-cell[data-row="${r}"][data-col="${c}"]`);
                    
                    if (cellElement) {
                        if (isChecked) {
                            cellElement.classList.add('bg-emerald-100', 'border-emerald-350', 'text-emerald-800', 'print-cell-solved');
                            cellElement.classList.remove('bg-slate-50', 'text-slate-700');
                        } else {
                            // Remover solo si no han sido encontradas por el jugador
                            const cleanWord = cleanSpanishText(meta.word);
                            if (!solvedWordsSet.has(cleanWord)) {
                                cellElement.classList.remove('bg-emerald-100', 'border-emerald-350', 'text-emerald-800', 'print-cell-solved');
                                cellElement.classList.add('bg-slate-50', 'text-slate-700');
                            }
                        }
                    }
                });
            });
        });

        // Lanzar la previsualización de impresión del navegador
        btnPrint.addEventListener('click', () => {
            window.print();
        });

        // Evento del botón Generar del docente
        btnGenerate.addEventListener('click', handleGenerateWordSearch);

        // ==========================================
        // 5. ANIMACIÓN DE VICTORIA (CONFETI EN CANVAS)
        // ==========================================

        function resizeConfettiCanvas() {
            confettiCanvas.width = window.innerWidth;
            confettiCanvas.height = window.innerHeight;
        }

        function triggerVictoryConfetti() {
            confettiActive = true;
            confettiParticles = [];
            
            // Crear 120 partículas de confeti
            for (let i = 0; i < 120; i++) {
                confettiParticles.push({
                    x: Math.random() * confettiCanvas.width,
                    y: Math.random() * confettiCanvas.height - confettiCanvas.height,
                    r: Math.random() * 6 + 4,
                    d: Math.random() * confettiCanvas.height,
                    color: confettiColors[Math.floor(Math.random() * confettiColors.length)],
                    tilt: Math.random() * 10 - 5,
                    tiltAngleIncremental: Math.random() * 0.07 + 0.02,
                    tiltAngle: 0
                });
            }
            
            animateConfetti();
            
            // Detener confeti automáticamente a los 5 segundos para ahorrar rendimiento
            setTimeout(() => {
                confettiActive = false;
                ctx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
            }, 5000);
        }

        function animateConfetti() {
            if (!confettiActive) return;
            
            ctx.clearRect(0, 0, confettiCanvas.width, confettiCanvas.height);
            
            confettiParticles.forEach((p, idx) => {
                p.tiltAngle += p.tiltAngleIncremental;
                p.y += (Math.cos(p.d) + 3 + p.r / 2) / 2;
                p.x += Math.sin(p.tiltAngle);
                p.tilt = Math.sin(p.tiltAngle - idx / 3) * 15;
                
                ctx.beginPath();
                ctx.lineWidth = p.r;
                ctx.strokeStyle = p.color;
                ctx.moveTo(p.x + p.tilt + p.r / 2, p.y);
                ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 2);
                ctx.stroke();
                
                // Si la partícula pasa del fondo de la pantalla, reiniciar arriba
                if (p.y > confettiCanvas.height) {
                    p.x = Math.random() * confettiCanvas.width;
                    p.y = -20;
                    p.tilt = Math.random() * 10 - 5;
                }
            });
            
            requestAnimationFrame(animateConfetti);
        }

    </script>
</body>
</html>
