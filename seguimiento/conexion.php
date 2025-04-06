<?php 

$servidorbd = '127.0.0.1:7000';

$usuariobd = 'root';

$passwordbd = '';

$basededatos = 'guagua';

$mysqli = new mysqli ('127.0.0.1:7000','root','','guagua');

if (mysqli_connect_errno()){

echo "error".mysqli_connect_errno();}else{

if($mysqli){

mysqli_set_charset($mysqli,'utf8');

#print_r($mysqli);

}

}

 ini_set('date.timezone', 'America/Bogota');

 date_default_timezone_set('America/Bogota');

 ?>

