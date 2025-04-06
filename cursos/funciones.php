<?php
function consultar_datos($consulta ){
require($_SERVER['DOCUMENT_ROOT']."/comun/conexion.php");
$gconsulta_red = $mysqli->prepare($consulta);
$gconsulta_red->execute();
$arraydedatos = $gconsulta_red->get_result();
$datos = $arraydedatos->fetch_all();
return $datos;
}
//actualizar_inscripcion();
function actualizar_inscripcion(){
require($_SERVER['DOCUMENT_ROOT']."/comun/conexion.php");
 $sql = 'select * from estudiante' ;
 $consulta = $mysqli -> query ($sql);
while ($row = $consulta ->fetch_assoc()){
    $asignacion =saber_categoria_asignacion_academica ( $row['categoria_curso']);
$sqlactualizarnscripcion= 'UPDATE `inscripcion` SET `categoria_curso`="'.$row['categoria_curso'].'" where id_estudiante = "'.$row['id_estudiante'].'"';
#echo $sqlactualizarnscripcion.'<br>';
$actualizar = $mysqli ->query($sqlactualizarnscripcion);
}
}
function saber_categoria_asignacion_academica ($categoria_curso){
require '../comun/conexion.php';
 $consulta = 'select * from asignacion where id_categoria_curso = "'.$categoria_curso.'"';
 $consultaquery= $mysqli ->query( $consulta);	
 if ($rowcategoria = $consultaquery ->fetch_assoc()){
 return $rowcategoria['id_asignacion'];
}
}



?>