
<?php
//Saber materia
//Asignacion acádemica (si existe el docente sino null)
//Seleccionamos las actividades
//Seleccionamos los red 
//
require 'conexion.php';
$materia ='35';
$sql_materia ='SELECT  * FROM `materia` WHERE id_materia = "'.$materia.'"' ;
$consulta_materia = $mysqli -> query($sql_materia);
while ($row_materia= $consulta_materia ->fetch_assoc()){
   $arraydemateria[] = $row_materia;
}
echo '------------Materia-----';

echo '<pre>';
print_r($arraydemateria);
echo '</pre>';

echo $sql_materia='INSERT INTO `materia`(`id_materia`, `nombre_materia`, `obligatoria`, `area`, `icono_materia`) VALUES ("'.$arraydemateria[0]['id_materia'].'","'.$arraydemateria[0]['nombre_materia'].'","'.$arraydemateria[0]['obligatoria'].'","'.$arraydemateria[0]['area'].'","'.$arraydemateria[0]['icono_materia'].'")';

$sql_asignacion ='SELECT  * FROM `asignacion_academica` WHERE asignacion_academica.id_materia = "'.$arraydemateria[0]['id_materia'].'"' ;
$consulta_asignacion = $mysqli -> query($sql_asignacion);
while ($row_asignacion= $consulta_asignacion ->fetch_assoc()){
   $arrayasignacion[] = $row_asignacion;
   
}
echo '<br>------------Asignacion-----';

echo '<pre>';
print_r($arrayasignacion);
echo '</pre>';

 $sqlasignacion='INSERT INTO `asignacion_academica`(`id_asignacion`, `id_materia`, `descripcion`, `id_docente`, `id_categoria_curso`, `ano_lectivo`, `visible`) VALUES ("'.$arrayasignacion[0]["id_asignacion"].'","'.$arrayasignacion[0]["id_materia"].'","'.$arrayasignacion[0]["descripcion"].'","'.$arrayasignacion[0]["id_docente"].'","'.$arrayasignacion[0]['id_categoria_curso'].'","'.$arrayasignacion[0]['ano_lectivo'].'","'.$arrayasignacion[0]['visible'].'")'; 
 


echo '<br>------------material-----';

$sql_material ='SELECT  * FROM `material` WHERE id_asignacion = "'.$arrayasignacion[0]['id_asignacion'].'"' ;
$consulta_material = $mysqli -> query($sql_material);
$sql_insertar_material='';
while ($row_material = $consulta_material ->fetch_assoc()){
   $arraydemateriales[] = $row_material;
   $red[]=$row_material['id_red'];
     $sql_insertar_material.=' INSERT INTO `material`(`id_material`, `id_asignacion`, `fecha_publicacion`, `hora_publicacion`, `id_red`, `nombre_material`, `Observaciones`, `adjunto`, `evaluable`, `fecha_entrega`, `hora_entrega`, `periodo`, `visible`, `cuestionario`, `id_cuestionario`, `foro`, `id_foro`) VALUES ("'.$row_material['id_material'].'","'.$row_material['id_asignacion'].'","'.$row_material['fecha_publicacion'].'","'.$row_material['hora_publicacion'].'","'.$row_material['id_red'].'","'.$row_material['nombre_material'].'","'.$row_material['Observaciones'].'","'.$row_material['adjunto'].'","'.$row_material['evaluable'].'","'.$row_material['evaluable'].'","'.$row_material['fecha_entrega'].'","'.$row_material['fecha_entrega'].'","'.$row_material['hora_entrega'].'","'.$row_material['periodo'].'","'.$row_material['visible'].'","'.$row_material['cuestionario'].'","'.$row_material['cuestionario'].'","'.$row_material['id_cuestionario'].'","'.$row_material['foro'].'","'.$row_material['id_foro'].'")';
}

echo '<pre>';
print_r($arraydemateriales);
echo '</pre>';
echo $sql_insertar_material;

foreach($red as $clavered => $valored){
  $sql_red='select * from red where id_red="'.$valored.'"';
  $consulta_red = $mysqli ->query($sql_red);
  while($rowred = $consulta_red -> fetch_assoc()){
      $rutasred[]=$rowred;
      $enlaces[]=$rowred['enlace'];
}
}
echo '<br>------------red-----';
$enlaces2= array_unique($enlaces);
echo '<pre>';
print_r($enlaces2);
echo '</pre>';

$sqlinsertared ='INSERT INTO `red`(`id_red`, `titulo_red`, `idioma_red`, `contexto`, `descripcion`, `palabras_clave`, `nivel_eductivo`, `autor`, `responsable`, `formato`, `tipo_interacción`, `tipo_recurso_educativo`, `dificultad`, `fecha`, `estrellas`, `enlace`, `scorm`, `adjunto`, `icono_red`, `materia_red`, `cantidad_estrellas`) VALUES ([value-1],[value-2],[value-3],[value-4],[value-5],[value-6],[value-7],[value-8],[value-9],[value-10],[value-11],[value-12],[value-13],[value-14],[value-15],[value-16],[value-17],[value-18],[value-19],[value-20],[value-21])';



?>