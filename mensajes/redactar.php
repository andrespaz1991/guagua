<?php
ob_start();
@session_start();
require("../comun/conexion.php");
if (isset($_POST['mensaje'])){
    require_once("../clases/Phpmailer.class.php");
$remite = $_SESSION['id_usuario'];
$usuario = $mysqli->real_escape_string($_SESSION['id_usuario']);
$mensaje = $mysqli->real_escape_string($_POST['mensaje']);
$mail = new PHPMailer();
require("../comun/autoload.php");
if(isset($_POST['correo']) and $_POST['correo']=="on" ) {
$persona=new persona($usuario);
$persona_remite= new persona($remite);
$mail->From = "Guagua";
$mail->FromName = "Guagua";
$mail->Subject = "Mensaje de Guagua";
$mail->Body = $_POST['mensaje'];
$mail->AddAddress($persona->correo, "User Name");
}
$sql = "INSERT INTO mensaje (usuario,mensaje,remite) VALUES ('".$usuario."','".$mensaje."','".$remite."')";
$resultado = $mysqli->query($sql);
#echo "affected rows: ".$mysqli->affected_rows;
if($mysqli->affected_rows>0){
    ?>
    <script>alert2('Mensaje Enviado');</script>
<?php
if($mail->Send()){
echo "<script>alert2('Correo Enviado');</script>";
}

?>

    <?php
}else{
    ?>
    <script>alert2('Mensaje NO Enviado','error');</script>
    <?php
}

}
?>
<form method="post">
    <input type="hidden" name="remite" value="<?php echo $_SESSION['id_usuario']?>">
<?php
?>
<br>
<p>
<div class="form-group">
    <label for="usuario">Usuario:</label>
<input class="form-control"  required type="text" name="usuario" list="list_usuario">



</div>
<?php 
 $sql2= "SELECT id_usuario,nombre,apellido FROM usuario;";
$consulta2 = $mysqli->query($sql2); ?>

<datalist id="list_usuario">
<?php
while($row2=$consulta2->fetch_assoc()){
?>
  <option  label="<?php echo $row2['id_usuario']  ?>" value="<?php echo $row2['nombre'].' '.$row2['apellido']  ?>">

 <?php
}
?>
</datalist>
<?php
echo '</datalist><input required type="hidden" name="usuario3" id="usuario-hidden" value="';
if (isset($row['usuario'])) echo $row['usuario'];
echo '"></p>';
?><br>
<div class="form-group">
    <label for="mensaje">Mensaje:</label><br>
    <textarea autofocus class="form-control" required name="mensaje" id="mensaje" cols="60" rows="10"><?php
if(isset($_POST['materia_asunto'])){echo $_POST['materia_asunto'];}
        if (isset($_POST['responder_n'], $_POST['responder_mensaje'])){
            if(isset($_POST['action']) and $_POST['action']=="responder"){
            echo $_POST['responder_n']." escribió: ".$_POST['responder_mensaje'];
            echo "\n";
            echo "______________________________________";
            echo "\n";
            }else{
            echo $_POST['responder_mensaje'];
            }
        }
        ?></textarea>
</div>
<label>Copia al correo</label>
<input type="checkbox" name="correo"><br>
<button class="btn btn-primary">Enviar</button>
</form>
 <script>
<?php
if (isset($_POST['responder_a'])){    ?>
var responder = document.querySelector('#usuario-hidden');
 var respondern = document.querySelector('#usuario');
responder.value = '<?php echo $_POST['responder_a'];?>';
 respondern.value = '<?php echo $_POST['responder_n'];?>';
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
<?php $contenido = ob_get_contents();
ob_clean();
require("../comun/plantilla.php");
 ?>