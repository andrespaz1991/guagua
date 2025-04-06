

<?php 
use PhpOffice\PhpSpreadsheet\IOFactory;
actualizar_Asistencia_Vallesol('C:\Users\Andres\OneDrive - UNIVERSIDAD DE SANTANDER - UDES\Escritorio\Asistencia 2025.xlsx');



function obtenerObservacionesPorEstudiante($documento){
    require '../comun/conexion.php'; // Conexión a la base de datos
    // Consulta SQL
    $sql =' select * from seguimiento innner join inscripcion on 
    seguimiento.id_inscripcion=inscripcion.id_inscripcion inner join usuario on 
    inscripcion.id_usuario = usuario.id_usuario

    
    ';

}

function obtenerAsistenciasPorEstudiante($documento) {
    require '../comun/conexion.php'; // Conexión a la base de datos
    // Consulta SQL
    $academico=new Academico();
    $fechas_periodo=$academico->periodo_academico();
    $fecha_inicio=Fecha::formato_fecha_corta($fechas_periodo[0]['fecha_inicio']);
    $fecha_fin=Fecha::formato_fecha_corta($fechas_periodo[0]['fecha_fin']);
    $sql = "
        SELECT 
            estudiante,
            materia,
            COUNT(CASE WHEN asistencias = 'SI' or asistencias = 'P' or asistencias = 'NR'   THEN 1 END) AS asistencias_si,
            COUNT(CASE WHEN asistencias = 'NO' THEN 1 END) AS inasistencias_no,
            COUNT(CASE WHEN asistencias = 'NR' THEN 1 END) AS asistencias_nr,
            GROUP_CONCAT(CASE WHEN asistencias = 'NO' THEN fechas_clase END) AS fechas_inasistencias
        FROM 
            asistencias
        WHERE 
            documento = ".$documento." and
         fechas_clase  BETWEEN '".$fecha_inicio."' AND '".$fecha_fin."'
        GROUP BY 
              materia;
    ";
#echo $sql;
    // Preparar la consulta
    if ($resultado = $mysqli->query($sql) and $resultado->num_rows>0) {
   
   
        #  $stmt->bind_param("s", $documento);
      #  $stmt->execute();
      #  $resultado = $stmt->get_result();
        $html='';
        $html.= "<br><table align='center' border='1' cellpadding='5' cellspacing='0'>";
        $fila = $resultado->fetch_assoc();
        $total_clases = $fila['asistencias_si'] + $fila['inasistencias_no'];

        $porcentaje_inasistencias = ($fila['inasistencias_no'] / $total_clases) * 100;

        #$html.= "<tr><th colspan='5'>" . $fila['estudiante'] . "</th></tr>";
        if ($resultado->num_rows > 0) {
            if($porcentaje_inasistencias>0){
            $html.= "<tr><th colspan='5'>Seguimiento de asistencia</th></tr>";
            $html.= "<tr>
                    <th>Materia</th>
                    <th># Asistencias</th>
                    <th># Inasistencias</th>
                    <th>Porcentaje de Inasistencias</th>
                    <th>Fechas de Inasistencias</th>
                  </tr>";   
            $contador=0;
            while ($fila = $resultado->fetch_assoc()  ) {
                $contador=$contador+1;
                $total_clases = $fila['asistencias_si'] + $fila['inasistencias_no'];
                $porcentaje_inasistencias = ($fila['inasistencias_no'] / $total_clases) * 100;

                // Aplicar color amarillo si el porcentaje de inasistencias es mayor a 30
                $style = ($porcentaje_inasistencias > 30) ? "style='background-color:yellow'" : "";
    $html.= "<tr>
    <td>" . $fila['materia'] . "</td>
    <td>" . $fila['asistencias_si'] . "</td>
    <td>" . $fila['inasistencias_no'] . "</td>
    <td $style>" . number_format($porcentaje_inasistencias, 2) . "%</td>";
if($porcentaje_inasistencias<50){
$html.="<td>" . $fila['fechas_inasistencias'] . "</td>";
}else{
$html.="<td>Para un Reporte detallado contacte al docente</td>";


}
                }

                       $html.="</tr>";
            }else{
                return  1;
            }
        } else {
            $html.= "No se encontraron registros para el documento: $documento";
        }

        $html.= "</table>";
       # $stmt->close();
        return $html;
    }

    $mysqli->close();
}

// Llamada a la función con el documento del estudiante














##########################################################################################




function obtenerNumeroMes($nombreMes) {
    $meses = ["Enero" => 1, "Febrero" => 2, "Marzo" => 3];
    return $meses[$nombreMes] ?? null;
}

function getDayOfWeek($dia, $mes) {
    $fecha = DateTime::createFromFormat('d/m/Y', sprintf('%02d/%02d/%d', $dia, $mes, date('Y')));
    return $fecha ? ['fecha_completa' => $fecha->format('d/m/Y'), 'dia_semana' => ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][$fecha->format('w')]] : null;
}

function getSubjectsForGrade($day, $grade, $horario) {
    return array_filter(array_map(function ($class) use ($grade) {
        foreach (explode(',', $class['grado']) as $rango) {
            if (strpos($rango = trim($rango), '-') !== false) {
                [$min, $max] = explode('-', $rango);
                if ($grade >= $min && $grade <= $max) return $class['materia'];
            } elseif ($grade == $rango) return $class['materia'];
        }
    }, $horario[$day] ?? []));
}




// Función para procesar una hoja de Excel con base en los rangos
function procesarHoja($hoja, $numeroMes, $rangos) {  
    require '../comun/conexion.php'; // Conexión a la base de datos
    global $horario;
    
  #  echo "Procesando hoja: " . $hoja->getTitle() . "\n";

    for ($fila = $rangos['startRow']; $fila <= $rangos['endRow']; $fila++) {
        $nombre = $hoja->getCell('C' . $fila)->getValue();
        $documento = $hoja->getCell('B' . $fila)->getValue();
        $grado = intval($hoja->getCell('D' . $fila)->getValue());

        for ($col = ord($rangos['startCol']); $col <= ord($rangos['endCol']); $col++) {
            $diaNumero = intval($hoja->getCell(chr($col) . '9')->getValue());
            $dateInfo = getDayOfWeek($diaNumero, $numeroMes);

            if (!$dateInfo || in_array($dateInfo['dia_semana'], ['Sábado', 'Domingo'])) continue;
            $fecha_completa = $dateInfo['fecha_completa'];
            $materias = getSubjectsForGrade($dateInfo['dia_semana'], $grado, $horario);
            $valor = trim($hoja->getCell(chr($col) . $fila)->getValue());
            $asistio = ($valor === 'X') ? 1 : 0;

            $valores = [];

            foreach ($materias as $materia) {
                $valores[] = "('$nombre', '$materia', '$fecha_completa', '$documento', '$valor')";
            }

            if (!empty($valores) ) {
                $sql_asis = "
                    INSERT INTO asistencias (estudiante, materia, fechas_clase, documento, asistencias) 
                    VALUES " . implode(', ', $valores) . "
                    ON DUPLICATE KEY UPDATE asistencias = VALUES(asistencias);
                ";

                $mysqli->query($sql_asis);
            }
        }
    }
}


// Función principal para actualizar asistencia desde el archivo de Excel
function actualizar_Asistencia_Vallesol($rutaarchivo) {
    global $horario;
    $horario = [
        'Lunes' => [
            ['hora' => '7:00-9:00', 'materia' => 'ECONOMIA/SOCIALES', 'grado' => '9-11'],
            ['hora' => '9:15-11:15', 'materia' => 'C. SOCIALES', 'grado' => '6-8'],
            ['hora' => '11:30-13:30', 'materia' => 'EMPRENDIMIENTO', 'grado' => '6-11'],
        ],
        'Martes' => [
            ['hora' => '7:00-9:00', 'materia' => 'ED. FISICA', 'grado' => '6-7, 9-11'],
            ['hora' => '9:15-11:15', 'materia' => 'MATEMATICAS', 'grado' => '6-8'],
            ['hora' => '11:30-13:30', 'materia' => 'MATEMATICAS', 'grado' => '9-11'],
        ],
        'Miercoles' => [
            ['hora' => '7:00-9:00', 'materia' => 'TECNOLOGIA', 'grado' => '9-11'],
            ['hora' => '9:15-11:15', 'materia' => 'TECNOLOGIA', 'grado' => '6-8'],
            ['hora' => '11:30-13:30', 'materia' => 'GEOMETRIA', 'grado' => '6-11'],
        ],
        'Jueves' => [
            ['hora' => '7:00-9:00', 'materia' => 'ED. FISICA', 'grado' => '9-11, 6-8'],
            ['hora' => '9:15-11:15', 'materia' => 'MATEMATICAS', 'grado' => '6-8'],
            ['hora' => '11:30-13:30', 'materia' => 'MATEMATICAS', 'grado' => '9-11'],
        ],
        'Viernes' => [
            ['hora' => '7:00-9:00', 'materia' => 'FISICA', 'grado' => '9-11'],
            ['hora' => '9:15-11:15', 'materia' => 'URBANIDAD', 'grado' => '6-7, 8-11'],
            ['hora' => '11:30-13:30', 'materia' => 'C. SOCIALES', 'grado' => '6-8'],
        ],
    ];



    $rangosMeses = [
        "Enero" => ['startCol' => 'F', 'endCol' => 'O', 'startRow' => 10, 'endRow' => 43],
        "Febrero" => ['startCol' => 'F', 'endCol' => 'X', 'startRow' => 11, 'endRow' => 40],
        "Marzo" => ['startCol' => 'H', 'endCol' => 'Y', 'startRow' => 12, 'endRow' => 41]
    ];

    try {
        require 'vendor/autoload.php';
require '../comun/conexion.php';
      #  echo "Cargando archivo...\n";
        $spreadsheet = IOFactory::load($rutaarchivo);

        if (!$spreadsheet) {
          //  echo "Error: No se pudo cargar el archivo $rutaarchivo\n";
           // return;
        }

        foreach ($spreadsheet->getSheetNames() as $nombreHoja) {
            if (!isset($rangosMeses[$nombreHoja])) continue;

            $numeroMes = obtenerNumeroMes($nombreHoja);
            if ($numeroMes === null) {
                echo "Mes no válido: $nombreHoja\n";
                continue;
            }

            procesarHoja($spreadsheet->getSheetByName($nombreHoja), $numeroMes, $rangosMeses[$nombreHoja]);
        }
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        echo 'Error al leer el archivo: ' . $e->getMessage();
    }
}

// Ejemplo de uso

