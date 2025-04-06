<?php 
require 'conexion.php'; 
ob_start();
@session_start();
  if(isset($_SESSION['rol']) and $_SESSION['rol']=='admin' ){
      $sql ='select * from usuario order by usuario.puntos desc' ;
  }
    if(isset($_SESSION['rol']) and $_SESSION['rol']=='docente' ){
      $sql ='select * from usuario where rol like "%docente%" ' ;
  }
  if(isset($_SESSION['rol']) and $_SESSION['rol']=='acudiente' ){
 $sql='  select distinct(id_estudiante),usuario.id_usuario,usuario.puntos, usuario.nombre, usuario.apellido,categoria_curso.nombre_categoria_curso from inscripcion,usuario,asignacion,categoria_curso where inscripcion.id_estudiante = usuario.id_usuario and inscripcion.id_asignacion = asignacion.id_asignacion and asignacion.id_categoria_curso = categoria_curso.id_categoria_curso and fecha_inscripcion like  "%'.date('Y').'%" and asignacion.id_asignacion in (select id_asignacion from inscripcion where id_estudiante = "'.$_SESSION['hijo'].'" and fecha_inscripcion like  "%'.date('Y').'%") order by usuario.apellido desc ';
     
  }
     if(isset($_SESSION['rol']) and $_SESSION['rol']=='estudiante' ){
       $sql='  select distinct(id_estudiante),usuario.id_usuario,usuario.puntos, usuario.nombre, usuario.apellido,categoria_curso.nombre_categoria_curso from inscripcion,usuario,asignacion,categoria_curso where inscripcion.id_estudiante = usuario.id_usuario and inscripcion.id_asignacion = asignacion.id_asignacion and asignacion.id_categoria_curso = categoria_curso.id_categoria_curso and fecha_inscripcion like  "%'.date('Y').'%" and asignacion.id_asignacion in (select id_asignacion from inscripcion where id_estudiante = "'.$_SESSION['id_usuario'].'" and fecha_inscripcion like  "%'.date('Y').'%") order by usuario.apellido desc ';
     }
          $consulta = $mysqli ->query ($sql);
     $cantidad = $consulta ->num_rows;
if($cantidad<=0){ ?>
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"> RANKING DE MONEDAS
      </div></div>
<?php
  echo 'No Hay Ranking';
} else {
#echo $cantidad;
while ($row = $consulta -> fetch_assoc() and $cantidad > 0 ) {
    $curso="";
    if (isset($row['nombre_categoria_curso'])) $curso=$row['nombre_categoria_curso'];
}
?> 
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">
      RANKING DE MONEDAS <?php  if(isset($_SESSION['rol']) and $_SESSION['rol']=='estudiante' or $_SESSION['rol']=='acudiente' ){ 
      echo $curso;
      }
      if(isset($_SESSION['rol']) and $_SESSION['rol']=='docente'){
        echo 'DOCENTES';
      }
 ?>    </h1>      
  </div>
</div>  
  
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
   <script type="text/javascript">
    google.charts.load("current", {packages:['corechart']});
    google.charts.setOnLoadCallback(DibujarColumnas);
function DibujarColumnas() {
var data = google.visualization.arrayToDataTable([
        ["Persona", "Monedas", { role: "style" } ],
     <?php
   @session_start();
     require 'conexion.php'; //Llamamos al archivo de conexion
      $consulta = $mysqli ->query ($sql);
      while ($row = $consulta -> fetch_assoc() and $cantidad > 0) {
          #echo "<pre>";print_r($row);echo "</pre>";
          $curso="";
          if (isset($row['nombre_categoria_curso'])) $curso=$row['nombre_categoria_curso'];        ?> 
        ["<?php
        if(isset($_SESSION['hijo']) and $row['id_usuario']==$_SESSION['hijo']){ echo '[*] '; }
        if($row['id_usuario']==$_SESSION['id_usuario']){ echo '[*] '; } echo mb_strtoupper($row['apellido'].' '.$row['nombre'],'UTF-8'); ?>",<?php echo $row['puntos'] ; ?>, ColorAleatorio()],
     
 <?php     }
      ?>    
      ]);
  var options = {
        title: "Persona", 
        width: 1024, 
        height: 600,
        bar: {groupWidth: "95%"}, 
      };
var chart = new google.visualization.BarChart(document.getElementById("Contenedor_monedas")); 
      chart.draw(data, options); 
  }
  </script>
<div id="Contenedor_monedas" style="width: 700px; height: 300px;"></div>
<?php
}
$contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
 ?>