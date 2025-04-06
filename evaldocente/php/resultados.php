<?php 
date_default_timezone_set('America/Bogota');?>
<?php
require 'conexion.php';
 echo $sqlultimaeval = 'select * from config where estado=1 or estado=0 order by id_config desc limit 1';
$cosnultaultimoeval = $mysqli->query ($sqlultimaeval);
if ($rowultimoeval =$cosnultaultimoeval->fetch_assoc()){
  setcookie('ultimaeval',$rowultimoeval['id_config'])  ;
}
ob_clean();
ob_start();
?>
<center>
<meta charset="utf-8"/>
<script type="text/javascript" src="../../comun/js/japi.js"></script>
<?php
function datarow_encode($array){
    $mivar = '';
    foreach ($array as $id => $valor){
    $mivar .= "data.addRows([         	
              ['$id',$valor],
               ]);
    ";
    }
    return $mivar;
}
function graficar($datos,$id,$ntipo=1,$tipo=array('ColumnChart','PieChart'),$opciones_generales="{'title':'Grafica general de la pregunta',
                       'width':600,
                       'height':500,
                       'backgroundColor': 'transparent',
}"){
return '
<!--script type="text/javascript" src="../../comun/js/japi.js"></script-->
<script type=\'text/javascript\'>
      google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
      	var data = new google.visualization.DataTable();
        data.addColumn(\'string\', \'Topping\');
        data.addColumn(\'number\', \'Slices\');
'.datarow_encode($datos).'
var options = '.$opciones_generales.';
var chart = new google.visualization.'.$tipo[$ntipo].'(document.getElementById(\''.$id.'\'));
        chart.draw(data, options);
      }
    </script>
<div style="margin-top:-6%;margin-bottom:-8%" id="'.$id.'"></div>';
}
#funciones para graficas fin
?>
<?php

function cpdocente ($id_asignacion,$nombre_asignatura,$nombre_curso="",$nombre_docente,$id_docente) {//cpdocente
require_once("../../comun/autoload.php");
$persona=new Persona($id_docente);
?>
<img style="margin-left:60%;position:absolute;margin-top:10%; border-radius:100%" width="150px" src="<?php echo SGA_COMUN_SGA_DATA.'/'.$persona->foto ?>">
<?php
require 'conexion.php';
  $sql ='select * from evaluacion where asignacion="'.$id_asignacion.'" and session ="'.$_COOKIE['ultimaeval'].'" ' ;
$consulta = $mysqli -> query ($sql);
$evaluaciones = array();
while ($row = $consulta -> fetch_assoc() ) {
echo  "El docente <strong>".$persona->nombre.' '.$persona->apellido."</strong> en la asignatura <strong>".$nombre_asignatura.'</strong> del curso <strong>' .$nombre_curso. '</strong> en la session de evaluación '.$_COOKIE['ultimaeval'].' presenta los siguientes resultados'.'<br>';

$sqlescala='select * from  escala';
$consultaescalasql = $mysqli-> query($sqlescala);
while ($rowescala=$consultaescalasql->fetch_Assoc()){
#echo $rowescala['valor'].':'.$rowescala['descripcion'].'<br>';
}

//    echo $row['respuestas'].'<br>';
//var_dump(json_decode($row['respuestas']));
$evaluacion = json_decode($row['respuestas'],true);
$evaluaciones[]=$evaluacion;
echo "<pre>";
//print_r($evaluacion);
foreach($evaluacion as $pregunta=>$valor)
	{
	    require 'conexion.php';
	     $sqlpregunta='select * from  pregunta';
$consultapreguntaa = $mysqli-> query($sqlpregunta);
while ($rowpregunta=$consultapreguntaa->fetch_Assoc()){
    if ($rowpregunta['id_pregunta']==$pregunta){
echo "<hr style='height: 2px;
  background-color: black;'></hr>";

echo "En el criterio ".$pregunta.' : <strong>'.$rowpregunta['pregunta'].' </strong>' ;
    }
}
	$mistotales =0 ;
	foreach($valor as $opcion=>$valor2)
	{
	    $sqlescala='select * from  escala';
	    	 $mistotales +=$valor2;
$consultaescalasql = $mysqli-> query($sqlescala);
while ($rowescala=$consultaescalasql->fetch_Assoc()){
    if ($rowescala['valor']==$opcion){
    	#echo "la respuesta " .$rowescala['descripcion'].'('.$rowescala['valor'].") tiene " . $valor2.' voto(s)';
	#echo "<br>"; 
    }
}
 
	}
    echo graficar($valor,'chart_div_'.$pregunta."_".$id_asignacion);
}
 #echo '<br>';
 echo  '<h1>'."Resultados Generales".'</h1>'.'<br>';
echo '<h3>'."total de participantes ".$mistotales.'</h3>';
  }//consulta cp
if (!empty($evaluacion)) {

$array_evaluacion = $evaluacion;
foreach($array_evaluacion as $jugada){
    foreach ($jugada as $clave=>$valor) {
        $totales[$clave]=+$valor; 
    }
}

#print_r($totales);

foreach ($totales as $opcion => $valor){
    echo "En la opcion ".$opcion." el valor es ".$valor.'<br>' ;
}
echo graficar($totales,'chart_div_tot_'.$id_asignacion,1);
}//fp
}//fin cpdocente
function cpmateriadeundocente($id_docente ='',$ano_lectivo='') {
  @session_start();  if (isset($_SESSION['identificacion_usu'])) {
      
  }
  else {
      @session_start();
      $id_docente = $_SESSION['id_usuario'];
      
  }
require 'conexion.php';
$sqlcp = 'select * from asignacion inner join materia on 
asignacion.id_asignatura =materia.id_materia 
inner join categoria_curso on
 categoria_curso.id_categoria_curso = asignacion.id_categoria_curso

' ;
$cosnutlacp = $mysqli -> query ($sqlcp);
while ($rowcp = $cosnutlacp ->fetch_assoc()) {
 $rowcp['nombre_curso']="";
  $rowcp['nombre']="";
cpdocente($rowcp['id_asignacion'],$rowcp['nombre_materia'],$rowcp['nombre_categoria_curso'],$rowcp['nombre'],$rowcp['id_docente']);
}
}
@session_start();
if(isset($_GET['id_docente'])or (isset($_SESSION['id_usuario'])) ){
if (isset($_GET['id_docente'])) {   $docente =$_GET['id_docente']; }else {
 @session_start(); $docente = $_SESSION['id_usuario'];
}

require 'conexion.php';
$ano_actual = date ('Y');
 $sqlanolectivo = 'select * from ano_lectivo where nombre_ano_lectivo ="'.$ano_actual.'"' ;
$sqlanolectivo = $mysqli ->query($sqlanolectivo);
if ($rowano = $sqlanolectivo ->fetch_assoc()){
  $lectivo =  $rowano['id_ano_lectivo'] ;
}
#echo $lectivo;
cpmateriadeundocente($docente,$lectivo);
}else{
cpmateriadeundocente($docente,$lectivo);
}


?>
</center>
<style>
    div {
    margin: 0 auto;
    }
</style>
<script>
    div.rect.fill = ""
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
?>
