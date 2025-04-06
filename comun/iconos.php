<?php 
ob_start();
require("conexion.php");
require_once("funciones.php");

 ?>
<center>
<?php 
if (isset($_POST['del'])){
 $sql='DELETE FROM `iconos` WHERE id_iconos= "'.$_POST['del'].'"';
if ($mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
?>
<script>alert2('Registro eliminado');</script>
<meta http-equiv="refresh" content="1; url=iconos.php" />
<?php 
}else{
?>
<script>alert2('Eliminación fallida, por favor compruebe que la usuario no esté en uso','error');</script>
<meta http-equiv="refresh" content="2; url=iconos.php" />
<?php 
}
}

function eliminar_icono($del){
 require dirname(__FILE__).'/conexion.php';
 $sql_mascota = 'select * from iconos where id_iconos="'.$_POST['del'].'"';
 $consulta_mascota = $mysqli -> query($sql_mascota);
 if ($rowmascota =$consulta_mascota->fetch_assoc()){
  $ruta_mascota = $rowmascota['imagen_icono'];
 }
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM iconos WHERE id_iconos="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
 if(unlink(dirname(__FILE__).'/img/png/'.$ruta_mascota)) {
  if ($eliminar = $mysqli->query($sql)){
   /*Validamos si el registro fue eliminado con éxito*/ 
  return true;
  }else{
  return false;
  }
 }
}
 ?>
<center>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">iconos</h1>      
  </div>
</div>

</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
require_once 'funciones.php';
 $tamaño_maximo= tamaño_maximo(); 
  $formatos =formatos();
  $total = count($_FILES['imagen_icono']['name']);
  for($i=0; $i<=$total; $i++) {
 $nombre_archivo=$_FILES['imagen_icono']['name'][$i]; 
      $ruta_tmp_archivo = $_FILES['imagen_icono']['tmp_name'][$i];
echo $ruta_destino = "img/png/".$_FILES['imagen_icono']['name'][$i];        if ($ruta_tmp_archivo != ""){ 
            $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION)); 
       if (in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; } 
            if(filesize($_FILES['imagen_icono']['tmp_name'][$i]) > $tamaño_maximo ) {
 echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
              exit(); 
            }

       }
  
 if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) { //
 /*recibo los campos del formulario proveniente con el método POST*/ 
 $sql = "INSERT INTO iconos ( `icono`, `imagen_icono`) VALUES ( '".$_POST['icono']."', '".$_FILES['imagen_icono']['name'][$i]."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
<script>alert2("Registro exitoso");</script>
<meta http-equiv="refresh" content="2; url=iconos.php" />
<?php 
 }else{ 
 ?>
<script>alert2("Registro fallido",'error');</script>
<meta http-equiv="refresh" content="2; url=iconos.php" />
<?php 
}
 }
  } 
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="iconos.php" ENCTYPE="multipart/form-data">
<h1><?php echo $textoh1 ?></h1>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_iconos"type="hidden" id="id_iconos" value="';if (isset($row['id_iconos'])) echo $row['id_iconos'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="icono">icono:</label><input class="form-control"name="icono"type="text" id="icono" value="';if (isset($row['icono'])) echo $row['icono'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p><label for="imagen_icono">Imagen icono:</label><input class="form-control" name="imagen_icono[]" type="file" multiple="multiple" id="imagen_icono" value="';if (isset($row['imagen_icono'])) echo $row['imagen_icono'];echo '"';echo ' required ></p>';
echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin nuevo*/ 
#if ($_POST['submit']=="Modificar"){



if (isset($_POST['cod'])){

$sql = "SELECT `id_iconos`, `icono`, `imagen_icono` FROM `iconos` WHERE id_iconos ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="iconos.php" ENCTYPE="multipart/form-data">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_iconos']))  echo $row['id_iconos'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id_iconos"type="hidden" id="id_iconos" value="';if (isset($row['id_iconos'])) echo $row['id_iconos'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="icono">icono:</label><input class="form-control"name="icono"type="text" id="icono" value="';if (isset($row['icono'])) echo $row['icono'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p><label for="imagen_icono">Imagen icono:</label><input class="form-control" name="imagen_icono[]" type="file"  multiple="multiple" id="imagen_icono" value="';if (isset($row['imagen_icono'])) echo $row['imagen_icono'];echo '"';echo '  ></p>';

echo "</div>";
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 require_once 'funciones.php';
 $tamaño_maximo= tamaño_maximo(); 
  $formatos =formatos();

 if($_FILES['imagen_icono']['tmp_name'][0] == ""){
 $sql = 'UPDATE iconos SET icono="'.$_POST['icono'].'" ';
 $sql.=' WHERE  id_iconos = "'.$_POST['cod'].'";'; 
//  $actualizar = $mysqli->query($sql);
if($consulta =mysqli_query($mysqli,$sql)){ 
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=iconos.php" />';
 }else{ 
echo 'Modificacion fallida';
echo '<meta http-equiv="refresh" content="2; url=iconos.php" />';
 }
 #echo '<meta http-equiv="refresh" content="2; url=iconos.php" />';

 }
 
 
  $total = count($_FILES['imagen_icono']['name']);

if(!empty($_FILES['imagen_icono']['name'][0])){
  for($i=0; $i<=$total; $i++) {
 $nombre_archivo=$_FILES['imagen_icono']['name'][$i]; 
  $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION));
      $ruta_tmp_archivo = $_FILES['imagen_icono']['tmp_name'][$i];
 $ruta_destino = "img/png/".$_POST['icono'].'.'.$extensión_archivo; 
 
 if ($ruta_tmp_archivo != ""){ 
            $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION)); 
       if (in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; } 
            if(filesize($_FILES['imagen_icono']['tmp_name'][$i]) > $tamaño_maximo ) {
 echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
              exit(); 
            }

       } 
                  if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) { 
 $cod = $_POST['cod'];
$sql = "UPDATE iconos SET icono='".$_POST['icono']."' ";
if($ruta_tmp_archivo<>""){
$sql.=" , imagen_icono='".$_POST['icono'].'.'.$extensión_archivo."' ";
}
$sql.=" WHERE  id_iconos = '".$_POST['cod']."';"; 
#echo $sql;
if ($actualizar = $mysqli->query($sql)) {
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=iconos.php" />';
 }else{ 
echo 'Modificacion fallida';
echo '<meta http-equiv="refresh" content="2; url=iconos.php" />';
 }
}        
                   
                  }

}



  }
 /*recibo los campos del formulario proveniente con el método POST*/ 

 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar_iconos" onkeyup ="buscar_iconos(this.value);" onchange="buscar_iconos(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="1" id="numeroresultados_iconos" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_iconos',this.value) ;buscar_iconos(document.getElementById('buscar_iconos').value);" mousewheel="grabarcookie('numeroresultados_iconos',this.value);buscar_iconos(document.getElementById('buscar_iconos').value);" onchange="grabarcookie('numeroresultados_iconos',this.value);buscar_iconos(document.getElementById('buscar_iconos').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_iconos','ASC');buscar_iconos(document.getElementById('buscar_iconos').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_iconos','DESC');buscar_iconos(document.getElementById('buscar_iconos').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<?php
boton_modal_nuevo_icono();
ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="icono no creado, revise su información e intentete nuevo" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();buscar_iconos();" callback_0="false" callback="buscar_iconos();"');
?>
<img width="50px" id="icono_seleccionado_img">
<input hidden id="icono_seleccionado">
<span id="txtresultadosicono">
<?php 
buscar_iconos();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_iconos = document.getElementById('menu_iconos')
if (vmenu_iconos){
vmenu_iconos.className ='active '+vmenu_iconos.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
 ?>
