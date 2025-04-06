<?php 
ob_start();
?>
<div align="center">
<label>Usuario</label>
	<input class="form form-control" type ="text"  value="apaz" name ="user"/><br/>
	<label>Clave</label>
<input class="form form-control" value="tesla" type ="password" name ="pass"/><br/>
<input class="btn btn-success"  type ="submit" name ="entrar" value ="entrar"/>
</form>
<?php
if (isset($_POST['user'])){
if (($_POST['user']=="apaz") and ($_POST['pass']=="tesla")) {

session_start();
 $_SESSION['user'] = 'andres';
header('location:index.php');
															}
											else { 
echo "No sea chismoso(a) :D";
											}

		}
?>
<div>
<?php

$contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
?>
