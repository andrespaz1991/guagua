<?php
//si no existe la variable de session y no esta  dentro e las excepciones entonces entonces debe ir al login
//si existe variable de session y al módulo al cual desea acceder tiene permisos para ese usuario
//

$roles = array("admin","docente","estudiante","acudiente","sinrol");
$modulos = array("cursos","foros","cuestionario","eventos","mensajes","red","reportes","usuario","comun","index.php");
$archivos = array("login.php","iconos.php");
$usuarios = array("1","1080232001");
$ruta = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
$raizPermisos = rtrim(str_replace('\\', '/', SGA_SERVER), '/') . '/';
$esRutaGuagua = DIRECTORY_SEPARATOR === '\\'
    ? strncasecmp($ruta, $raizPermisos, strlen($raizPermisos)) === 0
    : strncmp($ruta, $raizPermisos, strlen($raizPermisos)) === 0;
$ruta = $esRutaGuagua ? '/' . substr($ruta, strlen($raizPermisos)) : '';
$carpeta = explode("/",$ruta);#echo "Modulo: ".$carpeta[1];
$moduloactual = $carpeta[1] ?? '';

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
// Alerta (menú 88): reporte académico para el personal docente/directivo.
// Autorizar este archivo, sin abrir toda la carpeta ia ni el acceso anónimo.
$permisos_archivos[]['ia'][] = array('reporte.php' => 'admin');
$permisos_archivos[]['ia'][] = array('reporte.php' => 'docente');
$permisos_archivos[][$modulos[7]][]=array($archivos[0]=>$roles[4]);#sinrol en usuarios login.php
$permisos_archivos[][$modulos[8]][]=array($archivos[1]=>$roles[4]);#sinrol en comun iconos.php

//$usuarios

//negar va al final #permisos especiales
$negar_permisos_archivos[][$modulos[8]][]=array($archivos[1]=>$roles[4]);#negar a sinrol en comun iconos.php

@session_start();
if(isset($_SESSION['rol'])){
$rol = strtolower(trim((string) $_SESSION['rol']));
}else{
$rol = "sinrol";
}
$rol = $rol === '' ? 'sinrol' : $rol;
$rol = $rol === 'directivo' ? 'admin' : $rol;
#echo "Su rol actual es:".$rol;
#exit();
if ($rol!=""){
  //comprobar la ruta desde donde se hace la peticion
  $archivo = '';
  if (isset($carpeta[1])) $moduloactual = $carpeta[1];
  if (isset($carpeta[2])) $archivo = $carpeta[2];
  $acceso = false;//estado predeterminaro
  //validar permisos de acceso a carpetas
        // Las reglas de carpeta nunca convierten una sesión anónima en una
        // sesión autorizada. Las excepciones públicas se declaran abajo por
        // archivo (por ejemplo, usuario/login.php).
        if ($rol !== 'sinrol' && isset($permisos) and !empty($permisos))
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
    // Módulos del menú para personal docente/directivo omitidos en las reglas
    // originales. Lista explícita: no autorizar cualquier archivo bajo apps.
    // Las comprobaciones de cada aplicación (por ejemplo DBA) siguen vigentes.
    if (!$acceso && $esRutaGuagua && !empty($_SESSION['id_usuario'])
        && in_array($rol, ['admin', 'docente'], true)) {
        $rutaModulo = strtolower(ltrim($ruta, '/'));
        $carpetasPersonal = [
            'planeador', 'asistencia', 'seguimiento', 'sisga',
            'apps/restaurante', 'apps/mimercado', 'apps/asistencia',
            'apps/generacion_cursos', 'apps/generador_vallesol',
            'apps/utel', 'apps/planmind', 'apps/geminichat',
            'apps/carnet', 'apps/edutask', 'apps/rubrica', 'apps/davinci',
            'apps/evidencias', 'apps/configuracion_interfaz',
            'apps/customizador_menu', 'apps/planeador_anual',
            'apps/dba', 'apps/formatos', 'apps/preguntas_por_equipos', 'apps/sopas',
            'apps/simulador_notas',
            'apps/mapa_procesos',
        ];
        foreach ($carpetasPersonal as $carpetaPersonal) {
            if (strpos($rutaModulo, $carpetaPersonal . '/') === 0) {
                $acceso = true;
                break;
            }
        }
        if (in_array($rutaModulo, ['ia/reporte.php', 'ia/config_reporte.php'], true)) {
            $acceso = true;
        }
    }
    // Conversión DNG: disponible para cualquier sesión iniciada.
    if (!$acceso && $esRutaGuagua && !empty($_SESSION['id_usuario'])
        && strpos(strtolower(ltrim($ruta, '/')), 'apps/dng-word/') === 0) {
        $acceso = true;
    }
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
        http_response_code(empty($_SESSION['id_usuario']) ? 401 : 403);
        if (empty($_SESSION['id_usuario'])) {
          header('Location: /guagua/usuario/login.php');
        } else {
          header('Content-Type: text/plain; charset=utf-8');
          echo 'No tiene permiso para acceder a este recurso.';
        }
        exit();
     }
}
?>
