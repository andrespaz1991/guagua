<?php
//echo $_GET['v'];
//$id = $_GET['v'];
//$ruta = "documento"; // Indicar ruta donde se encuentra los archivos
//$filehandle = opendir($ruta); // Abrir directorio
//while (false !== ($entrada = readdir($filehandle))) { //Mientras exista, Abrir Archivos
//	  if ($entrada != "." && $entrada != ".." ) { //validación para evitar error
//echo $archivo ='documento/'.$_GET['v'];

$ruta_archivo = $_GET['v'];
$info = pathinfo($ruta_archivo);
$extension = $info['extension'];

header('Content-type: application/pdf');
header('Content-Disposition: attachment; filename="actividad.'.$extension.'"');
readfile($_GET['v']);
?>