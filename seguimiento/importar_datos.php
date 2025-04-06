<?php
require 'conexion.php';  //llamamos archivo de conexion para insertar datos en bd
if (($fichero = fopen("archivo2.csv", "r")) !== FALSE) {
$nombres_campos = fgetcsv($fichero, 0, ",", "\"", "\"");
$val ="";
$contador = '0';
 while ($data = fgetcsv ($fichero, 1000, ";")){  
 echo $r = 'select * from estudiante where identificacion = "'.$data[0].'"' ;
require 'conexion.php';
$consultar_estudiante = $mysqli -> query ($r);
 while ($data = $consultar_estudiante->fetch_assoc()) {
 	
 	$vfr =$contador++ ;
	//echo $data[0].'nombre'.$data[1].'<br>';

echo	$q = 'insert into estudiante (identificacion,nombre) VALUES("'.$data[0].'","'.$data[1].'")';
	$insertar_estudiante =$mysqli -> query ($q);

 $s =' INSERT INTO seguimiento(identificacion,cita,hora,proyecto,observaciones,asistio,listo_para_enviar,asesoria_tecnica)  VALUES';

// $sql="INSERT INTO personal(nombre,edad,profesion) VALUES ('$data[0]','$data[1]','$data[2]')";  
  $val = $val.'("'.$data[0].'","'.$data[2].'","'.$data[3].'","'.$data[4].'","'.$data[5].'","'.$data[6].'","'.$data[7].'","'.$data[8].'");';
               

$dec = substr ($val, 0, strlen($val) - 1); // eliminar el ultimos punto y coma del inset
echo $sql = $s.''.$dec.'';
$insertar = $mysqli -> query ($sql);
 }
}
}
 ?>


