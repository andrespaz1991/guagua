<?php
@session_start();
if (!isset($_SESSION['usuario'])){
    header ("Location: ../index.php");
}
ob_start();
if (isset($_POST['enc'])){
    $_GET['enc'] = $_POST['enc'];
}
require("../comun/conexion.php");
require_once("../comun/config.php");
require_once("../comun/funciones.php");
if (isset($_GET['enc'],$_GET['id_estudiante'])){
revisar_cuestionario($_GET['enc'],$_GET['id_estudiante']);
}

$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>