<?php 
ob_start();
 ?>
<center>
<?php 
require("../comun/conexion.php");
 /*require_once("../comun/funciones.php");*/ 
function buscar_competencia($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=competencia.xls");
}
require("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_competencia']) ? $_COOKIE['numeroresultados_competencia'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_competencia";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `competencia`.`id_competencia`, `competencia`.`nombre_competencia`, `competencia`.`porcentaje_competencia`, `competencia`.`descripcion_competencia` FROM `competencia`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`competencia`.`nombre_competencia`)," ",LOWER(`competencia`.`porcentaje_competencia`)," ",LOWER(`competencia`.`descripcion_competencia`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbycompetencia']) and $_COOKIE['orderbycompetencia']!=""){ $sql .= "`competencia`.`".$_COOKIE['orderbycompetencia']."`";
}else{ $sql .= "`competencia`.`id_competencia`";}
if (isset($_COOKIE['orderad_competencia'])){
$orderadcompetencia = $_COOKIE['orderad_competencia'];
$sql .=  " $orderadcompetencia ";
}else{
$sql .=  " desc ";
}
$consulta_total_competencia = $mysqli->query($sql);
$total_competencia = $consulta_total_competencia->num_rows;
$paginacion->records($total_competencia);
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
 ?>
<div align="center">
<table border="1" id="tbcompetencia">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbycompetencia']) and $_COOKIE['orderbycompetencia']== "nombre_competencia"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbycompetencia','nombre_competencia');buscar();" >Nombre Competencia</th>
<th <?php  if(isset($_COOKIE['orderbycompetencia']) and $_COOKIE['orderbycompetencia']== "porcentaje_competencia"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbycompetencia','porcentaje_competencia');buscar();" >Porcentaje</th>
<th <?php  if(isset($_COOKIE['orderbycompetencia']) and $_COOKIE['orderbycompetencia']== "descripcion_competencia"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbycompetencia','descripcion_competencia');buscar();" >Descripción</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo"><form id="formNuevo" name="formNuevo" method="post" action="competencia.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<th data-label="XLS"><form id="formNuevo" name="formNuevo" method="post" action="competencia.php?xls">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="XLS"><button type="submit" class="btn btn-sucess">XLS</button>
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td data-label='Nombre Competencia'><?php echo $row['nombre_competencia']?></td>
<td data-label='Porcentaje'><?php echo $row['porcentaje_competencia']?></td>
<td data-label='Descripción'><?php echo $row['descripcion_competencia']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="competencia.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_competencia']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar2('competencia.php',{'del':'<?php echo $row['id_competencia'];?>'},'<?php echo $row['id_competencia'];?>');" value="Eliminar">
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
buscar_competencia($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_competencia('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM competencia WHERE id_competencia="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=competencia.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=competencia.php" />
<?php 
}
}
 ?>
<center>
<h1>Competencia</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
if($_POST['porcentaje_competencia']>'0'and $_POST['porcentaje_competencia']<'101'){
 $sql = "INSERT INTO competencia (`id_competencia`, `nombre_competencia`, `porcentaje_competencia`, `descripcion_competencia`) VALUES ('".$_POST['id_competencia']."', '".$_POST['nombre_competencia']."', '".$_POST['porcentaje_competencia']."', '".$_POST['descripcion_competencia']."')";
}
else{
 $sql ='x';
}
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)  ) {
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
Registro exitoso
<meta http-equiv="refresh" content="1; url=competencia.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=competencia.php" />
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){
$sql_max = "SELECT SUM(`porcentaje_competencia`) as max FROM `competencia`";
$consulta_max = $mysqli->query($sql_max);
if($row_max = $consulta_max->fetch_assoc())
extract($row_max);
$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="competencia.php">
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_competencia"type="hidden" id="id_competencia" value="';if (isset($row['id_competencia'])) echo $row['id_competencia'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_competencia">Nombre Competencia:</label><input class="form-control"name="nombre_competencia"type="text" id="nombre_competencia" value="';if (isset($row['nombre_competencia'])) echo $row['nombre_competencia'];echo '"';echo ' required ></p></div>';
?><div class="form-group"><p><label for="porcentaje_competencia">Porcentaje:&nbsp;&nbsp;<span id="vr_porcentaje">0</span>%</label><input class="form-control"name="porcentaje_competencia"type="range" onchange="document.getElementById('vr_porcentaje').innerHTML = this.value" step="1"  min="0" max="<?php if(isset($max)) echo 100-$max; else echo "100"; ?>" id="porcentaje_competencia" value="<?php
if (isset($row['porcentaje_competencia'])) echo $row['porcentaje_competencia'];
else echo "0";
?>" required ></p>
</div>
<div class="form-group"><p>
<label for="descripcion_competencia">Descripción:</label></p>
<p><textarea class="form-control" name="descripcion_competencia" cols="60" rows="10"id="descripcion_competencia"  >
<?php if (isset($row['descripcion_competencia'])) echo $row['descripcion_competencia']; ?>
</textarea></p>
<?php 
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){
$sql_max = "SELECT SUM(`porcentaje_competencia`) as max FROM `competencia`";
$consulta_max = $mysqli->query($sql_max);
if($row_max = $consulta_max->fetch_assoc())
extract($row_max);
$sql = "SELECT `id_competencia`, `nombre_competencia`, `porcentaje_competencia`, `descripcion_competencia` FROM `competencia` WHERE id_competencia ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();
$max_actual = (100-$max)+$row['porcentaje_competencia'];
$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="competencia.php">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_competencia']))  echo $row['id_competencia'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_competencia"type="hidden" id="id_competencia" value="';if (isset($row['id_competencia'])) echo $row['id_competencia'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_competencia">Nombre Competencia:</label><input class="form-control"name="nombre_competencia"type="text" id="nombre_competencia" value="';if (isset($row['nombre_competencia'])) echo $row['nombre_competencia'];echo '"';echo ' required ></p></div>';
?><div class="form-group"><p><label for="porcentaje_competencia">Porcentaje:&nbsp;&nbsp;<span id="vr_porcentaje"><?php 
if (isset($row['porcentaje_competencia']))
    echo $row['porcentaje_competencia'];
else echo "0";
?></span>%</label></label><input class="form-control"name="porcentaje_competencia"type="range" onchange="document.getElementById('vr_porcentaje').innerHTML = this.value"  step="1"  min="0" max="<?php echo $max_actual; ?>" id="porcentaje_competencia" value="
<?php 
if (isset($row['porcentaje_competencia']))
    echo $row['porcentaje_competencia'];
else echo "0";
?>" required ></p><?php
echo "</div>";echo '<div class="form-group"><p>'; ?>
<label for="descripcion_competencia">Descripción:</label></p>
<p><textarea class="form-control" name="descripcion_competencia" cols="60" rows="10"id="descripcion_competencia"  >
<?php if (isset($row['descripcion_competencia'])) echo $row['descripcion_competencia']; ?>
</textarea></p>
<?php 
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
 if($_POST['porcentaje_competencia']>'0'and $_POST['porcentaje_competencia']<'101'){
  $sql = "UPDATE competencia SET id_competencia='".$_POST['id_competencia']."', nombre_competencia='".$_POST['nombre_competencia']."', porcentaje_competencia='".$_POST['porcentaje_competencia']."', descripcion_competencia='".$_POST['descripcion_competencia']."'WHERE  id_competencia = '".$cod."';";
 }

/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=competencia.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=competencia.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_competencia" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_competencia',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_competencia',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_competencia',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_competencia','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_competencia','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php 
buscar_competencia();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_competencia = document.getElementById('menu_competencia')
if (vmenu_competencia){
vmenu_competencia.className ='active '+vmenu_competencia.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
