<?php
ob_start();
require '../comun/conexion.php';
require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
unset($_SESSION['barra_busqueda']);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$actividad=new Actividad($_GET['a']);
$curso=new Curso($actividad->id_asignacion);
$docente=new Persona($curso->id_docente);
?>
<script type="text/javascript">
  function admin_secciones(contenedor){
var pestatanas= 7;
document.getElementById('content-'+contenedor).style.display="";

for (var i = 1; i <= 6; i++) {
if(i!=contenedor){
 document.getElementById('content-'+i).style.display="none";
}
if(contenedor!=6){
   document.getElementById('content-6').style.display="none";
 }  

}

}
</script>
<!--Espacio de pestañas -->
<div id="container_act" class="colorear">
<input onclick="admin_secciones(1);" id="tab-1" type="radio" name="tab-group" checked="checked" />
<label for="tab-1">Detalle de Actividad</label>


<input onclick="admin_secciones(2);" id="tab-2" type="radio" name="tab-group"/>
<label for="tab-2">Recurso Educativo Digital</label>


<input id="tab-3" type="radio" name="tab-group" />
<label  onclick="admin_secciones(3);" for="tab-3">Cuestionario</label>

<input id="tab-4" type="radio" name="tab-group" />
<label onclick="admin_secciones(4);" for="tab-4">Adjunto</label>

  <input id="tab-5" type="radio" onclick="admin_secciones(5);"  name="tab-group" />
  <label for="tab-5">Foro</label>


     <input onclick="admin_secciones(6);" id="tab-6" type="radio" name="tab-group" />
  <label for="tab-6">Seguimiento</label> 


  <?php 
  require_once ("../comun/funciones.php");
  ?>
<div ><!-- id="content"-->
  
    <div  id="content-1">
<h1 id="titulo">
<?php  echo deletrear(strtoupper($curso->nombre_materia));  
   $nombre_categoria_curso=$curso->categoria_curso();
  echo deletrear(' ('.$nombre_categoria_curso.')');  ?>
     <a href="curso.php?asignacion=<?php echo $curso->id_asignacion; ?>&curso=<?php echo $curso->nombre_materia ;?>"><input title="Ver Actividades de <?php echo $curso->nombre_materia; ?>" style="margin-left:10px;height:45px; width:50px;" type="image" src="<?php echo consultar_link_icono($curso->icono_materia); ?>"/></a> <?php if(($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" )){ ?> 
    <a href="actividad.php?actividad=<?php echo $_GET['a'] ?>">
  <img src="<?php echo SGA_COMUN_URL.'/'.'img/modificar.png' ; ?>" title = "Modificar <?php if(isset($rowa)) echo $rowa['nombre_materia'];  ?>"></img></a> <?php } ?></h1>
  <form method="post" action="<?php echo SGA_MENSAJE_URL ?>/redactar.php"; target="_blank">
<input type="hidden" name="responder_a" value="<?php if (isset($datos_actividad)) echo $datos_actividad['id_usuario'];?>">
<input type="hidden" name="responder_n" value="<?php if (isset($datos_actividad)) echo $datos_actividad['nombre']." ".$datos_actividad['apellido'];?>">
<input type="hidden" name="responder_mensaje" value="">
</form>
  <span><strong>Docente:</strong> </span><?php echo $docente->nombre.' '.$docente->apellido.'<br>'; ?>
   <span><strong>Nombre Actividad:</strong></span>  <?php echo $actividad->nombre_actividad.'<br>'; ?>
   <span><strong>Período académico :</strong></span>  <?php echo $actividad->periodo.'<br>'; ?>
   <span><strong>Fecha y Hora Publicación:</strong></span>  <?php echo formatofecha($actividad->fecha_publicacion).' / '.formatohora($actividad->hora_publicacion).'<br>'; ?>
   <span><strong>Descripción:</strong></span>  <?php echo $actividad->Observaciones.'<br>'; ?>
  <span><strong>Actividad Evaluable:</strong></span>  <?php
  echo $actividad->evaluable.'<br>';  ?>
  <span><strong>Cuestionario:</strong></span>  <?php 
  echo $actividad->cuestionario.'<br>';  ?>
  <span><strong>Adjuntar Archivo:</strong></span>  <?php echo $actividad->adjunto.'<br>'; //} ?>
<?php if ($actividad->evaluable=="SI"){ ?>
  <span><strong>Fecha y hora Entrega :</strong></span> <?php
   if($actividad->fecha_entrega != "0000-00-00"){
  echo formatofecha($actividad->fecha_entrega).'/'.formatohora($actividad->hora_entrega).'<br>';
  }
list($dia, $mes,$año) = diferenciaentrefechas($actividad->fecha_publicacion,$actividad->fecha_entrega);
list($dia2, $mes2,$año2) = diferenciaentrefechas($actividad->fecha_entrega,date('Y-m-d'));
@list($uno,$dos) = colores($dia,$dia2);
 if($dia2 > 0) {
    if($dia2>1){ $s = 's'; } else { $s = ''; } 
    if(date('Y-m-d')>$datos_actividad['fecha_entrega']){ $tiempo = 'Hace'; } else { $tiempo = 'Restan'; } 
     echo '<strong>"'.$tiempo.'": </strong>'.$dia2.' día'.$s; 
 } 
 }
?>  
<form method="post" action="<?php echo SGA_MENSAJE_URL ?>/redactar.php"; target="_blank">
<input type="hidden" name="responder_a" value="<?php echo $datos_actividad['id_docente'];?>">
<input type="hidden" name="responder_n" value="<?php echo $datos_actividad['nombre']." ".$datos_actividad['apellido'];?>">
</form>
</div>



 <div style="display:none;" id="content-2"  >       
<?php
$red = new red($actividad->id_red);
if($actividad->id_red != NULL and $actividad->id_red<>"" ){ 
      if(empty($formato)){
        $formato="";
        $ruta=$red->enlace;
      }    
reproductor($formato,$ruta);         
?>  
    <?php }else{ ?>
    No hay Red
    <?php }
if (isset($red->id_red)){
  $responsable=new Persona($red->responsable);
 echo '<div style="position:absolute;margin-top:-30%;margin-left:65%;">';
 echo '<h2>'.$red->titulo_red.'</h2>';
 if($red->adjunto=="no"){
  echo '<span>Enlace:  <a target="_blank" href="'.$red->enlace.'">'.$red->enlace.'</a></span><br>'; 
  } 
  echo '<span>Responsable:  '.$responsable->nombre.' '.$responsable->apellido.'</span><br>'; 
  $materia=new Materias($red->materia_red);
  echo '<span>Materia :'.$materia->nombre_materia.'</span><br>'; 
 $metadatos['nivel_eductivo'] = str_replace("[", "", $red->nivel_eductivo);$red->nivel_eductivo = str_replace("]", "", $red->nivel_eductivo);
 str_replace('"', '', $red->nivel_eductivo);
  echo '<span>Nivel Educativo :'.$red->nivel_eductivo.'</span><br>'; 
  echo '<span>Fecha Publicación : '.formatofecha2($red->fecha).'</span><br>'; 
  echo '<span>Descripción :'.$red->descripcion.'</span><br>'; 
  echo '<span>Dificultad :'.$red->dificultad.' (1 a 5)</span><br>';  ?>
  <span>Estrellas : <span id="num_fav_red"><?php $array_estrellas = json_decode($red->estrellas,true); echo count($array_estrellas) ?></span>&nbsp;<span class="fav_visor"><?php  echo mis_red_favoritos($red->id_red, $red->estrellas); ?></span>
  </span>
  <br/>
  <?php
  echo '<span>Palabras Clave :'.$red->palabras_clave.'</span><br>'; 
  echo '<span>Icono :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="'.$red->icono_red.'"></span></span><br><br/>'; 
 ?> <input style="color:#FFF" onclick="window.open('../comun/funciones.php?ruta_red=<?php echo $red->enlace; ?>&formato=<?php echo $red->formato; ?>&scorm=<?php echo $red->scorm; ?>')" type="button" class="btn btn-primary" value="Descargar"/>
<?php
 echo '</div>';
}
    ?>  
    </div>
    <?php 
#if (isset($datos_actividad['cuestionario']) and $datos_actividad['cuestionario']=="SI"){ ?>
<div id="content-3" style="display:none;width:100%;height:100%">
   <button onclick="mostrar_pantalla_completa('span-3');document.getElementById('botonpc-3').style.color='red'" type="button" class="btn btn-primary">Pantalla completa</button>
    <br>
    <span id='span-3' style="position:absolute;width:100%;height:100%">
    <iframe style="width:100%;height:100%" id="iframe-3" class="frame_cuestionario" src="<?php echo SGA_URL ?>/cuestionario/ver_cuestionario.php?embebido&enc=<?php echo $actividad->id_cuestionario; ?>&a=<?php echo $_GET['a']?>"></iframe>
    </span>
    </div>
    <div id="content-4">
      <?php 
    if(verificar_actividad_hecha($_GET['a'])==0){;
      ?>
<form>
     <label>Adjunto</label> 
    <input type="file" name="adjunto">
 </form>   
    <?php } ?>
     </div>
    <?php if ($_SESSION['rol']=="docente" or $_SESSION['rol']=="admin" ){ ?>
<div style="display:none" id="content-6">
<div class="col-md-5" >
<input type='search' id='buscar_estudiante_actividad' placeholder='Buscar Estudiante..' onkeyup="buscar_estudiantes_actividad(<?php echo $_GET['a']; ?>,this.value)">
<span id="txt_val_act">
<?php
#echo $_GET['a'];
echo buscar_estudiantes_actividad($_GET['a']); ?>
</span>
</div>
<div >
<label>
<input mostrarocultar='div_vista_tarea_cue' type="checkbox" id="checkboxdiv_cue" value="SI">Ver Tarea Estudiante</label>
<br>
<div class="foro" id="div_vista_tarea_cue" style="display: none;">
<p id="txtrevisar_adjunto"></p>
<p id="txtrevisar_cuestionario"></p>
</div>
Valoración de la actividad <?php echo $curso->nombre_materia; ?>
<form>
        <p>
        <input type="hidden" id="id_act_val" placeholder="id_act_val" value="<?php echo $_GET['a']; ?>">
        <input type="hidden" id="id_seguimiento" placeholder="id_seguimiento" value="">
        <input type="hidden" id="id_inscripcion" placeholder="id_inscripcion" value="">
        </p>
        <label>Valoración </label>&nbsp;&nbsp;<input type="text" id="valoracion"><br>
        <label>Observación</label>
        <p><textarea id="observacion" cols="60" rows="10"></textarea></p>
     </form>
     <button class="btn btn-primary" onclick="guardar_valoracion()">Guardar</button>
</div>
</div>
    </div>
    <?php } ?>
</div>
</div><!--div id="content"--> 
</div><!--div id="container"-->

<?php
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>