<?php
ob_start();
@session_start();
#header("Content-type: application/vnd.ms-excel");
#header("Content-Disposition: attachment; Filename=control.xls");
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
require '../comun/autoload.php';
#$_SESSION['id_institucion']=1;
#$_SESSION['id_usuario']=1085290375;
$control_ingreso =new control_ingreso;

$fecha = date('Y-m-d');
$mes = Fecha::mes($fecha);
$mesletras=Fecha::mes_letras($mes);
$entradas= ($control_ingreso->consultar_control($mes,date('Y')));
$institucion=new Institucion($_SESSION['id_institucion']);
$docente=new Persona($_SESSION['id_usuario']);
?>

<table border="2">

	<tr>

		<th colspan="6">Control de Horas de <?php echo $docente->nombre.' '.$docente->apellido ?> para el mes <?php echo $mes ?> (<?php echo $institucion->nombre_institucion; ?>)</th>

	</tr>

	<tr>

	<th>Grupo</th>

	<th>día</th>

	<th>Fecha</th>

	<th>Hora Entrada</th>

	<th>Hora Salida</th>

	<th>Número de Horas</th>

	</tr>

<?php 

$total_horas=0;
foreach($entradas as $num_entradas => $entrada) { 
$materia=new Curso($entrada['grupo']);
$materia->id_materia=$entrada['grupo'];
?>

	<tr>

	<td><?php echo $materia->nombre_materia?></td>	

<td><?php echo Fecha::saber_dia_letras($entrada['fecha_ingreso']); ?></td>

<td><?php 

echo Fecha::formato_fecha($entrada['fecha_ingreso']) ?></td>

	<td><?php echo Fecha::formato_hora( $entrada['hora_ingreso']) ?></td>

	<td><?php echo Fecha::formato_hora( $entrada['hora_salida']) ?></td>

	<td><?php

	echo $entrada['hora_salida'];
	#-$entrada['hora_ingreso'];

$total_horas+=$entrada['hora_salida']-$entrada['hora_ingreso'];

	  ?></td>	

	</tr>

<?php } ?>

<tr><td align="right" colspan="6">Total Horas: <?php echo $total_horas ?></td></tr>

<tr><td align="right"  colspan="6">Total a pagar: <?php echo Comun::formato_moneda($total_horas*8000) ?></td></tr>



</table>

<?php $contenido = ob_get_contents();
ob_clean();
include ($_SERVER['DOCUMENT_ROOT']."/comun/plantilla.php");
?>

