<?php
ob_start();
require_once("../comun/funciones.php"); ?>
<span id="txt_bandeja_salida">
<?php bandeja_salida();?>
</span>
<?php $contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>