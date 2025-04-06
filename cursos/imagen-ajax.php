<?php

if (isset($_FILES["file"]))
{
    require_once '../comun/funciones.php';
    $extensiones= formatos();
    $tamaño_maximo = tamaño_maximo();
    @session_start();
    $nombre = $_SESSION['id_usuario']; //identificacion del usuario
    $directorio = $_POST['id_act_adj']; //id de actividad
    $actividad = $_POST['id_act_adj'];
    $file = $_FILES["file"];
    $extension= pathinfo($file["name"],PATHINFO_EXTENSION);
    $tipo = $file["type"];
    $ruta_provisional = $file["tmp_name"];
    $size = $file["size"];
    $dimensiones = getimagesize($ruta_provisional);
    $width = $dimensiones[0];
    $height = $dimensiones[1];
    $carpeta = "imagenes/".$actividad;
if (file_exists($carpeta)) {

} else{
        mkdir($carpeta,0777);
}
    if (in_array($tipo,$extensiones))
    {
      echo "El archivo no es una imagen"; 
    }
    else if (filesize($_FILES['file']['tmp_name']) > $tamaño_maximo)
    {
        $tamaño_maximomb = $tamaño_maximo/1000000; 
      echo 'El tamaño máximo permitido es un '.$tamaño_maximomb.' MB';
    }
    else
    {
       
    
#    echo    $src = $nombre.$nombre;
 #   exit();
 $src= $carpeta.'/'.$nombre.'.'.$extension;
        move_uploaded_file($ruta_provisional,$src);
        #echo "<img src='$src'>";
        require '../comun/conexion.php';
   $sql='INSERT INTO `tarea_adjunto`( `id_actividad`, `id_estudiante`, `fechayhora_adjunto`, `observacion`,adjunto) VALUES ("'.$actividad.'","'.$nombre.'","'.date('Y-m-d H:i:s').'","'.$_POST['observaciones'].'","'.$src.'")' ;   
  if ($consulta = $mysqli -> query ($sql)){
    echo "1";
    }else{
    echo "0";//print_r($_POST)."\n<br>".$sql;
    }
        
    }
}
?>