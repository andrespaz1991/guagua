<?php
require_once __DIR__ . '/seguridad.php';
guagua_iniciar_sesion_segura();
ini_set('display_errors', '0');

// La ruta procede del archivo de configuración guardado o de la variable de entorno.
require_once __DIR__ . '/opciones.php';
define('RUTA_EXCEL_SINCRONIZACION', guagua_ruta_asistencia());


// Compatibilidad para módulos que todavía leen las constantes históricas.
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/academico.php';
$guaguaDatabaseSettings = GuaguaDatabase::settings();
$guaguaLegacyHost = strpos($guaguaDatabaseSettings['host'], ':') !== false
    ? '[' . $guaguaDatabaseSettings['host'] . ']' : $guaguaDatabaseSettings['host'];
define('SERVIDORBD', $guaguaLegacyHost . ':' . $guaguaDatabaseSettings['port']);
define('PUERTOBD', $guaguaDatabaseSettings['port']);
define('USUARIOBD', $guaguaDatabaseSettings['user']);
define('CLAVEBD', $guaguaDatabaseSettings['password']);
define('BASEDEDATOS', $guaguaDatabaseSettings['database']);
define('TIME_ZONE', $guaguaDatabaseSettings['timezone']);
define('TIME_ZONE_OFFSET', $guaguaDatabaseSettings['time_zone']);
date_default_timezone_set(TIME_ZONE);
#
#Configuración para ruta de carpetas comunes y modulos
define ("SGA_COMUN_SERVER",dirname(__FILE__));
$ruta_comun = str_replace("\\", "/", SGA_COMUN_SERVER);
$document_root = rtrim(str_replace("\\", "/", (string) ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2))), "/");

// Las rutas de Windows usan "\". Normalizarlas antes de retirar /comun evita
// que SGA_SERVER quede apuntando a la carpeta comun y que permisos redirija el
// login al no poder identificar el módulo usuario.
$url_comun = str_starts_with($ruta_comun, $document_root)
    ? substr($ruta_comun, strlen($document_root))
    : "/comun";
$url_comun = "/" . ltrim($url_comun, "/");
$url = preg_replace('#/comun$#', '', $url_comun);
$url_server = dirname(SGA_COMUN_SERVER);
$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
$url_comun = "//" . $host . $url_comun;
$url = "//" . $host . $url;
$ruta_sga_data = "/sga-data/";
define ("SGA_COMUN_URL",$url_comun);
define ("SGA_SERVER",$url_server);
define ("SGA_URL",$url);
define ("SGA_COMUN_PLANTILLAS",SGA_COMUN_URL."/plantillas/");
define ("SGA_MEDIA_FOTO",$url.$ruta_sga_data."/foto");
define ("SGA_COMUN_IMAGES",$url."/comun/img");
define ("SGA_COMUN_SGA_DATA",$url."/comun/sga-data/foto");
define ("SGA_COMUN_SOLOSGA_DATA",$url."/sga-data/foto");
define ("SGA_COMUN_SGA_DATA_BANNER",$url."/comun/sga-data/foto");
define ("SGA_COMUN_IMAGES_URL",$url."/comun/img");
define ("SGA_CURSOS_URL",$url."/cursos");
define ("SGA_APPS_URL",$url."/apps");
define ("SGA_PLANEADOR_URL",$url."/Planeador");
define ("SGA_CONTROL_URL",$url."/control_ingreso");
define ("SGA_SEGUIMIENTO_URL",$url."/seguimiento");
define ("SGA_REPORTES_URL",$url."/reportes");
define ("SGA_FOROS_URL",$url."/foros");
define ("SGA_USUARIO_URL",$url."/usuario");
define ("SGA_MENSAJE_URL",$url."/mensajes");
define ("SGA_CUESTIONARIO_URL",$url."/cuestionario");
define ("SGA_RED_URL",$url."/red");
define ("SGA_EVAL_URL",$url."/evaldocente");
define ("SGA",($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__, 2)));//cuidado es la raiz, no la ruta del sw
if (!file_exists(SGA_SERVER.$ruta_sga_data))
@mkdir (SGA_SERVER.$ruta_sga_data);
define ("READFILE_SERVER",SGA_SERVER.$ruta_sga_data);
define ("READFILE_URL",$url.$ruta_sga_data);
$array_roles = array("admin"=>"Administrador","directivo"=>"Directivo","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente","invitado"=>"Invitado");
require_once __DIR__ . '/sesion_contexto.php';
if (!empty($_SESSION['id_usuario'])) guagua_refrescar_contexto_sesion(guagua_db_request());
?>
