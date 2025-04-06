<?php
//si no existe la variable de session y no esta  dentro e las excepciones entonces entonces debe ir al login
//si existe variable de session y al módulo al cual desea acceder tiene permisos para ese usuario
//

$roles = array("admin","docente","estudiante","acudiente","sinrol");
$modulos = array("cursos","foros","cuestionario","eventos","mensajes","red","reportes","usuario","comun","index.php");
$archivos = array("login.php","iconos.php");
$usuarios = array("1","1080232001");
$ruta = $_SERVER['SCRIPT_FILENAME'];#echo "Ruta: ".$ruta."<br>";
$ruta = str_replace(SGA_SERVER,"",$ruta);
$carpeta = explode("/",$ruta);#echo "Modulo: ".$carpeta[1];
$moduloactual = $carpeta[1];

$permisos[]=array($modulos[0]=>$roles[0]); #admin en cursos
$permisos[]=array($modulos[1]=>$roles[0]); #admin en foros
$permisos[]=array($modulos[2]=>$roles[0]); #admin en cuestionario
$permisos[]=array($modulos[3]=>$roles[0]); #admin en eventos
$permisos[]=array($modulos[4]=>$roles[0]); #admin en mensajes
$permisos[]=array($modulos[5]=>$roles[0]); #admin en red
$permisos[]=array($modulos[6]=>$roles[0]); #admin en reportes
$permisos[]=array($modulos[7]=>$roles[0]); #admin en usuarios
$permisos[]=array($modulos[8]=>$roles[0]); #admin en comun
$permisos[]=array($modulos[9]=>$roles[0]); #admin en index.php

$permisos[]=array($modulos[0]=>$roles[1]); #docente en cursos
$permisos[]=array($modulos[1]=>$roles[1]); #docente en foros
$permisos[]=array($modulos[2]=>$roles[1]); #docente en cuestionario
$permisos[]=array($modulos[3]=>$roles[1]); #docente en eventos
$permisos[]=array($modulos[4]=>$roles[1]); #docente en mensajes
$permisos[]=array($modulos[5]=>$roles[1]); #docente en red
$permisos[]=array($modulos[6]=>$roles[1]); #docente en reportes
$permisos[]=array($modulos[7]=>$roles[1]); #docente en usuarios
$permisos[]=array($modulos[8]=>$roles[1]); #docente en comun
$permisos[]=array($modulos[9]=>$roles[1]); #docente en index.php

$permisos[]=array($modulos[0]=>$roles[2]); #acudiente en cursos
$permisos[]=array($modulos[1]=>$roles[2]); #acudiente en foros
$permisos[]=array($modulos[2]=>$roles[2]); #acudiente en cuestionario
$permisos[]=array($modulos[3]=>$roles[2]); #acudiente en eventos
$permisos[]=array($modulos[4]=>$roles[2]); #acudiente en mensajes
$permisos[]=array($modulos[5]=>$roles[2]); #acudiente en red
$permisos[]=array($modulos[6]=>$roles[2]); #acudiente en reportes
$permisos[]=array($modulos[7]=>$roles[2]); #acudiente en usuarios
$permisos[]=array($modulos[8]=>$roles[2]); #acudiente en comun
$permisos[]=array($modulos[9]=>$roles[2]); #acudiente en index.php

$permisos[]=array($modulos[0]=>$roles[3]); #estudiante en cursos
$permisos[]=array($modulos[1]=>$roles[3]); #estudiante en foros
$permisos[]=array($modulos[2]=>$roles[3]); #estudiante en cuestionario
$permisos[]=array($modulos[3]=>$roles[3]); #estudiante en eventos
$permisos[]=array($modulos[4]=>$roles[3]); #estudiante en mensajes
$permisos[]=array($modulos[5]=>$roles[3]); #estudiante en red
$permisos[]=array($modulos[6]=>$roles[3]); #estudiante en reportes
$permisos[]=array($modulos[7]=>$roles[3]); #estudiante en usuarios
$permisos[]=array($modulos[8]=>$roles[3]); #estudiante en comun
$permisos[]=array($modulos[9]=>$roles[3]); #estudiante en index.php

#$permisos[]=array($modulos[0]=>$roles[4]); #sinrol en cursos
$permisos[]=array($modulos[1]=>$roles[4]); #sinrol en foros
$permisos[]=array($modulos[2]=>$roles[4]); #sinrol en cuestionario
$permisos[]=array($modulos[3]=>$roles[4]); #sinrol en eventos
$permisos[]=array($modulos[4]=>$roles[4]); #sinrol en mensajes
$permisos[]=array($modulos[5]=>$roles[4]); #sinrol en red
$permisos[]=array($modulos[6]=>$roles[4]); #sinrol en reportes
#$permisos[]=array($modulos[7]=>$roles[4]); #sinrol en usuarios
#$permisos[]=array($modulos[8]=>$roles[4]); #sinrol en comun
$permisos[]=array($modulos[9]=>$roles[4]); #sinrol en index.php

#permisos especiales
$permisos_archivos[][$modulos[7]][]=array($archivos[0]=>$roles[4]);#sinrol en usuarios login.php
$permisos_archivos[][$modulos[8]][]=array($archivos[1]=>$roles[4]);#sinrol en comun iconos.php

//$usuarios

//negar va al final #permisos especiales
$negar_permisos_archivos[][$modulos[8]][]=array($archivos[1]=>$roles[4]);#negar a sinrol en comun iconos.php

@session_start();
if(isset($_SESSION['rol'])){
$rol = $_SESSION['rol'];
}else{
$rol = "sinrol";
}
#echo "Su rol actual es:".$rol;
#exit();
if ($rol!=""){
  //comprobar la ruta desde donde se hace la peticion
  if (isset($carpeta[1])) $moduloactual = $carpeta[1];
  if (isset($carpeta[2])) $archivo = $carpeta[2];
  $acceso = false;//estado predeterminaro
  //validar permisos de acceso a carpetas
        if (isset($permisos) and !empty($permisos))
        foreach ($permisos as $permiso){
           if (isset ($permiso[$moduloactual]) and $permiso[$moduloactual]==$rol){
             $acceso=true;
             break;
           }else{
            //
                   
            //
           }
        }
    //validar permisos de acceso a carpetas
    //validar permisos de acceso a archivos si no hay acceso a carpetas
        if (isset($permisos_archivos) and !empty($permisos_archivos) and !$acceso){
                   foreach ($permisos_archivos as $id => $permiso_carpeta){
                     if (isset($permiso_carpeta[$moduloactual]))
                        foreach ($permiso_carpeta[$moduloactual] as $carpeta_archivo_permiso){
                           if (isset($carpeta_archivo_permiso[$archivo]) and $carpeta_archivo_permiso[$archivo]==$rol){
                             $acceso=true;
                             break;
                           }
                         
                        }
                   }
        }
    //validar permisos de acceso a archivos si no hay acceso a carpetas
    //validar permisos de negacion a archivos si no hay acceso a carpetas
    if (isset($negar_permisos_archivos) and !empty($negar_permisos_archivos)){
                   foreach ($negar_permisos_archivos as $id => $permiso_carpeta){
                     if (isset($permiso_carpeta[$moduloactual]))
                        foreach ($permiso_carpeta[$moduloactual] as $carpeta_archivo_permiso){
                           if (isset($carpeta_archivo_permiso[$archivo]) and $carpeta_archivo_permiso[$archivo]==$rol){
                             $acceso=false;
                             break;
                           }
                         
                        }
                   }
     }
    //validar permisos de negacion a archivos si no hay acceso a carpetas
    
     if (!$acceso){
        #print_r($permisos_archivos[0][$moduloactual]);
        #echo $moduloactual." ".$archivo." ".$rol;
        #header("Location: index.php");
        #exit();
     }
}
?>