<?php
header("Location: mis_mensajes.php"); 
ob_start();
require("../comun/funciones.php");
?>

<?php
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>