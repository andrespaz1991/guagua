<?php

ob_start();

date_default_timezone_set('America/Bogota');

if (!isset($_POST['evaluable'])){

    $_POST['evaluable'] = "NO";

}

@session_start();

require ('../comun/conexion.php');// llamo al archivo de conexion 

$id_asignacion = $_POST['id_asignacion'];

if ($_POST['red']=='') { $_POST['red']="NULL"; }

if (!isset($_POST['id_red']) or (isset($_POST['id_red']) and $_POST['id_red']=='')) { $_POST['id_red']="NULL"; }



if (!isset($_POST['adjunto']) or $_POST['adjunto']=="") { $_POST['adjunto']="NO"; } else {  $_POST['adjunto']="SI"; }

$rescuestionario = empty($_POST['cuestionario']) ;

if(isset($_POST['cuestionario'])){

    $_POST['cuestionario']="SI";

} else{

    $_POST['cuestionario']="NO";

}

if(isset($_POST['foro'])){

    $_POST['foro']="SI";

    if($_POST['micheckbox_foro']="crear"){

    //nuevo_foro();

    $id_foro_nuevo = nuevo_foro($_POST['titulo_foro_actividad'],$_POST['roles_grupo_actividad'],$_POST['contexto_actividad'],NULL,$_POST['permitir_temas_actividad'],$_POST['icono_seleccionado_actividad'],true);

    $foro_entrada = new foro();

    $resultado = $foro_entrada->guardar_entrada($_POST['titulo_foro_actividad'],$id_foro_nuevo);

    $_POST['id_foro']=$id_foro_nuevo;

    }

    /*

    //crear foro en cascada en actividad

    $roles_grupo=array("admin","docente","estudiante","acudiente");

    $id_foro_nuevo = nuevo_foro($_POST['titulo_red'],$roles_grupo,"red",$id_red,"NO",$_POST['icono_seleccionado'],true);

    //crear foro en cascada en actividad

    */

} else{

    $_POST['foro']="NO";

}

if (!isset($_POST['foro'])) { $_POST['foro']="NO"; }



$sql ='INSERT INTO `actividad`(visible, ';

 if($_POST['id_actividad']<>""){

  $sql.='id_actividad,';

}

 

 $sql.='   `adjunto`,`cuestionario`,`id_cuestionario`,`id_asignacion`, `fecha_publicacion`, `hora_publicacion`, `id_red`, `nombre_actividad`, `Observaciones`, `evaluable`, `fecha_entrega`, `hora_entrega`, `periodo`, `foro`';
 if($_POST['id_foro']<>""){
$sql.=', `id_foro`';
 }
 $sql.=' ) VALUES ("'.$_POST['visible'].'" ,';

 if($_POST['id_actividad']<>""){

  $sql.='"'.$_POST['id_actividad'].'",';

}

 

 

 

 $sql.='"'.$_POST['adjunto'].'", ';

 $resultado = (empty($_POST['cuestionario']));

if ($resultado==true){

  $sql.='NULL,';

  // $_POST['cuestionario']="NULL";

}

else {

    $sql.='"'.$_POST['cuestionario'].'",';

}

if($_POST['cuestionario']==="NO"){

    $sql.=' NULL , ' ;

} else {

     $sql.='"'.$_POST['id_cuestionario'].'",' ;

}



 $sql.=' "'.$_POST['asignacion'].'","'.$_POST['fecha_publicacion'].'","'.$_POST['hora_publicacion'].'",'.$_POST['red'].',"'.$_POST['id'].'","'.$_POST['observacion'].'","'.$_POST['evaluable'].'","'.$_POST['fecha_entrega'].'","'.$_POST['hora_entrega'].'","'.$_POST['periodo'].'","'.$_POST['foro'].'"';
 
 if($_POST['id_foro']<>""){
  $sql.=',"'.$_POST['id_foro'].'"';
   }
 $sql.=')  ON DUPLICATE KEY UPDATE  ';

if($_POST['id_actividad']<>""){

  $sql.='id_actividad = VALUES(id_actividad) ,';

}

 

$sql.='adjunto = VALUES(adjunto),id_cuestionario = VALUES(id_cuestionario),cuestionario = VALUES(cuestionario),id_asignacion = VALUES(id_asignacion),fecha_publicacion = VALUES(fecha_publicacion),hora_publicacion = VALUES(hora_publicacion),id_red = VALUES(id_red),nombre_actividad = VALUES(nombre_actividad),Observaciones = VALUES(Observaciones),evaluable = VALUES(evaluable),fecha_entrega = VALUES(fecha_entrega),hora_entrega = VALUES(hora_entrega),periodo = VALUES(periodo),foro = VALUES(foro);';

#echo $sql;

$consulta = $mysqli ->query($sql);

if ($mysqli ->affected_rows>0){

$id_actividad = $mysqli ->insert_id;

if (isset($id_foro_nuevo) and $id_foro_nuevo!=""){

$sql_up_foro="UPDATE `guagua`.`grupo_foro` SET `valor` = '".$id_actividad."' WHERE `grupo_foro`.`id_grupo_foro` = ".$id_foro_nuevo.";";

$mysqli ->query($sql_up_foro);

}

    ?>

    <script>

    alert2('Se han guardado los cambios en actividad');

   setTimeout(function(){

  window.location="curso.php?<?php echo $_COOKIE['mirutactividades'];?>";

}, 3000);

        </script>

        <?php

}else{

   ?>

   <script>

   alert2('Hubo un error al guardar los cambios en actividad','error');

  setTimeout(function(){

window.location="curso.php?<?php echo $_COOKIE['mirutactividades'];?>";

   }, 3000);

   </script>

   <?php

}

$contenido = ob_get_clean();

require ("../comun/plantilla.php");

?>