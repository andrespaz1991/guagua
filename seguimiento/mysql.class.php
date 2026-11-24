<?php
class Conexion extends PDO
 { 



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



public function consultar_datos($consulta = '', $mysqli_assoc = false, array $parameters = []) {
        // La consulta proviene del código; nunca tomar SQL del cuerpo HTTP.
        if (!is_string($consulta) || !preg_match('/^\s*SELECT\b/i', $consulta)) {
            throw new InvalidArgumentException('Proporcione una consulta SELECT explícita.');
        }
        $connection = $this->conectar();
        $statement = $connection->prepare($consulta);
        if (!$statement) return null;
        try {
            if ($parameters) $statement->bind_param(str_repeat('s', count($parameters)), ...$parameters);
            $statement->execute();
            $result = $statement->get_result();
            if (!$result) return null;
            return json_encode($result->fetch_all($mysqli_assoc ? MYSQLI_ASSOC : MYSQLI_NUM));
        } finally { $statement->close(); }
    }

    public function conectar(){
    require_once __DIR__ . '/../comun/database.php';
    return guagua_db_mysqli();
}

   /**
     * @brief crea la conexión PDO.
    */  
     public function __CONSTRUCT() {
       // $this->conectar();
   } 
 }




?>
