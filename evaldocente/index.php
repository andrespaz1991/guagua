<section>
	<br>
	<p>
		<h1>Bienvenido a Evaluación Docente</h1>
	</p>
		<br>
	<form method = "POST" action = "php/login.php" >
	<label for="">Usuario</label>
	<input type = "text" name ="user"/><br/>
	<label for="">Clave</label>
	<input type = "password" name="pass"/><br/>
	<input type = "submit" value="Ingresar"/>
	</form>
</section>
<?php
# $contenido = ob_get_contents();
#ob_clean();
#include ("plantilla.php");
?>
