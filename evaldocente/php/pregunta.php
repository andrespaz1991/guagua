
<script language="javascript" src="js/funciones.js"></script>
<script language="javascript" src="js/ajax.js"></script>
<?php 
require("conexion.php");
require_once("../../comun/config.php");

function buscar_pregunta($datos=""){
require("conexion.php");
$sql = "SELECT `id_pregunta`, `cod_pregunta`, `pregunta`, `categoria`, `tipo` FROM `pregunta` ";$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(`id_pregunta`," ", `cod_pregunta`," ", `pregunta`," ", `categoria`," ", `tipo`," ") LIKE "%'.utf8_decode($dato).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY id_pregunta  LIMIT ";
if (isset($_COOKIE['numeroresultados']) and $_COOKIE['numeroresultados']!="") $sql .=$_COOKIE['numeroresultados'];
else $sql .= "10";
//echo $sql;

//echo $sql;
$consulta = $mysqli->query($sql);
//echo $sql;
if(isset($_POST['Descargar'])){
header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
header('Content-Disposition: attachment; filename=nombre_archivo.xls');
}

?>
<div align="center">
<table align="center" border="1" id="tb<?php ?>">
<thead>
<tr>
<th>id_pregunta</th>
<th>Código</th>
<th>Pregunta</th>
<th>Categoría</th>
<th>Tipo</th>
<th colspan="2">
	<form action="" method="POST">
<input type="submit" name="Descargar" value="Descargar">
<br><br>
<form/>
	<?php echo '<form id="formNuevo" name="formNuevo" method="post" action="pregunta.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>'; ?>
</td></th></tr>
</thead><tbody>
<?php
while($row=$consulta->fetch_assoc()){
?>
<tr>
<td><?php echo $row['id_pregunta']?></td>
<td><?php echo $row['cod_pregunta']?></td>
<td><?php echo $row['pregunta']?></td>
<td><?php echo $row['categoria']?></td>
<td><?php echo $row['tipo']?></td>
<td>
<form id="formModificar" name="formModificar" method="post" action="pregunta.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_pregunta']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_COMUN_URL; ?>/img/eliminar.png" onClick="confirmeliminar('pregunta.php',{'del':'<?php echo $row['id_pregunta'];?>'},'<?php echo $row['id_pregunta'];?>');" value="Eliminar">
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
buscar_pregunta($_POST['datos']);
exit();
}
if (isset($_POST['del'])){
//Instrucción SQL que permite eliminar en la BD
$sql = 'DELETE FROM pregunta WHERE id_pregunta="'.$_POST['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
//Validamos si el registro fue eliminado con éxito
echo 'Registro eliminado';
echo '<meta http-equiv="refresh" content="1; url=pregunta.php" />';
}else{
echo 'Eliminación fallida, por favor compruebe que la usuario no esté en uso';
echo '<meta http-equiv="refresh" content="2; url=pregunta.php" />';
}
}
ob_start();
?>
<title>Preguntas</title>
<section>
<p align="center">
<h1 align="center">Preguntas</h1>
<br>
<p>
<?php
if (isset($_POST['submit'])){
switch ($_POST['submit']){
case "Registrar":
//recibo los campos del formulario proveniente con el método POST
$sql = "INSERT INTO pregunta (`id_pregunta`, `cod_pregunta`, `pregunta`, `categoria`, `tipo`) VALUES ('".$_POST['id_pregunta']."', '".$_POST['cod_pregunta']."', '".$_POST['pregunta']."', '".$_POST['categoria']."', '".$_POST['tipo']."')";
//echo $sql;
if ($insertar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=pregunta.php" />';
}else{
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=pregunta.php" />';
}
break;
case "Nuevo":
echo "Ingresando un ";
echo $_POST['submit'];
echo '<form id="form1" name="form1" method="post" action="pregunta.php">
<h1>Registrar</h1>';

echo '<input name="id_pregunta" type="hidden" id="id_pregunta"  maxlength="11" value=""></p>';
echo '<p><label for="cod_pregunta">Código:</label></p>';
echo '<input name="cod_pregunta" type="text"  maxlength="255" id="cod_pregunta"  maxlength="255" value="" required></p>';
echo '<p><label for="pregunta">Pregunta:</label></p>';
echo '<input name="pregunta" type="text"  maxlength="3000" id="pregunta"  maxlength="3000" value="" required></p>';
echo '<p><label for="categoria">Categoría:</label></p>';
echo '<br>';
$sql= "SELECT id_categoria,nombre_categoria FROM categoria;";
echo '<select name="categoria" id="categoria"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo '<option value="'.$row['id_categoria'].'">'.$row['nombre_categoria'].'</option>';
}
echo '</select></p>';
echo '<input name="tipo" type="hidden"  maxlength="255" id="tipo"  maxlength="255" value="estudiante" required></p>';
echo '<a href="pregunta.php">Regresar</a>';
echo '<p><input type="submit" name="submit" id="submit" value="Registrar"></p>
</form>';
break;
case "Actualizar":
//recibo los campos del formulario proveniente con el método POST
$cod = $_POST['cod'];
//Instrucción SQL que permite insertar en la BD sig_tipo_documento`, `nom_tipo_documento
$sql = "UPDATE pregunta SET id_pregunta='".$_POST['id_pregunta']."', cod_pregunta='".$_POST['cod_pregunta']."', pregunta='".$_POST['pregunta']."', categoria='".$_POST['categoria']."', tipo='".$_POST['tipo']."'WHERE  id_pregunta = '".$cod."';";
//echo $sql;
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($actualizar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=pregunta.php" />';
}else{
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=pregunta.php" />';
break;
case "Modificar":
$sql = "SELECT * FROM `pregunta` WHERE id_pregunta ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
//echo $sql;
if($row=$consulta->fetch_assoc())
{
echo '<form id="form1" name="form1" method="post" action="pregunta.php">
<h1>Modificar '.$row['id_pregunta'].'</h1>';
echo '<p><label for="cod"></label><input name="cod" type="hidden" id="cod" value="'.$row['id_pregunta'].'" size="120" required></p>';
echo '<input name="id_pregunta" type="hidden" id="id_pregunta" value="'.$row['id_pregunta'].'" size="120" required>';

echo '<p><label for="cod_pregunta">Código:</label></p>';
echo '<input name="cod_pregunta" type="text"  maxlength="255" id="cod_pregunta"  maxlength="255" value="'.$row['cod_pregunta'].'" required></p>';
echo '<p><label for="pregunta">Pregunta:</label></p>';
echo '<input name="pregunta" type="text"  maxlength="3000" id="pregunta"  maxlength="3000" value="'.$row['pregunta'].'" required></p>';
echo '<p><label for="categoria">Categoría:</label></p>';
echo '<br>';
$sql= "SELECT * FROM categoria;";
echo '<select name="categoria" id="categoria"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row2=$consulta->fetch_assoc()){
echo '<option ';
if($row['categoria']==$row2['id_categoria']) echo " selected ";
echo 'value="'.$row2['id_categoria'].'">'.$row2['nombre_categoria'].'</option>';
}
echo '</select></p>';
echo '<p><label for="tipo">Tipo:</label></p>';
echo '<input name="tipo" type="text"  maxlength="255" id="tipo"  maxlength="255" value="'.$row['tipo'].'" required></p>';
echo '<a href="pregunta.php">Regresar</a>
<p><input type="submit" name="submit" id="submit" value="Actualizar"></p>
</form>';
}
break;
default:
echo "Ingreso erroneo";
}//fin switch
}else{
?>
<div align="center">
<b><label>Buscar: </label></b><input type="text" id="buscar" onkeyup ="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</div>
</p>
<span id="txtsugerencias">
<?php
buscar_pregunta();
?>
</span>
<?php
}//fin else if isset cod
?>
</section>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
?>