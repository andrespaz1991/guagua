<?php 
ob_start();

require_once '../comun/autoload.php'; 
$persona =new persona();
#Pomedio de asignaciones que tiene el docente por año
$sql='SELECT  materia.nombre_materia,count((asignacion.id_asignatura)) as cantidad from asignacion,materia WHERE materia.id_materia=asignacion.id_asignatura GROUP by asignacion.id_asignatura order by cantidad desc limit 5';

$datos=json_decode($persona->consultar_datos($sql,true),true);

$titulo="Asignaciones frecuentes";
$tipo="ColumnChart";
$persona->graficar($datos,$titulo,$tipo);

###################
$sql2='Select materia.nombre_materia,count(inscripcion.id_inscripcion) as cantidad  FROM inscripcion, asignacion, materia WHERE
inscripcion.id_asignacion=asignacion.id_asignacion and
asignacion.id_asignatura=materia.id_materia 
and asignacion.institucion_educativa=1
GROUP by id_materia order by cantidad desc limit 5';
$datos2=json_decode($persona->consultar_datos($sql2,true),true);
$titulo2="Distribución de Estudiantes";
$tipo2="ColumnChart";
$persona->graficar($datos2,$titulo2,$tipo2);

#####################

$sql3='select id_red,titulo_red from red ';
$datos3=json_decode($persona->consultar_datos($sql3,true),true);
$contador=0;
foreach ($datos3 as $key => $recurso) {
$sql33="SELECT '".$recurso['titulo_red']."' as recurso,count(*) as cantidad FROM `plan_clase` where (CHARACTER_LENGTH((JSON_SEARCH(`red`, 'all','".$recurso['id_red']."')))>3)";
$datos33=json_decode($persona->consultar_datos($sql33,true),true);
}

$titulo4="Recursos Educativos en planes ";
$tipo4="ColumnChart";
$persona->graficar($datos33,$titulo4,$tipo4);
########################
#Palabras más usadas en los mensajes

$tabla="mensaje";
$campo="mensaje";
$primaria="id_mensaje";
$tipografo="ColumnChart";
$tiuloPalabra='Palabras más usadas en mensajes';
$limitepalabras=10;
palabras($tabla,$campo,$primaria,$limitepalabras,$tipografo,$tiuloPalabra);
function palabras($tabla,$campo,$primaria,$limitepalabras,$tipografo,$tiuloPalabra){
    require_once '../comun/autoload.php'; 
    $persona =new persona();
    $sqlmensaje="select $campo from $tabla";
    $contador=0;
    $a=array();
    $diccionario=array();
   $datosmensaje=json_decode(($persona->consultar_datos($sqlmensaje,true)),true);
   
   foreach($datosmensaje as $clave=>$valor){
         $valor[$campo]= mb_strtolower(($valor[$campo]),'UTF-8');
        array_push($a,explode(' ',$valor[$campo]));
    $diccionario = array_merge_recursive($diccionario,$a[$contador++]);      
        }
    $diccionario=array_unique($diccionario);
    foreach($diccionario as $clave => $palabra){
    if(strlen($palabra)>3){
         $sqlpalabra="select SUM(veces) as Total_Veces 
        from   (select $primaria
                     , $campo
                     , length($campo) as largo_cadena
                     , length(replace($campo,'$palabra','')) as largo_cadena_sin_palabra
                     , (length($campo)-length(replace($campo,'$palabra','')))/length('$palabra')
                       as veces
                from   $tabla) q1";
                   
    $info=json_decode($persona->consultar_datos($sqlpalabra,true),true)[0]['Total_Veces'];
   
if($info<>"0.0000"){
$datost[$palabra]=json_decode($persona->consultar_datos($sqlpalabra,true),true)[0]['Total_Veces'];
}  
                
    }
        }
    arsort($datost);
    $datospalabras=array();
    $palabrasSeleccionadas= array_slice($datost, 0, $limitepalabras);
    $contadorpalabra=0;
    foreach($palabrasSeleccionadas as $clave => $cantidaduso){
        $total=$contadorpalabra++;
        $datospalabras[$total]['clave']=$clave;
        $datospalabras[$total]['valor']=round($cantidaduso,2);  
    }
    $persona->graficar($datospalabras,$tiuloPalabra,$tipografo);
    
}


###Palabras más usadas en notas

$tabla="edunotas";
$campo="nota";
$primaria="id_nota";
$tipografo="ColumnChart";
$tiuloPalabra='Palabras frecuentes en edunotas';
$limitepalabras=2;
palabras($tabla,$campo,$primaria,$limitepalabras,$tipografo,$tiuloPalabra);

#### Analisis de objetivos

$sqly="SELECT SUBSTR(objetivos,1, LOCATE(' ', objetivos)) AS nobjetivos FROM objetivos";
#echo $sqlx;
$datosp=json_decode($persona->consultar_datos($sqly,true),true);
$informacion=(array_count_values(array_values_recursive($datosp)));
array_shift($informacion);
$palabrasSeleccionadas= array_slice($informacion, 0, 10);
$contadorpalabra=0;
arsort($palabrasSeleccionadas);
foreach($palabrasSeleccionadas as $clave => $cantidaduso){
    $total=$contadorpalabra++;
    $datospalabras[$total]['clave']=$clave;
    $datospalabras[$total]['valor']=$cantidaduso;  
}
$persona->graficar($datospalabras,'Objetivos  de planes de clase','ColumnChart');
##observación
$sqly="SELECT SUBSTR(observaciones_plan,1, LOCATE(' ', observaciones_plan)) AS observaciones_plan FROM plan_clase";
#echo $sqlx;
$datosp=json_decode($persona->consultar_datos($sqly,true),true);
$informacion=(array_count_values(array_values_recursive($datosp)));
array_shift($informacion);
$palabrasSeleccionadas= array_slice($informacion, 0, 10);
$contadorpalabra=0;
arsort($palabrasSeleccionadas);
foreach($palabrasSeleccionadas as $clave => $cantidaduso){
    if(strlen($clave)>3){
    $total=$contadorpalabra++;
    $datospalabras[$total]['clave']=$clave;
    $datospalabras[$total]['valor']=$cantidaduso;  
    }
}
$persona->graficar($datospalabras,'Observaciones de planes de clase ','ColumnChart');
####Analisis de seguimiento


$tabla="seguimiento";
$campo="observaciones";
$primaria="id_seguimiento";
$tipografo="ColumnChart";
$tiuloPalabra='Observaciones de seguimiento';
$limitepalabras=5;
palabras($tabla,$campo,$primaria,$limitepalabras,$tipografo,$tiuloPalabra);







####función
function array_values_recursive($array)
{
    $arrayValues = array();

    foreach ($array as $value)
    {
       
      
        if (is_scalar($value) OR is_resource($value)  )
        {
        if(!empty($value)){
          
            $arrayValues[] = $value;
            
        }
        
               
          
             
        }
        elseif (is_array($value) and !empty($value) )
        {
            
            
                $arrayValues = array_merge($arrayValues, array_values_recursive($value));
            
        }

    }
    return $arrayValues;
}
$contenido = ob_get_contents();
ob_clean();
include ("../comun/plantilla.php");
?>