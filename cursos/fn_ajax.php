<?php
require '../comun/conexion.php';
if (isset($_POST['texto'])){
$datos = $_POST['texto'];
 $sql= 'SELECT * FROM `asignacion_academica`, materia , usuario, ano_lectivo where ano_lectivo.id_ano_lectivo = asignacion_academica.ano_lectivo and materia.id_materia = asignacion_academica.id_materia and usuario.id_usuario = asignacion_academica.id_docente and (LOWER(materia.nombre_materia)) like "%'.mb_strtolower($datos, 'UTF-8').'%" or (LOWER(usuario.nombre)) like "%'.mb_strtolower($datos, 'UTF-8').'%" or (LOWER(usuario.apellido)) like "%'.mb_strtolower($datos, 'UTF-8').'%"';
$consulta = $mysqli -> query($sql);
while ($row = $consulta ->fetch_assoc()){
    ?>$('#<?php echo "id_".$row['ano_lectivo'];?>').show();
    $('#<?php echo "cat_".$row['ano_lectivo'].$row['id_categoria_curso'];?>').show();<?php
}
}
?>