<?php if (isset($_GET['buscar_estudiante'])){ ?>
<!DOCTYPE html>
<html lang="es">
<head>
</head>
<body>
<section>
<?php
require '../conexion.php';
//require '../funciones.php';
$datosrecibidos = $_POST['datos'];
 $datos = explode(" ",$datosrecibidos); ?>
 <?php
 header('Content-Type: text/html; charset=ISO-8859-1'); 
  $sql = "select * from estudiante";
  $cont =0;
foreach ($datos as $id => $dato){
 $sql .= ' where  concat( enviado," ",estudiante.identificacion," ",estudiante.proyecto," ",`nombre`," ",`entidad`) LIKE "%'.utf8_decode($dato).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
$sql .=  ' ORDER BY `identificacion` DESC LIMIT   ';
if (isset($_COOKIE['numeroresultados']) and $_COOKIE['numeroresultados']!="") $sql .=$_COOKIE['numeroresultados'];
else $sql .= "8";
//echo $sql;
if ($consulta = $mysqli->query($sql)){
if ($consulta -> num_rows > 0) {  ?>
    <table border="1" id="tbdiagnosticos">
    <thead>
  <tr>
    <th>Identificacion</th>
    <th>Nombre</th>
    <th>Celular</th>
    <th>Proyecto</th>
    <th>Entidad</th>
     <th>Aprobado</th>
    <th colspan="2">Reporte</th>
</tr>
</thead><tbody>
<?Php
$contador = 1; 
while($row=$consulta->fetch_assoc())
{

  if ($contador % 2 == 0) echo '<tr class="par">';
  else echo '<tr class="impar">';
  //echo '<td>'.$row['id_pac'].'</td>';
 echo '<td>'.$row['identificacion'].'</td>';
    echo '<td>'.$row['nombre'].' '.$row['nombre'].'</td>';
    echo '<td>'.$row['celular'].'</td>';
    echo '<td>'.$row['proyecto'].'</td>';
    echo '<td>'.$row['entidad'].'</td>';
    echo '<td>'.$row['aprobado'].'</td>';
  //session_start();
 
  /*  echo '<td><form id="formModificar" name="formModificar" method="post" action="funciones.php">
  <input name="cod" class = "button" type="hidden" id="cod" value="'.$row['id_seguimiento'].'">
  <input type="submit"  name="submitm" id="button" value="Modificar">
  </form></td>';*/
  echo '<td><a><img src ="img/pdf.png"></a></td>'; 

  ?>
  <?php
    echo'</tr>'; 
  $contador ++; 
   }//fin while
//}//fin else if isset cod
}
}
}
} 
?>
</tbody>
</table>
</span>
</div>
</section>
</body>
</html>

<?php if (isset($_GET['buscar_seguimiento'])){ ?>

<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>

<?php
require '../conexion.php';
require '../funciones.php';

 $datosrecibidos = $_POST['datos'];
 $datos = explode(" ",$datosrecibidos); ?>
 <?php
 header('Content-Type: text/html; charset=ISO-8859-1'); 
  $sql = "select
estudiante.nombre,
 seguimiento.id_seguimiento, seguimiento.identificacion, seguimiento.cita,estudiante.proyecto, seguimiento.observaciones, seguimiento.asistio, seguimiento.listo_para_enviar, seguimiento.asesoria_tecnica,seguimiento.hora  from estudiante, seguimiento 
where estudiante.identificacion = seguimiento.identificacion and ";
$cont =  0;
foreach ($datos as $id => $dato){
 $sql .= ' concat(`id_seguimiento`," ",estudiante.identificacion," ",estudiante.proyecto," ",`nombre`," ",`observaciones`) LIKE "%'.utf8_decode($dato).'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `id_seguimiento` DESC LIMIT  ";
if (isset($_COOKIE['numeroresultados']) and $_COOKIE['numeroresultados']!="") $sql .=$_COOKIE['numeroresultados'];
else $sql .= "8";
//echo $sql;
if ($consulta = $mysqli->query($sql)){
if ($consulta -> num_rows > 0) {	?>
  <section>
	<div align="center">
<span id="txtsugerencias">
    <table border="1" id="tbdiagnosticos">
    <thead>
  <tr>
    <th>Seguimiento</th>
    <th>Estudiante</th>
    <th>Proyecto</th>
    <th>Fecha</th>
    <th>Observaciones</th>
    <th>Asistio</th>
     <th>Listo</th>
    <th colspan="2">
   Reporte</th>
</tr>
</thead><tbody>
<?Php
$contador = 1; 
while($row=$consulta->fetch_assoc())
{

  if ($contador % 2 == 0) echo '<tr class="par">';
  else echo '<tr class="impar">';
  //echo '<td>'.$row['id_pac'].'</td>';
  echo '<td>'.$row['id_seguimiento'].'</td>';
    echo '<td>'.$row['identificacion'].' '.$row['nombre'].'</td>';
    echo '<td>'.$row['proyecto'].'</td>';
       echo '<td>'.$row['cita'].' '.$row['hora'].'</td>';
    echo '<td>'.$row['observaciones'].'</td>';
    echo '<td>'.$row['asistio'].'</td>';
    echo '<td>'.$row['listo_para_enviar'].'</td>';
  //session_start();
 
   echo '<td><form id="formModificar" name="formModificar" method="post" action="funciones.php">
  <input name="cod" class = "button" type="hidden" id="cod" value="'.$row['id_seguimiento'].'">
  <input type="submit"  name="submitm" id="button" value="Modificar">
  </form></td>';
  //echo '<td><a><img src ="img/pdf.png"></a></td>'; 

  ?>
  <?php
    echo'</tr>'; 
  $contador ++; 
   }//fin while
//}//fin else if isset cod
}
}
}
?>
</tbody>
</table>
</span>
</section>
</body>
</html>

<!--  -->
