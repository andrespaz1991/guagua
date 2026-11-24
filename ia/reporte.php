<?php
require_once __DIR__ . '/../comun/persona_imagen.php';
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    ini_set('default_charset', 'UTF-8');
    header('Content-Type: text/html; charset=UTF-8');
    error_reporting(-1);

    // --- Dependencias y Autoloaders ---
    require_once __DIR__ . "/../comun/autoload.php";
    require_once __DIR__ . "/../comun/conexion.php";
    require_once __DIR__ . "/../apps/notas/lib/NotasAuditoriaService.php";
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

    $mensajeWhatsappPredeterminado = 'Hola, {nombre}. Te informamos que el período {periodo} finaliza el {fecha_fin}. Por favor, revisa tu reporte académico. Gracias.';
    $rutaSalidaPredeterminada = __DIR__ . DIRECTORY_SEPARATOR . 'salidas_whatsapp';
    $configGuardada = [];
    if (file_exists($configFile)) {
        $configLeida = json_decode(file_get_contents($configFile), true);
        if (is_array($configLeida)) {
            $configGuardada = $configLeida;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['guardar_rutas']) || isset($_POST['guardar_mensaje_whatsapp']))) {
        if (isset($_POST['guardar_rutas'])) {
            $configGuardada[1] = [
                'local' => trim($_POST['ruta_grupo_1']     ?? ''),
                'web'   => trim($_POST['ruta_grupo_1_web'] ?? '')
            ];
            $configGuardada[2] = [
                'local' => trim($_POST['ruta_grupo_2']     ?? ''),
                'web'   => trim($_POST['ruta_grupo_2_web'] ?? '')
            ];
            $rutaSalidaRecibida = trim((string)($_POST['ruta_salida_whatsapp'] ?? ''));
            $configGuardada['ruta_salida_whatsapp'] = $rutaSalidaRecibida !== ''
                ? $rutaSalidaRecibida
                : $rutaSalidaPredeterminada;
        }

        if (isset($_POST['guardar_mensaje_whatsapp'])) {
            $mensajeRecibido = trim((string)($_POST['mensaje_whatsapp'] ?? ''));
            $configGuardada['mensaje_whatsapp'] = $mensajeRecibido !== '' ? $mensajeRecibido : $mensajeWhatsappPredeterminado;
        }

        $configJson = json_encode($configGuardada, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($configJson === false || file_put_contents($configFile, $configJson, LOCK_EX) === false) {
            http_response_code(500);
            exit('No se pudo guardar la configuración. Revise los permisos de config_rutas.json e intente nuevamente.');
        }

        // Mantener parámetros GET actuales, incluido guagua_embedded para no
        // volver a cargar este reporte dentro de otro contenedor con menú.
        $queryString = http_build_query($_GET);
        header("Location: " . $_SERVER['PHP_SELF'] . ($queryString ? '?' . $queryString : ''));
        exit;
    }

    $mensajeWhatsapp = trim((string)($configGuardada['mensaje_whatsapp'] ?? '')) ?: $mensajeWhatsappPredeterminado;
    $rutaSalidaWhatsapp = trim((string)($configGuardada['ruta_salida_whatsapp'] ?? '')) ?: $rutaSalidaPredeterminada;

    // Sobrescribir rutas con las guardadas en config_rutas.json (compatible con formato antiguo y nuevo)
    if (!empty($configGuardada)) {
        $rutasGuardadas = $configGuardada;
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
        $checkedSinFoto = isset($_GET['solo_sin_foto']) && $_GET['solo_sin_foto'] == '1' ? 'checked' : '';
        $accionFormulario = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
        $inputEmbebido = isset($_GET['guagua_embedded'])
            ? '<input type="hidden" name="guagua_embedded" value="1">'
            : '';
        echo '<div class="card shadow-sm mb-4 no-print">
                <div class="card-body">
                    <h5 class="card-title">Seleccionar Grupo y Opciones</h5>
                    <form action="' . $accionFormulario . '" method="get" class="form-inline align-items-center mb-3">
                        ' . $inputEmbebido . '
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
                                <input type="checkbox" class="custom-control-input" id="solo_sin_foto" name="solo_sin_foto" value="1" ' . $checkedSinFoto . '>
                                <label class="custom-control-label" for="solo_sin_foto">Solo estudiantes sin foto</label>
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

        // ── Detectar si la ruta es una URL (Google Sheets publicado, etc.) ──────────
        if (filter_var($ruta, FILTER_VALIDATE_URL)) {
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
                throw new Exception("No se pudo descargar el archivo desde la URL. Verifica que la hoja esté publicada en Google Sheets como .xlsx.");
            }

            $archivoTemporal = sys_get_temp_dir() . '/reporte_temp_' . uniqid() . '.xlsx';
            file_put_contents($archivoTemporal, $contenido);
            $rutaLectura = $archivoTemporal;

        } else {
            if (!file_exists($ruta)) {
                throw new Exception("El archivo Excel no se encontró en: {$ruta}");
            }
            $rutaLectura = $ruta;
        }

        try {
            // Suprimir Deprecated de PHPSpreadsheet en PHP 8.2
            $nivelAnterior = error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_WARNING);
            $spreadsheet   = IOFactory::load($rutaLectura);
            $worksheet     = $spreadsheet->getActiveSheet();
            error_reporting($nivelAnterior);

            $notasPorEstudiante = [];

            for ($row = 2; $row <= $ultimaFila; $row++) {
                $documento = trim((string)$worksheet->getCell('B' . $row)->getValue());

                if (empty($documento)) continue;

                $notas = [];
                foreach ($materias as $nombreMateria => $columna) {
                    $celda = $worksheet->getCell($columna . $row);

                    // ── Estrategia de lectura de valor ───────────────────────────────────────
                    // Las celdas con referencias externas (='G:\...\[archivo.xlsx]'!$T12)
                    // NO pueden resolverse por PHPSpreadsheet en tiempo de ejecución.
                    // Excel guarda el último valor calculado en la propiedad "cached value".
                    // Usamos esa caché primero; solo si está vacía intentamos calcular.

                    $valor = $celda->getOldCalculatedValue(); // Valor en caché guardado por Excel

                    if (!is_numeric($valor)) {
                        // Segundo intento: valor crudo (puede ser número directo, no fórmula)
                        $valor = $celda->getValue();
                    }

                    if (!is_numeric($valor)) {
                        // Tercer intento: calcular (funciona para fórmulas simples sin refs externas)
                        try {
                            $valor = @$celda->getCalculatedValue();
                        } catch (\Throwable $e) {
                            $valor = 0;
                        }
                    }

                    $notas[$nombreMateria] = round((float)(is_numeric($valor) ? $valor : 0), 2);
                }

                $notasPorEstudiante[$documento] = [
                    'nombre' => trim((string)$worksheet->getCell('A' . $row)->getValue()),
                    'grado'  => trim((string)$worksheet->getCell('C' . $row)->getValue()),
                    'notas'  => $notas
                ];
            }
        } finally {
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

    function estudianteTieneFotoReporte($estudiante)
    {
        if (!is_array($estudiante)) return false;
        $foto = strtolower(basename(trim((string)($estudiante['foto'] ?? ''))));
        $fotosPredeterminadas = ['user-icon.png', 'user-iconf.png', 'user-icon2.png', 'user-iconf2.png', ''];
        return !in_array($foto, $fotosPredeterminadas, true);
    }

    function fotoReporteEstudiante($estudiante)
    {
        return rtrim(READFILE_URL, '/') . '/foto/' . rawurlencode(guagua_persona_foto($estudiante['foto'] ?? '', $estudiante['genero'] ?? ''));
    }

    function renderizarFichaEstudiante($grado, $documento, $estudianteDB, $infoExcel, $grupoSeleccionado, $config, $mensajeWhatsapp, $periodoActivo, $nombreParaMensaje = '', $estudianteRegistro = [])
    {
        $nombreCompleto = htmlspecialchars($estudianteDB);
        $nombreArchivo = preg_replace('/[^\\pL\\pN]+/u', '_', trim((string)$estudianteDB));
        $nombreArchivo = trim((string)$nombreArchivo, '_') ?: 'estudiante_' . $documento;
        $enlaceHojaVida = '../usuario/hoja_vida_estudiante.php?estudiante=' . rawurlencode((string)$documento);
        $docenteEmisor = 'Andres Paz';
        $sede = htmlspecialchars($config['sede']);
        $institucion = htmlspecialchars($config['institucion']);
        $partesNombre = preg_split('/\\s+/u', trim((string)($nombreParaMensaje ?: $estudianteDB)));
        $primerNombre = $partesNombre[0] ?? 'estudiante';
        $numeroPeriodo = !empty($periodoActivo['nombre_periodo']) ? (string)$periodoActivo['nombre_periodo'] : 'sin período activo';
        $fechaFin = !empty($periodoActivo['fecha_fin'])
            ? date('d/m/Y', strtotime($periodoActivo['fecha_fin']))
            : 'sin fecha programada';
        $mensajePersonalizado = strtr($mensajeWhatsapp, [
            '{nombre}' => $primerNombre,
            '{periodo}' => $numeroPeriodo,
            '{fecha_fin}' => $fechaFin,
        ]);
        $tieneFoto = estudianteTieneFotoReporte($estudianteRegistro);
        $fotoEstudiante = fotoReporteEstudiante($estudianteRegistro);
       
        ?>
        <div class="report-wrapper" data-reporte-archivo="<?php echo htmlspecialchars($nombreArchivo, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="card shadow-sm report-card">
                <div class="card-header">
                    <div class="report-student-heading">
                        <img class="report-student-photo" src="<?php echo htmlspecialchars($fotoEstudiante, ENT_QUOTES, 'UTF-8'); ?>" alt="Foto de <?php echo $nombreCompleto; ?>" crossorigin="anonymous">
                        <div>
                            <h5 class="mb-1">
                                SEGUIMIENTO DE: <a class="nombre-estudiante student-profile-link" href="<?php echo htmlspecialchars($enlaceHojaVida, ENT_QUOTES, 'UTF-8'); ?>" title="Abrir hoja de vida de <?php echo $nombreCompleto; ?>"><?php echo $nombreCompleto; ?> <span aria-hidden="true">↗</span></a>
                            </h5>
                            <span class="badge badge-secondary">Grado <?php echo $grado; ?> | <?php echo $institucion; ?> - <?php echo $sede; ?></span>
                            <?php if (!$tieneFoto): ?><span class="badge badge-warning report-photo-status">Sin foto registrada</span><?php endif; ?>
                        </div>
                    </div>
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

                <section class="report-signatures" aria-label="Confirmación de emisión y recibido del informe">
                    <div class="signature-block signature-issuer">
                        <span class="signature-label">Emite el informe</span>
                        <div class="signature-line"></div>
                        <strong class="signature-name"><?php echo $docenteEmisor; ?></strong>
                        <span class="signature-helper">Docente emisor del informe</span>
                    </div>

                    <div class="signature-block signature-receipt">
                        <span class="signature-label">Recibido por el acudiente</span>
                        <div class="signature-line signature-line-receipt"></div>
                        <strong class="signature-name signature-name-empty">Firma del acudiente</strong>
                        <span class="signature-helper">Nombre completo y fecha de recibido</span>
                    </div>
                </section>
            </div>
            </div>
            <div class="report-actions no-print text-right mt-2">
                <button type="button" class="btn btn-outline-success btn-sm copiar-mensaje-whatsapp"
                    data-mensaje-whatsapp="<?php echo htmlspecialchars($mensajePersonalizado, ENT_QUOTES, 'UTF-8'); ?>"
                    title="Copiar mensaje personalizado para WhatsApp">
                    Copiar mensaje WhatsApp
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm descargar-reporte-individual">
                    Descargar imagen de este reporte
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm copiar-imagen-reporte"
                    title="Copiar la imagen para pegarla directamente en WhatsApp">
                    Copiar imagen
                </button>
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
    <!-- Bootstrap 4.4.1 local: misma versión que usaba este reporte desde la CDN. -->
    <link rel="stylesheet" href="/guagua/comun/vendor/bootstrap-4.4.1/bootstrap.min.css">
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
        .student-profile-link { color: inherit; text-decoration: none; border-bottom: 1px dashed rgba(255,255,255,.7); }
        .student-profile-link:hover, .student-profile-link:focus { color: #dbeafe; text-decoration: none; border-bottom-style: solid; }
        .report-student-heading { align-items: center; display: flex; gap: 12px; }
        .report-student-photo { background: #e9eef5; border: 2px solid #fff; border-radius: 50%; box-shadow: 0 2px 8px rgba(15, 23, 42, .16); flex: 0 0 52px; height: 52px; object-fit: cover; width: 52px; }
        .report-photo-status { font-size: .68rem; margin-left: .35rem; vertical-align: middle; }
        .report-actions { min-height: 31px; }
        .report-group-capture {
            background: #fff;
            left: -100000px;
            padding: 1.5rem;
            position: absolute;
            top: 0;
            width: 1360px;
        }
        .report-group-capture .report-card { margin-bottom: 1.5rem; }

        .report-signatures {
            background: #f8fafc;
            border-top: 1px solid #dbe4ee;
            display: flex;
            gap: 2.5rem;
            margin-top: 1.5rem;
            padding: 1.25rem 1rem 0.5rem;
            page-break-inside: avoid;
        }

        .signature-block {
            flex: 1 1 0;
            min-height: 105px;
        }

        .signature-label {
            color: #52616f;
            display: block;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            margin-bottom: 2.5rem;
            text-transform: uppercase;
        }

        .signature-line {
            border-bottom: 1px solid #475569;
            height: 1px;
            width: 100%;
        }

        .signature-name {
            color: #1f2937;
            display: block;
            font-size: 0.95rem;
            margin-top: 0.5rem;
        }

        .signature-name-empty {
            color: #6b7280;
            font-weight: 500;
        }

        .signature-helper {
            color: #6b7280;
            display: block;
            font-size: 0.78rem;
            margin-top: 0.15rem;
        }

        .col-geometria { display: none; }
        body.mostrar-geometria .col-geometria { display: table-cell; }

        @media (max-width: 575.98px) {
            .report-signatures {
                flex-direction: column;
                gap: 1.5rem;
            }
        }

        @media print {
            body { background-color: #fff; font-size: 10pt; }
            .no-print { display: none !important; }
            .report-card { border: 1px solid #dee2e6 !important; box-shadow: none !important; display: block !important; margin-bottom: 1rem; }
            .cut-line::after { background: #fff; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .thead-dark th { background-color: #343a40 !important; color: #ffffff !important; }
            body.mostrar-geometria .col-geometria { display: table-cell !important; }
            .report-signatures { background: #fff; border-top-color: #9ca3af; }
            .report-student-photo { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="container-fluid" style="max-width: 1400px;">
        <div class="text-center mb-4 no-print">
            <img src="../comun/img/Banner_guias.jpg" width="400" alt="Banner Institucional">
            <h2 class="mt-3">Plataforma de Seguimiento Académico</h2>
        </div>
        
        <?php mostrarFormularioSeleccion($config['grupos']); ?>

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 no-print">
            <span class="text-muted small mr-3">Personaliza el mensaje que se copia para WhatsApp desde las fichas.</span>
            <button type="button" class="btn btn-outline-success btn-sm mt-2 mt-sm-0" data-toggle="modal" data-target="#mensajeWhatsappModal">
                Configurar mensaje WhatsApp
            </button>
        </div>

        <?php if (isset($_GET['grupo'])) : ?>
            <div class="alert alert-info no-print shadow-sm d-flex flex-wrap align-items-center justify-content-between">
                <span><strong>Resultados: </strong> Se encontraron <span id="numResultados">0</span> estudiante(s).</span>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 no-print">
                <span class="text-muted small mb-2 mb-sm-0">Exporte las fichas como imagen PNG o en un único PDF con sus fotografías.</span>
                <div class="btn-group btn-group-sm" role="group" aria-label="Opciones de exportación">
                    <button type="button" id="descargar-reportes-grupales" class="btn btn-success">Descargar imagen grupal</button>
                    <button type="button" id="exportar-reportes-pdf" class="btn btn-danger">Exportar PDF completo</button>
                </div>
            </div>
            <div id="estado-descarga" class="small text-muted mb-3 no-print" role="status" aria-live="polite"></div>
            <div id="contenedorTarjetas">
            <?php
            try {
                $grupoId = (int)$_GET['grupo'];
                $grupoSeleccionado = $config['grupos'][$grupoId] ?? $config['grupos'][2];
                
                $filtroSoloPerdidas = isset($_GET['solo_perdidas']) && $_GET['solo_perdidas'] == '1';
                $filtroSoloSinFoto = isset($_GET['solo_sin_foto']) && $_GET['solo_sin_foto'] == '1';

                // Usar 'ruta_activa': ya fue calculada arriba según el entorno (local o web)
                $notasExcel = cargarNotasDesdeExcel(
                    $grupoSeleccionado['ruta_activa'],
                    $grupoSeleccionado['ultimafila'],
                    $grupoSeleccionado['materias']
                );
                // Cada ejecución del reporte deja una instantánea auditable de las
                // valoraciones, únicamente si la fecha pertenece al período vigente.
                $servicioNotas = new NotasAuditoriaService($mysqli);
                $periodoActivo = $servicioNotas->obtenerPeriodoActual();
                $auditoriaLote = $servicioNotas->guardarLote($notasExcel);
                if ($auditoriaLote['ok']) {
                    $periodoActivo = $auditoriaLote['periodo'];
                    echo '<div class="alert alert-success no-print shadow-sm"><strong>Notas sincronizadas.</strong> ' . htmlspecialchars($auditoriaLote['mensaje']) . ' Período ' . htmlspecialchars($auditoriaLote['periodo']['nombre_periodo']) . ' (' . htmlspecialchars($auditoriaLote['periodo']['fecha_inicio']) . ' al ' . htmlspecialchars($auditoriaLote['periodo']['fecha_fin']) . '). <a href="../apps/notas/" class="alert-link">Abrir auditoría</a></div>';
                } else {
                    echo '<div class="alert alert-warning no-print shadow-sm"><strong>Notas no sincronizadas:</strong> ' . htmlspecialchars($auditoriaLote['mensaje']) . '</div>';
                }
                
                $idEstudiante = $_GET['id_estudiante'] ?? '';
               
                $estudiantesDB = COMUN::llamar_estudiantes_grado_Vallesol(
                    $grupoSeleccionado['min_grado'],
                    $grupoSeleccionado['max_grado'],
                    $idEstudiante
                );

                if (empty($estudiantesDB)) {
                    echo '<div class="alert alert-warning">No se encontraron estudiantes en la base de datos para el grupo y/o estudiante seleccionado.</div>';
                }

                $estudiantesPorDocumento = [];
                foreach ($estudiantesDB as $estudiante) {
                    $documentoNormalizado = preg_replace('/\s+/', '', (string)$estudiante['id_usuario']);
                    $estudiantesPorDocumento[$documentoNormalizado] = $estudiante;
                }

                foreach ($notasExcel as $documento => $datos) {
                    $nombre = $datos['nombre'];
                    $grado = $datos['grado'];
                    $notas = $datos['notas']; 
                    $documentoNormalizado = preg_replace('/\s+/', '', (string)$documento);
                    $estudianteRegistro = $estudiantesPorDocumento[$documentoNormalizado] ?? [];
                    
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

                    if ($filtroSoloSinFoto && estudianteTieneFotoReporte($estudianteRegistro)) {
                        $mostrarEstudiante = false;
                    }

                    if ($mostrarEstudiante) {
                        $nombreRegistrado = $estudianteRegistro['nombre'] ?? $nombre;
                        renderizarFichaEstudiante($grado, $documento, $nombre, $notas, $grupoSeleccionado, $config, $mensajeWhatsapp, $periodoActivo, $nombreRegistrado, $estudianteRegistro);
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
                <h5 class="modal-title" id="configModalLabel">Configurar rutas de entrada y salida</h5>
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
                  <p class="text-muted small mb-1">Ingrese las rutas absolutas de los archivos Excel y la carpeta donde se guardarán las imágenes para WhatsApp. <?php echo $entorno_badge; ?></p>
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
                                  value="<?php echo htmlspecialchars($config['grupos'][$gid]['ruta_excel_web'], ENT_QUOTES, 'UTF-8'); ?>">
                          </div>
                      </div>
                  </div>
                  <?php endforeach; ?>
                  <div class="form-group mb-0">
                      <label class="small mb-1" for="ruta_salida_whatsapp">&#128193; Carpeta de salida de imágenes para WhatsApp</label>
                      <input type="text" class="form-control form-control-sm" id="ruta_salida_whatsapp"
                          name="ruta_salida_whatsapp"
                          value="<?php echo htmlspecialchars($rutaSalidaWhatsapp, ENT_QUOTES, 'UTF-8'); ?>"
                          placeholder="Ej: D:\\Reportes\\WhatsApp">
                      <small class="form-text text-muted">Las imágenes individuales y grupales se guardarán aquí. La carpeta se crea automáticamente si no existe.</small>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="guardar_rutas" class="btn btn-primary">Guardar Cambios</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade no-print" id="mensajeWhatsappModal" tabindex="-1" role="dialog" aria-labelledby="mensajeWhatsappModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="post" action="">
              <div class="modal-header bg-light">
                <h5 class="modal-title" id="mensajeWhatsappModalLabel">Mensaje para WhatsApp</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                  <p class="small text-muted">Personaliza el mensaje que se copiará desde cada ficha. Puedes usar <code>{nombre}</code>, <code>{periodo}</code> y <code>{fecha_fin}</code>.</p>
                  <div class="form-group mb-0">
                      <label for="mensaje_whatsapp">Plantilla del mensaje</label>
                      <textarea class="form-control" id="mensaje_whatsapp" name="mensaje_whatsapp" rows="7" required aria-describedby="ayuda-mensaje-whatsapp"><?php echo htmlspecialchars($mensajeWhatsapp, ENT_QUOTES, 'UTF-8'); ?></textarea>
                      <small id="ayuda-mensaje-whatsapp" class="form-text text-muted">Se guarda para las próximas visitas y se aplica a todas las fichas. {nombre} es el primer nombre del estudiante; el período y su fecha de cierre se toman del período activo.</small>
                  </div>
                  <button type="button" id="restaurar-mensaje-whatsapp" class="btn btn-link btn-sm px-0 mt-2">Restaurar texto predeterminado</button>
                  <p class="small font-weight-bold mb-1 mt-3">Vista previa con datos de ejemplo</p>
                  <div id="vista-previa-whatsapp" class="border rounded bg-light p-3 small" style="white-space: pre-wrap; overflow-wrap: anywhere;" aria-live="polite"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" name="guardar_mensaje_whatsapp" class="btn btn-success">Guardar mensaje</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Dependencias JS para Bootstrap -->
    <script src="/guagua/comun/vendor/bootstrap-4.4.1/jquery-3.4.1.slim.min.js"></script>
    <script src="/guagua/comun/vendor/bootstrap-4.4.1/popper.min.js"></script>
    <script src="/guagua/comun/vendor/bootstrap-4.4.1/bootstrap.min.js"></script>
    <script src="js/html2canvas-1.4.1.min.js"></script>
    <script src="/guagua/comun/vendor/jspdf.umd.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const plantillaWhatsapp = document.getElementById('mensaje_whatsapp');
            const vistaPreviaWhatsapp = document.getElementById('vista-previa-whatsapp');
            function actualizarVistaPreviaWhatsapp() {
                const ejemplos = {nombre: 'María', periodo: '3', fecha_fin: '30/11/2026'};
                vistaPreviaWhatsapp.textContent = plantillaWhatsapp.value.replace(/\{(nombre|periodo|fecha_fin)\}/g, function(_, clave) {
                    return ejemplos[clave];
                });
            }
            plantillaWhatsapp.addEventListener('input', actualizarVistaPreviaWhatsapp);
            document.getElementById('restaurar-mensaje-whatsapp').addEventListener('click', function() {
                plantillaWhatsapp.value = <?php echo json_encode($mensajeWhatsappPredeterminado, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
                actualizarVistaPreviaWhatsapp();
            });
            actualizarVistaPreviaWhatsapp();

            const estadoDescarga = document.getElementById('estado-descarga');

            function informarDescarga(mensaje, esError) {
                if (!estadoDescarga) return;
                estadoDescarga.textContent = mensaje;
                estadoDescarga.className = 'small mb-3 no-print ' + (esError ? 'text-danger' : 'text-muted');
            }

            function nombreSeguro(nombre, respaldo) {
                const resultado = String(nombre || respaldo)
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-zA-Z0-9_-]+/g, '_')
                    .replace(/^_+|_+$/g, '');
                return resultado || respaldo;
            }

            function blobDesdeCanvas(canvas) {
                return new Promise(function(resolve, reject) {
                    canvas.toBlob(function(blob) {
                        if (blob) {
                            resolve(blob);
                        } else {
                            reject(new Error('No fue posible crear la imagen.'));
                        }
                    }, 'image/png');
                });
            }

            function descargarBlob(blob, nombreArchivo) {
                const enlace = document.createElement('a');
                const url = URL.createObjectURL(blob);
                enlace.href = url;
                enlace.download = nombreArchivo + '.png';
                document.body.appendChild(enlace);
                enlace.click();
                enlace.remove();
                window.setTimeout(function() {
                    URL.revokeObjectURL(url);
                }, 1000);
            }

            async function guardarEnRutaSalida(blob, nombreArchivo) {
                const datos = new FormData();
                datos.append('imagen', blob, nombreArchivo + '.png');
                datos.append('nombre', nombreArchivo);

                const respuesta = await fetch('guardar_resumen_whatsapp.php', {
                    method: 'POST',
                    body: datos,
                    credentials: 'same-origin'
                });
                const resultado = await respuesta.json().catch(function() { return {}; });
                if (!respuesta.ok || !resultado.ok) {
                    throw new Error(resultado.mensaje || 'No se pudo guardar la imagen en la carpeta configurada.');
                }
                return resultado;
            }

            async function crearImagen(elemento) {
                if (typeof window.html2canvas !== 'function') {
                    throw new Error('No se pudo cargar el generador local de imágenes.');
                }

                await esperarImagenes(elemento);
                if (document.fonts && document.fonts.ready) await document.fonts.ready;

                return window.html2canvas(elemento, {
                    backgroundColor: '#ffffff',
                    logging: false,
                    scale: 2,
                    useCORS: true
                });
            }

            function esperarImagenes(elemento) {
                const imagenes = Array.from(elemento.querySelectorAll('img'));
                return Promise.all(imagenes.map(function(imagen) {
                    if (imagen.complete) return Promise.resolve();
                    return new Promise(function(resolve) {
                        const terminar = function() {
                            imagen.removeEventListener('load', terminar);
                            imagen.removeEventListener('error', terminar);
                            resolve();
                        };
                        imagen.addEventListener('load', terminar, { once: true });
                        imagen.addEventListener('error', terminar, { once: true });
                        window.setTimeout(terminar, 5000);
                    });
                }));
            }

            async function convertirEnImagen(elemento, nombreArchivo) {
                const canvas = await crearImagen(elemento);
                const blob = await blobDesdeCanvas(canvas);
                descargarBlob(blob, nombreArchivo);
                const resultado = await guardarEnRutaSalida(blob, nombreArchivo);
                return resultado;
            }

            async function copiarImagenAlPortapapeles(canvas) {
                if (!navigator.clipboard || typeof window.ClipboardItem === 'undefined' || !window.isSecureContext) {
                    throw new Error('Tu navegador no permite copiar imágenes aquí. Abre la plataforma mediante HTTPS o localhost.');
                }

                const imagen = await blobDesdeCanvas(canvas);
                await navigator.clipboard.write([
                    new ClipboardItem({ 'image/png': imagen })
                ]);
            }

            document.querySelectorAll('.descargar-reporte-individual').forEach(function(boton) {
                boton.addEventListener('click', async function() {
                    const reporte = boton.closest('.report-wrapper');
                    const tarjeta = reporte ? reporte.querySelector('.report-card') : null;
                    if (!tarjeta) return;

                    boton.disabled = true;
                    informarDescarga('Preparando la imagen del reporte individual...');
                    try {
                        const resultado = await convertirEnImagen(tarjeta, 'reporte_' + nombreSeguro(reporte.dataset.reporteArchivo, 'estudiante'));
                        informarDescarga('Imagen descargada y guardada en la ruta configurada: ' + resultado.archivo + '.');
                    } catch (error) {
                        informarDescarga(error.message || 'No se pudo generar la imagen del reporte.', true);
                    } finally {
                        boton.disabled = false;
                    }
                });
            });

            document.querySelectorAll('.copiar-imagen-reporte').forEach(function(boton) {
                boton.addEventListener('click', async function() {
                    const reporte = boton.closest('.report-wrapper');
                    const tarjeta = reporte ? reporte.querySelector('.report-card') : null;
                    if (!tarjeta) return;

                    const textoOriginal = boton.textContent;
                    boton.disabled = true;
                    informarDescarga('Preparando la imagen para copiar...');
                    try {
                        const canvas = await crearImagen(tarjeta);
                        await copiarImagenAlPortapapeles(canvas);
                        boton.textContent = 'Imagen copiada';
                        informarDescarga('La imagen se copió. Abre WhatsApp y pégala en la conversación.');
                    } catch (error) {
                        informarDescarga(error.message || 'No se pudo copiar la imagen.', true);
                    } finally {
                        window.setTimeout(function() {
                            boton.textContent = textoOriginal;
                            boton.disabled = false;
                        }, 1600);
                    }
                });
            });

            async function copiarAlPortapapeles(texto) {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(texto);
                    return;
                }

                const areaTemporal = document.createElement('textarea');
                areaTemporal.value = texto;
                areaTemporal.setAttribute('readonly', '');
                areaTemporal.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0;';
                document.body.appendChild(areaTemporal);
                areaTemporal.select();
                const copiado = document.execCommand('copy');
                areaTemporal.remove();
                if (!copiado) throw new Error('El navegador no permitió copiar el mensaje.');
            }

            document.querySelectorAll('.copiar-mensaje-whatsapp').forEach(function(boton) {
                boton.addEventListener('click', async function() {
                    const mensaje = boton.dataset.mensajeWhatsapp || '';
                    if (!mensaje) {
                        informarDescarga('No hay un mensaje configurado para copiar.', true);
                        return;
                    }

                    const textoOriginal = boton.textContent;
                    boton.disabled = true;
                    try {
                        await copiarAlPortapapeles(mensaje);
                        boton.textContent = 'Mensaje copiado';
                        informarDescarga('El mensaje personalizado se copió. Ya puedes pegarlo en WhatsApp.');
                    } catch (error) {
                        informarDescarga(error.message || 'No se pudo copiar el mensaje.', true);
                    } finally {
                        window.setTimeout(function() {
                            boton.textContent = textoOriginal;
                            boton.disabled = false;
                        }, 1600);
                    }
                });
            });

            const botonGrupal = document.getElementById('descargar-reportes-grupales');
            if (botonGrupal) {
                botonGrupal.addEventListener('click', async function() {
                    const reportesVisibles = Array.from(document.querySelectorAll('.report-wrapper')).filter(function(reporte) {
                        return window.getComputedStyle(reporte).display !== 'none';
                    });

                    if (reportesVisibles.length === 0) {
                        informarDescarga('No hay reportes visibles para descargar.', true);
                        return;
                    }

                    const copia = document.createElement('div');
                    copia.className = 'report-group-capture';
                    reportesVisibles.forEach(function(reporte) {
                        const tarjeta = reporte.querySelector('.report-card');
                        if (tarjeta) copia.appendChild(tarjeta.cloneNode(true));
                    });
                    document.body.appendChild(copia);

                    botonGrupal.disabled = true;
                    informarDescarga('Preparando la imagen grupal de ' + reportesVisibles.length + ' reporte(s)...');
                    try {
                        const resultado = await convertirEnImagen(copia, 'reportes_grupales');
                        informarDescarga('Imagen grupal descargada y guardada en la ruta configurada: ' + resultado.archivo + '.');
                    } catch (error) {
                        informarDescarga(error.message || 'No se pudo generar la imagen grupal.', true);
                    } finally {
                        copia.remove();
                        botonGrupal.disabled = false;
                    }
                });
            }

            function agregarCanvasAlPdf(pdf, canvas) {
                const margen = 11;
                const anchoPagina = 297;
                const altoPagina = 210;
                const anchoDisponible = anchoPagina - (margen * 2);
                const altoDisponible = altoPagina - (margen * 2);
                const proporcion = anchoDisponible / canvas.width;
                const altoImagen = canvas.height * proporcion;
                const imagen = canvas.toDataURL('image/jpeg', 0.94);
                let desplazamiento = 0;

                while (desplazamiento < altoImagen) {
                    pdf.addImage(imagen, 'JPEG', margen, margen - desplazamiento, anchoDisponible, altoImagen, undefined, 'FAST');
                    desplazamiento += altoDisponible;
                    if (desplazamiento < altoImagen) pdf.addPage('a4', 'landscape');
                }
            }

            const botonPdf = document.getElementById('exportar-reportes-pdf');
            if (botonPdf) {
                botonPdf.addEventListener('click', async function() {
                    if (!window.jspdf || typeof window.jspdf.jsPDF !== 'function') {
                        informarDescarga('No se pudo cargar el generador de PDF.', true);
                        return;
                    }

                    const reportes = Array.from(document.querySelectorAll('.report-wrapper'));
                    if (reportes.length === 0) {
                        informarDescarga('No hay reportes para exportar.', true);
                        return;
                    }

                    botonPdf.disabled = true;
                    informarDescarga('Preparando el PDF completo de ' + reportes.length + ' reporte(s) con sus imágenes...');
                    try {
                        const pdf = new window.jspdf.jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4', compress: true });
                        pdf.setProperties({ title: 'Reporte académico completo', subject: 'Seguimiento de estudiantes' });

                        let primerReporte = true;
                        for (let indice = 0; indice < reportes.length; indice++) {
                            const tarjeta = reportes[indice].querySelector('.report-card');
                            if (!tarjeta) continue;
                            if (!primerReporte) pdf.addPage('a4', 'landscape');
                            informarDescarga('Generando PDF: ' + (indice + 1) + ' de ' + reportes.length + '...');
                            const canvas = await crearImagen(tarjeta);
                            agregarCanvasAlPdf(pdf, canvas);
                            primerReporte = false;
                        }

                        const hoy = new Date().toISOString().slice(0, 10);
                        pdf.save('reporte_academico_completo_' + hoy + '.pdf');
                        informarDescarga('El PDF completo se descargó con ' + reportes.length + ' reporte(s) e imágenes incluidas.');
                    } catch (error) {
                        informarDescarga(error.message || 'No se pudo generar el PDF.', true);
                    } finally {
                        botonPdf.disabled = false;
                    }
                });
            }

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
