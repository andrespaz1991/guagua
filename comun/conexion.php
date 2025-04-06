<?php
error_reporting(E_ALL);
require_once (dirname(__FILE__)."/funciones.php");
require_once (dirname(__FILE__)."/config.php");
#require_once ($_SERVER['DOCUMENT_ROOT']."//guagua/clases/Backup_Database.Class.php");

#$mysqli = new mysqli ('sql101.infinityfree.com','epiz_33170354','7KB2Kajq4e','epiz_33170354_guagua');
$mysqli = new mysqli ('localhost:7000','root','','guagua');
if (mysqli_connect_errno()){
echo "error".mysqli_connect_errno();
}else{
 if($mysqli){
	#echo "si";
 	  mysqli_set_charset($mysqli,'utf8');
 }
}

 ini_set('date.timezone', TIME_ZONE);
 date_default_timezone_set(TIME_ZONE);
 $mysqli->query("SET time_zone =  '".TIME_ZONE_OFFSET."'");
//datos institucion
$sql_ie = "SELECT * FROM `institucion_educativa` WHERE id_institucion_educativa ='1'"; 
if($consulta_ie = $mysqli->query($sql_ie)){
$row_ie=$consulta_ie->fetch_assoc();
if (!defined('NOMBRE_INSTITUCION')) if(isset($row_ie['nombre_institucion'])) define ("NOMBRE_INSTITUCION",$row_ie['nombre_institucion']);
if (!defined('LOGO_INSTITUCION')) if(isset($row_ie['logo_institucion'])) define ("LOGO_INSTITUCION",$row_ie['logo_institucion']);
if (!defined('BANNER_INSTITUCION')) if(isset($row_ie['BANNER_INSTITUCION'])) define ("BANNER_INSTITUCION",$row_ie['BANNER_INSTITUCION']);

}
require("permisos.php");//contiene las restricciones de acceso a carpetas y archivos de acuerdo al rol
?>