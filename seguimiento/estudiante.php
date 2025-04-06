<?php 
ob_start();
#include 'menu.php';
require('conexion.php'); 
//require('funciones.php');?>
<!doctype html>
<head>
<link rel="stylesheet" type="text/css" href="css/estilo.css" /><!--estilos -->
<title>Guagua</title>
<style type="text/css">
.h {
  font-family: Comic Sans MS, cursive;
}
</style>
<?php
echo '<center>';
require("conexion.php");
 /*require("funciones.php");*/ 
function buscar_estudiante($datos="",$reporte=""){
require("conexion.php");
$sql = "SELECT * FROM `usuario`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
#$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
/*$sql .= ' concat(LOWER(`estudiante`.`identificacion`)," ", LOWER(`estudiante`.`nombre`)," ", LOWER(`estudiante`.`celular`)," ", LOWER(`estudiante`.`direccion`)," ", LOWER(`estudiante`.`correo`)," ", LOWER(`estudiante`.`observaciones`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';*/
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
#$sql .= " and ";
}
}
#$sql .=  " ORDER BY `estudiante`.`identificacion` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_estudiante']) and $_COOKIE['numeroresultados_estudiante']!="") $sql .=$_COOKIE['numeroresultados_estudiante'];
else #$sql .= "10";
echo $sql;

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbestudiante">
<thead>
<tr>
<th>Identificacion</th>
<th>Nombre</th>
<th>Celular</th>
<th>Direccion</th>
<th>Correo</th>
<th>Observaciones</th>
<th>Reporte</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="estudiante.php">
<input name="cod" type="hidden" id="cod" value="0">
<input class="btn btn-success" type="submit" name="submit" id="submit" value="Nuevo">
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td><?php echo $row['identificacion']?></td>
<td><?php  if($row['nombre']<>"null")
echo $row['nombre']?></td>
<td><?php  if($row['celular']<>"null")
echo $row['celular']?></td>
<td><?php  if($row['direccion']<>"null")
echo $row['direccion']?></td>
<td><?php  if($row['correo']<>"null")
echo $row['correo']?></td>
<td><?php  if($row['observaciones']<>"null")
echo $row['observaciones']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="estudiante.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['identificacion']?>">
<input  class="btn btn-success" type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td><a target="_blank" href="pdf.php?estudiante=<?php echo $row['identificacion']; ?>"><img src ="img/pdf.png"></a></td> 

<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('estudiante.php',{'del':'<?php echo $row['identificacion'];?>'},'<?php echo $row['identificacion'];?>');" value="Eliminar">
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
buscar_estudiante($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM estudiante WHERE identificacion="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=estudiante.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=estudiante.php" />
<?php 
}
}
 ?>
<center>
<?php
require_once 'mysql.class.php';
require_once 'persona.class.php';
$Persona=new Persona();
 ?>
 <a href="plantillas/estudiantes.csv">
<h1>Estudiantes <?php echo $Persona->cantidad_personas(); ?></h1></a>

<form action ="importar.php" ENCTYPE="multipart/form-data" method="POST">
 <input  type="file" name="datos">
 <input class="btn btn-primary" type="submit" value="importar">
</form>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO estudiante (`identificacion`, `nombre`, `celular`, `direccion`, `correo`, `observaciones`) VALUES ('".$_POST['identificacion']."', '".ucwords(strtolower($_POST['nombre']))."', '".$_POST['celular']."', '".$_POST['direccion']."', '".$_POST['correo']."', '".$_POST['observaciones']."')";


 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=estudiante.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=estudiante.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="estudiante.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['identificacion']))  echo $row['identificacion'] ?>" size="120" required></p>
<?php 
echo '<p><label for="identificacion">Identificacion:</label><input class=""name="identificacion"type="text"  min="0" id="identificacion" value="';if (isset($row['identificacion'])) echo $row['identificacion'];echo '"';echo '></p>';
echo '<p><label for="nombre">Nombre:</label><input class=""name="nombre"type="text" id="nombre" value="';if (isset($row['nombre'])) echo $row['nombre'];echo '"';echo ' required ></p>';
echo '<p><label for="celular">Celular:</label><input class=""name="celular"type="text" id="celular" value="';if (isset($row['celular'])) echo $row['celular'];echo '"';echo '></p>';
echo '<p><label for="direccion">Direccion:</label><input class=""name="direccion"type="text" id="direccion" value="';if (isset($row['direccion'])) echo $row['direccion'];echo '"';echo '></p>';
echo '<p><label for="correo">Correo:</label><input class=""name="correo"type="email" id="correo" value="';if (isset($row['correo'])) echo $row['correo'];echo '"';echo '></p>';
echo '<p><label for="observaciones">Observaciones:</label><input class=""name="observaciones"type="text" id="observaciones" value="';if (isset($row['observaciones'])) echo $row['observaciones'];echo '"';echo '></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `identificacion`, `nombre`, `celular`, `direccion`, `correo`, `observaciones` FROM `estudiante` WHERE identificacion ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="estudiante.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['identificacion']))  echo $row['identificacion'] ?>" size="120" required></p>
<?php 
echo '<p><label for="identificacion">Identificacion:</label><input class=""name="identificacion"type="text"  min="0" id="identificacion" value="';if (isset($row['identificacion'])) echo $row['identificacion'];echo '"';echo '></p>';
echo '<p><label for="nombre">Nombre:</label><input class=""name="nombre"type="text" id="nombre" value="';if (isset($row['nombre'])) echo $row['nombre'];echo '"';echo ' required ></p>';
echo '<p><label for="celular">Celular:</label><input class=""name="celular"type="text" id="celular" value="';if (isset($row['celular'])) echo $row['celular'];echo '"';echo '></p>';
echo '<p><label for="direccion">Direccion:</label><input class=""name="direccion"type="text" id="direccion" value="';if (isset($row['direccion'])) echo $row['direccion'];echo '"';echo '></p>';
echo '<p><label for="correo">Correo:</label><input class=""name="correo"type="email" id="correo" value="';if (isset($row['correo'])) echo $row['correo'];echo '"';echo '></p>';
echo '<p><label for="observaciones">Observaciones:</label><input class=""name="observaciones"type="text" id="observaciones" value="';if (isset($row['observaciones'])) echo $row['observaciones'];echo '"';echo '></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE estudiante SET identificacion='".$_POST['identificacion']."', nombre='".$_POST['nombre']."', celular='".$_POST['celular']."', direccion='".$_POST['direccion']."', correo='".$_POST['correo']."', observaciones='".$_POST['observaciones']."'WHERE  identificacion = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=estudiante.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=estudiante.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_estudiante" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_estudiante',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_estudiante',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_estudiante',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_estudiante();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_estudiante').className ='active '+document.getElementById('menu_estudiante').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
 ?>
