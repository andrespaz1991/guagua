<?php 
ob_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);



echo '<center>';
 /*require("funciones.php");*/ 
function buscar_asignacion($datos="",$reporte=""){

require("conexion.php");
require_once ("../../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_asignacion']) ? $_COOKIE['numeroresultados_asignacion'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];

$sql = "SELECT `asignacion`.`id_asignacion`, `asignacion`.`id_curso`, `curso`.`nombre_curso` as cursonombre_curso, `asignacion`.`id_asignatura`, `materia`.`nombre_materia` as materianombre_materia, `asignacion`.`id_docente`, `docente`.`nombre_docente` as docentenombre_docente, `asignacion`.`ano_lectivo`, `ano_lectivo`.`descripcion_ano` as ano_lectivodescripcion_ano FROM `asignacion`  inner join `curso` on `asignacion`.`id_curso` = `curso`.`id_curso` inner join `materia` on `asignacion`.`id_asignatura` = `materia`.`id_materia` inner join `docente` on `asignacion`.`id_docente` = `docente`.`id_docente` inner join `ano_lectivo` on `asignacion`.`ano_lectivo` = `ano_lectivo`.`id_ano_lectivo`  ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`asignacion`.`id_asignacion`)," ", LOWER(`curso`.`nombre_curso`)," ", LOWER(`materia`.`nombre_materia`)," ", LOWER(`docente`.`nombre_docente`)," ", LOWER(`ano_lectivo`.`descripcion_ano`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$consulta=$mysqli->query($sql);
echo $paginacion->records($consulta->num_rows);
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= "";
}

if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `asignacion`.`id_asignacion` desc LIMIT ";
$sql .=  "  " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;

/*echo $sql;*/ 


$consulta = $mysqli->query($sql);

$numero_usuario = $consulta->num_rows;
$minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
$maximo_usuario += $minimo_usuario-1;
echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$numero_usuario." en página ".$paginacion->get_page()."</p>";

 ?>
<div align="center">
<table border="1" id="tbasignacion" align="center">
<thead>
<tr>
<th>Id Asignacion</th>
<th>Id Curso</th>
<th>Id Asignatura</th>
<th>Id Docente</th>
<th>Ano Lectivo</th>
<?php if ($reporte==""){ ?>
<th colspan="2"><form id="formNuevo" name="formNuevo" method="post" action="asignacion.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="Nuevo">
</form>
</th>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td><?php echo $row['id_asignacion']?></td>
<td><?php echo $row['cursonombre_curso']?></td>
<td><?php echo $row['materianombre_materia']?></td>
<td><?php echo $row['docentenombre_docente']?></td>
<td><?php echo $row['ano_lectivodescripcion_ano']?></td>
<?php if ($reporte==""){ ?>
<td>
<form id="formModificar" name="formModificar" method="post" action="asignacion.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_asignacion']?>">
<input type="submit" name="submit" id="submit" value="Modificar">
</form>
</td>
<td>
<input type="image" src="../../comun/img/eliminar.png" onClick="confirmeliminar('asignacion.php',{'del':'<?php echo $row['id_asignacion'];?>'},'<?php echo $row['id_asignacion'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<div class="text-center">
    <?php    $paginacion->render2();    ?>
    </div>
</div>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_asignacion($_POST['datos']);
exit();
}

if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM asignacion WHERE id_asignacion="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
echo '
Registro eliminado
<meta http-equiv="refresh" content="1; url=asignacion.php" />
'; 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=asignacion.php" />
<?php 
}
}
 ?>
<center>
<h1>Asignacion</h1>
</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO asignacion (`id_asignacion`, `id_curso`, `id_asignatura`, `id_docente`, `ano_lectivo`) VALUES ('".$_POST['id_asignacion']."', '".$_POST['id_curso']."', '".$_POST['id_asignatura']."', '".$_POST['id_docente']."', '".$_POST['ano_lectivo']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=asignacion.php" />';
 }else{ 
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=asignacion.php" />';
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo"){

$textoh1 ="Registrar";
$textobtn ="Registrar";

echo '<form id="form1" name="form1" method="post" action="asignacion.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_asignacion']))  echo $row['id_asignacion'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_asignacion"type="hidden" id="id_asignacion" value="';if (isset($row['id_asignacion'])) echo $row['id_asignacion'];echo '"';echo '></p>';
echo '<p><label for="id_curso">Id Curso:</label>';
$sql2= "SELECT id_curso,nombre_curso FROM curso;";
echo '<input type="text" autocomplete="off" list="list_id_curso" class="" name="id_curso" id="id_curso"><datalist id="list_id_curso">';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option data-value="'.$row2['id_curso'].'"';echo '>'.$row2['nombre_curso'].'</option>';
if ($row2["id_curso"]==$row["id_curso"]){
echo '<script>var varid_curso = document.getElementById("id_curso");varid_curso.value="'.$row2["id_curso"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_curso" id="id_curso-hidden" value="';
if (isset($row['id_curso'])) echo $row['id_curso'];
echo '"></p>';
echo '<p><label for="id_asignatura">Id Asignatura:</label>';
$sql3= "SELECT id_materia,nombre_materia FROM materia;";
echo '<input type="text" autocomplete="off" list="list_id_asignatura" class="" name="id_asignatura" id="id_asignatura"><datalist id="list_id_asignatura">';
$consulta3 = $mysqli->query($sql3);
while($row3=$consulta3->fetch_assoc()){
echo '<option data-value="'.$row3['id_materia'].'"';echo '>'.$row3['nombre_materia'].'</option>';
if ($row3["id_materia"]==$row["id_asignatura"]){
echo '<script>var varid_asignatura = document.getElementById("id_asignatura");varid_asignatura.value="'.$row2["id_asignatura"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_asignatura" id="id_asignatura-hidden" value="';
if (isset($row['id_asignatura'])) echo $row['id_asignatura'];
echo '"></p>';
echo '<p><label for="id_docente">Id Docente:</label>';
$sql4= "SELECT id_docente,nombre_docente FROM docente;";
echo '<input type="text" autocomplete="off" list="list_id_docente" class="" name="id_docente" id="id_docente"><datalist id="list_id_docente">';
$consulta4 = $mysqli->query($sql4);
while($row4=$consulta4->fetch_assoc()){
echo '<option data-value="'.$row4['id_docente'].'"';echo '>'.$row4['nombre_docente'].'</option>';
if ($row4["id_docente"]==$row["id_docente"]){
echo '<script>var varid_docente = document.getElementById("id_docente");varid_docente.value="'.$row2["id_docente"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_docente" id="id_docente-hidden" value="';
if (isset($row['id_docente'])) echo $row['id_docente'];
echo '"></p>';
echo '<p><label for="ano_lectivo">Ano Lectivo:</label>';
$sql5= "SELECT id_ano_lectivo,descripcion_ano FROM ano_lectivo;";
echo '<input type="text" autocomplete="off" list="list_ano_lectivo" class="" name="ano_lectivo" id="ano_lectivo"><datalist id="list_ano_lectivo">';
$consulta5 = $mysqli->query($sql5);
while($row5=$consulta5->fetch_assoc()){
echo '<option data-value="'.$row5['id_ano_lectivo'].'"';echo '>'.$row5['descripcion_ano'].'</option>';
if ($row5["id_ano_lectivo"]==$row["ano_lectivo"]){
echo '<script>var varano_lectivo = document.getElementById("ano_lectivo");varano_lectivo.value="'.$row2["ano_lectivo"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="ano_lectivo" id="ano_lectivo-hidden" value="';
if (isset($row['ano_lectivo'])) echo $row['ano_lectivo'];
echo '"></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin nuevo*/ 
if ($_POST['submit']=="Modificar"){

$sql = "SELECT `id_asignacion`, `id_curso`, `id_asignatura`, `id_docente`, `ano_lectivo` FROM `asignacion` WHERE id_asignacion ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
echo '<form id="form1" name="form1" method="post" action="asignacion.php">
<h1>'.$textoh1.'</h1>';
?>

<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_asignacion']))  echo $row['id_asignacion'] ?>" size="120" required></p>
<?php 
echo '<p><input class=""name="id_asignacion"type="hidden" id="id_asignacion" value="';if (isset($row['id_asignacion'])) echo $row['id_asignacion'];echo '"';echo '></p>';
echo '<p><label for="id_curso">Id Curso:</label>';
$sql2= "SELECT id_curso,nombre_curso FROM curso;";
echo '<input type="text" autocomplete="off" list="list_id_curso" class="" name="id_curso" id="id_curso"><datalist id="list_id_curso">';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option data-value="'.$row2['id_curso'].'"';echo '>'.$row2['nombre_curso'].'</option>';
if ($row2["id_curso"]==$row["id_curso"]){
echo '<script>var varid_curso = document.getElementById("id_curso");varid_curso.value="'.$row2["id_curso"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_curso" id="id_curso-hidden" value="';
if (isset($row['id_curso'])) echo $row['id_curso'];
echo '"></p>';
echo '<p><label for="id_asignatura">Id Asignatura:</label>';
$sql3= "SELECT id_materia,nombre_materia FROM materia;";
echo '<input type="text" autocomplete="off" list="list_id_asignatura" class="" name="id_asignatura" id="id_asignatura"><datalist id="list_id_asignatura">';
$consulta3 = $mysqli->query($sql3);
while($row3=$consulta3->fetch_assoc()){
echo '<option data-value="'.$row3['id_materia'].'"';echo '>'.$row3['nombre_materia'].'</option>';
if ($row3["id_materia"]==$row["id_asignatura"]){
echo '<script>var varid_asignatura = document.getElementById("id_asignatura");varid_asignatura.value="'.$row2["id_asignatura"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_asignatura" id="id_asignatura-hidden" value="';
if (isset($row['id_asignatura'])) echo $row['id_asignatura'];
echo '"></p>';
echo '<p><label for="id_docente">Id Docente:</label>';
$sql4= "SELECT id_docente,nombre_docente FROM docente;";
echo '<input type="text" autocomplete="off" list="list_id_docente" class="" name="id_docente" id="id_docente"><datalist id="list_id_docente">';
$consulta4 = $mysqli->query($sql4);
while($row4=$consulta4->fetch_assoc()){
echo '<option data-value="'.$row4['id_docente'].'"';echo '>'.$row4['nombre_docente'].'</option>';
if ($row4["id_docente"]==$row["id_docente"]){
echo '<script>var varid_docente = document.getElementById("id_docente");varid_docente.value="'.$row2["id_docente"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="id_docente" id="id_docente-hidden" value="';
if (isset($row['id_docente'])) echo $row['id_docente'];
echo '"></p>';
echo '<p><label for="ano_lectivo">Ano Lectivo:</label>';
$sql5= "SELECT id_ano_lectivo,descripcion_ano FROM ano_lectivo;";
echo '<input type="text" autocomplete="off" list="list_ano_lectivo" class="" name="ano_lectivo" id="ano_lectivo"><datalist id="list_ano_lectivo">';
$consulta5 = $mysqli->query($sql5);
while($row5=$consulta5->fetch_assoc()){
echo '<option data-value="'.$row5['id_ano_lectivo'].'"';echo '>'.$row5['descripcion_ano'].'</option>';
if ($row5["id_ano_lectivo"]==$row["ano_lectivo"]){
echo '<script>var varano_lectivo = document.getElementById("ano_lectivo");varano_lectivo.value="'.$row2["ano_lectivo"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="ano_lectivo" id="ano_lectivo-hidden" value="';
if (isset($row['ano_lectivo'])) echo $row['ano_lectivo'];
echo '"></p>';

echo '<p><input type="submit" name="submit" id="submit" value="'.$textobtn.'"></p>
</form>';
} /*fin modificar*/ 
if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE asignacion SET id_asignacion='".$_POST['id_asignacion']."', id_curso='".$_POST['id_curso']."', id_asignatura='".$_POST['id_asignatura']."', id_docente='".$_POST['id_docente']."', ano_lectivo='".$_POST['ano_lectivo']."'WHERE  id_asignacion = '".$cod."';";
/* echo $sql;*/ 
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=asignacion.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=asignacion.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_asignacion" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_asignacion',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_asignacion',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_asignacion',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_asignacion();
 ?>
</span>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var inputlists = document.querySelectorAll('input[list]');
for(var j = 0; j< inputlists.length; j++) {
inputlists[j].addEventListener('input', function(e) {
var input = e.target,
list = input.getAttribute('list'),
options = document.querySelectorAll('#' + list + ' option'),
hiddenInput = document.getElementById(input.id + '-hidden'),
inputValue = input.value;
hiddenInput.value = inputValue;
for(var i = 0; i < options.length; i++) {
var option = options[i];
if(option.innerText === inputValue) {
 hiddenInput.value = option.getAttribute('data-value');
break;
}
}
});
}
</script>
<script>
document.getElementById('menu_asignacion').className ='active '+document.getElementById('menu_asignacion').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
 ?>
