<?php 
ob_start();
#require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
require 'funciones.php';
require('conexion.php'); 
?>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.7.0/tinymce.min.js" integrity="sha512-XaygRY58e7fVVWydN6jQsLpLMyf7qb4cKZjIi93WbKjT6+kG/x4H5Q73Tff69trL9K0YDPIswzWe6hkcyuOHlw==" crossorigin="anonymous"></script>
  <script>tinymce.init({ selector:'textarea' });</script>

	<meta charset="UTF-8">

	<title>Mi revisión</title>

</head>

<body>

<section>

<?php 

require('../comun/autoload.php'); 

#$_POST['seguimiento']=1;

require 'conexion.php';

 $sql ='select * from usuario, seguimiento 
where usuario.id_usuario = seguimiento.identificacion and seguimiento.id_seguimiento = "'.$_POST['seguimiento'].'" ';

require 'conexion.php';
#echo $sql;
$consulta =$mysqli -> query ($sql);

if ($row=$consulta->fetch_assoc()){


$sql9 = 'select count(*) as total from seguimiento where identificacion = "'.$row['identificacion'].'" and seguimiento.id_seguimiento != "'.$_POST['seguimiento'].'"';

$a=$mysqli -> query ($sql9);

if ($c = $a ->fetch_Assoc() and $c <> 0){



 ?>
<br><br><br><br>
<form action="funciones.php" method="POST" align = "center" border = "2" >
<div class="f1">

<input type="hidden" name="identificacion" value="<?php echo  $row['identificacion'] ;?>"></input>

<label>Contenido</label>

<input name="pro" class = "azul" type="text"  value="<?php echo utf8_decode($row['contenido']) ; ?>" / ><br><br>

<textarea autofocus="" placeholder="Observaciones de la asesoria..." rows="12" cols="40" placeholder="Observaciones"  name="observaciones"><?php echo ($row['observaciones']) ; ?></textarea>

</div>

<div class="f2"> 

<div class="f4">

<script src="countdown.js" type="text/javascript"></script>

<script type="application/javascript">

/*

var myCountdown2 = new Countdown({

									time: 1200, 

									width:150, 

									height:80, 

									rangeHi:"minute"	// <- no comma on last item!

									});

*()

</script>

	<script>

	var inicio=0;

	var timeout=0;

 

	function empezarDetener(elemento)

	{

		if(timeout==0)

		{

			// empezar el cronometro

 

			elemento.value="Detener";

 

			// Obtenemos el valor actual

			inicio=vuelta=new Date().getTime();

 

			// iniciamos el proceso

			funcionando();

		}else{

			// detemer el cronometro

 

			elemento.value="Empezar";

			clearTimeout(timeout);

			timeout=0;

		}

	}

 

	function funcionando()

	{

		var actual = new Date().getTime();

		var diff=new Date(actual-inicio);

		var result=LeadingZero(diff.getUTCHours())+":"+LeadingZero(diff.getUTCMinutes())+":"+LeadingZero(diff.getUTCSeconds());

		document.getElementById('crono').innerHTML = result;

		timeout=setTimeout("funcionando()",1000);

	}

 

	/* Funcion que pone un 0 delante de un valor si es necesario */

	function LeadingZero(Time) {

		return (Time < 10) ? "0" + Time : + Time;

	}

	</script>

	<style>

	.crono_wrapper {text-align:center;width:200px;}

	</style>

</head>

 

<body>



<div style="margin-top:5%;" class="crono_wrapper">

<h1>Tiempo</h1>

	<h2 id='crono'>00:00:00</h2>

	<input type="button" value="Empezar" onclick="empezarDetener(this);">

</div>

</body>

</html>



</div>

<!--<label>Asistio</label> -->

<input type="hidden"  name="asistio" value="SI" checked ></input>

<input type="hidden" name="seguimiento" value="<?php echo  $_POST['seguimiento'] ;?>"></input>



<div id="informacion_basica" style="margin-left:20%">

<label ><strong>Estudiante:</strong> <?php echo $row['nombre'].' ' ; ?> </label><br><br>

<label><strong>Fecha:</strong><?php echo Fecha::formato_fecha($row['fecha_asesoria']).'<br> de las '.Fecha::formato_hora($row['hora_inicio'])  ; ?> a <?php echo  Fecha::formato_hora($row['hora_fin'])?> </label><br><br>

<label><strong>Asesoria tecnica</strong></label>

<input type="radio" 

<?php

if (!isset($rw['asesoria_tecnica'])){

 echo 'checked';

}

?> name="asesoria_tecnica" value="SI" <?php if (isset($rw['asesoria_tecnica']) and $rw['asesoria_tecnica'] == 'SI') echo 'checked' ; ?> >SI</input>

<input type="radio" name="asesoria_tecnica" <?php if (isset($rw['asesoria_tecnica']) and $rw['asesoria_tecnica'] == 'NO') echo 'checked' ;  ?> value="NO">NO</input><br><br>

<input type="hidden" name="seguimiento" value="<?php echo  $_POST['seguimiento'] ;?>"></input>


<label><strong>Asesoria #</strong></label>

<?php



$persona=new Persona($row['identificacion']);

echo $persona->cantidad_asesorias_persona();

# echo $row['id_seguimiento'];?><br><br>
<input id="button" name="guardar" type="submit" value="Guardar"></input>
</div>
</div>

</body>

</html>

<?php } 

 $e = "  and seguimiento.id_seguimiento != $_POST[seguimiento]";

  $sql ='select * from usuario, seguimiento where usuario.id_usuario = seguimiento.identificacion and seguimiento.identificacion  ='.$row['identificacion'].$e ; 

 $r = ' order by id_seguimiento DESC';

require 'conexion.php';

#echo $sql.$r;
$sql=$sql.$r;
$consulta=$mysqli->query($sql);
$cantidad=($consulta->num_rows);
if ($cantidad>0){ 

?>

<footer>

<table border="2">

<tr>

<th colspan="6">Asesorias Anteriores (<?php echo $c['total'];?>) </th></tr>

<tr>

<th>id</th>

<th>Fecha y hora</th>

<th>Contenido</th>

<th>Observaciones</th>

<th>Asistio</th>

<th>A.Tecnica</th>

</tr>

<?php

$contador =0 ;

while ($row = $consulta -> fetch_assoc()){

$contador ++ ;

if ($contador % 2 == 0) echo '<tr class="par">';

	else echo '<tr class="impar">';

echo '<td>',$row['id_seguimiento'],'</td>';



echo '<td>',Fecha::formato_fecha($row['fecha_fin']).' de '.Fecha::formato_hora($row['hora']).' a '.Fecha::formato_hora($row['hora_fin']),'</td>';

echo '<td>',$row['contenido'],'</td>';

echo '<td>',$row['observaciones'],'</td>';

echo '<td>',$row['asistio'],'</td>';

echo '<td>',$row['asesoria_tecnica'],'</td>';

echo '</tr>';



//echo "Cantidad de Asesorias".$conta;	

}				

#}

#else {

	#echo '<br>';

#echo "Primera Asesoria";

}	

}



?>

<table>

</section>

</footer>

<br>

</form>

<?php
 $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>

