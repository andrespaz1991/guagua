<?php
require_once 'lib/dompdf/vendor/autoload.php';
require_once '../clases/Fecha.Class.php';
require_once '../clases/Comun.Class.php';

// Asegúrate de que 'puntos_suspensivos' esté definido o inclúyelo aquí si es una función auxiliar
// Ejemplo de función puntos_suspensivos si no está en Comun.Class.php


$path = 'Banner.jpg';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

require '../comun/conexion.php'; // Asegúrate de que esta ruta sea correcta

#####################################
function obtenerPrimerUltimoDiaDelMes(string $nombreMes): ?array
{
    // Mapeo de nombres de meses a números
    $meses = [
        'enero'      => 1,
        'febrero'    => 2,
        'marzo'      => 3,
        'abril'      => 4,
        'mayo'       => 5,
        'junio'      => 6,
        'julio'      => 7,
        'agosto'     => 8,
        'septiembre' => 9,
        'octubre'    => 10,
        'noviembre'  => 11,
        'diciembre'  => 12,
    ];

    // Convertir el nombre del mes a minúsculas para una comparación sin distinción de mayúsculas y minúsculas
    $nombreMesLower = strtolower($nombreMes);

    // Verificar si el nombre del mes es válido
    if (!isset($meses[$nombreMesLower])) {
        return null; // O puedes lanzar una excepción, dependiendo de cómo quieras manejar errores
    }

    $numeroMes = $meses[$nombreMesLower];
    $anioActual = date('Y'); // Obtener el año actual

    // Crear un objeto DateTime para el primer día del mes
    $primerDia = new DateTime("$anioActual-$numeroMes-01");

    // Calcular el último día del mes
    $ultimoDiaNumero = $primerDia->format('t'); // 't' para obtener el número de días en el mes
    $ultimoDia = new DateTime("$anioActual-$numeroMes-$ultimoDiaNumero");

    return [
        'primer_dia' => $primerDia->format('Y-m-d'),
        'ultimo_dia' => $ultimoDia->format('Y-m-d'),
    ];
}


#########################################
if(isset($_GET['mes'])){
    $mesSolicitado2 = $_GET['mes'];
    $fechas2 = obtenerPrimerUltimoDiaDelMes($mesSolicitado2);
    $fecha_inicio = $fechas2['primer_dia'];
    $fecha_fin = $fechas2['ultimo_dia'];
} else {
    // Usar la fecha actual para la generación de PDF si no se especifica mes
    $fecha_inicio = date('Y-m-01'); // Primer día del mes actual
    $fecha_fin = date('Y-m-t');   // Último día del mes actual
}

$campo_orden = "fecha_inicio";
$orden = 'asc';

$sql_vallesol = 'SELECT * FROM planeador_vallesol
                 INNER JOIN asignacion ON planeador_vallesol.materia = asignacion.id_asignacion
                 INNER JOIN materia_oficial ON materia_oficial.id_materia = asignacion.id_asignatura';

if(isset($_GET['pdf']) && isset($_GET['idplan'])){
    $sql_vallesol .= ' WHERE id_plan = "' . $_GET['idplan'] . '"';
} else {
    $sql_vallesol .= ' WHERE fecha_inicio >= "' . $fecha_inicio . '" AND fecha_fin <= "' . $fecha_fin . '"';
}

$sql_vallesol .= ' ORDER BY materia_oficial.id_materia ASC, ' . $campo_orden . ' ' . $orden;
#cho $sql_vallesol;
#exit();

$consulta_vallesol = $mysqli->query($sql_vallesol);

// Función para generar el contenido de un plan, ahora envuelto en un div con page-break-inside: avoid;
function contenido($id_plan, $fecha_creacion, $fecha_inicio, $fecha_fin, $grado, $materia, $periodo, $tiempo_plan, $dba, $estrategias, $evidencias, $observaciones, $recursos, $reflexion, $objetivo, $eje_tematico) {
    $limite_estrategia = 200; // Puedes ajustar estos límites según tus necesidades
    $limite = 2000;
    $limite_recursos=300;
    $limite_objetivo=200;
    $limite_evidencia=200;
    $limite_eje=150;
    $html = '<div class="plan-container">'; // Contenedor para cada plan
    $html .= '<table id="miTabla" border="1" style="width: 100%; border-collapse: collapse;">'; // Ancho 100% y bordes colapsados
    $html .= '<tr>';
    $html .= ' <td colspan="4"><b>Plan ID:</b> ' . $id_plan . ' &nbsp; &nbsp; <b>Fecha:</b> ' . $fecha_inicio . ' &nbsp; &nbsp; <b>Grado:</b> ' . $grado . ' &nbsp; &nbsp; <b>Docente:</b> Hugo Andres Paz Burbano &nbsp; &nbsp;<b>Asignatura:</b> ' . $materia . ' &nbsp; &nbsp; <b>Periodo:</b> ' . $periodo . '</td>';
    $html .= '</tr>';
    $html .= '<tr><td colspan="4"><B>DBA</B>: ' . puntos_suspensivos(trim($dba), $limite) . '</td></tr>';
    $html .= '<tr><td colspan="4"><b>Objetivo</b>: ' . puntos_suspensivos(trim($objetivo), $limite_objetivo) . '</td></tr>';
    $html .= '<tr><td colspan="4"><b>Eje temático</b>: ' . puntos_suspensivos(trim($eje_tematico), $limite_eje) . '</td></tr>';
    $html .= '<tr>';
    $html .= ' <td style="text-align: center; background-color: #f2f2f2;"><span style="color: red;">Tiempo</span></td>';
    $html .= ' <td style="text-align: center; background-color: #f2f2f2;"><span style="color: red;">Estrategia Metodológica</span></td>';
    $html .= ' <td style="text-align: center; background-color: #f2f2f2;"><span style="color: red;">Momentos</span></td>';
    $html .= ' <td style="text-align: center; background-color: #f2f2f2;"><span style="color: red;">Evaluación</span></td>';
    $html .= '</tr>';
    $html .= '<tr style="height: 50px;">'; // Puedes ajustar la altura mínima si es necesario
    $html .= ' <td>' . puntos_suspensivos(trim($tiempo_plan), $limite) . '</td>';
    $html .= ' <td>' . puntos_suspensivos(trim($estrategias), $limite_estrategia) . '</td>';
    $html .= ' <td>' . puntos_suspensivos(trim($observaciones), $limite) . '</td>';
    $html .= ' <td>' . puntos_suspensivos(trim($evidencias), $limite_evidencia) . '</td>'; // Ajusta el límite si es un campo muy corto
    $html .= '</tr>';
    $html .= '<tr><td colspan="4"><B>Recursos</B>: ' . puntos_suspensivos(trim($recursos), $limite_recursos) . '</td></tr>';
    $html .= '<tr><td colspan="4"><B>Reflexión pedagógica:</B> ' . puntos_suspensivos(trim($reflexion), $limite) . '</td></tr>';
    $html .= '</table>';
    $html .= '</div>'; // Cierra el div del plan
    return $html;
}

$html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeador Andrés Paz</title>
    <style>
        @page {
            margin-top: 120px; /* Espacio para el encabezado */
            margin-bottom: 50px; /* Espacio para el pie de página */
        }
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
        }
        #header {
            position: fixed;
            top: -15%;
            left: 0;
            right: 0;
            height: 100px; /* Altura del encabezado */
            text-align: center;
            /* Puedes añadir un borde o fondo si quieres que el encabezado sea más distintivo */
            /* border-bottom: 1px solid #ccc; */
            background-color: white; /* Asegura que el fondo sea blanco si hay contenido detrás */
        }
        #header img {
            width: 40%;
            height: auto;
            margin-top: 5px; /* Pequeño margen superior para la imagen */

        }
        #header span {
            font-size: 12px;
        }
        #footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px; /* Altura del pie de página */
            text-align: center;
            font-size: 10px;
            color: #555;
            /* border-top: 1px solid #ccc; */
            background-color: white; /* Asegura que el fondo sea blanco */
        }
        .page-number::before {
            content: "Página " counter(page) " de " counter(pages);
        }
        .plan-container {
            /* Esto es CRUCIAL para evitar páginas vacías si el plan cabe */
            page-break-inside: avoid; 
            margin-top:5%;
            margin-bottom: 20px; /* Espacio entre planes si hay varios en la misma página */
        }
        table {
        maring-top:150%;
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 10px; /* Espacio después de cada tabla de plan */
        }
        table, th, td {
            border: 1px solid #000; /* Bordes de la tabla más definidos */
        }
        td {
            padding: 8px; /* Más padding en las celdas para legibilidad */
            vertical-align: top; /* Contenido de las celdas alineado arriba */
            font-size: 11px; /* Tamaño de fuente para el contenido de la tabla */
            line-height: 1.4; /* Espaciado entre líneas para mejor lectura */
        }
        b {
            color: #333; /* Color para el texto en negrita */
        }
        /* Estilos para puntos_suspensivos si se necesitan estilos específicos */
        .puntos-suspensivos {
            /* Puedes añadir estilos aquí si es necesario */
        }
    </style>
</head>
<body>';

// Encabezado que se repetirá en cada página
$html .= '<div id="header">';
$html .= ' <p><img src="' . $base64 . '" alt="Banner"></p>';
$html .= ' <span style="color: red;">Formato Planeador ' . date('Y') . '</span>';
$html .= '</div>';

// Pie de página que se repetirá en cada página
$html .= '<div id="footer">';
$html .= ' <span class="page-number"></span>';
$html .= '</div>';

// Iterar sobre los planes y generar su HTML
while($row = $consulta_vallesol->fetch_assoc()){ 
    // Los datos del plan
    $id_plan = $row['id_plan'];
    $fecha_creacion = $row['fecha_creacion'];
    $fecha_inicio = Fecha::formato_fecha($row['fecha_inicio']) . ' al ' . Fecha::formato_fecha($row['fecha_fin']);
    $fecha_fin = $row['fecha_fin']; // Esta variable no se usa directamente en el contenido, solo para el nombre del PDF
    $grado = ($row['grado']);
    $materia = Comun::eliminar_sobrante($row['nombre_materia']);
    $periodo = $row['periodo'];
    $tiempo_plan = $row['tiempo_plan'] . ' Horas';
    $dba = $row['dba'];
    $estrategias = $row['estrategias'];
    $evidencias = $row['evidencias'];
    $observaciones = $row['observaciones'];
    $recursos = $row['recursos'];
    $reflexion = $row['reflexion'];
    $objetivo = $row['objetivo'];
    $eje_tematico = $row['eje_tematico'];
    
    // Nombre del PDF (considera que esto se generaría solo una vez si descargas un solo PDF)
    $nombre_pdf = $materia . '_' . $grado . '_' . $periodo . 'periodo' . '_' . Fecha::formato_fecha($row['fecha_inicio']);

    // Agrega el contenido de cada plan. Dompdf manejará el salto de página automáticamente
    // gracias a `page-break-inside: avoid;` en el .plan-container
    $html .= contenido($id_plan, $fecha_creacion, $fecha_inicio, $fecha_fin, $grado, $materia, $periodo, $tiempo_plan, $dba, $estrategias, $evidencias, $observaciones, $recursos, $reflexion, $objetivo, $eje_tematico);
}

$html .= '</body> </html>'; 

// Configuración y renderizado de Dompdf
use Dompdf\Dompdf;
set_time_limit(27000); // Ya lo tienes, es un valor muy alto para evitar timeouts

$mipdf = new DOMPDF();
$mipdf->set_paper('legal', 'landscape'); // Orientación horizontal en tamaño legal
$mipdf->load_html(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$mipdf->render();

// Salida del PDF
if(!empty($_GET['descargar'])){
    $mipdf->stream("$nombre_pdf.pdf"); // Descarga el PDF con el nombre generado
} else {
    $mipdf->stream('planeadores.pdf', array("Attachment" => 0)); // Muestra el PDF en el navegador
}

?>