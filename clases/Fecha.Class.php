<?php
class Fecha{

public function read_date($str){
     if($str)
      return date('d/m/Y g:i:s a', strtotime($str));
     else
      return null;
  }
/*--------------------------------------------------------------*/
/* Function for  Readable Make date time
/*--------------------------------------------------------------*/
public function make_date(){
  return strftime("%Y-%m-%d %H:%M:%S", time());
}
/*--------------------------------------------------------------*/
/* Function for  Readable date time
/*--------------------------------------------------------------*/

/*MANEJO DE FECHAS_ REVISAR Y COMPACTAR*/
public static function formato_fecha($fecha){
if ($fecha!="" and $fecha!="0000-00-00"){
$meses = array ('','\\E\\n\\e\\r\\o','\\F\\e\\b\\r\\e\\r\\o','\\M\\a\\r\\z\\o','\\A\\b\\r\\i\\l','\\M\\a\\y\\o','\\J\\u\\n\\i\\o','\\J\\u\\l\\i\\o','\\A\\g\\o\\s\\t\\o','\\S\\e\\p\\t\\i\\e\\m\\b\\r\\e','\\O\\c\\t\\u\\b\\r\\e','\\N\\o\\v\\i\\e\\m\\b\\r\\e','\\D\\i\\c\\i\\e\\m\\b\\r\\e');
$fecha2 = date("d \\d\\e ".$meses[date("n",strtotime($fecha))]."  \\d\\e\\l  Y ",strtotime($fecha));
return $fecha2;
}else{
    return "";
}
}
function estado_dif_fecha_vencimiento($estado_dif_fecha){
    $estado_dif_fecha_s='';
    if ($estado_dif_fecha==0){
    $estado_dif_fecha_s = "Hoy";
    }else if ($estado_dif_fecha<0){
    if (abs($estado_dif_fecha)==1)
    $estado_dif_fecha_s = "Falta ".abs($estado_dif_fecha)." día";
    else
    $estado_dif_fecha_s = "Faltan ".abs($estado_dif_fecha)." días";
    }else if ($estado_dif_fecha>0){
    if ($estado_dif_fecha==1)
    $estado_dif_fecha_s = "Hace ".abs($estado_dif_fecha)." día";
    else
    $estado_dif_fecha_s = "Hase ".$estado_dif_fecha." días";
    }
    return $estado_dif_fecha_s;
}
function calculaedad($fecha) {
    $Y=$this->anio($fecha);
    $m=$this->mes($fecha);
    $d=$this->dia($fecha);
    $edad = date("md") < $m.$d ? date("Y")-$Y-1 : date("Y")-$Y;
    return($edad);
}
public function dia($fecha,$letras=false){
$salida = date("d",strtotime($fecha));
if ($letras){
require_once("enletras.class.php");
    $V=new Enletras(); 
    $salida = mb_strtoupper($V->ValorEnLetras($salida,''),'UTF-8');
}
return $salida;
}
public static function mes_letras($fecha,$letras=false){
$meses = array ('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
#if($fecha<10) $fecha = substr($fecha,1);
return $salida = $meses[$fecha];    
}
public static function mes($fecha,$letras=false){
$meses = array ('','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre');
 $salida = date("n",strtotime($fecha));
if ($letras){
$salida = $meses[date("n",strtotime($fecha))];
}
return mb_strtoupper($salida,'UTF-8');
}

public function anio($fecha,$letras=false){
$salida = date("Y",strtotime($fecha));
if ($letras){
require_once("enletras.class.php");
    $V=new Enletras(); 
    $salida = mb_strtoupper($V->ValorEnLetras($salida,''),'UTF-8');
}
return $salida;
}
public static function formato_fecha_reporte($fecha){
$date_unix = strtotime($fecha);
$meses = array ("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$date = date("d",$date_unix);
$date .= " dias del mes de ";
$mes = date("n",$date_unix);
$date .= $meses[$mes];
$date .= " del año ";
$date .= date("Y",$date_unix);
return $date;
}
public static function formato_fecha_corta($fecha){
if ($fecha!=""){
    $fecha2 = date("d\\/".date("n",strtotime($fecha))."\\/".date("Y",strtotime($fecha))."",strtotime($fecha));
    return $fecha2;
}else{
    return $fecha;
}
}
public  function formato_fecha2($fecha){
$date_unix = strtotime($fecha);
$meses = array ("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$date = date("d",$date_unix);
$date .= " de ";
$mes = date("n",$date_unix);
$date .= $meses[$mes];
$date .= " del a&ntilde;o ";
$date .= date("Y",$date_unix);
return $date;
}
public function formato_fechayhora($fecha){
$date_unix = strtotime($fecha);
$meses = array ("","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$date = date("d",$date_unix);
$date .= " de ";
$mes = date("n",$date_unix);
$date .= $meses[$mes];
$date .= " del a&ntilde;o ";
$date .= date("Y",$date_unix);
$date .= "<br> a las ";
$date .= date(" h:i:s a",$date_unix);
return $date;
}

public static function formato_hora($hora){
if ($hora!=""){
$time_unix = strtotime($hora);
$time = date("h:i:s a",$time_unix);
return $time;
}
}
public function formato_hora_corta($hora){
if ($hora!=""){
$time_unix = strtotime($hora);
$time = date("h:i a",$time_unix);
return $time;
}
}
public static function sumar_a_fecha($fecha1,$dias,$criterio="day"){
   # echo $dias.'<br>';
$mifecha = date("Y-m-d",strtotime('+'.$dias.$criterio, strtotime($fecha1)));
return $mifecha;
}

public static function RestarHoras($horaini,$horafin)
{
    $f1 = new DateTime($horaini);
    $f2 = new DateTime($horafin);
    $diferencia = $f1->diff($f2);
$horas =$diferencia->format('%h');
$minutos =$diferencia->format('%i');
$segundos =$diferencia->format('%s');
return array($horas,$minutos,$segundos);


}

public static function restar_horas ($hora_inicial,$hora_final) {
date_default_timezone_set('America/Bogota'); 
$hora_inicial_en_formato_24h  = date("H:i:s", strtotime($hora_inicial));
$hora_final_en_formato_24h  = date("H:i:s", strtotime($hora_final));
$hora_inicial = new DateTime($hora_inicial_en_formato_24h);
$hora_final = new DateTime($hora_final_en_formato_24h); //new DateTime -> creamos un objeto de tipo fecha 
$diferencia = $hora_final->diff($hora_inicial);//new DateTime -> creamos un objeto de tipo fecha
$horas =$diferencia->format('%h');
$minutos =$diferencia->format('%i');
$segundos =$diferencia->format('%s');
return array($horas,$minutos,$segundos);
                                                  }

public static function restar_a_fecha($fecha1,$dias,$criterio="day"){
$mifecha = date("Y-m-d",strtotime('-'.$dias.$criterio, strtotime($fecha1)));
return $mifecha;
}
public static function saber_dia() {
   $dias = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
   $dia = $dias[date("w")];
    return $dia;
}
public static function saber_dia_letras($nombredia) {
$dias = array('Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado');
$fecha = $dias[date('N', strtotime($nombredia))];
return $fecha;
}

public function ordenar_fecha($fecha,$formato='d-m-Y'){
$fecha = new DateTime( $fecha) ;// ejemplo ‘1991-07-20’
echo $fechaordenada = $fecha -> format($formato); 
}
public static function diferencia_fecha($fecha_i,$fecha_f,$abs = true)
{
	$dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
	if ($abs) $dias 	= abs($dias);
	$dias = floor($dias);		
	return $dias;
}
public static function diferencia_fecha_mes($fecha_i,$fecha_f,$abs = true)
{
	$dias	= (strtotime($fecha_i)-strtotime($fecha_f))/86400;
	if ($abs) $dias = abs($dias);
	$meses = $dias/30;
	$meses = floor($meses);		
	return $meses;
}
public static function diferencia_hora($hora1,$hora2){
$date1 = new DateTime($hora2);
$date2 = new DateTime($hora1);
$diff = $date1->diff($date2);
// 3036 seconds to go [number is variable]
$salida = ( ($diff->days * 24 ) * 60 ) + ( $diff->i * 60 ) + $diff->s;
// passed means if its negative and to go means if its positive
#$salida .=  ' seconds' . ($diff->invert == 1 ) ? ' passed ' : ' to go ';
return $salida;
}
/*FIN MANEJO DE FECHAS_ REVISAR Y COMPACTAR*/
}
?>