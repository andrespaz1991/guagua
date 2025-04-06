<?php 
ob_start();
@session_start();
?>
<center>
<?php 
require("conexion.php");

if(isset($_GET['idevento'])){
   $sql_ev='UPDATE `eventos` SET  `revisado`="1" WHERE id="'.$_GET['idevento'].'"';

if ($mysqli->query($sql_ev)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=../index.php" />';
 }else{
echo 'Modificación fallida';
echo '<meta http-equiv="refresh" content="1; url=../index.php" />';

 }

}



require_once("funciones.php");
function buscar_eventos($datos="",$reporte=""){
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=eventos.xls");
}
require("conexion.php");
require_once("funciones.php");
require_once ("lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = (isset($_COOKIE['numeroresultados_eventos']) ? $_COOKIE['numeroresultados_eventos'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_eventos";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `eventos`.`institucion`,`eventos`.`id`, `eventos`.`nom`, `eventos`.`descripcion`, `eventos`.`fecha`, `eventos`.`hora_inicio`, `eventos`.`hora_fin`, `eventos`.`id_usuario`, `usuario`.`nombre` as usuarionombre FROM `eventos`  inner join `usuario` on `eventos`.`id_usuario` = `usuario`.`id_usuario`  ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE `eventos`.`institucion` = "'.$_SESSION['institucion'].'" and ';
if (isset($_COOKIE['buscar_evento_fecha1'],$_COOKIE['buscar_evento_fecha2'])){
if ($_COOKIE['buscar_evento_fecha1']!="" and $_COOKIE['buscar_evento_fecha2']!=""){
$buscar_evento_fecha1 = $_COOKIE['buscar_evento_fecha1'];
$buscar_evento_fecha2 = $_COOKIE['buscar_evento_fecha2'];
$sql .= " fecha BETWEEN '$buscar_evento_fecha1' AND '$buscar_evento_fecha2' and ";
} 
}
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`eventos`.`nom`)," ",LOWER(`eventos`.`descripcion`)," ",LOWER(`eventos`.`fecha`)," ",LOWER(`eventos`.`hora_inicio`)," ",LOWER(`eventos`.`hora_fin`)," ",LOWER(`usuario`.`nombre`)) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']!=""){ $sql .= "`eventos`.`".$_COOKIE['orderbyeventos']."`";
}else{ $sql .= "`eventos`.`id`";}
if (isset($_COOKIE['orderad_eventos'])){
$orderadeventos = $_COOKIE['orderad_eventos'];
$sql .=  " $orderadeventos ";
}else{
$sql .=  " desc ";
}
$consulta_total_eventos = $mysqli->query($sql);
$total_eventos = $consulta_total_eventos->num_rows;
$paginacion->records($total_eventos);
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#echo $sql; 

$consulta = $mysqli->query($sql);
$numero_eventos = $consulta->num_rows;
$minimo_eventos = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_eventos = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_eventos>$numero_eventos) $maximo_eventos=$numero_eventos;
$maximo_eventos += $minimo_eventos-1;
echo "<p>Resultados de $minimo_eventos a $maximo_eventos del total de ".$total_eventos." en página ".$paginacion->get_page()."</p>";
/*
reloj
$hora=0;
$minutos=50;
$hora = ($hora > 12)? $hora -12 : $hora;
$hora = ($hora == '00')? 12 : $hora;
$hora = ($hora == 0)? 12 : $hora;
$rotateM =  "rotate(".$minutos * 6 .  "deg)";
$rotateH =  "rotate(".(((360*$hora)/12) + ($minutos / 2))."deg)";
 ?>
<section id="reloj">
  <!--div id="segundos">
  </div-->
  <div id="minutos" style="transform:<?php echo $rotateM ?>">
  </div>
  <div id="horas" style="transform:<?php echo $rotateH ?>">
  </div>
</section>

 <style>
 #reloj {
  position: relative;
  width: 200px;
  height: 200px;
  margin: 0 auto 0 auto;
  background: url(https://4.bp.blogspot.com/-JWBIk7o77hQ/T43_W-LtTKI/AAAAAAAACh4/n8wev2W3XJQ/s200/reloj%2Bsin%2Bmanecillas.GIF) no-repeat;
}

#segundos, #minutos, #horas {
   background-image: url(http://riotdesign.eu/assets/img/orologio/analogseconds.png);
  background-position: center center;
  height:200px;
  width: 30px;
  position: absolute;
  top: 0;
  left: 87px;
}

#segundos {
  background-size: 320px;
  -webkit-filter: grayscale(1);
}

#minutos {
  background-size: 280px;
}

#horas {
  background-size: 200px;
  background-repeat: no-repeat;
}
/ * * /
 #reloj {
  position: relative;
  width: 50px;
  height: 50px;
  margin: 0 auto 0 auto;
  background-size:50px 50px;
  background-image: url(https://4.bp.blogspot.com/-JWBIk7o77hQ/T43_W-LtTKI/AAAAAAAACh4/n8wev2W3XJQ/s200/reloj%2Bsin%2Bmanecillas.GIF) no-repeat;
}

#segundos, #minutos, #horas {
   background-image: url(http://riotdesign.eu/assets/img/orologio/analogseconds.png);
  background-position: center center;
  height:50px;
  width: 8px;
  position: absolute;
  top: 0;
  left: 22px;
}

#segundos {
  background-size: 80px;
  -webkit-filter: grayscale(1);
}

#minutos {
  background-size: 70px;
}

#horas {
  background-size: 50px;
  background-repeat: no-repeat;
}
*/
 /**/
?>
<style>
.eventos_ficha{
	font: 87.5%/1.5em 'Lato', sans-serif;
}
  .dia {
	font-size: 1.5em;
	font-weight: 100;
	line-height: 1em;
	color:rgba(150,0,0,.5);
 position: relative;
 top: -10px;
}

.mes {
	font-size: 1.5em;
	line-height: 1em;
	text-transform: uppercase;
	color:rgba(150,0,0,0.4);
	position: relative;
 top: -10px;
}
.nombre_evento{
position: relative;
top: -20px;
font-size: 1.2em;
font-weight:bold;
text-shadow: white 2px 2px 6px;
}
.hora{
position: relative;
}
.hora_inicio{
 float:left;
 top: -40px;
 left: -30px;
}
.hora_fin{
 float:right;
 top: -65px;
 right: -30px;
}
 </style>
<div align="center">
 <?php /*
<ul class="bs-glyphicons eventos_ficha">
	<?php
	$consulta_av = $consulta = $mysqli->query($sql);
	while($row_av=$consulta_av->fetch_assoc()){
	//$nombre2 = str_replace("Avatars/","",$nombre);
	/**
	id
	nom
descripcion
fecha
hora_inicio
hora_fin
usuarionombre
	* /
$date_unix = strtotime($row_av['fecha']);
$meses = array ("","ENE","FEB","MAR","ABR","MAY","JUN","JUL","AGO","SEP","Octubre","NOV","DIC");
?>
	<li title="<?php echo $row_av['descripcion']?>">
	 <span data-id="<?php echo $row_av['id'] ?>" >
	 <span class="mes"><?php
	 $mes = date("n",$date_unix);
	 echo $meses[$mes]; ?></span>&nbsp;<span class="dia"><?php echo date("d",$date_unix); ?></span><br>
	 <span class="nombre_evento" style="font-family:12px"><?php echo $row_av['nom']?></span>
	 
	 <!--span class="hora hora_inicio"style="font-family:12px"><?php echo formatohoracorta($row_av['hora_inicio'])?></span-->
	 <!--span class="hora hora_fin"style="font-family:12px"><?php echo formatohoracorta($row_av['hora_fin'])?></span-->
	 </span>
	 </li>
	<?php } ?>
    </ul>
*/ ?>
<hr>
<table border="1" id="tbeventos">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "nom"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyeventos','nom');buscar();" >Nombre</th>
<th <?php  if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "descripcion"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyeventos','descripcion');buscar();" >Descripción</th>
<th <?php  if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "fecha"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyeventos','fecha');buscar();" >Fecha</th>
<th <?php  if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "hora_inicio"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyeventos','hora_inicio');buscar();" >Hora Inicio</th>
<th <?php  if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "hora_fin"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyeventos','hora_fin');buscar();" >Hora Fin</th>
<th <?php  if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "id_usuario"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyeventos','id_usuario');buscar();" >Usuario</th>
<!--th <?php  #if(isset($_COOKIE['orderbyeventos']) and $_COOKIE['orderbyeventos']== "estrellas"){ echo " style='text-decoration:underline' ";} ?> >Estrellas</th-->
<?php if ($reporte==""){ ?>
<?php if(isset($_SESSION['rol']) and $_SESSION['rol']<>'estudiante' and $_SESSION['rol']<>'acudiente' ) { ?>

<th data-label="Nuevo"><form id="formNuevo" name="formNuevo" method="post" action="eventos.php">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<?php } ?>

<?php if(isset($_SESSION['rol']) and $_SESSION['rol']<>"estudiante" and $_SESSION['rol']<>"acudiente" ) { ?>
<th data-label="XLS"><form id="formNuevo" name="formNuevo" method="post" action="eventos.php?xls">
<input name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="XLS">
</form>
</th>
<?php } ?>
<?php } ?>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td data-label='Nombre'><?php echo $row['nom']?></td>
<td data-label='Descripción'><?php echo $row['descripcion']?></td>
<td data-label='Fecha'><?php echo formatofecha($row['fecha'])?></td>
<td data-label='Hora Inicio'><?php echo formatohora($row['hora_inicio'])?></td>
<td data-label='Hora Fin'><?php echo formatohora($row['hora_fin'])?></td>
<td data-label="Usuario"><?php echo $row['usuarionombre']?></td>
<!--td data-label='Estrellas'><?php #echo $row['estrellas']?></td-->
<?php if ($reporte=="" and isset($_SESSION['rol']) and $_SESSION['rol']<>"estudiante" and $_SESSION['rol']<>"acudiente" or $_SESSION['id_usuario']==$row['id_usuario']){ ?>
<td data-label="Modificar">
<form id="formModificar" name="formModificar" method="post" action="eventos.php">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input type="image" src="img/eliminar.png" onClick="confirmeliminar2('eventos.php',{'del':'<?php echo $row['id'];?>'},'<?php echo $row['id'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<?php $paginacion->render2();?>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_eventos($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_eventos('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM eventos WHERE id="'.$_POST['del'].'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
Registro eliminado
<meta http-equiv="refresh" content="1; url=eventos.php" />
<?php 
}else{
?>
Eliminación fallida, por favor compruebe que la usuario no esté en uso
<meta http-equiv="refresh" content="2; url=eventos.php" />
<?php 
}
}
 ?>
<div style="height:200px;" id="jumbotron" class="jumbotron">
<center>

<h1>Eventos</h1>
</center></div><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$sql = "INSERT INTO eventos ( `nom`, `descripcion`, `fecha`, `hora_inicio`, `hora_fin`, `id_usuario`,institucion ) VALUES ( '".$_POST['nom']."', '".$_POST['descripcion']."', '".$_POST['fecha']."', '".$_POST['hora_inicio']."', '".$_POST['hora_fin']."', '".$_POST['id_usuario']."','".$_SESSION['id_institucion']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
 ?>
Registro exitoso
<meta http-equiv="refresh" content="1; url=eventos.php" />
<?php 
 }else{ 
 ?>
Registro fallido
<meta http-equiv="refresh" content="1; url=eventos.php" />
<?php 
}
} /*fin Registrar*/ 
if ($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar"){
if ($_POST['submit']=="Modificar"){
$sql = "SELECT * FROM `eventos` WHERE id ='".$_POST['cod']."' Limit 1"; 
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();

$textoh1 ="Modificar";
$textobtn ="Actualizar";
}
if ($_POST['submit']=="Nuevo"){
$textoh1 ="Registrar";
$textobtn ="Registrar";
}
 ?>
<div class="col-md-3"></div><form class="col-md-6" id="form1" name="form1" method="post" action="eventos.php">
<h1><?php echo $textoh1 ?></h1>
<p><input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id']))  echo $row['id'] ?>" size="120" required></p>
<?php 
echo '<div class="form-group"><p><input class="form-control"name="id"type="hidden" id="id" value="';if (isset($row['id'])) echo $row['id'];echo '"';echo '></p>';
echo "</div>";echo '<div class="form-group"><p><label for="nom">Nombre:</label><input class="form-control"name="nom"type="text" id="nom" value="';if (isset($row['nom'])) echo $row['nom'];echo '"';echo ' required ></p>';
echo "</div>";echo '<div class="form-group"><p>'; ?>
<label for="descripcion">Descripción:</label></p>
<p><textarea class="form-control" name="descripcion" cols="60" rows="10"id="descripcion"  required>
<?php if (isset($row['descripcion'])) echo $row['descripcion']; ?>
</textarea></p></div>
<?php 
echo '<div class="form-group"><p><label for="fecha">Fecha:</label><input class="form-control"name="fecha"type="date" id="fecha" value="';if (isset($row['fecha'])) echo $row['fecha'];echo '"';echo ' required ></p></div>';
echo '<div class="form-group"><p><label for="hora_inicio">Hora Inicio:</label><input class="form-control"name="hora_inicio"type="time" id="hora_inicio" value="';if (isset($row['hora_inicio'])) echo $row['hora_inicio'];echo '"';echo ' required ></p></div>';
echo '<div class="form-group"><p><label for="hora_fin">Hora Fin:</label><input class="form-control"name="hora_fin"type="time" id="hora_fin" value="';if (isset($row['hora_fin'])) echo $row['hora_fin'];echo '"';echo ' required ></p></div>';
echo '<div class="form-group"><p><label for="id_usuario">Usuario:</label>';
$sql10= "SELECT id_usuario,concat(nombre,' ',apellido) as nombre FROM usuario order by nombre asc;";
 ?>
<select  class="form-control" name="id_usuario" id="id_usuario"  required>
<?php 
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta10 = $mysqli->query($sql10);
while($row10=$consulta10->fetch_assoc()){
echo '<option value="'.$row10['id_usuario'].'"';if (isset($row['id_usuario']) and $row['id_usuario'] == $row10['id_usuario']) echo " selected ";echo '>'.$row10['nombre'].'</option>';
}
echo '</select></p></div>';
echo '<div class="form-group"><p><label for="estrellas">Estrellas:</label><input class="form-control"name="estrellas"type="text" id="estrellas" value="';if (isset($row['estrellas'])) echo $row['estrellas'];echo '"';echo '></p></div>';
echo '<p><input type="hidden" name="submit" id="submit" value="'.$textobtn.'"><button type="submit" class="btn btn-primary">'.$textobtn.'</button></p>
</form><div class="col-md-3"></div>';
} /*fin mixto*/ 
?>


<?php


if ($_POST['submit']=="Actualizar"){
 /*recibo los campos del formulario proveniente con el método POST*/ 
$cod = $_POST['cod'];
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE eventos SET  nom='".$_POST['nom']."', descripcion='".$_POST['descripcion']."', fecha='".$_POST['fecha']."', hora_inicio='".$_POST['hora_inicio']."', hora_fin='".$_POST['hora_fin']."', id_usuario='".$_POST['id_usuario']."' WHERE  id = '".$cod."';";

 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/ 
if ($actualizar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/
echo 'Modificación exitosa';
echo '<meta http-equiv="refresh" content="1; url=eventos.php" />';
 }else{ 
echo 'Modificacion fallida';
}
echo '<meta http-equiv="refresh" content="2; url=eventos.php" />';
} /*fin Actualizar*/ 
 }else{ 
 ?>
 <script type="text/javascript" >

$(document).ready(function() {
 cambiar();
}
  );
  
   function cambiar(){
var busqueda = tipobusqueda1.checked;
if(busqueda==true){
 busqueda_texto_div.style.display='none';
busqueda_entre_fechas_div.style.display='';
} else {
busqueda_entre_fechas_div.style.display='none';
 busqueda_texto_div.style.display='';
 }
    }
   </script>
<center>
<br/><br/>
 <label>Busueda por rango de fechas<input onclick="cambiar();"  onchange="cambiar();" id='tipobusqueda1' type="radio" name="tipobusqueda" value="busqueda_rango_fechas"/></label>
 <label>Busueda por texto<input onclick="cambiar();" checked id="" type="radio" id="tipobusqueda2" name="tipobusqueda" value="busqueda_text"/></label>
<br>
<div id="busqueda_texto_div"> 
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
</div> 
<div id="busqueda_entre_fechas_div"> 
<label>Entre</label> 
<input type="date" name="fecha1" id="fecha1" onclick="grabarcookie('orderad_eventos','ASC');buscar(document.getElementById('buscar').value);" onchange="grabarcookie('buscar_evento_fecha1',this.value);buscar();"> y 
<input type="date" name="fecha2" id="fecha2" onclick="grabarcookie('orderad_eventos','ASC');buscar(document.getElementById('buscar').value);" onchange="grabarcookie('buscar_evento_fecha2',this.value);buscar();">
</div>
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_eventos" placeholder="Cantidad de resultados por página" title="Cantidad de resultados por página" value="10" onkeyup="grabarcookie('numeroresultados_eventos',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_eventos',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_eventos',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_eventos','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_eventos','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php 
buscar_eventos();
 ?>
</span>
<script>
document.getElementById('fecha1').value = obtenerCookie('buscar_evento_fecha1');
document.getElementById('fecha2').value = obtenerCookie('buscar_evento_fecha2');
//grabarcookie('buscar_evento_fecha1',document.getElementById('fecha1').value);
//grabarcookie('buscar_evento_fecha2',document.getElementById('fecha2').value);
</script>
<?php 
}/*fin else if isset cod*/
echo '</center>';
 ?>
<script>
var vmenu_eventos = document.getElementById('menu_eventos')
if (vmenu_eventos){
vmenu_eventos.className ='active '+vmenu_eventos.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("plantilla.php");
 ?>
