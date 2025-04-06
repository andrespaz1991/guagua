<?php
error_reporting(E_ALL);
$rutaPrincipal = $_SERVER['DOCUMENT_ROOT'].'/guagua/comun/';
require_once ($rutaPrincipal."/funciones.php");
require_once ($rutaPrincipal."/config.php");
$mysqli = new mysqli (SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);

#$mysqli= new mysqli('localhost','eshos_7650678','proinfox','eshos_7650678_evaluaciondocente');
if (mysqli_connect_errno()){
	echo 'error';
}
if (isset($mysqli)) {
	mysqli_set_charset($mysqli,"utf8");
	
}

?>
