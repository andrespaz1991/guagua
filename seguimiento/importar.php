<?php

require_once '../comun/conexion.php';  

#require '../comun/funciones.php'; 

require '../comun/autoload.php';  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$bandera_de_matriculacion = false;
$archivo= $_FILES['datos']['tmp_name']; 
$TablayCamposbd = '  `usuario` (`id_usuario`,
 `nombre`,
 `apellido`,
 `telefono`,
 `direccion`,
 `correo`,
 `usuario`,
 `genero`,
 `clave`,
 `mascota`,
 `rol`, 
 `foto`,  
 `ultima_sesion`,
 `num_visitas`,

 `puntos`,

 `estado`, 

 `tipo_sangre`, `observaciones`)';

if (($archivo_abierto = fopen($archivo, "r")) !== FALSE) { 
$insertar ="INSERT INTO $TablayCamposbd Values"; 
$ValoresDeInsert =""; 
$conta=0;

 while ($celdas = fgetcsv ($archivo_abierto,2000, ";")){
	$conta++;

	 if($conta==1){ $cabecera = $celdas[0]; 	}

 if($cabecera =="id_usuario"){
		$ced=$celdas[0];

}elseif((isset($cabecera) and $cabecera=="null") or (isset($cabecera) or $cabecera=="")  ){

	$ced="n/a";

	$ced.=rand();

}

if(isset($celdas[18]) and $celdas[18]<>"null" ){

	$bandera_de_matriculacion=true;

	$inscripciones[$ced]=$celdas[18];

} // verificar si hay matriculas

if(isset($celdas[7]) and $celdas[7]=="" ){

$persona =new persona();

$celdas[7]=$persona->probabilidad_sexo($celdas[1]);

echo $celdas[1].'->'.$celdas[7].'<br>';

if($celdas[7]=="m"){

	$celdas[11] = "user-icon.png";

}else{

	$celdas[11] = "user-iconf.png";

}



}




$persona="select * from usuario where nombre='".mb_convert_case(strtolower($celdas[1]), MB_CASE_TITLE, "UTF-8")."' and apellido='".mb_convert_case(strtolower($celdas[2]), MB_CASE_TITLE, "UTF-8")."'"  ;
$consulta = $mysqli->query($persona); 

$row=$consulta->fetch_assoc();

if(empty($row) and $celdas[1]<>"estudiante" ){

$ValoresDeInsert = $ValoresDeInsert.'("'.$ced.'","'.

mb_convert_case(strtolower($celdas[1]), MB_CASE_TITLE, "UTF-8").'","'.$celdas[2].'","'.$celdas[3].'","'.$celdas[4].'","'.$celdas[5].'","'.$celdas[6].'","'.$celdas[7].'","'.$celdas[8].'","'.$celdas[9].'","'.$celdas[10].'","'.$celdas[11].'","'.$celdas[12].'","'.$celdas[13].'","'.$celdas[14].'","'.$celdas[15].'","'.$celdas[16].'","'.$celdas[17].'"),';//  ValoresDeInsertUES (celda[n] )";  //determinamos la cantidad y posiciones de celda de un registro

}

				} 

				

				



$ValoresDeInsertSinComaAlFinal = substr ($ValoresDeInsert, 0, strlen($ValoresDeInsert) - 1); // eliminar el ultimos punto y coma del inset

 $sql = $insertar.''.$ValoresDeInsertSinComaAlFinal.''; //Unimos la consulta insert y los valores extraidos de la hoja de calculo para ser insertados



echo $sql;

 $insertar = $mysqli -> query ($sql); //Enviamos la consulta a base de datos



 if ($mysqli->affected_rows > 0) { 

 	if($bandera_de_matriculacion==true){

	foreach ($inscripciones as $estudiante => $asignacion) {

		inscribir_estudiante($asignacion,$estudiante);

		}		

 	}





 	?> <!-- si se inserto correctamente -->

<script type="text/javascript">

	alert ('datos importados correctamente'); //presentamos un mensaje de exito

	window.location  = '../usuario/usuario.php?u=estudiante' ;//

</script>

							<?php	}

else {?> <!-- de lo contrarioe -->

<script type="text/javascript">

	alert ('verificar información'); //presentamos un mensaje de verificación

	window.location  = '../usuario/usuario.php' ;//



</script>

							<?php

}



} //cerramos el if de la linea 5

    ?>