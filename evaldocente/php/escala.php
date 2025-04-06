<script language="javascript" src="js/funciones.js"></script>
<script language="javascript" src="js/ajax.js"></script>
<?php 
require("conexion.php");
//require("funciones.php");
function buscar_escala($datos=""){
require("conexion.php");

$sql = "SELECT `valor`, `descripcion` FROM `escala`";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(`valor`," ", `descripcion`," ") LIKE "%'.utf8_decode($dato).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `id_escala` ASC LIMIT ";
if (isset($_COOKIE['numeroresultados']) and $_COOKIE['numeroresultados']!="") $sql .=$_COOKIE['numeroresultados'];
else $sql .= "10";
//echo $sql;

//echo $sql;
$consulta = $mysqli->query($sql);
//echo $sql;
?>
<center>
<table border="1" id="tb<?php  ?>">
<thead>
<tr>
<th>Valor Escala</th>
<th>Descripción</th>
<th colspan="2"><?php echo '<form id="formNuevo" name="formNuevo" method="post" action="escala.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>'; ?>
</td></th></tr>
</thead><tbody>
<?php
while($row=$consulta->fetch_assoc()){
?>
<tr>
<td><?php echo $row['valor']?></td>
<td><?php echo $row['descripcion']?></td>
<td>
<form id="formModificar" name="formModificar" method="post" action="escala.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['valor']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
	<script language="javascript" src="js/funciones.js"></script>
<script language="javascript" src="js/ajax.js"></script>
<input type="image" src="../../comun/img/eliminar.png" onClick="confirmeliminar('escala.php',{'del':'<?php echo $row['valor'];?>'},'<?php echo $row['valor'];?>');" value="Eliminar">
</td>
</tr>
<?php
}//fin while
?>
</tbody>
</table></center>
<?php
}//fin function buscar
if (isset($_GET['buscar'])){
buscar_escala($_POST['datos']);
exit();
}
if (isset($_POST['del'])){
//Instrucción SQL que permite eliminar en la BD
$sql = 'DELETE FROM escala WHERE valor="'.$_POST['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
//Validamos si el registro fue eliminado con éxito
echo 'Registro eliminado';
echo '<meta http-equiv="refresh" content="1; url=escala.php" />';
}else{
echo 'Eliminación fallida, por favor compruebe que la usuario no esté en uso';
echo '<meta http-equiv="refresh" content="2; url=escala.php" />';
}
}
?>
<?php
ob_clean();
ob_start();
?>
<section>
<p align="center">
<h1 align="center">ESCALA</h1>
<br>
<p  align="center">
<?php
if (isset($_POST['submit'])){
switch ($_POST['submit']){
case "Registrar":
//recibo los campos del formulario proveniente con el método POST
$sql = "INSERT INTO escala (`valor`, `descripcion`) VALUES ('".$_POST['valor']."', '".$_POST['descripcion']."')";
//echo $sql;
if ($insertar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=escala.php" />';
}else{
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=escala.php" />';
}
break;
case "Nuevo":
echo "Ingresando un ";
echo $_POST['submit'];
echo '<form id="form1" name="form1" method="post" action="escala.php">
<h1>Registrar</h1>';
echo '<p><label for="valor">Valor Escala:</label></p>';
echo '<input name="valor" type="text"  maxlength="255" id="valor"  maxlength="255" value="" required></p>';
echo '<p><label for="descripcion">Descripción:</label></p>';
echo '<input name="descripcion" type="text"  maxlength="2000" id="descripcion"  maxlength="2000" value="" required></p>';
echo '<a href="escala.php">Regresar</a>';
echo '<p><input type="submit" name="submit" id="submit" value="Registrar"></p>
</form>';
break;
case "Actualizar":
//recibo los campos del formulario proveniente con el método POST
$cod = $_POST['cod'];
//Instrucción SQL que permite insertar en la BD sig_tipo_documento`, `nom_tipo_documento
$sql = "UPDATE escala SET valor='".$_POST['valor']."', descripcion='".$_POST['descripcion']."'WHERE  valor = '".$cod."';";
//echo $sql;
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($actualizar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=escala.php" />';
}else{
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=escala.php" />';
break;
case "Modificar":
$sql = "SELECT `valor`, `descripcion` FROM `escala` WHERE valor ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
//echo $sql;
if($row=$consulta->fetch_assoc())
{
echo '<form id="form1" name="form1" method="post" action="escala.php">
<h1>Modificar '.$row['valor'].'</h1>';
echo '<p><label for="cod"></label><input name="cod" type="hidden" id="cod" value="'.$row['valor'].'" size="120" required></p>';
echo '<p><label for="valor">Valor Escala:</label></p>';
echo '<input name="valor" type="text"  maxlength="255" id="valor"  maxlength="255" value="'.$row['valor'].'" required></p>';
echo '<p><label for="descripcion">Descripción:</label></p>';
echo '<input name="descripcion" type="text"  maxlength="2000" id="descripcion"  maxlength="2000" value="'.$row['descripcion'].'" required></p>';
echo '<a href="escala.php">Regresar</a>
<p><input type="submit" name="submit" id="submit" value="Actualizar"></p>
</form>';
}
break;
default:
echo "Ingreso erroneo";
}//fin switch
}else{
?>
<b><label>Buscar: </label></b><input type="text" id="buscar" onkeyup ="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</p>
<span id="txtsugerencias">
<?php
buscar_escala();
?>
</span>
<?php
}//fin else if isset cod
?>
</section>
<?php
$contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
?>
