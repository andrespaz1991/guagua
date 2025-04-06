<?php
$mysqli = new mysqli ('sql101.infinityfree.com','epiz_33170354','7KB2Kajq4e','epiz_33170354_guagua');
#$mysqli= new mysqli('localhost','eshos_7650678','proinfox','eshos_7650678_evaluaciondocente');
if (mysqli_connect_errno()){
	echo 'error';
}
if (isset($mysqli)) {
	mysqli_set_charset($mysqli,"utf8");
	
}

?>
