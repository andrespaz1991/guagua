<?php

ob_start();

@session_start();
$_SESSION['id_institucion']=1;
// Motrar todos los errores de PHP
error_reporting(-1);

// No mostrar los errores de PHP
error_reporting(0);

// Motrar todos los errores de PHP
error_reporting(E_ALL);

// Motrar todos los errores de PHP
ini_set('error_reporting', E_ALL);
unset($_SESSION['barra_busqueda']);

if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){ ?>

<script >

$(document).ready(function(){

required_en_formulario(id_formulario="form_nuevo_curso",color="red",elemento="*");

 });

</script>



<div class="jumbotron">

  <div class="container text-center">

    <h1 class="fip">CREAR CURSO</h1>      

  </div>

</div>

<div class="container-fluid bg-3 text-center">    

 <div class="row">

    <br/>

    <form id="form_nuevo_curso" action="" method="POST" ENCTYPE="multipart/form-data">

        <div class="form-group">

   <div class="col-xs-12">

<label  for="">Nombre Curso</label>



<select name="curso" class="form-control">

  <?php 

require '../comun/conexion.php';

    $sqles = 'SELECT * FROM materia';

$consultaes = $mysqli -> query ($sqles);

while ($rowes = $consultaes ->fetch_assoc()) {?>

   <option value="<?php echo $rowes['id_materia'] ; ?>" ><?php echo $rowes['nombre_materia'] ; ?></option>

   <?php } ?>

</select>

	<input  placeholder="curso de ejemplo" class="form-control" required type="hidden" name="cursos" id="curso"/></div>

</div>

           <div class="form-group">

   <div class="col-xs-12">  

	         <label for="">Docente</label>

<?php if($_SESSION['rol']=="admin"){ ?>

<input value="<?PHP $_SESSION['id_usuario']; ?>" class="form-control" required placeholder="Seleccione el docente" autocomplete="off" list="suggestionList" id="answerInput">

<datalist  id="suggestionList">



  <?php 

require '../comun/conexion.php';

    $sqles = 'SELECT * FROM usuario order by usuario.apellido asc';

$consultaes = $mysqli -> query ($sqles);

while ($rowes = $consultaes ->fetch_assoc()) {?>

   <option label ="<?php echo $rowes['id_usuario']; ?>" data-value="<?php echo $rowes['id_usuario'] ; ?>" ><?php echo $rowes['nombre'].' '.$rowes['apellido']  ; ?></option>

<?php 

}

?>

</datalist>

<input type="hidden" name="doc" id="answerInput-hidden"></p>



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

<?php } ?>

<?php if($_SESSION['rol']=="docente"){

 require '../comun/autoload.php';

      $persona=new Persona($_SESSION['id_usuario']);

  ?>

<p><input type="hidden" name="doc" value="<?php echo $_SESSION['id_usuario'] ?>">

<?php echo $persona->nombre." ".$persona->apellido ?></p>

<?php } ?>

</div></div>

    <div class="form-group">

   <div class="col-xs-12">  

<label for="">Categoria</label>

	    <select id="categoria_curso" class="form-control" required name="categoria_curso">

	        <?php 

	        require '../comun/conexion.php';

	        $sqlcategoria ='select * from categoria_curso order by id_categoria_curso desc ';

	        $consultacategoria= $mysqli ->query($sqlcategoria);

	  while ($rowcategoria = $consultacategoria ->fetch_Assoc()){ ?>

<option value="<?php echo $rowcategoria['id_categoria_curso']; ?>"><?php 

echo $rowcategoria['nombre_categoria_curso'];

?></option>

	        <?php } ?>

	    </select>

</div></div>



<label id="lb_institucion"  for="">Institución</label>
<?php
require("../comun/autoload.php");
$institucion=new Institucion(1);
$instutuciones=$institucion->datos_institucion(1);
?>

<select class="form-control" id="institucion" name="institucion">

<?php 


foreach ( $instutuciones as $key => $value) 

  {  ?> <option value="<?php echo $value['id_institucion_educativa'] ?>"><?php echo $value['nombre_institucion'] ?></option>   

<?php  } ?>

</select><br>







	    <label for="">Año Lectivo</label>

	    <?php   

require '../comun/conexion.php';

$sql ='select * from ano_lectivo'; ?>

<select required class="form-control" required id="ano_lectivo" name="ano_lectivo">

  <?php

    $consulta = $mysqli->query ($sql);

    while ($row= $consulta ->fetch_assoc()){   ?>

    <option <?php if ($row['nombre_ano_lectivo']==date('Y')) echo 'selected';  ?> value="<?php echo $row['id_ano_lectivo']; ?>" >

<?php echo $row['nombre_ano_lectivo']; ?>

    </option> <?php } ?>

</select>

  <div class="form-group">

   <div class="col-xs-12">  

<?php

echo '<p><label for="area">Área:</label>';

$sql4= "SELECT * FROM area;";

echo '<select class="form-control" name="area" id="area"  required>';

echo '<option value="">Seleccione una opci&oacute;n</option>';

$consulta4 = $mysqli->query($sql4);

while($row4=$consulta4->fetch_assoc()){

echo '<option value="'.$row4['id_area'].'"'; if (isset($row['area']) and $row['area'] == $row4['id_area'] ) echo " selected ";

elseif($row4['id_area']==9){

echo " selected ";

}



echo '>'.$row4['nombre_area'].'</option>';

}

echo '</select></p>';







?></div></div>

 <div class="form-group">

   <div class="col-xs-12"> 

	    <label for="">Descripción Curso</label><br/>

</div></div>

<textarea class="form-control" placeholder="Observación.." name="descripcion" rows="4" cols="50"></textarea>

 <div class="form-group">

   <div class="col-xs-12"> 

	    <label for="">Visible</label><br/>

	    <select required class="form-control" required id="visible" name="visible">

<option value="SI">SI</option>

<option value="NO">NO</option>



</select>

</div></div>

<div class="form-group">

   <div class="col-xs-12">

    	    <label for="">Imagen Portada del Curso</label><br/>

 <input id="subirportada" onchange="ValidarArchivo(this);validar_resolución(this)" name="portada[]" class="form-control" type="file" multiple="multiple" />

       </div></div><br/><br/><br/>

   <div class="form-group">

   <div class="col-xs-12"> 

  <br/>

   <label for="">Icono Representativo:</label>

<div class="btn-toolbar btn-lg" role="toolbar">

    <input type="hidden" name="icon" id="icono_seleccionado" >

<img title="Pulse aqui para cambiar el icono" width="50px" id="icono_seleccionado_img" src="<?php echo SGA_COMUN_URL ?>/img/png/app.png" onclick="parent.buscar_iconos();document.getElementById('btn_elegir_icono').click();">

<span style="display:none">

<?php boton_modal_elegir_icono();?>

</span>

</div>

    </div>

  </div>



</div>



</p><p>

      </div></div>

   

              <button type="submit" class="btn btn-success btn-md">Crear</button>



</form>

<?php ventana_modal_elegir_icono();?>

<?php ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="icono no creado, revise su información e intentete nuevo" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();" callback_0="false" callback="parent.buscar_iconos();"'); ?>

<?php 
if (isset($_POST['doc'])){

    require '../comun/conexion.php';

$_POST['icon'] = str_replace("icon-sga-","",$_POST['icon']);

        $id_materia=$_POST['curso'];
       // session_start();
echo $sqli = 'INSERT INTO `asignacion`(id_curso, ano_lectivo,`id_docente`, `id_asignatura`, `descripcion`,id_categoria_curso,visible, institucion_educativa,icono_asignacion) VALUES
("'.$id_materia.'","'.$_POST['ano_lectivo'].'","'.$_POST['doc'].'","'.$id_materia.'","'.$_POST['descripcion'].'","'.$_POST['categoria_curso'].'","'.$_POST['visible'].'","'.$_POST['institucion'].'","'.$_POST['icon'].'")';
$consultai = $mysqli ->query($sqli);
 $asignacion = $mysqli->insert_id; 

 $categoria =$_POST['categoria_curso'];

 $ano_lectivo=$_POST['ano_lectivo'];

require_once '../comun/funciones.php';

# inscribir_estudiante_materia($asignacion,$categoria,$ano_lectivo);

if(!empty($_FILES)){

  $tamaño_maximo=tamaño_maximo();

$formatos=formatos();

$total = count($_FILES['portada']['name']);// Contamos la cantidad de archivos
for($i=0; $i<=$total; $i++) {// Recorremos cada archivo

      $nombre_archivo=$_FILES['portada']['name'][$i];

    $ruta_tmp_archivo = $_FILES['portada']['tmp_name'][$i]; 

    if ($ruta_tmp_archivo != ""){

$extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION));

if (in_array($extensión_archivo, $formatos)){echo "El formato no es valido";  exit(); } 

if(filesize($_FILES['portada']['tmp_name'][$i]) > $tamaño_maximo ) {

              echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo;

              exit(); 

            }

$ruta_destino = "portada/".$asignacion.'.'.$extensión_archivo;

echo 'tmp'.$ruta_tmp_archivo;

echo 'des'.$ruta_destino;



 if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) {

$sql_actualizar_banner='UPDATE `asignacion_academica` SET `portada_asignacion`="'.$ruta_destino.'" WHERE id_asignacion="'.$asignacion.'"';

$consulta_banner=$mysqli->query($sql_actualizar_banner);

 }

   }

}
}
////
?>

<script type="text/javascript">

   alert2('Curso Creado correctamente');

   setTimeout(function(){

   //window.location="mis_cursos.php"; 

   },3000);



</script>

  <?php                  }

}

$contenido = ob_get_clean();

require ("../comun/plantilla.php");

?>