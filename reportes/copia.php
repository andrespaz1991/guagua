<?php
// El cron conserva su entrada CLI. Desde la web se exige administración y CSRF.
if (PHP_SAPI !== 'cli') {
    require_once __DIR__ . '/../comun/seguridad.php';
    guagua_exigir_rol(['admin', 'directivo']);
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        header('Allow: POST'); guagua_error_acceso(405, 'Use POST para crear una copia.');
    }
    guagua_exigir_csrf();
}
require(dirname(__FILE__)."/../clases/Backup_Database.Class.php");
$Backup_Database=new Backup_Database();
$Backup_Database->copia_bd(null,TRUE,1);
if(isset($_GET['web'])){
    header('Location:../index.php');
}
?>
