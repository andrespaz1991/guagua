<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- Dependencias y Autoloaders ---
require_once __DIR__ . "/../comun/autoload.php";
require_once __DIR__ . "/reporte3.php";
require_once __DIR__ . "/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

// --- Configuración Centralizada ---
$config = [
    'docente' => [
        'nombre' => "Andres Paz Burbano",
        'telefono' => "3158229433"
    ],
    'sede' => "Vallesol",
    'grupos' => [
        // --- CORRECCIÓN: Se restaura la configuración completa del Grupo 1 ---
        1 => [
            "nombre" => "Grupo 1 (6° a 8°)",
            "min_grado" => 6,
            "max_grado" => 8,
            "ruta_excel" => 'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\SEDUCA\La Josefina\Vallesol\2025\Valoraciones\resumen_6-8.xlsx',
            "ultimafila" => 16,
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

// =================================================================================
// --- FUNCIONES DE LÓGICA Y PRESENTACIÓN ---
// =================================================================================

/**
 * Muestra el formulario de selección de grupo.
 * @param array $grupos La configuración de los grupos.
 */
function mostrarFormularioSeleccion($grupos)
{
    echo '<div class="card shadow-sm mb-4 no-print">
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
 * Carga las notas desde un archivo Excel.
 * @param string $ruta Ruta al archivo.
 * @param int $ultimaFila Última fila a leer.
 * @param array $materias Mapeo de materias a columnas.
 * @return array Arreglo con las notas de los estudiantes.
 */
function cargarNotasDesdeExcel($ruta, $ultimaFila, $materias)
{
    if (!file_exists($ruta)) {
        throw new Exception("El archivo Excel no se encontró en: {$ruta}");
    }
    $spreadsheet = IOFactory::load($ruta);
    $worksheet = $spreadsheet->getActiveSheet();
    $notasPorEstudiante = [];
    for ($row = 2; $row <= $ultimaFila; $row++) {
        $documento = trim($worksheet->getCell('B' . $row)->getValue());
        if (empty($documento)) continue;
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
 * Determina el desempeño basado en una nota.
 * @param float $nota La nota del estudiante.
 * @return array Un array con texto, clase CSS e icono SVG.
 */
function obtenerDesempeno($nota)
{
    $desempenos = [
        'superior' => ['texto' => 'Superior', 'clase' => 'nota-superior', 'icono' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0zm-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"/></svg>'],
        'alto' => ['texto' => 'Alto', 'clase' => 'nota-alta', 'icono' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-right-circle-fill" viewBox="0 0 16 16"><path d="M0 8a8 8 0 1 0 16 0A8 8 0 0 0 0 8zm5.904 2.803a.5.5 0 1 1-.707-.707L9.293 6H6.525a.5.5 0 1 1 0-1H10.5a.5.5 0 0 1 .5.5v3.975a.5.5 0 0 1-1 0V6.707l-4.096 4.096z"/></svg>'],
        'basico' => ['texto' => 'Básico', 'clase' => 'nota-media', 'icono' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-circle-fill" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zM4.5 7.5a.5.5 0 0 0 0 1h5.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H4.5z"/></svg>'],
        'bajo' => ['texto' => 'Bajo', 'clase' => 'nota-baja', 'icono' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-down-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/></svg>']
    ];
    if ($nota >= 4.6) return $desempenos['superior'];
    if ($nota >= 4.0) return $desempenos['alto'];
    if ($nota >= 3.0) return $desempenos['basico'];
    return $desempenos['bajo'];
}

/**
 * Renderiza la ficha completa de seguimiento de un estudiante.
 * @param array $estudianteDB Datos del estudiante desde la base de datos.
 * @param array $infoExcel Datos del estudiante desde el archivo Excel.
 * @param array $grupoSeleccionado Configuración del grupo actual.
 * @param array $config Configuración general de la aplicación.
 */
function renderizarFichaEstudiante($estudianteDB, $infoExcel, $grupoSeleccionado, $config)
{
    $nombreCompleto = htmlspecialchars($estudianteDB['apellido'] . ' ' . $estudianteDB['nombre']);
    $grado = htmlspecialchars($infoExcel['grado']);
    $docente = htmlspecialchars($config['docente']['nombre'] . " (teléfono:" . $config['docente']['telefono'] . ")");
    $sede = htmlspecialchars($config['sede']);
    $documento = $estudianteDB['id_usuario'];

    // Obtenemos y formateamos el reporte de asistencias.
    $asistenciasHTML = obtenerAsistenciasPorEstudiante($documento);
    if (!empty($asistenciasHTML) && $asistenciasHTML !== 1) {
        $asistenciasHTML = str_replace('class="table-warning"', 'class="inasistencia-critica"', $asistenciasHTML);
    }
    ?>
    <div class="card shadow-sm report-card">
        <div class="card-header">
            <h5 class="mb-0">
                Seguimiento de: <strong><?php echo $nombreCompleto; ?></strong>
                <span class="badge badge-secondary ml-2">Grado <?php echo $grado; ?></span>
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6"><strong>Docente:</strong> <?php echo $docente; ?> (Sede: <?php echo $sede; ?>)</div>
                <div class="col-md-6"><strong>Acudiente:</strong> ______________________________</div>
            </div>
            
            <table class="table table-bordered table-sm text-center">
                <thead class="thead-dark">
                    <tr>
                        <?php foreach (array_keys($grupoSeleccionado['materias']) as $materia) : ?>
                            <th><?php echo htmlspecialchars($materia); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php foreach ($infoExcel['notas'] as $nota) : $desempeno = obtenerDesempeno($nota); ?>
                            <td class="<?php echo $desempeno['clase']; ?>"><h4><?php echo number_format($nota, 1); ?></h4></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <?php foreach ($infoExcel['notas'] as $nota) : $desempeno = obtenerDesempeno($nota); ?>
                            <td class="<?php echo $desempeno['clase']; ?>">
                                <span class="desempeno-icono"><?php echo $desempeno['icono']; ?></span>
                                <span class="desempeno-texto"><?php echo $desempeno['texto']; ?></span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>

            <?php if (!empty($asistenciasHTML) && $asistenciasHTML !== 1) {
                echo '<div class="tabla-asistencia">' . $asistenciasHTML . '</div>';
            } ?>
            
            <div class="alert alert-info mt-3" role="alert">
                <p class="mb-0"><strong>Nota:</strong>
                    <?php if (empty($asistenciasHTML) || $asistenciasHTML === 1) echo "El estudiante no registra inasistencias al momento."; ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}

// =================================================================================
// --- ESTRUCTURA PRINCIPAL DE LA PÁGINA ---
// =================================================================================
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Seguimiento Académico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { margin-top: 20px; }
        .report-card { page-break-inside: avoid; margin-bottom: 2rem; }
        .table td { vertical-align: middle; }
        .nota-superior { background-color: #e3f2fd !important; color: #0d47a1 !important; }
        .nota-alta     { background-color: #e8f5e9 !important; color: #1b5e20 !important; }
        .nota-media    { background-color: #fffde7 !important; color: #f57f17 !important; }
        .nota-baja     { background-color: #ffebee !important; color: #b71c1c !important; }
        .inasistencia-critica { background-color: #ffebee !important; color: #b71c1c !important; }
        .desempeno-texto { font-size: 0.8em; font-weight: bold; }
        .desempeno-icono { margin-right: 5px; }

        .tabla-asistencia table th:nth-child(2),
        .tabla-asistencia table td:nth-child(2) {
            display: none;
        }

        @media print {
            body { background-color: #fff; font-size: 10pt; }
            .no-print { display: none; }
            .report-card { border: 1px solid #dee2e6 !important; box-shadow: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .thead-dark th { background-color: #343a40 !important; color: #ffffff !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mb-4 no-print">
            <img src="Banner guías.png" width="400" alt="Banner Institucional">
            <h2 class="mt-3">Plataforma de Seguimiento Académico</h2>
        </div>
        
        <?php mostrarFormularioSeleccion($config['grupos']); ?>

        <?php if (isset($_GET['grupo'])) : ?>
            <?php
            try {
                // --- 1. SELECCIÓN Y CARGA DE DATOS ---
                $grupoId = (int)$_GET['grupo'];
                $grupoSeleccionado = $config['grupos'][$grupoId] ?? $config['grupos'][2];
                
                $notasExcel = cargarNotasDesdeExcel(
                    $grupoSeleccionado['ruta_excel'],
                    $grupoSeleccionado['ultimafila'],
                    $grupoSeleccionado['materias']
                );
                #echo "<pre>";
                #print_r(count($notasExcel));
                #echo "</pre>";
                $idEstudiante = $_GET['id_estudiante'] ?? '';
                $estudiantesDB = COMUN::llamar_estudiantes_grado_Vallesol(
                    $grupoSeleccionado['min_grado'],
                    $grupoSeleccionado['max_grado'],
                    $idEstudiante
                );

                if (empty($estudiantesDB)) {
                    echo '<div class="alert alert-warning">No se encontraron estudiantes en la base de datos para el grupo y/o estudiante seleccionado.</div>';
                }

                // --- 2. PROCESAMIENTO Y RENDERIZADO ---
                foreach ($estudiantesDB as $estudiante) {
                    $documento = $estudiante['id_usuario'];
                    if (isset($notasExcel[$documento])) {
                        renderizarFichaEstudiante($estudiante, $notasExcel[$documento], $grupoSeleccionado, $config);
                    }
                }

            } catch (ReaderException $e) {
                echo '<div class="alert alert-danger"><strong>Error de Lectura:</strong> No se pudo leer el archivo de Excel. Detalles: ' . $e->getMessage() . '</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger"><strong>Error General:</strong> ' . $e->getMessage() . '</div>';
            }
            ?>
        <?php endif; ?>
    </div>
</body>
</html>

