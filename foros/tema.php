<?php 
ob_start();
@session_start();
if(isset($_SESSION['usuario']) and $_SESSION['rol'] == "admin"){
}else{
	echo "Ingreso incorrecto";
exit();
}
echo '<center>';
require("../comun/conexion.php");
 /*require_once("funciones.php");*/ 
function buscar_tema_foro($datos="",$reporte=""){

require("../comun/conexion.php");

$sql = "SELECT `tema_foro`.`id`, `tema_foro`.`nombre` FROM `tema_foro`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`tema_foro`.`id`)," ", LOWER(`tema_foro`.`nombre`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `tema_foro`.`id` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_tema_foro']) and $_COOKIE['numeroresultados_tema_foro']!="") $sql .=$_COOKIE['numeroresultados_tema_foro'];
else $sql .= "10";
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbtema_foro">
<thead>
<tr>
<th>Id</th>
<th>Nombre</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="tema_foro.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
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
<td><?php echo $row['nombre']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="tema_foro.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_URL; ?>/comun/img/eliminar.png" onClick="confirmeliminar2('tema_foro.php',{'del':'<?php echo $row['id'];?>'},'<?php echo $row['id'];?>');" value="Eliminar">
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
buscar_tema_foro($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM tema_foro WHERE id="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=tema_foro.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=tema_foro.php" />
<?php 
}
}
 ?>
<center>
<h1>tema_foro</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO tema_foro (`id`, `nombre`) VALUES ('".$_POST['id']."', '".$_POST['nombre']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=tema_foro.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=tema_foro.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="tema_foro.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id']))  echo $row['id'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id"type="hidden" id="id" value="';if (isset($row['id'])) echo $row['id'];echo '"';echo '></p>';
echo '<p><label for="nombre">Nombre:</label><input class=""name="nombre"type="text" id="nombre" value="';if (isset($row['nombre'])) echo $row['nombre'];echo '"';echo ' required ></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id`, `nombre` FROM `tema_foro` WHERE id ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="tema_foro.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id']))  echo $row['id'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id"type="hidden" id="id" value="';if (isset($row['id'])) echo $row['id'];echo '"';echo '></p>';
echo '<p><label for="nombre">Nombre:</label><input class=""name="nombre"type="text" id="nombre" value="';if (isset($row['nombre'])) echo $row['nombre'];echo '"';echo ' required ></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE tema_foro SET id='".$_POST['id']."', nombre='".$_POST['nombre']."'WHERE  id = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=tema_foro.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=tema_foro.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_tema_foro" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_tema_foro',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_tema_foro',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_tema_foro',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_tema_foro();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_tema_foro').className ='active '+document.getElementById('menu_tema_foro').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
