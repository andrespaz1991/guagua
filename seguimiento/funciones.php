<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 

<link rel="stylesheet" type="text/css" href="css/estilo.css" />

<?php

if (isset($_GET['f'])) reporteindividual();

function reporteindividual(){

              header('Content-Type: text/html; charset=ISO-8859-1'); 

              require 'conexion.php';

              $sql ='select estudiante.nombre, seguimiento.id_seguimiento, seguimiento.identificacion, seguimiento.cita,estudiante.proyecto, seguimiento.observaciones, seguimiento.asistio, seguimiento.listo_para_enviar, seguimiento.asesoria_tecnica,seguimiento.hora from estudiante, seguimiento where estudiante.identificacion = seguimiento.identificacion  and id_seguimiento ="'.$_GET['f'].'" ';

              $consultarid = $mysqli -> query ($sql);

              while ($row =$consultarid ->fetch_assoc() ){

              $sqr = 'select * from estudiante where identificacion = "'.$row['identificacion'].'"';

              $consultarestudiante = $mysqli -> query ($sqr);

              $contador = 1; 

              while ($ruw = $consultarestudiante -> fetch_assoc()){

              $contador++; ?>



  <table border ="2" align="center" >

      <tr>

          <TH class="color" width="159" rowspan='5'>

              <img src="img/logo.PNG" width="152" height="120" >

          </TH>



         <TD class="color" width="585" ROWSPAN="2" align="center">

                  <strong> Instituto High System Training  </strong>

          </TD>    

               <TD class="color" width="258">

                    <strong>  Codigo</strong>

                </TD>

      </tr>

      <tr>

          <TD class="color">

                <strong>pagina 1 de 1</strong>

          </TD>

      </tr>

      <TR>

      <TD class="color" ROWSPAN="2" align="center">

          <strong>Asesorias </strong>

     </TD>

      <TD class="color">

            <strong>    versi&oacute;n</strong>

      </TD>

      </TR>

      <TR>

      <TD class="color">

  <strong>    fecha:<?php echo date("j-n-Y");?></strong>

      </TD>

      </TR>

    </table>



  <br/>



<section><?php

 echo '<h1>Informaci&oacute;n del estudiante</h1>';

echo '<label><strong>identificacion</strong> <label>';

echo $ruw['identificacion'].'<br>';

echo '<label><strong>nombre </strong><label>';

echo $ruw['nombre'].'<br>';

echo '<label><strong>celular</strong> <label>';

echo $ruw['celular'].'<br>';

echo '<label><strong>Formato RQ3</strong> <label>';

echo $ruw['rq3'].'<br>' ; 

echo '<label><strong>enviado</strong> <label>';

echo $ruw['enviado'].'<br>' ;

echo '<label><strong>aprobado</strong> <label>';

echo $ruw['aprobado'].'<br>' ;

echo '<label><strong>convenio</strong> <label>';

echo $ruw['convenio'].'<br>' ;

echo '<label><strong>acta_inicio</strong> <label>';

echo $ruw['acta_inicio'].'<br>' ;

echo '<label><strong>acta_finalizacion</strong> <label>';

echo $ruw['acta_finalizacion'].'<br>' ;

echo '<label><strong>convocatoria </strong><label>';

echo $ruw['convocatoria'].'<br>' ;

echo '<label><strong>proyecto</strong> <label>';

echo $ruw['proyecto'].'<br>' ;

echo '<label><strong>entidad </strong><label>';

echo $ruw['entidad'].'<br>' ;

echo '<label><strong>representante</strong> <label>';

echo $ruw['representante'].'<br>' ; 



                                            }

echo '<h1>Seguimiento</h1>';

echo '<table align="center" >';

echo '<thead>';

echo '<tr>';

echo  '<th>','Seguiento','</th>';

echo  '<th>','Cita','</th>';

echo  '<th>','proyecto','</th>';

echo  '<th>','observaciones','</th>';

echo  '<th>','asistio','</th>';

echo  '<th>','listo_para_enviar','</th>';

echo  '<th>','asesoria_tecnica','</th>';

echo  '<th>','hora','</th>';

 echo '</tr>';    

 echo '</thead>';    



if ($contador % 2 == 0) echo '<tr class="impar">';

  else echo '<tr class="par">';

echo  '<td>'.$row['id_seguimiento'].'</td>';

echo  '<td>'.$row['cita'].'</td>';

echo  '<td>'.$row['proyecto'].'</td>';

echo  '<td>'.$row['observaciones'].'</td>';

echo  '<td>'.$row['asistio'].'</td>';

echo  '<td>'.$row['listo_para_enviar'].'</td>';

echo  '<td>'.$row['asesoria_tecnica'].'</td>';

echo  '<td>'.$row['hora'].'</td>';

 echo '</tr>';        

echo '</table>';

                                            }





echo '</section>';

                          }





if (isset($_GET['v'])) logout () ;



function logout () { 

session_start();

unset($_SESSION['user']);

header('location:consultar.php');

                   }



if (isset ($_POST['cod'])) modificar_seguimiento ();

function modificar_seguimiento (){

    $_POST['cod'];

    require 'conexion.php';

    $sql = 'Select * from seguimiento where id_seguimiento = "'.$_POST['cod'].'" ';

    $consulta = $mysqli -> query ($sql);

        if ($row = $consulta ->fetch_assoc()) { 

            header('Content-Type: text/html; charset=ISO-8859-1');   ?>

                <form action ="f2.php" method="POST">

                  <label>proyecto</label>

                    <input type ="number" name= "id_seguimiento" value = "<?php echo $row['id_seguimiento']; ?>"></input><br/>

                    <input name= "proyecto" value = "<?php echo $row['proyecto']; ?>"></input><br/>

                    <label>cita</label>

                    <input name= "cita" value = "<?php echo $row['cita']; ?>" > </input><br/>

                    <label>observaciones</label>

                    <input name= "observaciones"  value = "<?php echo $row['observaciones']; ?>" ></input><br/>

                    <label>asistio</label>

                    <input name= "asistio" value = "<?php echo $row['asistio']; ?>" ></input><br/>

                    <label>listo_para_enviar</label>

                    <input name= "listo_para_enviar" value = "<?php echo $row['listo_para_enviar']; ?>"></input><br/>

                    <label>asesoria tecnica</label>

                    <input name= "asesoria_tecnica" value = "<?php echo $row['asesoria_tecnica']; ?>" ></input><br/>

                    <label>hora</label>

                    <input name= "hora" value = "<?php echo $row['hora']; ?>"></input>

                    <input name= "enviar" type="submit"/>



                                        <?php   }



echo '</form>';





                                  }



?>



<?php



if (isset($_POST['observaciones']) and !isset($_POST['cod'])) ingresarobservacion();

function ingresarobservacion (){
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
      require 'conexion.php';
      $sql0 = 'UPDATE estudiante set contenido = "'.$_POST['pro'].'" where identificacion = "'.$_POST['identificacion'].'" ; ';
      $actualizarproyecto = $mysqli -> query ($sql0);
      $sql = 'UPDATE seguimiento SET observaciones= "'.$_POST['observaciones'].'",asistio="'.$_POST['asistio'].'",asesoria_tecnica="'.$_POST['asesoria_tecnica'].'", contenido = "'.$_POST['pro'].'" WHERE id_seguimiento = "'.$_POST['seguimiento'].'"' ;
      echo $sql;
      require 'conexion.php';
      $actualizarasesoria = $mysqli -> query ($sql);
      if ($mysqli->affected_rows > 0) {?>
          <script type="text/javascript">
          //	alert('Actualización exitosa');

          	</script>



            	<?php

            #header('location:index.php');

                                    }



          									}



if (isset($_POST['noasistio'])) noasistio();



          function noasistio (){



                $observaciones = "No asistio"; 

                $asistio = "NO";

                echo $sql = 'UPDATE seguimiento SET observaciones= "'.$observaciones.'",asistio="'.$asistio.'" WHERE id_seguimiento ="'.$_POST['seguimiento'].'"';

                require 'conexion.php';

                $actualizarasesoria = $mysqli -> query ($sql);

                header('location:index.php');

                                 }



 function actualizarproyecto (){



      require 'conexion.php';

       	$sql = 'SELECT * FROM `estudiante` WHERE `proyecto` = "" ';

       	$consulta = $mysqli ->query ($sql);



     	while ($row = $consulta -> fetch_Assoc()) {

         $sql2 = 'SELECT `proyecto`,id_seguimiento FROM `seguimiento` WHERE identificacion = "'.$row['identificacion'].'" order by id_seguimiento desc limit 1';

		    $consultar = $mysqli ->query ($sql2);

				  while ($raw = $consultar -> fetch_Assoc()) {

              $s = 'UPDATE `estudiante` SET `proyecto`="'.$raw['proyecto'].'" WHERE identificacion  = "'.$row['identificacion'].'";';

              $cactuaizar = $mysqli ->query ($s);

							                             					}



 	                                              }

               header('location:index.php');



                               }





