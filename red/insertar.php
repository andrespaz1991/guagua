<?php
require_once("../comun/config.php");
require_once("../comun/funciones.php");
$carpeta_destino = READFILE_SERVER."/banco_red/";
$carpeta_destino_url = SGA_URL."/comun/sga-data/banco_red/";
$tituloRed=$_POST['titulo_red'];
function scrom($carpeta_destino="",$carpeta_destino_url=""){
if($carpeta_destino<>""){
    @session_start();
    @mkdir($carpeta_destino);
    mkdir($carpeta_destino.'/'.$_SESSION['id_usuario'].$_POST['titulo_red']);
      $zip = new ZipArchive;
      $zip->open($ruta_tmp_archivo);
      $zip->extractTo($carpeta_destino.'/'.$_SESSION['id_usuario'].$tituloRed);
      $zip->close();
      $ruta_destino2 = $carpeta_destino_url.'/'.$_SESSION['id_usuario'].$tituloRed;    
} 
}
echo insertar_red($ruta_destino,$extensión_archivo);
?>