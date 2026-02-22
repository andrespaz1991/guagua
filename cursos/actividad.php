<?php
// =================================================================
// 1. INICIALIZACIÓN Y CONFIGURACIÓN
// =================================================================
ob_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/Guagua/comun/autoload.php');
require("../comun/conexion.php");
require_once("../comun/config.php");
require_once("../comun/funciones.php");

unset($_SESSION['barra_busqueda']);

// =================================================================
// 2. LÓGICA DE NEGOCIO Y OBTENCIÓN DE DATOS (PREPARED STATEMENTS)
// =================================================================
$id_actividad = $_GET['actividad'] ?? null;
$id_asignacion = $_POST['asignacion'] ?? $_GET['asignacion'] ?? null;
$curso_nombre = $_POST['curso'] ?? $_GET['curso'] ?? '';

$datos_actividad = [];
$portada_asignacion = '';
$categoria = '';

// 2.1 Consultar datos de la actividad
if ($id_actividad) {
    $sql_actividad = "SELECT a.*, r.* FROM actividad a LEFT JOIN red r ON a.id_red = r.id_red WHERE a.id_actividad = ? LIMIT 1";
    if ($stmt = $mysqli->prepare($sql_actividad)) {
        $stmt->bind_param("s", $id_actividad);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($fila = $resultado->fetch_assoc()) {
            $datos_actividad = $fila;
            $id_asignacion = $fila['id_asignacion']; // Priorizar la asignación de la base de datos
        }
        $stmt->close();
    }
}

// 2.2 Consultar curso y portada
if ($id_asignacion) {
    $sql_curso = "SELECT a.portada_asignacion, m.nombre_materia, c.nombre_categoria_curso 
                  FROM asignacion a 
                  INNER JOIN categoria_curso c ON a.id_categoria_curso = c.id_categoria_curso 
                  INNER JOIN materia m ON a.id_asignatura = m.id_materia 
                  WHERE a.id_asignacion = ?";
                  
    if ($stmt_curso = $mysqli->prepare($sql_curso)) {
        $stmt_curso->bind_param("s", $id_asignacion);
        $stmt_curso->execute();
        $res_curso = $stmt_curso->get_result();
        if ($row_curso = $res_curso->fetch_assoc()) {
            $portada_asignacion = $row_curso['portada_asignacion'];
            $curso_nombre = $curso_nombre ?: $row_curso['nombre_materia'];
            $categoria = $row_curso['nombre_categoria_curso'];
        }
        $stmt_curso->close();
    }
}

// 2.3 Consultar periodos disponibles
$periodos = [];
$sql_periodo = 'SELECT * FROM periodo';
if ($res_periodo = $mysqli->query($sql_periodo)) {
    while ($row = $res_periodo->fetch_assoc()) {
        $periodos[] = $row;
    }
}

// Validación de acceso (requiere rol en sesión)
if (!isset($_SESSION['rol'])) {
    echo "Acceso denegado.";
    exit;
}

// Preparar título de materia
$materia = mb_strtoupper($curso_nombre, 'UTF-8');
$materia_recortada = puntos_suspensivos($materia, 20);

// =================================================================
// 3. PRESENTACIÓN HTML (UI/UX)
// =================================================================
?>
<style>
    /* Estilos consolidados para las pestañas y la UI */
    #container_act input[type="radio"] { display: none; }
    
    #container_act input#tab-1:checked ~ #content #content-1,
    #container_act input#tab-2:checked ~ #content #content-2,
    #container_act input#tab-3:checked ~ #content #content-3,
    #container_act input#tab-4:checked ~ #content #content-4 {
        opacity: 1;
        z-index: 100;
        display: block;
    }
    
    #content > div {
        display: none; /* Oculta por defecto los contenidos de los tabs */
        padding: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: -1px;
    }

    .jumbotron-custom {
        background-color: #333;
        color: white;
        background-repeat: no-repeat;
        background-position: left center;
        background-size: cover;
    }
    
    .jumbotron-overlay {
        background-color: rgba(0,0,0,0.5);
        padding: 40px 0;
    }

    .form-group label {
        font-weight: bold;
    }
</style>

<div class="jumbotron jumbotron-custom" <?php if(!empty($portada_asignacion)) echo "style=\"background-image: url('" . SGA_CURSOS_URL . "/" . $portada_asignacion . "');\""; ?>>
    <div class="container text-center jumbotron-overlay">
        <h1 title="<?php echo htmlspecialchars($curso_nombre); ?>" class="fip">
            <?php echo htmlspecialchars($materia_recortada); ?>
        </h1>      
    </div>
</div>

<div class="container-fluid bg-3">    
    <div class="row">
        <section class="col-md-10 col-md-offset-1">
            
            <form id="form_nueva_actividad" action="subir.php" method="POST" enctype="multipart/form-data">
                
                <input id="id_asignacion" name="id_asignacion" type="hidden" value="<?php echo htmlspecialchars($id_asignacion); ?>">
                <input id="id_actividad" name="id_actividad" type="hidden" value="<?php echo htmlspecialchars($id_actividad); ?>">

                <div id="container_act" class="colorear">
                    <!-- TABS HEADER -->
                    <input id="tab-1" type="radio" name="tab-group" checked="checked" />
                    <label for="tab-1" class="btn btn-default"><span class="glyphicon glyphicon-envelope"></span> Detalle Actividad</label>
                    
                    <input id="tab-2" type="radio" name="tab-group" />
                    <label for="tab-2" id="label-tab-2" class="btn btn-default" style="<?php echo (empty($datos_actividad['id_red']) || strtolower($datos_actividad['id_red']) === 'no') ? 'display:none;' : ''; ?>" onclick="focoared();">
                        <span class="glyphicon glyphicon-send"></span> Recurso Digital
                    </label>
                    
                    <input id="tab-3" type="radio" name="tab-group" />
                    <label for="tab-3" id="label-tab-3" class="btn btn-default" style="<?php echo (empty($datos_actividad['cuestionario']) || strtolower($datos_actividad['cuestionario']) === 'no' || $datos_actividad['cuestionario'] === 'NULL') ? 'display:none;' : ''; ?>">
                        <span class="glyphicon glyphicon-star"></span> Cuestionario
                    </label>
                    
                    <input id="tab-4" type="radio" name="tab-group" />
                    <label for="tab-4" id="label-tab-4" class="btn btn-default" style="<?php echo (empty($datos_actividad['foro']) || strtolower($datos_actividad['foro']) === 'no' || $datos_actividad['foro'] === 'NULL') ? 'display:none;' : ''; ?>">
                        <span class="glyphicon glyphicon-star"></span> Foro
                    </label>

                    <!-- CONTENIDO TABS -->
                    <div id="content">
                        
                        <!-- PESTAÑA 1: DETALLE ACTIVIDAD -->
                        <div id="content-1">
                            <div class="form-group">
                                <label for="nombre_actividad">Nombre Actividad:</label>
                                <input type="hidden" value="<?php echo htmlspecialchars($id_asignacion); ?>" name="asignacion" id="asignacion" required />
                                <input class="form-control" placeholder="Nombre.." type="text" name="id" id="nombre_actividad" required value="<?php echo htmlspecialchars($datos_actividad['nombre_actividad'] ?? ''); ?>" />
                            </div>

                            <div class="form-group">
                                <label>Periodo:</label><br> 
                                <?php foreach($periodos as $row_periodo): ?>
                                    <label class="radio-inline">
                                        <input type="radio" name="periodo" value="<?php echo $row_periodo['id_periodo']; ?>" <?php echo (isset($datos_actividad['periodo']) && $datos_actividad['periodo'] == $row_periodo['nombre_periodo']) ? 'checked' : ''; ?>>
                                        <?php echo $row_periodo['nombre_periodo']; ?>
                                    </label>      
                                <?php endforeach; ?>
                                
                                <!-- Opciones por defecto -->
                                <?php for($i=1; $i<=4; $i++): ?>
                                    <label class="radio-inline">
                                        <input type="radio" name="periodo" value="<?php echo $i; ?>" <?php echo (isset($datos_actividad['periodo']) && $datos_actividad['periodo'] == $i) || (!isset($datos_actividad['periodo']) && $i == 1) ? 'checked' : ''; ?>>
                                        <?php echo $i; ?>
                                    </label>
                                <?php endfor; ?>
                            </div>

                            <div class="form-group">
                                <label for="observacion">Descripción</label>
                                <textarea id="observacion" class="form-control" rows="4" name="observacion" placeholder="Observación.."><?php echo htmlspecialchars($datos_actividad['Observaciones'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="fecha_publicacion">Fecha de publicación</label>
                                    <input class="form-control" type="date" name="fecha_publicacion" id="fecha_publicacion" value="<?php echo isset($datos_actividad['fecha_publicacion']) ? date('Y-m-d', strtotime($datos_actividad['fecha_publicacion'])) : date('Y-m-d'); ?>" />
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="hora_publicacion">Hora de publicación</label>
                                    <input class="form-control" type="time" name="hora_publicacion" id="hora_publicacion" value="<?php echo $datos_actividad['hora_publicacion'] ?? date('H:i:s'); ?>" />
                                </div>
                            </div>

                            <hr>
                            
                            <div class="form-group">
                                <label class="checkbox-inline" title="Visualizar un recurso digital adjunto">
                                    <input mostrarocultar='label-tab-2' type="checkbox" id="checkbox2" name="id_red" value="SI" <?php echo !empty($datos_actividad['id_red']) ? 'checked' : ''; ?> onclick="verificar_red(this);limpiar_red();"> 
                                    Habilitar un Recurso Educativo Digital
                                </label>

                                <label class="checkbox-inline" title="Establece los tiempos de entrega y medios de valoración">
                                    <input onchange="verificar_evaluable(this)" mostrarocultar='fechas' type="checkbox" id="checkbox" name="evaluable" value="SI" <?php echo (isset($datos_actividad['evaluable']) && $datos_actividad['evaluable'] == "SI") ? 'checked' : ''; ?>> 
                                    Actividad Evaluable
                                </label>
                               
                                <label class="checkbox-inline" title="Crea un espacio de discusión de la actividad">
                                    <input onclick="mostrarcuestionar();" mostrarocultar='label-tab-4' type="checkbox" id="checkbox_foro" name="foro" value="SI" <?php echo (isset($datos_actividad['foro']) && $datos_actividad['foro'] == "SI") ? 'checked' : ''; ?>> 
                                    Habilitar Foro
                                </label>
                            </div>

                            <!-- Opciones Evaluable -->
                            <div id="eval" style="<?php echo (isset($datos_actividad['evaluable']) && $datos_actividad['evaluable'] == "SI") ? 'display:block;' : 'display:none;'; ?>">
                                <div id="fechas" class="well">
                                    <div class="row">
                                        <div class="col-md-6 form-group">
                                            <label for="fecha_entrega">Fecha de Entrega</label>
                                            <input class="form-control" type="date" id="fecha_entrega" name="fecha_entrega" value="<?php echo htmlspecialchars($datos_actividad['fecha_entrega'] ?? ''); ?>"/>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label for="hora_entrega">Hora de Entrega</label>
                                            <input class="form-control" id="hora_entrega" type="time" name="hora_entrega" value="<?php echo htmlspecialchars($datos_actividad['hora_entrega'] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mt-3">
                                        <label class="checkbox-inline">
                                            <input id="cuestionario" onclick="mostrarcuestionar();" mostrarocultar='label-tab-3' type="checkbox" name="cuestionario" value="SI" <?php echo (isset($datos_actividad['cuestionario']) && $datos_actividad['cuestionario'] == "SI") ? 'checked' : ''; ?>>
                                            Habilitar Cuestionario
                                        </label>
                                        
                                        <label class="checkbox-inline">
                                            <input type="checkbox" id="adjunto" name="adjunto" value="SI" <?php echo (isset($datos_actividad['adjunto']) && strtolower($datos_actividad['adjunto']) == 'si') ? 'checked' : ''; ?>>
                                            Permitir Subir archivo Adjunto
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 2: RECURSO EDUCATIVO DIGITAL -->
                        <div id="content-2">
                            <div id="div_red">
                                <div id="opciones_red" class="mb-3">
                                    <label class="radio-inline">
                                        <input type="radio" name="micheckbox_red" id="micheckbox_no" value="Buscar" checked onclick="if(document.getElementById('micheckbox').checked) document.getElementById('micheckbox').click();"> Buscar Existente
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="micheckbox_red" id="micheckbox_si" value="Nuevo RED" onclick="if(!document.getElementById('micheckbox').checked) document.getElementById('micheckbox').click();"> Nuevo RED
                                    </label>
                                    <!-- Checkbox oculto original para compatibilidad JS -->
                                    <input style="display:none" type="checkbox" id="micheckbox" onclick="uni('<?php echo $id_actividad; ?>');" value="Nuevo RED" />
                                </div>

                                <div id="uni" class="well">
                                    <div class="form-inline">
                                        <div class="form-group">
                                            <label>Buscar: </label>
                                            <input class="form-control" placeholder="ejemplo: plantas" type="text" id="busqueda" name="busqueda" onfocus="mostrarSugerenciaRed(this.value)" onkeyup="mostrarSugerenciaRed(this.value)" value="<?php echo htmlspecialchars($datos_actividad['titulo_red'] ?? ''); ?>"/>
                                        </div>
                                        <div class="form-group">
                                            <label>Resultados / Pág:</label>
                                            <input class="form-control" type="number" min="1" max="16" id="numeroresultados_red" value="<?php echo $_COOKIE['numeroresultados_red'] ?? '8'; ?>" onkeyup="grabarcookie('numeroresultados_red',this.value);mostrarSugerenciaRed();" onchange="grabarcookie('numeroresultados_red',this.value);mostrarSugerenciaRed();" style="width: 70px;"/>
                                        </div>
                                    </div>
                                    
                                    <div id="resultadoBusqueda" class="mt-3"></div>
                                    
                                    <div class="mt-2 text-info">
                                        <span id="rednombre">
                                            <?php if(!empty($datos_actividad['id_red'])) echo '<strong>Seleccionado:</strong> ' . $datos_actividad['id_red'] . ' - ' . htmlspecialchars($datos_actividad['titulo_red'] ?? ''); ?>
                                        </span>
                                        <input type="hidden" id="id_red" name="red" value="<?php echo htmlspecialchars($datos_actividad['id_red'] ?? ''); ?>"/>
                                        <img id="quitar_Seleccion" style="width:20px; cursor:pointer; visibility:hidden;" onclick="limpiar_red();focoared();ocultar_quitar_seleccion();" title="Quitar Seleccionado" src="../comun/img/negativo.png">
                                    </div>
                                </div>
                                
                                <div id="nuevo" style="display:none;"></div>
                            </div>
                        </div>

                        <!-- PESTAÑA 3: CUESTIONARIO -->
                        <div id="content-3">
                            <div id="div_cuestionarios">
                                <button type="button" class="btn btn-primary mb-3" onclick="nuevocuestionario()">Crear Nuevo Cuestionario</button>
                                
                                <div class="form-group">
                                    <label>Seleccione un cuestionario existente:</label>
                                    <input type="hidden" name="id_cuestionario" id="id_cuestionario" value="<?php echo htmlspecialchars($datos_actividad['id_cuestionario'] ?? ''); ?>">
                                    <button type="button" class="btn btn-danger btn-xs" onclick="dejardeelegirfilas();" title="Quitar selección actual"><span class="glyphicon glyphicon-remove"></span></button>
                                </div>

                                <div class="form-inline mb-3">
                                    <label class="radio-inline">
                                        <input type="radio" name="micheckbox_form" id="micheckbox_form_no" value="vista_previa" checked onclick="$('#checkboxdiv_vista_previa').prop('checked', true).change(); refrescar();"> Vista Previa
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="micheckbox_form" id="micheckbox_form_si" value="vista_diseno" onclick="$('#checkboxdiv_vista_previa').prop('checked', false).change(); refrescar();"> Vista Diseño
                                    </label>
                                </div>

                                <div class="form-inline well">
                                    <div class="form-group">
                                        <label>Buscar Cuestionario: </label>
                                        <input class="form-control" type="text" placeholder="Ejemplo: Taller" id="txt_buscar_cuestionario" onkeyup="buscar_cuestionario();">
                                    </div>
                                    <div class="form-group">
                                        <label>Mostrar: </label>
                                        <input class="form-control" type="number" min="1" max="16" id="numeroresultados_cuesionario" value="<?php echo $_COOKIE['numeroresultados_cuesionario'] ?? '8'; ?>" onchange="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario();" style="width: 70px;">
                                    </div>
                                    <button type="button" class="btn btn-default" onclick="buscar_cuestionario();"><span class="glyphicon glyphicon-search"></span></button>
                                </div>

                                <div id="txtbuscar_cuestionario"></div>
                                
                                <div class="mt-3">
                                    <label class="checkbox-inline">
                                        <input mostrarocultar="div_vista_previa" type="checkbox" id="checkboxdiv_vista_previa" value="SI"> Mostrar Panel Inferior (Vista)
                                    </label>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12" id="div_vista_previa" style="display:none">
                                        <iframe id="frame_vistaprevia" frameborder="0" style="width: 100%; height: 500px; border: 1px solid #ccc; border-radius: 4px;" src=""></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 4: FORO -->
                        <div id="content-4">
                            <div class="mb-3">
                                <label class="radio-inline">
                                    <input type="radio" name="micheckbox_foro" id="micheckbox_foro_no" value="crear" checked onclick="document.getElementById('crear_foro').style.display='block'; document.getElementById('seleccionar_foro').style.display='none';"> Crear Nuevo Foro
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="micheckbox_foro" id="micheckbox_foro_si" value="buscar" onclick="document.getElementById('crear_foro').style.display='none'; document.getElementById('seleccionar_foro').style.display='block';"> Seleccionar Foro Existente
                                </label>
                            </div>

                            <?php
                            $parametros = [
                                'contexto' => "actividad",
                                'titulo' => "Foro actividad",
                                'roles' => ["docente", "estudiante"]
                            ];
                            $camposocultos = "hidden";
                            ?>

                            <div id="crear_foro" class="well">
                                <div class="form-group">
                                    <label>Contexto del Foro:</label><br>
                                    <label class="radio-inline" title="Sólo usuarios del curso">
                                        <input name="contexto_actividad" type="radio" value="actividad" checked> Actividad
                                    </label>
                                    <label class="radio-inline" title="Todos los usuarios">
                                        <input name="contexto_actividad" type="radio" value="general"> General
                                    </label>
                                </div>
                                
                                <div class="form-group">
                                    <label for="titulo_foro_actividad">Tema del Foro:</label>
                                    <input type="text" name="titulo_foro_actividad" id="titulo_foro_actividad" class="form-control" placeholder="Título del Foro" value="<?php echo htmlspecialchars($parametros['titulo']); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Roles Permitidos:</label>
                                    <div class="checkbox">
                                        <label><input name="roles_grupo_actividad[]" value="admin" type="checkbox" checked> Administrador</label>
                                        <label><input name="roles_grupo_actividad[]" value="docente" type="checkbox" checked> Docente</label>
                                        <label><input name="roles_grupo_actividad[]" value="acudiente" type="checkbox"> Acudiente</label>
                                        <label><input name="roles_grupo_actividad[]" value="estudiante" type="checkbox" checked> Estudiante</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <input type="<?php echo $camposocultos; ?>" name="icono_seleccionado_actividad" id="icono_seleccionado_actividad" value="378">
                                    <img width="50" id="icono_seleccionado_img_actividad" src="<?php echo SGA_COMUN_URL; ?>/img/png/speech-bubble.png" class="img-thumbnail">
                                    <?php if(function_exists('boton_modal_elegir_icono')) boton_modal_elegir_icono('_actividad'); ?>
                                </div>
                                
                                <input type="<?php echo $camposocultos; ?>" name="valor" value="">
                                <input type="hidden" name="permitir_temas_actividad" value="NO">
                            </div>

                            <div id="seleccionar_foro" style="display:none;" class="well">
                                <?php if(function_exists('boton_modal_nuevo_foro')) boton_modal_nuevo_foro(); ?>
                                <div class="form-group mt-3">
                                    <input onkeyup="mis_foros(this.value);" onchange="mis_foros(this.value);" class="form-control" type="search" id="buscar_foro" placeholder="Buscar un foro existente...">
                                </div>
                                <div class="alert alert-info">
                                    <span id="id_span_foro">Seleccione un Foro</span>
                                    <input type="hidden" name="id_foro" id="id_foro" value="<?php echo htmlspecialchars($datos_actividad['id_foro'] ?? ''); ?>">
                                    <button type="button" id="dejar_seleccion_foro" class="close" style="display:none;" title="Quitar selección">&times;</button>
                                </div>
                                <input type="hidden" id="contexto_foro" value="<?php echo htmlspecialchars($parametros['contexto']); ?>">
                                <div id="mis_foros">
                                    <?php if(function_exists('mis_foros')) mis_foros('', $parametros['contexto']); ?>
                                </div>
                            </div>

                            <!-- Modales Funciones Externas -->
                            <?php 
                                if(function_exists('ventana_modal_nuevo_foro')) ventana_modal_nuevo_foro($parametros);
                                if(function_exists('ventana_modal_elegir_icono')){
                                    ventana_modal_elegir_icono('_actividad');
                                    ventana_modal_elegir_icono();
                                }
                                if(function_exists('ventana_modal_nuevo_icono')){
                                    ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="Error al crear" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();" callback_0="false" callback="buscar_iconos();"');
                                }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Botón de Guardar General Flotante / Fijo Inferior -->
                <div class="text-right" style="margin-top: 20px; margin-bottom: 40px;">
                    <button type="submit" class="btn btn-success btn-lg" id="guardar" title="Guardar Actividad">
                        <span class="glyphicon glyphicon-floppy-disk"></span> Guardar Actividad
                    </button>
                </div>

            </form>
        </section>
    </div>
</div>

<!-- =================================================================
// 4. SCRIPTS (Lógica Cliente)
// ================================================================= -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // Iniciar validaciones requeridas si existe la función
    if (typeof required_en_formulario === "function") {
        required_en_formulario("form_nueva_actividad", "red", "*");
    }
    
    // Cargar sugerencias por defecto
    if (typeof mostrarSugerenciaRed === "function") {
        mostrarSugerenciaRed();
    }
    
    if (typeof buscar_cuestionario === "function") {
        buscar_cuestionario();
    }

    // Lógica del nombre de actividad / foro
    const inputNombreActividad = document.getElementById("nombre_actividad");
    const inputTituloForo = document.getElementById("titulo_foro_actividad");

    function sugerir_tema_foro() {
        if(inputTituloForo && inputNombreActividad) {
            inputTituloForo.value = inputNombreActividad.value;
        }
    }

    if(inputNombreActividad) {
        inputNombreActividad.addEventListener("keyup", sugerir_tema_foro);
        inputNombreActividad.addEventListener("change", sugerir_tema_foro);
    }

    // Lógica selección de Foros
    $(document).on('click', '.li_grupo_foro', function() {
        const id_foro = $(this).attr('id_grupo_foro');
        const nombre_foro = $(this).attr('nombre_grupo_foro');
        const spanForo = document.getElementById("id_span_foro");
        const btnQuitar = document.getElementById("dejar_seleccion_foro");
        
        $("#id_foro").val(id_foro);
        
        if (id_foro !== "") {
            btnQuitar.style.display = "inline-block";
            spanForo.innerHTML = "<strong>Ha Seleccionado:</strong> " + nombre_foro;
        } else {
            btnQuitar.style.display = "none";
            spanForo.innerHTML = "Seleccione un Foro";
        }
        
        $('.li_grupo_foro').removeClass('active');
        $(this).addClass('active');
    });

    // Toggle de visibilidad para elementos mediante atributo custom "mostrarocultar"
    $(document).on('change', 'input[mostrarocultar]', function() {
        const targetId = $(this).attr('mostrarocultar');
        const isChecked = $(this).is(':checked');
        const targetElement = document.getElementById(targetId);
        
        if(targetElement) {
            targetElement.style.display = isChecked ? 'block' : 'none';
        }
    });
});

function uni(act) {
    if( $('#micheckbox').prop('checked') ) {
        $('#quitar_Seleccion').hide();
        $("#uni").hide(400);
        $("#nuevo").show(400);
        $("#nuevo").load("../red/nuevo_red.php?a=" + act);
    } else {
        $('#quitar_Seleccion').show();
        $("#uni").show(400);
        $("#nuevo").hide(400);
    }
}

function refrescar() {
    $('.filas.active').click();
}
</script>

<?php
$contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
?>