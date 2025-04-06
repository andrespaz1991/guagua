<?php
ob_start();
@session_start();
if(!empty($_SESSION['barra_busqueda'])){
$_SESSION['barra_busqueda']=="";  
}
unset($_SESSION['barra_busqueda']);
#require("../comun/capturar.php"); 
require("../comun/funciones.php");
require("../comun/conexion.php"); 
 require_once ("../comun/autoload.php");
 require_once ("../clases/Red.Class.php");
 $miredinstancia= new Red($_GET['red']);
?>
<?php 
if (isset($_GET['red'])){
 require("../comun/conexion.php");
$sql_visita ="UPDATE `red` SET `visitas` = `visitas`+1 WHERE `id_red`=".$_GET['red'];
    $mysqli->query($sql_visita);
    
  $sql_red= 'select * from red,usuario,materia where
  red.materia_red = materia.id_materia and
  red.responsable = usuario.id_usuario 
  and id_red = "'.$_GET['red'].'"' ;
 $consulta = $mysqli -> query($sql_red) ;
 $metadatos= array();
 if($row_red = $consulta -> fetch_assoc()){
  $metadatos = $row_red ;
 }
}

?>
<br/>
<?php
echo '<div id="visor_red">';
require_once("../comun/funciones.php"); // Carga código de encabezamiento
$datos = comprobar_red($_GET['red']); 
$formato =$miredinstancia->formato;
$ruta =  $miredinstancia->enlace;  
$scorm=$miredinstancia->scorm;
reproductor ($formato,$ruta,$scorm);
echo '</div>';
if (isset($_GET['red'])){
echo '<div style="position:absolute;margin-top:-30%;margin-left:60%;">';
 echo '<h2>'.$miredinstancia->titulo_red.'</h2>'; 

if($miredinstancia->adjunto=="no"){
echo '<span>Enlace:  <a target="_blank" href="'.$miredinstancia->enlace.'">Aquí</a></span><br>'; 
}

  echo '<span>Identificador:  '.$miredinstancia->id_red.'</span><br>'; 
$responsable=new Persona($miredinstancia->responsable);
  echo '<span>Responsable:  '.$responsable->nombre.' '.$responsable->apellido.'</span><br>'; 
  echo '<span>Materia :'.$miredinstancia->materia_red.'</span><br>'; 
 # $metadatos['nivel_eductivo'] = explode(",",json_decode($metadatos['nivel_eductivo'],true));
 $miredinstancia->nivel_eductivo = str_replace("[", "", $miredinstancia->nivel_eductivo);$miredinstancia->nivel_eductivo = str_replace("]", "", $miredinstancia->nivel_eductivo);
  echo '<span>Nivel Educativo :'.$miredinstancia->nivel_eductivo.'</span><br>'; 
  echo '<span>Fecha Publicación : '.formatofecha2($miredinstancia->fecha).'</span><br>'; 
  echo '<span>Descripción :'.$miredinstancia->descripcion.'</span><br>'; 
  echo '<span>Autor :'.$miredinstancia->autor.'</span><br>'; 
  echo '<span>Dificultad :'.$miredinstancia->dificultad.' (1 a 5)</span><br>';  
  echo '<span>Visitas :'.$miredinstancia->visitas.'</span><br>';
  ?>

  <span>Estrellas : <span id="num_fav_red"><?php $array_estrellas = json_decode($miredinstancia->cantidad_estrellas,true); echo sumar_valores($array_estrellas) ?></span>&nbsp;<span class="fav_visor"><?php  echo mis_red_favoritos($miredinstancia->id_red, $miredinstancia->cantidad_estrellas); ?></span>
  </span>
  <br/>
  <?php
  echo '<span>Palabras Clave :'.$miredinstancia->palabras_clave.'</span><br>'; 
  echo '<span>Icono :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class="'.$miredinstancia->icono_red.'"></span></span><br>'; 
 ?> <input style="color:#FFF" onclick="window.open('../comun/funciones.php?ruta_red=<?php echo $miredinstancia->id_red;  ?>')" type="button" class="btn btn-primary" value="Descargar"/>
<?php
if($miredinstancia->scorm=='SI'){
echo '<button type="submit" class="btn btn-success">Descargar Scorm</button>';
}
 echo '</div>';
}


$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>



   


