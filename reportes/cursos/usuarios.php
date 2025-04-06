<?php
ob_start();
@session_Start();
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
#require_once '../../comun/lib/dompdf/dompdf_config.inc.php';
require '../../comun/conexion.php';
$inf_ie ='select * from config';
$consulta = $mysqli->query($inf_ie);
$resultados = $consulta->num_rows;
$html='';
if($rowinfosede =$consulta->fetch_assoc()  ){ 
$sql = 'SELECT DISTINCT * FROM inscripcion, asignacion,materia,usuario,categoria_curso,ano_lectivo';
$sql.= ' where usuario.estado="activo" and
inscripcion.id_asignacion =asignacion.id_asignacion and
asignacion.ano_lectivo =ano_lectivo.id_ano_lectivo and
asignacion.id_asignatura = materia.id_materia and
 asignacion.id_categoria_curso  = categoria_curso.id_categoria_curso and
 usuario.id_usuario = inscripcion.id_estudiante  ';
if(isset($_POST['id_asignacion'])){
    $sql.=' and inscripcion.id_asignacion="'.$_POST['id_asignacion'].'" '; 
}
if(isset($_GET['id_asignacion'])){
    $sql.=' and inscripcion.id_asignacion="'.$_GET['id_asignacion'].'" '; 
}
$consulta =$mysqli -> query ($sql);
if ( $row = $consulta ->fetch_Assoc()) {
$nombre_materia = $row['nombre_materia'];
$nombre_categoria_curso = $row['nombre_categoria_curso'];
$nombre_ano_lectivo =$row['nombre_ano_lectivo'];
}
    
$html.='<style type="text/css">
    .logo {
    font-family: "Arial Narrow";
    font-size: 14px;
    margin: 0.2cm 20cm 0cm 3cm ;
}
.IEM{
margin: -1cm 5cm 1cm 2cm ;
margin-left:330px!important;
    font-size:24px;
    
}
  
</style>
<style media="print">
     #iemlogo {
      visibility:visible;
     }          
</style> 
<img style="visibility:hidden" id="iemlogo" class="logo" height="60" src="../../comun/sga-data/foto/hst.png"></img>
    <p class="IEM"><strong>
       <br/>
      </strong>   </p><h2 align="center">REPORTE ESTUDIANTES DE '.$nombre_materia.' ('.$nombre_categoria_curso.' / '.$nombre_ano_lectivo.') </h2>';
}

$html.='<table align="center" border="2"><tr>
<th>Identificación</th>
<th>nombre</th>
<th>Foto</th>
<th>usuario</th>
<th>Teléfono</th>
<th>Tipo de Sangre</th>
<th>Correo</th>
<th>Visitas</th>
<th>Estado</th>
<th>Ultimo ingreso</th>

</tr>';
require '../../comun/conexion.php';
require_once '../../comun/funciones.php';
#echo $sql;
if(isset($_POST['id_asignacion'])){
     $sql2='select * from usuario,inscripcion where
    inscripcion.id_estudiante = usuario.id_usuario and
    inscripcion.id_asignacion="'.$_POST['id_asignacion'].'" and usuario.estado="activo"   ';
}
if(isset($_GET['id_asignacion'])){
     $sql2='select * from usuario,inscripcion where
    inscripcion.id_estudiante = usuario.id_usuario and
    inscripcion.id_asignacion="'.$_GET['id_asignacion'].'"  and usuario.estado="activo"  ';
}
if(isset($_POST['id_inscripcion'])){
$sql2.=' and inscripcion.id_inscripcion="'.$_POST['id_inscripcion'].'"';
}
$sql2.=" group by usuario.id_usuario";
$sql2.=' order by usuario.apellido asc';
$consulta =$mysqli -> query ($sql2);
while ( $row = $consulta ->fetch_Assoc()) {
  $usuario=$row['id_usuario'];
#    $foto = '../../datos/foto/'.validarfoto($row['foto']);
 $foto = '../../sga-data/foto/'.$row['foto'];
$html.='<tr>
<td>'.$row['id_usuario'].'</td>
<td>'.$row['apellido'].' '.$row['nombre'].'</td>
<td><img  height="60" src="'.$foto.'"></img></td>
<td>'.$row['usuario'].'</td>
<td>';
if($row['telefono']<>"null"){
  $html.= $row['telefono'];
}

$html.='</td>
<td>'.$row['tipo_sangre'].'</td>
<td>';
if($row['correo']<>"null"){
  $html.= $row['correo'];
}

$html.='</td>
<td>'.$row['num_visitas'].'</td>
<td>'.$row['estado'].'</td>';

$fecha = new DateTime($row['ultima_sesion']) ;
$fechaordenada = $fecha -> format('d/m/Y');
$fechas = explode(" ", $row['ultima_sesion']);
$hora =date("g:i a",strtotime($fechas[0]));
if($row['ultima_sesion']=="0000-00-00 00:00:00"){
$html.="<td></td> ";
}
else{
$html.='<td>'.($fechaordenada).' <br/> '.$hora.'</td>';
}
$html.='</tr>';
}
$html.='</table>';
$html.='<footer style=" position: fixed; bottom: 0px; left: 0px; right: 0px; ">Generado por Guagua: '.date('d-m-Y / g:i:s a').'</footer>';
#echo $html;
if(isset($_POST['id_inscripcion'])){
  require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");

$estudiante = new persona($usuario);
$fecha = new Fecha;
$academico= New Academico($_POST['id_asignacion']);
$registro_asistencia=$estudiante->consultar_asistencia($_POST['id_asignacion']);
$teacher=$academico->consultar_docente($_POST['id_asignacion']);
$informacion_materia = $academico->consultar_materia($_POST['id_asignacion']);
$docente = new persona($teacher[0]['id_docente']);
$html.='<table style="margin-top:5%" border="2" align="center">
  <tr><th colspan="2">  Asistencia ';
$html.='</th></tr>';
$html.='<tr>
    <th>Fecha</th>
    <th>Asistencia</th>
  </tr>';
  $cantidad_clases= count($registro_asistencia);
  $datos['si']=0;
  $datos['no']=0;
       foreach ($registro_asistencia as $key => $value) { 
$html.='<tr>
    <td>'.$fecha->formato_fecha($value['fecha']).'</td>
    <td>'.$value['asistencia'].'</td></tr>';
    if($value['asistencia']=="si"){
     $datos['si']=$datos['si']+1;
    }
    if($value['asistencia']=="no"){
     $datos['no']=$datos['no']+1;
    }
}  
$html.='</table>';

#$datos['si']="70";
#$datos['no']="30";


}
else{

$fecha = new Fecha;
if (isset($_GET['id_asignacion'])) {
 $_POST['id_asignacion']= $_GET['id_asignacion'];
}
$academico= New Academico($_POST['id_asignacion']);
$fechas=$academico->fechas_asistencia($_POST['id_asignacion']);
$estudiantes= $academico->estudiantes_de_una_asignacion($_POST['id_asignacion']);
$html.='<div style="page-break-before:always"></div> ';
$html.=  "
<!--img style='position:absolute;margin-left:-45%;margin-top:-0.5%' class='logo' height='60' src='../../comun/sga-data/foto/hst.png'></img-->
<h1 id='titulo' align='center'>
Asistencia ".$nombre_materia.' ('.$nombre_categoria_curso.' / '.$nombre_ano_lectivo.')</h1>';
$html.= "<table border='2'>";
$html.=  "<tr>";
$html.=  "<th>Id estudiante</th>";
$html.=  "<th>Foto</th>";
$html.=  "<th>Nombre estudiante</th>";
foreach ($fechas as $key2 => $fechas_asistencia) {
$html.=  "<th title='".$fechas_asistencia['fecha']."'>";
$html.= Fecha::formato_fecha_corta($fechas_asistencia['fecha']);
$html.=  "</th>";
}
$html.=  "<th>SI</th>";
$html.=  "<th>No</th>";
$html.=  "<th>TOTAL</th>";
$html.= "</tr>";
 $datos['si']=0;
  $datos['no']=0;

foreach ($estudiantes as $key => $estudiante_asistencia) {
$estudiante = new persona($estudiante_asistencia['id_usuario']);
$html.= "<tr align='center'>";
$html.= "<td>";
$html.=$estudiante_asistencia['id_usuario'];
$html.= "</td>";
$html.= "<td style='background-color:white'>";
$foto = '../../sga-data/foto/'.$estudiante->foto;
$html.='<img  height="60" src="'.$foto.'"></img>';
$html.= "</td>";
$html.= "<td>";
$html.=$estudiante->apellido.' '.$estudiante->nombre;
$html.= "</td>";
$estudiante = new persona($estudiante_asistencia['id_usuario']);
$datoses[$estudiante_asistencia['id_usuario']]['si']=0;
$datoses[$estudiante_asistencia['id_usuario']]['no']=0;
foreach ($fechas as $key2 => $fechas_asistencia) {
$informacion_asistencia=$estudiante->consultar_asistencia($_POST['id_asignacion'],$fechas_asistencia['fecha']);
$html.=  "<td>";
if(!empty($informacion_asistencia)){
if($informacion_asistencia[0]['asistencia']=="si"){
     $datos['si']=$datos['si']+1;  
     $datoses[$estudiante_asistencia['id_usuario']]['si']=$datoses[$estudiante_asistencia['id_usuario']]['si']+1;  
    }
    if($informacion_asistencia[0]['asistencia']=="no"){
$html.="<strong>";
     $datos['no']=$datos['no']+1;
     $datoses[$estudiante_asistencia['id_usuario']]['no']=$datoses[$estudiante_asistencia['id_usuario']]['no']+1;
    }
if($informacion_asistencia[0]['asistencia']=="no"){
$html.=strtoupper($informacion_asistencia[0]['asistencia']);
}else{
  $html.=($informacion_asistencia[0]['asistencia']);
}
if($informacion_asistencia[0]['asistencia']=="no"){
$html.="</strong>";
}
}
$html.=  "</td>";

}
if(empty($datoses[$estudiante_asistencia['id_usuario']]['si'])){
  $datoses[$estudiante_asistencia['id_usuario']]['si']=0;
}
if(empty($datoses[$estudiante_asistencia['id_usuario']]['no'])){
  $datoses[$estudiante_asistencia['id_usuario']]['no']=0;
}
$html.=  "<td>".$datoses[$estudiante_asistencia['id_usuario']]['si']."</td>";
$html.=  "<td>".$datoses[$estudiante_asistencia['id_usuario']]['no']."</td>";
$total=$datoses[$estudiante_asistencia['id_usuario']]['si']+$datoses[$estudiante_asistencia['id_usuario']]['no'];
$html.="<td>".$total."</td>";

$html.=  "</tr>";
}
$html.= "</table>";
}
echo $html;
$comun=new Comun();
$persona=new Persona();
$persona->graficar($datos,"Asistencia general del curso","PieChart","1400","600");
$Academico= new Academico();
$reporte=$Academico->reporte_notas();
if(!empty($reporte)){
?>
<table border="2">
  <tr>
  <th>Taller</th>
  <th>Valoración</th>
  <th>Fecha</th>
  </tr>
  <?php
   foreach($reporte as $key =>$value) {?>
  <tr>
  <th><?php echo $value['nombre_actividad']; ?></th>
  <th><?php echo $value['valoracion']; ?></th>
  <th><?php echo $value['fecha_entrega']; ?></th>
  </tr>
<?php } } ?>
</table>
</div>
<?php
$contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
/*
$mipdf = new DOMPDF();
$mipdf ->set_paper("A4", "landscape");
$mipdf ->load_html($html,'UTF-8');
$mipdf ->render();
$mipdf ->stream('Acta.pdf', array("Attachment" => 0));
*/
?>
