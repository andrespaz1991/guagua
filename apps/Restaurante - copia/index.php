<?php
/**
 * ====================================================================
 * MÓDULO DE GENERACIÓN DE FORMATO PAE (Sede Vallesol)
 * ====================================================================
 * Procesa el reporte SIMAT/BD y mapea los campos al formato de Entrega.
 */
// Se mantiene la dependencia original del sistema
if(file_exists('../../clases/Fecha.Class.php')) {
    require_once '../../clases/Fecha.Class.php';
}

// Inclusión obligatoria del archivo de conexión a la base de datos
require_once '../../comun/conexion.php'; 


// Función auxiliar para buscar datos en la base de datos
function obtener_dato($doc_val, $campo){
    require_once("../../comun/conexion.php"); // Inclusión de conexión a BD
    global $mysqli; // Se requiere global para instanciar $mysqli en el ciclo
    
    if (!$mysqli) return '';
    
    $doc_escaped = $mysqli->real_escape_string($doc_val);
    
    if($campo=="fecha_nacimiento"){
        $sql='
        SELECT 
            id_usuario, 
            fecha_nacimiento, 
            IFNULL(TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()), 0) AS fecha_nacimiento 
        FROM 
            usuario
        WHERE id_usuario = "'.$doc_escaped.'" LIMIT 1 ;';
    } else {
        $sql = "SELECT ".$campo." FROM usuario WHERE id_usuario = '$doc_escaped' LIMIT 1";
    }

    $consulta = $mysqli->query($sql);
    if ($consulta && $consulta->num_rows > 0) {
        $row = $consulta->fetch_assoc();
        if(!empty($row[$campo])){
            return $row[$campo]; 
        } else {
            return ''; 
        }
    }
    return '';
}

$resultados = [];
$incompletos = [];
$error = '';
$db_errors = []; // Array para registrar errores SQL
$procesado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_bd'])) {
    $file = $_FILES['archivo_bd'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Habilitar detección automática de saltos de línea
        ini_set('auto_detect_line_endings', true);

        $handle = fopen($file['tmp_name'], 'r');
        if ($handle) {
            // Detección del delimitador
            $headers = fgetcsv($handle, 10000, ',');
            if (count($headers) === 1 && strpos($headers[0], ';') !== false) {
                rewind($handle);
                $headers = fgetcsv($handle, 10000, ';');
                $delimitador = ';';
            } else {
                $delimitador = ',';
            }

            // Función auxiliar para buscar múltiples variantes de un nombre de columna
            $find_header = function($variations, $headers) {
                foreach($variations as $v) {
                    $idx = array_search($v, $headers);
                    if ($idx !== false) return $idx;
                }
                return false;
            };

            // Normalizar cabeceras
            $headers_norm = array_map(function($h) { 
                $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);
                return strtoupper(trim($h)); 
            }, $headers);

            // Mapeo dinámico de índices
            $idx_sede = $find_header(['NOMBRE_SEDE', 'SEDE'], $headers_norm);
            $idx_doc = $find_header(['DOCUMENTO_ALUMNO', 'NUMERO_DOCUMENTO', 'DOCUMENTO'], $headers_norm);
            $idx_tipo_doc = $find_header(['TIPO_DOCUMENTO', 'TIPO_DOC'], $headers_norm);
            $idx_apellidos = $find_header(['APELLIDOS_ALUMNO', 'APELLIDOS'], $headers_norm);
            $idx_nombres = $find_header(['NOMBRES_ALUMNO', 'NOMBRES'], $headers_norm);
            $idx_grado = $find_header(['GRADO', 'NIVEL_ESCOLARIDAD', 'GRUPO'], $headers_norm);
            $idx_edad = $find_header(['EDAD'], $headers_norm);
            $idx_etnia = $find_header(['ETNIA', 'PERTENENCIA_ETNICA'], $headers_norm);
            $idx_genero = $find_header(['GENERO', 'SEXO'], $headers_norm);

            $contador = 1;

            while (($data = fgetcsv($handle, 10000, $delimitador)) !== FALSE) {
                // Prevenir procesamiento de líneas vacías
                if (array_filter($data) === []) continue;

                // Filtro para Vallesol
                if ($idx_sede !== false && isset($data[$idx_sede]) && stripos($data[$idx_sede], 'VALLESOL') === false) {
                    continue; 
                }

                // Extracción inicial desde el archivo CSV
                $grado_val = ($idx_grado !== false && isset($data[$idx_grado])) ? trim($data[$idx_grado]) : '';
                $doc_val = ($idx_doc !== false && isset($data[$idx_doc])) ? trim($data[$idx_doc]) : '';
                $tipo_doc_val = ($idx_tipo_doc !== false && isset($data[$idx_tipo_doc])) ? trim($data[$idx_tipo_doc]) : '';
                $apellidos_val = ($idx_apellidos !== false && isset($data[$idx_apellidos])) ? trim($data[$idx_apellidos]) : '';
                $nombres_val = ($idx_nombres !== false && isset($data[$idx_nombres])) ? trim($data[$idx_nombres]) : '';
                $edad_val = ($idx_edad !== false && isset($data[$idx_edad])) ? trim($data[$idx_edad]) : '';
                $etnia_val = ($idx_etnia !== false && isset($data[$idx_etnia])) ? trim($data[$idx_etnia]) : '';
                $genero_val = ($idx_genero !== false && isset($data[$idx_genero])) ? trim($data[$idx_genero]) : '';

                // ====================================================================
                // CONSULTAS DE BASE DE DATOS (Utilizando función obtener_dato)
                // ====================================================================
                if ($doc_val !== '') {
                    // Reemplaza los valores vacíos consultando a la Base de Datos
                    if ($tipo_doc_val === '') $tipo_doc_val = obtener_dato($doc_val, 'tipo_documento');
                    if ($edad_val === '')     $edad_val     = obtener_dato($doc_val, 'fecha_nacimiento');
                    if ($etnia_val === '')    $etnia_val    = obtener_dato($doc_val, 'etnia');
                    if ($genero_val === '')   $genero_val   = obtener_dato($doc_val, 'genero');
                }
                
                // Asignar valor por defecto "Ninguna" a la etnia si continúa vacía
                if (trim((string)$etnia_val) === '') {
                    $etnia_val = 'Ninguna';
                }
                // ====================================================================

                // Construcción de la fila conservando la estructura del formato
                $fila = [
                    'N°' => $contador++,
                    'NIVEL ESCOLARIDAD' => $grado_val,
                    'NÚMERO DE DOCUMENTO DEL BENEFICIARIO' => $doc_val,
                    'TIPO DE DOCUMENTO' => $tipo_doc_val,
                    'APELLIDOS DEL BENEFICIARIO' => $apellidos_val,
                    'NOMBRES DEL BENEFICIARIO' => $nombres_val,
                    'EDAD' => $edad_val,
                    'PERTENENCIA ÉTNICA' => $etnia_val,
                    'GÉNERO' => $genero_val
                ];

                $resultados[] = $fila;

                // Validación estricta de campos incompletos eliminando espacios residuales
                $es_incompleto = false;
                $campos_evaluados = [
                    'NIVEL ESCOLARIDAD', 'NÚMERO DE DOCUMENTO DEL BENEFICIARIO', 
                    'TIPO DE DOCUMENTO', 'APELLIDOS DEL BENEFICIARIO', 'NOMBRES DEL BENEFICIARIO', 
                    'EDAD', 'PERTENENCIA ÉTNICA', 'GÉNERO'
                ];
                
                foreach ($campos_evaluados as $campo) {
                    if (trim((string)$fila[$campo]) === '') {
                        $es_incompleto = true;
                        break;
                    }
                }

                if ($es_incompleto) {
                    $incompletos[] = $fila;
                }
            }
            fclose($handle);
            $procesado = true;
        } else {
            $error = 'No se pudo abrir el archivo.';
        }
    } else {
        $error = 'Error en la subida del archivo. Código: ' . $file['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generador Formato PAE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Incluir SheetJS para procesamiento XLSX -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <style>
        .tab-active { border-bottom-color: transparent; background-color: white; color: #1d4ed8; font-weight: 700; }
        .tab-inactive { background-color: #f3f4f6; color: #4b5563; }
        .tab-inactive:hover { background-color: #e5e7eb; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen p-8 font-sans">

    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-8 border-t-4 border-blue-600">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 uppercase">Procesador SIMAT a Formato PAE</h1>
            <p class="text-gray-600 mb-6">Cargue el archivo CSV de la base de datos para generar el formato de entrega.</p>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($db_errors)): ?>
                <!-- Panel de Auditoría de Base de Datos -->
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-4 mb-6 rounded shadow-sm" role="alert">
                    <h2 class="font-bold text-lg mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Registro de Auditoría SQL
                    </h2>
                    <ul class="list-disc ml-5 text-sm space-y-1 font-mono bg-yellow-50 p-3 rounded border border-yellow-200">
                        <?php 
                        $errores_unicos = array_slice(array_unique($db_errors), 0, 10);
                        foreach($errores_unicos as $err): 
                        ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="upload-form" method="POST" enctype="multipart/form-data" class="flex flex-col items-center justify-center gap-4 bg-gray-50 p-8 rounded border-2 border-dashed border-gray-300 relative transition-colors" ondragover="event.preventDefault(); this.classList.add('border-blue-500', 'bg-blue-50')" ondragleave="this.classList.remove('border-blue-500', 'bg-blue-50')" ondrop="event.preventDefault(); this.classList.remove('border-blue-500', 'bg-blue-50'); handleDrop(event)">
                <div class="text-center z-0 pointer-events-none">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="font-medium text-blue-600 hover:text-blue-500">Sube un archivo</span> o arrástralo y suéltalo aquí
                    </p>
                    <p class="text-xs text-gray-500">Soporta .CSV y .XLSX</p>
                </div>
                <input type="file" id="file-input" name="archivo_bd" accept=".csv, .xlsx" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="handleFileSelect(this.files[0])">
                
                <div id="loading-overlay" class="hidden absolute inset-0 bg-white/90 flex flex-col items-center justify-center z-20 rounded">
                    <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-600 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-blue-600 font-bold">Procesando archivo...</span>
                </div>
            </form>
        </div>

        <?php if ($procesado): ?>
            
            <!-- Contenedor de Pestañas -->
            <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="tabs-container">
                    <li class="mr-2">
                        <button onclick="switchTab('todos')" id="btn-tab-todos" class="inline-block p-4 rounded-t-lg border border-gray-200 tab-active transition-colors">
                            Todos los Estudiantes (<?= count($resultados) ?>)
                        </button>
                    </li>
                    <li class="mr-2">
                        <button onclick="switchTab('incompletos')" id="btn-tab-incompletos" class="inline-block p-4 rounded-t-lg border border-gray-200 tab-inactive transition-colors">
                            Datos Incompletos 
                            <?php if(count($incompletos) > 0): ?>
                                <span class="bg-red-100 text-red-800 text-xs font-semibold ml-2 px-2.5 py-0.5 rounded"><?= count($incompletos) ?></span>
                            <?php else: ?>
                                <span class="bg-green-100 text-green-800 text-xs font-semibold ml-2 px-2.5 py-0.5 rounded">0</span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <!--li class="mr-2">
                        <button onclick="switchTab('duplicada')" id="btn-tab-duplicada" class="inline-block p-4 rounded-t-lg border border-gray-200 tab-inactive transition-colors">
                            Tabla Duplicada
                        </button>
                    </li-->
                </ul>
            </div>

            <!-- Contenido Pestaña 1: Todos -->
            <div id="tab-todos" class="bg-white rounded-lg shadow-lg p-6 tab-content block">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Listado General PAE</h2>
                    <button onclick="exportarCSV('tablaTodos', 'Formato_Entrega_PAE_Vallesol.csv')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Exportar CSV General
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-gray-200 rounded">
                    <table id="tablaTodos" class="min-w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-4 py-3">N°</th>
                                <th scope="col" class="px-4 py-3">NIVEL ESCOLARIDAD</th>
                                <th scope="col" class="px-4 py-3">NÚMERO DE DOCUMENTO DEL BENEFICIARIO</th>
                                <th scope="col" class="px-4 py-3">TIPO DE DOCUMENTO</th>
                                <th scope="col" class="px-4 py-3">APELLIDOS DEL BENEFICIARIO</th>
                                <th scope="col" class="px-4 py-3">NOMBRES DEL BENEFICIARIO</th>
                                <th scope="col" class="px-4 py-3">EDAD</th>
                                <th scope="col" class="px-4 py-3">PERTENENCIA ÉTNICA</th>
                                <th scope="col" class="px-4 py-3">GÉNERO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $fila): ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900"><?= htmlspecialchars($fila['N°']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['NIVEL ESCOLARIDAD']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['NÚMERO DE DOCUMENTO DEL BENEFICIARIO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['TIPO DE DOCUMENTO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['APELLIDOS DEL BENEFICIARIO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['NOMBRES DEL BENEFICIARIO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['EDAD']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['PERTENENCIA ÉTNICA']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['GÉNERO']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contenido Pestaña 2: Incompletos -->
            <div id="tab-incompletos" class="bg-white rounded-lg shadow-lg p-6 tab-content hidden border-t-4 border-red-500">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-red-700">Registros con Datos Faltantes</h2>
                        <p class="text-sm text-gray-600">Estudiantes que requieren actualización en SIMAT o en la Base de Datos.</p>
                    </div>
                    <?php if(count($incompletos) > 0): ?>
                        <button onclick="exportarCSV('tablaIncompletos', 'Estudiantes_Incompletos_PAE.csv')" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Exportar CSV Incompletos
                        </button>
                    <?php endif; ?>
                </div>

                <?php if(count($incompletos) === 0): ?>
                    <div class="p-8 text-center text-green-600 font-bold bg-green-50 rounded border border-green-200">
                        Todos los estudiantes tienen la información obligatoria completa.
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto border border-red-200 rounded">
                        <table id="tablaIncompletos" class="min-w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-red-800 uppercase bg-red-50 border-b border-red-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3">N°</th>
                                    <th scope="col" class="px-4 py-3">NIVEL ESCOLARIDAD</th>
                                    <th scope="col" class="px-4 py-3">NÚMERO DE DOCUMENTO DEL BENEFICIARIO</th>
                                    <th scope="col" class="px-4 py-3">TIPO DE DOCUMENTO</th>
                                    <th scope="col" class="px-4 py-3">APELLIDOS DEL BENEFICIARIO</th>
                                    <th scope="col" class="px-4 py-3">NOMBRES DEL BENEFICIARIO</th>
                                    <th scope="col" class="px-4 py-3">EDAD</th>
                                    <th scope="col" class="px-4 py-3">PERTENENCIA ÉTNICA</th>
                                    <th scope="col" class="px-4 py-3">GÉNERO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incompletos as $fila): ?>
                                    <tr class="bg-white border-b hover:bg-red-50">
                                        <td class="px-4 py-2 font-medium text-gray-900"><?= htmlspecialchars($fila['N°']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['NIVEL ESCOLARIDAD']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['NIVEL ESCOLARIDAD']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['NÚMERO DE DOCUMENTO DEL BENEFICIARIO']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['NÚMERO DE DOCUMENTO DEL BENEFICIARIO']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['TIPO DE DOCUMENTO']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['TIPO DE DOCUMENTO']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['APELLIDOS DEL BENEFICIARIO']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['APELLIDOS DEL BENEFICIARIO']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['NOMBRES DEL BENEFICIARIO']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['NOMBRES DEL BENEFICIARIO']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['EDAD']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['EDAD']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['PERTENENCIA ÉTNICA']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['PERTENENCIA ÉTNICA']) ?></td>
                                        <td class="px-4 py-2 <?= trim((string)$fila['GÉNERO']) === '' ? 'bg-red-200' : '' ?>"><?= htmlspecialchars($fila['GÉNERO']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Contenido Pestaña 3: Duplicada -->
            <div id="tab-duplicada" class="bg-white rounded-lg shadow-lg p-6 tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Listado General PAE (Copia)</h2>
                    <button onclick="exportarCSV('tablaDuplicada', 'Formato_Entrega_PAE_Vallesol_Copia.csv')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Exportar CSV Copia
                    </button>
                </div>
                
                <div class="overflow-x-auto border border-gray-200 rounded">
                    <table id="tablaDuplicada" class="min-w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th scope="col" class="px-4 py-3">N°</th>
                                <th scope="col" class="px-4 py-3">NIVEL ESCOLARIDAD</th>
                                <th scope="col" class="px-4 py-3">NÚMERO DE DOCUMENTO DEL BENEFICIARIO</th>
                                <th scope="col" class="px-4 py-3">TIPO DE DOCUMENTO</th>
                                <th scope="col" class="px-4 py-3">APELLIDOS DEL BENEFICIARIO</th>
                                <th scope="col" class="px-4 py-3">NOMBRES DEL BENEFICIARIO</th>
                                <th scope="col" class="px-4 py-3">EDAD</th>
                                <th scope="col" class="px-4 py-3">PERTENENCIA ÉTNICA</th>
                                <th scope="col" class="px-4 py-3">GÉNERO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $fila): ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 font-medium text-gray-900"><?= htmlspecialchars($fila['N°']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['NIVEL ESCOLARIDAD']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['NÚMERO DE DOCUMENTO DEL BENEFICIARIO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['TIPO DE DOCUMENTO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['APELLIDOS DEL BENEFICIARIO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['NOMBRES DEL BENEFICIARIO']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['EDAD']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['PERTENENCIA ÉTNICA']) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($fila['GÉNERO']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Lógica de Pestañas y Exportación -->
            <script>
                // Función para alternar visualización de pestañas
                function switchTab(tabId) {
                    document.querySelectorAll('.tab-content').forEach(function(content) {
                        content.classList.add('hidden');
                        content.classList.remove('block');
                    });
                    
                    document.querySelectorAll('[id^="btn-tab-"]').forEach(function(btn) {
                        btn.classList.remove('tab-active', 'border-b-0');
                        btn.classList.add('tab-inactive');
                    });
                    
                    document.getElementById('tab-' + tabId).classList.remove('hidden');
                    document.getElementById('tab-' + tabId).classList.add('block');
                    
                    const activeBtn = document.getElementById('btn-tab-' + tabId);
                    activeBtn.classList.remove('tab-inactive');
                    activeBtn.classList.add('tab-active', 'border-b-0');
                }

                // Función parametrizada para exportar la tabla a CSV
                function exportarCSV(tablaId, nombreArchivo) {
                    const table = document.getElementById(tablaId);
                    if(!table) return;

                    const rows = table.querySelectorAll("tr");
                    let csvContent = "data:text/csv;charset=utf-8,\uFEFF"; 
                    
                    for (let i = 0; i < rows.length; i++) {
                        let row = [], cols = rows[i].querySelectorAll("td, th");
                        if(cols.length === 0) continue;

                        for (let j = 0; j < cols.length; j++) {
                            let data = cols[j].innerText.replace(/"/g, '""');
                            row.push('"' + data + '"');
                        }
                        csvContent += row.join(",") + "\n";
                    }

                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", nombreArchivo);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            </script>
        <?php endif; ?>
    </div>

    <script>
        function handleDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file) {
                // Actualizar el input con el archivo dropeado
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('file-input').files = dataTransfer.files;
                
                handleFileSelect(file);
            }
        }

        function handleFileSelect(file) {
            if (!file) return;
            
            document.getElementById('loading-overlay').classList.remove('hidden');

            if (file.name.toLowerCase().endsWith('.xlsx')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, {type: 'array'});
                        
                        // Buscar hoja que empiece con 'rpt' (case insensitive)
                        let targetSheetName = null;
                        for (let i = 0; i < workbook.SheetNames.length; i++) {
                            if (workbook.SheetNames[i].toLowerCase().startsWith('rpt')) {
                                targetSheetName = workbook.SheetNames[i];
                                break;
                            }
                        }
                        
                        if (targetSheetName) {
                            const worksheet = workbook.Sheets[targetSheetName];
                            // Convertir a CSV (forzamos ; como delimitador que el backend soporta)
                            const csvData = XLSX.utils.sheet_to_csv(worksheet, { FS: ";" }); 
                            
                            // Crear un blob y asignarlo al input
                            // Para asegurar que los acentos funcionen agregamos BOM
                            const blob = new Blob(["\uFEFF" + csvData], { type: 'text/csv;charset=utf-8;' });
                            const newFile = new File([blob], file.name.replace(/\.xlsx$/i, '.csv'), { type: 'text/csv' });
                            
                            submitFormWithFile(newFile);
                        } else {
                            alert('No se encontró ninguna hoja que comience con "rpt" en el archivo XLSX.');
                            document.getElementById('loading-overlay').classList.add('hidden');
                            document.getElementById('upload-form').reset();
                        }
                    } catch (error) {
                        alert('Error al procesar el archivo XLSX: ' + error.message);
                        document.getElementById('loading-overlay').classList.add('hidden');
                        document.getElementById('upload-form').reset();
                    }
                };
                reader.onerror = function() {
                    alert('Error al leer el archivo.');
                    document.getElementById('loading-overlay').classList.add('hidden');
                    document.getElementById('upload-form').reset();
                };
                reader.readAsArrayBuffer(file);
            } else if (file.name.toLowerCase().endsWith('.csv')) {
                submitFormWithFile(file);
            } else {
                alert('Por favor, suba un archivo .csv o .xlsx');
                document.getElementById('loading-overlay').classList.add('hidden');
                document.getElementById('upload-form').reset();
            }
        }

        function submitFormWithFile(file) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('file-input').files = dataTransfer.files;
            document.getElementById('upload-form').submit();
        }
    </script>
</body>
</html>