<?php
class Actividad extends Academico{
public $id_actividad;
public $id_asignacion;
public $fecha_publicacion;
public $hora_publicacion;
public $id_red;
public $nombre_actividad;
public $Observaciones;
public $adjunto;
public $evaluable;
public $fecha_entrega;
public $hora_entrega;

public $periodo;

public $visible;

public $cuestionario;

public $id_cuestionario;

public $foro;

public $id_foro;



public function __SET($atributo,$valor){

	return	$this-> $atributo= $valor ;

}

public function __construct($id_actividad=""){

		

		if ($id_actividad!=""){

		$this->id_actividad = $id_actividad;

		$this->consultar_actividad();

	}



}


public function asignaciones_de_un_docente($docente){
	 $listado_asignaciones=array();
	  $sql ='select * from asignacion where id_docente ="'.$docente.'"';
	   $datos = json_decode($this->consultar_datos($sql,true),true);
	foreach($datos as $clave => $row_docente){
		$listado_asignaciones[]=$row_docente['id_asignacion'];
	}
 return  $listado_asignaciones;
 }

public function mis_actividades_docente(){
	$mismateriashoy= $this->consultar_horario_Asignacionold();
	$cantidad_materias_hoy=count($mismateriashoy);
	$fecha=new Fecha;
	foreach ($mismateriashoy  as $key => $value) {
		echo "<pre>";
		echo "<a style='color: black;text-decoration:none;' href="
		.SGA_CURSOS_URL.'/curso.php?asignacion='.$value["id_asignacion"]." >";
		echo $value['nombre_materia'].' (Hoy) <br> desde '.$fecha->formato_hora_corta($value['hora_inicio']).' hasta '.$fecha->formato_hora_corta($value['hora_fin']);
		echo "</a>";
		echo "</pre>";
		}
		$mismateriashoy= $this->actividades_pendientes_por_fecha();
		if(!empty($mismateriashoy)){
		foreach ($mismateriashoy  as $key => $row) {
		echo "<pre>";
		$persona =  new Persona($row['identificacion']);
		
		print_r($persona->nombre);
echo "<br>";
print_r($row['contenido']);
echo "<br>";
print_r($fecha->formato_hora_corta($row['hora_inicio']));
echo " a ";
print_r($fecha->formato_hora_corta($row['hora_fin']));
echo "</pre>";
}
}

$notaspendientes= $this->notaspendientes();

foreach ($notaspendientes  as $keynota => $nota) {
  $datos_materia=$this->consultar_materia($nota['id_asignacion']);
?>
<div class="modal fade" id="exampleModal<?php echo $nota['id_nota']; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?php echo $datos_materia[0]->nombre_materia.'('.Fecha::formato_fecha($nota['fecha_nota']).'/'.Fecha::formato_hora($nota['hora_nota']) ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">

         <div class="form-group">
            <label for="message-text" class="col-form-label"><?php echo $nota['nota'] ?>:</label>
          </div>
     
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
 
      </div>
    </div>
  </div>
</div>
<?php
$idnota=$nota['id_nota'];
echo "<pre>";
echo '<p  data-toggle="modal" data-target="#exampleModal'.$idnota.'" data-whatever="@mdo" title='.$nota['nota'].'>'.(Comun::puntos_suspensivos($nota['nota'],20)).'('.$datos_materia[0]->nombre_materia.')</p>';
echo(Fecha::formato_fecha($nota['fecha_nota']));
echo "<a href=".SGA_CURSOS_URL.'/edunotas.php?estado='.$idnota."><img title='Desfijar' width='7%' src='".SGA_COMUN_URL."/img/negativo.png' ></img></a>";
echo "</pre>";
}
$asignaciones_de_un_docente =$this->asignaciones_de_un_docente($_SESSION['id_usuario']);
if(!empty($asignaciones_de_un_docente)){
	require("comun/conexion.php");
	foreach ($asignaciones_de_un_docente as $posicion => $asignacion) {


		$sql_estudiante_asignacion='select count(id_inscripcion) from inscripcion where id_asignacion ="'.$asignacion.'" ';
		$consulta_estudiante_asignacion =  $mysqli->query($sql_estudiante_asignacion);
		 $cantidad_estudiante_asignacion[$asignacion] = $consulta_estudiante_asignacion ->num_rows ;
		
		 
		 $sql_actividades = 'select * from actividad  where id_asignacion="'.$asignacion.'" and evaluable="SI" and   visible="SI"  and fecha_entrega>= "'.date('Y-m-d').'"  ' ;
		 
$consulta= $mysqli->query($sql_actividades);
while($row = $consulta ->fetch_assoc()){
	if($row['cuestionario']=='SI'){ 
		$sql_respuesta ='Select count(id) from respuesta where id_actividad = "'.$actividad.'" ';
		$consulta_respuesta = $mysqli ->query($sql_respuesta);
		$cantidad_respuestas[$actividad] = $consulta_respuesta -> num_rows;
	  }
	if($row['adjunto']=='SI' and isset($activiad)){ 
		$sql_adjunto ='Select count(id_tarea_adjunto) from tarea_adjunto where id_actividad = "'.$activiad.'" ';
		$consulta_adjunto = $mysqli ->query($sql_adjunto);
		$cantidad_adjunto[$actividad] = $consulta_adjunto -> num_rows;
	  }
	 
}
}
}
}
public function mis_actividades(){  ###muestra en el home
	$inscripcion_actual =inscripcion_actual($_SESSION['id_usuario']);
	$valoraciones_pendientes= $this->valoraciones_pendientes();
	if (empty($valoraciones_pendientes)) {
        echo 'No hay nuevas actividades  :)' ;
	}else{
		foreach ($valoraciones_pendientes as $info_actividad) {
			echo "<pre>";
			print_r($info_actividad);
			echo "</pre>";
			/*
			 echo '<a class="'.$clases_colores[$claves_aleatorias[0]].'" href="'.SGA_CURSOS_URL.'/visor_actividad.php?a='.$row['id_actividad'].'">'.$row['nombre_actividad'].'
               </a><a target="_blank" href="'.SGA_CURSOS_URL.'/visor_actividad.php?a='.$row['id_actividad'].'">
               <br/> <img title="'.$row['nombre_materia'].'" align="right" style="margin-top:-10%;" width="10%" src="'.consultar_link_icono($row['icono_materia']).'"></a><br/>';

			*/



	}

	}
	
	}





public function actividades_pendientes_por_fecha($fecha="",$limite=10){

if($fecha=="")	$fecha=date('Y-m-d');

	$sql='SELECT `seguimiento`.`id_seguimiento`, `seguimiento`.`identificacion`, `seguimiento`.`cita`, `seguimiento`.`asistio`, `seguimiento`.`tipo_asesoria`, `seguimiento`.`hora`, `seguimiento`.`fecha_asesoria`, `seguimiento`.`contenido`, `seguimiento`.`observaciones`, `seguimiento`.`asesoria_tecnica`, `seguimiento`.`fecha_fin`, `seguimiento`.`hora_inicio`, `seguimiento`.`hora_fin`, `seguimiento`.`docente`, `docente`.`nombre_docente` as nombre_docente FROM `seguimiento` inner join `usuario` on `seguimiento`.`docente` = `usuario`.`id_usuario` 
	INNER join docente
on usuario.id_usuario=docente.id_docente
	WHERE `seguimiento`.`fecha_asesoria` = "'.date('Y-m-d').'" and seguimiento.docente="'.$_SESSION['id_usuario'].'"  ORDER BY seguimiento.`id_seguimiento` desc LIMIT '.$limite.'';
#echo $sql.'<br>';
$datos = json_decode($this->consultar_datos($sql,true),true);

if(!empty($datos)){

	return $datos ;

}else{

	return "";

}







}







public function consultar_actividad(){

$sql='select * from actividad';

if (!empty($this->id_actividad)) {

$sql.=' where id_actividad="'.$this->id_actividad.'"';

}

$datos = json_decode($this->consultar_datos($sql,true),true);

if (!empty($this->id_actividad)) {

foreach ($datos[0] as $clave => $value) {

	$this -> __SET($clave,$value);

}

}else{

	return $datos;

}



}



public function actividades_actuales(){

	$sql ='select * from asignacion,actividad,materia  where 

materia.id_materia = asignacion.id_asignacion and

asignacion.id_asignacion = actividad.id_asignacion and evaluable ="SI" and actividad.visible="SI" and actividad.fecha_entrega >= "'.date('Y-m-d').'" and actividad.id_asignacion in (Select asignacion.id_asignacion from inscripcion,asignacion,materia where inscripcion.id_asignacion = asignacion.id_asignacion and materia.id_materia = asignacion.id_asignacion and inscripcion.id_estudiante ="'.$_SESSION['id_usuario'].'") limit 10';



}

}

?>