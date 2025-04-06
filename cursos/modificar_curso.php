
<?php
ob_start();
@session_start(); 
if (isset($_SESSION['rol']) and $_SESSION['rol']<>"estudiante") { 
#require_once '../comun/funciones.php';
require_once($_SERVER['DOCUMENT_ROOT'].'/'.'guagua'.'/'."/comun/autoload.php");
$curso=new Curso($_GET['asignacion']);
$persona=new Persona();
$academico=new Academico;
#foreach ($datos_curso as $key => $info_curso) { ?>
<div id="jumbotron" class="jumbotron" <?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
style="background-image: url('<?php echo SGA_CURSOS_URL.'/'.  $portada_asignacion; ?>');no-repeat left center;"
<?php } ?>>
  <div class="container text-center">
<h1 <?php if(isset($portada_asignacion) and $portada_asignacion<>""){ ?>
  style="opacity:0.01"> <?php } ?> class="fip">MODIFICAR CURSO</h1>      
  </div>
</div>
<div class="container-fluid bg-3 text-center">    
 <div class="row">
    <br/>
    <form action="" method="POST"  ENCTYPE="multipart/form-data">
         <div class="form-group">
  <div class="col-xs-12">
             <label for="">Modificar Curso</label>
	<input Placeholder="Nombre Curso" type="text" class="form-control" name="curso" value="<?php echo $curso->nombre_materia ; ?>"/></div></div><br/>
	<br>
<?php 
 if ($_SESSION['rol'] <> "docente" or $_SESSION['rol'] <> "admin") {   ?>
       <div class="form-group">
  <div class="col-xs-12">
      <br/>

        <label for="">Docente</label>

<input class="form-control" placeholder="Seleccione el docente" value="<?php echo $curso->id_docente.' ' ?>"  autocomplete="off" list="suggestionList" id="answerInput">
<?php $persona->listado_rol(); ?>
</div></div>
<input value="<?php echo $curso->id_docente; ?>" type="hidden" name="doc" id="answerInput-hidden"></p>

<script>

     document.querySelector('input[list]').addEventListener('input', function(e) {

    var input = e.target,

        list = input.getAttribute('list'),

        options = document.querySelectorAll('#' + list + ' option'),

        hiddenInput = document.getElementById(input.id + '-hidden'),

        inputValue = input.value;

    hiddenInput.value = inputValue;

    for(var i = 0; i < options.length; i++) {

        var option = options[i];

        if(option.innerText === inputValue) {

            hiddenInput.value = option.getAttribute('data-value');

            break;

        }

    }

});

</script>

<?php  } ?>

 <div class="form-group">

  <div class="col-xs-12">

      <br/>

<laabel for="">Categoria</label><br/>

  <?php $categorias=$curso->todas_categoria_curso(); 
#print_r($curso->id_categoria_curso);
  ?>

	    <select class="form-control" name="categoria_curso">
	        <?php 
foreach ($categorias as $key => $rowcategoria){ ?>
<option   <?php @session_Start();
 if($rowcategoria['id_categoria_curso']==$curso->id_categoria_curso) echo "selected"; ?> value="<?php echo $rowcategoria['id_categoria_curso']; ?>"><?php 
echo $rowcategoria['nombre_categoria_curso'];
?></option>
	        <?php } ?>
	    </select></div></div>
<br/><br/><br/><br/>



<div class="form-group">

  <div class="col-xs-12"><br/>

<label id="lb_institucion"  for="">Institución</label>

<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/'.'guagua'.'/'."/comun/autoload.php");
$institucion=new Institucion;
$instutuciones=$institucion->datos_institucion(7);
#echo "<pre>";
#print_r($instutuciones);
#echo "</pre>";

?>
<select class="form-control" id="institucion" name="institucion">

<?php 

foreach ($instutuciones as $key => $value) { ?> <option
<?php
if($curso->institucion_educativa==$value['id_institucion_educativa']){
  echo "selected";
} ?>
   value="<?php echo $value['id_institucion_educativa'] ?>"><?php echo $value['nombre_institucion'] ?></option>   
<?php  } ?>

</select><br>


<?php
$info_ano_lectivo=$academico->ano_lectivo();?>
   <label for="">Año Lectivo</label><br/>
  <select class="form-control" name="ano_lectivo">
    <?php
  
    foreach ($info_ano_lectivo as $key => $info_ano_lectivo){   ?>

    <option <?php if ($info_ano_lectivo['id_ano_lectivo']==$curso->ano_lectivo) echo 'selected';  ?> value="<?php echo $info_ano_lectivo['id_ano_lectivo']; ?>" >

<?php echo $info_ano_lectivo['nombre_ano_lectivo']; ?>

    </option> <?php } ?>

</select><br/></div></div>

<div class="form-group">

  <div class="col-xs-12">

<?php

echo '<p><label for="area">Área:</label>';

echo '<select class="form-control" name="area" id="area"  required>';

echo '<option value="">Seleccione una opci&oacute;n</option>';

$info_area=$academico->area();

foreach($info_area as $key => $row4 ){

echo '<option value="'.$row4['id_area'].'"';if ( $row4['id_area'] ==$curso->area) echo " selected ";echo '>'.$row4['nombre_area'].'</option>';

}

echo '</select></p>';

?></div></div>

<div class="form-group">

  <div class="col-xs-12">

      <label for="">Descripción Curso</label><br/>

<textarea placeholder="Una breve descripción de prueba..." class="form-control" name="descripcion" rows="4" cols="50"><?php echo $curso->descripcion ; ?></textarea><br/>

<div class="form-group">

   <div class="col-xs-12"> 

    	    <label for="">Visible</label><br/>

	    <select  class="form-control"  id="visible" name="visible">

<option <?php if($curso->visible =="SI") echo 'selected'; ?> value="SI">SI</option>

<option  <?php if($curso->visible =="NO") echo 'selected'; ?> value="NO">NO</option>

</select>

</div></div>

<div class="form-group">

   <div class="col-xs-12">

    	    <label for="">Imagen Portada del Curso <?php

if(isset($datos_curso['portada_asignacion']) and  $datos_curso['portada_asignacion']<>""){

    	        echo '<a target="_blank" href="'.$datos_curso['portada_asignacion'].'">Ver Actual</a>';

    	    }

    	    

    	    ?></label>

    	    

    	    

    	    <br/>

 <input id="subirportada" onchange="ValidarArchivo(this);validar_resolución(this)" name="portada[]" class="form-control" type="file" multiple="multiple" /><br/>

       </div></div><br/><br/><br/><br/>


<label for="">Icono Representativo:</label>

<br/><div class="btn-toolbar btn-lg" role="toolbar">

    <input type="hidden" name="icon" id="icono_seleccionado" value="11">

<img title="Pulse aqui para cambiar el icono" width="50px" id="icono_seleccionado_img" src="<?php 

#echo $curso->consultar_link_icono($curso->icono_asignacion); 

 ?>" onclick="parent.buscar_iconos();document.getElementById('btn_elegir_icono').click();">

<span style="display:none">

<?php #boton_modal_elegir_icono();?>

</span>

</div>

</p><p>

      </div></div>

<input class="btn-success" type ="submit" name="" value="Modificar"></input>

    </form>

</form>

<?php

#}

 #ventana_modal_elegir_icono();?>

<?php  #ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="icono no creado, revise su información e intentete nuevo" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();" callback_0="false" callback="parent.buscar_iconos();"'); ?>

</div>

</div>

<?php 

if (isset($_POST['curso'])){

if (isset($_FILES['portada'])){

    $total = count($_FILES['portada']['name']);

for($i=0; $i<=$total-1; $i++) {

        require_once '../comun/funciones.php';

    $formatos = formatos();

    $tamaño_maximo= tamaño_maximo(); 

    $nombre_archivo=$_FILES['portada']['name'][$i];

$ruta_tmp_archivo = $_FILES['portada']['tmp_name'][$i];

  if ($nombre_archivo != ""){

  $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION));

 if (in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; exit(); }

 if(filesize($_FILES['portada']['tmp_name'][$i]) > $tamaño_maximo ) {

              echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 

              exit(); 

            }

$ruta_destino = "portada/".$info_curso['asignacion']. '.'.$extensión_archivo;

 move_uploaded_file($ruta_tmp_archivo,$ruta_destino);

     

 }

}

   

}

require '../comun/conexion.php';

    $sql = 'insert into materia (nombre_materia) VALUES ("'.$_POST['curso'].'")' ;

    $consulta = $mysqli -> query ($sql) ;

         $id_materia = $mysqli->insert_id ;

 $_POST['icon'] = str_replace("icon-sga-","",$_POST['icon']);



 $sqli2 = 'UPDATE `materia` SET   `area`= "'.$_POST['area'].'", `nombre_materia`= "'.$_POST['curso'].'" ';

  $sqli2.=' WHERE `id_materia` ="'.$info_curso['id_materia'].'"';

$consulta2 = $mysqli -> query ($sqli2) ;

@session_start(); 



 $sqli = 'UPDATE `asignacion` SET `icono_asignacion` = "'.$_POST['icon'].'" , ano_lectivo="'.$_POST['ano_lectivo'].'",id_categoria_curso="'.$_POST['categoria_curso'].'", id_docente ="'.$_POST['doc'].'" , `descripcion`="'.$_POST['descripcion'].'",   institucion_educativa="'.$_POST['institucion'].'" ';

  

if (isset($nombre_archivo) and $nombre_archivo <> ""){

      $sqli.= ' , portada_asignacion= "'.$ruta_destino.'" '; 

 }

 

$sqli.=' WHERE id_asignacion ="'.$_GET['asignacion'].'" ';



if (($consultai = $mysqli -> query ($sqli)) or $consulta2>0 ) { 

  ?>

 <script type="text/javascript" >

      alert2('Curso modificado correctamente');

      setTimeout(function(){

        window.location="curso.php?asignacion=<?php echo $_GET['asignacion'] ?>"; 

      },3000);

</script>

<?php }else{ ?>



    <?php //session_start();

     if ($_SESSION['rol']=="docente") { ?> 

  <script type="text/javascript" >

      alert2('Verificar datos');

      setTimeout(function(){

        window.location="curso.php?asignacion=<?php echo $_GET['asignacion'] ?>"; 

      },3000);

</script>	

<?php } else { ?>

  <script type="text/javascript" >

        alert ('Verificar datos');

  window.location="curso.php?asignacion=<?php echo $info_curso['asignacion'] ?>"; 

</script>

<?php }  }

}

}
$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");

?>