<?php
ob_start();
@session_start();
$_SESSION['modulo']="red";
require_once("../comun/config.php");
require_once("funciones.php");
if(isset($_FILES['archivo'])){
#print_r($_FILES);
$destino = SGA_SERVER."/red/ej/".$_FILES['archivo']["name"];
$origen = $_FILES['archivo']["tmp_name"];
copy($origen,$destino);
  if(file_exists($destino)){
     #return true;
     echo "Archivo subido exitosamente";
  }else{
    #return false;
    echo "Error al Subir $origen,$destino";
  } 
}
$nombresinext = str_replace(".zip","",$_FILES['archivo']["name"]);
@mkdir (SGA_SERVER."/red/ej/".$nombresinext);
##descomprimir y eliminar
?>
<form method="post" ENCTYPE="multipart/form-data">
    Archivo: <input type="file" name="archivo"/>
    <br>
    <button class="btn btn-primary" type="submit" >Subir</button>
</form>
<?php
$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>