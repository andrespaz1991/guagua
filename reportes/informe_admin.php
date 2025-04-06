<?php
@session_start();
require("../comun/autoload.php");
$asignatura=20;
$asistencia_genero= $academico->asistencia_genero($asignatura);
$persona->graficar($asistencia_genero[0],"Estudiantes por género");
$contenido = ob_get_clean();
require '../comun/plantilla.php'; 
?>
