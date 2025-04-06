<?php
ob_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

@session_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/'.'Guagua'.'/'."/comun/autoload.php");
require("../comun/conexion.php");
require_once("../comun/config.php");
require_once("../comun/funciones.php");
unset($_SESSION['barra_busqueda']);
if (!isset ($_GET['actividad'])) $_GET['actividad']=NULL;
$datos_actividad=array();
$sql='select * from actividad left join red on actividad.id_red = red.id_red 
where actividad.id_actividad="'.$_GET['actividad'].'" limit 1';
$consulta = $mysqli ->query($sql);
$datos_actividad = array();
if(isset($_GET['asignacion'])) { $_POSt['asignacion']= mysqli_real_escape_string($mysqli,$_GET['asignacion']); }
while($dato_actividad=$consulta ->fetch_assoc()){
#echo $dato_actividad['id_red'];
$_POSt['asignacion'] = $dato_actividad['id_asignacion'];
    $datos_actividad = $dato_actividad;
}
 $consulta_curso ='select * from asignacion,materia,categoria_curso where 
asignacion.id_categoria_curso = categoria_curso.id_categoria_curso and
asignacion.id_asignatura = materia.id_materia and
asignacion.id_asignacion="'.$_POSt['asignacion'].'"';
$consulta_curso = $mysqli->query($consulta_curso);
while($row_curso=$consulta_curso->fetch_assoc()){
  
  $portada_asignacion= $row_curso['portada_asignacion'];
$_GET['curso']= $row_curso['nombre_materia'];
 $categoria =$row_curso['nombre_categoria_curso'];
}
if(isset($_POST['curso'])){
$_GET['curso'] = $_POST['curso'];
}

if (isset($_SESSION['rol'])) {     ?>
<div class="jumbotron" <?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
style="
background-image: url('<?php echo SGA_CURSOS_URL.'/'.  $portada_asignacion; ?>');no-repeat left center;
     -webkit-background-size: cover;
     -moz-background-size: cover;
     -o-background-size: cover; 
"
<?php } ?>>
  <div class="container text-center"
  <?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
    style="height:120px;background-color:gray;   opacity:0.01;" 
    <?php } ?>
    >
    
    <h1 title="<?php 
echo ''.$_GET['curso'] ?>" class="fip"
  <?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
style="opacity:0.01;"
<?php } ?>
><?php
if (isset($datos_actividad['id_asignacion'])){
$sql_materia='select * from asignacion,materia,categoria_curso where
asignacion.id_categoria_curso= categoria_curso.id_categoria_curso and
asignacion.id_asignatura = materia.id_materia and
asignacion.id_asignacion = "'.$datos_actividad['id_asignacion'].'" ';
$academico=new Academico;
$consultadatos_sqlmateria = $academico->consultar_datos($sql_materia);
if (count($consultadatos_sqlmateria)>0)
foreach($consultadatos_sqlmateria as $clave => $valor){
@print_r($valor[10].' ('.$valor[15].')');
}
}
    $materia = mb_strtoupper($_GET['curso'],'UTF-8');
    $materia2=puntos_suspensivos($materia,20); ?>

<?php echo  $materia2 ; ?>
   </h1>      
   
  </div>
</div>
<div class="container-fluid bg-3 text-center">    
 <div class="row">
<section>
<style>
<?php
$cant = 4;//cantidad de pestañas
for ($i=1;$i<=$cant;$i++){
echo '#container_act input#tab-'.$i.':checked ~ #content #content-'.$i.'';
if ($i!=$cant) echo ',';
}

?>{
 opacity: 1;
 z-index: 100;
}
</style>
      <script>
$(document).ready(function(){
    $("checkbox").click(function(){
        $("p").toggle();
    });
    mostrarSugerenciaRed();
});
</script>
<script >
$(document).ready(function(){
 required_en_formulario(id_formulario="form_nueva_actividad",color="red",elemento="*");

        });    
</script>
<form id="form_nueva_actividad" align="center" action="subir.php" method="POST" method="POST" ENCTYPE="multipart/form-data">
<input id="id_asignacion" name="id_asignacion" type="hidden" value="<?php
if(isset($_GET['actividad'])) echo $datos_actividad['id_asignacion'];
else if(isset($_POST['asignacion'])) echo $_POST['asignacion'];
else if(isset($_GET['asignacion'])) echo $_GET['asignacion'];
?>">

<input id="id_actividad" name="id_actividad" type="hidden" value = "<?php if( isset($_GET['actividad'])) echo $_GET['actividad']; ?>">
<div id="container_act" class="colorear">
<!--Pestaña 1 activa por defecto-->
  <input id="tab-1" type="radio" name="tab-group" checked="checked" />
  <label for="tab-1"><span class="glyphicon glyphicon-envelope"></span><span class="tab_text">&nbsp;Detalle Actividad</span></label>
  <!--Pestaña 2 inactiva por defecto-->
  <input onclick="focoared();"  id="tab-2" type="radio" name="tab-group" />
  <label <?php

  if(!isset($datos_actividad['id_red'])  or $datos_actividad['id_red']=="no" or $datos_actividad['id_red']=="NO" ) { echo 'style="display:none"'; } ?>  id="label-tab-2" for="tab-2"><span class="glyphicon glyphicon-send"></span><span  class="tab_text">&nbsp;Recurso Educativo Digital</span></label>
  <!--Pestaña 3 inactiva por defecto-->
  
 <input id="tab-3" type="radio" name="tab-group" />
  <label <?php if(!isset($datos_actividad['cuestionario']) or $datos_actividad['cuestionario']=="NULL" or  $datos_actividad['cuestionario']=="NO" or $datos_actividad['cuestionario']=="no") { echo 'style="display:none"'; } ?>  id="label-tab-3" for="tab-3"><span  class="glyphicon glyphicon-star"></span><span  class="tab_text">&nbsp;Cuestionario</span></label>
  <!--Pestaña 4 inactiva por defecto-->
  
 <input id="tab-4" type="radio" name="tab-group" />
  <label <?php if(!isset($datos_actividad['foro']) or $datos_actividad['foro']=="NULL" or  $datos_actividad['foro']=="NO" or $datos_actividad['foro']=="no") { echo 'style="display:none"'; } ?>  id="label-tab-4" for="tab-4"><span  class="glyphicon glyphicon-star"></span><span  class="tab_text">&nbsp;Foro</span></label>
<div id="content">
    <div id="content-1" align="center;" style="width:100%;">


 <div class="form-group">
    <input type="hidden" id="id_actividad2" name="id_actividad" value="<?php echo $_GET['actividad']; ?>"/>
       <label for="textfield">Nombre Actividad: </label>
    <input align="center" type="hidden"  value="<?php if(isset($_POSt['asignacion'])) echo $_POSt['asignacion']; ?>" name="asignacion" id="asignacion" required  />
    <input   align="center" class="form-control" placeholder="Nombre.." type="text" name="id" id="nombre_actividad" required  value="<?php if(isset($datos_actividad['nombre_actividad'])) echo $datos_actividad['nombre_actividad'];  ?>" />
 </div>
 <div class="form-group">
    <label for="textfield">Periodo: </label><br> 
  <?php $sqlperiodo ='select * from periodo'; 
  $consulta_periodo = $mysqli ->query($sqlperiodo);
  while($row_periodo = $consulta ->fetch_assoc()){ ?>
<input id="Periodo" <?php  if(isset($datos_actividad['periodo']) and $datos_actividad['periodo']==$row_periodo['nombre_periodo'] ) echo 'checked';  ?> type="radio" name="periodo" value="<?php echo $row_periodo['id_periodo']; ?>">&nbsp;<?php echo $row_periodo['nombre_periodo']; ?></label>      
 <?php }  ?>
     <label><input id="periodo"   <?php if(!isset($datos_actividad['periodo'])) echo 'checked="checked"'; ?>  <?php if(isset($datos_actividad['periodo']) and $datos_actividad['periodo']=="1" ) echo 'checked';  ?> type="radio" name="periodo" value="1">&nbsp;1</label>
    <label><input id="periodo" <?php if(isset($datos_actividad['periodo']) and $datos_actividad['periodo']=="2" ) echo 'checked';  ?> type="radio" name="periodo" value="2">&nbsp;2</label>
    <label><input id="periodo" <?php if(isset($datos_actividad['periodo']) and $datos_actividad['periodo']=="3" ) echo 'checked';  ?> type="radio" name="periodo" value="3">&nbsp;3</label>
    <label><input id="periodo" <?php if(isset($datos_actividad['periodo']) and $datos_actividad['periodo']=="4" ) echo 'checked';  ?> type="radio" name="periodo" value="4">&nbsp;4</label>
</div>
<div class="form-group">
<label>Descripción</label>
<textarea id="observacion"  class="form-control" rows="4" cols="50" name ="observacion"  placeholder="Observación.." ><?php if(isset($datos_actividad['Observaciones']) ) echo $datos_actividad['Observaciones']; ?>
</textarea>
</div>
<div style="display:none" class="form-group">
<label>Visible</label>
<select  class="form-control" name="visible">
    <option <?php    if(isset($datos_actividad['Observaciones'])  and $datos_actividad['Observaciones'] =="SI" ) echo $datos_actividad['visible']; ?>>SI</option>
    <option <?php if(isset($datos_actividad['Observaciones']) and $datos_actividad['Observaciones'] =="NO") echo $datos_actividad['visible']; ?>>NO</option>

</select>

</div>


<div class="form-group">
<label class="checkbox-inline" for="fecha_publicacion">Fecha de publicación</label>
<input type="date" name="fecha_publicacion" id="fecha_publicacion" value = "<?PHP 
if(!isset($datos_actividad['fecha_publicacion'])){ echo date('Y-m-d'); } else {
echo date('Y-m-d',strtotime($datos_actividad['fecha_publicacion'] )); } ?>"  />
</div>
<div class="form-group">
<label class="checkbox-inline" for="hora_publicacion">Hora de publicación</label><input type="time" name="hora_publicacion" id="hora_publicacion" value = "<?PHP 
if(isset($datos_actividad['hora_publicacion'])){ echo $datos_actividad['hora_publicacion'];
; } else {
echo date('H:i:s');
}
?>"  />
</div>


<p>        <label title="Puedes agregar o seleccionar un archivo adjunto para ser visualizado en la actividad"  style="margin-right:250px!important;" class="checkbox-inline">

<input mostrarocultar='label-tab-2'   <?php
if(isset($datos_actividad['id_red'])) echo 'checked'; ?> onclick="verificar_red(this);limpiar_red();"  type="checkbox" id="checkbox2" name="id_red" value="SI"  style="margin-right:120px!important;"> Habilitar un Recurso Educativo Digital</label>
    <label>
                <label title="Establece los tiempos de entrega y medios de valoración para esta actividad" style="margin-right:250px!important;" class="checkbox-inline">

    <input onchange="verificar_evaluable(this)" mostrarocultar='fechas'  type="checkbox" id="checkbox"  <?php if(isset($datos_actividad['evaluable']) and $datos_actividad['evaluable']=="SI") echo 'checked'; ?> name="evaluable" value="SI"> Actividad Evaluable</label>
   
     <label class="checkbox-inline" title="Crea un espacio de discución de la actividad para estudiantes y acudientes del curso"><input  onclick="mostrarcuestionar();" mostrarocultar='label-tab-4' type="checkbox" id="checkbox_foro" name="foro" <?php if (isset($datos_actividad['foro']) and $datos_actividad['foro']=="SI") echo 'checked'; ?>> Habilitar Foro</label>
 </label> <div <?php
if(isset($datos_actividad['evaluable'])){
 if(isset($datos_actividad['evaluable']) and $datos_actividad['evaluable']=="NO") {
 echo "style=display:none";
 }else if(isset($datos_actividad['evaluable']) and $datos_actividad['evaluable']=="SI") {
    echo "style=display:block";
 }
}else{
     echo "style=display:none";
}
 ?> id="eval">
    <div id="fechas">
    <div></p>
    <label class="checkbox-inline" for="fecha_entrega">Fecha de Entrega </label>
    <input style="margin-right:50px!important" type="date" id="fecha_entrega" name="fecha_entrega" value="<?php if(isset($datos_actividad['fecha_entrega']) ){ echo $datos_actividad['fecha_entrega'];} ?>"/>
<label class="checkbox-inline" for="hora_entrega">Hora de Entrega </label>
    <input id="hora_entrega" type="time" name="hora_entrega" value="<?php if(isset($datos_actividad['hora_entrega']) ) echo $datos_actividad['hora_entrega']; ?>"/>
<label>
</div>
<div>
<label>
    <br/><br/>
     <p>
        <label style="margin-right:500px!important;" class="checkbox-inline">
    <input id="cuestionario" onclick="mostrarcuestionar();" mostrarocultar='label-tab-3'  type="checkbox" id="checkbox_cuestionario" name="cuestionario" 
<?php if (isset($datos_actividad['cuestionario']) and $datos_actividad['cuestionario']=="SI") echo 'checked'; ?>
>Habilitar Cuestionario</label></label></input>
<input 
<?php if(!isset($datos_actividad['adjunto']) ) echo 'value="NO"'; ?>  type="checkbox" id="adjunto" name="adjunto"  <?php if(isset($datos_actividad['adjunto']) and  strtolower($datos_actividad['adjunto'])=='si' ) { echo "checked" ;  echo ' value="SI"'; }  ?> ><label id="adjunt" class="checkbox-inline" >Permitir Subir archivo Adjunto</label>
</div>
<div>
            <label class="checkbox-inline">

    <label></label></label>
    </div>
</div>
</p>
    </div>
</div>
    <div id="content-2">
       <center>
           <script type="text/javascript" >
               $(document).ready(function() {
uni();
                  
               });
         
 function uni (act){
if( $('#micheckbox').prop('checked') ) {
    document.getElementById('quitar_Seleccion').style.display='none';
  $("#uni").hide(800);
  $("#nuevo").show(800);
  $("#nuevo").load("../red/nuevo_red.php?a="+act);//se cambio load por funcion php y se llamó la funcion con el parametro a
	}
	else{
 document.getElementById('quitar_Seleccion').style.display='';

	$("#uni").show(800);
    $("#nuevo").hide(800);

	}
 
}
</script>
<div id="div_red">
<div id="opciones_red" style="">
<label>
<input checked style="display:inline;" type="radio" name="micheckbox_red" id="micheckbox_no" Value="Buscar" onclick="if(document.getElementById('micheckbox').checked==true) document.getElementById('micheckbox').click();"/>&nbsp;Buscar</label>
<label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input style="display:inline;" type="radio" name="micheckbox_red" id="micheckbox_si" Value="Buscar" onclick="if(document.getElementById('micheckbox').checked==false) document.getElementById('micheckbox').click();"/>&nbsp;Nuevo RED</label>
<div class="checkbox">
  <label>
</div>
<input style="margin-top:-70px;margin-left:-170px;display:none" type="checkbox" name="" id="micheckbox" onclick="uni('<?php echo $_GET['actividad']?>');" Value="Nuevo RED" /></label>
<div style="margin-top:-60px;margin-left:-0px" id="uni">
<br/>
 
<b><label>Buscar: </label></b>
<input placeholder="ejemplo: plantas" type="text" id="busqueda" name="busqueda" onfocus ="mostrarSugerenciaRed(this.value)" onkeyup ="mostrarSugerenciaRed(this.value)" value="<?php if(isset($datos_actividad['titulo_red'])) echo $datos_actividad['titulo_red']; ?>"/>
<label>Resultados Por página</label>
<input type="number" min="0" max="16" id="numeroresultados_red" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="<?php if (isset($_COOKIE['numeroresultados_red'])) echo $_COOKIE['numeroresultados_red']; else echo "8" ?>" onkeyup="grabarcookie('numeroresultados_red',this.value);mostrarSugerenciaRed();" mousewheel="grabarcookie('numeroresultados_red',this.value);mostrarSugerenciaRed();" onchange="grabarcookie('numeroresultados_red',this.value);mostrarSugerenciaRed();" size="4" style="width: 40px;"/>
 
<p><a onclick="mostrarSugerenciaRed();"></a></p>
   <div id="resultadoBusqueda">
 </div>
 
 <span id="rednombre"><?php if(isset($datos_actividad['id_red']) ) echo ' <strong>Seleccionó </strong>'.$datos_actividad['id_red'].' '.$datos_actividad['titulo_red'];

 ?></span><input type="hidden" id="id_red" name="red" value="<?php if(isset($datos_actividad['id_red']) ) echo $datos_actividad['id_red']; ?>"/>
 </div>

 <img id="quitar_Seleccion" style="width:20px;visibility: hidden;" onclick="limpiar_red();focoared();ocultar_quitar_seleccion();" title="Quitar Seleccionado" src="../comun/img/negativo.png"></img>
  
 
<div id="nuevo" >
 
    <form id="formulario" class="form-horizontal" role="form" action="" method="POST" ENCTYPE="multipart/form-data" >
        
        <form/>

</div>
    </div>  </div>
 </div> <!--fin div content-2-->
 <input id="guardar" title="Guardar Actividad" style="width:50px;margin-left:1024px!important;margin-top:-400px!important;"
 type="image" src="../comun/img/disco-flexible.png" />
    <?php  ?>
    <div id="content-3" style="width: 100%;position: absolute;">
<div id="div_cuestionarios">
<p><a class="btn btn-primary" onclick="nuevocuestionario()">Nuevo</a></p>
<label for="id_cuestionario">Seleccione un cuestionario</label>
<input type="hidden" name="id_cuestionario" id="id_cuestionario" value="<?php if(isset($datos_actividad['id_cuestionario'])) echo $datos_actividad['id_cuestionario'] ; ?> "></input><button type="button" class="btn-danger badge" style="background-color:#d9534f;" onclick="dejardeelegirfilas();">-</button> <br>
<div id="opciones_form" style="">
<label>
<input checked style="display:inline;" type="radio" name="micheckbox_form" id="micheckbox_form_no" Value="vista_previa" onclick="if(this.checked==true){ document.getElementById('div_vista_previa').checked=true;};refrescar();"/>&nbsp;Vista Previa</label>
<label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input style="display:inline;" type="radio" name="micheckbox_form" id="micheckbox_form_si" Value="vista_diseño" onclick="if(this.checked==true){ document.getElementById('div_vista_previa').checked=false;};refrescar();"/>&nbsp;Vista Diseño</label>
</div>
<p>
<b><label>Buscar: </label></b><input type="text" placeholder="Ejemplo: Taller" id="txt_buscar_cuestionario" onkeyup ="buscar_cuestionario();"  style="margin: 15px;" value="">
<input type="number" min="0" max="16" id="numeroresultados_cuesionario" placeholder="Cantidad de resultados" title="Cantidad de resultados que quiero ver por página" value="<?php if (isset($_COOKIE['numeroresultados_cuesionario'])) echo $_COOKIE['numeroresultados_cuesionario']; else echo "8" ?>" onkeyup="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario();" mousewheel="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario();" onchange="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario();" size="4" style="width: 40px;"></p>
<p>
<span style="width:80px;height:80px" onclick="buscar_cuestionario();" class="icon-sga-list-15"></span>
</p>
<br><br>
<span id="txtbuscar_cuestionario"></span>
<script>buscar_cuestionario();</script>
<br><br>
<p>

</p>
<label>
<input mostrarocultar="div_vista_previa" type="checkbox" id="checkboxdiv_vista_previa" value="SI">Mostrar Vista Previa / Diseño</label>
<br>
<div class="row">
<div class="cols-md-12" id="div_vista_previa" style="display:none">
<iframe id="frame_vistaprevia" frameborder="0" style="width: 100%;" height="500px" src=""></iframe>
</div><!--fin div vistaprevia-->
</div><!--div class="row"-->
</div><!--fin div cuestionarios-->
    </div><!--fin div content-3-->
    <div id="content-4" align="center;" style="width:100%;">
<div id="opciones_foro" style="">
<label>
<input checked style="display:inline;" type="radio" name="micheckbox_foro" id="micheckbox_foro_no" Value="crear" onclick="if(this.checked==true){ document.getElementById('crear_foro').style.display='block';document.getElementById('seleccionar_foro').style.display='none'}"/>&nbsp;Crear Foro</label>
<label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input style="display:inline;" type="radio" name="micheckbox_foro" id="micheckbox_foro_si" Value="buscar" onclick="if(this.checked==true){ document.getElementById('crear_foro').style.display='none';document.getElementById('seleccionar_foro').style.display='block'}"/>&nbsp;Buscar Foro existente</label>
</div>
<?php
$parametros=array();
$parametros['contexto']="actividad";
$parametros['titulo']="Foro actividad ";
$parametros['roles']=array("docente","estudiante");
$camposocultos = "hidden"
?>
<div id="crear_foro">
<div class="form-group">
<label>Contexto</label>
<span class="form-control">

<label title="Sólo los usuarios de este curso pueden acceder a este foro"><input name="contexto_actividad" type="radio" value="actividad" <?php 
if (isset($parametros['contexto']) and $parametros['contexto']=="actividad")
echo "checked";
if (!isset($parametros['contexto'])) echo "checked";
?> >&nbsp;Actividad</label>
&nbsp;&nbsp;&nbsp;&nbsp;
<label title="Todos los usuarios pueden acceder a este foro"><input name="contexto_actividad" type="radio" value="general" <?php 
if (isset($parametros['contexto']) and $parametros['contexto']=="general") 
echo "checked"; 
?> >&nbsp;General</label>
</span>
</div>
            <div class="form-group">
                <label for="titulo_foro_actividad">Tema del Foro</label>
                <input type="text" name="titulo_foro_actividad" id="titulo_foro_actividad" class="form-control" placeholder="Titulo del Foro" value="<?php if(isset($parametros['titulo'])) echo $parametros['titulo'];?>">
            </div>
            <div class="form-group">
                <label>Roles</label>
                <div class="form-control" value="1">
                <label><input name="roles_grupo_actividad[]" value="admin" type="checkbox" checked >&nbsp;Administrador</label>
                <label><input name="roles_grupo_actividad[]" value="docente" type="checkbox" <?php if(isset($parametros['roles']) and is_array($parametros['roles']) and in_array('docente',$parametros['roles'])) echo 'checked';?> >&nbsp;Docente</label>
                <label><input name="roles_grupo_actividad[]" value="acudiente" type="checkbox" <?php if(isset($parametros['roles']) and is_array($parametros['roles']) and in_array('acudiente',$parametros['roles'])) echo 'checked';?> >&nbsp;Acudiente</label>
                <label><input name="roles_grupo_actividad[]" value="estudiante" type="checkbox"<?php if(isset($parametros['roles']) and is_array($parametros['roles']) and in_array('estudiante',$parametros['roles'])) echo 'checked';?> >&nbsp;Estudiante</label>
                </div>
            </div>
            <div class="form-group">
            <input type="<?php echo $camposocultos ?>" name="icono_seleccionado_actividad" id="icono_seleccionado_actividad" value="378">
            <img width="50px" id="icono_seleccionado_img_actividad" src="<?php echo SGA_COMUN_URL ?>/img/png/speech-bubble.png">
            <?php boton_modal_elegir_icono('_actividad'); ?>
            </div>
              <input type="<?php echo $camposocultos ?>" name="valor" value="">
              <input value ="NO"  type="hidden" name="permitir_temas_actividad">
</div>
<div id="seleccionar_foro" style="display:none">
<?php boton_modal_nuevo_foro(); ?><br>
<p align="center"><br>
<input onkeyup="mis_foros(this.value);" onchange="mis_foros(this.value);" class="form-control" style="width:300px;" type="search" id="buscar_foro" placeholder="Buscar un foro">
</p>
<label style="display:block">
<span id="id_span_foro">Seleccione un Foro</span>
<input type="hidden" name="id_foro" id="id_foro" value="<?php if (isset($datos_actividad['id_foro'])) echo $datos_actividad['id_foro'] ?>">
<span id="dejar_seleccion_foro" style="margin:0 5px;padding:0;display:none" title="Dejar de seleccionar este Foro" type="button" id_grupo_foro="" nombre_grupo_foro="Seleccione un Foro" class="li_grupo_foro icon-sga-error" ></span>
</label>
<input type="hidden" id="contexto_foro" value="<?php echo $parametros['contexto']?>">
<span id="mis_foros">
<?php mis_foros('',$parametros['contexto']);?>
</span>
<!--span id="txt_entradas"></span-->
</div>
<?php ventana_modal_nuevo_foro($parametros);?>
<?php ventana_modal_elegir_icono('_actividad'); ?>
<?php ventana_modal_elegir_icono(); ?>
<?php ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="icono no creado, revise su información e intentete nuevo" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();" callback_0="false" callback="buscar_iconos();"'); ?>
</div><!--fin div content-4-->
<?php ?>
</div>
</div>
</section>
</div>
</div>
</form>
<script>
function sugerir_tema_foro(){
    $("#titulo_foro_actividad").val($("#nombre_actividad").val());
}
    $("#nombre_actividad").keyup(function(){
        sugerir_tema_foro();
    });
    $("#nombre_actividad").change(function(){
        sugerir_tema_foro();
    });
    var $lis = $(".li_grupo_foro").click(function(){
        var id_foro=$(this).attr('id_grupo_foro');
        var html="";
        $("#id_foro").val(id_foro);
        if (id_foro != ""){
            document.getElementById("dejar_seleccion_foro").style.display="inline";
        html="Ha Seleccionado: ";
        }else{
            document.getElementById("dejar_seleccion_foro").style.display="none";
            document.getElementById("txt_entradas").innerHTML="";
        }
        $("#id_span_foro").html(html+$(this).attr('nombre_grupo_foro'));
        $lis.removeClass('selected');
        $(this).addClass('selected');
    });
    function refrescar(){
        $('.filas.active').click();
    }
    </script>
<style>
    li.selected{
        border-color:red;
    }
</style>
<?php }
$contenido = ob_get_contents();
ob_clean();
require ("../comun/plantilla.php");
?>