<?php
require_once("../../comun/config.php");
require("conexion.php");
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=reporte.doc");
$sql='SELECT * FROM `pregunta`';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo $row['cod_pregunta'].')';
echo $row['pregunta'];
echo "a)"."Siempre";
echo "b)"."Aveces";
echo "c)"."Nunca";
echo "d)"."Necesita Mejorar";
echo "e)"."No sabe/No responde";
}
?>