<?php
require 'conexion.php';  //llamamos archivo de conexion para insertar datos en bd
if (($fichero = fopen("r.csv", "r")) !== FALSE) {
$nombres_campos = fgetcsv($fichero, 0, ";", "\"", "\"");
$val ="";
$contador = '0';
  while ($data = fgetcsv ($fichero, 1000, ";")){ 
  $data[0] ; 
   $r = 'select * from estudiante where identificacion = "'.$data[0].'"' ;
require 'conexion.php';
$consultar_estudiante = $mysqli -> query ($r);
if ($rew = $consultar_estudiante -> fetch_assoc()){
	$vfr =$contador++ ;
 echo $q = ' UPDATE estudiante SET 
 celular="'.$data[1].'",
 rq3="'.$data[2].'",
 enviado="'.$data[3].'",
 aprobado="'.$data[4].'",
 convenio="'.$data[5].'",
 acta_inicio="'.$data[6].'",
 acta_finalizacion="'.$data[7].'",
 convocatoria="'.$data[8].'",
 proyecto="'.$data[9].'",
 `entidad`="'.$data[10].'",
 `representante`="'.$data[11].'"
 where identificacion  ='.$data[0].';' ;

//$q = 'insert into estudiante (identificacion,nombre) VALUES("'.$data[0].'","'.$data[1].'")';
 //$we = $mysqli -> query ($q);
if ($mysqli->affected_rows > 0) {
}
else {
	echo $q.'<br>' ;
}

	//echo $data[0].'nombre'.$data[1].'<br>';
}
else {
	
}
 
//$s ='INSERT INTO seguimiento(identificacion,cita,hora,proyecto,observaciones,asistio,listo_para_enviar,asesoria_tecnica)  VALUES';

// $sql="INSERT INTO personal(nombre,edad,profesion) VALUES ('$data[0]','$data[1]','$data[2]')";  
//  $val = $val.'("'.$data[0].'","'.$data[2].'","'.$data[3].'","'.$data[4].'","'.$data[5].'","'.$data[6].'","'.$data[7].'","'.$data[8].'"),';
                } 
//$dec = substr ($val, 0, strlen($val) - 1); // eliminar el ultimos punto y coma del inset
//echo $sql = $s.''.$dec.'';
//$insertar = $mysqli -> query ($sql);
 ?>


<?php                  
}
    ?>
