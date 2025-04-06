<?php 
@session_start();
$_SESSION['username']="andres";
$_SESSION['id_institucion']="1";
$_SESSION['id_usuario']="1085290375";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'].'/comun/lib/dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
#require_once("../comun/autoload.php");
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
$asignacion=$_GET['asignacion'];
$notas= new Academico($asignacion);
$informacionHorario=($notas->informacion_horario_Asignacion()[0]);
$fechainicial=$informacionHorario['fecha_inicio'];
$fechafinal=date('Y-m-d');
generaredunota($asignacion,$fechainicial,$fechafinal);
function generaredunota($asignacion,$fechainicial,$fechafinal){
@session_start();
$institucion=new Institucion($_SESSION['id_institucion']);
$notas= new Academico();
$docente=new Persona($_SESSION['id_usuario']);
$infomateria=$notas->consultar_materia($asignacion);

#$instancia_materia=new Materias($planeacion->materia);
$html='<div style="overflow: hidden;
text-align:center;
  margin-bootom:10px;
  padding: 20px 10px;" class="header">';
#$html.='<img style="width:10%;position:absolute;" src="../../comun/sga-data/foto/'.$institucion->logo_institucion.'"></img>';
$html.='<a href="#default" class="logo"><strong>'.$institucion->nombre_institucion.'<br>'.$docente->nombre.' '.$docente->apellido.'</strong> </br> <strong>'.$infomateria[0]->nombre_materia.'</strong></a>';
$html.='<img style="width:10%;position:absolute;margin-left:70%;margin-top:-4%" src="../../comun/sga-data/foto/'.$institucion->logo_institucion.'"></img>';
$html.='</div>';
$todas=$notas->notasdeclase($asignacion,$fechainicial,$fechafinal);
if(!empty($todas)){
$html.='
<br><br>
<table border="2"  class="table table-striped">

  <tr>

    <th>Número</th>

    <th>Nota</th>

    <th>Fecha</th>

    <th>hora</th>

  </tr>';

$contador=0;

 foreach ($todas as $planes => $notas) { 

$html.='<tr align="center">

    <td>'.$notas['id_nota'].'</td>

    <td>'.($notas['nota']) .'</td>

    <td>'.Fecha::formato_fecha($notas['fecha_nota']).'</td>

    <td>'.Fecha::formato_hora($notas['hora_nota']).'</td>

  </tr>';

 } }

  $html.='</tr>';

$html.='</table>';

$dompdf = new DOMPDF();  //if you use namespaces you may use new \DOMPDF()
$dompdf->loadHtml($html);
$dompdf->render();
$dompdf->stream("sample.pdf", array("Attachment"=>0));

}



