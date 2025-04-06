<?php

class red extends Clase_mysqli{
  public $id_red ;
  public $titulo_red;
  public $idioma_red="Es";
  public $contexto="Primaria";
  public $descripcion;
  public $palabras_clave;
  public $nivel_eductivo ;
  public $institucion;
  public $autor;
  public $responsable;
  public $formato;
  public $tipo_interacción;
  public $tipo_recurso_educativo ;
  public $dificultad;
  public $fecha;
  public $estrellas="0";
  public $enlace;
  public $scorm;
  public $adjunto;
  public $icono_red=11;
  public $materia_red;
  public $cantidad_estrellas;
  public $visitas;
  public $version;
  public $entidad_origen;
  public $fecha_creacion ;
  public $tamano;
  public $requerimientos;
  public $costo;
  public $derechos_autor;



public function __SET($atributo,$valor){
	return	$this-> $atributo= $valor ;
}

function __construct($id='')  

{ 

   

    if($id!=''){

    $this->id_red=$id;

   $this->datos_red();

    }

}//Fin Constructor


function iconos_red(){
$sql='select imagen_icono from iconos where id_iconos='.$this->icono_red.' ';
return $datos = json_decode($this->consultar_datos($sql,true),true)[0]['imagen_icono'];
}


public  function datos_red(){

$sql="select * from red where id_red='".$this->id_red."' ";
 $datos = json_decode($this->consultar_datos($sql,true),true);
foreach ($datos[0] as $clave => $value) {
	$this -> __SET($clave,$value);
}



}


public function listar_red_home($limit=4){
  $sql='select * from red,iconos';
  if(isset($_SESSION['puntos'])) {
    $sql.="where 
    red.icono_red=icono.id_iconos and 
    red.cantidad_estrellas<= ".$_SESSION['puntos']."";
  }
  $sql.=' order by red.estrellas desc limit '.$limit.' ';
  $contador_eventos=0;
  $datos = json_decode($this->consultar_datos($sql,true),true);
          foreach($datos as $clave => $row){
          $asignaciones_docente[] =$row; 
          $contador_eventos++ ;
          $row['icono_red']= str_replace('icon-sga-','',$row['icono_red']);
          ?><a title="<?php echo $row['titulo_red']; ?>" href="<?php echo SGA_RED_URL.'/visor_red.php?red='.$row['id_red'].'&formato='.$row['formato'].'&enlace='.$row['enlace'].'&scorm='.$row['scorm']; ?>" > <?php echo puntos_suspensivos($row['titulo_red'],20).'<br/> <img align="right" style="margin-top:-10%;" width="10%" src="'.SGA_COMUN_IMAGES.'/png/'.$row['imagen_icono'].'"></a>';  echo '<br/>'; 
        }

}




}



?>