<?php header("Content-type: text/css; charset: UTF-8");
require_once (dirname(__FILE__)."/../../config.php");
$archivos = glob(dirname(__FILE__)."/*.png");
foreach($archivos as $archivo){
    $nombre_archivo = str_replace(dirname(__FILE__)."/","",$archivo);
    $nombre = str_replace(".png","",$nombre_archivo);
    ?>
.icon-sga-<?php echo $nombre ?>{
    background-image: url("<?php echo SGA_COMUN_URL."/img/png/".$nombre ?>.png");
}
<?php } ?>