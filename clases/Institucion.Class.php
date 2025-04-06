<?php



class Institucion extends Clase_mysqli{

public $id_institucion_educativa = 1 ;

public $nombre_institucion = "" ;

public $logo_institucion = "" ;

public $direccion_institucion = "" ;

public $telefono_institucion ="";

public $correo_institucion ="";

public $BANNER_INSTITUCION ="";

public $formatos_no_permitidos ="";

public $tamano_maximo_adjunto ="";

public $autoregistrarse ="";

public $plantilla ="";



function __construct($id_iem='')  

{ 

    if($id_iem!=''){

    $this->id_institucion_educativa=$id_iem;

   $this->datos_institucion();

    }

}//Fin Constructor

public function __SET($atributo,$valor){

	return	$this-> $atributo= $valor ;

}



public function datos_institucion($todas=0){
	$sql='SELECT * FROM `institucion_educativa`';
		if($todas==0){
			$sql.=' where `id_institucion_educativa`= "'.$this->id_institucion_educativa.'"';		
}
$sql.=' order by id_institucion_educativa desc';
$info_iem = json_decode($this->consultar_datos($sql,true),true);		
if($todas==1){
	return $info_iem; 
}else{
foreach ($info_iem as $clave => $value) {
	
	$this -> __SET($clave,$value);
	return $info_iem; 

	#print_r($clave).'/'.print_r($value);
}	
		}
}
}
?>