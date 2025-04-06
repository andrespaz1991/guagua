<?php
@session_start();
require_once("../comun/config.php");
require_once("../comun/funciones.php");
if (!isset($_SESSION['usuario'])) {
if (!isset($_GET['token'])){
	echo "Ingreso incorrecto";
	exit();
}else{
    $token = encriptar($_GET['file']);
    if($_GET['token']!=$token){
        echo "Ingreso incorrecto";
        exit();
    }
}
}
/*
* el objetivo es que se pueda descargar o visualizar un archivo solo si esta autenticado
* el input type="checkbox" name="descargar" permite descargar o visualizar
* adicionalmente que no se pueda acceder explorando en el navegador
* este parametro $_POST['file'] es el que nos permite enviar la ruta relativa
* puede cambiar a $_GET, si pasa por login, no hay problema
* ej si nosotros tenemos fuera de www
* ../datos/ #OJO variable $directorio
* dentro de datos tenemos la carpeta recursos
* y el archivo ejemplo.doc
* $_POST['file']="recursos/ejemplo.doc";
*/
function outputFile( $filePath, $fileName, $mimeType = '' , $contentdisposition = "inline") {
    // Setup
    $nopermitidas = array("php","js","css","sql");
    if (in_array($mimeType,$nopermitidas)){
    echo "No se puede leer archivos $mimeType";
    exit();
    }
    $mimeTypes = array(
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'html' => 'text/html',
        'exe' => 'application/octet-stream',
        'zip' => 'application/zip',
        'doc' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'jpeg' => 'image/jpg',
        'jpg' => 'image/jpg',
        'php' => 'text/plain'
    );

    // Send Headers
    //-- next line fixed as per suggestion --
    header('Content-Type: ' . $mimeTypes[$mimeType]);
    header('Content-Disposition: '.$contentdisposition.' ; filename="' . $fileName . '"');//inline -> en linea attachment -> forzar descarga
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('Cache-Control: private');
    header('Pragma: private');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    readfile($filePath);
}
if(isset($_GET['file']))
$_POST['file'] = $_GET['file'];
if(isset($_GET['file'])) $_GET['file'] = str_replace("..","",$_GET['file']);
if(isset($_POST['file'])) $_POST['file'] = str_replace("..","",$_POST['file']);
if (!isset($_POST['file'])){
?>
<form method="post" action="readfile.php">
	<label><input type="checkbox" name="descargar"> Descargar</label><br>
	<textarea name="file" cols="80" >fechasycookies.pdf</textarea><br>
	<input type="submit" value= "Leer">
</form>
<?php
}else{
$directorio = READFILE_SERVER;//aqui el directorio de los datos
$safe_filenamefile = $directorio .$_POST['file'];
$file = str_replace("\\", "/", $_POST['file']);
$file1 = explode("/",$file);
$safe_filename = end($file1);
if (isset($_POST['descargar']) or isset($_GET['descargar']))
$cont_att = "attachment";
else
$cont_att = "inline";
outputFile ( $safe_filenamefile, 
             $safe_filename, 
             substr($safe_filename, -3),
             $cont_att);
}
?>