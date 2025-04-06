<?php 
error_reporting(E_ALL);
$rutaPrincipal = $_SERVER['DOCUMENT_ROOT'].'/guagua/comun/';
require_once ($rutaPrincipal."/funciones.php");
require_once ($rutaPrincipal."/config.php");
$mysqli = new mysqli (SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);
if (mysqli_connect_errno()){
echo "error".mysqli_connect_errno();}else{
if($mysqli){
mysqli_set_charset($mysqli,'utf8');
}
}
 ini_set('date.timezone', 'America/Bogota');
 date_default_timezone_set('America/Bogota');
 ?>