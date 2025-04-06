<?php

class Materias extends Clase_mysqli{

  public $id_materia;

  public $nombre_materia;

  public $descripcion_materia;

  public $obligatoria;

  public $area;

  public $icono_materia;
  public $institucion;





public function __SET($atributo,$valor){

	return	$this-> $atributo= $valor ;

}



function __construct($id='')  

{ 

    if($id!=''){

    $this->id_materia=$id;

   $this->datos_materia();

    }

}
public function consultar_materias() {

  $sql='select * from materia_oficial order by nombre_materia';
return $this->consultar_datos($sql);
}


public  function datos_materia($todas=0){
$sql="select * from materia_oficial";
if($this->id_materia<>"" and $todas==0){
$sql.=" where id_materia='".$this->id_materia."' "; 
$datos = json_decode($this->consultar_datos($sql,true),true);
if(!empty($datos[0])){
  foreach ($datos[0] as $clave => $value) {
    $this -> __SET($clave,$value);
  }
}
}
else{
  $datos = json_decode($this->consultar_datos($sql,true),true);
  return $datos;
}
}



public  function datos_asignaturas(){

$sql="select * from materia_oficial ";

 return $datos = json_decode($this->consultar_datos($sql,true),true);

}









}



?>