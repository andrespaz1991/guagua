<?php 
@session_start();
ob_start();
?>
<?php
@session_start();
$_SESSION['modulo']="foros";
$_SESSION['barra_busqueda'] = "foros";
require_once("../comun/funciones.php");
require_once("../comun/config.php");
?>
<center>
<div class="jumbotron">
<h1 class="fip" id="titulo"><?php echo "Foros"; #deletrear("Foros"); ?></h1>
<?php if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){ ?>
  <br><input style="float:right;margin-top:-150px" id="opciones_foros" type="button" value="Opciones"  class="context-menu-one btn btn-warning" name=""/>
   <?php } ?>
<span style="display:none">
<?php boton_modal_nuevo_foro(); ?>
</span>
<?php ventana_modal_nuevo_foro(); ?>
<?php ventana_modal_elegir_icono(); ?>
<?php ventana_modal_nuevo_icono('id="form_guardar_icono" method="post" class="form_ajax" resp_1="Icono creado correctamente" resp_0="icono no creado, revise su información e intentete nuevo" action="?guardar_icono" callback_1="document.getElementById(\'cerrar_modal_nuevo_icono\').click();" callback_0="false" callback="buscar_iconos();"'); ?>
</div>
<div style="background-color:#337ab7;width:97%;height:5px;align:center"></div>
</center>
<div style="margin-top:25px;background-color:#ddf1ff;border-radius: 20px;    box-shadow: #6E9BAE 2px 2px 4px 2px;" class="row">
<div class="col-md-3"><!--#6E9BAE-->
<div style="background-color:white;margin-top:25px;text-align:center;border:solid 3px #E5E7E9;border-radius: 20px;box-shadow: #6E9BAE 4px 4px 18px 3px;">
<h2 id="titulo" style="font-size:16;"><?php echo deletrear("Mis Foros"); ?></h2>
<input type="hidden" id="contexto_foro" value="general">
<span id="mis_foros">
<?php
mis_foros('','general');
?>
</span>
<hr>
</div>
<div style="background-color:white;border:solid 3px #E5E7E9; margin-top:25px;border-radius: 20px;    box-shadow: #6E9BAE 4px 4px 18px 3px;">
<h2 id="titulo" style="font-size:16;text-align:center"><?php echo deletrear("Temas Destacados");?></h2>
<span id="mitop5"><?php #mitop5();?></span>
</div>
<br>
</div><!-- fin </div> col3 -->
<div style="margin-top:25px;
margin-lef:5px;text-align:center;border:solid 3px black opacity: 0.5;" class="col-md-9">
<span id="txt_entradas">
<?php /*
*/ ?>
<script>listar_entradas(1);</script>
</span>
<span id="txt_comentarios"></span>
</div>
</div>
<div class="row">
<div class="col-md-9">
<div class="col-md-12">
<?php #insertar_foro(1,"","","","Foro Institucional"); ?>
</div>
</div>
<div class="col-md-12">
<?php #insertar_foro(1,"","","","Foro Institucional"); ?>
</div>
<div class="col-md-12">
<?php #insertar_foro(2,"","","","Foro Directivos y Docentes"); ?>
</div><div class="col-md-12">
<?php #insertar_foro(3,"","","","Foro Acudientes"); ?>
</div>
</div>

<script>
$(function(){
    function createSomeMenu() {
        return {
            callback: function(key, options) {
                    if(key=="Nuevo Foro"){
              document.getElementById('btn_nuevo_foro').click();
          }
           //     var m = "clicked: " + key;
             //   window.console && console.log(m) || alert(m);
            },
            items: {
          <?php if($_SESSION['rol']=='admin'){ ?>
                "Nuevo Foro": {name: "Nuevo Foro"},
        <?php } ?>
            }
        };
    }

    // some asynchronous click handler
    $('.context-menu-one').on('mouseup', function(e){
        var $this = $(this);
        // store a callback on the trigger
        $this.data('runCallbackThingie', createSomeMenu);
        var _offset = $this.offset(),
            position = {
                x: _offset.left + 5, 
                y: _offset.top + 5
            }
        // open the contextMenu asynchronously
        setTimeout(function(){ $this.contextMenu(position); }, 1000);
    });

    // setup context menu
    $.contextMenu({
        selector: '.context-menu-one',
        trigger: 'none',
        build: function($trigger, e) {
            e.preventDefault();

            // pull a callback from the trigger
            return $trigger.data('runCallbackThingie')();
        }
    });
});
</script>
<?php $contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>
