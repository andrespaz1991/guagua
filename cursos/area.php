<?php 
ob_start();
 ?>
<center>

<?php 
require("../comun/conexion.php");
 /*require_once("funciones.php");*/ 
function buscar_area($datos="",$reporte=""){


if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=area.xls");
}
require("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_area']) ? $_COOKIE['numeroresultados_area'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_area";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `area`.`id_area`, `area`.`nombre_area` FROM `area`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`area`.`nombre_area`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
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
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyarea']) and $_COOKIE['orderbyarea']!=""){ $sql .= "`area`.`".$_COOKIE['orderbyarea']."`";
}else{ $sql .= "`area`.`id_area`";}
if (isset($_COOKIE['orderad_area'])){
$orderadarea = $_COOKIE['orderad_area'];
$sql .=  " $orderadarea ";
}else{
$sql.=  " desc ";
}


$consulta_total_area = $mysqli->query($sql);
$total_area = $consulta_total_area->num_rows;
$paginacion->records($total_area);
$sql.=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#echo $sql;


$consulta = $mysqli->query($sql);
$numero_area = $consulta->num_rows;
$minimo_area = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_area = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_area>$numero_area) $maximo_area=$numero_area;
$maximo_area += $minimo_area-1;
echo "<p>Resultados de $minimo_area a $maximo_area del total de ".$total_area." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbarea">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyarea']) and $_COOKIE['orderbyarea']== "nombre_area"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyarea','nombre_area');buscar();" >Nombre Área</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo"><form id="formNuevo" name="formNuevo" method="post" action="area.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<th data-label="XLS"><form id="formNuevo" name="formNuevo" method="post" action="area.php?xls">
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
<td data-label='Nombre Área'><?php echo $row['nombre_area']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="area.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_area']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar2('area.php',{'del':'<?php echo $row['id_area'];?>'},'<?php echo $row['id_area'];?>');" value="Eliminar">
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
buscar_area($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_area('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM area WHERE id_area="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=area.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=area.php" />
<?php 
}
}
 ?>
<center>
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"><?php echo 'Área'; ?></h1>      
  </div>
</div>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO area (`id_area`, `nombre_area`) VALUES ('".$_POST['id_area']."', '".$_POST['nombre_area']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
Registro exitoso
<meta http-equiv="refresh" content="1; url=area.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=area.php" />
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="area.php">
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_area"type="hidden" id="id_area" value="';if (isset($row['id_area'])) echo $row['id_area'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_area">Nombre Área:</label><input class="form-control"name="nombre_area"type="text" id="nombre_area" value="';if (isset($row['nombre_area'])) echo $row['nombre_area'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_area`, `nombre_area` FROM `area` WHERE id_area ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="area.php">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_area']))  echo $row['id_area'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_area"type="hidden" id="id_area" value="';if (isset($row['id_area'])) echo $row['id_area'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_area">Nombre Area:</label><input class="form-control"name="nombre_area"type="text" id="nombre_area" value="';if (isset($row['nombre_area'])) echo $row['nombre_area'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE area SET id_area='".$_POST['id_area']."', nombre_area='".$_POST['nombre_area']."'WHERE  id_area = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=area.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=area.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_area" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_area',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_area',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_area',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_area','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_area','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php 
buscar_area();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_area = document.getElementById('menu_area')
if (vmenu_area){
vmenu_area.className ='active '+vmenu_area.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
