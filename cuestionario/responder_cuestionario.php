<?php
#print_r($_POST);
#exit();
if (isset($_POST['embebido'])) $_GET['embebido'] = $_POST['embebido'];
ob_start();
@session_start;
require_once(dirname(__FILE__)."/../comun/funciones.php");
require(dirname(__FILE__)."/../comun/conexion.php");
//echo encriptar($_SESSION['id_usuario'].$_GET['enc']);
$token = encriptar($_POST['id_estudiante'].$_POST['id_cue']);
if ($token != $_POST['token']){
    echo "Token invalido";
    exit();
}
$respuestas = array();
$respuestas['pregunta'] = $_POST['id_cue'];
$respuestas['id_actividad'] = $_POST['id_actividad'];
$tipo = $_POST['tipo'];
foreach ($tipo as $id_tipo=>$value_tipo){
    if($value_tipo=="checkbox"){
        $_POST['pregunta'][$id_tipo] = json_encode($_POST['pregunta'][$id_tipo]);
    }
}
array_walk($_POST['pregunta'], 'escape_string');
$respuestas['respuesta'] = json_encode($_POST['pregunta']);
@session_start;
$respuestas['id_estudiante'] = $_POST['id_estudiante'];
$respuestas['fechayhora'] = date("Y-m-d H:i:s");
$sql = insertar($respuestas,"respuesta");
$consulta = $mysqli->query($sql);
if ($mysqli->affected_rows > 0){
    ?>
    <script>alert2('Respuesta registrada con exito')</script>
    <?php if (!isset($_POST['embebido'])){ ?>
    <meta http-equiv="refresh" content="3;url=index.php"/>
    <?php
    }else{
        echo "Su cuestionario fué guardado con éxito.";
    }
}else{
    ?>
    <script>alert2('Ocurró un error al registrar','error')</script>
     <?php if (!isset($_POST['embebido'])){ ?>
    <meta http-equiv="refresh" content="3;url=index.php"/>
    <?php
     }else{
    ?>
    <meta http-equiv="refresh" content="3;url=ver_cuestionario.php?embebido&a=<?php echo $_POST['id_actividad']; ?>&enc=<?php echo $_POST['id_cue']; ?>"/>
    <?php
    }
}
$contenido = ob_get_clean();
require("../comun/plantilla.php");
?>