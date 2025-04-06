<meta charset="utf-8"/>
<style type="text/css">
    span{
    text-decoration: underline;
    }
.logo {
    font-family: "Arial Narrow";
    font-size: 14px;
    margin: 0.2cm 15cm 0cm 3cm ;
}
.IEM{margin: -1cm 3cm 0cm 6cm ;
    font-size:25px;
}
.sed{ margin: -5cm 0cm 0cm 5cm ; }
</style>
<script type="text/javascript" src="../../comun/js/jquery.js"></script>
<script type="text/javascript" src="../../comun/js/jsapi.js"></script>
<script type="text/javascript" src="../../comun/js/uds_api_contents.js"></script>
<script type="text/javascript" src="../../comun/js/funciones.js"></script>
<script type="text/javascript" >
    
</script>
<script type="text/javascript" >
function imprimir()
{
  var Obj = document.getElementById("desaparece");
  Obj.style.visibility = 'hidden';
  window.print();
}
    
</script>
<?php
require '../../comun/conexion.php';
require_once '../../comun/funciones.php';
$sql1='select count(*) from red';
$sql2='select count(*), GROUP_CONCAT(idioma_red SEPARATOR ",") as idioma_red from red GROUP BY idioma_red'; //cantidad de recursos por idioma
$sql3='select * from red'; // cantidad de recursos por nivel educativo
$sql4='select count(responsable),CONCAT (usuario.nombre, usuario.apellido) as nombre, GROUP_CONCAT(responsable SEPARATOR ",") as responsable from red,usuario where usuario.id_usuario = red.responsable GROUP BY responsable';//cantidad de recursos por idioma
$sql5='select count(formato) , GROUP_CONCAT(formato SEPARATOR ",") as formato from red GROUP BY formato';//cantidad de recursos por formato
$sql6='select count(dificultad), GROUP_CONCAT(dificultad SEPARATOR ",") as dificultad from red GROUP BY dificultad';//cantidad de recursos por dificultad
$sql7='select titulo_red,estrellas from red ';//Los  recursos mejores calificados
$sql8='select count(*) from red where scorm="si" ';//cantidad de scorm
$sql9='select count(*) from red where adjunto="si" ';//cantidad de adjuntos
$sql10='SELECT materia.nombre_materia,count(red.materia_red) as cantdad_red FROM red,materia where materia.id_materia = red.materia_red GROUP BY red.materia_red';//cantidad de recursos por dificultad

echo '<div id="contenedor_general" align="center">';

$inf_ie ='SELECT * FROM `institucion_educativa` WHERE id_institucion_educativa=1';
$consulta = $mysqli ->query($inf_ie);
$resultados = $consulta -> num_rows;
if($resultados<=0 ){ ?>
  
    <script type="text/javascript" >
    alert('No hay resultados');
   window.history.back();
            </script>
<?php }
$html ="";
if($rowinfosede =$consulta ->fetch_assoc()  ){

$html.='<img style="margin-left:50px;" height="60" src="'.SGA_COMUN_SGA_DATA.'/'.$rowinfosede["logo_institucion"].'"></img>';

$html.='<img style="margin-left:-650px;" height="60" src="'.SGA_COMUN_SGA_DATA.'/'.$rowinfosede["logo_institucion"].'"></img>
    <p class="IEM"><strong>
      '.$rowinfosede["nombre_institucion"].' <br/>
      </strong>   </p>
                 <br/>
<h4 align="center">REPORTE GENERAL DE RECURSOS EDUCATIVOS<br/> </h4>';
}
echo $html;
echo '<input onclick="imprimir();"  type="submit" id="desaparece" value="Imprimir" >';

#####
if(!empty(consultar_datos($sql1))){
echo '<pre>  Cantidad de RED ';print_r(consultar_datos($sql1)[0][0]);echo '</pre>';
}
#######
 echo '<script>
var datos4 = {
      "elementos": [';
foreach (consultar_datos($sql7) as $key => $value) {
$estrellas= json_decode(stripslashes($value[1]),true);
$cantidad_estrellas=0;
   foreach ($estrellas as $clave => $valor) {
    $cantidad_estrellas = $cantidad_estrellas+$valor;
   }
echo '{ "cualitativo":" '.$value[0].'", "cuantitativo":"'.$cantidad_estrellas.'" },';
     }
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED mejor valorados",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="div4",tipo_grafica="PieChart",ancho="700px",alto="200px",datos4);
});
</script>';
#echo '<div id="div4" style="width: 500px; height: 300px;"></div>';

#####
#####
$primero =0 ;$segundo=0; $tercero =0; $cuarto=0; $quinto=0;
foreach (consultar_datos($sql3) as $clave => $campo ) {
$grados = json_decode($campo[6]);
if (is_array($grados) || is_object($grados)){

foreach($grados as $clave => $grado){
if(!empty($grado)){
switch ($grado) {
 case '1':
 $primero++;
  break;

 case '2':
 $segundo++;
  break;
 
 case '3':
  $tercero++;
  break;
 
 case '4':
  $cuarto++;
  break;
 
 case '5':
  echo $quinto.'</br>';
  break;

}
}
}
}
}
$cuantitativos=[$primero,$segundo,$tercero,$cuarto,$quinto];
echo '<script>
var datos = {
      "elementos": [';
     foreach ($cuantitativos as $value => $clave) {
echo '{ "cualitativo":"'.($value+1).'", "cuantitativo":"'.$clave.'" },';
     }
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED por grado",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="div",tipo_grafica="ColumnChart",ancho="700px",alto="200px",datos);
});
</script>';
echo '<div id="div" style="width: 500px; height: 300px;"></div>';
######

echo '<script>
var datosmateria = {
      "elementos": [';
foreach (consultar_datos($sql10) as $clave => $value) {
echo '{ "cualitativo":"'.($value[0]).'", "cuantitativo":"'.$value[1].'" },';
     }
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED por materia",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="divmateria",tipo_grafica="ColumnChart",ancho="700px",alto="200px",datosmateria);
});
</script>';
echo '<div id="divmateria" style="width: 500px; height: 300px;"></div>';
#####

####

echo '<script>
var datos1 = {
      "elementos": [';
     foreach (consultar_datos($sql4) as $clave => $value) {
$usuario = explode(",",$value[1]);
echo '{ "cualitativo":"'.($usuario[0]).'", "cuantitativo":"'.$value[0].'" },';
     }
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED por Usuario",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="div1",tipo_grafica="BarChart",ancho="700px",alto="200px",datos1);
});
</script>';
echo '<div id="div1" style="width: 500px; height: 300px;"></div>';

#######
 echo '<script>
var datos3 = {
      "elementos": [';
     foreach (consultar_datos($sql6) as $key => $value) {
$dificultad[] = explode(",",$value[1]);

echo '{ "cualitativo":"'.$dificultad[0][0].'", "cuantitativo":"'.$value[0].'" },';
     }
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED por nivel de dificultad",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="div3",tipo_grafica="PieChart",ancho="700px",alto="200px",datos3);
});
</script>';
echo '<div id="div3" style="width: 500px; height: 300px;"></div>';
####### 
echo '<br><br>';
####
echo '<script>
var datosid = {
      "elementos": [';
foreach (consultar_datos($sql2) as $cantdad => $values  ) {
$idioma = explode(",", $values[1]);
echo '{ "cualitativo":"'.$idioma[0].'", "cuantitativo":"'.$values[0].'" },';
     }
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED por Idioma",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="divid",tipo_grafica="ColumnChart",ancho="700px",alto="200px",datosid);
});
</script>';
echo '<div id="divid" style="width: 500px; height: 300px;"></div>';


####
 echo '<script>
var datosscorm = {
      "elementos": [';
  echo '{ "cualitativo":"Scorm", "cuantitativo":"'.consultar_datos($sql8)[0][0].'" },';
  
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED scorm",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="divscorm",tipo_grafica="PieChart",ancho="700px",alto="200px",datosscorm);
});
</script>';
echo '<div id="divscorm" style="width: 500px; height: 300px;"></div>';
####

#####
#echo '<br><br/>Cantidad de recursos con adjunto ';
 echo '<script>
var datosadjunto = {
      "elementos": [';
  echo '{ "cualitativo":"Adjunto", "cuantitativo":"'.consultar_datos($sql9)[0][0].'" },';
  
echo '
    ]
} ;
google.setOnLoadCallback(function() {
 Graficooffline(titulo="Grafica de RED adjunto",cualitativa="Opcíón",cuantitativa="Cantidad de RED",contenedor="divadjunto",tipo_grafica="PieChart",ancho="700px",alto="200px",datosadjunto);
});
</script>';
echo '<div id="divadjunto" style="width: 500px; height: 300px;"></div>';
#####
##### Cantidad de recursos por dificultad
echo '</div>';

?>