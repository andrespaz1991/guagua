<?php
require(dirname(__FILE__)."../comun/conexion.php");
require_once(dirname(__FILE__)."../comun/config.php");
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://code.jquery.com/jquery-1.11.1.min.js"></script> 
<script src="https://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
<link rel="stylesheet" href="<?php echo SGA_COMUN_URL.'/'.'css/bootstrap.min.css'; ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo SGA_COMUN_URL.'/'.'js/bootstrap.min.js'; ?>" type="text/css" />
<script type="text/javascript" src="<?php echo SGA_COMUN_URL.'/'.'js/funciones.js'; ?>"></script>
<link href="<?php echo SGA_COMUN_URL.'/'.'js/jquery-2.2.4.min.js' ; ?>" rel="stylesheet">
<link href="<?php echo SGA_COMUN_URL.'/'.'js/funciones.js' ; ?>" rel="stylesheet">
<link href="<?php echo SGA_COMUN_URL.'/'.'css/estilossga.css' ; ?>" rel="stylesheet"><div class="container">
  <ul class="bs-glyphicons">
      <?php
function buscar_red($datos="",$reporte=""){
require(dirname(__FILE__)."../comun/conexion.php");
$sql = "SELECT * FROM `red`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`red`.`titulo_red`)," ", LOWER(`red`.`nivel_eductivo`)," ",LOWER(`red`.`nivel_eductivo`)," ", LOWER(`red`.`autor`)," ", LOWER(`red`.`responsable`)," ", LOWER(`red`.`materia_red`)," ", LOWER(`red`.`descripcion`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%" ';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY `red`.`fecha` desc LIMIT ";
if (isset($_COOKIE['numeroresultados_red']) and $_COOKIE['numeroresultados_red']!="") $sql .=$_COOKIE['numeroresultados_red'];
else $sql .= "10";
$consulta = $mysqli->query($sql);
 ?>
<div align="center">


<?php 
while($row=$consulta->fetch_assoc()){
?>
<span onclick="document.getElementById('iconred_<?php echo $row['id_red'];?>').click();"><li><span onclick="obtener_red(this)" id="iconred_<?php echo $row['id_red'];?>" data-nombre="<?php echo $row['id_red'].'.'.$row['titulo_red']  ?>" id="<?php echo $row['id_red']  ?>"  class="<?php echo $row['icono_red']; ?>"></span onclick="obtener_red(this)"><?php echo $row['titulo_red']; ?></li></span>
        <?php } 
  
        echo '</ul> </div>';
    
#}/*fin while*/
 ?>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_red($_POST['datos']);
exit();
}


 ?>
<center>

<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_red" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="10" onkeyup="grabarcookie('numeroresultados_red',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_red',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_red',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
</center>
<span id="txtsugerencias">
<?php 
buscar_red();
 ?>
</span>
<?php 
echo '</center>';
 ?>
<script>
document.getElementById('menu_red').className ='active '+document.getElementById('menu_red').className;
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
