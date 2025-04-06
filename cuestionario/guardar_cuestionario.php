<?php
echo "<pre>";
print_r($_POST);
echo "</pre>";
exit();
$_GET['embebido']=true;
require ("../comun/conexion.php");
require_once("../comun/funciones.php");
$cuestionario = array();
$cuestionario['id'] = $_POST['id'];
$cuestionario['nombre'] = $_POST['nombre_cuestionario'];
$cuestionario['tipo_cuestionario'] = $_POST['tipo_cuestionario'];
array_walk($cuestionario, 'escape_string');
$sql_cuestionario = "UPDATE `cuestionario` SET `nombre`='".$cuestionario['nombre']."', `tipo_cuestionario`='".$cuestionario['tipo_cuestionario']."' WHERE `id`=".$cuestionario['id'];
$sql_cuestionario = insertar($cuestionario,"cuestionario",true);
$mysqli->query($sql_cuestionario);
$preguntas_cuestionario = array();
$preguntas_cuestionario['id_preguntas_cuestionario'] = $_POST['id_preguntas_cuestionario'];
$preguntas_cuestionario['id'] = $_POST['id'];
$preguntas_cuestionario['texto_pregunta'] = $_POST['texto_pregunta']['nombre'];#array
$preguntas_cuestionario['tipo_pregunta'] = $_POST['tipo_pregunta'];#array
$opciones_aux = $_POST['texto_pregunta']['txtop'];#array//solo para recorrer las preguntas
$preguntas_opciones = array();
$preguntas_aux = $preguntas_cuestionario['texto_pregunta'];
#echo "<pre>";
#print_r($preguntas_aux);
#echo "</pre>";
if (count($preguntas_aux)>0)
foreach ($preguntas_aux as $id => $nombre_pregunta){
$preguntas_cuestionario_sql['id_preguntas_cuestionario'] =  $preguntas_cuestionario['id_preguntas_cuestionario'][$id];
$preguntas_cuestionario_sql['id_cuestionario'] = $preguntas_cuestionario['id'];
$preguntas_cuestionario_sql['pregunta'] = $nombre_pregunta;
$preguntas_cuestionario_sql['tipo_pregunta'] = $preguntas_cuestionario['tipo_pregunta'][$id];
$preguntas_opciones = $_POST['texto_pregunta']['txtop'][$id];
$preguntas_cuestionario_sql['opciones'] = json_encode($preguntas_opciones);
if (isset($_POST['texto_pregunta']['op'][$id])){
$preguntas_opciones_c = $_POST['texto_pregunta']['op'][$id];
$preguntas_cuestionario_sql['correctas'] = json_encode($preguntas_opciones_c);
}
$sqlp = insertar($preguntas_cuestionario_sql,"preguntas_cuestionario",true);
#echo "$sqlp<br><br>";
$mysqli->query($sqlp);
#echo $preguntas_cuestionario_sql['correctas'] ;
}
#echo "<pre>";
#print_r($_POST);
#echo "</pre>";
exit();
ob_start();
if ($mysqli->affected_rows>0){
?>
<script>
    alert2("Registro Exitoso");
</script>
<?php
}
?>
<script>
<?php  if($_POST['embebido']=="NO"){ ?>
    document.location.href = "index.php";
<?php } ?>

<?php  if($_POST['embebido']=="SI"){ ?>
    document.location.href = "ver_cuestionario.php?embebido&enc=<?php echo $cuestionario['id'] ?>";
<?php } ?>
</script>
<?php
 $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>