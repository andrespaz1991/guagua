<?php
class Conexion extends PDO
 { 
   private $tipo_de_base    = 'mysql';          /**< Indica el tipo de motor de datos */
   private $host            = 'localhost';      /**< Indica el host */
   private $nombre_de_base = 'guagua';            /**< Indica el nombre de la base de datos */
   private $usuario         = 'root';           /**< Indica el nombre de usuario de la base de datos */
   private $contrasena      = '';       /**< Indica la contraseña de usuario de la base de datos */



public function query_insertar($sql,$insert_id=false){
    $mysqli = $this->conectar();
    $salida = 0;
    $consulta = $mysqli->query($sql);
    if ($mysqli->errno != 1062){
        if ($consulta==1 and $mysqli->affected_rows>0){
            if ($insert_id) $salida = $mysqli->insert_id;
            else $salida =  1;
        }else{
      //      $motivo = "EL usuario intentó realizar la consulta: ".$sql;
  //    Comun::insertar_log($motivo);
            if ($insert_id){
                 $salida = false;
            }else{
                $salida =  0;
            }
        }
    }else{
        $motivo = "EL usuario intentó realizar la consulta con error de registro duplicado Codigo 1062. ".$sql;
    #  Comun::insertar_log($motivo);
        if ($insert_id) $salida = false;
        else $salida = 1062;
    }

 //   $this->desconectar($mysqli);
    return $salida;
}



public function consultar_datos($consulta='',$mysqli_assoc=false){
  if ($consulta==''){//prueba desde 55 - 58
    $consulta = $_POST['consulta'];
    $mysqli_assoc = true;
}
$consulta = str_replace("DELETE","",$consulta);
$consulta = str_replace("UPDATE","",$consulta);
$consulta = str_replace("DROP","",$consulta);
$consulta = str_replace("CREATE","",$consulta);
$consulta = str_replace("ALTER","",$consulta);
//validar solo SELECT
if (strlen(stristr($consulta,"SELECT"))>0) {
$mysqli = $this->conectar();
if ($gconsulta_red = $mysqli->prepare($consulta)){
$gconsulta_red = $mysqli->prepare($consulta);
$gconsulta_red->execute();
$arraydedatos = $gconsulta_red->get_result();
if($mysqli_assoc){
$datos = $arraydedatos->fetch_all(MYSQLI_ASSOC);
}else{
$datos = $arraydedatos->fetch_all();
}
//$this->desconectar($mysqli);
#mysql_free_result($datos);
#return $datos;
return json_encode($datos);
}
}
}
public function conectar(){
    $conexion_mysqli = new mysqli ($this->host,$this->usuario,$this->contrasena,$this->nombre_de_base);
    if (mysqli_connect_errno()){
      $mysqli->set_charset("utf8");
            echo "error".mysqli_connect_errno();
    }else{
            if($conexion_mysqli){
              $conexion_mysqli->set_charset("utf8");
            mysqli_set_charset($conexion_mysqli,'utf8');
            }
    }
    if (!$conexion_mysqli->set_charset("utf8")) {
        # printf("Error cargando el conjunto de caracteres utf8: %s\n", $conexion_mysqli->error);
    #exit();
} else {
  mysqli_character_set_name($conexion_mysqli);
    #printf("Conjunto de caracteres actual canción: %s\n", $conexion_mysqli->character_set_name());
}
    return $conexion_mysqli;
}


   /**
     * @brief crea la conexión PDO.
    */  
     public function __CONSTRUCT() {
       // $this->conectar();
   } 
 }




?>