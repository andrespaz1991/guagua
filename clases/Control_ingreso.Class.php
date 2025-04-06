<?php
class control_ingreso extends Clase_mysqli{
 public $fecha_ingreso="";
 public $hora_ingreso="";
 public $grupo="";
 public $id="";
 public $fecha_salida="";
 public $hora_salida="";

public function __SET($atributo,$valor){
	return	$this-> $atributo= $valor ;
}

public function __construct($id_control=""){
		if ($id_control!=""){
				$this->id_control = $id_control;
		#$this->listar_persona();
	}
}

public function insertar_control(){
 $sql='INSERT INTO `control_ingreso` (`fecha_ingreso`, `hora_ingreso`, `grupo`, `fecha_salida`, `hora_salida`) VALUES ("'.$this->fecha_ingreso.'",
"'.$this->hora_ingreso.'","'.$this->grupo.'","'.$this->fecha_salida.'","'.$this->hora_salida.'")' ;
	return $this->query_insertar($sql);  
 }

public function consultar_control($mes="4",$año=""){
 $sql='select * from control_ingreso where MONTH(fecha_salida) ="'.$mes.'"  and YEAR(fecha_salida) ="'.$año.'"  order by grupo,fecha_ingreso asc';
#echo $sql;
return $datos = json_decode($this->consultar_datos($sql,true),true);
}


public function control_materia($grupo){
	$sql="select * from control_ingreso where grupo='$grupo' ";
	return $datos = json_decode($this->consultar_datos($sql,true),true);
	
}
public function verificar_asistencia($fecha,$grupo,$asistencia){
	$sql='select count(*) as cantidad from asistencia where fecha="'.$fecha.'" and id_materia="'.$grupo.'"  and asistencia<>"'.$asistencia.'"  ';
	$datos = json_decode($this->consultar_datos($sql,true),true);
	return $datos[0];
}





}
?>