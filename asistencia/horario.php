<?php
ob_start();

require("../comun/autoload.php");
require_once '../clases/Academico.Class.php';
require_once '../clases/Curso.Class.php';
$academico=new academico();
#exit();




$_GET['asignacion']=str_replace('"', "", $_GET['asignacion']);
$horarios= ($academico->consultar_horario_simple($_GET['asignacion']));
if (!empty($horarios) and !isset($_GET['modificar'])) {
$nombremateria=$horarios[0]['nombre_materia'];
echo $nombremateria.' <br>';

?>

 <br>
 <br>
 

    <table align="center" border="4" bordercolor="purple"

    cellpadding="10" cellspacing="20">

    <tr><th colspan="8" ><?php Echo $horarios[0]['nombre_materia']." Inicio: ".Fecha::formato_fecha($horarios[0]['fecha_inicio']).' hasta : '.Fecha::formato_fecha($horarios[0]['fecha_fin']); ?></th>

      <th><a href="horario.php?modificar&asignacion=<?php echo $_GET['asignacion']; ?>">

<button class="btn btn-primary"   id="opciones_cursos" type="button"  > Modificar </span>

  </button><a></th>

    </tr>

<tr>

        <th bgcolor="yellow">Hora Inicio</th>

        <th bgcolor="yellow">Hora Fin</th>

        <th>Lunes</th>

        <th>Martes</th>

        <th>Mi&eacute;rcoles</th>

		<th>Jueves</th>

		<th>Viernes</th>

		<th>Sabado</th>

		<th>Domingo</th>

</tr>

	<?php
$diassemana=array();
foreach ($horarios as $key => $horario) {?>
<tr>
<?php
echo '<td>'.Fecha::formato_hora($horario["hora_inicio"]).'</td>';
echo '<td>'.Fecha::formato_hora($horario["hora_fin"]).'</td>';
$nombre_asignatura=($academico->consultar_materia($_GET['asignacion']));
$nombre_asignatura=($horarios[0]['nombre_materia']);
?>
<td><?php if(strtoupper($horario["dia"])=="LUNES") echo $nombre_asignatura  ?></td>
<td><?php if(strtoupper($horario["dia"])=="MARTES") echo $nombre_asignatura  ?></td>
<td><?php if(strtoupper($horario["dia"])=="MIERCOLES") echo $nombre_asignatura; ?></td>
<td><?php if(strtoupper($horario["dia"])=="JUEVES") echo $nombre_asignatura;  ?></td>
<td><?php if(strtoupper($horario["dia"])=="VIERNES") echo $nombre_asignatura;  ?></td>
<td><?php if(strtoupper($horario["dia"])=="SABADO") echo $nombre_asignatura;  ?></td>
<td><?php if(strtoupper($horario["dia"])=="DOMINGO") echo $nombre_asignatura;  ?></td>
</tr>
<?php $diassemana[]=$horario["dia"]; ?>
<?PHP } 
######
?>
<br>
<table align="center">
<tr>
<th>id</th>
<th>Día</th>
<th>fecha</th>
<th>horas</th>
</tr>
<?php
$id=1;
$horas1=0;
$tminutos=0;
$i=0;
$inicio=DateTime::createFromFormat('Y-m-d',$horarios[0]['fecha_inicio'], new DateTimeZone('America/Bogota'));
while($inicio->format("Y-m-d")<=$horarios[0]['fecha_fin']){
$dias=array("domingo","lunes","martes","miercoles","jueves","viernes","sabado");
$inicio=DateTime::createFromFormat('Y-m-d',$horarios[0]['fecha_inicio'], new DateTimeZone('America/Bogota'));
$inicio->modify('+'.$i++.' day');
if(in_array(mb_strtolower($dias[$inicio->format("w")], 'UTF-8'),$diassemana)){
?>
<tr <?php if($inicio->format("Y-m-d")<=date('Y-m-d'))  echo 'style="background-color:#A3E4D7"'; ?> >
<td><?php echo $id++; ?></td>
<td><?php echo $dias[$inicio->format("w")] ?></td>
<td><?php echo Fecha::formato_fecha($inicio->format("Y-m-d")).'('.$inicio->format("d-m-Y").')' ?></td>
<td><?php 
foreach($horarios as $clave => $valor){
  if($dias[$inicio->format("w")]==$valor['dia']){
   # echo $valor['hora_inicio'].' '.$valor['hora_fin'].'<br>';
    $cantidad_horas=Fecha::restar_horas($valor["hora_fin"],$valor["hora_inicio"]);
    list($horas2,$minutos1,$segundos1)= Fecha::RestarHoras($valor["hora_fin"],$valor['hora_inicio']); 
  #echo 'minutos'.$minutos1.' '.$horas1;
     $horas1=$horas1+$horas2;
     $tminutos=$tminutos+$minutos1;
     #echo $horas1+($tminutos/60);
     echo ($tminutos/60)+$horas1;
  }
    
}
#echo $horario["hora_fin"].' '.$horario["hora_inicio"];
 ?></td>
</tr>
<?php
}
}

?>
<!-- -->
</table>
<table align="center">
<tr><th colspan="4">Horas de asistencia</th></tr>
<tr>
<th>id</th>
<th>Fecha</th>
<th>Horas</th>
<th>Asistentes</th>
</tr>
<?php
$control_ingreso=new Control_ingreso();
$datos= $control_ingreso->control_materia($_GET['asignacion']);
$contaminutos=0;
$id=1;
$thoras=0;
foreach($datos as $clave => $asistencia){
  $estado =$control_ingreso->verificar_asistencia($asistencia['fecha_ingreso'],$_GET['asignacion'],'no')
?>
<tr>
<td><?php echo $id++ ?></td>
<td><?php echo Fecha::formato_fecha($asistencia['fecha_ingreso']) ?></td>
<td><?php list($horas,$minutos,$segundos)= Fecha::RestarHoras($asistencia['hora_ingreso'],$asistencia['hora_salida']); 
echo $horas;
if($estado['cantidad']>0){
   $thoras=$thoras+$horas;  
}


if ($minutos>=30 or $contaminutos>=30) {
if($minutos>0){
echo ' y '.$minutos.' minutos';
}
if($estado['cantidad']>0){
$contaminutos=$contaminutos+$minutos;  
}
}
?></td>
<td><?php echo $estado['cantidad']; ?></td>

</tr>
<?php } ?>
<tr>
<td></td>
<td>Total</td>
<td><?php echo $thoras + ($contaminutos/60); ?></td>
<td></td>
</tr>
</table>
</table>










<?php } else {

if(isset($_GET['modificar'])){

$academico=new academico();



$horarios= ($academico->consultar_horario_simple($_GET['asignacion']));  

  $fecha_inicio= $horarios[0]['fecha_inicio'];

$fecha_fin= $horarios[0]['fecha_fin'];



foreach ($horarios as $key => $value) {

  $nombremateria=$horarios[0]['nombre_materia'];

    ?>

<script type="text/javascript">

     $(document).ready(function () {

    document.getElementById('<?php echo $value['dia']; ?>').click();

document.getElementById('hora_inicio<?php echo $value['dia']; ?>').value="<?php echo $value['hora_inicio']; ?>";

document.getElementById('hora_fin<?php echo $value['dia']; ?>').value="<?php echo $value['hora_fin']; ?>";

          });

</script>

<?php

} 

}

    ?>

    <form action="" method="post">

<?php

if(isset($_GET['modificar'])){

echo "<input type='hidden' name='modificar'></input>";

}





 ?>

 <br>

<h1><?php #echo $nombremateria; ?></h1>



<label>Fecha inicio:<input type="date" name="fecha_inicio" value="<?php

if(isset($_GET['modificar'])){

echo $fecha_inicio;

    }else{

 echo date('Y-m-d');       

    }

  ?>"></label>

<label>Fecha fin:<input type="date" name="fecha_fin" value="<?php

if(isset($_GET['modificar'])){ echo $fecha_fin; }

    else{ echo date('Y-m-d'); } ?>"></label>

<br>

<input type="hidden" name="asignacion" value="<?php echo $_GET['asignacion'] ?>">

<label><input id="lunes" onclick="adicionar_dias(this.value);" name="horario[]" type="checkbox" value="lunes"> lunes</label>

<label><input  onclick="adicionar_dias(this.value);" id="martes" name="horario[]" type="checkbox" value="martes"> martes</label>

<label><input  onclick="adicionar_dias(this.value);" id="miercoles" name="horario[]" type="checkbox" value="miercoles"> miercoles</label>

<label><input  onclick="adicionar_dias(this.value);" id="jueves" name="horario[]" type="checkbox" value="jueves"> jueves</label>

<label><input  onclick="adicionar_dias(this.value);" id="viernes" name="horario[]" type="checkbox" value="viernes"> viernes</label>

<label><input  onclick="adicionar_dias(this.value);" id="sabado" name="horario[]" type="checkbox" value="sabado"> sabado</label>

<label><input  onclick="adicionar_dias(this.value);" id="domingo" name="horario[]" type="checkbox" value="domingo"> domingo</label><br>

<div id="horario">

</div>

<br><label><input required name="seguro" type="checkbox" value="seguro"> seguro</label><br>

<input class="btn btn-success" type="submit" name="" value="Guardar">

</form>

<?php

}





if (!empty($_POST['horario']) ) {





$academico=new academico;

if (isset($_POST['modificar'])) {

    $academico->eliminar_horario();

}



foreach ($_POST['horario'] as $key => $value) {

	$academico->insertar_horario($value,$key);

}

?>

<script>

alert2('registro exitoso');

window.location='horario.php?asignacion="<?php echo $_POST['asignacion']?>"'; 

</script>

<?php 

}



$contenido = ob_get_clean();

require ("../comun/plantilla.php");

?>