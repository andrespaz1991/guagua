<?php
require '../comun/autoload.php';
$filename = "mi_archivo.zip";
$zip = new ZipArchive();
$zip->open($filename, ZipArchive::CREATE);
$zip->addFile("horario.php","horario.php");
$zip->close();
header("Content-disposition: attachment; filename=mi_archivo.zip");
header("Content-type: MIME");
readfile("mi_archivo.zip");
unlink("mi_archivo.zip");
agregar_zip("carpeta", "a.zip");
/*
$Backup_Database=new Backup_Database();
$Backup_Database->copia_bd("guagua",false,1);
*/


function agregar_zip($dir, $zip){
//verificamos si $dir es un directorio
   if (is_dir($dir)) {
   //abrimos el directorio y lo asignamos a $da
      if ($da = opendir($dir)) {          
      //leemos del directorio hasta que termine
         while (($archivo = readdir($da))!== false) {  
        //Si dentro del directorio hallamos otro directorio 
        //llamamos recursivamente esta función
        //para que verifique dentro del nuevo directorio
           if (is_dir($dir . $archivo) && $archivo!="." && $archivo!=".."){
               agregar_zip($dir.$archivo . "/", $zip);  
    
           }elseif(is_file($dir.$archivo) && $archivo!="." && $archivo!=".."){
               //echo "Agregando archivo: $dir$archivo                                   
               $zip->addFile($dir.$archivo, $dir.$archivo);                    
           }            
        }
      //cerramos el directorio abierto en el momento
      closedir($da);
     }
  }      
}

?>