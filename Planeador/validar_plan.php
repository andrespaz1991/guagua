<?php
require_once("../comun/config.php");

require (SGA_COMUN_SERVER.'/conexion.php');

$fechaInicio = $_POST['fechaInicio'];
$fechaFin = $_POST['fechaFin'];
$materia = $_POST['materia'];
$grado = $_POST['grado'];

$sql = "SELECT id_plan FROM planeador_vallesol WHERE (fecha_inicio >= '$fechaInicio' AND fecha_fin <= '$fechaFin')  AND materia = '$materia' ";
$result = $mysqli->query($sql);
if ($result->num_rows > 0) {
    echo 'exito';
    exit();
} else {
    echo "error";
    exit();
}

?>