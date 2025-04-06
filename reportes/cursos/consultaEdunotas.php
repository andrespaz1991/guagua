<?php 
require_once '../../comun/lib/dompdf/dompdf_config.inc.php';
require_once '../../clases/fecha.Class.php'; 
require_once '../../clases/comun.Class.php'; 
require_once '../../clases/clase_mysqli.Class.php'; 
require_once '../../clases/Academico.class.php'; 
require_once '../../clases/institucion.class.php'; 
require_once '../../clases/Persona.class.php'; 
@session_start();
$fechainicial="2020/04/13";
#$fechafinal="2020/04/25";
$fechafinal=date('Y-m-d');
$html2='';
$academico=new Academico();
$datos_curso = $academico ->mis_cursos_otros(); 
foreach ($datos_curso as $key => $datos_materia) { 
   $html2.=generaredunota($datos_materia['id_asignacion'],$fechainicial,$fechafinal);
}
$mipdf = new DOMPDF();
$mipdf ->set_paper("A4", "landascape"); 
$mipdf ->load_html($html2,'UTF-8');
$mipdf ->render();
$mipdf ->stream('FicheroEjemplo.pdf' , array("Attachment" => 0));

function generaredunota($asignacion,$fechainicial,$fechafinal){
$institucion=new institucion($_SESSION['institucion']);
$notas= new Academico();
$docente=new Persona($_SESSION['id_usuario']);
$infomateria=$notas->consultar_materia($asignacion);
#$instancia_materia=new Materias($planeacion->materia);
$todas=$notas->notasdeclase($asignacion,$fechainicial,$fechafinal);
if(!empty($todas)){
$notas2= new Academico($asignacion);
$informacionHorario=($notas2->informacion_horario_Asignacion()[0]);
$html='
<div style="overflow: hidden;
text-align:center;
  margin-bootom:10px;
  padding: 20px 10px;" class="header">';
$html.='<img style="width:10%;position:absolute;" src="../../comun/sga-data/foto/'.$institucion->logo_institucion.'"></img>';
$html.='<a href="#default" class="logo"><strong>'.$institucion->nombre_institucion.'<br>'.$docente->nombre.' '.$docente->apellido.'
<br><strong>'.$infomateria[0]->nombre_materia.'</strong>
<br>'.$informacionHorario['dia'].'('.Fecha::formato_hora($informacionHorario['hora_inicio']).')'.'</strong></a>';
$html.='<img style="width:10%;position:absolute;margin-left:70%;margin-top:-4%" src="../../comun/sga-data/foto/'.$institucion->logo_institucion.'"></img>';
$html.='</div>';

$todas=$notas->notasdeclase($asignacion,$fechainicial,$fechafinal);
if(!empty($todas)){
$html.='
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
$html.='</table>
<div style="page-break-after:always;"></div>

';
return $html;
}
}

