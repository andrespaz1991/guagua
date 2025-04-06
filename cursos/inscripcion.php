<?php @session_start(); if (isset($_SESSION['tipo'])) { ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Mis cursos</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <script src="../js/jquery.min.js"></script>
  <script src="../js/bootstrap.min.js"></script>
  <style>
    /* Remove the navbar's default margin-bottom and rounded borders */ 
    .navbar {
      margin-bottom: 0;
      border-radius: 0;
    }
    
    /* Add a gray background color and some padding to the footer */
    footer {
      background-color: #f2f2f2;
      padding: 25px;
    }
  </style>
</head>
<body>

<?php #require("navbar-default.php"); ?>

    <div class="collapse navbar-collapse" id="myNavbar">
     <ul class="nav navbar-nav">
        <?php @session_start(); ?>
 <?php ///session_start(); 
  if (   $_SESSION['tipo']=="superadmin" or $_SESSION['tipo']=="admin")  { ?>
        <li class="active"><a href="mis_cursos.php">Cursos</a></li>
                                           <?php } ?>
    <?php //session_start();
      if ($_SESSION['tipo']=="docente")  { ?>
        <li class="active"><a href="mis_cursosd.php">Mis cursos</a></li>
                                           <?php } ?>
      <?php //session_start(); 
       if ($_SESSION['tipo']=="estudiante")  { ?>
        <li class="active"><a href="mis_cursos.php">Mis cursos</a></li>
                                           <?php } ?>
                                           
<?php //session_start();  
if ( $_SESSION['tipo']=="estudiante") { ?>
        <li><a href="inscripcion.php">Nuevos Cursos</a></li>
                                   <?php } ?>
<?php if ($_SESSION['tipo']=="docentes" or $_SESSION['tipo']=="superadmin" or  $_SESSION['tipo']=="admin" ) { ?>
        <li><a href="estudiante.php">Estudiantes</a></li>
        <li><a href="docente.php">Docentes</a></li>

                                   <?php } ?>                                   
      </ul>
       <ul class="nav navbar-nav navbar-right">
 <?php # require 'menu.php'; ?>
       </ul>
    </div>
  </div>
</nav>

<div class="jumbotron">
  <div class="container text-center">
    <h1>Inscripción a Cursos</h1>      
    <p>Mi espacio de cursos </p>
  </div>
</div>
<div class="container-fluid bg-3 text-center">    
  <h3>Selecciona e inscribete al curso deseado..</h3><br>
 <div class="row">
<form action="" method="POST">
  <p>  
   
  <form id="form1" name="form1" method="post" action="estudiantei.php">
<h1>Inscribir</h1>
<label> Buscar curso:</label>
 <input placeholder="Seleccione el curso" autocomplete="off" list="suggestionList" id="answerInput">
<datalist id="suggestionList">

  <?php 
require '../comun/conexion.php';
    $sqles = 'SELECT *  FROM asignacion_academica,materia,docente where
  asignacion_academica.id_materia = materia.id_materia and
  docente.id_docente =  asignacion_academica.id_docente 
  group by materia.id_materia';
$consultaes = $mysqli -> query ($sqles);
while ($rowes = $consultaes ->fetch_assoc()) {?>
   <option label ="<?php echo $rowes['nombre_docente'].' '.$rowes['apellido_docente'] ; ?>" data-value="<?php echo $rowes['id_asignacion'] ; ?>" ><?php echo $rowes['nombre_materia'] ; ?></option>

<?php 
}

?>
</datalist>

<input  type="hidden" name="asignacion" id="answerInput-hidden"></p>
<script>
    document.querySelector('input[list]').addEventListener('input', function(e) {
    var input = e.target,
        list = input.getAttribute('list'),
        options = document.querySelectorAll('#' + list + ' option'),
        hiddenInput = document.getElementById(input.id + '-hidden'),
        inputValue = input.value;
    hiddenInput.value = inputValue;
    for(var i = 0; i < options.length; i++) {
        var option = options[i];
        if(option.innerText === inputValue) {
            hiddenInput.value = option.getAttribute('data-value');
            break;
        }
    }
});
</script>
    <?php #echo $sql ?>
  </p><br>
  <p>
    <input type="submit" name="button" id="button" value="Inscribir">
  </p>
</form>
<?php
if (isset($_POST['asignacion'])){
//session_start();
 $docente = $_POST['asignacion'];
$estudiante =  $_SESSION['identificacion_usu'];
$fechaactual = date('Y/m/d');
 $sql='select *  FROM inscripcion ,estudiante
where
estudiante.id_estudiante = inscripcion.id_estudiante and
 inscripcion.id_asignacion = "'.$docente.'"
and estudiante.identificacion = "'.$estudiante.'" ';
$consultarinscripcion = $mysqli	-> query ($sql);
if ($mysqli -> affected_rows > 0 ){
    echo 'usted ya se encuentra registrado en el curso';
  }
  else {
$sqlid_es = "select * from estudiante where identificacion =$estudiante " ;
$consulta_es  =$mysqli -> query ($sqlid_es);
while ($rowes =  $consulta_es ->fetch_assoc()){
 $sql ='insert into  inscripcion (id_asignacion,id_estudiante,fecha_inscripcion)VALUES("'.$docente.'","'.$rowes['id_estudiante'].'","'.$fechaactual.'")';
                                }

$insertar = $mysqli	-> query ($sql);
if ($mysqli -> affected_rows > 0 ){

echo "matricula exitosa, su código de inscripción es ".$mysqli->insert_id ; ;	
	
}
else {echo "Verifique la información";}
}
}                 

?>
</div>
</div><br>
<?php require 'footer.php'; ?>

</body>
</html>
<?php } ?>