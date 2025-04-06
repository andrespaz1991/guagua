<?php @session_start();require '../../comun/config.php';ob_start();?>
    <div style="margin-left auto;margin-right:auto ;" id="main">
      <div class="col-md-4"></div>
    <div class="container" role="main">
      <div class="col-md-4">
          <div class="panel panel-default">
            <div class="panel-heading"> Restaurar contrase&ntilde;a vía correo electrónico </div>
            <div style="height:150px!important;" class="panel-body">
              <div class="form-group">
  <form action="" method="post">
      <label for="email"> Escribe tu número de identificación o usuario  </label>
<input placeholder="ejemplo: 1085290345 o handres" type="text" id="email" class="form-control" name="email" required>
              </div>
              <div class="form-group">
<button  type="submit" class="btn btn-primary" >Recuperar contrase&ntilde;a</button>
        </form>
              </div>
            </div>
          </div>

           </div>
    </div> 
<?php
if(isset($_POST['email'])){
 require_once '../../comun/funciones.php'; 
 recuperar_contraseña($_POST['email']);
}
$contenido = ob_get_clean();
require ("../../comun/plantilla.php");
?>