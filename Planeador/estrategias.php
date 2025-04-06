<?php 
ob_start();
require("conexion.php");
require_once("../comun/config.php");
require_once("../comun/autoload.php");

echo '<center>';
 /*require("funciones.php");*/ 
function buscar_estrategias($datos="",$reporte=""){ 
require("conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_estrategias']) ? $_COOKIE['numeroresultados_estrategias'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `estrategias`.`estrategia`, `estrategias`.`descripcion_estrategia` FROM `estrategias`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`estrategias`.`estrategia`)," ", LOWER(`estrategias`.`descripcion_estrategia`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
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
$sql .=  " ORDER BY `estrategias`.`estrategia` desc LIMIT ";
/*
if (isset($_COOKIE['numeroresultados_estrategias']) and $_COOKIE['numeroresultados_estrategias']!="") $sql .=$_COOKIE['numeroresultados_estrategias'];
else $sql .= "10";*/

/*echo $sql;*/ 
$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;

$consulta = $mysqli->query($sql);
require_once 'template/menu.php';
$numero_usuario = $consulta->num_rows;
$minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
$maximo_usuario += $minimo_usuario-1;
echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$numero_usuario." en página ".$paginacion->get_page()."</p>";
 ?>

<div align="center">

<table border="1" id="tbestrategias">
<thead>
<tr>
<th>Estrategia</th>
<th>Descripcion Estrategia</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="estrategias.php">
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
<td><?php echo $row['estrategia']?></td>
<td><?php echo $row['descripcion_estrategia']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="estrategias.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['estrategia']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('estrategias.php',{'del':'<?php echo $row['estrategia'];?>'},'<?php echo $row['estrategia'];?>');" value="Eliminar">
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
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_estrategias($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM estrategias WHERE estrategia="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=estrategias.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=estrategias.php" />
<?php 
}
}
 ?>
<center>
<h1>Estrategia</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO estrategias (`estrategia`, `descripcion_estrategia`) VALUES ('".$_POST['estrategia']."', '".$_POST['descripcion_estrategia']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=estrategias.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=estrategias.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="estrategias.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['estrategia']))  echo $row['estrategia'] ?>" size="120" required></p>
<?php 
echo '<p><label for="estrategia">Estrategia:</label><input class=""name="estrategia"type="text" id="estrategia" value="';if (isset($row['estrategia'])) echo $row['estrategia'];echo '"';echo '></p>';
echo '<p><label for="descripcion_estrategia">Descripcion Estrategia:</label></p><p><textarea class="" name="descripcion_estrategia" cols="60" rows="10"id="descripcion_estrategia"  >';if (isset($row['descripcion_estrategia'])) echo $row['descripcion_estrategia'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `estrategia`, `descripcion_estrategia` FROM `estrategias` WHERE estrategia ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="estrategias.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['estrategia']))  echo $row['estrategia'] ?>" size="120" required></p>
<?php 
echo '<p><label for="estrategia">Estrategia:</label><input class=""name="estrategia"type="text" id="estrategia" value="';if (isset($row['estrategia'])) echo $row['estrategia'];echo '"';echo '></p>';
echo '<p><label for="descripcion_estrategia">Descripcion Estrategia:</label></p><p><textarea class="" name="descripcion_estrategia" cols="60" rows="10"id="descripcion_estrategia"  >';if (isset($row['descripcion_estrategia'])) echo $row['descripcion_estrategia'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE estrategias SET estrategia='".$_POST['estrategia']."', descripcion_estrategia='".$_POST['descripcion_estrategia']."'WHERE  estrategia = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=estrategias.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=estrategias.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
	<script type="text/javascript">

	</script>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_estrategias" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_estrategias',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_estrategias',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_estrategias',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_estrategias();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_estrategias').className ='active '+document.getElementById('menu_estrategias').className;
</script>
<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
 ?>
