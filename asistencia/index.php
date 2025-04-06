<?php
require("../comun/autoload.php");
date_default_timezone_set('America/Bogota'); 
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once '../clases/Academico.Class.php';
require_once '../clases/Control_ingreso.Class.php';
$academico=new Academico();
?>
        <script src="../comun/js/jquery.dataTables.min.js"></script>
        <script src="../comun/js/dataTables.bootstrap4.min.js"></script>
   
<script>
$(document).ready(function() {
    $('#example').dataTable( {
        "pageLength": 30 ,
        "ordering": true,
        "order": [[1, 'asc']],
        "autoWidth": false,
         "responsive": true
    } );
} );
</script>
<body>
       
    </body>
    
<?php
if(isset($_POST['Inicio']) and !empty($_POST['Inicio'])){
$_SESSION["asistencia"]="no";
}else{
	$_SESSION["asistencia"]="si";
}

if(isset($_SESSION["asistencia"]) and $_SESSION["asistencia"]=="no"){
header('location:../index.php');
}

$academica = new Academico;
$asignacion=$_GET['asignacion'];
$listado=$academica->listar_estudiantes_asignacion($asignacion);
$rango_grados=$academica->listar_grados_estudiantes_asignacion($asignacion)[0];
$grado_minimo=$rango_grados['grado_minimo'];
$grado_maximo=$rango_grados['grado_maximo'];
if (!empty($rango_grados)) {
    if(isset($_POST['registrar_asistencia']) and $_POST['registrar_asistencia']=="on"){
#$horarios=($academico->consultar_horario_Asignacion($_GET['asignacion'],0,0,$_POST['fecha']));
$horarios=($academico->consultar_horario_Asignacion(0,0,0,$_POST['fecha'],$grado_minimo,$grado_maximo));
      
foreach($horarios as $clave => $horario){
 
    $control_ingreso =new control_ingreso;
    $control_ingreso->hora_ingreso =$horario['hora_inicio'];
    $control_ingreso->hora_salida =$horario['hora_fin'];
    $control_ingreso->fecha_ingreso=$_POST['fecha'];
    $control_ingreso->fecha_salida=$_POST['fecha'];
    $control_ingreso->grupo=$horario['id_asignacion'];
$respuesta=$academica->registrar_asistencia($control_ingreso);
echo "<hr><pre>";
echo $horario['nombre_materia'].', Actualización:'.$respuesta;
echo "</pre>";
   
}


#$control_ingreso->insertar_control();
}

/*
if($respuesta==1){
	echo "<script>alert2('registro exitoso');window.location='../cursos/curso.php?asignacion=".$_GET['asignacion']."'</script>";
}
*/
}

$informacion_materia=$academica->consultar_materia();
$nombre_asignatura=($academico->consultar_materia($_GET['asignacion']));
#print_r($nombre_asignatura);
?>
<div class="jumbotron" >
	<div class="fip">
	<?php 
	echo $nombre_asignatura=($nombre_asignatura[0]->nombre_materia);
 ?>
</div>
</div>
<form action="" method="POST">



	<input  type="hidden" name="asignacion" value="<?php echo $_GET['asignacion']; ?>">
	<input type="hidden" name="docente" value="1085290375">
    <table id="example" class="table table-striped table-bordered" style="width:100%">
	
        <thead>
        <tr>
		<th colspan="4">
<label>Fecha de Asistencia</label>
	<input  type="date" name="fecha" value="<?php echo date('Y-m-d') ?>">
			<?php
#echo '<br>'.Fecha::formato_fecha(date('Y/m/d'));
echo '/ '.date('g:i:s a').'<br>';
?></th>
		</tr>
	<tr>
		<th>#</th>
		<th>Foto</th>
		<th>Nombre</th>
		<th>Asistencia</th>
	</tr>
    </thead>
    <tbody>
<?php
$contador=1;
foreach ($listado as $key => $info_estudiante) {  
	$persona=new persona($info_estudiante['id_estudiante']); ?>
    <tr>
		<td><?php echo $contador++; ?></td>
		<td>
            <?php if($persona->genero=="F"){
                $persona->foto="user-iconf.png";
            }  ?>
<img width="100px" src="<?php echo SGA_COMUN_SOLOSGA_DATA.'/'.$persona->foto ?>"></img>
			</td>
		<td><?php echo $persona->nombre.' '.$persona->apellido.'('.$info_estudiante['id_estudiante'].')' ?></td>
		<td>
        <style>
        /* Estilos para los radio buttons grandes */
        .large-radio input[type="radio"] {
            width: 20px;
            height: 20px;
        }

        .large-radio label {
            font-size: 1.5em;
            vertical-align: middle;
        }
    </style>
<div class="form-check form-check-inline">
<input class="form-check-input"  checked type="radio" name="asistencia[<?php echo $info_estudiante['id_estudiante'] ?>]" value="si">
			  <label for="usr">Si</label>
<input class="form-check-input"  type="radio" name="asistencia[<?php echo $info_estudiante['id_estudiante'] ?>]" value="no">
<label for="usr">No</label>
<input class="form-check-input" type="radio" name="asistencia[<?php echo $info_estudiante['id_estudiante'] ?>]" value="permiso">
<label for="usr">Permiso</label>

</div>
</td>
	</tr>
<?php } ?>
</tbody>
</table>
<label>Registrar Ingreso docente</label>
<input checked="true" type="checkbox" name="registrar_asistencia">
<br>
<input class="btn btn-warning" type="submit" name="" value="Guardar">
</form>

<form action="" method="post">
	<input class="btn btn-success" type="submit" name="Inicio" value="No quiero llamar asistencia">
</form>

<?php
$contenido = ob_get_clean();
require '../comun/plantilla.php'; 
?>