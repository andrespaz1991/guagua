<?php

class Curso extends Academico{

            public $id_curso;

            public $id_asignatura;

            public $id_docente;

            public $ano_lectivo;

            public $id_asignacion;

            public $descripcion;

            public $id_categoria_curso;

            public $visible;

            public $portada_asignacion;

            public $institucion_educativa;

            public $id_materia;

            public $nombre_materia;

            public $descripcion_materia;

            public $obligatoria;

            public $area;

            public $icono_materia;

            public $id_usuario;

            public $usuario;

            public $clave;

            public $mascota;

            public $nombre;

            public $apellido;

            public $rol;

            public $foto;

            public $direccion;

            public $telefono;

            public $correo;

            public $ultima_sesion;

            public $num_visitas;

            public $puntos;

            public $estado;

            public $tipo_sangre;

            public $genero;

            public $observaciones;
            public $icono_asignacion;
            public $asistencia ;
            public $institucion ;
            public $fecha_nacimiento;

public function __SET($atributo,$valor){

  return  $this-> $atributo= $valor ;

}

public function __construct($id_asignatura=""){

    if ($id_asignatura!=""){

        $this->id_asignatura = $id_asignatura;

    $this->informacion_curso();

  }

}

public function informacion_curso($todos=0){

	$sql='select * from asignacion,materia,usuario where 

asignacion.id_asignatura =materia.id_materia and

asignacion.id_docente = usuario.id_usuario 

and asignacion.id_asignacion ="'.$this->id_asignatura.'"';
$datos = json_decode($this->consultar_datos($sql,true),true);

if($todos==1)
{
return $datos;
}else{
foreach ($datos as $clave => $value) {
foreach($value as $clave2 => $value2){
  $this->__SET($clave2,$value2); 
  
}
  
   
}

}

}



public function todas_categoria_curso(){

  $sql='select * from categoria_curso ';

 return $datos = json_decode($this->consultar_datos($sql,true),true);

}



public function categoria_curso(){

  $sql='select * from categoria_curso ';

if(!empty($this->id_categoria_curso)){

$sql.=' where id_categoria_curso="'.$this->id_categoria_curso.'" ';

}

 $datos = json_decode($this->consultar_datos($sql,true),true);

  return $datos[0]['nombre_categoria_curso'];

}

public function deadeline_curso($asignacion){

$hoy =date('Y-m-d');

#$hoy =date('2020-05-06');

$horarios= ($this->consultar_horario_simple($asignacion));
if(!empty($horarios[0]['fecha_inicio'])){
  $fecha_inicio=$horarios[0]['fecha_inicio'];
 $fecha_fin=$horarios[0]['fecha_fin'];
if($hoy<=$fecha_fin){
 $fecha_inicio;
 $hoy;
$a = Fecha::diferencia_fecha($fecha_inicio,$fecha_fin);
$b = Fecha::diferencia_fecha($fecha_inicio,$hoy);
$resultado=round((($b)*100)/($a),2);
}else{
  $resultado="100";
}
return $resultado;
}
}

 





public function consultar_link_icono($icono){

$sql="SELECT imagen_icono FROM `iconos` WHERE id_iconos=$icono";

   $datos = json_decode($this->consultar_datos($sql,true),true);



    if (!empty($datos)){

    foreach ($datos as $key => $row ){

        return SGA_COMUN_URL."/img/png/".$row['imagen_icono'];

    }        

    }else{

        return SGA_COMUN_URL."/img/png/folder-10.png";

    }

}

public function listar_cursos_home() {
    // IMPORTANTE: Asegúrate de que tu método mis_cursos_otros() devuelva el campo 'visible' de cada curso.
    $datos_curso = $this->mis_cursos_otros();

    foreach ($datos_curso as $key => $datos_materia) {
        $this->componente_context_menu($datos_materia['id_asignacion'], $datos_materia['nombre_materia']);
        
        // Determina si el curso está visible. Por defecto, se asume 'si' si no existe el dato.
        $es_visible = (strtolower(trim($datos_materia['visible'] ?? 'si')) !== "no");
        $visible_class = $es_visible ? '' : 'oculto';

        ?>
        <div id="curso-home-<?php echo $datos_materia['id_asignacion']; ?>" class="menu_contextual<?php echo $datos_materia['id_asignacion'] ?> curso-home-card <?php echo $visible_class; ?>" style="margin-top:5%">
            <a target='_blank' class="enlace_sin_estilo" title="<?php echo htmlspecialchars($datos_materia['descripcion'], ENT_QUOTES, 'UTF-8'); ?>" href="<?php echo SGA_CURSOS_URL . '/curso.php?asignacion=' . $datos_materia['id_asignacion']; ?>">
                <?php echo puntos_suspensivos($datos_materia['nombre_materia'], 20) . '(' . $datos_materia['mid_categoria_curso'] . ') <br/>' ?>
            </a>
            
            <img title="Imagen del curso" class="imagen_tarjeta" src="<?php echo consultar_link_icono($datos_materia['icono_asignacion']); ?>">
            
            <?php
            // Solo muestra el botón si el rol es admin o docente
            $rol = $_SESSION['rol'] ?? 'invitado';
            if ($rol === 'admin' || $rol === 'docente') {
                $texto_boton = $es_visible ? 'Ocultar' : 'Mostrar';
                ?>
                <div class="curso-home-acciones" style="margin-top:-17%;margin-left:60%">
                    <button 
                        class="btn btn-default btn-xs control-visibilidad-btn" 
                        data-id="<?php echo $datos_materia['id_asignacion']; ?>"
                        data-visible="<?php echo $es_visible ? 'si' : 'no'; ?>">
                        <?php echo $texto_boton; ?>
                    </button>
                </div>
                <?php
            }
            ?>
        </div>
        <script type="text/javascript">
            $(function() {
                contexmenu('<?php echo $datos_materia['id_asignacion'] ?>');
            });
        </script>
        <?php
    }
}





}




?>