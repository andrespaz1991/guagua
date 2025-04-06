<?php 
ob_start();
echo '<center>';
require("../comun/conexion.php");
 /*require_once("funciones.php");*/ 
function buscar_grupo_foro($datos="",$reporte=""){
require("../comun/conexion.php");
$sql = "SELECT `grupo_foro`.`id_grupo_foro`, `grupo_foro`.`nombre_grupo` FROM `grupo_foro`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`grupo_foro`.`id_grupo_foro`)," ", LOWER(`grupo_foro`.`nombre_grupo`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `grupo_foro`.`id_grupo_foro` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_grupo_foro']) and $_COOKIE['numeroresultados_grupo_foro']!="") $sql .=$_COOKIE['numeroresultados_grupo_foro'];
else $sql .= "10";
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbgrupo_foro">
<thead>
<tr>
<th>Id Grupo Foro</th>
<th>Nombre Grupo</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="grupo_foro.php">
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
<td><?php echo $row['id_grupo_foro']?></td>
<td><?php echo $row['nombre_grupo']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="grupo_foro.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_grupo_foro']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_URL; ?>/comun/img/eliminar.png" onClick="confirmeliminar2('grupo_foro.php',{'del':'<?php echo $row['id_grupo_foro'];?>'},'<?php echo $row['id_grupo_foro'];?>');" value="Eliminar">
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
buscar_grupo_foro($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM grupo_foro WHERE id_grupo_foro="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=grupo_foro.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=grupo_foro.php" />
<?php 
}
}
 ?>
<center>
<h1>Grupo Foro</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO grupo_foro (`id_grupo_foro`, `nombre_grupo`) VALUES ('".$_POST['id_grupo_foro']."', '".$_POST['nombre_grupo']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=grupo_foro.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=grupo_foro.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="grupo_foro.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_grupo_foro']))  echo $row['id_grupo_foro'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_grupo_foro"type="hidden" id="id_grupo_foro" value="';if (isset($row['id_grupo_foro'])) echo $row['id_grupo_foro'];echo '"';echo '></p>';
echo '<p><label for="nombre_grupo">Nombre Grupo:</label><input class=""name="nombre_grupo"type="text" id="nombre_grupo" value="';if (isset($row['nombre_grupo'])) echo $row['nombre_grupo'];echo '"';echo ' required ></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_grupo_foro`, `nombre_grupo` FROM `grupo_foro` WHERE id_grupo_foro ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="grupo_foro.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_grupo_foro']))  echo $row['id_grupo_foro'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_grupo_foro"type="hidden" id="id_grupo_foro" value="';if (isset($row['id_grupo_foro'])) echo $row['id_grupo_foro'];echo '"';echo '></p>';
echo '<p><label for="nombre_grupo">Nombre Grupo:</label><input class=""name="nombre_grupo"type="text" id="nombre_grupo" value="';if (isset($row['nombre_grupo'])) echo $row['nombre_grupo'];echo '"';echo ' required ></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE grupo_foro SET id_grupo_foro='".$_POST['id_grupo_foro']."', nombre_grupo='".$_POST['nombre_grupo']."'WHERE  id_grupo_foro = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=grupo_foro.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=grupo_foro.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_grupo_foro" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_grupo_foro',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_grupo_foro',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_grupo_foro',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_grupo_foro();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_grupo_foro').className ='active '+document.getElementById('menu_grupo_foro').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>
