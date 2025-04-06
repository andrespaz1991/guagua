<?php 
ob_start();
echo '<center>';
require("../comun/conexion.php");

 /*require("funciones.php");*/ 
function buscar_control_ingreso($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=control_ingreso.xls");
}
require("../comun/conexion.php");

$sql = "SELECT `control_ingreso`.`id`, `control_ingreso`.`fecha_ingreso`, `control_ingreso`.`fecha_salida`, `control_ingreso`.`hora_ingreso`, `control_ingreso`.`hora_salida`, `control_ingreso`.`grupo`, `asignacion`.`id_asignacion` as asignacionid_asignacion FROM `control_ingreso`  inner join `asignacion` on `control_ingreso`.`grupo` = `asignacion`.`id_asignacion`  ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`control_ingreso`.`id`)," ", LOWER(`control_ingreso`.`fecha_ingreso`)," ", LOWER(`control_ingreso`.`fecha_salida`)," ", LOWER(`control_ingreso`.`hora_ingreso`)," ", LOWER(`control_ingreso`.`hora_salida`)," ", LOWER(`asignacion`.`id_asignacion`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `control_ingreso`.`id` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_control_ingreso']) and $_COOKIE['numeroresultados_control_ingreso']!="") $sql .=$_COOKIE['numeroresultados_control_ingreso'];
else $sql .= "10";
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbcontrol_ingreso">
<thead>
<tr>
<th>Id</th>
<th>Fecha Ingreso</th>
<th>Fecha Salida</th>
<th>Hora Ingreso</th>
<th>Hora Salida</th>
<th>Grupo</th>
<?php if ($reporte==""){ ?>
<th><form id="formNuevo" name="formNuevo" method="post" action="control_ingreso.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>
</th>
<th><form id="formNuevo" name="formNuevo" method="post" action="control_ingreso.php?xls">
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
<td><?php echo $row['id']?></td>
<?php $meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e'); ?>

<td><?php echo  date("d \\d\\e ".$meses[date("n",strtotime($row['fecha_ingreso']))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($row['fecha_ingreso'])); ?></td>
<?php $meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e'); ?>

<td><?php echo  date("d \\d\\e ".$meses[date("n",strtotime($row['fecha_salida']))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($row['fecha_salida'])); ?></td>
<td><?php echo $row['hora_ingreso']?></td>
<td><?php echo $row['hora_salida']?></td>
<td><?php 
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
$academico=new Academico;
$materia = $academico->consultar_materia($row['asignacionid_asignacion']);

print_r($materia[0]->nombre_materia)

?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="control_ingreso.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar('control_ingreso.php',{'del':'<?php echo $row['id'];?>'},'<?php echo $row['id'];?>');" value="Eliminar">
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
buscar_control_ingreso($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
buscar_control_ingreso('','xls');
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM control_ingreso WHERE id="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=control_ingreso.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=control_ingreso.php" />
<?php 
}
}
 ?>
<center>
<h1>Control Ingreso</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO control_ingreso (`id`, `fecha_ingreso`, `fecha_salida`, `hora_ingreso`, `hora_salida`, `grupo`) VALUES ('".$_POST['id']."', '".$_POST['fecha_ingreso']."', '".$_POST['fecha_salida']."', '".$_POST['hora_ingreso']."', '".$_POST['hora_salida']."', '".$_POST['grupo']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=control_ingreso.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=control_ingreso.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
if ($_POST['submit']=="Modificar"){
$sql = "SELECT `id`, `fecha_ingreso`, `fecha_salida`, `hora_ingreso`, `hora_salida`, `grupo` FROM `control_ingreso` WHERE id ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
}
if ($_POST['submit']=="Nuevo"){
$textoh1 ="Registrar";
$textobtn ="Registrar";
}
echo '<form id="form1" name="form1" method="post" action="control_ingreso.php">
<h1>'.$textoh1.'</h1>';
echo "id:".$row['id'];
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id']))  echo $row['id'] ?>" size="120" required></p>
<script type="text/javascript">
	function fecha_campo(id){
	document.getElementById('fecha_salida').value=document.getElementById('fecha_ingreso').value
	}
	function hora_campo(id){
	document.getElementById('hora_salida').value=document.getElementById('hora_ingreso').value
	}
</script>

<?php 
echo '<p><input class=""name="id"type="hidden" id="id" value="';if (isset($row['id'])) echo $row['id'];echo '"';echo '></p>';
echo '<p><label for="fecha_ingreso">Fecha Ingreso:</label><input 
onchange="fecha_campo();"
class=""name="fecha_ingreso"type="date" id="fecha_ingreso" value="';if (isset($row['fecha_ingreso'])) echo $row['fecha_ingreso'];echo '"';echo ' required ></p>';
echo '<p><label for="fecha_salida">Fecha Salida:</label><input class=""name="fecha_salida"type="date" id="fecha_salida" value="';if (isset($row['fecha_salida'])) echo $row['fecha_salida'];echo '"';echo ' required ></p>';
echo '<p><label for="hora_ingreso">Hora Ingreso:</label><input onchange="hora_campo();" class=""name="hora_ingreso"type="time" id="hora_ingreso" value="';if (isset($row['hora_ingreso'])) echo $row['hora_ingreso'];echo '"';echo ' required ></p>';
echo '<p><label for="hora_salida">Hora Salida:</label><input class=""name="hora_salida"type="time" id="hora_salida" value="';if (isset($row['hora_salida'])) echo $row['hora_salida'];echo '"';echo ' required ></p>';
echo '<p><label for="grupo">Grupo:</label>';
$sql7= "SELECT * FROM asignacion,materia,ano_lectivo WHERE
asignacion.ano_lectivo = ano_lectivo.id_ano_lectivo and
asignacion.id_asignatura = materia.id_materia order by ano_lectivo.nombre_ano_lectivo desc;";
echo '<select class="" name="grupo" id="grupo"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta7 = $mysqli->query($sql7);
while($row7=$consulta7->fetch_assoc()){
echo '<option title="'.$row7['descripcion'].'" value="'.$row7['id_asignacion'].'"';if (isset($row['grupo']) and $row['grupo'] == $row7['id_asignacion']) echo " selected ";echo '>'.$row7['id_asignacion'].' '.$row7['nombre_materia'].' ('.$row7['nombre_ano_lectivo'].')</option>';
}
echo '</select></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin mixto*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE control_ingreso SET id='".$_POST['id']."', fecha_ingreso='".$_POST['fecha_ingreso']."', fecha_salida='".$_POST['fecha_salida']."', hora_ingreso='".$_POST['hora_ingreso']."', hora_salida='".$_POST['hora_salida']."', grupo='".$_POST['grupo']."'WHERE  id = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=control_ingreso.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=control_ingreso.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_control_ingreso" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_control_ingreso',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_control_ingreso',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_control_ingreso',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_control_ingreso();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_control_ingreso').className ='active '+document.getElementById('menu_control_ingreso').className;
</script>
<?php 
$contenido = ob_get_contents();
ob_clean();
include "../comun/plantilla.php";
#include ("plantilla.php");
 ?>
