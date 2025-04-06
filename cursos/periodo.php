<?php 
ob_start();
 ?>
<center>
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"><?php
   echo 'PERÍODO';
   ?></h1>      
  </div>
</div>
<?php 
require("../comun/conexion.php");
 /*require_once("../comun/funciones.php");*/ 
function buscar_periodo($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=periodo.xls");
}
require("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_periodo']) ? $_COOKIE['numeroresultados_periodo'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_periodo";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `periodo`.`id_periodo`, `periodo`.`nombre_periodo` FROM `periodo`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`periodo`.`nombre_periodo`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyperiodo']) and $_COOKIE['orderbyperiodo']!=""){ $sql .= "`periodo`.`".$_COOKIE['orderbyperiodo']."`";
}else{ $sql .= "`periodo`.`id_periodo`";}
if (isset($_COOKIE['orderad_periodo'])){
$orderadperiodo = $_COOKIE['orderad_periodo'];
$sql .=  " $orderadperiodo ";
}else{
$sql .=  " desc ";
}
$consulta_total_periodo = $mysqli->query($sql);
$total_periodo = $consulta_total_periodo->num_rows;
$paginacion->records($total_periodo);
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
$numero_periodo = $consulta->num_rows;
$minimo_periodo = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_periodo = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_periodo>$numero_periodo) $maximo_periodo=$numero_periodo;
$maximo_periodo += $minimo_periodo-1;
echo "<p>Resultados de $minimo_periodo a $maximo_periodo del total de ".$total_periodo." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbperiodo">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyperiodo']) and $_COOKIE['orderbyperiodo']== "nombre_periodo"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyperiodo','nombre_periodo');buscar();" >Nombre Periodo</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo"><form id="formNuevo" name="formNuevo" method="post" action="periodo.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<th data-label="XLS"><form id="formNuevo" name="formNuevo" method="post" action="periodo.php?xls">
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
<td data-label='Nombre Periodo'><?php echo $row['nombre_periodo']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="periodo.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_periodo']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar2('periodo.php',{'del':'<?php echo $row['id_periodo'];?>'},'<?php echo $row['id_periodo'];?>');" value="Eliminar">
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
buscar_periodo($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_periodo('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM periodo WHERE id_periodo="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=periodo.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=periodo.php" />
<?php 
}
}
 ?>
<center>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO periodo (`id_periodo`, `nombre_periodo`) VALUES ('".$_POST['id_periodo']."', '".$_POST['nombre_periodo']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
Registro exitoso
<meta http-equiv="refresh" content="1; url=periodo.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=periodo.php" />
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="periodo.php">
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_periodo"type="hidden" id="id_periodo" value="';if (isset($row['id_periodo'])) echo $row['id_periodo'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_periodo">Nombre Periodo:</label><input class="form-control"name="nombre_periodo"type="text" id="nombre_periodo" value="';if (isset($row['nombre_periodo'])) echo $row['nombre_periodo'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_periodo`, `nombre_periodo` FROM `periodo` WHERE id_periodo ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="periodo.php">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_periodo']))  echo $row['id_periodo'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_periodo"type="hidden" id="id_periodo" value="';if (isset($row['id_periodo'])) echo $row['id_periodo'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_periodo">Nombre Periodo:</label><input class="form-control"name="nombre_periodo"type="text" id="nombre_periodo" value="';if (isset($row['nombre_periodo'])) echo $row['nombre_periodo'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE periodo SET id_periodo='".$_POST['id_periodo']."', nombre_periodo='".$_POST['nombre_periodo']."'WHERE  id_periodo = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=periodo.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=periodo.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_periodo" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_periodo',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_periodo',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_periodo',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_periodo','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_periodo','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php 
buscar_periodo();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_periodo = document.getElementById('menu_periodo')
if (vmenu_periodo){
vmenu_periodo.className ='active '+vmenu_periodo.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
