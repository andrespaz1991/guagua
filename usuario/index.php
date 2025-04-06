<?php 
ob_start();
@session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
if(!isset($_SESSION['usuario'])){
header("Location: login.php");
exit();
}

?>
<?php
exit();
@session_start();
$_SESSION['modulo']="usuario";
require("../comun/conexion.php");
#require_once("../comun/config.php");
if(isset($_SESSION['usuario']) and $_SESSION['rol'] == "admin"){
?>
<a class="btn btn-primary" href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=admin">Administradores</a><br>
<a class="btn btn-primary" href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=docente">Docentes</a><br>
<a class="btn btn-primary" href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=estudiante">Estudiantes</a><br>
<a class="btn btn-primary" href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=acudiente">Acudientes</a><br>
<a class="btn btn-primary" href="<?php echo SGA_URL; ?>/usuario/usuario.php?u=invitado">Invitados</a><br>
<?php
}else{
    ?> Usuario no autorizado <?php
}
$contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>
