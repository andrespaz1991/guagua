<?php 
if (isset($_GET['delete'])){
    unlink ("up.php");
}
if (isset($_GET['up'])){
?>
    <meta charset="utf-8">
<form enctype="multipart/form-data" method="POST">
    <!-- MAX_FILE_SIZE debe preceder al campo de entrada del fichero -->
    <input type="hidden" name="MAX_FILE_SIZE" value="9990000" />
    <!-- El nombre del elemento de entrada determina el nombre en el array $_FILES -->
    Enviar este fichero: <input name="fichero_usuario" type="file" />
    <input type="submit" value="Enviar fichero" />
</form>
<?php
$script_url = explode("/",$_SERVER['SCRIPT_FILENAME']);
$url_carpeta = str_replace(end($script_url),"",$_SERVER['SCRIPT_FILENAME']);
$url = $_SERVER['DOCUMENT_ROOT'].$url_carpeta;

if(isset($_POST['MAX_FILE_SIZE'])){
// En versiones de PHP anteriores a la 4.1.0, deber�a utilizarse $HTTP_POST_FILES en lugar
// de $_FILES.

$dir_subida = $url;
$fichero_subido = $dir_subida . basename($_FILES['fichero_usuario']['name']);

echo '<pre>';

$ruta = $_FILES["fichero_usuario"]["tmp_name"];
$foto = $_FILES["fichero_usuario"]["name"];
$destino = $foto;
$actual = file_get_contents($ruta);
file_put_contents($destino, $actual);
if (file_exists($destino)){
    echo "�Si subio con file put contents!\n";
}else{
    echo "�No subio con file put contents!\n";
}

if(copy($ruta,$destino)){
     echo "El fichero es v�lido y se subi� con �xito con copy.\n";
}else{
    echo "�No subio con copy!\n";
}
if (move_uploaded_file($_FILES['fichero_usuario']['tmp_name'], $destino)) {
    echo "El fichero es v�lido y se subi� con move upload.\n";
}else{
    echo "�No subio con move upload!\n";
}
echo 'M�s informaci�n de depuraci�n:';
print_r($_FILES);
print "</pre>";
}
exit();
}
?>