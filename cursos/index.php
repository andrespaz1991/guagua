<?php
ob_start();
@session_start();
require_once ("../comun/config.php");
require_once ("../comun/funciones.php");
if(!isset($_SESSION['id_usuario'])){
header("Location: ../usuario/login.php");
exit();
}
?>
<link rel="stylesheet" href="<?php echo SGA_COMUN_URL.'/css/estilossga.css' ?>" type="text/css" />
<?php
ob_start();
@session_start();
redireccionandoencursos(); ?>
