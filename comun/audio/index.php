<?php
ob_start();
@session_start();
if ($_SESSION['rol']!="admin") {
echo "Igreso incorrecto";
exit();    
}
require (dirname(__FILE__)."/../config.php");
	foreach(glob(dirname(__FILE__)."/*.*") as $nombre){
	$info = new SplFileInfo($nombre);
    $archivo = $info->getBasename();
    $formato = $info->getExtension();
	$nombre = str_replace(".".$formato,"",$archivo);
	if ($archivo!="index.php"){
	$ruta = SGA_COMUN_URL."/audio/".$archivo;
	?>
	<span style="">
	<label title="<?php echo $nombre ?>">
        <audio controls>
        <source src="<?php echo $ruta ?>" type="audio/<?php echo $formato ?>">
        <a src="<?php echo $ruta ?>"><?php echo $archivo ?></a>
        </audio></label>
	</span>
<?php }
}
?>
<?php $contenido = ob_get_contents();
ob_clean();
include (SGA_COMUN_SERVER."/plantilla.php");
?>