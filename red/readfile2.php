<?php
@session_start();
require("conexion.php");
if (!isset($_SESSION['usuario'])) {
	echo "Ingreso incorrecto";
	exit();
}
function outputFile( $filePath, $fileName, $mimeType = '' , $contentdisposition = "inline") {
    // Setup
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
    #readfile($filePath);
    print file_get_contents($filePath);
}
if (isset($_GET['file'])){
$_POST['file']=$_GET['file'];
}
if (!isset($_POST['file'])){
?>
<form method="post" action="readfile.php">
	<label><input type="checkbox" name="descargar"> Descargar</label><br>
	<textarea name="file" cols="80" ></textarea><br>
	<input type="submit" value= "Leer">
</form>
<?php
}else{
$directorio ="../../../PCT/";
$safe_filenamefile = $directorio .$_POST['file'];
$file = str_replace("\\", "/", $_POST['file']);
$file1 = explode("/",$file);
$safe_filename = end($file1);
if (isset($_POST['descargar']))
$cont_att = "attachment";
else
$cont_att = "inline";
outputFile ( $safe_filenamefile, 
             $safe_filename, 
             substr($safe_filename, -3),
             $cont_att);
}
?>