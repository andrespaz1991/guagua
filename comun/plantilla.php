<?php
require(dirname(__FILE__)."/conexion.php");
$row_ie = guagua_institution_context($mysqli, (int)($_SESSION['id_institucion'] ?? 0));
if (!defined('NOMBRE_INSTITUCION')) define ("NOMBRE_INSTITUCION",$row_ie['nombre_institucion']);
if (!defined('LOGO_INSTITUCION')) define ("LOGO_INSTITUCION",$row_ie['logo_institucion']);
if (!defined('BANNER_INSTITUCION')) define ("BANNER_INSTITUCION",$row_ie['BANNER_INSTITUCION']);
if(isset($_SESSION['rol'])){
$row_cookies = $row_ie;
if($row_cookies){
setcookie('tipos',$row_cookies['formatos_no_permitidos']);
setcookie('tamaño',$row_cookies['tamano_maximo_adjunto']);
   }
}

$plantilla = guagua_plantilla_institucion($row_ie);
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
