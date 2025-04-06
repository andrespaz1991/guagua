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
function buscar_comentario($datos="",$reporte=""){

require("../comun/conexion.php");

$sql = "SELECT `comentario`.`id_comentario`, `comentario`.`id_entrada`, `entrada`.`titulo` as entradatitulo, `comentario`.`fecha`, `comentario`.`contenido`, `comentario`.`usuario`, `usuario`.`usuario` as usuariousuario FROM `comentario`  inner join `entrada` on `comentario`.`id_entrada` = `entrada`.`id` inner join `usuario` on `comentario`.`usuario` = `usuario`.`id_usuario`  ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`comentario`.`id_comentario`)," ", LOWER(`entrada`.`titulo`)," ", LOWER(`comentario`.`fecha`)," ", LOWER(`comentario`.`contenido`)," ", LOWER(`usuario`.`usuario`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `comentario`.`id_comentario` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_comentario']) and $_COOKIE['numeroresultados_comentario']!="") $sql .=$_COOKIE['numeroresultados_comentario'];
else $sql .= "10";
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbcomentario">
<thead>
<tr>
<th>Id Comentario</th>
<th>Id Entrada</th>
<th>Fecha</th>
<th>Contenido</th>
<th>Usuario</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="comentario.php">
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
<td><?php echo $row['id_comentario']?></td>
<td><?php echo $row['entradatitulo']?></td>
<?php $meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e'); ?>

<td><?php echo  date("d \\d\\e ".$meses[date("n",strtotime($row['fecha']))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($row['fecha'])); ?></td>
<td><?php echo $row['contenido']?></td>
<td><?php echo $row['usuariousuario']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="comentario.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_comentario']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_URL; ?>/comun/img/eliminar.png" onClick="confirmeliminar2('comentario.php',{'del':'<?php echo $row['id_comentario'];?>'},'<?php echo $row['id_comentario'];?>');" value="Eliminar">
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
buscar_comentario($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM comentario WHERE id_comentario="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=comentario.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=comentario.php" />
<?php 
}
}
 ?>
<center>
<h1>Comentario</h1>
</center><?php 
if (isset($_POST['submit'])){
#print_r($_POST);
#exit();
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO comentario (`id_comentario`, `id_entrada`, `fecha`, `contenido`, `usuario`) VALUES ('".$_POST['id_comentario']."', '".$_POST['id_entrada']."', '".$_POST['fecha']."', '".$_POST['contenido']."', '".$_POST['usuario']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=comentario.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=comentario.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";


} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_comentario`, `id_entrada`, `fecha`, `contenido`, `usuario` FROM `comentario` WHERE id_comentario ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="comentario.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_comentario']))  echo $row['id_comentario'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_comentario"type="hidden" id="id_comentario" value="';if (isset($row['id_comentario'])) echo $row['id_comentario'];echo '"';echo '></p>';
echo '<p><label for="id_entrada">Id Entrada:</label>';
$sql2= "SELECT id,titulo FROM entrada;";
echo '<select class="" name="id_entrada" id="id_entrada"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option value="'.$row2['id'].'"';if (isset($row['id_entrada']) and $row['id_entrada'] == $row2['id']) echo " selected ";echo '>'.$row2['titulo'].'</option>';
}
echo '</select></p>';
echo '<p><label for="fecha">Fecha:</label><input class=""name="fecha"type="date" id="fecha" value="';if (isset($row['fecha'])) echo $row['fecha'];echo '"';echo ' required ></p>';
echo '<p><label for="contenido">Contenido:</label></p><p><textarea class="" name="contenido" cols="60" rows="10"id="contenido"  required>';if (isset($row['contenido'])) echo $row['contenido'];echo '</textarea></p>';
echo '<p><label for="usuario">Usuario:</label>';
$sql5= "SELECT id_usuario,usuario FROM usuario;";
echo '<select class="" name="usuario" id="usuario"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta5 = $mysqli->query($sql5);
while($row5=$consulta5->fetch_assoc()){
echo '<option value="'.$row5['id_usuario'].'"';if (isset($row['usuario']) and $row['usuario'] == $row5['id_usuario']) echo " selected ";echo '>'.$row5['usuario'].'</option>';
}
echo '</select></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE comentario SET id_comentario='".$_POST['id_comentario']."', id_entrada='".$_POST['id_entrada']."', fecha='".$_POST['fecha']."', contenido='".$_POST['contenido']."', usuario='".$_POST['usuario']."'WHERE  id_comentario = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=comentario.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=comentario.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_comentario" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_comentario',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_comentario',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_comentario',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_comentario();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_comentario').className ='active '+document.getElementById('menu_comentario').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
