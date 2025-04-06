<?php 
class Eventos extends Clase_mysqli{
public $id;    
public $nom;
public $descripcion;
public $fecha;
public $hora_inicio;
public $hora_fin;
public $id_usuario;

public function __SET($atributo,$valor){
	return	$this-> $atributo= $valor ;
}

function __construct($id='')  
{ 
    if($id!=''){
    $this->id_materia=$id;
   $this->datos_eventos();
    }
}



public  function datos_eventos($todas=0){
    $sql="select * from eventos";
  return $datos = json_decode($this->consultar_datos($sql,true),true);

}

public function notificador_eventos($fecha='hoy',$revisado=0){
  if($fecha=='hoy'){
    $fecha=date('Y-m-d');
  }
  $sql='select * from eventos where fecha="'.$fecha.'" and revisado="'.$revisado.'"';
  $listado_Eventos = json_decode($this->consultar_datos($sql,true),true);
  foreach($listado_Eventos as $listas => $info_evento){ ?>
    <script>
    notificar('<?php echo $info_evento['nom'] ?>','<?php echo $info_evento['descripcion'] ?>','<?php echo '6000' ?>');
    </script>
    <?php }
    
}


}