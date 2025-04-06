 <?php
function redireccionar () {//Inicio función redireccionar
@session_start();
  if ($_SESSION['rol']=="docente") { ?>
<script type="text/javascript">
 window.location="actividad_curso.php?<?php echo $_COOKIE['mirutactividades'];?>";
</script> 
<?php
                                    } 
 @session_start(); 
if ($_SESSION['rol']=="superadmin"  or $_SESSION['rol']=="admin") { 
    ?>
<script type="text/javascript">
    window.location="actividad_curso.php?<?php echo $_COOKIE['mirutactividades'];?>";
</script> 
<?php }
} // fin redireccionar () ;
if ($_POST['red']=='') { $_POST['red']="NULL"; }
$sqlactualizar = 'UPDATE `actividad` SET `id_asignacion`="'.$_POST['asignacion'].'",`fecha_publicacion`="'.$_POST['fecha_publicacion'].'",`hora_publicacion`="'.date('g:i:s a').'",`id_red`='.$_POST['red'].',`nombre_actividad`="'.$_POST['id'].'",`Observaciones`="'.$_POST['observacion'].'",`evaluable`="'.$_POST['evaluable'].'",`fecha_entrega`="'.$_POST['fecha_entrega'].'",`hora_entrega`="'.$_POST['hora_entrega'].'",`periodo`="'.$_POST['periodo'].'" WHERE id_actividad ="'.$_POST['actividad'].'"';
require ('../comun/conexion.php');
$consulta = $mysqli ->query($sqlactualizar);
redireccionar();
?>