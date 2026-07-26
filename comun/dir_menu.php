<?php
/**
 * =================================================================
 * MÓDULO DE PLANEACIÓN - DASHBOARD V4.0 (LAYOUT DOS COLUMNAS)
 * =================================================================
 *
 * @author Andres Paz
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';

// =================================================================
// 2. ENDPOINT AJAX (LÓGICA INTACTA, AUMENTO DE LÍMITE)
// =================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_dashboard_data') {
    header('Content-Type: application/json');

    try {
        // Se aumenta a 24 para aprovechar la grilla densa en la vista
        $recordsPerPage = 24; 
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $rawSearch = isset($_GET['search']) ? trim($_GET['search']) : '';
        $searchTerm = '%' . $rawSearch . '%';
        $offset = ($page - 1) * $recordsPerPage;

        // A. FAVORITOS
        $sqlFav = "SELECT menu_item_id, menu_item_name, menu_description, menu_url, icono, url_target, fav 
                   FROM menu_items2 
                   WHERE fav = 1 
                   ORDER BY menu_item_name ASC";
        
        $stmtFav = $mysqli->prepare($sqlFav);
        $stmtFav->execute();
        $favorites = $stmtFav->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtFav->close();

        // B. RESULTADOS GENERALES
        $countSql = "SELECT COUNT(menu_item_id) as total 
                     FROM menu_items2 
                     WHERE fav = 0 AND LOWER(menu_item_name) LIKE LOWER(?)";
        
        $stmtCount = $mysqli->prepare($countSql);
        $stmtCount->bind_param('s', $searchTerm);
        $stmtCount->execute();
        $totalResult = $stmtCount->get_result()->fetch_assoc();
        $totalRecords = $totalResult['total'];
        $stmtCount->close();

        $dataSql = "SELECT menu_item_id, menu_item_name, menu_description, menu_url, icono, url_target, fav 
                    FROM menu_items2 
                    WHERE fav = 0 AND LOWER(menu_item_name) LIKE LOWER(?) 
                    ORDER BY menu_item_name ASC 
                    LIMIT ? OFFSET ?";
        
        $stmtData = $mysqli->prepare($dataSql);
        $stmtData->bind_param('sii', $searchTerm, $recordsPerPage, $offset);
        $stmtData->execute();
        $results = $stmtData->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtData->close();

        echo json_encode([
            'status' => 'success',
            'favorites' => $favorites,
            'results' => $results,
            'pagination' => [
                'total' => $totalRecords,
                'current_page' => $page,
                'per_page' => $recordsPerPage
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

/**
 * =================================================================
 * VISTA (HTML / CSS / JS) - ESTRUCTURA DE DOS COLUMNAS
 * =================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestor de Menú</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            /* Minimalist SaaS Palette (Linear / Vercel style) */
            --bg-body: #fafafa;
            --bg-surface: #ffffff;
            --bg-hover: #f3f4f6;
            
            /* High Contrast Accents */
            --primary: #000000;
            --primary-hover: #374151;
            --accent: #2563eb; /* Crisp Blue for highlights */
            --accent-light: #eff6ff;
            
            /* Typography */
            --text-main: #030712;
            --text-secondary: #4b5563;
            --text-muted: #9ca3af;
            
            /* Crisp Borders */
            --border-light: #e5e7eb;
            --border-focus: #d1d5db;
            
            /* Refined Shadows */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.03);
            --shadow-float: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            
            /* Professional Radii */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-pill: 9999px;
            
            --transition-fast: all 0.2s ease;
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Subtle Developer Grid Background */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 100vh;
            background-image: 
                linear-gradient(to right, rgba(0,0,0,0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0,0,0,0.03) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);
            -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 0%, rgba(0,0,0,0) 100%);
            z-index: -1;
            pointer-events: none;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 4rem;
        }

        /* --- TOP NAV --- */
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: fadeInDown 0.6s ease-out;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-logo .logo-icon {
            width: 36px; height: 36px;
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
            font-size: 1.1rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-fast);
        }

        .brand-logo h1 {
            font-family: 'Inter', sans-serif;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.02em;
        }

        .admin-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: var(--primary);
            border-radius: var(--radius-pill);
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-fast);
        }

        .admin-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* --- HERO SECTION --- */
        .hero-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            animation: fadeInUp 0.7s ease-out 0.1s both;
            position: relative;
        }
        
        .pill-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-pill);
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }
        .pill-badge i { color: var(--accent); }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 4.5rem;
            font-weight: 800;
            letter-spacing: -0.06em;
            line-height: 1;
            margin-bottom: 1.25rem;
            color: var(--text-main);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin-bottom: 3rem;
            font-weight: 400;
        }

        .search-wrapper {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin-bottom: 2rem;
        }

        .search-input {
            width: 100%;
            padding: 1.2rem 1.5rem 1.2rem 3.5rem;
            font-family: inherit;
            font-size: 1.1rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            color: var(--text-main);
            box-shadow: var(--shadow-md);
            transition: var(--transition-smooth);
            outline: none;
        }

        .search-input::placeholder { color: var(--text-muted); }

        .search-input:focus {
            border-color: var(--text-muted);
            box-shadow: var(--shadow-float);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            pointer-events: none;
            transition: var(--transition-fast);
        }

        .search-input:focus + .search-icon { color: var(--text-main); }

        .search-shortcut {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: var(--bg-body);
            border: 1px solid var(--border-light);
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.8rem;
            color: var(--text-secondary);
            pointer-events: none;
        }

        /* --- FAVORITOS (Chips) --- */
        .favorites-section {
            width: 100%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
            animation: fadeInUp 0.7s ease-out 0.2s both;
        }

        .favorites-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 600;
            color: var(--text-muted);
        }

        .favorites-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.75rem;
        }

        .fav-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem 0.4rem 0.4rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-pill);
            text-decoration: none;
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-fast);
        }

        .fav-chip:hover {
            background: var(--bg-hover);
            color: var(--text-main);
            border-color: var(--border-focus);
        }

        .fav-chip .icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 26px; height: 26px;
            background: var(--accent-light);
            color: var(--accent);
            border-radius: 50%;
            font-size: 0.8rem;
        }

        /* --- RESULTADOS --- */
        .results-section {
            width: 100%;
            animation: fadeInUp 0.7s ease-out 0.3s both;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-light);
        }

        .results-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.03em;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* --- TARJETAS MINIMALISTAS --- */
        .card {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: inherit;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-smooth);
            animation: fadeInUp 0.5s ease-out backwards;
        }

        .card:hover {
            border-color: var(--border-focus);
            box-shadow: var(--shadow-float);
            transform: translateY(-4px);
        }

        .card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px; height: 48px;
            background: var(--bg-body);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-md);
            color: var(--text-main);
            font-size: 1.25rem;
            flex-shrink: 0;
            transition: var(--transition-fast);
        }

        .card:hover .card-icon {
            background: var(--text-main);
            color: white;
            border-color: var(--text-main);
        }

        .card-content {
            flex: 1;
            min-width: 0;
        }

        .card-content h3 {
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.25rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            letter-spacing: -0.01em;
        }

        .card-content p {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* --- PAGINACIÓN --- */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
            padding-bottom: 2rem;
        }

        .page-btn {
            min-width: 40px; height: 40px;
            padding: 0 0.5rem;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-sm);
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition-fast);
        }

        .page-btn:hover:not(.disabled) {
            background: var(--bg-hover);
            color: var(--text-main);
            border-color: var(--border-focus);
        }

        .page-btn.active {
            background: var(--text-main);
            color: white;
            border-color: var(--text-main);
        }

        .page-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: transparent;
        }

        .hidden { display: none !important; }

        .loader-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 0;
            width: 100%;
        }

        .spinner {
            width: 40px; height: 40px;
            border: 2px solid var(--border-light);
            border-top-color: var(--text-main);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .empty-state {
            text-align: center;
            padding: 5rem 2rem;
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            width: 100%;
        }
        .empty-state i {
            font-size: 2.5rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title { font-size: 3rem; }
            .container { padding: 1.5rem; gap: 3rem; }
            .grid-layout { grid-template-columns: 1fr; }
            .search-shortcut { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- NAVEGACIÓN SUPERIOR -->
    <header class="top-nav">
        <a href="#" class="brand-logo">
            <div class="logo-icon"><i class="fa-solid fa-layer-group"></i></div>
            <h1>Vallesol</h1>
        </a>
        <a href="gestor_menu.php" class="admin-btn">
            Administrar Menú
        </a>
    </header>

    <!-- SECCIÓN HERO Y BUSCADOR -->
    <section class="hero-section">
        <div class="pill-badge"><i class="fa-solid fa-bolt"></i> Acceso rápido a todas tus herramientas</div>
        <h2 class="hero-title">Gestor de Menú</h2>
        <p class="hero-subtitle">Encuentra y organiza rápidamente los módulos, recursos y configuraciones de tu entorno educativo.</p>
        
        <div class="search-wrapper">
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar en el gestor..." autocomplete="off">
            <i class="fa-solid fa-magnifying-glass search-icon"></i>
            <div class="search-shortcut">Ctrl K</div>
        </div>

        <!-- Favoritos (Chips) -->
        <div id="favoritesSection" class="favorites-section hidden">
            <div class="favorites-label">Módulos Anclados</div>
            <div id="favoritesList" class="favorites-list">
                <!-- JS inyecta chips aquí -->
            </div>
        </div>
    </section>

    <!-- SECCIÓN DE RESULTADOS -->
    <section class="results-section">
        <div class="results-header">
            <h3 id="resultsTitle" class="results-title">Directorio de Módulos</h3>
            <span style="color: var(--text-muted); font-size: 0.9rem;">Catálogo General</span>
        </div>

        <div id="resultsGrid" class="grid-layout">
            <!-- JS inyecta tarjetas aquí -->
        </div>

        <div id="mainLoader" class="loader-container hidden">
            <div class="spinner"></div>
            <p style="color: var(--text-secondary); font-size: 0.9rem;">Cargando módulos...</p>
        </div>

        <div id="noResults" class="empty-state hidden">
            <i class="fa-regular fa-folder-open"></i>
            <h3>No se encontraron módulos</h3>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">No hay coincidencias para tu búsqueda actual.</p>
        </div>

        <div id="pagination" class="pagination"></div>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const state = { page: 1, search: '', loading: false };
    const refs = {
        input: document.getElementById('searchInput'),
        favSection: document.getElementById('favoritesSection'),
        favList: document.getElementById('favoritesList'),
        resGrid: document.getElementById('resultsGrid'),
        pagination: document.getElementById('pagination'),
        loader: document.getElementById('mainLoader'),
        noResults: document.getElementById('noResults'),
        resultsTitle: document.getElementById('resultsTitle')
    };
    let debounceTimer;

    // Atajo de teclado para el buscador
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            refs.input.focus();
        }
    });

    // Helper para renderizar ícono (clase FA o Emoji)
    const renderIcon = (iconString) => {
        const val = iconString ? iconString.trim() : '';
        if (val === '') return '<i class="fa-regular fa-folder"></i>';
        if (/^[a-zA-Z0-9\-\s]+$/.test(val) && (val.startsWith('fa') || val.startsWith('bx'))) {
            return `<i class="${val}"></i>`;
        }
        return val;
    };

    async function fetchData() {
        if (state.loading) return;
        state.loading = true;
        
        if (state.page === 1 && !state.search && refs.resGrid.children.length === 0) {
            refs.loader.classList.remove('hidden');
        } else {
            refs.resGrid.style.opacity = '0.5';
            refs.resGrid.style.transition = 'opacity 0.2s ease';
        }

        const params = new URLSearchParams({
            action: 'get_dashboard_data',
            page: state.page,
            search: state.search
        });

        try {
            const response = await fetch(`?${params.toString()}`);
            if(!response.ok) throw new Error('Error de servidor');
            const data = await response.json();
            if (data.status === 'success') {
                renderView(data);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            refs.resGrid.innerHTML = `<div class="empty-state"><i class="fa-solid fa-triangle-exclamation"></i><h3>Error de conexión</h3><p>No se pudo cargar la información.</p></div>`;
        } finally {
            state.loading = false;
            refs.resGrid.style.opacity = '1';
            refs.loader.classList.add('hidden');
        }
    }

    function renderView(data) {
        // Render Favoritos
        if (data.favorites && data.favorites.length > 0) {
            refs.favSection.classList.remove('hidden');
            refs.favList.innerHTML = data.favorites.map((item, index) => createFavChipHTML(item, index)).join('');
        } else {
            refs.favSection.classList.add('hidden');
        }

        // Render Resultados
        const results = data.results;
        if (results.length > 0) {
            refs.resGrid.innerHTML = results.map((item, index) => createMainCardHTML(item, index)).join('');
            refs.noResults.classList.add('hidden');
            renderPagination(data.pagination);
        } else {
            refs.resGrid.innerHTML = '';
            refs.noResults.classList.add('hidden');
            refs.pagination.innerHTML = '';
        }

        refs.resultsTitle.textContent = state.search ? `Resultados: "${state.search}"` : 'Directorio de Módulos';
    }

    function createFavChipHTML(item, index) {
        const delay = index * 0.03;
        return `
            <a href="${item.menu_url}" target="${item.url_target}" class="fav-chip" title="${item.menu_description || ''}" style="animation-delay: ${delay}s">
                <div class="icon">${renderIcon(item.icono)}</div>
                <span>${item.menu_item_name}</span>
            </a>
        `;
    }

    function createMainCardHTML(item, index) {
        const delay = index * 0.03;
        return `
            <a href="${item.menu_url}" target="${item.url_target}" class="card" style="animation-delay: ${delay}s">
                <div class="card-icon">
                    ${renderIcon(item.icono)}
                </div>
                <div class="card-content">
                    <h3>${item.menu_item_name}</h3>
                    <p>${item.menu_description || 'Explora este módulo para gestionar sus configuraciones.'}</p>
                </div>
            </a>
        `;
    }

    function renderPagination(meta) {
        const { total, current_page, per_page } = meta;
        const totalPages = Math.ceil(total / per_page);
        if (totalPages <= 1) {
            refs.pagination.innerHTML = '';
            return;
        }

        let html = '';
        
        // Anterior
        html += `<button class="page-btn ${current_page === 1 ? 'disabled' : ''}" 
                 onclick="changePage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''}>
                 <i class="fa-solid fa-chevron-left"></i>
                 </button>`;

        const start = Math.max(1, current_page - 2);
        const end = Math.min(totalPages, start + 4);

        if (start > 1) {
            html += `<button class="page-btn" onclick="changePage(1)">1</button>`;
            if (start > 2) html += `<span style="color: var(--text-muted); padding: 0 0.25rem;">...</span>`;
        }

        for (let i = start; i <= end; i++) {
            html += `<button class="page-btn ${i === current_page ? 'active' : ''}" 
                     onclick="changePage(${i})">${i}</button>`;
        }

        if (end < totalPages) {
            if (end < totalPages - 1) html += `<span style="color: var(--text-muted); padding: 0 0.25rem;">...</span>`;
            html += `<button class="page-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
        }

        // Siguiente
        html += `<button class="page-btn ${current_page === totalPages ? 'disabled' : ''}" 
                 onclick="changePage(${current_page + 1})" ${current_page === totalPages ? 'disabled' : ''}>
                 <i class="fa-solid fa-chevron-right"></i>
                 </button>`;

        refs.pagination.innerHTML = html;
    }

    window.changePage = (newPage) => {
        if (newPage < 1 || state.loading) return;
        state.page = newPage;
        fetchData();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    refs.input.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.search = e.target.value;
            state.page = 1;
            fetchData();
        }, 350);
    });

    // Carga inicial
    fetchData();
});
</script>
</body>
</html>
<?php
$contenido = ob_get_clean();
require __DIR__ . "/../comun/plantilla.php";
?>