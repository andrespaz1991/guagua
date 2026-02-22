<?php
/**
 * =================================================================
 * MÓDULO DE PLANEACIÓN - DASHBOARD V2.1 (ALTA VISIBILIDAD)
 * =================================================================
 *
 * Actualización:
 * - Aumento general de escala tipográfica para accesibilidad.
 * - Tarjetas y zonas de clic expandidas.
 * - Mantiene lógica de separación Favoritos/Búsqueda.
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
// 2. ENDPOINT AJAX (LÓGICA INTACTA)
// =================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_dashboard_data') {
    header('Content-Type: application/json');

    try {
        $recordsPerPage = 10;
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
 * VISTA (HTML / CSS / JS) - ESTILOS AMPLIADOS
 * =================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><a href='gestor_menu.php'>Gestor de menú</a></title>
    <style>
        :root {
            /* Paleta de Colores */
            --bg-body: #f4f6f9;
            --bg-card: #ffffff;
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --accent: #d97706; /* Naranja un poco más oscuro para contraste */
            --text-main: #111827; /* Casi negro para máximo contraste */
            --text-muted: #4b5563; /* Gris oscuro, no tan claro */
            --border-light: #e5e7eb;
            
            /* Sombras suaves pero definidas */
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            
            --radius: 16px; /* Bordes más redondeados */
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            /* AUMENTO DE FUENTE BASE: De ~16px a 18px */
            font-size: 18px; 
            line-height: 1.6;
        }

        .container {
            max-width: 1400px; /* Contenedor más ancho */
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* --- Header y Buscador Grande --- */
        .dashboard-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .dashboard-header h1 {
            /* Título muy grande y claro */
            font-size: 3.5rem; 
            font-weight: 800;
            color: var(--primary-dark);
            margin-bottom: 2rem;
            letter-spacing: -0.03em;
        }

        .search-container {
            max-width: 700px; /* Buscador más ancho */
            margin: 0 auto;
            position: relative;
        }

        .search-input {
            width: 100%;
            /* Padding generoso para facilitar clic */
            padding: 1.25rem 2rem 1.25rem 4rem; 
            font-size: 1.4rem; /* Texto grande al escribir */
            border: 2px solid #cbd5e1; /* Borde más visible */
            border-radius: 60px;
            background: white;
            box-shadow: var(--shadow-card);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem; /* Lupa más grande */
            color: var(--text-muted);
            pointer-events: none;
        }

        /* --- Títulos de Sección --- */
        .section-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 2rem; /* Títulos de sección más grandes */
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-main);
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--border-light);
        }

        .section-icon { font-size: 2rem; }

        /* --- Grilla Ampliada --- */
        .grid-layout {
            display: grid;
            /* AUMENTO: Tarjetas mínimo 340px de ancho (antes 280px) */
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 2rem; /* Más espacio entre tarjetas */
            margin-bottom: 4rem;
        }

        /* --- Tarjetas Grandes --- */
        .card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 2rem; /* Más relleno interno */
            text-decoration: none;
            color: inherit;
            border: 1px solid var(--border-light);
            transition: transform 0.25s, box-shadow 0.25s;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-sizing: border-box;
            position: relative;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
            border-color: var(--primary);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .card-icon {
            /* AUMENTO: Iconos mucho más grandes */
            font-size: 4rem; 
            line-height: 1;
        }

        .fav-badge {
            font-size: 1.8rem; /* Estrella más grande */
            color: #cbd5e1;
        }
        
        .card.is-favorite .fav-badge {
            color: var(--accent);
        }
        
        .favorites-section .card {
            border-top: 6px solid var(--accent); /* Borde superior más grueso */
            background: linear-gradient(to bottom, #ffffff, #fffaf0);
        }

        .card h3 {
            margin: 0 0 1rem 0;
            font-size: 1.6rem; /* Título de tarjeta más grande */
            font-weight: 700;
            color: var(--primary); /* Azul para resaltar */
            line-height: 1.3;
        }

        .card p {
            margin: 0;
            font-size: 1.15rem; /* Descripción legible */
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* --- Paginación Grande --- */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .page-btn {
            min-width: 55px; /* Botones más anchos */
            height: 55px;   /* Botones más altos */
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: white;
            border: 2px solid var(--border-light);
            color: var(--text-main);
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 600;
        }

        .page-btn:hover:not(.disabled) {
            border-color: var(--primary);
            color: var(--primary);
            background: #eff6ff;
            transform: scale(1.1); /* Efecto zoom al pasar mouse */
        }

        .page-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .page-btn.disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* --- Utilidades --- */
        .hidden { display: none !important; }
        .empty-state { text-align: center; padding: 4rem; font-size: 1.3rem; color: var(--text-muted); }
        .loader { width: 50px; height: 50px; border: 5px solid #e2e8f0; border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 2rem auto; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Ajuste Mobile para que no se rompa la grilla grande */
        @media (max-width: 600px) {
            .grid-layout {
                grid-template-columns: 1fr; /* Una sola columna en móviles */
            }
            .dashboard-header h1 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <header class="dashboard-header">
        <h1>Gestor de menú</h1>
        <a href='gestor_menu.php'>Administrar menú</a>
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar..." autocomplete="off">
        </div>
    </header>

    <!-- Estado de Carga -->
    <div id="mainLoader" style="text-align: center; display: none;">
        <div class="loader"></div>
        <p style="font-size: 1.2rem;">Cargando recursos...</p>
    </div>

    <!-- Sección: Favoritos (Accesos Rápidos) -->
    <section id="favoritesSection" class="favorites-section hidden">
        <div class="section-title">
            <span class="section-icon">⭐</span>
            <span>Accesos Rápidos</span>
        </div>
        <div id="favoritesGrid" class="grid-layout">
            <!-- JS inyectará favoritos aquí -->
        </div>
    </section>

    <!-- Sección: Resultados / Todos -->
    <section id="resultsSection">
        <div class="section-title">
            <span class="section-icon">📚</span>
            <span id="resultsTitle">Explorar Todo</span>
        </div>
        
        <div id="resultsGrid" class="grid-layout">
            <!-- JS inyectará resultados aquí -->
        </div>

        <div id="noResults" class="empty-state hidden">
            <h3>No encontramos coincidencias</h3>
            <p>Intenta con otros términos.</p>
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
        favGrid: document.getElementById('favoritesGrid'),
        resGrid: document.getElementById('resultsGrid'),
        pagination: document.getElementById('pagination'),
        loader: document.getElementById('mainLoader'),
        noResults: document.getElementById('noResults'),
        resultsTitle: document.getElementById('resultsTitle')
    };
    let debounceTimer;

    async function fetchData() {
        if (state.loading) return;
        state.loading = true;
        refs.resGrid.style.opacity = '0.5';
        if (state.page === 1 && !state.search) refs.loader.style.display = 'block';

        const params = new URLSearchParams({
            action: 'get_dashboard_data',
            page: state.page,
            search: state.search
        });

        try {
            const response = await fetch(`?${params.toString()}`);
            if(!response.ok) throw new Error('Error en servidor');
            const data = await response.json();
            if (data.status === 'success') {
                renderView(data);
            }
        } catch (error) {
            console.error('Fetch error:', error);
            refs.resGrid.innerHTML = `<div class="empty-state">Error de conexión.</div>`;
        } finally {
            state.loading = false;
            refs.resGrid.style.opacity = '1';
            refs.loader.style.display = 'none';
        }
    }

    function renderView(data) {
        if (data.favorites && data.favorites.length > 0) {
            refs.favSection.classList.remove('hidden');
            refs.favGrid.innerHTML = data.favorites.map(item => createCardHTML(item, true)).join('');
        } else {
            refs.favSection.classList.add('hidden');
        }

        const results = data.results;
        if (results.length > 0) {
            refs.resGrid.innerHTML = results.map(item => createCardHTML(item, false)).join('');
            refs.noResults.classList.add('hidden');
            renderPagination(data.pagination);
        } else {
            refs.resGrid.innerHTML = '';
            refs.noResults.classList.remove('hidden');
            refs.pagination.innerHTML = '';
        }

        refs.resultsTitle.textContent = state.search ? `Resultados: "${state.search}"` : 'Explorar Todo';
    }

    function createCardHTML(item, isFav) {
        const favClass = isFav ? 'is-favorite' : '';
        const favIcon = isFav ? '★' : '☆';
        // Si no hay icono, usamos uno genérico grande
        const iconDisplay = item.icono ? item.icono : '📄'; 
        
        return `
            <a href="${item.menu_url}" target="${item.url_target}" class="card ${favClass}">
                <div class="card-header">
                    <span class="card-icon">${iconDisplay}</span>
                    <span class="fav-badge">${favIcon}</span>
                </div>
                <h3>${item.menu_item_name}</h3>
                <p>${item.menu_description || ''}</p>
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
        html += `<button class="page-btn ${current_page === 1 ? 'disabled' : ''}" 
                 onclick="changePage(${current_page - 1})" ${current_page === 1 ? 'disabled' : ''}>◀</button>`;

        const start = Math.max(1, current_page - 2);
        const end = Math.min(totalPages, start + 4);

        for (let i = start; i <= end; i++) {
            html += `<button class="page-btn ${i === current_page ? 'active' : ''}" 
                     onclick="changePage(${i})">${i}</button>`;
        }

        html += `<button class="page-btn ${current_page === totalPages ? 'disabled' : ''}" 
                 onclick="changePage(${current_page + 1})" ${current_page === totalPages ? 'disabled' : ''}>▶</button>`;

        refs.pagination.innerHTML = html;
    }

    window.changePage = (newPage) => {
        if (newPage < 1 || state.loading) return;
        state.page = newPage;
        fetchData();
        document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth' });
    };

    refs.input.addEventListener('input', (e) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            state.search = e.target.value;
            state.page = 1;
            fetchData();
        }, 300);
    });

    fetchData();
});
</script>
</body>
</html>
<?php
$contenido = ob_get_clean();
require __DIR__ . "/../comun/plantilla.php";
?>