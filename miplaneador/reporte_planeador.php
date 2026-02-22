<?php 
ob_start();
echo '<center>';
// Ya no se necesita la conexión a la base de datos aquí directamente para la tabla.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Registros Paginada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            width: 90%;
            max-width: 1200px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .form-container {
            width: 100%;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-sizing: border-box;
        }
        .form-container form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .form-container label {
            font-weight: bold;
        }
        .form-container select, .form-container input[type="submit"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-container input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            border: none;
            transition: background-color 0.3s ease;
        }
        .form-container input[type="submit"]:hover {
            background-color: #218838;
        }
        .pagination-container {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .pagination-container button, .pagination-container span {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            background-color: #fff;
        }
        .pagination-container button:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            color: #6c757d;
        }
        .pagination-container span.current-page {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
            font-weight: bold;
        }
        .loader {
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top: 8px solid #3498db;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Lista de Registros</h2>

        <div class="form-container">
            <form action="../Planeador/planeador.php" method="get" target="_blank">
                <label for="mes">Mes:</label>
                <select name="mes" id="mes">
                    <option value="enero">Enero</option>
                    <option value="febrero">Febrero</option>
                    <option value="marzo">Marzo</option>
                    <option value="abril">Abril</option>
                    <option value="mayo">Mayo</option>
                    <option value="junio">Junio</option>
                    <option value="julio">Julio</option>
                    <option value="agosto">Agosto</option>
                    <option value="septiembre">Septiembre</option>
                    <option value="octubre">Octubre</option>
                    <option value="noviembre">Noviembre</option>
                    <option value="diciembre">Diciembre</option>
                </select>

                <label for="anio">Año:</label>
                <select name="anio" id="anio">
                    <?php
                    $anio_actual = date("Y");
                    for ($i = $anio_actual; $i >= $anio_actual - 10; $i--) {
                        echo "<option value=\"$i\">$i</option>";
                    }
                    ?>
                </select>

                <input type="submit" value="Generar Reporte">
            </form>
        </div>
        
        <div class="loader" id="loader"></div>
        
        <table>
            <thead>
                <tr>
                    <th>ID Plan</th>
                    <th>Fecha de Creación</th>
                    <th>Fecha de Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Grado</th>
                    <th>Materia</th>
                    <th>DBA</th>
                    <th>Descargar</th>
                </tr>
            </thead>
            <tbody id="tabla-registros-body">
                <!-- Los datos se cargarán aquí con JavaScript -->
            </tbody>
        </table>

        <div class="pagination-container" id="paginacion-controles">
            <!-- Los controles de paginación se generarán aquí -->
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tbody = document.getElementById('tabla-registros-body');
            const paginationContainer = document.getElementById('paginacion-controles');
            const loader = document.getElementById('loader');

            let currentPage = 1;

            async function cargarRegistros(page) {
                loader.style.display = 'block';
                tbody.innerHTML = '';
                paginationContainer.innerHTML = '';

                try {
                    // Hacemos la petición al nuevo archivo PHP
                    const response = await fetch(`obtener_registros.php?page=${page}`);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    const data = await response.json();

                    // Limpiamos el cuerpo de la tabla
                    tbody.innerHTML = '';

                    // Llenamos la tabla con los nuevos datos
                    if (data.registros.length > 0) {
                        data.registros.forEach(row => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${row.id_plan}</td>
                                <td>${row.fecha_creacion}</td>
                                <td>${row.fecha_inicio}</td>
                                <td>${row.fecha_fin}</td>
                                <td>${row.grado}</td>
                                <td>${row.nombre_materia}</td>
                                <td>${row.dba}</td>
                                <td><a href='../Planeador/phpword/ejemplo.php?descargar&id=${row.id_plan}' target='_blank'>Descargar</a></td>
                            `;
                            tbody.appendChild(tr);
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="8">No se encontraron resultados.</td></tr>';
                    }
                    
                    // Actualizamos los controles de paginación
                    actualizarPaginacion(data.total_paginas, page);

                } catch (error) {
                    tbody.innerHTML = `<tr><td colspan="8">Error al cargar los datos: ${error.message}</td></tr>`;
                    console.error('Fetch error:', error);
                } finally {
                     loader.style.display = 'none';
                }
            }

            function actualizarPaginacion(totalPages, page) {
                paginationContainer.innerHTML = '';
                currentPage = parseInt(page);

                // Botón "Anterior"
                const prevButton = document.createElement('button');
                prevButton.textContent = 'Anterior';
                prevButton.disabled = currentPage === 1;
                prevButton.addEventListener('click', () => {
                    if (currentPage > 1) {
                        cargarRegistros(currentPage - 1);
                    }
                });
                paginationContainer.appendChild(prevButton);

                // Indicador de página actual
                const pageInfo = document.createElement('span');
                pageInfo.className = 'current-page';
                pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
                paginationContainer.appendChild(pageInfo);

                // Botón "Siguiente"
                const nextButton = document.createElement('button');
                nextButton.textContent = 'Siguiente';
                nextButton.disabled = currentPage === totalPages;
                nextButton.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        cargarRegistros(currentPage + 1);
                    }
                });
                paginationContainer.appendChild(nextButton);
            }

            // Carga inicial de la primera página
            cargarRegistros(currentPage);
        });
    </script>
</body>
</html>

<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>
