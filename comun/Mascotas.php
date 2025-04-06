<?php 
ob_start();
 ?>
<center>
<?php 
require("conexion.php");
 /*require_once("funciones.php");*/ 
function buscar_figuras($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=figuras.xls");
}
require("conexion.php");
require_once ("lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_figuras']) ? $_COOKIE['numeroresultados_figuras'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_figuras";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `figuras`.`id_figuras`, `figuras`.`figura`, `figuras`.`imagen_figura` FROM `figuras`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`figuras`.`figura`)," ",LOWER(`figuras`.`imagen_figura`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyfiguras']) and $_COOKIE['orderbyfiguras']!=""){ $sql .= "`figuras`.`".$_COOKIE['orderbyfiguras']."`";
}else{ $sql .= "`figuras`.`id_figuras`";}
if (isset($_COOKIE['orderad_figuras'])){
$orderadfiguras = $_COOKIE['orderad_figuras'];
$sql .=  " $orderadfiguras ";
}else{
$sql .=  " desc ";
}
$consulta_total_figuras = $mysqli->query($sql);
$total_figuras = $consulta_total_figuras->num_rows;
$paginacion->records($total_figuras);
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
/*echo $sql;*/ 

$consulta = $mysqli->query($sql);
$numero_figuras = $consulta->num_rows;
$minimo_figuras = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_figuras = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_figuras>$numero_figuras) $maximo_figuras=$numero_figuras;
$maximo_figuras += $minimo_figuras-1;
echo "<p>Resultados de $minimo_figuras a $maximo_figuras del total de ".$total_figuras." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbfiguras">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyfiguras']) and $_COOKIE['orderbyfiguras']== "figura"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyfiguras','figura');buscar();" >Figura</th>
<th <?php  if(isset($_COOKIE['orderbyfiguras']) and $_COOKIE['orderbyfiguras']== "imagen_figura"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyfiguras','imagen_figura');buscar();" >Imagen Figura</th>
<?php if ($reporte==""){ ?>
<th data-label="Nuevo"><form id="formNuevo" name="formNuevo" method="post"  action="Mascotas.php" ENCTYPE="multipart/form-data">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<th data-label="XLS"><form id="formNuevo" name="formNuevo" method="post" action="Mascotas.php?xls" ENCTYPE="multipart/form-data">
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
<td data-label='Figura'><?php echo $row['figura']?></td>
<td data-label='Imagen Figura'>
 <img width="50px" src="img/figuras/<?php echo $row['imagen_figura']; ?>"></img>
 <?php #echo $row['imagen_figura']?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="Mascotas.php" ENCTYPE="multipart/form-data">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_figuras']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input type="image" src="img/eliminar.png" onClick="confirmeliminar2('Mascotas.php',{'del':'<?php echo $row['id_figuras'];?>'},'<?php echo $row['id_figuras'];?>');" value="Eliminar">
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
buscar_figuras($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_figuras('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 require 'conexion.php';
 $sql_mascota = 'select * from figuras where id_figuras="'.$_POST['del'].'"';
 $consulta_mascota = $mysqli -> query($sql_mascota);
 if ($rowmascota =$consulta_mascota->fetch_assoc()){
  $ruta_mascota = $rowmascota['imagen_figura'];
 }
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM figuras WHERE id_figuras="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if(unlink('img/figuras/'.$ruta_mascota)) {

if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=Mascotas.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=Mascotas.php" />
<?php 
}
}
}
 ?>
<center>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">Mascotas</h1>      
  </div>
</div>

</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
require 'funciones.php';
 $tamaño_maximo= tamaño_maximo(); 
  $formatos =formatos();
  $total = count($_FILES['imagen_figura']['name']);
  for($i=0; $i<=$total; $i++) {
 $nombre_archivo=$_FILES['imagen_figura']['name'][$i]; 
      $ruta_tmp_archivo = $_FILES['imagen_figura']['tmp_name'][$i];
echo $ruta_destino = "img/figuras/".$_FILES['imagen_figura']['name'][$i];        if ($ruta_tmp_archivo != ""){ 
            $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION)); 
       if (in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; } 
            if(filesize($_FILES['imagen_figura']['tmp_name'][$i]) > $tamaño_maximo ) {
 echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
              exit(); 
            }

       }
  
 if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) { //
 /*recibo los campos del formulario proveniente con el método POST*/ 
 $sql = "INSERT INTO figuras ( `figura`, `imagen_figura`) VALUES ( '".$_POST['figura']."', '".$_FILES['imagen_figura']['name'][$i]."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
Registro exitoso
<meta http-equiv="refresh" content="1; url=Mascotas.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=Mascotas.php" />
<?php 
}
 }
  } 
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="Mascotas.php" ENCTYPE="multipart/form-data">
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_figuras"type="hidden" id="id_figuras" value="';if (isset($row['id_figuras'])) echo $row['id_figuras'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="figura">Figura:</label><input class="form-control"name="figura"type="text" id="figura" value="';if (isset($row['figura'])) echo $row['figura'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p><label for="imagen_figura">Imagen Figura:</label><input class="form-control" name="imagen_figura[]" type="file" multiple="multiple" id="imagen_figura" value="';if (isset($row['imagen_figura'])) echo $row['imagen_figura'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_figuras`, `figura`, `imagen_figura` FROM `figuras` WHERE id_figuras ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="Mascotas.php" ENCTYPE="multipart/form-data">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_figuras']))  echo $row['id_figuras'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_figuras"type="hidden" id="id_figuras" value="';if (isset($row['id_figuras'])) echo $row['id_figuras'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="figura">Figura:</label><input class="form-control"name="figura"type="text" id="figura" value="';if (isset($row['figura'])) echo $row['figura'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p><label for="imagen_figura">Imagen Figura:</label><input class="form-control" name="imagen_figura[]" type="file"  multiple="multiple" id="imagen_figura" value="';if (isset($row['imagen_figura'])) echo $row['imagen_figura'];echo '"';echo '  ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 require 'funciones.php';
 $tamaño_maximo= tamaño_maximo(); 
  $formatos =formatos();

 if($_FILES['imagen_figura']['tmp_name'][0] == ""){
 $sql = 'UPDATE figuras SET figura="'.$_POST['figura'].'" ';
$sql.=' WHERE  id_figuras = "'.$_POST['cod'].'";'; 

  
//  $actualizar = $mysqli->query($sql);
if($consulta =mysqli_query($mysqli,$sql)){ 
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=Mascotas.php" />';

 }else{ 
echo 'Modificacion fallida';
echo '<meta http-equiv="refresh" content="2; url=Mascotas.php" />';
 }
 echo '<meta http-equiv="refresh" content="2; url=Mascotas.php" />';

 }
 
 
  $total = count($_FILES['imagen_figura']['name']);
  for($i=0; $i<=$total; $i++) {
 $nombre_archivo=$_FILES['imagen_figura']['name'][$i]; 
  $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION));
      $ruta_tmp_archivo = $_FILES['imagen_figura']['tmp_name'][$i];
 $ruta_destino = "img/figuras/".$_POST['figura'].'.'.$extensión_archivo; 
 
 if ($ruta_tmp_archivo != ""){ 
            $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION)); 
       if (in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; } 
            if(filesize($_FILES['imagen_figura']['tmp_name'][$i]) > $tamaño_maximo ) {
 echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
              exit(); 
            }

       } 
                  if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) { 
 $cod = $_POST['cod'];
$sql = "UPDATE figuras SET figura='".$_POST['figura']."' ";
if($ruta_tmp_archivo<>""){
$sql.=" , imagen_figura='".$_POST['figura'].'.'.$extensión_archivo."' ";
}
$sql.=" WHERE  id_figuras = '".$_POST['cod']."';"; 
#echo $sql;
if ($actualizar = $mysqli->query($sql)) {
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=Mascotas.php" />';
 }else{ 
echo 'Modificacion fallida';
echo '<meta http-equiv="refresh" content="2; url=Mascotas.php" />';
 }
}        
                   
                  }
  }
 
 
 
 
 
 
 
 
 
 
 
 
 
 /*recibo los campos del formulario proveniente con el método POST*/ 

 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_figuras" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_figuras',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_figuras',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_figuras',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_figuras','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_figuras','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php 
buscar_figuras();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_Mascotas = document.getElementById('menu_Mascotas')
if (vmenu_Mascotas){
vmenu_Mascotas.className ='active '+vmenu_Mascotas.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
 ?>
