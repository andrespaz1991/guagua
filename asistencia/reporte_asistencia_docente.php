<?php 

ob_start();

if (!isset($_POST['descargar'])) {
	$mes = date('m');
?>

<form action="" method="post">

<select name="mes" class="hidden-print">

	<option <?php if($mes==1) echo "selected" ?> value="1">Enero</option>

	<option <?php if($mes==2) echo "selected" ?> value="2">Febrero</option>

	<option <?php if($mes==3) echo "selected" ?> value="3">Marzo</option>

	<option <?php if($mes==4) echo "selected" ?> value="4">Abril</option>

	<option <?php if($mes==5) echo "selected" ?> value="5">Mayo</option>

	<option <?php if($mes==6) echo "selected" ?> value="6">Junio</option>

	<option <?php if($mes==7) echo "selected" ?> value="7">Julio</option>

	<option <?php if($mes==8) echo "selected" ?> value="8">Agosto</option>

	<option <?php if($mes==9) echo "selected" ?> value="9">Septiembre</option>

	<option <?php if($mes==10) echo "selected" ?> value="10">octubre</option>

	<option <?php if($mes==11) echo "selected" ?> value="11">Noviembre</option>

	<option <?php if($mes==12) echo "selected" ?> value="12">Diciembre</option>

</select>

<select name="year" class="hidden-print">
<option value="2021">2021</option>
	<option value="2020">2020</option>

	<option value="2019">2019</option>

</select>

<input class="btn-success hidden-print" type="submit" name="consultar" value="consultar">

<br><br>

</form>

<?php

}

require("../comun/autoload.php");

if (isset($_POST['descargar'])) {

	echo '<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />';

header('Content-type: application/vnd.ms-excel;charset=utf-8');

header('Content-Disposition: attachment; filename=reporte.xls');

}



if (isset($_POST['enviar'])) {

/*

$mail= new GuaguaMailer();

$mail->para="andres.paz1991@gmail.com";

$mail->asunto="Prueba de mail";

$mail->ruta="pdf/word.pdf";

$mail->archivo="word.pdf";

$mail->tipo="application/pdf";

$mail->enviar_mail();

*/

#header("Content-Disposition: ;form-data=reporte.xls");

	$scarpeta=getcwd()."/pdf/"; 

	$sfile=$scarpeta."pagina.xls"; 

	$fp=fopen($sfile,"w" );

	fwrite($fp,$shtml);

	fclose($fp);

}



$control_ingreso =new control_ingreso;

$institucion=new Institucion($_SESSION['id_institucion']);

if(!isset($_POST['mes'])){

	$_POST['mes']=date('m')-1;

	

}

if(!isset($_POST['year'])){
	$_POST['year']=date('Y');
}

echo "Año".$_POST['year'];

$datos= $control_ingreso->consultar_control($_POST['mes'],$_POST['year']);

if (!isset($_POST['descargar'])) {

?>

<form action="" method="post">

<input class="btn-warning hidden-print" type="submit" name="enviar" value="Enviar">

<input  class="btn-primary hidden-print" type="submit" name="descargar" value="Descargar">

<input class="hidden-print" type="submit" onclick="window.print();" name="imprimir" value="Imprimir">

</form>

<?php } ?>



<table border="2">

	<tr><th colspan="5">Reporte de horas <?php echo Fecha::mes_letras($_POST['mes'])  ?> <?php echo $institucion->nombre_institucion?></th>	

	<?php if (!isset($_POST['descargar'])) { ?>	

<th> <form target="_blank" action="control_ingreso.php">

	<input type="submit" class="hidden-print" name="nuevo" value="Administrar">

</form></th>

<?php } ?>

	</tr>

	<tr>

	<th>Grupo</th>

	<th>Fecha</th>

	<th>Hora Entrada</th>

	<th>Hora salida</th>

	<th>Número de horas</th>

	<th class="hidden-print">Modificar</th>



	</tr>

<?php 	

$contador=0;

$contadortd=0;

$contaminutos=0;

$valorminutos=0;

foreach ($datos as $key => $asistencia) { ?>

	<tr>

	<td><?php 
$materia = new Curso($asistencia['grupo']);
#print_r($materia);
echo $materia->nombre_materia; ?></td>
	<td><?php echo Fecha::formato_fecha($asistencia['fecha_ingreso']); ?></td>

	<td><?php echo Fecha::formato_hora($asistencia['hora_ingreso']); ?></td>

	<td><?php echo Fecha::formato_hora($asistencia['hora_salida']); ?></td>

	<td><?php list($horas,$minutos,$segundos)= Fecha::RestarHoras($asistencia['hora_ingreso'],$asistencia['hora_salida']); 

	echo $horas;

if ($minutos>=30 or $contaminutos>=30) {

if($minutos>0){

	echo ' y '.$minutos.' minutos';

}

	$contaminutos=$contaminutos+$minutos;

}

	?></td> 

	<td class="hidden-print">

		<form class="btn-primary hidden-print" id="formModificar" name="formModificar" method="post" action="control_ingreso.php">

<input name="cod" type="hidden" id="cod" value="<?php echo $asistencia['id'] ?>">

<input type="submit" name="submit" id="submit" value="Modificar">

</form>

</td>







	</tr>

<?php

#$materias_td=array(36,27,37,39,60);
$materias_td=array();
if(in_array($asistencia['grupo'],$materias_td)){

$contadortd=$contadortd+$horas;

}else{

 $contador=$contador+$horas; 

}



} 

if($contaminutos>=30){

	$contaminutos=$contaminutos/60;

}

$contadortd=$contadortd+$contaminutos;



?>

<tr>

		<td align="center" colspan="6" align="rigth">

Total:<?php 
$t=($contador*8000)+(($contadortd*15000));

$cantidad_horas=$contador+$contadortd;

echo "(".$cantidad_horas.") ".Comun::formato_moneda($t); ?>

		</td>

</tr>

</table>

<?php

if (!isset($_POST['descargar'])) {

$contenido = ob_get_contents();

ob_clean();



include "../comun/plantilla.php";

}

?>

