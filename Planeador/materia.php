<?php 
ob_start();
echo '<center>';
require("conexion.php");
?>
<?php 
 /*require("funciones.php");*/ 
function buscar_materia($datos="",$reporte=""){
require("conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_materia']) ? $_COOKIE['numeroresultados_materia'] : 4);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `materia`.`id_materia`, `materia`.`nombre_materia`, `materia`.`descripcion_materia` FROM `materia`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`materia`.`id_materia`)," ", LOWER(`materia`.`nombre_materia`)," ", LOWER(`materia`.`descripcion_materia`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';

$consuta=$mysqli->query($sql);
echo $paginacion->records($consuta->num_rows);

$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= "";
}


if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `materia`.`id_materia` desc LIMIT ";


if (isset($_COOKIE['numeroresultados_materia']) and $_COOKIE['numeroresultados_materia']!="") 
{
   # $sql.=$_COOKIE['numeroresultados_materia'];
}


$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#else $sql .= "100000";
#echo $sql;
#else $sql.= "10";
/*echo $sql;*/ 
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

<table border="1" id="tbmateria">
<thead>
<tr>
<th>Id Materia</th>
<th>Nombre Materia</th>
<th>Descripcion Materia</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="materia.php">
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
<td><?php echo $row['id_materia']?></td>
<td><?php echo $row['nombre_materia']?></td>
<td><?php echo $row['descripcion_materia']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="materia.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_materia']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('materia.php',{'del':'<?php echo $row['id_materia'];?>'},'<?php echo $row['id_materia'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<div class="text-center">
    <?php    $paginacion->render2();    ?>
    </div>
</div>

</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_materia($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM materia WHERE id_materia="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=materia.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=materia.php" />
<?php 
}
}
 ?>
<center>
<h1>Materia</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO materia ( `nombre_materia`, `descripcion_materia`) VALUES ( '".$_POST['nombre_materia']."', '".$_POST['descripcion_materia']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=materia.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=materia.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="materia.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_materia']))  echo $row['id_materia'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_materia"type="hidden" id="id_materia" value="';if (isset($row['id_materia'])) echo $row['id_materia'];echo '"';echo '></p>';
echo '<p><label for="nombre_materia">Nombre Materia:</label><input class=""name="nombre_materia"type="text" id="nombre_materia" value="';if (isset($row['nombre_materia'])) echo $row['nombre_materia'];echo '"';echo ' required ></p>';
echo '<p><label for="descripcion_materia">Descripcion Materia:</label></p><p><textarea class="" name="descripcion_materia" cols="60" rows="10"id="descripcion_materia" >';if (isset($row['descripcion_materia'])) echo $row['descripcion_materia'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_materia`, `nombre_materia`, `descripcion_materia` FROM `materia` WHERE id_materia ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="materia.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_materia']))  echo $row['id_materia'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_materia"type="hidden" id="id_materia" value="';if (isset($row['id_materia'])) echo $row['id_materia'];echo '"';echo '></p>';
echo '<p><label for="nombre_materia">Nombre Materia:</label><input class=""name="nombre_materia"type="text" id="nombre_materia" value="';if (isset($row['nombre_materia'])) echo $row['nombre_materia'];echo '"';echo ' required ></p>';
echo '<p><label for="descripcion_materia">Descripcion Materia:</label></p><p><textarea class="" name="descripcion_materia" cols="60" rows="10"id="descripcion_materia" >';if (isset($row['descripcion_materia'])) echo $row['descripcion_materia'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE materia SET id_materia='".$_POST['id_materia']."', nombre_materia='".$_POST['nombre_materia']."', descripcion_materia='".$_POST['descripcion_materia']."'WHERE  id_materia = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=materia.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=materia.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_materia" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_materia',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_materia',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_materia',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_materia();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_materia').className ='active '+document.getElementById('menu_materia').className;
</script>
<?php
$contenido = ob_get_clean();
require '../comun/plantilla.php'; 
?>
