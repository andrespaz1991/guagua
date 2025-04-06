<?php
#print_r($_POST);

session_start();
require ("conexion.php");
$docente = $_POST['docente'];
$asignacion = $_POST['asignacion'];
$respuesta = $_POST['respuesta'];
//$sql ="INSERT INTO `respuesta_estudiante`(`id_estudiante`, `id_asignacion`) VALUES ('".$_SESSION['id_estudiante']."','$asignacion')";
//if ($consulta =$mysqli -> query ($sql)){
//$sql ="INSERT INTO `respuesta`(`id_pregunta`, `valor_escala`, `id_asignacion`) VALUES ";
$NR =0;
$I =0;
$MA = 0;
foreach($respuesta as $id_pregunta => $escala){
$miArray = array($id_pregunta=>$escala);
    $x= (json_encode($miArray));
    print_r(json_decode($x));
    
    $sql = 'select * from  escala' ;
  
    
if  ($escala == 'NR') {
    
    
   echo "cantidad de NR  ".$NR =$NR +1;
                    }
if  ($escala == 'I') {
    
   echo "cantidad de I  ".$I =$I +1;
                    }  
if  ($escala == 'MA') {
    
   echo "cantidad de MA  ".$MA =$MA +1;
                    }  
if  ($escala == 'MI') {
    
   echo "cantidad de MI  ".$MI =$MI +1;
                    }  
if  ($escala == 'NR') {
    
   echo "cantidad de NR  ".$NR =$NR +1;
                    }  

                                        }
?>