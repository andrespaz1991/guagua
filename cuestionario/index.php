<?php
ob_start();
@session_start();
if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){
}else{
echo "No tiene permisos para ingresar a esta sección";
$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
exit();
}
$_SESSION['modulo']="cuestionario";
$_SESSION['barra_busqueda']= "cuestionarios";
if (!isset($_COOKIE['numeroresultados_cuesionario'])) setcookie("numeroresultados_cuesionario",16);
require((dirname(__FILE__))."/../comun/conexion.php");
require_once("../comun/funciones.php");
if (isset($_POST['del'])){
//Instrucción SQL que permite eliminar en la BD
$sql = 'DELETE FROM cuestionario WHERE id="'.$_POST['del'].'"';
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($eliminar = $mysqli->query($sql)){
//Validamos si el registro fue eliminado con éxito
echo 'Registro eliminado';
echo '<meta http-equiv="refresh" content="1; url=cuestionario.php" />';
}else{
echo 'Eliminación fallida, por favor compruebe que la usuario no esté en uso';
echo '<meta http-equiv="refresh" content="2; url=cuestionario.php" />';
}
}
?>
<title>Cuestionarios</title>
<section>
<center>
<br>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">Cuestionarios</h1>
    <?php if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){ ?>
  <br>
  <button style="float:right;margin-top:-150px" class="btn btn-warning" onclick="nuevocuestionario();">Nuevo</button>
  <!--input style="float:right;margin-top:-150px" class="btn btn-warning" id="opciones_cuestionarios" type="button" value="Opciones"  class="context-menu-one btn btn-neutral" name=""/-->
   <?php } ?>
  </div>
</div>
<div style="background-color:#894094;width:97%;height:5px;align:center"></div>
</center>
<?php
if (isset($_POST['submit'])){
switch ($_POST['submit']){
case "Registrar":
//recibo los campos del formulario proveniente con el método POST
$sql = "INSERT INTO cuestionario (`id`, `nombre`, `descripcion`, `fecha`, `tipo_cuestionario`, `usuario`) VALUES ('".$_POST['id']."', '".$_POST['nombre']."', '".$_POST['descripcion']."', '".$_POST['fecha']."', '".$_POST['tipo_cuestionario']."', '".$_POST['usuario']."')";
//echo $sql;
if ($insertar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Registro exitoso';
echo '<meta http-equiv="refresh" content="1; url=cuestionario.php" />';
}else{
echo 'Registro fallido';
echo '<meta http-equiv="refresh" content="1; url=cuestionario.php" />';
}
break;
case "Nuevo":
echo '<form id="form1" name="form1" method="post" action="cuestionario.php">
<h1>Registrar</h1>';
echo '<p><input name="id" type="hidden" id="id"  maxlength="11" value=""></p>';
echo '<p><label for="nombre">Nombre:</label><input name="nombre" type="text"  maxlength="255" id="nombre"  maxlength="255" value="" required></p>';
echo '<p><label for="descripcion">Descripción:</label></p><p><textarea name="descripcion" cols="60" rows="10"id="descripcion"  required></textarea></p>';
echo '<p><label for="fecha">Fecha:</label><input name="fecha" type="date" id="fecha"  maxlength="0" value="" required></p>';

$sql= "SELECT `id`, `nombre` FROM `tipo_cuestionario` ;";
echo '<p><label for="tipo_cuestionario">Tipo de cuestionario:</label><select name="tipo_cuestionario" id="tipo_cuestionario"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo '<option value="'.$row['id'].'">'.$row['nombre'].'</option>';
}
echo '</select></p>';

$sql= "SELECT usuario,usuario FROM usuario;";
echo '<p><label for="usuario">Usuario:</label><select name="usuario" id="usuario"  required>';
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta = $mysqli->query($sql);
while($row=$consulta->fetch_assoc()){
echo '<option value="'.$row['usuario'].'">'.$row['usuario'].'</option>';
}
echo '</select></p>';
echo '<a href="cuestionario.php">Regresar</a>';
echo '<p><input type="submit" name="submit" id="submit" value="Registrar"></p>
</form>';
break;
case "Actualizar":
//recibo los campos del formulario proveniente con el método POST
$cod = $_POST['cod'];
//Instrucción SQL que permite insertar en la BD sig_tipo_documento`, `nom_tipo_documento
$sql = "UPDATE cuestionario SET id='".$_POST['id']."', nombre='".$_POST['nombre']."', descripcion='".$_POST['descripcion']."', fecha='".$_POST['fecha']."', tipo_cuestionario='".$_POST['tipo_cuestionario']."', usuario='".$_POST['usuario']."'WHERE  id = '".$cod."';";
//echo $sql;
//Se conecta a la BD y luego ejecuta la instrucción SQL
if ($actualizar = $mysqli->query($sql)) {
//Validamos si el registro fue ingresado con éxito
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=cuestionario.php" />';
}else{
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=cuestionario.php" />';
break;
case "Modificar":
$sql = "SELECT `id`, `nombre`, `descripcion`, `fecha`, `tipo_cuestionario`, `usuario` FROM `cuestionario` WHERE id ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
//echo $sql;
if($row=$consulta->fetch_assoc())
{
echo '<form id="form1" name="form1" method="post" action="cuestionario.php">
<h1>Modificar '.$row['id'].'</h1>';
echo '<p><label for="cod">Id:</label><input name="cod" type="hidden" id="cod" value="'.$row['id'].'" size="120" required></p>';
echo '<p><label for="id">id:</label><input name="id" type="hidden" id="id"  maxlength="11" value="'.$row['id'].'"></p>';
echo '<p><label for="nombre">Nombre:</label><input name="nombre" type="text"  maxlength="255" id="nombre"  maxlength="255" value="'.$row['nombre'].'" required></p>';
echo '<p><label for="descripcion">Descripción:</label></p><p><textarea name="descripcion" cols="60" rows="10"id="descripcion"  required>'.$row['descripcion'].'</textarea></p>';
echo '<p><label for="fecha">Fecha:</label><input name="fecha" type="date" id="fecha"  maxlength="0" value="'.$row['fecha'].'" required></p>';
echo '<p><label for="tipo_cuestionario">Tipo de cuestionario:</label><input name="tipo_cuestionario" type="llave_foranea" id="tipo_cuestionario"  maxlength="11" value="'.$row['tipo_cuestionario'].'" required></p>';
echo '<p><label for="usuario">Usuario:</label><input name="usuario" type="llave_foranea" id="usuario"  maxlength="255" value="'.$row['usuario'].'" required></p>';
echo '<a href="cuestionario.php">Regresar</a>
<p><input type="submit" name="submit" id="submit" value="Actualizar"></p>
</form>';
}
break;
default:
echo "Ingreso erroneo";
}//fin switch
}else{
?>
<center>
<!--p><a class="btn btn-primary" id="btn_nuevo_cuestionario" onclick="nuevocuestionario()">Nuevo Cuestionario</a></p-->
<label for="id_cuestionario">Seleccione un cuestionario</label>
<input type="hidden" name="id_cuestionario" id="id_cuestionario" value=""></input><br>
<?php /*
<b><label>Buscar: </label></b><input type="text" placeholder="Ejemplo: Taller" id="txt_buscar_cuestionario" onkeyup ="buscar_cuestionario_pag();"  style="margin: 15px;" value="">
<input type="number" min="0" max="16" id="numeroresultados_cuesionario" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="<?php if (isset($_COOKIE['numeroresultados_cuesionario'])) echo $_COOKIE['numeroresultados_cuesionario']; else echo "8" ?>" onkeyup="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" mousewheel="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" onchange="grabarcookie('numeroresultados_cuesionario',this.value);buscar_cuestionario_pag();" size="4" style="width: 40px;"></p>
*/ ?>
<!--p><a onclick="buscar_cuestionario_pag();"><span style="width:80px;height:80px" class="icon-sga-list-15"></span></a></p><br><br-->
<center>
<span id="txtbuscar_cuestionario">
<?php
#buscar_cuestionario("","cuestinario","buscar_cuestionario_pag();","buscar_cuestionario_pag");
?>
</span>
<script>buscar_cuestionario_pag();</script>
<?php
}//fin else if isset cod
?>
</section>
<script>
$(function(){
    function createSomeMenu() {
        return {
            callback: function(key, options) {
            if(key=="Nuevo Cuestionario"){
              nuevocuestionario();
          }
           //     var m = "clicked: " + key;
             //   window.console && console.log(m) || alert(m);
            },
            items: {
          <?php if(isset($_SESSION['rol']) and ($_SESSION['rol']=='admin' or $_SESSION['rol']=='docente')){?>
                "Nuevo Cuestionario": {name: "Nuevo Cuestionario"},
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
include ("../comun/plantilla.php");
?>