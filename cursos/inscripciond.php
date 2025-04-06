<?php @session_start(); if (isset($_SESSION['tipo'])) { ?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="copyright" content="© 2016">
<meta name="description" content="">
<link rel="stylesheet" type="text/css" href="css/header.css" />
<title>Inscripción</title>
<style type="text/css">
.h { font-family: Comic Sans MS, cursive;}
</style>
<script language="javascript" src="js/funciones.js"></script>
<script language="javascript" src="js/ajax.js"></script>
</head>
<body>
<?php #require("header.php"); ?>
<?php #require("menu.php"); ?>
<section>
<p align="center">
<h1>Inscripción a curso</h1>
<br>
<form action="" method="POST">
  <p>   
  Seleccione su curso:
    <select requerid name="asignacion">
        <option value="">Seleccione una opcion </option>
      <?php
	require ('../comun/conexion.php');

 $sqlcero = 'select *  FROM inscripcion where id_estudiante  = "'.$_SESSION['id_estudiante'].'" ';
    $consultacero = $mysqli->query($sqlcero);
	while($roww=$consultacero->fetch_assoc()){
echo $roww['id_asignacion'];

	}
	$sql = 'SELECT *  FROM asignacion_academica,materia,docente where
	asignacion_academica.id_materia = materia.id_materia and
	docente.id_docente =	asignacion_academica.id_docente 
	group by materia.id_materia';
    $consulta = $mysqli->query($sql);
	if ($consulta->num_rows){
	  echo "estoy funcionando";
	while($row=$consulta->fetch_assoc()){
	    print_r($row);
	
	?>
      <option value="<?php echo $row['id_asignacion']; ?>">
      <?php echo  $row['nombre_materia'].' (Docente '.$row['nombre_docente'].' '.$row['apellido_docente'].')'; ?>
      </option>
      <?php
  
	}
						}
?>
    </select>
    <?php #echo $sql ?>
  </p><br>
  <p>
    <input type="submit" name="button" id="button" value="Inscribir">
  </p>
</form>
<?php
if (isset($_POST['asignacion'])){
session_start();
 $docente = $_POST['asignacion'];
$estudiante =  $_SESSION['identificacion_usu'];
$fechaactual = date('Y/m/d');
 $sql='select *  FROM inscripcion where inscripcion.id_asignacion = "'.$docente.'"  ';
$consultarinscripcion = $mysqli	-> query ($sql);
if ($mysqli -> affected_rows > 0 ){
    echo 'usted ya se encuentra registrado en el curso';
  }
  else {
  $sql ='insert into  inscripcion (id_asignacion,id_estudiante,fecha_inscripcion)VALUES("'.$docente .'","'.$estudiante .'","'.$fechaactual.'")';

$insertar = $mysqli	-> query ($sql);
if ($mysqli -> affected_rows > 0 ){

echo "matricula exitosa, su código de inscripción es ".$mysqli->insert_id ; ;	
	
}
else {echo "Verifique la información";}
}
}                 

?>
</section>

<?php
require("footer.php"); }?>
</body>
</html>
