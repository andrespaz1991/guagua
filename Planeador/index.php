<?php ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("../comun/autoload.php");
require_once("../comun/conexion.php");
?>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    new ClipboardJS('.copy-button');
});

const elementCache = new Map();

function getElement(id) {
    if (!elementCache.has(id)) {
        elementCache.set(id, document.getElementById(id));
    }
    return elementCache.get(id);
}

async function completarPlanClase() {
    const guiaIA = getElement("guia_ia")?.textContent?.trim() || "";
    let loadingMessage = getElement("loadingMessage");

    let matriz = [
        { input: 'objetivo', contexto: guiaIA, accion: "Genera unicamente el texto del objetivo de la clase basado en la guía (no debe haber nada más que solo lo que te pido en este momento), ejemplo: Profundizar en la comprensión del concepto de proyecto de vida a través de actividades prácticas " },
        { input: 'browser', contexto: '', accion: "Genera unicamente nombre de la estrategia pedagógicas más adecuada  y en qué consiste esa estrategia teniendo en cuenta el objetivo (no debe haber nada más que solo lo que te pido en este momento, sin textos previos ve al grano)  ejemplo: Aprendizaje basado en proyectos: Los estudiantes  crearán un 'mapa del tesoro personal como herramienta de visualización. " },
        { input: 'contenidoHidden', contexto: '', accion: 'Genera unicamente  el texto de los momentos de clase (No debe superar los 2000 caracteres) basado en el objetivo y estrategias (no debe haber nada más que solo lo que te pido en este momento) ejemplo: Inicio :Creación de un ambiente positivo: Se iniciará la clase con una actividad motivadora y un mensaje de reconocimiento por  el esfuerzo de los estudiantes.Explicación del proceso: Se detallará cómo se realizará la entrega de notas y la retroalimentación.Reflexión inicial: Se invitará a los estudiantes a reflexionar sobre sus aprendizajes más significativos durante el período.Desarrollo :Entrega de notas individual: Se entregarán las notas y se proporcionará retroalimentación específica a cada estudiante.Celebración de logros: Se realizarán actividades para reconocer los logros individuales y grupales (ej. entrega de diplomas, menciones honorificas, etc.).Reflexión guiada: Se realizarán preguntas y actividades para que los estudiantes reflexionen sobre su proceso de aprendizaje y establezcan metas para el futuro.Fin :Compartir metas: Los estudiantes compartirán sus metas para el futuro y se motivarán mutuamente.Mensaje de cierre: Se dará un mensaje de cierre positivo y motivador, resaltando la importancia del emprendimiento en la vida.Cierre: Se realizará una actividad simbólica para cerrar el período (ej. aplausos, fotos grupales, etc.).' },
        { input: 'recursos', contexto: '', accion: "Genera estrictamente el texto del el recursos fisicos para la clase (no debe haber nada más que solo lo que te pido en este momento sin textos previos ve al grano) ejemplo: Papel, lápices de colores, marcadores, tijeras " },
        { input: 'reflexion', contexto: '', accion: "Genera unicamente el texto de la reflexión pedagógica basada en el contenido y recursos (no debe haber nada más que solo lo que te pido en este momento,sin textos previos ve al grano) ejemplo: Es fundamental crear un ambiente de creatividad " }
    ];
    
    for (let i = 0; i < matriz.length; i++) {
        let item = matriz[i];
        let valorActual = getElement(item.input)?.value.trim() || "";
        
        if (!valorActual) {
            let instruccion = item.accion + (i > 0 ? matriz[i - 1].contexto : "");
            let respuesta = await consultaIA(guiaIA, instruccion);
            
            if (respuesta) {
                getElement(item.input).value = respuesta;
                matriz[i].contexto = respuesta;
                if (item.input === 'contenidoHidden') {
                    let editor = document.querySelector(".ql-editor");
                    if (editor) {
                        editor.innerHTML = respuesta;
                    }
                }
                await new Promise(resolve => setTimeout(resolve, 500)); // Pequeña pausa antes de continuar
            }
        }
    }
    if (loadingMessage) loadingMessage.style.display = "none"; // Ocultar mensaje cuando termina

    console.log("Matriz Generada:", matriz);
}

async function consultaIA(guia, instruccion) {
    const body = JSON.stringify({ action: 'getData', guia, instruccion });

    console.log("Enviando solicitud a consulta_ia.php:", body);

    try {
        const response = await fetch('http://localhost/guagua/Planeador/consulta_ia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: body
        });

        const data = await response.json();
        console.log("Respuesta recibida:", data);

        if (data.nombre && data.nombre.content) {
            return data.nombre.content.trim();
        } else {
            console.error("Error: No se encontró 'content' en la respuesta.");
            return "";
        }
    } catch (error) {
        console.error("Error en la consulta IA:", error);
        return "";
    }
}

  </script>


<?php
$mat=new Materias();
$miplaneacion=new Planeacion();
$academico=new Academico();
if(!empty($_GET['asignacion'])){
$materia=( $academico->consultar_materia($_GET['asignacion']));
$miplaneacion->materia2=$materia[0]->id_asignatura;
$mismaterias=(json_decode($mat->consultar_materias()));
$periodo="2";
#$grado = extraerTextoEntreParentesisValida($materia[0]->nombre_materia);
$grado=$materia[0]->nombre_categoria_curso;
#echo "Texto extraído: $textoExtraido"; // Salida: Texto extraído: con contenido
$nombre=strtolower(Comun::eliminar_sobrante($materia[0]->nombre_materia));
if($nombre=="artística"){
 $nombre="Educación Artistica"; 
}
if($nombre=="ed. fisica"){
  $nombre="Educación Física";
}
if($nombre=="tecnologia"){
  $nombre="Tecnología e informática";
}

}

if(!empty($_GET['idplan'])){
  $sql_vallesol='SELECT
  *
FROM
  planeador_Vallesol
  inner join dba on planeador_Vallesol.dba= dba.descripcion_dba
  inner join estandar on dba.id_estandar= estandar.id_estandar

WHERE
  id_plan="'.$_GET['idplan'].'"  order by fecha_inicio asc limit 1';
  $accion='modificar';
  echo "<h1 align='center'>Modificar</h1>";
}
if(!empty($_GET['asignacion']) and empty($_GET['edit']) or (!empty($_GET['asignacion']))){
  $sql_vallesol= "
  SELECT
     *
  FROM
      `estandar` AS e
  INNER JOIN materia_oficial AS m ON e.id_materia_oficial = m.id_materia
  INNER JOIN dba AS d ON e.id_estandar = d.id_estandar
  LEFT JOIN eje_tematico AS et ON d.id_dba = et.id_dba
  LEFT JOIN evidencia_de_aprendizaje AS ea ON d.id_dba = ea.id_dba
  WHERE
      (LOWER(m.nombre_materia) = '".$nombre."')
      AND e.id_periodo = '$periodo'
      AND e.grado LIKE '%$grado%'
  GROUP BY
      ea.id_evidencia_aprendizaje, e.id_estandar
  ORDER BY
  id_evidencia_aprendizaje DESC,    
  m.nombre_materia ASC,
      e.grado ASC,
      ea.id_evidencia_aprendizaje  limit 1;
  ";
  #echo $sql_vallesol;
  echo "<h1 align='center'>Ingresar Planeación</h1>";
 $accion='ingresar';
#echo $sql_vallesol;
 #echo "<h1 align='center'>Ingresar</h1>";

}

#echo $sql_vallesol;

function horas($asignacion,$fecha_inicio="2010-11-01",$fecha_fin="2025-12-31"){
  require ("../comun/conexion.php");
 $sql=" SELECT id_asignacion, SUM(horas) AS horas_semanales FROM ( SELECT id_asignacion, TIMESTAMPDIFF(HOUR, hora_inicio, hora_fin) AS horas FROM horario WHERE id_asignacion = '".$asignacion."' AND fecha_inicio >= '".$fecha_inicio."'  AND fecha_fin <= '".$fecha_fin."' ) AS subquery GROUP BY id_asignacion";

  
  $consulta=$mysqli->query($sql);
$data=[];
while($row=$consulta->fetch_assoc()){
  if(!empty($row['horas_semanales'])){
    return $row['horas_semanales'];
  }else{
    return 2;
  }
   }
}
#echo $sql_vallesol;
$consulta=$mysqli->query($sql_vallesol);
$data=[];

?>
    <div class="col-md-3" style="margin-top:4%;margin-bottom:4%">
     <?php require_once 'template/menu.php'; 
     ?>

</div>
<script>
function copiarPrompt() {
  const textarea = document.getElementById("promptTextarea");
  textarea.select();
  document.execCommand("copy");
  window.getSelection().removeAllRanges(); // Deseleccionar el texto después de copiar
 // alert("¡El prompt ha sido copiado al portapapeles!"); // Opcional: Mostrar una confirmación
}
</script>
<?php
$data2=[];

while($row=$consulta->fetch_assoc()){ 
  if(!empty($_GET['idplan'])){
 # echo "hola!!!!!!!!!!!!";
 $data2=$row;
 $data['grado_estandar']=$row['grado'];
  $data['id_periodo']=$row['periodo'];
  $data['nombre_materia_oficial']=$row['materia'];
  $data['descripcion_dba']=$row['descripcion_dba'];
  $data['nombre_estandar']=$row['nombre_estandar'];
  $data['nombre_eje_tematico']=$row['eje_tematico'];
  $data['fecha_inicio']=$row['fecha_inicio'];
  $data['fecha_fin']=$row['fecha_fin'];
  $data['descripcion_evidencia']=$row['observaciones'];
  $data['estrategias']=$row['estrategias'];
  $data['id_materia']=($row['id_materia_oficial']);
  #$data['id_materia']=;
  $data['objetivo']=$row['objetivo'];
  $data['momentos']=trim($row['observaciones']);
  $data['recursos']=trim($row['recursos']);
  $data['reflexion']=trim($row['reflexion']);
  

}else{

  #echo "hola2!!!!!!!!!!!!";
  $data=$row;
 # echo "<pre>";
 # print_r($data);
 # echo "</pre>";
}
$descripcion_dba = $data['descripcion_dba'];
$data_materia=$academico->consultar_materia($_GET['asignacion']);
$nombre_materia=$data_materia[0]->nombre_materia;
echo "
<br>
     <br>
     <br>";
 echo '<p>
 <p>
  
</p>

  <a style="margin-left:20%;margin-top:2%" class="btn btn-success copy-button" data-toggle="collapse" data-clipboard-target=".copy-container pre" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
    Planeador con IA
  </a>
   <a style="margin-left:2%;margin-top:2%" class="btn btn-success" onclick="copiarPrompt()" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
    Prompt
  </a>
</p>
  <a style="postion:relative;margin-left:35%;margin-top:-7%" class="btn btn-primary copy-button"  onclick="completarPlanClase()">
    Guagua IA
  </a>
  <div class="collapse" id="collapseExample">
  <div class="card card-body">
  <textarea id="promptTextarea" autofocus rows="15" cols="130"> ';
  echo 'Eres un experto en pedagogía dialógica especializado en el sistema educativo colombiano. Tu misión es asistir a docentes en el diseño de planes de clase interactivos centrados en el estudiante, siguiendo los lineamientos oficiales educativos de Colombia.

CONTEXTO DEL DOCENTE:
- Soy un profesor colombiano que busca crear experiencias de aprendizaje significativas.
- Necesito diseñar planes de clase que integren los principios del aprendizaje activo, colaborativo y reflexivo .
- Mis planes deben alinearse con los Derechos Básicos de Aprendizaje (DBA) y estándares nacionales.

INSTRUCCIONES:
Cuando solicite un componente específico del plan de clase, proporciona ÚNICAMENTE el contenido solicitado sin texto introductorio ni explicativo adicional. Los componentes posibles son:

1. "objetivo": Genera un objetivo de aprendizaje claro, medible y alineado con el DBA y el eje temático.
2. "estrategia": Proporciona el nombre de una estrategia pedagógica dialógica adecuada y una breve descripción de cómo implementarla (máximo 2 líneas).
3. "momentos": Estructura detallada de la clase en tres fases: Inicio, Desarrollo y Cierre. Incluye actividades específicas que promuevan el diálogo , la participación y la reflexión crítica en caso de ser necesario. No debe exceder 2000 caracteres y no especifiques el tiempo por cada momento.
4. "recursos": Lista concisa de materiales físicos y digitales necesarios para implementar efectivamente las actividades propuestas.
5. "reflexion": Breve reflexión pedagógica (máximo 3 líneas) sobre el valor formativo de la clase propuesta y su alineación con principios dialógicos.
6. "completo": Genera el plan completo incluyendo todos los componentes anteriores en formato estructurado.

IMPORTANTE:
- Adapta todas las propuestas al contexto colombiano y al nivel cognitivo de estudiantes del grado especificado.
- Prioriza actividades que fomenten el diálogo, la construcción colectiva del conocimiento y el desarrollo del pensamiento crítico.
- Considera aspectos de inclusión y diversidad en el aula colombiana.
- Ten en cuenta la propuesta del docente y formalizala

INFORMACIÓN DEL PLAN DE CLASE:';
$texto_prompt="  
1) Grado: ".$data['grado']."
2) Periodo: ".$data['id_periodo']." 
3) Nombre materia: ".$nombre_materia." 
4) DBA (Derecho básico de aprendizaje): ".$data['descripcion_dba']." 
5) Nombre_estandar: ".$data['nombre_estandar']." 
6) Eje tematico: ".$data['nombre_eje_tematico']." " ;
echo $texto_prompt;
echo 'propuesta del docente:
yo propongo un plan de clase en el que ';

echo '</textarea>
  </div>
</div>
<div class="collapse" id="collapseExample">
  <div class="card card-body">';    
echo "<div class='copy-container'>



<div style='display:none' id='guia_ia2'>
<pre>";
#echo $sql_vallesol;


$texto="Eres un experto en pedagogía dialógica. Actúas como un colaborador para docentes que implementan este modelo educativo. Mi rol es el de un profesor colombiano que busca diseñar planes de clase  interactivos y centrados en el estudiante. Necesito tu apoyo para:
Optimizar el diseño de mis planes de clase: Asegurando que exista una excelente redacción e  integren de manera efectiva los principios del aprendizaje activo, colaborativo y reflexivo.
Profundizar el enfoque pedagógico: Proporcionando estrategias y recursos que fomenten el diálogo significativo, la construcción colectiva del conocimiento y el desarrollo del pensamiento crítico en mis estudiantes.
Adaptar los planes de clase a contextos específicos: Considerando las necesidades y características particulares de mis estudiantes y el entorno educativo.
A continuación, te proporcionaré el contexto específico del plan de clase que necesito desarrollar, y te solicito que me asistas en completar el dato requerido, siempre desde una perspectiva pedagógica dialógica y reflexiva. Ten en cuenta que todo ello debe ir orientado teniendo en cuenta lo siguiente:";
$texto.="  
1) Grado: ".$data['grado']."
2) Periodo: ".$data['id_periodo']." 
3) Nombre materia: ".$nombre_materia." 
4) DBA (Derecho básico de aprendizaje): ".$data['descripcion_dba']." 
5) Nombre_estandar: ".$data['nombre_estandar']." 
6) Eje tematico: ".$data['nombre_eje_tematico']." " ;
echo $texto;
}
echo "</pre></div></div>

<textarea id='guia_ia' name='guia_ia' rows='15' cols='130'>
'".$texto."'
</textarea>
"; ?>

<?php

if(!empty($_GET['asignacion'])){
#echo $_GET['asignacion'];
$_GET['materia']=$materia[0]->id_asignatura;
$_GET['grado']=$materia[0]->nombre_categoria_curso;
$miorden= $miplaneacion->ultimo_plan($_GET['materia'],$_GET['grado']);
if(!empty($miorden[0])){
 $miorden= ($miorden[0]->orden_plan)+1;
}else{
  $miorden=1;
}
}
?>
  </div>
</div>
 <div class="container">
  <div class="row">
  <form id="editorForm" method="post" action="">
 
  <input type="hidden" name="id_plantilla" value="<?php if(isset($_GET['idplan'])){ echo $_GET['idplan']; } ?>">
  <div class="col-md-12">
  <div align="center" id="resultado" class="text-bg-warning  p-3">Planeador   
  <div id="loadingMessage" style="display: none;">Generando contenido...</div>
  <div id="loadingSpinner" style="display: none;">
    <div class="spinner"></div>
</div>
<style>
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: auto;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

</div>
  <style>
.exito {
  color: green;
  font-weight: bold;
}
.error {
  color: red;
  font-weight: bold;
}
</style>
<script>
document.getElementById('fecha_inicio').addEventListener('change', validarFechas);
document.getElementById('fecha_fin').addEventListener('change', validarFechas);

function validarFechas() {
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFin = document.getElementById('fecha_fin').value;
    var resultado = document.getElementById('resultado');

    if (fechaInicio && fechaFin) { // Verifica que ambos campos estén llenos
        var inicio = new Date(fechaInicio);
        var fin = new Date(fechaFin);

        if (inicio > fin ) {
          fechaFin= fechaInicio;  
          alert('⚠️ La fecha de fin no puede ser menor que la fecha de inicio.');
            resultado.classList.remove('exito');
            resultado.classList.add('error');
        } else {
            resultado.innerHTML = '✔️ Fechas válidas.';
            resultado.classList.remove('error');
            resultado.classList.add('exito');
        }
    }
}


function verificarPlan() {
    document.getElementById('resultado').style.cssText = '';

    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFin = document.getElementById('fecha_fin').value;
    var materia = document.getElementById('asignacion').value;
    var grado = document.getElementById('grado').value;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'validar_plan.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 400) {
            var respuesta = xhr.responseText;
            var resultado = document.getElementById('resultado');

            if (respuesta === 'exito') {
                resultado.innerHTML = 'Plan existente. &#9888;';
                resultado.classList.remove('exito'); // Elimina clase verde
                resultado.classList.add('error'); // Agrega rojo
            } else {
                resultado.classList.remove('error'); // Elimina rojo
                resultado.classList.add('exito'); // Agrega verde
                resultado.innerHTML = 'Planeador &#10004;';
            }
        } else {
            document.getElementById('resultado').innerHTML = 'Error en la petición. &#9888;';
        }
    };

    xhr.onerror = function() {
        document.getElementById('resultado').innerHTML = 'Error de conexión. &#9888;';
    };

    xhr.send('fechaInicio=' + encodeURIComponent(fechaInicio) + 
             '&fechaFin=' + encodeURIComponent(fechaFin) + 
             '&materia=' + encodeURIComponent(materia) + 
             '&grado=' + encodeURIComponent(grado));
}

</script>
</div>
<hr ></hr>
<div class="col-md-6">
<label>Fecha inicio  </label> <?php #print_r($data2['fecha_fin']); ?>
<input onchange="verificarPlan();validarFechas();" class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="<?php
if(!empty($data2['fecha_inicio'])){
echo  $data2['fecha_inicio'];
}else{
  echo date('Y-m-d');
}
echo '"'; ?>" 
?> </div>
<input name='accion' type='hidden' value=<?php echo $accion;  ?>></input>
<div class="col-md-6">
<label>Fecha fin  </label>
<?php #echo $data['fecha_inicio']; ?> 
<input onchange="verificarPlan();validarFechas();"  class="form-control" type="date" id="fecha_fin" name="fecha_fin"  value="<?php
if(!empty($data2['fecha_fin'])){
echo  $data2['fecha_fin'];
}else{
  echo date('Y-m-d');
}
echo '"'; ?>" 
?> 
</div>

<div class="col-md-3">
<?php $grados = $miplaneacion->consultar_grado();  ?>

<label>Grado <?php #echo $data2['grado']; ?></label>
<select  id="grado" class="form-control" id="grado" name="grado">
<?php foreach ($grados as $key => $value) {    ?> 
<option
<?php 
if(isset($_GET['grado']) and $_GET['grado']==$value[1]){
  echo "selected";
}

if(isset($data2['grado']) and $data2['grado']==$value[1]){
  echo "selected";
}


elseif (isset($planeacion->grado) and $planeacion->grado==$value[1]){
  echo "selected";
} 
?> value="<?php echo $value[1]; ?>"><?php echo $value[1]; ?></option> <?php
 } ?>
         </select>
        </div>


<div  class="col-md-3">
<input type="hidden" id="asignacion" name="asignacion" value="<?php echo $_GET['asignacion']?>">
<label>Materia</label>
        <select id="materias" class="form-control"  name="materia">
<?php       
foreach($mismaterias as $campo => $valor){ ?>
        <option
<?php if(isset($planeacion->materia) and $planeacion->materia==$valor[0] or ($miplaneacion->materia2==$valor[0])){
  echo "selected";
}
if(isset($data2['materia']) and $data2['materia']==$valor[0]){
  echo "selected";
}


?>        value="<?php echo $valor[0]; ?>"><?php echo $valor[1]; ?></option>
<?php } ?>
         </select>
</div>
<div class="col-md-3"> 
<label>Periodo</label>       
<input id="periodo" class="form-control" type="number" name="periodo" value="<?php 
if(!empty($data['id_periodo'])) echo $data['id_periodo']; ?>">
</div>

<div class="col-md-3">
    <label>Tiempo  </label>
<input id="tiempo" placeholder="2 horas" class="form-control" type="text" name="tiempo_plan" value="<?php
#$estrategia=$data2['estrategias'];

if(!empty($data['tiempo'])){
echo $data['tiempo'];
}else{
  echo horas($_GET['asignacion']);

}

 ?>">

</div>
</div>
<div class="col-md-12" align="center" style="color:black">        
   <label><a  target="_blank" href="referente/taxonomia.png">
<font  color=#000000> <a href="TAXONOMiA.jpg" target="_blank">Plan A </font></a></label></div>
<div class="col-md-6">  
 <div  class="control-group" id="DBA">
<label>DBA</label>
<?php
#echo "<pre>";
#print_r($data);
#echo "</pre>";
$opciones = array($data['nombre_dba']);
// Generar el datalist
if(empty($data['nombre_dba'])){
  $data['nombre_dba']='';
}
echo "<input id='dba' title='".$data['nombre_dba']."' value='".$data['nombre_dba']."' name='dba' placeholder='Manifiesta actitud de goce ante el descubrimiento de sus condiciones de inventiva musical' class='form-control' list='dba'>";
echo "<datalist id='dba'>";
foreach ($opciones as $opcion) {
    echo "<option value='$opcion'>";
}
echo "</datalist>";
?>
</div>
</div>
<div class="col-md-6">        

<label>Evidencias de aprendizaje </label>
<?php
$opciones2 = array($data['descripcion_evidencia']);
// Generar el datalist
echo "<input id='evidencias' title='".$data['descripcion_evidencia']."' value='".trim($data['descripcion_evidencia'])."' name='evidencias' placeholder='Produce pequeñas composiciones o propuesta musicales de diferente índole ' class='form-control' list='evidencias'>";
echo "<datalist id='evidencias'>";
foreach ($opciones2 as $opcion) {
echo "<option value='$opcion'>";
}
echo "</datalist>";
?>

</div>
<div class="col-md-12">        

<label>Ejes tematicos </label>
<?php
// Generar el datalist
echo "<input id='eje_tematico' title='".$data['nombre_eje_tematico']."' value='".trim($data['nombre_eje_tematico'])."' name='eje_tematico' placeholder='robotica, ofimatica  ' class='form-control' >";
echo "</input>";
?>

</div>

<div class="col-md-6" style="display: inline;">

  <?php
  $id_materia = $data['id_materia'];
  $sql_Estrategias = 'SELECT * FROM estrategias';
  $consulta_estrategia = $mysqli->query($sql_Estrategias);
  $opciones2 = array();
  while ($row_estrategias = $consulta_estrategia->fetch_assoc()) {
    $nombre_corto = $row_estrategias['estrategia'];
    $descripcion = $row_estrategias['descripcion_estrategia'];
    $opciones2[] = array('nombre' => $nombre_corto, 'descripcion' => $descripcion);
  }
  ?>
  
  <label>Estrategias de clase</label>
<input placeholder="Estrategia" list="browsers" class="form-control" name="browser" id="browser">

<datalist id="browsers">
<?php foreach ($opciones2 as $opcion) : ?>
  <option title="<?php echo $opcion['nombre'].':'.$opcion['descripcion']; ?>" data-value="<?php echo $opcion['nombre'].':'.$opcion['descripcion']; ?>" value="<?php echo $opcion['nombre'].':'.$opcion['descripcion']; ?>">
  <?php endforeach; ?>

</datalist>

</div><div class="col-md-6" style="display: inline;">        
         <label>Objetivo de clase</label>
<input id='objetivo' name="objetivo" placeholder="OBJETIVO:  Exploración y experimentación de sus habilidades creativas en el ámbito musical" class='form-control' type="text" value="<?php
if(!empty($data['objetivo'])){
  echo $data['objetivo'];
}
 ?>" />
      </div>

       

<!--div class="col-md-4">        

        <label>Estrategias</label>

        <?php 


if(!empty($planeacion->estrategiaa)){

$estrategia = explode(",",$planeacion->estrategiaa);

foreach ($estrategia as $clave => $valor) { 

if($valor<>""){

  echo "<script>adicionar('estrategia','$valor','$actualizar');</script>"; }

   }

 } 

?>

</div>

      </div-->

     
</hr>

<hr>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor con Quill.js</title>
    <link href="../comun/js/css/quill.snow.css" rel="stylesheet">
    <script src="../comun/js/quill.js"></script>
    <script src="../comun/js/jquery-3.6.0.min.js"></script>
</head>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<div class="col-md-12">
<span>Momentos</span>
<button id="restaurar" type="button">Restaurar Contenido</button>

<input id="contenidoHidden" type="hidden" name="contenido" value="">
<div id="editor">
  <p></p>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
  const quill = new Quill('#editor', {
    theme: 'snow'
  });

  const STORAGE_KEY = "editorContenido";

  // Función para actualizar el input oculto y guardar en localStorage
  function actualizarContenido() {
    const contenido = quill.root.innerHTML.trim();
    document.getElementById('contenidoHidden').value = contenido;

    // Guarda solo si hay contenido válido
    if (contenido !== "" && contenido !== "<p><br></p>") {
      localStorage.setItem(STORAGE_KEY, contenido);
    } else {
      localStorage.removeItem(STORAGE_KEY);
    }
  }

  // Escucha cambios en el editor y guarda automáticamente
  quill.on('text-change', actualizarContenido);

  // Restaurar contenido solo cuando se presiona el botón
  document.getElementById("restaurar").addEventListener("click", function() {
    const contenidoGuardado = localStorage.getItem(STORAGE_KEY);
    if (contenidoGuardado) {
      quill.root.innerHTML = contenidoGuardado;
      console.log("Contenido restaurado.");
    } else {
      console.warn("No hay contenido guardado.");
    }
  });
</script>




</html>

                <div class="col-md-6">        
        <label>Recursos</label>
     <input id='recursos' name="recursos" placeholder="Computador,Tijeras" class="form-control" type="text" value="<?php if(!empty($data['recursos'])){
                echo $data['recursos'];
              }
              ?>
     ">
</div>
                <div class="col-md-6"> 
<label>Reflexión pedagogica</label>
<input id='reflexion' name="reflexion" placeholder="La música es una forma de expresión importante para los estudiantes" class="form-control" type="text" value="<?php if(!empty($data['recursos'])){
                echo $data['reflexion'];
              }
              ?>"></input>
                </div>             

          
<div class="col-md-5">  

 <div style="display:none"  class="control-group" id="red">

<label>Red</label>

<!--input type="hidden" id="countred" value="1"-->

<!--input class="form-control" list="red"  autocomplete="off"  autofocus="" class="form form-control" value="<?php   echo $valor; ?>"  class="inputred" id="red1" name="red[]" type="text" placeholder="red" /-->

<button id="addred" onclick="adicionar('red');" class="btn add-more btn-danger" type="button">+</button>

</div>

</div>

<script type="text/javascript">

  function llenarred(red){
          var inputs = $('input[name^=red]');
          var count = inputs.length;
          var inputasignar=count;
var b = document.getElementById(red); 
var estado = b.getAttribute("activo");
if(estado=="off"){
    document.getElementById('red'+inputasignar).value=red;
document.getElementById('red'+inputasignar).setAttribute("red",red);
      document.getElementById('addred').click();
     obj = document.getElementById(red);
b.setAttribute("activo", "on");  
obj.style.backgroundColor='#CCCCCC';
}
if(estado=="on"){
  var totalpara=document.getElementById("countred").value;  
for (var i=1;i<totalpara;i++)
    {
      if(document.getElementById("red"+i).value==red){
        document.getElementById('removered'+i).click();
      }
       }
    b.setAttribute("activo", "off"); 
  obj.style.backgroundColor='#FAFBFC';
}
  }
</script>



<?php 



require_once("../comun/config.php");
#require_once("../comun/autoload.php");
require (SGA_COMUN_SERVER.'/conexion.php');
/*
$sqlmateria='select id_asigG143natura from asignacion,materia where
asignacion.id_asignatura = materia.id_materia and asignacion.id_asignacion="'.$_GET['asignacion'].'"';
$consultan = $mysqli -> query($sqlmateria) ;
while ($rowa = $consultan ->fetch_assoc()){ 
mired($rowa['id_asignatura'],$parametro_buqueda="",$campo="",$planeacion->red);
}
*/
function mired($id_materia,$parametro_buqueda,$campo,$red=""){

require '../comun/conexion.php';

#require '../comun/autoload.php';

require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");

$persona=new Persona($_SESSION['id_usuario']);

require_once '../comun/funciones.php';









$sql="SELECT * FROM `red` WHERE CHARACTER_LENGTH((JSON_SEARCH(`materia_red`, 'all',$id_materia)))>3";

if ($parametro_buqueda!=""){

$sql.= ''; 

$parametro_buqueda_array = explode(" ",$parametro_buqueda);

foreach ($parametro_buqueda_array as $id => $parametro_buquedai){

$tabla='red';

if($campo=="nombre"){ $tabla ='usuario' ;}

if($campo=="nombre_materia"){ $tabla ='materia' ;}

if($campo=="nivel_eductivo"){ $parametro_buquedai = '["'.$parametro_buquedai.'"]' ;

}

$sql.= " and concat(LOWER(".$tabla.".".$campo.")) LIKE '%".mb_strtolower($parametro_buquedai, 'UTF-8')."%' ";

}

}

$consultan = $mysqli -> query($sql) ;

#$pagination->records($consultan->num_rows);



#$sql .=  " LIMIT ".(($orden - 1)*$records_per_page) . ", " .$records_per_page;





#echo $sql;



$consultan = $mysqli -> query($sql) ;

$resultados[] = $consultan->num_rows;



$materia="";

$cat="";

$nivel_educativo_estudiante ="";

if($_SESSION['rol']=="estudiante" or $_SESSION['rol']=="acudiente" ){

$año_lectivo = ano_lectivo();

$nivel_educativo_estudiante = nivel_educativo_de_estudiante($_SESSION['id_usuario'],$año_lectivo);

}

while ($rowa = $consultan ->fetch_assoc()){ 

/*

$niveles = json_decode($rowa['nivel_eductivo']);

 $valor_alto = max($niveles);

*/

if( $_SESSION['rol']=='admin' or  $_SESSION['rol']=='docente'){$pertinencia = 1;}

else {$pertinencia = 0;}

if ($materia!=$rowa['materia_red']){

    $materia=$rowa['materia_red'];

    $estado_materia=true;

}else{

    $estado_materia=false;

}

if($cat!=$rowa['materia_red']){

    $cat=$rowa['materia_red'];

    $estado_cat=true;

}else{

    $estado_cat=false;

}

if($estado_materia==true){ ?>

</div>

<?php }

if($estado_materia==true){?>
<script type="text/javascript" src="../comun/js/funciones.js"></script>

    <div  class="col-sm-12">
        <div class="row"><div>
        <div id="tooglemateria"  style="align:center;background-color:#f2721d;height:5px; "><span style="float:right;opacity:0.7"><?php echo " Resultados Encontrados:".$consultan->num_rows; ?></span></div>
       <p title='clic para desplegar' align="center" onclick="mitoogle('#materia')" >Agregar recursos educativos para <?php
 $materia_nombre=$materia;

 #require_once ("../comun/autoload.php");

$instanciamaterias=new Materias($id_materia);

        echo $instanciamaterias->nombre_materia; ?></p>

    <?php if(!isset($actual)) $actual="#id_".$materia; ?>

    </div>

</div></div>



<?php } ?>

<?php


if($estado_materia==true){ //Controla toogle?>
        <p onclick="mitoogle('#cat_<?php echo $materia.$cat; ?>')" class="Abckids"><?php if(isset($rowa['nombre_categoria_curso'])) echo $rowa['nombre_categoria_curso']; ?></p>



<div id="materia" class="cats" >

<?php }

if($pertinencia ==1){



 ?>



<script>

$(function(){

    $.contextMenu({

        selector: '.context-menu-one', 

        trigger: 'hover',

        delay: 500,

        callback: function(key, options) {

    if(key=="Nuevo RED"){window.location='../red/nuevo_red.php';}

    if(key=="Estadisticas"){window.location='../reportes/RED/estadisticas_red.php';}

    

            var m = "clicked: " + key;

        //    window.console && console.log(m) || alert(m); 

        },

        items: {

            <?php if($_SESSION['rol']=='admin' or $_SESSION['rol']=='docente' ) { ?>

            "Nuevo RED": {name: "Nuevo RED"},

            <?php } ?>

            "Estadisticas": {name: "Estadisticas"},

            "sep1": "---------",

            "Salir": {name: "Salir"}

        }

    });

});

//document.getElementById('txt_buscar_red').focus();

function menu_contextual(red,nombre,formato){

 $.contextMenu({

            selector: '.f_inicio'+red, 

            callback: function(key, options) {

             if(key=="Modificar"){

               

            window.location='../red/nuevo_red.php?id_red='+red;

}



if(key=="Descargar"){

            window.location='../comun/funciones.php?ruta_red='+red;

}



if(key=="materia"){

  window.open('../red/visor_red.php?red='+red, '_blank');



//            window.open = "../red/visor_red.php?red=red,'_blank'" ;

}





             if (key=="Eliminar"){

               var confirmar = window.confirmeliminar2("¿Está seguro que desea eliminar "+nombre+" ?");

    if (confirmar) {

                                 window.location='../comun/funciones.php?elred='+red;



    }

             

             }

            },

            items: {

            "materia": {name: nombre, icon: ""},

            "sep1": "---------",

                "Descargar": {name: "Descargar", icon: "edit"},



                "Modificar": {name: "Modificar", icon: "edit"},

                "Eliminar": {name: "Eliminar", icon: "delete"},

                "sep2": "---------",

                "quit": {name: "Salir", icon: function(){

                    return 'context-menu-icon context-menu-icon-quit';

                }}

            }

        });



        $('.f_inicio').on('click', function(e){

            console.log('clicked', this);

        })    

}



</script>

 <div ondblclick="window.open('../red/visor_red.php?red=<?php echo $rowa['id_red']; ?>', '_blank');" activo="off" onclick="llenarred('<?php echo $rowa['id_red']; ?>');" onContextMenu="menu_contextual('<?php echo $rowa['id_red']; ?>','<?php echo  $rowa['titulo_red']; ?>.<?php echo  $rowa['formato']; ?>');" onclick="location.href = '../red/visor_red.php?red=<?php echo $rowa['id_red']; ?>&formato=<?php echo $rowa['formato']; ?>&enlace=<?php echo $rowa['enlace']; ?>&scorm=<?php echo $rowa['scorm']; ?>' "  <?php

 @session_start();

 if($rowa['responsable']==$_SESSION['id_usuario'] or $_SESSION['id_usuario']=="admin" ){ ?> 

 onContextMenu="menu_contextual('<?php echo $rowa['id_red']; ?>''<?php echo  $rowa['titulo_red']; ?>.<?php echo  $rowa['formato']; ?>');" <?php } ?> style="width:160px;margin-bottom:15px;" id="<?php echo $rowa['id_red']; ?>" name="red" align="center" class="col-sm-2 f_inicio<?php echo $rowa['id_red']; ?>">

   <?php mis_red_favoritos($rowa['id_red'], $rowa['estrellas']); ?>

        <h3 title="<?php echo $rowa['titulo_red'] ; ?>" ><strong><?php   $rowa['nivel_eductivo'] = str_replace("[", "", $rowa['nivel_eductivo']);$rowa['nivel_eductivo'] = str_replace("]", "", $rowa['nivel_eductivo']);

        $rowa['nivel_eductivo'] = str_replace('"','', $rowa['nivel_eductivo']);

        echo Comun::puntos_suspensivos($rowa['titulo_red'],15); ?></strong></h3>

<img style="width:50px;margin-right:40px"  class="img-responsive" align="right" style="margin-top:-5%;max-width: 100%;" width="15%" src="<?php echo   consultar_link_icono($rowa['icono_red']); ?>        

"></img>
   <!--span style="background-size: 40px 40px;margin-top:-10px;margin-left:-20px;"   title = " <?php echo $rowa['descripcion'].'Nivel Educativo:'.$rowa['nivel_eductivo'].', Monedas para ver:'.$rowa['cantidad_estrellas'];  ?>" class="<?php echo $rowa['icono_red']; ?>"/-->
        <?php 
              
                 
        
        if($persona->puntos>=$rowa['cantidad_estrellas'] or $_SESSION['rol']=='admin' or $_SESSION['rol']=='admin'){  } ?>

<div>

</div>

</div> 





<?php

} //fin validación de pertinencia del nivel de formación del recurso para el estudiante y acudiente 

  $acumulador_de_resultados_consulta[]=$resultados;

  } 
  if(isset($_GET['id'])){
  foreach(json_decode($red) as $clave => $valor){
    echo "<script>
    document.getElementById('$valor').click();
    </script>";
    }
}
  
?>

<br>

<div  class="text-center col-sm-12">

    <?php  #  $pagination->render();    ?>

    </div>

</div>
<?php
     
}

?>



</div>

     

                <div class="col-md-12">        

                <label>Estoy Seguro

<input type="checkbox" name="seguro" required/>

                </label></div>

                <div class="col-md-12">        

<input class="btn btn-success" type="submit" name="guardar" value="guardar">

            </div></div>



        </div>

  </div>

</div>

</form>



  <?php
if(!empty($_POST['seguro'])){

#echo "<pre>";
#print_r($_POST);
#echo "</pre>";
$_POST['fecha_creacion']=date('Y-m-d');
$patronComillas = '"'; // Patrón para comillas dobles
$_POST['dba'] = str_replace($patronComillas,'', $_POST['dba']);
$_POST['estrategia'] = str_replace($patronComillas,'', $_POST['browser']); //estrategia
$_POST['evidencias'] = str_replace($patronComillas,'', $_POST['evidencias']);
$_POST['observaciones'] = str_replace($patronComillas,'', $_POST['contenido']);
$_POST['recursos'] = str_replace($patronComillas,'', $_POST['recursos']);
$_POST['reflexion'] = str_replace($patronComillas,'', $_POST['reflexion']);



// Función para guardar el contenido
#echo "<pre>";
#print_r($_POST);
#echo "</pre>";

// Leer contenido si se proporciona un ID
if (isset($_POST['accion']) && $_POST['accion'] == "ingresar") {
  if (isset($_GET['id'])) {
      $contenidoLeido = leerContenido($_GET['id']);
  }
    // Consulta para insertar en planeador_vallesol
  $sql = "INSERT INTO `planeador_vallesol` (
      `fecha_inicio`,
      `fecha_fin`,
      `grado`,
      `materia`, 
      `periodo`, 
      `tiempo_plan`, 
      `dba`, 
      `estrategias`, 
      `evidencias`, 
      `observaciones`, 
      `recursos`,
      `reflexion`,
      `eje_tematico`,
      `objetivo`
  ) VALUES (
      '" . $_POST['fecha_inicio'] . "',
      '" . $_POST['fecha_fin'] . "',
      '" . $_POST['grado'] . "',
      '" . $_POST['asignacion'] . "',
      '" . $_POST['periodo'] . "',
      '" . $_POST['tiempo_plan'] . "',
      '" . trim($_POST['dba']) . "',
      '" . trim($_POST['browser']) . "',
      '" . trim($_POST['evidencias']) . "',
      '" . trim($_POST['contenido']) . "',
      '" . $_POST['recursos'] . "',
      '" . trim($_POST['reflexion']) . "',
      '" . trim($_POST['eje_tematico']) . "',
      '" . $_POST['objetivo'] . "'
  )";

  // "Limpiar" la consulta para que no contenga comillas (alternativa: escapar usando real_escape_string)
  $consulta_limpia = str_replace(["'", '"'], "", $sql);
  // Si deseas conservar las comillas pero escaparlas, usa:
  // $consulta_limpia = $mysqli->real_escape_string($sql);
  
  // Construir la consulta para auditoría. 
  // Se encierran entre comillas los campos de texto (por ejemplo, texo_sql y observaciones)
  $sql_auditoria = "INSERT INTO `auditoria_planeador_vallesol` (`texo_sql`, `materia`, `grado`, `observaciones`) VALUES (
      '" . $consulta_limpia . "',
      " . $_POST['asignacion'] . ",
      " . $_POST['grado'] . ",
      '" . $_POST['observaciones'] . "'
  )";
  

  // Ejecutar la consulta de auditoría
  if (!$mysqli->query($sql_auditoria)) {
      echo "Error en auditoría: " . $mysqli->error;
  }
  

  if($mysqli->query($sql)){
      $sql = urldecode($sql);
      setcookie("sql_query", $sql, time() + 3600, "/"); // Cookie válida por 1 hora

    ?>
 <script>
    // Función para copiar al portapapeles
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                title: "SQL copiado!",
                text: "La consulta SQL ha sido copiada al portapapeles",
                icon: "success"
            });
        }).catch(function(err) {
            console.error('Error al copiar: ', err);
        });
    }

    // Obtener SQL de la cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
    }

    // SQL almacenado en variable
    const sqlQuery = getCookie("sql_query");

    // Mostrar modal con botón de copia
    Swal.fire({
        title: "Registro exitoso!",
        html: `
            <div style="margin-bottom: 15px">
                La operación se completó correctamente.
            </div>
            <div style="text-align: center">
                <button id="copySqlBtn" class="btn btn-success">
                    <i class="fas fa-copy"></i> Copiar SQL
                </button>
            </div>
        `,
        icon: "success",
        didRender: () => {
            // Agregar evento al botón dentro del modal
            document.getElementById('copySqlBtn').addEventListener('click', function() {
                copyToClipboard((sqlQuery));
            });
        }
    });

    // OPCIÓN ALTERNATIVA: Copiar automáticamente al portapapeles
    // Descomenta estas líneas si prefieres esa opción
    // copyToClipboard(sqlQuery);
</script>
<script src="../comun/js/sweetalert.min.js" integrity="sha512-MqEDqB7me8klOYxXXQlB4LaNf9V9S0+sG1i8LtPOYmHqICuEZ9ZLbyV3qIfADg2UJcLyCm4fawNiFvnYbcBJ1w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<style>
  body {
  font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; 
}
  </style>
    <?php
   echo "";
   echo '<div class="alert alert-success" role="alert">
    <h4 class="alert-heading">Well done!</h4>
    <p>Aww yeah, you successfully read this important alert message. This example text is going to run a bit longer so that you can see how spacing within an alert works with this kind of content.</p>
    <hr>
    <p class="mb-0">Registro exitoso.</p>
  </div>';
    echo '<meta http-equiv="refresh" content="4; url=index.php?asignacion='.$_GET['asignacion'].'" />';  
  }else{
    ?>
    <script>
      Swal.fire({
  title: "Registro Incorrecto!",
  text: "<?php echo $sql; ?>",
  icon: "warning"
});
    </script>
    <?php
    #echo "<script>alert2('registro incorrecto');window.location='index.php'</script>";

  echo '<meta http-equiv="refresh" content="4; url=index.php?asignacion='.$_GET['asignacion'].'" />';
  }
  
}

if (isset($_POST['accion']) && $_POST['accion'] == "modificar") {
  #if (isset($_GET['id'])) {
    #$contenidoLeido = leerContenido($_GET['id']); // Puedes usar esto si necesitas leer el contenido actual

    // Consulta de actualización (UPDATE)
    $sql = "UPDATE `planeador_vallesol` SET
      `fecha_creacion` = '" . $_POST['fecha_creacion'] . "',
      `fecha_inicio` = '" .$_POST['fecha_inicio'] . "',
      `fecha_fin` = '" .$_POST['fecha_fin'] . "',
      `grado` = '" .$_POST['grado'] . "',
      `materia` = '" .$_POST['asignacion'] . "',
      `periodo` = '" .$_POST['periodo'] . "',
      `tiempo_plan` = '" . $_POST['tiempo_plan'] . "',
      `dba` = '" . trim($_POST['dba']) . "',
      `estrategias` = '" .trim($_POST['estrategia']) . "',
      `evidencias` = '" .trim($_POST['evidencias']) . "',
      `observaciones` = '" .trim($_POST['contenido']) . "',
      `recursos` = '" . $_POST['recursos'] . "',
      `reflexion` = '" . trim($_POST['reflexion']) . "',
      `eje_tematico` = '" . trim($_POST['eje_tematico']) . "',
      `objetivo` = '" .$_POST['objetivo'] . "'
      WHERE `id_plan` = " .$_POST['id_plantilla'];
echo $sql;
exit();
    // Ejecuta la consulta y muestra el mensaje correspondiente
    if ($mysqli->query($sql)) {
      echo '<div class="alert alert-success" role="alert">
        <h4 class="alert-heading">¡Actualización exitosa!</h4>
        <p>El plan ha sido actualizado correctamente.</p>
        <hr>
        <p class="mb-0">Actualización completada con éxito.</p>
      </div>';
      echo '<meta http-equiv="refresh" content="1; url=index.php?asignacion=' . $_GET['asignacion'] . '" />';
    } else {
      echo '<div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Error en la actualización</h4>
        <p>Ocurrió un problema al intentar actualizar el registro.</p>
        <hr>
        <p class="mb-0">Actualización fallida.</p>
      </div>';
      echo '<meta http-equiv="refresh" content="1; url=index.php?asignacion=' . $_GET['asignacion'] . '" />';
    }
# } else {
/*   
echo '<div class="alert alert-warning" role="alert">
      <h4 class="alert-heading">ID no encontrado</h4>
      <p>No se pudo encontrar el ID del plan a actualizar.</p>
    </div>'; */
 # }
}
exit();

}




$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");

?>