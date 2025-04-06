<?php 

ob_start();

echo '<center>';

require("../comun/conexion.php");
?>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.7.0/tinymce.min.js" integrity="sha512-XaygRY58e7fVVWydN6jQsLpLMyf7qb4cKZjIi93WbKjT6+kG/x4H5Q73Tff69trL9K0YDPIswzWe6hkcyuOHlw==" crossorigin="anonymous"></script>
  

<?php
#eliminar

if ((isset($_GET['estado'])) and $_GET['estado']<>""){

 $sql = 'UPDATE edunotas SET fijar="0" WHERE id_nota='.$_GET['estado'].'';



if ($actualizar = $mysqli->query($sql)) {

 /*Validamos si el registro fue ingresado con éxito*/

echo 'Modificación exitosa';

echo '<meta http-equiv="refresh" content="1; url=../index.php" />';

 }else{ 

echo 'Modificacion fallida';

}

echo '<meta http-equiv="refresh" content="2; url=../index.php" />';

} /*fin Actualizar*/ 





if ((isset($_POST['del'])  or isset($_GET['eliminar']))){

if (isset($_GET['eliminar'])){

 $_POST['del']=$_GET['eliminar'];

}

$sql = 'DELETE FROM edunotas WHERE id_nota="'.$_POST['del'].'"';

 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/

if ($eliminar = $mysqli->query($sql)){

 /*Validamos si el registro fue eliminado con éxito*/ 

echo '

Registro eliminado

<meta http-equiv="refresh" content="1; url=edunotas.php" />

'; 

}else{

?>

Eliminación fallida, por favor compruebe que la usuario no esté en uso

<meta http-equiv="refresh" content="2; url=edunotas.php" />

<?php 

}

}



#if ($_POST['submit']=="Modificar"){

if ((isset($_POST['submit']) and $_POST['submit']=="Modificar") or isset($_GET['idnota'])){

 if(isset($_GET['idnota'])) {

$_POST['cod']=$_GET['idnota'];

 }

$sql = "SELECT * FROM `edunotas` WHERE id_nota ='".$_POST['cod']."' Limit 1"; 

$consulta = $mysqli->query($sql);

 /*echo $sql;*/ 

$row=$consulta->fetch_assoc();



$textoh1 ="Modificar";

$textobtn ="Actualizar";

echo '<form id="form1" name="form1" method="post" action="edunotas.php">

<h1>'.$textoh1.'</h1>';

?>





<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_nota']))  echo $row['id_nota'] ?>" size="120" required></p>

<?php 

echo '<p><input class=""name="id_nota"type="hidden" id="id_nota" value="';if (isset($row['id_nota'])) echo $row['id_nota'];echo '"';echo '></p>';







echo '<p><label for="id_asignacion">Id Asignacion:</label>';

$sql3= "SELECT * FROM asignacion,materia,ano_lectivo WHERE

asignacion.ano_lectivo = ano_lectivo.id_ano_lectivo and

asignacion.id_asignatura = materia.id_materia order by ano_lectivo.nombre_ano_lectivo desc;";





echo '<select class="" name="id_asignacion" id="id_asignacion"  required>';

echo '<option value="">Seleccione una opci&oacute;n</option>';

$consulta3 = $mysqli->query($sql3);

while($row3=$consulta3->fetch_assoc()){

echo '<option value="'.$row3['id_asignacion'].'"';if (isset($row['id_asignacion']) and $row['id_asignacion'] == $row3['id_asignacion']) echo " selected ";echo '>'.$row3['id_asignacion'].' '.$row3['nombre_materia'].' ('.$row3['nombre_ano_lectivo'].')</option>';

}

echo '</select></p>';

echo '<p><label for="fecha_nota">Fecha Nota:</label><input class=""name="fecha_nota"type="date" id="fecha_nota" value="';if (isset($row['fecha_nota'])) echo $row['fecha_nota'];echo '"';echo ' required ></p>';

echo '<p><label for="hora_nota">Hora Nota:</label><input class=""name="hora_nota"type="time" id="hora_nota" value="';if (isset($row['hora_nota'])) echo $row['hora_nota'];echo '"';echo ' required ></p>';



echo '<p><label for="nota">Nota:</label></p><p><textarea class="" name="nota" cols="60" rows="10"id="nota"  required>';if (isset($row['nota'])) echo $row['nota'];echo '</textarea></p>';



?>

<p><label for="nota">Fijar:</label></p>

<label class="checkbox-inline"><input name="fijar" type="radio" value="1" <?php

if ((isset($row['fijar'])) and $row['fijar']==1){

  echo "checked";

}

 ?> >Si</label>

<label class="checkbox-inline"><input name="fijar" type="radio" value="0" 



<?php 

if ((isset($row['fijar'])) and $row['fijar']=='0'){

  echo "checked";

}

?>

  >No</label>

<?php

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>

</form>';

} /*fin modificar*/ 



else {

####Registrar



if ((isset($_POST['submit']) and $_POST['submit']=="Nuevo") or isset($_GET['asignacion'])){



$textoh1 ="Registrar";

$textobtn ="Registrar";



echo '<form id="form1" name="form1" method="post" action="edunotas.php">

<h1>'.$textoh1.'</h1>';

?>







<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_nota']))  echo $row['id_nota'] ?>" size="120" required></p>

<?php 

echo '<p><input class=""name="id_nota"type="hidden" id="id_nota" value="';if (isset($row['id_nota'])) echo $row['id_nota'];echo '"';echo '></p>';

echo '<p><label for="id_asignacion">Id Asignacion:</label>';

$sql3= "SELECT * FROM asignacion,materia,ano_lectivo WHERE

asignacion.ano_lectivo = ano_lectivo.id_ano_lectivo and

asignacion.id_asignatura = materia.id_materia order by ano_lectivo.nombre_ano_lectivo desc;";

echo '<select class="" name="id_asignacion" id="id_asignacion"  required>';

echo '<option title="'.$row3['descripcion'].'" value="">Seleccione una opci&oacute;n</option>';

$consulta3 = $mysqli->query($sql3);

while($row3=$consulta3->fetch_assoc()){

echo '<option value="'.$row3['id_asignacion'].'"';



if (isset($_GET['asignacion']) and $_GET['asignacion'] == $row3['id_asignacion']) echo " selected ";



if (isset($row['id_asignacion']) and $row['id_asignacion'] == $row3['id_asignacion']) echo " selected ";echo '>'.$row3['id_asignacion'].' '.$row3['nombre_materia'].' ('.$row3['nombre_ano_lectivo'].')</option>';

}

echo '</select></p>';

echo '<p><label for="fecha_nota">Fecha Nota:</label><input class=""name="fecha_nota"type="date" id="fecha_nota" value="';

if (!isset($row['fecha_nota'])) echo date('Y-m-d');

if (isset($row['fecha_nota'])) echo $row['fecha_nota'];echo '"';echo ' required ></p>';

echo '<p><label for="hora_nota">Hora Nota:</label>

<input class=""name="hora_nota"type="time" id="hora_nota" value="';

if (!isset($row['hora_nota'])) echo date("H:i:s");

if (isset($row['hora_nota'])) echo $row['hora_nota'];echo '"';echo ' required ></p>';



?>

<p><label for="nota">Fijar:</label></p>

<label class="checkbox-inline"><input name="fijar" type="radio" value="1">Si</label>

<label class="checkbox-inline"><input name="fijar" type="radio" value="0" checked>No</label>



<?php



echo '<p><label for="nota">Nota:</label></p><p> ' ?>
<textarea class="" autofocus="" name="nota" cols="60" rows="10" id="nota"  required><?php if (isset($row['nota'])) echo $row['nota'] ?></textarea></p> 




<?php

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>

</form>';

} /*fin nuevo*/ 

else{





 /*require("funciones.php");*/ 

function buscar_edunotas($datos="",$reporte=""){

if ($reporte=="xls"){

header("Content-type: application/vnd.ms-excel");

header("Content-Disposition: attachment; Filename=edunotas.xls");

}

require("../comun/conexion.php");



$sql = "SELECT `edunotas`.`id_nota`, `edunotas`.`nota`, `edunotas`.`id_asignacion`, `asignacion`.`id_asignatura` as asignacionid_asignatura, `edunotas`.`fecha_nota`, `edunotas`.`hora_nota` FROM `edunotas`  inner join `asignacion` on `edunotas`.`id_asignacion` = `asignacion`.`id_asignacion`  ";

$datosrecibidos = $datos;

$datos = explode(" ",$datosrecibidos);

$datos[]="";

$cont =  0;

$sql .= ' WHERE ';

foreach ($datos as $id => $dato){

$sql .= ' concat(LOWER(`edunotas`.`id_nota`)," ", LOWER(`edunotas`.`nota`)," ", LOWER(`asignacion`.`id_asignatura`)," ", LOWER(`edunotas`.`fecha_nota`)," ", LOWER(`edunotas`.`hora_nota`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';

$cont ++;

if (count($datos)>1 and count($datos)<>$cont){

$sql .= " and ";

}

}

$sql .=  " ORDER BY `edunotas`.`id_nota` desc LIMIT ";

if (isset($_COOKIE['numeroresultados_edunotas']) and $_COOKIE['numeroresultados_edunotas']!="") $sql .=$_COOKIE['numeroresultados_edunotas'];

else $sql .= "10";

/*echo $sql;*/ 



$consulta = $mysqli->query($sql);

 ?>

<div align="center">

<table border="1" id="tbedunotas">

<thead>

<tr>

<th>Id Nota</th>

<th>Nota</th>

<th>Id Asignacion</th>

<th>Fecha Nota</th>

<th>Hora Nota</th>

<?php if ($reporte==""){ ?>

<th><form id="formNuevo" name="formNuevo" method="post" action="edunotas.php">

<input name="cod" type="hidden" id="cod" value="0">

<input type="submit" name="submit" id="submit" value="Nuevo">

</form>

</th>

<th><form id="formNuevo" name="formNuevo" method="post" action="edunotas.php?xls">

<input name="cod" type="hidden" id="cod" value="0">

<input type="submit" name="submit" id="submit" value="XLS">

</form>

</th>

<?php } ?>

</tr>

</thead><tbody>

<?php 

while($row=$consulta->fetch_assoc()){

 ?>

<tr>

<td><?php echo $row['id_nota']?></td>

<td><?php echo $row['nota']?></td>

<td><?php echo $row['asignacionid_asignatura']?></td>

<?php $meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e'); ?>



<td><?php echo  date("d \\d\\e ".$meses[date("n",strtotime($row['fecha_nota']))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($row['fecha_nota'])); ?></td>

<td><?php echo $row['hora_nota']?></td>

<?php if ($reporte==""){ ?>

<td>

<form id="formModificar" name="formModificar" method="post" action="edunotas.php">

<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_nota']?>">

<input type="submit" name="submit" id="submit" value="Modificar">

</form>

</td>

<td>

<input type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar('edunotas.php',{'del':'<?php echo $row['id_nota'];?>'},'<?php echo $row['id_nota'];?>');" value="Eliminar">

</td>

<?php } ?>

</tr>

<?php 

}/*fin while*/

 ?>

</tbody>

</table></div>

<?php 

}/*fin function buscar*/

if (isset($_GET['buscar'])){

buscar_edunotas($_POST['datos']);

exit();

}

if (isset($_GET['xls'])){

buscar_edunotas('','xls');

exit();

}



 ?>

<center>

<h1>Edunotas</h1>

</center><?php 

if (isset($_POST['submit'])){

if ($_POST['submit']=="Registrar"){

 /*recibo los campos del formulario proveniente con el método POST*/ 

$sql = "INSERT INTO edunotas ( `nota`, `id_asignacion`, `fecha_nota`, `hora_nota`,fijar) VALUES ( '".$_POST['nota']."', '".$_POST['id_asignacion']."', '".$_POST['fecha_nota']."', '".$_POST['hora_nota']."', '".$_POST['fijar']."')";

#echo $sql;

if ($insertar = $mysqli->query($sql)) {

 /*Validamos si el registro fue ingresado con éxito*/ 

echo 'Registro exitoso';

echo '<meta http-equiv="refresh" content="1; url=edunotas.php" />';

 }else{ 

echo 'Registro fallido';

echo '<meta http-equiv="refresh" content="1; url=edunotas.php" />';

}

} /*fin Registrar*/ 







if ($_POST['submit']=="Actualizar"){

 /*recibo los campos del formulario proveniente con el método POST*/ 

$cod = $_POST['cod'];

 /*Instrucción SQL que permite insertar en la BD */ 

$sql = "UPDATE edunotas SET id_nota='".$_POST['id_nota']."', nota='".$_POST['nota']."', id_asignacion='".$_POST['id_asignacion']."', fecha_nota='".$_POST['fecha_nota']."', hora_nota='".$_POST['hora_nota']."' , fijar='".$_POST['fijar']."'  WHERE  id_nota = '".$cod."';";



 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 

if ($actualizar = $mysqli->query($sql)) {

 /*Validamos si el registro fue ingresado con éxito*/

echo 'Modificación exitosa';

echo '<meta http-equiv="refresh" content="1; url=edunotas.php" />';

 }else{ 

echo 'Modificacion fallida';

}

echo '<meta http-equiv="refresh" content="2; url=edunotas.php" />';

} /*fin Actualizar*/ 

 }else{ 

 ?>

<center>

<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">

<b><label>N° de Resultados:</label></b>

<input type="number" min="0" id="numeroresultados_edunotas" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_edunotas',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_edunotas',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_edunotas',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">

</center>

<span id="txtsugerencias">

<?php 

buscar_edunotas();

 ?>

</span>

<?php 

}/*fin else if isset cod*/

echo '</center>';

}

}

 ?>

<script>

document.getElementById('menu_edunotas').className ='active '+document.getElementById('menu_edunotas').className;

</script>

<?php $contenido = ob_get_contents();

ob_clean();

include ("../comun/plantilla.php");

 ?>

