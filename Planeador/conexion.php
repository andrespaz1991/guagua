<?php 
$servidorbd = "localhost:7000";
$usuariobd = "root";
$clavebd = "";
$basededatos = "guagua";

#$servidorbd = "sql101.infinityfree.com";
#$usuariobd = "epiz_33170354";
#$clavebd = "7KB2Kajq4e";
#$basededatos = "epiz_33170354_guagua";
$mysqli = new mysqli ($servidorbd,$usuariobd,$clavebd,$basededatos);
if (mysqli_connect_errno()){
echo "error".mysqli_connect_errno();}else{
if($mysqli){
mysqli_set_charset($mysqli,'utf8');
}
}
 ini_set('date.timezone', 'America/Bogota');
 date_default_timezone_set('America/Bogota');
 ?>