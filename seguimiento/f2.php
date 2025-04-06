<?php
header('Content-Type: text/html; charset=ISO-8859-1');   

if (isset($_POST['proyecto'])) {

echo $sql ='update seguimiento set proyecto ="'.$_POST['proyecto'].'",cita="'.$_POST['cita'].'",observaciones="'.$_POST['observaciones'].'",asistio ="'.$_POST['asistio'].'",listo_para_enviar="'.$_POST['listo_para_enviar'].'",asesoria_tecnica="'.$_POST['asesoria_tecnica'].'",hora="'.$_POST['hora'].'" where id_seguimiento ="'.$_POST['id_seguimiento'].'"';
			require 'conexion.php';
				$consulta = $mysqli -> query ($sql);	
				header('location:consultar.php');
								}
?>