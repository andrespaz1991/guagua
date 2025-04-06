<?php 
ob_start();
require("../comun/conexion.php");
 require_once("../comun/funciones.php"); 
 ?>
<center>
<?php 
function buscar_grupo_foro($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=grupo_foro.xls");
}
require("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
if (isset($_COOKIE['numeroresultados_grupo_foro']) and $_COOKIE['numeroresultados_grupo_foro']=="")  $_COOKIE['numeroresultados_grupo_foro']="0";
$resultados = ((isset($_COOKIE['numeroresultados_grupo_foro']) and $_COOKIE['numeroresultados_grupo_foro']!="" ) ? $_COOKIE['numeroresultados_grupo_foro'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_grupo_foro";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `grupo_foro`.`id_grupo_foro`, `grupo_foro`.`nombre_grupo`, `grupo_foro`.`roles_grupo`, `grupo_foro`.`contexto`, `grupo_foro`.`valor` FROM `grupo_foro`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
if (isset($_COOKIE['busqueda_avanzada_grupo_foro']) and $_COOKIE['busqueda_avanzada_grupo_foro']=="true"){
if (isset($_COOKIE['busqueda_av_grupo_foronombre_grupo']) and $_COOKIE['busqueda_av_grupo_foronombre_grupo']!=""){
$sql .= ' LOWER(`grupo_foro`.`nombre_grupo`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_grupo_foronombre_grupo'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_grupo_fororoles_grupo']) and $_COOKIE['busqueda_av_grupo_fororoles_grupo']!=""){
$sql .= ' LOWER(`grupo_foro`.`roles_grupo`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_grupo_fororoles_grupo'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_grupo_forocontexto']) and $_COOKIE['busqueda_av_grupo_forocontexto']!=""){
$sql .= ' LOWER(`grupo_foro`.`contexto`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_grupo_forocontexto'], 'UTF-8').'%" and ';
}
}
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`grupo_foro`.`nombre_grupo`)," ",LOWER(`grupo_foro`.`roles_grupo`)," ",LOWER(`grupo_foro`.`contexto`)) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbygrupo_foro']) and $_COOKIE['orderbygrupo_foro']!=""){ $sql .= "`grupo_foro`.`".$_COOKIE['orderbygrupo_foro']."`";
}else{ $sql .= "`grupo_foro`.`id_grupo_foro`";}
if (isset($_COOKIE['orderad_grupo_foro'])){
$orderadgrupo_foro = $_COOKIE['orderad_grupo_foro'];
$sql .=  " $orderadgrupo_foro ";
}else{
$sql .=  " desc ";
}
$consulta_total_grupo_foro = $mysqli->query($sql);
$total_grupo_foro = $consulta_total_grupo_foro->num_rows;
$paginacion->records($total_grupo_foro);
if ($reporte=="") $sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
$numero_grupo_foro = $consulta->num_rows;
$minimo_grupo_foro = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_grupo_foro = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_grupo_foro>$numero_grupo_foro) $maximo_grupo_foro=$numero_grupo_foro;
$maximo_grupo_foro += $minimo_grupo_foro-1;
if ($reporte=="") echo "<p>Resultados de $minimo_grupo_foro a $maximo_grupo_foro del total de ".$total_grupo_foro." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbgrupo_foro">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbygrupo_foro']) and $_COOKIE['orderbygrupo_foro']== "nombre_grupo"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbygrupo_foro','nombre_grupo');buscar();" >Nombre Grupo</th>
<th <?php  if(isset($_COOKIE['orderbygrupo_foro']) and $_COOKIE['orderbygrupo_foro']== "roles_grupo"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbygrupo_foro','roles_grupo');buscar();" >Roles Grupo</th>
<th <?php  if(isset($_COOKIE['orderbygrupo_foro']) and $_COOKIE['orderbygrupo_foro']== "contexto"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbygrupo_foro','contexto');buscar();" >Contexto</th>
<th <?php  if(isset($_COOKIE['orderbygrupo_foro']) and $_COOKIE['orderbygrupo_foro']== "valor"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbygrupo_foro','valor');buscar();" >Valor</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo" class="thbotones"><form id="formNuevo" name="formNuevo" method="post" action="grupo_foro.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="image" name="submit" id="submit" value="Nuevo" title="Nuevo" src="<?php echo SGA_COMUN_URL ?>/img/nuevo2.png">
</form>
</th>
<th data-label="XLS" class="thbotones"><form id="formNuevo" name="formNuevo" method="post" action="grupo_foro.php?xls">
<input name="cod" type="hidden" id="cod" value="0">
<input type="image" title="Descargar en XLS" src="<?php echo SGA_COMUN_URL ?>/img/xls.png" name="submit" id="submit" value="XLS">
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td data-label='Nombre Grupo'><?php echo $row['nombre_grupo']?></td>
<?php $datosroles_grupo = array("admin" => "Administrador", "docente" => "Docente", "estudiante" => "Estudiante", "acudiente" => "Acudiente"); ?>
<?php $sm_valores = explode(",",$row['roles_grupo']);
 ?><td><?php foreach ($sm_valores as $id_valores){
 echo $datosroles_grupo[$id_valores];
if (end($sm_valores) != $id_valores) echo ","; } 
 ?></td>

<td data-label='Contexto'><?php echo $row['contexto']?></td>
<td data-label='Valor'><?php echo $row['valor']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="grupo_foro.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_grupo_foro']; ?>">
<input type="image" src="<?php echo SGA_COMUN_URL ?>/img/modificar.png"name="submit" id="submit" value="Modificar" title="Modificar">
</form>
</td>
<td data-label="Eliminar">
<input title="Eliminar" type="image" src="<?php echo SGA_COMUN_URL ?>/img/eliminar2.png" onClick="confirmeliminar2('grupo_foro.php',{'del':'<?php echo $row['id_grupo_foro'];?>'},'<?php echo $row['nombre_grupo'];?>');" value="Eliminar">
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
buscar_grupo_foro($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_grupo_foro('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (!empty($_POST)){
 /*Validación de los datos recibidos mediante el método POST*/ 
 foreach ($_POST as $id => $valor){
  if (!is_array($_POST[$id]))
  $_POST[$id]=$mysqli->real_escape_string($valor);
 }

}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM grupo_foro WHERE concat(`grupo_foro`.`id_grupo_foro`)="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
$eliminar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=grupo_foro.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=grupo_foro.php" />
<?php 
}
}
 ?>
<center>
<h1>Grupo Foro</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
 @session_start(); 
$sql = "INSERT INTO grupo_foro (`id_grupo_foro`, `nombre_grupo`, `roles_grupo`, `contexto`, `valor`) VALUES ('".$_POST['id_grupo_foro']."', '".$_POST['nombre_grupo']."', '".implode(",",$_POST['roles_grupo'])."', '".$_POST['contexto']."', '".$_POST['valor']."')";
 /*echo $sql;*/
$insertar = $mysqli->query($sql);
if ($mysqli->errno != 1062){
if ($mysqli->affected_rows>0){
$insertid = $mysqli->insert_id;
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
<script>alert('Registro Exitoso');</script>
<meta http-equiv="refresh" content="1; url=grupo_foro.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=grupo_foro.php" />
<?php 
}
 }else{ 
 ?><script>alert('Este registro ya existe');</script>
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
if ($_POST['submit']=="Modificar"){
$sql = 'SELECT `id_grupo_foro`, `nombre_grupo`, `roles_grupo`, `contexto`, `valor` FROM `grupo_foro` WHERE concat(`grupo_foro`.`id_grupo_foro`) ="'.$_POST['cod'].'" Limit 1'; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
}
if ($_POST['submit']=="Nuevo"){
$textoh1 ="Registrar";
$textobtn ="Registrar";
}
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="grupo_foro.php" >
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_grupo_foro']))  echo $row['id_grupo_foro'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input title="" class="form-control" name="id_grupo_foro" type="hidden" id="id_grupo_foro" value="';if (isset($row['id_grupo_foro'])) echo $row['id_grupo_foro'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nombre_grupo">Nombre Grupo:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label><input title="" class="form-control" name="nombre_grupo" type="text" id="nombre_grupo" value="';if (isset($row['nombre_grupo'])) echo $row['nombre_grupo'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p>'; ?>
<label for="roles_grupo">Roles Grupo:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label><br><select multiple title=" Para seleccionar varios mantenga presionada la tecla Ctrl" class="form-control" name="roles_grupo[]" id="roles_grupo"  required>
<?php if (isset($row['roles_grupo'])) $sm_roles_grupo=explode(",",$row['roles_grupo']); ?>
<option value="admin" <?php if (isset($row['roles_grupo']) and in_array("admin",$sm_roles_grupo)) echo " selected "; ?> >Administrador</option>
<option value="docente" <?php if (isset($row['roles_grupo']) and in_array("docente",$sm_roles_grupo)) echo " selected "; ?> >Docente</option>
<option value="estudiante" <?php if (isset($row['roles_grupo']) and in_array("estudiante",$sm_roles_grupo)) echo " selected "; ?> >Estudiante</option>
<option value="acudiente" <?php if (isset($row['roles_grupo']) and in_array("acudiente",$sm_roles_grupo)) echo " selected "; ?> >Acudiente</option>
</select></p>
<?php 
echo "</div>";echo '<div class="form-group"><p><label for="contexto">Contexto:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label><input title="" class="form-control" name="contexto" type="text" id="contexto" value="';if (isset($row['contexto'])) echo $row['contexto'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p><label for="valor">Valor:</label><input title="" class="form-control" name="valor" type="text" id="valor" value="';if (isset($row['valor'])) echo $row['valor'];echo '"';echo '></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin mixto*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
 @session_start(); 
$sql = "UPDATE grupo_foro SET id_grupo_foro='".$_POST['id_grupo_foro']."', nombre_grupo='".$_POST['nombre_grupo']."', roles_grupo='".  implode(",",$_POST['roles_grupo'])."', contexto='".$_POST['contexto']."', valor='".$_POST['valor']."'WHERE  `grupo_foro`.`id_grupo_foro` = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
$actualizar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue ingresado con éxito*/
?>
Modificación exitosa
<meta http-equiv="refresh" content="1; url=grupo_foro.php" />
<?php 
 }else{ 
?>
Modificación fallida
<meta http-equiv="refresh" content="2; url=grupo_foro.php" />
<?php 
}
} /*fin Actualizar*/ 
 }else{ 
if (isset($_COOKIE['numeroresultados_grupo_foro']) and $_COOKIE['numeroresultados_grupo_foro']=="")  $_COOKIE['numeroresultados_grupo_foro']="10";
 ?>
<center>
<b><label>Buscar: </label></b><input placeholder="Buscar por palabra clave" title="Buscar por palabra clave: Nombre Grupo, Roles Grupo, Contexto" type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_grupo_foro" placeholder="Cant." title="Cantidad de resultados" value="<?php $no_resultados = ((isset($_COOKIE['numeroresultados_grupo_foro']) and $_COOKIE['numeroresultados_grupo_foro']!="" ) ? $_COOKIE['numeroresultados_grupo_foro'] : 10); echo $no_resultados; ?>" onkeyup="grabarcookie('numeroresultados_grupo_foro',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_grupo_foro',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_grupo_foro',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 60px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_grupo_foro','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_grupo_foro','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
<p><label><input type="checkbox" onchange="grabarcookie('busqueda_avanzada_grupo_foro',this.checked);mostrar_busqueda_avanzada(this.checked);buscar();" <?php if (isset($_COOKIE['busqueda_avanzada_grupo_foro']) and $_COOKIE['busqueda_avanzada_grupo_foro']=='true') echo 'checked' ?>>Búsqueda Avanzada</label></p>
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
<div class="busqueda_avanzada" <?php if (!isset($_COOKIE['busqueda_avanzada_grupo_foro']) or (isset($_COOKIE['busqueda_avanzada_grupo_foro']) and $_COOKIE['busqueda_avanzada_grupo_foro']!='true')) echo 'style="display:none"' ?>>
<div class="row">
<div class="form-group col-md-4"><p><label for="nombre_grupo">Nombre Grupo<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_grupo_foronombre_grupo',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_grupo_foronombre_grupo'])) echo $_COOKIE['busqueda_av_grupo_foronombre_grupo']; ?>
"></label></p></div>
<div class="form-group col-md-4"><p><label for="roles_grupo">Roles Grupo<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_grupo_fororoles_grupo',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_grupo_fororoles_grupo'])) echo $_COOKIE['busqueda_av_grupo_fororoles_grupo']; ?>
"></label></p></div>
<div class="form-group col-md-4"><p><label for="contexto">Contexto<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_grupo_forocontexto',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_grupo_forocontexto'])) echo $_COOKIE['busqueda_av_grupo_forocontexto']; ?>
"></label></p></div>
</div>
<input type="button" onclick="buscar();" class="btn btn-primary" value="Buscar">
</div>
<span id="txtsugerencias">
<?php 
buscar_grupo_foro();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_grupo_foro = document.getElementById('menu_grupo_foro')
if (vmenu_grupo_foro){
vmenu_grupo_foro.className ='active '+vmenu_grupo_foro.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
