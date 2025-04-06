<?php
@session_start();
ob_start();
if(!isset($_SESSION['id_usuario'])){
    header("Location:login.php");
    exit();
}
if (isset($_GET['hijo'])){
    #print_r($_POST);
    $_SESSION['hijo'] = $_GET['hijo'];
    $_SESSION['foto_hijo']=$_GET['foto_hijo'];
        header("Location:index.php");
}
else {
$mensaje ='<h1>No tienes estudiantes asignados<br/>
    Contacta a un docente o administrador de la institución para que te pueda asignar
        uno </h1>';
}

?>
<style>
div.show-top-margin{margin-top:2em;}.show-grid{margin-bottom:2em;}.show-grid [class^="col-"]{padding-top:10px;padding-bottom:10px;border:1px solid #AAA;background-color:#EEE;background-color:rgba(200,200,200,0.3);}.responsive-utilities-test .col-xs-6{margin-bottom:10px;}.responsive-utilities-test span{padding:15px 10px;font-size:14px;font-weight:bold;line-height:1.1;text-align:center;border-radius:4px;}.visible-on .col-xs-6 .hidden-xs,.visible-on .col-xs-6 .hidden-sm,.visible-on .col-xs-6 .hidden-md,.visible-on .col-xs-6 .hidden-lg,.hidden-on .col-xs-6 .visible-xs,.hidden-on .col-xs-6 .visible-sm,.hidden-on .col-xs-6 .visible-md,.hidden-on .col-xs-6 .visible-lg{color:#999;border:1px solid #ddd;}.visible-on .col-xs-6 .visible-xs,.visible-on .col-xs-6 .visible-sm,.visible-on .col-xs-6 .visible-md,.visible-on .col-xs-6 .visible-lg,.hidden-on .col-xs-6 .hidden-xs,.hidden-on .col-xs-6 .hidden-sm,.hidden-on .col-xs-6 .hidden-md,.hidden-on .col-xs-6 .hidden-lg{color:#468847;background-color:#dff0d8;border:1px solid #d6e9c6;}div.controls input,div.controls select{margin-bottom:.5em;}#inputSeleccionado{border-color:rgba(82,168,236,.8);outline:0;outline:thin dotted \9;-moz-box-shadow:0 0 8px rgba(82,168,236,.6);box-shadow:0 0 8px rgba(82,168,236,.6);}.bs-glyphicons{padding-left:0;padding-bottom:1px;margin-bottom:20px;list-style:none;overflow:hidden;}.bs-glyphicons li{float:left;width:25%;height:115px;padding:10px;margin:0 -1px -1px 0;font-size:12px;line-height:1.4;text-align:center;border:1px solid #ddd;}.bs-glyphicons .glyphicon{display:block;margin:5px auto 10px;font-size:24px;}.bs-glyphicons li:hover{background-color:rgba(86,61,124,.1);}@media (min-width: 768px) {.bs-glyphicons li{width:12.5%;}}.btn-toolbar+.btn-toolbar{margin-top:10px;}.dropdown>.dropdown-menu{position:static;display:block;margin-bottom:5px;}form .row{margin-bottom:1em;}.nav .dropdown-menu{display:none;}.nav .open .dropdown-menu{display:block;position:absolute;}
</style>
<!--form id="form_avatar" method="post" action="<?php echo SGA_URL; ?>/index.php">
<!--input type="hidden" id="hijo" name="hijo" value="<?php if (isset($_SESSION['foto_hijo'])) echo READFILE_URL."/foto/".$_SESSION['foto_hijo']
?>"/>
<input type="hidden" id="foto_hijo" name="foto_hijo" value="<?php if (isset($_SESSION['foto_hijo'])) echo READFILE_URL."/foto/".$_SESSION['foto_hijo']
 ?>"/-->
   <div class="jumbotron">
  <div class="container text-center">
      <h1 class="fip" align="center"><?php echo strtoupper('Selecciona un Estudiante'); ?></h1>      
  </div>
</div>
      
  <ul  class="bs-glyphicons">
	<?php
	require '../comun/conexion.php'; 
 $sql="SELECT *,usuario.nombre,usuario.apellido,usuario.foto FROM `acudiente_estudiante`, `usuario` WHERE `acudiente_estudiante`.`id_estudiante` = `usuario`.`id_usuario` and `acudiente_estudiante`.`id_acudiente` = '".$_SESSION['id_usuario']."'";
      $consulta = $mysqli -> query ($sql);
      while ($row= $consulta ->fetch_assoc()){
          if($consulta ->num_rows==1){
          $_SESSION['hijo']=$row['id_estudiante'];
  $sql_estudiante = "SELECT * from usuario WHERE id_usuario = '".$_SESSION['hijo']."'";
 $consulta_estudiante = $mysqli->query($sql_estudiante);

        if ($row_estudiante= $consulta_estudiante->fetch_assoc()){
            //    $_SESSION['id_categoria_curso'] = $row_estudiante['id_categoria_curso'];
        }
      
          $_SESSION['foto_hijo']=$row['foto'];
          }   else {
echo $mensaje;

          } ?>
	<li  <?php	if ($row['id_estudiante']==$_SESSION['hijo']) { echo 'style="height:170px;background-color:#31708f"' ; } else { echo 'style="background-color:#d6e6d6"'; } ?>>
	  <p><font size="3" color="white"><?php echo $row['nombre']." ".$row['apellido'] ?></font></p>
	  <span title="Elegir" onclick="obtener_icono(this);document.getElementById('form_avatar').submit();" data-src="<?php echo $row['foto'] ?>" data-id="<?php echo $row['id_estudiante'] ?>" ><a href="elegir_hijo.php?hijo=<?php echo $row['id_estudiante']; ?>&foto_hijo=<?php echo $row['foto']; ?>"><img style="margin: 0 auto;" height="70px" src="<?php echo READFILE_URL."/foto/".$row['foto'] ?>"/></a></span>
	     <a title="modificar información" href="" class="btn btn-success">Modificar</a>

	  </li>
	<?php  }	?>
    </ul> 
</form>
<?php
$contenido = ob_get_clean();
require ("../comun/plantilla.php");
if(!isset($_SESSION['hijo'])){ ?>
<script>
    document.getElementById('elegir_avatar').click();
</script>
<?php }


?>

