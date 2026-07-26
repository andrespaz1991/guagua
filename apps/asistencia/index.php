<?php
/**
 * Dashboard de Asistencia Vallesol 2026
 * Autor: Andres Paz
 * Versión: 3.5 - Match Semántico Avanzado de Documentos
 * Stack: PHP 8 + TailwindCSS + Vanilla JS + MySQLi
 */

require '../../comun/lib/vendor/autoload.php';
require '../../comun/conexion.php'; // Conexión a Base de Datos ($mysqli)

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// =================================================================
// 1. BACKEND: PROCESAMIENTO DE DATOS DEL EXCEL
// =================================================================

$config = [
    'archivo'     =>  'G:/Mi unidad/Vallesol2026/Planeaciones/Asistencia Josefina-Vallesol2026.xlsx',
    'anio'        =>  2026,
    'meses'       => [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre'
    ],
    'fila_header' => 11,
    'fila_inicio' => 12,
    'fila_fin'    => 42,
    'col_nombre'  => 'C',
    'col_grado'   => 'B'
];

$mesesNumeros = [
    'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 'Mayo' => 5, 'Junio' => 6, 
    'Julio' => 7, 'Agosto' => 8, 'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11
];

$diasSemanaMap = [
    1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
];

// =================================================================
// 1.1 HORARIO DEL DOCENTE
// =================================================================
$horarioDocente = [
    'Lunes' => [
        ['hora' => '8:00-8:55',   'materia' => 'Economía / Ciencias Sociales', 'grados' => '9, 10, 11'],
        ['hora' => '8:55-9:50',   'materia' => 'Economía / Ciencias Sociales', 'grados' => '9, 10, 11'],
        ['hora' => '10:05-11:00', 'materia' => 'Ciencias Sociales',            'grados' => '6, 7, 8'],
        ['hora' => '11:00-11:55', 'materia' => 'Ciencias Sociales',            'grados' => '6, 7, 8'],
        ['hora' => '12:10-01:05', 'materia' => 'Emprendimiento',               'grados' => '9, 10, 11'],
        ['hora' => '01:05-02:00', 'materia' => 'Emprendimiento',               'grados' => '6, 7, 8'],
    ],
    'Martes' => [
        ['hora' => '8:00-8:55',   'materia' => 'Educación Física',             'grados' => '6, 7, 8'],
        ['hora' => '8:55-9:50',   'materia' => 'Educación Física',             'grados' => '9, 10, 11'],
        ['hora' => '10:05-11:00', 'materia' => 'Matemáticas',                  'grados' => '6, 7, 8'],
        ['hora' => '11:00-11:55', 'materia' => 'Matemáticas',                  'grados' => '6, 7, 8'],
        ['hora' => '12:10-01:05', 'materia' => 'Matemáticas',                  'grados' => '9, 10, 11'],
        ['hora' => '01:05-02:00', 'materia' => 'Matemáticas',                  'grados' => '9, 10, 11'],
    ],
    'Miércoles' => [
        ['hora' => '8:00-8:55',   'materia' => 'Tecnología',                   'grados' => '9, 10, 11'],
        ['hora' => '8:55-9:50',   'materia' => 'Tecnología',                   'grados' => '9, 10, 11'],
        ['hora' => '10:05-11:00', 'materia' => 'Tecnología',                   'grados' => '6, 7, 8'],
        ['hora' => '11:00-11:55', 'materia' => 'Tecnología',                   'grados' => '6, 7, 8'],
        ['hora' => '12:10-01:05', 'materia' => 'Geometría',                    'grados' => '9, 10, 11'],
        ['hora' => '01:05-02:00', 'materia' => 'Geometría',                    'grados' => '6, 7, 8'],
    ],
    'Jueves' => [
        ['hora' => '8:00-8:55',   'materia' => 'Educación Física',             'grados' => '9, 10, 11'],
        ['hora' => '8:55-9:50',   'materia' => 'Educación Física',             'grados' => '6, 7, 8'],
        ['hora' => '10:05-11:00', 'materia' => 'Matemáticas',                  'grados' => '6, 7, 8'],
        ['hora' => '11:00-11:55', 'materia' => 'Matemáticas',                  'grados' => '6, 7, 8'],
        ['hora' => '12:10-01:05', 'materia' => 'Matemáticas',                  'grados' => '9, 10, 11'],
        ['hora' => '01:05-02:00', 'materia' => 'Matemáticas',                  'grados' => '9, 10, 11'],
    ],
    'Viernes' => [
        ['hora' => '8:00-8:55',   'materia' => 'Física',                       'grados' => '9, 10, 11'],
        ['hora' => '8:55-9:50',   'materia' => 'Física',                       'grados' => '9, 10, 11'],
        ['hora' => '10:05-11:00', 'materia' => 'Urbanidad',                    'grados' => '6, 7, 8'],
        ['hora' => '11:00-11:55', 'materia' => 'Urbanidad',                    'grados' => '9, 10, 11'],
        ['hora' => '12:10-01:05', 'materia' => 'Ciencias Sociales',            'grados' => '6, 7, 8'],
        ['hora' => '01:05-02:00', 'materia' => 'Ciencias Sociales',            'grados' => '6, 7, 8'],
    ]
];

$datosReporte = [];
$reporteMaestroEstudiantes = []; // Directorio Global Anual
$errores = [];

if (file_exists($config['archivo'])) {
    try {
        $spreadsheet = IOFactory::load($config['archivo']);
        
        foreach ($config['meses'] as $mes) {
            $hoja = $spreadsheet->getSheetByName($mes);
            
            if (!$hoja) {
                $datosReporte[$mes] = null;
                continue;
            }

            // A. Detección Dinámica de Columnas
            $highestColumn = $hoja->getHighestDataColumn();
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
            
            $cols = ['ASISTENCIA' => null, 'FALTAS' => null, 'TARDE' => null, 'UNIFORME' => null];
            $columnas_dias = []; 

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $letra = Coordinate::stringFromColumnIndex($col);
                
                $cellHeader = $hoja->getCell($letra . $config['fila_header']);
                $valHeader = '';
                if ($cellHeader->isFormula()) {
                    $valHeader = $cellHeader->getOldCalculatedValue() ?? $cellHeader->getCalculatedValue();
                } else {
                    $valHeader = $cellHeader->getValue();
                }
                
                $valLimpiado = trim((string)($valHeader ?? ''));
                $valUpper = mb_strtoupper($valLimpiado);
                
                if (array_key_exists($valUpper, $cols)) {
                    $cols[$valUpper] = $letra;
                }
                
                if (is_numeric($valLimpiado) && (int)$valLimpiado >= 1 && (int)$valLimpiado <= 31) {
                    $columnas_dias[(int)$valLimpiado] = $letra;
                }
            }

            if (!$cols['ASISTENCIA']) {
                $datosReporte[$mes] = ['error' => 'Estructura de columnas no detectada'];
                continue;
            }

            $cols['JUSTIFICACIÓN'] = null;
            if ($cols['UNIFORME']) {
                $idxUniforme = Coordinate::columnIndexFromString($cols['UNIFORME']);
                $cols['JUSTIFICACIÓN'] = Coordinate::stringFromColumnIndex($idxUniforme + 1);
            }

            // B. Extracción de Estudiantes
            $getStringVal = function($colLetra, $fila) use ($hoja) {
                if ($colLetra === null) return '';
                try {
                    $cell = $hoja->getCell($colLetra . $fila);
                    $val = $cell->isFormula() ? ($cell->getOldCalculatedValue() ?? $cell->getCalculatedValue()) : $cell->getValue();
                    return trim((string)($val ?? ''));
                } catch (\Exception $e) { return ''; }
            };

            $getVal = function($colLetra, $fila) use ($hoja) {
                if ($colLetra === null) return null;
                try {
                    $cell = $hoja->getCell($colLetra . $fila);
                    $val = $cell->isFormula() ? ($cell->getOldCalculatedValue() ?? $cell->getCalculatedValue()) : $cell->getValue();
                    return ($val !== null && $val !== '') ? (int)$val : null;
                } catch (\Exception $e) { return 0; }
            };

            $estudiantes = [];
            
            for ($i = $config['fila_inicio']; $i <= $config['fila_fin']; $i++) {
                $nombre = $getStringVal($config['col_nombre'], $i);
                if (empty($nombre) || $nombre === '0') continue;

                $grado = $getStringVal($config['col_grado'], $i);
                if (empty($grado) || $grado === '0') $grado = '-';
                
                // Extraer solo números del grado para coincidencia exacta
                preg_match('/\d+/', $grado, $matches);
                $gradoNum = $matches[0] ?? '';

                // === ALIMENTAR DIRECTORIO MAESTRO GLOBAL ===
                $hashEstudiante = md5($nombre . $grado);
                if (!isset($reporteMaestroEstudiantes[$hashEstudiante])) {
                    $reporteMaestroEstudiantes[$hashEstudiante] = [
                        'hash'         => $hashEstudiante,
                        'nombre'       => mb_convert_case($nombre, MB_CASE_TITLE, "UTF-8"),
                        'grado'        => $grado,
                        'total_dias'   => 0,
                        'total_tardes' => 0,
                        'materias'     => [], 
                        'detalle_anual'=> [] // Historial completo del año
                    ];
                }

                // Sumar tardanzas
                $tardesMes = $getVal($cols['TARDE'], $i);
                if ($tardesMes > 0) {
                    $reporteMaestroEstudiantes[$hashEstudiante]['total_tardes'] += $tardesMes;
                }

                $fechas_faltas_detalle = [];
                
                foreach ($columnas_dias as $dia => $colLetra) {
                    $valorCeldaDia = mb_strtoupper($getStringVal($colLetra, $i));
                    
                    if (in_array($valorCeldaDia, ['F', 'A', 'X', '0', 'FALTA'])) {
                        
                        $numMes = $mesesNumeros[$mes] ?? 1;
                        $fechaStr = sprintf('%04d-%02d-%02d', $config['anio'], $numMes, $dia);
                        $numDiaSemana = (int)date('N', strtotime($fechaStr));
                        $nombreDiaSemana = $diasSemanaMap[$numDiaSemana] ?? 'Desconocido';
                        
                        $materias_perdidas = [];
                        $esDiaHabil = false;
                        
                        if (isset($horarioDocente[$nombreDiaSemana])) {
                            foreach ($horarioDocente[$nombreDiaSemana] as $clase) {
                                $gradosPermitidos = array_map('trim', explode(',', $clase['grados']));
                                if (in_array($gradoNum, $gradosPermitidos)) {
                                    $materias_perdidas[] = $clase;
                                    $esDiaHabil = true;
                                    
                                    $nombreMateriaGlobal = $clase['materia'];
                                    if (!isset($reporteMaestroEstudiantes[$hashEstudiante]['materias'][$nombreMateriaGlobal])) {
                                        $reporteMaestroEstudiantes[$hashEstudiante]['materias'][$nombreMateriaGlobal] = 0;
                                    }
                                    $reporteMaestroEstudiantes[$hashEstudiante]['materias'][$nombreMateriaGlobal]++;
                                }
                            }
                        }

                        if ($esDiaHabil) {
                            $reporteMaestroEstudiantes[$hashEstudiante]['total_dias']++;
                            
                            $infoDiaPerdido = [
                                'mes'          => $mes,
                                'dia'          => $dia,
                                'dia_semana'   => $nombreDiaSemana,
                                'fecha_formato'=> "$nombreDiaSemana, $dia de $mes",
                                'materias'     => $materias_perdidas,
                                'uniforme'     => (string)($getVal($cols['UNIFORME'], $i) ?? ''),
                                'justificacion'=> (string)($getStringVal($cols['JUSTIFICACIÓN'], $i) ?? '')
                            ];

                            $fechas_faltas_detalle[] = $infoDiaPerdido;
                            $reporteMaestroEstudiantes[$hashEstudiante]['detalle_anual'][] = $infoDiaPerdido;
                        }
                    }
                }

                $estudiantes[] = [
                    'hash'          => $hashEstudiante,
                    'nombre'        => $nombre,
                    'grado'         => $grado,
                    'asist'         => $getVal($cols['ASISTENCIA'], $i),
                    'falta'         => $getVal($cols['FALTAS'], $i),
                    'fechas_faltas' => $fechas_faltas_detalle, 
                    'tarde'         => $getVal($cols['TARDE'], $i),
                    'unif'          => $getVal($cols['UNIFORME'], $i),
                    'justif'        => $getStringVal($cols['JUSTIFICACIÓN'], $i)
                ];
            }
            $datosReporte[$mes] = $estudiantes;
        }

    } catch (Exception $e) {
        $errores[] = "Error crítico al cargar Excel: " . $e->getMessage();
    }
} else {
    $errores[] = "Archivo no encontrado: " . basename($config['archivo']);
}

// Filtrar reporte global: solo alumnos con inasistencias
$estudiantesConFaltasGlobal = array_filter($reporteMaestroEstudiantes, fn($e) => $e['total_dias'] > 0);
usort($estudiantesConFaltasGlobal, function($a, $b) {
    if ($a['grado'] == $b['grado']) return strcmp($a['nombre'], $b['nombre']);
    return strcmp($a['grado'], $b['grado']);
});


// =================================================================
// 1.2 EXPORTACIÓN A CSV
// =================================================================
if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=Reporte_Inasistencias_Vallesol_' . $config['anio'] . '.csv');
    
    $output = fopen('php://output', 'w');
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // Forzar BOM para Excel
    
    fputcsv($output, ['Grado', 'Estudiante', 'Días Perdidos', 'Tardanzas', 'Acumulado por Asignaturas']);
    
    foreach ($estudiantesConFaltasGlobal as $est) {
        $materiasArray = [];
        arsort($est['materias']);
        foreach($est['materias'] as $materia => $cantidad) {
            $materiasArray[] = "$materia ($cantidad)";
        }
        $materiasTexto = implode(', ', $materiasArray);
        
        fputcsv($output, [
            $est['grado'],
            $est['nombre'],
            $est['total_dias'],
            $est['total_tardes'],
            $materiasTexto
        ]);
    }
    fclose($output);
    exit(); 
}

// =================================================================
// 1.3 LÓGICA ROBUSTA DE SINCRONIZACIÓN AJAX
// =================================================================
if (isset($_GET['sync_bd']) && $_GET['sync_bd'] === '1') {
    header('Content-Type: application/json');
    
    // Función auxiliar para normalizar nombres a un formato estrictamente plano
    function limpiarTexto($str) {
        $str = mb_strtolower(trim($str), 'UTF-8');
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ä', 'ë', 'ï', 'ö', 'ü', ' '],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', ''],
            $str
        );
        return $str;
    }

    try {
        global $mysqli; 
        
        // 1. Construir un Mapa Semántico de la Base de Datos
        $sqlUsuarios = "SELECT id_usuario, nombre, apellido FROM usuario WHERE rol LIKE '%estudiante%'";
        $resUsers = $mysqli->query($sqlUsuarios);
        
        $mapaUsuarios = [];
        if ($resUsers) {
            while ($u = $resUsers->fetch_assoc()) {
                $nom = $u['nombre'] ?? '';
                $ape = $u['apellido'] ?? '';
                
                // Generar ambas combinaciones porque el Excel puede tener "Apellidos Nombres" o "Nombres Apellidos"
                $comb1 = limpiarTexto($nom . $ape);
                $comb2 = limpiarTexto($ape . $nom);
                
                // Guardar las combinaciones sin espacios ni tildes apuntando al ID real
                $mapaUsuarios[$comb1] = $u['id_usuario'];
                $mapaUsuarios[$comb2] = $u['id_usuario'];
            }
        }

        $procesados = 0;
        $doc_encontrados = 0;
        $doc_fallidos = 0;
        $nombres_erroneos = [];

        foreach ($estudiantesConFaltasGlobal as $est) {
            
            // 2. Limpiar el nombre extraído de Excel con la misma regla radical
            $nombreExcelLimpio = limpiarTexto($est['nombre']);
            $idUsuarioReal = 0;
            
            // 3. Buscar el Documento
            if (isset($mapaUsuarios[$nombreExcelLimpio])) {
                // Coincidencia exacta (sin espacios ni tildes)
                $idUsuarioReal = $mapaUsuarios[$nombreExcelLimpio];
                $doc_encontrados++;
            } else {
                // Fallback: Buscar coincidencia parcial (Si en Excel solo puso un apellido)
                $encontradoFallback = false;
                foreach($mapaUsuarios as $keyBD => $idBD) {
                    if (strpos($keyBD, $nombreExcelLimpio) !== false || strpos($nombreExcelLimpio, $keyBD) !== false) {
                        $idUsuarioReal = $idBD;
                        $doc_encontrados++;
                        $encontradoFallback = true;
                        break;
                    }
                }
                
                if (!$encontradoFallback) {
                    $doc_fallidos++;
                    if(!in_array($est['nombre'], $nombres_erroneos)) {
                        $nombres_erroneos[] = $est['nombre'];
                    }
                }
            }

            // 4. Inserción de la Inasistencia
            foreach ($est['detalle_anual'] as $faltaDia) {
                $fechaSQL = sprintf('%04d-%02d-%02d', $config['anio'], $mesesNumeros[$faltaDia['mes']], $faltaDia['dia']);
                
                foreach ($faltaDia['materias'] as $materiaPerdida) {
                    
                    $est_nom = $mysqli->real_escape_string($est['nombre']);
                    $mat_nom = $mysqli->real_escape_string($materiaPerdida['materia']);
                    $just    = $mysqli->real_escape_string($faltaDia['justificacion'] ?? '');
                    $unif    = $mysqli->real_escape_string($faltaDia['uniforme'] ?? '');
                    
                    // Si el ID sigue siendo 0, convertimos a cadena vacía o lo dejamos en 0 según diseño DB
                    // Como el id_usuario puede ser VARCHAR('1045231554' o 'n/a999'), lo manejamos como string
                    $doc_sql = $mysqli->real_escape_string($idUsuarioReal);

                    $sqlInsert = "INSERT INTO asistencias 
                                  (estudiante, materia, asistencias, fechas_clase, documento, uniforme, justificacion, fecha_actualizacion) 
                                  VALUES 
                                  ('$est_nom', '$mat_nom', 'NO', '$fechaSQL', '$doc_sql', '$unif', '$just', CURRENT_TIMESTAMP())
                                  ON DUPLICATE KEY UPDATE 
                                  asistencias = 'NO', 
                                  documento = VALUES(documento),
                                  uniforme = VALUES(uniforme),
                                  justificacion = VALUES(justificacion),
                                  fecha_actualizacion = CURRENT_TIMESTAMP()";
                    
                    if ($mysqli->query($sqlInsert)) {
                        $procesados++;
                    }
                }
            }
        }
        
        // Formatear mensaje para notificar cuántos alumnos no se encontraron
        $msg = "Se actualizaron $procesados inasistencias. $doc_encontrados alumnos vinculados exitosamente.";
        if ($doc_fallidos > 0) {
            $msg .= " ADVERTENCIA: $doc_fallidos alumnos no se encontraron en BD (Revisar nombres: " . implode(", ", array_slice($nombres_erroneos, 0, 3)) . ").";
        }

        echo json_encode([
            'success' => true, 
            'message' => $msg,
            'fallos' => $doc_fallidos
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => "Error BD: " . $e->getMessage()]);
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S.I. Asistencia Vallesol <?php echo $config['anio']; ?></title>
    <script src="../../comun/css/tailwindcss.css"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .fade-in { animation: fadeIn 0.3s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex flex-col overflow-hidden relative">

    <!-- Notificación Toast UI (Asíncrona) -->
    <div id="toast-sync" class="fixed bottom-6 right-6 bg-white border-l-4 border-indigo-500 shadow-2xl rounded-xl p-5 transform translate-y-32 opacity-0 transition-all duration-500 z-50 flex items-start gap-4 min-w-[340px] max-w-md">
        <div class="flex-shrink-0 mt-1">
            <i class="fa-solid fa-cloud-arrow-up text-indigo-500 text-2xl" id="toast-icon"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-slate-800" id="toast-title">Sincronizando...</h4>
            <p class="text-xs text-slate-500 font-medium mt-1 leading-relaxed" id="toast-msg">Conectando con la base de datos.</p>
        </div>
        <button onclick="hideToast()" class="ml-auto text-slate-400 hover:text-slate-600 focus:outline-none">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- HEADER -->
    <header class="bg-white border-b border-slate-200 shadow-sm z-10 flex-shrink-0">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 text-white p-2.5 rounded-lg shadow-md">
                    <i class="fa-solid fa-school"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 leading-none">Vallesol</h1>
                    <p class="text-xs text-slate-500 font-medium tracking-wide">Sistema de Seguimiento Estudiantil <?php echo $config['anio']; ?></p>
                </div>
            </div>
            <div class="text-sm text-slate-500 font-semibold border-l-2 border-slate-200 pl-4 hidden sm:block">
                Lic. Andres Paz
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex overflow-hidden">
        <!-- SIDEBAR / TABS -->
        <aside class="w-64 bg-white border-r border-slate-200 flex-shrink-0 overflow-y-auto hidden md:block">
            
            <!-- Menú Anual Centralizado -->
            <div class="p-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Reporte General</h3>
                <button onclick="switchTab('ReporteAnual')" 
                        id="btn-ReporteAnual"
                        class="w-full text-left px-4 py-2.5 rounded-r-md text-sm font-bold flex items-center justify-between group transition-all bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 mb-2">
                    <span class="flex items-center gap-2">
                        <i class="fa-solid fa-folder-open w-4 text-indigo-500"></i> Reporte Anual Detallado
                    </span>
                </button>
            </div>

            <div class="p-5 pt-0 border-t border-slate-200 mt-2">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 mt-6">Periodos Académicos</h3>
                <nav class="space-y-1.5">
                    <?php 
                    foreach ($config['meses'] as $index => $mes): 
                        $hasData = is_array($datosReporte[$mes] ?? null) && !isset($datosReporte[$mes]['error']);
                    ?>
                        <button onclick="switchTab('<?php echo $mes; ?>')" 
                                id="btn-<?php echo $mes; ?>"
                                class="w-full text-left px-4 py-2.5 rounded-r-md text-sm font-medium flex items-center justify-between group transition-all text-slate-600 hover:bg-slate-50 border-l-4 border-transparent hover:border-indigo-400 hover:text-indigo-600">
                            <span><?php echo $mes; ?></span>
                            <?php if ($hasData): ?>
                                <span class="bg-white text-slate-600 py-0.5 px-2.5 rounded-full text-xs font-bold border border-slate-200 shadow-sm">
                                    <?php echo count($datosReporte[$mes]); ?>
                                </span>
                            <?php else: ?>
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                </nav>
            </div>
        </aside>

        <!-- DATA AREA -->
        <div class="flex-1 flex flex-col bg-slate-50 overflow-hidden relative">
            
            <?php if (!empty($errores)): ?>
                <div class="p-6">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">
                                    <?php echo implode('<br>', $errores); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex-1 overflow-auto p-4 sm:p-8" id="content-container">
                
                <!-- TAB 1: REPORTE ANUAL DETALLADO -->
                <div id="view-ReporteAnual" class="tab-content fade-in">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div>
                                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                                    Reporte Anual Detallado
                                </h2>
                                <p class="text-sm text-slate-500 mt-1">Directorio consolidado de inasistencias.</p>
                            </div>
                            <div class="flex gap-3">
                                <button onclick="ejecutarSincronizacion()" class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-md transition-all active:scale-95 group focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <i class="fa-solid fa-database group-hover:fa-bounce" id="sync-icon"></i>
                                    Sincronizar BD
                                </button>
                                <a href="?exportar=csv" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-md transition-all focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                    <i class="fa-solid fa-file-csv text-lg"></i> Exportar
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if(count($estudiantesConFaltasGlobal) > 0): ?>
                        <div class="bg-white shadow-sm border border-slate-200 sm:rounded-xl overflow-x-auto no-scrollbar">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr>
                                        <th scope="col" class="py-4 pl-4 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider sm:pl-6 w-16">Grado</th>
                                        <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[200px] w-64">Estudiante</th>
                                        <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-32">Días Perdidos (Total Anual)</th>
                                        <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Tardanzas</th>
                                        <th scope="col" class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Acumulado por Asignaturas</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <?php foreach ($estudiantesConFaltasGlobal as $est): ?>
                                        <tr class="hover:bg-rose-50/30 transition-colors group">
                                            <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm font-bold text-slate-700 sm:pl-6">
                                                <?php echo htmlspecialchars($est['grado']); ?>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-slate-900 font-semibold">
                                                <?php echo htmlspecialchars($est['nombre']); ?>
                                            </td>
                                            
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-center">
                                                <button onclick="toggleFaltas('anual-faltas-<?php echo $est['hash']; ?>')" class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-extrabold bg-rose-100 text-rose-800 shadow hover:bg-rose-200 hover:scale-105 transition-all outline-none" title="Desplegar historial anual completo">
                                                    <?php echo $est['total_dias']; ?> 
                                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                                </button>
                                            </td>
                                            
                                            <td class="whitespace-nowrap px-3 py-5 text-sm text-center font-bold <?php echo $est['total_tardes'] > 0 ? 'text-amber-600' : 'text-slate-400'; ?>">
                                                <?php echo $est['total_tardes']; ?>
                                            </td>

                                            <td class="px-4 py-5 text-sm">
                                                <div class="flex flex-wrap gap-2">
                                                    <?php 
                                                    arsort($est['materias']);
                                                    foreach($est['materias'] as $materia => $cantidadFaltas): 
                                                        $colorBadge = $cantidadFaltas >= 5 ? 'bg-rose-500 text-white border-rose-600' : 
                                                                     ($cantidadFaltas >= 3 ? 'bg-amber-100 text-amber-800 border-amber-200' : 
                                                                     'bg-slate-100 text-slate-700 border-slate-200');
                                                    ?>
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border <?php echo $colorBadge; ?> shadow-sm">
                                                            <?php echo htmlspecialchars($materia); ?>
                                                            <span class="ml-1.5 pl-1.5 border-l border-current opacity-80 font-bold"><?php echo $cantidadFaltas; ?></span>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- ACORDEÓN: REPORTE CRONOLÓGICO ANUAL -->
                                        <tr id="anual-faltas-<?php echo $est['hash']; ?>" class="hidden bg-slate-100/60 border-b-4 border-slate-200">
                                            <td colspan="5" class="px-6 py-6">
                                                <div class="max-w-5xl mx-auto">
                                                    <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4">
                                                        <i class="fa-solid fa-timeline text-rose-500"></i>
                                                        Historial Cronológico de Clases Perdidas en <?php echo $config['anio']; ?>
                                                    </h4>
                                                    
                                                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                                        <?php foreach ($est['detalle_anual'] as $faltaDia): ?>
                                                            <div class="bg-white border border-slate-300 rounded-lg shadow-sm overflow-hidden relative">
                                                                <div class="absolute top-0 left-0 w-1 h-full bg-rose-500"></div>
                                                                
                                                                <div class="bg-slate-50 border-b border-slate-200 px-4 py-2.5 flex justify-between items-center pl-5">
                                                                    <span class="font-bold text-slate-800 text-sm capitalize">
                                                                        <?php echo $faltaDia['fecha_formato']; ?>
                                                                    </span>
                                                                </div>
                                                                
                                                                <div class="p-4 pl-5">
                                                                    <ul class="space-y-3">
                                                                        <?php foreach ($faltaDia['materias'] as $materiaPerdida): ?>
                                                                            <li class="flex items-start gap-3 text-sm">
                                                                                <span class="font-mono font-semibold text-slate-600 bg-slate-200/70 px-2 py-0.5 rounded text-xs whitespace-nowrap border border-slate-300">
                                                                                    <?php echo $materiaPerdida['hora']; ?>
                                                                                </span>
                                                                                <span class="font-medium text-slate-900 leading-tight">
                                                                                    <?php echo htmlspecialchars($materiaPerdida['materia']); ?>
                                                                                </span>
                                                                            </li>
                                                                        <?php endforeach; ?>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-slate-300 rounded-xl bg-white shadow-sm">
                            <i class="fa-solid fa-medal text-5xl text-emerald-400 mb-3"></i>
                            <h3 class="mt-2 text-sm font-bold text-slate-900">¡Asistencia Perfecta Anual!</h3>
                            <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                                No se registran inasistencias consolidadas en el periodo evaluado.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>


                <!-- TABS: MESES REGULARES -->
                <?php foreach ($config['meses'] as $mes): ?>
                    <div id="view-<?php echo $mes; ?>" class="tab-content hidden fade-in">
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
                            <h2 class="text-3xl font-extrabold text-slate-800 tracking-tight"><?php echo $mes; ?></h2>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-white text-slate-600 border border-slate-200 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span> Asistencia
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-white text-slate-600 border border-slate-200 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 mr-2"></span> Faltas
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-white text-slate-600 border border-slate-200 shadow-sm">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span> Tardes
                                </span>
                            </div>
                        </div>

                        <?php if (isset($datosReporte[$mes]) && is_array($datosReporte[$mes]) && !isset($datosReporte[$mes]['error'])): ?>
                            
                            <div class="bg-white shadow-sm border border-slate-200 sm:rounded-xl overflow-x-auto no-scrollbar">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50 border-b border-slate-200">
                                        <tr>
                                            <th scope="col" class="py-4 pl-4 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider sm:pl-6 w-16">Grado</th>
                                            <th scope="col" class="px-3 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider min-w-[200px]">Estudiante</th>
                                            <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Asistencia</th>
                                            <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Faltas</th>
                                            <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Tardes</th>
                                            <th scope="col" class="px-3 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider w-24">Uniforme</th>
                                            <th scope="col" class="px-4 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider w-48 xl:w-64">Justificación</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        <?php foreach ($datosReporte[$mes] as $idxEst => $est): ?>
                                            
                                            <tr class="hover:bg-slate-50 transition-colors group">
                                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-slate-700 sm:pl-6">
                                                    <?php echo htmlspecialchars($est['grado']); ?>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-900 font-semibold">
                                                    <?php echo htmlspecialchars(mb_convert_case($est['nombre'], MB_CASE_TITLE, "UTF-8")); ?>
                                                </td>
                                                
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold <?php echo ($est['asist'] > 0) ? 'text-emerald-700 bg-emerald-50' : 'text-slate-400'; ?>">
                                                        <?php echo $est['asist'] ?? '-'; ?>
                                                    </span>
                                                </td>

                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                                    <?php if ($est['falta'] > 0): ?>
                                                        <?php if (!empty($est['fechas_faltas'])): ?>
                                                            <button onclick="toggleFaltas('mes-faltas-<?php echo md5($mes.$idxEst); ?>')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-bold bg-rose-100 text-rose-800 shadow-sm hover:bg-rose-200 hover:scale-105 transition-all outline-none" title="Ver reporte mensual">
                                                                <?php echo $est['falta']; ?>
                                                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-rose-100 text-rose-800 shadow-sm">
                                                                <?php echo $est['falta']; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-slate-300 font-medium">-</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                                    <?php if ($est['tarde'] > 0): ?>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-amber-50 text-amber-700">
                                                            <?php echo $est['tarde']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-slate-300 font-medium">-</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                                    <?php if ($est['unif'] === null): ?>
                                                        <span class="text-slate-300 text-xs font-medium">N/A</span>
                                                    <?php elseif ($est['unif'] > 0): ?>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-indigo-50 text-indigo-700">
                                                            <?php echo $est['unif']; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-slate-300 font-medium">-</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="px-4 py-4 text-sm text-slate-600 max-w-xs xl:max-w-sm truncate" title="<?php echo htmlspecialchars($est['justif']); ?>">
                                                    <?php if (!empty($est['justif']) && $est['justif'] !== '0'): ?>
                                                        <div class="flex items-center gap-2">
                                                            <i class="fa-solid fa-circle-info text-indigo-400"></i>
                                                            <span class="truncate font-medium text-slate-700"><?php echo htmlspecialchars($est['justif']); ?></span>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-slate-300">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                            <!-- Acordeón Mensual -->
                                            <?php if (!empty($est['fechas_faltas'])): ?>
                                                <tr id="mes-faltas-<?php echo md5($mes.$idxEst); ?>" class="hidden bg-slate-100/50 border-b-2 border-slate-200">
                                                    <td colspan="7" class="px-6 py-6">
                                                        <div class="max-w-5xl mx-auto">
                                                            <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-4">
                                                                <i class="fa-solid fa-calendar-xmark text-rose-500"></i>
                                                                Reporte Mensual de Clases Perdidas
                                                            </h4>
                                                            
                                                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                                                <?php foreach ($est['fechas_faltas'] as $faltaDia): ?>
                                                                    <div class="bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden relative">
                                                                        <div class="absolute top-0 left-0 w-1 h-full bg-rose-400"></div>
                                                                        
                                                                        <div class="bg-rose-50 border-b border-rose-100 px-4 py-2.5 flex justify-between items-center pl-5">
                                                                            <span class="font-bold text-rose-900 text-sm capitalize">
                                                                                <?php echo $faltaDia['fecha_formato']; ?>
                                                                            </span>
                                                                        </div>
                                                                        
                                                                        <div class="p-4 pl-5">
                                                                            <?php if (empty($faltaDia['materias'])): ?>
                                                                                <p class="text-sm text-slate-500 italic py-2">No se detectan clases curriculares.</p>
                                                                            <?php else: ?>
                                                                                <ul class="space-y-3">
                                                                                    <?php foreach ($faltaDia['materias'] as $materiaPerdida): ?>
                                                                                        <li class="flex items-start gap-3 text-sm">
                                                                                            <span class="font-mono font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded text-xs whitespace-nowrap border border-slate-200">
                                                                                                <?php echo $materiaPerdida['hora']; ?>
                                                                                            </span>
                                                                                            <span class="font-medium text-slate-800 leading-tight">
                                                                                                <?php echo htmlspecialchars($materiaPerdida['materia']); ?>
                                                                                            </span>
                                                                                        </li>
                                                                                    <?php endforeach; ?>
                                                                                </ul>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>

                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center h-64 text-center border-2 border-dashed border-slate-300 rounded-xl bg-white shadow-sm">
                                <i class="fa-regular fa-folder-open text-5xl text-slate-300 mb-3"></i>
                                <h3 class="mt-2 text-sm font-bold text-slate-900">Sin registros tabulados</h3>
                                <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
                                    <?php echo isset($datosReporte[$mes]['error']) ? htmlspecialchars($datosReporte[$mes]['error']) : 'No hay información procesable para ' . $mes . '.'; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        function showToast(title, msg, iconClass, colorClass, bounce) {
            const toast = document.getElementById('toast-sync');
            const icon = document.getElementById('toast-icon');
            
            document.getElementById('toast-title').innerText = title;
            document.getElementById('toast-msg').innerText = msg;
            
            icon.className = `fa-solid ${iconClass} ${colorClass} text-2xl ${bounce ? 'fa-bounce' : ''}`;
            toast.classList.remove('translate-y-32', 'opacity-0');
        }

        function hideToast() {
            const toast = document.getElementById('toast-sync');
            toast.classList.add('translate-y-32', 'opacity-0');
        }

        function ejecutarSincronizacion() {
            const syncIcon = document.getElementById('sync-icon');
            if (syncIcon) syncIcon.classList.add('fa-spin', 'fa-rotate');
            
            showToast('Sincronizando BD', 'Comparando nombres de Excel con BD y guardando inasistencias...', 'fa-cloud-arrow-up', 'text-indigo-500', true);
            
            fetch('?sync_bd=1')
                .then(res => res.json())
                .then(data => {
                    if (syncIcon) syncIcon.classList.remove('fa-spin', 'fa-rotate');
                    if(data.success) {
                        // Cambiamos a amarillo si hubo fallos de nombres
                        if(data.fallos && data.fallos > 0) {
                             showToast('Sincronización Incompleta', data.message, 'fa-triangle-exclamation', 'text-amber-500', false);
                        } else {
                             showToast('¡Sincronización Completada!', data.message, 'fa-circle-check', 'text-emerald-500', false);
                        }
                    } else {
                        showToast('Error de Sincronización', data.message, 'fa-circle-xmark', 'text-rose-500', false);
                    }
                    // Dar más tiempo si el mensaje es largo
                    setTimeout(hideToast, 12000);
                })
                .catch(err => {
                    if (syncIcon) syncIcon.classList.remove('fa-spin', 'fa-rotate');
                    showToast('Error de Red', 'Fallo de conexión. Verifica la configuración PDO/MySQLi.', 'fa-wifi', 'text-rose-500', false);
                    setTimeout(hideToast, 5000);
                });
        }

        function switchTab(mes) {
            document.querySelectorAll('.tab-content').forEach(el => {
                el.classList.add('hidden');
            });
            
            const view = document.getElementById('view-' + mes);
            if(view) view.classList.remove('hidden');

            document.querySelectorAll('nav button').forEach(btn => {
                btn.className = 'w-full text-left px-4 py-2.5 rounded-r-md text-sm font-medium flex items-center justify-between group transition-all text-slate-600 hover:bg-slate-50 border-l-4 border-transparent hover:border-indigo-400 hover:text-indigo-600 mb-2';
            });

            const activeBtn = document.getElementById('btn-' + mes);
            if(activeBtn) {
                activeBtn.className = 'w-full text-left px-4 py-2.5 rounded-r-md text-sm font-bold flex items-center justify-between group transition-all bg-indigo-50 text-indigo-700 border-l-4 border-indigo-600 mb-2';
            }
        }

        function toggleFaltas(idFila) {
            const fila = document.getElementById(idFila);
            if (fila) {
                fila.classList.toggle('hidden');
            }
        }
    </script>
</body>
</html>