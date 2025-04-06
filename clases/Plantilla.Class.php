<?php
class Plantilla extends Institucion{
public $id_plantilla=1;
public $ruta;
public $observaciones;
public $nombre_plantilla;


public function __SET($atributo,$valor){
	return	$this-> $atributo= $valor ;
}
function __construct($id='')  
{   
    if($id!=''){
    $this->id_plantilla=$id;
   $this->datos_plantilla();
    }
}//Fin Constructor

public  function datos_plantilla(){
$sql="select * from plantilla where id_plantilla='".$this->id_plantilla."' ";
 $datos = json_decode($this->consultar_datos($sql,true),true);
foreach ($datos[0] as $clave => $value) {
	$this -> __SET($clave,$value);
	#print_r($clave).'/'.print_r($value);
}
$this->ruta='../comun/plantillas/'.$this->ruta.'/plantilla_kids_m.php';

}




}
?>