<?php
@session_start();
require("../comun/autoload.php");



$contenido = ob_get_clean();
require '../comun/plantilla.php'; 
?>
