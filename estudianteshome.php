<?php
require("comun/autoload.php");
$academico=new Academico;
if(!empty($_GET['nid'])){
$academico->id_asignacion =$_GET['nid'];
/*
$nid=$_GET['nid'];
}
if(!isset($_GET['nid'])){
    $nid="";
*/
    }

$lista= $academico->estudiantes_de_una_asignacion($_GET['nombre'],$_GET['nid']);
#echo "<pre>";
#print_r($_GET['nombre']);
#echo "</pre>";
echo json_Encode($lista);

?>