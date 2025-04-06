<?php date_default_timezone_set('America/Bogota');?>

<?php 
@session_start();
#echo $_SESSION['identificacion_usu'];
ob_start();
require("conexion.php");
//require("funciones.php");
function buscar_config($datos=""){
require("conexion.php");

$sql = "SELECT `config`.`id_config`, `config`.`fecha_inicio`, `config`.`hora_inicio`, `config`.`ano`, `config`.`fecha_fin`, `config`.`hora_fin`, `config`.`docente`, `config`.`estado` FROM `config`   ";$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`config`.`id_config`)," ", LOWER(`config`.`fecha_inicio`)," ", LOWER(`config`.`hora_inicio`)," ", LOWER(`config`.`ano`)," ", LOWER(`config`.`fecha_fin`)," ", LOWER(`config`.`hora_fin`)," ", LOWER(`config`.`docente`)," ", LOWER(`config`.`estado`)," ") LIKE "%'.utf8_decode(mb_strtolower($dato, 'UTF-8')).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `config`.`id_config` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_config']) and $_COOKIE['numeroresultados_config']!="") $sql .=$_COOKIE['numeroresultados_config'];
else $sql .= "10";
#$sql;

$consulta = $mysqli->query($sql);
?>
<?php if ( !isset ($_POST['Actualizar']))  { ?>

<div align="center">
<table align="center" border="1" id="tb<?php  ?>">
<thead>
<tr>
<th>Id Config</th>
<th>Fecha Inicio</th>
<th>Hora Inicio</th>
<th>Año Lectivo</th>
<th>Fecha Fin</th>
<th>Hora Fin</th>
<th>Responsable</th>
<th>Estado</th>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="config.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>
</td></th></tr>
</thead><tbody>
<?php
$contador = 0 ; 
while($row=$consulta->fetch_assoc()){
    $contador++;
?>
<tr>
<td><?php echo $row['id_config']?></td>
<td><?php echo $row['fecha_inicio']?></td>
<td><?php echo $row['hora_inicio']?></td>
<td><?php echo $row['ano']?></td>
<td><?php echo $row['fecha_fin']?></td>
<td><?php echo $row['hora_fin']?></td>
<?php 
require 'conexion.php';
$sqldocente ='select * from usuario where id_usuario ="'.$row['docente'].'"';
$consultadocente =$mysqli->query($sqldocente);
if ($rowdocente =$consultadocente->fetch_Assoc()){ ?>

<td><?php echo $rowdocente['nombre'].' '.$rowdocente['apellido']; ?></td>
<td><?php }
if ($row['estado']=="1"){
   echo "Activa"; 
}
else{
echo "inactiva";
}
 ?></td>
<td>
    <?php if ($contador == 1) { ?>
    
<form id="formModificar" name="formModificar" method="post" action="config.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_config']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
<?php } ?>
</td>
<td>
    <?php if ($row['estado'] =="1"){    ?>
    <a href="el.php?del=<?php echo $row['id_config']; ?>">
      <img src="img/cerrar.png"/>
</a>
<?php } ?>
</td>
</tr>
<?php
}//fin while
?>
</tbody>
</table></div>
<?php
}//
}//fin function buscar
if (isset($_GET['buscar'])){
buscar_config($_POST['datos']);
exit();
}    if (isset($_POST['Actualizar'] )){      @session_start();
    require 'conexion.php';
 $sqlup ='UPDATE `config` SET estado = 1, `fecha_fin`="'.$_POST['fecha_fin'].'",`hora_fin`="'.$_POST['hora_fin'].'",docente="'.$_SESSION['identificacion_usu'].'" where id_config="'.$_POST['cod'].'"';
$consultaup =$mysqli-> query ($sqlup);
header ("location:config.php");
}
if (isset($_POST['submit']) and $_POST['submit']=="Modificar"  ){
 $fecha_actual = date ('Y-m-d');
  $hora_actual = date('H:i:s');
?>
<form action="" method="POST">
   <h1>Modificar</h1>
<label>Fecha Finalización</label>
<input type="hidden" name="cod" value ="<?php echo $_POST['cod']; ?>" />

<input type="Date" name="fecha_fin" value ="<?php echo $fecha_actual; ?>" /><br/><br/>
<label>Fecha Finalización</label>
<input type="time" name="hora_fin" value ="<?php echo $hora_actual; ?>" /><br/>
<input type="submit" name="Actualizar" value ="Actualizar" />
</form>

<?php

}
if (isset($_POST['submit']) and $_POST['submit']=="Nuevo" ){
  $fecha_actual = date ('Y-m-d');
  $hora_actual = date('g:i:s a');
  $año_actual = date('Y');
  @session_start();
 $sqlnuevaeval='INSERT INTO `config`(`fecha_inicio`, `hora_inicio`, `ano`, `docente`, `estado`) VALUES
("'.$fecha_actual.'","'.$hora_actual.'","'.$año_actual.'","'.$_SESSION['identificacion_usu'].'",1)';
#echo $sqlnuevaeval ;
require 'conexion.php';
$consultanuevoeval = $mysqli-> query ($sqlnuevaeval);

 $sqlupateestado = 'UPDATE `config` SET `estado`=0 where id_config <> "'.$mysqli->insert_id.'"';

$consultaupate = $mysqli-> query ($sqlupateestado);

header ("location:config.php");

}

?>
<?php if (!isset ($_POST['Actualizar']))  { ?>
<?php if (!isset($_POST['submit'])){ ?>

<p align="center">
<h1 align="center">Configuraciones</h1>
<br>

<p align="center">
<b><label>Buscar: </label></b><input type="text" id="buscar" onkeyup ="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_config" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_config',this.value);buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_config',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_config',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</p>
<span id="txtsugerencias">
<?php
buscar_config();
?>
</span>
<?php
}
}

//}//fin else if isset cod
?>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
#include ("plantilla.php");
?>
