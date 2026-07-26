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
    $rol = $_SESSION['rol'] ?? 'invitado';

    // Estilos básicos para el menú contextual y el buscador
    echo '
    <style>
        .buscador-contenedor { margin-bottom: 20px; }
        .buscador-input { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ccc; font-size: 16px; box-sizing: border-box; }
        .curso-home-card { position: relative; padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 20px; transition: opacity 0.2s ease; }
        .btn-contextual { background: none; border: none; font-size: 20px; cursor: pointer; padding: 0 10px; }
        .dropdown-context-menu { position: absolute; top: 10px; right: 10px; }
        .context-menu-list { display: none; position: absolute; right: 0; background: #fff; border: 1px solid #ccc; box-shadow: 0 4px 8px rgba(0,0,0,0.1); list-style: none; padding: 5px 0; border-radius: 4px; z-index: 100; min-width: 150px; margin: 0; }
        .context-menu-list li a, .context-menu-list li button { display: block; padding: 8px 15px; text-decoration: none; color: #333; width: 100%; text-align: left; background: none; border: none; font-size: 14px; cursor: pointer; }
        .context-menu-list li a:hover, .context-menu-list li button:hover { background-color: #f5f5f5; }
        .oculto { opacity: 0.6; background-color: #f9f9f9; }
        .oculto-por-busqueda { display: none !important; }
    </style>';

    // Input de búsqueda
    echo '
    <div class="buscador-contenedor">
        <input type="text" id="buscador-cursos" class="buscador-input" placeholder="Buscar curso por nombre o categoría...">
    </div>
    <div id="contenedor-lista-cursos">';

    foreach ($datos_curso as $key => $datos_materia) {
        // Saneamiento y asignación de variables
        $id_asignacion = htmlspecialchars($datos_materia['id_asignacion'], ENT_QUOTES, 'UTF-8');
        $nombre_materia = htmlspecialchars($datos_materia['nombre_materia'], ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($datos_materia['descripcion'], ENT_QUOTES, 'UTF-8');
        $categoria = htmlspecialchars($datos_materia['mid_categoria_curso'], ENT_QUOTES, 'UTF-8');
        $icono = consultar_link_icono($datos_materia['icono_asignacion']); 
        
        // Determina si el curso está visible.
        $es_visible = (strtolower(trim($datos_materia['visible'] ?? 'si')) !== "no");
        $visible_class = $es_visible ? '' : 'oculto';
        $texto_visibilidad = $es_visible ? 'Ocultar curso' : 'Mostrar curso';

        // Variables para facilitar la búsqueda en el DOM
        $texto_busqueda = strtolower($nombre_materia . ' ' . $categoria);

        ?>
        <!-- Se añade el atributo data-busqueda para indexar el texto filtrable -->
        <div id="curso-home-<?= $id_asignacion ?>" class="curso-home-card <?= $visible_class ?>" data-busqueda="<?= $texto_busqueda ?>">
            
            <!-- Menú Contextual Integrado -->
            <div class="dropdown-context-menu">
                <button class="btn-contextual" onclick="toggleMenu('menu-<?= $id_asignacion ?>')" title="Opciones">⋮</button>
                <ul id="menu-<?= $id_asignacion ?>" class="context-menu-list">
                    <li>
                        <a href="<?= SGA_CURSOS_URL ?>/curso.php?asignacion=<?= $id_asignacion ?>" target="_blank">Entrar al curso</a>
                    </li>
                    <li>
                        <a href="<?= SGA_URL ?>/apps/PlanMind/index.php?asignacion=<?= $id_asignacion ?>">Planeación</a>
                    </li>
                    
                    <?php if ($rol === 'admin' || $rol === 'docente'): ?>
                        <li style="border-top: 1px solid #eee;">
                            <button class="control-visibilidad-btn" 
                                    data-id="<?= $id_asignacion ?>" 
                                    data-visible="<?= $es_visible ? 'si' : 'no' ?>">
                                <?= $texto_visibilidad ?>
                            </button>
                        </li>
                        <li>
                            <a href="#">Editar configuración</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Contenido Principal de la Tarjeta -->
            <a target="_blank" class="enlace_sin_estilo" title="<?= $descripcion ?>" href="<?= SGA_CURSOS_URL ?>/curso.php?asignacion=<?= $id_asignacion ?>">
                <strong><?= puntos_suspensivos($nombre_materia, 20) ?></strong> (<?= $categoria ?>)
            </a>
            
            <div style="margin-top: 10px;">
                <img title="Imagen del curso" class="imagen_tarjeta" src="<?= $icono ?>" alt="Icono de <?= $nombre_materia ?>" style="max-width: 100px; height: auto;">
            </div>
        </div>
        <?php
    }
    
    echo '</div>'; // Cierra contenedor-lista-cursos

    // Script para manejar la búsqueda y los menús contextuales
    ?>
    <script type="text/javascript">
        // Lógica de búsqueda asíncrona en el cliente
        document.addEventListener('DOMContentLoaded', function() {
            var buscador = document.getElementById('buscador-cursos');
            if(buscador) {
                buscador.addEventListener('input', function(e) {
                    var termino = e.target.value.toLowerCase().trim();
                    var tarjetas = document.querySelectorAll('.curso-home-card');

                    tarjetas.forEach(function(tarjeta) {
                        var textoIndexado = tarjeta.getAttribute('data-busqueda');
                        if (textoIndexado.includes(termino)) {
                            tarjeta.classList.remove('oculto-por-busqueda');
                        } else {
                            tarjeta.classList.add('oculto-por-busqueda');
                        }
                    });
                });
            }
        });

        // Lógica de menús contextuales
        function toggleMenu(menuId) {
            // Cierra todos los menús primero
            document.querySelectorAll('.context-menu-list').forEach(function(menu) {
                if (menu.id !== menuId) menu.style.display = 'none';
            });
            // Alterna el menú actual
            var menu = document.getElementById(menuId);
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        }

        // Cierra el menú si se hace clic fuera de él
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown-context-menu')) {
                document.querySelectorAll('.context-menu-list').forEach(function(menu) {
                    menu.style.display = 'none';
                });
            }
        });
    </script>
    <?php
}
}



?>
