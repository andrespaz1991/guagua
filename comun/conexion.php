<?php
error_reporting(E_ALL);

require_once __DIR__ . "/funciones.php";
require_once __DIR__ . "/config.php";

$mysqli = new mysqli(SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);

if (mysqli_connect_errno()) {
    echo "error " . mysqli_connect_errno();
} else {
    mysqli_set_charset($mysqli, 'utf8');
}

ini_set('date.timezone', TIME_ZONE);
date_default_timezone_set(TIME_ZONE);
$mysqli->query("SET time_zone = '".TIME_ZONE_OFFSET."'");

// Datos institución
$sql_ie = "SELECT * FROM `institucion_educativa` WHERE id_institucion_educativa ='1'"; 
if ($consulta_ie = $mysqli->query($sql_ie)) {
    $row_ie = $consulta_ie->fetch_assoc();
    if (!defined('NOMBRE_INSTITUCION') && isset($row_ie['nombre_institucion'])) define("NOMBRE_INSTITUCION", $row_ie['nombre_institucion']);
    if (!defined('LOGO_INSTITUCION') && isset($row_ie['logo_institucion'])) define("LOGO_INSTITUCION", $row_ie['logo_institucion']);
    if (!defined('BANNER_INSTITUCION') && isset($row_ie['BANNER_INSTITUCION'])) define("BANNER_INSTITUCION", $row_ie['BANNER_INSTITUCION']);
}

require_once __DIR__ . "/permisos.php"; // también aquí
?>
