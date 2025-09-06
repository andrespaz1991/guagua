<?php
// =================================================================
// 1. CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// =================================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asumiendo que 'conexion.php' está en un directorio superior y seguro
require_once __DIR__ . "/../comun/conexion.php"; // Ajusta la ruta a tu archivo de conexión

// Array para almacenar los mensajes de la operación
$mensajes = [];

// =================================================================
// 2. DEFINICIÓN DE FUNCIONES
// =================================================================

/**
 * Convierte una cadena de texto a codificación UTF-8 para evitar problemas con caracteres especiales.
 * @param string $string Cadena de texto a limpiar.
 * @return string Cadena de texto en UTF-8.
 */
function cleanString($string) {
    return mb_convert_encoding($string, 'UTF-8', 'auto');
}

/**
 * Procesa un archivo CSV subido e inserta los datos en la base de datos de forma segura.
 * @param string $filePath Ruta temporal del archivo CSV subido.
 * @param mysqli $mysqli Objeto de conexión a la base de datos.
 * @return array Un array con mensajes de éxito y error.
 */
function importDataFromCSV($filePath, $mysqli) {
    $mensajes_importacion = ['exito' => [], 'error' => []];

    // Abrir el archivo CSV para lectura
    if (($handle = fopen($filePath, "r")) === FALSE) {
        $mensajes_importacion['error'][] = "Error: No se pudo abrir el archivo CSV subido.";
        return $mensajes_importacion;
    }

    // Omitir la primera fila (encabezados)
    fgetcsv($handle, 1000, ";");

    // Preparar las sentencias SQL para evitar inyección SQL
    $stmt_estandar = $mysqli->prepare("INSERT INTO `estandar` (`nombre_estandar`, `descripcion_estandar`, `grado`, `id_periodo`, `id_materia_oficial`) VALUES (?, ?, ?, ?, ?)");
    $stmt_dba = $mysqli->prepare("INSERT INTO `dba` (nombre_dba, id_estandar) VALUES (?, ?)");
    $stmt_evidencia = $mysqli->prepare("INSERT INTO `evidencia_de_aprendizaje` (descripcion_evidencia, id_dba) VALUES (?, ?)");
    $stmt_eje = $mysqli->prepare("INSERT INTO `eje_tematico` (nombre_eje_tematico, descripcion_eje_tematico, id_dba) VALUES (?, ?, ?)");

    $fila = 1; // Contador para la fila actual del CSV

    // Leer el archivo línea por línea
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $fila++;
        // Asignar los datos del CSV a variables, limpiando los strings
        $estandar = cleanString($data[0]);
        $grado = $data[1];
        $periodo = $data[2];
        $id_materia_oficial = $data[3];
        $nombre_dba = cleanString($data[4]);
        $evidencia_aprendizaje = cleanString($data[5]);
        $eje_tematico = cleanString($data[6]);

        // Iniciar transacción para asegurar la integridad de los datos
        $mysqli->begin_transaction();

        try {
            // 1. Insertar en 'estandar'
            $stmt_estandar->bind_param("ssisi", $estandar, $estandar, $grado, $periodo, $id_materia_oficial);
            if (!$stmt_estandar->execute()) throw new Exception("Error en 'estandar': " . $stmt_estandar->error);
            $id_estandar = $mysqli->insert_id;

            // 2. Insertar en 'dba'
            $stmt_dba->bind_param("si", $nombre_dba, $id_estandar);
            if (!$stmt_dba->execute()) throw new Exception("Error en 'dba': " . $stmt_dba->error);
            $id_dba = $mysqli->insert_id;

            // 3. Insertar en 'evidencia_de_aprendizaje'
            $stmt_evidencia->bind_param("si", $evidencia_aprendizaje, $id_dba);
            if (!$stmt_evidencia->execute()) throw new Exception("Error en 'evidencia': " . $stmt_evidencia->error);

            // 4. Insertar en 'eje_tematico'
            $stmt_eje->bind_param("ssi", $eje_tematico, $eje_tematico, $id_dba);
            if (!$stmt_eje->execute()) throw new Exception("Error en 'eje temático': " . $stmt_eje->error);

            // Si todo fue bien, confirmar la transacción
            $mysqli->commit();
            $mensajes_importacion['exito'][] = "Fila $fila: '$estandar' importada correctamente.";

        } catch (Exception $e) {
            // Si algo falla, revertir la transacción
            $mysqli->rollback();
            $mensajes_importacion['error'][] = "Fila $fila: " . $e->getMessage();
        }
    }

    // Cerrar las sentencias preparadas y el archivo
    $stmt_estandar->close();
    $stmt_dba->close();
    $stmt_evidencia->close();
    $stmt_eje->close();
    fclose($handle);

    return $mensajes_importacion;
}

// =================================================================
// 3. LÓGICA DE PROCESAMIENTO DEL FORMULARIO
// =================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"])) {
    // Validar subida del archivo
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $filePath = $_FILES['csv_file']['tmp_name'];
        
        // Verificar que el tipo de archivo sea CSV
        $mime_type = mime_content_type($filePath);
        if ($mime_type == 'text/csv' || $mime_type == 'application/vnd.ms-excel') {
            
            // Procesar el archivo
            $mensajes = importDataFromCSV($filePath, $mysqli);

        } else {
            $mensajes['error'][] = "Error: El archivo subido no es un CSV válido. Tipo detectado: $mime_type";
        }
    } else {
        $mensajes['error'][] = "Error al subir el archivo. Código de error: " . $_FILES['csv_file']['error'];
    }
}

// Cerrar la conexión principal a la base de datos
$mysqli->close();
?>

<!-- 
// =================================================================
// 4. PRESENTACIÓN (HTML)
// =================================================================
-->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Datos desde CSV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="container mx-auto mt-10 p-8 bg-white rounded-lg shadow-md max-w-2xl">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-700">Importar Estándares desde Archivo CSV</h1>
        
        <!-- Bloque para mostrar mensajes de resultado -->
        <?php if (!empty($mensajes)): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo !empty($mensajes['error']) ? 'bg-red-100 border border-red-400' : 'bg-green-100 border border-green-400'; ?>">
                <h3 class="font-bold <?php echo !empty($mensajes['error']) ? 'text-red-800' : 'text-green-800'; ?>">
                    <?php echo !empty($mensajes['error']) ? 'Se encontraron errores durante la importación:' : 'Proceso de importación finalizado:'; ?>
                </h3>
                <div class="max-h-60 overflow-y-auto mt-2 text-sm">
                    <?php if (!empty($mensajes['exito'])): ?>
                        <ul class="list-disc list-inside text-green-700">
                            <?php foreach ($mensajes['exito'] as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                     <?php if (!empty($mensajes['error'])): ?>
                        <ul class="list-disc list-inside text-red-700">
                            <?php foreach ($mensajes['error'] as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Formulario de subida -->
        <form action="importar_csv.php" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Selecciona el archivo CSV (Dentro de la carpeta planeador en guagua hay una plantilla csv)</label>
                <input type="file" name="csv_file" id="csv_file" required accept=".csv" class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                <p class="mt-2 text-xs text-gray-500">
                    <strong>Formato esperado:</strong> El archivo debe usar punto y coma (;) como delimitador y tener 7 columnas en este orden:
                    <br>1. Estandar, 2. Grado, 3. Periodo, 4. ID Materia, 5. Nombre DBA, 6. Evidencia Aprendizaje, 7. Eje Temático.
                </p>
            </div>
            <div>
                <button type="submit" name="importar" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-300">
                    <svg class="inline w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Subir e Importar Datos
                </button>
            </div>
        </form>
    </div>

</body>
</html>
