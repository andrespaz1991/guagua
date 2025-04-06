<?php
require("comun/autoload.php");
$academico=new Academico;
if(!empty($_GET['nid'])){
$academico->id_asignacion =$_GET['nid'];
$nid=$_GET['nid'];
}
if(!isset($_GET['nid'])){
    $nid="";
}
if(empty($_GET['nombre'])){
    $_GET['nombre']="";
}
$lista= $academico->estudiantes_de_una_asignacion($_GET['nombre'],$nid);
#echo "<pre>";
#print_r($lista);
echo json_Encode($lista);
#echo "</pre>";
?>