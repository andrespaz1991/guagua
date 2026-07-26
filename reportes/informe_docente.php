<?php
/**
 * =================================================================
 * DASHBOARD ANALÍTICO DOCENTE - IER JOSEFINA (SEDE VALLESOL)
 * =================================================================
 * Año Lectivo: 2026
 * Modelo: Postprimaria y Media Rural
 * Desarrollado con enfoque UX/UI Profesional (Tailwind CSS + Chart.js)
 */

session_start();
require_once("../comun/conexion.php"); // Descomentar en producción

// =================================================================
// LÓGICA DE BACKEND (API INTERNA)
// =================================================================
// Si se detecta una petición AJAX para obtener datos, devolvemos JSON
if (isset($_GET['action']) && $_GET['action'] === 'get_demographics') {
    header('Content-Type: application/json');

    $anio = 2026;
    
    // SQL dinámico usando sexo y fecha_registro según el esquema real
    $sql = "SELECT 
                observaciones AS grado, 
                COUNT(id_usuario) as total,
                SUM(CASE WHEN UPPER(genero) = 'M' THEN 1 ELSE 0 END) as total_hombres,
                SUM(CASE WHEN UPPER(genero) = 'F' THEN 1 ELSE 0 END) as total_mujeres
            FROM usuario 
            WHERE rol = 'estudiante' 
              AND estado != 'inactivo' 
              AND YEAR(fecha_creacion) = '$anio' 
              AND observaciones IS NOT NULL 
              AND observaciones != ''
            GROUP BY observaciones 
            ORDER BY (observaciones + 0) ASC"; // ASC para que ordene 6, 7, 8...

    $consulta = $mysqli->query($sql);

    // Variables acumuladoras para los KPIs generales
    $total_estudiantes_kpi = 0;
    $total_hombres_kpi = 0;
    $total_mujeres_kpi = 0;
    $array_por_grado = [];

    if ($consulta) {
        while ($row = $consulta->fetch_assoc()) {
            // Parsear a enteros para seguridad y cálculos precisos
            $t = (int)$row['total'];
            $h = (int)$row['total_hombres'];
            $m = (int)$row['total_mujeres'];
            
            // Sumar a los KPIs globales
            $total_estudiantes_kpi += $t;
            $total_hombres_kpi += $h;
            $total_mujeres_kpi += $m;
            
            // Llenar el array por grado
            $array_por_grado[] = [
                'grado'   => 'Grado ' . $row['grado'],
                'total'   => $t,
                'hombres' => $h,
                'mujeres' => $m
            ];
        }
    }

    // Construcción final del objeto JSON a devolver
    $demographics = [
        'kpis' => [
            'total_estudiantes' => $total_estudiantes_kpi,
            'total_hombres'     => $total_hombres_kpi,
            'total_mujeres'     => $total_mujeres_kpi
        ],
        'por_grado' => $array_por_grado
    ];

    echo json_encode($demographics);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Docente - Vallesol 2026</title>
    
    <!-- Fuentes y Estilos -->
    <link href="../comun/css/fontsgoogle.css" rel="stylesheet">
    <script src="../comun/css/tailwindcss.css"></script>
    <script src="../comun/js/chart.js"></script>
    
    <!-- Tailwind Config Personalizada para UX -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        },
                        men: '#3b82f6',   // Azul profesional
                        women: '#ea77f2'  // Esmeralda profesional
                    }
                }
            }
        }
    </script>
    <style>
        body { background-color: #f8fafc; }
        .card-hover { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    </style>
</head>
<body class="text-slate-800 antialiased">

    <!-- Navegación / Header Superior -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-3">
                    <div class="bg-brand-600 p-2 rounded-lg text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-xl text-slate-900 leading-tight">Dashboard Analítico</h1>
                        <p class="text-xs text-slate-500 font-medium">IER Josefina - Sede Vallesol (Postprimaria y Media)</p>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="bg-brand-100 text-brand-900 text-xs font-semibold px-3 py-1 rounded-full">Año Lectivo 2026</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- KPIs (Key Performance Indicators) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="kpi-container">
            <!-- Cargando KPIs... -->
            <div class="col-span-3 flex justify-center py-4">
                <svg class="animate-spin h-8 w-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </div>
        </div>

        <!-- Sección de Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            
            <!-- Gráfico 1: Estudiantes por Grado -->
            <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 shadow-sm card-hover">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold text-slate-800">Población por Grado</h2>
                    <span class="text-sm text-slate-500 font-medium">Distribución Total</span>
                </div>
                <div class="relative h-72">
                    <canvas id="chartGrados"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Distribución por Género (General) -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm card-hover flex flex-col">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-slate-800">Proporción de Género</h2>
                    <span class="text-sm text-slate-500 font-medium">Sede Vallesol</span>
                </div>
                <div class="relative flex-grow flex items-center justify-center h-64">
                    <canvas id="chartGenero"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico 3: Desglose de Género por Grado -->
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm card-hover">
            <div class="mb-4">
                <h2 class="text-lg font-bold text-slate-800">Análisis Demográfico Detallado</h2>
                <span class="text-sm text-slate-500 font-medium">Hombres vs Mujeres por Grado Escolar</span>
            </div>
            <div class="relative h-80">
                <canvas id="chartGeneroGrado"></canvas>
            </div>
        </div>

    </main>

    <!-- Scripts de Lógica y Renderizado de Gráficos -->
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            
            // 1. Configuración global de Chart.js
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = '#64748b';
            Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15, 23, 42, 0.9)';
            Chart.defaults.plugins.tooltip.padding = 12;
            Chart.defaults.plugins.tooltip.cornerRadius = 8;

            try {
                // 2. Obtener datos del backend
                const response = await fetch('?action=get_demographics');
                const data = await response.json();

                // 3. Renderizar KPIs
                renderKPIs(data.kpis);

                // 4. Preparar Datos para Gráficos
                const labelsGrados = data.por_grado.map(item => item.grado);
                const dataTotal = data.por_grado.map(item => item.total);
                const dataHombres = data.por_grado.map(item => item.hombres);
                const dataMujeres = data.por_grado.map(item => item.mujeres);

                // 5. Inicializar Gráficos
                initChartGrados(labelsGrados, dataTotal);
                initChartGenero(data.kpis.total_hombres, data.kpis.total_mujeres);
                initChartGeneroGrado(labelsGrados, dataHombres, dataMujeres);

            } catch (error) {
                console.error("Error cargando los datos demográficos:", error);
                document.getElementById('kpi-container').innerHTML = `
                    <div class="col-span-3 bg-red-50 text-red-600 p-4 rounded-lg border border-red-200 text-center font-medium">
                        Error al conectar con la base de datos de Vallesol.
                    </div>`;
            }
        });

        // ==========================================
        // FUNCIONES DE RENDERIZADO UI
        // ==========================================

        function renderKPIs(kpis) {
            const container = document.getElementById('kpi-container');
            container.innerHTML = `
                <!-- Tarjeta Total -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm card-hover flex items-center gap-4 border-l-4 border-l-brand-500">
                    <div class="bg-brand-50 p-4 rounded-full text-brand-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Matriculados</p>
                        <h3 class="text-3xl font-bold text-slate-800">${kpis.total_estudiantes}</h3>
                    </div>
                </div>

                <!-- Tarjeta Hombres -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm card-hover flex items-center gap-4 border-l-4 border-l-blue-500">
                    <div class="bg-blue-50 p-4 rounded-full text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Hombres</p>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-3xl font-bold text-slate-800">${kpis.total_hombres}</h3>
                            <span class="text-sm font-semibold text-blue-500">${((kpis.total_hombres / kpis.total_estudiantes) * 100).toFixed(1)}%</span>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Mujeres -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm card-hover flex items-center gap-4 border-l-4 border-l-emerald-500">
                    <div class="bg-emerald-50 p-4 rounded-full text-emerald-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-500">Mujeres</p>
                        <div class="flex items-baseline gap-2">
                            <h3 class="text-3xl font-bold text-slate-800">${kpis.total_mujeres}</h3>
                            <span class="text-sm font-semibold text-emerald-500">${((kpis.total_mujeres / kpis.total_estudiantes) * 100).toFixed(1)}%</span>
                        </div>
                    </div>
                </div>
            `;
        }

        function initChartGrados(labels, data) {
            const ctx = document.getElementById('chartGrados').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Estudiantes Inscritos',
                        data: data,
                        backgroundColor: '#0ea5e9',
                        borderRadius: 6,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [4, 4], color: '#e2e8f0' }, ticks: { stepSize: 1 } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        function initChartGenero(hombres, mujeres) {
            const ctx = document.getElementById('chartGenero').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Hombres', 'Mujeres'],
                    datasets: [{
                        data: [hombres, mujeres],
                        backgroundColor: ['#3b82f6', '#f566f2'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    }
                }
            });
        }

        function initChartGeneroGrado(labels, dataHombres, dataMujeres) {
            const ctx = document.getElementById('chartGeneroGrado').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Hombres',
                            data: dataHombres,
                            backgroundColor: '#3b82f6',
                            borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 4, bottomRight: 4 },
                        },
                        {
                            label: 'Mujeres',
                            data: dataMujeres,
                            backgroundColor: '#f772e1',
                            borderRadius: { topLeft: 4, topRight: 4, bottomLeft: 0, bottomRight: 0 },
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } }
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 }, grid: { borderDash: [4, 4], color: '#e2e8f0' } }
                    }
                }
            });
        }
    </script>
</body>
</html>