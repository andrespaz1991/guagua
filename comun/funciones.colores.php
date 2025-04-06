<?php
/*Incio Función Colores*/
function forcolores($numero,$numero_dias,$inicio_naranja,$inicio_verde){
$rojo = "amarillo.PNG"; $naranja="verde.PNG"; $verde ="rojo.PNG";//asignacion de colores
     for ($i=0; $i <= $numero_dias; $i++){
        //$inicio_naranja,$inicio_verde
        if($numero==$i){
            if($numero<$inicio_naranja){
            $color= $rojo;
            }
            if($numero>=$inicio_naranja and $numero<$inicio_verde){
            $color=$naranja;    
            }
            if($numero>=$inicio_verde){
            $color=$naranja;    
            }
        }
    }
    return array($color);//no entiendo por que arrat color, pero funcionaria
}
function colores2 ($numero,$numero_dias){
return forcolores($numero,$numero_dias,2,2);//case 1
return forcolores($numero,$numero_dias,1,2);//case 2
return forcolores($numero,$numero_dias,1,3);//case 3
return forcolores($numero,$numero_dias,2,4);//case 4
return forcolores($numero,$numero_dias,2,4);//case 5
return forcolores($numero,$numero_dias,2,5);//case 6
return forcolores($numero,$numero_dias,3,5);//case 7
return forcolores($numero,$numero_dias,3,6);//case 8
return forcolores($numero,$numero_dias,4,7);//case 9
return forcolores($numero,$numero_dias,4,7);//case 10
return forcolores($numero,$numero_dias,4,8);//case 11
return forcolores($numero,$numero_dias,4,8);//case 12
return forcolores($numero,$numero_dias,5,9);//case 13
return forcolores($numero,$numero_dias,5,10);//case 14
return forcolores($numero,$numero_dias,5,11);//case 15
return forcolores($numero,$numero_dias,6,12);//case 16
return forcolores($numero,$numero_dias,6,12);//case 17
return forcolores($numero,$numero_dias,6,13);//case 18
return forcolores($numero,$numero_dias,6,14);//case 19
return forcolores($numero,$numero_dias,7,15);//case 20
return forcolores($numero,$numero_dias,8,16);//case 21
return forcolores($numero,$numero_dias,9,17);//case 22
return forcolores($numero,$numero_dias,9,17);//case 23
return forcolores($numero,$numero_dias,9,18);//case 24
return forcolores($numero,$numero_dias,11,19);//case 25
return forcolores($numero,$numero_dias,12,20);//case 26
return forcolores($numero,$numero_dias,10,21);//case 27
return forcolores($numero,$numero_dias,10,21);//case 28
return forcolores($numero,$numero_dias,11,22);//case 29
return forcolores($numero,$numero_dias,12,21);//case 30
return forcolores($numero,$numero_dias,10,21);//case 31
if ($numero_dias>31){
$tercio = floor($numero_dias/3);
return forcolores($numero,$numero_dias,$tercio,$tercio);//case mayor a 31
}
}
function colores ($numero,$numero_dias){   
$rojo = "#ea4335";
$naranja="#fbbc05";
$verde ="#4AB73B";

switch ($numero_dias) {
  case 0:
      return array($rojo);
      
   case 1:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
                                              } break;
    case 2:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $naranja;return array($color);}
          if($numero==2){$color= $verde;return array($color);}
                                              } break;
    case 3:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $naranja;return array($color);}
          if($numero==2){$color= $naranja;return array($color);}
          if($numero==3){$color= $verde;return array($color);}
                                              } break;
    case 4:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $naranja;return array($color);}
          if($numero==3){$color= $naranja;return array($color);}
          if($numero==4){$color= $verde;return array($color);}
                                              } break;
  case 5:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $naranja;return array($color);}
          if($numero==3){$color= $naranja;return array($color);}
          if($numero==4){$color= $verde;return array($color);}
          if($numero==5){$color= $verde;return array($color);}
                                              } break;
  case 6:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $naranja;return array($color);}
          if($numero==3){$color= $naranja;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $verde;return array($color);}
          if($numero==6){$color= $verde;return array($color);}
                                              } break;  
    case 7:
          for ($i=0; $i <= $numero_dias; $i++) { 
         if($numero==0){$color=  $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $naranja;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $verde;return array($color);}
          if($numero==6){$color= $verde;return array($color);}
          if($numero==7){$color= $verde;return array($color);}

                                              } break;     
    case 8:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color=  $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $naranja;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $verde;return array($color);}
          if($numero==7){$color= $verde;return array($color);}
          if($numero==8){$color= $verde;return array($color);}
                                              } break;
    case 9:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $verde;return array($color);}
          if($numero==8){$color= $verde;return array($color);}
          if($numero==8){$color= $verde;return array($color);}
                                              } break;
    case 10:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $verde;return array($color);}
          if($numero==8){$color= $verde;return array($color);}
          if($numero==9){$color= $verde;return array($color);}
          if($numero==10){$color= $verde;return array($color);}
                                              } break; 
    case 11:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $verde;return array($color);}
          if($numero==9){$color= $verde;return array($color);}
          if($numero==10){$color= $verde;return array($color);}
          if($numero==11){$color= $verde;return array($color);}
                                              } break; 
    case 12:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $naranja;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $verde;return array($color);}
          if($numero==9){$color= $verde;return array($color);}
          if($numero==10){$color= $verde;return array($color);}
          if($numero==11){$color= $verde;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
                                              } break;                      
    case 13:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $verde;return array($color);}
          if($numero==10){$color= $verde;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
                                              } break; 
     case 14:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $verde;return array($color);}
          if($numero==11){$color= $verde;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}

                                              } break;
     case 14:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $verde;return array($color);}
          if($numero==11){$color= $verde;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
                                              } break;
   case 15:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $naranja;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $verde;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
          if($numero==15){$color= $verde;return array($color);}

                                              } break ;       
    case 16:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
          if($numero==15){$color= $verde;return array($color);}
          if($numero==16){$color= $verde;return array($color);}

                                              } break ; 
    case 17:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
          if($numero==15){$color= $verde;return array($color);}
          if($numero==16){$color= $verde;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
                                              } break;
    case 17:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $verde;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
          if($numero==15){$color= $verde;return array($color);}
          if($numero==16){$color= $verde;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
                                              } break ;                                            
    case 18:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $verde;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
          if($numero==15){$color= $verde;return array($color);}
          if($numero==16){$color= $verde;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
                                              } break;
    case 19:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $naranja;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $verde;return array($color);}
          if($numero==15){$color= $verde;return array($color);}
          if($numero==16){$color= $verde;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
                                              } break;  
   case 20:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $naranja;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $verde;return array($color);}
          if($numero==16){$color= $verde;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
                                              } break ;                                                                                       
   case 21:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $naranja;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $verde;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
                                              } break ;  
   case 22:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
                                              } break;
   case 23:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $verde;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
                                              } break;
   case 24:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color= $naranja;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $verde;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
                                              } break;
  case 25:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color=  $rojo;return array($color);}
          if($numero==10){$color= $rojo;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $verde;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
                                              } break;
 case 26:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color=$rojo;return array($color);}
          if($numero==10){$color= $rojo;return array($color);}
          if($numero==11){$color= $rojo;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $naranja;return array($color);}
          if($numero==20){$color= $verde;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
          if($numero==26){$color= $verde;return array($color);}
                                              } break;
 case 27:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color= $rojo;return array($color);}
          if($numero==10){$color=$naranja; return array($color);}
          if($numero==11){$color=$naranja; return array($color);}
          if($numero==12){$color=$naranja; return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $naranja;return array($color);}
          if($numero==20){$color= $naranja;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
          if($numero==26){$color= $verde;return array($color);}
          if($numero==27){$color= $verde;return array($color);}
                                              } break;
 case 28:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color=$rojo;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $naranja;return array($color);}
          if($numero==20){$color= $naranja;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
          if($numero==26){$color= $verde;return array($color);}
          if($numero==27){$color= $verde;return array($color);}
          if($numero==28){$color= $verde;return array($color);}
                                                 } break;
 case 29:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color=$rojo;return array($color);}
          if($numero==10){$color= $rojo;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $naranja;return array($color);}
          if($numero==20){$color= $naranja;return array($color);}
          if($numero==21){$color= $naranja;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
          if($numero==26){$color= $verde;return array($color);}
          if($numero==27){$color= $verde;return array($color);}
          if($numero==28){$color= $verde;return array($color);}
          if($numero==29){$color= $verde;return array($color);}
                                              } break;
 case 30:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color=$rojo;return array($color);}
          if($numero==10){$color= $rojo;return array($color);}
          if($numero==11){$color= $rojo;return array($color);}
          if($numero==12){$color= $narnaja;return array($color);}
          if($numero==13){$color= $narnaja;return array($color);}
          if($numero==14){$color= $narnaja;return array($color);}
          if($numero==15){$color= $narnaja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $naranja;return array($color);}
          if($numero==20){$color= $naranja;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
          if($numero==26){$color= $verde;return array($color);}
          if($numero==27){$color= $verde;return array($color);}
          if($numero==28){$color= $verde;return array($color);}
          if($numero==29){$color= $verde;return array($color);}
          if($numero==30){$color= $verde;return array($color);}
                                              } break ;                                                                                           
 case 31:
          for ($i=0; $i <= $numero_dias; $i++) { 
          if($numero==0){$color= $rojo;return array($color);}
          if($numero==1){$color= $rojo;return array($color);}
          if($numero==2){$color= $rojo;return array($color);}
          if($numero==3){$color= $rojo;return array($color);}
          if($numero==4){$color= $rojo;return array($color);}
          if($numero==5){$color= $rojo;return array($color);}
          if($numero==6){$color= $rojo;return array($color);}
          if($numero==7){$color= $rojo;return array($color);}
          if($numero==8){$color= $rojo;return array($color);}
          if($numero==9){$color= $rojo;return array($color);}
          if($numero==10){$color= $naranja;return array($color);}
          if($numero==11){$color= $naranja;return array($color);}
          if($numero==12){$color= $naranja;return array($color);}
          if($numero==13){$color= $naranja;return array($color);}
          if($numero==14){$color= $naranja;return array($color);}
          if($numero==15){$color= $naranja;return array($color);}
          if($numero==16){$color= $naranja;return array($color);}
          if($numero==17){$color= $naranja;return array($color);}
          if($numero==18){$color= $naranja;return array($color);}
          if($numero==19){$color= $naranja;return array($color);}
          if($numero==20){$color= $naranja;return array($color);}
          if($numero==21){$color= $verde;return array($color);}
          if($numero==22){$color= $verde;return array($color);}
          if($numero==23){$color= $verde;return array($color);}
          if($numero==24){$color= $verde;return array($color);}
          if($numero==25){$color= $verde;return array($color);}
          if($numero==26){$color= $verde;return array($color);}
          if($numero==27){$color= $verde;return array($color);}
          if($numero==28){$color= $verde;return array($color);}
          if($numero==29){$color= $verde;return array($color);}
          if($numero==30){$color= $verde;return array($color);}
                                              } break ;  
}
}
##### Fin función Colores
?>