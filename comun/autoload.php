<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
@session_start();
require_once 'config.php';
require_once 'funciones.php';
spl_autoload_register(function ($clase) {
#include $_SERVER['DOCUMENT_ROOT'].'/clases/'.ucwords($clase).'.Class.php';
include $_SERVER['DOCUMENT_ROOT'].'/'.'guagua/'.'Clases/'.ucwords($clase).'.Class.php';
});

?>