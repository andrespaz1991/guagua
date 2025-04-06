<?php @session_start();require '../../comun/config.php';
require '../../comun/funciones.php'; ob_start();
#$token='55c33ee6bd167c33ab7dafc90963e11d7a3a93bb';
$token = $_GET['token'];
list($bandera,$nombre,$usuario) = confirmar_token($token);
if($bandera==1){ ?>
  <div style="margin-left auto;margin-right:auto ;" id="main">
      <div class="col-md-4"></div>
    <div class="container" role="main">
      <div class="col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading"> Restaurando contrase&ntilde;a de <?php echo $nombre ; ?> </div>
            <div style="height:150px!important;" class="panel-body">
              <div class="form-group">
  <form action="" method="post">
      <label for="clave"> Escribe tu nueva contraseña  </label>
<input placeholder="ejemplo: @dm1nistr4d0r" type="text" id="clave" class="form-control" name="clave" required>
<input type="hidden" id="usuario" class="form-control" name="usuario" value="<?php echo $usuario; ?>">

      <label for="clave"> Confirma tu nueva contraseña  </label>
<input placeholder="ejemplo: @dm1nistr4d0r" type="text" id="confirm_clave" class="form-control" name="confirm_clave" required>
              </div>
              <div class="form-group">
                <input  type="submit" class="btn btn-primary" value="Actualizar contrase&ntilde;a" />
        </form>
              </div>
            </div>
          </div>
           </div>
    </div> 
<?php
}
if(isset($_POST['clave'])){
if($_POST['confirm_clave']==$_POST['clave']) $actualizar ='UPDATE `usuario` SET `clave`= sha1("'.$_POST['clave'].'"),`mascota`="NO" WHERE id_usuario="'.$_POST['usuario'].'"';
actualizar_datos($actualizar);
}
$contenido = ob_get_clean();
require ("../../comun/plantilla.php");
?>
