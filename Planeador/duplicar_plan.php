<?php 
ob_start();
@session_start();
if(isset($_POST['id_plan'])){
require_once("../comun/autoload.php");
$mat=new materias();
$sql='
INSERT INTO plan_clase (
    `contenido_plan`,
    `fecha_plan`, 
    `objetivos_plan`, 
    `plan_a`, 
    `plan_b`, 
   materia,  
    `grado`,
    `observaciones_plan`,
    `tiempo_plan`, 
    `orden_plan`)
  SELECT 
    `contenido_plan`,
    `fecha_plan`, 
    `objetivos_plan`, 
    `plan_a`, 
    `plan_b`, 
    "'.$_POST['materia'].'",
    `grado`,
    `observaciones_plan`,
    `tiempo_plan`, 
    `orden_plan`

  FROM plan_clase WHERE
  `id_plan` ="'.$_POST['id_plan'].'"';

		 if($mat->query_insertar($sql)){

		 	?>
      <script type="text/javascript">
      	
                        alert('Su plan ha sido duplicado correctamente');
                        window.location= '../index.php';    
      </script>
<?php 

                    }else{
      	?>
 <script type="text/javascript">
                        alert('Por favor verifique información y vuelva a intentar');
                        window.location= '../index.php';    
                       
      </script>
      <?php 

                     
                    }



}

 
require_once("../comun/autoload.php");
$mat=new materias();
$Planeacion= new planeacion($_GET['id']);
$mismaterias=(($mat->datos_asignaturas()));
?>
<form action="" method="post">
  <input type="hidden" name="id_plan" value="<?php echo $_GET['id'] ?>">
  <div class="col-md-12" style="margin-top:2%;">
<h1>  Contenido: <?php echo $Planeacion->contenido_plan; ?> </h1>
        <label>Materia</label>
        <select class="form-control"  name="materia">
<?php       
foreach($mismaterias as $campo => $valor){ ?>
        <option
<?php ?>  value="<?php echo $valor['id_materia']; ?>"><?php echo $valor['nombre_materia']; ?></option>
<?php } ?>
         </select>
</div>

<div class="col-md-12" style="margin-top:2%;">
<input class="btn btn-success" type="submit" name="guardar" value="Duplicar">
</div>
</form>
<?php 
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
?>