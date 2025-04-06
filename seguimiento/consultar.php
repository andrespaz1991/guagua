<?php 
# include 'menu.php';
#require('conexion.php'); 
ob_start();
require 'fecha.class.php';
require 'mysql.class.php';
require 'persona.class.php';
require ('funciones.php');
//require('funciones.php');?>
<!doctype html>
<head>
<link rel="stylesheet" type="text/css" href="css/estilo.css" /><!--estilos -->
<title>Seguimiento</title>
<style type="text/css">
.h {
	font-family: Comic Sans MS, cursive;
}
</style>
</head>
<body>
<section><br>
<?php
require("ajax/buscador.php");
barra_buscar("buscar_seguimiento");
#header('Content-Type: text/html; charset=ISO-8859-1'); 
$sql="select * from estudiante, seguimiento 
where estudiante.identificacion = seguimiento.identificacion 
order by id_seguimiento Desc limit 8 ";

$conexion=new Conexion();
$mysqli = $conexion->conectar();
$consulta = $mysqli->query($sql);
?>
<div align="center">
<span id="txtsugerencias">
     <table border="1" id="tbdiagnosticos">
    <thead>
  <tr>
    <th>Seguimiento</th>
    <th>Estudiante</th>
    <th>Tematica</th>
    <th>Fecha</th>
    <th>Observaciones</th>
    <th>Asistio</th>
    <th>Valor</th>
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
    echo "<meta charset='utf-8'/>";
 	echo '<td>'.$row['id_seguimiento'].'</td>';
    echo '<td>'.$row['identificacion'].' '.$row['nombre'].'</td>';
    echo '<td>'.(($row['contenido'])).'</td>';
    echo '<td>'.$row['fecha_asesoria'].' '.$row['hora'].'</td>';
    echo '<td>'.($row['observaciones']).'</td>';
    echo '<td>'.$row['asistio'].'</td>';
    echo '<td>'.$row['asistio'].'</td>';
 ?>
<td><a href="pdf.php?f=<?php echo $row['id_seguimiento']; ?>"><img src ="img/pdf.png"></a></td> 
	<?php
    echo '</tr>'; 
	$contador ++; 
	 }//fin while
//}//fin else if isset cod
?>
</tbody>
</table>
</span>
</div>
</section>
</body>
</html>
<?php $contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
 ?>
