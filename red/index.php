<?php 
require '../comun/conexion.php';
require_once ("../comun/funciones.php");
require_once("../comun/config.php");
require_once($_SERVER['DOCUMENT_ROOT'].'/'."guagua/comun/autoload.php");
$persona=new Persona($_SESSION['id_usuario']);
ob_start();
@session_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$_SESSION['barra_busqueda'] = "red";?>
<?php
#if(!isset($_SESSION['rol'])){ exit(); }
if (isset($_POST['datos'])) $parametro_buqueda = $_POST['datos']; ?>

<span id="span_buscar_red">
    <div id="false" name="contenedor"  class="jumbotron">
<input style="z-index:1" id="opciones_cursos2"  type="button" value="Opciones"  class="btn btn-warning context-menu-one" name=""/>
  <div class="container text-center">
     
<div id="false" onclick="MostrarTodosLosRed(this.id);">
    <h1  id="titulo_red"  class="fip">
        <?php if($_SESSION['rol']=="estudiante"){
            echo 'Entretenmiento';
        }
        else{
                        echo 'RECURSOS EDUCATIVOS DIGITALES';

        }
    echo '</h1>';    
              ?></div>
              
  </div>
</div>
<div class="container-fluid bg-3 text-center">    
<div class="row"></div>
<?php 
if (isset($_GET['buscar_red'])){
?>
<?php 
busqueda_red($_GET['buscar_red'],$_GET['campo']);
exit();
} ?>
</span><!-- 14 -->

<?php
function busqueda_red($parametro_buqueda="",$campo){ 
require '../comun/conexion.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/guagua'.'/'."/comun/autoload.php");
 $sql ='select distinct( `id_materia`) , `id_materia`, `nombre_materia`, `obligatoria`, `area`, `icono_materia` from materia  ';
$consulta = $mysqli ->query($sql);
if(!empty( $consulta)){
while($row = $consulta ->fetch_assoc()){
     #$arreglo[]=$row;
     mired($row['id_materia'],$parametro_buqueda,$campo);
}
}  
else{
  echo "Aún no hay recursos";
}
}
?></div>
<script type="text/javascript">
$(function(){

    document.getElementById("false").click();
    $.contextMenu({
        selector: '.context-menu-one', 
        trigger: 'hover',
        delay: 500,
        callback: function(key, options) {
    if(key=="Nuevo RED"){window.location='nuevo_red.php';}
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
  
function menu_contextual(red,nombre){
$(function() {
        $.contextMenu(        {
            selector: '.context-menu-one'+red, 
            callback: function(key, options) {
              if(key=="Descargar"){
            window.location='../comun/funciones.php?ruta_red='+red;
}

             if(key=="Modificar"){
            window.location='nuevo_red.php?id_red='+red;
}


             if (key=="Eliminar"){
    //var confirmar = window.confirmeliminar2("¿Está seguro que desea eliminar "+nombre+" ?");
    var confirmar = confirm("¿Está seguro que desea eliminar "+nombre+" ?");
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

        $('.context-menu-one'+red).on('click', function(e){
            console.log('clicked', this);
        })    
    });
}

</script>
<?php
function mired($materia,$parametro_buqueda,$campo){

@session_start();
  $lamateria= $materia;


$persona=new Persona($_SESSION['id_usuario']);
$instanciamateria=New Materias($lamateria);
@session_start();
$_SESSION['id_institucion']=1;
$sql="SELECT * FROM red WHERE red.institucion ='".$_SESSION['id_institucion']."' and CHARACTER_LENGTH((JSON_SEARCH(`materia_red`, 'all',$lamateria)))>3 ";
if ($parametro_buqueda!=""){
$sql.= ''; 
$parametro_buqueda_array = explode(" ",$parametro_buqueda);
foreach ($parametro_buqueda_array as $id => $parametro_buquedai){
$tabla='red';
if($campo=="nombre"){ $tabla ='usuario' ;}
if($campo=="nombre_materia"){ $tabla ='materia' ;}
if($campo=="nivel_eductivo"){ $parametro_buquedai = '["'.$parametro_buquedai.'"]' ;
}
if($campo<>""){
    $sql.= " and concat(LOWER(".$tabla.".".$campo.")) LIKE '%".mb_strtolower($parametro_buquedai, 'UTF-8')."%' ";
}
}
}
require_once '../comun/conexion.php';
$resultadossql=json_decode($instanciamateria->consultar_datos($sql,true),true);
#$consultan=$mysqli->query($sql);
$resultados[] = count($resultadossql);
###############Fin Buscar
$materia="";
$cat="";
$nivel_educativo_estudiante ="";
?></div>
   <div style="text-align:center;background-color:#f2721d;height:5px;margin-bottom:35px"  class="col-sm-12">       
         <p onclick="mitoogle('#id_<?php echo $lamateria; ?>')" align="center" onclick="mitoogle('#id_<?php echo $lamateria; ?>')" >
        <?php echo $instanciamateria->nombre_materia; ?></p>
    </div>


<style> 
        .flex-container {
            display: -webkit-flex;
            display: flex;
            background-color: lightgrey;
        }
        .flex-item {
            background-color: cornflowerblue;
            width: 700px;
            height: 100px;
            margin: 10px;
        }
    </style>
<div   class="cats" id="id_<?php echo $lamateria; ?>" >
<?php
foreach($resultadossql as $rowb => $rowa){ ?>
<div onclick="location.href = '../red/visor_red.php?red=<?php echo $rowa['id_red']; ?>&formato=<?php echo $rowa['formato']; ?>&enlace=<?php echo $rowa['enlace']; ?>&scorm=<?php echo $rowa['scorm']; ?>' "  onContextMenu="menu_contextual('<?php echo $rowa['id_red']; ?>','<?php echo  $rowa['titulo_red']; ?>');" style="width:160px;margin-bottom:15px;" class="context-menu-one<?php echo $rowa['id_red']; ?> btn btn-neutral col-sm-2"  >
 <h3 align="center" title="<?php echo $rowa['titulo_red'] ; ?>" ><strong><?php   $rowa['nivel_eductivo'] = str_replace("[", "", $rowa['nivel_eductivo']);$rowa['nivel_eductivo'] = str_replace("]", "", $rowa['nivel_eductivo']);
        $rowa['nivel_eductivo'] = str_replace('"','', $rowa['nivel_eductivo']);
       echo puntos_suspensivos($rowa['titulo_red'],12); ?></strong></h3>

    <img style="width:50px;margin-right:40px"  class="img-responsive" align="right" style="margin-top:-5%;max-width: 100%;" width="15%" src="<?php echo   consultar_link_icono($rowa['icono_red']); ?>        
"></img>

  </div>  
 
<?php
  $acumulador_de_resultados_consulta[]=$resultados;
  } ?>  </div> <?php
}
#echo ' </div>';
#echo ' </div>';
$contenido = ob_get_clean();
require '../comun/plantilla.php'; 
?>
