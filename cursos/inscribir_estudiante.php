<?php
require("../comun/conexion.php");
 $sql="INSERT INTO `inscripcion`(`id_asignacion`, `id_estudiante`, `fecha_inscripcion`, `estado_inscripcion`) VALUES (".$_GET['asignacion'].",'".$_GET['estudiante']."','".date('Y-m-d')."','Aprobado')"; 
 $consulta = $mysqli->query($sql);
  ?>