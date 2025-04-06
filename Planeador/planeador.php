<?php require_once 'lib/dompdf/vendor/autoload.php';
require_once '../clases/Fecha.Class.php';
require_once '../clases/Comun.Class.php';
$path = 'Banner.jpg';
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
require '../comun/conexion.php';
$fecha_inicio="2025-03-01";
$fecha_fin="2025-03-31";
$campo_orden="fecha_inicio";
$orden='asc';

$sql_vallesol='select * from planeador_vallesol
inner join asignacion on planeador_vallesol.materia= asignacion.id_asignacion
inner join materia_oficial on materia_oficial.id_materia=asignacion.id_asignatura';
if(isset($_GET['pdf']) and isset($_GET['idplan'])){
#$sql_vallesol.=' where id_plan="'.$_GET['idplan'].'" order by fecha_inicio asc';
}
#if(isset($_GET['pdf']) and isset($_GET['idasignacion'])){
    $sql_vallesol.=' where  fecha_inicio>="'.$fecha_inicio.'" and fecha_fin <="'.$fecha_fin.'" 
    
    ';
$sql_vallesol.='order by  materia_oficial.id_materia asc ,  '.$campo_orden.' '.$orden.'';
   # echo $sql_vallesol;
#}


$consulta_vallesol=$mysqli->query($sql_vallesol);
function contenido($id_plan,$fecha_creacion, $fecha_inicio, $fecha_fin, $grado, $materia, $periodo, $tiempo_plan, $dba, $estrategias, $evidencias, $observaciones, $recursos, $reflexion, $objetivo, $eje_tematico
){
    $limite_estrategia=500;
    $limite=2000;
    $html='<table  id="miTabla" border="1">
    <tr>
        <td colspan="4">'.$id_plan.'<b>Fecha:</b>'.$fecha_inicio.'  &nbsp; &nbsp;  <b>Grado:</b> '.$grado.' &nbsp; &nbsp; &nbsp; &nbsp; <b>Docente:</b> Hugo Andres Paz Burbano &nbsp; &nbsp;<b>Asignatura:</b>'.$materia.' &nbsp; &nbsp; &nbsp; &nbsp; <b>Periodo:</b> '.$periodo.'</td>
    </tr>


<tr>
<td colspan="4"><B>DBA</B>: '.$dba.'  </td>
</tr>

<tr>
<td colspan="4"><b>Objetivo</b>: '.$objetivo.'</td>
</tr>
<tr>
<td colspan="4"><b>Eje tematicoo</b>: '.puntos_suspensivos(trim($eje_tematico),150).'</td>
</tr>

    <tr >
        <td style="text-align: center;  "><span style="color: red;">Tiempo</span></td>
        <td style="text-align: center;  "><span style="color: red;">Estrategia Metodológica</span></td>
        <td style="text-align: center;  "><span style="color: red;">Momentos</span></td>
        <td style="text-align: center;  "><span style="color: red;">Evaluación </span></td>
    </tr>
    <tr style="height: 50px;" >
        <td >'.puntos_suspensivos(trim($tiempo_plan),$limite).'</td>
        <td >'.puntos_suspensivos(trim($estrategias),$limite_estrategia).'</td>
        <td ">'.puntos_suspensivos(trim($observaciones),$limite).'</td>
        <td >'.puntos_suspensivos(trim($evidencias),40).'</td>
    </tr>
    <tr>
<td colspan="4"><B>Recursos </B>:'.puntos_suspensivos(trim($recursos)).'</td>
</tr>

<tr>
<td colspan="4"><B>Reflexión pedagogica:</B> '.puntos_suspensivos(trim($reflexion)).'</td>
</tr>

    </table>
    ';
    return $html;
}
$html='<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planeador Andres Paz</title>
</head>
<body>';
$html.='<p><img style="margin-left:30%;width:40%;" src="';
$html.=$base64;
$html.='" /></p><span style="  display: block;
    width: 200px;
    margin-left:80%;
    margin: 0 auto;color: red;">Formato Planeador '.date('Y').'</span>';
$contador=0;
while($row=$consulta_vallesol->fetch_assoc()){ 
$contador=$contador+1;
if($contador>1 and $contador<(count($row))){
    $html.=$row['id_plan'];
    $html.=' <div style="page-break-after:always;"></div>';
    $html.='<p><img style="margin-left:30%;width:40%;" src="';
$html.=$base64;
$html.='" /></p><span style="  display: block;
    width: 200px;
    margin-left:80%;
    margin: 0 auto;color: red;">Formato Planeador 2025</span>';

    }
    $id_plan=$row['id_plan'];
    $fecha_creacion=$row['fecha_creacion'];
    $fecha_inicio=Fecha::formato_fecha($row['fecha_inicio']).' al '.Fecha::formato_fecha($row['fecha_fin']);
    $fecha_fin=$row['fecha_fin'];
    $grado=($row['grado']);
    $materia=Comun::eliminar_sobrante($row['nombre_materia']);
    $periodo=$row['periodo'];
    $tiempo_plan=$row['tiempo_plan'].' Horas';
    $dba=$row['dba'];
    $estrategias=$row['estrategias'];
    $evidencias=$row['evidencias'];
    $observaciones=$row['observaciones'];
    $recursos=$row['recursos'];
    $reflexion=$row['reflexion'];
    $objetivo=$row['objetivo'];
    $eje_tematico=$row['eje_tematico'];
    $nombre_pdf=$materia.'_'.$grado.'_'.$periodo.'periodo'.'_'.$fecha_inicio;
$html.=contenido($id_plan,$fecha_creacion, $fecha_inicio, $fecha_fin, $grado, $materia, $periodo, $tiempo_plan, $dba, $estrategias, $evidencias, $observaciones, $recursos, $reflexion, $objetivo, $eje_tematico
);
$html.='    <div style="margin-left:50%" class="page-number">Página ' . $contador . '</div>';
}

  
$html.='</body> </html>';  


use Dompdf\Dompdf;
set_time_limit(27000);
$mipdf = new DOMPDF();
$mipdf->set_paper('legal', 'landscape'); 
$mipdf->load_html(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
#$mipdf->load_html($html,'UTF-8');
$mipdf->render();
if(!empty($_GET['descargar'])){

    $mipdf->stream("$nombre_pdf.pdf");
}else{
$output = $mipdf->output();
$mipdf->stream('usuarios.pdf', array("Attachment" => 0) );

}

?>
