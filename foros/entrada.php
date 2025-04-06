<?php 
ob_start();
@session_start();
if(isset($_SESSION['usuario']) and $_SESSION['rol'] == "admin"){
}else{
	echo "Ingreso incorrecto";
exit();
}
echo '<center>';
require_once("../comun/conexion.php");
 /*require_once("funciones.php");*/ 
function buscar_entrada($datos="",$reporte=""){

require_once("../comun/conexion.php");

$sql = "SELECT `entrada`.`id`, `entrada`.`titulo`, `entrada`.`contenido`, `entrada`.`fecha`, `entrada`.`usuario` FROM `entrada`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`entrada`.`id`)," ", LOWER(`entrada`.`titulo`)," ", LOWER(`entrada`.`contenido`)," ", LOWER(`entrada`.`fecha`)," ", LOWER(`entrada`.`usuario`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `entrada`.`id` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_entrada']) and $_COOKIE['numeroresultados_entrada']!="") $sql .=$_COOKIE['numeroresultados_entrada'];
else $sql .= "10";
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbentrada">
<thead>
<tr>
<th>Id</th>
<th>Titulo</th>
<th>Contenido</th>
<th>Fecha de publicación</th>
<th>Usuario</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="entrada.php">
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
<td><?php echo $row['titulo']?></td>
<td><?php echo $row['contenido']?></td>
<?php $meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e'); ?>

<td><?php echo  date("d \\d\\e ".$meses[date("n",strtotime($row['fecha']))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($row['fecha'])); ?></td>
<td><?php echo $row['usuario']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="entrada.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_URL; ?>/comun/img/eliminar.png" onClick="confirmeliminar2('entrada.php',{'del':'<?php echo $row['id'];?>'},'<?php echo $row['id'];?>');" value="Eliminar">
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
buscar_entrada($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM entrada WHERE id="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=entrada.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=entrada.php" />
<?php 
}
}
 ?>
<center>
<h1>Entrada</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO entrada (`id`, `titulo`, `contenido`, `fecha`, `usuario`) VALUES ('".$_POST['id']."', '".$_POST['titulo']."', '".$_POST['contenido']."', '".$_POST['fecha']."', '".$_SESSION['usuario']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=entrada.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=entrada.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="entrada.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id']))  echo $row['id'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id"type="hidden" id="id" value="';if (isset($row['id'])) echo $row['id'];echo '"';echo '></p>';
echo '<p><label for="titulo">Titulo:</label><input class=""name="titulo"type="text" id="titulo" value="';if (isset($row['titulo'])) echo $row['titulo'];echo '"';echo ' required ></p>';
echo '<p><label for="contenido">Contenido:</label></p><p><textarea class="" name="contenido" cols="60" rows="10"id="contenido"  required>';if (isset($row['contenido'])) echo $row['contenido'];echo '</textarea></p>';
echo '<p><label for="fecha">Fecha:</label><input class=""name="fecha"type="date" id="fecha" value="';if (isset($row['fecha'])) echo $row['fecha'];echo '"';echo ' required ></p>';
echo '<p><input class=""name="usuario"type="hidden" id="usuario" value="';if (isset($row['usuario'])) echo $row['usuario'];echo '"';echo ' required ></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id`, `titulo`, `contenido`, `fecha`, `usuario` FROM `entrada` WHERE id ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="entrada.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id']))  echo $row['id'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id"type="hidden" id="id" value="';if (isset($row['id'])) echo $row['id'];echo '"';echo '></p>';
echo '<p><label for="titulo">Titulo:</label><input class=""name="titulo"type="text" id="titulo" value="';if (isset($row['titulo'])) echo $row['titulo'];echo '"';echo ' required ></p>';
echo '<p><label for="contenido">Contenido:</label></p><p><textarea class="" name="contenido" cols="60" rows="10"id="contenido"  required>';if (isset($row['contenido'])) echo $row['contenido'];echo '</textarea></p>';
echo '<p><label for="fecha">Fecha:</label><input class=""name="fecha"type="date" id="fecha" value="';if (isset($row['fecha'])) echo $row['fecha'];echo '"';echo ' required ></p>';
echo '<p><input class=""name="usuario"type="hidden" id="usuario" value="';if (isset($row['usuario'])) echo $row['usuario'];echo '"';echo ' required ></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE entrada SET id='".$_POST['id']."', titulo='".$_POST['titulo']."', contenido='".$_POST['contenido']."', fecha='".$_POST['fecha']."', usuario='".$_POST['usuario']."'WHERE  id = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=entrada.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=entrada.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_entrada" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_entrada',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_entrada',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_entrada',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_entrada();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_entrada').className ='active '+document.getElementById('menu_entrada').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
