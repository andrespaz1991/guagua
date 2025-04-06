<?php
/*
    Tomar una fotografía y guardarla en un archivo
    @date 2017-11-22
    @author parzibyte
    @web parzibyte.me/blog
*/
    
    $baseFromJavascript=$_POST['foo'];
$base_to_php = explode(',', $baseFromJavascript);
// y usar base64_decode para obtener la información binaria de la imagen
$data = base64_decode($base_to_php[1]);// BBBFBfj42Pj4....

// Proporciona una locación a la nueva imagen (con el nombre y formato especifico)
$filepath = '../sga-data/foto/'.$_POST['lorem'].".png"; // or image.jpg

// Finalmente guarda la imágen en el directorio especificado y con la informacion dada
file_put_contents($filepath, $data);
?>
