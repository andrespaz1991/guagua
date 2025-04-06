<?php 
ob_start();
?>
<script language="javascript" src="js/funciones.js"></script>
<script language="javascript" src="js/ajax.js"></script>
<?php
require("conexion.php");
require_once("../../comun/config.php");

function buscar_categoria($datos=""){
require("conexion.php");

$sql = "SELECT `categoria`.`id_categoria`, `categoria`.`nombre_categoria` FROM `categoria`   ";$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`categoria`.`id_categoria`)," ", LOWER(`categoria`.`nombre_categoria`)," ") LIKE "%'.utf8_decode(mb_strtolower($dato, 'UTF-8')).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `categoria`.`id_categoria` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_categoria']) and $_COOKIE['numeroresultados_categoria']!="") $sql .=$_COOKIE['numeroresultados_categoria'];
else $sql .= "10";
#echo $sql;

$consulta = $mysqli->query($sql);
?>
<div align="center">
<table align="center" border="1" id="tb<?php  ?>">
<thead>
<tr>
<th>Id Categoria</th>
<th>Nombre Categoria</th>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="categoria.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>
</td></th></tr>
</thead><tbody>
<?php
while($row=$consulta->fetch_assoc()){
?>
<tr>
<td><?php echo $row['id_categoria']?></td>
<td><?php echo $row['nombre_categoria']?></td>
<td>
<form id="formModificar" name="formModificar" method="post" action="categoria.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_categoria']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_COMUN_URL; ?>/img/eliminar.png" onClick="confirmeliminar('categoria.php',{'del':'<?php echo $row['id_categoria'];?>'},'<?php echo $row['id_categoria'];?>');" value="Eliminar">
</td>
</tr>
<?php
}//fin while
?>
</tbody>
</table></div>
<?php
}//fin function buscar
if (isset($_GET['buscar'])){
buscar_categoria($_POST['datos']);
exit();
}
if (isset($_POST['del'])){
//Instrucción SQL que permite eliminar en la BD
$sql = 'DELETE FROM categoria WHERE id_categoria="'.$_POST['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
//Validamos si el registro fue eliminado con éxito
?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=categoria.php" />
<?php
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=categoria.php" />
<?php
}
}
?>
<p align="center">
<h1 align="center">Categoria</h1>
<br>
<p>
<?php
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
//recibo los campos del formulario proveniente con el método POST
$sql = "INSERT INTO categoria (`id_categoria`, `nombre_categoria`) VALUES ('".$_POST['id_categoria']."', '".$_POST['nombre_categoria']."')";
//echo $sql;
if ($insertar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=categoria.php" />';
}else{
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=categoria.php" />';
}
}#fin Registrar
if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
if ($_POST['submit']=="Modificar"){
$sql = "SELECT `id_categoria`, `nombre_categoria` FROM `categoria` WHERE id_categoria ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
//echo $sql;
$row = array();
if($raw=$consulta->fetch_assoc())
{
$row = $raw;
}
$textoh1 ="Modificar";
$textobtn ="Actualizar";
}
if ($_POST['submit']=="Nuevo"){
$textoh1 ="Registrar";
$textobtn ="Registrar";
}echo '<form id="form1" name="form1" method="post" action="categoria.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php echo $row['id_categoria'] ?>" size="120" required></p>
<?php
echo '<p><input class="" name="id_categoria" type="hidden" id="id_categoria" value="';if (isset($row['id_categoria'])) echo $row['id_categoria'];echo '"';echo '></p>';
echo '<p><label for="nombre_categoria">Nombre Categoria:</label><input class="" name="nombre_categoria" type="text" id="nombre_categoria" value="';if (isset($row['nombre_categoria'])) echo $row['nombre_categoria'];echo '"';echo '></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
}#fin Nuevo
if ($_POST['submit']=="Actualizar"){
//recibo los campos del formulario proveniente con el método POST
$cod = $_POST['cod'];
//Instrucción SQL que permite insertar en la BD sig_tipo_documento`, `nom_tipo_documento
$sql = "UPDATE categoria SET id_categoria='".$_POST['id_categoria']."', nombre_categoria='".$_POST['nombre_categoria']."'WHERE  id_categoria = '".$cod."';";
//echo $sql;
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($actualizar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=categoria.php" />';
}else{
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=categoria.php" />';
}#fin Actualizar
}else{
?>
<div align="center">
<b><label>Buscar: </label></b><input align="center" type="text" id="buscar" onkeyup ="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_categoria" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_categoria',this.value);buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_categoria',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_categoria',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</p>
</div>
<span id="txtsugerencias">
<?php
buscar_categoria();
?>
</span>
<?php
}//fin else if isset cod
?>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
?>
