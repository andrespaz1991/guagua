<?php
ob_start();
@session_start();
$_SESSION['barra_busqueda'] = "mensajes";
require_once("../comun/config.php");
require("../comun/conexion.php");
require_once("../comun/funciones.php");
?>
<style>
<?php
$cant = 7;//cantidad de pestañas
for ($i=1;$i<=$cant;$i++){
echo '#container_act input#tab-'.$i.':checked ~ #content #content-'.$i.'';
if ($i!=$cant) echo ',';
}

?>{
 opacity: 1;
 z-index: 100;
}
</style>
<?php
$_GET['id_usuario']="1";
$_GET['nombre']="Administrador SGA";
include('modal_nuevo_mensaje.php'); ?>
<div id="container_act" class="colorear">
<!--Pestaña 1 activa por defecto-->
  <input id="tab-1" onclick="elegir_fn_pestana_mens(this);" funcion="buscar_mensaje();" type="radio" name="tab-group" checked="checked" />
  <label for="tab-1"><span class="glyphicon glyphicon-envelope"></span><span class="tab_text">&nbsp;Mis Mensajes</span></label>
  <!--Pestaña 2 inactiva por defecto-->
  <input id="tab-2" onclick="elegir_fn_pestana_mens(this);" funcion="bandeja_salida();" type="radio" name="tab-group" />
  <label for="tab-2"><span class="glyphicon glyphicon-send"></span><span class="tab_text">&nbsp;Mensajes Enviados</span></label>
  <!--Pestaña 3 inactiva por defecto-->
  <input id="tab-3" onclick="elegir_fn_pestana_mens(this);" funcion="buscar_mensaje_favoritos();" type="radio" name="tab-group" />
  <label for="tab-3"><span class="glyphicon glyphicon-star"></span><span class="tab_text">&nbsp;Favoritos</span></label>
  <?php if(isset($_SESSION['rol']) and $_SESSION['rol']=="admin"){ ?>
  <!--Pestaña 4 inactiva por defecto-->
  <input id="tab-4" onclick="elegir_fn_pestana_mens(this);" funcion="buscar_mensaje_denuncias();" type="radio" name="tab-group" />
  <label for="tab-4"><span class="glyphicon glyphicon-warning-sign"></span><span class="tab_text">&nbsp;Denuncias</label>
  <?php } ?>
<!--Pestaña 5 inactiva por defecto-->
  <input id="tab-5" onclick="elegir_fn_pestana_mens(this);" funcion="buscar_mensaje_suscripciones();" type="radio" name="tab-group" />
  <label for="tab-5"><span class="glyphicon glyphicon-comment"></span><span class="tab_text">&nbsp;Suscripciones</label>
<!--Pestaña 6 inactiva por defecto-->
  <input id="tab-6" onclick="elegir_fn_pestana_mens(this);" funcion="buscar_mensaje_papelera();" type="radio" name="tab-group" />
  <label for="tab-6"><span class="glyphicon glyphicon-trash"></span><span class="tab_text">&nbsp;Papelera</label>
  
<div id="content"><span data-toggle="modal" data-target="#myModal_nuevo_mensaje" title="Nuevo Mensaje" class="icon-sga-add-1"></span>
    <div id="content-1">
    <span id="span_buscar_mensaje">
        <?php buscar_mensajes(); ?>
        </span>
        <hr>
    <span id="span_leer_mensaje"></span>
    </div>
    <div id="content-2">
    <span id="txt_bandeja_salida">
    <?php bandeja_salida();?>
    </span>
    <hr>
    <span id="span_leer_mensaje_enviado"></span>
    </div>
    <div id="content-3">
     <span id="span_buscar_favoritos">
        <?php buscar_mensajes("","favoritos"); ?>
    </span>
        <hr>
    <span id="span_leer_mensaje_favoritos"></span>
    </div>
    <?php if(isset($_SESSION['rol']) and $_SESSION['rol']=="admin"){ ?>
    <div id="content-4">
    <span id="span_buscar_denuncias">
        <?php buscar_mensajes("","denuncias"); ?>
    </span>
    <span id="span_leer_mensaje_span_buscar_denuncias"></span>
    </div>
    <?php } ?>
    <div id="content-5">
    <span id="span_buscar_suscripciones">
        <?php buscar_mensajes("","suscripciones"); ?>
    </span>
    <span id="span_leer_mensaje_suscripciones"></span>
    </div>
    <div id="content-6">
    <span id="span_buscar_papelera">
        <?php buscar_mensajes("","papelera"); ?>
    </span>
    <span id="span_leer_mensaje_papelera"></span>
    </div>
    </div>
</div><!--div id="content"-->
</div><!--div id="container_act"-->
<link rel="stylesheet" href="estilo_tabla2.css">
<?php $contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>