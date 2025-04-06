<?php 
ob_start();
echo '<center>';
require("conexion.php");
 /*require("funciones.php");*/ 
function buscar_institucion_educativa($datos="",$reporte=""){

require("conexion.php");

$sql = "SELECT `institucion_educativa`.`id_institucion_educativa`, `institucion_educativa`.`nombre_institucion`, `institucion_educativa`.`logo_institucion`, `institucion_educativa`.`direccion_institucion`, `institucion_educativa`.`telefono_institucion`, `institucion_educativa`.`correo_institucion` FROM `institucion_educativa`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`institucion_educativa`.`id_institucion_educativa`)," ", LOWER(`institucion_educativa`.`nombre_institucion`)," ", LOWER(`institucion_educativa`.`logo_institucion`)," ", LOWER(`institucion_educativa`.`direccion_institucion`)," ", LOWER(`institucion_educativa`.`telefono_institucion`)," ", LOWER(`institucion_educativa`.`correo_institucion`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `institucion_educativa`.`id_institucion_educativa` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_institucion_educativa']) and $_COOKIE['numeroresultados_institucion_educativa']!="") $sql .=$_COOKIE['numeroresultados_institucion_educativa'];
else $sql .= "10";
#echo $sql;

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbinstitucion_educativa">
<thead>
<tr>
<th>Id Institucion Educativa</th>
<th>Nombre Institucion</th>
<th>Logo Institucion</th>
<th>Direccion Institucion</th>
<th>Telefono Institucion</th>
<th>Correo Institucion</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="institucion_educativa.php">
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
<td><?php echo $row['id_institucion_educativa']?></td>
<td><?php echo $row['nombre_institucion']?></td>
<td><?php echo $row['logo_institucion']?></td>
<td><?php echo $row['direccion_institucion']?></td>
<td><?php echo $row['telefono_institucion']?></td>
<td><?php echo $row['correo_institucion']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="../usuario/configuraciones.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_institucion_educativa']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('institucion_educativa.php',{'del':'<?php echo $row['id_institucion_educativa'];?>'},'<?php echo $row['id_institucion_educativa'];?>');" value="Eliminar">
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
buscar_institucion_educativa($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM institucion_educativa WHERE id_institucion_educativa="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=institucion_educativa.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=institucion_educativa.php" />
<?php 
}
}
 ?>
<center>
<h1>Institucion Educativa</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO institucion_educativa (`id_institucion_educativa`, `nombre_institucion`, `logo_institucion`, `direccion_institucion`, `telefono_institucion`, `correo_institucion`) VALUES ('".$_POST['id_institucion_educativa']."', '".$_POST['nombre_institucion']."', '".$_POST['logo_institucion']."', '".$_POST['direccion_institucion']."', '".$_POST['telefono_institucion']."', '".$_POST['correo_institucion']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=institucion_educativa.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=institucion_educativa.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="institucion_educativa.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_institucion_educativa']))  echo $row['id_institucion_educativa'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_institucion_educativa"type="hidden" id="id_institucion_educativa" value="';if (isset($row['id_institucion_educativa'])) echo $row['id_institucion_educativa'];echo '"';echo '></p>';
echo '<p><label for="nombre_institucion">Nombre Institucion:</label><input class=""name="nombre_institucion"type="text" id="nombre_institucion" value="';if (isset($row['nombre_institucion'])) echo $row['nombre_institucion'];echo '"';echo ' required ></p>';
echo '<p><label for="logo_institucion">Logo Institucion:</label><input class=""name="logo_institucion"type="text" id="logo_institucion" value="';if (isset($row['logo_institucion'])) echo $row['logo_institucion'];echo '"';echo '></p>';
echo '<p><label for="direccion_institucion">Direccion Institucion:</label></p><p><textarea class="" name="direccion_institucion" cols="60" rows="10"id="direccion_institucion" >';if (isset($row['direccion_institucion'])) echo $row['direccion_institucion'];echo '</textarea></p>';
echo '<p><label for="telefono_institucion">Telefono Institucion:</label><input class=""name="telefono_institucion"type="number"  min="0" id="telefono_institucion" value="';if (isset($row['telefono_institucion'])) echo $row['telefono_institucion'];echo '"';echo '></p>';
echo '<p><label for="correo_institucion">Correo Institucion:</label><input class=""name="correo_institucion"type="email" id="correo_institucion" value="';if (isset($row['correo_institucion'])) echo $row['correo_institucion'];echo '"';echo '></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_institucion_educativa`, `nombre_institucion`, `logo_institucion`, `direccion_institucion`, `telefono_institucion`, `correo_institucion` FROM `institucion_educativa` WHERE id_institucion_educativa ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="institucion_educativa.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_institucion_educativa']))  echo $row['id_institucion_educativa'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_institucion_educativa"type="hidden" id="id_institucion_educativa" value="';if (isset($row['id_institucion_educativa'])) echo $row['id_institucion_educativa'];echo '"';echo '></p>';
echo '<p><label for="nombre_institucion">Nombre Institucion:</label><input class=""name="nombre_institucion"type="text" id="nombre_institucion" value="';if (isset($row['nombre_institucion'])) echo $row['nombre_institucion'];echo '"';echo ' required ></p>';
echo '<p><label for="logo_institucion">Logo Institucion:</label><input class=""name="logo_institucion"type="text" id="logo_institucion" value="';if (isset($row['logo_institucion'])) echo $row['logo_institucion'];echo '"';echo '></p>';
echo '<p><label for="direccion_institucion">Direccion Institucion:</label></p><p><textarea class="" name="direccion_institucion" cols="60" rows="10"id="direccion_institucion" >';if (isset($row['direccion_institucion'])) echo $row['direccion_institucion'];echo '</textarea></p>';
echo '<p><label for="telefono_institucion">Telefono Institucion:</label><input class=""name="telefono_institucion"type="number"  min="0" id="telefono_institucion" value="';if (isset($row['telefono_institucion'])) echo $row['telefono_institucion'];echo '"';echo '></p>';
echo '<p><label for="correo_institucion">Correo Institucion:</label><input class=""name="correo_institucion"type="email" id="correo_institucion" value="';if (isset($row['correo_institucion'])) echo $row['correo_institucion'];echo '"';echo '></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE institucion_educativa SET id_institucion_educativa='".$_POST['id_institucion_educativa']."', nombre_institucion='".$_POST['nombre_institucion']."', logo_institucion='".$_POST['logo_institucion']."', direccion_institucion='".$_POST['direccion_institucion']."', telefono_institucion='".$_POST['telefono_institucion']."', correo_institucion='".$_POST['correo_institucion']."'WHERE  id_institucion_educativa = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=institucion_educativa.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=institucion_educativa.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_institucion_educativa" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_institucion_educativa',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_institucion_educativa',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_institucion_educativa',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_institucion_educativa();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_institucion_educativa').className ='active '+document.getElementById('menu_institucion_educativa').className;
</script>
<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
 ?>
