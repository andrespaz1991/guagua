<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<?php 
function barra_buscar($funcion){
?>
<script src="ajax/ajax.js"></script>
<!--script src="js/cookies.js"></script-->
<b><label>Buscar:</label></b>
<input placeholder="nombre,identificaci&oacute;n" type="text" id="buscar" onkeyup ="buscar('<?php echo $funcion ?>',this.value);">
<?php } ?>