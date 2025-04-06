<?php
require("../comun/autoload.php");
ob_start();
$ruta = "D:\Cuadernillo\JAOMER IVAN ROSERO BRAVO_10832_assignsubmission_file_";
$estudiante="JAOMER IVAN ROSERO BRAVO";
$carpeta_evaluar="";
$total_ejercicios=18;
$dir= $ruta."\\".$carpeta_evaluar;
$ver=1;
$insertar=1;
$valoracion_maxima="5";
$academico= new academico();

#esto es en caso de querer guardar los ejercicios
$academico->obtener_estructura_directorios($ruta,"guagua",$ver=1,$insertar=1,$ruta);

############ Valorar todos los puntos
############ insertar directorio estudiantes
#$arbol = $academico->obtener_estructura_directorios($dir,$estudiante,$ver,$insertar,$ruta);
############ 


############ Valorar todos los puntos
echo "<h2>Revisión a ".$estudiante."</h2>";
$valoracion_final=0;
$excepciones=array(15,16,17,18);
for ($i=1; $i <=$total_ejercicios ; $i++) { 
if(in_array($i,$excepciones)){
    $puntacion=5;
    echo 'Punto'.$i.' => '.round($puntacion,1).'<br>';
    $valoracion_final=$valoracion_final+$puntacion/$total_ejercicios;
}else{
    $puntacion= $academico->calificador($estudiante,$i,$valoracion_maxima);
    echo 'Punto'.$i.' => '.round($puntacion,1).'<br>';
    $valoracion_final=$valoracion_final+$puntacion/$total_ejercicios;
}
}
echo "<h3> Valoración ".round($valoracion_final,1).'</H3>';
echo "<hr></hr>";


############ Estructura de carpetas estudiante
echo "<hr></hr>";
echo "<div style='position:Absolute;margin-left:60%'>";
echo "<h1>Carpetas ".$estudiante."</h1>";
$arbol = $academico->obtener_estructura_directorios($dir,$estudiante,$ver,$insertar=0,$ruta);
echo "</div>";
############ 

############ Estructura de carpetas correcta
echo "<hr></hr>";
echo "<h1>Carpetas Correctamente </h1>";
$ruta = "D:\Camila Timaran";
$arbol = $academico->obtener_estructura_directorios($ruta,$usuario="guagua",$ver=1,$insertar=0,$ruta);
############ 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>