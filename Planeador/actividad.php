<?php 
ob_start();
echo '<center>';
require("conexion.php");
 /*require("funciones.php");*/ 
function buscar_actividad($datos="",$reporte=""){
require("conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_actividad']) ? $_COOKIE['numeroresultados_actividad'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];

$sql = "SELECT `actividades`.`actividad`, `actividades`.`descripcion_actividad` FROM `actividades`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`actividades`.`actividad`)," ", LOWER(`actividades`.`descripcion_actividad`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$consulta=$mysqli->query($sql);
echo $paginacion->records($consulta->num_rows);

$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= "";
}

if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `actividades`.`actividad` desc LIMIT ";

if (isset($_COOKIE['numeroresultados_actividad']) and $_COOKIE['numeroresultados_actividad']!="") 
{
   # $sql.=$_COOKIE['numeroresultados_materia'];
}


#else $sql .= "10";

$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#echo $sql;
require_once 'template/menu.php';
$consulta = $mysqli->query($sql);

$numero_usuario = $consulta->num_rows;
$minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
$maximo_usuario += $minimo_usuario-1;
echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$numero_usuario." en página ".$paginacion->get_page()."</p>";

 ?>
<div align="center">
<table border="1" id="tbactividad">
<thead>
<tr>
<th>Actividad</th>
<th>Descripcion Actividad</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="actividad.php">
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
<td><?php echo $row['actividad']?></td>
<td><?php echo $row['descripcion_actividad']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="actividad.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['actividad']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('actividad.php',{'del':'<?php echo $row['actividad'];?>'},'<?php echo $row['actividad'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
2
</div>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_actividad($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM actividades WHERE actividad="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=actividad.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=actividad.php" />
<?php 
}
}
 ?>
<center>
<h1>Actividad</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO actividades (`actividad`, `descripcion_actividad`) VALUES ('".$_POST['actividad']."', '".$_POST['descripcion_actividad']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=actividad.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=actividad.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="actividad.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['actividad']))  echo $row['actividad'] ?>" size="120" required></p>
<?php 
echo '<p><label for="actividad">Actividad:</label><input class=""name="actividad"type="text" id="actividad" value="';if (isset($row['actividad'])) echo $row['actividad'];echo '"';echo '></p>';
echo '<p><label for="descripcion_actividad">Descripcion Actividad:</label></p><p><textarea class="" name="descripcion_actividad" cols="60" rows="10"id="descripcion_actividad" >';if (isset($row['descripcion_actividad'])) echo $row['descripcion_actividad'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `actividad`, `descripcion_actividad` FROM `actividades` WHERE actividad ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="actividad.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['actividad']))  echo $row['actividad'] ?>" size="120" required></p>
<?php 
echo '<p><label for="actividad">Actividad:</label><input class=""name="actividad"type="text" id="actividad" value="';if (isset($row['actividad'])) echo $row['actividad'];echo '"';echo '></p>';
echo '<p><label for="descripcion_actividad">Descripcion Actividad:</label></p><p><textarea class="" name="descripcion_actividad" cols="60" rows="10"id="descripcion_actividad" >';if (isset($row['descripcion_actividad'])) echo $row['descripcion_actividad'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE actividades SET actividad='".$_POST['actividad']."', descripcion_actividad='".$_POST['descripcion_actividad']."'WHERE  actividad = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=actividad.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=actividad.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_actividad" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_actividad',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_actividad',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_actividad',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_actividad();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_actividad').className ='active '+document.getElementById('menu_actividad').className;
</script>
<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
 ?>
