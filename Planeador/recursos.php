<?php 
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
echo '<center>';
require("conexion.php");
?>
   <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.7.0/tinymce.min.js" integrity="sha512-XaygRY58e7fVVWydN6jQsLpLMyf7qb4cKZjIi93WbKjT6+kG/x4H5Q73Tff69trL9K0YDPIswzWe6hkcyuOHlw==" crossorigin="anonymous"></script>
  <script>tinymce.init({ selector:'textarea' });</script>


<?php
/*require("funciones.php");*/ 
function buscar_recursos($datos="",$reporte=""){

require("conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_recursos']) ? $_COOKIE['numeroresultados_recursos'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];


$sql = "SELECT `recursos`.`nombre_recurso`, `recursos`.`descripcion_recurso` FROM `recursos`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`recursos`.`nombre_recurso`)," ", LOWER(`recursos`.`descripcion_recurso`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
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
$sql .=  " ORDER BY `recursos`.`nombre_recurso` desc LIMIT ";
$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
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
<table border="1" id="tbrecursos">
<thead>
<tr>
<th>Nombre Recurso</th>
<th>Descripcion Recurso</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="recursos.php">
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
<td><?php echo $row['nombre_recurso']?></td>
<td><?php echo $row['descripcion_recurso']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="recursos.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['nombre_recurso']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="img/eliminar.png" onClick="confirmeliminar('recursos.php',{'del':'<?php echo $row['nombre_recurso'];?>'},'<?php echo $row['nombre_recurso'];?>');" value="Eliminar">
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
buscar_recursos($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM recursos WHERE nombre_recurso="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=recursos.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=recursos.php" />
<?php 
}
}
 ?>
<center>
<h1>Recursos</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO recursos (`nombre_recurso`, `descripcion_recurso`) VALUES ('".$_POST['nombre_recurso']."', '".$_POST['descripcion_recurso']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=recursos.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=recursos.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="recursos.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['nombre_recurso']))  echo $row['nombre_recurso'] ?>" size="120" required></p>
<?php 
echo '<p><label for="nombre_recurso">Nombre Recurso:</label><input class=""name="nombre_recurso"type="text" id="nombre_recurso" value="';if (isset($row['nombre_recurso'])) echo $row['nombre_recurso'];echo '"';echo ' required ></p>';
echo '<p><label for="descripcion_recurso">Descripcion Recurso:</label></p><p><textarea class="" name="descripcion_recurso" cols="60" rows="10"id="descripcion_recurso" >';if (isset($row['descripcion_recurso'])) echo $row['descripcion_recurso'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `nombre_recurso`, `descripcion_recurso` FROM `recursos` WHERE nombre_recurso ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="recursos.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['nombre_recurso']))  echo $row['nombre_recurso'] ?>" size="120" required></p>
<?php 
echo '<p><label for="nombre_recurso">Nombre Recurso:</label><input class=""name="nombre_recurso"type="text" id="nombre_recurso" value="';if (isset($row['nombre_recurso'])) echo $row['nombre_recurso'];echo '"';echo ' required ></p>';
echo '<p><label for="descripcion_recurso">Descripcion Recurso:</label></p><p><textarea class="" name="descripcion_recurso" cols="60" rows="10"id="descripcion_recurso" >';if (isset($row['descripcion_recurso'])) echo $row['descripcion_recurso'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE recursos SET nombre_recurso='".$_POST['nombre_recurso']."', descripcion_recurso='".$_POST['descripcion_recurso']."'WHERE  nombre_recurso = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=recursos.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=recursos.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_recursos" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_recursos',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_recursos',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_recursos',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_recursos();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_recursos').className ='active '+document.getElementById('menu_recursos').className;
</script>
<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
 ?>
