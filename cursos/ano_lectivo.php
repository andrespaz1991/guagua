<?php 
ob_start();
 ?>
<center>
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"><?php
    echo 'AÑO LECTIVO';
    ?></h1>      
  </div>
</div>
<?php 
require("../comun/conexion.php");
 /*require_once("funciones.php");*/ 
function buscar_ano_lectivo($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=ano_lectivo.xls");
}
require("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_ano_lectivo']) ? $_COOKIE['numeroresultados_ano_lectivo'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_ano_lectivo";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `ano_lectivo`.`id_ano_lectivo`, `ano_lectivo`.`nombre_ano_lectivo` FROM `ano_lectivo`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`ano_lectivo`.`nombre_ano_lectivo`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyano_lectivo']) and $_COOKIE['orderbyano_lectivo']!=""){ $sql .= "`ano_lectivo`.`".$_COOKIE['orderbyano_lectivo']."`";
}else{ $sql .= "`ano_lectivo`.`id_ano_lectivo`";}
if (isset($_COOKIE['orderad_ano_lectivo'])){
$orderadano_lectivo = $_COOKIE['orderad_ano_lectivo'];
$sql .=  " $orderadano_lectivo ";
}else{
$sql .=  " desc ";
}
$consulta_total_ano_lectivo = $mysqli->query($sql);
$total_ano_lectivo = $consulta_total_ano_lectivo->num_rows;
$paginacion->records($total_ano_lectivo);
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
$numero_ano_lectivo = $consulta->num_rows;
$minimo_ano_lectivo = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_ano_lectivo = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_ano_lectivo>$numero_ano_lectivo) $maximo_ano_lectivo=$numero_ano_lectivo;
$maximo_ano_lectivo += $minimo_ano_lectivo-1;
echo "<p>Resultados de $minimo_ano_lectivo a $maximo_ano_lectivo del total de ".$total_ano_lectivo." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbano_lectivo">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyano_lectivo']) and $_COOKIE['orderbyano_lectivo']== "nombre_ano_lectivo"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyano_lectivo','nombre_ano_lectivo');buscar();" >Nombre Ano Lectivo</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo"><form id="formNuevo" name="formNuevo" method="post" action="ano_lectivo.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<th data-label="XLS"><form id="formNuevo" name="formNuevo" method="post" action="ano_lectivo.php?xls">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="XLS">
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td data-label='Nombre Ano Lectivo'><?php echo $row['nombre_ano_lectivo']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="ano_lectivo.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_ano_lectivo']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar2('ano_lectivo.php',{'del':'<?php echo $row['id_ano_lectivo'];?>'},'<?php echo $row['id_ano_lectivo'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<?php $paginacion->render2();?>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_ano_lectivo($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_ano_lectivo('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM ano_lectivo WHERE id_ano_lectivo="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=ano_lectivo.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=ano_lectivo.php" />
<?php 
}
}
 ?>
<center>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
if(empty($_POST['estado'])){
  $_POST['estado']="Activo";
}
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO ano_lectivo ( `nombre_ano_lectivo`,estado) VALUES ('".$_POST['nombre_ano_lectivo']."','".$_POST['estado']."')";
#echo $sql;
if (isset($_POST['estado_anio_lectivo']) and $_POST['estado_anio_lectivo']=="Activo"){
  $sql_activo = "UPDATE ano_lectivo SET estado_anio_lectivo='Inactivo'";
$mysqli->query($sql_activo);
}

if ($insertar = $mysqli->query($sql)) {
require '../comun/funciones.php';
if(isset($_POST['automatico'])){
  
  inscribir_estudiante($_POST['id_ano_lectivo']);
}
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
Registro exitoso
<meta http-equiv="refresh" content="1; url=ano_lectivo.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=ano_lectivo.php" />
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="ano_lectivo.php">
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_ano_lectivo"type="hidden" id="id_ano_lectivo" value="';if (isset($row['id_ano_lectivo'])) echo $row['id_ano_lectivo'];echo '"';echo '></p>';
echo "</div>";
echo '<div class="form-group"><p><label for="nombre_ano_lectivo">Nombre Año Lectivo:</label><input class="form-control"name="nombre_ano_lectivo"type="text" id="nombre_ano_lectivo" value="';if (isset($row['nombre_ano_lectivo'])) echo $row['nombre_ano_lectivo'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<div class="form-group"><p><input type="hidden" name="estado_anio_lectivo" value="Inactivo"><input title="" class="" name="estado_anio_lectivo" type="checkbox" id="estado_anio_lectivo" value="';echo 'Activo';echo '"';if (isset($row['estado_anio_lectivo']) and $row['estado_anio_lectivo']=="Activo") echo " checked ";echo '><label for="estado_anio_lectivo">Activo</label></p></div>';
echo '
<div class="checkbox">
  <label><input title="si deseas hacerlo de forma manual más tarde, debes deschequear la casilla" name="automatico" checked type="checkbox" value="">Deseo que automaticamente se creen los cursos de ley  y se inscriban  a los estudiantes a cada curso asociados a este año lectivo</label>
</div>
';

echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_ano_lectivo`, `nombre_ano_lectivo` FROM `ano_lectivo` WHERE id_ano_lectivo ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="ano_lectivo.php">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_ano_lectivo']))  echo $row['id_ano_lectivo'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_ano_lectivo"type="hidden" id="id_ano_lectivo" value="';if (isset($row['id_ano_lectivo'])) echo $row['id_ano_lectivo'];echo '"';echo '></p>';
echo "</div>";
echo '<div class="form-group"><p><label for="nombre_ano_lectivo">Nombre Ano Lectivo:</label><input class="form-control"name="nombre_ano_lectivo"type="text" id="nombre_ano_lectivo" value="';if (isset($row['nombre_ano_lectivo'])) echo $row['nombre_ano_lectivo'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<div class="form-group"><p><input type="hidden" name="estado_anio_lectivo" value="Inactivo"><input title="" class="" name="estado_anio_lectivo" type="checkbox" id="estado_anio_lectivo" value="';echo 'Activo';echo '"';if (isset($row['estado_anio_lectivo']) and $row['estado_anio_lectivo']=="Activo") echo " checked ";echo '><label for="estado_anio_lectivo">Activo</label></p></div>';
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
echo "<pre>";
print_r($_POST);
echo "</pre>";
if (!isset($_POST['estado_anio_lectivo']) or $_POST['estado_anio_lectivo']<>"Activo"){
  $_POST['estado_anio_lectivo']="Inactivo";
}

 /*Instrucción SQL que permite insertar en la BD */ 
 $sql = "UPDATE ano_lectivo SET id_ano_lectivo='".$_POST['id_ano_lectivo']."', nombre_ano_lectivo='".$_POST['nombre_ano_lectivo']."',estado='".$_POST['estado_anio_lectivo']."'  WHERE  id_ano_lectivo = '".$cod."';";
if (isset($_POST['estado_anio_lectivo']) and $_POST['estado_anio_lectivo']=="Activo"){
$sql_activo = "UPDATE ano_lectivo SET estado='Inactivo'";
$mysqli->query($sql_activo);
}
 #echo $sql;
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=ano_lectivo.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=ano_lectivo.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_ano_lectivo" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_ano_lectivo',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_ano_lectivo',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_ano_lectivo',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_ano_lectivo','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_ano_lectivo','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php 
buscar_ano_lectivo();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_ano_lectivo = document.getElementById('menu_ano_lectivo')
if (vmenu_ano_lectivo){
vmenu_ano_lectivo.className ='active '+vmenu_ano_lectivo.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
