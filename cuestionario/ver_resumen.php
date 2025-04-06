<?php
@session_start();
require_once("../comun/funciones.php");
if (!isset($_SESSION['usuario'])){
    header ("Location: ../index.php");
}
ob_start();
if (isset($_POST['enc'])){
    $_GET['enc'] = $_POST['enc'];
}
if (isset($_GET['enc'])){
?>
<title>Respuestas</title>
<script type="text/javascript" src="../comun/js/jquery.js"></script>
<script type="text/javascript" src="../comun/js/jsapi.js"></script>
<script type="text/javascript" src="../comun/js/uds_api_contents.js"></script>
<script type="text/javascript">
    function Graficooffline2(titulo,cualitativa,cuantitativa,contenedor,tipo_grafica,ancho,alto,datos) {
var objeto = [[cualitativa,cuantitativa]];
for (id in datos.elementos){objeto[objeto.length]=[datos.elementos[id].cualitativo, parseInt(datos.elementos[id].cuantitativo)];}
var data = google.visualization.arrayToDataTable(objeto);//Cerramos la creación de la variable datodocument.write();s
eval('new google.visualization.'+tipo_grafica+'(document.getElementById("'+contenedor+'")).       draw(          data,{title:"'+titulo+'",width: "'+ancho+'",height:"'+alto+'",});');
}
</script>
<br>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"><?php echo consultar_nombre_cuestionario($_GET['enc']); ?></h1>      
  </div>
</div>
<section>
    <?php
    require("../comun/conexion.php");
    require_once("../comun/config.php");
    require_once("../comun/funciones.php");
    $sql = "SELECT * FROM `respuesta` WHERE `pregunta` = ".$_GET['enc'];
    $sql2 = "SELECT `id_preguntas_cuestionario`,`pregunta`, `tipo_pregunta` FROM `preguntas_cuestionario` WHERE `id_cuestionario` = ".$_GET['enc'];
    $consulta = $mysqli->query($sql);
    $consulta2 = $mysqli->query($sql2);
    $preguntas = array();
    $tipo = array();
    if ($consulta->num_rows>0){
    while ($row2 = $consulta2->fetch_assoc()){
        $preguntas[$row2['id_preguntas_cuestionario']]=$row2['pregunta'];
        $tipo[$row2['id_preguntas_cuestionario']]=$row2['tipo_pregunta'];
    }
    $respuestas = array();
    $personas_responden = array();
    while ($row = $consulta->fetch_assoc()){
        $respuesta = json_decode($row['respuesta'],true);
        $personas_responden[$row['id']] = array('id_estudiante'=>$row['id_estudiante'],'fechayhora'=>$row['fechayhora']);
        foreach($respuesta as $id => $valor){
            $respuestas[] = $respuesta;
        }
    }
    $totales = (repeatedElements1($respuestas));
    foreach($totales as $id => $total){
    $cantidad = count($total);
    if ($cantidad<=5) $tipo_grafico = 'PieChart';
    else $tipo_grafico = 'ColumnChart';
     $opciones_generales = "{'title':'".$preguntas[$id]."','fontSize': 24,'is3D': true,'width':900,'height':700}";
    echo graficar_respuestas($total,$opciones_generales,'chart_div'.$id,$tipo_grafico);
    }
    }else{
        echo "No hay resultados";
    }
    ?>
<div id="graficas"></div>
</section>
<?php
}
$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>