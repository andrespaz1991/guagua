<?php
require_once ("../../comun/autoload.php");
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=Listado de estudiantes.xls");
$academico->id_asignacion=$_POST['id_asignacion'];
$listado_estudiantes= $academico->estudiantes_de_una_asignacion();
?>
<table>
<tr>
	<th>Identificación</th>
	<th>Nombre</th>
</tr>	
<?php
foreach ($listado_estudiantes as $estudiantes => $estudiante) {
echo "<tr>";
echo "<td>";
echo $estudiante['id_usuario'];
echo "</td>";
echo "<td>";
$persona=new Persona($estudiante['id_usuario']);
echo $persona->apellido.' '.$persona->nombre.'<br>';
echo "</td>";
echo "</tr>";
}
?>
</table>