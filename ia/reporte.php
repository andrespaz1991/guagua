<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Dependencias y Autoloaders ---
// Asegúrate de que las rutas sean correctas desde la ubicación de este archivo.
require_once __DIR__ . "/../comun/autoload.php";
require_once __DIR__ . "/reporte3.php"; // Contiene la función obtenerAsistenciasPorEstudiante()
require_once __DIR__ . "/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

// --- Configuración Centralizada ---
// Mover la configuración a un solo lugar facilita el mantenimiento.
$config = [
    'docente' => "Andres Paz Burbano (teléfono:3158229433)",
    'sede' => "Vallesol",
    'grupos' => [
        1 => [
            "nombre" => "Grupo 1 (6° a 8°)",
            "min_grado" => 6,
            "max_grado" => 8,
            // IMPORTANTE: Usa rutas relativas para que el script funcione en cualquier servidor.
            "ruta_excel" => 'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\Escritorio\Valoraciones\resumen_6-8.xlsx',
            "ultimafila" => 18,
            "materias" => [
                "Geometría" => "D", "Ciencias Sociales" => "E", "Educación Física" => "F",
                "Emprendimiento" => "G", "Matemáticas" => "H", "Tecnología" => "I", "Urbanidad" => "J"
            ]
        ],
        2 => [
            "nombre" => "Grupo 2 (9° a 11°)",
            "min_grado" => 9,
            "max_grado" => 11,
            "ruta_excel" => 'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\Escritorio\Valoraciones\resumen_9-11.xlsx',
            "ultimafila" => 19,
            "materias" => [
                "Geometría" => "D", "Ciencias Sociales/Economia" => "E", "Educación Física" => "F",
                "Emprendimiento" => "G", "Matemáticas" => "H", "Tecnología" => "I", "Urbanidad" => "J"
            ]
        ]
    ]
];

/**
 * Muestra el formulario de selección de grupo.
 * @param array $grupos La configuración de los grupos.
 */
function mostrarFormularioSeleccion($grupos)
{
    echo '<div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Seleccionar Grupo</h5>
                <form action="" method="get" class="form-inline">
                    <div class="form-group mr-3">';
    
    foreach ($grupos as $id => $grupo) {
        $checked = (isset($_GET['grupo']) && $_GET['grupo'] == $id) || (!isset($_GET['grupo']) && $id == 2) ? 'checked' : '';
        echo "<div class='form-check form-check-inline'>
                  <input class='form-check-input' type='radio' name='grupo' id='grupo-{$id}' value='{$id}' {$checked}>
                  <label class='form-check-label' for='grupo-{$id}'>{$grupo['nombre']}</label>
              </div>";
    }

    echo '      </div>
                <button type="submit" class="btn btn-primary">Consultar Reporte</button>
            </form>
        </div>
    </div>';
}

/**
 * Carga y procesa las notas desde el archivo Excel una sola vez.
 * @param string $ruta Ruta al archivo Excel.
 * @param int $ultimaFila La última fila a leer.
 * @param array $materias Mapeo de materias a columnas.
 * @return array Arreglo con las notas de los estudiantes, usando el documento como clave.
 */
function cargarNotasDesdeExcel($ruta, $ultimaFila, $materias)
{
    $notasPorEstudiante = [];
    if (!file_exists($ruta)) {
        throw new Exception("El archivo Excel no se encontró en la ruta especificada: {$ruta}");
    }

    $spreadsheet = IOFactory::load($ruta);
    $worksheet = $spreadsheet->getActiveSheet();

    for ($row = 2; $row <= $ultimaFila; $row++) {
        $documento = trim($worksheet->getCell('B' . $row)->getValue());
        if (empty($documento)) continue; // Saltar filas sin documento

        $notas = [];
        foreach ($materias as $columna) {
            $notas[] = round($worksheet->getCell($columna . $row)->getCalculatedValue(), 1);
        }

        $notasPorEstudiante[$documento] = [
            'nombre' => $worksheet->getCell('A' . $row)->getValue(),
            'grado' => $worksheet->getCell('C' . $row)->getValue(),
            'notas' => $notas
        ];
    }
    return $notasPorEstudiante;
}

/**
 * Genera el código de color para una celda de nota.
 * @param float $nota La nota del estudiante.
 * @return string Estilo CSS para la celda.
 */
function obtenerEstiloNota($nota)
{
    if ($nota < 3.0) return 'style="background-color:#f8d7da; color:#721c24;"'; // Rojo suave
    if ($nota >= 3.0 && $nota < 3.5) return 'style="background-color:#fff3cd; color:#856404;"'; // Amarillo suave
    return 'style="background-color:#d4edda; color:#155724;"'; // Verde suave
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Seguimiento Académico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
        }
        .report-card {
            page-break-inside: avoid;
            margin-bottom: 2rem;
        }
        @media print {
            body { background-color: #fff; }
            .no-print { display: none; }
            .report-card { 
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4 no-print">
            <img src="Banner guías.png" width="400" alt="Banner Institucional">
            <h2 class="mt-3">Plataforma de Seguimiento Académico</h2>
        </div>
        
        <div class="no-print">
            <?php mostrarFormularioSeleccion($config['grupos']); ?>
        </div>

        <?php if (isset($_GET['grupo'])) : ?>
            <?php
            $grupoId = (int)$_GET['grupo'];
            // Validar que el grupo exista, si no, usar el 2 por defecto.
            $grupoSeleccionado = $config['grupos'][$grupoId] ?? $config['grupos'][2];

            try {
                // --- OPTIMIZACIÓN CLAVE ---
                // Leemos el archivo Excel UNA SOLA VEZ y guardamos los datos en memoria.
                $notasExcel = cargarNotasDesdeExcel(
                    $grupoSeleccionado['ruta_excel'],
                    $grupoSeleccionado['ultimafila'],
                    $grupoSeleccionado['materias']
                );

                $idEstudiante = $_GET['id_estudiante'] ?? '';
                $estudiantesDB = COMUN::llamar_estudiantes_grado_Vallesol(
                    $grupoSeleccionado['min_grado'],
                    $grupoSeleccionado['max_grado'],
                    $idEstudiante
                );

                if (empty($estudiantesDB)) {
                    echo '<div class="alert alert-warning">No se encontraron estudiantes en la base de datos para el grupo seleccionado.</div>';
                }

                $fecha = Fecha::formato_fecha(date('d-m-Y'));

                // Recorremos los estudiantes de la BASE DE DATOS
                foreach ($estudiantesDB as $estudiante) {
                    $documento = $estudiante['id_usuario'];
                    
                    // Buscamos si el estudiante tiene notas en los datos que cargamos del Excel
                    if (isset($notasExcel[$documento])) {
                        $infoExcel = $notasExcel[$documento];
                        $nombreCompleto = $estudiante['apellido'] . ' ' . $estudiante['nombre'];
                        ?>
                        <div class="card shadow-sm report-card">
                            <div class="card-header">
                                <h5 class="mb-0">Seguimiento de: <strong><?php echo htmlspecialchars($nombreCompleto); ?></strong></h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Docente:</strong> <?php echo htmlspecialchars($config['docente']); ?> (Sede: <?php echo htmlspecialchars($config['sede']); ?>)
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Acudiente:</strong> ______________________________
                                    </div>
                                </div>
                                
                                <table class="table table-bordered table-striped table-sm text-center">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Nombre</th>
                                            <!--th>Documento</th-->
                                            <th>Grado</th>
                                            <?php foreach (array_keys($grupoSeleccionado['materias']) as $materia) : ?>
                                                <th><?php echo htmlspecialchars($materia); ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><?php echo htmlspecialchars($infoExcel['nombre']); ?></td>
                                            <!--td><?php #echo htmlspecialchars($documento); ?></td-->
                                            <td><?php echo htmlspecialchars($infoExcel['grado']); ?></td>
                                            <?php foreach ($infoExcel['notas'] as $nota) : ?>
                                                <td <?php echo obtenerEstiloNota($nota); ?>><?php echo number_format($nota, 1); ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    </tbody>
                                </table>

                                <?php
                                // --- SOLUCIÓN AL WARNING ---
                                // Verificamos que la función devuelva algo con lo que se pueda trabajar.
                                $asistenciasHTML = obtenerAsistenciasPorEstudiante($documento);
                                if (!empty($asistenciasHTML) && $asistenciasHTML !== 1) {
                                    echo $asistenciasHTML; // Asumimos que la función ya retorna HTML formateado.
                                }
                                ?>
                                
                                <div class="alert alert-info mt-3" role="alert">
                                    <p class="mb-0">
                                        <strong>Nota:</strong>
                                        <?php if (empty($asistenciasHTML) || $asistenciasHTML === 1) : ?>
                                            <strong>El estudiante no registra inasistencias al momento.</strong>
                                        <?php endif; ?>
                                        Tenga en cuenta que el informe presentado el <strong><?php echo $fecha; ?></strong> solo representa un avance, NO es el informe final y puede variar según las actividades pendientes.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        // Opcional: Mostrar un aviso si un estudiante de la BD no está en el Excel.
                        // echo '<div class="alert alert-secondary">El estudiante ' . htmlspecialchars($estudiante['apellido'] . ' ' . $estudiante['nombre']) . ' no fue encontrado en el archivo de notas.</div>';
                    }
                } // Fin del foreach

            } catch (ReaderException $e) {
                echo '<div class="alert alert-danger"><strong>Error de Lectura:</strong> No se pudo leer el archivo de Excel. Detalles: ' . $e->getMessage() . '</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger"><strong>Error General:</strong> ' . $e->getMessage() . '</div>';
            }
            ?>
        <?php endif; // Fin de if(isset($_GET['grupo'])) ?>
    </div>
</body>
</html>
