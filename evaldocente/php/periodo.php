<?php
ob_clean();
ob_start();
?>
<link rel="stylesheet" type="text/css" href="css/estilo_tabla1.css" /><!--estilo cuerpo--->

<?php 
require("conexion.php");
//require("funciones.php");
function buscar_periodo($datos=""){
require("conexion.php");

 $sql = "SELECT `id_periodo`, `nombre_periodo` FROM `periodo` ";
if(isset( $_POST['datos'])){
 $datosrecibidos = $_POST['datos'];
}else{
	$datosrecibidos ="";
}
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(`nombre_periodo`," ", `nombre_periodo`," ") LIKE "%'.utf8_decode($dato).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY nombre_periodo  LIMIT ";
if (isset($_COOKIE['numeroresultados']) and $_COOKIE['numeroresultados']!="") $sql .=$_COOKIE['numeroresultados'];
else $sql .= "10";
//echo $sql;

//echo $sql;
$consulta = $mysqli->query($sql);
#echo $sql;
?>
<center>
<table border="1" id="tb<?php echo $nombretabla ?>">
<thead>
<tr>
<th>Año Lectivo</th>
<th>Periodo</th>
<th colspan="2"><?php echo '<form id="formNuevo" name="formNuevo" method="post" action="periodo.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>'; ?>
</td></th></tr>
</thead><tbody>
<?php
while($row=$consulta->fetch_assoc()){
?>
<tr>
<td><?php echo $row['id_periodo']?></td>
<td><?php echo $row['nombre_periodo']?></td>
<td>
<form id="formModificar" name="formModificar" method="post" action="periodo.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['ano_lectivo']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('periodo.php',{'del':'<?php echo $row['ano_lectivo'];?>'},'<?php echo $row['ano_lectivo'];?>');" value="Eliminar">
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
buscar_periodo($_POST['datos']);
exit();
}
if (isset($_POST['del'])){
//Instrucción SQL que permite eliminar en la BD
$sql = 'DELETE FROM periodo WHERE ano_lectivo="'.$_POST['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
//Validamos si el registro fue eliminado con éxito
echo 'Registro eliminado';
echo '<meta http-equiv="refresh" content="1; url=periodo.php" />';
}else{
echo 'Eliminación fallida, por favor compruebe que la usuario no esté en uso';
echo '<meta http-equiv="refresh" content="2; url=periodo.php" />';
}
}
?>
<?php
ob_clean();
ob_start();
?>
<section>
<p align="center">
<h1 align="center">periodo</h1>
<br>
<p align="center">
<?php
if (isset($_POST['submit'])){
switch ($_POST['submit']){
case "Registrar":
//recibo los campos del formulario proveniente con el método POST
$sql = "INSERT INTO periodo (`ano_lectivo`, `periodo`) VALUES ('".$_POST['ano_lectivo']."', '".$_POST['periodo']."')";
//echo $sql;
if ($insertar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=periodo.php" />';
}else{
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=periodo.php" />';
}
break;
case "Nuevo":
echo "Ingresando un ";
echo $_POST['submit'];
echo '<form id="form1" name="form1" method="post" action="periodo.php">
<h1>Registrar</h1>';
echo '<p><label for="ano_lectivo">Año Lectivo:</label></p>';
echo '<br>';
$sql= "SELECT ano_lectivo,ano_lectivo FROM ano_lectivo;";
echo '<select name="ano_lectivo" id="ano_lectivo" >';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo '<option value="'.$row['ano_lectivo'].'">'.$row['ano_lectivo'].'</option>';
}
echo '</select></p>';
echo '<p><label for="periodo">Periodo:</label></p>';
echo '<input name="periodo" type="number"  min="0"  max="99" id="periodo"  maxlength="2" value=""></p>';
echo '<a href="periodo.php">Regresar</a>';
echo '<p><input type="submit" name="submit" id="submit" value="Registrar"></p>
</form>';
break;
case "Actualizar":
//recibo los campos del formulario proveniente con el método POST
$cod = $_POST['cod'];
//Instrucción SQL que permite insertar en la BD sig_tipo_documento`, `nom_tipo_documento
$sql = "UPDATE periodo SET ano_lectivo='".$_POST['ano_lectivo']."', periodo='".$_POST['periodo']."'WHERE  ano_lectivo = '".$cod."';";
//echo $sql;
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($actualizar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=periodo.php" />';
}else{
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=periodo.php" />';
break;
case "Modificar":
$sql = "SELECT `ano_lectivo`, `periodo` FROM `periodo` WHERE ano_lectivo ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
//echo $sql;
if($row=$consulta->fetch_assoc())
{
echo '<form id="form1" name="form1" method="post" action="periodo.php">
<h1>Modificar '.$row['ano_lectivo'].'</h1>';
echo '<p><label for="cod"></label><input name="cod" type="hidden" id="cod" value="'.$row['ano_lectivo'].'" size="120" required></p>';
echo '<p><label for="ano_lectivo">Año Lectivo:</label></p>';
echo '<br>';
$sql= "SELECT ano_lectivo,ano_lectivo FROM ano_lectivo;";
echo '<select name="ano_lectivo" id="ano_lectivo" >';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row2=$consulta->fetch_assoc()){
echo '<option ';
if($row['ano_lectivo']==$row2['ano_lectivo']) echo " selected ";
echo 'value="'.$row2['ano_lectivo'].'">'.$row2['ano_lectivo'].'</option>';
}
echo '</select></p>';
echo '<p><label for="periodo">Periodo:</label></p>';
echo '<input name="periodo" type="number"  min="0"  max="99" id="periodo"  maxlength="2" value="'.$row['periodo'].'"></p>';
echo '<a href="periodo.php">Regresar</a>
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
buscar_periodo();
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
