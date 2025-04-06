<?php
require(dirname(__FILE__)."/../clases/Backup_Database.Class.php");
$Backup_Database=new Backup_Database();
$Backup_Database->copia_bd("guagua",TRUE,1);
if(isset($_GET['web'])){
    header('Location:../index.php');
}
?>