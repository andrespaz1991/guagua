<?php ob_start(); ?>
<div class="jumbotron">
  <div class="container text-center">
  <h1>Recuperar Clave</h1>
      </div>
    </div>
<br/><br/>
	<form align="center" action="" method="POST">
  <label >Recuperación de clave vía correo electronico</label><br/>
		<input placeholder="Numero de identificación" type="text" name="usuario">
		<input type="submit" name="recuperar" value="Recuperar">
	</form>
</body>
</html>
<?php
@session_start();
if (isset($_POST['usuario'])){
  require '../comun/conexion.php';
 $sql='select correo_docente  as correo,clave from docente where id_docente ="'.$_POST['usuario'].'" union SELECT  `correo` ,  `clave` 
FROM estudiante
WHERE  `identificacion`='.$_POST['usuario'].'  ';
$recuepracontra = $mysqli ->query ($sql);
if ($rowcontra = $recuepracontra ->fetch_Assoc()){
	$mensaje = $rowcontra['clave'];
	 $correo_destino = $rowcontra['correo'];

#require_once("phpmailer.class.php"); 
#echo $correo_remitente ="estudiantehst@gmail.com"; 
#echo $correo_destino; 
 $asunto="Recuperación de de contraseña aplicativo de cursos Pasto Salud"; 
#$email = new phpmailer_smtp_class("pqr@corregiduriasanfernando.org.co",'sanfer*corre${2016:junio}',"mail.corregiduriasanfernando.org.co","26");
#$email->enviar_correo($correo_remitente,$correo_destino,$asunto,$mensaje)
$resultado = mail($correo_destino, $asunto, $mensaje);
if ($resultado ==true){ ?>
  <div align="center">
  <?php
  echo "Su contraseña se ha enviado al correo electronico ".$correo_destino ;
  ?>
  </div>
  <?php
}
else{ ?>
   <div align="center">
  <?php
  echo " Por favor verifique su información o contacte al administrador" ;
  ?>
  </div>
  <?php
}
}
else {
  ?>
   <div align="center">
  <?php
  echo " Por favor verifique su información o contacte al administrador" ;
  ?>
  </div>
  <?php

}
}
 $contenido = ob_get_contents();
ob_clean();
require ("../comun/plantilla.php");
 ?>
