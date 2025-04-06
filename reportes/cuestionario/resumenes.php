<?php 
@session_start();
$_SESSION['barra_busqueda']="";
require_once(dirname(__FILE__)."/../../comun/config.php");
require_once(dirname(__FILE__)."/../../comun/funciones.php");
require(dirname(__FILE__)."/../../comun/conexion.php");
function preparar_datos_grafica($array,$datos,$cualitativo=true){
    $salidas=array();
    $datos_a = explode(",",$datos);
    $array2=array();
    if ($cualitativo){
        foreach ($array as $i => $valor){
            foreach ($valor as $j => $dato){
                $var_u = $valor[$datos_a[0]]." ".$dato;
                @$$var_u++;
            }
            $array2[$var_u]=$$var_u;
        $array = $array2;
        }
        echo "2:<br>";
        echo "<pre>";
        print_r($array2);
        echo "</pre>";
        echo "<hr>";
        foreach ($array as $i => $valor){
            $var_u == $i.$valor;
            @$$var_u++;
        }
    }
    foreach ($array as $i => $valor){
        #foreach ($valor as $j => $dato){
            $salidas[] = '{ "cualitativo":" '.$i.'", "cuantitativo":"'.$valor.'" }';
        #}
    }
    $salida = implode(',',$salidas);
    return $salida;
}

ob_start();
?>
<script type="text/javascript" src="../../comun/js/jquery.js"></script>
<script type="text/javascript" src="../../comun/js/jsapi.js"></script>
<script type="text/javascript" src="../../comun/js/uds_api_contents.js"></script>
<script>

function Graficooffline2(titulo,cualitativa,cuantitativa,contenedor,tipo_grafica,ancho,alto,datos) {
var objeto = [[cualitativa,cuantitativa]];
for (id in datos.elementos){objeto[objeto.length]=[datos.elementos[id].cualitativo, parseInt(datos.elementos[id].cuantitativo)];}
var data = google.visualization.arrayToDataTable(objeto);//Cerramos la creación de la variable datodocument.write();s
eval('new google.visualization.'+tipo_grafica+'(document.getElementById("'+contenedor+'")).       draw(          data,{title:"'+titulo+'",width: "'+ancho+'",height:"'+alto+'",});');
}
function preparar_grafica(datos,nombre,contenedor,contenedorpadre,tipo_grafica){
var salida = '';
salida += 'var datos'+contenedor+' = {"elementos": ['+datos+']} ;';
salida += 'google.setOnLoadCallback(function() {'
salida += 'Graficooffline2(titulo="Grafica de Cuestionarios",cualitativa="Opcíón",cuantitativa="'+nombre+'",contenedor="'+contenedor+'",tipo_grafica="'+tipo_grafica+'",ancho="700px",alto="200px",datos'+contenedor+');';
salida += '});';
var plantilla = document.createElement('div');
plantilla.innerHTML = '<div id="'+contenedor+'" style="width: 500px; height: 300px;"></div>';
var area_contenedorpadre = document.getElementById(contenedorpadre);
area_contenedorpadre.appendChild(plantilla.firstChild);
eval (salida);
}
</script>
<div id="graficas"></div>
<?php
/*
Descripción de consulta:
Esta consulta $sql1 consulta las actividades que tienen cuestionarios del año lectivo marcado como vigente
*/
if(isset($_GET['ano_lectivo']))
$id_ano_lectivo = $_GET['ano_lectivo'];
else
$id_ano_lectivo = consultar_id_ano_lectivo();

$sql1 = "SELECT `actividad`.`id_actividad`, `actividad`.`id_asignacion`, `actividad`.`fecha_publicacion`, `actividad`.`hora_publicacion`, `actividad`.`id_red`, `actividad`.`nombre_actividad`, `actividad`.`Observaciones`, `actividad`.`adjunto`, `actividad`.`evaluable`, `actividad`.`fecha_entrega`, `actividad`.`hora_entrega`, `actividad`.`periodo`, `actividad`.`visible`, `actividad`.`cuestionario`, `actividad`.`id_cuestionario`, `actividad`.`foro`, `actividad`.`id_foro` FROM `actividad` inner join  `cuestionario` on  `cuestionario`. `id` = `actividad`.`id_cuestionario` inner join  `asignacion_academica` on  `actividad`. `id_asignacion` = `asignacion_academica`.`id_asignacion` WHERE `asignacion_academica`.`ano_lectivo` =".$id_ano_lectivo;
#echo $sql1;

foreach (consultar_datos($sql1,true) as $row1){
   # echo $row1['nombre_actividad'];
}
#echo "<pre>";
#print_r($datos_cuestionario_actividad);
#echo "</pre>";
#echo "<hr>";
$sql2 = "SELECT `cuestionario`.`id`, `cuestionario`.`nombre`, CONCAT( '[', GROUP_CONCAT( CONCAT( '[', `respuesta`.`id_estudiante`, ', ', '\"', `respuesta`.`estado_respuesta`, '\"', ']') ORDER BY `respuesta`.`id_estudiante` ASC SEPARATOR ','), ']' ) AS datos_respuesta FROM `cuestionario` JOIN `respuesta` ON `cuestionario`.`id` = `respuesta`.`pregunta` GROUP BY `cuestionario`.`id`";
$sql22 = "SELECT `cuestionario`.`nombre` as Actividad,`respuesta`.`estado_respuesta` as Estado FROM `cuestionario` JOIN `respuesta` ON `cuestionario`.`id` = `respuesta`.`pregunta`";
resultados_graficar_tabla(consultar_datos($sql22,true),'Actividad,Estado','Pendiente,No Aprobado,Aprobado');
#$sql3 = "SELECT * FROM usuario";
#$resutados = consultar_datos($sql3,true);
#echo "<pre>";
/*
$resutados = consultar_datos($sql2,true);
#print_r($resutados);
#echo "</pre>";
#echo "<br>";
$datos_cuestionario_actividad=array();
foreach ($resutados as $row2){
    #echo $row2['nombre'];
    #echo "<br>";
    
#echo "<pre>";
#print_r($row2);
#echo "</pre>";
    $datosa = json_decode($row2['datos_respuesta'],true);
    foreach ($datosa as $rows_respuestas){
        $datos_cuestionario_actividad[] = array("Actividad"=>$row2['nombre'],"Estudiante"=>$rows_respuestas[0],"Estado"=>$rows_respuestas[1]);
    }
}
*/
#echo "<pre>";
#print_r($datos_cuestionario_actividad);
#echo "</pre>";
#echo "<hr>";
#$datos_grafica = preparar_datos_grafica($datos_cuestionario_actividad,'Actividad,Estado');
#$resutados = consultar_datos($sql2,true);
#resultados_graficar_tabla($datos_cuestionario_actividad,'Actividad,Estado','No Aprobado,Aprobado,Pendiente');
echo "<hr>";
$sql23 = "SELECT `rol`,`tipo_sangre` FROM `usuario`";
resultados_graficar_tabla(consultar_datos($sql23,true),'rol,tipo_sangre');
echo "<hr>";
$sql24 = "SELECT `grupo_foro`.`nombre_grupo` as Foro, `entrada`.`contenido` as `Comentarios por Tema` FROM `grupo_foro` inner join `entrada` on `entrada`.`grupo` = `grupo_foro`.`id_grupo_foro` inner join `comentario` on `comentario`.`id_entrada` = `entrada`.`id` group BY  `comentario`.`id_comentario`";
resultados_graficar_tabla(consultar_datos($sql24,true),'Foro,Comentarios por Tema');

function ordenar_arreglo(&$desorden,$orden){
/*
//http://www.forosdelweb.com/f18/como-ordenar-array-segun-otro-array-1017795/*/ /*$orden = array('juan','pedro','alejando','alberto','jesus','alfredo');$desorden = $orden;/* "desordenamos" *//* shuffle($desorden);//vemos como se desordenaron echo 'Array ordenado '.implode(',',$orden).'</br>';echo 'Array desordenado '.implode(',', $desorden).'</br>'; */
//volvemos a ordenar
$ordenado = array();
 
$numItems = count($orden);
$numItems = count($desorden);
 
for($i=0; $i<$numItems; $i++) {
    $buscar = array_search($orden[$i], $desorden);
 
    if ($buscar !== false) {
        $ordenado[] = $desorden[$buscar];
    }
}
$desorden = $ordenado;
}
$contenido = ob_get_clean();
require(dirname(__FILE__)."/../../comun/plantilla.php");
?>