<?php
error_reporting(E_ALL);

require_once __DIR__ . "/funciones.php";
require_once __DIR__ . "/config.php";
require_once __DIR__ . '/academico.php';

// Adaptador de las páginas existentes; la factoría no incorpora permisos ni consultas de negocio.
$mysqli = guagua_db_request();

// Datos institución
require_once __DIR__ . '/context.php';
$row_ie = guagua_institution_context($mysqli,(int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0));
if ($row_ie) {
    if (!defined('NOMBRE_INSTITUCION') && isset($row_ie['nombre_institucion'])) define("NOMBRE_INSTITUCION", $row_ie['nombre_institucion']);
    if (!defined('LOGO_INSTITUCION') && isset($row_ie['logo_institucion'])) define("LOGO_INSTITUCION", $row_ie['logo_institucion']);
    if (!defined('BANNER_INSTITUCION') && isset($row_ie['BANNER_INSTITUCION'])) define("BANNER_INSTITUCION", $row_ie['BANNER_INSTITUCION']);
}
if (!defined('NOMBRE_INSTITUCION')) define('NOMBRE_INSTITUCION', '');
if (!defined('LOGO_INSTITUCION')) define('LOGO_INSTITUCION', '');
if (!defined('BANNER_INSTITUCION')) define('BANNER_INSTITUCION', '');

require_once __DIR__ . "/permisos.php"; // también aquí
?>
