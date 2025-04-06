<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../comun/config.php';
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/comun/lib/dompdf/vendor/autoload.php';
use Dompdf\Dompdf;
$ruta_foto_estudiante="../../sga-data/foto/";
$seguimiento=new Seguimiento();
$academico=new Academico();

if(isset($_POST['id_asignacion'])){
  $alumnos=$academico->listar_estudiantes_asignacion($_POST['id_asignacion']);
}else{
  $alumnos=array($_GET['inscripcion']);
}
if(isset($_GET['inscripcion'])){
  $_POST['id_inscripcion']=$_GET['inscripcion'];
}
#$alumnos=array("15364","15362");
$html='';
$contador=0;
$html.=reporte_estudiante($_GET['inscripcion']);
foreach($alumnos as $clave=> $documento ){
  #print_r();
  $contador++;
#if($contador<20){

#}
}

$mipdf = new DOMPDF();
$mipdf->set_paper("A4", "landascape"); 
$mipdf->load_html($html,'UTF-8');
$mipdf->render();
$nombre_estudiante_completo="informe";
if(isset($_POST['descargar_x'])){
$mipdf->stream($nombre_estudiante_completo );
}else{
  $mipdf->stream($nombre_estudiante_completo , array("Attachment" => 0));
}



function reporte_estudiante($inscripcion){
  $ruta_foto_estudiante="../../sga-data/foto/";
require_once '../../comun/config.php';
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/comun/lib/dompdf/vendor/autoload.php';
$seguimiento=new Seguimiento;
$academico=new Academico;
  $_POST['id_inscripcion']=$inscripcion;
  $datos = $academico->inscripcion($_POST['id_inscripcion'])[0];
  $id_estudiante=$datos['id_estudiante'];
  $estudiante=new Persona($id_estudiante);

  $seguimiento->identificacion=$id_estudiante;
  $seguimiento->grupo=$datos['id_asignacion'];
  $institucion=new Institucion($_SESSION['id_institucion']);
  $nombre_estudiante_completo=mb_strtoupper($estudiante->apellido.' '.$estudiante->nombre, 'UTF-8').'.pdf';
  $observaciones_estudiante= $seguimiento->consultar_seguimiento_asignacion();
  #print_r($observaciones_estudiante);
  $estilo="<style>
  #header { position: fixed; left: 0px; top: 12%; right: 0px; height: 150px; text-align: center; }
  
  #datos_personales{
      position:absolute;
      margin-top:7%;
      margin-left:30%;
  }
  #datos_personales2{
    position:absolute;
    margin-top:7%;
    margin-left:65%;
  }
  #foto_estudiante{
      width:15%;
      margin-left:-70%;
      margin-top:-1.5%;
  
  }
  #nombre_estudiante{
      margin-top:-2%;
      margin-left:15.5cm;
      position:absolute;
  }
  <style>
  span{
    text-decoration: underline;
    }
  .parrafo {
   font-family: 'Arial Narrow';
    font-size: 14px;
    margin: 0.2cm 0cm 0cm 2cm ;
  }
  .logo {
    font-family: 'Arial Narrow';
    font-size: 14px;
    margin: 0.2cm 15cm 0cm 0.5cm ;
    position:absolute;
  }
  .logo2 {
    margin-top: 0.2cm;
    margin-left:15.7cm;
    position:absolute;
  }
  #footer {font-size:12px;  position: fixed; left: -6cm; bottom: -130px; right: 0px; height: 150px; background-color: white; }
  #footer .page:after { content: counter(page, upper-roman); }
  .CEM{position:absolute;
  margin-left:7cm;
  margin-bottom:3cm;
  }
  .sed{ margin: -5cm 0cm 0cm 5cm ; }
  .info{
    font-size:10;
    display:inline;
  }
  #linea1{
    margin: 0.2cm 15cm 0cm 2cm ;
    position:absolute;
  }
  
  </style>";
  $html='<!DOCTYPE html>
  <html lang="es">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Estudiante</title>
      '.$estilo.'
  </head>
  <body>';
  /*
      <img class="logo" width:"65" src="../../sga-data/foto/hst.png"></img>';
      if($institucion->id_institucion_educativa==4){
        $html.='<img class="logo2" height="65" src="../../sga-data/foto/licenar2.jpeg"></img>';
      }else{
  $html.='<img class="logo2" height="65" src="./sga-data/foto/'.$institucion->logo_institucion.'"></img>';
  
      }
  */ 
  $html.='<p class="CEM"><strong>
      '.$institucion->nombre_institucion.'    </strong>   </p><br><br>
  <SPAN STYLE="margin: 0cm 0.5cm 0cm 7.7cm ;font-size:12px;"">AÑO LECTIVO '.date('Y').' </SPAN><BR>
  <SPAN STYLE="margin-left:7.7cm;margin-top:2%;font-size:17px;font-weight: bold;">SEGUIMIENTO</SPAN>
       </p><br>
  
  <div id="header" >
  </div>
  <h3 style="margin-left:30%;margin-top:5%" id="nombre_estudiante">'.mb_strtoupper($estudiante->apellido.' '.$estudiante->nombre, 'UTF-8').'</h3>
  <div id="datos_personales">
   '.$estudiante->tipo_sangre.' </span><br>';
   $html.='<span class="info"><strong>Identificación:'.$estudiante->id_persona.' </span><br/>';
   $html.='<span class="info"><strong>Télefono:'.$estudiante->telefono.' </span><br/>';
  $acudiente= $estudiante->acudiente();
  if(count($acudiente)>0) {
  foreach($acudiente as $clave => $dato){
    $acudiente=new Persona($dato['id_acudiente']);
    if($dato['parentesco']=="acudiente"){
     $roles= $estudiante->verificar_rol_acudiente($dato['id_acudiente'],$estudiante->id_persona);

     if(isset($roles[0]['parentesco']) and ($roles[0]['parentesco']<>1)){
      if(!empty($acudiente->nombre)){
     # $html.='<span class="info"><strong>'.strtoupper($dato['parentesco']).': </strong>'.$acudiente->nombre.' '.$acudiente->apellido.' Tel:'.$acudiente->telefono.' </span><br/>';
      }
    }
  
    }else{
      if(!empty($acudiente->nombre)){
        $html.='<span class="info"><strong>'.strtoupper($dato['parentesco']).': </strong>'.$acudiente->nombre.' '.$acudiente->apellido.' Tel:'.$acudiente->telefono.' </span><br/>';
      }
     
    }
    
  }
  }
  $html.='<span class="info"><strong>Dirección: </strong>'.$estudiante->direccion.' </span><br/>
  </div>
  
  <div id="datos_personales2">
  </div>
  
  <hr>
  <div></div>
  
  <style>
  @import url(https://fonts.googleapis.com/css?family=Roboto);
  body {
      font-family: "Roboto", sans-serif;
    }
  .separator {
      margin-top: 10px;
      border: 1px solid #C3C3C3;
    }
    #card {
      border-radius: 5px;
      position: absolute;
      margin: 200px auto;
      background-color: #FFF;
      -webkit-box-shadow: 10px 10px 93px 0px rgba(255, 0, 0, 0.75);
      -moz-box-shadow: 10px 10px 93px 0px rgba(0, 0, 0, 0.75);
      box-shadow: 10px 10px 93px 0px rgba(0, 0, 0, 0.75);
    }
    .right {
  
    }
    .thumbnail {
      float: left;
      position: relative;
      left: 30px;
      top: -30px;
      height: 320px;
      width: 630px;
    }
    p {
      text-align: justify;
      font-size: 0.95rem;
      line-height: 150%;
      color: #4B4B4B;
    }
    .footer {
      position: fixed;
      left: 27%;
      top: 85%;
      bottom: 0;
      width: 100%;
      color: white;
      text-align: center;
    }
    #title, span {display: inline;}
    </style>
    <div id="card">';
    if(!empty($observaciones_estudiante)){
$html.='<table border="1" style="width:auto;margin-left:20%;width:120%;border-collapse: collapse;">
    <tr>
  <th>Fecha</th>
  <th>Observación</th>
    </tr> ';
    $contador=0;
  foreach($observaciones_estudiante as $clave=>$observacion){
    $contador = $contador +strlen($observacion['observaciones']);
  $html.="  <tr>
  <td align='center' style='width=70%;height:auto;font-size:10;'>".Fecha::formato_fecha($observacion['fecha_asesoria']).' <br> '.Fecha::formato_hora($observacion['hora_inicio'])."</td>
  <td align='justify' style='width=70%;height:auto;font-size:10;'>".$observacion['observaciones']."'</td>
    </tr> ";  
    }  
    if($contador>=2000){
      $html.='</table>';
      $html.=' <div style="page-break-before:always;"></div> ';
    }
    
}

$html.=' <div style="page-break-after:always;"></div> ';
$html.='</div>
  </body>

  </html>
  ';

  #$html.=' <div style="page-break-after:always;"></div> ';

  return $html;
}

