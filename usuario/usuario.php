<?php 
ob_start();
@session_start();
require ("../comun/conexion.php");
require_once ("../comun/config.php");
require_once ("../comun/funciones.php");
?>
<center>
<?php 
function buscar_usuario($datos="",$reporte=""){
require ("../comun/conexion.php");
require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
if ($reporte=="xls"){
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; Filename=usuario.xls");
}
if (isset($_GET['u'])){
$_SESSION['u']=mysqli_real_escape_string($mysqli,$_GET['u']);
}
if (isset($_POST['u'])){
$_SESSION['u']=mysqli_real_escape_string($mysqli,$_POST['u']);
}
$resultados = (isset($_COOKIE['numeroresultados_usuario']) ? $_COOKIE['numeroresultados_usuario'] : 4);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);

$cookiepage="page_usuario";
$funcionjs="buscar();";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);
$paginacion->padding(false);
@mkdir(READFILE_SERVER."/foto");

if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
$sql = "SELECT `usuario`.`tipo_sangre`,`usuario`.`id_usuario`, `usuario`.`usuario`, `usuario`.`clave`, `usuario`.`nombre`, `usuario`.`apellido`, `usuario`.`rol`, `usuario`.`foto`, `usuario`.`direccion`, `usuario`.`telefono`, `usuario`.`correo`, `usuario`.`ultima_sesion`, `usuario`.`num_visitas`, `usuario`.`puntos`, `usuario`.`estado` FROM `usuario`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`usuario`.`tipo_sangre`),LOWER(`usuario`.`usuario`)," ",LOWER(`usuario`.`clave`)," ",LOWER(`usuario`.`nombre`)," ",LOWER(`usuario`.`apellido`)," ",LOWER(`usuario`.`rol`)," ",LOWER(`usuario`.`foto`)," ",LOWER(`usuario`.`direccion`)," ",LOWER(`usuario`.`telefono`)," ",LOWER(`usuario`.`correo`)," ",LOWER(`usuario`.`ultima_sesion`)," ",LOWER(`usuario`.`num_visitas`)," ",LOWER(`usuario`.`puntos`)," ",LOWER(`usuario`.`estado`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
if (isset($_SESSION['u'])){
$sql .= " and `usuario`.`rol` LIKE '%".$_SESSION['u']."%'";
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']!=""){ $sql .= "`usuario`.`".$_COOKIE['orderbyusuario']."`";
}else{ $sql .= "`usuario`.`id_usuario`";}
if (isset($_COOKIE['orderad_usuario'])){
$orderadusuario = $_COOKIE['orderad_usuario'];
$sql .=  " $orderadusuario ";
}else{
$sql .=  " desc ";
}

$consulta_total_usuario = $mysqli->query($sql);
$total_usuario = $consulta_total_usuario->num_rows;
$paginacion->records($total_usuario);
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#echo $sql;

$consulta = $mysqli->query($sql);
$numero_usuario = $consulta->num_rows;
$minimo_usuario = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_usuario = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_usuario>$numero_usuario) $maximo_usuario=$numero_usuario;
$maximo_usuario += $minimo_usuario-1;
echo "<p>Resultados de $minimo_usuario a $maximo_usuario del total de ".$total_usuario." en página ".$paginacion->get_page()."</p>";
 ?>
<div align="center">
<table border="1" id="tbusuario">
<thead>
<tr>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "usuario"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','usuario');buscar();" >usuario</th>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "foto"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','foto');buscar();" >Persona</th>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "direccion"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','direccion');buscar();" >Dirección</th>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "telefono"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','telefono');buscar();" >Teléfono</th>
<th <?php  if(isset($_COOKIE['orderbytipo_sangre']) and $_COOKIE['orderbytipo_sangre']== "tipo_sangre"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbytipo_sangre','id_tipo_sangre');buscar();" >Sangre</th>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "correo"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','correo');buscar();" >Correo</th>


<?php if(isset($_SESSION['u']) and $_SESSION['u']=="estudiante"){
?>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "num_visitas"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','num_visitas');buscar();" >Acudiente</th>
<?php } ?>

<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "num_visitas"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','num_visitas');buscar();" >Visitas</th>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "puntos"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','puntos');buscar();" >Puntos</th>
<th <?php  if(isset($_COOKIE['orderbyusuario']) and $_COOKIE['orderbyusuario']== "estado"){ echo " style='text-decoration:underline' ";} ?>  onclick="grabarcookie('orderbyusuario','estado');buscar();" >Estado</th>
<th>
<?php if ($reporte==""){ ?>
<form  id="formNuevo" name="formNuevo" method="post" action="usuario.php" ENCTYPE="multipart/form-data">
<input name="u" type="hidden" id="u" value="<?php echo $_SESSION['u']; ?>">
<input name="cod" type="hidden" id="cod" value="0">
<input type="hidden" name="submit" id="submit" value="Nuevo"><button type="submit" class="btn btn-primary">Nuevo</button>
</form>
</th>
<?php } ?>
<th>
<form id="formNuevo" name="formNuevo" method="post" action="usuario.php?xls">
<input class="btn btn-primary" name="cod" type="hidden" id="cod" value="0">
<input type="submit" name="submit" id="submit" value="XLS">
</form></th>
</tr>
</thead><tbody>
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<tr>
<td data-label='usuario'><?php echo $row['usuario']?></td>
<?php $datosrol = array("admin" => "administrador", "estudiante" => "estudiante", "acudiente" => "acudiente"); ?>
<td data-label='Foto'>


 <?php echo $row['nombre'].' '.$row['apellido']?>
 <?php if($row['foto']!=""){ ?>
	<img height="120" src="<?php echo READFILE_URL ?>foto/<?php echo $row['foto']?>">
	<?php } ?>
	<?php echo $row['id_usuario']?>
	</td>
<td data-label='Direccion'><?php 
if(!empty($row['direccion']) and $row['direccion']<>"null") echo $row['direccion']?></td>
<td data-label='Telefono'><?php
if($_SESSION['u']=="estudiante"){
  require ("../comun/conexion.php");
 $sql_padre ='select * from acudiente_estudiante,usuario where 
 acudiente_estudiante.id_acudiente = usuario.id_usuario  and acudiente_estudiante.id_estudiante = "'.$row['id_usuario'].'" ';
 $consulta_padre = $mysqli -> query($sql_padre);
while($rowpadre=$consulta_padre->fetch_assoc()){
 '<br>';
} 
}
if(!empty($row['telefono']) and $row['telefono']<>"null") echo $row['telefono']?></td>
<td data-label='Tipo de Sangre'><?php echo $row['tipo_sangre']?></td>
<td data-label='Correo'><?php 
if(!empty($row['correo']) and $row['correo']<>"null")
echo $row['correo']?></td>
<?php if(isset($_SESSION['u']) and $_SESSION['u']=="estudiante"){
?><td data-label='acudiente'><?php 
 require ("../comun/conexion.php");
 $sql_padre ='select * from acudiente_estudiante,usuario where 
 acudiente_estudiante.id_acudiente = usuario.id_usuario  and acudiente_estudiante.id_estudiante = "'.$row['id_usuario'].'" ';
 $consulta_padre = $mysqli -> query($sql_padre);
while($rowpadre=$consulta_padre->fetch_assoc()){
 echo '<a title="Ver Perfil" href="'.SGA_USUARIO_URL.'/perfil.php?u='.$row['id_usuario'].'">('.$rowpadre['nombre'].' '.$rowpadre['apellido'].')</a><br/>';
} 

?></td>
<?php } ?>
<td data-label='Num Visitas'><?php echo $row['num_visitas']?></td>
<td data-label='Puntos'><?php echo $row['puntos']?></td>
<?php $datosestado = array("activo" => "Activo", "inactivo" => "inactivo"); ?>
<td data-label='Estado'><?php echo $datosestado[$row['estado']] ?></td>
<?php if ($reporte==""){ ?>
<td data-label="Modificar">
<form  id="formModificar" name="formModificar" method="post" action="usuario.php" ENCTYPE="multipart/form-data">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_usuario']?>">
<input name="u" type="hidden" id="u" value="<?php echo $_SESSION['u']?>">
<input type="hidden" name="submit" id="submit" value="Modificar"><button type="submit" class="btn btn-success">Modificar</button>
</form>
</td>
<td data-label="Eliminar">
<input title="Eliminar" type="image" src="../comun/img/eliminar.png" onClick="confirmeliminar2('usuario.php',{'del':'<?php echo $row['id_usuario'];?>'},'<?php echo $row['id_usuario'];?>');" value="Eliminar">
</td>
<?php } ?>
</tr>
<?php 
}/*fin while*/
 ?>
</tbody>
</table>
<?php $paginacion->render();?>
</div>
<?php 
}/*fin function buscar*/
if (isset($_GET['buscar'])){
buscar_usuario($_POST['datos']);
exit();
}
if (isset($_GET['xls'])){
ob_start();
buscar_usuario('','xls');
$excel = ob_get_clean();
echo utf8_decode($excel);
exit();
exit();
}
if (isset($_POST['del'])){
 require ("../comun/conexion.php");
 require_once ("../comun/config.php");
 $sqldel='select * from usuario where id_usuario = "'.$_POST['del'].'"';
$consultadel = $mysqli->query($sqldel);
while($rowdel = $consultadel ->fetch_assoc()){
 $_POST['rol'] =$rowdel['rol'] ;
}
/*Instrucción SQL que permite eliminar en la BD*/ 
 switch ($_POST['rol']) {
  case 'estudiante':
 $sql_usu ='DELETE FROM `estudiante` where `id_estudiante`="'.$_POST['del'].'"';
   break;
   case 'admin':
 $sql_usu = 'DELETE FROM `docente` WHERE `id_docente` = "'.$_POST['del'].'")' ;
   break;
   
    case 'acudiente':
 $sql_usu = 'DELETE FROM `docente` WHERE `id_docente` = "'.$_POST['del'].'")' ;
   break;
 }
 
 $consultadel = $mysqli->query($sql_usu);

 $sql = 'DELETE FROM usuario WHERE id_usuario="'.$_POST['del'].'"';
$sqls = 'SELECT * FROM usuario WHERE id_usuario="'.$_POST['del'].'"';
$consultas = $mysqli->query($sqls);
$eliminar_img = "";
if ($row = $consultas->fetch_assoc()){
 $rol = $row['rol'];
$eliminar_img = READFILE_SERVER."foto/".$row['foto'];
if(file_exists($eliminar_img)) unlink ($eliminar_img);
}
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
if ($eliminar = $mysqli->query($sql)){
 /*Validamos si el registro fue eliminado con éxito*/ 
 ?>
<script type="text/javascript" >alert2('Registro eliminado')</script>
<?php 
echo '<meta http-equiv="refresh" content="3; url=usuario.php?u='.$_SESSION['u'].'" />';
}else{
?>
<script type="text/javascript" >alert2('Eliminación fallida, por favor compruebe que la usuario no esté en uso')</script>


<?php 
echo '<meta http-equiv="refresh" content="3; url=usuario.php?u='.$_SESSION['u'].'" />';
}
}
 ?>
<center>
 <div  class="jumbotron">
  <?php if(isset($_GET['u'])){ ?>
  <div style="margin-left:-3%;margin-top:-2%;position:absolute;">
  <form  action ="../seguimiento/importar.php" ENCTYPE="multipart/form-data" method="POST">
    <a href="../seguimiento/plantillas/estudiantes.csv">Plantilla</a>
 <input   type="file" name="datos">
 <input class="btn btn-primary" type="submit" value="importar">
</form>
</div>
<?php } ?>
  <div class="container text-center">
    <h1 class="fip"> <?php       
    if(isset($_GET['u'])){
     $parametro =$_GET['u']; echo strtoupper($array_roles[$_GET['u']]);
    }  else { 
    $parametro = $_SESSION['u']; 
    echo strtoupper($array_roles[$_SESSION['u']]
    );
    } ?> </h1>      
  </div>
</div>

</center><?php 
if (isset($_POST['submit'])){
if ($_POST['submit']=="Registrar"){
require ("../comun/conexion.php");
require_once ("../comun/funciones.php");
//print_r($_POST);
$array_usuario['tipo_sangre'] = $mysqli->real_escape_string($_POST['tipo_sangre']);
$array_usuario['id_usuario'] = $mysqli->real_escape_string($_POST['id_usuario']);
$array_usuario['usuario'] = $mysqli->real_escape_string($_POST['usuario']);
$array_usuario['clave'] = $mysqli->real_escape_string($_POST['clave']);
$array_usuario['nombre'] = $mysqli->real_escape_string($_POST['nombre']);
$array_usuario['apellido'] = $mysqli->real_escape_string($_POST['apellido']);
$array_usuario['mascota'] = $mysqli->real_escape_string($_POST['mascota']);

foreach($_POST['rol'] as $id => $valor)
$_POST['rol'][$id] = $mysqli->real_escape_string($valor);
$roles = implode(",",$_POST['rol']);
$array_usuario['rol'] = $roles;

//foto
if(isset($_POST['inputcamara']) and $_POST['inputcamara']<>""){
$array_usuario['foto']=$_POST['inputcamara'];
}
elseif(isset($_POST['usuariodefault']) and $_POST['usuariodefault']<>""){
$array_usuario['foto']=$_POST['inputcamara'];
}
if ($_FILES['foto']['error']!=4){
$partes_nombre = explode (".",$_FILES['foto']['name']);
$ext = obtener_extension_archivo($_FILES['foto']['name']);
$array_usuario['foto'] = $mysqli->real_escape_string($_POST['id_usuario']).'.'.$ext;//nombrefoto
}



$array_usuario['direccion'] = $mysqli->real_escape_string($_POST['direccion']);
$array_usuario['telefono'] = $mysqli->real_escape_string($_POST['telefono']);
$array_usuario['correo'] = $mysqli->real_escape_string($_POST['correo']);
$array_usuario['estado'] = $mysqli->real_escape_string($_POST['estado']);
//print_r($array_usuario);
$sql = insertar($array_usuario,'usuario');
#$extension =obtener_extension_archivo($_FILES['foto']['tmp_name']) ;
$info = new SplFileInfo($_FILES['foto']['tmp_name']);
$extension=$info->getExtension();

if(isset($_FILES)){

move_uploaded_file($_FILES['foto']['tmp_name'],'../sga-data/foto/'.$_FILES['foto']['name']); 
}

if ($insertar = $mysqli->query($sql)) { ?>
 <script type="text/javascript" >alert2('Registro exitoso');</script>
<?php 
if($_POST['id_categoria_curso']){
 $ano_lectivo=consultar_id_ano_lectivo();
inscribir_estudiante_ano_lectivo($_POST['id_usuario'],$_POST['id_categoria_curso'],$ano_lectivo);
}
echo '<meta http-equiv="refresh" content="3; url=usuario.php?u='.$_SESSION['u'].'" />';
 }else{ 
 ?>
 <script type="text/javascript" >alert2('Registro fallido');</script>


<?php 
echo '<meta http-equiv="refresh" content="3; url=usuario.php?u='.$_SESSION['u'].'" />';
  
 }
} /*fin Registrar*/ 
formulario_usuario($_POST);

if ($_POST['submit']=="Actualizar"){
$cod = $_POST['cod'];
require ("../comun/conexion.php");
if(isset($_POST['foto'])){ $dato = ',foto='.$_POST['foto'].',' ; } else {  $dato = ''; }
 /*Instrucción SQL que permite insertar en la BD */ 
$sql = "UPDATE usuario SET ";
$sql.="genero='".$_POST['genero']."',";
if($_FILES['foto']['size']>0){
$total = count($_FILES['foto']['name']);// Contamos la cantidad de 
$total = count($_FILES['foto']['name']);// Contamos la cantidad de   move_uploaded_file($_FILES['foto']['tmp_name'],'../datos/foto/'.$_POST['id_usuario'].".".obtener_extension_archivo($_FILES['foto']['name']));
}

$actualizar_clave = "";
if (isset($_POST['mascota']) and $_POST['mascota']=="SI"){
 $sql.= "mascota='SI'," ;
} else{
  $sql.= "mascota='NO'," ;
}


if ($_POST['cambiar_clave']=="SI")
$actualizar_clave = ", clave='".encriptar($_POST['clave'])."'";
if($_FILES['foto']['tmp_name']<>''){
$sql.= "foto='".$_POST['id_usuario'].".".obtener_extension_archivo($_FILES['foto']['name'])."'," ;
}
$sql.= " tipo_sangre='".$_POST['tipo_sangre']."', id_usuario='".$_POST['id_usuario']."', usuario='".$_POST['usuario']."' ".$actualizar_clave.", nombre='".$_POST['nombre']."', apellido='".$_POST['apellido']."', rol='". implode(",",$_POST['rol'])."',  direccion='".$_POST['direccion']."', telefono='".$_POST['telefono']."', correo='".$_POST['correo']."', estado='".$_POST['estado']."' WHERE  id_usuario = '".$cod."';";

if ($actualizar = $mysqli->query($sql)) {
  require ("../comun/conexion.php");
$sql_id = "SELECT * FROM usuario WHERE id_usuario='".$cod."';";
$user_id = $mysqli->query($sql_id);
if($row_id=$user_id->fetch_assoc());
if($row_id['foto']=="user-icon.png" and $row_id['genero']=="f"){
 $sql="UPDATE `usuario` SET `foto`='user-iconf.png' WHERE `id_usuario`='".$cod."' ";
$mysqli->query($sql);
}
if($row_id['foto']=="user-iconf.png" and $row_id['genero']=="m"){
 $sql="UPDATE `usuario` SET `foto`='user-icon.png' WHERE `id_usuario`='".$cod."' ";
echo $sql;
exit();
 $mysqli->query($sql);
}
@session_start();
?>
<script>alert2('Modificación exitosa');</script>
<meta http-equiv="refresh" content="3; url=usuario.php?u=<?php echo $_POST['u']; ?>" />
<?php
}else{ 
#echo $sql;
?><script>alert2('Modificacion fallida','error');</script>
<meta http-equiv="refresh" content="2; url=usuario.php?u=<?php echo $_POST['u']; ?>" />
<?php
}
} /*fin Actualizar*/ 
}else{ 
?>
<center>
<b><label>Buscar: </label></b><input type="search" id="buscar" onkeyup ="buscar(this.value);" onchange="buscar(this.value);"  style="margin: 15px;">
<b><label>N° de Resultados:</label></b>
<input type="number" min="0" id="numeroresultados_usuario" placeholder="Cantidad de resultados" title="Cantidad de resultados" value="4" onkeyup="grabarcookie('numeroresultados_usuario',this.value) ;buscar(document.getElementById('buscar').value);" mousewheel="grabarcookie('numeroresultados_usuario',this.value);buscar(document.getElementById('buscar').value);" onchange="grabarcookie('numeroresultados_usuario',this.value);buscar(document.getElementById('buscar').value);" size="4" style="width: 40px;">
<button title="Orden Ascendente" onclick="grabarcookie('orderad_usuario','ASC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-up"></span></button><button title="Orden Descendente" onclick="grabarcookie('orderad_usuario','DESC');buscar(document.getElementById('buscar').value);"><span class="  glyphicon glyphicon-arrow-down"></span></button>
</center>
<span id="txtsugerencias">
<?php buscar_usuario(); ?>
</span>
<?php 
}/*fin else if isset cod*/
?>
</center>
<script>
required_en_formulario('form_usuario',"red","*");
password_en_formulario('form_usuario');
var vmenu_usuario = document.getElementById('menu_usuario')
if (vmenu_usuario){
vmenu_usuario.className ='active '+vmenu_usuario.className;
}
</script>
<?php $contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
 ?>
