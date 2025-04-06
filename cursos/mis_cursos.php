<?php 
ob_start();
error_reporting(1);
require_once($_SERVER['DOCUMENT_ROOT'].'/'."guagua/comun/autoload.php");
#require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");

$academico=new Academico();
$persona=new Persona();
$institucion = new institucion(7);
$persona->validar_acudiente();
$_SESSION['modulo']="cursos";
$_SESSION['barra_busqueda'] = "cursos";
if (isset($_GET['buscar_mis_cursos'])){
buscar_mis_cursos($_POST['datos'], $_POST['campo']);
exit();
}
$anos_inactivos=json_encode($academico->consultar_anios());
?>
<script>
$(document).ready(function() {
 ocultar_anios_no_vigentes(<?php echo $anos_inactivos; ?>);
});
</script>
<div 
<?php if(!empty($institucion->BANNER_INSTITUCION)) { ?>
style="background-size: contain;background-image: url('<?php  echo SGA_COMUN_SGA_DATA_BANNER.'/'.$institucion->BANNER_INSTITUCION; ?>')" <?php } ?>
 id="jumbotron"  class="jumbotron">
  <div  class="container text-center">
    <?php if(empty($institucion->BANNER_INSTITUCION)) { ?>
    <h1 class="fip" >MIS CURSOS</h1> 
<?php } ?>
<?php if($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){ ?>
<div class="btn-group" id="boton_opcion_curso">
  <button  id="opciones_cursos" type="button" class="btn btn-default dropdown-toggle"  data-toggle="dropdown"> Opciones <span class="caret"></span>
  </button>
  <ul class="dropdown-menu" role="menu">
    <li><a href="crear_curso.php">Nuevo Curso</a></li>
  </ul>
</div>
   <?php } ?>
  </div>
</div>


<span id="span_buscar_mis_cursos">
    <?php buscar_mis_cursos() ?>
</span>
<?php function buscar_mis_cursos($parametro_buqueda="",$campo="nombre_materia"){
require_once ("../comun/autoload.php");
require ("../comun/conexion.php");
require_once("../comun/config.php");
require_once ("../comun/funciones.php");
$where="";
$academica=new Academico();
@session_start();
?>
<div class="container-fluid bg-3 text-center">    
<?php
$categorias=array();

?>
<div class="row">
<?php
$sqlp=$academica->ano_estudiante();
$consultap = $mysqli -> query($sqlp) ;
      $cantidad_anos=0;
        while ($rowp = $consultap->fetch_assoc()){ 
$cantidad_anos=$cantidad_anos+1;
          ?>
<div class="col-md-<?php
if (isset($_COOKIE['checked_lista_docentes']) and $_COOKIE['checked_lista_docentes']=="true"){
echo '10'; 
}else{
echo '12';
}
?> espacio_curso">
<div id='estilo_ano'></div>
<p id="pid_<?php echo $rowp['id_ano_lectivo']; ?>" onclick="mitoogle('#id_<?php echo $rowp['id_ano_lectivo']; ?>')" >
<?php echo $rowp['nombre_ano_lectivo']; ?>
<span id="estilo_categoria_curso">
Categorias en uso: <?php
$sql_cat_ano = "SELECT count(*) as num_cat_ano FROM `seguimiento_categoria_ano` WHERE ano_lectivo = '".$rowp['id_ano_lectivo']."'";
#echo $sql_cat_ano;
require(dirname(__FILE__)."/../comun/conexion.php");
$consulta_cat_ano = $mysqli->query($sql_cat_ano);
$rowca = $consulta_cat_ano->fetch_assoc();
echo $rowca['num_cat_ano']; ?>
</span>
</p>
<?php if(!isset($actual)) $actual="#id_".$rowp['id_ano_lectivo']; ?>
<div class="anos" id="id_<?php echo $rowp['id_ano_lectivo']; ?>">
<?php
if ($parametro_buqueda!=""){
$where = '';
$parametro_buqueda_array = explode(" ",$parametro_buqueda);
foreach ($parametro_buqueda_array as $id => $parametro_buquedai){
if ($campo == "nombre_materia")
$datocampo = 'LOWER(materia.nombre_materia)';
elseif($campo == "anio")
$datocampo = 'LOWER(ano_lectivo.nombre_ano_lectivo)';
elseif($campo == "nombre_categoria")
$datocampo = 'LOWER(categoria_curso.nombre_categoria_curso)';
elseif($campo == "nombre_docente")
$datocampo = 'concat(LOWER(usuario.id_usuario)," ",LOWER(usuario.nombre)," ",LOWER(usuario.apellido))';
elseif($campo == "todos")
$datocampo = 'concat(LOWER(categoria_curso.nombre_categoria_curso)," ",LOWER(materia.nombre_materia)," ",LOWER(usuario.nombre)," ",LOWER(usuario.apellido)," ",LOWER(ano_lectivo.nombre_ano_lectivo))';
else
$datocampo = 'LOWER(materia.nombre_materia)';
$where .= ' and '.$datocampo.' LIKE "%'.mb_strtolower($parametro_buquedai, 'UTF-8').'%"  ';
}
}
/**/
$categorias =consultar_categoria_curso();
foreach ($categorias as $id_cat => $nombre_cat){
    if ($_SESSION['rol']=="admin"){
     $sql=cursos_para_admin($id_cat,$rowp['id_ano_lectivo'],$where);
   }
    if ($_SESSION['rol']=="docente"){
      #echo $rowp['id_ano_lectivo'].'<br>';
 $sql=cursos_para_docente($id_cat,$rowp['id_ano_lectivo'],$where);
    }
if ($_SESSION['rol']=="acudiente"){
$sql=cursos_para_padres($id_cat,$rowp['id_ano_lectivo'],$where);
    }
   if ($_SESSION['rol']=="estudiante"){
  $sql=cursos_para_estudiante($id_cat,$rowp['id_ano_lectivo'],$where);
    }    
    
    #echo $sql;
$consultan = $mysqli->query($sql) ; 
    ?>
<div class="row">
    <div class="col-sm-1"></div>
    <?php if($consultan->num_rows>0){ ?>
    <div class="col-sm-11">
        <div id="separador_cursos"></div>
        <p title ="Total de Cursos: <?php echo  $consultan->num_rows ?>" style="cursor:pointer;" id="id_<?php echo $rowp['id_ano_lectivo'].$id_cat; ?>"  onmouseup="ocultar_ano_cat('cat_<?php echo $rowp['id_ano_lectivo'].$id_cat; ?>');" class="Abckids"><?php echo $nombre_cat; ?><span id="separador_cursos_encontrados"><?php echo " Cursos Encontrados:".$consultan->num_rows; ?></span></p>
    </div>
    <?php } ?>
</div><div class="cats" id="cat_<?php echo $rowp['id_ano_lectivo'].$id_cat; ?>">
<div class="row">
<?php
$mistooltip = "";
while ($rowa = $consultan ->fetch_assoc()){ 
  ?>
<div contextmenu="menu_curso<?php echo $rowa['id_asignacion'] ?>" id="ficha_curso<?php echo $rowa['id_asignacion'] ?>" style="text-align:center;margin-bottom:2%;margin-right:55px;margin-left:5%;border:2px solid #ccc;border-radius:15px;"  class="col-sm-2 menu_curso<?php echo $rowa['id_asignacion'] ?>  droppable" id_asignacion="<?php echo $rowa['id_asignacion'] ?>">
      <h4 class="Abckids"><strong><span
            <?php if($rowa['visible']=="NO") echo "style=color:gray;" ?>  id="texto_curso_<?php echo $rowa['id_asignacion'] ?>" title="<?php echo $rowa['nombre_materia']; ?>" class="materia_droppable"><?php $puntos = puntos_suspensivos($rowa['nombre_materia'],20);
      echo mb_strtoupper($puntos,'UTF-8');
      ?></span></strong>
      <?php
   
      $mistooltip .= '$("#texto_docentecurso_'.$rowa['id_asignacion'].'" ).tooltip({ content: "<img height=\'20\' src=\''.READFILE_URL.'/foto/'.validarfoto($rowa['foto']).'\'></img>"});';
     #echo $mistooltip
 ?>
      </h4>
      <h5 class="Abckids">Docente:
        
      <?php echo $rowa['id_asignacion'];?>
      <span
      <?php
      if($rowa['visible']=="NO") echo "style=color:gray;" ?>
      onmouseover="cargar_tooltips();"
      id="texto_docentecurso_<?php echo $rowa['id_asignacion']; ?>" class="docente_droppable"><?php echo $rowa['nombre_docente'].' '.$rowa['apellido_docente']; ?></span></h5>
    <a id="materia_<?php echo $rowa['id_asignacion'] ?>" href ="<?php echo SGA_CURSOS_URL?>/curso.php?asignacion=<?php echo $rowa['id_asignacion']; ?>">
    <img    id="iconomateria_<?php echo $rowa['id_asignacion'] ?>"     <?php if($rowa['visible']=="NO")  echo "style='-webkit-filter: grayscale(1);
filter:gray; display: block;margin-left: auto;      margin-right: auto;border:none;'"  ?>
 width="70%" height="70%" src="<?php echo consultar_link_icono($rowa['icono_asignacion']); ?>" title="Descripción: <?php echo $rowa['descripcion']; ?>" class="img-responsive <?php if($rowa['visible']=="NO") echo 'grises'; ?>" style="margin-left:30px!important" alt="Image">
   </a>   
    <span id="botones_curso_<?php // echo $i; ?>">
<?php if(($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" )){ ?>
<span style="width:50px!important;margin-left:40px;margin-top:-15px;" onclick="ver_curso(this);" id="ver_curso_<?php echo $rowa['id_asignacion'] ?>" id_curso="<?php echo $rowa['id_asignacion'] ?>" visible="<?php echo $rowa['visible'] ?>" class="<?php echo $rowa['visible']=="SI" ? "icon-sga-view" : "icon-sga-view-line"; ?>" title="<?php echo $rowa['visible']=="SI" ? "Ocultar" : "Mostrar"; ?>"></span>
<?php } ?>
</form>
<?php
$academica->componente_context_menu($rowa['id_asignacion'],$rowa['nombre_materia']);
?>
<script>
 $(function(fn){
    fn.contextMenu({
    selector: '.menu_curso<?php echo $rowa['id_asignacion'] ?>', 
    items: fn.contextMenu.fromMenu($('#menu_curso<?php echo $rowa['id_asignacion'] ?>'))
});
});
</script>
<a style="display:none" class="btn_duplicar" id="duplicar_<?php echo $rowa['id_asignacion'] ?>" onclick="if_confirm_swal('¿Esta seguro de duplicar el curso <?php echo $rowa['nombre_materia'];  ?>?','clonar_curso(\'<?php echo $rowa['id_asignacion']; ?>\');','false','Confirmar','Cancelar','info','Guagua');" url="#" value="Duplicar">
    <img width="45px" src="<?php echo SGA_COMUN_URL.'/'.'img/png/line-15-icons.png' ; ?>" title = "Duplicar <?php echo $rowa['nombre_materia'];  ?>"></img></a>
<?php if(($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" )){ ?>
    <a id="modificar_<?php echo $rowa['id_asignacion'] ?>" href="modificar_curso.php?icon=<?php  echo $rowa['icono_asignacion'];  ?>&area=<?php  echo $rowa['ano_lectivo'];  ?>&ano_lectivo=<?php  echo $rowa['ano_lectivo'];  ?>&id_materia=<?php  echo $rowa['id_materia'];  ?>&nombre_materia=<?php echo $rowa['nombre_materia']; ?>&id_doc=<?php echo $rowa['id_docente']; ?>&asignacion=<?php echo $rowa['id_asignacion']; ?>&descripcion=<?php echo $rowa['descripcion']; ?>&nombre_docente=<?php echo $rowa['nombre_docente'].' '.$rowa['apellido_docente']; ?>" >
  <!--img style="position:absolute;margin-top:12px;margin-left:0px;" width="45px" src="<?php echo SGA_COMUN_URL.'/'.'img/png/settings-10.png' ; ?>" title = "Modificar <?php echo $rowa['nombre_materia'];  ?>"></img--></a> <?php } ?>
      </span>
      </div>  
<?php } ?>   
</div>
</div>
<?php } ?>
</div>
<?php } ?>
</div><!--col-md-10-->
</div><!--row-->
</div>
<?php }//Fin function buscar_mis_cursos ?>
<br>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>
