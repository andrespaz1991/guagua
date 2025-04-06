<?php 
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("../comun/autoload.php");
@session_start();
#$session->solo_Validar_session();
#if(($_SESSION['identificacion']<>1)){  $session->validar_session(); }
#$session->validar_session();
$menu_items = new menu_items();
$mysqli = $menu_items->conectar();
if (isset($_GET['buscar'])){
$menu_items->buscar_menu_items($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
$menu_items->buscar_menu_items('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
}
if (!empty($_POST)){
 /*Validación de los datos recibidos mediante el método POST*/ 
 foreach ($_POST as $id => $valor){$_POST[$id]=$mysqli->real_escape_string($valor);} 
}
if (isset($_POST['del'])){
$res_eliminar = $menu_items->eliminar_menu_items($_POST['del']);
if ($res_eliminar){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
<script>alert('Registro eliminado exitosamente');</script>
<meta http-equiv="refresh" content="1; url=menu_items.php" />
<?php 
}else{
?>
<script>alert('Eliminación fallida, por favor compruebe que la usuario no esté en uso');</script>
<meta http-equiv="refresh" content="2; url=menu_items.php" />
<?php 
}
}//fin if isset POST del
 ?>
<center>
<center>
<h1><strong>Opciones de Menú</strong></h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
$res_insertar = $menu_items->registrar_menu_items();
 /*recibo los campos del formulario proveniente con el método POST*/ 
 @session_start(); 
 /*Validamos si el registro fue ingresado con éxito*/ 
if ($res_insertar==1){
 ?>
<script>alert('Registro Exitoso');</script>
<meta http-equiv="refresh" content="1; url=menu_items.php" />
<?php 
}elseif ($res_insertar==0){
 ?>
<script>alert('No se pudo registrar, por favor verifique su información e intente de nuevo');</script>
<meta http-equiv="refresh" content="1; url=menu_items.php" />
<?php }elseif ($res_insertar==1062){
 ?> <script>alert('Este registro ya existe');</script><meta http-equiv="refresh" content="1; url=menu_items.php" />
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Actualizar"){
 @session_start(); 
$res_actualizar = $menu_items->actualizar_menu_items();
if ($res_actualizar){
 /*Validamos si el registro fue ingresado con éxito*/
?>
<script>alert('Modificación exitosa');</script>
<meta http-equiv="refresh" content="1; url=menu_items.php" />
<?php 
 }else{ 
?>
<script>alert('No se pudo modificar, por favor verifique su información e intente de nuevo');</script>
<meta http-equiv="refresh" content="2; url=menu_items.php" />
<?php 
}
} /*fin Actualizar*/ 
if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
if ($_POST['submit']=="Modificar"){
$sql = 'SELECT `menu_item_id`, `menu_item_name`, `menu_description`, `menu_url`, `url_target`, `class`, `icono`, `ubicacion`, `tipo`, `color` FROM `menu_items` WHERE concat(`menu_items`.`menu_item_id`) ="'.$_POST['cod'].'" Limit 1'; 
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
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="menu_items.php" >
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['menu_item_id']))  echo $row['menu_item_id'] ?>" size="120" required></p>
<div class="form-group">
<p>
<input tabla="menu_items" title="" class="form-control" name="menu_item_id" type="hidden" id="id_menu_items" value="<?php if (isset($row['menu_item_id'])) echo $row['menu_item_id']; ?>">
</p> 
</div>
<div class="form-group">
<p>
<label for="menu_item_name">Nombre:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span>
</label>
<input tabla="menu_items" title="" class="form-control" name="menu_item_name" type="text" id="menu_item_name" value="<?php if (isset($row['menu_item_name'])) echo $row['menu_item_name']; ?>" required >
</p> 
</div>
<div class="form-group">
<p>
<label for="menu_description">Descripción:</label>
<input tabla="menu_items" title="" class="form-control" name="menu_description" type="text" id="menu_description" value="<?php if (isset($row['menu_description'])) echo $row['menu_description']; ?>" >
</p> 
</div>
<div class="form-group">
<p>
<label for="menu_url">Url:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span>
</label>
<input tabla="menu_items" title="" class="form-control" name="menu_url" type="text" id="menu_url" value="<?php if (isset($row['menu_url'])) echo $row['menu_url']; ?>" required >
</p> 
</div>
<div class="form-group">
<p>
<p>
<label for="url_target">Target:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label>
</p>
<label><input type="radio" title="" class="" name="url_target" id="url_target[1]"  required value="_blank" <?php if (isset($row['url_target']) and $row['url_target'] =="_blank") echo " checked "; ?> >Nueva Ventana</label>
<br>
<label><input type="radio" title="" class="" name="url_target" id="url_target[2]"  required value="_self" <?php if (isset($row['url_target']) and $row['url_target'] =="_self") echo " checked "; ?> >Misma Ventana</label>
<br>
</p>
</div>
<div class="form-group">
<p>
<label for="class">Class</label>
<input tabla="menu_items" title="" class="form-control" name="class" type="text" id="class" value="<?php if (isset($row['class'])) #echo $row['class']; ?>" >
</p> 
</div>
<div class="form-group">
<p>
<label for="icono">Icono:</label>
<input tabla="menu_items" title="" class="form-control" name="icono" type="text" id="icono" value="<?php if (isset($row['icono'])) echo $row['icono']; ?>" >
</p> 
</div>
<div class="form-group">
<p>
<p>
<label for="ubicacion">Ubicacion:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span></label>
</p>
<label><input type="radio" title="" class="" name="ubicacion" id="ubicacion[1]"  required value="metro" <?php if (isset($row['ubicacion']) and $row['ubicacion'] =="metro") echo " checked "; ?> >Metro</label>
<br>
<label><input type="radio" title="" class="" name="ubicacion" id="ubicacion[2]"  required value="sidebar" <?php if (isset($row['ubicacion']) and $row['ubicacion'] =="sidebar") echo " checked "; ?> >Panel 'Sidebar'</label>
<br>
<label><input type="radio" title="" class="" name="ubicacion" id="ubicacion[3]"  required value="ambas" <?php if (isset($row['ubicacion']) and $row['ubicacion'] =="ambas") echo " checked "; ?> >Metro y Panel</label>
<br>
<label><input type="radio" title="" class="" name="ubicacion" id="ubicacion[4]"  required value="" <?php if (isset($row['ubicacion']) and $row['ubicacion'] =="") echo " checked "; ?> >No Definida</label>
<br>
</p>
</div>
<div class="form-group">
<p>
<label for="tipo">Tipo:</label>
<input tabla="menu_items" title="" datalist="menu_items" list="list_tipo" class="form-control" name="tipo" type="text" id="tipo" value="<?php if (isset($row['tipo'])) echo $row['tipo']; ?>" >
</p> 
</div>
<div class="form-group">
<p>
<label for="color">Color:<span title="Este campo es obligatorio" style="color:red;font-size: 30px;margin: 2px;/top: 12px;top: 13px;position: relative;">*</span>
</label>
<input tabla="menu_items" title="" class="form-control" name="color" type="color" id="color" value="<?php if (isset($row['color'])) echo $row['color']; ?>" required >
</p> 
</div>
<p><input type="hidden" name="submit" id="submit" value="<?php echo $textobtn; ?>"><button type="submit" class="btn btn-primary"><?php echo $textobtn; ?></button></p>
</form><div class="col-md-3"></div>
<?php 
} /*fin mixto*/ 

 }else{ 
if (isset($_COOKIE['numeroresultados_menu_items']) and $_COOKIE['numeroresultados_menu_items']=="")  $_COOKIE['numeroresultados_menu_items']="10";
 ?>
<center>
<b><label>Buscar: </label></b><input placeholder="Buscar por palabra clave" title="Buscar por palabra clave: Nombre, Descripción, Url, Target, Class, Icono, Ubicacion, Tipo, Color" type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados por página:</label></b>
<input type="number" min="0" id="numeroresultados_menu_items" placeholder="Cant." title="Cantidad de resultados" value="<?php $no_resultados = ((isset($_COOKIE['numeroresultados_menu_items']) and $_COOKIE['numeroresultados_menu_items']!="" ) ? $_COOKIE['numeroresultados_menu_items'] : 10); echo $no_resultados; ?>" onkeyup="grabarcookie('numeroresultados_menu_items',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_menu_items',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_menu_items',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 60px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_menu_items','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_menu_items','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
<p><label><input type="checkbox" onchange="grabarcookie('busqueda_avanzada_menu_items',this.checked);mostrar_busqueda_avanzada(this.checked);buscar();" <?php if (isset($_COOKIE['busqueda_avanzada_menu_items']) and $_COOKIE['busqueda_avanzada_menu_items']=='true') echo 'checked' ?>>Búsqueda Avanzada</label></p>
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
<div class="busqueda_avanzada" <?php if (!isset($_COOKIE['busqueda_avanzada_menu_items']) or (isset($_COOKIE['busqueda_avanzada_menu_items']) and $_COOKIE['busqueda_avanzada_menu_items']!='true')) echo 'style="display:none"' ?>>
<div class="form-group"><p><label for="menu_item_name">Nombre<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsmenu_item_name',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsmenu_item_name'])) echo $_COOKIE['busqueda_av_menu_itemsmenu_item_name']; ?>
"></label></p></div>
<div class="form-group"><p><label for="menu_description">Descripción<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsmenu_description',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsmenu_description'])) echo $_COOKIE['busqueda_av_menu_itemsmenu_description']; ?>
"></label></p></div>
<div class="form-group"><p><label for="menu_url">Url<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsmenu_url',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsmenu_url'])) echo $_COOKIE['busqueda_av_menu_itemsmenu_url']; ?>
"></label></p></div>
<div class="form-group"><p><label for="url_target">Target<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsurl_target',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsurl_target'])) echo $_COOKIE['busqueda_av_menu_itemsurl_target']; ?>
"></label></p></div>
<div class="form-group"><p><label for="class">Class<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsclass',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsclass'])) echo $_COOKIE['busqueda_av_menu_itemsclass']; ?>
"></label></p></div>
<div class="form-group"><p><label for="icono">Icono<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsicono',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsicono'])) echo $_COOKIE['busqueda_av_menu_itemsicono']; ?>
"></label></p></div>
<div class="form-group"><p><label for="ubicacion">Ubicacion<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemsubicacion',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemsubicacion'])) echo $_COOKIE['busqueda_av_menu_itemsubicacion']; ?>
"></label></p></div>
<div class="form-group"><p><label for="tipo">Tipo<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemstipo',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemstipo'])) echo $_COOKIE['busqueda_av_menu_itemstipo']; ?>
"></label></p></div>
<div class="form-group"><p><label for="color">Color<input title="" class="form-control input_busqueda_avanzada" type="search" onchange="grabarcookie('busqueda_av_menu_itemscolor',this.value);buscar();" onblur="this.onchange();" value="
<?php if (isset($_COOKIE['busqueda_av_menu_itemscolor'])) echo $_COOKIE['busqueda_av_menu_itemscolor']; ?>
"></label></p></div>

<input type="button" onclick="buscar();" class="btn btn-primary" value="Buscar">
</div>
<span id="txtsugerencias">
<?php 
$menu_items->buscar_menu_items();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_menu_items = document.getElementById('menu_menu_items')
if (vmenu_menu_items){
vmenu_menu_items.className ='active '+vmenu_menu_items.className;
}
</script>
<?php
$contenido = ob_get_clean();
$plantilla=new Plantilla(1);
require "$plantilla->ruta";  
?>
