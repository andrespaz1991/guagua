
<?php

function redireccionar () {//Inicio función redireccionar
@session_start();
  if ($_SESSION['rol']=="docente") { ?>
<script type="text/javascript">
   window.location="curso.php?<?php echo $_COOKIE['mirutactividades'];?>";
</script> 
<?php
                                    } 
 @session_start(); 
if ($_SESSION['rol']=="superadmin" or $_SESSION['rol']=="admin" ) { 
    ?>
<script type="text/javascript">
    window.location="curso.php?<?php echo $_COOKIE['mirutactividades'];?>";
</script> 
                             <?php }                                                                    
} // fin redireccionar () ;
?>

<?php
require '../comun/conexion.php';
$sql = 'delete  from actividad where id_actividad = "'.$_GET['id_m'].'"' ;
#$insertar = $mysqli -> query ($sql);
if ($insertar = $mysqli->query($sql)) { ?>
<script type="text/javascript">
    alert ('Actividad eliminada con exito');</script>	
             <?php        redireccionar();                 
                                        }// fin si existe 

else{ ?>
<script type="text/javascript">
    alert ('Verificar información');</script>  
             <?php        redireccionar();               
                                        }// fin si existe 
/*
   $dir="../documento/";
$dirr = opendir($dir);
 $carpetacurso = "../documento/".$_GET['asignacion']."/";
if (is_dir($carpetacurso)){

         //mkdir($dir.$asignacion);
         if ($_GET['v'] == ''){
             
         }
         else {
unlink($_GET['v']);
                }
}
 $sql = 'delete  from actividad where id_actividad = "'.$_GET['id_m'].'"' ;

$consultar = $mysqli -> query ($sql);
//Validamos si el registro fue ingresado con éxito
if ($insertar = $mysqli->query($sql)) { ?>
<script type="text/javascript">
    alert ('Actividad eliminada con exito');</script>	
             <?php        redireccionar();                 
                                        }// fin si existe 

else{ ?>
<script type="text/javascript">
    alert ('Verificar información');</script>  
             <?php        redireccionar();               
                                        }// fin si existe 
//}
*/
?>
