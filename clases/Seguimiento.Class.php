<?php

class Seguimiento extends Academico{

    public $id_seguimiento;
    public $identificacion;
    public $cita;
    public $asistio;
    public $tipo_asesoria;
    public $hora;
    public $fecha_asesoria;
    public $contenido;
    public $observaciones;
    public $asesoria_tecnica;
    public $fecha_fin;
    public $hora_inicio;
    public $hora_fin;
    public $docente;
    public $taller;
    public $grupo;


    public function __SET($atributo,$valor){
        return	$this-> $atributo= $valor ;   
    }
    
    public function __construct($id_seguimiento=""){   
            if ($id_seguimiento!=""){
                    $this->id_seguimiento = $id_seguimiento;
        }
       
    }
    public function consultar_seguimiento(){
        $sql='select * from seguimiento ';
        if ($this->id_seguimiento!=""){
        $sql.=' where id_seguimiento="'.$this->id_seguimiento.'" ';
        }
        $datos = json_decode($this->consultar_datos($sql,true),true);
        if(!empty($datos[0]) and $todos==0 ){
            foreach ($datos[0] as $clave => $value) {
              $this -> __SET($clave,$value);
              #print_r($clave).'/'.print_r($value);
            }  
             }else{
              return $datos;
             }
    }

    public function consultar_seguimiento_asignacion(){
         $sql='select * from seguimiento where grupo="'.$this->grupo.'" and identificacion="'.$this->identificacion.'"';
          return $datos = json_decode($this->consultar_datos($sql,true),true);
    }

}



?>