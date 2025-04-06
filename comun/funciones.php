<?php

if (!file_exists(dirname(__FILE__)."/config.php")) {
if (!isset($_COOKIE['instalacion'])) {//no mover del principo
if (file_exists("instalar/index.php")) header("Location: instalar/index.php");
if (file_exists("../instalar/index.php")) header("Location: ../instalar/index.php");
}
}//no mover del principo
/*Rutas*/



require (dirname(__FILE__)."/funciones.rutas.php");
/*Fin Rutas*/
/*Clases*/
include_once (dirname(__FILE__)."/foro.class.php");
/*Fin Clases*/
@session_start();
if(isset($_POST['valora'])){
  @session_start();
require (dirname(__FILE__)."/conexion.php");
  $sql=' select * from usuario where id_usuario="'.$_POST['valora'].'"  and clave ="'.$_POST['valorb'].'"';
      $consulta = $mysqli->query($sql);
   while ($row = $consulta->fetch_Assoc()){ 
$_SESSION['rol'] = $mysqli->real_escape_string($_POST['rol']);
 $_SESSION['id_usuario'] = $row['id_usuario'];
 $_SESSION['id_institucion']=$_POST['institucion']; 
}
print_r($consulta->num_rows);
}
function cursos_para_padres($id_cat,$id_ano_lectivo,$where){ #lista_cursos para padres
  return $sql= 'SELECT *, usuario.nombre as nombre_docente, usuario.apellido as apellido_docente FROM asignacion, materia ,  ano_lectivo, usuario, inscripcion , categoria_curso where asignacion.id_categoria_curso = categoria_curso.id_categoria_curso and asignacion.id_docente = usuario.id_usuario and 
        inscripcion.id_asignacion = asignacion.id_asignacion and
        inscripcion.id_estudiante = "'.$_SESSION['hijo'].'"  and ano_lectivo.id_ano_lectivo = asignacion.ano_lectivo and materia.id_materia = asignacion.id_curso and  asignacion.visible="SI" and asignacion.ano_lectivo="'.$id_ano_lectivo.'" and asignacion.id_categoria_curso = "'.$id_cat.'"'.$where.'  Order By materia.nombre_materia asc ';
}
function cursos_para_estudiante($id_cat,$id_ano_lectivo,$where){ #lista_cursos para estudiantes
  $sql= 'select *,usuario.apellido as apellido_docente ,usuario.nombre as nombre_docente from inscripcion,asignacion,ano_lectivo,materia,usuario,
categoria_curso
  where
inscripcion.id_asignacion = asignacion.id_asignacion 
and asignacion.id_asignatura = materia_oficial.id_materia 
and   asignacion.id_categoria_curso=categoria_curso.id_categoria_curso and
asignacion.id_docente=usuario.id_usuario and
asignacion.ano_lectivo = ano_lectivo.id_ano_lectivo and
asignacion.id_categoria_curso="'.$id_cat.'"
and id_estudiante="'.$_SESSION['id_usuario'].'" AND
asignacion.ano_lectivo="'.$id_ano_lectivo.'"';
return $sql;
}
function inscripciones_estudiantes(){
require 'conexion.php';
return $sql ='SELECT * FROM `inscripcion`INNER join asignacion on asignacion.id_asignacion = inscripcion.id_asignacion INNER join materia on asignacion.id_asignatura =materia.id_materia INNER JOIN usuario on usuario.id_usuario = asignacion.id_docente WHERE
asignacion.visible="SI" and
 `id_estudiante` ="'.$_SESSION['id_usuario'].'"  ';
}
function verificacion_evaluacion_docente(){ 
require_once 'conexion.php';
 $sqlactivo='SELECT `config`.`id_config`, `config`.`fecha_inicio`, `config`.`hora_inicio`, `config`.`ano`, `config`.`fecha_fin`, `config`.`hora_fin`, `config`.`docente`, `config`.`estado` FROM `config` where estado="1"';
$consulta_eval_activo = $mysqli -> query ($sqlactivo);
 $disponibilidad =  $consulta_eval_activo -> num_rows;
if($disponibilidad>0){
$sql=inscripciones_estudiantes();
$consulta = $mysqli -> query ($sql);
$cantidad_inscripciones =  $consulta -> num_rows;
   while ($row = $consulta->fetch_Assoc()){ 
$sql1=estado_votacion_materia($row['id_asignacion']);
$consulta1 = $mysqli -> query ($sql1);
$cantidad_llenos =  $consulta1 -> num_rows;
}
$materias_pendientes= $cantidad_inscripciones-$cantidad_llenos;
}else{
  //return 
  $materias_pendientes= $disponibilidad;
}
if(isset($_SESSION['rol']) and $_SESSION['rol']=="estudiante" ){
    if($materias_pendientes>0){
    header("location:evaldocente/php/evaluacion.php");
  }
}

}

function estado_votacion_materia($id_asignacion){
return $sql1="SELECT * FROM `respuesta_estudiante` WHERE `id_estudiante` = '".$_SESSION['id_usuario']."' and `id_asignacion` = '".$id_asignacion."' ";
}


function cursos_para_docente($id_cat,$id_ano_lectivo,$where){ #lista_cursos para padres
  @session_start();
  #$_SESSION['id_institucion']=6; //pilas borrar
  if(!isset($_SESSION['id_institucion'])){
   // $_SESSION['id_institucion']=6;
  }
return $sql= 'SELECT *, usuario.nombre as nombre_docente, usuario.apellido as apellido_docente FROM asignacion, materia_oficial ,  ano_lectivo, usuario,  categoria_curso where asignacion.id_categoria_curso = categoria_curso.id_categoria_curso and asignacion.id_docente = usuario.id_usuario and ano_lectivo.id_ano_lectivo = asignacion.ano_lectivo and materia_oficial.id_materia = asignacion.id_curso and asignacion.ano_lectivo="'.$id_ano_lectivo.'" and asignacion.id_categoria_curso = "'.$id_cat.'" 
and asignacion.institucion_educativa = "'.$_SESSION['id_institucion'].'"
and asignacion.id_docente = "'.$_SESSION['id_usuario'].'"'.$where.'  Order By materia_oficial.nombre_materia asc ';
#echo $sql;

}

function cursos_para_admin($id_cat,$id_ano_lectivo,$where){ #lista_cursos para admin

  $sql= 'SELECT *, usuario.nombre as nombre_docente, usuario.apellido as apellido_docente FROM asignacion, materia_oficial ,  ano_lectivo, usuario,categoria_curso where asignacion.id_categoria_curso = categoria_curso.id_categoria_curso and asignacion.id_docente = usuario.id_usuario and  ano_lectivo.id_ano_lectivo = asignacion.ano_lectivo and materia_oficial.id_materia = asignacion.id_curso and asignacion.ano_lectivo="'.$id_ano_lectivo.'" 
and asignacion.institucion_educativa = "'.$_SESSION['id_institucion'].'" and asignacion.id_categoria_curso = "'.$id_cat.'"'.$where.'  Order By materia_oficial.nombre_materia asc ';
#echo $sql;
return $sql;
}
 function consultar_categoria_curso(){
  require ("../comun/conexion.php");
  $sqlc="SELECT * FROM `categoria_curso` ";
 if($_SESSION['rol']=='docente'){
require_once ("../comun/funciones.php");
$categorias_docente = consultar_categoria_docente();
   if ($_SESSION['rol']=="acudiente" or $_SESSION['rol']=="estudiante") {
      if (isset($_SESSION['id_categoria_curso'])) {
#      $sqlc.= ' where id_categoria_curso = "'.$_SESSION['id_categoria_curso'].'" ' ; 
      }
      }
  $sqlc.=' order by id_categoria_curso desc';
  }
#echo $sqlc;
$consultac = $mysqli -> query($sqlc);
while ($rowc = $consultac ->fetch_assoc()){
    $categorias[$rowc['id_categoria_curso']]=$rowc['nombre_categoria_curso'];
}
return $categorias;
}
function verificar_horario($asignacion){
   require dirname(__FILE__).'/conexion.php';
 $sql='select SELECT * FROM `horario` WHERE `fecha_inicio` > "'.date('Y-m-d').'" and `fecha_fin` < "'.date('Y-m-d').'"
and id_asignacion="'.$asignacion.'" ';
if(empty(consultar_datos2($sql)[0])){
return 0;
}else{
  return 1;
}



}



function inscribir_estudiante_ano_lectivo($estudiante,$categoria,$ano_lectivo){
    require dirname(__FILE__).'/conexion.php';
$inscripcion ='INSERT INTO `inscripcion`(`id_estudiante`, `id_asignacion`, `fecha_inscripcion`) select "'.$estudiante.'",id_asignacion,"'.date('Y-m-d').'" from asignacion where id_categoria_curso = "'.$categoria.'" and ano_lectivo = "'.$ano_lectivo.'";';  
$insertar = $mysqli->query($inscripcion);
}
function obtener_extension_archivo($archivo){
  $extensión_archivo = (pathinfo($archivo,PATHINFO_EXTENSION)); 
  return $extensión_archivo;
}

function cerrar_session ($redireccionar=false){
    @session_start();
    unset ($_COOKIE['repetidas']);
    unset ($_SESSION['clave']);
    unset ($_SESSION['id_usuario']);
    unset ($_SESSION['usuario']);
    unset ($_SESSION['foto']);
    unset ($_SESSION['rol']);
    unset ($_SESSION['roles']);
    unset ($_SESSION['num_visitas']);
    unset ($_SESSION['puntos']);
    session_destroy();
    if($redireccionar==true){
    header("Location: ../index.php");  
    }
}


function cantidad_estudiantes_Categoria($categoria){
    require(dirname(__FILE__)."/conexion.php");
$sql='select count( distinct `id_estudiante`) from inscripcion, asignacion where inscripcion.id_asignacion = asignacion.id_asignacion and asignacion.id_categoria_curso="'.$categoria.'" ';
return consultar_datos2($sql)[0];
}


function consultar_datos2_usuario($usuario){
  if($usuario<>""){
   require_once(dirname(__FILE__)."/config.php");
    require(dirname(__FILE__)."/conexion.php");
    $roles = "";
    $resultado="user-icon.png";
    $sql = "SELECT * FROM `usuario` WHERE `usuario` = '$usuario' or `id_usuario` = '$usuario'";
    $consulta = $mysqli->query($sql);
    $roles = array();
    $roles2 = array();
$array_roles = array("admin"=>"Administrador","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente");
//if ($mysqli->num_rows>0){
    if ($row=$consulta->fetch_assoc()){
    $roles = explode(",",$row['rol']);
    if (count($roles)>0)
    foreach ($roles as $id => $rol){
    $roles2[$rol] = $array_roles[$rol];
    }

    $resultado = $row['foto'];
    if ($row['foto'] !=''){
    $resultado = $row['foto'];
    }else{
    $resultado = "user-icon.png";
    }
    }
    
    $resultado = $resultado!="" ? $resultado : "user-icon.png";
    $mifoto = "/foto/".$resultado;
    $ruta_foto = READFILE_URL.$mifoto;//

 if($row['id_usuario'] != "" and $row['mascota']=='SI'){
 $sql_figura = 'select * from figuras where sha1(concat(figura,"SGA"))="'.$row['clave'].'" ' ;
 
  $consulta_figura = $mysqli->query($sql_figura);
    if($row_figura = $consulta_figura->fetch_assoc()){
 $array22[]    = array("id_figuras" => $row_figura['figura'] ,"figura"=>$row_figura['figura'],"imagen_figura" =>$row_figura['imagen_figura']);
     }
$sql_mascotas ='select * from figuras ' ; 
if(isset($row['clave']) and $row['clave'] <> ""){
$sql_mascotas.='where sha1(concat(figura,"SGA"))<> "'.$row['clave'].'" ' ;
}
$sql_mascotas.='ORDER BY figura limit 4 '; 
$consulta_mascotas = $mysqli ->query($sql_mascotas);
while ($row_mascotas = $consulta_mascotas->fetch_assoc()){ 
$array22[]=$row_mascotas;
}
shuffle($array22);
$contador = 0;
foreach ($array22 as $clave =>$llave) {
$contador++;
@session_start();  
@$nombre_mascota = $row['figura'];
#echo "figura".$llave['figura'].' clave'.$row['clave'].' clave2 '.encriptar($llave['figura']);
if($row['clave']==encriptar($llave['figura'])){
  echo $llave['figura'].'->'.$row['clave'].'<br>';
    $datainfo = $row['clave'];
}
else{
    $datainfo = sha1(uniqid());
   
}
$img[$contador] = " <img class='mascotas_login' title='".$llave['figura']."' style='border-radius:120px' id='mascota_".$contador." height='100px' width='120px' name='' onclick='login_para_boy(this); ";
$img[$contador].="' src='../comun/img/figuras/".$llave['imagen_figura']."' data-info='".$datainfo."' hola></img>";
 }
 }
$datos = array('clave'=>$row['clave'],'nombre'=>$row['nombre'],'apellido'=>$row['apellido'], 'roles'=>$roles2, 'foto'=>$ruta_foto
, 'mascota'=>$row['mascota'],'usuario'=>$row['usuario']);

 if(empty($array22)) {
$array_resultante= array_merge($datos);  
 }else{
  $array_resultante= array_merge($datos,$array22,$img);
 }
 if(empty($img)) {
$array_resultante= array_merge($datos);  
 }else{
  $array_resultante= array_merge($datos,$array22,$img);
 }

     return  $array_resultante;   
}
}
/**
 * Fin Funciones Login
 **/

/**
 *  Modulo Red
 **/

/*Include Funciones generales*/
/*Insertar*/

function escape_string(&$elemento1, $clave)
{
    require("conexion.php");
    $elemento1 = $mysqli->real_escape_string($elemento1);
}
function insertar($array,$tabla,$update = false,$where = ""){
quitar_vacios($array);

$sql = "";
if (count($array)>0){
//array_walk($array, 'escape_string');
if (isset($array['clave']) and $array['clave']!="")
$array['clave']=encriptar($array['clave']);
$columns = implode("`, `",array_keys($array));
$escaped_values = array_values($array);
$values  = implode("', '", $escaped_values);

$array_values = values_columnas($array);
$value_columns = implode(", ",array_values($array_values));
$sql = "INSERT INTO `$tabla`(`$columns`) VALUES ('$values') $where ";
if($update) $sql .=" ON DUPLICATE KEY UPDATE $value_columns;";
}
return $sql;
}
function quitar_vacios(&$array)
{
  foreach($array as $id=>$valor){
    if ($valor=="") unset($array[$id]);
  }
}
function values_columnas($array)
{
    foreach ($array as $id => $value){
        $array[$id]= " `".$id."` = VALUES(".$id.")";
    }
  return($array);
}
/*Insertar*/
/*Fechas*/

function formatofecha($fecha){
$meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e');
$fecha2 = date("d \\d\\e ".$meses[date("n",strtotime($fecha))]."  \\d\\e\\l \\a\\ñ\\o Y ",strtotime($fecha));
return $fecha2;
}
function formatofecha2($fecha){
$date_unix = strtotime($fecha);
$meses = array ("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$date = date("d",$date_unix);
$date .= " de ";
$mes = date("n",$date_unix);
$date .= $meses[$mes];
$date .= " del a&ntilde;o ";
$date .= date("Y",$date_unix);
return $date;
}
function formatofechayhora($fecha){
$date_unix = strtotime($fecha);
$meses = array ("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$date = date("d",$date_unix);
$date .= " de ";
$mes = date("n",$date_unix);
$date .= $meses[$mes];
$date .= " del a&ntilde;o ";
$date .= date("Y",$date_unix);
$date .= "<br> a las ";
$date .= date(" h:i:s a",$date_unix);
return $date;
}
function formatohora($hora){
$time_unix = strtotime($hora);
$time = date("h:i:s a",$time_unix);
return $time;
}
function formatohoracorta($hora){
$time_unix = strtotime($hora);
$time = date("h:i a",$time_unix);
return $time;
}
function diferenciaentrefechas($fecha_inicial,$fecha_final) {
date_default_timezone_set('America/Bogota'); 
if ($fecha_inicial>$fecha_final) {
$año = 0;
$mes = 0;
$dia = 0;
       return array ($dia, $mes,$año);
       exit();

}     
$fecha_inicial = new DateTime($fecha_inicial);
$fecha_final = new DateTime($fecha_final);
$intervalo = $fecha_final->diff($fecha_inicial);
$año = $intervalo->format('%y');
$mes = $intervalo->format('%m');
$dia = $intervalo->format('%R%a');;
//$mifecha = $intervalo->format('%y años, %m mes, %d días');
   return array ($dia, $mes,$año);
}
/*Fin Fechas*/

/*Fin Include Funciones generales*/
/**
 * Funciones Generales
**
 *  Funciones Login
 **/
if (isset($_POST['fn_login_guagua'])){ //login para niños
    @session_start();
require 'conexion.php';
    $usuario = $mysqli->real_escape_string($_POST['usuario']);
    $clave = $mysqli->real_escape_string($_POST['clave']);
  $sql = "SELECT * from usuario WHERE `clave` = '$clave' and `usuario` = '$usuario' and estado='activo'  ";
   $consulta = $mysqli->query($sql);
   if ($row=$consulta->fetch_assoc()){
     $ultima_sesion_f = formatofecha($row['ultima_sesion']);
       $hoy = date("Y-m-d H:i:s");
       if ($ultima_sesion_f!= formatofecha($hoy)){
       $sql_visitas = "UPDATE `usuario` SET `num_visitas`=`num_visitas`+1 , `puntos`=`puntos`+1 WHERE `id_usuario` = '".$row['id_usuario']."'";
       $consulta_visitas = $mysqli->query($sql_visitas);
       }
       $sql_ultima_sesion = "UPDATE `usuario` SET `ultima_sesion`='".$hoy."' WHERE `id_usuario` = '".$row['id_usuario']."'";
       $consulta_ultima_sesion = $mysqli->query($sql_ultima_sesion);
@session_start();
       $_SESSION['clave'] = $row['clave'];
       $_SESSION['id_usuario'] = $row['id_usuario'];
       $_SESSION['usuario'] = $row['usuario'];
       $_SESSION['nombre_usu'] = $row['nombre']." ".$row['apellido'];
       $_SESSION['nombre'] = $row['nombre'];
       $_SESSION['apellido'] = $row['apellido'];
       $_SESSION['foto'] = $row['foto'];
       $misroles = explode(",",$row['rol']);
     
    if(in_array($_POST['rol'],$misroles)){
          $_SESSION['rol'] = $mysqli->real_escape_string($_POST['rol']);
       }
       else{
          $_SESSION['rol'] = $misroles[0];

       }
       $_SESSION['roles'] = $row['rol'];
       $ruta = $_SERVER['DOCUMENT_ROOT']."/../sga-data/foto/".$row['foto'];
       //recordarme
if (isset($_POST['recordarme']) and $_POST['recordarme']=="SI"){
$cookie_name = "usuarios[".$row['id_usuario']."]";
$cookie_value = $row['usuario'];
setcookie($cookie_name, $cookie_value, time() + (86400 * 365), "/"); // 86400 = 1 dia
}
//fin recordarme
       $ext1 = explode(".",$ruta);
       $ext = end($ext1);
       if (file_exists($ruta)){
       $ruta_tmp = "login.$ext";
       move_uploaded_file($ruta,$ruta_tmp);
       }
       $_SESSION['num_visitas'] = $row['num_visitas'];//+1;
       $_SESSION['puntos'] = $row['puntos'];
      if ($_SESSION['rol']=="estudiante"){
         $sql_estudiante = "SELECT * from usuario WHERE id_usuario = '".$_SESSION['id_usuario']."'";
         $consulta_estudiante = $mysqli->query($sql_estudiante);
        if ($row_estudiante= $consulta_estudiante->fetch_assoc()){
                $_SESSION['id_categoria_curso'] = $row_estudiante['id_categoria_curso'];
        }
       }
   }
   $numrow = $consulta->num_rows;
   echo $numrow;//muestra el resultado de la consulta 0 o 1  
}
function nivel_educativo_de_estudiante($estudiante,$ano_lectivo){
        require 'conexion.php';
   $sql='Select * from  inscripcion,categoria_curso,asignacion where 
inscripcion.id_asignacion =
asignacion.id_asignacion and
categoria_curso.id_categoria_curso = categoria_curso.id_categoria_curso and
inscripcion.id_estudiante = "'.$estudiante.'" and Year(inscripcion.fecha_inscripcion) = "'.$ano_lectivo.'" '; 
#echo $sql;
$consulta_sql = $mysqli ->query($sql);
while($rowsql= $consulta_sql ->fetch_assoc()){
        return $rowsql['nivel_educativo']; 
    }
}




function categoria_de_estudiante($estudiante,$ano_lectivo){
        require 'conexion.php';
   $sql='Select * from  inscripcion,categoria_curso,asignacion where 
inscripcion.id_asignacion =
asignacion.id_asignacion and
categoria_curso.id_categoria_curso = categoria_curso.id_categoria_curso and
 asignacion.institucion_educativa ="'.$_SESSION['institucion'].'"
and inscripcion.id_estudiante = "'.$estudiante.'" and Year(inscripcion.fecha_inscripcion) = "'.$ano_lectivo.'" '; 
$consulta_sql = $mysqli ->query($sql);
while($rowsql= $consulta_sql ->fetch_assoc()){
        return $rowsql['nombre_categoria_curso']; 
    }
}


function asignaciones_de_un_docente($docente){
   require 'conexion.php';
    $listado_asignaciones=array();
     $sql_docente ='select * from asignacion where id_docente ="'.$docente.'"';
    $consulta_docente=$mysqli->query($sql_docente);
    while($row_docente=$consulta_docente->fetch_assoc()){
    $listado_asignaciones[]=$row_docente['id_asignacion'];
    }
return  $listado_asignaciones;
    
}


function soloNumeros($laCadena) {
    $carsValidos = "0123456789";
    for ($i=0; $i<strlen($laCadena); $i++) {
      if (strpos($carsValidos, substr($laCadena,$i,1))===false) {
         return false; 
      }
    }
    return true; 
}


function getRealIP() { //obitiene la ip real apresar de usar proxy
if (!empty($_SERVER[‘HTTP_CLIENT_IP’]))
return $_SERVER[‘HTTP_CLIENT_IP’];
if (!empty($_SERVER[‘HTTP_X_FORWARDED_FOR’]))
return $_SERVER[‘HTTP_X_FORWARDED_FOR’];
return $_SERVER[‘REMOTE_ADDR’];
}
function encriptar($valor,$clave="SGA"){
  $valor=strtolower($valor);
    return sha1($valor.$clave);
}
function deletrear($palabra){
    $salida = "";
    $palabra = mb_convert_encoding($palabra, "UTF-8", mb_detect_encoding($palabra));
    //mb_decode_utf8();
    for ($i=0;$i<strlen($palabra);$i++){
      
       #$salida.= "<span>".mb_convert_encoding($palabra[$i], "UTF-8", mb_detect_encoding($palabra[$i]))."</span>";
    }
    return $salida;
}
function importarjscss(){
require_once((dirname(__FILE__))."/config.php");
$javascript = glob((dirname(__FILE__))."/js/*.js");
foreach ($javascript as $archivojs){
$archivojs = str_replace((dirname(__FILE__)),"",$archivojs);
?><script src="<?php echo SGA_COMUN_URL."/".$archivojs ?>"></script><?php
}
$javascript = glob((dirname(__FILE__))."/css/*.css");
foreach ($javascript as $archivojs){
$archivojs = str_replace((dirname(__FILE__)),"",$archivojs);
?><link rel="stylesheet" href="<?php echo SGA_COMUN_URL."/".$archivojs ?>"><?php
}
}
function mostrarSugerenciaiconos(){
  ?><ul class="bs-glyphicons"><?php
    require_once ("../comun/config.php");
    require_once ("../comun/lib/Zebra_Pagination/Zebra_Pagination.php");
    $paginacion = new Zebra_Pagination();
    $valorBusqueda = $_POST['valorBusqueda'];
    $resultados_por_página = 4; 
$cookiepage = 'page_red';//cookie para mandar el parametro de la página
$paginacion->cookie_page($cookiepage);//requerida para que se envíe el parametro en la paginacion
    $paginacion->fn_js_page('mostrarSugerenciaiconos();');//funcion para buscar despues de pasar la pagina
    $paginacion->records_per_page($resultados_por_página);
    $paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"]; 
  $encontrados = glob("../comun/img/icono_curso/*.png");
  if ($valorBusqueda!=""){
  foreach ($encontrados as $id => $nombre){
  if (preg_match('/'.$valorBusqueda.'/',$nombre,$coincidencias)){
  
  }else{
    unset($encontrados[$id]);
  }
  }
  }
  $total_resultados = count($encontrados);
  $paginacion->records($total_resultados);
foreach(array_slice($encontrados, $paginacion->get_page() - 1, $resultados_por_página) as $archivo){ #delimitamos la parte del array que queremos mostrar con array_slice
  $nombre = str_replace(".png","",$archivo);
  $nombre = str_replace("../comun/img/icono_curso/","",$nombre);

  ?><li><span  style="margin-left: -5;background-size: 40px 40px;margin-top: 40px;" onclick="selecciona_icono(this);"  class="icon-sga-<?php echo $nombre; ?>"   ></span><?php echo $nombre ; ?></li><?php } 
  ?></ul><?php echo $paginacion->render2();
}
/*
* Representación en archivo de Modelos, Vista, Controladores
* Representación de Rutas
*/

##### Función para eliminar un RED

function comprobar_red($red){
require 'conexion.php';
  $sql_red = 'select * from red where id_red= "'.$red.'"';
$consulta_red = $mysqli ->query($sql_red);
$datos=array();
while($rowred = $consulta_red -> fetch_assoc()){
  if(!empty($rowred['enlace'])){
$datos['ruta_red']=$rowred['enlace'];
  }
$datos['formato']=$rowred['formato'];
$datos['titulo_red']=$rowred['titulo_red'];
$datos['scorm']=$rowred['scorm'];
}
return $datos;
}

if(isset($_GET['ruta_red'])){

if(is_numeric($_GET['ruta_red'])){
$_GET = comprobar_red($_GET['ruta_red']); 
}
  descargar($_GET['ruta_red'],$_GET['formato'],$_GET['titulo_red'],$_GET['scorm']);
}
/**
 * Fin Modulo Red
 **/
 
/**
 *  Modulo Cursos
 **/
function docente_en_asignacion($asignacion){
 require 'conexion.php';
    $sql_docente ='select * from usuario , asignacion where 
asignacion.id_docente =usuario.id_usuario and asignacion.id_asignacion = "'.$asignacion.'" ';
$consulta_docente = $mysqli ->query($sql_docente);
while($rowdocente = $consulta_docente -> fetch_assoc()){
   return  strtoupper($rowdocente['nombre'].' '.$rowdocente['apellido']); 
}
}
 function fn_clonar_curso($asignacion) {
require 'conexion.php';
require_once 'config.php';
/*consultar id materia de asignacion*/
 $sql_materia ='SELECT `id_asignatura` FROM `asignacion` WHERE id_asignacion = "'.$asignacion.'"' ;
$consulta_materia = $mysqli -> query($sql_materia);
if ($row_materia = $consulta_materia ->fetch_assoc())

 $sql0 ='SELECT  * FROM `actividad` WHERE id_asignacion = "'.$asignacion.'"' ;
$consulta0 = $mysqli -> query($sql0);
$arraydeactividades = array() ;
while ($row0 = $consulta0 ->fetch_assoc()){
   $arraydeactividades[] = array(
    'id_actividad' => $row0['id_actividad']
    );
}

$sql='INSERT INTO `asignacion`( `id_docente`,`id_curso`, `id_asignatura`, `descripcion`, `id_categoria_curso`, `ano_lectivo`,`visible`,`portada_asignacion`)SELECT  `id_docente`, "'.$row_materia['id_asignatura'].'","'.$row_materia['id_asignatura'].'", `descripcion`, `id_categoria_curso`, `ano_lectivo`,`visible`,`portada_asignacion` FROM `asignacion` WHERE id_asignacion = "'.$asignacion.'"';

if($consulta = $mysqli->query($sql)){
$nueva_asignacion = $mysqli ->insert_id;
 $sql2= 'INSERT INTO `actividad`( `id_asignacion`, `fecha_publicacion`, `hora_publicacion`, `id_red`, `nombre_actividad`, `Observaciones`, `etiqueta`, `evaluable`, `fecha_entrega`, `hora_entrega`, `periodo`) SELECT  `id_asignacion`, `fecha_publicacion`, `hora_publicacion`, `id_red`, `nombre_actividad`, `Observaciones`, `etiqueta`, `evaluable`, `fecha_entrega`, `hora_entrega`, `periodo` ,`visible`,`cuestionario`,`id_cuestionario`,`foro`,`id_foro`  FROM `actividad` WHERE id_asignacion = "'.$asignacion.'"' ;
}
$consulta2 = $mysqli -> query ($sql2);
$nuevo_actividad = $mysqli ->insert_id;
 $sql3 ='UPDATE `actividad` SET `id_asignacion`="'.$nueva_asignacion.'" WHERE id_asignacion = "'.$asignacion.'" and ';

foreach ($arraydeactividades as $valores){
        if ($valores == end($arraydeactividades)) {
$sql3.= 'id_actividad <> "'.$valores['id_actividad'].'" ' ;

}
else{
 $sql3.= 'id_actividad <> "'.$valores['id_actividad'].'" and ' ;

}
    
}
$consulta3 = $mysqli->query($sql3);
require 'conexion.php';
$sql4 ='SELECT  * FROM `inscripcion` WHERE id_asignacion = "'.$asignacion.'"' ;
$consulta4 = $mysqli -> query($sql4);
$arraydeinscritos = "" ;
while ($row4 = $consulta4 ->fetch_assoc()){
   $arraydeinscritos[] = array(
    'id_inscripcion' => $row4['id_inscripcion']
    );
}

/*
$sql5 = 'INSERT INTO `inscripcion`( `id_estudiante`, `id_asignacion`, `fecha_inscripcion`)
SELECT  `id_estudiante`, `id_asignacion`, `fecha_inscripcion` FROM `inscripcion` WHERE id_asignacion = "'.$asignacion.'"';
if ($consulta5 = $mysqli -> query ($sql5)){
    $sql3 ='UPDATE `inscripcion` SET `id_asignacion`="'.$nueva_asignacion.'" WHERE id_asignacion = "'.$asignacion.'" and ';
if(!empty($arraydeinscritos)){
foreach ($arraydeinscritos as $valores){
        if ($valores == end($arraydeinscritos)) {
$sql3.= 'id_inscripcion <> "'.$valores['id_inscripcion'].'" ' ;

}else{
 $sql3.= 'id_inscripcion <> "'.$valores['id_inscripcion'].'" and ' ;
}
}
}//fin if

if ($consulta3 = $mysqli->query($sql3)){
    return 1;
    }else{
    return 0 ;
}
}
*/
} # Fin fn_clonar_curso
/**
 *  Fin Modulo Cursos
 **/
/**
 * Modulo de Foros
 * Funciones, Rutas y Vistas
*/
function span_fav_entradas($entrada_id, $estrellas_ent_json){
    @session_start();//echo $_SESSION['id_usuario'];
    $likes_entradas = json_decode($estrellas_ent_json,true);
  $total_likes_entradas = count($likes_entradas);
  $s = "s";
  $s2 = "";
  if ($total_likes_entradas>2) $s2 = "s";
    if ($total_likes_entradas==1) $s = "";
    
    if ($total_likes_entradas>0){
    }
    if ($total_likes_entradas!=0 and in_array($_SESSION['id_usuario'],$likes_entradas)){
    $texto_span = "Ya no Me Gusta";
    $class="me_gusta";
    $estado = "NO";
    if ($total_likes_entradas>1){
    $com_a_like = "tú y ";
    $com_b_like = "más ";
    $total_likes_entradas--;
    }else if ($total_likes_entradas==1){
    $com_a_like = "tí";
    $com_b_like = "";
    }else{
    $com_a_like = "";
    $com_b_like = "";
    }
    }else{
    $texto_span = "Me Gusta";
    $class="";
    $estado = "SI";
    $com_a_like = "";
    $com_b_like = "";
    }
    $quienes_arr = array();
    $quienes = "";
    if ($total_likes_entradas>0)
    foreach ($likes_entradas as $id => $valor){
    $quienes_arr[] = consultar_nombre($id);
    }else{
        $quienes_arr[] = "Ninguna";
    }
    $quienes = implode(",",$quienes_arr);
  ?>
  <span id="area_like_<?php echo $entrada_id; ?>">
  <button style="float: left;" title="a <?php 
  if ($com_a_like != "tí" and $total_likes_entradas==0) echo "Nadie";//se el primero
  else echo $total_likes_entradas;
  if ($com_a_like != "tí" and $com_a_like!=0){
  echo $total_likes_entradas;
  if ($total_likes_entradas==0){ 
    echo "";
  }else{ echo "Persona";
  echo $s2;
  }
  ?> <?php echo $com_b_like ?>le<?php echo $s2;
  }else if ($com_a_like==0){ 
      echo " le";
  }else{
      echo " te";
  }
  ?> gusta este tema" id="btn_like_ent_<?php echo $entrada_id; ?>" estado="<?php echo $estado ?>" <?php if (isset($_SESSION['id_usuario'])){ ?> onclick="fav_ent('<?php echo $entrada_id; ?>','btn_like_ent_<?php echo $entrada_id; ?>')" <?php }else{ ?> onclick="alert3('Para indicar que te gusta, debes identificarte','warning')" <?php } ?> class="btn btn-primary btn-sm <?php echo $class ?>" id="like_usu_<?php echo $entrada_id; ?>"><span style="margin-left: 0px;"class="icon-sga-like"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span id="txt_span_entrada_<?php echo $entrada_id; ?>">&nbsp;<?php echo $texto_span ?>&nbsp;<span class="badge"><?php echo count($likes_entradas); ?></span></span></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span title="<?php echo $quienes ?>" id="total_likes_<?php echo $entrada_id; ?>"></span>
  </span>
  <?php
}
function mitop5($grupo){
    $foro = new foro();
$top5 = $foro->top5($grupo);
#print_r($top5);
if (count($top5)>0){
foreach ($top5 as $id => $row_entrada){
?><a href="#t<?php echo $row_entrada['id']; ?>"><span><img style="border-radius:5px" height="18" src="<?php echo READFILE_URL."/foto/".validarfoto($row_entrada['foto']) ?>" ></span>&nbsp;&nbsp;<?php echo $row_entrada['contenido'] ?>&nbsp;&nbsp;<span class="badge badge_amarillo_azul"><?php echo $row_entrada['num_comentarios'] ?></span></a><br>
<?php }
}else{
  echo "No hay temas destacados en este Foro";
}
}
function mis_foros($buscar="",$contexto="general"){
?>    
<ul class="bs-glyphicons">
<?php
$foro = new foro();
$mis_foros = $foro->consultar_mis_foros($buscar,$contexto);
#print_r($mis_foros);
foreach($mis_foros as $id => $mi_foro){
?>
<li style="margin:5px;background-image: url('<?php echo SGA_URL ?>/comun/img/png/<?php echo $mi_foro['imagen_icono']?>');
    background-size: 100% 100%;background-color: rgba(255,255,255,.4);
    background-blend-mode: overlay;" class="li_grupo_foro" id_grupo_foro="<?php echo $mi_foro['id_grupo_foro']?>" nombre_grupo_foro="<?php echo $mi_foro['nombre_grupo']?>" onclick="grabarcookie('foro_actual',<?php echo $mi_foro['id_grupo_foro']?>);listar_temas(<?php echo $mi_foro['id_grupo_foro']?>);" title="Hay <?php echo $mi_foro['num_entradas']; ?> Temas en este foro"><span style="font-size:18"><?php echo $mi_foro['nombre_grupo']?></span>&nbsp;&nbsp;<span class="badge"><?php echo $mi_foro['num_entradas']; ?></span></li>
<?php
}
?>
</ul>
<?php
}
if(isset($_GET['mis_foros'])){
    ob_clean();
    mis_foros($_POST['buscar'],$_POST['contexto']);
    exit();
}
if(isset($_GET['mitop5'])){
    ob_clean();
    mitop5($_POST['grupo']);
    exit();
}

if(isset($_GET['listar_entradas'])){
    @session_start();
    require_once("config.php");
  if ($_GET['listar_entradas']!=""){
  $foro = new foro();
  $datos_foro=$foro->datos_foro($_GET['listar_entradas']);
  $entradas = $foro->listar_entradas($_GET['listar_entradas']);
  if(count($datos_foro)==0) exit();
  $temas_nombre = ": ".$datos_foro['nombre_grupo'];
  if ($datos_foro['contexto']=="general"){
  echo "<h3 id='titulo'>".deletrear("Temas en foro".$temas_nombre)."</h3>";
  ?>
    <div class="panel panel-default" style=";border-radius: 20px;box-shadow: #6E9BAE 4px 4px 18px 3px;">
      <div class="panel-header" style="margin: 0px 25px;text-align:left">
      <!--h3>Proponer un Tema</h3-->
      </div>
      <div class="panel-body2" style="margin: 0px 25px;">
      <span class="form-group">
      <br/>
      <style type="text/css">
          ::-webkit-textarea-placeholder { font-size:25px;!important; }
      </style>
      <?php 
      require '../comun/autoload.php';
      $persona=new Persona($_SESSION['id_usuario']); ?>
      <textarea style=";border-radius:8px;box-shadow: #6E9BAE 1px 1px 2px 1px;" placeholder="<?php 
       echo $persona->nombre.' '.$persona->apellido 
      ?> propón una tema " class="form-control emojionearea" emojionearea="<?php echo $_GET['listar_entradas']; ?>" id="contenido_<?php echo $_GET['listar_entradas']; ?>"></textarea>
      <p id="contenido_<?php echo $_GET['listar_entradas']; ?>_emojiareas"></p>
      </span>
      </div>
      <div class="panel-footer" style="margin: 0px 25px;text-align:right;background-color: transparent;
    border-top: 1px solid transparent;">
          <?php if (isset($_SESSION['nombre'])){ ?>
      <button type="button" class="btn btn-info" onclick="guardar_entrada(<?php echo $_GET['listar_entradas']; ?>)">Publicar</button>
      <?php }else{ ?>
      <button type="button" title="Para publicar un tema debes identificarte" class="btn btn-info" onclick="document.location.href='<?php echo SGA_USUARIO_URL ?>/login.php'">Identificarse</button>
      <?php } ?>
      </div>
    </div>
  <?php
  }//fin if $ent['contexto']
  foreach ($entradas as $id => $entrada) {
    if ($entrada['num_comentarios']=="") $entrada['num_comentarios']=0;
      ?>
      <a name="t<?php echo $entrada['id']?>"></a>
      <span id='txt_comentarios_<?php echo $entrada['id']?>'>
     <?php echo area_entrada($entrada); ?>
  <h2 onclick='listar_comentarios("<?php echo $entrada['id']?>")'>Comentarios  <span class='badge badge_amarillo_azul'><?php echo $entrada['num_comentarios'] ?></span></h2>
  <div id="area_nuevo_comentario">
      <textarea style="border-radius: 8px;box-shadow: #6E9BAE 1px 1px 2px 1px;margin-bottom:5px" class="form-control" id="comentario_<?php echo $entrada['id']; ?>"></textarea>
      <div class="panel-footer" style="margin: 0px 25px;text-align:right;background-color: transparent;
    border-top: 1px solid transparent;">
      <button type="button" class="btn btn-info" onclick="guardar_comentario(<?php echo $entrada['id']; ?>)">Comentar</button>
      </div>
  </div>
  <?php if ($entrada['num_comentarios']==0){ ?>
  Aún no hay comentarios
  <?php }else{ ?>
  <button type="button" style="margin-bottom:20px;" class="btn btn-info"  onclick='listar_comentarios("<?php echo $entrada['id']?>")'>Ver Comentarios</button>
      <?php } ?>
  </div>
  </span><br><br>
  <?php
  }
  }
  exit();
}
function area_entrada($row_entrada){
    ?>
<?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == "admin"){ ?>
<span class="close2 tema" style="color:#d9534f;margin-right:35px;margin-top:5px;float:right;z-index:100;">
<?php if ($row_entrada['estado']=="Publicado"){ ?>
<span style="z-index:100;width:40px;height:40px;background-size: 40px 40px;" onclick="ver_tema(this);" id="ver_tema_<?php echo $row_entrada['id']; ?>" id_tema="<?php echo $row_entrada['id']; ?>" visible="Publicado" class="icon-sga-view" title="Ocultar"></span>
<?php }elseif ($row_entrada['estado']=="Desactivado"){ ?>
<span style="z-index:100;width:40px;height:40px;background-size: 40px 40px;" onclick="ver_tema(this);" id="ver_tema_<?php echo $row_entrada['id']; ?>" id_tema="<?php echo $row_entrada['id']; ?>" visible="Desactivado" class="icon-sga-view-line" title="Mostrar"></span>
<?php } ?>
</span>
<?php } ?>
      <div style=";border-radius: 20px;box-shadow: #6E9BAE 4px 4px 18px 3px;" id="areatema_<?php echo $row_entrada['id']; ?>" class="panel panel-default foro <?php
if ($row_entrada['estado']=="Desactivado"){
    echo 'areatema_oculta';
}
?>">
            <div  onclick="toogle_entradas('<?php echo $row_entrada['id']; ?>')" id="titulo_<?php echo $row_entrada['id']; ?>" class="panel-heading">
                <span class="cabecera_entrada">
                    <span class="autor_publicacion"><?php #print_r($row_entrada) ?>
                    &nbsp;&nbsp;<i><strong><span><img style="border-radius:5px" height="40" src="<?php if (isset($row_entrada['foto'])) echo READFILE_URL."/foto/".validarfoto($row_entrada['foto']) ?>" ></span><?php echo  $row_entrada['nombre_usuario'] ?></strong> dice:</i><br><span class="fecha_publicacion" style="font-size:14px;color:gray;"><?php echo diferenciaentrefechas_foro($row_entrada['fecha']) ?></span>
                    </span>
<?php 
$suscripciones = contar_suscripciones_json($row_entrada['suscribirse']);
@session_start();
if (isset($suscripciones['todo'][$_SESSION['id_usuario']]) and $suscripciones['todo'][$_SESSION['id_usuario']]['estado']=="SI"){
$cambio =  "NO"; 
$accion = "Cancelar Suscipción";
$estilo_boton = "default";
$estilo_badge = "color: #000;background-color: transparent;";
$title = "Ud ya se encuentra suscrito, este tema tiene ".$suscripciones['total']." suscripcion";
if ($suscripciones['total']>1) $title.="es";
}else{
if (isset($_SESSION['id_usuario']))
$accion = "Suscribirse";
else
$accion = "Suscriptores";
$cambio =  "SI";
$estilo_boton = "danger";
$estilo_badge = "color: #d9534f;background-color: #fff;";
$title="Al suscribirse, ud recibirá mensajes de este tema";
if ($suscripciones['total']>0) $title.=", hay ".$suscripciones['total']." suscritos.";
}
?>
            <span title="<?php echo $title ?>" style="display:inline;float:right" class="btn btn-<?php echo $estilo_boton ?>" id="suscripcion_<?php echo $row_entrada['id']; ?>" <?php if (isset($_SESSION['id_usuario'])){ ?>
            onclick="suscribir_tema_foro(<?php echo $row_entrada['id']; ?>,'<?php
echo $cambio;
?>');"<?php } ?>><?php echo $accion; ?>&nbsp;<?php
if ($cambio ==  "SI"){
?><span title="Este tema tiene <?php print($suscripciones['total']);
            ?> suscripciones" class='badge' style ="<?php echo $estilo_badge ?>"><?php print($suscripciones['total']); ?></span>
<?php } ?></span>
                        </span>
            </div>
            <div class="entradas" id="entrada_<?php echo  $row_entrada['id']; ?>">
            <span style="float:right;position:relative">
            </span>
              
            </span>
            <div class="panel-body">
            <p><?php echo $row_entrada['contenido'] ?></p>
            </div>
            
            <div class="panel-footer">
  <?php span_fav_entradas($row_entrada['id'],$row_entrada['estrellas']); ?>
  <span class="area_denuncias">
  <span style="display:inline;"><?php if (isset($_SESSION['id_usuario'])){ ?><button class="btn btn-warning btn-sm"  class="btn btn-info btn-lg"  data-toggle="modal" data-target="#modal_denuncia_<?php echo  $row_entrada['id']; ?>" type="button">Denunciar</button><?php } ?></span>
  </span>
  <span class="area_visitas">
            <?php $visitas = contar_visitas_json($row_entrada['visitas'],$_SESSION['id_usuario']);
            ?>
            <h4 style="float:right;display:inline;">Visitas: <span title="Hay <?php           echo $visitas['total'];
            ?> visitas en este tema" class='badge badge_amarillo_azul'><?php            echo $visitas['total'];
            ?></span></h4>
    </span>
  
  <div id="modal_denuncia_<?php echo  $row_entrada['id']; ?>" class="modal fade" role="dialog">
      
      <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Denunciar este tema</h4>
      </div>
      <div class="modal-body">
        <ul class="bs-glyphicons modal_denuncias">
            <span class="elemento_motivo_denuncia">
            <input hidden type="checkbox" class="tipo_denuncia<?php echo  $row_entrada['id']; ?>" id="chk_li_<?php echo  $row_entrada['id']; ?>1" value="El tema contiene Agresión">
            <label for="chk_li_<?php echo  $row_entrada['id']; ?>1">
            <li>
            <img class="circle" src="<?php echo SGA_COMUN_URL ?>/img/bullying.jpg" width="90px">
            <h3 style="z-index:100;position:relative;margin-top:-10px">Agresión</h3>
            </li>
            </label>
            </span>
            <span class="elemento_motivo_denuncia">
            <input hidden type="checkbox" class="tipo_denuncia<?php echo  $row_entrada['id']; ?>" id="chk_li_<?php echo  $row_entrada['id']; ?>2" value="El tema contiene sexting">
            <label for="chk_li_<?php echo  $row_entrada['id']; ?>2">
            <!--li>
            <img class="circle" src="<?php echo SGA_COMUN_URL ?>/img/sexting.jpeg" height="90px">
            <h3 style="z-index:100;position:relative;margin-top:-10px">Sexting</h3>
            </li-->
            </label>
            </span>
        </ul>
        <br>
        <div class="form-group">
        <textarea style="border-radius: 8px;box-shadow: #6E9BAE 1px 1px 2px 1px;" placeholder="Si usted necesita escriba aquí un mensaje"  id="contenido_denuncia<?php echo  $row_entrada['id']; ?>" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <button type="button" class="btn btn-success" onclick="denunciar_tema(<?php echo  $row_entrada['id']; ?>);">Enviar denuncia</button>
        </div>
      </div>
      <div class="modal-footer">
        <button id="cerrar_modal_denuncia<?php echo  $row_entrada['id']; ?>" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
      
  </div><!--fin denunciar-->
  </div></div><!--Fin areatema-->
  <?php
}
if(isset($_GET['listar_comentarios'])){
  #if ($_GET['listar_comentarios']!=""){
    $entrada = $_GET['listar_comentarios'];
    $foro = new foro();
    $entradas = $foro->listar_comentarios($entrada);
    /**/
  $num = count($entradas);
  if ($num>0){
  $row_entrada=$entradas['datos_entrada'];
  area_entrada($row_entrada);
  }
  $num = count($entradas['comentarios']);
  ?>
  <h2 onclick="document.getElementById('comentarios_<?php echo $row_entrada['id']?>').style.display = document.getElementById('comentarios_<?php echo $row_entrada['id']?>').style.display == 'block' ? 'none':'block'">Comentarios  <span class='badge badge_amarillo_azul'><?php echo $num ?></span></h2>
  <div id="area_nuevo_comentario">
      <textarea style="border-radius: 8px;box-shadow: #6E9BAE 1px 1px 2px 1px;margin-bottom:5px" class="form-control" id="comentario_<?php echo $row_entrada['id']; ?>"></textarea>
      <div class="panel-footer" style="margin: 0px 25px;text-align:right;background-color: transparent;
    border-top: 1px solid transparent;">
      <button type="button" class="btn btn-info" onclick="guardar_comentario(<?php echo $row_entrada['id']; ?>)">Comentar</button>
      </div>
  </div>
  <button type="button" style="margin-bottom:20px;" class="btn btn-info" onclick="if (document.getElementById('comentarios_<?php echo $row_entrada['id']?>').style.display == 'none'){ document.getElementById('comentarios_<?php echo $row_entrada['id']?>').style.display = 'block';this.innerHTML='Ocultar  Comentarios' }else{ document.getElementById('comentarios_<?php echo $row_entrada['id']?>').style.display ='none';this.innerHTML='Ver Comentarios'}">Ocultar  Comentarios</button>
  <?php
  echo "<div id='comentarios_".$row_entrada['id']."'>";
  if ($num>0){
  foreach  ($entradas['comentarios'] as $id => $row_comentarios){
    echo "<div class='panel-body comentarios'  id='com_".$row_comentarios['id_comentario']."'>";?>
    <span class="cabecera_entrada" style="position:absolute;text-align:left;left: 90px;">
    <span class="foto_autor_comentario">
        <img style="border-radius:5px" height="40" src="<?php if(isset($row_comentarios['foto'])) echo READFILE_URL."/foto/".validarfoto($row_comentarios['foto']) ?>" >
    </span>
    <span class="nombre_autor_comentario">
    <i><strong><?php echo $row_comentarios['nombre_usuario']?></strong> dice:</i>
    </span>
    <br>&nbsp;&nbsp;&nbsp;&nbsp;
    <span class="fecha_publicacion"  style='font-size:14px;'>
        <span style="font-size:14px;color:gray;"><?php diferenciaentrefechas_foro($row_comentarios['fecha'])?>
        </span>
    </span>
    <br>
    </span><!--cabecera_entrada-->
    <div class="contenido_comentario"><?php echo $row_comentarios['contenido']; ?></div>
    <br>
    </div>
    <?php
  }
  }else{
      echo "<i style='margin-bottom:20px;'>Aún no hay comentarios</i><br>";
  }
  echo "</div>";
    /**/
  #}
  exit();
}
/* Funciones Foros*/
function consultar_nombre_grupo($gru){
    require((dirname(__FILE__))."/../comun/conexion.php");
    $sql = "SELECT `nombre_grupo` FROM `grupo_foro` WHERE `id_grupo_foro` ='$gru'";
    $consulta = $mysqli->query($sql);
    if($resultado=$consulta->fetch_assoc()){
    return $resultado['nombre_grupo'];
    }else{
    return "";
    }
}
if(isset($_GET['guardar_entrada'])){
    require("conexion.php");
$contenido = $mysqli->real_escape_string($_POST['contenido']);
$grupo = $mysqli->real_escape_string($_POST['grupo']);
$foro_entrada = new foro();
$resultado = $foro_entrada->guardar_entrada($contenido,$grupo);
if($resultado){
    echo "1";
}else{
    echo "0";
}
exit();
}
if(isset($_GET['guardar_comentario'])){
$foro_entrada = new foro();
$resultado = $foro_entrada->guardar_comentario($_POST['contenido'],$_POST['entrada']);
if($resultado){
    echo "1";
}else{
    echo "0";
}
exit();
}
if(isset($_GET['nueva_entrada'])){
nueva_entrada();
exit();
}
if(isset($_GET['nuevo_comentario'])){
nuevo_comentario();
exit();
}

function nueva_entrada(){
if(isset($_POST['token']) and $_POST['token']==encriptar($_POST['grupo'].$_POST['categoria'].$_POST['asignacion'].$_POST['actividad'])){
          require (dirname(__FILE__)."/../comun/conexion.php");
    $_POST['token'] = $mysqli->real_escape_string($_POST['token']);
    $_POST['grupo'] = $mysqli->real_escape_string($_POST['grupo']);
    $_POST['categoria'] = $mysqli->real_escape_string($_POST['categoria']);
    $_POST['asignacion'] = $mysqli->real_escape_string($_POST['asignacion']);
    $_POST['actividad'] = $mysqli->real_escape_string($_POST['actividad']);
    $_POST['contenido'] = $mysqli->real_escape_string($_POST['contenido']);
          if (isset($_POST['contenido'])){
          $sqlcategoria="";
          $sql1categoria = "";
          if ($_POST['categoria']!="" or $_POST['categoria']!=NULL){
          $sqlcategoria = ",'".$_POST['categoria']."'";
          $sql1categoria = ", `categoria_curso`";
          }
          $sqlid_actividad="";
          $sql1id_actividad = "";
          if ($_POST['actividad']!="" or $_POST['actividad']!=NULL){
          $sqlid_actividad = ",'".$_POST['actividad']."'";
          $sql1id_actividad = ", `id_actividad`";
          }
          $sqlid_asignacion="";
          $sql1id_asignacion = "";
          if ($_POST['asignacion']!="" or $_POST['asignacion']!=NULL){
          $sqlid_asignacion = ",'".$_POST['asignacion']."'";
          $sql1id_asignacion = ", `id_asignacion`";
          }
          $sql = "INSERT INTO `entrada`(`contenido`, `usuario`, `fecha`, `grupo`".$sql1categoria.$sql1id_actividad.$sql1id_asignacion.") VALUES ('".$_POST['contenido']."','".$_SESSION['id_usuario']."','".date("Y-m-d H:i:s")."','".$_POST['grupo']."'".$sqlcategoria.$sqlid_actividad.$sqlid_asignacion.")";
          if ($_POST['contenido']!=""){
            $insertar = $mysqli->query($sql);
            if ($mysqli->affected_rows > 0){
              echo '1';
            }else{
              echo '0';
            }
          }
          }
}else{
    #echo "grupo:".$_POST['grupo']." categoria:".$_POST['categoria']." asignacion:".$_POST['asignacion']." actividad:".$_POST['actividad'];
    #echo "\n";
    echo "Token no valido ";
    #echo $_POST['token']." no es igual a".encriptar($_POST['grupo'].$_POST['categoria'].$_POST['asignacion'].$_POST['actividad']);
}
}
function nuevo_comentario(){
require(dirname(__FILE__)."/../comun/conexion.php");
 /*recibo los campos del formulario proveniente con el método POST*/ 
if (isset($_POST['contenido']) and $_POST['contenido']!=""){//valida comentarios en blanco
$sql = "INSERT INTO comentario (`id_entrada`, `fecha`, `contenido`, `usuario`) VALUES ('".$_POST['id_entrada']."', '".date("Y-m-d H:i:s")."', '".$_POST['contenido']."', '".$_SESSION['id_usuario']."')";
 /*echo $sql;*/
if ($insertar = $mysqli->query($sql)) {
 /*Validamos si el registro fue ingresado con éxito*/ 
echo '1';
 }else{ 
echo '0';
}
}else{
echo '0';
}
exit();
}/*fin Comentar*/ 
/*
function insertar_foro($gru,$cat="",$cur="",$act="",$titulo="Foro",$id_span=""){
    if ($id_span=="") $id_span = sha1($gru.$cat.$cur.$act);
    ?>
    <div class="foro miforo<?php echo $id_span ?>">
    <label><input id="chkforo_<?php echo $id_span ?>" mostrarocultar="foro_<?php echo $id_span ?>" hidden type="checkbox"></input><span id="titulo"><?php echo "<span>".implode("</span> <span>",explode(" ",$titulo))."</span>" ?></span></label>
    <div style="display: none;" id="foro_<?php echo $id_span ?>" role="form">
<input name="grupo" type="hidden" id="grupo<?php echo $id_span ?>" value="<?php echo $gru ?>">
<input name="categoria" type="hidden" id="categoria<?php echo $id_span ?>" value="<?php echo $cat ?>">
<input name="asignacion" type="hidden" id="asignacion<?php echo $id ?>" value="<?php echo $cur ?>">
<input name="actividad" type="hidden" id="actividad<?php echo $id_span ?>" value="<?php echo $act ?>">
<input name="token" type="hidden" id="token<?php echo $id_span ?>" value="<?php echo encriptar($gru.$cat.$cur.$act)?>">
    <div>
    <span style="left: -10px;" class="icon-sga-speech-bubble icon-foro"></span>
    <div class="form-group">
        <label for="contenido<?php echo $id_span ?>"><span class="h_comentar">Proponer un Tema</span></label>
            <textarea name="contenido<?php echo $id_span ?>" id="contenido<?php echo $id_span ?>" type="text" class="form-control" placeholder="¿Que estas pensando?" title="¿Que estas pensando?" required></textarea>
    </div>
        <div class="form-group">
        <button onclick="nueva_entrada('<?php echo $id_span ?>')" name="Enviar" class="btn btn-primary">Publicar</button>
      </div>
      </div>
<span id="txt_foros<?php echo $id_span ?>">
<?php echo foro(encriptar($gru.$cat.$cur.$act),$gru,$cat,$cur,$act,$id_span); ?>
</span>
</div>
</div>
    <?php
}
*/
/*Fin Funciones Foros*/
if (isset($_GET['fav_ent'])){
    @session_start();
    require(dirname(__FILE__)."/conexion.php");
    $usuario = $_SESSION['id_usuario'];
    $sql = "SELECT `estrellas`,`usuario` FROM `entrada` WHERE `id` = '".$_POST['id']."'";
    $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();
    if ($row['estrellas']=="null" or $row['estrellas']=="NULL" or $row['estrellas']=="" or $row['estrellas']=="[]")
    $estrellas=array();
    else
    $estrellas = json_decode($row['estrellas'],true);
    #echo "Est-".$_POST['estado'];
    if ($_POST['estado']=="SI"){
    $estrellas[$usuario]="1";
    puntos($row['usuario'],"+");
    }elseif ($_POST['estado']=="NO"){
    unset($estrellas[$usuario]);
    puntos($row['usuario'],"-");
    }
    #print_r($_POST);
    $sql_up = "UPDATE `entrada` SET `estrellas`= '".json_encode($estrellas)."' WHERE `id` = '".$_POST['id']."'";
    $consulta_up = $mysqli->query($sql_up);
    $sql_fin = "SELECT `estrellas` FROM `entrada` WHERE `id` = '".$_POST['id']."'";
    $consulta_fin = $mysqli->query($sql_fin);
    $row_fin = $consulta_fin->fetch_assoc();
    $estrellas_fin = json_decode($row_fin['estrellas'],true);
    $estrellasmias=$estrellas_fin[$_SESSION['id_usuario']];
    if($estrellasmias=="1"){
    $estado_fin = "NO";
    }else{
    $estado_fin = "SI";
    }
    $quienes = array();
    foreach ($estrellas_fin as $id => $valor){
    $quienes[] = consultar_nombre($id);
    }
    $salida = array("estado"=>$estado_fin,"total"=>count($estrellas_fin),"quienes"=>implode(",",$quienes));
    echo json_encode($salida);
    exit();
}
if (isset($_GET['visita_foro'])){
    @session_start();
    $visitante = $_SESSION['id_usuario'];
    registrar_visita("entrada","visitas","id",$_POST['id'],$visitante,true,"usuario",1);
    //echo json_encode($respuesta);
    exit();
}
//visita_foro(5);
function suscribir_tema_foro($id_entrada,$estado_suscripcion){
    @session_start();
    $respuesta = registrar_suscripcion('entrada','suscribirse','id',$id_entrada,$_SESSION['id_usuario'],$estado_suscripcion,false,"usuario",2);
    if ($respuesta){
        echo "1";
    }else{
        echo "0";
    }
}
function registrar_suscripcion($tabla,$campo,$pk,$valor_pk,$suscriptor,$estado_suscripcion,$respuesta = true,$responsable="",$puntos=0){
    require(dirname(__FILE__)."/conexion.php");
    $sql = "SELECT `$campo`,`$responsable` FROM `$tabla` WHERE `$pk` = '".$valor_pk."'";
    $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();

    $estrellas = json_decode($row[$campo],true);
    
    $actuales = $estrellas[$suscriptor];
    
    $ultima = count($actuales)+1;
    $estrellas[$suscriptor]["suscripciones"][$ultima]=array("estado"=>$estado_suscripcion,"fecha"=>date("Y-m-d h:i:s"));
    $estrellas[$suscriptor]["estado"]=$estado_suscripcion;

    $sql_up = "UPDATE `$tabla` SET `$campo`= '".json_encode($estrellas)."' WHERE `$pk` = '".$valor_pk."'";
    $salida=false;
    $consulta_up = $mysqli->query($sql_up);
    if ($mysqli->affected_rows>0)
    $salida=true;
    if ($respuesta){
    $sql_fin = "SELECT `$campo` FROM `$tabla` WHERE `$pk` = '".$valor_pk."'";
    $consulta_fin = $mysqli->query($sql_fin);
    $row_fin = $consulta_fin->fetch_assoc();
    //desde aqui contar_visitas_json
    $salida = contar_suscripciones_json($row_fin[$campo],$suscriptor);
    }
    return $salida;
}
function contar_suscripciones_json($json,$usuario=""){
//el parametro recibe un registro string
//con forato json
//ejemplo {"1":{"1":"2017-12-2 8:15:00"},"1085232001":{"1":"2017-12-2 8:45:00"}}
//el algoritmo contará las fechas que se han registrado, cada fecha corresponde a una visita, los registros llave corresponden al id de usuario
     //require_once("funciones.php");
     #echo $json;
    /*
    $estrellas[$suscriptor][$ultima]=array("estado"=>$estado_suscripcion,"fecha"=>date("Y-m-d h:i:s"));
    $estrellas[$suscriptor]["estado"]=$estado_suscripcion;

    */
     $array = json_decode($json,true);
     $resultado = 0;
     $salida = array();
        $quienes = array();
                if (count($array)>0)
                foreach ($array as $id => $valor){
                if ($array[$id]["estado"]=="SI")
                $quienes[$id] = consultar_nombre($id);
                }
     if (count($array)>0){
         if (isset($array[$usuario]["estado"]))
         $estado = $array[$usuario]["estado"];
         else
         $estado = "NO";
         #print_r($array);
         foreach ($array as $id=>$valor){
            if ($array[$id]['estado']=="SI")
            $resultado += count($id);
            
         }
     }else{
        $estado="NO";
        $resultado=0;
        $estrellas_usuario=0;
        $quienes="";
     }
    
     $salida = array("total"=>$resultado,"estado"=>$estado,"quienes"=>$quienes,'todo'=>$array);
    return $salida; 
}
/*Ejemplo
print_r(contar_visitas_json('{"1":{"1":"2017-12-2 8:15:00","2":"2017-12-2 8:15:00","3":"2017-12-2 8:15:00"},"1080232001":{"1":"2017-12-2 8:45:00"}}',"1080232001"));
//respuesta Array ( [total] => 4 [usuario] => 1 [quienes] => Array ( [1] => Administrador SGA [1080232001] => Pedro Mena ) )
<?php $visitas = contar_visitas_json($row_entrada['visitas'],$_SESSION['id_usuario']);
            ?>
            
<span title="Has visitado <?php           echo $visitas['usuario'];
            ?> veces este tema" class='badge badge_amarillo_azul'><?php           echo $visitas['total'];
            ?></span>         
*/
function registrar_visita($tabla,$campo,$pk,$valor_pk,$visitante,$respuesta = true,$responsable="",$puntos=0){
    require(dirname(__FILE__)."/conexion.php");
    $sql = "SELECT `$campo`,`$responsable` FROM `$tabla` WHERE `$pk` = '".$valor_pk."'";
    $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();

    $estrellas = json_decode($row[$campo],true);
    
    $actuales = $estrellas[$visitante];
    $ultima = count($actuales)+1;
    $estrellas[$visitante][$ultima]=date("Y-m-d h:i:s");

    $sql_up = "UPDATE `$tabla` SET `$campo`= '".json_encode($estrellas)."' WHERE `$pk` = '".$valor_pk."'";
    $salida=false;
    $consulta_up = $mysqli->query($sql_up);
    if ($mysqli->affected_rows>0)
    $salida=true;
    if ($respuesta){
    $sql_fin = "SELECT `$campo` FROM `$tabla` WHERE `$pk` = '".$valor_pk."'";
    $consulta_fin = $mysqli->query($sql_fin);
    $row_fin = $consulta_fin->fetch_assoc();
    //desde aqui contar_visitas_json
    $salida = contar_visitas_json($row_fin[$campo],$visitante);
    }
    return $salida;
}
function consultar_susctiptores($id_tema){
    require(dirname(__FILE__)."/conexion.php");
    $sql = "SELECT `suscribirse` FROM `entrada` WHERE `id` ='".$id_tema."'";
     $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();
    //desde aqui contar_visitas_json
    $salida = contar_visitas_json($row['suscribirse'],$_SESSION['id_usuario']);
    //el for each suscripciones envio mensaje en guardar comentario
    return $salida['quienes'];
    
}

function contar_visitas_json($json,$usuario=""){
//el parametro recibe un registro string
//con forato json
//ejemplo {"1":{"1":"2017-12-2 8:15:00"},"1085232001":{"1":"2017-12-2 8:45:00"}}
//el algoritmo contará las fechas que se han registrado, cada fecha corresponde a una visita, los registros llave corresponden al id de usuario
     //require_once("funciones.php");
     #echo $json;
     $array = json_decode($json,true);
     $resultado = 0;
     $salida = array();
        $quienes = array();
        if (count($array)>0)
        foreach ($array as $id => $valor){
        $quienes[$id] = consultar_nombre($id);
        }
    if ($usuario!="") $estrellas_usuario=0;
     if (count($array)>0){
         #print_r($array);
         foreach ($array as $id=>$valor){
             if (count($valor)>0)
             foreach ($valor as $id2=>$valor2){
                 if (count($valor2)>0){
                 if ($usuario!=""){
                    if ($usuario==$id){
                      $estrellas_usuario+=count($id2);
                    }
                 }
                 $resultado += count($id2);
                 }
             }
         }
     }
     $salida = array("total"=>$resultado,"usuario"=>$estrellas_usuario,"quienes"=>$quienes);
    return $salida; 
}
/*Ejemplo
print_r(contar_visitas_json('{"1":{"1":"2017-12-2 8:15:00","2":"2017-12-2 8:15:00","3":"2017-12-2 8:15:00"},"1080232001":{"1":"2017-12-2 8:45:00"}}',"1080232001"));
//respuesta Array ( [total] => 4 [usuario] => 1 [quienes] => Array ( [1] => Administrador SGA [1080232001] => Pedro Mena ) )
<?php $visitas = contar_visitas_json($row_entrada['visitas'],$_SESSION['id_usuario']);
            ?>
            
<span title="Has visitado <?php           echo $visitas['usuario'];
            ?> veces este tema" class='badge badge_amarillo_azul'><?php           echo $visitas['total'];
            ?></span>         
*/
if (isset($_GET['elred'])) {
  $red=$_GET['elred'];
  eliminar_red($red);
}
if (isset($_GET['mostrarSugerenciaiconos'])){
  ob_clean();
  mostrarSugerenciaiconos();
  exit();
}

########## Fin Simuladores de rutas //se habla español :)

########## Inicio  Simuladores de Controladores y Modelos combinados //se habla español :)
function puntos_suspensivos($string, $length=NULL)
{
    //Si no se especifica la longitud por defecto es 50
    if ($length == NULL)
        $length = 50;
    //Primero eliminamos las etiquetas html y luego cortamos el string
    $stringDisplay = substr(strip_tags($string), 0, $length);
    //Si el texto es mayor que la longitud se agrega puntos suspensivos
    if (strlen(strip_tags($string)) > $length)
        $stringDisplay .= ' ...';
    return $stringDisplay;
}     



function consultar_nombre($id_usuario){
    @session_start();
    require(dirname(__FILE__)."/conexion.php");
    $usuario = $_SESSION['id_usuario'];
    $sql = "SELECT `nombre`,`apellido` FROM `usuario` WHERE `id_usuario` = '".$id_usuario."'";
    $consulta = $mysqli->query($sql);
    if ($row = $consulta->fetch_assoc())
    return $row['nombre']." ".$row['apellido'];
    else
    return $id_usuario;
}
function consultar_nombre_tema($entrada){
    @session_start();
    require(dirname(__FILE__)."/conexion.php");
    $usuario = $_SESSION['id_usuario'];
    $sql = "SELECT `contenido` FROM `entrada` WHERE `id` = '".$entrada."'";
    $consulta = $mysqli->query($sql);
    if ($row = $consulta->fetch_assoc())
    return $row['contenido'];
    else
    return "";
}
if (isset($_GET['fav_red'])){
    @session_start();
    require(dirname(__FILE__)."/conexion.php");
    $usuario = $_SESSION['id_usuario'];
    $sql = "SELECT `estrellas` FROM `red` WHERE `id_red` = '".$_POST['id_red']."'";
    $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();
    if ($row['estrellas']=="null" or $row['estrellas']=="NULL" or $row['estrellas']=="")
    $estrellas=array();
    else
    $estrellas = json_decode($row['estrellas'],true);
    if ($_POST['estado']=="SI"){
    $estrellas[$usuario]="1";
    }elseif ($_POST['estado']=="NO"){
    unset($estrellas[$usuario]);
    }
    #print_r($_POST);
    $sql_up = "UPDATE `red` SET `estrellas`= '".json_encode($estrellas)."' WHERE `id_red` = '".$_POST['id_red']."'";
    $consulta_up = $mysqli->query($sql_up);
    $sql_fin = "SELECT `estrellas` FROM `red` WHERE `id_red` = '".$_POST['id_red']."'";
    $consulta_fin = $mysqli->query($sql_fin);
    $row_fin = $consulta_fin->fetch_assoc();
    $estrellas_fin = json_decode($row_fin['estrellas'],true);
    $estrellasmias=$estrellas_fin[$_SESSION['id_usuario']];
    if($estrellasmias=="1"){
    $estado_fin = "NO";
    }else{
    $estado_fin = "SI";
    }
    $salida = array("estado"=>$estado_fin,"total"=>count($estrellas_fin));
    ob_clean();
    echo json_encode($salida);
    exit();
}

function descomprimir_zip ($nombre_zip,$ruta_destino){
$zip = new ZipArchive;
$zip->open($nombre_zip);
$zip->extractTo('../red/banco_red'.$ruta_destino);
$zip->close();
}

function formatos(){
    require 'conexion.php';
    $sql ='select * from config';
   /*
    $consulta = $mysqli ->query($sql);
    $extensiones = array();
    while($row=$consulta->fetch_assoc()){
    $extensiones= (explode(",",$row['formatos_no_permitidos']));
    }
    */
   # return $extensiones;
}

function tamaño_maximo(){
    require 'conexion.php';
    $sql ='select * from config';
    /*
    $consulta = $mysqli ->query($sql);
    $extensiones = array();
    while($row=$consulta->fetch_assoc()){
    return $row['tamano_maximo_adjunto'];
    }
    */
}


#20/03/2017 Función utilizada en cursos 

function verificar_actividad_hecha ($actividad){
   require '../comun/conexion.php';
    @session_start();
    $sql='select id_tarea_adjunto from tarea_adjunto where tarea_adjunto.id_actividad="'.$actividad.'" and tarea_adjunto.id_estudiante ="'.$_SESSION['id_usuario'].'" ';
    $consulta = $mysqli ->query($sql);
    $cuantos = $consulta -> num_rows;
    return $cuantos;
}

#Fin 20/03/2017

#04/04/2017 Función para seleccionar hijo
if (isset($_GET['hijo'])){
    $hijo = $_GET['hijo'];
    seleccionar_hijo($hijo);
}
function seleccionar_hijo($hijo){
@session_start();
$_SESSION['mihijo']=$hijo;
    #setcookie('hijo',$hijo);
   header("location:../index.php");
}
function ultima_inscripcion ($estudiante) {
        require 'conexion.php';
       $sql='select * from inscripcion where id_estudiante = "'.$estudiante.'"';
        $consulta = $mysqli->query($sql); 
        while($row = $consulta -> fetch_assoc()){
            return $row['id_inscripcion']; 
        }

}
#04/04/2017 Fin Función para seleccionar hijo
function inscribir_estudiante_materia($asignacion,$categoria,$ano_lectivo){
    require 'conexion.php';
    $ano_lectivo=ano_lectivo();
     $sql = 'select * from inscripcion,asignacion where 
asignacion.id_asignacion= inscripcion.id_asignacion and      
    asignacion.id_categoria_curso = "'.$categoria.'" and Year(inscripcion.fecha_inscripcion) = "'.$ano_lectivo.'" '; 
       $consulta = $mysqli ->query($sql); #Consultamos estudiante
    $estudiante= array(); 
    while($row=$consulta->fetch_assoc()){
    $estudiante[]= $row; 
    }
    foreach ($estudiante as $key => $id_estudiante) {
    require 'conexion.php';
    $inscripcion ='INSERT INTO `inscripcion`( `id_asignacion`, `id_estudiante`, `fecha_inscripcion`) VALUES ("'.$asignacion.'","'.$id_estudiante['id_estudiante'].'","'.date('Y-m-d').'" )';
    #echo $inscripcion.'<br>';
if($inscribir = $mysqli->query($inscripcion)){
    echo 'todo ok';
}

 
    }
}

function consultar_datos2($consulta,$mysqli_assoc=false){
require ("conexion.php");
if ($gconsulta_red = $mysqli->prepare($consulta)){
$gconsulta_red = $mysqli->prepare($consulta);
$gconsulta_red->execute();
$arraydedatos = $gconsulta_red->get_result();
if($mysqli_assoc){
$datos = $arraydedatos->fetch_all(MYSQLI_ASSOC);
}else{
$datos = $arraydedatos->fetch_all();
}
return $datos;
}
}

function actualizar_datos($consulta ){
require ("conexion.php");
if ($gconsulta_red = $mysqli->prepare($consulta)){
$gconsulta_red = $mysqli->prepare($consulta);
if($gconsulta_red->execute()){
    echo '<script> alert2("Información Actualizada"); </script>';
} else {
     echo '<script> alert2("Información NO Actualizada","error"); </script>';
}

    
    
}
}


function inscribir_estudiante($asignacion,$estudiante){
require 'conexion.php';
 $sql='INSERT INTO `inscripcion`( `id_asignacion`, `id_estudiante`, `fecha_inscripcion`) VALUES ("'.$asignacion.'","'.$estudiante.'","'.date('Y-m-d').'")';
$consulta = $mysqli ->query($sql); #Consultamos estudiante
}



#22/03/2017 función para inscribir a asignaturas de su curso
function inscribir_estudiante_individual($ano_lectivo){
require 'conexion.php';
$sql = 'select * from usuario inner join seguimiento_categoria_ano on seguimiento_categoria_ano.id_estudiante = usuario.id_usuario where usuario.rol LIKE "%estudiante%" and seguimiento_categoria_ano.estado="En curso";';
$consulta = $mysqli ->query($sql); #Consultamos estudiante
$estudiante= array(); 
while($row=$consulta->fetch_assoc()){
$estudiante[]= $row;  
}
#echo "<pre>";
#print_r($estudiante);
#echo "</pre>";
foreach ($estudiante as $key => $id_estudiante) {
$inscripcion ='INSERT INTO `inscripcion`(`id_estudiante`, `id_asignacion`, `fecha_inscripcion`) select "'.$id_estudiante['id_estudiante'].'",id_asignacion,"'.date('Y-m-d').'" from asignacion where id_categoria_curso = "'.$id_estudiante['categoria'].'" and ano_lectivo = "'.$ano_lectivo.'";';
$inscribir = $mysqli->query($inscripcion); #Inscribimos a los estudiantes
}
}
#22/03/2017 Fin función para inscribir a asignaturas de su curso

#22/03/2017 función para generar las asignaturas por ley para primaria cuando se crea el año lectivo (Ojo implementarse cuando se cree el crud de año lectivo despues de su insert)ok
function consultar_materia_obligatorias(){
    require dirname(__FILE__).'/conexion.php';
    $sql_materia = 'select * from materia where obligatoria = "SI"';
    return consultar_datos2($sql_materia);
    /*
    [0] => 1
    [1] => CIENCIAS NATURALES
    [2] => SI
    [3] => 5
    [4] => microscope
    */
}

function generar_cursos_DeLey($ano_lectivo,$inscribir_est=false,$id_docente="") {
@session_start();
if ($id_docente=="") $id_docente = $_SESSION['id_usuario'];
#echo "id_docente:".$id_docente;
require dirname(__FILE__).'/conexion.php';
#borrar#$sql_materia = 'select * from materia where obligatoria = "SI"';
/*
Actual:
Consulta materias obligatorias
Recorre categorias y crea cursos por categoria 
y crea asignaciones academicas
incribir a los estudiantes 
Finalidad:
Prerrequisitos para usar esta funcion
Revisar el estado del año vigente
Organizar el flujo del proceso desde la instalación para validar errores, por ejemlo que el sistema quede sin año lectivo
*/
#borrar#$consulta_materia = $mysqli->query($sql_materia);
$datos_materia = consultar_materia_obligatorias();//probado
$cont=0;
foreach ($datos_materia as $campo => $materia){
$cont++;
#echo "#".$cont."_".$id_materia."_";
$id_materia = $materia[0];
#if ($campo==0){
require dirname(__FILE__).'/conexion.php';//puede quitarse
$sqlcategoria_curso = 'select * from categoria_curso ';
#$datos_categoria_curso = consultar_datos2($sqlcategoria_curso);
#/*
$consulta_categoria_curso = $mysqli -> query($sqlcategoria_curso);
while ($datos_categoria_curso = $consulta_categoria_curso ->fetch_assoc()){
#*/
foreach ($datos_categoria_curso as $campo_c_c => $valores) { #cc 
if ($campo_c_c=="id_categoria_curso"){
/*duplicar nombre materia*/ 
$sql_copia_materia='INSERT INTO `materia`(`nombre_materia`, `obligatoria`, `area`, `icono_materia`) SELECT  `nombre_materia`, "NO", `area`, `icono_materia` FROM `materia` WHERE id_materia = "'.$id_materia.'"';
if($consulta_copia_materia = $mysqli->query($sql_copia_materia))
$nueva_materia = $mysqli->insert_id;
/*fin duplicar nombre materia*/
$insertar_nueva_Asignacion ='INSERT INTO `asignacion`(`id_materia`,  `id_categoria_curso`, `ano_lectivo`, `id_docente`) VALUES ("'.$nueva_materia.'","'.$valores.'","'.$ano_lectivo.'","'.$id_docente.'")';
$consulta_nueva_Asignacion = $mysqli->query($insertar_nueva_Asignacion);
$id_nueva_asignacion = $mysqli ->insert_id;
}//fin if ($campo_c_c=="id_categoria_curso"){
}//fin foreach
}//while
#}
}//fin foreach
if ($inscribir_est==true) inscribir_estudiante($ano_lectivo);
#Voluntario si queremos que al crear el año y las asignaturas automaticamente se inscriban los niños a esas asignaturas
}
#22/03/2017 Fin función para generar las asignaturas por ley para primaria cuando se crea el año lectivo


function play_scorm($carpeta){
    require dirname(__FILE__).'/config.php';
 $carpeta1= $_SERVER['DOCUMENT_ROOT'].'/red/'.$carpeta;
#function buscar_html_en_scorm($directorio)
    $scorm= simplexml_load_file($carpeta1.'/imsmanifest.xml');
    #echo "<pre>";
    #echo $scorm->resources->resource[0];
    #print_r($scorm->resources);
   echo "<br>";
   echo '<iframe   id="miframe" src=""></iframe><br/>';
#print_r($scorm);
echo '<div  style="position:absolute;margin-left:50%;margin-top:-25%;width:10%;" >'; #border-style: solid;
foreach ($scorm->resources->resource as $nodo) 
  {
  $atributos = $nodo->attributes();
echo "<a onclick=\"loadframe('".SGA_RED_URL."/".$carpeta."/" .$atributos['href']."')\">".str_replace(".html","",$atributos['href'])."</a><br>";
   

  #print_r($scorm);
  }    
  echo '</div>';
    echo "</pre>";
 /*
 Fuente: 
 * http://www.anerbarrena.com/php-simplexml-4382/
 http://web.tursos.com/como-leer-un-archivo-xml-con-php/
 */
}
### 16/03/2017 Función reproductor (A,1)
function reproductor ($formato,$ruta,$scorm="") {
  if(!isset($adicionales)) $adicionales="true";
  
  if ($formato=="") reproducir_html_sin_formato();  
if ($formato=="zip" and $scorm=="si" ) {    
  play_scorm($ruta);$scorm="true";    }
$extensiones_video = array("mp4","ogg","webm","mpg","wmv");
$extensiones_audio = array("mp3","wav");//
$extensiones_imagenes = array("jpg","png","gif");
$extensiones_texto = array ("pdf","txt");
$extensiones_adicionales = array ("swf");
$texto = "";
if (in_array($formato,$extensiones_video)) { reproducir_video($ruta,$formato); } else { $video ="false"; }
if (in_array($formato,$extensiones_audio)) { reproducir_audio($ruta,$formato);  } else { $audio ="false"; }
if (in_array($formato,$extensiones_imagenes)) { reproducir_imagen($ruta,$formato);  } else { $imagenes ="false"; }
if (in_array($formato,$extensiones_texto)) {
 reproducir_texto($ruta,$formato);  }   else { $texto ="false"; }  
#if (in_array($formato,$extensiones_adicionales)) { reproducir_adicionales($ruta,$formato);  }  else { $adicionales ="false"; } 


if ($video =="false" and $audio =="false" and isset($imagenes) and $imagenes=="false" and $texto == "false" and  $adicionales =="false" and $scorm=="no") { ?>
 <div style=" display: flex;
    justify-content: center;
    align-content: center;
    flex-direction: column;vertical-align: text-top;width:60%;height:350px!important;  background-color:#F5F5DC;z-index:0;    background-size: 100% 100%;">
 <h1 align="center">El formato <?php echo $formato; ?> <br/> No se puede visualizar en linea </h1> <h3 align="center">Pero puedes descargarlo :)</h3></div>
<?php
    #descargar($ruta,$formato);
    ?><?php
}
                                                }
##Fin función reproductor
##### 16/03/2017 Funciones de Reproducción Html5 (A,1)
/*

echo '<script type="text/javascript">
        function launchFullscreen(element) {
  if(element.requestFullScreen) {
    element.requestFullScreen();
  } else if(element.mozRequestFullScreen) {
    element.mozRequestFullScreen();
  } else if(element.webkitRequestFullScreen) {
    element.webkitRequestFullScreen();
  }
}
        
    </script>'; */
function descargar ($ruta,$formato,$nombre="archivo"){
 $nombre= $nombre.'.'.$formato;
 $ruta = substr($ruta,47); // devuelve "d"
 $ruta='../comun/sga-data/banco_red/'.$ruta;   
 #echo $ruta;
 #exit();
    header("Content-type: application/octet-stream"); 
     header("Content-Type: application/force-download"); 
header("Content-Disposition: attachment; filename=\"$nombre\"\n"); readfile($ruta); 
}

function reproducir_video($ruta,$formato){ ?>
 <video id="vídeo_completo" style="postion:absolute;margin-top:1%;border-style:solid;" width="700" height="350" src="<?php echo $ruta; ?>" controls>
</video>    
<?php }
 function reproducir_html_sin_formato(){  ?>
  <div style="width:60%;height:370px!important;  background-image: url('../comun/img/fondo_login.jpg');z-index:-1;    background-size: 100% 100%;
  border-style: solid;"  >
  </div>
 <?php }
 
 function reproducir_audio ($ruta,$formato){ ?>
 <div style="width:60%;height:370px!important;  background-image: url('../comun/img/fondo_musica.jpg');z-index:-1;    background-size: 100% 100%;
border-style: solid;"  >
 <audio style="width:100%;margin-top:49%;z-index:0;" id="audio" controls="controls">
<source src="<?php echo $ruta ; ?>" type="audio/mp3" />
</audio>
</div>

<?php
}
?>
<?php
function reproducir_imagen($ruta,$formato){ ?>
 <button style="margin-top:-30px;margin-left:540px;position:absolute;!important" class="btn btn-primary" onclick="launchFullscreen(document.getElementById('imagen_previa'));">Pantalla Completa</button>  
<img id="imagen_previa" width="650" height="370" src="<?php echo  $ruta ; ?>"></img>
 <?php } 

function reproducir_texto ($ruta,$formato){
require_once ("config.php");
if($formato=="" and (isset($web) and $web<>false)){ 
$ruta= trim($ruta,SGA_RED_URL);
}
 switch ($formato) {
     case "txt": ?><object data='<?php echo $ruta ;?>' width='600' height='500'></object> <?php
     break;
    case "pdf":
    case "":
            ?>

    <iframe style="height:380px;width: 62%!important;margin-top:0%!important;"     id="miframered" name="miframered"  src="<?php echo $ruta ;?>" scrolling="auto" allowfullscreen></iframe>
    <button style="margin-top:0px;margin-left:-500px;position:absolute;!important" class="btn btn-primary" onclick="launchFullscreen(document.getElementById('miframered'));">Pantalla Completa</button>    
 <?php    break;
 case "":
    case "":    ?>
        <iframe id="miframered" name="miframered"  src="<?php echo $ruta ;?>" scrolling="auto"></iframe>
<?php
 }
} 

function reproducir_adicionales($ruta,$formato) { ?>
   <object width="250" height="250" >
<param name="movie" value="calendar.swf" >
<param name=" movie" align="left" > 
<param name="play" value="true" > 
<embed src="<?php echo $ruta ;?>" >
</embed>
</object>
<?php }


### Funciones de Reproducción Html5 (A,1)


#15/03/2017 - Función diseñada con el fin de clonar un curso con sus inscritos,actividades y asignación acádemica (A,1)
if (isset($_POST['asignaciond'])){ ?>
<?php
    $asignacion=$_POST['asignaciond'];
   return clonar_curso($asignacion);
   header('location:index.php');
 }
function clonar_curso($asignacion) {
    if(fn_clonar_curso($asignacion)){
    ob_start();
    ?>
    <script>
    alert2("Duplicación Exitosa");
    </script>
     <meta http-equiv="refresh" content="3; url=<?php echo SGA_CURSOS_URL?>/php/mis_cursos.php" />.
    <?php
    $contenido = ob_get_clean();
    require ("plantilla.php");
    exit();
}
} # Fin clonar_curso

function eliminar_red($red){
require 'conexion.php';
 $sql_red = 'select * from red where id_red= "'.$red.'"';
$consulta_red = $mysqli -> query($sql_red);
while($rowred= $consulta_red->fetch_assoc())
{
 $rutaa = $rowred['enlace'];
}
require_once("../comun/config.php");
require 'conexion.php';
  $sql = 'DELETE FROM `red` WHERE `id_red` = "'.$red.'"';





if ($mysqli ->query($sql)){
if(isset($_GET['scorm']) and $_GET['scorm']=="si"){ eliminarDir($rutaa); }
else{
$rutaa=str_replace("///localhost//guagua/comun/sga-data/banco_red/","",$rutaa);

$rutaa=$_SERVER['DOCUMENT_ROOT'].'/guagua/comun/sga-data/banco_red/'.$rutaa;

///localhost//guagua/comun/sga-data/banco_red/
 if (unlink($rutaa)) { ?>
     <script type="text/javascript" >
         alert('Recurso Borrado con Exito');
        window.location='../red/index.php';
    </script>
    <?php } else {         ?>
     <script type="text/javascript" >
         alert2('El recurso posiblemente se esta utilizando en una actividad','warning');
        window.location='../red/index.php'; 
             </script>
<?php
        
    }
  
}


}
else {
     ?>
     <script type="text/javascript" >
         alert2('Verifique su información','danger');
       window.location='../red/index.php'; 
             </script>
<?php
}
        
}
#####  FIN Función para eliminar un RED
#Inicio Función Elimnar directorio
function eliminarDir($carpeta)
{
     $carpeta2 = '../red/'.$carpeta;

    foreach(glob($carpeta2. "/*") as $archivos_carpeta)
    {
         $archivos_carpeta;
 
        if (is_dir($archivos_carpeta))
        {
            eliminarDir($archivos_carpeta);
        }
        else
        {
            unlink($archivos_carpeta);
        }
    }
    rmdir($carpeta2);
}


#Fin Función Eliminar directorio
#
function redireccionandoencursos(){
   @session_start();
          header('location:../cursos/mis_cursos.php');
       
}
# Función que permite insertar un nuevo RED 09/03/2017
function insertar_red ($ruta_destino,$extensión_archivo){
require ("../comun/conexion.php");
   $array['cantidad_estrellas']= $_POST['cantidad_estrellas'];
    $array['titulo_red'] = $_POST['titulo_red'];
    $array['icono_red'] = $_POST['icono_seleccionado'];
  if (!empty($_POST['icono_seleccionado'])) {
    $array['adjunto'] = $_POST['adjunto'];
  }

  #  $array['icono_red'] = $_POST['asignatura'];
    $array['enlace'] = $_POST['enlace'][0] ; 
    $array['fecha'] = date('Y-m-d');
    $array['scorm'] = $_POST['scorm'];
    $array['institucion'] = $_SESSION['institucion'];

if ($_POST['asignatura']<> ""){
   foreach ($_POST['asignatura'] as $i => $value) {
$array2['asignatura'][$i]=$value ;
}
}


  if ($_POST['nivel_eductivo']<> ""){
   foreach ($_POST['nivel_eductivo'] as $j => $value2) {
$array2['nivel_eductivo'][$j]  = $value2 ;
}
}


 $array['materia_red']= json_encode($array2['asignatura']);
    $array['nivel_eductivo'] = json_encode ($array2['nivel_eductivo']);
    $array['idioma_red'] =$_POST['idioma_red'];
    $array['dificultad'] =$_POST['dificultad'];
    $array['palabras_clave'] =$_POST['palabras_clave'];
    @session_start();
    $array['responsable'] = $_SESSION['id_usuario'] ;
    $array['descripcion'] =$_POST['descripcion']; 
    $array['formato'] =$extensión_archivo; 
    $array['contexto'] =$_POST['contexto']; 
    $array['tipo_interacción'] =$_POST['tipo_interacción']; 
    $array['tipo_recurso_educativo'] =$_POST['tipo_recurso_educativo']; 


require ("../comun/conexion.php");

$sql_datos_red = insertar($array,"red",true);
#echo $sql_datos_red;
#exit();
#echo $sql_datos_red;
$consulta=$mysqli->query($sql_datos_red);
$id_red = $mysqli->insert_id;
    if ($mysqli->affected_rows>0){
    /*
    //crear foro en cascada en red
    $roles_grupo=array("admin","docente","estudiante","acudiente");
    nuevo_foro($_POST['titulo_red'],$roles_grupo,"red",$id_red,"NO",$_POST['icono_seleccionado']);
    //crear foro en cascada  en red
    */
    ?>
<script type="text/javascript" >
    alert('Registro Correcto');
     setTimeout(function(){ window.location= 'index.php'; }, 4000);
</script>

<?php }
else{
      ?>
<script type="text/javascript" >
    alert('Verifique su información e intente de nuevo','danger');
     setTimeout(function(){ window.location= 'index.php'; }, 4000);
</script>

<?php

} 
}#fin función 

#inicio función actualizar red
function actualizar_red ($ruta_destino="",$extensión_archivo=""){
  @session_start();
  $array = array();
     if ($_POST['nivel_eductivo']<> ""){
   foreach ($_POST['nivel_eductivo'] as $i => $value) {
$array['nivel_eductivo']=$value ;
}
}
$json= json_encode($array['nivel_eductivo']);
$sql_actualizar = 'UPDATE `red` SET 
    `cantidad_estrellas`="'.$_POST['cantidad_estrellas'].'",
    `materia_red`=\''.json_encode($_POST['asignatura']).'\',
    `titulo_red`= "'.$_POST['titulo_red'].'",
    `idioma_red`="'.$_POST['idioma_red'].'",
    `contexto`="'.$_POST['contexto'].'",
    `descripcion`="'.$_POST['descripcion'].'",
    `palabras_clave`="'.$_POST['palabras_clave'].'",
    `nivel_eductivo`='."'".$json."'".',
    `autor`="'.$_POST['autor'].'",
    `responsable`="'.$_SESSION['id_usuario'].'", ';
if($ruta_destino<>"no" and $extensión_archivo<>"no"){ 

   if ($extensión_archivo<>"" ) {
   $sql_actualizar.=' `formato`="'.$extensión_archivo.'",' ;
   }
   else {
       $sql_actualizar.='';
   }
   if ($ruta_destino<>"" ){
           $sql_actualizar.=' `enlace`="'.$ruta_destino.'",';
   }
   else {
             $sql_actualizar.=' ';
   }

}
$sql_actualizar.=' `tipo_interacción`="'.$_POST['tipo_interacción'].'",
    `tipo_recurso_educativo`="'.$_POST['tipo_recurso_educativo'].'",
    `dificultad`="'.$_POST['dificultad'].'",
    `fecha`="'.date('Y-m-d').'",
    `scorm`="'.$_POST['scorm'].'",
    `adjunto`="'.$_POST['adjunto'].'",';
if($_POST['adjunto']=="no" and isset($_POST['enlace']) ){
  $sql_actualizar.='`enlace`="'.$_POST['enlace'][0].'",';
}    


$sql_actualizar.='`icono_red`="'.$_POST['icono_seleccionado'].'"
    WHERE id_red = "'.$_POST['id_red'].'"    '; 




require 'conexion.php';
#echo $sql_actualizar;
if ($actualizar = $mysqli ->query ($sql_actualizar)) { ?>
<script type="text/javascript">alert2('Modificación Exitosa');
     //setTimeout(function(){ window.location= 'index.php'; }, 4000);

</script>
    <?php
}
else {
   ?>
<script type="text/javascript">alert2('Verifique su información');
    // setTimeout(function(){ window.location= 'index.php'; }, 4000);
</script>
    <?php

}
    
}

#Fin función actualizar red






function consultar_nombre_categoria($cat){
    require((dirname(__FILE__))."/../comun/conexion.php");
    $sql = "SELECT `nombre_categoria_curso` FROM `categoria_curso` WHERE  `id_categoria_curso`='$cat'";
    $consulta = $mysqli->query($sql);
    if($resultado=$consulta->fetch_assoc()){
    return $resultado['nombre_categoria_curso'];
    }else{
    return "";
    }
}
function consultar_nombre_cuestionario($id){
    require((dirname(__FILE__))."/../comun/conexion.php");
    $sql = "SELECT `nombre` FROM `cuestionario` WHERE  `id`='$id'";
    $consulta = $mysqli->query($sql);
    if($resultado=$consulta->fetch_assoc()){
    return $resultado['nombre'];
    }else{
    return "";
    }
}

function consultar_nombre_actividad($mat){
    require((dirname(__FILE__))."/../comun/conexion.php");
    $sql = "SELECT `nombre_actividad` FROM `actividad` WHERE `id_actividad` ='$mat'";
    $consulta = $mysqli->query($sql);
    if($resultado=$consulta->fetch_assoc()){
    return $resultado['nombre_actividad'];
    }else{
    return "";
    }
}
function consultar_nombre_asignacion($asi){
    require((dirname(__FILE__))."/../comun/conexion.php");
    $sql = "SELECT `materia`.`nombre_materia` FROM `asignacion` inner join `materia` on `materia`.`id_materia` = `asignacion`.`id_asignacion` WHERE `id_asignacion`='$asi'";
    $consulta = $mysqli->query($sql);
    if($resultado=$consulta->fetch_assoc()){
    return $resultado['nombre_materia'];
    }else{
    return "";
    }
}

function consulta_id_cat($nombre,$nivel){
    $salida="a";
    require(dirname(__FILE__)."/conexion.php");
    $sql ="SELECT `id_categoria_curso` FROM `categoria_curso` WHERE `nombre_categoria_curso`='".$nombre."'";
    $guardar = $mysqli->query($sql);
    if ($guardar->num_rows>0){
       if ($row = $guardar->fetch_assoc()){
           $salida = $row['id_categoria_curso'];
       }
    }else{
        $salida = nueva_categoria_curso($nombre,$nivel);
        
    }
    return $salida;
}
function nueva_categoria_curso($nombre,$nivel){
    require (dirname(__FILE__)."/conexion.php");
    $sql = "INSERT INTO `categoria_curso`(`nombre_categoria_curso`,`nivel_educativo`) VALUES ('".$nombre."','".$nivel."')";
    #echo $sql."<br>";
    $guardar = $mysqli->query($sql);
    $salida = $mysqli->insert_id;
    return $salida;
}
function validarfoto($miurl="user-icon.png",$sexo=0){
  /*
    $returl = "user-icon.png";
    $returf = "user-iconf.png";
if($sexo=="f"){     return $returf;      }
if($sexo=="m" or $sexo==0 ){    return  $miurl;      }
*/
}
if (isset($_GET['estado_visible'])){
ob_clean();
echo actividad_visible($_POST['id_actividad'],$_POST['estado_visible']);
exit();
}

if (isset($_GET['estado_curso'])){
ob_clean();
echo curso_visible($_POST['id_curso'],$_POST['estado_curso']);
exit();
}

function curso_visible($id_curso,$estado){
    require ("conexion.php");
$sql_cam_fav = "UPDATE `asignacion` SET `visible`='".$estado."' where 
id_asignacion =  '".$id_curso."';";
$mysqli->query($sql_cam_fav);
 $sql_fav =  "SELECT `visible` FROM `asignacion` WHERE `id_asignacion` = '".$id_curso."';";
    $consulta_fav = $mysqli->query($sql_fav);
    if ($row = $consulta_fav->fetch_assoc()){
    return $row['visible'];
    }
}
if (isset($_GET['estado_tema'])){
ob_clean();
echo tema_visible($_POST['id_tema'],$_POST['estado_tema']);
exit();
}
function tema_visible($id_tema,$estado){
    require ("conexion.php");
$sql_cam_fav = "UPDATE `entrada` SET `estado`='".$estado."' where 
id =  '".$id_tema."';";
$mysqli->query($sql_cam_fav);
$sql_fav =  "SELECT `estado` FROM `entrada` WHERE `id` = '".$id_tema."';";
    $consulta_fav = $mysqli->query($sql_fav);
    if ($row = $consulta_fav->fetch_assoc()){
    return $row['estado'];
    }
}


function actividad_visible($id_actividad,$estado){
    require ("conexion.php");
    $sql_cam_fav = "UPDATE `actividad` SET `visible` = '".$estado."' WHERE `actividad`.`id_actividad` = '".$id_actividad."';";
    $mysqli->query($sql_cam_fav);
    $sql_fav =  "SELECT `visible` FROM `actividad` WHERE `id_actividad` = '".$id_actividad."';";
    $consulta_fav = $mysqli->query($sql_fav);
    if ($row = $consulta_fav->fetch_assoc()){
    return $row['visible'];
    }
}

include_once("funciones.colores.php");// se busca organizar separando lo que es grande o de otro contexto en archivos diferentes

function guardar_valoracion($id_seguimiento,$id_act_val,$id_inscripcion,$fechayhora,$valoracion,$observacion){
require(dirname(__FILE__)."/conexion.php");
require_once(dirname(__FILE__)."/funciones.php");
$datos=array();
$datos['id_seguimiento'] = $id_seguimiento;
$datos['id_actividad'] = $id_act_val;
$datos['id_inscripcion'] = $id_inscripcion;
$datos['fechayhora_valoracion'] = $fechayhora;
$datos['valoracion'] = $valoracion;
$datos['observacion'] = $observacion;
$sql = insertar($datos,'seguimiento_es',true);
#echo $sql;
$consulta = $mysqli->query($sql);
ob_clean();
if($mysqli->affected_rows>0){
echo "1";
}else{
echo "0";
}
}

function inscripcion_actual ($id_estudiante) {
require("conexion.php");
$sqlinscripcion='select * from inscripcion,asignacion where 
asignacion.id_asignacion = inscripcion.id_asignacion and
asignacion.institucion_educativa="'.$_SESSION['id_institucion'].'" and 
id_estudiante="'.$id_estudiante.'" and fecha_inscripcion like "%'.date('Y').'%" ';

$consulta_inscripcion = $mysqli->query($sqlinscripcion);
while ($rowinscripcion = $consulta_inscripcion -> fetch_assoc()) {
    return $rowinscripcion['id_inscripcion'];
}
}




function buscar_estudiantes_actividad($id_actividad,$datos=""){ 
if ($id_actividad!=""){
require(dirname(__FILE__)."/../comun/conexion.php");
require_once(dirname(__FILE__)."/../comun/config.php");
require_once(dirname(__FILE__)."/../comun/funciones.php");
$sql_act = 'SELECT * FROM asignacion RIGHT JOIN `actividad` ON `asignacion`.`id_asignacion` = `actividad`.`id_asignacion` WHERE `actividad`.`id_actividad` = "'.$id_actividad.'"';
    $consulta_act = $mysqli->query($sql_act);
if ($row_act = $consulta_act->fetch_assoc()){
$asignacion = $row_act['id_asignacion'];
$cate = $row_act['id_categoria_curso'];
}
$sql = 'SELECT *,respuesta.id as id_resp_cuestionario,inscripcion.id_inscripcion,`usuario`.`id_usuario` as id_estudiante FROM inscripcion left join `seguimiento_es` on `inscripcion`.`id_inscripcion` = `seguimiento_es`.`id_inscripcion` and "'.$id_actividad.'" = `seguimiento_es`.`id_actividad`, usuario left join `tarea_adjunto` on `usuario`.`id_usuario` = `tarea_adjunto`.`id_estudiante` and "'.$id_actividad.'" = `tarea_adjunto`.`id_actividad`  left join `respuesta` on `usuario`.`id_usuario` = `respuesta`.`id_estudiante` and "'.$id_actividad.'" = `respuesta`.`id_actividad`';
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE inscripcion.estado_inscripcion<>"Retirado" and  inscripcion.id_asignacion= "'.$asignacion.'" ';
#echo $sql;
foreach ($datos as $id => $dato){
$sql .= 'and usuario.id_usuario = inscripcion.id_estudiante and concat(usuario.id_usuario, LOWER ( usuario.nombre )," ", LOWER (usuario.apellido)) LIKE LOWER ( "%'.utf8_decode($dato).'%" )  ';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= "";
}
}
#$sql .=  'estudiante.id_categoria_curso =  "'.$cate.'"';
$sql .=  ' ORDER BY usuario.apellido, usuario.nombre ';
#echo $sql;
$consulta = $mysqli->query($sql);
?>
<div align="center">
<table align="center" border="1" width="100%">
<thead>
<tr>
<th>Estudiante</th>
<th title="Valoración">V</th>
</tr>
</thead>
<tbody>
<?php
while($row=$consulta->fetch_assoc()){
$tarea = array();
if($row_act['adjunto']=="SI"){
if (isset($row['id_tarea_adjunto']) and $row['id_tarea_adjunto']!=""){
$tarea[] = true;
}else{
$tarea[] = false;
}
}
if($row_act['cuestionario']=="SI"){
if (isset($row['id_resp_cuestionario']) and $row['id_resp_cuestionario']!=""){
$tarea[] = true;
}else{
$tarea[] = false;
}
}
#print_r($tarea);
?>
<tr class="filas" id="fila_<?php echo $row['id_inscripcion']?>" onclick="elegirfila_est('<?php echo $row['id_inscripcion']?>');document.getElementById('id_inscripcion').value='<?php echo $row['id_inscripcion']?>';<?php if (isset($row['id_seguimiento']) and $row['id_seguimiento']!="NULL") echo "modificar_nota('".$row['id_seguimiento']."');"; else echo "nueva_nota();"; ?>revisar_tareas('<?php echo $row['id_estudiante']?>','<?php echo $row['apellido']?> <?php echo $row['nombre']?>','<?php echo $row_act['id_cuestionario']?>');<?php
/*revisar_cuestionario('<?php echo $row_act['id_cuestionario']?>','<?php echo $row['id_estudiante']?>');revisar_adjunto('<?php echo $row['id_estudiante']?>','<?php echo $row['apellido']?> <?php echo $row['nombre']?>');*/?>">
<td style="text-align:left" title="Revisar a: <?php echo $row['id_estudiante']?>, <?php echo $row['apellido']?> <?php echo $row['nombre']?> "><img width="30px" style="background-color: transparent; border-radius: 15px;" src="<?php echo READFILE_URL."/foto/".($row['foto'])?>">&nbsp;&nbsp;<?php echo $row['id_inscripcion']?> <?php echo $row['apellido']?> <?php echo $row['nombre'];?><?php echo ' (Monedas: '.$row['puntos'].'  )'?></td><td title="<?php if ($row['observacion']) echo $row['observacion'] ?>" id="seguimiento_<?php echo $row['id_seguimiento']?>" valoracion="<?php echo $row['valoracion']?>" observacion="<?php echo $row['observacion']?>" style="<?php 
if(!in_array(false,$tarea)){
    echo 'background-color:#7bba3f';
    }elseif(in_array(true,$tarea)){
    echo 'background-color:#faae16';
    }elseif(!in_array(true,$tarea)){
    echo 'background-color:#de3153';
    }
?>"><?php
if ($row['valoracion'] and $row['valoracion']!=""){
    echo $row['valoracion'];
}
?></td>
</tr>
<?php
}//fin while
?>
</tbody>
</table></div>
<?php
}
}//fin function buscar estudiante en actividad

function revisar_adjunto($fecha_entrega,$actividad="",$id_usuario="",$nombre=""){
    @session_start();
    
    if ($id_usuario=="") $id_usuario=$_SESSION['id_usuario'];
    if ($nombre!="")echo $nombre.", ";
    elseif ($id_usuario==$_SESSION['id_usuario']) echo "Usted, ";
        require(dirname(__FILE__)."/conexion.php");
     $sql_adj ="SELECT `id_tarea_adjunto`, `id_actividad`, `id_estudiante`, `fechayhora_adjunto`, `observacion`, `adjunto` FROM `tarea_adjunto` WHERE `id_estudiante` = '".$id_usuario."'";
    if($actividad<>""){
    $sql_adj.=' and id_actividad = "'.$actividad.'" ';    
    }
    $consulta_adj = $mysqli ->query($sql_adj);
    if($consulta_adj -> num_rows>0){
        if($row_adj=$consulta_adj->fetch_assoc()){
            echo "Envió la tarea Enviada el día ".formatofechayhora($row_adj['fechayhora_adjunto']).", ";
            echo " con el archivo <a class='btn btn-primary' target='_blank' href='".$row_adj['adjunto']."'>Abrir / Descargar</a>";
        }
    }else{
    if ($_SESSION['rol']=="estudiante"){
    if(date('Y-m-d')<= $fecha_entrega){
    ?>
    
    <form method="post" id="formulario" enctype="multipart/form-data">
         <div style="width:1024px" class="form-group">
         <div class="col-xs-12">
    Subir adjunto: 
    <input onchange="hola_mundo()" class="form-control" type="file" name="file"><br/><br/>
    </div></div>
    Observaciones:
      <div style="width:1024px" class="form-group">
         <div class="col-xs-12">
<textarea placeholder="Descripción Voluntaria..." class="form-control" name="observaciones"></textarea><br/><br/>
    <input type="hidden" value="<?php echo $_GET['a']?>" name="id_act_adj" id="id_act_adj"></div></div>
 <input id="guardarimage"   name="guardarimage" value="Guardar" title="Guardar Adjunto" style="width:50px;"
 type="image" src="../comun/img/disco-flexible.png" />
<?php } ?>
<!--input id="guardarimage" type="button" onclick="guardarimagenajax()" name="guardarimage" value="Guardar"/-->
 </form>
  <div id="respuesta"></div>
     <?php
}else{
echo 'Aún no ha realizado esta tarea';
}
}
}
function revisar_cuestionario($id_cuetionario,$id_estudiante){
require(dirname(__FILE__)."/conexion.php");
require_once(dirname(__FILE__)."/config.php");
require_once(dirname(__FILE__)."/funciones.php");
if (isset($id_cuetionario,$id_estudiante)){
?>
<title>Respuestas</title>
<br>
<div class="jumbotron">
  <div class="container text-center">
    <h1 class="fip">Respuestas de <?php echo consultar_nombre_cuestionario($id_cuetionario); ?></h1>      
  </div>
</div>
    <?php
    $sql = "SELECT * FROM `respuesta` WHERE `pregunta` = '".$id_cuetionario."' and id_estudiante = '".$id_estudiante."'";
    $sql2 = "SELECT `id_preguntas_cuestionario`,`pregunta`, `tipo_pregunta`, `opciones`, `correctas` FROM `preguntas_cuestionario` WHERE `id_cuestionario` = '".$id_cuetionario."'";
    $consulta = $mysqli->query($sql);
    $consulta2 = $mysqli->query($sql2);
    $preguntas = array();
    $tipo = array();
    if ($consulta->num_rows>0){
    while ($row2 = $consulta2->fetch_assoc()){
        $preguntas[$row2['id_preguntas_cuestionario']]=$row2['pregunta'];
        $tipo[$row2['id_preguntas_cuestionario']]=$row2['tipo_pregunta'];
    }
    $respuestas = array();
    while ($row = $consulta->fetch_assoc()){
        $respuesta = json_decode($row['respuesta'],true);
        $fecha = $row['fechayhora'];
        foreach($respuesta as $id => $valor){
            $respuestas[] = $respuesta;
        }
    }
?>
<p>Estudiante: <?php echo consultar_nombre($id_estudiante); ?></p>
<p>Fecha: <?php echo str_replace("<br>","",formatofechayhora($fecha)); ?></p>
<section>
<?php
    $totales = (repeatedElements1($respuestas));

    foreach($totales as $id => $total){
    ?>
    <div class="panel panel-default">
        <div class="panel-heading">
          <h1><?php echo $preguntas[$id];?></h1>
        </div>
        <div class="panel-body cuerpo_cuestionario">
        <ul>
        <?php 
            foreach($total as $id2 => $total2){
                if ($id2!="") echo "<li>".$id2."</li><br>";
            }
        ?>
        </ul>
        </div>   
    </div>
    <?php
    
    }
    }else{
        echo "No hay resultados del cuestionario";
    }
    ?>
</section>
<?php 
}
}
function consultar_foto($id_usuario){
    require(dirname(__FILE__)."/conexion.php");
    require_once(dirname(__FILE__)."/config.php");
    $resultado="user-icon.png";
    $sql = "SELECT `foto` FROM `usuario` WHERE `usuario` = '$id_usuario'";
    $consulta = $mysqli->query($sql);
    if ($row=$consulta->fetch_assoc()){
    $resultado = $row['foto'];
    $resultado = validarfoto($resultado)!="" ? validarfoto($resultado) : $row['foto'];
    }
    $mifoto = "/foto/".$resultado;
    $ruta = READFILE_URL.$mifoto."&token=".encriptar($mifoto);
    return $ruta;//url
}
########## Fin Simuladores de  Controladores  y Modelos combinados ( controller and Model )
function mis_red_favoritos($id_red, $estrellas_json){
    /* inicio favoritos */
@session_start();
$estrellas = json_decode(stripslashes($estrellas_json),true);
if (isset($estrellas[$_SESSION['id_usuario']])) $estrellasmias=$estrellas[$_SESSION['id_usuario']];
else $estrellasmias=0;
if ($estrellasmias>="1"){
?><span id="span_red_<?php echo $id_red ?>" onclick="fav_red('<?php echo $id_red ?>','span_red_<?php echo $id_red ?>')" estado="NO" title="Estrellas <?php echo sumar_valores($estrellas) ?>" class="glyphicon glyphicon-star estrella_red"></span>
<?php }else{
?><span id="span_red_<?php echo $id_red ?>" onclick="fav_red('<?php echo $id_red ?>','span_red_<?php echo $id_red ?>')" estado="SI" title="Estrellas <?php echo sumar_valores($estrellas) ?>" class="glyphicon glyphicon-star-empty estrella_red">
</span>
<?php }
/* fin favoritos */
}

function puntos($usuario,$perador="+",$cantidad="1"){
     require 'conexion.php';
    $sql_visitas = "UPDATE `usuario` SET `puntos`=`puntos` ".$perador.$cantidad." WHERE `id_usuario` = '".$usuario."'";
    $consulta_visitas = $mysqli->query($sql_visitas);
    if ($mysqli->affected_rows>0){
    return true;
    }else{
    return false;
    }
}

function cambiar_docente_asignacion($id_asignacion, $id_docente){
    require(dirname(__FILE__)."/conexion.php");
    $sql = "UPDATE `asignacion` SET  `id_docente`= '$id_docente' WHERE `id_asignacion` = '$id_asignacion'";
    $consulta = $mysqli->query($sql);
    if ($mysqli->affected_rows>0){
        return true;
    }else{
        return false;
    }
}

function autorregistro(){
    require(dirname(__FILE__)."/conexion.php");
    $sql ='select * from config';
    $consulta = $mysqli ->query($sql);
    $extensiones = array();
    $row=$consulta->fetch_assoc();
    if (isset($row['autoregistrarse']) and $row['autoregistrarse']!=""){
    $roles = (explode(",",$row['autoregistrarse']));
    return $roles;
    }else{
    return array();
    }
}
function buscar_docente_para_asignar($parametro_buqueda=""){
require '../comun/conexion.php';
@session_start ;
$sql = 'SELECT * FROM `usuario` where rol like "%docente%" ';
if ($parametro_buqueda!=""){
$sql.= ' ';
$parametro_buqueda_array = explode(" ",$parametro_buqueda);
#print_r($parametro_buqueda_array);
foreach ($parametro_buqueda_array as $id => $parametro_buquedai){
$sql.= ' and concat(LOWER(`usuario`.`nombre`)," ",LOWER(`usuario`.`apellido`)) LIKE "%'.mb_strtolower($parametro_buquedai, 'UTF-8').'%" ';
#echo $parametro_buquedai;
}
}


$sql = str_replace("where  and ","where ",$sql);
/*Consulta para atajo de asignación de docente a curso */
$consulta = $mysqli->query($sql);
 while ($row = $consulta->fetch_assoc()){
echo "<li style='position:relative;float:left;margin:2px;list-style:none;text-align:center;align:center' title='".$row['apellido']." ".$row['nombre']."(".$row['id_usuario'].")"."'><span class='draggable'><span style='display:none' class='id_docente_draggable'>".$row['id_usuario']."</span>";
?>
<img style="z-index:100;position:relative;border-radius:5px;" height="60" src="<?php echo READFILE_URL.'/foto/'.validarfoto($row['foto']); ?>">
<?php echo "<br><span style='z-index:100;position:relative;text-align:center' class='docente_draggable'>".$row['apellido']." ".$row['nombre']."</span></span></li>";
 }
}
function buscar_acudiente_para_asignar($parametro_buqueda=""){
require_once ('../comun/config.php');
require_once ('../comun/funciones.php');
require ('../comun/conexion.php');
//@session_start ;
$sql = 'SELECT * FROM `usuario` where rol like "%estudiante%" ';
if ($parametro_buqueda!=""){
#$sql.= ' where ';
$parametro_buqueda_array = explode(" ",$parametro_buqueda);
#print_r($parametro_buqueda_array);
foreach ($parametro_buqueda_array as $id => $parametro_buquedai){
$sql.= ' and concat(LOWER(`usuario`.`nombre`)," ",LOWER(`usuario`.`apellido`)) LIKE "%'.mb_strtolower($parametro_buquedai, 'UTF-8').'%" ';
#echo $parametro_buquedai;
}
}
$sql.= ' ORDER BY apellido, nombre ';
$sql = str_replace("where  and ","where ",$sql);
/*Consulta para atajo de asignación de docente a curso */
#echo $sql;
$consulta = $mysqli->query($sql);
 while ($row = $consulta->fetch_assoc()){
echo "<li id='draggable'
value='".$row['id_usuario']."'
style='margin:2px;list-style:none;text-align:center;align:center' title='".$row['apellido']." ".$row['nombre']." (".$row['id_usuario'].")'><span ";
echo 'fn-dranganddrop="estudiante_drop" dranganddrop-helper="clone" dranganddrop-datos=\'{"id_acudiente":"'.$row['id_usuario'].'","nombre_acudiente":"'.$row['apellido'].' '.$row['nombre'].'"}\'>';
?>
<img  draggable="true" style="z-index:100;position:relative;border-radius:5px;" height="60" src="<?php echo READFILE_URL.'/foto/'.($row['foto']); ?>">
<?php
echo "<span style='z-index:100;position:relative;display:block;text-align:center' class='acudiente_draggable'>".$row['apellido']." ".$row['nombre']."</span></span></li>";
 }
}
/**
 * Fin Modulo de Foros
 **/
/**
 * Modulo de Mensajes
 */
if (isset($_POST['fav_mensaje'])){
    require(dirname(__FILE__)."/conexion.php");
    $sql_cam_fav = "UPDATE `mensaje` SET `favorito` = '".$_POST['fav_estado']."' WHERE `mensaje`.`id_mensaje` = '".$_POST['fav_mensaje']."';";
    $mysqli->query($sql_cam_fav);
    $sql_fav =  "SELECT `favorito` FROM `mensaje` WHERE `id_mensaje` = '".$_POST['fav_mensaje']."';";
    $consulta_fav = $mysqli->query($sql_fav);
    if ($row = $consulta_fav->fetch_assoc()){
    if ($row['favorito'] == "SI"){
        $return ="NO";
        $class="glyphicon glyphicon-star";
    }
    if ($row['favorito'] == "NO"){
        $return ="SI";
        $class="glyphicon glyphicon-star-empty";
    }
    ob_clean();
    $span = '<input type="hidden" name="fav_mensaje" value="'.$_POST['fav_mensaje'].'">
                <a estado="'.$return.'" id="span_ev_'.$_POST['fav_mensaje'].'" onclick="favorito(\''.$_POST['fav_mensaje'].'\')">
        <span id="span_fav_'.$_POST['fav_mensaje'].'" class="'.$class.'"></span>
        </a>';
    echo $span;
    }
    exit();
}
function buscar_mensajes($datos="",$bandeja="entrada"){
@session_start();
require_once(dirname(__FILE__)."/config.php");
require(dirname(__FILE__)."/conexion.php");
require_once(dirname(__FILE__)."/funciones.php");
require_once (dirname(__FILE__)."/lib/Zebra_Pagination/Zebra_Pagination.php");
//zebra pagination
$sql_favorito = "";
if ($bandeja=="entrada"){
$sql_favorito = " estado_usuario = 'entrada' and ";
$fn_js_page = 'buscar_mensaje();';
$cookiepage = 'page_mensaje';
}elseif($bandeja=="favoritos"){
$sql_favorito = " favorito='SI' and ";
$fn_js_page = 'buscar_mensaje_favoritos();';
$cookiepage = 'page_mensaje_favoritos';
}elseif($bandeja=="suscripciones"){
$sql_favorito = " estado_usuario = 'suscripciones' and ";
$fn_js_page = 'buscar_mensaje_suscripciones();';
$cookiepage = 'page_mensaje_suscripciones';
}elseif($bandeja=="denuncias"){
$sql_favorito = " estado_usuario = 'denuncias' and ";
$fn_js_page = 'buscar_mensaje_denuncias();';
$cookiepage = 'page_mensaje_denuncias';
}elseif($bandeja=="papelera"){
$sql_favorito = " estado_usuario = 'papelera' and ";
$fn_js_page = 'buscar_mensaje_papelera();';
$cookiepage = 'page_mensaje_papelera';
}
$sql = "SELECT * FROM mensaje inner join usuario on mensaje.usuario = usuario.id_usuario WHERE ".$sql_favorito." and mensaje.remite = '".$_SESSION['id_usuario']."' ORDER BY fecha DESC";

$sql_count = "SELECT count(mensaje.id_mensaje) as total_total FROM mensaje inner join usuario on mensaje.remite = usuario.id_usuario WHERE estado_usuario != 'papelera' and mensaje.usuario = '".$_SESSION['id_usuario']."'";
$consulta_total = $mysqli->query($sql_count);
$row_total=$consulta_total->fetch_assoc();
$total_total = $row_total['total_total'];//se debe consultar para paginar
$resultados_pordefecto   = 5;
$resultados = (isset($_COOKIE['numeroresultados_mensaje']) ? $_COOKIE['numeroresultados_mensaje'] : $resultados_pordefecto);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
// Quitar ceros en numeros con 1 digito en paginacion
$paginacion->padding(false);
#print_r($_POST);
$paginacion->fn_js_page($fn_js_page);
$paginacion->cookie_page($cookiepage);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
//
$sql1 = "SELECT * FROM mensaje inner join usuario on mensaje.remite = usuario.id_usuario ";
$sql2 = "SELECT count(mensaje.mensaje) as total_resultados FROM mensaje inner join usuario on mensaje.remite = usuario.id_usuario ";
$sql = "";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE '.$sql_favorito.' estado_usuario ';
if($bandeja!="papelera") $sql .= '!';
$sql .= '= "papelera" and';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(mensaje.mensaje)," ", mensaje.remite," ", LOWER(`usuario`.`nombre`)," ", LOWER(`usuario`.`apellido`)," ", mensaje.fecha," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .= " and mensaje.usuario = '".$_SESSION['id_usuario']."' ORDER BY fecha DESC ";
$sql2 = $sql2.$sql;
#echo $sql2;
$resultado2 = $mysqli->query($sql2);
if ($rowr2=$resultado2->fetch_assoc())
$total_resultados = $rowr2['total_resultados'];
$paginacion->records($total_resultados);
$sql .= " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados.";";
$sql = $sql1.$sql;
$resultado = $mysqli->query($sql);
?>
<h1>Mis Mensajes <?php if ($bandeja=="favoritos") echo "favoritos"; if ($bandeja=="papelera") echo "papelera" ?></h1>
<table>
    <thead>
        <tr tabindex="-1">
        <th colspan="1">
            <?php if ($bandeja=="entrada"){ ?>
            <button class="btn btn-success" onclick="document.location.href='redactar.php'">Nuevo</button>
            <?php } ?>
            </th>
                          <?php if ($bandeja<>"papelera"){ ?>
        <th colspan="1">#</th>
            <?php } ?>

        <th>De</th>
        <th>Mensaje</th>
        <th>Fecha y Hora</th>
        <th>Acciones</th>
    </tr>
    </thead>
<tbody>
<?php
$cont=0;
while ($row=$resultado->fetch_assoc()){
$cont++;
#echo "<pre>";
#print_r($row);
#echo "</pre>";
?>
<menu id="menu_mensajes<?php echo $row['id_mensaje']?>" style="display:none" class="showcase">
  <command label="Mensajes" >
  <hr>
  <command label="Responder" onclick="favorito('<?php echo $row['id_mensaje']; ?>');">

  <command label="<?php if($row['favorito']=="SI"){
  #echo "Quitar marca de ";
  }else{
  #echo "Marcar como ";
  }?>Favorito" onclick="favorito('<?php echo $row['id_mensaje']; ?>');">
</menu>
<span style="display:none">

<form style="display:none" method="post" action="redactar.php">
    <input type="hidden" name="action" value="responder">
    <input type="hidden" name="responder_a" value="<?php echo $row['remite'];?>">
    <input type="hidden" name="responder_n" value="<?php echo $row['nombre']." ".$row['apellido'];?>">
    <input type="hidden" name="responder_mensaje" value="<?php echo $row['mensaje'];?>">
    <button style="display:none" id="responder<?php echo $row['id_mensaje'];?>" class="btn btn-info">Responder</button>
    </form>
</span>
    <tr tabindex="<?php echo $cont ?>" contextmenu="menu_mensajes<?php echo $row['id_mensaje']?>" class="menu_mensajes<?php echo $row['id_mensaje']?> <?php echo $row['leido'] ?>_leido" id="<?php echo $row['id_mensaje']?>" tabindex="-1" ondblclick="leer_mensaje('<?php echo $row['id_mensaje']?>');">
        <td id="<?php echo $row['id_mensaje']?>" style="">
        <!--input type="checkbox" name="sel_men[<?php echo $row['id_mensaje']?>]"-->
        </td>
<script>
$(function(fn){
    fn.contextMenu({
    selector: '.menu_mensajes<?php echo $row['id_mensaje']?>', 
    items: fn.contextMenu.fromMenu($('#menu_mensajes<?php echo $row['id_mensaje']?>'))
});
});
</script>
              <?php if ($bandeja<>"papelera"){ ?>

        <td data-label="Favorito">
        <span id="area_mensaje_<?php echo $row['id_mensaje']?>">
            
        <input type="hidden" name="fav_mensaje" value="<?php echo $row['id_mensaje']; ?>">
        <?php if($row['favorito']=="SI"){ ?>
        <a estado="NO" id="span_ev_<?php echo $row['id_mensaje']; ?>" onclick="favorito('<?php echo $row['id_mensaje']; ?>','<?php echo $fn_js_page ?>')">
        <span id="span_fav_<?php echo $row['id_mensaje']; ?>" class="glyphicon glyphicon-star"></span>
        </a>
        <?php }else{ ?>
        <a  estado="SI" id="span_ev_<?php echo $row['id_mensaje']; ?>" onclick="favorito('<?php echo $row['id_mensaje']; ?>')">
        <span id="span_fav_<?php echo $row['id_mensaje']; ?>" class="glyphicon glyphicon-star-empty"></span>
        </a>
        <?php } ?>
        </span>
        </td>
                <?php } ?>

        <td data-label="De" title="Última vez en linea: <?php echo formatofechayhora($row['ultima_sesion'])?>"><input type="hidden" name="remite" value="<?php echo $row['remite']; ?>"><img  width="30px" style="background-color:#fff;border-radius:15px;" src="<?php echo READFILE_URL."/foto/".($row['foto']);?>">&nbsp;&nbsp;&nbsp;<?php echo $row['nombre']." ".$row['apellido']; ?>
        </td>
        <td title="<?php echo $row['mensaje']; ?>" data-label="Mensaje"><?php echo substr($row['mensaje'], 0, 50);
        if (strlen($row['mensaje'])>50) echo "..."; ?></td>
        <td data-label="Fecha y Hora"><span title="<?php echo formatofechayhora($row['fecha']);?>"><?php echo formatofechayhora($row['fecha']);?></span></td>
        <td><button class="btn btn-danger" onclick="denunciar_mensaje('<?php echo $row['id_mensaje']; ?>');">Denunciar</button>
                      <?php if ($bandeja<>"papelera"){ ?>
        <button class="btn btn-default" onclick="papelera_mensaje('<?php echo $row['id_mensaje']; ?>');">Palpelera</button>
                        <?php } ?>
        </td>
    </tr>
    <?php } ?>
<tr>
    <td colspan="5"><?php $paginacion->render2();?></td>
</tr>
</tbody>
</table>
<?php
}
function num_mensajes($id_usuario=""){
    @session_start();
    if ($id_usuario=="") $id_usuario=$_SESSION['id_usuario'];
      require(dirname(__FILE__)."/conexion.php");
      $sqlms = "SELECT count(id_mensaje) as cant_mensajes from mensaje WHERE leido='NO' and estado_usuario = 'entrada' and usuario = '".$id_usuario."'";
      $resultadoms = $mysqli->query($sqlms);
      if ($rowms=$resultadoms->fetch_assoc())
      echo $rowms['cant_mensajes'];
}
function leer_mensaje($id_mensaje){
require_once("../comun/config.php");
require("../comun/conexion.php");
require_once("../comun/funciones.php");
$sql_leido = "UPDATE `mensaje` SET `leido` = 'SI' WHERE `mensaje`.`id_mensaje` = '".$id_mensaje."';";
$mysqli->query($sql_leido);
$sql = "SELECT * FROM mensaje inner join usuario on mensaje.remite = usuario.id_usuario WHERE mensaje.usuario = '".$_SESSION['id_usuario']."' and mensaje.id_mensaje = '".$id_mensaje."' ORDER BY fecha DESC";
$resultado = $mysqli->query($sql);
while ($row=$resultado->fetch_assoc()){
    ?>
    <div><div>
    <?php
    ?>
    <form method="post" action="redactar.php">
    <input type="hidden" name="responder_a" value="<?php echo $row['remite'];?>">
    <input type="hidden" name="responder_n" value="<?php echo $row['nombre']." ".$row['apellido'];?>">
    <input type="hidden" name="responder_mensaje" value="<?php echo $row['mensaje'];?>">
    <button class="btn btn-info">Responder</button>
    </form>
    <?php
    echo '<span title="Última vez en linea: '.$row['ultima_sesion'].'"><strong>De:</strong> '.$row['nombre']." ".$row['apellido']."</span>";
    echo '&nbsp;&nbsp;<strong>Fecha:</strong> '.formatofechayhora($row['fecha']);
    
    echo "<br>";
    echo '<strong>Mensaje:</strong><p>'.$row['mensaje']."</p>";
    echo "<br>";
    ?>
    </div></div><br><?php
}
}

function ano_estudiante(){

 $sqlp= "SELECT * FROM `ano_lectivo`";
     if ($_SESSION['rol']=="acudiente" or $_SESSION['rol']=="estudiante"){ 
        $sqlp.= ' where nombre_ano_lectivo like "'.date('Y').'" '  ; }
        $sqlp.=" order by id_ano_lectivo desc";
        
return    $sqlp;
}
function notificacion_mensajes(){
require(dirname(__FILE__)."/conexion.php");
require_once(dirname(__FILE__)."/config.php");
@session_start();
$salida=array();
      $sqlms = "SELECT * FROM mensaje inner join usuario on mensaje.remite = usuario.id_usuario WHERE leido='NO' and mensaje.usuario = '".$_SESSION['id_usuario']."'";
      $resultadoms = $mysqli->query($sqlms);
      $cant_mensajes = $resultadoms-> num_rows;
      if ($rowms=$resultadoms->fetch_assoc()) {
        $salida['num'] = $cant_mensajes;
        $salida['url'] = SGA_URL."/mensajes/mis_mensajes.php";
        if ($cant_mensajes>0){
        if ($cant_mensajes==1){
        $salida['mensaje'] = $rowms['nombre']." ".$rowms['apellido']." dice: ".substr($rowms['mensaje'], 0, 60)."...";//$rowms;
        }else{
        $salida['mensaje'] = "Tiene $cant_mensajes mensajes nuevos";
        }
        }else{
        $salida['mensaje'] = "0";
        }
      }
    return $salida;
}

function denunciar_tema($id_tema,$contenido_denuncia,$tipo){
    require(dirname(__FILE__)."/conexion.php");
    @session_start();
    $tema = consultar_nombre_tema($id_tema);
    $usuario = $_SESSION['id_usuario'];
    $nombre_usuario = $_SESSION['nombre']." ".$_SESSION['apellido'];
    //$observacion=$_POST['comentario_denuncia'];
    //$tipo=$_POST['tipo_denuncia'];
    if ($tipo=!"" and $observacion!="") $tipo+=", ";
    $mensaje = "El usuario $nombre_usuario denuncia que el tema $tema, manifestado la siguiente observación del contenido: $tipo $contenido_denuncia";
    $sql = "SELECT * FROM `usuario` WHERE `rol` LIKE '%admin%'";
    $consulta = $mysqli->query($sql);
    $array=array();
    $sql2="";
    $resultado_mensaje=array();
    while ($row=$consulta->fetch_assoc()){
        $resultado_mensaje[] = enviar_mensaje($row['id_usuario'],$mensaje,$usuario,'denuncias');
    }
    if (in_array(false,$resultado_mensaje)){
        return false;
    }else{
        return true;
    }
    
}
function denunciar_mensaje($id_mensaje){
    require("conexion.php");
    $sql_mensaje = "SELECT `mensaje`.`id_mensaje`, `mensaje`.`usuario`, `mensaje`.`mensaje`, `mensaje`.`fecha`, `mensaje`.`leido`, `mensaje`.`remite`, `mensaje`.`estado_usuario`, `mensaje`.`estado_remite`, `mensaje`.`favorito`, CONCAT(usuario_recibe.nombre,\" \",usuario_recibe.apellido) as nombre_recibe, CONCAT(usuario_envia.nombre,\" \",usuario_envia.apellido) as nombre_remite FROM `mensaje`, usuario as usuario_envia, usuario as usuario_recibe WHERE mensaje.remite = usuario_envia.id_usuario and mensaje.usuario = usuario_recibe.id_usuario and id_mensaje = '".$id_mensaje."'";
    $consulta = $mysqli->query($sql_mensaje);
    if ($row1=$consulta->fetch_assoc())
    $datos_mensaje = $row1;
    #print_r($datos_mensaje);
    $mensaje_denuncia="Denuncia de mensaje: ".$datos_mensaje['nombre_recibe']."(".$datos_mensaje['usuario'].") recibió de ".$datos_mensaje['nombre_remite']."(".$datos_mensaje['remite'].") el mensaje: ".$datos_mensaje['mensaje']." el dia ".formatofechayhora($datos_mensaje['fecha']);
    $sql = "SELECT * FROM `usuario` WHERE `rol` LIKE '%admin%'";
    $consulta = $mysqli->query($sql);
    $array=array();
    $sql2="";
    while ($row=$consulta->fetch_assoc()){
        $sql2 .= "INSERT INTO `mensaje`(`usuario`, `mensaje`, `fecha`, `remite`, `estado_usuario`) VALUES ('". $row['id_usuario']."','".$mensaje_denuncia."','".date("Y-m-d H:i:s")."','".$datos_mensaje['usuario']."','denuncias');";
    }
        $consulta2 = $mysqli->multi_query($sql2);
        if ($mysqli->affected_rows>0){
            return true;
        }else{
            return false;
        }
}
function papelera_mensaje($id_mensaje){
    require("conexion.php");
    $sql = "UPDATE `mensaje` SET `estado_usuario`='papelera' WHERE `id_mensaje` = '$id_mensaje'";
    $consulta = $mysqli->multi_query($sql);
    if ($mysqli->affected_rows>0){
        return true;
    }else{
        return false;
    }
}
function enviar_mensaje($usuario,$mensaje,$bandeja,$remite=""){
session_start();
require(dirname(__FILE__)."/../comun/conexion.php");
if ($remite=="") $remite = $_SESSION['id_usuario'];
$usuario = $mysqli->real_escape_string($usuario);
$mensaje = $mysqli->real_escape_string($mensaje);
$sql = "INSERT INTO mensaje (usuario,mensaje,remite,estado_usuario) VALUES ('".$usuario."','".$mensaje."','".$remite."','".$bandeja."')";
$resultado = $mysqli->query($sql);
if($mysqli->affected_rows>0){
    return true;
}else{
    return false;
}
}
function bandeja_salida($datos=""){
@session_start();
require_once(dirname(__FILE__)."/config.php");
require(dirname(__FILE__)."/conexion.php");
require_once(dirname(__FILE__)."/funciones.php");
require_once (dirname(__FILE__)."/lib/Zebra_Pagination/Zebra_Pagination.php");
//zebra pagination
#print_r($_POST);
$sql = "SELECT * FROM mensaje inner join usuario on mensaje.usuario = usuario.id_usuario WHERE estado_usuario != 'papelera' and mensaje.remite = '".$_SESSION['id_usuario']."' ORDER BY fecha DESC";
//parametros
$sql_count=	 "SELECT count(mensaje.id_mensaje) as total_total FROM mensaje inner join usuario on mensaje.usuario = usuario.id_usuario WHERE estado_usuario != 'papelera' and mensaje.remite = '".$_SESSION['id_usuario']."'";
$consulta_total = $mysqli->query($sql_count);
$row_total=$consulta_total->fetch_assoc();
$total_total = $row_total['total_total'];//se debe consultar para paginar
$resultados_pordefecto   = 5;
$resultados = (isset($_COOKIE['numeroresultados_mensaje']) ? $_COOKIE['numeroresultados_mensaje'] : $resultados_pordefecto);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
// Quitar ceros en numeros con 1 digito en paginacion
$paginacion->padding(false);
#print_r($_POST);
$paginacion->fn_js_page('bandeja_salida();');
$cookiepage = 'page_bandeja_salida';
$paginacion->cookie_page($cookiepage);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
//
$sql1 = "SELECT * FROM mensaje inner join usuario on mensaje.usuario = usuario.id_usuario ";
$sql2 = "SELECT count(mensaje.mensaje) as total_resultados FROM mensaje inner join usuario on mensaje.usuario = usuario.id_usuario ";
$sql = "";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE estado_usuario != "papelera" and';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(mensaje.mensaje)," ", mensaje.remite," ", LOWER(`usuario`.`nombre`)," ", LOWER(`usuario`.`apellido`)," ", mensaje.fecha," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .= " and mensaje.remite = '".$_SESSION['id_usuario']."' ORDER BY fecha DESC ";
$sql2 = $sql2.$sql;
$resultado2 = $mysqli->query($sql2);
if ($rowr2=$resultado2->fetch_assoc())
$total_resultados = $rowr2['total_resultados'];
$paginacion->records($total_resultados);
$sql .= " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados.";";
$sql = $sql1.$sql;
//parametros
$resultado = $mysqli->query($sql);
?>
<h1>Mensajes Enviados</h1>
<table>
    <thead>
        <tr tabindex="-1">
        <th>#</th>
        <th>Para</th>
        <th>Mensaje</th>
        <th>Fecha y Hora</th>
        <th>#</th>
        </tr>
    </thead>
<tbody>
<?php
while ($row=$resultado->fetch_assoc()){
?>
    <form name="mensaje_<?php echo $row['id_mensaje']?>" id="mensaje_<?php echo $row['id_mensaje']?>" method="post">
    <tr id="<?php echo $row['id_mensaje']?>" tabindex="-1" ondblclick="document.getElementById('mensaje_<?php echo $row['id_mensaje']?>').submit();">
        <td id="<?php echo $row['id_mensaje']?>" style=""><input type="hidden" name="id_mensaje" value="<?php echo $row['id_mensaje']; ?>"><input hidden type="checkbox" name="sel_men[<?php echo $row['id_mensaje']?>]"></td>
        <td><input type="hidden" name="remite" value="<?php echo $row['remite']; ?>"><img  width="30px" style="background-color:#fff;border-radius:15px;" src="<?php
        $mifoto = ($row['foto']);
        echo READFILE_URL."/foto/".$mifoto;?>">&nbsp;&nbsp;&nbsp;<?php echo $row['nombre']." ".$row['apellido']; ?>
        <td><?php echo substr($row['mensaje'], 0, 50);
        if (strlen($row['mensaje'])>50) echo "..."; ?></td>
        
        <td><span title="<?php echo str_replace("<br>","",formatofechayhora($row['fecha']));?>"><?php echo formatofechayhora($row['fecha']);?></span></td>
        <td title="Enviado<?php if($row['leido']=="SI") echo " y Leido"?>."><span style="font-size: 7pt;" class="glyphicon glyphicon-ok"></span><?php
        if($row['leido']=="SI"){ ?>
        <span style="font-size: 7pt;margin-left:-7px" class="glyphicon glyphicon-ok"></span>
        <?php } ?>
        </td>
    </tr>
    </form>
    <?php
}
?>
<tr>
    <td colspan="5"><?php $paginacion->render2();?></td>
</tr>
</tbody>
</table>
<link rel="stylesheet" href="estilo_tabla2.css">
<?php
}
function leer_mensaje_enviado($id_mensaje){
if ($id_mensaje!=""){
$sql = "SELECT * FROM mensaje inner join usuario on mensaje.remite = usuario.id_usuario WHERE mensaje.usuario = '".$_SESSION['id_usuario']."' and mensaje.id_mensaje = '".$_POST['id_mensaje']."' ORDER BY fecha DESC";
$resultado = $mysqli->query($sql);
while ($row=$resultado->fetch_assoc()){
    ?>
    <div><div>
    <?php
    #echo 'id_mensaje: '.$row['id_mensaje']; echo "<br>";
    #echo 'usuario: '.$row['usuario']; echo "<br>";
    echo 'De: '.$row['remite'];
    echo ' Fecha: '.formatofecha($row['fecha']);
    echo "<br>";
    echo 'mensaje: '.substr($row['mensaje'], 0, 50);
    if (strlen($row['mensaje'])>50) echo "...";
    echo "<br>";
    #echo 'leido: '.$row['leido']; echo "<br>";
    ?>
    </div></div><?php
}
}
}
/**
 * Fin Modulo de Mensajes
 **/
/**
 * Modulo de Cuestionarios
 */
 function buscar_cuestionario_pag($datos=""){
buscar_cuestionario($datos,"cuestionario","buscar_cuestionario_pag();","page_cuestionarios_pag");
}
function buscar_cuestionario_encurso($datos=""){
buscar_cuestionario($datos,"curso","buscar_cuestionario();","page_cuestionarios_c");
}
function buscar_cuestionario($datos,$peticion,$funcionjs,$cookiepage){
ob_clean();
require((dirname(__FILE__))."/../comun/conexion.php");
require_once((dirname(__FILE__))."/../comun/config.php");
require_once '../comun/lib/Zebra_Pagination/Zebra_Pagination.php';
$resultados = (isset($_COOKIE['numeroresultados_cuesionario']) ? $_COOKIE['numeroresultados_cuesionario'] : 8);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page($cookiepage);//dimensionar $_COOKIE['page']
// Quitar ceros en numeros con 1 digito en paginacion
$paginacion->padding(false);
if (isset($_COOKIE["$cookiepage"])) $_GET['page'] = $_COOKIE["$cookiepage"];
//
$sql = "SELECT `cuestionario`.`id`,`cuestionario`.`estrellas`, `cuestionario`.`nombre`, `cuestionario`.`fecha`, concat(`usuario`.`nombre`,' ',`usuario`.`apellido`) as usuario,`usuario`.`foto`, `tipo_cuestionario` as nombre_tipo_cuestionario FROM `cuestionario` inner join `usuario` on `cuestionario`.`usuario` = `usuario`.`id_usuario`";
$sql_todos_sis = "SELECT count(cuestionario.id) as numero_cuestionarios FROM cuestionario  inner join `usuario` on `cuestionario`.`usuario` = `usuario`.`id_usuario`";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$cont =  0;
$sql .= ' WHERE ';
$sql_todos_sis .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql_aux1 = ' concat(LOWER(`cuestionario`.`nombre`)," ", `cuestionario`.`fecha`," ", LOWER(`usuario`.`nombre`)," ", LOWER(`cuestionario`.`tipo_cuestionario`)," ", LOWER(`usuario`.`apellido`)," ") LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$sql .= $sql_aux1;
$sql_todos_sis .= $sql_aux1;
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
$sql_todos_sis .= " and ";
}
}
#echo $sql_todos_sis;
$consulta_todos_sis = $mysqli->query($sql_todos_sis);
if($row_todos_sis=$consulta_todos_sis->fetch_assoc()){
    extract($row_todos_sis);
}
$sql_total = "SELECT count(cuestionario.id) as numero_total_cuestionarios FROM cuestionario";
$consulta_total = $mysqli->query($sql_total);
if($row_total=$consulta_total->fetch_assoc()){
    extract($row_total);
}
$paginacion->records($numero_cuestionarios);
$sql .=  " ORDER BY fecha desc ";
$sql_todos = $sql;
$sql .= 'LIMIT ' . (($paginacion->get_page() - 1) * $resultados) . ', ' . $resultados;
#echo $sql;
$consulta2 = $mysqli->query($sql);
$consulta_todos = $mysqli->query($sql_todos);

$array_todos = array();
while($row_todos=$consulta_todos->fetch_assoc()){
    #print_r($row_todos);
    $array_todos[]=$row_todos;
}
#echo $sql;
$total_mostrados = $consulta2->num_rows;
#echo $total_mostrados;
if($peticion=="cuestionario"){
    ?>
<div class="row">
 <div class="col-md-4"><label><input type='radio' checked name='modo_vista' id='modo_vista' title=''>&nbsp;&nbsp;Vista Previa</label></div>
 <div class="col-md-4"><label><input type='radio' name='modo_vista' id='modo_resumen' title=''>&nbsp;&nbsp;Vista Resumen</label></div>
 <div class="col-md-4"><label><input type='radio' name='modo_vista'  id='modo_diseño' title=''>&nbsp;&nbsp;Vista Diseño</label></div>
</div>
 <?php
#echo "<p><label><input type='checkbox' id='modo_diseño' title=''>Vista Diseño</label></p>";
}
echo "<p title='Existen ".$numero_total_cuestionarios." cuestionarios en este sistema'>Cuestionarios en pantalla: ".$total_mostrados." de ".count($array_todos); ?>
</p>
<center>
<ul class="bs-glyphicons" style="max-width:920px;">
<?php

while($row = $consulta2->fetch_assoc()){
if($peticion=="curso"){
?>
<li id="fila_<?php echo $row['id']?>" class="filas" onclick="elegirfila('<?php echo $row['id']?>','<?php echo $row['nombre']?>');if(document.getElementById('micheckbox_form_no').checked==true){elegirframe('<?php echo SGA_URL ?>/cuestionario/ver_cuestionario.php?embebido&enc=<?php echo $row['id']?>');}else{elegirframe('<?php echo SGA_URL ?>/cuestionario/disenador.php?embebido&enc=<?php echo $row['id']?>');}" ondblclick="window.open('<?php echo SGA_URL ?>/cuestionario/ver_cuestionario.php?enc=<?php echo $row['id']?>')" title="Creada por <?php echo $row['usuario']?> el <?php echo $row['fecha']; ?>">
<?php
/* inicio estrellas */
$estrellas = json_decode(stripslashes($row['estrellas']),true);
if (isset($estrellas[$_SESSION['id_usuario']]))
$estrellasmias=$estrellas[$_SESSION['id_usuario']];
else
$estrellasmias=0;
if ($estrellasmias>="1"){
?><span style="z-index: 101;" id="span_cue_<?php echo $row['id']?>" onclick="fav_cuestionario('<?php echo $row['id']?>','span_cue_<?php echo $row['id']?>')" estado="NO" title="Estrellas <?php echo array_sum($estrellas) ?>" class="glyphicon glyphicon-star estrella_cuestionario"></span>
<?php }else{
?><span style="z-index: 101;" id="span_cue_<?php echo $row['id']?>" onclick="fav_cuestionario('<?php echo $row['id']?>','span_cue_<?php echo $row['id']?>')" estado="SI" title="Estrellas <?php echo array_sum($estrellas) ?>" class="glyphicon glyphicon-star-empty estrella_cuestionario"></span>
<?php }
/* fin estrellas */
?>

<p><img style="bottom:0px;right:0px;border-radius:10px;margin:0;margin-bottom: -30px;left: -34px;top: -10px;    position: relative;" height="20px" src="<?php echo READFILE_URL."/foto/".($row['foto'])?>"></p>
<span style="z-index: 100;top: -20px;position: relative;">
<?php echo $row['nombre']?>
</span>
</li>
<?php
}
if($peticion=="cuestionario"){

?>
<menu id="menu_cuestionario<?php echo $row['id']?>" style="display:none" class="showcase">
  <command label="<?php echo $row['nombre']?>" >
  <hr>
  <menu label="Ordenar por...">
      <menuitem onclick="alert2('fecha')" label="Fecha"></menuitem>
      <menuitem onclick="alert2('nombre')" label="Nombre"></menuitem>
      <menuitem onclick="alert2('autor')" label="Autor"></menuitem>
   </menu>
  <command label="Vista Cuestionario" onclick="window.open('<?php echo SGA_URL ?>/cuestionario/ver_cuestionario.php?enc=<?php echo $row['id']?>');">
  <command label="Vista Diseño" onclick="window.open('<?php echo SGA_URL ?>/cuestionario/disenador.php?enc=<?php echo $row['id']?>');">
  <command label="Resumen de Respuestas" onclick="window.open('<?php echo SGA_URL ?>/cuestionario/ver_resumen.php?enc=<?php echo $row['id']?>');">
  <command label="Crear una copia" onclick="alert('Copiar');">
</menu>
<span contextmenu="menu_cuestionario<?php echo $row['id']?>" class="menu_cuestionario<?php echo $row['id']?>">
<li id="fila_<?php echo $row['id']?>" class="filas" onclick="elegirfila('<?php echo $row['id']?>','<?php echo $row['nombre']?>');" ondblclick="if(document.getElementById('modo_diseño').checked==true){window.open('<?php echo SGA_URL ?>/cuestionario/disenador.php?enc=<?php echo $row['id']?>');}else if(document.getElementById('modo_resumen').checked==true){window.open('<?php echo SGA_URL ?>/cuestionario/ver_resumen.php?enc=<?php echo $row['id']?>');}else{window.open('<?php echo SGA_URL ?>/cuestionario/ver_cuestionario.php?enc=<?php echo $row['id']?>')};" title="Creada por <?php echo $row['usuario']?> el <?php echo $row['fecha']; ?>">
<?php
/* inicio favoritos */
$estrellas = json_decode(stripslashes($row['estrellas']),true);
@$estrellasmias=$estrellas[$_SESSION['id_usuario']];
if ($estrellasmias>="1"){
?><span style="z-index: 101;" id="span_cue_<?php echo $row['id']?>" onclick="fav_cuestionario('<?php echo $row['id']?>','span_cue_<?php echo $row['id']?>')" estado="NO" title="Estrellas <?php echo count($estrellas) ?>" class="glyphicon glyphicon-star estrella_cuestionario"></span>
<?php }else{
?><span style="z-index: 101;" id="span_cue_<?php echo $row['id']?>" onclick="fav_cuestionario('<?php echo $row['id']?>','span_cue_<?php echo $row['id']?>')" estado="SI" title="Estrellas <?php echo count($estrellas) ?>" class="glyphicon glyphicon-star-empty estrella_cuestionario"></span>
<?php }
/* inicio favoritos */
?>
<p><img style="bottom:0px;right:0px;border-radius:10px;margin:0;left: -34px;top: -10px;    position: relative;" height="20px" src="<?php echo READFILE_URL."/foto/".validarfoto($row['foto'])?>"></p>
<span style="z-index: 100;top: -30px;position: relative;">
<?php echo $row['nombre']?>
</span>
</li>
</span>
<script>
setTimeout(function(){
$(function(fn){
    fn.contextMenu({
    selector: '.menu_cuestionario<?php echo $row['id']?>', 
    items: fn.contextMenu.fromMenu($('#menu_cuestionario<?php echo $row['id']?>'))
});
});
}, 5000);
</script>
<?php
}
}//fin while
?>
</ul>
</center>
<?php
$paginacion->render2();
}//fin function buscar
function guardar_cuestionario($cuestionario){
@session_start();
if ($_SESSION['rol']=="admin" or $_SESSION['rol']=="docente" ){
ob_clean();//limpia el texto para evitar errores de validación con javascript
//Funcion que crea nuevo cuestionario o modifica existente mediante javascript
require((dirname(__FILE__))."/conexion.php");
// real_escape_string -> evita inyeccion SQL
$cuestionario['id'] = $mysqli->real_escape_string($cuestionario['id']);
$cuestionario['nombre'] = $mysqli->real_escape_string($cuestionario['nombre']);
$cuestionario['tipo_cuestionario'] = $mysqli->real_escape_string($cuestionario['tipo_cuestionario']);
if ($cuestionario['nombre'] ==""){
    echo " Por favor escriba un nombre para el cuestionario e intente de nuevo";
    exit();
    }
@session_start();
    $sql_cuestionario = "INSERT INTO `cuestionario` (`id`,`nombre`,`fecha`,`tipo_cuestionario`, `usuario`) VALUES ('".$cuestionario['id']."','".$cuestionario['nombre']."','".date("Y-m-d")."','".$cuestionario['tipo_cuestionario']."','".$_SESSION['id_usuario']."') ON DUPLICATE KEY UPDATE `nombre`= VALUES(`nombre`),`tipo_cuestionario`= VALUES(`tipo_cuestionario`);";
    $guardar = $mysqli->query($sql_cuestionario);
    if ($mysqli->affected_rows>0){
    echo "1";
    }else{
    echo "";
    }
}
}
 /*Inicio Graficas*/
if (isset($_GET['fav_cuestionario'])){
    @session_start();
    require(dirname(__FILE__)."/conexion.php");
    $usuario = $_SESSION['id_usuario'];
    $sql = "SELECT `estrellas`, usuario FROM `cuestionario` WHERE `id` = '".$_POST['id_cue']."'";
    $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();
    if ($row['estrellas']=="null" or $row['estrellas']=="NULL" or $row['estrellas']=="")
    $estrellas=array();
    else
    $estrellas = json_decode($row['estrellas'],true);
    if ($_POST['estado']=="SI"){
    $estrellas[$usuario]="1";
    }elseif ($_POST['estado']=="NO"){
    unset($estrellas[$usuario]);
    }
    #print_r($_POST);
    $sql_up = "UPDATE `cuestionario` SET `estrellas`= '".json_encode($estrellas)."' WHERE `id` = '".$_POST['id_cue']."'";
    $consulta_up = $mysqli->query($sql_up);
    if ($mysqli->affected_rows>0){
    if ($_POST['estado']=="SI") $abonar = puntos($usuario,"+");
    elseif ($_POST['estado']=="NO") $abonar = puntos($usuario,"-");
    }
    $sql_fin = "SELECT `estrellas` FROM `cuestionario` WHERE `id` = '".$_POST['id_cue']."'";
    $consulta_fin = $mysqli->query($sql_fin);
    $row_fin = $consulta_fin->fetch_assoc();
    $estrellas_fin = json_decode($row_fin['estrellas'],true);
    $estrellasmias=$estrellas_fin[$_SESSION['id_usuario']];
    if($estrellasmias=="1"){
    $estado_fin = "NO";
    }else{
    $estado_fin = "SI";
    }
    $salida = array("estado"=>$estado_fin,"total"=>count($estrellas_fin));
    echo json_encode($salida);
    exit();
}
/**
 * Fin Modulo de Cuestionarios
 **/
/**
 * Modulo de Eventos
 */
if (isset($_GET['fav_eve'])){
    @session_start();
    require(dirname(__FILE__)."/conexion.php");
    $usuario = $_SESSION['id_usuario'];
    $sql = "SELECT `estrellas` FROM `eventos` WHERE `id` = '".$_POST['id_evento']."'";
    $consulta = $mysqli->query($sql);
    $row = $consulta->fetch_assoc();
    if ($row['estrellas']=="null" or $row['estrellas']=="NULL" or $row['estrellas']=="")
    $estrellas=array();
    else
    $estrellas = json_decode($row['estrellas'],true);
    if ($_POST['estado']=="SI"){
    $estrellas[$usuario]="1";
    }elseif ($_POST['estado']=="NO"){
    unset($estrellas[$usuario]);
    }
    #print_r($_POST);
    $sql_up = "UPDATE `eventos` SET `estrellas`= '".json_encode($estrellas)."' WHERE `id_evento` = '".$_POST['id_evento']."'";
    $consulta_up = $mysqli->query($sql_up);
    $sql_fin = "SELECT `estrellas` FROM `eventos` WHERE `id` = '".$_POST['id_evento']."'";
    $consulta_fin = $mysqli->query($sql_fin);
    $row_fin = $consulta_fin->fetch_assoc();
    $estrellas_fin = json_decode($row_fin['estrellas'],true);
    $estrellasmias=$estrellas_fin[$_SESSION['id_usuario']];
    if($estrellasmias=="1"){
    $estado_fin = "NO";
    }else{
    $estado_fin = "SI";
    }
    $salida = array("estado"=>$estado_fin,"total"=>count($estrellas_fin));
    echo json_encode($salida);
    exit();
}
if(isset($_GET['guardar_evento'])){
  require(dirname(__FILE__)."/conexion.php");
  $nombre_eve = $mysqli->real_escape_string($_POST['nombre_eve']);
  $descripcion_eve = $mysqli->real_escape_string($_POST['descripcion_eve']);
  $fecha_eve = $mysqli->real_escape_string($_POST['fecha_eve']);
  $hora_inicio_eve = $mysqli->real_escape_string($_POST['hora_inicio_eve']);
  $hora_fin_eve = $mysqli->real_escape_string($_POST['hora_fin_eve']);
  guardar_evento($nombre_eve,$descripcion_eve,$fecha_eve,$hora_inicio_eve,$hora_fin_eve);
  exit();
}
function guardar_evento($nombre_eve,$descripcion_eve,$fecha_eve,$hora_inicio_eve,$hora_fin_eve){
      require(dirname(__FILE__)."/conexion.php");
      $sql = "INSERT INTO `eventos`(`nom`, `descripcion`, `fecha`, `hora_inicio`, `hora_fin`, `id_usuario`) VALUES ('$nombre_eve','$descripcion_eve','$fecha_eve','$hora_inicio_eve','$hora_fin_eve','".$_SESSION['id_usuario']."');";
      $consulta = $mysqli->query($sql);
      if($mysqli->affected_rows=="1")
      echo "1";
      else
      echo "0";
}
/**
 * Fin Modulo de Eventos
 **/
 function sumar_valores($array){
     $resultado = 0;
     if (is_array($array) and count($array>0))
     foreach ($array as $id=>$valor){
         $resultado+=$valor;
     }
 return $resultado;
 }
 
 function consultar_acudientes($dato=""){
 $resultado = 0;
  $sql2= "SELECT * FROM usuario WHERE rol LIKE '%acudiente%';";
 require(dirname(__FILE__)."/conexion.php");
 ?>
<select  title="" class="form-control" name="id_estudiante" id="id_estudiante"  required multiple>
<?php 
echo '<option value="">Seleccione una opci&oacute;n</option>';
$consulta2 = $mysqli->query($sql2);
while($row2=$consulta2->fetch_assoc()){
echo '<option value="'.$row2['id_usuario'].'"';if (isset($row['id_estudiante']) and $row['id_estudiante'] == $row2['id_usuario']) echo " selected ";echo '>'.$row2['nombre']." ".$row2['apellido'].'</option>';
}
echo '</select>';
return $resultado;
}
function asignar_acudiente($id_acudiente,$id_estudiante){
    require(dirname(__FILE__)."/conexion.php");
    $sql = "INSERT INTO `guagua`.`acudiente_estudiante` (`id_acudiente`,`id_estudiante`) VALUES ('$id_acudiente', '$id_estudiante');";
    $consulta = $mysqli->query($sql);
    if ($mysqli->errno != 1062){
        if ($consulta==1){
            echo "1";
        }else{
            echo "0";
        }
    }else{
        echo "1062";
    }
}

 function consultar_acudientes_estudiante($id_estudiante){
 require_once ('../comun/config.php');
require_once ('../comun/funciones.php');
require(dirname(__FILE__)."/conexion.php");
 $resultado = 0;
$sql2= "SELECT * FROM `acudiente_estudiante` inner join `usuario` on `usuario`.`id_usuario` = `acudiente_estudiante`.`id_acudiente`  WHERE `acudiente_estudiante`.`id_estudiante` = '$id_estudiante'";
$consulta = $mysqli->query($sql2);
while ($row = $consulta->fetch_assoc()){
echo "<li style='margin:2px;list-style:none;text-align:center;align:center' title='".$row['apellido']." ".$row['nombre']." (".$row['id_usuario'].")'><span style='width:60px'";
#echo 'fn-dranganddrop="estudiante_drop" dranganddrop-helper="clone" dranganddrop-datos=\'{"id_acudiente":"'.$row['id_usuario'].'","nombre_acudiente":"'.$row['apellido'].' '.$row['nombre'].'"}\''; 
echo'>';
?>
<span class="close" title="Retirar acudiente <?php echo $row['apellido']." ".$row['nombre']." (".$row['id_usuario'].")"; ?>" onclick="if_confirm_swal('Esta seguro que desea retirar el acudiente <?php echo $row['apellido']." ".$row['nombre']." (".$row['id_usuario'].") del estudiante ".consultar_nombre($id_estudiante)." (".$id_estudiante.")"; ?>','eliminar_asignacion_acudiente(\'<?php echo $row['id_acudiente_estudiante']; ?>\')')" style='float:right;position:relative'>&times;</span>
<img style="z-index:100;position:relative;border-radius:5px;" height="60" src="<?php echo READFILE_URL.'/foto/'.validarfoto($row['foto']); ?>">
<?php
echo "<span style='z-index:100;position:relative;display:block;text-align:center' class='acudiente_draggable'>".$row['apellido']." ".$row['nombre']."</span></span></li>";
 }
}
function eliminar_asignacion_acudiente($id){
require_once ('../comun/conexion.php');
 /*Instrucción SQL que permite eliminar en la BD*/ 
$sql = 'DELETE FROM acudiente_estudiante WHERE `acudiente_estudiante`.`id_acudiente_estudiante`="'.$id.'"';
 /*Se conecta a la BD y luego ejecuta la instrucción SQL*/
$eliminar = $mysqli->query($sql);
if ($mysqli->affected_rows>0){
 /*Validamos si el registro fue eliminado con éxito*/ 
return true; 
}else{
return false;
}
}

function count_values_array($array){
     $resultado = 0;
     if (is_array($array) and count($array>0))
     foreach ($array as $id=>$valor){
         $resultado+=count($valor);
     }
    return $resultado; 
 }
 
 function get_format($df) {

    $str = '';
    $str .= ($df->invert == 1) ? ' - ' : '';
    if ($df->y > 0) {
        // years
        $str .= ($df->y > 1) ? $df->y . ' años ' : $df->y . ' año ';
    }elseif ($df->m > 0) {
        // month
        $str .= ($df->m > 1) ? $df->m . ' meses ' : $df->m . ' mes ';
    }elseif ($df->d > 0) {
        // days
        $str .= ($df->d > 1) ? $df->d . ' días ' : ' ayer ';
    }elseif ($df->h > 0) {
        // hours
        $str .= ($df->h > 1) ? $df->h . ' horas ' : $df->h . ' hora ';
    }elseif ($df->i > 0) {
        // minutes
        $str .= ($df->i > 1) ? $df->i . ' mins ' : $df->i . ' min ';
    } elseif ($df->s > 1) {
        // seconds
        $str .= ($df->s > 1) ? $df->s . ' segs ' : $df->s . ' seg ';
    }elseif($df->s <= 1){
        $str .= ($df->s <= 1) ? ' justo ahora ' : ' justo ahora ';
    }
    if (($df->d != 1) and ($df->s > 1)) echo "hace ";
    echo $str;
}
function diferenciaentrefechas_foro($fecha1,$fecha2="now"){
$date1 = new DateTime($fecha1);
$date2 = new DateTime($fecha2);
$diff = $date1->diff($date2);
echo get_format($diff);
}

if (isset($_GET['valida_existe'])){
echo $_POST['tabla'].$_POST['campo'].$_POST['valor'];
ob_clean();
if (valida_existe($_POST['tabla'],$_POST['campo'],$_POST['valor'])){
    echo "1";
}else{
    echo "0";
}
exit();
}
function valida_existe($tabla,$campo,$valor){
    require("conexion.php");
    $sql ="SELECT * FROM `".$tabla."` WHERE `".$campo."` = '".$valor."'";
    $consulta = $mysqli->query($sql);
    if ($consulta->num_rows > 0) {
        return true;
     }else{ 
        return false;
    }
}
if (isset($_GET['valida_existe_par'])){
#echo $_POST['tabla'].$_POST['campo'].$_POST['valor'].$_POST['campo2'].$_POST['valor2'];
ob_clean();
if (valida_existe_par($_POST['tabla'],$_POST['campo'],$_POST['valor'],$_POST['campo2'],$_POST['valor2'])){
    echo "1";
}else{
    echo "0";
}
exit();
}
function valida_existe_par($tabla,$campo,$valor,$campo2,$valor2){
    require("conexion.php");
    $sql ="SELECT * FROM `".$tabla."` WHERE `".$campo."` = '".$valor."' and `".$campo2."` = '".$valor2."'";
    #echo $sql;
    $consulta = $mysqli->query($sql);
    if ($consulta->num_rows > 0) {
        return true;
    }else{
        return false;
    }
}

function formulario_usuario($post){
?>
<script type="text/javascript">
window.addEventListener('load', calculos, false);
function calculos() {
document.getElementById('sinfoto').click();
}
function validaCheckbox(){
var checkboxcam = document.getElementById('cam');
var checkboxsubir = document.getElementById('subir');
var checkboxdeafult = document.getElementById('sinfoto');
  var checkedcam = checkboxcam.checked;
  var checkedsubir = checkboxsubir.checked;
  var checkeddefault= checkboxdeafult.checked;
  if(checkedcam){
  $('#camara').show(1000);
  $('#imagendefault').hide(1000);
  $('#subirfile').hide(1000);
  }

  if(checkedsubir){
    $('#subirfile').show(1000);
  $('#imagendefault').hide(1000);
  $('#camara').hide(1000);
  }


if(checkeddefault){
  $('#imagendefault').show(1000);
  $('#camara').hide(1000);
  $('#subirfile').hide(1000);
  validardefault();
  }
} 
function validardefault(){

   var select = document.getElementById("genero"); 
var genero=select.value;      
if(genero=='m'){
  document.getElementById("default").src = "../sga-data/foto/user-icon.png";
}
if(genero=='f'){
  document.getElementById("default").src = "../sga-data/foto/user-iconf.png";
}


}

function validargenero(){

var select = document.getElementById("genero"); 
var genero=select.value;     
var foto=document.getElementById("img_foto").src;
if(foto=="http://localhost//guagua/sga-data//foto/user-icon.png" or foto=="http://localhost//guagua/sga-data//foto/user-iconf.png"){
  if(genero=='m'){
document.getElementById("img_foto").src = "../sga-data/foto/user-icon.png";
document.getElementById("default").src = "../sga-data/foto/user-icon.png";

}
if(genero=='f'){
document.getElementById("img_foto").src = "../sga-data/foto/user-iconf.png";
document.getElementById("default").src = "../sga-data/foto/user-iconf.png";
}

}




}

</script>
<?php

$_POST=$post;
require ("../comun/conexion.php");
require_once ("../comun/funciones.php");
if (isset($_POST['cod'])) $usuario = $_POST['cod'];
//verificar permisos
if ($_POST['submit']=="perfil"){
$usuario = $_POST['perfil_usuario'];
    if (isset($_SESSION['rol']) and $_SESSION['id_usuario']==$usuario){
        $tiene_permiso=true;
    }else{
        if (isset($_SESSION['rol']) and$_SESSION['rol']=="admin"){
            $tiene_permiso=true;
        }else{
            $tiene_permiso=false;
        }
    }
}else{
    if (isset($_SESSION['rol']) and $_SESSION['rol']=="admin"){
        $tiene_permiso=true;
    }else{
        $tiene_permiso=false;
    }
}
//fin verificar permisos
if (($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar" or $_POST['submit']=="perfil")){
if ($_POST['submit']=="Nuevo"){
$textoh1 ="Registrar";
$textobtn ="Registrar";
}
if ($_POST['submit']=="Modificar" or $_POST['submit']=="perfil"){
require ("../comun/conexion.php");
$sql = "SELECT * FROM `usuario` WHERE id_usuario ='".$usuario."' Limit 1";
$consulta = $mysqli->query($sql);
 /*echo $sql;*/ 
$row=$consulta->fetch_assoc();
if ($_POST['submit']=="perfil"){
$textoh1 ="Actualizar Perfil";
}
if ($_POST['submit']=="Modificar"){
$textoh1 ="Modificar";
}
$textobtn ="Actualizar";
}

 ?>
<form <?php if (($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar") or ($_POST['submit']=="perfil" and $tiene_permiso)){ ?> ENCTYPE="multipart/form-data" autocomplete="off" class="col-md-6" id="form_usuario" name="form1" method="post" action="<?php
if ($_POST['submit']=="perfil"){
    echo "perfil.php";
}else{
    echo "usuario.php";
}
?>"<?php } ?>>
<!--div rojo-->
<div  class="container">
<div  class="col-md-10">
<h1><?php echo $textoh1 ?></h1>
  <style>
    @media only screen and (max-width: 700px) {
      video {
        max-width: 100%;
      }
    }
  </style>
  <script type="text/javascript">
  

  </script>
<script  src="../comun/js/funciones.js"></script>
<script type="text/javascript">

  function activo2020(valor){
if(valor=="subir"){
//var isChecked = document.getElementById('imagen').checked;
//if(isChecked){
  mitoogle('#cam');
}

  }
 
</script>

<input name="u" type="hidden" id="u" value="<?php if (isset($_GET['u']))  echo $_GET['u']; else if (isset($_POST['u']))  echo $_POST['u']; else echo "admin" ?>" size="120">
<input type="hidden" name="MAX_FILE_SIZE" id="MAX_FILE_SIZE" value="<?php echo 1024 * (1024 * 4)?>" />
<?php if ($_POST['submit']=="Modificar" or $_POST['submit']=="perfil"){ ?>
<input name="cod" type="hidden" id="cod" value="<?php if (isset($row['id_usuario']))  echo $row['id_usuario'] ?>" size="120" required>
<?php } ?>

<div class="form-group"><p><label for="foto">
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Elige tu foto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" >
        </label>
  <style>
    @media only screen and (max-width: 700px) {
      video {
        max-width: 100%;
      }
    }
  </style>
 <h1>Tomar foto</h1>
  <div class="form-check">
    <input onclick="validaCheckbox()" class="form-check-input" type="radio" name="exampleRadios" id="cam" value="cam" >
  <label class="form-check-label" for="exampleRadios1">
    Cámara
  </label>
  <input onclick="validaCheckbox()" class="form-check-input" type="radio" name="exampleRadios" id="subir" value="subir" >
  <label class="form-check-label" for="exampleRadios1">
    Subir
  </label>
  <input onclick="validaCheckbox()" class="form-check-input" type="radio" name="exampleRadios" id="sinfoto" value="sinfoto" checked>
  <label class="form-check-label" for="exampleRadios1">
    Sin Foto
  </label>

</div>
  <div id="camara">
    <select name="listaDeDispositivos" id="listaDeDispositivos"></select>
   <input class="form-check-input" type="radio" name="opcionescamara" id="iniciar" value="iniciar" >
 <label>Iniciar</label>
   <input class="form-check-input" type="radio" name="opcionescamara" id="boton" value="boton" >
 <label>Tomar foto</label>
   <input class="form-check-input" type="radio" name="opcionescamara" id="guardar" value="Guardar" >
  <label>Guardar</label>
   <input class="form-check-input" type="radio" name="opcionescamara" id="detener" value="detener" >
<label>Detener</label>

<div style="width:200;height:400;position:absolute;margin-top:3%;margin-left:3%;"> 
  <video style="width:100;height:400"  muted="muted" id="video"></video>
<input type="hidden" id="inputcamara" name="inputcamara" value="">
</div > 
<script src="script.js"></script>
<script
  src="https://code.jquery.com/jquery-3.4.1.js"
  integrity="sha256-WpOohJOqMqqyKL9FccASB9O0KwACQJpFTUBLTYOVvVU="
  crossorigin="anonymous"></script>
<script src="../comun/js/funciones.js"></script>
<div >
<canvas  id="canvas" style="border: black 5px solid;
;width:42%;height:65%;margin-top:4%;position:relative;margin-left:50%; background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcTeb9mdXVg787Q5XA5HGwkiye9o_qa9W9fDOMN9rQrfZp9Otp5D'); background-repeat: repeat-y; background-repeat: repeat-x;" >
</canvas>
</div>
</div>

<div id="subirfile">
  <label>File</label>
<input type="file" name="foto">
</div>

<div id="imagendefault">
<input width="300" height="300" id="default" type="image" name="usuariodefault" src="">


</div>
<br><br><br>
</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
</div>


<div class="col-md-5">
<div class="form-group"><p><label for="id_usuario">Identificación:</label>
<span style="float:right" id="txteid_usuario"></span>
<input required autocomplete="off" onchange="valida_existe_id_usuario(this.value);soloNumeros(this.value);" class="form-control" name="id_usuario" type="text" id="id_usuario" value="<?php if (isset($row['id_usuario'])) echo $row['id_usuario'];?>"></p>
</div>
</div>

<div class="col-md-5">
<div class="form-group">
<p><label for="usuario">Usuario:</label><span style="float:right" id="txteusuario"></span>
<input class="form-control" autocomplete="off" onchange="valida_existe_nombre_usuario(this.value);" name="usuario" type="text" id="usuario" value="<?php if (isset($row['usuario'])) echo $row['usuario']; ?>" required >
</p>
</div>
</div>
<div class="col-md-5">
<div class="col-md-8">
<input type="hidden" name="cambiar_clave" value="NO">
<?php if ($_POST['submit']=="Modificar" or $_POST['submit']=="perfil"){ ?>
<label><input id="cambiar_clave" name="cambiar_clave" value="SI" type="checkbox" onclick="verificar_requerido('cambiar_clave','clave'); if (this.checked == true){ document.getElementById('grupo_claves').style.display='block';}else{document.getElementById('grupo_claves').style.display='none';}"> Cambiar Clave
<label><input type="hidden" name="mascota" value="NO"><input onchange="limpiar_mascota();" name="mascota" id="mascota" value="NO" type="checkbox" onclick="if (this.checked == true){ document.getElementById('clave_texto').style.display='none';document.getElementById('clave_mascota').style.display='block';}else{document.getElementById('clave_texto').style.display='block';document.getElementById('clave_mascota').style.display='none';}" <?php if (isset($row['mascota']) and $row['mascota']=="SI") echo "checked";?> > Mascota</label>


</label>
<?php } ?>
<div class="form-group" id="grupo_claves" <?php if ($_POST['submit']=="Modificar" or $_POST['submit']=="perfil"){ ?> style='display:none;'<?php } ?>><!-- grupo claves-->
<div class="form-group" id="clave_mascota" style="display:<?php if (isset($row['mascota']) and $row['mascota']=="SI") echo "block"; else echo "none";?>;">
<img height="80" id="icon-img" src="">
<span id="icono"></span>
<button type="button" class="btn btn-info btn-s" data-toggle="modal" data-target="#myModal">Elegir Mascota</button>
<div class="container">
  <!-- Modal -->
  <div class="modal modal-fullscreen fade" id="myModal" role="dialog">
    <div class="modal-dialog">
  <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
  <ul class="bs-glyphicons">
	<?php
	require("../comun/conexion.php");
	$sql_av = "SELECT * FROM `figuras`";
	$consulta_av = $mysqli->query($sql_av);
	while($row_av=$consulta_av->fetch_assoc()){
	//$nombre2 = str_replace("Avatars/","",$nombre);
	?>
	<li><span onclick="obtener_icono_mascota(this)" data-src="<?php echo SGA_COMUN_URL?>/img/figuras/<?php echo $row_av['imagen_figura'] ?>" data-id="<?php echo $row_av['figura'] ?>" ><img height="80" src="<?php echo SGA_COMUN_URL?>/img/figuras/<?php echo $row_av['imagen_figura'] ?>"><?php echo $row_av['figura']?></span></li>
	<?php } ?>
    </ul> </div>
       <div class="modal-footer">
        <a class="close btn" id="cerrar_modal_avatar" data-dismiss="modal">Cerrar</a>
    </div>
      </div>
      
    </div>
  </div>
  
</div>
</div>
<div class="form-group" id="clave_texto" style="display:<?php
if (isset($row['mascota']) and $row['mascota']=="SI"){
echo "none";
}else{
echo "block";
}
?>;">
<p>
<label for="clave">Clave:</label>
<input autocomplete="off" class="form-control" name="clave" type="password" id="clave" <?php if ($_POST['submit']=="Nuevo"){ ?> required <?php } ?>>
</p>
</div>
</div><!-- grupo claves-->

</div>

</div>
<div class="col-md-5">
<div class="form-group">
 <p>
  <label for="nombre">Nombre:</label>
  <input required="required" autocomplete="off"  class="form-control" name="nombre" type="text" id="nombre_usuario" value="<?php if (isset($row['nombre'])) echo $row['nombre'];?>">
</p>

</div>
</div>
<div class="col-md-5">
<div class="form-group"><p><label for="apellido">Apellido:</label><input class="form-control"name="apellido"type="text" id="apellido" value="<?php if (isset($row['apellido'])) echo $row['apellido'];?>" required ></p>
</div>
</div>

<div class="col-md-5">
<div class="form-group">
 <label for="direccion">Dirección:</label><input class="form-control"name="direccion"type="text" id="direccion" value="<?php if (isset($row['direccion']) and $row['direccion']<>"null") echo $row['direccion'];?>" >
</div>
</div>

<div class="col-md-5">
<div class="form-group">
 <p>
  <label for="sexo">Sexo:</label>
  <select onchange="validargenero()" class="form-control" name="genero" type="text" id="genero">
<option <?php if(isset($row['genero']) and $row['genero']=="f") echo "selected" ?> value="f">Femenino</option>
<option <?php if(isset($row['genero']) and $row['genero']=="m") echo "selected" ?> value="m">Masculino</option>
</select>
</p>
</div>
</div>
<div class="col-md-5">
<div class="form-group">
 <p><label for="correo">Correo:</label><input class="form-control"name="correo"type="email" id="correo" value="<?php if (isset($row['correo']) and $row['correo']<>"null") echo $row['correo'];?>"></p>
</div>
</div>


<div class="col-md-5">
<div class="form-group">
 <p><label for="telefono">Telefono:</label><input class="form-control"name="telefono" type="tel" onKeyPress="return soloNumeros(event)" id="telefono" value="<?php if (isset($row['telefono']) and $row['telefono']<>"null") echo $row['telefono'];?>"></p>
</div>
</div>
<div class="col-md-5">
<div class="form-group"><p><label for="tipo_sangre">Tipo de Sangre:</label>
<select class="form-control"name="tipo_sangre" type="text" id="tipo_sangre">
<?php
if (isset($row['tipo_sangre'])) $pre = $row['tipo_sangre'];
else $pre="";
tipos_sangre($pre); ?>
</select>
</p>
</div>
</div>

<div class="col-md-5">
<div id="area_categoria" style="<?php if ($_POST['u']=="estudiante"){
echo "display:block";
}else{
echo "display:none";
} ?>">
<label for="rol">Inscribir Estudiante a Categoria:</label>
<select class="form-control" name="id_categoria_curso" id="id_categoria_curso">
<?php 
require ("../comun/conexion.php");
$sql_categoria='select * from categoria_curso' ;
$consulta_categoria = $mysqli -> query($sql_categoria);
while ($row_categoria = $consulta_categoria -> fetch_assoc()) { ?>
<option value="<?php echo $row_categoria['id_categoria_curso'];  ?>"><?php echo $row_categoria['nombre_categoria_curso'];  ?></option>
<?php } ?> 
</select>
</p>
</div>
<?php } 
?>
</div>
</div>


<?php if ($_POST['submit']!="perfil"){ ?>
  <div class="col-md-12">
<label for="estado">Estado:</label>
<input type="radio" name="estado" id="estado[1]" <?php if (!isset($row['estado'])) echo "checked"; ?>   required value="activo" <?php if (isset($row['estado']) and $row['estado'] =="activo") echo " checked "; ?> >Activo
<input type="radio" name="estado" id="estado[2]"  required value="inactivo" <?php if (isset($row['estado']) and $row['estado'] =="inactivo") echo " checked "; ?> >Inactivo
</div>
<?php } ?>

<div class="col-md-12">

<?php if ($_POST['submit']!="perfil"){ ?>

<!--label for="rol">Rol:</label-->
<?php
$roles = array();
if (isset($row['rol']))
$roles = explode(",",$row['rol']);
?>
<input type="hidden" name="roles_old" value="<?php if (isset($row['rol'])) echo $row['rol'] ?>">
<label for="rol_admin">
<input name="rol[]" id="rol_admin" type="checkbox" <?php if((isset($row['rol']) and in_array("admin",$roles)) or $_POST['u']=="admin") echo "checked"; ?> value="admin"/>Administrador
</label>
<label for="rol_docente">
<input name="rol[]" id="rol_docente" type="checkbox" <?php if((isset($row['rol']) and in_array("docente",$roles)) or $_POST['u']=="docente") echo "checked"; ?> value="docente"/>Docente
</label>
<label for="rol_estudiante">
<input onclick="if (this.checked == true){ document.getElementById('area_categoria').style.display='block';}else{document.getElementById('area_categoria').style.display='none';}" name="rol[]" id="rol_estudiante" type="checkbox" <?php if((isset($row['rol']) and in_array("estudiante",$roles)) or $_POST['u']=="estudiante") echo "checked"; ?> value="estudiante"/>Estudiante
</label>
<label for="rol_acudiente">
<input name="rol[]" id="rol_acudiente" type="checkbox" <?php if((isset($row['rol']) and in_array("acudiente",$roles)) or $_POST['u']=="acudiente") echo "checked"; ?> value="acudiente"/>Acudiente
</label>


</div>
</div>
<?php } ?>
<div class="col-md-12">
<?php 

if (($_POST['submit']=="Nuevo" or $_POST['submit']=="Modificar") or ($_POST['submit']=="perfil" and $tiene_permiso)){ ?>
<input type="hidden" name="submit" id="submit" value="<?php echo $textobtn ?>"><button type="submit" class="btn btn-primary"><?php echo $textobtn ?></button>
<?php if(isset($_SESSION['hijo'])){ ?>
<a href="perfil.php?usuario=<?php
@session_start();
echo $_SESSION['hijo'] ?>" class="btn btn-success">Modificar Información estudiante</a>
<?php } } ?>
<?php if (($_POST['submit']=="Modificar") or ($_POST['submit']=="perfil" and $tiene_permiso)){ ?>
<button class="btn btn-danger" type="button" onClick="confirmeliminar2('perfil.php',{'del':'<?php echo $usuario;?>'},'<?php echo $row['nombre']." ".$row['apellido'];?>');">Eliminar este Perfil</button>
<?php } ?>
</div>
<style>
.imagen {
  max-width: 100%;
  height: auto;
}
</style>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<img class="imagen"  data-target="#exampleModal" id="img_foto"  data-toggle="modal" style='position_absolute;width:150px;
    height:150px;
    border-radius:150px;
    margin-left:70%;
    margin-top:-140%;
    '  height="300" src="<?php if (isset($row['foto'])) echo READFILE_URL."/foto/".$row['foto'];
 ?>">





</form>
</div><!-- todo-->
<div class="col-md-3"></div>
<?php 
#} /*fin nuevo*/ 

}//fin fucntion formulario usuario
function boton_modal_nuevo_icono(){
    ?>
    <button id="btn_nuevo_foro" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal_nuevo">Nuevo Icono</button>
    <?php
}
function ventana_modal_nuevo_icono($atributos){
    ?>
    <!-- Modal -->
<div id="myModal_nuevo" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nuevo Icono</h4>
      </div>
      <div class="modal-body">
        <form <?php echo $atributos ?> ENCTYPE="multipart/form-data">
              <div class="form-group">
                <label for="icono">Nombre</label>
                <input type="text" name="icono" id="icono" class="form-control" placeholder="Nombre del icono">
            </div>
            <div class="form-group">
                <label for="imagen_icono">Archivo</label>
                <input type="file" multiple name="imagen_icono" id="imagen_icono" class="form-control" placeholder="Titulo del Foro">
            </div>
            <div class="form-group">
                <button  type="submit" name="titulo_foro" id="titulo_foro" class="btn btn-success">Guardar</button>
            </div>
            </form>
      </div>
      <div class="modal-footer">
        <button id="cerrar_modal_nuevo_icono" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
<!---->
    <?php
}
function boton_modal_elegir_icono($destino=""){
    ?>
    <button id="btn_elegir_icono" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal2_elegiricono<?php echo $destino ?>" onmouseup="parent.buscar_iconos('','<?php echo $destino; ?>');">Elegir Icono</button>
    <?php
}
function ventana_modal_elegir_icono($destino=""){
    ?>
    <!-- Modal -->
<div id="myModal2_elegiricono<?php echo $destino ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Elegir un Icono</h4>
      </div>
      <div class="modal-body">
        <input name="contexto" type="hidden" value="general">
            <div class="form-group">
                <?php boton_modal_nuevo_icono($destino); ?>
            </div>
            <div class="form-group">
                <label for="buscar_iconos">Buscar Icono</label>
                <input type="text" name="buscar_iconos" id="buscar_iconos" class="form-control" placeholder="Escriba aqui para buscar" value="" onkeyup ="parent.buscar_iconos(this.value,'<?php echo $destino?>');" onchange="parent.buscar_iconos(this.value,'<?php echo $destino?>');"  >
            </div>
        <div class="form-group">
            <span id="txtresultadosicono<?php echo $destino ?>">
            </span>
        </div>
      </div>
      <div class="modal-footer">
        <button id="cerrar_modal_elegir_icono<?php echo $destino ?>" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
<!---->
    <?php
}
function boton_modal_nuevo_foro(){
?>
<!-- Trigger the modal with a button -->
<button id="btn_nuevo_foro" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModalnuevo_foro">Nuevo Foro</button>
<?php
}
function ventana_modal_nuevo_foro($parametros=""){
    if ($parametros=="") $parametros = array();
    ?><!-- Modal -->
<div id="myModalnuevo_foro" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nuevo Foro</h4>
      </div>
      <div class="modal-body">
       <?php formulario_nuevo_foro($parametros);?>
      </div>
      <div class="modal-footer">
        <button id="cerrar_modal_nuevo_foro" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
<!----><?php
}
function formulario_nuevo_foro($parametros){
    ?>
    <!-- inicio formulario nuevo foro -->
        <form method="post" class="form_ajax" resp_1="Foro creado" resp_0="Foro no creado, revise su información e intentete nuevo" action="?nuevo_foro" callback_1="document.getElementById('cerrar_modal_nuevo_foro').click();mis_foros();">
        <input name="contexto" type="hidden" value="<?php if (isset($parametros['contexto'])) echo $parametros['contexto']; else echo "general" ?>">
            <div class="form-group">
                <label for="titulo_foro">Titulo del Foro</label>
                <input type="text" name="titulo_foro" id="titulo_foro" class="form-control" placeholder="Titulo del Foro" value="<?php if(isset($parametros['titulo'])) echo $parametros['titulo'];?>">
            </div>
            <div class="form-group">
                <label>Roles</label>
                <div class="form-control" value="1">
                <label><input name="roles_grupo[]" value="admin" type="checkbox" checked >&nbsp;Administrador</label>
                <label><input name="roles_grupo[]" value="docente" type="checkbox" <?php if(isset($parametros['roles']) and is_array($parametros['roles']) and in_array('docente',$parametros['roles'])) echo 'checked';?> >&nbsp;Docente</label>
                <label><input name="roles_grupo[]" value="acudiente" type="checkbox" <?php if(isset($parametros['roles']) and is_array($parametros['roles']) and in_array('acudiente',$parametros['roles'])) echo 'checked';?> >&nbsp;Acudiente</label>
                <label><input name="roles_grupo[]" value="estudiante" type="checkbox"<?php if(isset($parametros['roles']) and is_array($parametros['roles']) and in_array('estudiante',$parametros['roles'])) echo 'checked';?> >&nbsp;Estudiante</label>
                </div>
            </div>
            <div class="form-group">
            <input type="hidden" name="icono_seleccionado" id="icono_seleccionado" value="">
            <img width="50px" id="icono_seleccionado_img">
            <?php boton_modal_elegir_icono(); ?>
            </div>
            <div class="form-group">
              <input type="hidden" name="valor" value="">
              <input value ="NO"  type="hidden" name="permitir_temas">
                <label> <input value ="SI" name="permitir_temas" type="checkbox" title="Permitir que otros usuarios diferentes a Ud ingresen temas">&nbsp;Permitir Temas</label>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Crear</button>
            </div>
        </form>
        <!-- fin formulario nuevo foro -->
    <?php
}
function nuevo_foro($titulo_foro,$roles_grupo,$contexto,$valor,$permitir_temas,$icono_seleccionado,$insertid=false){
    require(dirname(__FILE__)."/conexion.php");
  #$sql = "SELECT * FROM `figuras`";
  
$titulo_foro = $mysqli->real_escape_string($titulo_foro);
$roles_grupo = implode(",",$roles_grupo);
$roles_grupo = $mysqli->real_escape_string($roles_grupo);
$contexto = $mysqli->real_escape_string($contexto);
$valor = $mysqli->real_escape_string($valor);
$permitir_temas = $mysqli->real_escape_string($permitir_temas);
$icono_seleccionado = $mysqli->real_escape_string($icono_seleccionado);
$sql="INSERT INTO `grupo_foro`(`nombre_grupo`, `roles_grupo`, `contexto`, `valor`, `permitir_temas`, `icono`) VALUES ('$titulo_foro','$roles_grupo','$contexto','$valor','$permitir_temas','$icono_seleccionado');";
$mysqli->query($sql);
$insert_id = $mysqli->insert_id;
    if ($insertid){
        return $insert_id;
    }else{
        if ($mysqli->affected_rows>0){
            return true;
        }else{
            return false;
        }
    }
}
function tipos_sangre($pre=""){ ?>
<option value="">Seleccione Tipo de Sangre</option>
<option <?php if($pre == "O-") echo "selected";?> value="O-">O negativo</option>
<option <?php if($pre == "O+") echo "selected";?> value="O+">O positivo</option>
<option <?php if($pre == "A-") echo "selected";?> value="A-">A negativo</option>
<option <?php if($pre == "A+") echo "selected";?> value="A+">A positivo</option>
<option <?php if($pre == "B-") echo "selected";?> value="B-">B negativo</option>
<option <?php if($pre == "B+") echo "selected";?> value="B+">B positivo</option>
<option <?php if($pre == "AB-") echo "selected";?> value="AB-">AB negativo</option>
<option <?php if($pre == "AB+") echo "selected";?> value="AB+">AB positivo</option><?php
}
function ano_lectivo() {
    require(dirname(__FILE__)."/conexion.php");
    $sql="SELECT id_ano_lectivo, nombre_ano_lectivo FROM `ano_lectivo` WHERE estado = 'Activo'";
    $consulta_sql = $mysqli ->query($sql);
    while($rowsql= $consulta_sql->fetch_assoc()){
        return $rowsql['nombre_ano_lectivo']; 
    }
}



function consultar_id_ano_lectivo(){
    require(dirname(__FILE__)."/conexion.php");
  $sql = "SELECT id_ano_lectivo, nombre_ano_lectivo FROM `ano_lectivo` WHERE estado = 'Activo'";
    $consulta = $mysqli->query($sql);
    $salida="";
    if ($row=$consulta->fetch_assoc()){
    $salida = $row['id_ano_lectivo'];
    }
    return $salida;
}
function importar_xls($ruta){
require (dirname(__FILE__).'/conexion.php');
require_once(dirname(__FILE__).'/funciones.php');
require_once(dirname(__FILE__).'/config.php');
require_once (dirname(__FILE__).'/lib/PHPExcel/Classes/PHPExcel/IOFactory.php');
if (file_exists($ruta)){
$objPHPExcel = PHPExcel_IOFactory::load($ruta);
#revisar remplazos de `rol`, actualmente se asigna un solo rol al subir
#el rol se sube cocatenado con comas si es multiple
$sql = "INSERT INTO `usuario`(`id_usuario`, `usuario`, `clave`, `mascota`, `nombre`, `apellido`, `rol`, `direccion`, `telefono`, `correo`, `tipo_sangre`) VALUES ";
$sql3a1 = "INSERT INTO `usuario`(`id_usuario`, `usuario`, `clave`, `mascota`, `nombre`, `apellido`, `rol`, `direccion`, `telefono`, `correo`, `tipo_sangre`) VALUES ";
$sql3a2 = "INSERT INTO `usuario`(`id_usuario`, `usuario`, `clave`, `mascota`, `nombre`, `apellido`, `rol`, `direccion`, `telefono`, `correo`, `tipo_sangre`) VALUES ";
$sql7 = "INSERT INTO `acudiente_estudiante`(`id_estudiante`, `id_acudiente`) VALUES ";
$sql8 = "INSERT INTO `acudiente_estudiante`(`id_estudiante`, `id_acudiente`) VALUES ";
$sql2 = "INSERT IGNORE INTO `seguimiento_categoria_ano`(`id_estudiante`,`categoria`, `ano_lectivo`, `estado`) VALUES ";
$sql4 = "INSERT INTO `docente`(`id_docente`) VALUES ";
$sql5 = "INSERT INTO `acudiente`(`id_acudiente`) VALUES ";
$sql6 = "INSERT INTO `acudiente`(`id_acudiente`) VALUES ";
$id_ano_lectivo=consultar_id_ano_lectivo();
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
$worksheetTitle     = $worksheet->getTitle();
$highestRow         = $worksheet->getHighestRow(); // e.g. 10 "50";
$highestColumn      = "AD";//$worksheet->getHighestColumn();  // e.g 'F' 17
//$highestColumn -> la ultima columna a importar
$primera_fila = 2;
$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
$nrColumns = ord($highestColumn) - 64;
for ($row = $primera_fila; $row <= $highestRow; ++ $row) {

$primeracol=$worksheet->getCellByColumnAndRow(0, $row);
$valcol = $primeracol->getValue();//obtiene valor
$valcol = trim($valcol);//quita espacios
$valcoln = (int)$valcol;//convierte a numero entero
$estado = false;//bandera para saber si es numero
if(is_numeric($valcol)){//valida numero entero
$estado=true;//bandera para saber si es numero
$col_rol = $worksheet->getCellByColumnAndRow(5, $row);
$col_cat = $worksheet->getCellByColumnAndRow(10, $row);
$col_nivel_cat = $worksheet->getCellByColumnAndRow(11, $row);
//consulta_id_cat($col_cat,$col_nivel_cat);
if($primera_fila==$row and $worksheetTitle == "Hoja1"){
$sql .= ' ( ';
if($col_rol == "estudiante")  $sql2 .= ' (';
if($col_rol == "estudiante")  $sql3a1 .= ' (';
if($col_rol == "estudiante")  $sql3a2 .= ' (';
if($col_rol == "docente")   $sql4 .= ' (';
if($col_rol == "estudiante")  $sql5 .= ' (';
if($col_rol == "estudiante")  $sql6 .= ' (';
}else{
$sql .= ', (';
if($col_rol == "estudiante")  $sql2 .= ', (';
if($col_rol == "estudiante")  $sql3a1 .= ', (';
if($col_rol == "estudiante")  $sql3a2 .= ', (';
if($col_rol == "docente")   $sql4 .= ', (';
if($col_rol == "estudiante")  $sql5 .= ', (';
if($col_rol == "estudiante")  $sql6 .= ', (';
}
for ($col = 0; $col < $highestColumnIndex; ++ $col) {
$cell = $worksheet->getCellByColumnAndRow($col, $row);
$val = $cell->getValue();
$val = utf8_decode($val);
$dataType = PHPExcel_Cell_DataType::dataTypeForValue($val);
if($col==2) $val=encriptar($val);
if($col==14) $val=encriptar($val);
if($col==23) $val=encriptar($val);
//mascotas
if($col==3 and $col_rol == "estudiante") $sql .= '"SI",';
elseif($col==3 and $col_rol != "estudiante") $sql .= '"NO",';
//fin mascotas
//if($col==9) $val=consulta_id_cat($val);
if($col==20 and $col_rol == "estudiante") $sql3a1 .= '"'.$val.'"';
else if($col>=12 and $col<20 and $col_rol == "estudiante"){
$sql3a1 .= '"'.$val.'",';
if($col==14 and $col_rol == "estudiante") $sql3a1 .= '"NO",';
} 
if($col==16 and $col_rol == "estudiante")  $sql3a1.= '"acudiente",';

if($col==29 and $col_rol == "estudiante") $sql3a2.= '"'.$val.'"';
if($col>=21 and $col<29){
if($col_rol == "estudiante") $sql3a2.= '"'.$val.'",';
if($col==25 and $col_rol == "estudiante")  $sql3a2.= '"acudiente",';
if($col==23 and $col_rol == "estudiante") $sql3a2 .= '"NO",';
}
if($col==11 and $col_rol == "estudiante")  $sql5.= '"'.$val.'"';
if($col==20 and $col_rol == "estudiante")  $sql6.= '"'.$val.'"';
if($col==9) $sql .= '"'.$val.'"';
elseif($col<9) $sql .= '"'.$val.'",';
if($col==5){
$col_id = $worksheet->getCellByColumnAndRow(0, $row);
if($val == "estudiante") $sql2.= '"'.$col_id.'"';
if ($val == "docente") $sql4 .= '"'.$col_id.'"';
}
if($col_rol == "estudiante" and $col==12) $sql7.= ',("'.$col_id.'","'.$val.'")';
if($col_rol == "estudiante" and $col==21) $sql8.= ',("'.$col_id.'","'.$val.'")';
if($col==10 and $col_rol == "estudiante"){
$id_cat = consulta_id_cat($col_cat,$col_nivel_cat);
$sql2.= ',"'.$id_cat.'","'.$id_ano_lectivo.'","En curso"';
}
}//recorrido de columnas
$sql .= ')';
if($col_rol == "estudiante") $sql2 .= ')';
if($col_rol == "estudiante")  $sql3a1 .= ')';
if($col_rol == "estudiante")  $sql3a2 .= ')';
if($col_rol == "docente") $sql4 .= ')';
if($col_rol == "estudiante") $sql5 .= ')';
if($col_rol == "estudiante") $sql6 .= ')';
}//valida numero entero
}

}
if($worksheetTitle != "Hoja1") if($estado) $sql .= ',';

$sql .= ' ON DUPLICATE KEY UPDATE `usuario`=VALUES(usuario), `clave`=VALUES(clave), `nombre`=VALUES(nombre), `apellido`=VALUES(apellido), `rol`=VALUES(rol), `direccion`=VALUES(direccion), `telefono`=VALUES(telefono), `correo`=VALUES(correo), `tipo_sangre`=VALUES(tipo_sangre);';
//$sql2 .= ' ON DUPLICATE KEY UPDATE `id_estudiante`=VALUES(id_estudiante), `id_categoria_curso`=VALUES(id_categoria_curso);';
$sql3a1 .= ' ON DUPLICATE KEY UPDATE `usuario`=VALUES(usuario), `clave`=VALUES(clave), `nombre`=VALUES(nombre), `apellido`=VALUES(apellido), `rol`=VALUES(rol), `direccion`=VALUES(direccion), `telefono`=VALUES(telefono), `correo`=VALUES(correo), `tipo_sangre`=VALUES(tipo_sangre);';
$sql3a2 .= ' ON DUPLICATE KEY UPDATE `usuario`=VALUES(usuario), `clave`=VALUES(clave), `nombre`=VALUES(nombre), `apellido`=VALUES(apellido), `rol`=VALUES(rol), `direccion`=VALUES(direccion), `telefono`=VALUES(telefono), `correo`=VALUES(correo), `tipo_sangre`=VALUES(tipo_sangre);';
$sql4 .= ' ON DUPLICATE KEY UPDATE `id_docente`=VALUES(id_docente);';
$sql5 .= ' ON DUPLICATE KEY UPDATE `id_acudiente`=VALUES(id_acudiente);';
$sql6 .= ' ON DUPLICATE KEY UPDATE `id_acudiente`=VALUES(id_acudiente);';
$sql7 .= " ON DUPLICATE KEY UPDATE `id_acudiente`=VALUES(id_acudiente), `id_estudiante`=VALUES(id_estudiante);";
$sql8 .= " ON DUPLICATE KEY UPDATE `id_acudiente`=VALUES(id_acudiente), `id_estudiante`=VALUES(id_estudiante);";
//$sql2 = str_replace("VALUES ,","VALUES ",$sql2);
$sql3a1 = str_replace("VALUES ,","VALUES ",$sql3a1);
$sql3a2 = str_replace("VALUES ,","VALUES ",$sql3a2);
$sql4 = str_replace("VALUES ,","VALUES ",$sql4);
$sql5 = str_replace("VALUES ,","VALUES ",$sql5);
$sql6 = str_replace("VALUES ,","VALUES ",$sql6);
$sql7 = str_replace("VALUES ,","VALUES ",$sql7);
$sql8 = str_replace("VALUES ,","VALUES ",$sql8);
//$sql_def = $sql.$sql2.$sql4.$sql3a1.$sql3a2.$sql5.$sql6.$sql7.$sql8;
//$sql4
//$sql5
//$sql6
$sql_def = $sql.$sql3a1.$sql3a2.$sql7.$sql8.$sql2;
if($insertar = $mysqli->multi_query($sql_def)){
    @unlink($ruta);
    return true;
    }else{
    return false;
    }
}else{//si no existe ruta
    return false;
    }
}//fin function importar xls

function buscar_iconos($datos="",$reporte="",$destino=""){
require(dirname(__FILE__)."/conexion.php");
require_once (dirname(__FILE__)."/lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = ((isset($_COOKIE['numeroresultados_iconos']) and $_COOKIE['numeroresultados_iconos']!= ""  and $_COOKIE['numeroresultados_iconos']!= 0 ) ? $_COOKIE['numeroresultados_iconos'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$funcionjs="buscar_iconos('','".$destino."');";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page("page_iconos");
$paginacion->padding(false);
if (isset($_COOKIE["page_iconos"]))
$_GET['page'] = $_COOKIE["page_iconos"];
else
$_GET['page'] = 1;
$sql = "SELECT `iconos`.`id_iconos`, `iconos`.`icono`, `iconos`.`imagen_icono` FROM `iconos`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`iconos`.`icono`)," ",LOWER(`iconos`.`imagen_icono`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyiconos']) and $_COOKIE['orderbyiconos']!=""){ $sql .= "`iconos`.`".$_COOKIE['orderbyiconos']."`";
}else{ $sql .= "`iconos`.`id_iconos`";}
if (isset($_COOKIE['orderad_iconos'])){
$orderadiconos = $_COOKIE['orderad_iconos'];
$sql .=  " $orderadiconos ";
}else{
$sql .=  " desc ";
}
$consulta_total_iconos = $mysqli->query($sql);
$total_iconos = $consulta_total_iconos->num_rows;
$paginacion->records($total_iconos);
if (isset($_COOKIE["page_iconos"]))
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#echo $sql; 
$consulta = $mysqli->query($sql);
$numero_iconos = $consulta->num_rows;
$minimo_iconos = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_iconos = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_iconos>$numero_iconos) $maximo_iconos=$numero_iconos;
$maximo_iconos += $minimo_iconos-1;
echo "<p>Resultados de $minimo_iconos a $maximo_iconos del total de ".$total_iconos." en página ".$paginacion->get_page()."</p>";
 ?>
<div class="css_table" align="center">
<div border="1" id="tbiconos">
<div vlass="css_thead">
<span class="css_tr">
</span>
</div>
<div class="css_tbody">
<ul class="bs-glyphicons">
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
<li id="icono_<?php echo $row['id_iconos']?>" class="icono_a_seleccionar" onclick="document.getElementById('icono_seleccionado<?php echo $destino ?>').value = '<?php echo $row['id_iconos']?>';document.getElementById('icono_seleccionado_img<?php echo $destino ?>').src='<?php echo SGA_COMUN_URL ?>/img/png/<?php echo $row['imagen_icono']; ?>';document.getElementById('cerrar_modal_elegir_icono<?php echo $destino ?>').click();" data-label='Imagen icono'>
  <?php if ($reporte==""){ ?>
<span data-label="Modificar">
<form style="float:left;position:relative;margin-bottom:-10px" id="formModificar" name="formModificar" method="post" action="<?php echo SGA_COMUN_URL ?>/iconos.php" ENCTYPE="multipart/form-data">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_iconos']?>">
<input class='btn btn-md' style='position:absolute;background-color:transparent;background-image: url("<?php echo SGA_COMUN_URL.'/img/modificar.png' ?>");' type="submit" name="submit"  height="10px" value="">
</form>
</span>
<span style="float:right;position:relative;margin-bottom:-10px" data-label="Eliminar">
<input type="image" src="<?php echo SGA_COMUN_URL ?>/img/eliminar.png" height="20px" onClick="confirmeliminar2('<?php echo SGA_COMUN_URL ?>/iconos.php',{'del':'<?php echo $row['id_iconos'];?>'},'<?php echo $row['icono'];?>');" value="Eliminar">
</span>
<?php } ?>
 <img width="50px" src="<?php echo SGA_COMUN_URL ?>/img/png/<?php echo $row['imagen_icono']; ?>"></img>
 <?php #echo $row['imagen_icono']?><br>
 <span data-label='icono'><?php echo $row['icono']?></span>
 </li>
<?php 
}/*fin while*/
 ?>
</ul>
</div>
</div>
<?php $paginacion->render2();?>
</div>
<?php 
}/*fin function buscar*/
function niveles_educativos($nivel_educativo){    ?>
          <?php 
          require '../comun/conexion.php';
         echo  $sqlcategoria ='select * from categoria_curso ';
          $consultacategoria= $mysqli->query($sqlcategoria);
    while ($rowcategoria = $consultacategoria->fetch_Assoc()){ 
      ?>
<option value="<?php echo $rowcategoria['id_categoria_curso']; ?>"><?php 
echo $rowcategoria['nombre_categoria_curso'];
?></option>
          <?php } ?>

    <?php
}


function recuperar_contraseña($idusuario){
$consultar_datos2 ='select * from usuario where id_usuario='.$idusuario.' ';
require '../../comun/config.php';
$cadena = consultar_datos2($consultar_datos2)[0][
  0].consultar_datos2($consultar_datos2)[0][
  1].date('Y-m-d');
$token = sha1($cadena);
$enlace = SGA_USUARIO_URL.'/restablecer.php?token='.$token;
return enviarEmail(consultar_datos2($consultar_datos2)[0][
  10], $enlace );
  }

  function enviarEmail( $email, $link ){
$email_institucion=$email;  
$mensaje = '<html>
    <head>
      <title>Restablece tu contraseña</title>
    </head>
    <body>
      <p>Hemos recibido una petición para restablecer la contraseña de tu cuenta.</p>
      <p>Si hiciste esta petición, haz clic en el siguiente enlace, si no hiciste esta petición puedes ignorar este correo.</p>
      <p>
        <strong>Enlace para restablecer tu contraseña</strong><br>
        <a href="'.$link.'"> Restablecer contraseña </a>
      </p>
    </body>
    </html>';

    $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
    $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $cabeceras .= 'From: Guagua <'.$email_institucion.'>' . "\r\n";
    
if(mail($email, "Recuperar contraseña", $mensaje, $cabeceras))  {
echo '<script>';
echo 'alert2("Revisa la bandeja de entrada o spam(no deseado) \n de tu correo electrónico '.$email.' ");';
echo '</script>';
  }
  }

function confirmar_token($token){ //recuperar_login.php
$bandera=0;
 $sql_token='select nombre,apellido,usuario, sha1(CONCAT(`id_usuario`, `usuario` ,'.date('Y-m-d').')) as miclave from usuario';
$nombre='';
if(!empty(consultar_datos2($sql_token))){
foreach (consultar_datos2($sql_token) as $clave ) {
if($clave[3]==$token){
    $bandera=1; $nombre=$clave[0].' '.$clave[1];  
 }

  return array ($bandera, $nombre,$clave[2]) ;
}

}

}
function base64_encode_image ($filename) {
    if (file_exists($filename)){
    #$imgbinary = fread(fopen($filename, "r"), filesize($filename));
    #$imgbinary = file_get_contents($filename);
    ob_clean();
    $imgbinary = readfile($filename);
    ob_clean();
    return base64_encode($imgbinary);
    }
}


function categorias_docente(){

}


function consultar_categoria_docente(){
    require("conexion.php");
    #$ano_lectivo=3;
$sql='
select distinct(categoria_curso.id_categoria_curso),nombre_categoria_curso from asignacion,categoria_curso where
asignacion.id_categoria_curso =categoria_curso.id_categoria_curso and
 id_docente='.$_SESSION['id_usuario'].'  and ano_lectivo = (SELECT id_ano_lectivo FROM `ano_lectivo` WHERE estado ="Activo")';
 return consultar_datos2($sql);
}
function actualizar_posicion_portada($x,$y,$tabla='config',$campo='banner_position',$campoid='id_config',$valorid='1'){
    if ($x=="") $x=0;
    if ($y=="") $y=0;
    require(dirname(__FILE__)."/conexion.php");
    $sql = "UPDATE `$tabla` SET `$campo` = '".$x."px ".$y."px' WHERE `$campoid` = $valorid;";
    $mysqli->query($sql);
    if ($mysqli->affected_rows>0){
        return true;
    }else{
        return false;
    }
}
function botones_portada(){
    if(isset($_SESSION['rol']) and $_SESSION['rol']=="admin"){
if(defined('BANNER_POSITION')) $position_baner = explode(" ",BANNER_POSITION);
?>
<input hidden id="jumbotronbgPosY" type="number" onchange="jumbotron.style.backgroundPositionY=this.value+'px';actualizar_posicion_portada();" value="<?php if (isset($position_baner)) echo $position_baner[0] ?>">
<input hidden id="jumbotronbgPosX" type="number" onchange="jumbotron.style.backgroundPositionX=this.value+'px';actualizar_posicion_portada();" value="<?php if (isset($position_baner)) echo $position_baner[1] ?>">
<label title="Para mover el fondo de su portada presione la tecla Alt y las flechas de dirección"><input id="mover_fondo" type="checkbox">&nbsp;Ajustar Fondo de portada</label>
<?php }
}
function consultar_link_icono($icono=1){
      require(dirname(__FILE__)."/conexion.php");
    require_once(dirname(__FILE__)."/config.php");
$sql="SELECT imagen_icono FROM `iconos` WHERE id_iconos=".$icono."";

    if ($consulta = $mysqli->query($sql)){
    if ($row = $consulta->fetch_assoc()){
        return SGA_COMUN_URL."/img/png/".$row['imagen_icono'];
    }else{
        return SGA_COMUN_URL."/img/png/folder-10.png";
    }
  
    }else{
        return SGA_COMUN_URL."/img/png/folder-10.png";
    }
}
#echo consultar_link_icono(1);
#exit();

function resultados_graficar_tabla($array,$datos,$orden="",$intervalo = 0,$grafica=true,$tabla=true){
//se usa en reportes
    $datos_a= explode(",",$datos);
    $orden_a= explode(",",$orden);
    
    $array2=array();
         foreach ($array as $i => $valor){
            foreach ($valor as $j => $dato){
                    $var_u = $valor[$datos_a[0]].$valor[$datos_a[1]];
                    @$$var_u++;
                    $array2[$valor[$datos_a[0]]][$valor[$datos_a[1]]]=array("Marca"=>$valor[$datos_a[1]],"Intervalo"=>"","FA"=>$$var_u/count($valor));
            }
   }
             
$i=0;
if($orden!=""){
    #echo $orden."<br>";
$ordenado = array();
foreach ($array2 as $nombre => $array3){
foreach ($orden_a as $i => $val){
foreach ($array3 as $id2 => $valor2){
    if ($valor2['Marca']==$val){
        $ordenado[$nombre][$id2]=$valor2;
    }
    }
    
}
}
$array2 = $ordenado;
}//fin if($orden!="")
//imprimir tabla de frecuencias
foreach ($array2 as $nombre => $array3){
$i++;
$faa = 0;
$faat = 0;
$salidas=array();
foreach ($array3 as $id2 => $valor2){
  $faat += $valor2["FA"];
  //organizar salida para grafica
  $salidas[] = '{ "cualitativo":" '.$valor2["Marca"].'", "cuantitativo":"'.$valor2["FA"].'" }';
}
if ($tabla){
?>
<h1>Tabla de Frecuencias para <?php echo $nombre ?></h1>
<table border="1">
    <thead>
        <tr>
        <th><?php echo $datos_a[1] ?></th>
        <?php if ($intervalo != 0){ ?>
        <th>Intervalo de Clase Rango</th>
        <?php } ?>
        <th>Frecuencia Absoluta</th>
        <th>Frecuencia Absoluta Acumulada</th>
        <th>Frecuencia Relativa</th>
        <th>Frecuencia Relativa Acumulada</th>
        <th>Frecuencia Relativa %</th>
        <th>Frecuencia Relativa Acumulada %</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_datos=0;
             foreach ($array3 as $id2 => $valor2){
             ?><tr>
                    <td><?php echo $valor2["Marca"] ?></td>
                    <?php if ($intervalo != 0){ ?>
                    <td><?php echo $valor2["Intervalo"] ?></td>
                    <?php } ?>
                    <td><?php echo $valor2["FA"] ?></td>
                    <td><?php $faa += $valor2["FA"]; echo $faa ?></td>
                    <td><?php echo round($valor2["FA"]/$faat,2) ?></td>
                    <td><?php echo round($faa/$faat,2) ?></td>
                    <td><?php echo round((($valor2["FA"]/$faat)*100),2) ?>%</td>
                    <td><?php echo round((($faa/$faat)*100),2) ?>%</td>
              </tr><?php
            $total_datos++;
             }
            //$array2
            #$array2[$var_u]=$$var_u;
        #$array2;
    ?>
    </tbody>
</table>
    <?php
}
if ($grafica){
$datos_grafica = implode(",",$salidas);
$div = $nombre.$i;
#echo $total_datos;
?>
<script>
var datos<?php echo $div ?> = {
      "elementos": [<?php echo $datos_grafica ?>]} ;
google.setOnLoadCallback(function() {
Graficooffline2(titulo="Grafica de <?php echo $nombre ?>",cualitativa="Opcíón",cuantitativa="<?php echo $datos_a[1]?>",contenedor="div<?php echo $div ?>",tipo_grafica="<?php if ($total_datos<=5) echo "PieChart"; else echo "ColumnChart"; ?>",ancho="700px",alto="200px",datos<?php echo $div ?>);
});
</script>
<div id="div<?php echo $div ?>" style="width: 500px; height: 300px;"></div>
<?php
}
}
}
//estadisticas

function estadistica_tipo_sangre_roles(){
    $sql23 = "SELECT `rol`,`tipo_sangre` FROM `usuario`";
    return resultados_graficar_tabla(consultar_datos2($sql23,true),'rol,tipo_sangre');
}
function graficar_respuestas($totales,$opciones_generales,$id,$tipo){
        //se usa en cuestionarios
        #Google Chart
   // print_r($totales);
return '<script type=\'text/javascript\'>
      google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn(\'string\', \'Topping\');
        data.addColumn(\'number\', \'Respuestas\');
'.datarow_encode($totales).'
var options = '.$opciones_generales.';
var chart = new google.visualization.'.$tipo.'(document.getElementById(\''.$id.'\'));
        chart.draw(data, options);
      }
    </script>
<div id="'.$id.'"></div>';
}
/**/
function estadistica($totales,$id='chart_div',$opciones_generales = "",$tipo = 'PieChart'){
return '<script type="text/javascript" src="js/japi.js"></script><script type=\'text/javascript\'>
      google.load(\'visualization\', \'1.0\', {\'packages\':[\'corechart\']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn(\'string\', \'Topping\');
        data.addColumn(\'number\', \'Slices\');
'.datarow_encode($totales).'
var options = '.$opciones_generales.';
var chart = new google.visualization.'.$tipo.'(document.getElementById(\''.$id.'\'));
        chart.draw(data, options);
      }
    </script>
<div id="'.$id.'"></div>';
}
  
function repeatedElements1($array)
{
    //organiza los datos para los resultados de un cuestionario de manera que sean unicos en sus respuestas permitiendo graficar las repeticiones de estos datos
  $salida =array();
  foreach($array as $id => $valor)
  {
    foreach($valor as $id2 => $valor2)
    {
    $ob = json_decode(stripslashes($valor2),true);
      if(is_array($ob)){
        foreach($ob as $id3 => $valor3)
        { $vartemp = $id2.$valor3;
          @$$vartemp++;
          @$salida[$id2][$valor3]=$$vartemp/count($valor);
        }
      }else{
        $vartemp = $id2.$valor2;
        @$$vartemp++;
        @$salida[$id2][$valor2]=$$vartemp/count($valor);
      }
    }
  }
  return $salida;
}
function isJson($string) {
$ob = json_decode(stripslashes($string),true);
if(is_array($ob)) return true;
else return false;
}
function contarValoresArray($array)
{
  $contar=array();
 
  foreach($array as $value)
  {
    if(isset($contar[$value]))
    {
      // si ya existe, le añadimos uno
      $contar[$value]+=1;
    }else{
      // si no existe lo añadimos al array
      $contar[$value]=1;
    }
  }
  return $contar;
}
function repeatedElements($array, $returnWithNonRepeatedItems = false)
{
  $repeated = array();
 
  foreach( (array)$array as $value )
  {
    $inArray = false;
 
    foreach( $repeated as $i => $rItem )
    {
      if( $rItem['value'] === $value )
      {
        $inArray = true;
        ++$repeated[$i]['count'];
      }
    }
 
    if( false === $inArray )
    {
      $i = count($repeated);
      $repeated[$i] = array();
      $repeated[$i]['value'] = $value;
      $repeated[$i]['count'] = 1;
    }
  }
 
  if( ! $returnWithNonRepeatedItems )
  {
    foreach( $repeated as $i => $rItem )
    {
      if($rItem['count'] === 1)
      {
        unset($repeated[$i]);
      }
    }
  }
 
  sort($repeated);
 
  return $repeated;
}
/*Fin Graficas*/
function registrar_visita_usuario($hoy,$usuario){
    require (dirname(__FILE__)."/conexion.php");
    $sql_ultima_sesion = "UPDATE `usuario` SET `ultima_sesion`='".$hoy."' WHERE `id_usuario` = '".$usuario."'";
    $consulta_ultima_sesion = $mysqli->query($sql_ultima_sesion);
}
function duplicar_cuestionario($id){
    @session_start();
    $hoy = date("Y-m-d");
    $usuario = $_SESSION['id_usuario'];
    require (dirname(__FILE__)."/conexion.php");
    $sql = "INSERT INTO `cuestionario`(`nombre`, `fecha`, `tipo_cuestionario`, `usuario`) SELECT concat('Copia de ',`nombre`), '$hoy', `tipo_cuestionario`, '$usuario' FROM `cuestionario` WHERE `id` = $id";
    $consulta = $mysqli->query($sql);
    if ($mysqli->affected_rows>0){
    $id_nuevo = $mysqli->insert_id;
    $sql2 = "INSERT INTO `preguntas_cuestionario`(`id_cuestionario`, `pregunta`, `tipo_pregunta`, `opciones`, `correctas`)  SELECT '$id_nuevo', `pregunta`, `tipo_pregunta`, `opciones`, `correctas` FROM `preguntas_cuestionario` WHERE `id_cuestionario` = $id";
    $consulta2 = $mysqli->query($sql2);
    if ($mysqli->affected_rows>0)
    return true;
    else return false;
    }else{
        return false;
    }
}
?>