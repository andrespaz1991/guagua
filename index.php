<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();
@session_start();
?>
<script>
window.onload = function() {
    document.getElementById('nombrees').focus();
};


$(document).ready(function() {
    // Se usa delegación de eventos para que funcione con contenido cargado dinámicamente.
    $(document.body).on('click', '.control-visibilidad-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var button = $(this);
        var asignacionId = button.data('id');
        var currentState = button.data('visible');
        var newState = (currentState === 'si') ? 'no' : 'si';

        $.ajax({
            url: 'cursos/actualizar_visibilidad.php', // Asegúrate que esta ruta sea correcta
            type: 'POST',
            data: JSON.stringify({
                id_asignacion: asignacionId,
                visible: newState
            }),
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Al tener éxito, se recarga la página.
                    // Esto asegura que la lista de cursos se sincronice perfectamente
                    // con el estado actualizado en la base de datos.
                    location.reload();
                } else {
                    console.error('Error del servidor: ' + response.message);
                    alert('Hubo un error al actualizar el curso.');
                }
            },
            error: function() {
                console.error('Error de comunicación AJAX.');
                alert('Hubo un error de comunicación con el servidor.');
            }
        });
    });
});

  </script>
  
<?php
###############################
$_SESSION['rol']="docente";
$_SESSION['id_usuario']="1085290375";
$_SESSION['id_institucion']="7";
##################################
require_once("comun/autoload.php");
#unset($_SESSION['barra_busqueda']);
$academico=new Academico();
$eventos=new Eventos();
$mensajes=new Mensajes();   
$actividad=new Actividad();
$curso=new Curso();
$red=new Red(); 
$fecha=new Fecha();
$persona=new Persona();             
####### Notificar Sobre Eventos

$eventos->notificador_eventos(date('Y-m-d'));
$academico->verificarAsistenciaYRedirigir();
#exit();
####### Notificar Sobre Eventos
$academico->ano_lectivo =ano_lectivo();

if(isset($_SESSION['id_institucion'])){
  $datos_curso =$academico ->mis_cursos_otros(); 
}

if(!isset($_SESSION['rol'])) $_SESSION['rol']="invitado";
$tarjetas=$persona->permiso_home();

if(!empty($_SESSION) and $_SESSION['rol']=="docente"){
if(!isset($_SESSION["asistencia"]) or $_SESSION["asistencia"]=="si"){
$asistencia=$academico->consultar_horario(true);
}
}

?>
<style type="text/css">
body{
    background-color:#31708f!important;
}
</style>
<br>
<?php 
foreach($tarjetas as $clave => $row){ ?>
 <div class="col-xs-12 col-lg-4 widget-header-item">
 <div class="panel panel-<?php echo $row['class_color'] ?>">
                    <div class="panel-heading">
                      <h4><?php echo $row['titulo'] ?></h4>
                                <a href="<?php echo $row['accion_rapida'] ?>"><img style='margin-top:-12%' align="right" id="icono_tarjeta_home" src="<?php echo SGA_COMUN_URL ?>/img/png/<?php echo $row['icono'] ?>"></a>
                    </div>
                    <div style="overflow: scroll;"  class="panel-body tarjeta">
                              <p>
                              <?php
                            #  echo "<pre>";
                            # print_R($row['funcion']);
                            # print_R("</pre>");
                             eval($row['funcion']) ?>
                              </p>                 
                    </div>
                    <div class="panel-footer">
                                <a href="<?php echo SGA_URL.$row['href'] ?>" class="btn btn-<?php echo $row['class_color'] ?>">Ver más</a>
                    </div>
                          
                          </div>
  </div>                          
<?php } ?>

<?php $contenido = ob_get_contents();
ob_clean();
include (dirname(__FILE__)."/comun/plantilla.php");
?>