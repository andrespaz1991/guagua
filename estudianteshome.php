<?php
require("comun/autoload.php");
$academico=new Academico;

if(!empty($_GET['name'])){
$name=$_GET['name'];
}else{
$name='';
}

if(!empty($_GET['nombre'])){
$nombre=$_GET['nombre'];
}else{
$nombre='';
}


if(!empty($_GET['nid'])){
$nid =$_GET['nid'];
    }else{
        $nid='';
    }

$lista= $academico->estudiantes_de_una_asignacion($nombre,$nid,$name);
#echo "<pre>";
#print_r($_GET['nombre']);
#echo "</pre>";
echo json_Encode($lista);

?>