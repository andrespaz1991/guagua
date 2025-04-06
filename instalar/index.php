<?php ob_start();
@session_start();
setcookie('instalacion',true);
require (dirname(__FILE__)."/../comun/funciones.php");
if(isset($_POST['base_url'])){
#echo "<pre>";
#print_r($_POST);
#echo "</pre>";
$servidorbd = addslashes($_POST['servidorbd']);
$usuariobd  = addslashes($_POST['usuariobd']);
$clavebd  = addslashes($_POST['clavebd']);
$basededatos = addslashes($_POST['basededatos']);
$mysqli2 = new mysqli($servidorbd,$usuariobd,$clavebd);
mysqli_set_charset($mysqli2,'utf8');
$mysqli2->query("CREATE DATABASE IF NOT EXISTS `$basededatos` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;");
/*
*/
#$mysqli = new mysqli($servidorbd,$usuariobd,$clavebd,$basededatos);
#mysqli_set_charset($mysqli,'utf8');
//time zone
#Configuración de Zona Horaria
$timezone  = addslashes($_POST['timezone']);
$time_zone_offset  = addslashes($_POST['time_zone_offset']);
#
//time zone

//archivo de configuracion
$base_url = "//".addslashes($_POST['base_url']);
$base_server = addslashes($_POST['base_server']);

##crear carpeta de datos
$ruta_sga_data = $_POST['sga_data'];
if (!file_exists($ruta_sga_data))
@mkdir ($ruta_sga_data);
##
$plantilla_congif = '<?php
#Configuración para conexion a Base de datos
define (\'SERVIDORBD\',\''.$servidorbd.'\');
define (\'USUARIOBD\',\''.$usuariobd.'\');
define (\'CLAVEBD\',\''.$clavebd.'\');
define (\'BASEDEDATOS\',\''.$basededatos.'\');
#
#Configuración de Zona Horaria
define (\'TIME_ZONE\',\''.$timezone.'\');
define (\'TIME_ZONE_OFFSET\',\''.$time_zone_offset.'\');
#
#Configuracion para ruta de carpetas comunes y modulos
#
define (\'SGA_URL\',\''.$base_url.'\');
define (\'SGA\',$_SERVER[\'DOCUMENT_ROOT\']);#cuidado es la raiz
define (\'SGA_SERVER\',\''.$base_server.'\');
#
define (\'SGA_COMUN_SERVER\',\''.$base_server.'comun\');
#
define (\'SGA_COMUN_URL\',SGA_URL.\'comun\');
define (\'SGA_CURSOS_URL\',SGA_URL.\'cursos\');
define (\'SGA_FOROS_URL\',SGA_URL.\'foros\');
define (\'SGA_USUARIO_URL\',SGA_URL.\'usuario\');
define (\'SGA_MENSAJE_URL\',SGA_URL.\'mensajes\');
define (\'SGA_CUESTIONARIO_URL\',SGA_URL.\'cuestionario\');
define (\'SGA_RED_URL\',SGA_URL.\'red\');
#
define (\'READFILE_SERVER\',\''.$ruta_sga_data.'\');
define (\'READFILE_URL\',SGA_RED_URL.\'/readfile.php?file=\');
#
$array_roles = array("admin"=>"Administrador","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente");
?>';
$plantilla_congif = '<?php
#Configuración para conexion a Base de datos
define (\'SERVIDORBD\',\''.$servidorbd.'\');
define (\'USUARIOBD\',\''.$usuariobd.'\');
define (\'CLAVEBD\',\''.$clavebd.'\');
define (\'BASEDEDATOS\',\''.$basededatos.'\');
#
#Configuración de Zona Horaria
define (\'TIME_ZONE\',\''.$timezone.'\');
define (\'TIME_ZONE_OFFSET\',\''.$time_zone_offset.'\');
#
#Configuración para ruta de carpetas comunes y modulos
define ("SGA_COMUN_SERVER",dirname(__FILE__));
$url_comun = str_replace("\\\\","/",dirname(__FILE__)); $url_comun = str_replace($_SERVER[\'DOCUMENT_ROOT\'],"",$url_comun); $url_comun = "//".$_SERVER[\'SERVER_NAME\']."/".$url_comun;
$url =  str_replace("/comun","",$url_comun);                                    
$url_server =  str_replace("/comun","",SGA_COMUN_SERVER);
define ("SGA_COMUN_URL","//".$url_comun);
define ("SGA_SERVER",$url_server);
define ("SGA_URL","//".$url);
define ("SGA_COMUN_IMAGES","//".$url."/comun/img");
define ("SGA_COMUN_IMAGES_URL","//".$url."/comun/img");
define ("SGA_CURSOS_URL","//".$url."/cursos");
define ("SGA_REPORTES_URL","//".$url."/reportes");
define ("SGA_FOROS_URL","//".$url."/foros");
define ("SGA_USUARIO_URL","//".$url."/usuario");
define ("SGA_MENSAJE_URL","//".$url."/mensajes");
define ("SGA_CUESTIONARIO_URL","//".$url."/cuestionario");
define ("SGA_RED_URL","//".$url."/red");
define ("SGA",$_SERVER[\'DOCUMENT_ROOT\']);//cuidado es la raiz, no la ruta del sw
$ruta_sga_data = "/'.$ruta_sga_data.'";
if (!file_exists(SGA_SERVER.$ruta_sga_data))
@mkdir (SGA_SERVER.$ruta_sga_data);
define ("READFILE_SERVER",SGA_SERVER.$ruta_sga_data);
#define ("READFILE_URL",SGA_RED_URL."/readfile.php?file=");
define ("READFILE_URL","//".$url.$ruta_sga_data);
#
$array_roles = array("admin"=>"Administrador","directivo"=>"Directivo","docente"=>"Docente","estudiante"=>"Estudiante","acudiente"=>"Acudiente","invitado"=>"Invitado");
?>';
file_put_contents($base_server.'comun/config.php',$plantilla_congif);
#insertar administrador y configuracion de institucion
//requerir conexion
require($base_server.'comun/conexion.php');

//Usuario Administrador
$nombre_ie  = addslashes($_POST['nombre_ie']);
$id_admin  = addslashes($_POST['id_admin']);
$nombre_admin  = addslashes($_POST['nombre_admin']);
$apellido_admin  = addslashes($_POST['apellido_admin']);
$usuario_admin  = addslashes($_POST['usuario_admin']);
$clave_admin  = encriptar(addslashes($_POST['clave_admin']));
$rol="admin";
#conguracion institucion
$nombre_ie = addslashes($_POST['nombre_ie']);
$formatos_no_permitidos = addslashes($_POST['formatos_no_permitidos']);
$tamano_maximo_adjunto = addslashes($_POST['tamano_maximo_adjunto']);
$autoregistro = implode(",",$_POST['autoregistro']);
#fin configuracion institucion
$script_tablas = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";
CREATE TABLE IF NOT EXISTS `actividad` (
  `id_actividad` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(10) NOT NULL,
  `fecha_publicacion` date NOT NULL,
  `hora_publicacion` time NOT NULL,
  `id_red` int(11) DEFAULT NULL,
  `nombre_actividad` varchar(120) COLLATE utf8_bin NOT NULL,
  `Observaciones` text COLLATE utf8_bin NOT NULL,
  `adjunto` varchar(2) COLLATE utf8_bin NOT NULL,
  `evaluable` varchar(2) COLLATE utf8_bin NOT NULL,
  `fecha_entrega` date NOT NULL,
  `hora_entrega` varchar(30) COLLATE utf8_bin NOT NULL,
  `periodo` int(1) NOT NULL,
  `visible` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'SI',
  `cuestionario` varchar(2) COLLATE utf8_bin DEFAULT 'NO',
  `id_cuestionario` int(11) DEFAULT NULL,
  `foro` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'SI',
  `id_foro` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_actividad`),
  KEY `id_asignacion_academica` (`id_asignacion`),
  KEY `id_red` (`id_red`),
  KEY `id_cuestionario` (`id_cuestionario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `acudiente_estudiante` (
  `id_acudiente_estudiante` int(11) NOT NULL AUTO_INCREMENT,
  `id_acudiente` varchar(12) COLLATE utf8_bin NOT NULL,
  `id_estudiante` varchar(12) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_estudiante`,`id_acudiente`),
  UNIQUE KEY `id_acudiente_estudiante` (`id_acudiente_estudiante`),
  KEY `id_acudiente` (`id_acudiente`,`id_estudiante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Asociación de acudiente con estudiante' AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `ano_lectivo` (
  `id_ano_lectivo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_ano_lectivo` varchar(255) COLLATE utf8_bin NOT NULL,
  `estado` enum('Activo','Inactivo') COLLATE utf8_bin NOT NULL DEFAULT 'Inactivo',
  PRIMARY KEY (`id_ano_lectivo`),
  UNIQUE KEY `nombre_ano_lectivo` (`nombre_ano_lectivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `area` (
  `id_area` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_area` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_area`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=10 ;

INSERT INTO `area` (`id_area`, `nombre_area`) VALUES
(1, 'Ciencias naturales y educación ambiental'),
(2, 'Ciencias sociales, historia, geografía, constitución política y democracia'),
(3, 'Educación artística'),
(4, 'Educación ética y en valores humanos'),
(5, 'Educación física, recreación y deportes'),
(6, 'Educación religiosa'),
(7, 'Humanidades, lengua castellana e idiomas extranjeros'),
(8, 'Matemáticas'),
(9, 'Tecnología e informática');

CREATE TABLE IF NOT EXISTS `asignacion_academica` (
  `id_asignacion` int(10) NOT NULL AUTO_INCREMENT,
  `id_materia` int(10) NOT NULL,
  `descripcion` varchar(120) COLLATE utf8_bin NOT NULL,
  `id_docente` varchar(12) COLLATE utf8_bin DEFAULT NULL,
  `id_categoria_curso` int(11) NOT NULL,
  `ano_lectivo` int(11) NOT NULL,
  `visible` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'SI',
  `portada_asignacion` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_asignacion`),
  KEY `id_asignacion` (`id_asignacion`),
  KEY `id_materia` (`id_materia`),
  KEY `id_docente` (`id_docente`),
  KEY `id_materia_2` (`id_materia`),
  KEY `id_categoria_curso` (`id_categoria_curso`),
  KEY `ano_lectivo` (`ano_lectivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `categoria_curso` (
  `id_categoria_curso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_categoria_curso` varchar(120) COLLATE utf8_bin NOT NULL,
  `descripcion_categoria_curso` varchar(255) COLLATE utf8_bin NOT NULL,
  `nivel_educativo` int(1) NOT NULL,
  PRIMARY KEY (`id_categoria_curso`),
  UNIQUE KEY `nombre_categoria_curso` (`nombre_categoria_curso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `comentario` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_entrada` int(11) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contenido` text COLLATE utf8_bin NOT NULL,
  `usuario` varchar(12) COLLATE utf8_bin NOT NULL,
  `estrellas` text COLLATE utf8_bin NOT NULL,
  `rol_quien_comenta` varchar(255) COLLATE utf8_bin NOT NULL,
  `estado` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_comentario`),
  KEY `usuario` (`usuario`),
  KEY `id_entrada` (`id_entrada`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `config` (
  `id_config` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_institucion` varchar(255) COLLATE utf8_bin NOT NULL,
  `logo_institucion` varchar(255) COLLATE utf8_bin NOT NULL,
  `banner_institucion` varchar(255) COLLATE utf8_bin NOT NULL,
  `banner_position` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0px 0px',
  `formatos_no_permitidos` text COLLATE utf8_bin NOT NULL COMMENT 'separado por comas ,',
  `tamano_maximo_adjunto` int(11) NOT NULL COMMENT 'en kilobytes',
  `autoregistrarse` text COLLATE utf8_bin NOT NULL COMMENT 'separado por comas ,',
  `zona_horaria` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_config`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `cuestionario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8_bin NOT NULL,
  `fecha` date NOT NULL,
  `tipo_cuestionario` varchar(255) COLLATE utf8_bin NOT NULL,
  `usuario` varchar(12) COLLATE utf8_bin NOT NULL,
  `estrellas` text COLLATE utf8_bin,
  `visitas` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tipo_encuesta` (`tipo_cuestionario`),
  KEY `usuario` (`usuario`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=2 ;

CREATE TABLE IF NOT EXISTS `entrada` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contenido` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `rol_quien_comenta` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `fecha` datetime DEFAULT NULL,
  `grupo` int(11) NOT NULL,
  `usuario` varchar(12) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `estrellas` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `visitas` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `suscribirse` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `estado` enum('Publicado','Desactivado') CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `permitir_comentarios` varchar(2) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'SI',
  PRIMARY KEY (`id`),
  KEY `autor` (`usuario`),
  KEY `grupo` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8_bin NOT NULL,
  `descripcion` text COLLATE utf8_bin NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `id_usuario` varchar(12) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_usuario_2` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `figuras` (
  `id_figuras` int(8) NOT NULL AUTO_INCREMENT,
  `figura` varchar(120) COLLATE utf8_bin NOT NULL,
  `imagen_figura` varchar(120) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_figuras`),
  UNIQUE KEY `figura` (`figura`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=6 ;

INSERT INTO `figuras` (`id_figuras`, `figura`, `imagen_figura`) VALUES
(1, 'perro', 'perro.PNG'),
(2, 'gato', 'gato.PNG'),
(3, 'caballo', 'caballo.PNG'),
(4, 'conejo', 'conejo.PNG'),
(5, 'pato', 'pato.PNG');

CREATE TABLE IF NOT EXISTS `grupo_foro` (
  `id_grupo_foro` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_grupo` varchar(255) COLLATE utf8_bin NOT NULL,
  `roles_grupo` varchar(255) COLLATE utf8_bin NOT NULL,
  `contexto` varchar(255) COLLATE utf8_bin NOT NULL,
  `valor` int(11) DEFAULT NULL,
  `permitir_temas` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'SI',
  `icono` int(11) NOT NULL,
  PRIMARY KEY (`id_grupo_foro`),
  KEY `icono` (`icono`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=9 ;

CREATE TABLE IF NOT EXISTS `iconos` (
  `id_iconos` int(8) NOT NULL AUTO_INCREMENT,
  `icono` varchar(120) COLLATE utf8_bin NOT NULL,
  `imagen_icono` varchar(120) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_iconos`),
  UNIQUE KEY `icono` (`icono`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=987 ;

INSERT INTO `iconos` (`id_iconos`, `icono`, `imagen_icono`) VALUES
(1, 'add-2', 'add-2.png'),
(2, 'add-3', 'add-3.png'),
(3, 'add', 'add.png'),
(4, 'adicionar', 'adicionar.png'),
(5, 'agenda', 'agenda.png'),
(6, 'alarm-1', 'alarm-1.png'),
(7, 'alarm-clock-1', 'alarm-clock-1.png'),
(8, 'alarm-clock', 'alarm-clock.png'),
(9, 'alarm', 'alarm.png'),
(10, 'albums', 'albums.png'),
(11, 'app', 'app.png'),
(12, 'apple', 'apple.png'),
(13, 'archive-1', 'archive-1.png'),
(14, 'archive-2', 'archive-2.png'),
(15, 'archive-3', 'archive-3.png'),
(16, 'archive', 'archive.png'),
(17, 'attachment', 'attachment.png'),
(18, 'audio', 'audio.png'),
(19, 'audiobook-1', 'audiobook-1.png'),
(20, 'audiobook', 'audiobook.png'),
(21, 'back', 'back.png'),
(22, 'battery-1', 'battery-1.png'),
(23, 'battery-2', 'battery-2.png'),
(24, 'battery-3', 'battery-3.png'),
(25, 'battery-4', 'battery-4.png'),
(26, 'battery-5', 'battery-5.png'),
(27, 'battery-6', 'battery-6.png'),
(28, 'battery-7', 'battery-7.png'),
(29, 'battery-8', 'battery-8.png'),
(30, 'battery-9', 'battery-9.png'),
(31, 'battery', 'battery.png'),
(32, 'binoculars', 'binoculars.png'),
(33, 'blueprint', 'blueprint.png'),
(34, 'bluetooth-1', 'bluetooth-1.png'),
(35, 'bluetooth', 'bluetooth.png'),
(36, 'book', 'book.png'),
(37, 'bookmark-1', 'bookmark-1.png'),
(38, 'bookmark', 'bookmark.png'),
(39, 'briefcase', 'briefcase.png'),
(40, 'broken-link', 'broken-link.png'),
(41, 'browser-1', 'browser-1.png'),
(42, 'browser-2', 'browser-2.png'),
(43, 'browser', 'browser.png'),
(44, 'calculator-1', 'calculator-1.png'),
(45, 'calculator', 'calculator.png'),
(46, 'calendar-1', 'calendar-1.png'),
(47, 'calendar-2', 'calendar-2.png'),
(48, 'calendar-3', 'calendar-3.png'),
(49, 'calendar-4', 'calendar-4.png'),
(50, 'calendar-5', 'calendar-5.png'),
(51, 'calendar-6', 'calendar-6.png'),
(52, 'calendar-7', 'calendar-7.png'),
(53, 'calendar', 'calendar.png'),
(54, 'certificate', 'certificate.png'),
(55, 'chat-7', 'chat-7.png'),
(56, 'chat-8', 'chat-8.png'),
(57, 'chat', 'chat.png'),
(58, 'checked-1', 'checked-1.png'),
(59, 'checked', 'checked.png'),
(60, 'chemistry', 'chemistry.png'),
(61, 'chip', 'chip.png'),
(62, 'clock-1', 'clock-1.png'),
(63, 'clock', 'clock.png'),
(64, 'close', 'close.png'),
(65, 'cloud-computing-1', 'cloud-computing-1.png'),
(66, 'cloud-computing-2', 'cloud-computing-2.png'),
(67, 'cloud-computing-3', 'cloud-computing-3.png'),
(68, 'cloud-computing-4', 'cloud-computing-4.png'),
(69, 'cloud-computing-5', 'cloud-computing-5.png'),
(70, 'cloud-computing', 'cloud-computing.png'),
(71, 'cloud', 'cloud.png'),
(72, 'code', 'code.png'),
(73, 'command', 'command.png'),
(74, 'compact-disc-1', 'compact-disc-1.png'),
(75, 'compact-disc-2', 'compact-disc-2.png'),
(76, 'compact-disc', 'compact-disc.png'),
(77, 'compass', 'compass.png'),
(78, 'compose', 'compose.png'),
(79, 'controls-1', 'controls-1.png'),
(80, 'controls-2', 'controls-2.png'),
(81, 'controls-3', 'controls-3.png'),
(82, 'controls-4', 'controls-4.png'),
(83, 'controls-5', 'controls-5.png'),
(84, 'controls-6', 'controls-6.png'),
(85, 'controls-7', 'controls-7.png'),
(86, 'controls-8', 'controls-8.png'),
(87, 'controls-9', 'controls-9.png'),
(88, 'controls', 'controls.png'),
(89, 'crm', 'crm.png'),
(90, 'database-1', 'database-1.png'),
(91, 'database-2', 'database-2.png'),
(92, 'database-3', 'database-3.png'),
(93, 'database', 'database.png'),
(94, 'design', 'design.png'),
(95, 'desk', 'desk.png'),
(96, 'desktop', 'desktop.png'),
(97, 'diamond', 'diamond.png'),
(98, 'diploma', 'diploma.png'),
(99, 'dislike-1', 'dislike-1.png'),
(100, 'dislike', 'dislike.png'),
(101, 'divide-1', 'divide-1.png'),
(102, 'divide', 'divide.png'),
(103, 'division', 'division.png'),
(104, 'doc', 'doc.png'),
(105, 'document', 'document.png'),
(106, 'download', 'download.png'),
(107, 'earth-globe', 'earth-globe.png'),
(108, 'ebook-1', 'ebook-1.png'),
(109, 'ebook-2', 'ebook-2.png'),
(110, 'ebook-3', 'ebook-3.png'),
(111, 'ebook', 'ebook.png'),
(112, 'edit-1', 'edit-1.png'),
(113, 'edit', 'edit.png'),
(114, 'eject-1', 'eject-1.png'),
(115, 'eject', 'eject.png'),
(116, 'emoji-cuy-risas', 'emoji-cuy-risas.png'),
(117, 'equal-1', 'equal-1.png'),
(118, 'equal-2', 'equal-2.png'),
(119, 'equal', 'equal.png'),
(120, 'ereader', 'ereader.png'),
(121, 'error', 'error.png'),
(122, 'exam', 'exam.png'),
(123, 'exit-1', 'exit-1.png'),
(124, 'exit-2', 'exit-2.png'),
(125, 'exit', 'exit.png'),
(126, 'eyeglasses', 'eyeglasses.png'),
(127, 'fast-forward-1', 'fast-forward-1.png'),
(128, 'fast-forward', 'fast-forward.png'),
(129, 'fax', 'fax.png'),
(130, 'file-1', 'file-1.png'),
(131, 'file-2', 'file-2.png'),
(132, 'file', 'file.png'),
(133, 'film', 'film.png'),
(134, 'fingerprint', 'fingerprint.png'),
(135, 'flag-1', 'flag-1.png'),
(136, 'flag-2', 'flag-2.png'),
(137, 'flag-3', 'flag-3.png'),
(138, 'flag-4', 'flag-4.png'),
(139, 'flag', 'flag.png'),
(140, 'focus', 'focus.png'),
(141, 'folder-1', 'folder-1.png'),
(142, 'folder-10', 'folder-10.png'),
(143, 'folder-11', 'folder-11.png'),
(144, 'folder-12', 'folder-12.png'),
(145, 'folder-13', 'folder-13.png'),
(146, 'folder-14', 'folder-14.png'),
(147, 'folder-15', 'folder-15.png'),
(148, 'folder-16', 'folder-16.png'),
(149, 'folder-17', 'folder-17.png'),
(150, 'folder-18', 'folder-18.png'),
(151, 'folder-19', 'folder-19.png'),
(152, 'folder-2', 'folder-2.png'),
(153, 'folder-3', 'folder-3.png'),
(154, 'folder-4', 'folder-4.png'),
(155, 'folder-5', 'folder-5.png'),
(156, 'folder-6', 'folder-6.png'),
(157, 'folder-7', 'folder-7.png'),
(158, 'folder-8', 'folder-8.png'),
(159, 'folder-9', 'folder-9.png'),
(160, 'folder', 'folder.png'),
(161, 'forbidden', 'forbidden.png'),
(162, 'foro', 'foro.png'),
(163, 'funnel', 'funnel.png'),
(164, 'garbage-1', 'garbage-1.png'),
(165, 'garbage-2', 'garbage-2.png'),
(166, 'garbage', 'garbage.png'),
(167, 'geography', 'geography.png'),
(168, 'gift', 'gift.png'),
(169, 'google-glasses', 'google-glasses.png'),
(170, 'help', 'help.png'),
(171, 'hide', 'hide.png'),
(172, 'hold', 'hold.png'),
(173, 'home-1', 'home-1.png'),
(174, 'home-2', 'home-2.png'),
(175, 'home', 'home.png'),
(176, 'homework', 'homework.png'),
(177, 'hourglass-1', 'hourglass-1.png'),
(178, 'hourglass-2', 'hourglass-2.png'),
(179, 'hourglass-3', 'hourglass-3.png'),
(180, 'hourglass', 'hourglass.png'),
(181, 'house', 'house.png'),
(182, 'id-card-1', 'id-card-1.png'),
(183, 'id-card-2', 'id-card-2.png'),
(184, 'id-card-3', 'id-card-3.png'),
(185, 'id-card-4', 'id-card-4.png'),
(186, 'id-card-5', 'id-card-5.png'),
(187, 'id-card', 'id-card.png'),
(188, 'idea', 'idea.png'),
(189, 'image', 'image.png'),
(190, 'incoming', 'incoming.png'),
(191, 'infinity', 'infinity.png'),
(192, 'info', 'info.png'),
(193, 'information', 'information.png'),
(194, 'internet', 'internet.png'),
(195, 'key', 'key.png'),
(196, 'keyboard', 'keyboard.png'),
(197, 'lamp', 'lamp.png'),
(198, 'laptop', 'laptop.png'),
(199, 'layers-1', 'layers-1.png'),
(200, 'layers', 'layers.png'),
(201, 'learning-1', 'learning-1.png'),
(202, 'learning-2', 'learning-2.png'),
(203, 'learning', 'learning.png'),
(204, 'lecture', 'lecture.png'),
(205, 'library', 'library.png'),
(206, 'like-1', 'like-1.png'),
(207, 'like-2', 'like-2.png'),
(208, 'like', 'like.png'),
(209, 'line-15-icons', 'line-15-icons.png'),
(210, 'link', 'link.png'),
(211, 'list-1', 'list-1.png'),
(212, 'list-15', 'list-15.png'),
(213, 'list', 'list.png'),
(214, 'listening', 'listening.png'),
(215, 'lock-1', 'lock-1.png'),
(216, 'lock', 'lock.png'),
(217, 'locked-1', 'locked-1.png'),
(218, 'locked-2', 'locked-2.png'),
(219, 'locked-3', 'locked-3.png'),
(220, 'locked-4', 'locked-4.png'),
(221, 'locked-5', 'locked-5.png'),
(222, 'locked-6', 'locked-6.png'),
(223, 'locked', 'locked.png'),
(224, 'login', 'login.png'),
(225, 'magic-wand', 'magic-wand.png'),
(226, 'magnet-1', 'magnet-1.png'),
(227, 'magnet-2', 'magnet-2.png'),
(228, 'magnet', 'magnet.png'),
(229, 'map-1', 'map-1.png'),
(230, 'map-2', 'map-2.png'),
(231, 'map-location', 'map-location.png'),
(232, 'map', 'map.png'),
(233, 'medal', 'medal.png'),
(234, 'megaphone-1', 'megaphone-1.png'),
(235, 'megaphone', 'megaphone.png'),
(236, 'mensaje', 'mensaje.png'),
(237, 'menu-1', 'menu-1.png'),
(238, 'menu-2', 'menu-2.png'),
(239, 'menu-3', 'menu-3.png'),
(240, 'menu-4', 'menu-4.png'),
(241, 'menu', 'menu.png'),
(242, 'microphone-1', 'microphone-1.png'),
(243, 'microphone', 'microphone.png'),
(244, 'minus-1', 'minus-1.png'),
(245, 'minus', 'minus.png'),
(246, 'mkv', 'mkv.png'),
(247, 'moneda', 'moneda.png'),
(248, 'more-1', 'more-1.png'),
(249, 'more-2', 'more-2.png'),
(250, 'more', 'more.png'),
(251, 'mortarboard', 'mortarboard.png'),
(252, 'mouse', 'mouse.png'),
(253, 'mp3', 'mp3.png'),
(254, 'multiply-1', 'multiply-1.png'),
(255, 'multiply', 'multiply.png'),
(256, 'music-player-1', 'music-player-1.png'),
(257, 'music-player-2', 'music-player-2.png'),
(258, 'music-player-3', 'music-player-3.png'),
(259, 'music-player', 'music-player.png'),
(260, 'mute', 'mute.png'),
(261, 'muted', 'muted.png'),
(262, 'navigation-1', 'navigation-1.png'),
(263, 'navigation', 'navigation.png'),
(264, 'network', 'network.png'),
(265, 'newspaper', 'newspaper.png'),
(266, 'next', 'next.png'),
(267, 'note', 'note.png'),
(268, 'notebook-1', 'notebook-1.png'),
(269, 'notebook-2', 'notebook-2.png'),
(270, 'notebook-3', 'notebook-3.png'),
(271, 'notebook-4', 'notebook-4.png'),
(272, 'notebook-5', 'notebook-5.png'),
(273, 'notebook', 'notebook.png'),
(274, 'notepad-1', 'notepad-1.png'),
(275, 'notepad-2', 'notepad-2.png'),
(276, 'notepad', 'notepad.png'),
(277, 'notes', 'notes.png'),
(278, 'notification', 'notification.png'),
(279, 'open-book', 'open-book.png'),
(280, 'paper-plane-1', 'paper-plane-1.png'),
(281, 'paper-plane', 'paper-plane.png'),
(282, 'pause-1', 'pause-1.png'),
(283, 'pause', 'pause.png'),
(284, 'pdf', 'pdf.png'),
(285, 'pendrive', 'pendrive.png'),
(286, 'percent-1', 'percent-1.png'),
(287, 'percent', 'percent.png'),
(288, 'perspective', 'perspective.png'),
(289, 'photo-camera-1', 'photo-camera-1.png'),
(290, 'photo-camera', 'photo-camera.png'),
(291, 'photos', 'photos.png'),
(292, 'picture-1', 'picture-1.png'),
(293, 'picture-2', 'picture-2.png'),
(294, 'picture', 'picture.png'),
(295, 'pin', 'pin.png'),
(296, 'placeholder-1', 'placeholder-1.png'),
(297, 'placeholder-2', 'placeholder-2.png'),
(298, 'placeholder-3', 'placeholder-3.png'),
(299, 'placeholder', 'placeholder.png'),
(300, 'placeholders', 'placeholders.png'),
(301, 'play-button-1', 'play-button-1.png'),
(302, 'play-button', 'play-button.png'),
(303, 'plus', 'plus.png'),
(304, 'power', 'power.png'),
(305, 'previous', 'previous.png'),
(306, 'price-tag', 'price-tag.png'),
(307, 'print', 'print.png'),
(308, 'professor', 'professor.png'),
(309, 'projector', 'projector.png'),
(310, 'push-pin', 'push-pin.png'),
(311, 'question-1', 'question-1.png'),
(312, 'question', 'question.png'),
(313, 'questions', 'questions.png'),
(314, 'radar', 'radar.png'),
(315, 'reading', 'reading.png'),
(316, 'record', 'record.png'),
(317, 'repeat-1', 'repeat-1.png'),
(318, 'repeat', 'repeat.png'),
(319, 'restart', 'restart.png'),
(320, 'resume', 'resume.png'),
(321, 'rewind-1', 'rewind-1.png'),
(322, 'rewind', 'rewind.png'),
(323, 'route', 'route.png'),
(324, 'save', 'save.png'),
(325, 'science-book', 'science-book.png'),
(326, 'search-1', 'search-1.png'),
(327, 'search-engine', 'search-engine.png'),
(328, 'search', 'search.png'),
(329, 'send', 'send.png'),
(330, 'server-1', 'server-1.png'),
(331, 'server-2', 'server-2.png'),
(332, 'server-3', 'server-3.png'),
(333, 'server', 'server.png'),
(334, 'settings-1', 'settings-1.png'),
(335, 'settings-10', 'settings-10.png'),
(336, 'settings-2', 'settings-2.png'),
(337, 'settings-3', 'settings-3.png'),
(338, 'settings-4', 'settings-4.png'),
(339, 'settings-5', 'settings-5.png'),
(340, 'settings-6', 'settings-6.png'),
(341, 'settings-7', 'settings-7.png'),
(342, 'settings-8', 'settings-8.png'),
(343, 'settings-9', 'settings-9.png'),
(344, 'settings', 'settings.png'),
(345, 'share-1', 'share-1.png'),
(346, 'share-2', 'share-2.png'),
(347, 'share', 'share.png'),
(348, 'shopping-cart', 'shopping-cart.png'),
(349, 'shuffle-1', 'shuffle-1.png'),
(350, 'shuffle', 'shuffle.png'),
(351, 'shutdown', 'shutdown.png'),
(352, 'sign-1', 'sign-1.png'),
(353, 'sign', 'sign.png'),
(354, 'skip', 'skip.png'),
(355, 'smartphone-1', 'smartphone-1.png'),
(356, 'smartphone-10', 'smartphone-10.png'),
(357, 'smartphone-11', 'smartphone-11.png'),
(358, 'smartphone-2', 'smartphone-2.png'),
(359, 'smartphone-3', 'smartphone-3.png'),
(360, 'smartphone-4', 'smartphone-4.png'),
(361, 'smartphone-5', 'smartphone-5.png'),
(362, 'smartphone-6', 'smartphone-6.png'),
(363, 'smartphone-7', 'smartphone-7.png'),
(364, 'smartphone-8', 'smartphone-8.png'),
(365, 'smartphone-9', 'smartphone-9.png'),
(366, 'smartphone', 'smartphone.png'),
(367, 'speaker-1', 'speaker-1.png'),
(368, 'speaker-2', 'speaker-2.png'),
(369, 'speaker-3', 'speaker-3.png'),
(370, 'speaker-4', 'speaker-4.png'),
(371, 'speaker-5', 'speaker-5.png'),
(372, 'speaker-6', 'speaker-6.png'),
(373, 'speaker-7', 'speaker-7.png'),
(374, 'speaker-8', 'speaker-8.png'),
(375, 'speaker-sga', 'speaker-sga.png'),
(376, 'speaker', 'speaker.png'),
(377, 'speech-bubble-2', 'speech-bubble-2.png'),
(378, 'speech-bubble', 'speech-bubble.png'),
(379, 'spotlight', 'spotlight.png'),
(380, 'star-1', 'star-1.png'),
(381, 'star', 'star.png'),
(382, 'statistics', 'statistics.png'),
(383, 'stop-1', 'stop-1.png'),
(384, 'stop', 'stop.png'),
(385, 'stopwatch-1', 'stopwatch-1.png'),
(386, 'stopwatch-2', 'stopwatch-2.png'),
(387, 'stopwatch-3', 'stopwatch-3.png'),
(388, 'stopwatch-4', 'stopwatch-4.png'),
(389, 'stopwatch', 'stopwatch.png'),
(390, 'street-1', 'street-1.png'),
(391, 'street', 'street.png'),
(392, 'student-1', 'student-1.png'),
(393, 'student-2', 'student-2.png'),
(394, 'student-3', 'student-3.png'),
(395, 'student', 'student.png'),
(396, 'substract-1', 'substract-1.png'),
(397, 'substract', 'substract.png'),
(398, 'success', 'success.png'),
(399, 'switch-1', 'switch-1.png'),
(400, 'switch-2', 'switch-2.png'),
(401, 'switch-3', 'switch-3.png'),
(402, 'switch-4', 'switch-4.png'),
(403, 'switch-5', 'switch-5.png'),
(404, 'switch-6', 'switch-6.png'),
(405, 'switch-7', 'switch-7.png'),
(406, 'switch', 'switch.png'),
(407, 'tablet', 'tablet.png'),
(408, 'tabs-1', 'tabs-1.png'),
(409, 'tabs', 'tabs.png'),
(410, 'target', 'target.png'),
(411, 'television-1', 'television-1.png'),
(412, 'television', 'television.png'),
(413, 'test-1', 'test-1.png'),
(414, 'test-2', 'test-2.png'),
(415, 'test-3', 'test-3.png'),
(416, 'test', 'test.png'),
(417, 'time', 'time.png'),
(418, 'touchscreen', 'touchscreen.png'),
(419, 'translator', 'translator.png'),
(420, 'trash', 'trash.png'),
(421, 'tutorial', 'tutorial.png'),
(422, 'txt', 'txt.png'),
(423, 'umbrella', 'umbrella.png'),
(424, 'university-1', 'university-1.png'),
(425, 'university', 'university.png'),
(426, 'unlink', 'unlink.png'),
(427, 'unlocked-1', 'unlocked-1.png'),
(428, 'unlocked-2', 'unlocked-2.png'),
(429, 'unlocked', 'unlocked.png'),
(430, 'upload', 'upload.png'),
(431, 'user-1', 'user-1.png'),
(432, 'user-2', 'user-2.png'),
(433, 'user-3', 'user-3.png'),
(434, 'user-4', 'user-4.png'),
(435, 'user-5', 'user-5.png'),
(436, 'user-6', 'user-6.png'),
(437, 'user-7', 'user-7.png'),
(438, 'user', 'user.png'),
(439, 'users-1', 'users-1.png'),
(440, 'users', 'users.png'),
(441, 'verification', 'verification.png'),
(442, 'video-call', 'video-call.png'),
(443, 'video-camera-1', 'video-camera-1.png'),
(444, 'video-camera', 'video-camera.png'),
(445, 'video-player-1', 'video-player-1.png'),
(446, 'video-player-2', 'video-player-2.png'),
(447, 'video-player', 'video-player.png'),
(448, 'view-1', 'view-1.png'),
(449, 'view-2', 'view-2.png'),
(450, 'view-line', 'view-line.png'),
(451, 'view', 'view.png'),
(452, 'volume-control-1', 'volume-control-1.png'),
(453, 'volume-control', 'volume-control.png'),
(454, 'warning', 'warning.png'),
(455, 'wifi-1', 'wifi-1.png'),
(456, 'wifi', 'wifi.png'),
(457, 'windows-1', 'windows-1.png'),
(458, 'windows-2', 'windows-2.png'),
(459, 'windows-3', 'windows-3.png'),
(460, 'windows-4', 'windows-4.png'),
(461, 'windows', 'windows.png'),
(462, 'wireless-internet', 'wireless-internet.png'),
(463, 'worldwide-1', 'worldwide-1.png'),
(464, 'worldwide', 'worldwide.png'),
(465, 'zoom-in', 'zoom-in.png'),
(466, 'zoom-out', 'zoom-out.png'),
(467, 'abacus', 'abacus.png'),
(468, 'american-football', 'american-football.png'),
(469, 'apple.1', 'apple.1.png'),
(470, 'baseball', 'baseball.png'),
(471, 'basketball', 'basketball.png'),
(472, 'bell', 'bell.png'),
(473, 'books', 'books.png'),
(474, 'bookshelf', 'bookshelf.png'),
(475, 'calculator-1.1', 'calculator-1.1.png'),
(476, 'calculator.1', 'calculator.1.png'),
(477, 'calendar.1', 'calendar.1.png'),
(478, 'calendario', 'calendario.png'),
(479, 'chalkboard-1', 'chalkboard-1.png'),
(480, 'chalkboard', 'chalkboard.png'),
(481, 'chemistry-1', 'chemistry-1.png'),
(482, 'chemistry.1', 'chemistry.1.png'),
(483, 'clock.1', 'clock.1.png'),
(484, 'college', 'college.png'),
(485, 'compact-disc.1', 'compact-disc.1.png'),
(486, 'compass.1', 'compass.1.png'),
(487, 'computer', 'computer.png'),
(488, 'crayon', 'crayon.png'),
(489, 'cup', 'cup.png'),
(490, 'diploma-1', 'diploma-1.png'),
(491, 'diploma-2', 'diploma-2.png'),
(492, 'diploma-3', 'diploma-3.png'),
(493, 'diploma.1', 'diploma.1.png'),
(494, 'earth-globe-1', 'earth-globe-1.png'),
(495, 'earth-globe.1', 'earth-globe.1.png'),
(496, 'foro.1', 'foro.1.png'),
(497, 'glue', 'glue.png'),
(498, 'marker', 'marker.png'),
(499, 'mensaje.1', 'mensaje.1.png'),
(500, 'microscope', 'microscope.png'),
(501, 'mortarboard.1', 'mortarboard.1.png'),
(502, 'notebook-1.1', 'notebook-1.1.png'),
(503, 'notebooka', 'notebooka.png'),
(504, 'open-book.1', 'open-book.1.png'),
(505, 'paint-palette', 'paint-palette.png'),
(506, 'pen', 'pen.png'),
(507, 'pencil', 'pencil.png'),
(508, 'pendrive.1', 'pendrive.1.png'),
(509, 'regalo', 'regalo.png'),
(510, 'research-1', 'research-1.png'),
(511, 'research', 'research.png'),
(512, 'ruler', 'ruler.png'),
(513, 'school-bus', 'school-bus.png'),
(514, 'school-material', 'school-material.png'),
(515, 'science', 'science.png'),
(516, 'scissors', 'scissors.png'),
(517, 'set-square', 'set-square.png'),
(518, 'sharpener', 'sharpener.png'),
(519, 'statistics.1', 'statistics.1.png'),
(520, 'telescope', 'telescope.png');

CREATE TABLE IF NOT EXISTS `inscripcion` (
  `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT,
  `id_asignacion` int(10) NOT NULL,
  `id_estudiante` varchar(12) COLLATE utf8_bin NOT NULL,
  `fecha_inscripcion` date NOT NULL,
  `estado_inscripcion` enum('Aprobado','No aprobado','En curso','Retirado') COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_inscripcion`),
  UNIQUE KEY `id_asignacion` (`id_asignacion`,`id_estudiante`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_curso` (`id_asignacion`),
  KEY `id_estudiante_2` (`id_estudiante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `materia` (
  `id_materia` int(10) NOT NULL AUTO_INCREMENT,
  `nombre_materia` varchar(255) COLLATE utf8_bin NOT NULL,
  `obligatoria` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'NO',
  `area` int(11) NOT NULL,
  `icono_materia` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_materia`),
  KEY `area` (`area`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=11 ;

INSERT INTO `materia` (`id_materia`, `nombre_materia`, `obligatoria`, `area`, `icono_materia`) VALUES
(1, 'CIENCIAS NATURALES', 'SI', 5, '500'),
(2, 'CIENCIAS SOCIALES', 'SI', 2, '494'),
(3, 'EDUCACIÓN ARTÍSTICA', 'SI', 3, '514'),
(4, 'EDUCACIÓN ÉTICA Y VALORES', 'SI', 4, '491'),
(5, 'EDUCACIÓN FISICA', 'SI', 5, '471'),
(6, 'EDUCACIÓN RELIGIOSA', 'SI', 6, '279'),
(7, 'LENGUA CASTELLANA', 'SI', 7, '474'),
(8, 'INGLES', 'SI', 7, '473'),
(9, 'MATEMÁTICAS', 'SI', 8, '44'),
(10, 'TECNOLOGÍA E INFORMÁTICA', 'SI', 9, '487');

CREATE TABLE IF NOT EXISTS `mensaje` (
  `id_mensaje` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(12) COLLATE utf8_bin NOT NULL,
  `mensaje` text COLLATE utf8_bin NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` varchar(2) COLLATE utf8_bin NOT NULL DEFAULT 'NO',
  `remite` varchar(12) COLLATE utf8_bin NOT NULL,
  `estado_usuario` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'entrada',
  `estado_remite` varchar(255) COLLATE utf8_bin NOT NULL,
  `favorito` varchar(2) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_mensaje`),
  KEY `usuario` (`usuario`),
  KEY `remite` (`remite`),
  KEY `usuario_2` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `periodo` (
  `id_periodo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_periodo` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_periodo`),
  UNIQUE KEY `nombre_periodo` (`nombre_periodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `preguntas_cuestionario` (
  `id_preguntas_cuestionario` int(11) NOT NULL AUTO_INCREMENT,
  `id_cuestionario` int(11) NOT NULL,
  `pregunta` varchar(255) COLLATE utf8_bin NOT NULL,
  `tipo_pregunta` varchar(255) COLLATE utf8_bin NOT NULL,
  `opciones` text COLLATE utf8_bin NOT NULL,
  `correctas` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_preguntas_cuestionario`),
  KEY `id_encuesta` (`id_cuestionario`),
  KEY `tipo_pregunta` (`tipo_pregunta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `red` (
  `id_red` int(11) NOT NULL AUTO_INCREMENT,
  `titulo_red` varchar(255) COLLATE utf8_bin NOT NULL,
  `idioma_red` varchar(120) COLLATE utf8_bin NOT NULL,
  `contexto` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'Primaria',
  `descripcion` varchar(120) COLLATE utf8_bin NOT NULL,
  `palabras_clave` text COLLATE utf8_bin NOT NULL,
  `nivel_eductivo` text COLLATE utf8_bin NOT NULL,
  `autor` varchar(255) COLLATE utf8_bin NOT NULL,
  `responsable` varchar(12) COLLATE utf8_bin NOT NULL,
  `formato` varchar(10) COLLATE utf8_bin NOT NULL,
  `tipo_interacción` varchar(255) COLLATE utf8_bin NOT NULL,
  `tipo_recurso_educativo` text COLLATE utf8_bin NOT NULL,
  `dificultad` int(1) NOT NULL,
  `fecha` date NOT NULL,
  `estrellas` text COLLATE utf8_bin NOT NULL,
  `enlace` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'Hace referencia a la ubicación del objeto',
  `scorm` varchar(4) COLLATE utf8_bin NOT NULL,
  `adjunto` varchar(4) COLLATE utf8_bin NOT NULL,
  `icono_red` varchar(255) COLLATE utf8_bin NOT NULL,
  `materia_red` int(10) NOT NULL COMMENT 'FUente de clasificación por materia',
  `cantidad_estrellas` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '0',
  `visitas` bigint(20) NOT NULL,
  `version` varchar(255) COLLATE utf8_bin NOT NULL,
  `entidad_origen` varchar(255) COLLATE utf8_bin NOT NULL,
  `fecha_creacion` date NOT NULL,
  `tamano` varchar(255) COLLATE utf8_bin NOT NULL,
  `requerimientos` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'requerimientos técnicos',
  `costo` varchar(255) COLLATE utf8_bin NOT NULL COMMENT 'libre o comercial',
  `derechos_autor` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_red`),
  KEY `materia_red` (`materia_red`),
  KEY `responsable` (`responsable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `respuesta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_actividad` int(11) DEFAULT '3',
  `pregunta` int(11) NOT NULL,
  `respuesta` text COLLATE utf8_bin NOT NULL,
  `fechayhora` datetime NOT NULL,
  `id_estudiante` varchar(12) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pregunta` (`pregunta`),
  KEY `id_estudiante` (`id_estudiante`),
  KEY `id_actividad` (`id_actividad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `seguimiento` (
  `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_actividad` int(11) NOT NULL,
  `id_inscripcion` int(11) NOT NULL,
  `fechayhora_valoracion` datetime NOT NULL,
  `valoracion` varchar(255) COLLATE utf8_bin NOT NULL,
  `observacion` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_seguimiento`),
  UNIQUE KEY `id_actividad_2` (`id_actividad`,`id_inscripcion`),
  KEY `id_actividad` (`id_actividad`),
  KEY `id_inscripcion` (`id_inscripcion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `seguimiento_categoria_ano` (
  `id_seguimiento_categoria_ano` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` int(11) NOT NULL,
  `ano_lectivo` int(11) NOT NULL,
  `id_estudiante` varchar(12) COLLATE utf8_bin NOT NULL,
  `estado` enum('Aprobado','No aprobado','En curso','Retirado') COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_seguimiento_categoria_ano`),
  KEY `categoria` (`categoria`),
  KEY `ano_lectivo` (`ano_lectivo`),
  KEY `id_estudiante` (`id_estudiante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tarea_adjunto` (
  `id_tarea_adjunto` int(11) NOT NULL AUTO_INCREMENT,
  `id_actividad` int(11) NOT NULL,
  `id_estudiante` varchar(12) COLLATE utf8_bin NOT NULL,
  `fechayhora_adjunto` datetime NOT NULL,
  `observacion` text COLLATE utf8_bin NOT NULL,
  `adjunto` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_tarea_adjunto`),
  UNIQUE KEY `id_actividad_2` (`id_actividad`,`id_estudiante`),
  UNIQUE KEY `id_actividad_3` (`id_actividad`,`id_estudiante`),
  KEY `id_actividad` (`id_actividad`),
  KEY `id_inscripcion` (`id_estudiante`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `tipo_pregunta` (
  `id` varchar(255) COLLATE utf8_bin NOT NULL,
  `nombre` varchar(255) COLLATE utf8_bin NOT NULL,
  `categorias` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `categorias` (`categorias`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `tipo_pregunta` (`id`, `nombre`, `categorias`) VALUES
('checkbox', 'Casilla de Verificación', 'Respuesta Múltiple'),
('input', 'Respuesta breve', 'Respuesta Única'),
('radio', 'Opción Múltiple', 'Respuesta Única'),
('select', 'Lista desplegable', 'Respuesta Única'),
('textarea', 'Respuesta extensa', 'Respuesta Única');

CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` varchar(12) COLLATE utf8_bin NOT NULL,
  `usuario` varchar(255) COLLATE utf8_bin NOT NULL,
  `clave` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '6139bdc23a06bc12f1fa866f5297385bbda6354e',
  `mascota` enum('SI','NO') COLLATE utf8_bin NOT NULL DEFAULT 'NO',
  `nombre` varchar(255) COLLATE utf8_bin NOT NULL,
  `apellido` varchar(255) COLLATE utf8_bin NOT NULL,
  `rol` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'estudiante',
  `foto` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'user-icon.png',
  `direccion` varchar(255) COLLATE utf8_bin NOT NULL,
  `telefono` varchar(25) COLLATE utf8_bin NOT NULL,
  `correo` varchar(255) COLLATE utf8_bin NOT NULL,
  `ultima_sesion` datetime NOT NULL,
  `num_visitas` int(11) NOT NULL,
  `puntos` int(11) NOT NULL COMMENT 'Son los puntos que un usuario acumula',
  `estado` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'activo',
  `tipo_sangre` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";
$sql_admin = "INSERT INTO `usuario`(`id_usuario`, `usuario`, `clave`, `nombre`, `apellido`, `rol`) VALUES ('$id_admin','$usuario_admin','$clave_admin','$nombre_admin','$apellido_admin','$rol');";
$script_tablas = $script_tablas.$sql_admin;
#conguracion institucion
if (isset($_FILES['logo_ie'])){
$info = new SplFileInfo($_FILES['logo_ie']['name']);
$nombre_archivo = $info->getBasename();
$ext = $info->getExtension();
@mkdir ($base_server.$ruta_sga_data."/foto");
if(copy($_FILES['logo_ie']['tmp_name'],$base_server.$ruta_sga_data."/foto/logo.".$ext))
$logo_institucion = "logo.".$ext;
else
$logo_institucion="";
}
if (isset($_FILES['banner_ie'])){
$info2 = new SplFileInfo($_FILES['banner_ie']['name']);
$nombre_archivo2 = $info2->getBasename();
$ext2 = $info->getExtension();
@mkdir ($base_server.$ruta_sga_data."/foto");
if(copy($_FILES['banner_ie']['tmp_name'],$base_server.$ruta_sga_data."/foto/banner.".$ext2))
$banner_institucion = "banner.".$ext2;
else
$banner_institucion = "";
}
@mkdir ($base_server.$ruta_sga_data."/foto");
@mkdir ($base_server.$ruta_sga_data."/portada");
copy(dirname(__FILE__)."/403.php",$base_server.$ruta_sga_data."/index.php");
copy(dirname(__FILE__)."/403.php",$base_server.$ruta_sga_data."/foto/index.php");
copy(dirname(__FILE__)."/403.php",$base_server.$ruta_sga_data."/portada/index.php");
copy(dirname(__FILE__)."/user-icon.png",$base_server.$ruta_sga_data."/foto/user-icon.png");
$sql_config = "INSERT INTO `config`(`id_config`, `nombre_institucion`, `logo_institucion`, `banner_institucion`, `formatos_no_permitidos`, `tamano_maximo_adjunto`, `autoregistrarse`) VALUES (1,'$nombre_ie','$logo_institucion','$banner_institucion','$formatos_no_permitidos','$tamano_maximo_adjunto','$autoregistro');";
$script_tablas = $script_tablas.$sql_config;
#fin conguracion institucion
#configuracion de periodo
if (isset($_POST['periodo'])){
$periodo = $_POST['periodo'];
if (count($periodo)>0)
foreach($periodo as $id => $per){
$sql_peer = "INSERT INTO `periodo`(`nombre_periodo`) VALUES ('$per');";
$script_tablas = $script_tablas.$sql_peer;
}
}
#fin configuracion de periodo
#configuracion de categoria
if (isset($_POST['categoria'])){
$categoria = $_POST['categoria'];
if (count($categoria)>0)
foreach($categoria as $id => $cat){
if($cat!=""){
$sql_cats = "INSERT INTO `categoria_curso`(`nombre_categoria_curso`,`nivel_educativo`) VALUES ('$cat','".$_POST['nivel_educativo'][$id]."');";
$script_tablas = $script_tablas.$sql_cats;
}
}
}
#fin configuracion de categoria
#año lectivo
if (isset($_POST['ano_lectivo'])){
$sql_ano = "INSERT INTO `ano_lectivo`(`nombre_ano_lectivo`, `estado`) VALUES('".$_POST['ano_lectivo']."', 'Activo');";
$script_tablas = $script_tablas.$sql_ano;
}
#fin año lectivo
$script_tablas = $script_tablas."ALTER TABLE `actividad`
  ADD CONSTRAINT `actividad_ibfk_1` FOREIGN KEY (`id_asignacion`) REFERENCES `asignacion_academica` (`id_asignacion`) ON UPDATE CASCADE,
  ADD CONSTRAINT `actividad_ibfk_2` FOREIGN KEY (`id_red`) REFERENCES `red` (`id_red`) ON UPDATE CASCADE,
  ADD CONSTRAINT `actividad_ibfk_3` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionario` (`id`) ON UPDATE CASCADE;

ALTER TABLE `acudiente_estudiante`
  ADD CONSTRAINT `acudiente_estudiante_ibfk_2` FOREIGN KEY (`id_estudiante`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `acudiente_estudiante_ibfk_3` FOREIGN KEY (`id_acudiente`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

ALTER TABLE `asignacion_academica`
  ADD CONSTRAINT `asignacion_academica_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_academica_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materia` (`id_materia`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_academica_ibfk_3` FOREIGN KEY (`id_categoria_curso`) REFERENCES `categoria_curso` (`id_categoria_curso`) ON UPDATE CASCADE,
  ADD CONSTRAINT `asignacion_academica_ibfk_4` FOREIGN KEY (`ano_lectivo`) REFERENCES `ano_lectivo` (`id_ano_lectivo`) ON UPDATE CASCADE;

ALTER TABLE `comentario`
  ADD CONSTRAINT `comentario_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comentario_ibfk_2` FOREIGN KEY (`id_entrada`) REFERENCES `entrada` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `cuestionario`
  ADD CONSTRAINT `cuestionario_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `entrada`
  ADD CONSTRAINT `entrada_ibfk_2` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `entrada_ibfk_3` FOREIGN KEY (`grupo`) REFERENCES `grupo_foro` (`id_grupo_foro`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `eventos`
  ADD CONSTRAINT `eventos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `eventos_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`),
  ADD CONSTRAINT `eventos_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

ALTER TABLE `inscripcion`
  ADD CONSTRAINT `inscripcion_ibfk_2` FOREIGN KEY (`id_asignacion`) REFERENCES `asignacion_academica` (`id_asignacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inscripcion_ibfk_3` FOREIGN KEY (`id_estudiante`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

ALTER TABLE `materia`
  ADD CONSTRAINT `materia_ibfk_1` FOREIGN KEY (`area`) REFERENCES `area` (`id_area`) ON UPDATE CASCADE,
  ADD CONSTRAINT `materia_ibfk_2` FOREIGN KEY (`area`) REFERENCES `area` (`id_area`);

ALTER TABLE `mensaje`
  ADD CONSTRAINT `mensaje_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mensaje_ibfk_2` FOREIGN KEY (`remite`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `preguntas_cuestionario`
  ADD CONSTRAINT `preguntas_cuestionario_ibfk_2` FOREIGN KEY (`tipo_pregunta`) REFERENCES `tipo_pregunta` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `preguntas_cuestionario_ibfk_3` FOREIGN KEY (`id_cuestionario`) REFERENCES `cuestionario` (`id`) ON UPDATE CASCADE;

ALTER TABLE `red`
  ADD CONSTRAINT `red_ibfk_1` FOREIGN KEY (`materia_red`) REFERENCES `materia` (`id_materia`) ON UPDATE CASCADE,
  ADD CONSTRAINT `red_ibfk_2` FOREIGN KEY (`responsable`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

ALTER TABLE `respuesta`
  ADD CONSTRAINT `respuesta_ibfk_1` FOREIGN KEY (`pregunta`) REFERENCES `cuestionario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_ibfk_4` FOREIGN KEY (`id_estudiante`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `respuesta_ibfk_5` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`) ON UPDATE CASCADE;

ALTER TABLE `seguimiento`
  ADD CONSTRAINT `seguimiento_ibfk_2` FOREIGN KEY (`id_inscripcion`) REFERENCES `inscripcion` (`id_inscripcion`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguimiento_ibfk_3` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`) ON UPDATE CASCADE;

ALTER TABLE `seguimiento_categoria_ano`
  ADD CONSTRAINT `seguimiento_categoria_ano_ibfk_1` FOREIGN KEY (`categoria`) REFERENCES `categoria_curso` (`id_categoria_curso`) ON UPDATE CASCADE,
  ADD CONSTRAINT `seguimiento_categoria_ano_ibfk_2` FOREIGN KEY (`ano_lectivo`) REFERENCES `ano_lectivo` (`id_ano_lectivo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `seguimiento_categoria_ano_ibfk_3` FOREIGN KEY (`id_estudiante`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE;

ALTER TABLE `tarea_adjunto`
  ADD CONSTRAINT `tarea_adjunto_ibfk_3` FOREIGN KEY (`id_estudiante`) REFERENCES `usuario` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `tarea_adjunto_ibfk_4` FOREIGN KEY (`id_actividad`) REFERENCES `actividad` (`id_actividad`) ON UPDATE CASCADE;";
$mysqli->multi_query($script_tablas);
$resultados=$mysqli->affected_rows;
#print($resultados);

if ($_FILES['imagen']['error']){
#print "error";	
}else{
$ruta = $_FILES["imagen"]["tmp_name"];
$resultado = importar_xls($ruta);
if($resultado){
    /*
?>
<script>alert2('Instalación Finalizada')</script>
<?php
*/
}else{
?>
<script>alert('Hubo un error en la instalación','error')</script>
<?php
}
}
if ($resultados != -1){
$id_ano_lectivo = consultar_id_ano_lectivo();
if ($_POST['cursos_de_ley']=="SI") $cursos_de_ley = true;
else $cursos_de_ley = false;
if ($cursos_de_ley){
generar_cursos_DeLey($id_ano_lectivo,false,$_POST['id_admin']);
}

if ($_POST['inscribir_estudiantes']=="SI") $inscribir_est = true;
else  $inscribir_est = true;
if ($inscribir_est){
inscribir_estudiante($id_ano_lectivo);
}
}
?>
<script>
    document.location.href="../index.php";
</script>
<?php
unset ($_COOKIE['instalacion']);
//unlink dirname(__FILE__);
exit();
}
function calcular_timezone_hora($offset){
                                $resultado = floor($offset/36);
                                $resultado = str_replace("","",$resultado);//forzar a string
                                $resultado2="";
                                for ($i=0;$i<strlen($resultado);$i++){
                                #for ($i=strlen($resultado)-1;$i>=0;$i--){
                                    $resultado2 = $resultado2.$resultado[$i];
                                    if($i==strlen($resultado)-3) $resultado2 .= ":";
                                }
                                return $resultado2;
}
?>

        <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <style>
    .sin_borde, .sin_borde:focus, .sin_borde:active, .sin_borde:hover{
        border: 1px solid transparent;
        box-shadow: none;
        cursor:text;
    }
    .fondo_transparente{
       background-color: transparent !important;
    }
    </style>
    <style type="text/css">
    .wizard {
    margin: 20px auto;
    background: #fff;
}

    .wizard .nav-tabs {
        position: relative;
        margin: 40px auto;
        margin-bottom: 0;
        border-bottom-color: #e0e0e0;
    }

    .wizard > div.wizard-inner {
        position: relative;
    }

.connecting-line {
    height: 2px;
    background: #e0e0e0;
    position: absolute;
    width: 80%;
    margin: 0 auto;
    left: 0;
    right: 0;
    top: 50%;
    z-index: 1;
}

.wizard .nav-tabs > li.active > a, .wizard .nav-tabs > li.active > a:hover, .wizard .nav-tabs > li.active > a:focus {
    color: #555555;
    cursor: default;
    border: 0;
    border-bottom-color: transparent;
}

span.round-tab {
    width: 70px;
    height: 70px;
    line-height: 70px;
    display: inline-block;
    border-radius: 100px;
    background: #fff;
    border: 2px solid #e0e0e0;
    z-index: 2;
    position: absolute;
    left: 0;
    text-align: center;
    font-size: 25px;
}
span.round-tab i{
    color:#555555;
}
.wizard li.active span.round-tab {
    background: #fff;
    border: 2px solid #5bc0de;
    
}
.wizard li.active span.round-tab i{
    color: #5bc0de;
}

span.round-tab:hover {
    color: #333;
    border: 2px solid #333;
}

.wizard .nav-tabs > li {
    width: 20%;
}

.wizard li:after {
    content: " ";
    position: absolute;
    left: 46%;
    opacity: 0;
    margin: 0 auto;
    bottom: 0px;
    border: 5px solid transparent;
    border-bottom-color: #5bc0de;
    transition: 0.1s ease-in-out;
}

.wizard li.active:after {
    content: " ";
    position: absolute;
    left: 46%;
    opacity: 1;
    margin: 0 auto;
    bottom: 0px;
    border: 10px solid transparent;
    border-bottom-color: #5bc0de;
}

.wizard .nav-tabs > li a {
    width: 70px;
    height: 70px;
    margin: 20px auto;
    border-radius: 100%;
    padding: 0;
}

    .wizard .nav-tabs > li a:hover {
        background: transparent;
    }

.wizard .tab-pane {
    position: relative;
    padding-top: 50px;
}

.wizard h3 {
    margin-top: 0;
}
.step1 .row {
    margin-bottom:10px;
}
.step_21 {
    border :1px solid #eee;
    border-radius:5px;
    padding:10px;
}
.step33 {
    border:1px solid #ccc;
    border-radius:5px;
    padding-left:10px;
    margin-bottom:10px;
}
.dropselectsec {
    width: 68%;
    padding: 6px 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
    color: #333;
    margin-left: 10px;
    outline: none;
    font-weight: normal;
}
.dropselectsec1 {
    width: 74%;
    padding: 6px 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
    color: #333;
    margin-left: 10px;
    outline: none;
    font-weight: normal;
}
.mar_ned {
    margin-bottom:10px;
}
.wdth {
    width:25%;
}
.birthdrop {
    padding: 6px 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
    color: #333;
    margin-left: 10px;
    width: 16%;
    outline: 0;
    font-weight: normal;
}
/* according menu */
#accordion-container {
    font-size:13px
}
.accordion-header {
    font-size:13px;
	background:#ebebeb;
	margin:5px 0 0;
	padding:7px 20px;
	cursor:pointer;
	color:#fff;
	font-weight:400;
	-moz-border-radius:5px;
	-webkit-border-radius:5px;
	border-radius:5px
}
.unselect_img{
	width:18px;
	-webkit-user-select: none;  
	-moz-user-select: none;     
	-ms-user-select: none;      
	user-select: none; 
}
.active-header {
	-moz-border-radius:5px 5px 0 0;
	-webkit-border-radius:5px 5px 0 0;
	border-radius:5px 5px 0 0;
	background:#F53B27;
}
.active-header:after {
	content:"\f068";
	font-family:'FontAwesome';
	float:right;
	margin:5px;
	font-weight:400
}
.inactive-header {
	background:#333;
}
.inactive-header:after {
	content:"\f067";
	font-family:'FontAwesome';
	float:right;
	margin:4px 5px;
	font-weight:400
}
.accordion-content {
	display:none;
	padding:20px;
	background:#fff;
	border:1px solid #ccc;
	border-top:0;
	-moz-border-radius:0 0 5px 5px;
	-webkit-border-radius:0 0 5px 5px;
	border-radius:0 0 5px 5px
}
.accordion-content a{
	text-decoration:none;
	color:#333;
}
.accordion-content td{
	border-bottom:1px solid #dcdcdc;
}



@media( max-width : 585px ) {

    .wizard {
        width: 90%;
        height: auto !important;
    }

    span.round-tab {
        font-size: 16px;
        width: 50px;
        height: 50px;
        line-height: 50px;
    }

    .wizard .nav-tabs > li a {
        width: 50px;
        height: 50px;
        line-height: 50px;
    }

    .wizard li.active:after {
        content: " ";
        position: absolute;
        left: 35%;
    }
}
    </style>
    
    <script src="bootstrap.min.js"></script>
    <script type="text/javascript">
    /*
        window.alert = function(){};
        var defaultCSS = document.getElementById('bootstrap-css');
        function changeCSS(css){
            if(css) $('head > link').filter(':first').replaceWith('<link rel="stylesheet" href="'+ css +'" type="text/css" />'); 
            else $('head > link').filter(':first').replaceWith(defaultCSS); 
        }
        $( document ).ready(function() {
          var iframe_height = parseInt($('html').height()); 
          window.parent.postMessage( iframe_height, 'https://bootsnipp.com');
        });
        */
        function validar_required_p1(){
            var base_url = document.getElementById('base_url');
            var base_server = document.getElementById('base_server');
            var sga_data = document.getElementById('sga_data');
            var timezone = document.getElementById('timezone');
            //alert(sga_data.value);
            if (base_url.value!="" && base_server.value!="" && sga_data.value!="" && timezone.value!=""){
                if (base_url.value!="") base_url.style="";
                if (base_server.value!="") base_server.style="";
                if (sga_data.value!="") sga_data.style="";
                if (timezone.value!="") timezone.style="";
                return true;
            }else{
                if (base_url.value=="") base_url.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (base_server.value=="") base_server.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (sga_data.value=="") sga_data.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (timezone.value=="") timezone.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                alert2('Hay campos que requieren Atención','info')
                return false;
            }
            
        }
        function validar_required_p2(){
            var servidorbd = document.getElementById('servidorbd');
            var basededatos = document.getElementById('basededatos');
            var usuariobd = document.getElementById('usuariobd');
            //alert(sga_data.value);
            if (servidorbd.value!="" && basededatos.value!="" && usuariobd.value!=""){
                if (servidorbd.value!="") servidorbd.style="";
                if (basededatos.value!="") basededatos.style="";
                if (usuariobd.value!="") usuariobd.style="";
                return true;
            }else{
                if (servidorbd.value=="") servidorbd.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (basededatos.value=="") basededatos.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (usuariobd.value=="") usuariobd.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                alert2('Hay campos que requieren Atención','info')
                return false;
            }
            
        }
        function validar_required_p3(){
            var id_admin = document.getElementById('id_admin');
            var nombre_admin = document.getElementById('nombre_admin');
            var apellido_admin = document.getElementById('apellido_admin');
            var usuario_admin = document.getElementById('usuario_admin');
            var clave_admin = document.getElementById('clave_admin');
            
            if (id_admin.value!="" && nombre_admin.value!="" && apellido_admin.value!="" && usuario_admin.value!="" && clave_admin.value!=""){
                if (id_admin.value!="") id_admin.style="";
                if (nombre_admin.value!="") nombre_admin.style="";
                if (apellido_admin.value!="") apellido_admin.style="";
                if (usuario_admin.value!="") usuario_admin.style="";
                if (clave_admin.value!="") clave_admin.style="";
                return true;
            }else{
                if (id_admin.value=="") id_admin.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (nombre_admin.value=="") nombre_admin.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (apellido_admin.value=="") apellido_admin.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (usuario_admin.value=="") usuario_admin.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (clave_admin.value=="") clave_admin.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                alert2('Hay campos que requieren Atención','info')
                return false;
            }
            
        }
        function validar_required_p4(){
            var nombre_ie = document.getElementById('nombre_ie');
            var logo_ie = document.getElementById('logo_ie');
            var formatos_no_permitidos = document.getElementById('formatos_no_permitidos');
            var tamano_maximo_adjunto = document.getElementById('tamano_maximo_adjunto');
            var periodos = document.getElementById('periodos');
            var class_periodo = document.getElementsByClassName('periodo');
            //class_periodo
            var i;
            var vacios_class_periodo = false;
            for (i=0;i<class_periodo.length;i++){
                if(class_periodo[i]=="")
                vacios_class_periodo = true;
            }
            if (vacios_class_periodo==false && nombre_ie.value!="" && formatos_no_permitidos.value!="" && tamano_maximo_adjunto.value!="" && periodos.value!=""){
                if (nombre_ie.value!="") nombre_ie.style="";
                //if (logo_ie.value!="") logo_ie.style="";
                if (formatos_no_permitidos.value!="") formatos_no_permitidos.style="";
                if (tamano_maximo_adjunto.value!="") tamano_maximo_adjunto.style="";
                if (periodos.value!="") periodos.style="";
                for (i=0;i<class_periodo.length;i++){
                    class_periodo[i].style="";
                }
                return true;
            }else{
                if (nombre_ie.value=="") nombre_ie.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                //if (logo_ie.value=="") logo_ie.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (formatos_no_permitidos.value=="") formatos_no_permitidos.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (tamano_maximo_adjunto.value=="") tamano_maximo_adjunto.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                if (periodos.value=="") periodos.style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                for (i=0;i<class_periodo.length;i++){
                    class_periodo[i].style="box-shadow: inset 0px 0px 2px 1px rgb(140, 2, 2);";
                }
                alert2('Hay campos que requieren Atención','info')
                return false;
            }
            
        }
        function listado_periodos(n,sugerir = false){
            var area_periodos = document.getElementById('area_periodos');
            var i;
            var nombre = "";
            area_periodos.innerHTML = "";
            for (i=1;i<=n;i++){
            if (sugerir) nombre = i;
            area_periodos.innerHTML+='<input class="form-control  periodo" type="text" placeholder="Periodo '+i+'" name="periodo['+i+']" value="'+nombre+'" required></label>\n';
            } 
        }
        function agregar_categoria(){
            var area_categorias = document.getElementById('area_categorias');
            var num = document.getElementById('num_categorias').value;
            var plantilla = document.createElement('div');
            plantilla.innerHTML+='<span id="cat_'+num+'"><input style="width: 45%;display: inline;" class="form-control periodo" type="text" placeholder="Nombre de Categoría" name="categoria['+num+']" required><select style="width: 45%;display: inline;" class="form-control periodo" type="text" placeholder="Nombre de Categoría" name="nivel_educativo['+num+']" required><option value="0">Seleccione Nivel Educativo</option> <option value="1">Primero</option><option value="2">Segundo</option><option value="3">Tercero</option><option value="4">Cuarto</option><option value="5">Quinto</option></select>&nbsp;<button type="button" class="btn-danger badge" style="background-color:#d9534f;" onclick="quitar_categoria(\'cat_'+num+'\');">-</button></span>';
            area_categorias.appendChild(plantilla.firstChild);
            document.getElementById('area_periodos').value++;
        }
        function quitar_categoria(id){
        var nodo = document.getElementById(id);
        nodo.parentNode.removeChild(nodo);
    }
    </script>
<?php
$url_instalar = str_replace("\\","/",dirname(__FILE__));
$url_instalar = str_replace($_SERVER['DOCUMENT_ROOT'],"",$url_instalar); 
$url_instalar = "".$_SERVER['SERVER_NAME']."/".$url_instalar;
$base_url =  str_replace("\\","/",$url_instalar);
$base_url =  str_replace("/instalar","",$url_instalar);
$base_server =  str_replace("\\","/",dirname(__FILE__));
$base_server =  str_replace("/instalar","",$base_server);
$base_server =  $base_server."/";
?>
	<div class="container">
    <div class="row">
        <center><h1>Asistente de Instalación y Configuración</h1></center>
        <input type="hidden" id="pestana" value="1">
        <input type="hidden" id="actual" value="1">
    	<section>
        <div class="wizard">
            <div class="wizard-inner">
                <div class="connecting-line"></div>
                <ul class="nav nav-tabs" role="tablist">

                    <li role="presentation" class="active">
                        <a href="#step1" data-toggle="tab" pestana = "1" aria-controls="step1" role="tab" title="Configuración de Rutas">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-folder-open"></i>
                            </span>
                        </a>
                    </li>

                    <li role="presentation" class="disabled">
                        <a href="#step2" data-toggle="tab" pestana = "2" aria-controls="step2" role="tab" title="Conexión a Base de datos">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-wrench"></i>
                            </span>
                        </a>
                    </li>
                    <li role="presentation" class="disabled">
                        <a href="#step4" data-toggle="tab" pestana = "4" aria-controls="step4" role="tab" title="Cuenta de Administración">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-user"></i>
                            </span>
                        </a>
                    </li>
                    <li role="presentation" class="disabled">
                        <a href="#step3" data-toggle="tab" pestana = "3" aria-controls="step3" role="tab" title="Datos de la Institución">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-list-alt"></i>
                            </span>
                        </a>
                    </li>
                    <li role="presentation" class="disabled">
                        <a href="#complete" data-toggle="tab" aria-controls="complete" role="tab" title="Resumen de Instalación">
                            <span class="round-tab">
                                <i class="glyphicon glyphicon-ok"></i>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
            <form id="form_registro" role="form" method="post" enctype="multipart/form-data">
                <div class="tab-content">
                    <div class="tab-pane active" role="tabpanel" id="step1">
                        <div class="step1">
                            <div class="row">
                            <div class="col-md-12">
                                <p>Bienvenido al asistente de configuración del Sistema de gestión del Aprendizaje Guagua, El primer pasó que debe hacer es verificar las rutas donde se quedará instalado este software</p>
                            </div>    
                            </div>
                            <div class="row">
                            <div class="col-md-6">
<div class="form-group">
<label>URL del software</label>
<input readonly name="base_url" id="base_url" value="<?php echo $base_url?>" class="form-control sin_borde fondo_transparente">
</div>    
                            </div>
                            <div class="col-md-6">
<div class="form-group">
    <label>Ruta del Servidor del software</label>
    <input readonly name="base_server" id="base_server" value="<?php echo $base_server?>" class="form-control sin_borde fondo_transparente">
</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
        <div class="form-group">
            <p><label>Ruta de Datos (Fotos, Adjuntos)</label></p>
            <span style="display:inline-block"></span>
            <span style="font-weight:normal;display:inline;color:gray;"><?php echo $base_server ?></span>
            <input style="font-weight:normal;display:inline;color:gray;" name="sga_data" id="sga_data" value="sga-data/" class="sin_borde fondo_transparente">
        </div>
                            </div>
                             <div class="col-md-6">
                             <label>Zona horaria</label>
                             <?php
                             echo '<select name="timezone" id="timezone" class="form-control" onchange="document.getElementById(\'time_zone_offset\').value = this.options[this.selectedIndex].getAttribute(\'offset\')">';
                             foreach(timezone_abbreviations_list() as $abbr => $timezone){
        foreach($timezone as $val){
                if(isset($val['timezone_id'])){
                        echo "<option ";
                        if($val['timezone_id']=="America/Bogota"){
                        echo "selected";
                        #print_r($timezone);
                        }
                        echo " offset='".calcular_timezone_hora($val['offset'])."' value='".$val['timezone_id']."'>".calcular_timezone_hora($val['offset'])."   ".$val['timezone_id']."</option>";
                }
        }
}
                        echo '</select><input name="time_zone_offset" id="time_zone_offset" value="-5:00" type="hidden">';
                        
                             ?>
                             <p>Advertencia: La configurará de Zona Horaria se utiliza para el registro de fechas y horas</p>
                             </div>
                        </div>
                        </div>
                        <ul class="list-inline pull-right">
                            <li><button type="button" onmousedown="return validar_required_p1();" class="btn btn-primary next-step">Siguiente</button></li>
                        </ul>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="step2">
                        <div class="step2">
                            <div class="step_21">
                                <div class="row">
                                    <div class="col-md-12">
                                   <p>Por favor Ingrese la configuración para la Base de datos</p>
                                    </div>
                                </div>
                                 <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
            <label>Servidor de Base de datos</label>
            <input name="servidorbd" id="servidorbd" placeholder="Ejemplo: localhost" value="localhost" class="form-control" required>
        </div>
        </div><div class="col-md-6">
        <div class="form-group">
            <label>Nombre de la Base de datos</label>
            <input name="basededatos" id="basededatos" placeholder="Ejemplo: guagua" value="guagua" class="form-control" required>
        </div>
        </div>
        </div><div class="row">
            
        <div class="col-md-6">
        <div class="form-group">
            <label>Nombre de usuario de Base de datos</label>
            <input name="usuariobd" id="usuariobd" placeholder="Ejemplo: root" value="" class="form-control" required>
        </div>
        </div><div class="col-md-6">
        <div class="form-group">
            <label>Clave de usuario de Base de datos</label>
            <input type="password" name="clavebd" id="clavebd" placeholder="Escriba su clave" value="" class="form-control">
        </div>
                                    </div>
                                </div>
                                <div class="row"><a class="btn btn-info" href="usuarios.php" target="_blank">Creación de base de datos y usuarios</a>
        </div>
                            </div>
                            <div class="step-22">
                            
                            </div>
                        </div>
                        <ul class="list-inline pull-right">
                            <li><button type="button" class="btn btn-default prev-step">Anterior</button></li>
                            <li><button type="button" onmousedown="return validar_required_p2();" class="btn btn-primary next-step">Siguiente</button></li>
                        </ul>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="step3">
                        <div class="step33">
                            <center><h2>Datos de la Institución</h2></center>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
            <label>Nombre de la Institución / Entidad</label>
            <input name="nombre_ie" id="nombre_ie" placeholder="Ejemplo: Institución Educativa Municipal ..." value="" class="form-control" required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
            <label>Logotipo, escudo o imagen que represente la Institución o Entidad</label>
            <img id="img_logo_ie" src="" height="80">
            <input name="logo_ie" onchange="mostrarImagen(this);setTimeout(function(){
        document.getElementById('resumen_img_logo_ie').src = document.getElementById('img_logo_ie').src;
        }, 5000);" id="logo_ie" type="file" value="" class="form-control">
        </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
            <label>Portada o Banner que represente la Institución o Entidad</label>
            <img id="img_banner_ie" src="" height="80">
            <input name="banner_ie" onchange="mostrarImagen(this);setTimeout(function(){
        document.getElementById('resumen_img_banner_ie').src = document.getElementById('img_banner_ie').src;
        }, 5000);" id="banner_ie" type="file" value="" class="form-control">
        </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
            <label>Formatos no permitidos, Escriba los formatos separados por una , (coma):</label>
            <input name="formatos_no_permitidos" id="formatos_no_permitidos" placeholder="Ejemplo: exe,bin,zdf ..." value="" class="form-control">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
            <label>Tamaño Máximo de archivo adjunto(Kb):</label>
            <input type="number" name="tamano_maximo_adjunto" id="tamano_maximo_adjunto" placeholder="Ejemplo: 2000000 ..." value="" class="form-control" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
        <div class="form-group">
                <label for="ano_lectivo">Año Lectivo:</label>
                <input class="form-control" name="ano_lectivo" id="ano_lectivo" type="text" value="<?php echo date("Y"); ?>">
                <!---->
                 <input name="cursos_de_ley" type="hidden" value="NO" >
                  <label title="Son aquellas asignaturas correspondientes a las areas obligatorias de acuerdo a la ley 115: <?php /*
                  $datos_materia = consultar_materia_obligatorias();
                  $datos_materia1 = array();
                  foreach ($datos_materia as $id => $materia)
                  $datos_materia1[] = $materia[1];
                  echo implode(", ",$datos_materia1) 
                  */
                  ?>"><input name="cursos_de_ley" type="checkbox" value="SI" checked>
                Crear cursos automaticamente </label>
                <br>
                <!---->
                <label>Permitir el auto registro:</label><br>
        <label><input type="checkbox" name="autoregistro[]" id="autoregistro_docente" value="docente">&nbsp;Docente</label>
        <label><input type="checkbox" name="autoregistro[]" id="autoregistro_estudiante" value="estudiante">&nbsp;Estudiante</label>
        <label><input type="checkbox" name="autoregistro[]" id="autoregistro_acudiente" value="acudiente">&nbsp;Acudiente</label>
                
        </div>
        </div>
        <div class="col-md-3">
        <div class="form-group">
                <label>Periodos:</label>
                <input name="periodos" title="Haga doble click sobre esta caja ce texto para sugerir nombres" ondblclick="listado_periodos(this.value,true)" onchange="listado_periodos(this.value)" id="periodos" type="number" value="4" class="form-control">
            <span id="area_periodos">
                <input class="form-control periodo" type="text" placeholder="Periodo 1" name="periodo[1]" required></label>
                <input class="form-control periodo" type="text" placeholder="Periodo 2" name="periodo[2]" required></label>
                <input class="form-control periodo" type="text" placeholder="Periodo 3" name="periodo[3]" required></label>
                <input class="form-control periodo" type="text" placeholder="Periodo 4" name="periodo[4]" required></label>
            </span>
        </div>
        </div>
        <div class="col-md-6">
        <div class="form-group">    
    <script type="text/javascript" >
    function importar_usuarios(obj){
    var estado = obj.value;
    console.log(estado);
    if(estado=='si') {
   document.getElementById("no").style.display = 'none';
   document.getElementById("si").style.display = '';

}
        if(estado=='no') {
    document.getElementById("no").style.display = "";
   document.getElementById("si").style.display = "none";
}
        }
        
    </script>
    
    <input type="radio" id="btn_chk_importar_usuarios" name="importar" onclick="importar_usuarios(this);" checked value="si" >        
<label for="btn_chk_importar_usuarios">Importar Usuarios</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="radio" id="btn_chk_crear_categorias" name="importar" onclick="importar_usuarios(this);" value="no" >        
        <label for="btn_chk_crear_categorias">Crear Categorias</label>
    <br/>
            <span id="no" style="display:none">
            
        <label for="categoria">Categorias</label><br>
                <input type="hidden" id="num_categorias" value="1">
                <span id="area_categorias">
                <?php /*
                <span id="cat_1"><input style="width: 45%;display: inline;" class="form-control periodo" type="text" placeholder="Nombre de Categoría" name="categoria[1]" required>
                <select style="width: 45%;display: inline;" class="form-control periodo" type="text" placeholder="Nombre de Categoría" name="nivel_educativo[1]" required><option value="0">Seleccione Nivel Educativo</option> <option value="1">Primero</option><option value="2">Segundo</option><option value="3">Tercero</option><option value="4">Cuarto</option><option value="5">Quinto</option></select>&nbsp;<button type="button" class="btn-danger badge" style="background-color:#d9534f;" onclick="quitar_categoria('cat_1');">-</button></span>
                */ ?>
                </span><button type="button" class="badge btn-success" style="background-color:#5cb85c;" onclick="agregar_categoria();">+</button>
        
        </span>
        
        <span id="si">
           <!--form action="<?php echo SGA_USUARIO_URL?>/importar_xls.php" method="POST" ENCTYPE="multipart/form-data" target="_blank"-->
<a class="btn btn-success" href="../usuario/documentos/importar_usuario_nuevo.xlsx" >Descargar Plantilla en Blanco</a>
<a class="btn btn-info" href="../usuario/documentos/importar_usuario_ejemplo.xlsx" >Descargar Plantilla con Ejemplo</a>
<br>
<label for=""><i>Formato: <stong>.xls o .xlsx</stong></i></label>
<p>
    <input name="imagen" type="file" id="bnt_file"/>
  </p>
  <!---->
                 <input name="inscribir_estudiantes" type="hidden" value="NO" >
                  <label title="Requiere la existencia de cursos en el sistema"><input name="inscribir_estudiantes" type="checkbox" value="SI" checked>
                Inscribir Estudiantes</label>
                <br>
                
                <!---->
  <!--p>
      <input type="hidden" name="submit" value="">
    <button class="btn btn-primary" id="btn_reporte" >Subir</button>
  </p-->
<!--/form-->
            <!-- Espacio para importar  -->
        </span>
        </div>
        </div>
        </div>
        </div>
                        <ul class="list-inline pull-right">
                            <li><button type="button" class="btn btn-default prev-step">Anterior</button></li>
                            <li><button type="button" onmousedown="return validar_required_p4();" class="btn btn-primary btn-info-full next-step">Siguiente</button></li>
                        </ul>
                    </div>
                
                    <div class="tab-pane" role="tabpanel" id="step4">
                        <div class="step44">
                            <div class="row">
        <div class="col-md-4">
  <div class="form-group">
            <label>Identificación del Administrador</label>
            <input type="number" name="id_admin" id="id_admin" placeholder="Ejemplo: 1085123456" value="" class="form-control" required>
        </div>
        
        
        </div>
        <div class="col-md-4">
            <div class="form-group">
            <label>Nombre de Administrador</label>
            <input name="nombre_admin" id="nombre_admin" placeholder="Ejemplo: Juan José" value="" class="form-control" required>
        </div>
        
        </div>
        <div class="col-md-4">
            <div class="form-group">
            <label>Apellido de Administrador</label>
            <input name="apellido_admin" id="apellido_admin" placeholder="Ejemplo: Rosero" value="" class="form-control" required>
        </div>
        </div>
                        </div>
                        <div class="row">
        <div class="col-md-6">
  <div class="form-group">
            <label>Usuario</label>
            <input name="usuario_admin" id="usuario_admin" placeholder="Ejemplo: Rosero" value="" class="form-control" required>
        </div>
        
        </div>
        <div class="col-md-6">
            <div class="form-group">
            <label>Clave</label>
            <input type="password" name="clave_admin" id="clave_admin" placeholder="Ejemplo: Rosero" value="" class="form-control" required>
        </div>
        </div>
                        </div>
                          
                        </div>
                        <ul class="list-inline pull-right">
                            <li><button type="button" class="btn btn-default prev-step">Anterior</button></li>
                            <li><button type="button"  onmousedown="return validar_required_p3();" class="btn btn-primary btn-info-full next-step">Siguiente</button></li>
                        </ul>
                    </div>
                    <div class="tab-pane" role="tabpanel" id="complete">
                        <div class="step55">
                            <div id="resumen_instalacion">
                                <style>
                                 @media print{
                                    .form-control{
                                    width:100% !important;
                                    }
                                    .col-md-3{
                                        float:left;
                                    }
                                }
                                </style>
                            <center><h2>Resumen de Instalación</h2></center>
                            <div class="row">
                            <div class="col-md-3"><h3>Rutas</h3>

<label for="resumen_base_url">URL del software</label>
<span class="form-control sin_borde" id="resumen_base_url"><?php echo $base_url?></span>

<label for="resumen_base_server">Ruta del Servidor del software</label>
<span id="resumen_base_server" class="form-control sin_borde"><?php echo $base_server?></span>
<label for="resumen_sga_data">Ruta de Datos (Fotos, Adjuntos)</label>
<?php echo $base_server ?><span id="resumen_sga_data" class="form-control sin_borde">/sga-data/</span>

<label for="resumen_timezone">Zona horaria</label>
<span id="resumen_timezone" class="form-control sin_borde">-5:00  America/Bogota</span>

                            </div>
                            <div class="col-md-3"><h3>Base de datos</h3>
<label for="resumen_servidorbd">Servidor de Base de datos</label>
<span class="form-control sin_borde" id="resumen_servidorbd">localhost</span>

<label for="resumen_basededatos">Nombre de la Base de datos</label>
<span id="resumen_basededatos" class="form-control sin_borde">guagua</span>

<label for="resumen_usuariobd">Nombre de usuario de Base de datos</label>
<span id="resumen_usuariobd" class="form-control sin_borde">guagua</span>

<label for="resumen_clavebd">Clave de usuario de Base de datos</label>
<span id="resumen_clavebd" class="form-control sin_borde"></span>

                            </div>
                            <div class="col-md-3"><h3>Usuario</h3>
<label for="resumen_id_admin">Identificación del Administrador</label>
<span class="form-control sin_borde" id="resumen_id_admin">localhost</span>

<label for="resumen_nombre_admin">Nombre de Administrador</label>
<span id="resumen_nombre_admin" class="form-control sin_borde"></span>

<label for="resumen_apellido_admin">Apellido de Administrador</label>
<span id="resumen_apellido_admin" class="form-control sin_borde"></span>

<label for="resumen_usuario_admin">Usuario</label>
<span id="resumen_usuario_admin" class="form-control sin_borde"></span>

<label for="resumen_clave_admin">Clave</label>
<span id="resumen_clave_admin" class="form-control sin_borde"></span>
                            </div>
                            <div class="col-md-3"><h3>Datos de Institución</h3>
<label for="resumen_nombre_ie">Nombre de la Institución / Entidad</label>
<span class="form-control sin_borde" id="resumen_nombre_ie"></span>

<label for="resumen_logo_ie">Logotipo, escudo o imagen que represente la Institución o Entidad</label>
<img id="resumen_img_logo_ie"  height="80">
<span id="resumen_logo_ie" class="form-control sin_borde"></span>

<label for="resumen_formatos_no_permitidos">Formatos no permitidos, Escriba los formatos separados por una , (coma):</label>
<span id="resumen_formatos_no_permitidos" class="form-control sin_borde"></span>

<label for="resumen_tamano_maximo_adjunto">Tamaño Máximo de archivo adjunto(Kb):</label>
<span id="resumen_tamano_maximo_adjunto" class="form-control sin_borde"></span>

<label for="resumen_periodos">Periodos:</label>
<span id="resumen_periodos" class="form-control sin_borde">4</span>
                            </div>
                            </div>
                            </div>
                            <hr>
<div class="row">
    <div class="col-md-12"> 
        <div class="form-group" align="center">
            <!-- Trigger the modal with a button -->

<button type="button" class="btn btn-info" style="background:purple; border-color:transparent" data-toggle="modal" data-target="#myModal">Requisitos del servidor</button>
<form action="" method="post">
        <a  class="btn btn-info" href="javascript:imprSelec('resumen_instalacion')">Imprimir</a>
        <button class="btn btn-primary" type="submit">Instalar</button>
      </form>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Requisitos del servidor</h4>
      </div>
      <div class="modal-body">
         <div class="row">
    <div class="col-md-12">
        Para instalar este software su servidor debe contar con el servicio de Apache Versión v 2.4.9 o superior, un servicio MySQL v 5.6.17 o superior. Se deben tener habilitadas las siguientes funciones:
        <?php
            $funciones[] = 'file_get_contents';
            $funciones[] = 'file_put_contents';
            $funciones[] = 'copy';
            $funciones[] = 'unlink';
            $funciones[] = 'glob';
            $funciones[] = 'json_encode';
            $funciones[] = 'json_decode';
            $funciones[] = 'ob_start';
            $funciones[] = 'ob_clean';
            $funciones[] = 'ob_get_clean';
            $funciones[] = 'ob_get_contents';
            $funciones[] = 'dirname';
            $funciones[] = 'count';
            $funciones[] = 'session_start';
            $funciones[] = 'explode';
            $funciones[] = 'implode';
            $funciones[] = 'header';
            $funciones[] = 'date';
            $funciones[] = 'simplexml_load_file';
            $funciones[] = 'str_replace';
            $funciones[] = 'in_array';
            $funciones[] = 'trim';
            $funciones[] = 'sha1';
            $funciones[] = 'extract';
            $funciones[] = 'end';
            $funciones[] = 'is_dir';
            $funciones[] = 'rmdir';
            $funciones[] = 'array_walk';
            $funciones[] = 'array_keys';
            $funciones[] = 'array_values';
            $funciones[] = 'function_exists';
            $funciones[] = 'date_default_timezone_set';
            $funciones[] = 'stripslashes';
            $funciones[] = 'addslashes';
            $funciones[] = 'sort';
            $funciones[] = 'strtotime';
            $funciones[] = 'strlen';
            $funciones[] = 'mb_strtolower';
            $funciones[] = 'mail';
            //$funciones[] = 'probando';
            //echo implode (", ",$funciones);
            ?>
            <div style="overflow-y:auto; height:300px;">
            <?php
            foreach ($funciones as $funcion){
                echo '<span style="float:left;margin:10px;';
                if (function_exists ($funcion))
                echo 'color:green" ';
                else 
                echo 'color:red" title="La función '.$funcion.' no se encuentra habilitada"';
                echo '>'.$funcion.'</span>';
            }
            ?>
            </div>
    </p>
    
        <p>
                <?php
                $metodosPHP[] = 'unset';
                $metodosPHP[] = 'require';
                $metodosPHP[] = 'require_once';
                $metodosPHP[] = 'include';
                $metodosPHP[] = 'include_once';
                $metodosPHP[] = 'empty';
            
                $librerias[] = 'ZipArchive';
                $librerias[] = 'DateTime';
                ?>
        </p>

    </div>
    </div>
      </div>
      <div class="modal-footer">
        <p style="float:left;text-align:left">Convenciones<br>
            <span style="color:green">Verde: La función no presenta errores</span>
            <br>
            <span style="color:red">Rojo: Error con la función</span>
        </p>
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
   
        
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </form>
        </div>
    </section>
   </div>
</div>
	<script type="text/javascript">
	$(document).ready(function () {
    //Initialize tooltips
    $('.nav-tabs > li a[title]').tooltip();
    
    //Wizard
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var $target = $(e.target);
        if ($target.parent().hasClass('disabled')) {
            return false;
        }
    });

    $(".next-step").click(function (e) {

        var $active = $('.wizard .nav-tabs li.active');
        $active.next().removeClass('disabled');
        nextTab($active);

    });
    $(".prev-step").click(function (e) {

        var $active = $('.wizard .nav-tabs li.active');
        prevTab($active);

    });
});

function nextTab(elem) {
    var siguiente = $(elem).next().find('a[data-toggle="tab"]');
    var active = document.querySelector('.wizard .nav-tabs li.active a');
    pestana.value = active.getAttribute('pestana');
    pestana.value = siguiente.attr('pestana');
    siguiente.click();
}
function prevTab(elem) {
    var anterior = $(elem).prev().find('a[data-toggle="tab"]');
    var active = document.querySelector('.wizard .nav-tabs li.active a');
    pestana.value = active.getAttribute('pestana');
    pestana.value = anterior.attr('pestana');
    anterior.click();
}

//according menu

$(document).ready(function()
{
    //Add Inactive Class To All Accordion Headers
    $('.accordion-header').toggleClass('inactive-header');
	
	//Set The Accordion Content Width
	var contentwidth = $('.accordion-header').width();
	$('.accordion-content').css({});
	
	//Open The First Accordion Section When Page Loads
	$('.accordion-header').first().toggleClass('active-header').toggleClass('inactive-header');
	$('.accordion-content').first().slideDown().toggleClass('open-content');
	
	// The Accordion Effect
	$('.accordion-header').click(function () {
		if($(this).is('.inactive-header')) {
			$('.active-header').toggleClass('active-header').toggleClass('inactive-header').next().slideToggle().toggleClass('open-content');
			$(this).toggleClass('active-header').toggleClass('inactive-header');
			$(this).next().slideToggle().toggleClass('open-content');
		}
		
		else {
			$(this).toggleClass('active-header').toggleClass('inactive-header');
			$(this).next().slideToggle().toggleClass('open-content');
		}
	});
	
	return false;
});
	</script>
<?php
$contenido = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="es">
    <meta charset="UTF-8">
    <meta name="" content="">
    <meta name="description" content="Sistema de Gestión de Aprendizaje">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>Guagua - Sistema de Gestión de Aprendizaje</title>
	<link rel="shortcut icon" href="../comun/img/logo.png" type="image/x-icon" /><!--Logo de la IEM-->
    <link rel="stylesheet" href="../comun/css/bootstrap.min.css">
    <link rel="stylesheet" href="../comun/css/estilos_guagua.css">
    <script src="../comun/js/jquery-2.2.4.min.js"></script>
    <script src="../comun/js/bootstrap.min.js"></script>
    <script src="../comun/js/funciones.js"></script>
    <script src="../comun/js/sweetalert.min.js"></script>
    <link rel="stylesheet" href="../comun/css/sweetalert.css"!>
    <link href="../comun/css/jquery-ui.css" rel="stylesheet">
    <script src="../comun/js/jquery.js"></script>
    <script src="../comun/js/jquery-ui.js"></script>
    <script src="../comun/js/i18n/datepicker-es.js"></script>
    <script src="../comun/js/sweetalert.multi.js"></script>
    <script src="../comun/js/jquery.steps.js" type="text/javascript"></script>
    <script src="../comun/js/svgcheckbx.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="../comun/img/png/icon-sga.php">
    <link href="../comun/css/jquery.contextMenu.css" rel="stylesheet" type="text/css" />
    <script src="../comun/js/jquery.contextMenu.js" type="text/javascript"></script>
    <link href="../comun/css/checkbox_animado.css" rel="stylesheet" type="text/css" /><link href="../comun/css/colores.css.php" rel="stylesheet" type="text/css" />
    
</head>
<body>
    <div class="container">
         <div class="page-header">
           	<main>
           	    <section>
           	        <?php if (isset($contenido)) echo $contenido; ?>
           	    </section>
            </main>
        </div>
    </div>
    <style>
    #sgafooter{
        width:100%;
        z-index:100;
        background-color:rgba(255, 255, 255, 0.72);
        bottom:0;
        position:fixed;
        right:0px;
        text-align:right;
        border-top:#00aceb 1px solid;
    }
    #sgafooter div{
        margin-right:50px;
        text-align:center;
        
    }
    @media print{
        #sgafooter{
        position:absolute;
        }
    }
    body{
        overflow-x:hidden;
    }
</style>
<script>
form = document.querySelectorAll('form');
for (i=0;i<form.length;i++){
required_en_formulario(form[i].id,"red","*");
password_en_formulario(form[i].id);
};
inputs = document.querySelectorAll('input, select');
for (i=0;i<inputs.length;i++){
inputs[i].addEventListener("change", function(){
    var obj = this.id;
    if (obj)
    var obj2 = document.getElementById('resumen_'+obj);
    if (obj2){
        if (this.type=="file"){
            var contenido = this.value;
            obj2.innerHTML = contenido.replace('C:\\fakepath\\',"");
        }else{
            obj2.innerHTML = this.value;
        }
    }
}, false); 
}
</script>
<footer id="sgafooter">
<div>Guagua - <span style="floar:right; margin-right:5px">Desarrollado por: <a href="mailto:mcems7@gmail.com">Manuel Vicente Cerón Solarte</a>
                 y <a href="mailto:andres.paz1991@gmail.com">Hugo Andrés Paz Burbano</a></span></div>
</footer>
</body>
</html>