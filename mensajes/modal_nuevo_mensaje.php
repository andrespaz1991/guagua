<div id="myModal_nuevo_mensaje" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><span id="span_nombre_mensaje">Nuevo mensaje</span> <span id="span_favoritos"></span></h4>
      </div>
      <div class="modal-body modal-lg">
      <div method="post" role="form">
          <div class="row">
            <div class="col-lg-8">
              <input type="hidden" id ="id_eve">
              <p>
<div class="form-group">
    <label for="usuario">Usuario:</label>
    <input class="form-control"  required type="text" autocomplete="off" list="list_usuario" onblur="datalist_required('usuario')" class="" name="usuario" id="usuario" required>
</div>
<datalist id="list_usuario">
<?php
require("../comun/conexion.php");
$sql2= "SELECT id_usuario,nombre,apellido FROM usuario;";//admin
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option data-value="'.$row2['id_usuario'].'"';echo '>'.$row2['nombre']." ".$row2['apellido'].'</option>';
if ($row2["id_usuario"]==$row["usuario"]){
echo '<script>var varusuario = document.getElementById("usuario");varusuario.value="'.$row2["usuario"].'"; </script>';
}
}
echo '</datalist><input required type="hidden" name="usuario" id="usuario-hidden" value="';
if (isset($row['usuario'])) echo $row['usuario'];
echo '"></p>';
?><br>
              <div class="form-group">
                  <label>Mensaje:</label>
                  <textarea class="form-control"  id="mensaje" name="mensaje"></textarea>
              </div>
             <span id="txt_resp_mensaje"></span>
              <div class="form-group" align="center">
                  <button onclick="enviar_mensaje(document.getElementById('usuario-hidden').value, document.getElementById('mensaje').value);" class="btn btn-primary">Enviar mensaje</button>
              </div>
          </div>
        </div>
      </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" id="cerrar_modal_mensaje">Cerrar</button>
      </div>
    </div>
    </div>
    </div>
    <script>
<?php
if (isset($_GET['id_usuario'])){
    ?>
    var responder = document.querySelector('#usuario-hidden');
    var respondern = document.querySelector('#usuario');
    responder.value = '<?php echo $_GET['id_usuario'];?>';
    respondern.value = '<?php echo $_GET['nombre'];?>';
    <?php
}
?>
    var inputlists = document.querySelectorAll('input[list]');
for(var j = 0; j< inputlists.length; j++) {
inputlists[j].addEventListener('input', function(e) {
var input = e.target,
list = input.getAttribute('list'),
options = document.querySelectorAll('#' + list + ' option'),
hiddenInput = document.getElementById(input.id + '-hidden'),
inputValue = input.value;
hiddenInput.value = inputValue;
for(var i = 0; i < options.length; i++) {
var option = options[i];
if(option.innerText === inputValue) {
hiddenInput.value = option.getAttribute('data-value');
break;
}else{
hiddenInput.value = "";
}
}
});
}
var theArea = document.getElementById('mensaje');
theArea.setSelectionRange(theArea.value.length,theArea.value.length);
</script>