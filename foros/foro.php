<?php
ob_start();
@session_start();
$_SESSION['barra_busqueda'] = "foros";
require_once("funciones.php");
gruposforos();
?>
<?php
$conenido = ob_get_clean();
require("../comun/plantilla.php")
?>