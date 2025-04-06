<form action ="importar.php" ENCTYPE="multipart/form-data" method="POST">
    <td><input type="file" name="datos"/>
    	<input name="enviar" type="submit" value="importar"/>
    </td>
<?php
if (isset($_POST['enviar'])){
require '../comun/conexion.php';  
$archivo= $_FILES['datos']['tmp_name'];
require_once '../comun/funciones.php'; 
require '../comun/conexion.php'; 
$TablayCamposbd = '`usuario`(`id_usuario`, `usuario`, `clave`, `nombre`, `apellido`, `rol`, `foto`, `direccion`, `telefono`, `correo`, `ultima_sesion`) '; // determinamos el nombre de la tabla y campos a insertar en el orden de la base de datos
$sql_estudiante ='INSERT INTO `estudiante`(`id_estudiante`, `id_categoria_curso`)';
if (($archivo_abierto = fopen($archivo, "r")) !== FALSE) { //
//$num_campos = count($nombres_campos); 
$insertar ="INSERT INTO $TablayCamposbd Values"; 
$insertar_estudiante = "INSERT INTO $sql_estudiante Values";
$ValoresDeInsert =""; 
$ValoresDeInsert_estudiante ="";
 while ($celdas = fgetcsv ($archivo_abierto, 1000, ";")){  //obtenemos las celdas del archivo separadas por ;
$ValoresDeInsert = $ValoresDeInsert.'("'.$celdas[0].'","'.$celdas[1].'","'.encriptar($celdas[2]).'","'.$celdas[3].'","'.$celdas[4].'","'.$celdas[5].'","'.$celdas[6].'","'.$celdas[7].'","'.$celdas[8].'","'.$celdas[9].'","'.$celdas[10].'","'.$celdas[11].'"),';//  ValoresDeInsertUES (celda[n] )";  //determinamos la cantidad y posiciones de celda de un registro
$ValoresDeInsert_estudiante = $ValoresDeInsert_estudiante.'("'.$celdas[0].'","'.$celdas[11].'"),';//  ValoresDeInsertUES (celda[n] )";  //determinamos la cantidad y posiciones de celda de un registro
                } 
$ValoresDeInsertSinComaAlFinal = substr ($ValoresDeInsert, 0, strlen($ValoresDeInsert) - 1); // eliminar el ultimos punto y coma del inset
$ValoresDeInsert_estudiante_SinComaAlFinal = substr ($ValoresDeInsert_estudiante, 0, strlen($ValoresDeInsert_estudiante) - 1);

$sql_estudiante = $insertar_estudiante.''.$ValoresDeInsertSinComaAlFinal.'';
$sql = $insertar.''.$ValoresDeInsertSinComaAlFinal.''; //Unimos la consulta insert y los valores extraidos de la hoja de calculo para ser insertados
 $sql = $sql.' '.$sql_estudiante ;
#exit();
$insertar = $mysqli -> query ($sql); //Enviamos la consulta a base de datos
 if ($mysqli->affected_rows > 0) { ?> <!-- si se inserto correctamente -->
<script type="text/javascript">
	alert ('datos importados correctamente'); //presentamos un mensaje de exito
	window.location = 'index.php' ; //redireccionamos al archivo inicial
</script>
							<?php	}
else {?> <!-- de lo contrarioe -->
<script type="text/javascript">
	alert ('verificar información'); //presentamos un mensaje de verificación
	window.location  = 'index.php' ;//redireccionamos al archivo inicial

</script>
							<?php
	}

} //cerramos el if de la linea 5
}
    ?>