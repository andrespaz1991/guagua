<?php 
 require '../comun/conexion.php';
 $sqln = 'select * from asignacion_academica where id_materia="'.$_POST['del'].'"';
$consultan=$mysqli -> query ($sqln);
while ($rown = $consultan -> fetch_assoc()){
 require '../comun/conexion.php';
 $eliminar = 'DELETE FROM `inscripcion` WHERE id_asignacion = '.$rown['id_asignacion'].'';    
 $resultadoeliminar = $mysqli ->query($eliminar);
$dir="../documento/";
$carpetacurso = '../documento/'.$rown['id_asignacion'];
if (is_dir($carpetacurso)){
$dirr = opendir($dir);
 $dir = '../documento/'.$rown['id_asignacion'];
function eliminarDir($carpeta)
{
    foreach(glob($carpeta . "/*") as $archivos_carpeta)
    {
         $archivos_carpeta;
 
        if (is_dir($archivos_carpeta))
        {
            eliminarDir($archivos_carpeta);
        }
        else
        {
            unlink($archivos_carpeta);
        }
    }
 
    rmdir($carpeta);
}
eliminarDir ($dir) ;


}
}

 $sql ='DELETE FROM `asignacion_academica` WHERE id_materia="'.$_POST['del'].'"' ;
$consulta = $mysqli -> query ($sql);
if ($insertar = $mysqli->query($sql)) {
$sql0 ='DELETE FROM `materia` WHERE `id_materia`="'.$_POST['del'].'"';
$consulta0 = $mysqli -> query ($sql0);
?>
<script type="text/javascript">
  alert('Curso eliminado correctamente');
</script>
<?php session_start(); if ($_SESSION['tipo']=="docente") { ?> 
  <script type="text/javascript" >
  window.location="mis_cursosd.php"; 
</script>	
<?php } else { ?>
  <script type="text/javascript" >
  window.location="mis_cursos.php"; 
</script>
<?php } ?>
<?php
}else{
echo 'Verificar información';
}
 session_start(); if ($_SESSION['tipo']=="docente") { ?> 
  <script type="text/javascript" >
  window.location="mis_cursosd.php"; 
</script>	
<?php } else { ?>
  <script type="text/javascript" >
  window.location="mis_cursos.php"; 
</script>
<?php } ?>
