 <?php
@session_start();
require_once("../../comun/autoload.php");
require_once("../..//comun/config.php");
require_once("../../comun/funciones.php");

if (isset($_SESSION['rol']) and $_SESSION['rol']=="estudiante" ) {
ob_clean();
ob_start();
?>
<?php
require 'conexion.php';
$sql=inscripciones_estudiantes();
$consulta = $mysqli -> query ($sql);
$cantidad_inscripciones =  $consulta -> num_rows;
if ($cantidad_inscripciones == 0){
  echo "Al parecer no existe asignación acádemcia para su grado, por favor contacte al administrador para mayor información";
}
?> 
<?php if (!isset($_POST['Evaluar'])){ ?>
Seleccione la asignatura a valorar
<br>
<br>
<form action="" method="POST">
<?php
 while ($row = $consulta->fetch_Assoc()){ 
$sql1=estado_votacion_materia($row['id_asignacion']);
$consulta1 = $mysqli -> query ($sql1);
$cantidad_llenos =  $consulta1 -> num_rows;
  ?>
    <input
<?php if($cantidad_llenos>0){
echo "disabled";
} ?>
     type="radio" name="asignacion" required value="<?php echo $row['id_asignacion']; ?>"/>  
   <?php echo $row['nombre_materia']. '('.$row['nombre'].' '.$row['apellido'].') ';
if($cantidad_llenos>0){
  echo "<strong>Valorado</strong>";
}

    echo'<br>'; ?>
<?php } ?>
    <input type="submit" value="Evaluar"/>
</form>
<?php } ?>

<?php 
if (isset($_GET['asignacion']) or isset($_POST['asignacion'])){

 if(isset($_POST['asignacion'])){
  $asignacion=$_POST['asignacion'];
 }
   if(isset($_GET['asignacion'])){
  $asignacion=$_GET['asignacion'];
 }
require("conexion.php");
@session_start();
$sql="SELECT * FROM `inscripcion`INNER join asignacion on asignacion.id_asignacion = inscripcion.id_asignacion INNER join materia on asignacion.id_asignatura =materia.id_materia INNER JOIN usuario on usuario.id_usuario = asignacion.id_docente WHERE asignacion.id_asignacion = '".$asignacion."';";
 $consulta =$mysqli -> query ($sql); ?>
<div align="center">
 <?php
while ($row =  $consulta -> fetch_assoc()){   ?>
MATERIA : <?php echo $row['nombre_materia'];?><br>
DOCENTE: <?php echo  $row['nombre']." ". $row['apellido'];
?>
<img style="margin-left:20%;position:absolute;margin-top:-8%" width="150px" src="<?php echo SGA_COMUN_SGA_DATA.'/'.$row['foto'] ?>">
</div>
  <?php
setcookie("nombre",$row['nombre']);
setcookie("apellido",$row['apellido']);
setcookie("nombre_materia",$row['nombre_materia']);
setcookie("id_asignacion",$row['id_asignacion']);
require 'conexion.php';
  $sqlconfig ='select * from config where estado="1" order by id_config desc';
$consulta = $mysqli-> query ($sqlconfig);
while ($row= $consulta ->fetch_Assoc()) {
    setcookie("session",$row['id_config']);
}
}
?>
<br/>
<form method="post" action="registrar2.php"><br/><br/>
<input type="hidden" name="asignacion" value="<?php echo $_GET['asignacion']; ?>"><br>
<?php
require ("conexion.php");
$sql = "SELECT `valor`, `descripcion` FROM `escala`";
$consulta =$mysqli -> query ($sql);
$escala = array();
while ($row =  $consulta -> fetch_assoc()){
    $escala[$row['valor']]=$row['descripcion'];
}
$sql="SELECT `categoria`.`id_categoria`, `categoria`.`nombre_categoria` FROM `categoria` inner join `pregunta` on `categoria`.`id_categoria` = `pregunta`.`categoria` where `pregunta`.`tipo` = 'estudiante' group by `categoria`.`id_categoria`";
$consulta =$mysqli -> query ($sql);
$categoria = array();
while ($row =  $consulta -> fetch_assoc()){
    $categoria[$row['id_categoria']]=$row['nombre_categoria'];
}
$sql = "SELECT `id_pregunta`, `cod_pregunta`, `pregunta`, `categoria` FROM `pregunta` where tipo = 'estudiante'";

?>
<table border="2" align="center" style="margin:0 auto;">
<?php
    foreach($categoria as $id_categoria => $value_categoria){
?>
<tr>
<th><?php echo $value_categoria; ?></th>
<?php
$respuestas_json = array();
$array_escala = array();
    foreach($escala as $id => $valor){
         echo "<th title='".$valor."'>".$valor."</th>";
          $array_escala[$id]="0";//array(A=>0,N=>0,S=>0)
    }
?>
</tr>
<?php
$consulta =$mysqli -> query ($sql);
while ($row =  $consulta -> fetch_assoc()){
if ($row['categoria']==$id_categoria){
    echo "<tr>";
    echo "<td><b>".$row['cod_pregunta'].". </b> ".$row['pregunta']."</td>";
    foreach($escala as $id => $valor){
         echo  "<td><input title='".$valor."' type='radio' name='respuesta[".$row['id_pregunta']."]' value='".$id."'";
         if (end($escala)==$valor) echo "checked";
         echo " ></td>";
        
    }
    echo "</tr>";
$respuestas_array [$row['id_pregunta']]=$array_escala;//array(A=>0,N=>0,S=>0)
}//if categoria
}//query
}//categoria

?>
<!--tr>
    <th colspan="4">Escriba en este espacio sus sugerencias para mejorar el proceso educativo:</th>
</tr>
<tr>
    <td colspan="4" style="margin:0;padding:0;<heigh></heigh>t:200px"><textarea style="width:100%;margin:0;padding:0;font-size:15px;	resize: none;height:103%" name="observacion" placeholder ="Observación"></textarea></td>
</tr-->

<tr><td colspan="6" style="text-align:center;"><input type="submit" value="Registrar Evaluaci&oacute;n"></td></tr>
</table><br>
</form>
<?php
 $sqlconfig ='select * from config where estado = "1" order by id_config desc limit 1' ;
require 'conexion.php';
$consultaconfig = $mysqli->query ($sqlconfig);
while ($rowconfig= $consultaconfig->fetch_assoc()){
    $var = $rowconfig['id_config'];
}
require("conexion.php");
$respuestas_json = json_encode($respuestas_array);
 $misql = "INSERT INTO `evaluacion`(`asignacion`, `respuestas`, `session`) VALUES ('".$asignacion."','$respuestas_json',$var);";
$consulta_ev =$mysqli->query($misql);
}
?>
</section>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../../comun/plantilla.php");
}
?>
