<?php 
ob_start();

require_once("../comun/conexion.php");
require_once("../comun/config.php");
require_once("../comun/funciones.php");

echo '<center>';

if (isset($_POST['nombre_institucion'])) {

  $cod = intval($_POST['cod']);
    $imgavatar = "";
    $imgbanner = "";
    
    // Procesar logo
    if (!empty($_FILES['logo_institucion']['name']) && $_FILES['logo_institucion']['error'] === 0) {
        $ext = pathinfo($_FILES['logo_institucion']['name'], PATHINFO_EXTENSION);
        $imgavatar = ", logo_institucion='logo." . $ext . "' ";
        $logo_path = READFILE_SERVER . "/foto/logo." . $ext;
    }
    
    // Procesar banner
    if (!empty($_FILES['banner_institucion']['name']) && $_FILES['banner_institucion']['error'] === 0) {
        $ext = pathinfo($_FILES['banner_institucion']['name'], PATHINFO_EXTENSION);
        $imgbanner = ", banner_institucion='banner." . $ext . "' ";
        $banner_path = READFILE_SERVER . "/foto/banner." . $ext;
    }
    #print_r($_POST);
    $sql = "UPDATE institucion_educativa SET 
            nombre_institucion = '" . $mysqli->real_escape_string($_POST['nombre_institucion']) . "', 
            formatos_no_permitidos = '" . $mysqli->real_escape_string($_POST['formatos_no_permitidos']) . "', 
            telefono_institucion = '" . $mysqli->real_escape_string($_POST['telefono_institucion']) . "', 
            direccion_institucion = '" . $mysqli->real_escape_string($_POST['direccion_institucion']) . "', 
            tamano_maximo_adjunto = '" . intval($_POST['tamano_maximo_adjunto']) . "', 
            autoregistrarse = 'si' " . $imgavatar . $imgbanner . "
            WHERE id_institucion_educativa = '" .$_POST['cod']. "'";
   # echo $sql;
    if ($mysqli->query($sql)) {
        // Guardar archivos si fueron subidos
        if (!empty($logo_path)) {
            @unlink(READFILE_SERVER . "/foto/" . $_POST['foto_old']);
            move_uploaded_file($_FILES['logo_institucion']['tmp_name'], $logo_path);
        }
        
        if (!empty($banner_path)) {
            @unlink(READFILE_SERVER . "/foto/" . $_POST['banner_old']);
            move_uploaded_file($_FILES['banner_institucion']['tmp_name'], $banner_path);
        }
        
        echo "<script>alert('Modificación exitosa');</script>";
    } else {
        echo "<script>alert('Modificación fallida');</script>";
    }
}

$sql = "SELECT * FROM institucion_educativa WHERE id_institucion_educativa = '" . intval($_POST['cod']) . "'";
$result = $mysqli->query($sql);
$row = $result->fetch_assoc();
?>

<!-- Código HTML para el formulario -->
<center>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">Modificar Perfil de Institución</h1>
  </div>
</div>
<form id="form1" method="post" action="configuraciones.php" enctype="multipart/form-data">
<input name="cod" type="hidden" value='<?php echo $row['id_institucion_educativa']; ?>'>    
<p>
        <label for="nombre_institucion">Nombre Institución:</label>
        <input class="form-control" name="nombre_institucion" type="text" id="nombre_institucion" value="<?php echo htmlspecialchars($row['nombre_institucion']); ?>">
    </p>
    <p>
        <label for="direccion">Dirección:</label>
        <input class="form-control" name="direccion_institucion" type="text" id="direccion_institucion" value="<?php echo htmlspecialchars($row['direccion_institucion']); ?>">
    </p>
    <p>
        <label for="direccion">Telefono:</label>
        <input class="form-control" name="telefono_institucion" type="text" id="telefono_institucion" value="<?php echo htmlspecialchars($row['telefono_institucion']); ?>">
    </p>
    <p>
        <label for="formatos_no_permitidos">Formatos no permitidos:</label>
        <input class="form-control" name="formatos_no_permitidos" type="text" id="formatos_no_permitidos" value="<?php echo htmlspecialchars($row['formatos_no_permitidos']); ?>">
    </p>
    <p>
        <label for="tamano_maximo_adjunto">tamano maximo adjunto:</label>
        <input class="form-control" name="tamano_maximo_adjunto" type="text" id="tamano_maximo_adjunto" value="<?php echo htmlspecialchars($row['tamano_maximo_adjunto']); ?>">
    </p>
    
    
    <p>
        <label for="logo_institucion">Logo:</label>
        <?php if (!empty($row['logo_institucion'])): ?>
            <img height="80" src="<?php echo READFILE_URL ?>/foto/<?php echo $row['logo_institucion']; ?>">
        <?php endif; ?>
        <input class="form-control" name="logo_institucion" type="file">
    </p>
    
    <p>
        <label for="banner_institucion">Banner:</label>
        <?php if (!empty($row['banner_institucion'])): ?>
            <img height="80" src="<?php echo READFILE_URL ?>/foto/<?php echo $row['banner_institucion']; ?>">
        <?php endif; ?>
        <input class="form-control" name="banner_institucion" type="file">
    </p>
    <p>
        <label for="logo_institucion">Logo:</label>
        <?php if (!empty($row['logo_institucion'])): ?>
            <img height="80" src="<?php echo READFILE_URL ?>/foto/<?php echo $row['logo_institucion']; ?>">
        <?php endif; ?>
        <input class="form-control" name="logo_institucion" type="file">
    </p>
    
    <button class="btn btn-info" type="submit">Actualizar</button>
</form>
</center>

<?php
$contenido = ob_get_contents();
ob_end_clean();
include("../comun/plantilla.php");
?>
