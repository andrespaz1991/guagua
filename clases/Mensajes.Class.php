<?php
class Mensajes extends Clase_mysqli{

public function leer_mensajes($home=false){
	 $sql=' SELECT * FROM `mensaje` where leido="NO" and usuario="'.$_SESSION['id_usuario'].'" order by fecha asc';
	 $mis_mensajes = json_decode($this->consultar_datos($sql,true),true);
	 if($home==false){
 return $mis_mensajes; 
	 }else{
	 	   if(empty($mis_mensajes)){
                     echo 'No hay nuevos mensajes :)';
                 }                      
                 $contador_eventos=0;
foreach ($mis_mensajes as $key => $info_mensaje) {
  $contador_eventos=$contador_eventos+1;
      echo $contador_eventos.'.'.$info_mensaje['mensaje'].'<strong> Fecha: </strong> '.formatofechayhora($info_mensaje['fecha']).' <br/>';
}
	 }






}

public function mensajes_favoritos($listar=false){
 $sql='SELECT * FROM `mensaje` where favorito="SI" and usuario="'.$_SESSION['id_usuario'].'" order by fecha desc limit 1 ';
 $favoritos = json_decode($this->consultar_datos($sql,true),true);
 if($listar==false){
 return $favoritos;
 }else{
 	      if (!empty($favoritos)){             ?>
                              <h3>Favoritos</h3>
                              <?php } ?>
                              <p>
                                  <?php                               
                 $contador_eventros = 0 ;
      foreach ($favoritos as $lista_mensajes =>$info_mensaje) {
              $contador_eventos++ ;
             ?>
              <img width="10%" src="<?php echo SGA_COMUN_URL.'/img/png/mensaje.png'; ?>"></img>
             <?php
               echo $contador_eventos.'.'.$info_mensaje['mensaje'].'<strong> Fecha:  </strong> '.formatofechayhora($info_mensaje['fecha']).' <br/>';
                       }
                       }
          
 }
}


