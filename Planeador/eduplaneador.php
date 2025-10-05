<?php
/**
 * =================================================================
 * MÓDULO DE PLANEACIÓN - VERSIÓN CON PAGINACIÓN ASINCRÓNICA
 * =================================================================
 *
 * Este módulo carga las opciones de planeación desde la base de datos
 * e implementa paginación numérica y búsqueda en tiempo real sin
 * recargar la página.
 *
 * Características:
 * 1.  Endpoint AJAX: Maneja las solicitudes de datos de forma asíncrona.
 * 2.  Paginación Numérica: Permite navegar entre páginas de resultados.
 * 3.  Búsqueda Server-Side: La búsqueda se realiza en la base de datos
 * para un rendimiento óptimo.
 * 4.  Código Seguro: Utiliza sentencias preparadas (mysqli).
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/funciones.php';

// =================================================================
// 2. ENDPOINT AJAX PARA BÚSQUEDA Y PAGINACIÓN
// =================================================================
if (isset($_GET['action']) && $_GET['action'] == 'get_menu_items') {
    header('Content-Type: application/json');

    $recordsPerPage = 6; // Cantidad de tarjetas por página
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    $offset = ($page - 1) * $recordsPerPage;

    // --- Consulta para contar el total de registros ---
    $totalSql = "SELECT COUNT(menu_item_id) as total FROM menu_items2 WHERE menu_item_name LIKE ?";
    $stmtTotal = $mysqli->prepare($totalSql);
    $stmtTotal->bind_param('s', $searchTerm);
    $stmtTotal->execute();
    $totalResult = $stmtTotal->get_result()->fetch_assoc();
    $totalRecords = $totalResult['total'];
    $stmtTotal->close();

    // --- Consulta para obtener los datos de la página actual ---
    $dataSql = "SELECT menu_item_name, menu_description, menu_url, icono, url_target 
                FROM menu_items2 
                WHERE menu_item_name LIKE ? 
                ORDER BY menu_item_name ASC 
                LIMIT ? OFFSET ?";
    
    $stmtData = $mysqli->prepare($dataSql);
    $stmtData->bind_param('sii', $searchTerm, $recordsPerPage, $offset);
    $stmtData->execute();
    $resultado = $stmtData->get_result();
    $opciones = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmtData->close();

    echo json_encode(['data' => $opciones, 'total' => $totalRecords]);
    exit;
}

/**
 * =================================================================
 * INICIO DE LA VISTA (HTML, CSS, JS)
 * =================================================================
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Planeación Dinámico</title>
    <style>
        :root {
            --primary-color: #2c5282; --accent-color: #ed8936;
            --background-color: #f7fafc; --card-background: #ffffff;
            --text-color: #2d3748; --light-gray: #e2e8f0;
            --shadow-color: rgba(0, 0, 0, 0.08);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color); color: var(--text-color);
            margin: 0; padding: 0;
        }
        .main-container { max-width: 1200px; margin: 20px auto; padding: 0 20px; }
        .jumbotron {
            background: linear-gradient(135deg, var(--primary-color), #1a365d);
            color: white; border-radius: 12px; padding: 40px;
            margin-bottom: 40px; text-align: center;
            box-shadow: 0 4px 15px rgba(44, 82, 130, 0.4);
        }
        .jumbotron h1 { margin: 0 0 20px 0; font-size: 2.8em; font-weight: 700; }
        .search-wrapper { position: relative; max-width: 500px; margin: 0 auto; }
        #opcion-search-input {
            width: 100%; padding: 12px 20px 12px 45px; border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.5); background-color: rgba(255, 255, 255, 0.2);
            color: white; font-size: 1em; transition: background-color 0.3s, box-shadow 0.3s;
        }
        #opcion-search-input::placeholder { color: rgba(255, 255, 255, 0.7); }
        #opcion-search-input:focus { outline: none; background-color: rgba(255, 255, 255, 0.3); box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3); }
        .search-wrapper::before { content: '🔍'; position: absolute; left: 15px; top: 50%; transform: translateY(-50%); opacity: 0.7; }
        .opciones-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 25px; min-height: 300px; }
        .opcion-card {
            background-color: var(--card-background); border-radius: 12px; padding: 25px;
            text-decoration: none; color: var(--text-color); box-shadow: 0 4px 10px var(--shadow-color);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        .opcion-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px var(--shadow-color); }
        .opcion-card .icono { font-size: 4rem; line-height: 1; margin-bottom: 1rem; }
        .opcion-card h3 { font-size: 1.4em; font-weight: 600; margin: 0 0 0.5rem 0; color: var(--primary-color); }
        .opcion-card p { font-size: 0.95em; color: #555; line-height: 1.5; }
        .no-resultados { text-align: center; padding: 50px; font-size: 1.2em; color: #777; display: none; }
        #pagination-controls { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; }
        .page-link {
            padding: 8px 14px; border: 1px solid var(--light-gray); border-radius: 8px;
            background-color: #fff; color: var(--primary-color); text-decoration: none;
            cursor: pointer; transition: all 0.2s; font-weight: 500;
        }
        .page-link:hover { background-color: #edf2f7; border-color: #cbd5e0; }
        .page-link.active { background-color: var(--primary-color); color: #fff; border-color: var(--primary-color); cursor: default; }
        .page-link.disabled { color: #a0aec0; cursor: not-allowed; background-color: #f7fafc; }
        #loading-spinner { text-align: center; padding: 50px; font-size: 1.2em; color: #777; display: none; }
    </style>
</head>
<body>

<div class="main-container">
    <div class="jumbotron">
        <h1>Centro de Planeación</h1>
        <div class="search-wrapper">
            <input type="search" id="opcion-search-input" placeholder="Buscar opción de planeación...">
        </div>
    </div>
    
    <div id="opciones-grid" class="opciones-grid"></div>
    <div id="loading-spinner">Cargando...</div>
    <div id="no-resultados" class="no-resultados"><p>No se encontraron opciones que coincidan.</p></div>
    <div id="pagination-controls"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('opcion-search-input');
    const opcionesGrid = document.getElementById('opciones-grid');
    const noResultados = document.getElementById('no-resultados');
    const paginationControls = document.getElementById('pagination-controls');
    const loadingSpinner = document.getElementById('loading-spinner');
    
    const recordsPerPage = 6;
    let searchDebounceTimer;

    async function fetchMenuItems(page = 1, searchTerm = '') {
        loadingSpinner.style.display = 'block';
        opcionesGrid.innerHTML = '';
        noResultados.style.display = 'none';
        paginationControls.innerHTML = '';

        const params = new URLSearchParams({
            action: 'get_menu_items',
            page: page,
            search: searchTerm.trim()
        });

        try {
            const response = await fetch(`?${params.toString()}`);
            if (!response.ok) throw new Error('Network response was not ok.');
            
            const { data, total } = await response.json();

            if (data.length > 0) {
                renderMenuItems(data);
                renderPaginationControls(total, page);
            } else {
                noResultados.style.display = 'block';
            }
        } catch (error) {
            console.error('Error al obtener los ítems del menú:', error);
            opcionesGrid.innerHTML = '<p style="text-align:center; color: red;">Error al cargar los datos.</p>';
        } finally {
            loadingSpinner.style.display = 'none';
        }
    }

    function renderMenuItems(items) {
        let content = '';
        items.forEach(opcion => {
            content += `
                <a target="${opcion.url_target}" href="${opcion.menu_url}" class="opcion-card" data-nombre="${opcion.menu_item_name}">
                    <div class="icono">${opcion.icono}</div>
                    <h3>${opcion.menu_item_name}</h3>
                    <p>${opcion.menu_description}</p>
                </a>`;
        });
        opcionesGrid.innerHTML = content;
    }

    function renderPaginationControls(total, currentPage) {
        const totalPages = Math.ceil(total / recordsPerPage);
        if (totalPages <= 1) return;

        let html = `<a href="#" class="page-link ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">&laquo;</a>`;
        for (let i = 1; i <= totalPages; i++) {
            html += `<a href="#" class="page-link ${i === currentPage ? 'active' : ''}" data-page="${i}">${i}</a>`;
        }
        html += `<a href="#" class="page-link ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}">&raquo;</a>`;
        
        paginationControls.innerHTML = html;
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchMenuItems(1, this.value);
        }, 300); // Espera 300ms después de que el usuario deja de escribir
    });

    paginationControls.addEventListener('click', e => {
        e.preventDefault();
        const target = e.target.closest('.page-link');
        if (target && !target.classList.contains('disabled') && !target.classList.contains('active')) {
            const page = parseInt(target.dataset.page, 10);
            fetchMenuItems(page, searchInput.value);
        }
    });

    // Carga inicial
    fetchMenuItems(1);
});
</script>

</body>
</html>
<?php
$contenido = ob_get_clean();
require __DIR__ . "/../comun/plantilla.php";
?>

