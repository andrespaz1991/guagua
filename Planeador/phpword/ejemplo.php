<?php
/**
 * GENERADOR DE PLANEADOR - VERSIÓN PERSONALIZADA
 * Autor: Asistente (Para Andres Paz)
 * * Cambios recientes:
 * 1. Nombre de archivo dinámico: Materia + Grado.
 * 2. Sanitización de nombres de archivo.
 * 3. Integración de lógica de carpetas por grado (6-8, 9-11).
 */

// 1. CONTROL DE BUFFER (CRÍTICO)
ob_start();

// --- CONFIGURACIÓN DE SALIDA ---
if(isset($_GET['descargar'])){
$modo_salida = 'download'; // 'download' o 'save'

}else{
$modo_salida = 'save'; // 'download' o 'save'
}

if(isset($_GET['asignacion'])){
$asignacion=$_GET['asignacion'];
}else{
    $asignacion=1;
}

// -------------------------------

try {
    // Inclusión de librerías
    include_once('tbs_class.php'); 
    include_once('plugins/tbs_plugin_opentbs.php'); 
    
    // Validaciones de archivos requeridos
    if (!file_exists('../conexion.php')) throw new Exception("No se encuentra conexion.php");
    require '../conexion.php';
    
    if (!file_exists('../../Clases/Fecha.Class.php')) throw new Exception("No se encuentra Fecha.Class.php");
    require '../../Clases/Fecha.Class.php';
    
    require '../../Clases/Comun.Class.php';

    // Instancia TBS
    $TBS = new clsTinyButStrong; 
    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); 

    // Parámetros
    $id_plan = isset($_GET['id']) ? (int)$_GET['id'] : 217;
    
    // Consulta SQL
    

    $sql_vallesol = "SELECT p.*,materia_oficial.* FROM planeador_vallesol p INNER join asignacion on asignacion.id_asignacion=p.materia INNER join materia_oficial on materia_oficial.id_materia=asignacion.id_asignatura WHERE p.id_plan = '$id_plan' LIMIT 1;";

    $consulta_vallesol = $mysqli->query($sql_vallesol);

    if (!$consulta_vallesol || $consulta_vallesol->num_rows === 0) {
        throw new Exception("Error o sin registros para ID: $id_plan");
    }

    $row = $consulta_vallesol->fetch_assoc();

    // --- PROCESAMIENTO DE DATOS ---
    $f_inicio = isset($row['fecha_inicio']) ? Fecha::formato_fecha($row['fecha_inicio']) : 'S/F';
    $f_fin = isset($row['fecha_fin']) ? Fecha::formato_fecha($row['fecha_fin']) : 'S/F';
    $fecha_inicio = $f_inicio . ' al ' . $f_fin;
    
    $materia_raw = $row['nombre_materia'];
    $materia = Comun::eliminar_sobrante($materia_raw);
    
    $profesor = 'Andres Paz';
    $objetivo = $row['objetivo'];
    $periodo = $row['periodo'];
    $tiempo_plan = $row['tiempo_plan'] . ' Horas';
    $mes = 'Febrero'; 
    $template = 'PLANEADOR2026.docx';

    // --- LÓGICA DE GRADOS Y RUTAS (Tu código personalizado) ---
    $grado = isset($row['grado']) ? $row['grado'] : '0';
    $grado_destino = 'General'; // Valor por defecto para evitar errores

    if($grado > 5 and $grado < 9){
        $grado_destino = '6-8';
    }
    if($grado > 8){
        $grado_destino = '9-11';
    }
    
    // Ruta destino basada en tu lógica
    $ruta_destino = 'G:/Mi unidad/Vallesol2026/Planeaciones_guagua/' . $grado_destino . '/' . $mes . '/'; 

    // Sanitización de campos de texto
    $dba = htmlspecialchars($row['dba'] ?? '', ENT_QUOTES, 'UTF-8');
    $estrategias = htmlspecialchars($row['estrategias'] ?? '', ENT_QUOTES, 'UTF-8');
    $observaciones = htmlspecialchars($row['observaciones'] ?? '', ENT_QUOTES, 'UTF-8');
    $reflexion = htmlspecialchars($row['reflexion'] ?? '', ENT_QUOTES, 'UTF-8');

    // --- CARGA DE PLANTILLA ---
    if (!file_exists($template)) {
        throw new Exception("La plantilla $template no existe.");
    }

    $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);

    // Fusionar campos
    $TBS->MergeField('pro.mes', trim($mes));
    $TBS->MergeField('pro.fechas', trim($fecha_inicio));
    $TBS->MergeField('pro.periodo', ($periodo));
    $TBS->MergeField('pro.profesor', trim($profesor));
    $TBS->MergeField('pro.asignatura', trim($materia));
    $TBS->MergeField('pro.dba', trim($dba));
    $TBS->MergeField('pro.objetivo', trim($objetivo));
    $TBS->MergeField('pro.tiempo', trim($tiempo_plan)); // Corregido nombre de campo según tu ejemplo
    $TBS->MergeField('pro.estrategia', trim($estrategias)); // Corregido nombre de campo según tu ejemplo
    $TBS->MergeField('pro.momentos', trim($observaciones)); // Corregido nombre de campo según tu ejemplo
    $TBS->MergeField('pro.evaluacion', trim($objetivo)); 
    $TBS->MergeField('pro.reflexion', trim($reflexion));

    $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

    // --- NOMBRE DE ARCHIVO PERSONALIZADO ---
    
    // Función anónima o lógica simple para limpiar nombre de archivo
    // Elimina tildes, caracteres raros y espacios para evitar errores de sistema de archivos
    $materia_limpia = preg_replace('/[^A-Za-z0-9]/', '_', remover_acentos($materia));
    $grado_limpio = preg_replace('/[^A-Za-z0-9]/', '', $grado);
    
    // Construcción del nombre: MATERIA_GRADO.docx
    $output_file_name = $grado_limpio.'_'.$materia_limpia.'.docx';

    // --- LIMPIEZA DE BUFFER (Obligatorio antes de cualquier salida) ---
    ob_end_clean(); 

    // --- LÓGICA DE SALIDA ---
    if ($modo_salida === 'save') {
        // Verificar y crear directorio recursivamente
        if (!is_dir($ruta_destino)) {
            // El @ silencia advertencias si la ruta ya existe o hay problemas de permisos menores
            if (!@mkdir($ruta_destino, 0777, true)) {
                 // Si falla, intentamos guardar en la carpeta local temporalmente
                 throw new Exception("No se pudo crear el directorio: $ruta_destino. Verifique permisos o unidad G:.");
            }
        }
        
        $ruta_completa = $ruta_destino . $output_file_name;
        
        $TBS->Show(OPENTBS_FILE, $ruta_completa);
        
        echo "<div style='color:green; font-family:Arial; padding:20px; border:1px solid green; background:#eeffee;'>";
        echo "✅ <strong>Archivo guardado:</strong> $output_file_name<br>";
        echo '<meta http-equiv="refresh" content="2; url=../index.php?asignacion=' .$asignacion. '" />';

        echo "<small>Ruta: $ruta_completa</small>";
        echo "</div>";
        
    } else {
        $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name); 
        exit();
    }

} catch (Exception $e) {
    ob_end_clean();
    echo "<div style='color:red; font-family:Arial; padding:20px; border:1px solid red; background:#ffeeee;'>";
    echo "<strong>Error:</strong> " . $e->getMessage();
    echo "</div>";
}

// Función auxiliar para limpieza de caracteres latinos
function remover_acentos($str) {
    $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
    $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
    return str_replace($a, $b, $str);
}