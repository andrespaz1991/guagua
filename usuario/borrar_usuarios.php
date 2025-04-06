<?php
require("conexion.php");
$consulta = $mysqli->query("SELECT * FROM `usuario`");
while ($resutados = $consulta->fetch_assoc()){
    $eliminar = $mysqli->query("DELETE FROM `usuario` WHERE `id_usuario` ='".$resutados['id_usuario']."'");
}

?>