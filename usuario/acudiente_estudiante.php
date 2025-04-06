<?php 
ob_start();
require("../comun/conexion.php");
 require_once("../comun/funciones.php"); 
 ?>
<center>
<?php 
function buscar_acudiente_estudiante($datos="",$reporte=""){
require("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
if (isset($_COOKIE['numeroresultados_acudiente_estudiante']) and $_COOKIE['numeroresultados_acudiente_estudiante']=="")  $_COOKIE['numeroresultados_acudiente_estudiante']="0";
$resultados = ((isset($_COOKIE['numeroresultados_acudiente_estudiante']) and $_COOKIE['numeroresultados_acudiente_estudiante']!="" ) ? $_COOKIE['numeroresultados_acudiente_estudiante'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_acudiente_estudiante";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `acudiente_estudiante`.`id_acudiente_estudiante`, `acudiente_estudiante`.`id_estudiante`, `usuario`.`nombre` as usuarionombre, `acudiente_estudiante`.`id_acudiente`, `usuario2`.`nombre` as acudientenombre FROM `acudiente_estudiante` inner join `usuario` on `acudiente_estudiante`.`id_estudiante` = `usuario`.`id_usuario` inner join `usuario`as usuario2 on `acudiente_estudiante`.`id_acudiente` = `usuario2`.`id_usuario` ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
if (isset($_COOKIE['busqueda_avanzada_acudiente_estudiante']) and $_COOKIE['busqueda_avanzada_acudiente_estudiante']=="true"){
if (isset($_COOKIE['busqueda_av_acudiente_estudianteid_estudiante']) and $_COOKIE['busqueda_av_acudiente_estudianteid_estudiante']!=""){
$sql .= ' LOWER(`acudiente_estudiante`.`id_estudiante`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_acudiente_estudianteid_estudiante'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_acudiente_estudianteid_acudiente']) and $_COOKIE['busqueda_av_acudiente_estudianteid_acudiente']!=""){
$sql .= ' LOWER(`acudiente_estudiante`.`id_acudiente`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_acudiente_estudianteid_acudiente'], 'UTF-8').'%" and ';
}
}
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`usuario`.`nombre`)," ",LOWER(`usuario`.`nombre`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyacudiente_estudiante']) and $_COOKIE['orderbyacudiente_estudiante']!=""){ $sql .= "`acudiente_estudiante`.`".$_COOKIE['orderbyacudiente_estudiante']."`";
}else{ $sql .= "`acudiente_estudiante`.`id_acudiente_estudiante`";}
if (isset($_COOKIE['orderad_acudiente_estudiante'])){
$orderadacudiente_estudiante = $_COOKIE['orderad_acudiente_estudiante'];
$sql .=  " $orderadacudiente_estudiante ";
}else{
$sql .=  " desc ";
}
$consulta_total_acudiente_estudiante = $mysqli->query($sql);
$total_acudiente_estudiante = $consulta_total_acudiente_estudiante->num_rows;
$paginacion->records($total_acudiente_estudiante);
if ($reporte=="") $sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
echo $sql;

$consulta = $mysqli->query($sql);
$numero_acudiente_estudiante = $consulta->num_rows;
$minimo_acudiente_estudiante = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_acudiente_estudiante = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_acudiente_estudiante>$numero_acudiente_estudiante) $maximo_acudiente_estudiante=$numero_acudiente_estudiante;
$maximo_acudiente_estudiante += $minimo_acudiente_estudiante-1;
if ($reporte=="") echo "<p>Resultados de $minimo_acudiente_estudiante a $maximo_acudiente_estudiante del total de ".$total_acudiente_estudiante." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbacudiente_estudiante">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyacudiente_estudiante']) and $_COOKIE['orderbyacudiente_estudiante']== "id_estudiante"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyacudiente_estudiante','id_estudiante');buscar();" >Estudiante</th>
<th <?php  if(isset($_COOKIE['orderbyacudiente_estudiante']) and $_COOKIE['orderbyacudiente_estudiante']== "id_acudiente"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyacudiente_estudiante','id_acudiente');buscar();" >Acudiente</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo" colspan="2" class="thbotones"><form id="formNuevo" name="formNuevo" method="post" action="acudiente_estudiante.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="image" name="submit" id="submit" value="Nuevo" title="Nuevo" src="<?php echo SGA_COMUN_URL ?>/img/nuevo2.png">
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td data-label="Estudiante"><?php echo $row['usuarionombre']?></td>
<td data-label="Acudiente"><?php echo $row['acudientenombre']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="acudiente_estudiante.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_acudiente_estudiante']; ?>">
<input type="image" src="<?php echo SGA_COMUN_URL ?>/img/modificar.png"name="submit" id="submit" value="Modificar" title="Modificar">
</form>
</td>
<td data-label="Eliminar">
<input title="Eliminar" type="image" src="<?php echo SGA_COMUN_URL ?>/img/eliminar2.png" onClick="confirmeliminar2('acudiente_estudiante.php',{'del':'<?php echo $row['id_acudiente_estudiante'];?>'},'<?php echo $row['id_estudiante']." ".$row['id_acudiente'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<?php if ($reporte=="") $paginacion->render2();?>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_acudiente_estudiante($_POST['datos']);
exit();
}

if (!empty($_POST)){
 /*Validación de los datos recibidos mediante el método POST*/ 
 foreach ($_POST as $id => $valor){$_POST[$id]=$mysqli->real_escape_string($valor);} 
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM acudiente_estudiante WHERE concat(`acudiente_estudiante`.`id_acudiente_estudiante`)="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
$eliminar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=acudiente_estudiante.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=acudiente_estudiante.php" />
<?php 
}
}
 ?>
<center>
<h1>Acudiente Estudiante</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
 @session_start(); 
$sql = "INSERT INTO acudiente_estudiante (`id_acudiente_estudiante`, `id_estudiante`, `id_acudiente`) VALUES ('".$_POST['id_acudiente_estudiante']."', '".$_POST['id_estudiante']."', '".$_POST['id_acudiente']."')";
 /*echo $sql;*/
$insertar = $mysqli->query($sql);
if ($mysqli->errno != 1062){
if ($mysqli->affected_rows>0){
$insertid = $mysqli->insert_id;
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
<script>alert('Registro Exitoso');</script>
<meta http-equiv="refresh" content="1; url=acudiente_estudiante.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=acudiente_estudiante.php" />
<?php 
}
 }else{ 
 ?><script>alert('Este registro ya existe');</script>
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="acudiente_estudiante.php" >
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input title="" class="form-control" name="id_acudiente_estudiante" type="hidden" id="id_acudiente_estudiante" value="';if (isset($row['id_acudiente_estudiante'])) echo $row['id_acudiente_estudiante'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="id_estudiante">Estudiante:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label>';
$sql2= "SELECT * FROM usuario;";
 ?>
<select  title="" class="form-control" name="id_estudiante" id="id_estudiante"  required>
<?php 
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option value="'.$row2['id_usuario'].'"';if (isset($row['id_estudiante']) and $row['id_estudiante'] == $row2['id_usuario']) echo " selected ";echo '>'.$row2['nombre'].'</option>';
}
echo '</select></p>';
echo "</div>";echo '<div class="form-group"><p><label for="id_acudiente">Acudiente:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label>';
$sql3= "SELECT * FROM usuario;";
 ?>
<select  title="" class="form-control" name="id_acudiente" id="id_acudiente"  required>
<?php 
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta3 = $mysqli->query($sql3);
while($row3=$consulta3->fetch_assoc()){
echo '<option value="'.$row3['id_usuario'].'"';if (isset($row['id_acudiente']) and $row['id_acudiente'] == $row3['id_usuario']) echo " selected ";echo '>'.$row3['nombre'].'</option>';
}
echo '</select></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = 'SELECT `id_acudiente_estudiante`, `id_estudiante`, `id_acudiente` FROM `acudiente_estudiante` WHERE concat(`acudiente_estudiante`.`id_acudiente_estudiante`) ="'.$_POST['cod'].'" Limit 1'; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="acudiente_estudiante.php" >
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_acudiente_estudiante']))  echo $row['id_acudiente_estudiante'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input title="" class="form-control" name="id_acudiente_estudiante" type="hidden" id="id_acudiente_estudiante" value="';if (isset($row['id_acudiente_estudiante'])) echo $row['id_acudiente_estudiante'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="id_estudiante">Estudiante:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label>';
$sql2= "SELECT id_usuario,nombre FROM usuario;";
 ?>
<select  title="" class="form-control" name="id_estudiante" id="id_estudiante"  required>
<?php 
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option value="'.$row2['id_usuario'].'"';if (isset($row['id_estudiante']) and $row['id_estudiante'] == $row2['id_usuario']) echo " selected ";echo '>'.$row2['nombre'].'</option>';
}
echo '</select></p>';
echo "</div>";echo '<div class="form-group"><p><label for="id_acudiente">Acudiente:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label>';
$sql3= "SELECT id_usuario,nombre FROM usuario;";
 ?>
<select  title="" class="form-control" name="id_acudiente" id="id_acudiente"  required>
<?php 
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta3 = $mysqli->query($sql3);
while($row3=$consulta3->fetch_assoc()){
echo '<option value="'.$row3['id_usuario'].'"';if (isset($row['id_acudiente']) and $row['id_acudiente'] == $row3['id_usuario']) echo " selected ";echo '>'.$row3['nombre'].'</option>';
}
echo '</select></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
 @session_start(); 
$sql = "UPDATE acudiente_estudiante SET id_acudiente_estudiante='".$_POST['id_acudiente_estudiante']."', id_estudiante='".$_POST['id_estudiante']."', id_acudiente='".$_POST['id_acudiente']."'WHERE  `acudiente_estudiante`.`id_acudiente_estudiante` = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
$actualizar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue ingresado con éxito*/
?>
Modificación exitosa
<meta http-equiv="refresh" content="1; url=acudiente_estudiante.php" />
<?php 
 }else{ 
?>
Modificación fallida
<meta http-equiv="refresh" content="2; url=acudiente_estudiante.php" />
<?php 
}
} /*fin Actualizar*/ 
 }else{ 
if (isset($_COOKIE['numeroresultados_acudiente_estudiante']) and $_COOKIE['numeroresultados_acudiente_estudiante']=="")  $_COOKIE['numeroresultados_acudiente_estudiante']="10";
 ?>
<center>
<b><label>Buscar: </label></b><input placeholder="Buscar por palabra clave" title="Buscar por palabra clave: Estudiante, Acudiente" type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_acudiente_estudiante" placeholder="Cant." title="Cantidad de resultados" value="<?php $no_resultados = ((isset($_COOKIE['numeroresultados_acudiente_estudiante']) and $_COOKIE['numeroresultados_acudiente_estudiante']!="" ) ? $_COOKIE['numeroresultados_acudiente_estudiante'] : 10); echo $no_resultados; ?>" onkeyup="grabarcookie('numeroresultados_acudiente_estudiante',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_acudiente_estudiante',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_acudiente_estudiante',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 60px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_acudiente_estudiante','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_acudiente_estudiante','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
<p><label><input type="checkbox" onchange="grabarcookie('busqueda_avanzada_acudiente_estudiante',this.checked);mostrar_busqueda_avanzada(this.checked);buscar();" <?php if (isset($_COOKIE['busqueda_avanzada_acudiente_estudiante']) and $_COOKIE['busqueda_avanzada_acudiente_estudiante']=='true') echo 'checked' ?>>Búsqueda Avanzada</label></p>
</center>
<script>function mostrar_busqueda_avanzada(valor){
  if (valor==true){
    $(".busqueda_avanzada").show();
    $(".input_busqueda_avanzada").val("");
    $(".input_busqueda_avanzada").change();
  }else if (valor==false){
    $(".busqueda_avanzada").hide();
  }
}</script>
<div class="busqueda_avanzada" <?php if (!isset($_COOKIE['busqueda_avanzada_acudiente_estudiante']) or (isset($_COOKIE['busqueda_avanzada_acudiente_estudiante']) and $_COOKIE['busqueda_avanzada_acudiente_estudiante']!='true')) echo 'style="display:none"' ?>>
<div class="form-group"><p><label for="id_estudiante">Estudiante<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_acudiente_estudianteid_estudiante',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_acudiente_estudianteid_estudiante'])) echo $_COOKIE['busqueda_av_acudiente_estudianteid_estudiante']; ?>
"></label></p></div>
<div class="form-group"><p><label for="id_acudiente">Acudiente<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_acudiente_estudianteid_acudiente',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_acudiente_estudianteid_acudiente'])) echo $_COOKIE['busqueda_av_acudiente_estudianteid_acudiente']; ?>
"></label></p></div>

<input type="button" onclick="buscar();" class="btn btn-primary" value="Buscar">
</div>

<div fn-dranganddrop="123d" dranganddrop-helper="clone" dranganddrop-datos='{"id_acudiente":"12345","nombre_acudiente":"Juan"}' style="width:50px">
<span>Origen</span>
</div>

<div class="123d" fn-droppable="asignar_acudiente" fn-confirm="Esta seguro que desea asignar el acudiente [nombre_acudiente]([id_acudiente]) a el estudiante [nombre_estudiante]([id_estudiante])" dranganddrop-datos='{"id_estudiante":"123","nombre_estudiante":"Pepito Perez"}'>
<span>Destino</span>
</div>

<script>
 function asignar_acudiente2(parametros){
 confirm("id_acudiente:"+parametros.id_acudiente+" id_estudiante:"+parametros.id_estudiante);
 }
</script>
<span id="txtsugerencias">
<?php 
consultar_acudientes();
buscar_acudiente_estudiante();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_acudiente_estudiante = document.getElementById('menu_acudiente_estudiante')
if (vmenu_acudiente_estudiante){
vmenu_acudiente_estudiante.className ='active '+vmenu_acudiente_estudiante.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
