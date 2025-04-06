<?php
class Comun extends Fecha{
  public $errors = array();
public function encriptar($valor,$clave="SGA"){
    /*
Objetivo: Enctriptar un string
Estado: Activa
    */
    return sha1($valor.$clave);
}
static function eliminar_sobrante($cadena){
  #$cadena = "Esta cadena tiene un texto (después del paréntesis)";
  $patron = "/\(.*/"; // Expresión regular para encontrar el primer paréntesis y todo el texto después
  $textoSinParentesis = preg_replace($patron, "", $cadena); // Reemplaza el texto después del paréntesis con una cadena vacía
  return trim($textoSinParentesis); // Salida: Esta cadena tiene un texto
}

static function extraerTextoEntreParentesisValida($cadena) {
  $patron = "/\((.*?)\)/"; // Expresión regular para encontrar texto entre paréntesis
  preg_match($patron, $cadena, $matches); // Busca coincidencias del patrón
  if (isset($matches[1])) {
    return $matches[1]; // Devuelve el texto capturado entre paréntesis
  } else {
    return 0; // Si no se encuentra texto entre paréntesis, devuelve 1
  }
}

 static function remover_ultimo_caracter($cadena){
  return $cadena = substr($cadena, 0, -1);
}

public function graficar($datos,$titulo="Gráfico",$tipo="PieChart"){ 
echo '<div id="chart_div"></div> ';

} public static function ia_guagua($userContent, $model = "gemma-2-2b-instruct", $temperature = 0.7, $maxTokens = -1) {
  $url = "http://localhost:1234/v1/chat/completions";
  $systemContent = 'Mi nombre es Andres Paz, 
  soy Licenciado en informática pero me encuentro en el municipio 
  de San Luis Antioquia-Colombia y me encuentro trabajando como docente de postprimaria 
  Enseñando diversas asignaturas desde los grados 6 a 11. 
  Necesito que me respondas en español. 
  Eres un experto en educación';

  $data = [
      "model" => $model,
      "messages" => [
          ["role" => "system", "content" => $systemContent],
          ["role" => "user", "content" => $userContent]
      ],
      "temperature" => $temperature,
      "max_tokens" => $maxTokens,
      "stream" => false
  ];

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
      "Content-Type: application/json"
  ]);

  $response = curl_exec($ch);

  if (curl_errno($ch)) {
      throw new Exception(curl_error($ch));
  }

  curl_close($ch);

  // 🔍 Imprimir la respuesta para depuración
  error_log("Respuesta IA: " . $response);

  // Decodificar el JSON completo de la respuesta
  $decodedResponse = json_decode($response, true);

  // 🔍 Verificar si la API devuelve correctamente 'choices'
  if (!isset($decodedResponse['choices'])) {
      return json_encode(['error' => 'La API no devolvió choices'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  }

  // Verificar si 'choices' tiene elementos y si 'content' está presente
  if (isset($decodedResponse['choices'][0]['message']['content'])) {
      $content = $decodedResponse['choices'][0]['message']['content'];
      return json_encode(['content' => $content], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  } else {
      return json_encode(['error' => 'No se encontró el campo content en la respuesta de la IA'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  }
}

public static function verificar_archivo($ruta,$archivo){
  if(file_exists($ruta."\\".$archivo)){
  return 1;
}else{
  return 0;
}

}

public static function llamar_estudiantes_grado_Vallesol($min=6,$max=8,$estudiante=''){
  require(dirname(__FILE__)."/../comun/conexion.php");
  
  #$sql='SELECT  DISTINCT id_usuario,nombre, observaciones FROM `inscripcion` INNER join usuario on inscripcion.id_estudiante= usuario.id_usuario where observaciones>='.$min.' and observaciones<='.$max.' 
   # ORDER BY `usuario`.observaciones DESC, usuario.nombre asc;';
 $sql='
 SELECT 
    usuario.id_usuario, 
    usuario.* 
FROM `inscripcion` 
INNER JOIN asignacion ON inscripcion.id_asignacion = asignacion.id_asignacion 
INNER JOIN usuario ON usuario.id_usuario = inscripcion.id_estudiante 
WHERE 
 asignacion.id_categoria_curso >= "'.$min.'"  AND
asignacion.id_categoria_curso <="'.$max.'"';
if(!empty($estudiante)){
  $sql.='  and id_usuario="'.$estudiante.'" ';
}

$sql.=' GROUP BY usuario.apellido asc,usuario.nombre asc;';
 #echo $sql;
  $consulta=$mysqli->query($sql);
  $estudiantes=array();
  while($row=$consulta->fetch_Assoc()){
 $estudiantes[]=$row;
  }
  return $estudiantes;
}
public function copia_guagua($source ='D:\xampp\htdocs\guagua',$destination = 'D:\bda\guagua'){
$this->full_copy($source, $destination);
}

public static function  full_copy( $source, $target ) {
    if ( is_dir( $source ) ) {
        @mkdir( $target );
        $d = dir( $source );
        while ( FALSE !== ( $entry = $d->read() ) ) {
            if ( $entry == '.' || $entry == '..' ) {
                continue;
            }
            $Entry = $source . '/' . $entry; 
            if ( is_dir( $Entry ) ) {
                Comun::full_copy( $Entry, $target . '/' . $entry );
                continue;
            }
            copy( $Entry, $target . '/' . $entry );
        }
 
        $d->close();
    }else {
        copy( $source, $target );
    }
}


public function nombre_clase(){
    return get_called_class();
}


public static function boton_modal_nuevo_icono(){
    ?>
    <!--button id="btn_nuevo_foro" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal_nuevo">Nuevo Icono</button-->
    <?php
}
public static function ventana_modal_nuevo_icono($atributos){
    ?>
    <!-- Modal -->
<div id="myModal_nuevo" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nuevo Icono</h4>
      </div>
      <div class="modal-body">
        <form <?php echo $atributos ?> ENCTYPE="multipart/form-data">
              <div class="form-group">
                <label for="icono">Nombre</label>
                <input type="text" name="icono" id="icono" class="form-control" placeholder="Nombre del icono">
            </div>
            <div class="form-group">
                <label for="imagen_icono">Archivo</label>
                <input type="file" multiple name="imagen_icono" id="imagen_icono" class="form-control" placeholder="Titulo del Foro">
            </div>
            <div class="form-group">
                <button  type="submit" name="titulo_foro" id="titulo_foro" class="btn btn-success">Guardar</button>
            </div>
            </form>
      </div>
      <div class="modal-footer">
        <button id="cerrar_modal_nuevo_icono" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
<!---->
    <?php
}
public static function boton_modal_elegir_icono($destino=""){
    ?>
    <button id="btn_elegir_icono" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal2_elegiricono<?php echo $destino ?>" onmouseup="parent.buscar_iconos('','<?php echo $destino; ?>');">Elegir Icono</button>
    <?php
}
public static function ventana_modal_elegir_icono($destino=""){
    ?>
    <!-- Modal -->
<div id="myModal2_elegiricono<?php echo $destino ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Elegir un Icono</h4>
      </div>
      <div class="modal-body">
        <input name="contexto" type="hidden" value="general">
            <div class="form-group">
                <?php Self::boton_modal_nuevo_icono($destino); ?>
            </div>
            <div class="form-group">
                <!--label for="buscar_iconos">Buscar Icono</label-->
                <input type="hidden" name="buscar_iconos" id="buscar_iconos" class="form-control" placeholder="Escriba aqui para buscar" value="" onkeyup ="parent.buscar_iconos(this.value,'<?php echo $destino?>');" onchange="parent.buscar_iconos(this.value,'<?php echo $destino?>');"  >
            </div>
        <div class="form-group">
            <span id="txtresultadosicono<?php echo $destino ?>">
            </span>
        </div>
      </div>
      <div class="modal-footer">
        <button id="cerrar_modal_elegir_icono<?php echo $destino ?>" type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
<!---->
    <?php
}
public function setear($array=''){
    if($array=='') $array = $_POST;
    $clase = get_called_class();
    foreach($array as $propiedad => $valor){
        if (property_exists($clase, $propiedad)){
           $this->$propiedad=$valor;
        }      
    }
}
public function to_array(){
    return json_decode(json_encode($this),true);
}
public static function ceiling($number, $significance = 1)
   {
       return ( is_numeric($number) && is_numeric($significance) ) ? (ceil($number/$significance)*$significance) : false;
   }
public static function generar_datalist($tabla='',$campo=''){
    if ($tabla=='' and isset ($_POST['tabla'])) $tabla = $_POST['tabla'];
    if ($campo=='' and isset ($_POST['campo'])) $campo = $_POST['campo'];
    #echo $tabla."-".$campo;
    if ($tabla!='' and $campo!=''){
    $persona_generar_datalist = new Persona();
    $array = $persona_generar_datalist->consultar_distict_usado($tabla,$campo);
    $html = '';
    $html .= '<datalist id="list_'.$campo.'">';
    if (count($array)>0){
        foreach($array as $sug){
             if ($tabla!="cargo"  or ($tabla=="cargo" and $sug!="superadmin"))
             $html .= '<option>'.$sug.'</option>';
        }
    }
    $html .= '</datalist>';
    return $html;
    }
}

/*--------------------------------------------------------------*/
/* Function for Remove html characters
/*--------------------------------------------------------------*/
  public static function validar_foto($foto,$ruta=true){
  
  if(empty($foto)){ 
        if ($ruta)
        return USUARIO_SIN_FOTO;
        else
        return SIN_FOTO;
    }else{
                $salida = $foto;
                if ($ruta) $salida = URL_RAIZ.'/media/persona_media/'.$foto;
                return $salida;
    }
}



public static function remove_junk($str){
  $str = nl2br($str);
  $str = htmlspecialchars(strip_tags($str, ENT_QUOTES));
  return $str;
}


/*--------------------------------------------------------------*/
/* Function for redirect
/*--------------------------------------------------------------*/
public function redirect($url, $permanent = false)
{
    if (headers_sent() === false)
    {
      header('Location: ' . $url, true, ($permanent === true) ? 301 : 302);
    }

    exit();
}
public static function json_encode_assoc($array,$criterio){
$sum = array();
if (is_array($array) and !empty($array))
foreach($array as $num => $values) {
    if (is_array($values)){
    $sum[] = $values[$criterio];
    }
}
return json_encode($sum);
}
public static function array_sum_assoc($array,$criterio){
$sum = 0;
if (!empty($array) and is_array($array))
foreach($array as $num => $values) {
    $sum += $values[$criterio];
}
return $sum;
}
public static function formato_barcode($codigo,$nombre_codigo='',$orientacion='horizontal',$formato='code128') {
    if ($nombre_codigo=='') $nombre_codigo=$codigo;
    include dirname(__FILE__).'/../lib/barcode/barcode.php';
    @mkdir("codigos");
    $archivo = '/views/codigos/'.$nombre_codigo.'.png';
    barcode(dirname(__FILE__).'/..'.$archivo, $codigo, 40, $orientacion, $formato, false,1,"");
    if (file_exists(dirname(__FILE__).'/..'.$archivo)){
        return $archivo;
    }else{
        return false;
    }
}
public static function formato_moneda($numero,$decimales=0,$formato = "$") {
    if (is_numeric($numero)){
    $formatdado = $formato.number_format($numero,$decimales,",",".");
    return $formatdado;
    }else{
    return $numero;
    }
}
public static function unir_arrays($array_antiguo,$array_nuevo){
$array_nuevo_filtrado = array();
    foreach($array_nuevo as $valor_n){
        if (!in_array($valor_n,$array_antiguo)){
            $array_nuevo_filtrado[]=$valor_n;
        }
}
$array_nuevo_filtrado = array_merge($array_nuevo_filtrado,$array_antiguo);
return $array_nuevo_filtrado;
}
public static function letras($numero=''){
    if (isset($_POST['numero']) and $_POST['numero']!='') $numero=$_POST['numero'];
    if ($numero!=''){
    require_once("enletras.class.php");
    $V=new Enletras(); 
    $letras = mb_strtoupper($V->ValorEnLetras($numero,''),'UTF8');
    return $letras;
    }
}

public static function array_utf8_decode(&$datos){
    foreach ($datos as $id => $valor){
        if (!is_array($valor)){
            $datos[$id]=utf8_decode($valor);
        }else{
            Self::array_utf8_decode($datos[$id]);
        }
    }
}

public static function subir_archivo($name,$tamaño_maximo,$formatos,$ruta_destino,$nombre,$extensión_archivo){
   $total = count($_FILES[$name]['name']);
  for($i=0; $i<$total; $i++) {
     $nombre_archivo=$_FILES[$name]['name'][$i]; 
     $ruta_tmp_archivo = $_FILES[$name]['tmp_name'][$i];
       if ($ruta_tmp_archivo != ""){  
     $extensión_archivo = (pathinfo($nombre_archivo,PATHINFO_EXTENSION));
       if (!in_array($extensión_archivo, $formatos)){echo "El formato no es valido"; } 
            if(filesize($_FILES[$name]['tmp_name'][$i]) > $tamaño_maximo ) {
              echo "No se puede subir archivos con tamaño mayor a ".$tamaño_maximo; 
            }
            $ruta_destino.='/'.$nombre.'.'.$extensión_archivo;
if(move_uploaded_file($ruta_tmp_archivo,$ruta_destino)) {  
#                  echo "1";
                    return 1; }
                 else{
                     return 0 ; }
            }
          }
        }



public static function ordenar_telefonos($origen,&$destino){
/*Funcion ordenar telefono*/
/**/
$telefonos_per = json_decode($origen,true);
#print_r($telefonos_per);
$telefono_per = "";
$cont=1;
if (count($telefonos_per)>0 and $telefonos_per<>"")
foreach ($telefonos_per as $numero_tel_per => $lugar_tel_per){
    $telefono_per .= $lugar_tel_per
    ." : ".$numero_tel_per;
    if ($cont<count($telefonos_per)) $telefono_per .= ", ";
    $cont++;
}
$destino = $telefono_per;
/*Fin Funcion ordenar telefono*/
} 
public static function ordenar_telefonos_json($numeros,$lugares){
/*Funcion ordenar telefono json*/
$salida = array();
$cont=1;
if (count($numeros)>0)
foreach ($numeros as $id => $numero){
    $salida[$numero] = $lugares[$id];
    $cont++;
}
return json_encode($salida);
/*Fin Funcion ordenar telefono*/
} 
public function modal($titulo,$cuerpo,$id){
/*
recibe un titulo, contenido que asicuado a una id crea una modal
Estado: pendiente actul¿alizar
*/
?>
    <!-- Modal -->
<div id="myModal_nuevo_fondo" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nuevo Fondo</h4>
      </div>
      <div class="modal-body">
        <form method="post">
	<label for="nombre_fondo">Nombre del fondo</label>
		<input id="nombre_fondo" type="text" name="nombre_fondo" required>
<br>
<label>Perfil del fondo</label>
<?php 
$consulta = $fondo->consultar_datos("SELECT * FROM `perfil_acciones",true);
#print_r($consulta);
//`id_perfil_acciones`, `nombre_perfil_acciones`, `acciones` 
?>
<div id="fondo"><!--lista de chequeo con las caracteristicas del perfil del fondo-->
<select name="perfil">
    <?php 
    foreach ($consulta as $id => $row){
    ?>
    <option value="<?php echo $row['id_perfil_acciones'] ?>"><?php echo $row['nombre_perfil_acciones'] ?> - <?php echo $row['acciones'] ?></option>
    <?php
    }
    ?>
    <option value="">Crear Nuevo</option>
</select>
</div>
<label for="">Color</label><br/>
<input type="text" name="color"/><br/>
<br>
<label for="">Icono</label><br/>
<input type="text" name="icono"/><br/>
<br>
<label>Entidad</label>
		<select name="entidad"><?php
foreach ($fondo->consultar_datos('select * from entidad') as $key => $value) {
		echo '<option value="'.$value[0].'">'.$value[1].'</option>';
			} ?>
		</select>
<br>
<label>Fondo superior</label>
<?php 
$consulta = $fondo->consultar_datos("SELECT * FROM `fondo",true);
#print_r($consulta);
//`id_perfil_acciones`, `nombre_perfil_acciones`, `acciones` 
?>
<div id="padre"><!--lista de chequeo con las caracteristicas del perfil del fondo-->
<select name="padre">
    <option value="">Es Fondo Principal</option>
    <?php 
    foreach ($consulta as $id => $row){
    ?>
    <option value="<?php echo $row['id_fondo'] ?>"><?php echo $row['nombre'] ?></option>
    <?php
    }
    ?>
</select>
		<button type="submit">Crear</button>
	</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>

  </div>
</div>
<!-- Fin Modal -->
    <?php
}

 /*--------------------------------------------------------------*/
 /* Function for Remove escapes special
 /* characters in a string for use in an SQL statement
 /*--------------------------------------------------------------*/
public static function sin_permisos($motivo, $valor){
    //Insertar log
    //Redireccionar a un page de notificación de permisos
        //Redireccionar al login
Comun::insertar_log($motivo, $valor);
//header ("Location: pagina.php");
header ("Location: ".URL_RAIZ);
}

public static function ordenar_string($str,$string="-"){
$datos[]=explode($string,$str);
return Comun::mes_letras($datos[0][1],true).'-'.$datos[0][0]; 
}
public static function getRealIP(){
if(isset($_SERVER)){if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))return $_SERVER["HTTP_X_FORWARDED_FOR"];if(isset($_SERVER["HTTP_CLIENT_IP"]))return $_SERVER["HTTP_CLIENT_IP"];return $_SERVER["REMOTE_ADDR"];}if(getenv('HTTP_X_FORWARDED_FOR'))return getenv('HTTP_X_FORWARDED_FOR');if(getenv('HTTP_CLIENT_IP'))return getenv('HTTP_CLIENT_IP');return getenv('REMOTE_ADDR');}

public static function start_log(){
$sql = "CREATE TABLE IF NOT EXISTS `log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `fechayhoralog` datetime NOT NULL,
  `ipclientelog` varchar(255) COLLATE utf8_bin NOT NULL,
  `valor` decimal(12,4) NOT NULL,
  `usuario` int(11) NOT NULL,
  `motivo` text COLLATE utf8_bin NOT NULL,
  `categoria` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT 'sistema',
  PRIMARY KEY (`id_log`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1;";
$clase_mysqli = new Clase_mysqli;
$mysqli = $clase_mysqli->conectar();
$mysqli->query($sql);
}
public static function insertar_log($motivo, $valor="",$categoria="sistema"){
Comun::start_log();
if(isset($_POST['identificacion'])){
$_POST['username']=$_POST['identificacion'];
}
    @session_start();
    $clase_mysqli = new Clase_mysqli;
    $datos_log = array();
    $datos_log['fechayhoralog'] = date("Y-m-d H:i:s");
    $datos_log['ipclientelog'] = Comun::getRealIP();
    $datos_log['valor'] = $valor;
    $datos_log['categoria'] = $categoria;
if(isset($_SESSION['id_usuario'])){
    $datos_log['usuario'] = $_SESSION['id_usuario'];
}elseif (isset($_POST['username'])){
    $datos_log['usuario'] = ' se intento acceder desde el usuario  '.$_POST['username'];
}else{
    $datos_log['usuario'] = '';
}
    $datos_log['motivo'] = $motivo.' detalle de session: '.json_encode($_SESSION);
    $clase_mysqli->sql_insertar($datos_log,'log',$query = true);
}
public static function totalizar_valores_con_formato($array,$formato_valores,$campo='tasa'){
    $resultado = 0;
    foreach ($array as $datos){
        if ($datos['formato_valores']==$formato_valores){
        $resultado += $datos[$campo];
        }
    }
    $resultado = Comun::formato_valores($formato_valores,$resultado);
    return $resultado;
}
public static function is_in_array($array, $key, $key_value){
      $within_array = 0;
      foreach( $array as $k=>$v ){
        if( is_array($v) ){
            $within_array = Comun::is_in_array($v, $key, $key_value);
            if( $within_array == 1 ){
                break;
            }
        } else {
                if( $v == $key_value && $k == $key ){
                        $within_array = 1;
                        break;
                }
        }
      }
      return $within_array;
}

public static function puntos_suspensivos($string, $length=NULL)
{
if ($length == NULL){
        $length = 50;
  }
    //Primero eliminamos las etiquetas html y luego cortamos el string
    mb_internal_encoding("UTF-8");
    $stringDisplay= mb_substr($string,0,$length);
    $stringDisplay=$stringDisplay.'...';
    return ($stringDisplay);
}


public static function remove_accents($string)
{
 $sql="DROP FUNCTION IF EXISTS `remove_accents`;

DELIMITER //
CREATE FUNCTION `remove_accents`(`str` TEXT)
    RETURNS text
    LANGUAGE SQL
    DETERMINISTIC
    NO SQL
    SQL SECURITY INVOKER
    COMMENT ''

BEGIN

    SET str = REPLACE(str,'Š','S');
    SET str = REPLACE(str,'š','s');
    SET str = REPLACE(str,'Ð','Dj');
    SET str = REPLACE(str,'Ž','Z');
    SET str = REPLACE(str,'ž','z');
    SET str = REPLACE(str,'À','A');
    SET str = REPLACE(str,'Á','A');
    SET str = REPLACE(str,'Â','A');
    SET str = REPLACE(str,'Ã','A');
    SET str = REPLACE(str,'Ä','A');
    SET str = REPLACE(str,'Å','A');
    SET str = REPLACE(str,'Æ','A');
    SET str = REPLACE(str,'Ç','C');
    SET str = REPLACE(str,'È','E');
    SET str = REPLACE(str,'É','E');
    SET str = REPLACE(str,'Ê','E');
    SET str = REPLACE(str,'Ë','E');
    SET str = REPLACE(str,'Ì','I');
    SET str = REPLACE(str,'Í','I');
    SET str = REPLACE(str,'Î','I');
    SET str = REPLACE(str,'Ï','I');
    SET str = REPLACE(str,'Ñ','N');
    SET str = REPLACE(str,'Ò','O');
    SET str = REPLACE(str,'Ó','O');
    SET str = REPLACE(str,'Ô','O');
    SET str = REPLACE(str,'Õ','O');
    SET str = REPLACE(str,'Ö','O');
    SET str = REPLACE(str,'Ø','O');
    SET str = REPLACE(str,'Ù','U');
    SET str = REPLACE(str,'Ú','U');
    SET str = REPLACE(str,'Û','U');
    SET str = REPLACE(str,'Ü','U');
    SET str = REPLACE(str,'Ý','Y');
    SET str = REPLACE(str,'Þ','B');
    SET str = REPLACE(str,'ß','Ss');
    SET str = REPLACE(str,'à','a');
    SET str = REPLACE(str,'á','a');
    SET str = REPLACE(str,'â','a');
    SET str = REPLACE(str,'ã','a');
    SET str = REPLACE(str,'ä','a');
    SET str = REPLACE(str,'å','a');
    SET str = REPLACE(str,'æ','a');
    SET str = REPLACE(str,'ç','c');
    SET str = REPLACE(str,'è','e');
    SET str = REPLACE(str,'é','e');
    SET str = REPLACE(str,'ê','e');
    SET str = REPLACE(str,'ë','e');
    SET str = REPLACE(str,'ì','i');
    SET str = REPLACE(str,'í','i');
    SET str = REPLACE(str,'î','i');
    SET str = REPLACE(str,'ï','i');
    SET str = REPLACE(str,'ð','o');
    SET str = REPLACE(str,'ñ','n');
    SET str = REPLACE(str,'ò','o');
    SET str = REPLACE(str,'ó','o');
    SET str = REPLACE(str,'ô','o');
    SET str = REPLACE(str,'õ','o');
    SET str = REPLACE(str,'ö','o');
    SET str = REPLACE(str,'ø','o');
    SET str = REPLACE(str,'ù','u');
    SET str = REPLACE(str,'ú','u');
    SET str = REPLACE(str,'û','u');
    SET str = REPLACE(str,'ý','y');
    SET str = REPLACE(str,'ý','y');
    SET str = REPLACE(str,'þ','b');
    SET str = REPLACE(str,'ÿ','y');
    SET str = REPLACE(str,'ƒ','f');
    RETURN str;
END
//
DELIMITER ;";
$clase_mysqli = new Clase_mysqli();
$mysqli = $clase_mysqli->conectar();
$mysqli->query($sql);
    $string = trim($string);
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(
        array("\\", "¨", "º", "-", "~",
             "#", "@", "|", "!", "\"",
             "·", "$", "%", "&", "/",
             "(", ")", "?", "'", "¡",
             "¿", "[", "^", "<code>", "]",
             "+", "}", "{", "¨", "´",
             ">", "< ", ";", ",", ":",
             ".", " "),
        '',
        $string
    );
 
 
    return $string;
}
public static function formato_valores($formato_valores,$valor){
    if ($formato_valores=="$"){
        return Comun::formato_moneda($valor);
    }else if ($formato_valores=="%"){
        return $valor.$formato_valores;
    }else if ($valor==0){
        return $formato_valores;
    }else{
         return $valor;
    }
}
public static function restar_assoc($minuendo,$sustraendo,$hijo=false){
$resta = array();
if ($hijo==false){
foreach ($minuendo as $i => $valor){
    if (isset($minuendo[$i],$sustraendo[$i])){
        $resta[$i] = ($minuendo[$i] - $sustraendo[$i]);
    }else if (isset($minuendo[$i])){
        $resta[$i] = $minuendo[$i];
    }
}
}else{
foreach ($minuendo as $i => $valor){
    if (isset($minuendo[$i][$hijo],$sustraendo[$i][$hijo])){
        $resta[$i][$hijo] = ($minuendo[$i][$hijo] - $sustraendo[$i][$hijo]);
    }else if (isset($minuendo[$i])){
        $resta[$i] = $minuendo[$i];
    }
}
}
return $resta;
}
public static function generar_ruta_de_migas($excepciones,$array=""){
    $excepciones[]='Submit';
    $excepciones[]='Submit_x';
    $excepciones[]='Submit_y';
    $excepciones[]='x';
    $excepciones[]='y';
    
    if ($array=="") $array=$_GET;
    ?>
    <h6>
    <ol class="breadcrumb">
    <?php 
    foreach ($_GET as $parametro => $valor) 
    { 
    if(!in_array($parametro,$excepciones)){ ?>
    <li><a href="<?php echo $valor; ?>"><?php echo ucwords(str_replace("_"," ",$parametro)); ?></a></li>
    <?php    
    }    ?>  <?php  }    ?> </ol>
    </h6>
    <?php
}

public static function generar_tala($array="",$tabla="Datos"){
$personas = $array;
?>
<h1><?php echo $tabla ?></h1>
<?php 
$personas_array = json_decode($personas,true);
$personas_campos = array_keys(end($personas_array));
//print(json_encode($personas_campos));
?>
<table>
<tbody>
<tr>
<?php

foreach ($personas_campos as $personas_campo){
echo "<th>".ucwords($personas_campo)."</th>";
}
?>
</tbody>
<?php
foreach ($personas_array as $id => $row){
    echo "<tr>";
  foreach ($row as $nombre_campo => $valor_campo){
    echo "<td data-label=\"".$nombre_campo."\">".$valor_campo."</td>";
} ?>
</tr>
<?php } ?>
</table>
<?php
}

public function tipos_sangre($pre=""){ ?>
<option value="">Seleccione Tipo de Sangre</option>
<option <?php if($pre == "O-") echo "selected";?> value="O-">O negativo</option>
<option <?php if($pre == "O+") echo "selected";?> value="O+">O positivo</option>
<option <?php if($pre == "A-") echo "selected";?> value="A-">A negativo</option>
<option <?php if($pre == "A+") echo "selected";?> value="A+">A positivo</option>
<option <?php if($pre == "B-") echo "selected";?> value="B-">B negativo</option>
<option <?php if($pre == "B+") echo "selected";?> value="B+">B positivo</option>
<option <?php if($pre == "AB-") echo "selected";?> value="AB-">AB negativo</option>
<option <?php if($pre == "AB+") echo "selected";?> value="AB+">AB positivo</option><?php
}



/*--------------------------------------------------------------*/
/* Function for Uppercase first character
/*--------------------------------------------------------------*/

public static function redondear_ajuste($valor,$ajuste_valor_moneda){
  $resultado = (ceil($valor/$ajuste_valor_moneda)*$ajuste_valor_moneda);
  return $resultado;
}
public function first_character($str){
  $val = str_replace('-'," ",$str);
  $val = ucfirst($val);
  return $val;
}
/*--------------------------------------------------------------*/
/* Function for Checking input fields not empty
/*--------------------------------------------------------------*/
/*
public function validate_fields($var){
  global $errors;
  foreach ($var as $field) {
    $val = $this->remove_junk($_POST[$field]);
    if(isset($val) && $val==''){
      $errors = $field ." No puede estar en blanco.";
      return $errors;
    }
  }
}
*/
/*--------------------------------------------------------------*/
/* Function for Display Session Message
   Ex echo displayt_msg($message);
/*--------------------------------------------------------------*/

public function display_msg($msg =''){
   $output = array();
   if(!empty($msg)) {
      foreach ($msg as $key => $value) {
         $output  = "<div class=\"alert alert-{$key}\">";
         $output .= "<a href=\"#\" class=\"close\" data-dismiss=\"alert\">&times;</a>";
         $output .= $this->remove_junk($this->first_character($value));
         $output .= "</div>";
      }
      return $output;
   } else {
     return "" ;
   }
}

/*--------------------------------------------------------------*/

/*
public function randString($length = 5)
{
    //Revisar
  $str='';
  $cha = "0123456789abcdefghijklmnopqrstuvwxyz";

  for($x=0; $x<$length; $x++)
   $str .= $cha[mt_rand(0,strlen($cha))];
  return $str;
}
*/
//funciones adicionales
/*
public function find_all($table) {
    //Revisar OJO
   global $db;
   if($this->tableExists($table))
   {
     return find_by_sql("SELECT * FROM ".$db->escape($table));
   }
}
*/
/*--------------------------------------------------------------*/
/* Function for Perform queries
/*--------------------------------------------------------------*/
/*
public function find_by_sql($sql)
{
  global $db;
  $result = $db->query($sql);
  $result_set = $db->while_loop($result);
 return $result_set;
}
*/
/*--------------------------------------------------------------*/
/*  Function for Find data from table by id
/*--------------------------------------------------------------*/
/*
public function find_by_id($table,$id)
{
  global $db;
  $id = (int)$id;
    if($this->tableExists($table)){
          $sql = "SELECT * FROM {$db->escape($table)} WHERE id='{$db->escape($id)}' LIMIT 1");
          if($result = $this->consultar_datos($sql,true))
            return $result;
          else
            return null;
     }
}
*/
/*--------------------------------------------------------------*/
/* Function for Delete data from table by id
/*--------------------------------------------------------------*/
/*
public function delete_by_id($table,$id)
{
  global $db;
  if(tableExists($table))
   {
    $sql = "DELETE FROM ".$db->escape($table);
    $sql .= " WHERE id=". $db->escape($id);
    $sql .= " LIMIT 1";
    $db->query($sql);
    return ($db->affected_rows() === 1) ? true : false;
   }
}
*/
/*--------------------------------------------------------------*/
/* Function for Count id  By table name
/*--------------------------------------------------------------*/
/*
public function count_by_id($table){
  global $db;
  if(tableExists($table))
  {
    $sql    = "SELECT COUNT(id) AS total FROM ".$db->escape($table);
    $result = $db->query($sql);
     return($db->fetch_assoc($result));
  }
}
*/
/*--------------------------------------------------------------*/
/* Determine if database table exists
/*--------------------------------------------------------------*/
/*
public function tableExists($table){
  global $db;
  $table_exit = $db->query('SHOW TABLES FROM '.DB_NAME.' LIKE "'.$db->escape($table).'"');
      if($table_exit) {
        if($db->num_rows($table_exit) > 0)
              return true;
         else
              return false;
      }
  }
  */
  /*--------------------------------------------------------------*/
  /* Find current log in user by session id
  /*---------------------------F-----------------------------------*/
/*
public function current_user(){
    @session_start();
      static $current_user;
      global $db;
      if(!$current_user){
             $current_user = $_SESSION['user_id'];
      }
    return $current_user;
  }
*/  
  /*--------------------------------------------------------------*/
  /* Find all user by
  /* Joining users table and user gropus table
  /*--------------------------------------------------------------*/
/*
public function find_all_user(){
      global $db;
      $results = array();
      $sql = "SELECT u.id,u.name,u.username,u.user_level,u.status,u.last_login,";
      $sql .="g.group_name ";
      $sql .="FROM users u ";
      $sql .="LEFT JOIN user_groups g ";
      $sql .="ON g.group_level=u.user_level ORDER BY u.name ASC";
      $result = find_by_sql($sql);
      return $result;
  }
*/
  /*--------------------------------------------------------------*/
  /* Function to update the last log in of a user
  /*--------------------------------------------------------------*/
/*
public function updateLastLogIn($user_id)
	{
		global $db;
    $date = make_date();
    $sql = "UPDATE users SET last_login='{$date}' WHERE id ='{$user_id}' LIMIT 1";
    $result = $db->query($sql);
    return ($result && $db->affected_rows() === 1 ? true : false);
	}
*/
  /*--------------------------------------------------------------*/
  /* Find all Group name
  /*--------------------------------------------------------------*/
/*
public function find_by_groupName($val)
  {
    global $db;
    $sql = "SELECT group_name FROM user_groups WHERE group_name = '{$db->escape($val)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
*/
  /*--------------------------------------------------------------*/
  /* Find group level
  /*--------------------------------------------------------------*/
/*
public function find_by_groupLevel($level)
  {
    global $db;
    $sql = "SELECT group_level FROM user_groups WHERE group_level = '{$db->escape($level)}' LIMIT 1 ";
    $result = $db->query($sql);
    return($db->num_rows($result) === 0 ? true : false);
  }
  */
  /*--------------------------------------------------------------*/
  /* Function for cheaking which user level has access to page
  /*--------------------------------------------------------------*/
/*
public function page_require_level($require_level){
     global $session;
     $current_user = current_user();
     $login_level = $this->find_by_groupLevel($current_user['user_level']);
     //if user not login
     if (!$session->isUserLoggedIn(true)):
            $session->msg('d','Por favor Iniciar sesión...');
            $this->redirect('index.php', false);
      //if Group status Deactive
     elseif($login_level['group_status'] === '0'):
           $session->msg('d','Este nivel de usaurio esta inactivo!');
           $this->redirect('home.php',false);
      //cheackin log in User level and Require level is Less than or equal to
     elseif($current_user['user_level'] <= (int)$require_level):
              return true;
      else:
            $session->msg("d", "¡Lo siento!  no tienes permiso para ver la página.");
            $this->redirect('home.php', false);
        endif;

     }
  */
  /*--------------------------------------------------------------*/
   /* Function for Finding all product name
   /* JOIN with categorie  and media database table
   /*--------------------------------------------------------------*/
/*
public function join_product_table(){
     global $db;
     $sql  =" SELECT p.id,p.name,p.quantity,p.buy_price,p.sale_price,p.media_id,p.date,c.name";
    $sql  .=" AS categorie,m.file_name AS image";
    $sql  .=" FROM products p";
    $sql  .=" LEFT JOIN categories c ON c.id = p.categorie_id";
    $sql  .=" LEFT JOIN media m ON m.id = p.media_id";
    $sql  .=" ORDER BY p.id ASC";
    return $this->find_by_sql($sql);

   }
   */
  /*--------------------------------------------------------------*/
  /* Function for Finding all product name
  /* Request coming from ajax.php for auto suggest
  /*--------------------------------------------------------------*/
/*
public function find_product_by_title($product_name){
     global $db;
     $p_name = $this->remove_junk($db->escape($product_name));
     $sql = "SELECT name FROM products WHERE name like '%$p_name%' LIMIT 5";
     $result = $this->find_by_sql($sql);
     return $result;
   }
*/
  /*--------------------------------------------------------------*/
  /* Function for Finding all product info by product title
  /* Request coming from ajax.php
  /*--------------------------------------------------------------*/
/*
public function find_all_product_info_by_title($title){
    global $db;
    $sql  = "SELECT * FROM products ";
    $sql .= " WHERE name ='{$title}'";
    $sql .=" LIMIT 1";
    return find_by_sql($sql);
  }
*/
  /*--------------------------------------------------------------*/
  /* Function for Update product quantity
  /*--------------------------------------------------------------*/
/*
public function update_product_qty($qty,$p_id){
    global $db;
    $qty = (int) $qty;
    $id  = (int)$p_id;
    $sql = "UPDATE products SET quantity=quantity -'{$qty}' WHERE id = '{$id}'";
    $result = $db->query($sql);
    return($db->affected_rows() === 1 ? true : false);

  }
  */
  /*--------------------------------------------------------------*/
  /* Function for Display Recent product Added
  /*--------------------------------------------------------------*/
/*
public function find_recent_product_added($limit){
   global $db;
   $sql   = " SELECT p.id,p.name,p.sale_price,p.media_id,c.name AS categorie,";
   $sql  .= "m.file_name AS image FROM products p";
   $sql  .= " LEFT JOIN categories c ON c.id = p.categorie_id";
   $sql  .= " LEFT JOIN media m ON m.id = p.media_id";
   $sql  .= " ORDER BY p.id DESC LIMIT ".$db->escape((int)$limit);
   return find_by_sql($sql);
 }
 */
 /*--------------------------------------------------------------*/
 /* Function for Find Highest saleing Product
 /*--------------------------------------------------------------*/
/*
public function find_higest_saleing_product($limit){
   global $db;
   $sql  = "SELECT p.name, COUNT(s.product_id) AS totalSold, SUM(s.qty) AS totalQty";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON p.id = s.product_id ";
   $sql .= " GROUP BY s.product_id";
   $sql .= " ORDER BY SUM(s.qty) DESC LIMIT ".$db->escape((int)$limit);
   return $db->query($sql);
 }
 */
 /*--------------------------------------------------------------*/
 /* Function for find all sales
 /*--------------------------------------------------------------*/
/*
public function find_all_sale(){
   global $db;
   $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
   $sql .= " FROM sales s";
   $sql .= " LEFT JOIN products p ON s.product_id = p.id";
   $sql .= " ORDER BY s.date DESC";
   return find_by_sql($sql);
 }
 */
 /*--------------------------------------------------------------*/
 /* Function for Display Recent sale
 /*--------------------------------------------------------------*/
/*
public function find_recent_sale_added($limit){
  global $db;
  $sql  = "SELECT s.id,s.qty,s.price,s.date,p.name";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " ORDER BY s.date DESC LIMIT ".$db->escape((int)$limit);
  return find_by_sql($sql);
}
*/
/*--------------------------------------------------------------*/
/* Function for Generate sales report by two dates
/*--------------------------------------------------------------*/
/*
public function find_sale_by_dates($start_date,$end_date){
  global $db;
  $start_date  = date("Y-m-d", strtotime($start_date));
  $end_date    = date("Y-m-d", strtotime($end_date));
  $sql  = "SELECT s.date, p.name,p.sale_price,p.buy_price,";
  $sql .= "COUNT(s.product_id) AS total_records,";
  $sql .= "SUM(s.qty) AS total_sales,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price,";
  $sql .= "SUM(p.buy_price * s.qty) AS total_buying_price ";
  $sql .= "FROM sales s ";
  $sql .= "LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE s.date BETWEEN '{$start_date}' AND '{$end_date}'";
  $sql .= " GROUP BY DATE(s.date),p.name";
  $sql .= " ORDER BY DATE(s.date) DESC";
  return $db->query($sql);
}
*/
/*--------------------------------------------------------------*/
/* Function for Generate Daily sales report
/*--------------------------------------------------------------*/
/*
public function  dailySales($year,$month){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y-%m' ) = '{$year}-{$month}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%e' ),s.product_id";
  return find_by_sql($sql);
}
*/
/*--------------------------------------------------------------*/
/* Function for Generate Monthly sales report
/*--------------------------------------------------------------*/
/*
public function  monthlySales($year){
  global $db;
  $sql  = "SELECT s.qty,";
  $sql .= " DATE_FORMAT(s.date, '%Y-%m-%e') AS date,p.name,";
  $sql .= "SUM(p.sale_price * s.qty) AS total_saleing_price";
  $sql .= " FROM sales s";
  $sql .= " LEFT JOIN products p ON s.product_id = p.id";
  $sql .= " WHERE DATE_FORMAT(s.date, '%Y' ) = '{$year}'";
  $sql .= " GROUP BY DATE_FORMAT( s.date,  '%c' ),s.product_id";
  $sql .= " ORDER BY date_format(s.date, '%c' ) ASC";
  return find_by_sql($sql);
}
*/

public function buscar_iconos($datos="",$reporte="",$destino=""){
require(dirname(__FILE__)."/../config/conexion.php");
require_once (dirname(__FILE__)."/../lib/Zebra_Pagination/Zebra_Pagination.php");
$resultados = ((isset($_COOKIE['numeroresultados_iconos']) and $_COOKIE['numeroresultados_iconos']!= ""  and $_COOKIE['numeroresultados_iconos']!= 0 ) ? $_COOKIE['numeroresultados_iconos'] : 10);
$paginacion = new Zebra_Pagination();
$paginacion->records_per_page($resultados);
$paginacion->records_per_page($resultados);
$funcionjs="buscar_iconos('','".$destino."');";$paginacion->fn_js_page("$funcionjs");
$paginacion->cookie_page("page_iconos");
$paginacion->padding(false);
if (isset($_COOKIE["page_iconos"]))
$_GET['page'] = $_COOKIE["page_iconos"];
else
$_GET['page'] = 1;
$sql = "SELECT `iconos`.`id_iconos`, `iconos`.`icono`, `iconos`.`imagen_icono` FROM `iconos`   ";
$datosrecibidos = $datos;
$datos = explode(" ",$datosrecibidos);
$datos[]="";
$cont =  0;
$sql .= ' WHERE ';
foreach ($datos as $id => $dato){
$sql .= ' concat(LOWER(`iconos`.`icono`)," ",LOWER(`iconos`.`imagen_icono`)," "   ) LIKE "%'.mb_strtolower($dato, 'UTF-8').'%"';
$cont ++;
if (count($datos)>1 and count($datos)<>$cont){
$sql .= " and ";
}
}
$sql .=  " ORDER BY ";
if (isset($_COOKIE['orderbyiconos']) and $_COOKIE['orderbyiconos']!=""){ $sql .= "`iconos`.`".$_COOKIE['orderbyiconos']."`";
}else{ $sql .= "`iconos`.`id_iconos`";}
if (isset($_COOKIE['orderad_iconos'])){
$orderadiconos = $_COOKIE['orderad_iconos'];
$sql .=  " $orderadiconos ";
}else{
$sql .=  " desc ";
}
$consulta_total_iconos = $mysqli->query($sql);
$total_iconos = $consulta_total_iconos->num_rows;
$paginacion->records($total_iconos);
if (isset($_COOKIE["page_iconos"]))
$sql .=  " LIMIT " . (($paginacion->get_page() - 1) * $resultados) . ", " . $resultados;
#echo $sql; 
$consulta = $mysqli->query($sql);
$numero_iconos = $consulta->num_rows;
$minimo_iconos = (($paginacion->get_page() - 1) * $resultados)+1;
$maximo_iconos = (($paginacion->get_page() - 1) * $resultados) + $resultados;
if ($maximo_iconos>$numero_iconos) $maximo_iconos=$numero_iconos;
$maximo_iconos += $minimo_iconos-1;
echo "<p>Resultados de $minimo_iconos a $maximo_iconos del total de ".$total_iconos." en página ".$paginacion->get_page()."</p>";
 ?>
<div class="css_table" align="center">

<div border="1" id="tbiconos">
<div vlass="css_thead">
<span class="css_tr">
</span>
</div>
<div class="css_tbody">
<ul class="bs-glyphicons">
<?php 
while($row=$consulta->fetch_assoc()){
 ?>
 aaaa
<li id="icono_<?php echo $row['id_iconos']?>" class="icono_a_seleccionar" onclick="document.getElementById('icono_seleccionado<?php echo $destino ?>').value = '<?php echo $row['id_iconos']?>';document.getElementById('icono_seleccionado_img<?php echo $destino ?>').src='<?php echo URL_RAIZ ?>/img/png/<?php echo $row['imagen_icono']; ?>';document.getElementById('cerrar_modal_elegir_icono<?php echo $destino ?>').click();" data-label='Imagen icono'>
  <?php if ($reporte==""){ ?>
<span data-label="Modificar">
<form style="float:left;position:relative;margin-bottom:-10px" id="formModificar" name="formModificar" method="post" action="<?php echo URL_RAIZ ?>/iconos.php" ENCTYPE="multipart/form-data">
<input name="cod" type="hidden" id="cod" value="<?php echo $row['id_iconos']?>">
<input  type="hidden" name="submit" src="<?php echo URL_RAIZ ?>/img/modificar.png" height="20px" value="Modificar">
</form>
</span>
<span style="float:right;position:relative;margin-bottom:-10px" data-label="Eliminar">
<input type="hidden" src="<?php echo URL_RAIZ ?>/img/eliminar.png" height="20px" onClick="confirmeliminar2('<?php echo URL_RAIZ ?>/iconos.php',{'del':'<?php echo $row['id_iconos'];?>'},'<?php echo $row['icono'];?>');" value="Eliminar">
</span>
<?php } ?>
 <img width="50px" src="<?php echo URL_RAIZ ?>/img/png/<?php echo $row['imagen_icono']; ?>">
 <?php #echo $row['imagen_icono']?><br>
 <span data-label='icono'><?php echo $row['icono']?></span>
 </li>
<?php 
}/*fin while*/
 ?>
</ul>
</div>
</div>
<?php $paginacion->render2();?>
</div>
<?php 
}/*fin function buscar*/
public static function llamar_comet(){
 $unico = uniqid();
 $filename  = dirname(__FILE__).'/../lib/comet/data.txt';//ruta del archivo
 file_put_contents($filename,$unico);
} /*fin llamar_comet*/ 
    
} //Fin de clase

