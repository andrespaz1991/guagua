<?php
require(dirname(__FILE__)."/conexion.php");
$sql_ie = "SELECT * FROM `institucion_educativa` WHERE id_institucion_educativa ='1'"; 
$consulta_ie = $mysqli->query($sql_ie);
$row_ie=$consulta_ie->fetch_assoc();
if (!defined('NOMBRE_INSTITUCION')) define ("NOMBRE_INSTITUCION",$row_ie['nombre_institucion']);
if (!defined('LOGO_INSTITUCION')) define ("LOGO_INSTITUCION",$row_ie['logo_institucion']);
if (!defined('BANNER_INSTITUCION')) define ("BANNER_INSTITUCION",$row_ie['BANNER_INSTITUCION']);
if(isset($_SESSION['rol'])){
$sql_cookies ='select * from institucion_educativa WHERE id_institucion_educativa ="1"';
    $consulta_cookies = $mysqli ->query($sql_cookies);
if($row_cookies=$consulta_cookies->fetch_assoc()){
setcookie('tipos',$row_cookies['formatos_no_permitidos']);
setcookie('tamaño',$row_cookies['tamano_maximo_adjunto']);
   }
}

$plantilla = dirname(__FILE__)."/plantillas/plantilla_kids_m.php";
require_once (dirname(__FILE__)."/config.php");
include($plantilla);
#include(dirname(__FILE__)."/manifiesto_cookie.php");
?>
<script>
function cambioplantilla(e) {
tecla=(document.all) ? e.keyCode : e.which; 
if (tecla==80 && e.altKey) //80 = letra p 
   document.location.href="<?php echo SGA_URL; ?>/index.php?cambiar_plantilla";
}
</script>