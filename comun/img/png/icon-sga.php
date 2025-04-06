<?php header("Content-type: text/css; charset: UTF-8");
//<!--http://www.flaticon.es/-->
#require (dirname(__FILE__)."/../../conexion.php");
#$sql = "INSERT IGNORE INTO `iconos`(`icono`, `imagen_icono`) VALUES ";
require_once (dirname(__FILE__)."/../../config.php");
	$archivos = glob(dirname(__FILE__)."/*.png");
	$cont=0;
	$todos = count($archivos);
	foreach($archivos as $archivo){
	$cont++;
	$nombre_archivo = str_replace(dirname(__FILE__)."/","",$archivo);
	$nombre = str_replace(".png","",$nombre_archivo);
	if (!isset($tamano)) $tamano = 20;
	#$sql .= "('$nombre','$nombre_archivo')";
	#if ($cont<$todos) $sql .= ",";
	?>
	.icon-sga-<?php echo $nombre ?>{
		position:absolute;
		width:<?php echo $tamano ?>px;
		height:<?php echo $tamano ?>px;
		background-image: url("<?php echo SGA_COMUN_URL."/img/png/".$nombre ?>.png");
		background-size: <?php echo $tamano ?>px <?php echo $tamano ?>px;
    	background-repeat: no-repeat;
    	margin-left:-<?php echo ($tamano/2)+3 ?>px;
    	padding:<?php echo $tamano ?>px;
	}
<?php } 
#$mysqli->query($sql);
?>