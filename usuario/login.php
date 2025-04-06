<?php
ob_start();
@session_destroy();
@session_unset();
#require_once($_SERVER['DOCUMENT_ROOT']."/comun/autoload.php");
require_once("../comun/autoload.php");
$institucion=new Institucion();
$instutuciones=$institucion->datos_institucion(true);
?>
<div class="container-fluid bg-3 text-center" style="background-image: url('../comun/img/fondo_login.jpg');">    
 <div class="row">
		<form id="form_login" action="" style="width:50%;align:center;margin-left:25%;" method = "POST" >
<div align="center" style="margin:0 auto">

<div id="usuarios_recordados">
<span>
<div id="" class="estilos_fotos" onclick="elegir_cuenta(this.id);">
<button title="Dejar de recordarme" type="button" style="margin-bottom: -25px;z-index: 1;" class="close" onclick="olvidar_usuario('')">&times;</button>
<img  id="foto_" title="Cambiar cuenta" onclick="cambiar_cuenta()" height="120px" src="<?php echo SGA_MEDIA_FOTO ?>/user-icon.png">
<div><label style="margin-top:15px;font-size: 17px;"></label></div>
</div>
</span>
</div></div>
    <span id="imgs_login" style="display:none">
	<p><h1 class="Abckids" id="nombre_usuario">Usuario</h1></p>
	    <input id="btn_submit" height="90px" type="submit" hidden>
<label for="btn_submit"><div class="estilos_fotos">
<img  height="120px"  id="imgusuario"  src="<?php echo SGA_MEDIA_FOTO ?>/user-icon.png">
</div>
</label>
</span>
<br/>
<label id="ingresare">Ingresaré como&nbsp;</label><label id="un_rol" style="display:none"></label>
<select onchange="" id="rol"  name="rol" ></select>
<br/>
<label id="lb_institucion"  for="">Institución</label>
<select id="institucion" name="institucion">
<?php 
foreach ( $instutuciones as $key => $value) 
  {  ?>
 <option value="<?php echo $value['id_institucion_educativa'] ?>">
 <?php echo COMUN::puntos_suspensivos($value['nombre_institucion'],20) ?></option>   
<?php  } ?>
</select><br>

<label id="user"  for="">Usuario</label>
	<input value="1085290375" autofocus onfocus="datosparalogin(this.value);" onblur="datosparalogin(this.value);" onfocus="datosparalogin(this.value);"  onkeyup="datosparalogin(this.value);"  onchange="datosparalogin(this.value);" placeholder="Ingresa Usuario"  type = "text" name ="usuario" value="" id ="usuario"/><br/>
<div id="mascotas" style="display:flex-contaniner">
</div>
	<label id="labelclave" for="">Contraseña</label>
	<input  value="admin"  required placeholder="Ingresa Clave" type = "password" name="clave" id="clave"/>
    <br>

    <label><input checked type="checkbox" value="SI" name="recordarme">Recordarme</label>
<a href="recuperar/recuperar_cuenta.php"><img onclick="window.location='../index.php'" style="position:absolute;margin-top:-30%;margin-left:-30%;display:none;" width="120px" height="120px" id="" src="">Recuperar Contraseña</img></a>
    <br><button id="ingresar" type="submit" class="btn btn-success btn-md">Ingresar</button>
    </form>
</div>
</div><br>
</center>
<?php $contenido = ob_get_contents();
ob_clean();
require ("../comun/plantilla.php");
 ?>
