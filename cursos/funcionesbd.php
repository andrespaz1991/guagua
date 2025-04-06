<?php
function escape_string(&$elemento1, $clave)
{
    require("../comun/conexion.php");
    $elemento1 = $mysqli->real_escape_string($elemento1);
}
function quitar_vacios(&$array)
{
	foreach($array as $id=>$valor){
    if ($valor=="") unset($array[$id]);
	}
}
function values_columnas($array)
{
    foreach ($array as $id => $value){
        $array[$id]= " `".$id."` = VALUES(".$id.")";
    }
  return($array);
}
function test_print($elemento2, $clave)
{
    echo "$clave: $elemento2<br />\n";
}

function insertar($array,$tabla,$update = false,$where = ""){
quitar_vacios($array);
$sql = "";
if (count($array)>0){
array_walk($array, 'escape_string');
if (isset($array['clave']) and $array['clave']!="")
$array['clave']=sha1($array['clave']);
$columns = implode("`, `",array_keys($array));
$escaped_values = array_values($array);
$values  = implode("', '", $escaped_values);
$array_values = values_columnas($array);
$value_columns = implode(", ",array_values($array_values));
$sql = "INSERT INTO `$tabla`(`$columns`) VALUES ('$values') $where ";
if($update) $sql .=" ON DUPLICATE KEY UPDATE $value_columns;";
}
return $sql;
}
?>