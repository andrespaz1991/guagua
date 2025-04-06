<?php
if (isset($_GET['del'])){
	require 'conexion.php';
       $fecha_actual = date ('Y-m-d');
  $hora_actual = date('g:i:s a');
  $año_actual = date('Y');
//Instrucción SQL que permite eliminar en la BD
 $sql = 'UPDATE `config` SET `fecha_fin`="'.$fecha_actual.'",`hora_fin`="'.$hora_actual.'",`estado`=0 WHERE id_config="'.$_GET['del'].'"';


#$sql = 'DELETE FROM config WHERE id_config="'.$_GET['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
header ("location:config.php");
?>
Evaluación cerrada
<?php
}else{
    header ("location:config.php");

?>
No se ha podido realizar el cierre de la evaluación 
<?php
}
}
?>
