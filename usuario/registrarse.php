<?php ob_start(); @session_start();
require("../comun/conexion.php");
require_once("../comun/funciones.php");
?>
 <div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip"><?php echo strtoupper('Registrarse'); ?> </h1>      
  </div>
</div>
<?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
$sql = "INSERT INTO usuario (`id_usuario`, `usuario`, `clave`, `nombre`, `apellido`,`tipo_sangre`, `rol`, `foto`) VALUES ('".$_POST['id_usuario']."', '".$_POST['usuario']."', '".encriptar($_POST['clave'])."', '".$_POST['nombre']."', '".$_POST['apellido']."', '".$_POST['tipo_sangre']."', '".implode(",",$_POST['rol'])."', '')";
$insertar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
$id_usuario = $_POST['id_usuario'];
$partes_nombre = explode (".",$_FILES['foto']['name']);
$ext = end($partes_nombre);
if (copy($_FILES['foto']['tmp_name'],READFILE_SERVER."foto/".$id_usuario.".".$ext))
$sql_foto = "UPDATE usuario SET foto='".$id_usuario.".".$ext."'WHERE  id_usuario = '".$id_usuario."'";
if ($insertar = $mysqli->query($sql_foto)){
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
<script>alert2('Registro exitoso');</script>
<meta http-equiv="refresh" content="3; url=login.php" />
<?php 
 }else{
 ?>
<script>alert2('Registro fallido');</script>
<meta http-equiv="refresh" content="3; url=login.php" />
<?php 
}
}
} /*fin Registrar*/ 
}
$textoh1 ="Registrarse";
$textobtn ="Registrar";
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="registrarse.php<?php if(isset($_GET['u'])) echo "?u=".$_GET['u'] ?>" enctype="multipart/form-data">
<div class="form-group">
 <label for="id_usuario">Identificación:</label><span style="float:right" id="txteid_usuario"></span>
 <input class="form-control" name="id_usuario" type="number" min="0" id="id_usuario" value="<?php if (isset($row['id_usuario'])) echo $row['id_usuario'];?>" autocomplete="off" onchange="valida_existe_id_usuario(this.value);" required>
 </div>
<div class="form-group">
 <label for="usuario">Usuario:</label>
 <span style="float:right" id="txteusuario"></span>
 <input class="form-control" name="usuario" type="text" id="usuario" value="" required autocomplete="off" onchange="valida_existe_nombre_usuario(this.value);">
 </div>
<div class="form-group">
 <label for="clave">Clave:</label>
 <input class="form-control" name="clave" type="password" id="clave" value="" required>
 <label>
 <input type="checkbox" onclick="document.getElementById('clave').type = document.getElementById('clave').type == 'text' ? 'password' : 'text'"/>Ver</label>
 </div>
<div class="form-group">
 <label for="nombre">Nombre:</label>
 <input class="form-control"name="nombre"type="text" id="nombre" value="" required >
</div>
<div class="form-group">
 <label for="apellido">Apellido:</label>
 <input class="form-control" name="apellido" type="text" id="apellido" value="" required >
</div>
<div class="form-group">
<label for="tipo_sangre">Tipo de Sangre:</label>
<select class="form-control" name="tipo_sangre" type="text" id="tipo_sangre">
<?php
$pre="";
tipos_sangre($pre); ?>
</select>
</div>
<div class="form-group">
<p><label for="rol">Rol:</label></p>
<?php 
$autorregistro_roles = autorregistro();
foreach ($autorregistro_roles as $rol){ ?>
<label><input type="checkbox" class="" name="rol[]" id="rol_$rol" value="<?php echo $rol ?>" <?php if (isset($row['rol']) and $row['rol'] ==$rol) echo " checked "; ?> >&nbsp;<?php echo $array_roles[$rol]; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;
<?php } ?>
</div>
<div class="form-group"><label for="foto">Foto Usuario:</label>
<p><img id="img_foto" height="80" src=""></p>
<span id="span_img_foto">
<?php if (isset($row['foto']) and $row['foto']!=""){
?>
<input style="display:none" class="form-control" name="mifoto" type="text" id="mifoto" value="<?php echo $row['foto']?>" required>
<br><input onclick="document.getElementById('span_img_foto').innerHTML='<input style=\'width: 90%;display: inline;\' onchange=\'mostrarImagen(this);\' name=\'foto\' type=\'file\' id=\'foto\' required ><button class=\'btn-danger badge\' style=\'background-color:#d9534f;\' onclick=\'sinfoto();\' type=\'button\' title=\'Sin Foto\' class=\'close\'>-</button>'" type="button" value="Cambiar Foto">
<?php
}else{
?><input style="width: 90%;display: inline;" onchange="ValidarArchivo(this);validar_resolución(this);mostrarImagen(this);" class="form-control"name="foto"type="file" id="foto" required><?php
}
?>
<button class="btn-danger badge" style="background-color:#d9534f;" onclick="sinfoto();" type="button" title="Sin Foto" class="close">-</button></span>
</p>
<p><button class="btn btn-success" type="submit" name="submit" id="submit"><?php
echo $textobtn?></button></p>
</form><div class="col-md-3"></div>
</center>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
