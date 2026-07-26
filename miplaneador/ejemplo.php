<?php
/**
 * Script Refactorizado para Gestión de Planeaciones Vallesol 2026
 * Configuración: Jerarquía [Mes] -> [Materia] (Sin subcarpetas extras)
 * Autor: Andres Paz
 */

include_once('tbs_class.php'); 
include_once('plugins/tbs_plugin_opentbs.php'); 
require_once("../comun/autoload.php");
require_once("../Clases/Fecha.Class.php");
require '../comun/conexion.php';

// Inicializar TBS
$TBS = new clsTinyButStrong; 
$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); 

// 1. Obtención de parámetros
$id_plan = isset($_GET['id']) ? $_GET['id'] : exit("ID no proporcionado");
// Modo predeterminado: guardar
$modo_salida = isset($_GET['modo']) ? $_GET['modo'] : 'guardar'; 

$sql_vallesol = "SELECT * FROM planeador_vallesol
                INNER JOIN asignacion ON asignacion.id_asignacion = planeador_vallesol.materia
                INNER JOIN materia_oficial ON asignacion.id_asignatura = materia_oficial.id_materia
                WHERE planeador_vallesol.id_plan = '$id_plan'";

$consulta_vallesol = $mysqli->query($sql_vallesol);

if ($row = $consulta_vallesol->fetch_assoc()) {
    // 2. Procesamiento de variables
    $fecha_inicio = Fecha::formato_fecha($row['fecha_inicio']) . ' al ' . Fecha::formato_fecha($row['fecha_fin']);
    $mes = Fecha::mes($row['fecha_inicio'], true);
    $grado = $row['grado']; // Grado directo como se solicitó
    $materia_raw = trim(Comun::eliminar_sobrante($row['nombre_materia']));
    $docente = 'Andres Paz';

    // 3. Lógica de Mapeo y Excepción para Economía (evita subcarpetas como "politica")
    $mapeo_carpetas = [
        'Ciencias Sociales' => 'Ciencias Sociales',
        'Educación Física'  => 'Ed Fisicia',
        'Ed. Física'        => 'Ed Fisicia',
        'Física'            => 'Fisica',
        'Matemáticas'       => 'Matematicas',
        'Tecnología'        => 'Tecnologia',
        'Emprendimiento'    => 'Emprendimiento',
        'Urbanidad'         => 'Urbanidad'
    ];

    // Normalización de Economía: Si contiene "Economia" (con o sin tilde), se fuerza a Ciencias Sociales
    if (stripos($materia_raw, 'Economia') !== false || stripos($materia_raw, 'Economía') !== false) {
        $carpeta_materia = 'Ciencias Sociales';
        $materia_para_nombre = 'Ciencias Sociales';
    } else {
        $carpeta_materia = isset($mapeo_carpetas[$materia_raw]) ? $mapeo_carpetas[$materia_raw] : $materia_raw;
        $materia_para_nombre = $materia_raw;
    }

    // 4. Configuración de Archivo y Rutas (Mes -> Materia)
    // Limpiamos el nombre de la materia para el archivo (un solo nivel, sin subcarpetas)
    $materia_clean = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $materia_para_nombre);
    $nombre_archivo = "{$materia_clean} {$grado}.docx";
    
    // Ruta final: Planeaciones \ [Mes] \ [Materia]
    $base_path = "G:\\Mi unidad\\Vallesol2026\\Planeaciones\\";
    $target_dir = $base_path . $mes . "\\" . $carpeta_materia;
    $full_path = $target_dir . "\\" . $nombre_archivo;

    // 5. Carga de Plantilla y Mezcla de Campos
    $template = 'PLANEADOR2026.docx';
    $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);

    $TBS->MergeField('fecha_inicio', trim($fecha_inicio));
    $TBS->MergeField('periodo',      trim($row['periodo']));
    $TBS->MergeField('dba',          trim($row['dba']));
    $TBS->MergeField('objetivo',     trim($row['objetivo']));
    $TBS->MergeField('tiempo',       trim($row['tiempo_plan'] . ' Horas semanales'));
    $TBS->MergeField('estrategia',   trim($row['estrategias']));
    $TBS->MergeField('momentos',     trim(strip_tags($row['observaciones'])));
    $TBS->MergeField('materia',      trim($materia_para_nombre));
    $TBS->MergeField('evaluacion',   trim($row['recursos']));
    $TBS->MergeField('mes',          trim($mes));
    $TBS->MergeField('docente',      trim($docente));

    $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

    // 6. Ejecución de Salida
    if ($modo_salida === 'guardar') {
        // Creamos la carpeta del mes y la de la materia (sin niveles extra)
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $TBS->Show(OPENTBS_FILE, $full_path);
        echo "Planeación guardada con éxito en: " . $full_path;
        echo '<meta http-equiv="refresh" content="2; url=../planeador/index.php?asignacion='. $_GET['asignacion'].'&id='.$id_nuevo_planeador . '" />';

    } else {
        // Descarga si es necesario
        $TBS->Show(OPENTBS_DOWNLOAD, $nombre_archivo);
    }
    exit();
} else {
    echo "No se encontró información para el ID: " . $id_plan;
}
?>