<?php 
ob_start();
require_once 'mysql.class.php'; 
require_once'materias.class.php'; 
require_once 'planeacion.class.php'; 
$mat=new materia();
$mismaterias=( json_decode($mat->consultar_materias()));
$planeacion=new Planeacion("");
$datos=json_decode($planeacion->consultar_planeacion());
?>
	<form action="" method="POST">
		      <label>Materia</label>
        <select name="materia">
<?php        foreach($mismaterias as $campo => $valor){ ?>
        <option
<?php 
if (isset($_POST['materia']) and $_POST['materia']==$valor[0] ) {
echo "selected"; }
?>
         value="<?php echo $valor[0]; ?>"><?php echo $valor[1]; ?></option>
<?php } ?>
         </select>
<?php

$grados=array();
for ($i=1; $i <5 ; $i++) { 
 $grados[].="tecnico".$i; 
}
for ($i=1; $i <12 ; $i++) { 
	$grados[].=$i;
}
 ?>

<label>Grado</label>
  <select name="grado">
<?php 

for ($i=0; $i <count($grados) ; $i++) { 
?>
<option
<?php 
if (isset($_POST['grado']) and $_POST['grado']==$grados[$i] ) {
echo "selected"; }
?> value="<?php echo $grados[$i]; ?>"><?php echo $grados[$i]; ?></option> <?php
}
?>
         </select>
<input type="submit" class="btn btn-success" name="guardar" value="Consultar">
		
	</form>
</body>
</html>
<?php 
if(!empty($_POST)){ 
$todas=$planeacion->mostrar_todas_planeaciones($_POST['materia'],$_POST['grado']);
	?>
	<div class="table-responsive">

<table border="2"  class="table">
	<tr>
		<th>Contenido del plan</th>
		<th>Objetivos del plan</th>
		<th>Estrategia</th>
		<th>Actividad</th>
		<th>Recurso</th>
<?php 	$planeacion=new Planeacion();  ?>
<th>Intensidad( <?php echo $planeacion->intensidad_horaria($_POST['materia']); ?>) </th>
		<th>Editar</th>
		<th>Descargar</th>
	</tr>

	<tr>
<meta charset="utf-8">
<?php
$contador=0;
 foreach ($todas as $planes => $plan) { 
	$contador=$contador+$planeacion->tiempo_plan;
	$planeacion=new Planeacion($plan[0]); ?>
	<tr>
		<td><?php echo  $planeacion->contenido_plan ; ?></td>
		<td><?php echo  $planeacion->objetivos_plan ; ?></td>
		<td><?php echo  $planeacion->estrategiaa ; ?></td>
		<td><?php echo  $planeacion->Actividada ; ?></td>
		<td><?php echo  $planeacion->Recursoa ; ?></td>
		<td><?php echo  $planeacion->tiempo_plan ; ?></td>
		<!--td><?php echo  $planeacion->estrategiab ; ?></td>
		<td><?php echo  $planeacion->Actividadb ; ?></td>
		<td><?php echo  $planeacion->Recursob ; ?></td-->
		<td><a class="btn btn-success" href="index.php?id=<?php echo $planeacion->id_plan ?>">Editar</td>
		<td><a class="btn btn-primary" href="reporte.php?id=<?php echo $planeacion->id_plan ?>">Descargar</td>
	</tr>
<?php } ?>
	</tr>
</table>	
</div>
<?php } 
$contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
?>
