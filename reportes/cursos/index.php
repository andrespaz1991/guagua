<?php 
ob_start();
require("../../comun/config.php");
require("../../comun/funciones.php");
$sql_ano_lectivo ='select * from ano_lectivo';
 ?>
 <h1>Reporte Individual  </h1>
 <form>
     <input type="text" placeholder="Identificación" name="estudiante"/>
<select id="ano_lectivo" onchange="consultar_asignaciones();" name="ano_lectivo">
    <?php foreach (consultar_datos($sql_ano_lectivo) as $datos) {
echo '<option value='.$datos[0].'>'.$datos[1].'</option>  ';
  } ?>
    
</select>
<select name="asignaciones">
    
</select>


     <input type="submit" value="Submit"/>
 </form>
 
 
 <?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
 ?>