<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once("../comun/autoload.php");
require 'reporte3.php';
require 'vendor/autoload.php'; // Cargar la librería de PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;
$docente = "Andres Paz Burbano";
$fecha = Fecha::formato_fecha(date('d-m-Y'));
##################
if(empty($_GET)){
?>
<form action="" method="get">
<div class= "form-check form-check-inline">
  <input checked class="form-check-input" type="radio" name="grupo" id="inlineRadio1" value="1">
  <label class="form-check-label" for="inlineRadio1">Grupo 1</label>
</div>
<div class="form-check form-check-inline">
  <input class="form-check-input" type="radio" name="grupo" id="inlineRadio2" value="2">
  <label class="form-check-label" for="inlineRadio2">Grupo 2</label>
</div>
<input type="submit"  value="Consultar"> </input>
</form>

<?php
}
################

$grupoSeleccionado = isset($_GET['grupo']) ? $_GET['grupo'] : 2;
if(!empty($_GET['id_estudiante'])){
    $estudiante=$_GET['id_estudiante'];
}else{
    $estudiante='';
}
// Definir la configuración para cada grupo
$configuracionGrupos = [
    1 => [
        "min" => 6,
        "max" => 8,
        "cantidad_Estudiantes" => 18,
        "ruta" => 'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\Escritorio\Valoraciones\resumen_6-8.xlsx',
        "ultimafila" => 18,
        "materias" => [
            "Geometría" => "D",
            "Ciencias Sociales" => "E",
            "Educación Física" => "F",
            "Emprendimiento" => "G",
            "Matemáticas" => "H",
            "Tecnología" => "I",
            "Urbanidad" => "J"
        ]
    ],
    2 => [
        "min" => 9,
        "max" => 11,
        "cantidad_Estudiantes" => 18,
        "ruta" => 'C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\Escritorio\Valoraciones\resumen_9-11.xlsx',
        "ultimafila" => 19,
        "materias" => [
            "Geometría" => "D",
            "Ciencias Sociales/Economia" => "E",
            "Educación Física" => "F",
            "Emprendimiento" => "G",
            "Matemáticas" => "H",
            "Tecnología" => "I",
            "Urbanidad" => "J"
        ]
    ]
];

// Validar que el grupo seleccionado exista, si no, usar el grupo 1 por defecto
if (!isset($configuracionGrupos[$grupoSeleccionado])) {
    $grupoSeleccionado = 2;
}

// Obtener los datos del grupo seleccionado
$grupo = $configuracionGrupos[$grupoSeleccionado];

$min = $grupo["min"];
$max = $grupo["max"];
$ruta = $grupo["ruta"];
$ultimafila = $grupo["ultimafila"];
$materias = $grupo["materias"];

// Obtener la lista de estudiantes del sistema
$estudiantes = COMUN::llamar_estudiantes_grado_Vallesol($min, $max,$estudiante);

// Recorrer la lista de estudiantes y generar el informe individual
foreach ($estudiantes as $dato) {
    $documento_Estudiante = $dato['id_usuario'];
    $nombre_estudiante = $dato['apellido'] . ' ' . $dato['nombre'];
    generarTablaPorEstudiante($documento_Estudiante, $nombre_estudiante, $ruta, $ultimafila, $materias, $fecha, $docente);
}




function generarTablaPorEstudiante($documentoEstudiante, $nombre_estudiante, $ruta, $ultimafila,$materias,$fecha,$docente)
{
    try {
        $sede='Vallesol';
        // Cargar el archivo de Excel
        $spreadsheet = IOFactory::load($ruta);
        $worksheet = $spreadsheet->getActiveSheet();
        // Definir las materias y sus respectivas columnas en el archivo de Excel
          // Estructura de la tabla HTML
        $htmlTable = '
        <img style="margin-left:33%;margin-top:2%" src="Banner guías.png" width="30%" align="center"></img><br>
        <h4 align="center">Seguimiento de ' . $nombre_estudiante . '</h4>
        <span style="margin-left:10%;margin-right:30%">Docente: <b>' . $docente .' (Sede:Vallesol)' . '</b></span> 
        <span>Acudiente:______________________________</span> 
        <table class="table-stripped" style="margin-top:1%" align="center" border="2" cellpadding="5" cellspacing="0">
            <thead class="table-stripped">
                <tr ><th colspan="' . (count($materias) + 3) . '">Seguimiento de notas</th></tr>
                <tr  >
                    <th>Nombre</th>
                    <th>Documento</th>
                    <th>Grado</th>';

        // Generar dinámicamente los encabezados de las materias
        foreach ($materias as $materia => $columna) {
            $htmlTable .= "<th>$materia</th>";
        }

        $htmlTable .= '</tr>
            </thead>
            <tbody>';

        // Variable para saber si se encontró el estudiante
        $encontrado = false;

        // Recorrer las filas con los estudiantes en el archivo
        for ($row = 2; $row <= $ultimafila; $row++) {
            $documento = trim($worksheet->getCell('B' . $row)->getValue()); // Columna B: Documento

            if ($documento === $documentoEstudiante) {
                $encontrado = true;

                // Obtener los datos del estudiante
                $nombre = $worksheet->getCell('A' . $row)->getValue();   // Columna A: Nombre
                $grado = $worksheet->getCell('C' . $row)->getValue();    // Columna C: Grado

                // Comenzar la fila de la tabla
                $htmlTable .= "<tr>
                    <td>$nombre</td>
                    <td>$documento</td>
                    <td>$grado</td>";

                // Obtener y mostrar las notas de cada materia
                foreach ($materias as $materia => $columna) {
                    $nota = $worksheet->getCell($columna . $row)->getCalculatedValue();
                    $nota= round($nota,1);
                    if($nota < 3){
                        $style="style='background-color:red;color:white'";
                    }
                    if($nota == 3){
                        $style="style='background-color:Yellow'";
                    }
                    if($nota > 3){
                        #$style="style='background-color:#58d68d'";
                        $style="style='background-color:white'";
                    }
           
                    $htmlTable .= "<td ".$style.">".$nota."</td>";
                }

                $htmlTable .= "</tr>";

                break; // Salir del bucle tras encontrar el estudiante
            }
        }

        // Si el estudiante no se encontró, agregar un mensaje a la tabla
        if (!$encontrado) {
            $htmlTable .= '<tr><td colspan="' . (count($materias) + 3) . '">Estudiante no encontrado</td></tr>';
        }

        // Cerrar la tabla
        $htmlTable .= '
            </tbody>
        </table>';

        // Agregar asistencia si aplica
        $respuesta =obtenerAsistenciasPorEstudiante($documentoEstudiante);
        if($respuesta<>1){
            $htmlTable .= $respuesta;
        }
        
        

        // Agregar mensaje sobre el informe
        $htmlTable .= 'Nota: ';
        if($respuesta==1){
                $htmlTable .= "<b>El estudiante no registra inasistencias al momento.</b>Tenga en cuenta que ";   
        }
            $htmlTable .="el informe presentado  <b> $fecha </b>
        solo representa un avance y NO es el informe final y puede variar según las actividades pendientes.</p>";
 $htmlTable .='</ul>';
        // Imprimir el informe final
        echo $htmlTable;
        echo "<hr></hr>";
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        echo 'Error al leer el archivo: ' . $e->getMessage();
    }
}


