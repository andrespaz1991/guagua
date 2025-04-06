<?php

require("../comun/autoload.php");

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
require("../clases/Academico.Class.php");
$academico=new academico();

$horarios= ($academico->consultar_horario_completo());

if (!empty($horarios)) {

$horas=$academico->consultar_horas_horario();

 ?>

    <table class="table-responsive" align="center" border="4" bordercolor="purple"

    cellpadding="10" cellspacing="20">

    <tr><th colspan="9" >Horario Semanal</th></tr>

<tr>

       <th bgcolor="yellow">Hora Inicio</th>

        <th bgcolor="yellow">Hora Fin</th>

        <th>Lunes</th>

        <th>Martes</th>

        <th>Mi&eacute;rcoles</th>

		<th>Jueves</th>

		<th>Viernes</th>

		<th>Sabado</th>

		<th>Domingo</th>

</tr>

	<?php

  foreach ($horas as $key => $hora) {

        echo "<tr>";

 echo '<td>'.Fecha::formato_hora($hora["hora_inicio"]).'</td>';   

 echo '<td>'.Fecha::formato_hora($hora["hora_fin"]).'</td>'; 

 ?>

 <td >

     <?php  foreach ($horarios as $keys => $horass) {

 if($horass['dia']=="lunes" and $horass['hora_inicio']==$hora["hora_inicio"]){    

echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

   echo $horass['nombre_materia'] ; 

echo "</span>";

}

} ?>

 </td>

 <td> <?php  foreach ($horarios as $keys => $horass) {

 if($horass['dia']=="martes" and $horass['hora_inicio']==$hora["hora_inicio"]){    

echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

   echo $horass['nombre_materia'] ; 

echo "</span>";

}

} ?></td>

 <td> <?php  foreach ($horarios as $keys => $horass) {

 if($horass['dia']=="miercoles" and $horass['hora_inicio']==$hora["hora_inicio"]){   

echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

   echo $horass['nombre_materia'] ; 

echo "</span>";     }

} ?></td>

 <td><?php  foreach ($horarios as $keys => $horass) {

 if($horass['dia']=="jueves" and $horass['hora_inicio']==$hora["hora_inicio"]){    

  echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

   echo $horass['nombre_materia'] ; 

echo "</span>";

    }

} ?></td>

 <td><?php 
    $materia= array();
 foreach ($horarios as $keys => $horass) {
    echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

    if($horass['dia']=="viernes" and $horass['hora_inicio']==$hora["hora_inicio"]){ 
      if(!in_array($horass['id_asignacion'],$materia)){
         $materia[]=$horass['id_asignacion'];
       echo $horass['nombre_materia'] ;
      }else{
         echo $horass['nombre_materia'] ;
         echo "no";
      }
      
   

echo "</span>";

  }

} ?></td>

 <td><?php  foreach ($horarios as $keys => $horass) {

 if($horass['dia']=="sabado" and $horass['hora_inicio']==$hora["hora_inicio"]){    

echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

   echo $horass['nombre_materia'] ; 

echo "</span>";



    }

} ?></td>

 <td><?php  foreach ($horarios as $keys => $horass) {

 if($horass['dia']=="domingo" and $horass['hora_inicio']==$hora["hora_inicio"]){     

echo "<span title=".Fecha::formato_fecha_corta($horass['fecha_inicio']).'-'.Fecha::formato_fecha_corta($horass['fecha_fin'])." >";

   echo $horass['nombre_materia'] ; 

echo "</span>"; 

}

} ?></td>

</tr>

<?PHP  

    }

     ?>

</table>

<!-- ------------------------------------------------->

<?php } else {?>

<form action="" method="post">

<input type="hidden" name="asignacion" value="<?php echo $_GET['asignacion'] ?>">

<label><input name="horario[]" type="checkbox" value="lunes"> lunes</label>

<label><input name="horario[]" type="checkbox" value="martes"> martes</label>

<label><input name="horario[]" type="checkbox" value="miercoles"> miercoles</label>

<label><input name="horario[]" type="checkbox" value="jueves"> jueves</label>

<label><input name="horario[]" type="checkbox" value="viernes"> viernes</label>

<label><input name="horario[]" type="checkbox" value="sabado"> sabado</label>

<label><input name="horario[]" type="checkbox" value="domingo"> domingo</label><br>

<label>Fecha inicio:<input type="date" name="fecha_inicio" value="<?php echo date('Y-m-d'); ?>"></label>

<label>Fecha fin:<input type="date" name="fecha_fin" value="<?php echo date('Y-m-d'); ?>"></label>

<br>

<label>hora inicio:<input type="time" name="hora_inicio" value="<?php echo date('Y-m-d'); ?>"></label>

<label>hora fin:<input type="time" name="hora_fin" value="<?php echo date('Y-m-d'); ?>"></label>

<br><label><input required name="seguro" type="checkbox" value="seguro"> seguro</label><br>

<input type="submit" name="" value="Guardar">

</form>

<?php

}

if (!empty($_POST['horario'])) {

$academico=new academico;

foreach ($_POST['horario'] as $key => $value) {

	$academico->insertar_horario($value);

}

}

$contenido = ob_get_clean();

require ("../comun/plantilla.php");

?>