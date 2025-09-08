<?php
/**
 * =================================================================
 * MÓDULO DE PLANEACIÓN - VERSIÓN OPTIMIZADA
 * =================================================================
 *
 * Este módulo actúa como un centro de control para las opciones de planeación.
 *
 * Características:
 * 1.  Independiente de la Base de Datos: Las opciones se definen en un array de PHP.
 * 2.  Búsqueda Asíncrona: Un campo de búsqueda filtra las opciones en tiempo real sin recargar la página.
 * 3.  UI/UX Moderna: Interfaz limpia, con tarjetas interactivas y diseño adaptable (responsive).
 * 4.  Código Auto-contenido: Todo el HTML, CSS y JavaScript está en este único archivo.
 */

// 1. GESTIÓN DE SESIÓN Y CONFIGURACIÓN INICIAL
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../comun/funciones.php'; // Para funciones como 'puntos_suspensivos' si se usan.

// 2. DEFINICIÓN DE DATOS (SIN BASE DE DATOS)
// Las opciones de planeación se gestionan en este array.
// Para añadir una nueva opción, simplemente agrega un nuevo elemento al array.
$opciones_planeacion = [
    [
        'nombre' => 'Calendario',
        'icono' => '📅',
        'enlace' => 'calendario.php', // Enlace de destino
        'descripcion' => 'Visualiza y organiza tus eventos y plazos de planeación.'
    ],
    [
        'nombre' => 'Nueva Planeación',
        'icono' => '📝',
        'enlace' => 'planeador_vallesol.php', // Enlace al CRUD de planes
        'descripcion' => 'Crea un nuevo plan de clase detallado desde cero.'
    ],
    [
        'nombre' => 'Búsqueda por Plan',
        'icono' => '🔍',
        'enlace' => '../miplaneador/reporte_planeador.php', // Enlace a la vista de todos los planes
        'descripcion' => 'Busca y filtra entre todos los planes de clase existentes.'
    ],
    [
        'nombre' => 'Unir Diarios',
        'icono' => '🔗',
        'enlace' => 'unir.php',
        'descripcion' => 'Consolida múltiples diarios de campo en un solo documento.'
    ],
    [
        'nombre' => 'Importar Malla',
        'icono' => '📥',
        'enlace' => 'importador_malla.php',
        'descripcion' => 'Importa estructuras curriculares y mallas de forma masiva.'
    ],
];

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
    <title>Centro de Planeación</title>
    <style>
        :root {
            --primary-color: #2c5282; /* Azul oscuro para un look más profesional */
            --accent-color: #ed8936;   /* Naranja para acentos */
            --background-color: #f7fafc;
            --card-background: #ffffff;
            --text-color: #2d3748;
            --light-gray: #e2e8f0;
            --shadow-color: rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }

        .main-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .jumbotron {
            background: linear-gradient(135deg, var(--primary-color), #1a365d);
            color: white;
            border-radius: 12px;
            padding: 40px;
            margin-bottom: 40px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(44, 82, 130, 0.4);
        }
        .jumbotron h1 {
            margin: 0 0 20px 0;
            font-size: 2.8em;
            font-weight: 700;
        }
        
        .search-wrapper {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }
        
        #opcion-search-input {
            width: 100%;
            padding: 12px 20px 12px 45px;
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 1em;
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        #opcion-search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        #opcion-search-input:focus {
            outline: none;
            background-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .search-wrapper::before {
            content: '🔍';
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.7;
        }
        
        .opciones-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
        }

        .opcion-card {
            background-color: var(--card-background);
            border-radius: 12px;
            padding: 25px;
            text-decoration: none;
            color: var(--text-color);
            box-shadow: 0 4px 10px var(--shadow-color);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .opcion-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px var(--shadow-color);
        }

        .opcion-card .icono {
            font-size: 4rem;
            line-height: 1;
            margin-bottom: 1rem;
        }

        .opcion-card h3 {
            font-size: 1.4em;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
            color: var(--primary-color);
        }

        .opcion-card p {
            font-size: 0.95em;
            color: #555;
            line-height: 1.5;
        }

        .no-resultados {
            text-align: center;
            padding: 50px;
            font-size: 1.2em;
            color: #777;
            display: none; /* Oculto por defecto */
        }
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
    
    <div id="opciones-grid" class="opciones-grid">
        <?php foreach ($opciones_planeacion as $opcion): ?>
            <a target='_blank' href="<?= htmlspecialchars($opcion['enlace']) ?>" class="opcion-card" data-nombre="<?= htmlspecialchars($opcion['nombre']) ?>">
                <div class="icono"><?= htmlspecialchars($opcion['icono']) ?></div>
                <h3><?= htmlspecialchars($opcion['nombre']) ?></h3>
                <p><?= htmlspecialchars($opcion['descripcion']) ?></p>
            </a>
        <?php endforeach; ?>
    </div>

    <div id="no-resultados" class="no-resultados">
        <p>No se encontraron opciones que coincidan con tu búsqueda.</p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('opcion-search-input');
    const opcionesGrid = document.getElementById('opciones-grid');
    const noResultados = document.getElementById('no-resultados');
    const cards = opcionesGrid.getElementsByClassName('opcion-card');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.trim().toLowerCase();
        let visibleCount = 0;

        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const nombre = card.dataset.nombre.toLowerCase();
            
            if (nombre.includes(searchTerm)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        }
        
        if (visibleCount === 0) {
            noResultados.style.display = 'block';
        } else {
            noResultados.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
<?php
$contenido = ob_get_clean();
require __DIR__ . "/../comun/plantilla.php";
?>
