<?php 
ob_start();
require_once($_SERVER['DOCUMENT_ROOT'].'/guagua'.'/'."/comun/autoload.php");
require (SGA_COMUN_SERVER.'/conexion.php');
#require_once($_SERVER['DOCUMENT_ROOT'].'/guagua'.'/'."/comun/funciones.php");

#require_once (SGA_COMUN_SERVER.'/funciones.php');
if(isset($_GET['eliminar'])){
  echo $_GET['eliminar'];
}
$curso=new Curso();
$estado_temporal= $curso->deadeline_curso($_GET['asignacion']);
$academico=new academico();
$horarios= ($academico->consultar_horario_simple($_GET['asignacion']));
if(!empty($horarios['fecha_inicio'])){
?>
<span style="margin-left:0%;position:absolute;">
Desde
<?php echo Fecha::formato_fecha($horarios['fecha_inicio']);?>
   Hasta 
<?php echo Fecha::formato_fecha($horarios['fecha_fin']); ?>
</span>
<span style="margin-left:62%;position:absolute;"><?php echo $estado_temporal; ?>% Completado</span>
<meter title="progreso temporal del <?php echo $estado_temporal; ?>%" style="margin-left:92%"  low="30" high="60" optimum="80"  max="100" min="0" value="<?php echo 100-$estado_temporal; ?>"> </meter>
<?php
}
$_SESSION['modulo']="actividad_curso";
$_SESSION['barra_busqueda'] = "actividad_curso";
$asignacion =mysqli_real_escape_string($mysqli,$_GET['asignacion']);
$sql_asignacion='select * from asignacion,materia,usuario,categoria_curso where 
asignacion.id_categoria_curso   = categoria_curso.id_categoria_curso and
asignacion.id_docente=usuario.id_usuario and
asignacion.id_asignatura = materia.id_materia and
id_asignacion ="'.$asignacion.'"';
$consulta_asignacion = $mysqli ->query($sql_asignacion);
while($infoactividad_asignacion=$consulta_asignacion->fetch_assoc() ){
    $nombre_docente = $infoactividad_asignacion['nombre'].' ' .$infoactividad_asignacion['apellido'];
    $foto_docente =$infoactividad_asignacion['foto'];
    $categoria_curso = $infoactividad_asignacion['nombre_categoria_curso'];
    $descripcion = $infoactividad_asignacion['descripcion'];
    $portada_asignacion = $infoactividad_asignacion['portada_asignacion'];
    $curso=$infoactividad_asignacion['nombre_materia'];
    setcookie('mirutactividades','asignacion='.$infoactividad_asignacion['id_asignacion'].'&curso='.$infoactividad_asignacion['nombre_materia']);
    $materia = $infoactividad_asignacion['nombre_materia'];
    $id_materia=$infoactividad_asignacion['id_materia'];
    $id_asignacion=$infoactividad_asignacion['id_asignacion'];
    $id_grado=$infoactividad_asignacion['id_categoria_curso'];
    $nombre_grado=$infoactividad_asignacion['nombre_categoria_curso'];
}

if (isset($_GET['actividad_curso'])){
$datos_busqueda  =mysqli_real_escape_string($mysqli,$_GET['actividad_curso']);
$campo_bd=mysqli_real_escape_string($mysqli,$_GET['campo']);
$asignacion = $_GET['asignacion'];
actividad_curso($asignacion,$datos_busqueda,$campo_bd);
#exit();
} 

?>
<script type="text/javascript" >
    function menu_contextual(actividad,nombre,formato){
    console.log('actividad'+actividad+'Nombre'+nombre);

    $.contextMenu({
            selector: '.Contenedor_periodos'+actividad, 
            callback: function(key, options) {
                
if(key=="Modificar Actividad"){window.location='actividad.php?actividad='+actividad; }
if(key=="Nuevo"){window.location='actividad.php?id_asignacion='+ObtenerGetJavascript('asignacion'); }           
            },
            items: {
               <?php @session_start(); if($_SESSION['rol']=="admin" or  $_SESSION['rol']=="docente") { ?>

                "titulo": {name: nombre},
                "sep1": "---------",
                "Modificar Actividad": {name: "Modificar Actividad"},
                "Nueva Actividad": {name: "Nueva Actividad"},
                "sep2": "---------",
                "Salir": {name: "Salir", icon: function(){
                    return 'context-menu-icon context-menu-icon-quit';
                <?php } ?>    
                    
                }}
            }
        });

        $('.Contenedor_periodos'+actividad).on('click', function(e){
            console.log('clicked', this);
        })    
   
}

</script>
<script>
$(function(){
    function createSomeMenu() {
        return {
            callback: function(key, options) {
if(key=="Nuevo Red"){window.location='../red/nuevo_red.php?asignacion=<?php echo $id_materia; ?>'; }
if(key=="asistencia"){window.location='../asistencia/index.php?asignacion='+ObtenerGetJavascript('asignacion'); }
if(key=="horario"){window.location='../asistencia/horario.php?asignacion='+ObtenerGetJavascript('asignacion'); }
if(key=="Nueva Actividad"){window.location='actividad.php?asignacion='+ObtenerGetJavascript('asignacion'); }
if(key=="Modificar Curso"){window.location='modificar_curso.php?asignacion='+ObtenerGetJavascript('asignacion'); } 

if(key=="Estudiantes del curso"){window.location='estudiante_curso.php?asignacion='+ObtenerGetJavascript('asignacion'); } 
if(key=="Reporte Valorativo"){window.open('../reportes/informe_valorativo.php?asignacion='+ObtenerGetJavascript('asignacion'),'_BLANK'); } 

if(key=="plan"){window.open('../../Planeador/consultas.php?asignacion='+ObtenerGetJavascript('asignacion'),'_BLANK'); } 

if(key=="Estadisticas"){window.open('../reportes/promedio_estudiantil.php?asignacion='+ObtenerGetJavascript('asignacion'),'_BLANK'); } 
            },
            items: {
               <?php @session_start(); if($_SESSION['rol']=="admin" or  $_SESSION['rol']=="docente") { ?>
                "Nuevo Red": {name: "Nuevo Red"},
                "horario": {name: "horario"},
                "asistencia": {name: "asistencia"},
                "Nueva Actividad": {name: "Nueva Actividad"},
            "Modificar Curso": {name: "Modificar Curso"},
<?php } ?>
                "Estudiantes del curso": {name: "Estudiantes del curso"},
                 "Estudiantes del curso": {name: "Estudiantes del curso"},
                 "Reporte Valorativo": {name: "Reporte Valorativo"},
                 "Estadisticas": {name: "Estadisticas"},
                 //"plan": {name: "Planes de Clase"},
            }
        };
    }
    $('.context-menu-one').on('mouseup', function(e){
        var $this = $(this);
        $this.data('runCallbackThingie', createSomeMenu);
        var _offset = $this.offset(),
            position = {
                x: _offset.left + 5, 
                y: _offset.top + 5
            }
        setTimeout(function(){ $this.contextMenu(position); }, 1000);
    });
    // setup context menu
    $.contextMenu({
        selector: '.context-menu-one',
        trigger: 'none',
        build: function($trigger, e) {
            e.preventDefault();
            return $trigger.data('runCallbackThingie')();
        }
    });
});
</script>
<div  id="portada" 
<?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
style="background-image: url('<?php echo SGA_CURSOS_URL.'/'.  $portada_asignacion; ?>');no-repeat left center;-webkit-background-size: cover;-moz-background-size: cover; -o-background-size: cover;"
class="jumbotron jumbotron-curso" <?php }else{  ?> class="jumbotron" <?php } ?> >
<?php 
#if($_SESSION['rol']<>"admin" and $_SESSION['rol']<>"docente" ){
$sql_informacion_academica='select * from asignacion,usuario where 
asignacion.id_docente = usuario.id_usuario and asignacion.id_asignacion = "'.$asignacion.'"' ;
#echo $sql_informacion_academica;
foreach(consultar_datos2($sql_informacion_academica) as $informacion => $campos_bd){
$datos_busqueda_actividad['id_docente'] = $campos_bd[8];
$datos_busqueda_actividad['nombre'] = $campos_bd[12].' '.$campos_bd[13];
$datos_busqueda_actividad['foto'] = $campos_bd[15] ;
}
#}
?>
    <form method="post" action="<?php echo SGA_MENSAJE_URL ?>/redactar.php"; target="_blank">
<input type="hidden" name="responder_a" value="<?php if (isset($datos_busqueda_actividad)) echo $datos_busqueda_actividad['id_docente'];?>">
<input type="hidden" name="responder_n" value="<?php if (isset($datos_busqueda_actividad)) echo $datos_busqueda_actividad['nombre'];?>">
<?php if(isset($_SESSION['rol']) and $_SESSION['rol']<>'docente' ){ ?>
<input  type="image" id="imgdocente" title="Enviar Mensaje al docente <?php echo  $nombre_docente; ?>"  src="<?php echo (SGA_COMUN_SGA_DATA.'/'.$foto_docente); ?>" >
<?php } ?>
</form>
<a href="<?php echo SGA_CURSOS_URL ?>/edunotas.php?asignacion=<?php echo $asignacion; ?>" style="position:absolute;margin-left:60%;margin-top:8%" class="btn btn-primary" href="">Nueva Nota</a>


<a href="<?php echo SGA_PLANEADOR_URL ?>/index.php?asignacion=<?php echo $asignacion; ?>" style="position:absolute;margin-left:-5%;margin-top:-4%" class="btn btn-success" href="">Nuevo Plan</a>
<?php if ($_SESSION['rol']=="docente" or $_SESSION['rol']=="admin" ) {
?><input id="opciones_cursos2"  type="button" value="Opciones"  class="btn btn-warning context-menu-one" name=""/>
<?php } ?>
  </form><div class="<?php if(isset($portada_asignacion) and $portada_asignacion<>""){ 
  echo 'container text-center'; } else { echo 'fip'; } ?> " 
  <?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
  style="width:580px;background-color:blue;opacity:0.01">
      <?php } ?>
    <h1 title="<?php echo ucwords($curso)?>" class="fip"> <?php echo Comun::puntos_suspensivos(ucwords($curso),12); ?></h1>  
 <form method="post" action="<?php echo SGA_MENSAJE_URL ?>/redactar.php"; target="_blank">
<input type="hidden" name="responder_a" value="<?php
@session_start();
echo $_SESSION['id_usuario'];?>">
<input type="hidden" name="responder_n" value="<?php echo $nombre_docente;?>">
<input type="hidden" name="materia_asunto" value="<?php echo ucwords($materia).' ('.$categoria_curso.'). ';?>">
<input type="hidden" name="responder_mensaje" value="">

</form>
  </div>
</div>
 <div onclick="mitoogle('#div_parrafo_descripcion_curso')"  id="Contenedor_descripcion_curso"  ><p style="margin-top:5px"><?php echo 'CURSO '.ucwords($materia).' ('.$categoria_curso.')'; ?></p></div>
<div   id="div_parrafo_descripcion_curso">
<p  id="parrafo_descripcion_curso">
    <?php echo "Asignación (".$id_asignacion.') descripción '.$descripcion; ?></p>
</div>
<?php
function actividad_curso($asignacion,$datos_busqueda='',$campo_bd="nombre_actividad"){ 
@session_start();
require ("../comun/conexion.php");
if(!isset($campo_bd)) $campo_bd="nombre_actividad";
if(!isset($_GET['asignacion']))  $asignacion = $_SESSION['asigna'];
$sql='SELECT DISTINCT (periodo) FROM actividad where id_asignacion="'.$asignacion.'" order by periodo desc ';
$consulta = $mysqli ->query($sql);
$resultados_periodos =$consulta->num_rows;  
while($infoactividad_cuantos_periodos = $consulta -> fetch_assoc()){
 $cuantos_periodos[]= $infoactividad_cuantos_periodos['periodo'];
}
if($resultados_periodos<=0){    echo '<p Align="center">No Hay Actividades</p>';}
else {
foreach ($cuantos_periodos as $periodo) {
$sql_actividades_periodo = 'select * from actividad where periodo="'.$periodo.'" and actividad.id_asignacion="'.$asignacion.'" ' ;
$sql_actividades_periodo.= ' and concat(LOWER(`actividad`.'.$campo_bd.')," " ) LIKE "%'.mb_strtolower($datos_busqueda, 'UTF-8').'%"  order by actividad.id_actividad desc ';
$consulta_actividades_periodo = $mysqli -> query  ($sql_actividades_periodo); ?> 
 <div id="contenedor_<?php echo $periodo ?>" class="flex-container">
     <div style="margin-left:-5%;width:82%"  id="separador_de_periodos">
    <p class="<?php echo $periodo ; ?>" onclick="actividades_por_periodo(this);" id="checkbox<?php echo $periodo; ?>">
        <?php echo 'Actividades Periodo '.$periodo ?><span id="span_actividades_encontradas">
     <?php echo " Actividades Encontradas:".$consulta_actividades_periodo -> num_rows ?></span>
    </p>
     </div>
    
 <?php


$sql_actividades = 'select * from actividad where periodo="'.$periodo.'" and  actividad.id_asignacion="'.$asignacion.'" ' ;
if($_SESSION['rol']=="estudiante" or $_SESSION['rol']=="acudiente"){    $sql_actividades.=' and visible = "SI" ';
    $sql_actividades.=' and "'.date('Y-m-d').'" >= `fecha_publicacion`  and 
"'.date('H:i:s').'" >= `hora_publicacion`';
} 

#$campo_bd="nombre_actividad" ;
$sql_actividades .= ' and concat(LOWER(`actividad`.'.$campo_bd.')," " ) LIKE "%'.mb_strtolower($datos_busqueda, 'UTF-8').'%"';
$sql_actividades.=' order by actividad.id_actividad desc';
#echo $sql_actividades_periodo;

$consulta_actividade = $mysqli -> query  ($sql_actividades);
 while ($infoactividad = $consulta_actividade -> fetch_assoc() and $infoactividad['id_asignacion']="'.$asignacion.'"  ) { 
 $actividad = $infoactividad['id_actividad'];
 $nombre = $infoactividad['nombre_actividad'];
 $evaluable = $infoactividad['evaluable'];
 $fecha_entreg =$infoactividad['fecha_entrega'];
 $fech_publi =$infoactividad['fecha_publicacion']; 

  ?>
<div style="margin-top:30%;border:solid 2px!important;text-align:center;margin-left:20px;border-radius:20px" onContextMenu="menu_contextual('<?php echo $actividad; ?>','<?php echo $nombre; ?>.'<?php echo "asd"; ?>');"   id="periodo<?php echo $periodo; ?>" class="Contenedor_periodos<?php echo $actividad; ?>"
<?php if($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente") { ?>

<?php } ?>
class="col-*-* f_inicio<?php echo $nombre; ?>">
<form method="post" action="<?php echo SGA_CURSOS_URL ?>/valorar_actividad.php"; target="_blank">
<span id="texto_activiad_<?php echo $actividad; ?>" style="color:<?php echo $infoactividad['visible']=="SI" ? "black" : ""; ?>">
<?php echo Comun::puntos_suspensivos($infoactividad['nombre_actividad'],20); ?></strong> <br/> 
<?php
if ($infoactividad['evaluable']=="SI"and $_SESSION['rol']=="estudiante" and  $_SESSION['rol']=="acudiente"   ){

     list($dia2,$mes2,$año2) = diferenciaentrefechas($infoactividad['fecha_publicacion'],$fecha_entrega);
$fecha_actual = date('Y-m-d');
list($dia,$mes,$año) = diferenciaentrefechas($fecha_entrega,$fecha_actual);
$micolor = colores($dia,$dia2);
?>
<a href="<?php echo SGA_CURSOS_URL.'/visor_actividad.php?a='.$actividad.''; ?>">
<?php
if (verificar_actividad_hecha($actividad) ==0 and $infoactividad['fecha_entrega'] < date('Y-m-d')   ) $periodocono_actividad="triste.PNG" ; 
if (verificar_actividad_hecha($actividad) ==0  and date('Y-m-d') < $infoactividad['fecha_entrega'] ) $periodocono_actividad="regalo.png"; 
if (verificar_actividad_hecha($actividad) >0 ) $periodocono_actividad="feliz.PNG"; 
}
 else {
    $periodocono_actividad="notebooka.png";
}
echo '<a target="_BLANK" href="'.SGA_CURSOS_URL.'/visor_actividad.php?a='.$actividad.'">
';
?>

    <img id="imagen_actividad<?php echo $actividad; ?>" <?php if($infoactividad['visible']=="NO")  echo "style='-webkit-filter: grayscale(1);
filter:gray; display: block;margin-left: auto;      margin-right: auto;border:none;'"; else {
   echo "style='-webkit-filter: grayscale(0);
filter:gray; display: block;margin-left: auto;      margin-right: auto;border:none;'"; 
}
?> 

width="50%" src="<?php echo SGA_COMUN_URL.'/img/png/'.$periodocono_actividad ; ?>"></img></a><br/>

<?php
if(strtolower($evaluable)=="si" and $_SESSION['rol']=="estudiante" ){
  list($dia, $mes,$año) = diferenciaentrefechas($fech_publi,$fecha_entreg);
list($dia2, $mes2,$año2) = diferenciaentrefechas(date('Y-m-d'),$fecha_entreg);
list($uno,$dos) = colores($dia,$dia2);
?>
<div title="
<?php if($dia2 > 0) { ?>
Restan <?php echo $dia2 ?> día(s)
<?php } 
else {
echo 'La Actividad finalizó el '.formatofecha($fecha_entreg) ;    
}
?>
" style="margin-top:-7px;margin-left:130px;position:absolute;width: 25px;
     height: 25px;
     -moz-border-radius: 50%;
     -webkit-border-radius: 50%;
     border-radius: 50%;
     background: <?php echo $uno; ?>;"></div>
<?php

}
if($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){ ?>
<input type="hidden" name="id_actividad" value="<?php echo $actividad;?>"> <?php } 
if($_SESSION['rol']<>"estudiante" and $_SESSION['rol']<>"acudiente" ){ ?>
<span style="position: ;width:50px!important;margin-left:20px;margin-top:-40px;" onclick="ver_actividad(this);" id="ver_actividad_<?php echo $actividad ?>" id_actividad="<?php echo $actividad ?>" visible="<?php echo $infoactividad['visible'] ?>" class="<?php echo $infoactividad['visible']=="SI" ? "icon-sga-view" : "icon-sga-view-line"; ?>" title="<?php echo $infoactividad['visible']=="SI" ? "Ocultar" : "Mostrar"; ?>"></span>
<?php } ?>
<br/></form>

</div>
 <?php   

 }
 echo '</div>'  ;

}


 } #Fin función
}
  ?>
  <span id="span_actividad_curso">
<?php
$asignacion = $_GET['asignacion'];

echo actividad_curso($asignacion,$datos_busqueda="",$campo_bd="nombre_actividad"); 
?>
</span>
<style>
#ver_actividad{
   margin-top: -8px !important;
    margin-left: 0px !important;
    height:20px !important;
    width:20px !important;
    background-size: 40px 40px !important;
}
</style>

<?php

   echo '</body>'    ;                    
?>
<hr>
</hr>
<?php 
 $sqlmateria='select id_asignatura from asignacion,materia where
asignacion.id_asignatura = materia.id_materia and asignacion.id_asignacion="'.$_GET['asignacion'].'"';
$consultan = $mysqli -> query($sqlmateria) ;




 if ($_SESSION['rol']=="docente" or $_SESSION['rol']=="admin" ){ ?>
<div  class="col-sm-12">
        <div class="row"><div>
        <div  style="margin-left:10%;width:80%;background-color:#f2721d;height:5px; "><span style="float:right;opacity:0.7"></span></div>
       <p align="center" onclick="mitoogle('#id_10')" >Planes de clase</p>
    </div>
</div></div>

<div id="id_10">
<?php 
#require_once '../Planeador/mysql.class.php'; 
#require_once'../Planeador/materias.class.php'; 
#require_once'../clases/planeacion.class.php'; 
#$academico= new Academico();
#$datos_materia=$academico->consultar_materia($_GET['asignacion']);
#print_r();
$planeacion=new Planeacion();
$todas=$planeacion->mostrar_todas_planeaciones($_GET['asignacion'],$nombre_grado);
if(!empty($todas)){
?>
<div style="margin-left:5%;margin-right:90%">
<table border="2"  class="table table-striped">
  <tr>
    <th>Número</th>
    <th>Contenido del plan</th>
    <th>Objetivos del plan</th>
    <th>Estrategia</th>
    <!--th>Actividad</th-->
    <th>Recurso</th>
<?php   $planeacion=new Planeacion();  ?>
<th>Intensidad( <?php echo $planeacion->intensidad_horaria($id_materia); ?>) </th>
    <th>Editar</th>
    <th><a target="_blank" href="<?php echo SGA_PLANEADOR_URL ?>/reporte_todos.php?id_materia=<?php echo $id_materia ?>&nombre_grado=<?php echo $nombre_grado ?>">
<img width="70%" src="<?php echo SGA_COMUN_URL ?>/img/pdf.png"></img>
    </a></th>
     <th>Eliminar</th>
  </tr>
  <tr>
<meta charset="utf-8">
<?php
$contador=0;
 foreach ($todas as $planes => $plan) { 
  if(!empty($planeacion->tiempo_plan)){
  $contador=$contador+$planeacion->tiempo_plan;
  }
  $planeacion=new Planeacion($todas[0]['id_plan']);
 # print_r($planeacion);
  ?>
  <tr>
    <td><?php echo  $planeacion->orden ; ?></td>
    <td>
      <?php echo  (Comun::puntos_suspensivos($planeacion->contenido_plan)); ?>
      <!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalLong<?php echo $todas[0]['id_plan']?>">
  ver
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModalLong<?php echo $todas[0]['id_plan']?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle"><?php echo $planeacion->objetivos_plan; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo  ($planeacion->contenido_plan); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
      
      <?php  
     ?></td>
    <td><?php echo  Comun::remover_ultimo_caracter($planeacion->objetivos_plan) ; ?></td>
    <td><?php echo  Comun::remover_ultimo_caracter($planeacion->estrategias) ; ?></td>
    <!--td><?php #echo  $planeacion->Actividada ; ?></td-->
    <td><?php echo  Comun::remover_ultimo_caracter($planeacion->recursoa) ; ?></td>
    <td><?php echo  $planeacion->tiempo_plan ; ?></td>

    <!--td><?php #echo  $planeacion->estrategiab ; ?></td>
    <td><?php echo  $planeacion->Actividadb ; ?></td>
    <td><?php echo  $planeacion->Recursob ; ?></td-->
    <td><a class="btn btn-success" href="<?php echo SGA_PLANEADOR_URL ?>/index.php?edit=1&idplan=<?php echo $planeacion->id_plan ?>&asignacion=<?php echo $_GET['asignacion'] ?>">Editar</a><br/><br/>
<a class="btn btn-warning" href="<?php echo SGA_PLANEADOR_URL ?>/duplicar_plan.php?id=<?php echo $planeacion->id_plan ?>&asignacion=<?php echo $_GET['asignacion'] ?>">Duplicar</a>

    </td>
    <td><a class="btn btn-primary" href="<?php echo SGA_PLANEADOR_URL ?>/planeador.php?descargar=1&idplan=<?php echo $planeacion->id_plan ?>">Descargar</a>

<a target="_blank" href="<?php echo SGA_PLANEADOR_URL ?>/planeador.php?pdf=1&idplan=<?php echo $planeacion->id_plan ; ?>">
  <img width="50px" src="<?php echo SGA_COMUN_URL ?>/img/pdf.png"></img>
</a>




    </td>
    <td><a class="btn btn-danger" href="<?php echo SGA_PLANEADOR_URL ?>/reporte.php?id=<?php echo $planeacion->id_plan ?>&eliminar=<?php echo $planeacion->id_plan ?>">Eliminar</td>
  </tr>
<?php } ?>
  </tr>
</table>  
</div>
</div>
</div>
<?php
}
}
#####Notas de clase
 if ($_SESSION['rol']=="docente" or $_SESSION['rol']=="admin" ){ ?>
<div  class="col-sm-12">
        <div class="row"><div>
        <div  style="margin-left:10%;width:80%;background-color:#f2721d;height:5px; "><span style="float:right;opacity:0.7"></span></div>
       <p align="center" onclick="mitoogle('#id_11')" >Notas de clase</p>
    </div>
</div></div>

<div id="id_11">
<?php 
#require_once '../clases/Academico.class.php';
#require_once '../clases/comun.class.php'; 
$notas= new Academico();
$todas=$notas->notasdeclase($_GET['asignacion']);
if(!empty($todas)){
?>
<div style="margin-left:5%;margin-right:5%">
<table border="2"  class="table table-striped">
  <tr>
    <th>Número</th>
    <th>Nota</th>
    <th>Fecha</th>
    <th>hora</th>
    <th>Editar</th>

    <th><a target="_blank" href="<?php echo SGA_REPORTES_URL ?>/cursos/reporte_edunotas.php?asignacion=<?php echo $_GET['asignacion'] ?>">
<img width="70%" src="<?php echo SGA_COMUN_URL ?>/img/pdf.png"></img>
    </a></th>
     <th>Eliminar</th>
  </tr>
  <tr>
<meta charset="utf-8">
<?php
$contador=0;

 foreach ($todas as $planes => $notas) {  ?>
  <tr>
    <td><?php echo  $notas['id_nota']  ; ?></td>
    <td><?php echo  Comun::puntos_suspensivos($notas['nota'],120)  ;  ?></td>
    <td><?php echo  Fecha::formato_fecha($notas['fecha_nota'])  ;  ?></td>
    <td><?php echo  Fecha::formato_hora($notas['hora_nota'])  ;  ?></td>
   

    <td><a class="btn btn-success" href="<?php echo SGA_CURSOS_URL ?>/edunotas.php?idnota=<?php echo $notas['id_nota'] ?>">Editar</a><br/><br/>
<!--a class="btn btn-warning" href="<?php echo SGA_PLANEADOR_URL ?>/duplicar_plan.php?id=">Duplicar</a-->

    </td>
    <td>
      <?php 
if($notas['fijar']=="1"){
  echo "Fijado en inicio";
}

      ?>


    </td>
    <td><a class="btn btn-danger" href="<?php echo SGA_CURSOS_URL ?>/edunotas.php?eliminar=<?php echo $notas['id_nota'] ?>">Eliminar</td>
  </tr>
<?php } ?>
  </tr>
</table>  
</div>
</div>
</div>
<?php
}
}



$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>

