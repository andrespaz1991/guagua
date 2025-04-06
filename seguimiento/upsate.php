<?php
require 'conexion.php';
$sql = 'SELECT * FROM `seguimiento` ';
$consulta = $mysqli -> query ($sql);
while ($row = $consulta -> fetch_assoc()){
while ($row['id_seguimiento']>353 and $row['id_seguimiento']<500) {
$resultado = $row['id_seguimiento'] - 2 ;
echo $sq =' update seguimiento set id_seguimiento = "'.$resultado.'" where id_seguimiento ="'.$row['id_seguimiento'].'";';
								}
	$actualiza =$mysqli -> query ($sq);


											}
?>