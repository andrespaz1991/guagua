<?php
ob_start();
@session_start();
$_SESSION['modulo']="foros";
$_SESSION['grupo_foro']="4";
$_SESSION['cat_curso_foro']="1";
require_once("../foros/funciones.php");
if(isset($_SESSION['cat_curso_foro']) and $_SESSION['cat_curso_foro']!="")
echo foro($_SESSION['grupo_foro'],$_SESSION['cat_curso_foro']);
$contenido = ob_get_clean();
require("../comun/plantilla.php");
?>