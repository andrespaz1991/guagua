<?php 
ob_start();
@session_start();
if(isset($_SESSION['id_usuario'])){
$cod = $_SESSION['id_usuario'];
}else{
echo "<script>alert2('Ingreso incorrecto','error');</script>";
$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
exit();
}
require ("../comun/conexion.php");
require_once ("../comun/funciones.php");
echo '<center>';
if (isset($_GET['cambiar_rol'])){
$sql = "SELECT * FROM `usuario` WHERE id_usuario ='".$_SESSION['id_usuario']."' Limit 1"; 
$consulta = $mysqli->query($sql);
$row=$consulta->fetch_assoc();
$misroles = explode(",",$row['rol']);
if(in_array($_GET['cambiar_rol'],$misroles))
$_SESSION['rol']=$_GET['cambiar_rol'];
if($_SESSION['rol']<>"acudiente") { unset($_SESSION['hijo']); }
if (isset($_GET['redirect'])) 
if($_SESSION['rol']=="acudiente"){ $redirect="../usuario/elegir_hijo.php" ; } else { $redirect = $_GET['redirect']; }
else
$redirect = "perfil.php";
header("Location: $redirect");
}
if (isset($_GET['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM usuario WHERE id_usuario="'.$_SESSION['id_usuario'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=login.php?logout" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=perfil.php" />
<?php 
}
}
if (isset($_POST['submit'])){
if ($_POST['submit']=="Actualizar"){

$sqlfoto = "";
$sqlclave= "";
 
if ($_FILES['foto']['error']!=4){
$partes_nombre = explode (".",$_FILES['foto']['name']);
$ext = end($partes_nombre);
$sqlfoto= ", foto='".$_POST['id_usuario'].".".$ext."'";
}
/*Cambio de clave*/
if (isset($_POST['cambiar_clave']) and $_POST['cambiar_clave']=="SI"){
if ($_POST['clave']!="")
$sqlclave= ", clave='".encriptar($_POST['clave'])."'";
}
/*Cambio de clave*/
$array_usuario['id_usuario'] = $mysqli->real_escape_string($_POST['id_usuario']);
$array_usuario['nombre'] = $mysqli->real_escape_string($_POST['nombre']);
$array_usuario['apellido'] = $mysqli->real_escape_string($_POST['apellido']);
$array_usuario['usuario'] = $mysqli->real_escape_string($_POST['usuario']);
$array_usuario['mascota'] = $mysqli->real_escape_string($_POST['mascota']);
$array_usuario['tipo_sangre'] = $mysqli->real_escape_string($_POST['tipo_sangre']);
$array_usuario['direccion'] = $mysqli->real_escape_string($_POST['direccion']);
$array_usuario['telefono'] = $mysqli->real_escape_string($_POST['telefono']);
$array_usuario['correo'] = $mysqli->real_escape_string($_POST['correo']);
$array_usuario['genero'] = $mysqli->real_escape_string($_POST['genero']);

$sql = "UPDATE usuario SET genero='".$array_usuario['genero']."', id_usuario ='".$array_usuario['id_usuario']."', nombre ='".$array_usuario['nombre']."',  apellido ='".$array_usuario['apellido']."',  usuario='".$array_usuario['usuario']."', mascota='".$array_usuario['mascota']."', tipo_sangre='".$array_usuario['tipo_sangre']."',  direccion='".$array_usuario['direccion']."',  telefono='".$array_usuario['telefono']."',  correo='".$array_usuario['correo']."' ".$sqlclave." ".$sqlfoto."  WHERE id_usuario = '".$cod."';";
/*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)){

?>
	<script>
alert2("Actualización exitosa");
 setTimeout(function(){
        window.location="../index.php"; 
      },3000);
</script>	
<?php
//actualizar foto
$sql_id = "SELECT foto FROM usuario WHERE id_usuario='".$cod."';";
$user_id = $mysqli->query($sql_id);
if($row_id=$user_id->fetch_assoc())
if (isset($_FILES['foto']) and $_FILES['foto']['error']!=4){
$foto_anterior = $row_id['foto'];
@unlink(READFILE_SERVER."foto/".$foto_anterior);
move_uploaded_file($_FILES['foto']['tmp_name'],READFILE_SERVER."foto/".$_POST['id_usuario'].".".$ext);
if($_POST['id_usuario']==$cod)
$_SESSION['foto'] = $_POST['id_usuario'].".".$ext;
}
elseif($row_id['foto']=="user-icon.png" and $row_id['genero']=="f"){
$sql="UPDATE `usuario` SET `foto`='user-iconf.png' WHERE `id_usuario`='".$_POST['id_usuario']."' ";
$mysqli->query($sql_id);
}
if($row_id['foto']=="user-iconf.png" and $row_id['genero']=="m"){
$sql="UPDATE `usuario` SET `foto`='user-icon.png' WHERE `id_usuario`='".$_POST['id_usuario']."' ";
$mysqli->query($sql_id);
}

if($_POST['id_usuario']==$cod){
   $_SESSION['id_usuario'] = $_POST['id_usuario'];
   $_SESSION['nombre'] = $_POST['nombre'];
   $_SESSION['apellido'] = $_POST['apellido'];
   $_SESSION['usuario'] = $_POST['usuario'];
}
#echo $sql;
#exit();
 /*Validamos si el registro fue ingresado con éxito*/
echo "<script>alert2('Modificación exitosa')</script>";
#echo '<meta http-equiv="refresh" content="1; url=perfil.php" />';
}else{
echo "<script>alert2('Modificacion fallida','error')</script>";
}
#echo '<meta http-equiv="refresh" content="2; url=perfil.php" />';
} /*fin Actualizar*/ 
}/*fin if isset cod*/
@session_start();
if (isset($_GET['usuario'])){
$_POST['perfil_usuario']=$_GET['usuario'];
//validar si tiene permisos
}else{
$_POST['perfil_usuario']=$cod; 
}
?>
<center>
<h1><?php echo consultar_nombre($cod); ?></h1>
</center>
<?php
$_POST['submit']='perfil';
formulario_usuario($_POST);
?>
<?php 

echo '</center>';
 ?>
<script>
required_en_formulario('form_usuario',"red","*");
password_en_formulario('form_usuario');
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
