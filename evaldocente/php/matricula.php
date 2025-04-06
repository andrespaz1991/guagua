<?php 
ob_start();
echo '<center>';
require("conexion.php");
 /*require("funciones.php");*/ 
function buscar_inscripcion($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=inscripcion.xls");
}
require("conexion.php");
require_once ("../../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_inscripcion']) ? $_COOKIE['numeroresultados_inscripcion'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];

$sql = "SELECT `inscripcion`.`id_inscripcion`, `inscripcion`.`id_estudiante`, `usuario`.`nombre` as estudiantenombre, `inscripcion`.`id_asignacion`, `asignacion`.`id_asignacion` as asignacionid_asignacion, `inscripcion`.`fecha_inscripcion` FROM `inscripcion`  inner join `usuario` on `inscripcion`.`id_estudiante` = `usuario`.`id_usuario` inner join `asignacion` on `inscripcion`.`id_asignacion` = `asignacion`.`id_asignacion`  ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`inscripcion`.`id_inscripcion`)," ", LOWER(`usuario`.`nombre`)," ", LOWER(`asignacion`.`id_asignacion`)," ", LOWER(`inscripcion`.`fecha_inscripcion`)," ", LOWER(`inscripcion`.`fecha_inscripcion`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$consulta=$mysqli->query($sql);
echo $paginacion->records($consulta->num_rows);
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
    $sql .= "";
    }
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `inscripcion`.`id_inscripcion` desc LIMIT ";
$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
$numero_usuario = $consulta->num_rows;
$minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
$maximo_usuario += $minimo_usuario-1;
echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$numero_usuario." en página ".$paginacion->get_page()."</p>";

 ?>
<div align="center">
<table border="1" id="tbinscripcion" align="center">
<thead>
<tr>
<th>Id inscripcion</th>
<th>Id Estudiante</th>
<th>Id Asignacion</th>
<th>Fecha inscripcion</th>
<?php if ($reporte==""){ ?>
<th><form id="formNuevo" name="formNuevo" method="post" action="inscripcion.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>
</th>
<th><form id="formNuevo" name="formNuevo" method="post" action="inscripcion.php?xls">
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
<td><?php echo $row['id_inscripcion']?></td>
<td><?php echo $row['estudiantenombre']?></td>
<td><?php echo $row['asignacionid_asignacion']?></td>
<?php $meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e'); ?>

<td><?php echo  date("d \\d\\e ".$meses[date("n",strtotime($row['fecha_inscripcion']))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($row['fecha_inscripcion'])); ?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="inscripcion.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_inscripcion']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="../../comun/img/eliminar.png" onClick="confirmeliminar('inscripcion.php',{'del':'<?php echo $row['id_inscripcion'];?>'},'<?php echo $row['id_inscripcion'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<div class="text-center">
    <?php    $paginacion->render2();    ?>
    </div>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_inscripcion($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
buscar_inscripcion('','xls');
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM inscripcion WHERE id_inscripcion="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=inscripcion.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=inscripcion.php" />
<?php 
}
}
 ?>
<center>
<h1>inscripcion</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO inscripcion (`id_inscripcion`, `id_estudiante`, `id_asignacion`, `fecha_inscripcion`, `obserbaciones_inscripcion`) VALUES ('".$_POST['id_inscripcion']."', '".$_POST['id_estudiante']."', '".$_POST['id_asignacion']."', '".$_POST['fecha_inscripcion']."', '".$_POST['obserbaciones_inscripcion']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=inscripcion.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=inscripcion.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="inscripcion.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_inscripcion']))  echo $row['id_inscripcion'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_inscripcion"type="hidden" id="id_inscripcion" value="';if (isset($row['id_inscripcion'])) echo $row['id_inscripcion'];echo '"';echo '></p>';
echo '<p><label for="id_estudiante">Id Estudiante:</label>';
$sql2= "SELECT identificacion,nombre FROM estudiante;";
echo '<input type="text" autocomplete="off" list="list_id_estudiante" class="" name="id_estudiante" id="id_estudiante" required><datalist id="list_id_estudiante">';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option data-value="'.$row2['identificacion'].'"';echo '>'.$row2['nombre'].'</option>';
if ($row2["identificacion"]==$row["id_estudiante"]){
echo '<script>var varid_estudiante = document.getElementById("id_estudiante");varid_estudiante.value="'.$row2["id_estudiante"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_estudiante" id="id_estudiante-hidden" value="';
if (isset($row['id_estudiante'])) echo $row['id_estudiante'];
echo '"></p>';
echo '<p><label for="id_asignacion">Id Asignacion:</label>';
$sql3= "SELECT id_asignacion,id_asignacion FROM asignacion;";
echo '<input type="text" autocomplete="off" list="list_id_asignacion" class="" name="id_asignacion" id="id_asignacion" required><datalist id="list_id_asignacion">';
$consulta3 = $mysqli->query($sql3);
while($row3=$consulta3->fetch_assoc()){
echo '<option data-value="'.$row3['id_asignacion'].'"';echo '>'.$row3['id_asignacion'].'</option>';
if ($row3["id_asignacion"]==$row["id_asignacion"]){
echo '<script>var varid_asignacion = document.getElementById("id_asignacion");varid_asignacion.value="'.$row2["id_asignacion"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_asignacion" id="id_asignacion-hidden" value="';
if (isset($row['id_asignacion'])) echo $row['id_asignacion'];
echo '"></p>';
echo '<p><label for="fecha_inscripcion">Fecha inscripcion:</label><input class=""name="fecha_inscripcion"type="date" id="fecha_inscripcion" value="';if (isset($row['fecha_inscripcion'])) echo $row['fecha_inscripcion'];echo '"';echo '></p>';
echo '<p><label for="obserbaciones_inscripcion">Obserbaciones inscripcion:</label></p><p><textarea class="" name="obserbaciones_inscripcion" cols="60" rows="10"id="obserbaciones_inscripcion" >';if (isset($row['obserbaciones_inscripcion'])) echo $row['obserbaciones_inscripcion'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_inscripcion`, `id_estudiante`, `id_asignacion`, `fecha_inscripcion` FROM `inscripcion` WHERE id_inscripcion ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="inscripcion.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_inscripcion']))  echo $row['id_inscripcion'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_inscripcion"type="hidden" id="id_inscripcion" value="';if (isset($row['id_inscripcion'])) echo $row['id_inscripcion'];echo '"';echo '></p>';
echo '<p><label for="id_estudiante">Id Estudiante:</label>';
$sql2= "SELECT identificacion,nombre FROM estudiante;";
echo '<input type="text" autocomplete="off" list="list_id_estudiante" class="" name="id_estudiante" id="id_estudiante" required><datalist id="list_id_estudiante">';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option data-value="'.$row2['identificacion'].'"';echo '>'.$row2['nombre'].'</option>';
if ($row2["identificacion"]==$row["id_estudiante"]){
echo '<script>var varid_estudiante = document.getElementById("id_estudiante");varid_estudiante.value="'.$row2["id_estudiante"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_estudiante" id="id_estudiante-hidden" value="';
if (isset($row['id_estudiante'])) echo $row['id_estudiante'];
echo '"></p>';
echo '<p><label for="id_asignacion">Id Asignacion:</label>';
$sql3= "SELECT id_asignacion,id_asignacion FROM asignacion;";
echo '<input type="text" autocomplete="off" list="list_id_asignacion" class="" name="id_asignacion" id="id_asignacion" required><datalist id="list_id_asignacion">';
$consulta3 = $mysqli->query($sql3);
while($row3=$consulta3->fetch_assoc()){
echo '<option data-value="'.$row3['id_asignacion'].'"';echo '>'.$row3['id_asignacion'].'</option>';
if ($row3["id_asignacion"]==$row["id_asignacion"]){
echo '<script>var varid_asignacion = document.getElementById("id_asignacion");varid_asignacion.value="'.$row2["id_asignacion"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_asignacion" id="id_asignacion-hidden" value="';
if (isset($row['id_asignacion'])) echo $row['id_asignacion'];
echo '"></p>';
echo '<p><label for="fecha_inscripcion">Fecha inscripcion:</label><input class=""name="fecha_inscripcion"type="date" id="fecha_inscripcion" value="';if (isset($row['fecha_inscripcion'])) echo $row['fecha_inscripcion'];echo '"';echo '></p>';
echo '<p><label for="obserbaciones_inscripcion">Obserbaciones inscripcion:</label></p><p><textarea class="" name="obserbaciones_inscripcion" cols="60" rows="10"id="obserbaciones_inscripcion" >';if (isset($row['obserbaciones_inscripcion'])) echo $row['obserbaciones_inscripcion'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE inscripcion SET id_inscripcion='".$_POST['id_inscripcion']."', id_estudiante='".$_POST['id_estudiante']."', id_asignacion='".$_POST['id_asignacion']."', fecha_inscripcion='".$_POST['fecha_inscripcion']."', obserbaciones_inscripcion='".$_POST['obserbaciones_inscripcion']."'WHERE  id_inscripcion = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=inscripcion.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=inscripcion.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_inscripcion" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_inscripcion',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_inscripcion',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_inscripcion',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_inscripcion();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var inputlists = document.querySelectorAll('input[list]');
for(var j = 0; j< inputlists.length; j++) {
inputlists[j].addEventListener('input', function(e) {
var input = e.target,
list = input.getAttribute('list'),
options = document.querySelectorAll('#' + list + ' option'),
hiddenInput = document.getElementById(input.id + '-hidden'),
inputValue = input.value;
hiddenInput.value = inputValue;
for(var i = 0; i < options.length; i++) {
var option = options[i];
if(option.innerText === inputValue) {
 hiddenInput.value = option.getAttribute('data-value');
break;
}
}
});
}
</script>
<script>
document.getElementById('menu_inscripcion').className ='active '+document.getElementById('menu_inscripcion').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
 ?>
