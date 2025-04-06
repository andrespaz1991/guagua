<?php
@session_start();
date_default_timezone_set('America/Bogota'); 
#Configuración para conexion a Base de datos
define ('SERVIDORBD','localhost');
define ('USUARIOBD','root');
define ('CLAVEBD','');
define ('BASEDEDATOS','guagua');

/*
define ('SERVIDORBD','5.145.174.49');
define ('USUARIOBD','educatecomco_guagua');
define ('CLAVEBD','PA1t-z$}&3UM');
define ('BASEDEDATOS','educatecomco_guaguas');
*/
#Configuración de Zona Horaria
define ('TIME_ZONE','America/Bogota');
define ('TIME_ZONE_OFFSET','-5:00');
#
#Configuración para ruta de carpetas comunes y modulos
define ("SGA_COMUN_SERVER",dirname(__FILE__));
$url_comun = str_replace("\\","/",dirname(__FILE__)); $url_comun = str_replace($_SERVER['DOCUMENT_ROOT'],"",$url_comun); $url_comun = "//".$_SERVER['SERVER_NAME']."/".$url_comun;
$url =  str_replace("/comun","",$url_comun);                                    
$url_server =  str_replace("/comun","",SGA_COMUN_SERVER);
$ruta_sga_data = "/sga-data/";
define ("SGA_COMUN_URL","//".$url_comun);
define ("SGA_SERVER",$url_server);
define ("SGA_URL","//".$url);
define ("SGA_COMUN_PLANTILLAS","//".$url_server."/plantillas/");
define ("SGA_MEDIA_FOTO","//".$url.$ruta_sga_data."/foto");
define ("SGA_COMUN_IMAGES","//".$url."/comun/img");
define ("SGA_COMUN_SGA_DATA","//".$url."/comun/sga-data/foto");
define ("SGA_COMUN_SOLOSGA_DATA","//".$url."/sga-data/foto");
define ("SGA_COMUN_SGA_DATA_BANNER","//".$url."/comun/sga-data/foto");
define ("SGA_COMUN_IMAGES_URL","//".$url."/comun/img");
define ("SGA_CURSOS_URL","//".$url."/cursos");
define ("SGA_PLANEADOR_URL","//".$url."/Planeador");
define ("SGA_CONTROL_URL","//".$url."/control_ingreso");
define ("SGA_SEGUIMIENTO_URL","//".$url."/seguimiento");
define ("SGA_REPORTES_URL","//".$url."/reportes");
define ("SGA_FOROS_URL","//".$url."/foros");
define ("SGA_USUARIO_URL","//".$url."/usuario");
define ("SGA_MENSAJE_URL","//".$url."/mensajes");
define ("SGA_CUESTIONARIO_URL","//".$url."/cuestionario");
define ("SGA_RED_URL","//".$url."/red");
define ("SGA_EVAL_URL","//".$url."/evaldocente");
define ("SGA",$_SERVER['DOCUMENT_ROOT']);//cuidado es la raiz, no la ruta del sw
if (!file_exists(SGA_SERVER.$ruta_sga_data))
@mkdir (SGA_SERVER.$ruta_sga_data);
define ("READFILE_SERVER",SGA_SERVER.$ruta_sga_data);
define ("READFILE_URL","//".$url.$ruta_sga_data);
$array_roles = array("admin"=>"Administrador","directivo"=>"Directivo","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente","invitado"=>"Invitado");
?>