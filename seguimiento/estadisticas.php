<?php 

ob_start(); ?>
<style type="text/css">
	.vertical-menu {
  width: 600px; /* Set a width if you like */
    position: absolute;
}

.vertical-menu a {

  color: black; /* Black text color */

  display: inline-block; /* Make the links appear below each other */

  padding: 12px; /* Add some padding */

  text-decoration: none; /* Remove underline from links */

}

.botones {

      background-color: white; 

      color: black; 

      border: 2px solid #008CBA;

}

</style>
<div class="vertical-menu">
<a class="botones" href="citas.php">Citas</a> 
<a class="botones" href="seguimiento.php">seguimiento</a> 
<a class="botones" href="estadisticas.php">estadisticas</a> 
</div><br><br><br>
<?php

require('../comun/autoload.php'); 

$persona =new persona();

$personas=$persona->distribución_genero();

$tipo="PieChart"; #LineChart ColumnChart PieChart ColumnChart

$datos=$personas[0];

$titulo="Estudiante por género";

if(!empty($datos)){

$persona->graficar($datos,$titulo);

}else{

	echo "No hay datos";

}

$datos=$persona->estudiantes_con_mayor_Asesoria();

$tipo="ColumnChart"; #LineChart ColumnChart PieChart ColumnChart



$titulo="Estudiante con más asesorias";

$persona->graficar($datos,$titulo,$tipo);

?>



 <?php $contenido = ob_get_contents();

ob_clean();

include ("../comun/plantilla.php");

?>