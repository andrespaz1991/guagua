<?php
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
//TEMPORAL
if (!isset($_SESSION['usuario'])){
    header ("Location: sesion.php");
}
ob_start();
?>
	<style>
	    label, input, select{
	        font-size:16pt;
	        font-weight:normal;
	    }
	    #datos_adicionales, textarea{
	        font-size:12pt;
	    }
	    input[type="radio"], input[type="checkbox"]{
	        width:1em;
            height:1em;
	    }
	</style>
<title>Ver cuestionario</title>
<?php if (isset($_GET['embebido'])){ ?>
<button title="Salir de pantalla completa" class="btn btn-default" style="position:absolute;top:0;left:0" id="btn_salir"  onclick="parent.cancelar_pantalla_completa();">X</button>
<?php }
    if (isset($_POST['cod']) and empty($_GET)) $_GET['enc'] =  $_POST['cod'];
require("../comun/conexion.php");
require_once("../comun/funciones.php");
    #echo $_GET['enc'];
$sql_validar_usu = "SELECT * FROM `respuesta` WHERE pregunta = '".$_GET['enc']."' and `id_estudiante` = '".$_SESSION['id_usuario']."'";
$consulta = $mysqli->query($sql_validar_usu);
$_SESSION['ya']="NO";
$intentos = $consulta->num_rows>0;
#if ($intentos>0) echo "Intentos: ".$intentos."<br>";
/* consulta si este formulario ya fue respondido * /
if ($consulta->num_rows>0 and isset($_GET['a']) and $_SESSION['rol']=="estudiante"){
    $_SESSION['ya']="SI";
    echo "<script>alert2('Usted ya ha diligenciado este cuestionario, No puede responder dos veces el mismo cuestionario','warning');</script>";
        if ($_SESSION['rol']=="estudiante"){
            echo '<meta http-equiv="refresh" content="4; url=index.php" />';
            $contenido = ob_get_clean();
            include ("../comun/plantilla.php");
            exit();
        }
}
/* fin consulta si este formulario ya fue respondido, en esta seccion es posible parametrizar el numero de intentos de respuesta, sin embargo hay que revisar la consulta de las respuesta separadas por intento*/
?>
<section style="background-color:#e4e9ff;padding:20px;border-radius:10px">
    <form <?php if ($_SESSION['ya']="NO"){    if (isset($_GET['a'])){ ?> action="responder_cuestionario.php" method="post" <?php }else{ echo ' onsubmit="return false" '; } } ?>>
    <input type="hidden" name="id_estudiante" value="<?php echo $_SESSION['id_usuario'] ?>">
    <input type="hidden" name="id_cue" value="<?php if(isset($_GET['enc'])) echo $_GET['enc'] ?>">
    <input type="hidden" name="id_actividad" value="<?php if(isset($_GET['a'])) echo $_GET['a'] ?>">
    <input type="hidden" name="token" value="<?php echo encriptar($_SESSION['id_usuario'].$_GET['enc']) ?>">
    <?php 
    $sql_visita ="UPDATE `cuestionario` SET `visitas` = `visitas`+1 WHERE `id`=".$_GET['enc'];
    $mysqli->query($sql_visita);
    $sql ="SELECT `cuestionario`.`id`, `cuestionario`.`nombre`, `cuestionario`.`fecha`, `tipo_cuestionario`, concat (`usuario`.`nombre`,\" \", `usuario`.`apellido`) as `usuario` FROM `cuestionario` inner join `usuario` on `cuestionario`.`usuario` = `usuario`.`id_usuario` WHERE `cuestionario`.`id` = ".$_GET['enc'];
    $sql2 = "SELECT `preguntas_cuestionario`.`id_preguntas_cuestionario`, `preguntas_cuestionario`.`pregunta`, `preguntas_cuestionario`.`tipo_pregunta`, `preguntas_cuestionario`.`opciones` FROM `preguntas_cuestionario` WHERE `preguntas_cuestionario`.`id_cuestionario` = ".$_GET['enc'];
    $consulta = $mysqli->query($sql);
    if ($row = $consulta->fetch_assoc()){
    #echo "<pre>";
    #print_r($row);
    #echo "</pre>";
    echo "&nbsp;&nbsp;";
    echo "<input type='hidden' name ='id' value='".$row['id']."'>";
    echo "<h1>".$row['nombre']."<h1>";
    
    echo "<span id='datos_adicionales'>&nbsp;&nbsp; Fecha: ".formatofecha($row['fecha']);
    if ($row['tipo_cuestionario']!="")echo "&nbsp;&nbsp; Tipo de cuestionario: ".$row['tipo_cuestionario'];
    echo "&nbsp;&nbsp; Usuario: ".$row['usuario']."</span>";
    }
    $consulta2 = $mysqli->query($sql2);
    $num = 1;
    while ($row2 = $consulta2->fetch_assoc()){
    #echo "<pre>";
   #print_r($row2);
    #echo "</pre>";
    ?>
    <section style="background-color:#fdffbd;padding:10px;margin:10px;border-radius:10px"><h3>
    <input type="hidden" name="tipo[<?php echo $row2['id_preguntas_cuestionario'] ?>]" value="<?php echo  $row2['tipo_pregunta'] ?>">
    <label <?php echo 'for = "pregunta_'. $row2['id_preguntas_cuestionario'].'"';?>><?php
    echo $num;
    $num++;
    echo ".&nbsp;";
    echo $row2['pregunta'];
    echo "</label></h3>";
    if ($row2['tipo_pregunta'] == "checkbox"){
    echo '<div class="mipregunta ac-custom ac-checkbox ac-checkmark">';//ac-cross ac-boxfill
    $opciones = json_decode($row2['opciones'],true);
    foreach($opciones as $id => $valor){
        ?>
        <label><input type="checkbox" name="pregunta[<?php echo $row2['id_preguntas_cuestionario']?>][<?php echo $id ?>]" value="<?php echo $valor ?>">&nbsp;<?php echo $valor ?></label><br><br>
        <?php
    }#foreach($opciones as $id => $valor){
    echo '</div>';
    }# if ($row2['tipo_pregunta'] == "checkbox"){
    if ($row2['tipo_pregunta'] == "radio"){
    echo '<div class="mipregunta ac-custom ac-radio ac-fill">';//ac-swirl
    $opciones = json_decode($row2['opciones'],true);
    foreach($opciones as $id => $valor){
        ?>
        <label><input type="radio" name="pregunta[<?php echo $row2['id_preguntas_cuestionario']?>]" value="<?php echo $valor ?>">&nbsp;<?php echo $valor ?></label><br><br>
        <?php
    
    }#foreach($opciones as $id => $valor){
    echo '</div>';
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
    echo "</section>";
    }# while ($row2 = $consulta2->fetch_assoc()){
    ?> 
    <?php if ($_SESSION['ya']=="NO"){
    if (isset($_GET['a'])){
    ?> 
    <?php if (isset($_GET['embebido'])){ ?>
    <input type="hidden" name="embebido" value="1">
    <?php } ?> 
    <button type="submit" class="btn btn-primary">Responder</button>
    <?php } } ?> 
    </form>
</section>
<br><br>
<?php
    if ($_SESSION['rol']!="estudiante" or $_SESSION['rol']!="acudiente"){
           ?>
           <a onclick="window.print();"><span class="icon-sga-print"></span></a>
           <?php
        }
?>
<style>
    label{
    display: inline-block;
    position: relative;
    font-size: 1em !important;
    margin: 0 0 0 2px !important;
    vertical-align: top;
    color: #000;
    cursor: pointer;
    -webkit-transition: color 0.3s;
    transition: color 0.3s;
    }
    .ac-custom label::before {
	width: 25px !important;
	height: 25px !important;
	margin-top: -15px !important;
	margin-left: 50px !important;
    }
    .ac-custom label:hover {
	color: #7bba3f;
    }
    .ac-custom svg {
    width: 15px !important;
    height: 15px !important;
    padding: 0 !important;
    margin-top: -10px !important;
    margin-left: 50px !important;
    }
</style>
<?php require_once("../comun/config.php"); ?>
<script src="<?php echo SGA_COMUN_URL ?>/js/svgcheckbx.js" type="text/javascript"></script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>
