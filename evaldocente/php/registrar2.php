<meta charset ="utf-8"/>
<?php
echo "<pre>";
#print_r($_POST);
echo "<pre>";
@session_start();
require ("conexion.php");
$session = $_COOKIE['session'];//optimizar
$asignacion = $_POST['asignacion'];
$respuesta = $_POST['respuesta'];
if (!isset($_POST['observacion'])){
	$observacion='';
}
else{
	$observacion = $_POST['observacion'];

}
  $sql ="INSERT INTO `respuesta_estudiante`(`id_estudiante`, `id_asignacion`, `session`, `observacion`) VALUES ('".$_SESSION['id_usuario']."','$asignacion','$session','$observacion')";
 $consulta =$mysqli -> query ($sql);

$sql = "SELECT `respuestas` FROM `evaluacion` WHERE `asignacion` = '".$asignacion."' and `session`  = '".$session."'";
$respuestasbd="";
$consulta =$mysqli -> query ($sql);
if ($row =  $consulta -> fetch_assoc()){
    $respuestasbd = json_decode($row['respuestas'],true);
}
foreach($respuesta as $id_pregunta => $escala){
$respuestasbd[$id_pregunta][$escala]++;
}
//print_r($respuestasbd);
$respuestasbd_json = json_encode($respuestasbd);
 $sql = "UPDATE `evaluacion` SET `respuestas`= '".$respuestasbd_json."' WHERE `asignacion` = '".$asignacion."' and `session`  = '".$session."'";
$consulta =$mysqli -> query ($sql);?>
<script type="text/javascript" >
  
  alert ('evaluacion registrada con exito');
      window.location="evaluacion.php";

</script>

<?php
if ($consulta){

}else{ ?>
  <script type="text/javascript" >
    
    alert ('no se ha registrado sus datos , verifique si usted ya realizo este proceso a este docente en la asignatura  e intente de nuevo');
      window.location="evaluacion.php";

  </script> 
  
<?php 

}
                                
#[respuesta] = Array ([1] => A [2] => N [3] => A [4] => S [5] => N [6] => N [7] => S [8] => A [9] => N [10]
?>