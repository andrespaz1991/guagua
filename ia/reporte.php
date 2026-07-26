<?php
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);

    // --- Dependencias y Autoloaders ---
    require_once __DIR__ . "/../comun/autoload.php";
    require_once __DIR__ . "/vendor/autoload.php";

    use PhpOffice\PhpSpreadsheet\IOFactory;
    use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

    // =================================================================================
    // --- DETECCIÓN DE ENTORNO (local vs. web) — mismo patrón que Clase_mysqli.Class.php ---
    // =================================================================================
    $es_local = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);

    // --- Configuración Centralizada ---
    // Cada grupo tiene DOS rutas:
    //   'ruta_excel'     → ruta absoluta en tu máquina local (Windows)
    //   'ruta_excel_web' → ruta absoluta en el servidor de Hostinger
    $config = [
        'institucion' => "IER LA JOSEFINA",
        'docente' => [
            'nombre' => "Andres Paz Burbano",
            'telefono' => "3158229433"
        ],
        'sede' => "Vallesol",
        'grupos' => [
            1 => [
                "nombre" => "Grupo 1 (6° a 8°)",
                "min_grado" => 6,
                "max_grado" => 8,
                "ruta_excel"     => 'G:\Mi unidad\PC_HANDRES\SEDUCA\La Josefina\Vallesol\2026\Valoraciones\resumen_6-8.xlsx',
                "ruta_excel_web" => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vTMJd1B4kIFXs3I9cFLOWfiOl4LP4RCW1CHNq47P4D2eFlrow3DzGI8lPZENChYpw/pub?output=xlsx',
                "ultimafila" => 17,
                "materias" => [
                    "Geometría" => "D", "Ciencias Sociales" => "E", "Educación Física" => "F",
                    "Emprendimiento" => "G", "Matemáticas" => "H", "Tecnología" => "I", "Urbanidad" => "J"
                ]
            ],
            2 => [
                "nombre" => "Grupo 2 (9° a 11°)",
                "min_grado" => 9,
                "max_grado" => 11,
                "ruta_excel"     => 'G:\Mi unidad\PC_HANDRES\SEDUCA\La Josefina\Vallesol\2026\Valoraciones\resumen_9-11.xlsx',
                "ruta_excel_web" => '', // Agrega aquí la URL publicada del segundo grupo cuando la tengas
                "ultimafila" => 19,
                "materias" => [
                    "Geometría" => "D", "Ciencias Sociales/Economia" => "E", "Educación Física" => "F",
                    "Emprendimiento" => "G", "Matemáticas" => "H", "Tecnología" => "I", "Urbanidad" => "J","Fisica" => "K"
                ]
            ]
        ]
    ];

    // --- Carga y Guardado de Configuración de Rutas (local Y web) ---
    $configFile = __DIR__ . "/config_rutas.json";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_rutas'])) {
        $nuevasRutas = [
            1 => [
                'local' => $_POST['ruta_grupo_1']     ?? '',
                'web'   => $_POST['ruta_grupo_1_web'] ?? ''
            ],
            2 => [
                'local' => $_POST['ruta_grupo_2']     ?? '',
                'web'   => $_POST['ruta_grupo_2_web'] ?? ''
            ]
        ];
        file_put_contents($configFile, json_encode($nuevasRutas, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Mantener parámetros GET actuales
        $queryParams = $_GET;
        unset($queryParams['guardar_rutas']);
        $queryString = http_build_query($queryParams);
        header("Location: " . $_SERVER['PHP_SELF'] . ($queryString ? '?' . $queryString : ''));
        exit;
    }

    // Sobrescribir rutas con las guardadas en config_rutas.json (compatible con formato antiguo y nuevo)
    if (file_exists($configFile)) {
        $rutasGuardadas = json_decode(file_get_contents($configFile), true);
        foreach ([1, 2] as $id) {
            if (!empty($rutasGuardadas[$id])) {
                if (is_array($rutasGuardadas[$id])) {
                    // Formato nuevo: {"local": "...", "web": "..."}
                    if (!empty($rutasGuardadas[$id]['local'])) $config['grupos'][$id]['ruta_excel']     = $rutasGuardadas[$id]['local'];
                    if (!empty($rutasGuardadas[$id]['web']))   $config['grupos'][$id]['ruta_excel_web'] = $rutasGuardadas[$id]['web'];
                } else {
                    // Formato antiguo (solo string): actualizar solo ruta local
                    $config['grupos'][$id]['ruta_excel'] = $rutasGuardadas[$id];
                }
            }
        }
    }

    // =================================================================================
    // --- SELECCIONAR RUTA ACTIVA SEGÚN ENTORNO (local o web) ---
    // =================================================================================
    foreach ($config['grupos'] as $id => &$grupo) {
        if ($es_local) {
            $grupo['ruta_activa'] = $grupo['ruta_excel'];     // Usar ruta local en XAMPP
        } else {
            // En producción: usar ruta_excel_web si está definida, sino fallback a ruta_excel
            $grupo['ruta_activa'] = !empty($grupo['ruta_excel_web']) ? $grupo['ruta_excel_web'] : $grupo['ruta_excel'];
        }
    }
    unset($grupo); // Liberar referencia

    // =================================================================================
    // --- FUNCIONES DE LÓGICA Y PRESENTACIÓN ---
    // =================================================================================

    function mostrarFormularioSeleccion($grupos)
    {
        $checkedPerdidas = isset($_GET['solo_perdidas']) && $_GET['solo_perdidas'] == '1' ? 'checked' : '';
        echo '<div class="card shadow-sm mb-4 no-print">
                <div class="card-body">
                    <h5 class="card-title">Seleccionar Grupo y Opciones</h5>
                    <form action="" method="get" class="form-inline align-items-center mb-3">
                        <div class="form-group mr-4">';

        foreach ($grupos as $id => $grupo) {
            $checked = (isset($_GET['grupo']) && $_GET['grupo'] == $id) || (!isset($_GET['grupo']) && $id == 2) ? 'checked' : '';
            echo "<div class='form-check form-check-inline'>
                      <input class='form-check-input' type='radio' name='grupo' id='grupo-{$id}' value='{$id}' {$checked}>
                      <label class='form-check-label' for='grupo-{$id}'>{$grupo['nombre']}</label>
                  </div>";
        }
        echo '          </div>
                        
                        <div class="form-group mr-4 border-left pl-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="solo_perdidas" name="solo_perdidas" value="1" ' . $checkedPerdidas . '>
                                <label class="custom-control-label" for="solo_perdidas">Solo con materias perdidas (< 3.0)</label>
                            </div>
                        </div>

                        <div class="form-group mr-4 border-left pl-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="toggleGeometria">
                                <label class="custom-control-label" for="toggleGeometria">Mostrar Geometría</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary ml-auto">Consultar Reporte</button>
                        <button type="button" class="btn btn-outline-secondary ml-2 no-print" data-toggle="modal" data-target="#configModal" title="Configurar Rutas">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear-fill" viewBox="0 0 16 16">
                              <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311c.446.82.023 1.841-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413 1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.86 2.929 2.929 0 0 1 0 5.858z"/>
                            </svg>
                        </button>
                    </form>
                    
                    <hr>
                    
                    <div class="form-group mb-0">
                        <label for="buscadorEstudiantes" class="sr-only">Buscar Estudiante</label>
                        <input type="text" id="buscadorEstudiantes" class="form-control w-100" placeholder="🔍 Buscar estudiante por nombre de manera asíncrona...">
                    </div>
                </div>
            </div>';
    }

    function cargarNotasDesdeExcel($ruta, $ultimaFila, $materias)
    {
        $archivoTemporal = null;

        // Detectar si la ruta es una URL (Google Sheets publicado, etc.)
        if (filter_var($ruta, FILTER_VALIDATE_URL)) {
            // Descargar el archivo a una ruta temporal en el servidor
            $contenido = @file_get_contents($ruta, false, stream_context_create([
                'http' => [
                    'timeout'         => 30,
                    'follow_location' => true,
                    'user_agent'      => 'Mozilla/5.0 (compatible; PHP)'
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ]));

            if ($contenido === false || strlen($contenido) < 100) {
                throw new Exception("No se pudo descargar el archivo desde la URL: {$ruta}. Verifica que la hoja esté publicada correctamente en Google Sheets (Archivo → Compartir → Publicar en la web → .xlsx).");
            }

            // Guardar en un archivo temporal con extensión .xlsx
            $archivoTemporal = sys_get_temp_dir() . '/reporte_temp_' . uniqid() . '.xlsx';
            file_put_contents($archivoTemporal, $contenido);
            $rutaLectura = $archivoTemporal;

        } else {
            // Es una ruta de archivo local
            if (!file_exists($ruta)) {
                throw new Exception("El archivo Excel no se encontró en: {$ruta}");
            }
            $rutaLectura = $ruta;
        }

        try {
            $spreadsheet = IOFactory::load($rutaLectura);
            $worksheet = $spreadsheet->getActiveSheet();
            $notasPorEstudiante = [];

            for ($row = 2; $row <= $ultimaFila; $row++) {
                $documento = trim($worksheet->getCell('B' . $row)->getValue());
               
                if (empty($documento)) continue;
                
                $notas = [];
                foreach ($materias as $nombreMateria => $columna) {
                    $notas[$nombreMateria] = round($worksheet->getCell($columna . $row)->getCalculatedValue(), 1);
                }
                
                $notasPorEstudiante[$documento] = [
                    'nombre' => $worksheet->getCell('A' . $row)->getValue(),
                    'grado'  => $worksheet->getCell('C' . $row)->getValue(),
                    'notas'  => $notas
                ];
            }
        } finally {
            // Limpiar el archivo temporal si se creó
            if ($archivoTemporal && file_exists($archivoTemporal)) {
                @unlink($archivoTemporal);
            }
        }

        return $notasPorEstudiante;
    }


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

    function renderizarFichaEstudiante( $grado, $documento, $estudianteDB, $infoExcel, $grupoSeleccionado, $config)
    {
        $nombreCompleto = htmlspecialchars($estudianteDB);
        $docente = htmlspecialchars($config['docente']['nombre'] . " (teléfono:" . $config['docente']['telefono'] . ")");
        $sede = htmlspecialchars($config['sede']);
        $institucion = htmlspecialchars($config['institucion']);
       
        ?>
        <div class="report-wrapper">
            <div class="card shadow-sm report-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        SEGUIMIENTO DE: <strong class="nombre-estudiante"><?php echo $nombreCompleto; ?></strong>
                        <span class="badge badge-secondary ml-2">Grado <?php echo $grado; ?> | <?php echo $institucion; ?> - <?php echo $sede; ?></span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                </div>
                
                <table class="table table-bordered table-sm text-center table-notas">
                    <thead class="thead-dark">
                        <tr>
                            <?php foreach ($infoExcel as $materia => $nota) : ?>
                                <?php 
                                    if ($materia === 'Fisica' && !in_array((int)$grado, [10, 11])) continue; 
                                    $claseGeo = ($materia === 'Geometría') ? 'col-geometria' : '';
                                ?>
                                <th class="<?php echo $claseGeo; ?>"><?php echo htmlspecialchars($materia); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php foreach ($infoExcel as $materia => $nota) : ?>
                                <?php 
                                    if ($materia === 'Fisica' && !in_array((int)$grado, [10, 11])) continue;
                                    $desempeno = obtenerDesempeno($nota); 
                                    $claseGeo = ($materia === 'Geometría') ? 'col-geometria' : '';
                                ?>
                                <td class="<?php echo $desempeno['clase'] . ' ' . $claseGeo; ?>"><h4><?php echo number_format($nota, 1); ?></h4></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <?php foreach ($infoExcel as $materia => $nota) : ?>
                                <?php 
                                    if ($materia === 'Fisica' && !in_array((int)$grado, [10, 11])) continue;
                                    $desempeno = obtenerDesempeno($nota); 
                                    $claseGeo = ($materia === 'Geometría') ? 'col-geometria' : '';
                                ?>
                                <td class="<?php echo $desempeno['clase'] . ' ' . $claseGeo; ?>">
                                    <span class="desempeno-icono"><?php echo $desempeno['icono']; ?></span>
                                    <span class="desempeno-texto"><?php echo $desempeno['texto']; ?></span>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="cut-line"></div>
        </div>
        <?php
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
        body { background-color: #f8f9fa; }
        .container-fluid { margin-top: 20px; }
        .report-wrapper { transition: opacity 0.3s ease; }
        .report-card { page-break-inside: avoid; margin-bottom: 1.5rem; }
        .cut-line { border-top: 2px dashed #a0a0a0; margin: 2rem 0; text-align: center; position: relative; }
        .cut-line::after { content: "✂️ Línea de corte"; position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #f8f9fa; padding: 0 10px; color: #6c757d; font-size: 13px; font-weight: bold; }
        .table td { vertical-align: middle; }
        .nota-superior { background-color: #e3f2fd !important; color: #0d47a1 !important; }
        .nota-alta     { background-color: #e8f5e9 !important; color: #1b5e20 !important; }
        .nota-media    { background-color: #fffde7 !important; color: #f57f17 !important; }
        .nota-baja     { background-color: #ffebee !important; color: #b71c1c !important; }
        .desempeno-texto { font-size: 0.8em; font-weight: bold; }
        .desempeno-icono { margin-right: 5px; }

        .col-geometria { display: none; }
        body.mostrar-geometria .col-geometria { display: table-cell; }

        @media print {
            body { background-color: #fff; font-size: 10pt; }
            .no-print { display: none !important; }
            .report-card { border: 1px solid #dee2e6 !important; box-shadow: none !important; display: block !important; margin-bottom: 1rem; }
            .cut-line::after { background: #fff; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .thead-dark th { background-color: #343a40 !important; color: #ffffff !important; }
            body.mostrar-geometria .col-geometria { display: table-cell !important; }
        }
    </style>
</head>
<body>

    <div class="container-fluid" style="max-width: 1400px;">
        <div class="text-center mb-4 no-print">
            <img src="..\comun\img\Banner_guias.jpg" width="400" alt="Banner Institucional">
            <h2 class="mt-3">Plataforma de Seguimiento Académico</h2>
        </div>
        
        <?php mostrarFormularioSeleccion($config['grupos']); ?>

        <?php if (isset($_GET['grupo'])) : ?>
            <div class="alert alert-info no-print shadow-sm">
                <strong>Resultados: </strong> Se encontraron <span id="numResultados">0</span> estudiante(s).
            </div>
            <div id="contenedorTarjetas">
            <?php
            try {
                $grupoId = (int)$_GET['grupo'];
                $grupoSeleccionado = $config['grupos'][$grupoId] ?? $config['grupos'][2];
                
                $filtroSoloPerdidas = isset($_GET['solo_perdidas']) && $_GET['solo_perdidas'] == '1';

                // Usar 'ruta_activa': ya fue calculada arriba según el entorno (local o web)
                $notasExcel = cargarNotasDesdeExcel(
                    $grupoSeleccionado['ruta_activa'],
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
                    echo '<div class="alert alert-warning">No se encontraron estudiantes en la base de datos para el grupo y/o estudiante seleccionado.</div>';
                }

                foreach ($notasExcel as $documento => $datos) {
                    $nombre = $datos['nombre'];
                    $grado = $datos['grado'];
                    $notas = $datos['notas']; 
                    
                    $mostrarEstudiante = true;

                    if ($filtroSoloPerdidas) {
                        $tienePerdidas = false;
                        foreach ($notas as $materia => $nota) {
                            if ($materia === 'Fisica' && !in_array((int)$grado, [10, 11])) continue;
                            if (is_numeric($nota) && $nota < 3.0) {
                                $tienePerdidas = true;
                                break;
                            }
                        }
                        $mostrarEstudiante = $tienePerdidas;
                    }

                    if ($mostrarEstudiante) {
                        renderizarFichaEstudiante($grado, $documento, $nombre, $notas, $grupoSeleccionado, $config);
                    }
                }

            } catch (ReaderException $e) {
                echo '<div class="alert alert-danger"><strong>Error de Lectura:</strong> No se pudo leer el archivo de Excel. Detalles: ' . $e->getMessage() . '</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger"><strong>Error General:</strong> ' . $e->getMessage() . '</div>';
            }
            ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal de Configuración -->
    <div class="modal fade no-print" id="configModal" tabindex="-1" role="dialog" aria-labelledby="configModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <form method="post" action="">
              <div class="modal-header bg-light">
                <h5 class="modal-title" id="configModalLabel">Configurar Rutas de Archivos de Excel</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                  <?php
                  $entorno_badge = $es_local
                      ? '<span class="badge badge-primary">&#128187; Entorno LOCAL activo</span>'
                      : '<span class="badge badge-success">&#127760; Entorno WEB activo</span>';
                  ?>
                  <p class="text-muted small mb-1">Ingrese las rutas absolutas de los archivos Excel para cada entorno. <?php echo $entorno_badge; ?></p>
                  <hr>
                  <?php foreach ([1, 2] as $gid): ?>
                  <div class="card mb-3 shadow-sm">
                      <div class="card-header py-2"><strong><?php echo htmlspecialchars($config['grupos'][$gid]['nombre']); ?></strong></div>
                      <div class="card-body py-2">
                          <div class="form-group mb-2">
                              <label class="small mb-1">&#128193; Ruta Local (XAMPP)</label>
                              <input type="text" class="form-control form-control-sm"
                                  name="ruta_grupo_<?php echo $gid; ?>"
                                  value="<?php echo htmlspecialchars($config['grupos'][$gid]['ruta_excel']); ?>"
                                  placeholder="Ej: G:\Mi unidad\...\resumen.xlsx">
                          </div>
                          <div class="form-group mb-0">
                              <label class="small mb-1">&#127760; Ruta Web (Hostinger)</label>
                              <input type="text" class="form-control form-control-sm"
                                  name="ruta_grupo_<?php echo $gid; ?>_web"
                                  value="https://docs.google.com/spreadsheets/d/e/2PACX-1vTMJd1B4kIFXs3I9cFLOWfiOl4LP4RCW1CHNq47P4D2eFlrow3DzGI8lPZENChYpw/pub?output=xlsx">
                          </div>
                      </div>
                  </div>
                  <?php endforeach; ?>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="guardar_rutas" class="btn btn-primary">Guardar Cambios</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Dependencias JS para Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para actualizar contador
            function actualizarContador() {
                const tarjetas = document.querySelectorAll('.report-wrapper');
                let visibles = 0;
                tarjetas.forEach(function(t) {
                    if (t.style.display !== 'none') visibles++;
                });
                const contador = document.getElementById('numResultados');
                if (contador) contador.textContent = visibles;
            }

            // Inicializar contador al cargar
            actualizarContador();

            // Control de visibilidad de Geometría
            const toggleGeo = document.getElementById('toggleGeometria');
            if (toggleGeo) {
                toggleGeo.addEventListener('change', function() {
                    if (this.checked) {
                        document.body.classList.add('mostrar-geometria');
                    } else {
                        document.body.classList.remove('mostrar-geometria');
                    }
                });
            }

            // Buscador asíncrono en tiempo real
            const buscador = document.getElementById('buscadorEstudiantes');
            if (buscador) {
                buscador.addEventListener('keyup', function(e) {
                    const terminoBusqueda = e.target.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                    const wrappers = document.querySelectorAll('.report-wrapper');

                    wrappers.forEach(function(wrapper) {
                        const nombreElemento = wrapper.querySelector('.nombre-estudiante');
                        if (nombreElemento) {
                            const nombre = nombreElemento.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
                            if (nombre.includes(terminoBusqueda)) {
                                wrapper.style.display = 'block';
                            } else {
                                wrapper.style.display = 'none';
                            }
                        }
                    });
                    
                    actualizarContador();
                });
            }
        });
    </script>
</body>
</html>