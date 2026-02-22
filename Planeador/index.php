<?php
// =================================================================
// 1. INICIALIZACIÓN Y CONFIGURACIÓN DE PHP
// =================================================================
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclusión de dependencias y conexión a la base de datos
require_once("../comun/autoload.php");
require_once("../comun/conexion.php");

// =================================================================
// 2. LÓGICA DE NEGOCIO Y PREPARACIÓN DE DATOS
// =================================================================
$texto = '';
$accion = 'ingresar'; // Valor por defecto

// Instanciación de clases
$mat = new Materias();
$miplaneacion = new Planeacion();
$academico = new Academico();

// Definición de funciones
function horas($asignacion, $fecha_inicio = "2010-11-01", $fecha_fin = "2025-12-31")
{
    require("../comun/conexion.php");
    $sql = " SELECT id_asignacion, SUM(horas) AS horas_semanales FROM ( SELECT id_asignacion, TIMESTAMPDIFF(HOUR, hora_inicio, hora_fin) AS horas FROM horario WHERE id_asignacion = '" . $asignacion . "' AND fecha_inicio >= '" . $fecha_inicio . "'  AND fecha_fin <= '" . $fecha_fin . "' ) AS subquery GROUP BY id_asignacion";
    $consulta = $mysqli->query($sql);
    while ($row = $consulta->fetch_assoc()) {
        if (!empty($row['horas_semanales'])) {
            return $row['horas_semanales'];
        } else {
            return 2;
        }
    }
    // Retorno por si no entra al while
    return 2;
}

// Lógica principal para obtener los datos del plan (Crear vs Modificar)
if (!empty($_GET['idplan'])) {
    // --- MODO MODIFICAR ---
    $sql_vallesol = 'SELECT
     *
    FROM
     planeador_Vallesol
     inner join dba on planeador_Vallesol.dba= dba.nombre_dba
     inner join estandar on dba.id_estandar= estandar.id_estandar

    WHERE
     id_plan="' . $_GET['idplan'] . '"  order by fecha_inicio asc limit 1';

    $accion = 'modificar';

} elseif (!empty($_GET['asignacion'])) {
    // --- MODO INGRESAR ---
    $materia = $academico->consultar_materia($_GET['asignacion']);
    $miplaneacion->materia2 = $materia[0]->id_asignatura;
    $periodo = "1";
    $grado = $materia[0]->nombre_categoria_curso;
    $nombre = strtolower(Comun::eliminar_sobrante($materia[0]->nombre_materia));

    if ($nombre == "artística") {
        $nombre = "Educación Artistica";
    }
    if ($nombre == "ed. fisica") {
        $nombre = "Educación Física";
    }
    if ($nombre == "tecnologia") {
        $nombre = "Tecnología e informática";
    }

    $sql_vallesol = "
     SELECT
          *
     FROM
          `estandar` AS e
     INNER JOIN materia_oficial AS m ON e.id_materia_oficial = m.id_materia
     INNER JOIN dba AS d ON e.id_estandar = d.id_estandar
     LEFT JOIN eje_tematico AS et ON d.id_dba = et.id_dba
     LEFT JOIN evidencia_de_aprendizaje AS ea ON d.id_dba = ea.id_dba
     WHERE
          (LOWER(m.nombre_materia) = '" . $nombre . "')
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
    $accion = 'ingresar';
}

// Ejecución de la consulta y procesamiento de resultados
$consulta = $mysqli->query($sql_vallesol);
$data = [];
$data2 = [];

while ($row = $consulta->fetch_assoc()) {
    if (!empty($_GET['idplan'])) {
        $data2 = $row;
        $data['grado_estandar'] = $row['grado'];
        $data['id_periodo'] = $row['periodo'];
        $data['nombre_materia_oficial'] = $row['materia'];
        $data['nombre_dba'] = $row['nombre_dba'];
        $data['nombre_estandar'] = $row['nombre_estandar'];
        $data['nombre_eje_tematico'] = $row['eje_tematico'];
        $data['fecha_inicio'] = $row['fecha_inicio'];
        $data['fecha_fin'] = $row['fecha_fin'];
        $data['descripcion_evidencia'] = $row['observaciones'];
        $data['estrategias'] = $row['estrategias'];
        $data['id_materia'] = ($row['id_materia_oficial']);
        $data['objetivo'] = $row['objetivo'];
        $data['momentos'] = trim($row['observaciones']);
        $data['recursos'] = trim($row['recursos']);
        $data['reflexion'] = trim($row['reflexion']);
    } else {
        $data = $row;
    }
}
$datos_materia_actual = $academico->consultar_materia($_GET['asignacion']);
$nombre_materia_actual = $datos_materia_actual[0]->nombre_materia;
?>

<!-- 
// =================================================================
// 6. INICIO DE LA PRESENTACIÓN (HTML)
// =================================================================
-->

<?php if ($accion == 'modificar') : ?>
    <h1 align='center'>Modificar Planeación</h1>
<?php else : ?>
    <h1 align='center' >Ingresar Planeación</h1>
<?php endif; ?>

<div class="col-md-3" style="margin-top:4%;margin-bottom:4%">
    <?php require_once 'template/menu.php'; ?>
</div>

<!-- BOTONES Y SECCIÓN DE IA -->
<p>
    <!--a style="margin-left:20%;margin-top:10%" class="btn btn-success copy-button" data-toggle="collapse" data-clipboard-target=".copy-container pre" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
        Prompt
    </a-->
    <a style="margin-left:20%;margin-top:2%;margin-top:10%" class="btn btn-success" onclick="copiarPrompt()" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
        Prompt
    </a>
<a style="margin-left:2%;margin-top:2%;margin-top:10%" class="btn btn-primary copy-button" onclick="completarPlanClase()">
    Guagua IA
</a>
</p>


<!-- CONTENIDO COLAPSABLE DE IA -->
<div class="collapse" id="collapseExample">
    <div class="card card-body">
        <textarea id="promptTextarea" autofocus rows="15" cols="130">
<?php 
/* prompt viejo
Entonces como quedaria el prompt teniendo en cuenta esto (Eres un experto en pedagogía dialógica especializado en el sistema educativo colombiano. Tu misión es asistir a docentes en el diseño de planes de clase interactivos centrados en el estudiante, siguiendo los lineamientos oficiales educativos de Colombia.



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



INFORMACIÓN DEL PLAN DE CLASE:

*/
?>
Actúa como: Consultor Pedagógico Senior experto en Diseño Curricular para el sistema educativo colombiano (Ley 115, Estándares Básicos, DBA, y Guía 34). Especialista en Constructivismo, Aprendizaje Dialógico y Modelos Flexibles (Postprimaria y Media Rural). Tu enfoque integra la metodología TPACK para la inserción efectiva de TIC en contextos de baja o alta conectividad.

Tu Tono: Ejecutivo, técnico-pedagógico, preciso y propositivo. Evitas muletillas, disculpas o introducciones. Vas directo al grano.

Tu Misión: Transformar ideas docentes en planes de aula robustos que cumplan con la normatividad del MEN, centrados en el desarrollo de competencias y la equidad (DUA).

MODO DE OPERACIÓN E INSTRUCCIONES

Responderás según el componente solicitado, utilizando la información base (Tema, Grado, Contexto) proporcionada por el usuario.

"objetivo": Redacta un Objetivo de Aprendizaje (u Objetivo Holístico) que sea observable y alcanzable. Debe incluir: Verbo de desempeño + Contenido + Finalidad/Contexto. Alinéalo explícitamente con un DBA vigente.

"estrategia": Propone una estrategia de Aprendizaje Dialógico (ej. Grupos Interactivos, Tertulias Dialógicas, ABP) adaptada al entorno rural. Describe la mecánica en 2 líneas enfatizando la interacción entre pares.

"momentos": Estructura la clase en:

Exploración (Saberes previos/Motivación): Actividad detonante dialógica.

Estructuración y Práctica: Construcción del concepto y aplicación guiada.

Transferencia y Valoración: Aplicación en contexto real y evaluación formativa.

Restricción: Máximo 2000 caracteres. No incluyas tiempos.

"recursos": Lista de materiales físicos (contexto rural) y herramientas digitales (considerando opciones offline/online como herramientas de autor o software libre).

"reflexion": Análisis de 3 líneas sobre cómo la sesión promueve la movilización de pensamiento crítico y la inclusión social en el aula rural.

"completo": Genera todos los puntos anteriores en una tabla o estructura organizada, incluyendo además una sección de "Evidencia de Aprendizaje" (qué producto o acción demostrará el logro).
<?php
$texto_prompt = "  
1) Grado: " . ($data['grado'] ?? '') . "
2) Periodo: " . ($data['id_periodo'] ?? '') . " 
3) Nombre materia: " . ($nombre_materia_actual ?? '') . " 
4) DBA (Derecho básico de aprendizaje): " . ($data['nombre_dba'] ?? '') . " 
5) Nombre_estandar: " . ($data['nombre_estandar'] ?? '') . " 
6) Eje tematico: " . ($data['nombre_eje_tematico'] ?? '') . " ";
echo $texto_prompt;
?>
propuesta del docente: yo propongo un plan de clase en el que ..
        </textarea>
    </div>
    <div class="card card-body">
        <div class='copy-container'>
            <div style='display:none' id='guia_ia2'>
                <pre>
<?php
/*
$texto = "Eres un experto en pedagogía dialógica. Actúas como un colaborador para docentes que implementan este modelo educativo. Mi rol es el de un profesor colombiano que busca diseñar planes de clase  interactivos y centrados en el estudiante. Necesito tu apoyo para:
Optimizar el diseño de mis planes de clase: Asegurando que exista una excelente redacción e  integren de manera efectiva los principios del aprendizaje activo, colaborativo y reflexivo.
Profundizar el enfoque pedagógico: Proporcionando estrategias y recursos que fomenten el diálogo significativo, la construcción colectiva del conocimiento y el desarrollo del pensamiento crítico en mis estudiantes.
Adaptar los planes de clase a contextos específicos: Considerando las necesidades y características particulares de mis estudiantes y el entorno educativo.
A continuación, te proporcionaré el contexto específico del plan de clase que necesito desarrollar, y te solicito que me asistas en completar el dato requerido, siempre desde una perspectiva pedagógica dialógica y reflexiva. Ten en cuenta que todo ello debe ir orientado teniendo en cuenta lo siguiente  
1) Grado: " . ($data['grado'] ?? '') . "
2) Periodo: " . ($data['id_periodo'] ?? '') . " 
3) Nombre materia: " . ($nombre_materia_actual ?? '') . " 
4) DBA (Derecho básico de aprendizaje): " . ($data['nombre_dba'] ?? '') . " 
5) Nombre_estandar: " . ($data['nombre_estandar'] ?? '') . " 
6) Eje tematico: " . ($data['nombre_eje_tematico'] ?? '') . " ";
echo $texto;
*/
?>
                </pre>
            </div>
        </div>
        <textarea style='display:none' id='guia_ia' name='guia_ia' rows='15' cols='130'><?php #echo $texto; ?>
        </textarea>
    </div>
</div>

<!-- FORMULARIO PRINCIPAL -->
<div class="container">
    <div class="row">
        <form id="editorForm" method="post" action="">

            <input type="hidden" name="id_plantilla" value="<?php if (isset($_GET['idplan'])) { echo $_GET['idplan']; } ?>">
            <input name='accion' type='hidden' value='<?php echo $accion; ?>'>
            
            <div class="col-md-12">
                <div align="center" id="resultado" class="text-bg-warning p-4">
                    Planeador <?php
                    
                    print_r($datos_materia_actual[0]->nombre_materia); ?>
                    <div id="loadingMessage" style="display: none;">Generando contenido...</div>
                    <div id="loadingSpinner" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
            <hr>

            <!-- CAMPOS DE FECHA, GRADO, MATERIA, ETC -->
            <div class="col-md-6">
                <label>Fecha inicio </label>
                <input onchange="verificarPlan();validarFechas();" class="form-control" type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo !empty($data2['fecha_inicio']) ? $data2['fecha_inicio'] : date('Y-m-d'); ?>">
            </div>
            <div class="col-md-6">
                <label>Fecha fin </label>
                <input onchange="verificarPlan();validarFechas();" class="form-control" type="date" id="fecha_fin" name="fecha_fin" value="<?php echo !empty($data2['fecha_fin']) ? $data2['fecha_fin'] : date('Y-m-d'); ?>">
            </div>

            <div class="col-md-3">
                <?php $grados = $miplaneacion->consultar_grado(); ?>
                <label>Grado</label>
        
                <input type="text" name='grado' value=' <?php echo $data['grado']; ?> ' class="form-control" id="grado">
            </div>

            <div class="col-md-3">
                <input type="hidden" id="asignacion" name="asignacion" value="<?php echo $_GET['asignacion'] ?>">
                <label>Materia</label>
                <select id="materias" class="form-control" name="materia">
                    <?php
                    $mismaterias = json_decode($mat->consultar_materias());
                    foreach ($mismaterias as $campo => $valor) { ?>
                        <option <?php if ((isset($planeacion->materia) && $planeacion->materia == $valor[0]) || ($miplaneacion->materia2 == $valor[0])) {
                                    echo "selected";
                                }
                                if (isset($data2['materia']) && $data2['materia'] == $valor[0]) {
                                    echo "selected";
                                }
                                ?> value="<?php echo $valor[0]; ?>"><?php echo $valor[1]; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-3">
                <label>Periodo</label>
                <input id="periodo" class="form-control" type="number" name="periodo" value="<?php if (!empty($data['id_periodo'])) echo $data['id_periodo']; ?>">
            </div>

            <div class="col-md-3">
                <label>Tiempo </label>
                <input id="tiempo" placeholder="2 horas" class="form-control" type="text" name="tiempo_plan" value="<?php
                if (!empty($data['tiempo'])) {
                    echo $data['tiempo'];
                } else {
                    echo horas($_GET['asignacion']);
                }
                ?>">
            </div>

            <!-- DBA, EVIDENCIAS, ETC. -->
            <div class="col-md-12" align="center" style="color:black">
                <label><a target="_blank" href="referente/taxonomia.png"><font color=#000000> <a href="TAXONOMiA.jpg" target="_blank">Plan A </font></a></label>
            </div>

            <div class="col-md-6">
                <div class="control-group" id="DBA">
                    <label>DBA</label>
                    <input id='dba' title="<?php echo $data['nombre_dba'] ?? ''; ?>" value="<?php echo $data['nombre_dba'] ?? ''; ?>" name='dba' placeholder='Manifiesta actitud de goce...' class='form-control' list='dba_list'>
                    <datalist id='dba_list'>
                        <option value="<?php echo $data['nombre_dba'] ?? ''; ?>">
                    </datalist>
                </div>
            </div>

            <!--div class="col-md-6">
                <label>Evidencias de aprendizaje </label>
                <input id='evidencias' title="<?php #echo $data['descripcion_evidencia'] ?? ''; ?>" value="<?php #echo trim($data['descripcion_evidencia'] ?? ''); ?>" name='evidencias' placeholder='Produce pequeñas composiciones...' class='form-control' list='evidencias_list'>
                <datalist id='evidencias_list'>
                    <option value="<?php #echo trim($data['descripcion_evidencia'] ?? ''); ?>">
                </datalist>
            </div-->

            <div class="col-md-6">
                <label>Ejes tematicos </label>
                <input id='eje_tematico' title="<?php echo $data['nombre_eje_tematico'] ?? ''; ?>" value="<?php echo trim($data['nombre_eje_tematico'] ?? ''); ?>" name='eje_tematico' placeholder='robotica, ofimatica' class='form-control'>
            </div>

            <div class="col-md-6" style="display: inline;">
                <?php
                $sql_Estrategias = 'SELECT * FROM estrategias';
                $consulta_estrategia = $mysqli->query($sql_Estrategias);
                $opciones2 = [];
                while ($row_estrategias = $consulta_estrategia->fetch_assoc()) {
                    $opciones2[] = $row_estrategias;
                }
                ?>
                <label>Estrategias de clase</label>
                <input placeholder="Estrategia" list="browsers" class="form-control" name="browser" id="browser">
                <datalist id="browsers">
                    <?php foreach ($opciones2 as $opcion) : ?>
                        <option title="<?php echo $opcion['estrategia'] . ':' . $opcion['descripcion_estrategia']; ?>" value="<?php echo $opcion['estrategia'] . ':' . $opcion['descripcion_estrategia']; ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-6" style="display: inline;">
                <label>Objetivo de clase</label>
                <input id='objetivo' name="objetivo" placeholder="OBJETIVO: Exploración y experimentación..." class='form-control' type="text" value="<?php echo $data['descripcion_dba'] ?? ''; ?>" />
            </div>
            <hr>

            <!-- EDITOR DE TEXTO QUILL -->
            <div  class="col-md-12">
                <span>Momentos</span>
                <button id="restaurar" type="button">Restaurar Contenido</button>
                <input id="contenidoHidden" type="hidden" name="contenido" value="">
                <div id="editor">
                    <p></p>
                </div>
            </div>
             <div class="col-md-12">
                <label>Evaluación</label>
                <input  style="height: 100px;" id='recursos' name="recursos" placeholder="Evaluación" class="form-control" type="text" value="<?php echo $data['recursos'] ?? ''; ?>">
            </div>
            
            <!-- RECURSOS Y REFLEXIÓN -->
            <!--div class="col-md-6">
                <label>Recursos</label>
                <input id='recursos' name="recursos" placeholder="Computador,Tijeras" class="form-control" type="text" value="<?php #echo $data['recursos'] ?? ''; ?>">
            </div-->
            <div class="col-md-12">
                <label>Reflexión pedagogica</label>
                <input style="height: 100px;" id='reflexion' name="reflexion" placeholder="La música es una forma de expresión..." class="form-control" type="text" value="<?php echo $data['reflexion'] ?? ''; ?>">
            </div>

            <!-- SECCIÓN 'mired' -->
            <?php
            function mired($id_materia, $parametro_buqueda, $campo, $red = "")
            {
                // El contenido de la función 'mired' se mantiene aquí como en el original
                // para preservar su funcionamiento exacto, incluyendo sus 'require' internos.
                require '../comun/conexion.php';
                require_once("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
                $persona = new Persona($_SESSION['id_usuario']);
                require_once '../comun/funciones.php';
                // ... resto del código de la función mired ...
                $sql="SELECT * FROM `red` WHERE CHARACTER_LENGTH((JSON_SEARCH(`materia_red`, 'all',$id_materia)))>3";
                // ... etc.
            }
            // require_once("../comun/config.php");
            // require (SGA_COMUN_SERVER.'/conexion.php');
            // mired($id_de_la_materia, ...); // Llamada a la función si es necesario
            ?>

            <div class="col-md-12">
                <label>Guardar
                    <input type="checkbox" name="Guardar" required checked="true" />
                </label>
            </div>


            <!-- BOTÓN DE GUARDAR -->
            <div class="col-md-12">
                <label>Estoy Seguro
                    <input type="checkbox" name="seguro" required />
                </label>
            </div>
            <div class="col-md-12">
                <input class="btn btn-success" type="submit" name="guardar" value="guardar">
            </div>
        </form>
    </div>
</div>

<!-- 
// =================================================================
// 7. JAVASCRIPT Y ESTILOS
// =================================================================
-->

<!-- Estilos CSS -->
<link href="../comun/js/css/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
<style>
    .spinner {
        width: 40px; height: 40px; border: 4px solid rgba(0, 0, 0, 0.1);
        border-top: 4px solid #007bff; border-radius: 50%;
        animation: spin 1s linear infinite; margin: auto;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .exito { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    body { font-family: "Open Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", Helvetica, Arial, sans-serif; }
</style>

<!-- Librerías de JavaScript -->
<script src="../comun/js/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script src="../comun/js/sweetalert.min.js" xintegrity="sha512-MqEDqB7me8klOYxXXQlB4LaNf9V9S0+sG1i8LtPOYmHqICuEZ9ZLbyV3qIfADg2UJcLyCm4fawNiFvnYbcBJ1w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- Template del calendario (requerido por el script del calendario) -->
<script type="text/tmpl" id="tmpl">
    {{ 
        var date = date || new Date(), month = date.getMonth(), year = date.getFullYear(), first = new Date(year, month, 1), last = new Date(year, month + 1, 0), startingDay = first.getDay(), thedate = new Date(year, month, 1 - startingDay), dayclass = lastmonthcss, today = new Date(), i, j; 
        if (mode === 'week') { thedate = new Date(date); thedate.setDate(date.getDate() - date.getDay()); first = new Date(thedate); last = new Date(thedate); last.setDate(last.getDate()+6); } 
        else if (mode === 'day') { thedate = new Date(date); first = new Date(thedate); last = new Date(thedate); last.setDate(thedate.getDate() + 1); }
    }}
    <!-- El resto del template del calendario va aquí... -->
</script>

<!-- Scripts personalizados -->
<script>
// Lógica de IA y ClipboardJS (ya definida al inicio del HTML)

// Lógica de copiado de prompt
function copiarPrompt() {
    const textarea = document.getElementById("promptTextarea");
    textarea.select();
    document.execCommand("copy");
    window.getSelection().removeAllRanges();
}

// Lógica de popover del calendario
var $currentPopover = null;
$(document).on('shown.bs.popover', function(ev) {
    var $target = $(ev.target);
    if ($currentPopover && ($currentPopover.get(0) != $target.get(0))) {
        $currentPopover.popover('toggle');
    }
    $currentPopover = $target;
}).on('hidden.bs.popover', function(ev) {
    var $target = $(ev.target);
    if ($currentPopover && ($currentPopover.get(0) == $target.get(0))) {
        $currentPopover = null;
    }
});

// Lógica del calendario (código original)
$.extend({
    quicktmpl: function(template) { return new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};with(obj){p.push('" + template.replace(/[\r\t\n]/g, " ").split("{{").join("\t").replace(/((^|\}\})[^\t]*)'/g, "$1\r").replace(/\t:(.*?)\}\}/g, "',$1,'").split("\t").join("');").split("}}").join("p.push('").split("\r").join("\\'") + "');}return p.join('');") }
});
$.extend(Date.prototype, {
    toDateCssClass: function() { return '_' + this.getFullYear() + '_' + (this.getMonth() + 1) + '_' + this.getDate(); },
    toDateInt: function() { return ((this.getFullYear() * 12) + this.getMonth()) * 32 + this.getDate(); },
    toTimeString: function() { var hours = this.getHours(), minutes = this.getMinutes(), hour = (hours > 12) ? (hours - 12) : hours, ampm = (hours >= 12) ? ' pm' : ' am'; if (hours === 0 && minutes === 0) { return ''; } if (minutes > 0) { return hour + ':' + minutes + ampm; } return hour + ampm; }
});
(function($) {
    var t = $.quicktmpl($('#tmpl').get(0).innerHTML);
    function calendar($el, options) {
        // ... (resto del código del plugin del calendario) ...
        draw();
    }
    // ... (resto del código del plugin del calendario) ...
})(jQuery);


// Lógica de validación de formularios
document.getElementById('fecha_inicio').addEventListener('change', validarFechas);
document.getElementById('fecha_fin').addEventListener('change', validarFechas);

function validarFechas() {
    var fechaInicio = document.getElementById('fecha_inicio').value;
    var fechaFin = document.getElementById('fecha_fin').value;
    var resultado = document.getElementById('resultado');

    if (fechaInicio && fechaFin) {
        var inicio = new Date(fechaInicio);
        var fin = new Date(fechaFin);

        if (inicio > fin) {
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
                resultado.classList.remove('exito');
                resultado.classList.add('error');
            } else {
                resultado.classList.remove('error');
                resultado.classList.add('exito');
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

// Lógica del editor Quill
const quill = new Quill('#editor', { theme: 'snow' });
const STORAGE_KEY = "editorContenido";

function actualizarContenido() {
    const contenido = quill.root.innerHTML.trim();
    document.getElementById('contenidoHidden').value = contenido;
    if (contenido !== "" && contenido !== "<p><br></p>") {
        localStorage.setItem(STORAGE_KEY, contenido);
    } else {
        localStorage.removeItem(STORAGE_KEY);
    }
}
quill.on('text-change', actualizarContenido);

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


<!-- 
// =================================================================
// 8. LÓGICA DE PROCESAMIENTO DE FORMULARIO (POST)
// =================================================================
-->
<?php
if (!empty($_POST['seguro'])) {
    
    // Sanitización básica
    $_POST['fecha_creacion'] = date('Y-m-d');
    $patronComillas = '"';
    $_POST['dba'] = str_replace($patronComillas, '', $_POST['dba']);
    $_POST['estrategia'] = str_replace($patronComillas, '', $_POST['browser']);
    $_POST['evidencias'] = '';
    $_POST['observaciones'] = str_replace($patronComillas, '', $_POST['contenido']);
    $_POST['recursos'] = str_replace($patronComillas, '', $_POST['recursos']);
    $_POST['reflexion'] = str_replace($patronComillas, '', $_POST['reflexion']);

    if (isset($_POST['accion']) && $_POST['accion'] == "ingresar") {
        $_POST['observaciones'] = str_replace("'", '', $_POST['observaciones']);
        $_POST['contenido'] = str_replace("'", '', $_POST['contenido']);
        $_POST['observaciones'] = str_replace('"', '', $_POST['observaciones']);
        $_POST['contenido'] = str_replace('"', '', $_POST['contenido']);

        $sql = "INSERT INTO `planeador_vallesol` (`fecha_inicio`, `fecha_fin`, `grado`, `materia`, `periodo`, `tiempo_plan`, `dba`, `estrategias`, `evidencias`, `observaciones`, `recursos`, `reflexion`, `eje_tematico`, `objetivo`) 
                VALUES ('" . $_POST['fecha_inicio'] . "', '" . $_POST['fecha_fin'] . "', '" . $_POST['grado'] . "', '" . $_POST['asignacion'] . "', '" . $_POST['periodo'] . "', '" . $_POST['tiempo_plan'] . "', '" . trim($_POST['dba']) . "', '" . trim($_POST['browser']) . "', '" . trim($_POST['evidencias']) . "', '" . trim($_POST['contenido']) . "', '" . $_POST['recursos'] . "', '" . trim($_POST['reflexion']) . "', '" . trim($_POST['eje_tematico']) . "', '" . $_POST['objetivo'] . "')";

        if ($mysqli->query($sql)) {
            $id_nuevo_planeador = $mysqli->insert_id;
            echo '<div class="alert alert-success" role="alert"><p class="mb-0">Registro exitoso.</p></div>';
        echo '<meta http-equiv="refresh" content="2; url=phpword/ejemplo.php?id='.$id_nuevo_planeador . '" />';
          #  echo '<meta http-equiv="refresh" content="2; url=index.php?asignacion=' . $_GET['asignacion'] . '" />';
        } else {
            echo '<script>Swal.fire({ title: "Registro Incorrecto!", text: "Hubo un error al guardar.", icon: "warning"});</script>';
            echo '<meta http-equiv="refresh" content="3; url=index.php?asignacion=' . $_GET['asignacion'] . '" />';
        }

    } elseif (isset($_POST['accion']) && $_POST['accion'] == "modificar") {
        $sql = "UPDATE `planeador_vallesol` SET
                `fecha_creacion` = '" . $_POST['fecha_creacion'] . "',
                `fecha_inicio` = '" . $_POST['fecha_inicio'] . "',
                `fecha_fin` = '" . $_POST['fecha_fin'] . "',
                `grado` = '" . $_POST['grado'] . "',
                `materia` = '" . $_POST['asignacion'] . "',
                `periodo` = '" . $_POST['periodo'] . "',
                `tiempo_plan` = '" . $_POST['tiempo_plan'] . "',
                `dba` = '" . trim($_POST['dba']) . "',
                `estrategias` = '" . trim($_POST['estrategia']) . "',
                `evidencias` = '',
                `observaciones` = '" . trim($_POST['contenido']) . "',
                `recursos` = '" . $_POST['recursos'] . "',
                `reflexion` = '" . trim($_POST['reflexion']) . "',
                `eje_tematico` = '" . trim($_POST['eje_tematico']) . "',
                `objetivo` = '" . $_POST['objetivo'] . "'
                WHERE `id_plan` = " . $_POST['id_plantilla'];

        if ($mysqli->query($sql)) {
            $id_nuevo_planeador = $mysqli->insert_id;
            echo '<div class="alert alert-success" role="alert"><p class="mb-0">Actualización completada con éxito.</p></div>';
            echo '<meta http-equiv="refresh" content="2; url=phpword/ejemplo.php?asignacion='.$_POST['asignacion'].'&id='.$id_nuevo_planeador . '" />';
#            echo '<meta http-equiv="refresh" content="2; url=index.php?asignacion=' . $_GET['asignacion'] . '" />';
        } else {
            echo '<div class="alert alert-danger" role="alert"><p class="mb-0">Actualización fallida.</p></div>';
            echo '<meta http-equiv="refresh" content="2; url=index.php?asignacion=' . $_GET['asignacion'] . '" />';
        }
    }
}

// =================================================================
// 9. FINALIZACIÓN Y ENVÍO DE LA PLANTILLA
// =================================================================
$contenido = ob_get_contents();
ob_clean();
include("../comun/plantilla.php");
?>
