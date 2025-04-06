<?php
require("../comun/conexion.php");
require_once("../comun/funciones.php");
/*
$sql = "SELECT `encuesta`.`id`, `encuesta`.`nombre`, `encuesta`.`fecha`, concat(usuario.nombre,' ',usuario.apellido) as nombre_usuario, `tipo_encuesta` FROM `encuesta` inner join `usuario` on `encuesta`.`usuario` = `usuario`.`id_usuario`";
$consulta = $mysqli->query($sql);
?>
<table border="1" id="tb">
<thead>
<tr>
<th>id</th>
<th>Nombre</th>
<th>Fecha</th>
<th>Tipo de Encuesta</th>
<th>Usuario</th>
</tr>
</thead><tbody>
<?php
while($row=$consulta->fetch_assoc()){
?>
<tr>
<td><?php echo $row['id']?></td>
<td><?php echo $row['nombre']?></td>
<td><?php echo formatofecha($row['fecha']); ?></td>
<td><?php echo $row['tipo_encuesta']?></td>
<td><?php echo $row['nombre_usuario']?></td>
</tr>
<?php
}//fin while
?>
</tbody>
</table>
*/
<?php
ob_start();
@session_start();
$_SESSION['modulo']="cuestionario";
require((dirname(__FILE__))."/../comun/conexion.php");
#require("../comun/conexion.php");
require_once("../comun/funciones.php");
function buscar_encuesta_encurso($datos=""){
require((dirname(__FILE__))."/../comun/conexion.php");

$sql = "SELECT `encuesta`.`id`, `encuesta`.`nombre`, `encuesta`.`fecha`, concat(`usuario`.`nombre`,' ',`usuario`.`apellido`) as usuario, `tipo_encuesta` as nombre_tipo_encuesta FROM `encuesta` inner join `usuario` on `encuesta`.`usuario` = `usuario`.`id_usuario`";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(`encuesta`.`nombre`," ", `encuesta`.`fecha`," ", `usuario`.`nombre`," ", `usuario`.`apellido`," ") LIKE "%'.utf8_decode($dato).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY id  LIMIT ";
if (isset($_COOKIE['numeroresultados']) and $_COOKIE['numeroresultados']!="") $sql .=$_COOKIE['numeroresultados'];
else $sql .= "10";
$consulta = $mysqli->query($sql);
#echo $sql;
?>
<div align="center">
<table border="1" id="tb">
<thead>
<tr>
<th>id</th>
<th>Nombre</th>
<th>Fecha</th>
<th>Tipo de Encuesta</th>
<th>Usuario</th>
<th></th>
</tr>
</thead><tbody>
<?php
while($row=$consulta->fetch_assoc()){
?>
<tr>
<td><?php echo $row['id']?></td>
<td><?php echo $row['nombre']?></td>
<td><?php echo formatofecha($row['fecha']); ?></td>
<td><?php echo $row['nombre_tipo_encuesta']?></td>
<td><?php echo $row['usuario']?></td>
<td>
<form id="formForm" name="formForm" method="post" action="ver_encuesta.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id']?>">
<input type="submit" name="submit" id="submit" class="btn btn-default btn-sm" value="Responder">
</form>
</td>
</tr>
<?php
}//fin while
?>
</tbody>
</table></div>
<?php
}//fin function buscar
if (isset($_GET['buscar_encuesta'])){
buscar_encuesta($_POST['datos']);
exit();
}
if (isset($_POST['del'])){
//Instrucción SQL que permite eliminar en la BD
$sql = 'DELETE FROM encuesta WHERE id="'.$_POST['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
//Validamos si el registro fue eliminado con éxito
echo 'Registro eliminado';
echo '<meta http-equiv="refresh" content="1; url=encuesta.php" />';
}else{
echo 'Eliminación fallida, por favor compruebe que la usuario no esté en uso';
echo '<meta http-equiv="refresh" content="2; url=encuesta.php" />';
}
}
?>
<title>Encuesta</title>
<section>
<p align="center">
<h1>Encuesta</h1>
<br>
<p>
<?php
if (isset($_POST['submit'])){
switch ($_POST['submit']){
case "Registrar":
//recibo los campos del formulario proveniente con el método POST
$sql = "INSERT INTO encuesta (`id`, `nombre`, `descripcion`, `fecha`, `tipo_encuesta`, `usuario`) VALUES ('".$_POST['id']."', '".$_POST['nombre']."', '".$_POST['descripcion']."', '".$_POST['fecha']."', '".$_POST['tipo_encuesta']."', '".$_POST['usuario']."')";
//echo $sql;
if ($insertar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=encuesta.php" />';
}else{
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=encuesta.php" />';
}
break;
case "Nuevo":

echo '<form id="form1" name="form1" method="post" action="encuesta.php">
<h1>Registrar</h1>';
echo '<p><input name="id" type="hidden" id="id"  maxlength="11" value=""></p>';
echo '<p><label for="nombre">Nombre:</label><input name="nombre" type="text"  maxlength="255" id="nombre"  maxlength="255" value="" required></p>';
echo '<p><label for="descripcion">Descripción:</label></p><p><textarea name="descripcion" cols="60" rows="10"id="descripcion"  required></textarea></p>';
echo '<p><label for="fecha">Fecha:</label><input name="fecha" type="date" id="fecha"  maxlength="0" value="" required></p>';

$sql= "SELECT `id`, `nombre` FROM `tipo_encuesta` ;";
echo '<p><label for="tipo_encuesta">Tipo de Encuesta:</label><select name="tipo_encuesta" id="tipo_encuesta"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
}
echo '</select></p>';

$sql= "SELECT usuario,usuario FROM usuario;";
echo '<p><label for="usuario">Usuario:</label><select name="usuario" id="usuario"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo '<option value="'.$row['usuario'].'">'.$row['usuario'].'</option>';
}
echo '</select></p>';
echo '<a href="encuesta.php">Regresar</a>';
echo '<p><input type="submit" name="submit" id="submit" value="Registrar"></p>
</form>';
break;
case "Actualizar":
//recibo los campos del formulario proveniente con el método POST
$cod = $_POST['cod'];
//Instrucción SQL que permite insertar en la BD sig_tipo_documento`, `nom_tipo_documento
$sql = "UPDATE encuesta SET id='".$_POST['id']."', nombre='".$_POST['nombre']."', descripcion='".$_POST['descripcion']."', fecha='".$_POST['fecha']."', tipo_encuesta='".$_POST['tipo_encuesta']."', usuario='".$_POST['usuario']."'WHERE  id = '".$cod."';";
//echo $sql;
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($actualizar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=encuesta.php" />';
}else{
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=encuesta.php" />';
break;
case "Modificar":
$sql = "SELECT `id`, `nombre`, `descripcion`, `fecha`, `tipo_encuesta`, `usuario` FROM `encuesta` WHERE id ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
//echo $sql;
if($row=$consulta->fetch_assoc())
{
echo '<form id="form1" name="form1" method="post" action="encuesta.php">
<h1>Modificar '.$row['id'].'</h1>';
echo '<p><label for="cod">Id:</label><input name="cod" type="hidden" id="cod" value="'.$row['id'].'" size="120" required></p>';
echo '<p><label for="id">id:</label><input name="id" type="hidden" id="id"  maxlength="11" value="'.$row['id'].'"></p>';
echo '<p><label for="nombre">Nombre:</label><input name="nombre" type="text"  maxlength="255" id="nombre"  maxlength="255" value="'.$row['nombre'].'" required></p>';
echo '<p><label for="descripcion">Descripción:</label></p><p><textarea name="descripcion" cols="60" rows="10"id="descripcion"  required>'.$row['descripcion'].'</textarea></p>';
echo '<p><label for="fecha">Fecha:</label><input name="fecha" type="date" id="fecha"  maxlength="0" value="'.$row['fecha'].'" required></p>';
echo '<p><label for="tipo_encuesta">Tipo de Encuesta:</label><input name="tipo_encuesta" type="llave_foranea" id="tipo_encuesta"  maxlength="11" value="'.$row['tipo_encuesta'].'" required></p>';
echo '<p><label for="usuario">Usuario:</label><input name="usuario" type="llave_foranea" id="usuario"  maxlength="255" value="'.$row['usuario'].'" required></p>';
echo '<a href="encuesta.php">Regresar</a>
<p><input type="submit" name="submit" id="submit" value="Actualizar"></p>
</form>';
}
break;
default:
echo "Ingreso erroneo";
}//fin switch
}else{
?>
<p>
<b><label>Buscar: </label></b><input type="text" id="buscar" onkeyup ="buscar_encuesta(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</p>
<span id="txtsugerencias">
<?php
buscar_encuesta();
?>
</span>
<?php
}//fin else if isset cod
?>
</section>
<?php $contenido = ob_get_clean();
echo $contenido;
?>