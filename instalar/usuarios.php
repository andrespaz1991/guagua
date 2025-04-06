<?php
#printf("<pre>%s</pre>",print_r($_POST,true));
@session_start();
if (isset($_GET['logout'])){
unset($_SESSION['host']);
unset($_SESSION['user']);
unset($_SESSION['password']);
session_destroy();
?>
<meta http-equiv="refresh" content="0; url=usuarios.php" />
<?php
exit();
}

function conexion($servidorbd,$usuariobd,$passwordbd,$basededatos=""){
if ($basededatos!="") $mysqli = new mysqli ($servidorbd,$usuariobd,$passwordbd,$basededatos);
else $mysqli = new mysqli ($servidorbd,$usuariobd,$passwordbd);
if (mysqli_connect_errno()){
#echo "error".mysqli_connect_errno();
echo "Error de conexión con al base de datos";
?>
<meta http-equiv="refresh" content="0; url=?logout" />
<?php
exit();
}else{
if($mysqli){
mysqli_set_charset($mysqli,'utf8');
}
}
return $mysqli;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Usuarios Base de Datos</title>
	<link rel="stylesheet" href="../comun/css/jquery.mobile-1.4.5.min.css">
	<link rel="stylesheet" href="../comun/css/bootstrap.min.css">
    <link rel="stylesheet" href="../comun/css/estilo_tabla.css">
	<link rel="shortcut icon" href="../comun/img/logo.png" type="image/x-icon" />
	<script src="../comun/js/funciones.js"></script>
	<script src="../comun/js/jquery-2.2.4.min.js"></script>
	<script src="../comun/js/bootstrap.min.js"></script>
</head>
<body>
<div class="wrapper container">
<div class="page-header">
<a href="?logout">Salir</a>
<br>
<?php
 ini_set('date.timezone', 'America/Bogota');
 date_default_timezone_set('America/Bogota');
if (isset($_POST['loginusuario'],$_POST['loginclave'],$_POST['Host'])){
$usuarion = $_POST['loginusuario'];
$claven = $_POST['loginclave'];
$host = $_POST['Host'];
$mysqli = conexion($servidorbd,$usuarion,$claven);
$sql = "SELECT * FROM `mysql`.`user` WHERE `Host` = '$host' and `User` = '$usuarion' and `Password`= PASSWORD('$claven');";
echo $sql."<br>";
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
$_SESSION['host'] = $row['Host'];
$_SESSION['user'] = $row['User'];
$_SESSION['password'] = $claven;
}
header("Location: ".$_SERVER['PHP_SELF']);
}
if (!isset($_SESSION['user'])){
?>
 <h1>Login</h1>
<form method="post">
    <div class="form-group"><label for="Host">Host<input type="text" class="form-control" name="Host" id="Host" value="localhost"></label>    </div>
    <div class="form-group"><label for="loginusuario">Usuario<input type="text" class="form-control" name="loginusuario" id="loginusuario" value="root"></label></div>
    <div class="form-group"><label for="loginclave">Clave<input type="password" class="form-control" name="loginclave" id="loginclave"></label></div>
    <div class="form-group"><label for="enviar"><input type="submit" class="form-control btn btn-primary" id="enviar" value="Ingresar"></label></div>
</form>
<?php
    exit();
}
if (isset($_SESSION['user'])){
    echo "Sesión Iniciada como ".$_SESSION['user'];
$mysqli = conexion($_SESSION['host'],$_SESSION['user'],$_SESSION['password']);
?>
<?php
}
if (isset($_POST['usuario'],$_POST['clave'])){
$usuarion = $_POST['usuario'];
$claven = $_POST['clave'];
$sql = "CREATE USER '$usuarion'@'".$_SESSION['host']."' IDENTIFIED BY '$claven';";
#echo $sql."<br>";
$consulta = $mysqli->query($sql);
}
if (isset($_POST['usuario'],$_POST['nuevaclave'])){
$usuarion = $_POST['usuario'];
$nuevaclave = $_POST['nuevaclave'];
$sql = "use mysql;
update user set password=password('$nuevaclave') where user = '$usuarion'; 
FLUSH PRIVILEGES;";
#echo $sql."<br>";
$consulta = $mysqli->multi_query($sql);
}

if (isset($_POST['basededatos'])){
$dasededatosn = $_POST['basededatos'];
$sql="CREATE DATABASE IF NOT EXISTS $dasededatosn;";
#echo $sql."<br>";
$consulta = $mysqli->query($sql);
}
if (isset($_POST['permisos'],$_POST['permisos'])){
$usuarion = $_POST['usuario'];
$dasededatosn = $_POST['basededatos'];
$permisosn = $_POST['permisos'];
foreach ($permisosn as $is => $permiso){
$sql = "GRANT $permiso ON $dasededatosn.* TO '$usuarion'@'".$_SESSION['host']."';";
#echo $sql."<br>";
$consulta = $mysqli->query($sql);
}
$sql = "FLUSH PRIVILEGES;";
#echo $sql."<br>";
$consulta = $mysqli->query($sql);
}
?>
   
    <h1>Crear Usuario</h1>
<form method="post">
    <div class="form-group"><label for="usuario">Usuario<input type="text" class="form-control" name="usuario" id="usuario"></label></div>
    <div class="form-group"><label for="Clave">Clave<input type="password" class="form-control" name="clave" id="Clave"></label></div>
    <div class="form-group"><label for="enviar"><button type="submit" class="form-control btn btn-primary" id="enviar" >Crear</button></label></div>
</form>
<h1>Crear Base de datos</h1>
<form method="post">
    <div class="form-group"><label for="basededatos">Base de datos<input type="text" class="form-control" name="basededatos" id="basededatos"></label></div>
    <div class="form-group"><label for="enviar"><input type="submit" class="form-control btn btn-primary" id="enviar" value="Crear"></label></div>
</form>
<h1>Asignar Privilegios</h1>
<form method="post">
    <div class="form-group"><label for="usuario">Usuario
        <?php
        $sql = "select distinct(User) from mysql.user";
        $consulta = $mysqli->query($sql);
        ?>
        <select class="form-control" name="usuario" id="usuario">
        <?php 
        while($row=$consulta->fetch_assoc()){
         ?>
         <option><?php echo $row['User']; ?></option>
        <?php } ?>
        </select></label>
    </div>
    <div class="form-group">
        <label for="basededatos">Base de datos
        <?php
        $sql = "show databases";
        $consulta = $mysqli->query($sql);
        ?>
        <select class="form-control" name="basededatos" id="basededatos">
        <?php 
        while($row=$consulta->fetch_assoc()){
         ?>
         <option><?php echo $row['Database']; ?></option>
        <?php } ?>
        </select></label>
    </div>
    <div class="form-group">
        <label for="permisos">Permisos (Para seleccionar varios mantenga presiona la tecla Ctrl
        <select style="width:300px;height:300px" class="form-control" name="permisos[]" multiple id="permisos">
            <option title="Esto permite a un usuario de MySQL acceder a todas las bases de datos asignadas en el sistema.">ALL PRIVILEGES</option>
            <option title="Permite crear nuevas tablas o bases de datos.">CREATE</option>
            <option title="Permite eliminar tablas o bases de datos.">DROP</option>
            <option title="Permite eliminar registros de tablas.">DELETE</option>
            <option title="Permite insertar registros en tablas.">INSERT</option>
            <option title="Permite leer registros en las tablas.">SELECT</option>
            <option title="Permite actualizar registros seleccionados en tablas.">UPDATE</option>
            <option title="Permite remover privilegios de usuarios.">GRANT OPTION</option>
        </select>
        </label>
    </div>
    <div class="form-group"><label for="enviar"><input type="submit" class="form-control btn btn-primary" id="enviar" value="Crear"></label></div>
</form>
</div>
</div>
</body>
</html><?php
/*

CREATE USER 'nombre_usuario'@'localhost' IDENTIFIED BY 'tu_contrasena';
GRANT ALL PRIVILEGES ON * . * TO 'nombre_usuario'@'localhost';
FLUSH PRIVILEGES;

permiso

ALL PRIVILEGES: como mencionamos previamente esto permite a un usuario de MySQL acceder a todas las bases de datos asignadas en el sistema.
CREATE: permite crear nuevas tablas o bases de datos.
DROP: permite eliminar tablas o bases de datos.
DELETE: permite eliminar registros de tablas.
INSERT: permite insertar registros en tablas.
SELECT: permite leer registros en las tablas.
UPDATE: permite actualizar registros seleccionados en tablas.
GRANT OPTION: permite remover privilegios de usuarios.


GRANT [permiso] ON [nombre de bases de datos].[nombre de tabla] TO '[nombre de usuario]’@'localhost’;

REVOKE [permiso] ON [nombre de base de datos].[nombre de tabla] FROM '[nombre de usuario]'@'localhost';
REVOKE ALL PRIVILEGES ON `andres1`.* FROM 'andres'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON `andres1`.* TO 'andres'@'localhost';
DROP USER ‘usuario_prueba’@‘localhost’;

mysql -u [nombre de usuario]-p

*/