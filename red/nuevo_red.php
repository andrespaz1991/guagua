<?php 
ob_start();
@session_start();
require_once("../comun/config.php");
require_once("../comun/funciones.php");
require_once("../comun/autoload.php");
require_once("../clases/Red.Class.php");
require_once("../clases/Materias.Class.php");
require '../clases/Academico.Class.php';
require "../comun/conexion.php"; 
unset($_SESSION['barra_busqueda']); 

$tamaño_maximo = tamaño_maximo();
$formatos=formatos();
$carpeta_destino = READFILE_SERVER."/banco_red/";
$carpeta_destino_url = SGA_URL."/comun/sga-data/banco_red/";
@mkdir($carpeta_destino);

if(isset($_GET['id_red'])){
  $red=new Red($_GET['id_red']);
}else{
  $red=new Red();
}

$academico=new Academico();
if(!isset($_GET['asignacion'])) $_GET['asignacion']="";
$materia=new Materias($_GET['asignacion']);
$info_materia=$materia->datos_materia(); 
$listado_materias=$materia->datos_materia(1); #lista todas las 
$listado_nivelEducativo=$academico->NivelEducativo(); #lista todas 
if(!empty($red->nivel_eductivo)){
  $nivelEducativo=json_Decode($red->nivel_eductivo,true);
}

if (isset($_GET['id_red']) and $_GET['id_red']!= ""){
  $name = "Modificar" ;
  }else{
  $name="Guardar";
  }
if (isset($_GET['a'])){ 
$tipoBoton='button';
  } else {  
  $tipoBoton='submit';
}

if (!isset($_GET['a'])){ 
?>
<script>
$(document).ready(function(){
required_en_formulario(id_formulario="form_nuevo_red",color="red",elemento="*");
revisar_vermas();
 });
</script>
<div class="jumbotron">
  <div class="container text-center">
    <h1 id="jumbo_nuevo_red" class="fip">RECURSO EDUCATIVO DIGITAL </h1>      
  </div>
</div><br><br>
<?php
}
?>
<form  id="form_nuevo_red" id="formulario" class="form-horizontal" role="form" action="" method="POST" ENCTYPE="multipart/form-data" >
<!--form role="form"-->
  <div class="form-group">
  <div class="col-xs-12">
<p><input name="id_red" type="hidden"  value="<?php echo $red->id_red; ?>"></p>
  <label for="titulo_red">Titulo Red:</label>
<input placeholder="eje:Naturaleza" id="id_red2"  required class="form-control"  name="titulo_red" type="text" id="titulo_red" value="<?php echo $red->titulo_red; ?>">
</div>
</div>
<label for="">Icono Representativo:</label>
<div class="btn-toolbar btn-lg" role="toolbar">
<input type="hidden" name="icono_seleccionado" id="icono_seleccionado" value="<?php $icono= $red->iconos_red();  echo $red->icono_red;?>">
<img title="Pulse aqui para cambiar el icono" width="50px" id="icono_seleccionado_img" src="<?php echo SGA_COMUN_URL.'/img/png/'.$icono; ?>" onclick="parent.buscar_iconos(); document.getElementById('btn_elegir_icono').click();">
<span style="display:none">
<?php boton_modal_elegir_icono();?>
</span>
</div>
</p><p>
   <div class="form-group">
  <div class="col-xs-12">
    <label >Adjunto:</label><br><label class="checkbox-inline">
    <input onclick="tipoinput(this.value)" type="radio" class="" name="adjunto"  checked id="adjunto" value="si" <?php if($red->adjunto=="si") echo "checked"; ?><?php  ?>>Si</label>
    <label class="checkbox-inline">
    <input onclick="tipoinput(this.value)" type="radio"  class="" name="adjunto"   value="no" <?php if($red->adjunto=="no") echo "checked" ;?>>No</label></p>
    </div>
    </div>
    
    <div class="form-group">
  <div class="col-xs-12">
<p><label for="enlace">Enlace:</label>
<input <?php if (!isset($red->id_red)) echo 'required'; ?>
 onchange="ValidarArchivo(this); validar_resolución(this)" class="form-control" name="enlace[]"  type="
 <?php
 if($red->adjunto=="no"){
   echo "text";
 }else{
  echo "file";
 }
 ?>
 " id="enlace" value="<?php echo $red->enlace;?>"></p>
</div>
</div>
  <div class="form-group">
  <div class="col-xs-12">
<p><label for="enlace">Cantidad de Estrellas Para Visualizar:</label><input  class="form-control" name="cantidad_estrellas"  type="number" id="cantidad_estrellas" value="<?php  echo $red->cantidad_estrellas;?>"></p>
</div>
</div>

  <div class="form-group">
  <div class="col-xs-12">
<p><label for="scorm">Scorm:</label><br/>
<label class="checkbox-inline">
    <input type="radio" class="checkbox-inline" name="scorm" id="scorm[1]"  value="si" <?php if($red->scorm=="si") echo "checked"; ?> /> 
    Si </label><label class="checkbox-inline"><input type="radio"  name="scorm" id="scorm[2]" checked ="true"  value="no" <?php if ($red->scorm=="no") echo "checked "?>>No</label><br></p>
    </div>
    </div>
    <div class="form-group">
  <div class="col-xs-12">   
<p><label for="label_asignatura">Asignatura:</label>
<?php

if(!empty($red->materia_red)){
  if(in_array($listado_materias[0]['id_materia'],json_decode($red->materia_red))){
    echo "Selected";
   }
}
   
?>

<select required class="form-control" id="asignatura"  name="asignatura[]" multiple >
  <?php
  foreach($listado_materias as $listadoMaterias => $valor){  ?>
         <option <?php   
if(!empty($red->materia_red)){         
if(in_array($valor['id_materia'],json_decode($red->materia_red))){
  echo "Selected";
 } }  ?>  value="<?php echo $valor['id_materia']; ?>"><?php echo $valor['nombre_materia']; ?> </option>
    <?php } ?>
</select>
</div></div>
  <div class="form-group">
  <div class="col-xs-12">
<p><label for="nivel_eductivo">Nivel Eductivo:</label>

<select required class="form-control" name="nivel_eductivo[]" id="nivel_eductivo" multiple >
<?php
  foreach($listado_nivelEducativo as $listadonivelEducativo => $valor){  ?>
         <option <?php 
if(!empty($red->materia_red)){           
if($valor['id_categoria_curso']==$nivelEducativo){
 echo "Selected";
}
 }  ?>  value="<?php echo $valor['id_categoria_curso']; ?>"><?php echo $valor['nombre_categoria_curso']; ?> </option>
    <?php }  ?>
</select>
</p></div></div>
     <div class="form-group">
  <div class="col-xs-12">
<p><label for="descripcion">Descripción:</label>
<textarea id="descripcion" class="form-control" placeholder="Lectura comprensiva.." rows="4" cols="50" name="descripcion"><?php echo $red->descripcion ; ?></textarea><br></div></div>
<div class="checkbox2">
  <label><input onclick="revisar_vermas()" type="checkbox" id="vermas" name="vermas" value="vermas">&nbsp;Ver Más</label>
</div>
<br> 
<body >
 
<div id="toogle">
<div class="form-group">
  <div class="col-xs-12">
<label for="idioma_red">Idioma Red:</label><input class="form-control" name="idioma_red"type="text" id="idioma_red" value=" <?php echo $red->idioma_red; ?>" ></div></div>

<p><label for="dificultad">Dificultad:</label>
<div class="form-group">
  <div class="col-xs-12">
<select class="form-control"  id="dificultad" name="dificultad">
<option value="2" <?php if ($red->dificultad=="2") echo "selected"; ?> >Media</option>
<option value="3" <?php if  ($red->dificultad=="3")  echo "selected"; ?> >Alta</option>
<option value="1" <?php  if($red->dificultad=="1")  echo "selected"; ?> >Baja</option>
</select></div></div>
<div class="form-group">
  <div class="col-xs-12">
<br><label for="palabras_clave">Autor:</label><input class="form-control"name="autor"type="text" placeholder="eje:Andrés Paz"  id="autor" value="<?php echo $red->autor; ?>"></div></div>
<div class="form-group">
  <div class="col-xs-12">
<p><label for="palabras_clave">Palabras Clave:</label></p><p><textarea placeholder="Lenguaje,innovación,educación.." class="form-control" name="palabras_clave" cols="60" rows="10"id="palabras_clave" ><?php echo $red->palabras_clave; ?></textarea></p>
</div></div>
<p><input class=""name="responsable"type="hidden" id="responsable" value="<?php echo $red->responsable ?>"></p>
<p><input class=""name="formato"type="hidden" id="formato" value=" <?php echo $red->formato ;?>" ></p>
<p>
    <div class="form-group">
  <div class="col-xs-12">
    <label style="display:none;" for="contexto">Contexto Educativo:</label><input class="form-control" name="contexto" type="hidden" placeholder="" id="contexto" value=" <?php echo $red->contexto;  ?>"></p></div></div>
    <div class="form-group">
  <div class="col-xs-12">
<p><label for="tipo_interacción">Tipo de  Interacción:</label>

<input  class="form-control" placeholder="eje: Alto,Medio,Bajo"  name="tipo_interacción"type="text" id="tipo_interacción" value="<?php echo $red->tipo_interacción; ?>"></p></div></div>
 <div class="form-group">
  <div class="col-xs-12">
<p><label for="tipo_recurso_educativo">Tipo Recurso Educativo:</label></p><p><textarea placeholder="eje:cuestionario,software educativo,diapositiva.." class="form-control" name="tipo_recurso_educativo" cols="60" rows="10"id="tipo_recurso_educativo" ><?php echo $red->tipo_recurso_educativo; ?></textarea></p>
</div></div>
<p><input class=""name="estrellas"type="hidden" id="estrellas" value=" <?php echo $red->estrellas; ?>"></p>
</div>
 <div class="checkbox2">
      <label><input type="checkbox" id="terminos" name="terminos" value="terminos" required value="">&nbsp;Acepto la responsabilidad por el anterior recurso digital</label></div>
<br>
<div id='mensajedeespera'></div>
<p><input class="btn btn-success" <?php 
 ?> name="<?php echo $name; ?>" id="guardarred" type="<?php
 if (isset($_GET['id_red'])){ 
  echo "submit"; 
  }else{
    echo "button"; 

  }
   ?>"  <?php
if (!isset($_GET['id_red'])){ 
echo " onclick='Insertarredconajax()' "; 
}
?>  value="<?php echo $name; ?>"></p><br><br><br>
</form>
<?php ventana_modal_elegir_icono();?>
<?php ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="icono no creado, revise su información e intentete nuevo" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();" callback_0="false" callback="parent.buscar_iconos();"'); ?>
</div>
</div>
</div>
</div></div>

<?php 
#} //Fin Consulta
#Si es adjunto


if (isset($_POST) and isset($_POST['Modificar']) and $_POST['Modificar']){
if(isset($_FILES['enlace']['name']) and $_FILES['enlace']['name']<>""){
$total = count($_FILES['enlace']['name']);
if($_FILES['enlace']['name']){
$total = count($_FILES['enlace']['name']);
for($i=0; $i<$total; $i++) {// Recorremos cada archivo
 $nombre_archivo=$_FILES['enlace']['name'][$i];
 //$_FILES['nombre_campo_file']['name']['posición'];
$ruta_tmp_archivo = $_FILES['enlace']['tmp_name'][$i];
if ($ruta_tmp_archivo != ""){ 
$extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION));

#if (!in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; } 
/*
if(filesize($_FILES['enlace']['tmp_name'][$i]) > $tamaño_maximo ) {
echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
exit(); 
}
*/
 $ruta_destino = $carpeta_destino.$_SESSION['id_usuario'].$_FILES['enlace']['name'][$i];
 $ruta_destino_url = $carpeta_destino_url.$_SESSION['id_usuario'].$_FILES['enlace']['name'][$i]; 
                if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) { 
            actualizar_red($ruta_destino_url,$extensión_archivo);
                }                    
        }
      }

}
}
if(!empty($_FILES['enlace']['name'])){
actualizar_red("no","no");
}

if ($_POST['adjunto']<>"si" and $_POST['titulo_red']<>""){#Inicio si no es adjunto

    actualizar_red($ruta_destino="null",$extensión_archivo="");
}#Fin si no es adjunto
}
?>
<?php

if (!isset($_GET['a'])){
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
}
 ?>