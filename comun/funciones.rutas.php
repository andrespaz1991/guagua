<?php

if (isset($_GET['actualizar_posicion_portada'])){
$contenidoc = ob_get_clean();
actualizar_posicion_portada($_POST['x'],$_POST['y']);
exit();
}
if (isset($_GET['buscar_iconos'])){
$contenidoc = ob_get_clean();
buscar_iconos($_POST['datos'],'',$_POST['destino']);
exit();
}
if (isset($_GET['nuevo_foro'])){
  ob_clean();
//print_r($_POST);
$resultado = nuevo_foro($_POST['titulo_foro'],$_POST['roles_grupo'],$_POST['contexto'],$_POST['valor'],$_POST['permitir_temas'],$_POST['icono_seleccionado']);//_actividad
if($resultado){
   echo "1";
}else{
    echo "0";
}
  exit();
}
if (isset($_GET['duplicar_cuestionario'])){
  ob_clean();
//print_r($_POST);
$resultado = duplicar_cuestionario($_POST['id']);
if($resultado){
   echo "1";
}else{
   echo "0";
}
  exit();
}
if (isset($_GET['denunciar_mensaje'])){
$contenidoc = ob_get_clean();
  if(denunciar_mensaje($_GET['denunciar_mensaje'])){
      echo "1";
  }else{
      echo "0";
  }
  exit();
}
if (isset($_GET['enviar_mensaje'])){
$contenidoc = ob_get_clean();
$resp = enviar_mensaje($_POST['usuario'],$_POST['mensaje']);
    if ($resp==true){
        echo "1";
    }else{
        echo "0";
    }
    exit();
}
if (isset($_GET['bandeja_salida'])){
    ob_clean();
if (isset($_POST['datos'])) bandeja_salida($_POST['datos']);
    exit();
}
if (isset($_GET['olvidar_usuario'])){
  ob_clean();
  $olvidar_usuario = $_GET['olvidar_usuario'];

$cookie_name = "usuarios[".$olvidar_usuario."]";
$cookie_value ="";
  if (setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/")){
      echo "1";
  }else{
      echo "0";
  }
  exit();
}
if (isset($_GET['consultar_foto'])){
ob_clean();
echo consultar_foto($_GET['consultar_foto']);
exit();
}
if (isset($_GET['consultar_datos_usuario'])){
ob_clean();
require("../comun/autoload.php");
$persona=New Persona();
$datos = $persona->consultar_datos_usuario($_GET['consultar_datos_usuario']);
print_r (json_encode($datos));
exit();
}
if (isset($_GET['revisar_adjunto'])){
$contenidoc = ob_get_clean();
revisar_adjunto($_POST['id_estudiante'],$_POST['nombre']);
exit();
}
if (isset($_GET['revisar_cuestionario'])){
$contenidoc = ob_get_clean();
revisar_cuestionario($_POST['id_cuestionario'],$_POST['id_estudiante']);
exit();
}
if (isset($_GET['guardar_valoracion'])){
$contenidoc = ob_get_clean();
if (isset($_POST['id_act_val']))
guardar_valoracion($_POST['id_seguimiento'],$_POST['id_act_val'],$_POST['id_inscripcion'],date("Y-m-d H:i:s"),$_POST['valoracion'],$_POST['observacion']);
exit();
}
if (isset($_GET['buscar_estudiantes_actividad'])){
$contenidoc = ob_get_clean();
if (isset($_POST['id_actividad'],$_POST['datos'])) buscar_estudiantes_actividad($_POST['id_actividad'],$_POST['datos']);
exit();
}
if (isset($_GET['buscar_cuestionario'])){
$contenidoc = ob_get_clean();
if (isset($_POST['datos'])) buscar_cuestionario_encurso($_POST['datos']);
exit();
}
if (isset($_GET['nueva_categoria_curso'])){
$contenidoc = ob_get_clean();
if (isset($_POST['datos'])) nueva_categoria_curso($_POST['datos']);
exit();
}
if (isset($_GET['buscar_cuestionario_pag'])){
$contenidoc = ob_get_clean();
if (isset($_POST['datos'])) buscar_cuestionario_pag($_POST['datos']);
exit();
}
if (isset($_GET['buscar_mensaje'])){
ob_clean();
if (isset($_POST['datos'])) buscar_mensajes($_POST['datos']);
exit();
}
if (isset($_GET['buscar_mensaje_denuncias'])){
ob_clean();
if (isset($_POST['datos'])) buscar_mensajes($_POST['datos'],"denuncias");
exit();
}
if (isset($_GET['buscar_mensaje_suscripciones'])){
ob_clean();
if (isset($_POST['datos'])) buscar_mensajes($_POST['datos'],"suscripciones");
exit();
}
if (isset($_GET['buscar_mensaje_favoritos'])){
ob_clean();
if (isset($_POST['datos'])) buscar_mensajes($_POST['datos'],"favoritos");
exit();
}
if (isset($_GET['buscar_mensaje_papelera'])){
ob_clean();
if (isset($_POST['datos'])) buscar_mensajes($_POST['datos'],"papelera");
exit();
}
if (isset($_GET['papelera_mensaje'])){
ob_clean();
if(papelera_mensaje($_GET['papelera_mensaje'])){
      echo "1";
  }else{
      echo "0";
  }
exit();
}
if (isset($_GET['cambiar_docente_asignacion'])){
ob_clean();
if(cambiar_docente_asignacion($_POST['id_asignacion'],$_POST['id_docente'])){
      echo "1";
  }else{
      echo "0";
  }
exit();
}
if (isset($_GET['asignar_acudiente'])){
ob_clean();
asignar_acudiente($_POST['id_acudiente'],$_POST['id_estudiante']);
exit();
}
if (isset($_GET['eliminar_asignacion_acudiente'])){
$contenidoc = ob_get_clean();
echo eliminar_asignacion_acudiente($_POST['id_acudiente_estudiante']);
exit();
}
if (isset($_GET['num_mensajes'])){
$contenidoc = ob_get_clean();
num_mensajes();
exit();
}
if (isset($_GET['denunciar_tema'])){
require_once("funciones.php");
ob_clean();
if (isset($_POST['id_tema']))
if ($_POST['id_tema']!="")
$respuesta = denunciar_tema($_POST['id_tema'],$_POST['contenido_denuncia'],$_POST['tipo_denuncia']);
if ($respuesta==true)
echo "1";
else
echo "0";
exit();
}
if (isset($_GET['suscribir_tema_foro'])){
ob_clean();
if (isset($_POST['id_entrada']))
if ($_POST['id_entrada']!="")
suscribir_tema_foro($_POST['id_entrada'],$_POST['valor']);
exit();
}
if (isset($_GET['contar_suscripbiones'])){
ob_clean();
if (isset($_POST['id_entrada']))
if ($_POST['id_entrada']!="") suscribir_tema_foro($_POST['id_entrada']);
exit();
}
if (isset($_GET['leer_mensaje'])){
if (isset($_POST['id_mensaje']))
if ($_POST['id_mensaje']!="") leer_mensaje($_POST['id_mensaje']);
exit();
}
if (isset($_GET['nuevo_cuestionario'])){
$datos_nuevo = array();
$datos_nuevo['id']=NULL;
$datos_nuevo['nombre']=$_POST['nombre'];
$datos_nuevo['tipo_cuestionario']=$_POST['tipo'];
if (isset($_POST['nombre']))
 guardar_cuestionario($datos_nuevo);
exit();
}
if (isset($_GET['guardar_cuestionario'])){
$datos_nuevo = array();
$datos_nuevo['id']=$_POST['id'];
$datos_nuevo['nombre']=$_POST['nombre'];
$datos_nuevo['tipo_cuestionario']=$_POST['tipo_cuestionario'];
if (isset($_POST['nombre'])) guardar_cuestionario($datos_nuevo);
  exit();
}
if (isset($_GET['guardar_icono'])){
require("../comun/conexion.php");
require_once("../comun/config.php");
require_once("../comun/funciones.php");
$tamaño_maximo= tamaño_maximo(); 
$formatos =formatos();
#print_r($_POST);
//print_r($_FILES);
$nombre_archivo = $_FILES['imagen_icono']['name']; 
$ruta_tmp_archivo = $_FILES['imagen_icono']['tmp_name'];
$ruta_destino = dirname(__FILE__)."/img/png/".$_FILES['imagen_icono']['name'];
#echo $ruta_destino;
if ($ruta_tmp_archivo != ""){ 
            $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION)); 
       if (in_array($extensión_archivo, $formatos)){
echo "El formato no es valido";
       } 
       if(filesize($_FILES['imagen_icono']['tmp_name'][$i]) > $tamaño_maximo ){
echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
      exit(); 
       }

       }
    if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) {
     /*recibo los campos del formulario proveniente con el método POST*/ 
     $sql = "INSERT INTO iconos ( `icono`, `imagen_icono`) VALUES ( '".$_POST['icono']."', '".$_FILES['imagen_icono']['name']."')";
     /*echo $sql;*/
        if ($insertar = $mysqli->query($sql)) {
         /*Validamos si el registro fue ingresado con éxito*/ 
echo "1";
        }else{ 
echo "0";
        }
    }
exit();
}
if(isset($_GET['buscar_docente_para_asignar'])){
ob_clean();
buscar_docente_para_asignar($_POST['datos']);
exit();
}
if(isset($_GET['buscar_acudiente_para_asignar'])){
ob_clean();
buscar_acudiente_para_asignar($_POST['datos']);
exit();
}
if (isset($_GET['clonar_curso'])){
  require_once("funciones.php");
    ob_clean();
     if(fn_clonar_curso($_GET['clonar_curso'])){
         echo "1";
     }else{
         echo "0";
     }
     exit();
}
?>