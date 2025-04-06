<?php ob_start(); 
@session_start();
?>
<?php require_once("../comun/config.php"); ?>
<?php
if (isset($_SESSION['rol'])) {
?>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">Actividades <?php echo $_COOKIE['mirutactividadescurso'];  ?></h1>      
  </div>
</div>
<div class="container-fluid bg-3 text-center">    
 <div class="row">
<form action="actualizar.php" method="POST" ENCTYPE="multipart/form-data">
<h2 class="Abckids">Modificar Actividad</h2>
<input type="hidden" name="actividad" value="<?php echo $_GET['id_m'] ;?>"/>
<input type="hidden" name="archivo" value="<?php echo $_GET['v'] ;?>"/>
<strong><?php echo $_GET['cur']; ?></strong>
<form action="subir.php" method="POST" ENCTYPE="multipart/form-data">
  <p>
      <input type="hidden" name="asignacion" value="<?php echo $_GET['asignacion'] ;?>">
      <label for="textfield">Nombre actividad: </label>
    <input type="text" name="id" id="textfield" value ="<?php echo $_GET['nombre_actividad'] ?>" required  />
   <label for="textfield">Periodo: </label>
 <?php
 require '../comun/conexion.php';
 $sqlconsultaactividad = 'select * from actividad where id_actividad ="'.$_GET['id_m'].'" ';
$consultamateria = $mysqli ->query ($sqlconsultaactividad);
$datos_actividad = array();
while ($rowmateria = $consultamateria ->fetch_assoc() ){
    $datos_actividad[] = $rowmateria;
    
$periodo = $rowmateria['periodo'];  $fecha_entrega =$rowmateria['fecha_entrega'];
 $scorm = $rowmateria['tipo_m']; $hora_entrega = $rowmateria['hora_entrega'];
$evaluable = $rowmateria['evaluable'];
$fecha_publicacion = $rowmateria['fecha_publicacion'];
    }

?>
<body style="overflow-x: hidden;"<?php echo 'onload="revisar_chk()"'; ?>  >
<select name="periodo" >
    <option value="1" <?php if ($periodo=="1" )  {echo "selected";} ?> >1</option>
    <option value="2" <?php if ($periodo=="2" )  {echo "selected";} ?> >2</option>
    <option value="3" <?php if ($periodo=="3" ) { echo "selected";} ?> >3</option>
    <option value="4" <?php if ($periodo=="4" ) { echo "selected";} ?> >4</option>
</select>
  </p>
  <p>
          <!--label for="textfield">Adjunto: </label>
    <input name="imagen" type="file"/-->
  </p>
  <!--label title="Prueba de titulo">¿El archivo adjunto es Scorm (1.2)?</label-->
      </p>
     <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script type="text/javascript">
</script>
<br/>

<label for="">Recurso Educativo Digital</label>
<div class="btn-toolbar" role="toolbar">
  <button type="button" class="btn btn-default btn-lg">
   <span data-toggle="modal" data-target="#myModal"  id="red" class="<?php if (isset($datos_actividad['icono_red'])) echo $datos_actividad['icono_red']; else { echo 'glyphicon glyphicon-book'; } ?>"></span>
  </button>
<?php require '../red/listado_red.php'; #fin iconos  ?>
   <p id="red-nombre"></p>
     <input type="hidden" name="red" id="id_red" value="<?php echo $datos_actividad[0]['id_red']; ?>" />
      
      
      <br/>
  <p>
      <label>Descripción</label><br>
<textarea rows="4" cols="50" name ="observacion"  required placeholder="Descripción..." >
<?php echo $_GET['obs']; ?>
</textarea>
  </p>
     <input onclick="revisar_chk();" <?php if($evaluable=="SI") echo  "checked";  ?>  type="checkbox" id="checkbox" name="evaluable" value="SI"> Actividad Evaluable<br><br>
<div id="fechas" style="display:hidden">
    <label for="">Fecha de Publicación </label>
    <input type="date" name="fecha_publicacion" value ="<?php echo $fecha_publicacion; ?>"/>
    <label for="">Fecha de Entrega </label>
    <input type="date" name="fecha_entrega" value ="<?php echo $fecha_entrega; ?>"/>
<label for="">Hora de Entrega </label>
    <input type="time" name="hora_entrega" value ="<?php echo $hora_entrega; ?>"/>

    </div>
   <p>
    <input type="submit" name="submit" id="submit" value="Actualizar" >
  </p>
</form> 
</section>
<style>
    .navbar {
     background-color: #FFF !important;
     border-color: #FFF !important;
}
</style>
<?php } 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>