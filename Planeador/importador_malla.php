<?php
// =================================================================
// 1. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// =================================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asumiendo que 'conexion.php' está en un directorio superior y seguro
require_once __DIR__ . "/../comun/conexion.php"; // Ajusta la ruta a tu archivo de conexión

// Array para almacenar los mensajes de la operación
$mensajes = ['exito' => [], 'error' => []];

// =================================================================
// 2. DEFINICIÓN DE FUNCIONES
// =================================================================

/**
 * Convierte una cadena de texto a codificación UTF-8 si es necesario.
 * @param string $string Cadena de texto a limpiar.
 * @return string Cadena de texto en UTF-8.
 */
function cleanString($string) {
    $string = trim($string);
    // Limpiar prefijos comunes como "?", viñetas, etc.
    $string = preg_replace('/^[\?\t\s\-\*]*/', '', $string);
    if (!mb_check_encoding($string, 'UTF-8')) {
        return mb_convert_encoding($string, 'UTF-8', 'auto');
    }
    return $string;
}

/**
 * Función genérica para obtener el ID de un registro si existe, o crearlo si no.
 * Evita la duplicación de datos.
 * @param mysqli $mysqli Conexión a la BD.
 * @param string $table Nombre de la tabla.
 * @param string $pkColumn Nombre de la columna de la clave primaria.
 * @param array $conditions Array asociativo de columnas y valores para la cláusula WHERE.
 * @param array $insertData Array asociativo de columnas y valores para el INSERT.
 * @return int|null El ID del registro existente o recién creado.
 */
function getOrCreateRecord($mysqli, $table, $pkColumn, $conditions, $insertData) {
    // Construir la consulta SELECT usando el nombre de la clave primaria proporcionado
    $sql_select = "SELECT {$pkColumn} FROM {$table} WHERE ";
    $where_clauses = [];
    $types = '';
    $params = [];
    foreach ($conditions as $column => $value) {
        $where_clauses[] = "{$column} = ?";
        $types .= is_int($value) ? 'i' : 's';
        $params[] = $value;
    }
    $sql_select .= implode(' AND ', $where_clauses);
    
    $stmt_select = $mysqli->prepare($sql_select);
    if (!$stmt_select) {
        throw new Exception("Error al preparar SELECT para la tabla {$table}: " . $mysqli->error);
    }
    $stmt_select->bind_param($types, ...$params);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt_select->close();
        return $row[$pkColumn]; // Devolver el valor de la clave primaria dinámica
    }
    $stmt_select->close();

    // El registro no existe, lo creamos
    $columns = implode(', ', array_keys($insertData));
    $placeholders = implode(', ', array_fill(0, count($insertData), '?'));
    $sql_insert = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    
    $stmt_insert = $mysqli->prepare($sql_insert);
    if (!$stmt_insert) {
         throw new Exception("Error al preparar INSERT para la tabla {$table}: " . $mysqli->error);
    }

    $insert_types = '';
    $insert_params = [];
    foreach ($insertData as $value) {
        $insert_types .= is_int($value) ? 'i' : 's';
        $insert_params[] = $value;
    }
    $stmt_insert->bind_param($insert_types, ...$insert_params);
    
    if ($stmt_insert->execute()) {
        $new_id = $mysqli->insert_id;
        $stmt_insert->close();
        return $new_id;
    } else {
        $stmt_insert->close();
        throw new Exception("Error al insertar en la tabla {$table}: " . $stmt_insert->error);
    }
    return null;
}


/**
 * Procesa un archivo CSV, normaliza los datos y los inserta en tablas relacionadas.
 * @param string $filePath Ruta del archivo CSV.
 * @param mysqli $mysqli Objeto de conexión a la BD.
 * @param string $delimitador El caracter delimitador (',' o ';').
 * @return array Mensajes de la operación.
 */
function importDataFromCSV($filePath, $mysqli, $delimitador = ',') {
    $mensajes_importacion = ['exito' => [], 'error' => []];
    $counters = ['estandares' => 0, 'dbas' => 0, 'evidencias' => 0, 'ejes' => 0];
    $fila = 1;

    if (($handle = fopen($filePath, "r")) !== FALSE) {
        // Omitir la fila de encabezado usando el delimitador seleccionado.
        fgetcsv($handle, 2000, $delimitador);

        while (($data = fgetcsv($handle, 2000, $delimitador)) !== FALSE) {
            $fila++;
            if (count($data) != 7) {
                $mensajes_importacion['error'][] = "La fila {$fila} fue omitida (se esperaban 7 columnas, pero se detectaron " . count($data) . ").";
                continue;
            }

            $mysqli->begin_transaction();
            try {
                // --- 1. Procesar Estándar ---
                $estandar_nombre = cleanString($data[0]);
                $grado = (int)cleanString($data[1]);
                $periodo = (int)cleanString($data[2]);
                $id_materia = (int)cleanString($data[3]);
                
                if (empty($estandar_nombre)) continue;

                $estandar_id = getOrCreateRecord(
                    $mysqli,
                    'estandar',
                    'id_estandar',
                    ['nombre_estandar' => $estandar_nombre, 'grado' => $grado, 'id_periodo' => $periodo, 'id_materia_oficial' => $id_materia],
                    ['nombre_estandar' => $estandar_nombre, 'grado' => $grado, 'id_periodo' => $periodo, 'id_materia_oficial' => $id_materia]
                );
                $counters['estandares']++;


                // --- 2. Procesar DBAs ---
                $dbas = preg_split('/[\n\r]+/', $data[4]);
                foreach ($dbas as $dba_nombre) {
                    $dba_nombre = cleanString($dba_nombre);
                    if (empty($dba_nombre)) continue;
                     
                    $dba_id = getOrCreateRecord(
                        $mysqli,
                        'dba',
                        'id_dba',
                        ['nombre_dba' => $dba_nombre, 'id_estandar' => $estandar_id],
                        ['nombre_dba' => $dba_nombre, 'id_estandar' => $estandar_id]
                    );
                    $counters['dbas']++;

                    // --- 3. Procesar Evidencias de Aprendizaje ---
                    $evidencias = preg_split('/[\n\r]+/', $data[5]);
                    foreach ($evidencias as $evidencia_nombre) {
                        $evidencia_nombre = cleanString($evidencia_nombre);
                        if(empty($evidencia_nombre)) continue;

                        getOrCreateRecord(
                           $mysqli,
                           'evidencia_de_aprendizaje',
                           'id_evidencia_aprendizaje',
                           ['descripcion_evidencia' => $evidencia_nombre, 'id_dba' => $dba_id],
                           ['descripcion_evidencia' => $evidencia_nombre, 'id_dba' => $dba_id]
                        );
                        $counters['evidencias']++;
                    }

                    // --- 4. Procesar Ejes Temáticos ---
                    $ejes = preg_split('/[\n\r]+/', $data[6]);
                    foreach ($ejes as $eje_nombre) {
                        $eje_nombre = cleanString($eje_nombre);
                        if(empty($eje_nombre)) continue;

                        getOrCreateRecord(
                           $mysqli,
                           'eje_tematico',
                           'id_eje_tematico',
                           ['nombre_eje_tematico' => $eje_nombre, 'id_dba' => $dba_id],
                           ['nombre_eje_tematico' => $eje_nombre, 'id_dba' => $dba_id]
                        );
                        $counters['ejes']++;
                    }
                }
                
                $mysqli->commit();
            } catch (Exception $e) {
                $mysqli->rollback();
                $mensajes_importacion['error'][] = "Error en la fila {$fila}: " . $e->getMessage() . ". Se revirtieron los cambios de esta fila.";
            }
        }
        fclose($handle);

        $mensajes_importacion['exito'][] = "¡Importación finalizada! Resumen:";
        $mensajes_importacion['exito'][] = "Filas del CSV procesadas: " . ($fila - 1);
        $mensajes_importacion['exito'][] = "Registros creados/verificados: Estándares ({$counters['estandares']}), DBAs ({$counters['dbas']}), Evidencias ({$counters['evidencias']}), Ejes Temáticos ({$counters['ejes']}).";

    } else {
        $mensajes_importacion['error'][] = "No se pudo abrir el archivo CSV.";
    }

    return $mensajes_importacion;
}


// =================================================================
// 3. LÓGICA DE PROCESAMIENTO DEL FORMULARIO
// =================================================================
if (isset($_POST["importar"])) {
    // Capturar el delimitador seleccionado por el usuario.
    $delimitador = isset($_POST['delimitador']) && in_array($_POST['delimitador'], [',', ';']) ? $_POST['delimitador'] : ',';

    if (isset($_FILES["csv_file"]) && $_FILES["csv_file"]["error"] == 0) {
        $fileName = $_FILES["csv_file"]["name"];
        $fileTmpPath = $_FILES["csv_file"]["tmp_name"];
        $fileType = $_FILES["csv_file"]["type"];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedExtensions = ['csv'];
        $allowedMimeTypes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'];

        if (in_array($fileExtension, $allowedExtensions) && in_array($fileType, $allowedMimeTypes)) {
            // Pasar el delimitador a la función de importación.
            $mensajes = importDataFromCSV($fileTmpPath, $mysqli, $delimitador);
        } else {
            $mensajes['error'][] = "Error: El formato del archivo no es válido. Sube un archivo .csv.";
        }
    } else {
        $mensajes['error'][] = "Error al subir el archivo. Código: " . $_FILES["csv_file"]["error"];
    }
}

$mysqli->close();
?>

<!-- ================================================================= -->
<!-- 4. VISTA HTML PARA EL FORMULARIO Y MENSAJES -->
<!-- ================================================================= -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importador de Malla Curricular (Relacional)</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-lg mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Importar Malla Curricular</h1>

        <?php if (!empty($mensajes['exito']) || !empty($mensajes['error'])): ?>
            <div class="mb-6">
                <?php foreach ($mensajes['exito'] as $msg): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-2 rounded-md" role="alert">
                        <p><?php echo htmlspecialchars($msg); ?></p>
                    </div>
                <?php endforeach; ?>
                <?php foreach ($mensajes['error'] as $msg): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2 rounded-md" role="alert">
                        <p class="font-bold">Error</p>
                        <p><?php echo htmlspecialchars($msg); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">1. Selecciona el archivo CSV</label>
                <input type="file" name="csv_file" id="csv_file" required accept=".csv" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">2. Elige el delimitador del archivo</label>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input id="delimitador_coma" name="delimitador" type="radio" value="," checked class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="delimitador_coma" class="ml-2 block text-sm text-gray-900">Coma (,)</label>
                    </div>
                    <div class="flex items-center">
                        <input id="delimitador_puntoycoma" name="delimitador" type="radio" value=";" class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                        <label for="delimitador_puntoycoma" class="ml-2 block text-sm text-gray-900">Punto y coma (;)</label>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" name="importar" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300">
                    <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Importar Archivo
                </button>
            </div>
        </form>
         <p class="mt-4 text-xs text-gray-500 text-center">
            <strong>Recordatorio:</strong> El archivo debe tener 7 columnas y estar guardado con codificación UTF-8.
        </p>
    </div>
</body>
</html>

