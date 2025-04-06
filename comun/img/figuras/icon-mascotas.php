<?php header("Content-type: text/css; charset: UTF-8");
//<!--http://www.flaticon.es/-->
require_once (dirname(__FILE__)."/../../config.php");
	foreach(glob(dirname(__FILE__)."/*.PNG") as $archivo){
	$nombre = str_replace(".PNG","",$archivo);
	$nombre = str_replace(dirname(__FILE__)."/","",$nombre);
	if (!isset($tamano)) $tamano = 20;
	?>
	.icon-sga-<?php echo $nombre ?>{
		position:absolute;
		width:<?php echo $tamano ?>px;
		height:<?php echo $tamano ?>px;
		background-image: url("<?php echo SGA_COMUN_URL."/img/figuras/".$nombre ?>.PNG");
		background-size: <?php echo $tamano ?>px <?php echo $tamano ?>px;
    	background-repeat: no-repeat;
    	margin-left:-<?php echo ($tamano/2)+3 ?>px;
    	padding:<?php echo $tamano ?>px;
	}
<?php } ?>