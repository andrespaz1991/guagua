<?php 
class menu_items extends Clase_mysqli{
    public $menu_item_id ;
    public $permisos_menu=[];
    public $nombre_menu;
    public $menu_url;
    public $ubicacion;
    public $tipo;
public function __construct($menu_item_id='',$menu_url=''){
   if ($menu_item_id!='' or $menu_url!=''){
     require(dirname(__FILE__)."/../config/conexion.php");
     $sql = "SELECT * FROM `menu_items` WHERE ";
     if ($menu_url!=''){
     $sql .= " `menu_url` = '".$menu_url."';";
     }else{
     $sql .= " `menu_item_id` = '".$menu_item_id."';";
     }
     $consulta = $mysqli->query($sql);
     if($row=$consulta->fetch_assoc()){
       $this->setear($row);
     }
   }
}
public function listar_menu(){
  $sql='select menu_item_name,ubicacion,tipo from  menu_items where menu_item_id = "'.$this->menu_item_id.'" limit 1';
 	$funcionario = json_decode($this->consultar_datos($sql,true),true);
foreach ($funcionario as $value) {
 $this->nombre_menu= $value['menu_item_name'];
 $this->ubicacion= $value['ubicacion'];
 $this->tipo= $value['tipo'];
}

}
public function buscar_menu_items($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=menu_items.xls");
}
$mysqli = $this->conectar();
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
if (isset($_COOKIE['numeroresultados_menu_items']) and $_COOKIE['numeroresultados_menu_items']=="")  $_COOKIE['numeroresultados_menu_items']="0";
$resultados = ((isset($_COOKIE['numeroresultados_menu_items']) and $_COOKIE['numeroresultados_menu_items']!="" ) ? $_COOKIE['numeroresultados_menu_items'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$cookiepage="page_menu_items";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `menu_items`.`menu_item_id`, `menu_items`.`menu_item_name`, `menu_items`.`menu_description`, `menu_items`.`menu_url`, `menu_items`.`url_target`, `menu_items`.`icono`, `menu_items`.`ubicacion`, `menu_items`.`tipo`, `menu_items`.`color` FROM `menu_items`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
if (isset($_COOKIE['busqueda_avanzada_menu_items']) and $_COOKIE['busqueda_avanzada_menu_items']=="true"){
if (isset($_COOKIE['busqueda_av_menu_itemsmenu_item_name']) and $_COOKIE['busqueda_av_menu_itemsmenu_item_name']!=""){
$sql .= ' LOWER(`menu_items`.`menu_item_name`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemsmenu_item_name'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemsmenu_description']) and $_COOKIE['busqueda_av_menu_itemsmenu_description']!=""){
$sql .= ' LOWER(`menu_items`.`menu_description`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemsmenu_description'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemsmenu_url']) and $_COOKIE['busqueda_av_menu_itemsmenu_url']!=""){
$sql .= ' LOWER(`menu_items`.`menu_url`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemsmenu_url'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemsurl_target']) and $_COOKIE['busqueda_av_menu_itemsurl_target']!=""){
$sql .= ' LOWER(`menu_items`.`url_target`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemsurl_target'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemsicono']) and $_COOKIE['busqueda_av_menu_itemsicono']!=""){
$sql .= ' LOWER(`menu_items`.`icono`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemsicono'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemsubicacion']) and $_COOKIE['busqueda_av_menu_itemsubicacion']!=""){
$sql .= ' LOWER(`menu_items`.`ubicacion`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemsubicacion'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemstipo']) and $_COOKIE['busqueda_av_menu_itemstipo']!=""){
$sql .= ' LOWER(`menu_items`.`tipo`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemstipo'], 'UTF-8').'%" and ';
}
if (isset($_COOKIE['busqueda_av_menu_itemscolor']) and $_COOKIE['busqueda_av_menu_itemscolor']!=""){
$sql .= ' LOWER(`menu_items`.`color`) LIKE "%'.mb_strtolower($_COOKIE['busqueda_av_menu_itemscolor'], 'UTF-8').'%" and ';
}
}
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`menu_items`.`menu_item_name`)," ",LOWER(`menu_items`.`menu_description`)," ",LOWER(`menu_items`.`menu_url`)," ",LOWER(`menu_items`.`url_target`)," ",LOWER(`menu_items`.`class`)," ",LOWER(`menu_items`.`icono`)," ",LOWER(`menu_items`.`ubicacion`)," ",LOWER(`menu_items`.`tipo`)," ",LOWER(`menu_items`.`color`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']!=""){ $sql .= "`menu_items`.`".$_COOKIE['orderbymenu_items']."`";
}else{ $sql .= "`menu_items`.`menu_item_id`";}
if (isset($_COOKIE['orderad_menu_items'])){
$orderadmenu_items = $_COOKIE['orderad_menu_items'];
$sql .=  " $orderadmenu_items ";
}else{
$sql .=  " desc ";
}
#echo $sql;
$consulta_total_menu_items = $mysqli->query($sql);
$total_menu_items = $consulta_total_menu_items->num_rows;
$paginacion->records($total_menu_items);
if ($reporte=="") $sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 
$consulta = $mysqli->query($sql);
$numero_menu_items = $consulta->num_rows;
$minimo_menu_items = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_menu_items = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_menu_items>$numero_menu_items) $maximo_menu_items=$numero_menu_items;
$maximo_menu_items += $minimo_menu_items-1;
if ($reporte==""){
 ?>
<center><p><?php echo "Resultados de $minimo_menu_items a $maximo_menu_items del total de ".$total_menu_items." en página ".$paginacion->get_page(); ?></p></center>
 <?php } ?>
<div align="center">
<table border="1" id="tbmenu_items">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "menu_item_id"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','menu_item_id');buscar();" >ID</th>

<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "menu_item_name"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','menu_item_name');buscar();" >Nombre</th>
<th>Permisos</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "menu_description"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','menu_description');buscar();" >Descripción</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "menu_url"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','menu_url');buscar();" >Url</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "url_target"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','url_target');buscar();" >Target</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "class"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','class');buscar();" >Class</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "icono"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','icono');buscar();" >Icono</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "ubicacion"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','ubicacion');buscar();" >Ubicacion</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "tipo"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','tipo');buscar();" >Tipo</th>
<th <?php  if(isset($_COOKIE['orderbymenu_items']) and $_COOKIE['orderbymenu_items']== "color"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbymenu_items','color');buscar();" >Color</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo" class="thbotones"><form id="formNuevo" name="formNuevo" method="post" action="menu_items.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="image" name="submit" id="submit" value="Nuevo" title="Nuevo" src="<?php echo URL_RAIZ ?>img/nuevo.png">
</form>
</th>
<th data-label="XLS" class="thbotones"><form id="formNuevo" name="formNuevo" method="post" action="menu_items.php?xls">
<input name="cod" type="hidden" id="cod" value="0">
<input type="image" title="Descargar en XLS" src="<?php echo URL_RAIZ ?>img/xls.png" name="submit" id="submit" value="XLS">
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){  ?>
<tr>
<td data-label='ID'><?php echo $row['menu_item_id']?></td>
<td data-label='Nombre'><?php echo $row['menu_item_name']?></td>
<td data-label='permisos'><a class="btn btn-success" target="_blank" href="<?php echo URL_RAIZ.'/views/permisos.php?id_menu='.$row['menu_item_id'].''; ?>">Permisos</a></td>

<td data-label='Descripción'><?php echo $row['menu_description']?></td>
<td data-label='Url'><a class="btn btn-primary" target="_blank" href="<?php echo URL_RAIZ.$row['menu_url']?>"><?php echo $row['menu_url']?></a></td>
<?php $datosurl_target = array("_blank" => "Nueva Ventana", "_self" => "Misma Ventana"); ?>
<td data-label='Target'><?php echo $datosurl_target[$row['url_target']] ?></td>
<td data-label='Class'><?php echo $row['class']?></td>
<td data-label='Icono'>
 <span class="<?php echo $row['icono']?>"></span>
 </td>
<?php $datosubicacion = array("ambas" => "Metro y Panel 'Sidebar","metro" => "Metro", "sidebar" => "Panel 'Sidebar'", "" => "No Definida"); ?>
<td data-label='Ubicacion'><?php echo $datosubicacion[$row['ubicacion']] ?></td>
<td data-label='Tipo'><?php echo $row['tipo']?></td>
<td data-label='Color' bgcolor="<?php echo $row['color']?>"></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="../comun/menu_items.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['menu_item_id']; ?>">
<input type="image" src="<?php echo URL_RAIZ ?>img/modificar.png"name="submit" id="submit" value="Modificar" title="Modificar">
</form>
</td>
<td data-label="Eliminar">
<input title="Eliminar" type="image" src="<?php echo URL_RAIZ ?>img/eliminar.png" onClick="confirmeliminar('menu_items.php',{'del':'<?php echo $row['menu_item_id'];?>'},'<?php echo $row['menu_item_id']." ".$row['menu_item_name'];?>');" value="Eliminar">
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
public function eliminar_menu_items($del){
$mysqli = $this->conectar();
/*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM menu_items WHERE concat(`menu_items`.`menu_item_id`)="'.$del.'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
$eliminar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue eliminado con éxito*/ 
 return true;
}else{
 return false;
}
}
public function registrar_menu_items(){
$mysqli = $this->conectar();
 /*recibo los campos del formulario proveniente con el método POST*/ 
 @session_start(); 
$sql = "INSERT INTO menu_items (`menu_item_id`, `menu_item_name`, `menu_description`, `menu_url`, `url_target`, `class`, `icono`, `ubicacion`, `tipo`, `color`,permisos_menu) VALUES ('".$_POST['menu_item_id']."', '".$_POST['menu_item_name']."', '".$_POST['menu_description']."', '".$_POST['menu_url']."', '".$_POST['url_target']."', '".$_POST['class']."', '".$_POST['icono']."', '".$_POST['ubicacion']."', '".$_POST['tipo']."', '".$_POST['color']."', '[\"1\"]')";
$insertar = $mysqli->query($sql);
if ($mysqli->errno != 1062){
if ($mysqli->affected_rows>0){
$insertid = $mysqli->insert_id;
 /*Validamos si el registro fue ingresado con éxito*/ 
 return 1;
 }else{ 
 return 0; 
}
 }else{ 
 return 1062;
}
} /*fin Registrar*/ 

public function actualizar_estado_menu(){
  $sql="UPDATE `menu_items` SET `permisos_menu`='".$this->permisos_menu."' WHERE `menu_item_id` =$this->menu_item_id";
$mysqli = $this->conectar();
  $actualizar = $mysqli->query($sql);
  if ($mysqli->affected_rows>0){
   /*Validamos si el registro fue ingresado con éxito*/
  return true;
   }else{ 
  return false;
  }
}

public function actualizar_menu_items(){
$mysqli = $this->conectar();
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
 @session_start(); 
$sql = "UPDATE menu_items SET menu_item_id='".$_POST['menu_item_id']."', menu_item_name='".$_POST['menu_item_name']."', menu_description='".$_POST['menu_description']."', menu_url='".$_POST['menu_url']."', url_target='".$_POST['url_target']."', class='".$_POST['class']."', icono='".$_POST['icono']."', ubicacion='".$_POST['ubicacion']."', tipo='".$_POST['tipo']."', color='".$_POST['color']."'WHERE  `menu_items`.`menu_item_id` = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
$actualizar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue ingresado con éxito*/
return true;
 }else{ 
return false;
}
} /*fin Actualizar*/ 

public function listar_permisos_menu(){
 $this->setear();
$sql= 'Select  permisos_menu from menu_items where menu_url ="'.$this->menu_url.'" ';
$permisos = json_decode($this->consultar_datos($sql,true),true);
   if(!empty($permisos))   return($permisos[0]);  
}

 
}


