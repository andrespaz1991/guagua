<?php
require ("../comun/conexion.php");
@session_start();
$_SESSION['barra_busqueda']= "";
//TEMPORAL
$_SESSION['nombre']="admin";
$_SESSION['clave']="admin";
$_SESSION['apellido']="admin";
$_SESSION['id_usuario']="1";
$_SESSION['usuario']="admin";
$_SESSION['foto']="user-icon.jpg";
$_SESSION['rol']="admin";
$_SESSION['roles']="admin,docente,acudiente,estudiante";
$_SESSION['num_visitas']="6";
$_SESSION['puntos']="5";
$_GET['a']=1;
$_GET['enc']=2;
//TEMPORAL
if (!isset($_SESSION['usuario'])){
    header ("Location: sesion.php");
}
ob_start();
?>
<title>Descargar cuestionario</title>
    <?php 
    $sql ="SELECT `cuestionario`.`id`, `cuestionario`.`nombre`, `cuestionario`.`fecha`, `tipo_cuestionario`, concat (`usuario`.`nombre`,\" \", `usuario`.`apellido`) as usuario FROM `cuestionario` inner join `usuario` on `cuestionario`.`usuario` = `usuario`.`id_usuario` WHERE `cuestionario`.`id` = ".$_GET['enc'];
    $sql2 = "SELECT `preguntas_cuestionario`.`id_preguntas_cuestionario`, `preguntas_cuestionario`.`pregunta`, `preguntas_cuestionario`.`tipo_pregunta`, `preguntas_cuestionario`.`opciones`,`tipo_pregunta`.`nombre`, `tipo_pregunta`.`categorias` FROM `preguntas_cuestionario` inner join `tipo_pregunta` on `tipo_pregunta`.`id` = `preguntas_cuestionario`.`tipo_pregunta` WHERE `preguntas_cuestionario`.`id_cuestionario` = ".$_GET['enc'];
    $consulta = $mysqli->query($sql);
    #echo $sql;
    if ($row = $consulta->fetch_assoc()){
    ?>
<table border = "1">
    <tr>
    <td><?php echo $row['nombre'];?><td>
    <td>Fecha: <?php echo formatofecha($row['fecha']); ?></td>
    <td><?php if ($row['tipo_cuestionario']!="") echo "Tipo de cuestionario: ".$row['tipo_cuestionario'];?></td>
    <td><?php echo "Usuario: ".$row['usuario']?></td>
    </tr>
</table>
<br>
<table border = "1">
    <?php
    }
    $consulta2 = $mysqli->query($sql2);
    $num = 1;
    while ($row2 = $consulta2->fetch_assoc()){
    #echo "<pre>";
   #print_r($row2);
    #echo "</pre>";
    ?>
    <tr>
    <td><?php echo $num; $num++; ?></td>
    <td><?php echo $row2['categorias'] ?></td>
    <td><?php echo $row2['nombre'] ?></td>
    <td><?php echo $row2['pregunta'];?></td>
    <td>
    <?php
    if ($row2['tipo_pregunta'] == "checkbox"){
    echo '<p>';//ac-cross ac-boxfill
    $opciones = json_decode($row2['opciones'],true);
    foreach($opciones as $id => $valor){
    echo $valor;
    }#foreach($opciones as $id => $valor){
    echo '</p>';
    }# if ($row2['tipo_pregunta'] == "checkbox"){
    if ($row2['tipo_pregunta'] == "radio"){
    echo '<div class="mipregunta ac-custom ac-radio ac-fill">';//ac-swirl
    $opciones = json_decode($row2['opciones'],true);
    foreach($opciones as $id => $valor){
        ?>
        <p><?php echo $valor ?></p>
        <?php
    }#foreach($opciones as $id => $valor){
    ?></td><?php
    }# if ($row2['tipo_pregunta'] == "radio"){
    if ($row2['tipo_pregunta'] == "select"){
    $opciones = json_decode($row2['opciones'],true);
    ?>
    <select id="pregunta_<?php echo $row2['id_preguntas_cuestionario']?>" name="pregunta[<?php echo $row2['id_preguntas_cuestionario']?>]">
    <option value="">Seleccione una opción</option>
    <?php
    foreach($opciones as $id => $valor){
        ?>
        <option value="<?php echo $valor ?>"><?php echo $valor ?></option>
        <?php
    
    }#foreach($opciones as $id => $valor){
    ?>
    </select>
    <?php
    }# if ($row2['tipo_pregunta'] == "select"){
    if ($row2['tipo_pregunta'] == "input"){
    $opciones = json_decode($row2['opciones'],true);
    ?>
    <input autocomplete="off" id="pregunta_<?php echo $row2['id_preguntas_cuestionario']?>" style="width:550px" name="pregunta[<?php echo $row2['id_preguntas_cuestionario']?>]">
    <?php
    }# if ($row2['tipo_pregunta'] == "input"){
    if ($row2['tipo_pregunta'] == "textarea"){
    $opciones = json_decode($row2['opciones'],true);
    ?>
    <textarea id="pregunta_<?php echo $row2['id_preguntas_cuestionario']?>" class="textarea" style="width:550px" name="pregunta[<?php echo $row2['id_preguntas_cuestionario']?>]"></textarea>
    <?php
    }# if ($row2['tipo_pregunta'] == "textarea"){
    echo "</tr>";
    }# while ($row2 = $consulta2->fetch_assoc()){
    ?> 
</table>
<?php require_once("../comun/config.php"); ?>
<script src="<?php echo SGA_COMUN_URL ?>/js/svgcheckbx.js" type="text/javascript"></script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>
