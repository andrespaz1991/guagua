<?php 
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');
echo '<center>';
require_once("../comun/funciones.php");
require_once("../comun/config.php");
function buscar_categoria_curso($datos="",$reporte=""){
  
require("../comun/conexion.php");
require_once("../comun/config.php");
require_once("../comun/funciones.php");

require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_categoria_curso']) ? $_COOKIE['numeroresultados_categoria_curso'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];

$sql = "SELECT `categoria_curso`.`id_categoria_curso`, `categoria_curso`.`nombre_categoria_curso`, `categoria_curso`.`descripcion_categoria_curso` FROM `categoria_curso`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`categoria_curso`.`id_categoria_curso`)," ", LOWER(`categoria_curso`.`nombre_categoria_curso`)," ", LOWER(`categoria_curso`.`descripcion_categoria_curso`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
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
$sql .=  " ORDER BY `categoria_curso`.`id_categoria_curso` desc LIMIT ";
$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;



$consulta = $mysqli->query($sql);

$numero_usuario = $consulta->num_rows;
$minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
$maximo_usuario += $minimo_usuario-1;
echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$numero_usuario." en página ".$paginacion->get_page()."</p>";

 ?>
<div align="center">
<table border="1" id="tbcategoria_curso">
<thead>
<tr>
<!--th>Id Categoria Curso</th-->
<th>Nombre Categoria</th>
<th>Cantidad de Estudiantes</th>
<th>Descripcion Categoria</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="categoria_curso.php">
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
<!--td><?php echo $row['id_categoria_curso']?></td-->
<td><?php echo $row['nombre_categoria_curso']?></td>
<td><a href="<?php echo SGA_CURSOS_URL; ?>/estudiante_curso.php?categoria=<?php echo $row['id_categoria_curso']?>"><?php print_r(cantidad_estudiantes_Categoria($row['id_categoria_curso'])[0]) ; ?></a></td>

<td><?php echo $row['descripcion_categoria_curso']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="categoria_curso.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_categoria_curso']?>">
<input class="btn btn-success" type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="<?php echo SGA_COMUN_URL; ?>/img/eliminar.png" onClick="confirmeliminar2('categoria_curso.php',{'del':'<?php echo $row['id_categoria_curso'];?>'},'<?php echo $row['id_categoria_curso'];?>');" value="Eliminar">
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
buscar_categoria_curso($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM categoria_curso WHERE id_categoria_curso="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
 require_once("../comun/conexion.php");

if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=categoria_curso.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=categoria_curso.php" />
<?php 
}
}
ob_start();
?>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">CATEGORIA </h1>      
  </div>
</div>
<div class="container-fluid bg-3 text-center">    
 <div class="row">
<section>
<p align="center">
<br>
<p><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO categoria_curso (`id_categoria_curso`, `nombre_categoria_curso`, `descripcion_categoria_curso`) VALUES ('".$_POST['id_categoria_curso']."', '".$_POST['nombre_categoria_curso']."', '".$_POST['descripcion_categoria_curso']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=categoria_curso.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=categoria_curso.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="categoria_curso.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_categoria_curso']))  echo $row['id_categoria_curso'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_categoria_curso"type="hidden" id="id_categoria_curso" value="';if (isset($row['id_categoria_curso'])) echo $row['id_categoria_curso'];echo '"';echo '></p>';
echo '<p><label for="nombre_categoria_curso">Nombre Categoria:</label><input class=""name="nombre_categoria_curso"type="text" id="nombre_categoria_curso" value="';if (isset($row['nombre_categoria_curso'])) echo $row['nombre_categoria_curso'];echo '"';echo ' required ></p>';
echo '<p><label for="descripcion_categoria_curso">Descripcion Categoria:</label></p><p><textarea class="" name="descripcion_categoria_curso" cols="60" rows="10"id="descripcion_categoria_curso" >';if (isset($row['descripcion_categoria_curso'])) echo $row['descripcion_categoria_curso'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_categoria_curso`, `nombre_categoria_curso`, `descripcion_categoria_curso` FROM `categoria_curso` WHERE id_categoria_curso ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="categoria_curso.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_categoria_curso']))  echo $row['id_categoria_curso'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_categoria_curso"type="hidden" id="id_categoria_curso" value="';if (isset($row['id_categoria_curso'])) echo $row['id_categoria_curso'];echo '"';echo '></p>';
echo '<p><label for="nombre_categoria_curso">Nombre Categoria:</label><input class=""name="nombre_categoria_curso"type="text" id="nombre_categoria_curso" value="';if (isset($row['nombre_categoria_curso'])) echo $row['nombre_categoria_curso'];echo '"';echo ' required ></p>';
echo '<p><label for="descripcion_categoria_curso">Descripcion Categoria:</label></p><p><textarea class="" name="descripcion_categoria_curso" cols="60" rows="10"id="descripcion_categoria_curso" >';if (isset($row['descripcion_categoria_curso'])) echo $row['descripcion_categoria_curso'];echo '</textarea></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE categoria_curso SET id_categoria_curso='".$_POST['id_categoria_curso']."', nombre_categoria_curso='".$_POST['nombre_categoria_curso']."', descripcion_categoria_curso='".$_POST['descripcion_categoria_curso']."'WHERE  id_categoria_curso = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=categoria_curso.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=categoria_curso.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_categoria_curso" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_categoria_curso',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_categoria_curso',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_categoria_curso',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_categoria_curso();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
document.getElementById('menu_categoria_curso').className ='active '+document.getElementById('menu_categoria_curso').className;
</script>
<?php
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>